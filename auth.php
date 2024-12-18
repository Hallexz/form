<?php

// Функция для проверки, авторизован ли пользователь
function isUserLoggedIn() {
    // Проверяем, есть ли в сессии идентификатор пользователя
    return isset($_SESSION['user_id']);
}

// Функция для выхода из системы
function logout() {
    // Уничтожаем текущую сессию пользователя
    session_destroy();
    // Перенаправляем пользователя на страницу входа
    header("Location: login.php");
    exit;  // Завершаем выполнение скрипта
}
