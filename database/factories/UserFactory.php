<?php

// Пространство имен фабрик базы данных.
// Фабрики используются в тестах, чтобы быстро создавать тестовых пользователей.
namespace Database\Factories;

// Модель пользователя, для которой создается фабрика.
use App\Models\User;
// Базовый класс фабрики Laravel.
use Illuminate\Database\Eloquent\Factories\Factory;
// Hash нужен, чтобы пароль в тестовых пользователях был сохранен в зашифрованном виде.
use Illuminate\Support\Facades\Hash;
// Str нужен для генерации случайной строки remember_token.
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
// UserFactory описывает, как автоматически создать пользователя для тестов.
// Например тест может вызвать User::factory()->create(), и Laravel возьмет данные отсюда.
class UserFactory extends Factory
{
    // Статическое поле хранит уже созданный хэш пароля.
    // Так Laravel не пересчитывает Hash::make('password') для каждого тестового пользователя.
    protected static ?string $password;

    // Метод definition возвращает стандартные данные тестового пользователя.
    // Эти данные используются только для тестов и автоматического заполнения.
    public function definition(): array
    {
        return [
            // fake()->name() генерирует случайное имя.
            'name' => fake()->name(),
            // fake()->unique()->safeEmail() генерирует уникальный безопасный email.
            'email' => fake()->unique()->safeEmail(),
            // now() ставит текущее время как дату подтверждения email.
            'email_verified_at' => now(),
            // Пароль у тестовых пользователей - password, но в базу идет хэш.
            'password' => static::$password ??= Hash::make('password'),
            // remember_token нужен Laravel для функции "запомнить меня".
            'remember_token' => Str::random(10),
        ];
    }

    // Метод unverified создает состояние пользователя без подтвержденного email.
    // Его можно использовать в тестах, если нужно проверить неподтвержденного пользователя.
    public function unverified(): static
    {
        // state меняет стандартные данные фабрики только для конкретного вызова.
        return $this->state(fn (array $attributes) => [
            // null означает, что email не подтвержден.
            'email_verified_at' => null,
        ]);
    }
}
