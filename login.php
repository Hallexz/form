<?php
session_start();
require_once 'captcha.php';

function validatePhoneNumber($phone) {
    return preg_match('/^[0-9]{10,15}$/', $phone);
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validateLogin($login) {
    return strlen($login) >= 5;
}

function validatePassword($password) {
    return (strlen($password) >= 7 && preg_match('/[0-9]/', $password));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Получаем токен капчи
    $token = $_POST['smart-token'] ?? '';

    // Проверка капчи
    if (!check_captcha($token)) {
        echo "Проверка капчи не пройдена! Пожалуйста, попробуйте снова.";
        exit;
    }

    // Получаем данные пользователя
    $login = trim($_POST['login']);
    $password = $_POST['password'];

    // Валидация логина
    if (!validateLogin($login)) {
        echo "Логин должен быть не менее 5 символов.";
        exit;
    }

    // Валидация пароля
    if (!validatePassword($password)) {
        echo "Пароль должен быть не менее 7 символов и содержать хотя бы одну цифру.";
        exit;
    }

    // Подключаемся к базе данных
    $conn = new mysqli('localhost', 'username', 'password', 'user_database');
    if ($conn->connect_error) {
        die("Ошибка подключения: " . $conn->connect_error);
    }

    // Определяем тип логина (телефон или email)
    $login_type = validatePhoneNumber($login) ? 'phone' : (validateEmail($login) ? 'email' : 'invalid');

    if ($login_type === 'invalid') {
        echo "Неверный формат логина. Используйте email или номер телефона.";
        exit;
    }

    // Подготавливаем и выполняем запрос для поиска пользователя
    $stmt = $conn->prepare("SELECT * FROM users WHERE $login_type = ?");
    $stmt->bind_param("s", $login);
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

    // Закрываем соединение
    $stmt->close();
    $conn->close();
}
?>

<!-- Форма входа -->
<form method="POST" action="login.php">
    <input type="text" name="login" required placeholder="Телефон или Почта">
    <input type="password" name="password" required placeholder="Пароль">
    <div
            style="height: 100px"
            id="captcha-container"
            class="smart-captcha"
            data-sitekey="ysc1_9ybtZeKUOhsku9tCLSGlfzWeIsly1w8YXScedhoI6aa8cba3"
    ></div>
    <button type="submit">Войти</button>
</form>

<!-- Скрипт инициализации CAPTCHA -->
<script>
    window.onload = function() {
        // Инициализация CAPTCHA при загрузке страницы
        smartCaptcha.render({
            id: "captcha-container",
            sitekey: "ysc1_9ybtZeKUOhsku9tCLSGlfzWeIsly1w8YXScedhoI6aa8cba3"
        });

        // Добавление токена CAPTCHA в форму перед отправкой
        document.querySelector('form').onsubmit = function() {
            const token = smartCaptcha.getToken('captcha-container');
            const inputToken = document.createElement('input');
            inputToken.type = 'hidden';
            inputToken.name = 'smart-token';
            inputToken.value = token;
            document.querySelector('form').appendChild(inputToken);
        }
    };
</script>

<script src="https://smartcaptcha.yandexcloud.net/captcha.js" defer></script>