<?php

// Миграция создает историю действий по заявкам.
use Illuminate\Database\Migrations\Migration;
// Blueprint описывает колонки таблицы.
use Illuminate\Database\Schema\Blueprint;
// Schema управляет таблицами базы.
use Illuminate\Support\Facades\Schema;

// Анонимный класс миграции истории заявок.
return new class extends Migration
{
    // Метод up создает таблицу request_histories.
    public function up(): void
    {
        // Таблица request_histories хранит события по заявке:
        // создана, согласована кадровиком, утверждена директором, отклонена.
        Schema::create('request_histories', function (Blueprint $table) {
            // ID записи истории.
            $table->id();
            // К какой заявке относится событие.
            // Если заявку удалить, ее история тоже удалится.
            $table->foreignId('document_request_id')->constrained()->cascadeOnDelete();
            // Кто сделал действие. Поле nullable, потому что событие может быть системным.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            // Технический код действия: created, hr_approved, director_approved, rejected.
            $table->string('action');
            // Короткий заголовок события для интерфейса.
            $table->string('title');
            // Подробное описание события. Может быть пустым.
            $table->text('body')->nullable();
            // created_at и updated_at. created_at показывает время события.
            $table->timestamps();
        });
    }

    // Метод down удаляет таблицу при откате миграции.
    public function down(): void
    {
        // Удаляем таблицу истории заявок.
        Schema::dropIfExists('request_histories');
    }
};
