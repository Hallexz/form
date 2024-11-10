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

    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Проверка совпадения паролей
    if ($password !== $confirm_password) {
        echo "Пароли не совпадают!";
        exit;
    }

    // Хеширование пароля
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Подключение к БД
    $conn = new mysqli('localhost', 'username', 'password', 'user_database');

    // Проверка существующих данных
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? OR phone = ?");
    $stmt->bind_param("ss", $email, $phone);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "Пользователь с таким email или телефоном уже существует!";
        exit;
    }

    // Добавление пользователя
    $stmt = $conn->prepare("INSERT INTO users (name, phone, email, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $phone, $email, $hashed_password);
    if ($stmt->execute()) {
        // Получаем ID зарегистрированного пользователя и сохраняем его в сессию
        $_SESSION['user_id'] = $stmt->insert_id;
        // Перенаправление на страницу профиля после успешной регистрации
        header("Location: index.php");
        exit;
    } else {
        echo "Ошибка регистрации!";
    }
}
?>


