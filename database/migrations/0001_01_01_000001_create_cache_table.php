<?php

// Миграция нужна для создания служебных таблиц кэша Laravel.
use Illuminate\Database\Migrations\Migration;
// Blueprint описывает колонки таблицы.
use Illuminate\Database\Schema\Blueprint;
// Schema создает и удаляет таблицы.
use Illuminate\Support\Facades\Schema;

// Анонимный класс миграции, который Laravel запускает командой php artisan migrate.
return new class extends Migration
{
    // Метод up создает таблицы.
    public function up(): void
    {
        // Таблица cache хранит временные данные Laravel.
        // Например сюда могут попадать результаты кэширования, чтобы сайт работал быстрее.
        Schema::create('cache', function (Blueprint $table) {
            // key - уникальное имя записи кэша.
            $table->string('key')->primary();
            // value - сохраненное значение.
            $table->mediumText('value');
            // expiration - время, когда кэш должен устареть.
            $table->bigInteger('expiration')->index();
        });

        // Таблица cache_locks нужна Laravel для блокировок.
        // Блокировки помогают не выполнять одну и ту же задачу одновременно несколько раз.
        Schema::create('cache_locks', function (Blueprint $table) {
            // key - уникальное имя блокировки.
            $table->string('key')->primary();
            // owner - кто сейчас владеет блокировкой.
            $table->string('owner');
            // expiration - когда блокировка закончится.
            $table->bigInteger('expiration')->index();
        });
    }

    // Метод down откатывает миграцию.
    public function down(): void
    {
        // Удаляем таблицу cache при откате.
        Schema::dropIfExists('cache');
        // Удаляем таблицу cache_locks при откате.
        Schema::dropIfExists('cache_locks');
    }
};
