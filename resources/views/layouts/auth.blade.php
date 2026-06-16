<!DOCTYPE html>
{{-- Layout для страниц авторизации: вход и регистрация. --}}
{{-- Он отличается от layouts/app.blade.php тем, что здесь нет бокового меню. --}}
<html lang="ru">
<head>
    {{-- Кодировка страницы, чтобы русский текст отображался правильно. --}}
    <meta charset="utf-8">
    {{-- Делает страницу адаптивной для телефона и компьютера. --}}
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- Заголовок вкладки браузера. Если title не передали, будет "ERP Кадры". --}}
    <title>{{ $title ?? 'ERP Кадры' }}</title>
    {{-- Подключает CSS и JS проекта через Vite. --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
{{-- auth-body нужен CSS, чтобы у страницы входа/регистрации был отдельный дизайн. --}}
<body class="auth-body">
    {{-- Если контроллер положил success в сессию, показываем зеленое сообщение. --}}
    @if (session('success'))
        <div class="auth-alert success">{{ session('success') }}</div>
    @endif

    {{-- Если Laravel нашел ошибки валидации, показываем их пользователю. --}}
    @if ($errors->any())
        <div class="auth-alert error">
            {{-- Выводим каждую ошибку отдельной строкой. --}}
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    {{-- Сюда вставляется конкретная страница: login.blade.php или register.blade.php. --}}
    @yield('content')
</body>
</html>
