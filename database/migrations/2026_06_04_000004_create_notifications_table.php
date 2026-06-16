<?php

// Миграция создает простые уведомления внутри ERP.
use Illuminate\Database\Migrations\Migration;
// Blueprint описывает колонки таблицы.
use Illuminate\Database\Schema\Blueprint;
// Schema создает и удаляет таблицы.
use Illuminate\Support\Facades\Schema;

// Анонимный класс миграции для таблицы erp_notifications.
return new class extends Migration
{
    // Метод up выполняется командой php artisan migrate.
    public function up(): void
    {
        // Создаем таблицу уведомлений.
        // Она нужна, чтобы показывать пользователям события: новая заявка, заявка утверждена и т.д.
        Schema::create('erp_notifications', function (Blueprint $table) {
            // ID уведомления.
            $table->id();
            // Кому предназначено уведомление.
            // Если пользователя удалить, его уведомления тоже удалятся.
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // К какой заявке относится уведомление.
            // Поле nullable, потому что теоретически уведомление может быть и без заявки.
            $table->foreignId('document_request_id')->nullable()->constrained()->nullOnDelete();
            // Короткий заголовок уведомления.
            $table->string('title');
            // Подробный текст уведомления.
            $table->text('body');
            // Когда уведомление прочитали. Сейчас поле подготовлено на будущее.
            $table->timestamp('read_at')->nullable();
            // created_at и updated_at.
            $table->timestamps();
        });
    }

    // Метод down откатывает миграцию.
    public function down(): void
    {
        // Удаляем таблицу уведомлений.
        Schema::dropIfExists('erp_notifications');
    }
};
