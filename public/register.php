<?php
// Начинаем сессию для работы с данными о пользователе
session_start();
require_once '../auth.php';  // Подключаем файл с функциями аутентификации
require_once '../user.php';  // Подключаем файл с функциями для работы с пользователями
require_once '../validation.php';  // Подключаем файл с функциями валидации данных
require_once '../captcha.php';  // Подключаем файл с функциями для работы с CAPTCHA

// Если пользователь уже авторизован, перенаправляем на главную страницу
if (isUserLoggedIn()) {
    header("Location: profile.php");  // Перенаправление на страницу профиля
    exit;  // Завершаем выполнение скрипта
}

// Обработка данных формы регистрации
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Получаем и обрабатываем данные из формы
    $name = trim($_POST['name']);  // Убираем лишние пробелы вокруг имени
    $contact = trim($_POST['contact']);  // Убираем лишние пробелы вокруг контактных данных
    $password = $_POST['password'];  // Получаем пароль
    $confirmPassword = $_POST['confirm_password'];  // Получаем повтор пароля
    $captchaToken = $_POST['smart-token'] ?? '';  // Получаем токен CAPTCHA

    // Проверка капчи
    if (!check_captcha($captchaToken)) {
        echo "Проверка капчи не пройдена! Попробуйте снова.";  // Если капча не пройдена, выводим сообщение
        exit;  // Завершаем выполнение скрипта
    }

    // Валидация имени
    if (!validateName($name)) {
        echo "Имя пользователя должно быть не менее 3 символов.";  // Если имя некорректное, выводим сообщение
        exit;
    }

    // Определяем тип контакта (email или телефон)
    $contactType = validatePhoneNumber($contact) ? 'phone' : (validateEmail($contact) ? 'email' : null);
    if (!$contactType) {
        echo "Пожалуйста, введите корректный email или номер телефона.";  // Если контакт не является телефоном или email, выводим сообщение
        exit;
    }

    // Проверка, существует ли уже пользователь с таким контактом
    if (isUserExists($contact, $contactType)) {
        echo ucfirst($contactType === 'email' ? "Email" : "Телефон") . " уже используется!";  // Если контакт уже используется, выводим сообщение
        exit;
    }

    // Валидация пароля
    if (!validatePassword($password)) {
        echo "Пароль должен быть не менее 7 символов и содержать хотя бы одну цифру.";  // Если пароль не удовлетворяет требованиям, выводим сообщение
        exit;
    }

    // Проверка, что пароли совпадают
    if ($password !== $confirmPassword) {
        echo "Пароли не совпадают!";  // Если пароли не совпадают, выводим сообщение
        exit;
    }

    // Хеширование пароля перед сохранением в базе
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    // Регистрация нового пользователя
    if (registerUser($name, $contact, $passwordHash, $contactType)) {
        header("Location: login.php");  // Если регистрация успешна, перенаправляем на страницу входа
        exit;
    } else {
        echo "Ошибка регистрации!";  // Если произошла ошибка при регистрации, выводим сообщение
    }
}
?>

<!-- Форма регистрации -->
<form method="POST" action="register.php">
    <label>
        <input type="text" name="name" required placeholder="Имя">
    </label>  <!-- Поле для имени -->
    <label for="contact"></label><input type="text" name="contact" required placeholder="Телефон или Почта" id="contact">  <!-- Поле для телефона или почты -->
    <label for="password"></label><input type="password" name="password" required placeholder="Пароль" id="password">  <!-- Поле для пароля -->
    <label>
        <input type="password" name="confirm_password" required placeholder="Повтор пароля">
    </label>  <!-- Поле для повторного ввода пароля -->

    <!-- Контейнер для CAPTCHA -->
    <div
            style="height: 100px"
            id="captcha-container"
            class="smart-captcha"
            data-sitekey="ysc1_9ybtZeKUOhsku9tCLSGlfzWeIsly1w8YXScedhoI6aa8cba3"
    ></div>

    <!-- Кнопка для отправки формы -->
    <button type="submit">Зарегистрироваться</button>
</form>

<!-- Ссылка на страницу входа, если уже есть аккаунт -->
<p>Уже есть аккаунт? <a href="login.php" class="auth-button">Войти</a></p>

<script>
    window.onload = function() {
        // Инициализация CAPTCHA при загрузке страницы
        smartCaptcha.render({
            id: "captcha-container",
            sitekey: "ysc1_9ybtZeKUOhsku9tCLSGlfzWeIsly1w8YXScedhoI6aa8cba3"
        });

        // Добавление токена CAPTCHA в форму перед отправкой
        document.querySelector('form').onsubmit = function() {
            var token = smartCaptcha.getToken('captcha-container');  // Получаем токен CAPTCHA
            var inputToken = document.createElement('input');  // Создаем скрытое поле для токена
            inputToken.setAttribute('type', 'hidden');  // Устанавливаем тип поля как скрытое
            inputToken.setAttribute('name', 'smart-token');  // Имя поля для передачи токена
            inputToken.setAttribute('value', token);  // Присваиваем значение токена
            document.querySelector('form').appendChild(inputToken);  // Добавляем поле в форму
        }
    };
</script>

<!-- Скрипт для работы с CAPTCHA -->
<script src="https://smartcaptcha.yandexcloud.net/captcha.js" defer></script>
