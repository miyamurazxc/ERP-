@extends('layouts.auth', ['title' => 'Вход в ERP Кадры'])

{{-- Секция content вставляется в @yield('content') файла layouts/auth.blade.php. --}}
@section('content')
    {{-- auth-split делит экран на левую красивую часть и правую форму входа. --}}
    <main class="auth-split">
        {{-- Левая визуальная часть страницы входа. --}}
        <section class="auth-visual">
            {{-- Декоративный фон с линиями/элементами, aria-hidden скрывает его от скринридеров. --}}
            <div class="mesh-bg" aria-hidden="true">
                <span></span><span></span><span></span><span></span><span></span>
            </div>

            {{-- Главный текст о системе. --}}
            <div class="auth-hero">
                {{-- Логотип системы. asset() строит ссылку на файл из public/images. --}}
                <div class="auth-mark">
                    <img src="{{ asset('images/erp-logo.png') }}" alt="ERP Кадры">
                </div>
                {{-- Название на большом экране входа. --}}
                <h1>Администратор ERP Кадры</h1>
                {{-- Краткое описание, что делает сайт. --}}
                <p>
                    Единая система для заявок на отпуск, больничных,
                    согласования документов и контроля статусов сотрудников.
                </p>

                {{-- Список преимуществ/модулей проекта. --}}
                <div class="auth-benefits">
                    <div>
                        <strong>01</strong>
                        <span>Отпуска и больничные в одном журнале</span>
                    </div>
                    <div>
                        <strong>02</strong>
                        <span>Согласование кадровиком и директором</span>
                    </div>
                    <div>
                        <strong>03</strong>
                        <span>Комментарии, уведомления и история заявки</span>
                    </div>
                    <div>
                        <strong>04</strong>
                        <span>Админ управляет ролями и правами</span>
                    </div>
                </div>
            </div>
        </section>

        {{-- Правая часть: форма авторизации. --}}
        <section class="auth-panel">
            {{-- Мини-бренд над формой. --}}
            <div class="auth-brand">
                <div class="auth-brand-icon">
                    <img src="{{ asset('images/erp-logo.png') }}" alt="ERP Кадры">
                </div>
                <strong>ERP <span>Кадры</span></strong>
            </div>

            {{-- Заголовок формы входа. --}}
            <div class="auth-heading">
                <h2>Добро пожаловать</h2>
                <p>Войдите в свою учетную запись, чтобы продолжить работу с кадровыми документами.</p>
            </div>

            {{-- Форма отправляет email и пароль в маршрут login.store, то есть AuthController@store. --}}
            <form method="POST" action="{{ route('login.store') }}" class="auth-form">
                {{-- CSRF-защита: Laravel проверяет, что форму отправил наш сайт, а не чужая страница. --}}
                @csrf

                {{-- Поле email. old('email') возвращает старое значение после ошибки входа. --}}
                <label>
                Адрес электронной почты
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required autofocus>
                </label>

                {{-- Поле пароля. Пароль не заполняем обратно после ошибки ради безопасности. --}}
                <label>
                    Пароль
                    <input type="password" name="password" placeholder="Введите пароль" required>
                </label>

                {{-- remember отправляет 1, если пользователь хочет долгую авторизацию. --}}
                <label class="checkbox-line auth-remember">
                    <input type="checkbox" name="remember" value="1">
                    Запомнить меня
                </label>

                {{-- Кнопка отправляет форму входа. --}}
                <button class="auth-submit" type="submit">Войти</button>
            </form>

            {{-- Ссылка на регистрацию для новых пользователей. --}}
            <p class="auth-switch">
                У вас нет аккаунта?
                <a href="{{ route('register') }}">Создайте его.</a>
            </p>
        </section>
    </main>
@endsection
