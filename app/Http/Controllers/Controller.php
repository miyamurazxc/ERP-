<?php

// Пространство имен контроллеров.
// Все контроллеры сайта лежат в app/Http/Controllers и начинаются с этого namespace.
namespace App\Http\Controllers;

// Базовый контроллер проекта.
// Сам он ничего не делает, но от него наследуются другие контроллеры:
// AuthController, DocumentRequestController, AdminUserController и RequestCommentController.
// Это стандартная точка Laravel, куда при необходимости можно добавить общую логику
// для всех контроллеров сразу.
abstract class Controller
{
    // Сейчас общей логики нет, поэтому класс пустой.
    // Пустой базовый Controller - нормальная стандартная структура Laravel.
}
