@extends('layouts.app', ['title' => 'Заявка #' . $requestItem->id])

@section('content')
    <header class="page-header request-show-header">
        <div>
            <p class="eyebrow">{{ $requestItem->typeLabel() }}</p>
            <h1>Заявка #{{ $requestItem->id }}</h1>
        </div>

        <a class="secondary-button" href="{{ route('requests.index') }}">Назад</a>
    </header>

    <section class="request-show">
        <div class="panel request-summary-panel">
            <div class="request-summary-top">
                <div>
                    <p class="eyebrow">Карточка заявки</p>
                    <h2>{{ $requestItem->typeLabel() }} сотрудника</h2>
                </div>

                <span class="status {{ $requestItem->status }}">{{ $requestItem->statusLabel() }}</span>
            </div>

            <div class="request-summary-grid">
                <div class="summary-item">
                    <span>Работник</span>
                    <strong>{{ $requestItem->user->name }}</strong>
                </div>

                <div class="summary-item">
                    <span>Период</span>
                    <strong>{{ $requestItem->start_date->format('d.m.Y') }} - {{ $requestItem->end_date->format('d.m.Y') }}</strong>
                </div>

                <div class="summary-item">
                    <span>Календарные дни</span>
                    <strong>{{ $requestItem->calendar_days }}</strong>
                </div>

                <div class="summary-item">
                    <span>Рабочие дни</span>
                    <strong>{{ $requestItem->working_days }}</strong>
                </div>

                <div class="summary-item">
                    <span>Кадровик</span>
                    <strong>{{ $requestItem->hrApprover?->name ?? 'Еще не согласовано' }}</strong>
                </div>

                <div class="summary-item">
                    <span>Директор</span>
                    <strong>{{ $requestItem->directorApprover?->name ?? 'Еще не утверждено' }}</strong>
                </div>
            </div>

            @if ($requestItem->comment)
                <div class="request-note">
                    <span>Комментарий работника</span>
                    <p>{{ $requestItem->comment }}</p>
                </div>
            @endif

            <div class="actions request-action-bar">
                @if ($currentUser->canApproveHr() && $requestItem->status === 'pending_hr')
                    <form method="POST" action="{{ route('requests.hr-approve', $requestItem) }}">
                        @csrf
                        @method('PATCH')
                        <button class="small-button" type="submit">Согласовать кадровиком</button>
                    </form>
                @endif

                @if ($currentUser->canApproveDirector() && $requestItem->status === 'pending_director')
                    <form method="POST" action="{{ route('requests.director-approve', $requestItem) }}">
                        @csrf
                        @method('PATCH')
                        <button class="small-button" type="submit">Утвердить директором</button>
                    </form>
                @endif

                @if (($currentUser->canApproveHr() || $currentUser->canApproveDirector()) && ! in_array($requestItem->status, ['approved', 'rejected'], true))
                    <form method="POST" action="{{ route('requests.reject', $requestItem) }}">
                        @csrf
                        @method('PATCH')
                        <button class="danger-button" type="submit">Отклонить</button>
                    </form>
                @endif

                @if ($requestItem->status === 'rejected' && $currentUser->canCreateRequests() && $currentUser->id === $requestItem->user_id)
                    <a class="primary-button" href="{{ route('requests.repeat', $requestItem) }}">Повторить заявку</a>
                @endif
            </div>
        </div>

        <div class="panel history-panel request-history-panel">
            <div class="section-title-row">
                <h2>История заявки</h2>
            </div>

            <div class="timeline">
                @forelse ($requestItem->histories as $history)
                    <article class="timeline-item">
                        <span class="timeline-dot"></span>
                        <div>
                            <strong>{{ $history->title }}</strong>
                            <p>{{ $history->body }}</p>
                            <small>
                                {{ $history->user?->name ?? 'Система' }}
                                · {{ $history->created_at->format('d.m.Y H:i') }}
                            </small>
                        </div>
                    </article>
                @empty
                    <p class="empty">История пока не записана.</p>
                @endforelse
            </div>
        </div>

        @php
            $visibleComments = $requestItem->comments->reject(function ($comment) use ($requestItem) {
                return $comment->user_id === $requestItem->user_id && $comment->body === $requestItem->comment;
            });

            $alreadyCommentedByCurrentRole = $requestItem->comments->contains(function ($comment) use ($currentUser) {
                return $comment->user?->role === $currentUser->role;
            });
        @endphp

        <div class="panel comments-panel request-comments-panel">
            <div class="section-title-row">
                <h2>Ответы по заявке</h2>
            </div>

            <div class="comments-list">
                @forelse ($visibleComments as $comment)
                    <article class="comment role-comment">
                        <div class="comment-head">
                            <strong>{{ $comment->user->name }}</strong>
                            <span>{{ $comment->user->roleLabel() }} · {{ $comment->created_at->format('d.m.Y H:i') }}</span>
                        </div>

                        <p>{{ $comment->body }}</p>
                    </article>
                @empty
                    <p class="empty">Ответов по заявке пока нет.</p>
                @endforelse
            </div>

            @if ($currentUser->canComment() && ! $alreadyCommentedByCurrentRole)
                <form class="comment-form new-comment-form" method="POST" action="{{ route('requests.comments.store', $requestItem) }}">
                    @csrf
                    <label>
                        Ваш ответ
                        <textarea name="body" rows="4" placeholder="Напишите комментарий по заявке" required></textarea>
                    </label>
                    <button class="primary-button" type="submit">Отправить ответ</button>
                </form>
            @elseif ($currentUser->canComment())
                <p class="role-comment-limit">
                    Ваша роль уже оставила ответ по этой заявке.
                </p>
            @endif
        </div>

    </section>
@endsection
