@extends('layouts.app', ['title' => 'Пользователи и роли'])

{{-- Страница админки вставляется в основной layout с боковым меню. --}}
@section('content')
    {{-- Заголовок страницы. --}}
    <header class="page-header">
        <div>
            <p class="eyebrow">Администрирование</p>
            <h1>Пользователи и роли</h1>
        </div>
    </header>

    {{-- Форма создания нового пользователя админом. --}}
    <section class="panel user-create-panel">
        <h2>Создать пользователя</h2>

        {{-- Форма отправляется в AdminUserController@store. --}}
        <form method="POST" action="{{ route('admin.users.store') }}" class="user-create-form">
            {{-- CSRF-защита обязательна для POST-форм Laravel. --}}
            @csrf

            {{-- Имя нового пользователя. --}}
            <label>
                Имя
                <input type="text" name="name" value="{{ old('name') }}" required>
            </label>

            {{-- Email нового пользователя, он должен быть уникальным. --}}
            <label>
                Email
                <input type="email" name="email" value="{{ old('email') }}" required>
            </label>

            {{-- Пароль нового пользователя. В модели User он сохранится как хэш. --}}
            <label>
                Пароль
                <input type="password" name="password" required minlength="6">
            </label>

            {{-- Роль определяет, какие права получит пользователь. --}}
            <label>
                Роль
                <select name="role" required>
                    {{-- $roles приходит из AdminUserController@index. --}}
                    @foreach ($roles as $value => $label)
                        {{-- value сохраняется в базу, label показывается человеку. --}}
                        <option value="{{ $value }}" @selected(old('role') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>

            {{-- Отправляет форму создания пользователя. --}}
            <button class="primary-button" type="submit">Создать</button>
        </form>
    </section>

    {{-- Форма поиска сотрудников. Метод GET нужен, чтобы текст поиска был виден в URL. --}}
    <form method="GET" action="{{ route('admin.users.index') }}" class="filters admin-user-search">
        <label>
            Поиск сотрудника
            <input
                type="search"
                {{-- q - параметр поиска, который читает AdminUserController@index. --}}
                name="q"
                value="{{ $search ?? '' }}"
                placeholder="Имя, email или роль"
            >
        </label>

        {{-- Кнопка запускает поиск. --}}
        <button class="primary-button" type="submit">Найти</button>

        {{-- Если поиск активен, показываем ссылку для сброса фильтра. --}}
        @if (! empty($search))
            <a href="{{ route('admin.users.index') }}" class="ghost-button search-reset">Сбросить</a>
        @endif
    </form>

    {{-- Таблица всех найденных пользователей. --}}
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
                {{-- @forelse показывает пользователей, а если список пустой - блок @empty. --}}
                @forelse ($users as $user)
                    <tr>
                        <td>
                            <div class="table-user">
                                {{-- Аватар из первой буквы имени. --}}
                                <span class="table-avatar">{{ mb_substr($user->name, 0, 1) }}</span>
                                <div>
                                    {{-- Имя и русское название роли пользователя. --}}
                                    <strong>{{ $user->name }}</strong>
                                    <small>{{ $user->roleLabel() }}</small>
                                </div>
                            </div>
                        </td>
                        {{-- Email пользователя. --}}
                        <td>{{ $user->email }}</td>
                        <td>
                            {{-- Красивый бейдж роли. Класс role-... нужен CSS для цвета. --}}
                            <span class="role-badge role-{{ $user->role }}">{{ $user->roleLabel() }}</span>
                        </td>
                        <td>
                            <div class="row-actions">
                                {{-- Кнопка-карандаш открывает модальное окно редактирования этого пользователя. --}}
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

                                {{-- Форма удаления пользователя. DELETE обрабатывает AdminUserController@destroy. --}}
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm(@js('Удалить пользователя '.$user->name.'?'));">
                                    {{-- CSRF-защита. --}}
                                    @csrf
                                    {{-- HTML не умеет DELETE напрямую, поэтому Laravel использует скрытое поле @method. --}}
                                    @method('DELETE')
                                    {{-- Кнопка удаления пользователя. --}}
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
                    {{-- Если по поиску ничего не найдено. --}}
                    <tr>
                        <td colspan="4" class="empty-table">
                            Сотрудники не найдены. Попробуйте изменить текст поиска.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>

    {{-- Для каждого пользователя создается отдельное модальное окно редактирования роли. --}}
    @foreach ($users as $user)
        {{-- hidden означает, что окно скрыто, пока JS не откроет его по кнопке-карандашу. --}}
        <div class="modal-backdrop" id="user-modal-{{ $user->id }}" hidden>
            <div class="modal-window" role="dialog" aria-modal="true" aria-labelledby="user-modal-title-{{ $user->id }}">
                {{-- Верх модального окна: имя, email и кнопка закрытия. --}}
                <div class="modal-head">
                    <div>
                        <p class="eyebrow">Редактирование</p>
                        <h2 id="user-modal-title-{{ $user->id }}">{{ $user->name }}</h2>
                        <small>{{ $user->email }}</small>
                    </div>

                    <button class="modal-close" type="button" data-modal-close aria-label="Закрыть">&times;</button>
                </div>

                {{-- Форма изменения роли пользователя. PATCH обрабатывает AdminUserController@update. --}}
                <form method="POST" action="{{ route('admin.users.update', $user) }}" class="modal-form">
                    {{-- CSRF-защита формы. --}}
                    @csrf
                    {{-- Метод PATCH означает частичное обновление пользователя. --}}
                    @method('PATCH')

                    {{-- Админ выбирает только роль. Разрешения включаются автоматически в контроллере. --}}
                    <label>
                        Роль
                        <select name="role">
                            {{-- Перебираем список всех ролей. --}}
                            @foreach ($roles as $value => $label)
                                {{-- @selected отмечает текущую роль пользователя. --}}
                                <option value="{{ $value }}" @selected($user->role === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>

                    {{-- Подсказка объясняет, почему чекбоксов прав больше нет. --}}
                    <p class="role-help">
                        Разрешения назначаются автоматически по выбранной роли.
                    </p>

                    {{-- Кнопки модального окна. --}}
                    <div class="modal-actions">
                        <button class="secondary-button" type="button" data-modal-close>Отмена</button>
                        <button class="primary-button" type="submit">Сохранить</button>
                    </div>
                </form>

                {{-- Если пользователь еще не одобрен или был отклонен, показываем кнопки доступа. --}}
                @if (! $user->is_approved || $user->is_rejected)
                    <div class="modal-access-actions">
                        {{-- Кнопка одобрения появляется только если пользователь еще не одобрен. --}}
                        @if (! $user->is_approved)
                            <form method="POST" action="{{ route('admin.users.approve', $user) }}">
                                @csrf
                                @method('PATCH')
                                <button class="small-button" type="submit">Одобрить доступ</button>
                            </form>
                        @endif

                        {{-- Кнопка отклонения доступа. --}}
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
