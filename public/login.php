<?php
// Начало сессии для работы с данными о пользователе
session_start();
require_once '../auth.php';  // Подключение файла с функциями для аутентификации
require_once '../user.php';  // Подключение файла с функциями для работы с пользователями
require_once '../validation.php';  // Подключение файла для валидации данных
require_once '../captcha.php';  // Подключение файла для проверки капчи

// Если пользователь уже авторизован, перенаправляем его на страницу профиля
if (isUserLoggedIn()) {
    header("Location: profile.php");
    exit; // Завершаем выполнение скрипта
}

// Обработка формы при отправке данных
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = trim($_POST['login']);  // Обрезаем пробелы по краям логина
    $password = $_POST['password'];  // Пароль из формы
    $captchaToken = $_POST['smart-token'] ?? '';  // Токен для проверки капчи (если он есть)

    // Проверка капчи
    if (!check_captcha($captchaToken)) {
        echo "Проверка капчи не пройдена! Попробуйте снова.";  // Выводим сообщение, если капча не пройдена
        exit; // Завершаем выполнение скрипта
    }

    // Определяем тип логина (телефон или email)
    $loginType = validatePhoneNumber($login) ? 'phone' : (validateEmail($login) ? 'email' : null);
    if (!$loginType) {
        echo "Некорректный формат логина. Используйте email или номер телефона.";  // Сообщение об ошибке
        exit; // Завершаем выполнение скрипта
    }

    // Проверка наличия пользователя в базе данных
    $user = getUserByLogin($login, $loginType);
    if (!$user) {
        echo "Пользователь с таким логином не найден. Проверьте email или номер телефона.";  // Сообщение о несуществующем пользователе
        exit; // Завершаем выполнение скрипта
    }

    // Проверка пароля
    if (!password_verify($password, $user['password'])) {
        echo "Неправильный пароль!";  // Сообщение о неверном пароле
        exit; // Завершаем выполнение скрипта
    }

    // Авторизация пользователя, сохраняем ID в сессии
    $_SESSION['user_id'] = $user['id'];
    header("Location: profile.php");  // Перенаправляем на страницу профиля
    exit; // Завершаем выполнение скрипта
}
?>

<!-- Форма входа -->
<form method="POST" action="login.php">
    <label>
        <input type="text" name="login" required placeholder="Телефон или Почта">
    </label>  <!-- Поле для ввода логина -->
    <label>
        <input type="password" name="password" required placeholder="Пароль">
    </label>  <!-- Поле для ввода пароля -->
    <div
            style="height: 100px"
            id="captcha-container"
            class="smart-captcha"
            data-sitekey="ysc1_9ybtZeKUOhsku9tCLSGlfzWeIsly1w8YXScedhoI6aa8cba3"
    ></div> <!-- Контейнер для CAPTCHA, использующий Yandex Smart CAPTCHA -->
    <button type="submit" class="auth-button">Войти</button>  <!-- Кнопка отправки формы -->
</form>

<!-- Кнопка перехода на страницу регистрации -->
<p>Нет аккаунта? <a href="register.php" class="auth-button">Зарегистрироваться</a></p>

<!-- Скрипт инициализации CAPTCHA -->
<script>
    window.onload = function() {
        // Инициализация CAPTCHA при загрузке страницы
        smartCaptcha.render({
            id: "captcha-container",  // ID контейнера для CAPTCHA
            sitekey: "ysc1_9ybtZeKUOhsku9tCLSGlfzWeIsly1w8YXScedhoI6aa8cba3"  // Ваш sitekey для CAPTCHA
        });

        // Добавление токена CAPTCHA в форму перед отправкой
        document.querySelector('form').onsubmit = function() {
            const token = smartCaptcha.getToken('captcha-container');  // Получаем токен CAPTCHA
            const inputToken = document.createElement('input');  // Создаем скрытое поле для токена
            inputToken.type = 'hidden';
            inputToken.name = 'smart-token';  // Имя поля для передачи токена
            inputToken.value = token;  // Значение поля - сам токен
            document.querySelector('form').appendChild(inputToken);  // Добавляем поле в форму
        }
    };
</script>

<!-- Скрипт для загрузки Yandex CAPTCHA -->
<script src="https://smartcaptcha.yandexcloud.net/captcha.js" defer></script>
