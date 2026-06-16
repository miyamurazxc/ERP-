<?php

// Миграция создает служебные таблицы очередей Laravel.
use Illuminate\Database\Migrations\Migration;
// Blueprint описывает колонки таблиц.
use Illuminate\Database\Schema\Blueprint;
// Schema управляет созданием и удалением таблиц.
use Illuminate\Support\Facades\Schema;

// Анонимный класс миграции, который выполняется через php artisan migrate.
return new class extends Migration
{
    // Метод up создает таблицы очередей.
    public function up(): void
    {
        // jobs хранит фоновые задачи Laravel.
        // В текущем ERP проекте очереди почти не используются, но Laravel создает таблицу по умолчанию.
        Schema::create('jobs', function (Blueprint $table) {
            // ID задачи.
            $table->id();
            // Название очереди, например default.
            $table->string('queue')->index();
            // payload хранит данные задачи.
            $table->longText('payload');
            // attempts показывает, сколько раз Laravel пытался выполнить задачу.
            $table->unsignedSmallInteger('attempts');
            // reserved_at - когда задача была взята в работу.
            $table->unsignedInteger('reserved_at')->nullable();
            // available_at - когда задачу можно выполнить.
            $table->unsignedInteger('available_at');
            // created_at - когда задача создана.
            $table->unsignedInteger('created_at');
        });

        // job_batches хранит группы фоновых задач.
        Schema::create('job_batches', function (Blueprint $table) {
            // ID группы задач.
            $table->string('id')->primary();
            // Название группы.
            $table->string('name');
            // Всего задач в группе.
            $table->integer('total_jobs');
            // Сколько задач еще ожидают выполнения.
            $table->integer('pending_jobs');
            // Сколько задач завершились ошибкой.
            $table->integer('failed_jobs');
            // ID задач, которые упали с ошибкой.
            $table->longText('failed_job_ids');
            // Дополнительные настройки группы.
            $table->mediumText('options')->nullable();
            // Время отмены группы.
            $table->integer('cancelled_at')->nullable();
            // Время создания группы.
            $table->integer('created_at');
            // Время завершения группы.
            $table->integer('finished_at')->nullable();
        });

        // failed_jobs хранит задачи, которые не смогли выполниться.
        Schema::create('failed_jobs', function (Blueprint $table) {
            // ID ошибки.
            $table->id();
            // uuid - уникальный идентификатор упавшей задачи.
            $table->string('uuid')->unique();
            // connection - через какое подключение выполнялась очередь.
            $table->string('connection');
            // queue - название очереди.
            $table->string('queue');
            // payload - данные задачи.
            $table->longText('payload');
            // exception - текст ошибки.
            $table->longText('exception');
            // failed_at - когда задача упала.
            $table->timestamp('failed_at')->useCurrent();

            // Индекс ускоряет поиск ошибок по подключению, очереди и времени.
            $table->index(['connection', 'queue', 'failed_at']);
        });
    }

    // Метод down удаляет таблицы при откате миграции.
    public function down(): void
    {
        // Удаляем таблицу задач.
        Schema::dropIfExists('jobs');
        // Удаляем таблицу групп задач.
        Schema::dropIfExists('job_batches');
        // Удаляем таблицу упавших задач.
        Schema::dropIfExists('failed_jobs');
    }
};
