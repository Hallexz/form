<?php
session_start();
require_once 'captcha.php';

// Проверка авторизации пользователя
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Перенаправление на страницу входа, если пользователь не авторизован
    exit;
}

// Функции валидации
function validatePhoneNumber($phone) {
    return preg_match('/^[0-9]{10,15}$/', $phone);
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validateName($name) {
    return strlen($name) >= 3;
}

function validatePassword($password) {
    return (strlen($password) >= 7 && preg_match('/[0-9]/', $password));
}

// Подключаемся к базе данных
$conn = new mysqli('localhost', 'username', 'password', 'user_database');
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

// Получаем данные текущего пользователя
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $password = $_POST['password'] ? $_POST['password'] : '';

    // Валидация полей
    if (!validateName($name)) {
        echo "Имя должно быть не менее 3 символов.";
        exit;
    }
    if (!validatePhoneNumber($phone)) {
        echo "Некорректный номер телефона.";
        exit;
    }
    if (!validateEmail($email)) {
        echo "Некорректный формат email.";
        exit;
    }
    if ($password && !validatePassword($password)) {
        echo "Пароль должен быть не менее 7 символов и содержать хотя бы одну цифру.";
        exit;
    }

    // Проверка уникальности почты и телефона
    $stmt = $conn->prepare("SELECT id FROM users WHERE (email = ? OR phone = ?) AND id != ?");
    $stmt->bind_param("ssi", $email, $phone, $_SESSION['user_id']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo "Email или телефон уже заняты!";
        exit;
    }

    // Обновление данных пользователя
    $password_hash = $password ? password_hash($password, PASSWORD_BCRYPT) : $user['password'];
    $stmt = $conn->prepare("UPDATE users SET name = ?, phone = ?, email = ?, password = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $name, $phone, $email, $password_hash, $_SESSION['user_id']);

    if ($stmt->execute()) {
        echo "Изменения сохранены!";
    } else {
        echo "Ошибка сохранения!";
    }

    $stmt->close();
}

$conn->close();
?>
