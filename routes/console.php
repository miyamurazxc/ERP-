<?php

// Этот файл содержит консольные команды Laravel.
// Они запускаются не через браузер, а через терминал командой php artisan ...

// Inspiring - стандартный класс Laravel, который возвращает случайную цитату.
use Illuminate\Foundation\Inspiring;
// Artisan - фасад для регистрации собственных консольных команд.
use Illuminate\Support\Facades\Artisan;

// Стандартная демо-команда Laravel: php artisan inspire.
Artisan::command('inspire', function () {
    // Выводит вдохновляющую цитату в терминал.
    $this->comment(Inspiring::quote());
// purpose задает описание команды, которое видно в списке php artisan list.
})->purpose('Display an inspiring quote');
