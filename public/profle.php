<?php

// Начинаем сессию для работы с данными о пользователе
session_start();
require_once '../auth.php';  // Подключение файла с функциями для аутентификации
require_once '../user.php';  // Подключение файла с функциями для работы с пользователями
require_once '../validation.php';  // Подключение файла для валидации данных

// Проверяем, авторизован ли пользователь
if (!isUserLoggedIn()) {
    header("Location: login.php");  // Если не авторизован, перенаправляем на страницу входа
    exit; // Завершаем выполнение скрипта
}

// Получаем данные текущего пользователя по ID из сессии
$user = getUserById($_SESSION['user_id']);

// Обработка данных формы
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Проверка, если нажата кнопка выхода
    if (isset($_POST['logout']) && $_POST['logout'] == 'true') {
        logout();  // Вызываем функцию для выхода пользователя
        header("Location: login.php");  // Перенаправляем на страницу входа после выхода
        exit; // Завершаем выполнение скрипта
    }

    // Получаем и обрабатываем данные из формы
    $name = trim($_POST['name']);  // Убираем пробелы по краям имени
    $phone = trim($_POST['phone']);  // Убираем пробелы по краям телефона
    $email = trim($_POST['email']);  // Убираем пробелы по краям email
    $password = $_POST['password'] ?? '';  // Если пароль не указан, оставляем пустое значение

    // Валидация данных (проверка имени, телефона и email)
    if (!validateName($name) || !validatePhoneNumber($phone) || !validateEmail($email)) {
        echo "Некорректные данные!";  // Если данные некорректные, выводим сообщение об ошибке
        exit;  // Завершаем выполнение скрипта
    }

    // Если введен новый пароль, то хешируем его, иначе оставляем старый пароль
    $passwordHash = $password ? password_hash($password, PASSWORD_BCRYPT) : $user['password'];

    // Обновляем данные пользователя в базе данных
    if (updateUser($_SESSION['user_id'], $name, $phone, $email, $passwordHash)) {
        echo "Данные обновлены!";  // Сообщение об успешном обновлении данных
    } else {
        echo "Ошибка!";  // Сообщение об ошибке при обновлении данных
    }
}

?>

<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование профиля</title>
</head>
<body>
<h1>Редактирование профиля</h1>

<!-- Форма для редактирования данных пользователя -->
<form method="POST" action="">
    <label for="name">Имя:</label>
    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>  <!-- Поле для имени пользователя -->
    <br>
    <label for="phone">Телефон:</label>
    <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>  <!-- Поле для телефона -->
    <br>
    <label for="email">Email:</label>
    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>  <!-- Поле для email -->
    <br>
    <label for="password">Пароль (оставьте пустым, если не хотите менять):</label>
    <input type="password" id="password" name="password">  <!-- Поле для пароля -->
    <br>
    <button type="submit">Сохранить изменения</button>  <!-- Кнопка для отправки формы -->
</form>

<!-- Форма для выхода из учетной записи -->
<form method="POST" action="">
    <button type="submit" name="logout" value="true">Выйти</button>  <!-- Кнопка для выхода из системы -->
</form>

</body>
</html>
