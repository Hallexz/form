<?php
session_start();
require_once 'captcha.php';

function validatePhoneNumber($phone) {
    return preg_match('/^[0-9]{10,15}$/', $phone);
}

function validatePassword($password) {
    return (strlen($password) >= 7 && preg_match('/[0-9]/', $password));
}

function validateName($name) {
    return strlen($name) >= 5;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $token = $_POST['smart-token'];
    if (!check_captcha($token)) {
        echo "Проверка капчи не пройдена! Пожалуйста, попробуйте снова.";
        exit;
    }

    $name = trim($_POST['name']);
    $contact = trim($_POST['contact']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Валидация имени
    if (!validateName($name)) {
        echo "Имя пользователя должно быть не менее 5 символов.";
        exit;
    }

    // Валидация контакта
    if (!filter_var($contact, FILTER_VALIDATE_EMAIL) && !validatePhoneNumber($contact)) {
        echo "Пожалуйста, введите корректный email или номер телефона.";
        exit;
    }

    // Валидация пароля
    if (!validatePassword($password)) {
        echo "Пароль должен быть не менее 7 символов и содержать хотя бы одну цифру.";
        exit;
    }

    if ($password !== $confirm_password) {
        echo "Пароли не совпадают!";
        exit;
    }

    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    $conn = new mysqli('localhost', 'username', 'password', 'user_database');

    $contact_type = filter_var($contact, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

    $stmt = $conn->prepare("SELECT * FROM users WHERE $contact_type = ?");
    $stmt->bind_param("s", $contact);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo "Пользователь с таким " . ($contact_type == 'email' ? "email" : "телефоном") . " уже существует!";
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO users (name, $contact_type, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $contact, $hashed_password);
    if ($stmt->execute()) {
        $_SESSION['user_id'] = $stmt->insert_id;
        header("Location: index.php");
        exit;
    } else {
        echo "Ошибка регистрации!";
    }
}
?>

<form method="POST" action="register.php">
    <input type="text" name="name" required placeholder="Имя">
    <input type="text" name="contact" required placeholder="Телефон или Почта" id="contact">
    <input type="password" name="password" required placeholder="Пароль" id="password">
    <input type="password" name="confirm_password" required placeholder="Повтор пароля">
    <div
        style="height: 100px"
        id="captcha-container"
        class="smart-captcha"
        data-sitekey="ysc1_9ybtZeKUOhsku9tCLSGlfzWeIsly1w8YXScedhoI6aa8cba3"
    ></div>
    <button type="submit">Зарегистрироваться</button>
</form>
<script>
    window.onload = function() {
        smartCaptcha.render({
            id: "captcha-container",
            sitekey: "ysc1_9ybtZeKUOhsku9tCLSGlfzWeIsly1w8YXScedhoI6aa8cba3"
        });
        document.querySelector('form').onsubmit = function() {
            var token = smartCaptcha.getToken('captcha-container');
            var inputToken = document.createElement('input');
            inputToken.setAttribute('type', 'hidden');
            inputToken.setAttribute('name', 'smart-token');
            inputToken.setAttribute('value', token);
            document.querySelector('form').appendChild(inputToken);
        }
    };
</script>
<script src="https://smartcaptcha.yandexcloud.net/captcha.js" defer></script>
