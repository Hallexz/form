<?php
session_start();
require_once 'captcha.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $token = $_POST['smart-token']; // Получаем токен капчи

    // Проверка капчи
    if (!check_captcha($token)) {
        echo "Проверка капчи не пройдена! Пожалуйста, попробуйте снова.";
        exit;
    }

    $login = trim($_POST['login']);
    $password = $_POST['password'];

    $conn = new mysqli('localhost', 'username', 'password', 'user_database');

    // Поиск пользователя
    $stmt = $conn->prepare("SELECT * FROM users WHERE phone = ? OR email = ?");
    $stmt->bind_param("ss", $login, $login);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Проверка пароля
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        header("Location: index.php");
        exit;
    } else {
        echo "Неправильные данные!";
    }
}