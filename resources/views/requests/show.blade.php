@extends('layouts.app', ['title' => 'Заявка #' . $requestItem->id])

{{-- Детальная страница одной заявки. --}}
@section('content')
    {{-- Верх страницы: тип заявки, номер и кнопка назад. --}}
    <header class="page-header request-show-header">
        <div>
            {{-- typeLabel() переводит vacation/sick_leave в русский текст. --}}
            <p class="eyebrow">{{ $requestItem->typeLabel() }}</p>
            {{-- ID нужен, чтобы заявку можно было легко найти и назвать. --}}
            <h1>Заявка #{{ $requestItem->id }}</h1>
        </div>

        {{-- Возвращает пользователя в журнал заявок. --}}
        <a class="secondary-button" href="{{ route('requests.index') }}">Назад</a>
    </header>

    {{-- Основной блок страницы: карточка заявки, история и ответы. --}}
    <section class="request-show">
        {{-- Главная карточка с данными заявки. --}}
        <div class="panel request-summary-panel">
            {{-- Верх карточки: название и статус. --}}
            <div class="request-summary-top">
                <div>
                    <p class="eyebrow">Карточка заявки</p>
                    <h2>{{ $requestItem->typeLabel() }} сотрудника</h2>
                </div>

                {{-- statusLabel() переводит pending_hr/approved/rejected в русский текст. --}}
                <span class="status {{ $requestItem->status }}">{{ $requestItem->statusLabel() }}</span>
            </div>

            {{-- Сетка основных данных заявки. --}}
            <div class="request-summary-grid">
                {{-- Работник, который создал заявку. --}}
                <div class="summary-item">
                    <span>Работник</span>
                    <strong>{{ $requestItem->user->name }}</strong>
                </div>

                {{-- Период отпуска или больничного. --}}
                <div class="summary-item">
                    <span>Период</span>
                    <strong>{{ $requestItem->start_date->format('d.m.Y') }} - {{ $requestItem->end_date->format('d.m.Y') }}</strong>
                </div>

                {{-- Все дни периода вместе с выходными и праздниками. --}}
                <div class="summary-item">
                    <span>Календарные дни</span>
                    <strong>{{ $requestItem->calendar_days }}</strong>
                </div>

                {{-- Рабочие дни без субботы, воскресенья и праздников. --}}
                <div class="summary-item">
                    <span>Рабочие дни</span>
                    <strong>{{ $requestItem->working_days }}</strong>
                </div>

                {{-- Кто согласовал заявку на этапе кадровика. --}}
                <div class="summary-item">
                    <span>Кадровик</span>
                    <strong>{{ $requestItem->hrApprover?->name ?? 'Еще не согласовано' }}</strong>
                </div>

                {{-- Кто утвердил заявку на этапе директора. --}}
                <div class="summary-item">
                    <span>Директор</span>
                    <strong>{{ $requestItem->directorApprover?->name ?? 'Еще не утверждено' }}</strong>
                </div>
            </div>

            {{-- Комментарий, который работник написал при создании заявки. --}}
            @if ($requestItem->comment)
                <div class="request-note">
                    <span>Комментарий работника</span>
                    <p>{{ $requestItem->comment }}</p>
                </div>
            @endif

            {{-- Кнопки действий зависят от роли пользователя и текущего статуса заявки. --}}
            <div class="actions request-action-bar">
                {{-- Кадровик видит кнопку только когда заявка на этапе pending_hr. --}}
                @if ($currentUser->canApproveHr() && $requestItem->status === 'pending_hr')
                    <form method="POST" action="{{ route('requests.hr-approve', $requestItem) }}">
                        {{-- CSRF-защита формы. --}}
                        @csrf
                        {{-- PATCH нужен, потому что мы обновляем существующую заявку. --}}
                        @method('PATCH')
                        <button class="small-button" type="submit">Согласовать кадровиком</button>
                    </form>
                @endif

                {{-- Директор видит кнопку только когда заявка уже прошла кадровика. --}}
                @if ($currentUser->canApproveDirector() && $requestItem->status === 'pending_director')
                    <form method="POST" action="{{ route('requests.director-approve', $requestItem) }}">
                        @csrf
                        @method('PATCH')
                        <button class="small-button" type="submit">Утвердить директором</button>
                    </form>
                @endif

                {{-- Отклонить может кадровик или директор, пока заявка еще не финальная. --}}
                @if (($currentUser->canApproveHr() || $currentUser->canApproveDirector()) && ! in_array($requestItem->status, ['approved', 'rejected'], true))
                    <form method="POST" action="{{ route('requests.reject', $requestItem) }}">
                        @csrf
                        @method('PATCH')
                        <button class="danger-button" type="submit">Отклонить</button>
                    </form>
                @endif

                {{-- Если заявка отклонена, сам работник может быстро создать похожую заявку заново. --}}
                @if ($requestItem->status === 'rejected' && $currentUser->canCreateRequests() && $currentUser->id === $requestItem->user_id)
                    <a class="primary-button" href="{{ route('requests.repeat', $requestItem) }}">Повторить заявку</a>
                @endif
            </div>
        </div>

        {{-- История заявки показывает бизнес-процесс по шагам. --}}
        <div class="panel history-panel request-history-panel">
            <div class="section-title-row">
                <h2>История заявки</h2>
            </div>

            <div class="timeline">
                {{-- Перебираем события: создана, согласована, утверждена, отклонена. --}}
                @forelse ($requestItem->histories as $history)
                    <article class="timeline-item">
                        <span class="timeline-dot"></span>
                        <div>
                            {{-- Заголовок события. --}}
                            <strong>{{ $history->title }}</strong>
                            {{-- Описание события. --}}
                            <p>{{ $history->body }}</p>
                            <small>
                                {{-- Если user удален или событие системное, показываем "Система". --}}
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

        {{-- Небольшая подготовка данных прямо в Blade для блока ответов. --}}
        @php
            // Убираем из списка ответов первый комментарий работника,
            // потому что он уже показан выше как "Комментарий работника".
            $visibleComments = $requestItem->comments->reject(function ($comment) use ($requestItem) {
                return $comment->user_id === $requestItem->user_id && $comment->body === $requestItem->comment;
            });

            // Проверяем, оставляла ли текущая роль уже ответ по этой заявке.
            // Это ограничивает спам: одна роль может оставить только один служебный ответ.
            $alreadyCommentedByCurrentRole = $requestItem->comments->contains(function ($comment) use ($currentUser) {
                return $comment->user?->role === $currentUser->role;
            });
        @endphp

        {{-- Служебные ответы кадровика, директора или админа. --}}
        <div class="panel comments-panel request-comments-panel">
            <div class="section-title-row">
                <h2>Ответы по заявке</h2>
            </div>

            <div class="comments-list">
                {{-- Показываем ответы, кроме комментария работника из верхней карточки. --}}
                @forelse ($visibleComments as $comment)
                    <article class="comment role-comment">
                        <div class="comment-head">
                            {{-- Автор ответа и его роль. --}}
                            <strong>{{ $comment->user->name }}</strong>
                            <span>{{ $comment->user->roleLabel() }} · {{ $comment->created_at->format('d.m.Y H:i') }}</span>
                        </div>

                        {{-- Текст ответа. --}}
                        <p>{{ $comment->body }}</p>
                    </article>
                @empty
                    {{-- Если никто еще не ответил. --}}
                    <p class="empty">Ответов по заявке пока нет.</p>
                @endforelse
            </div>

            {{-- Если пользователь может комментировать и его роль еще не отвечала, показываем форму. --}}
            @if ($currentUser->canComment() && ! $alreadyCommentedByCurrentRole)
                <form class="comment-form new-comment-form" method="POST" action="{{ route('requests.comments.store', $requestItem) }}">
                    {{-- CSRF-защита формы ответа. --}}
                    @csrf
                    <label>
                        Ваш ответ
                        <textarea name="body" rows="4" placeholder="Напишите комментарий по заявке" required></textarea>
                    </label>
                    <button class="primary-button" type="submit">Отправить ответ</button>
                </form>
            {{-- Если эта роль уже оставила ответ, новую форму не показываем. --}}
            @elseif ($currentUser->canComment())
                <p class="role-comment-limit">
                    Ваша роль уже оставила ответ по этой заявке.
                </p>
            @endif
        </div>

    </section>
@endsection
