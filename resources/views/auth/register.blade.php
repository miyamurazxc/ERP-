@extends('layouts.auth', ['title' => 'Регистрация в ERP Кадры'])

{{-- Секция content вставляется в общий layout авторизации. --}}
@section('content')
    {{-- Экран регистрации построен так же, как экран входа: слева описание, справа форма. --}}
    <main class="auth-split">
        {{-- Левая декоративно-информационная часть. --}}
        <section class="auth-visual">
            {{-- Фоновая сетка/линии для дизайна. --}}
            <div class="mesh-bg" aria-hidden="true">
                <span></span><span></span><span></span><span></span><span></span>
            </div>

            {{-- Текст объясняет пользователю, что после регистрации нужен ответ админа. --}}
            <div class="auth-hero">
                <div class="auth-mark">
                    <img src="{{ asset('images/erp-logo.png') }}" alt="ERP Кадры">
                </div>
                <h1>Регистрация в ERP Кадры</h1>
                <p>
                    Создайте аккаунт кандидата. Администратор проверит заявку,
                    одобрит доступ и отдельно назначит роль с нужными правами.
                </p>

                {{-- Коротко показываем бизнес-логику регистрации. --}}
                <div class="auth-benefits">
                    <div>
                        <strong>01</strong>
                        <span>Кандидат ждет решения администратора</span>
                    </div>
                    <div>
                        <strong>02</strong>
                        <span>После одобрения можно войти в систему</span>
                    </div>
                    <div>
                        <strong>03</strong>
                        <span>Права выдаются отдельно и безопасно</span>
                    </div>
                </div>
            </div>
        </section>

        {{-- Правая часть: форма создания аккаунта. --}}
        <section class="auth-panel">
            {{-- Логотип и название проекта. --}}
            <div class="auth-brand">
                <div class="auth-brand-icon">
                    <img src="{{ asset('images/erp-logo.png') }}" alt="ERP Кадры">
                </div>
                <strong>ERP <span>Кадры</span></strong>
            </div>

            {{-- Заголовок формы регистрации. --}}
            <div class="auth-heading">
                <h2>Создать аккаунт</h2>
                <p>Заполните данные, чтобы отправить заявку на доступ к системе.</p>
            </div>

            {{-- Форма отправляется в AuthController@registerStore через route('register.store'). --}}
            <form method="POST" action="{{ route('register.store') }}" class="auth-form">
                {{-- CSRF-защита Laravel. --}}
                @csrf

                {{-- Имя нового пользователя. --}}
                <label>
                    Имя
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="Ваше имя" required autofocus>
                </label>

                {{-- Email должен быть уникальным, это проверяется в AuthController. --}}
                <label>
                Адрес электронной почты
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required>
                </label>

                {{-- Пароль минимум 6 символов. --}}
                <label>
                    Пароль
                    <input type="password" name="password" placeholder="Минимум 6 символов" required minlength="6">
                </label>

                {{-- password_confirmation нужен правилу confirmed в контроллере. --}}
                <label>
                    Повторите пароль
                    <input type="password" name="password_confirmation" placeholder="Повторите пароль" required minlength="6">
                </label>

                {{-- После отправки пользователь создается как candidate и ждет одобрения. --}}
                <button class="auth-submit" type="submit">Зарегистрироваться</button>
            </form>

            {{-- Ссылка обратно на страницу входа. --}}
            <p class="auth-switch">
                Уже есть аккаунт?
                <a href="{{ route('login') }}">Войти.</a>
            </p>
        </section>
    </main>
@endsection
