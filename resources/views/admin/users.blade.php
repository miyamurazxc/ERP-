@extends('layouts.app', ['title' => 'Пользователи и роли'])

@section('content')
    <header class="page-header">
        <div>
            <p class="eyebrow">Администрирование</p>
            <h1>Пользователи и роли</h1>
        </div>
    </header>

    <section class="panel user-create-panel">
        <h2>Создать пользователя</h2>

        <form method="POST" action="{{ route('admin.users.store') }}" class="user-create-form">
            @csrf

            <label>
                Имя
                <input type="text" name="name" value="{{ old('name') }}" required>
            </label>

            <label>
                Email
                <input type="email" name="email" value="{{ old('email') }}" required>
            </label>

            <label>
                Пароль
                <input type="password" name="password" required minlength="6">
            </label>

            <label>
                Роль
                <select name="role" required>
                    @foreach ($roles as $value => $label)
                        <option value="{{ $value }}" @selected(old('role') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>

            <button class="primary-button" type="submit">Создать</button>
        </form>
    </section>

    <form method="GET" action="{{ route('admin.users.index') }}" class="filters admin-user-search">
        <label>
            Поиск сотрудника
            <input
                type="search"
                name="q"
                value="{{ $search ?? '' }}"
                placeholder="Имя, email или роль"
            >
        </label>

        <button class="primary-button" type="submit">Найти</button>

        @if (! empty($search))
            <a href="{{ route('admin.users.index') }}" class="ghost-button search-reset">Сбросить</a>
        @endif
    </form>

    <section class="table-wrap">
        <table class="users-table">
            <thead>
                <tr>
                    <th>Пользователь</th>
                    <th>Email</th>
                    <th>Роль</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td>
                            <div class="table-user">
                                <span class="table-avatar">{{ mb_substr($user->name, 0, 1) }}</span>
                                <div>
                                    <strong>{{ $user->name }}</strong>
                                    <small>{{ $user->roleLabel() }}</small>
                                </div>
                            </div>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <span class="role-badge role-{{ $user->role }}">{{ $user->roleLabel() }}</span>
                        </td>
                        <td>
                            <div class="row-actions">
                                <button
                                    class="icon-button edit-user-button"
                                    type="button"
                                    data-modal-open="user-modal-{{ $user->id }}"
                                    aria-label="Редактировать {{ $user->name }}"
                                >
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M12 20h9" />
                                        <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z" />
                                    </svg>
                                </button>

                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm(@js('Удалить пользователя '.$user->name.'?'));">
                                    @csrf
                                    @method('DELETE')
                                    <button class="icon-button delete-user-button" type="submit" aria-label="Удалить {{ $user->name }}">
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M3 6h18" />
                                            <path d="M8 6V4h8v2" />
                                            <path d="M19 6l-1 14H6L5 6" />
                                            <path d="M10 11v6" />
                                            <path d="M14 11v6" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="empty-table">
                            Сотрудники не найдены. Попробуйте изменить текст поиска.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>

    @foreach ($users as $user)
        <div class="modal-backdrop" id="user-modal-{{ $user->id }}" hidden>
            <div class="modal-window" role="dialog" aria-modal="true" aria-labelledby="user-modal-title-{{ $user->id }}">
                <div class="modal-head">
                    <div>
                        <p class="eyebrow">Редактирование</p>
                        <h2 id="user-modal-title-{{ $user->id }}">{{ $user->name }}</h2>
                        <small>{{ $user->email }}</small>
                    </div>

                    <button class="modal-close" type="button" data-modal-close aria-label="Закрыть">&times;</button>
                </div>

                <form method="POST" action="{{ route('admin.users.update', $user) }}" class="modal-form">
                    @csrf
                    @method('PATCH')

                    <label>
                        Роль
                        <select name="role">
                            @foreach ($roles as $value => $label)
                                <option value="{{ $value }}" @selected($user->role === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>

                    <p class="role-help">
                        Разрешения назначаются автоматически по выбранной роли.
                    </p>

                    <div class="modal-actions">
                        <button class="secondary-button" type="button" data-modal-close>Отмена</button>
                        <button class="primary-button" type="submit">Сохранить</button>
                    </div>
                </form>

                @if (! $user->is_approved || $user->is_rejected)
                    <div class="modal-access-actions">
                        @if (! $user->is_approved)
                            <form method="POST" action="{{ route('admin.users.approve', $user) }}">
                                @csrf
                                @method('PATCH')
                                <button class="small-button" type="submit">Одобрить доступ</button>
                            </form>
                        @endif

                        <form method="POST" action="{{ route('admin.users.reject', $user) }}">
                            @csrf
                            @method('PATCH')
                            <button class="danger-button" type="submit">Отклонить доступ</button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    @endforeach
@endsection
