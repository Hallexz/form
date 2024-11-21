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

// Массив для ошибок
$errors = [];

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
    if (!validateName($name)) {
        $errors[] = "Некорректное имя!";
    }
    if (!validatePhoneNumber($phone)) {
        $errors[] = "Некорректный телефон!";
    }
    if (!validateEmail($email)) {
        $errors[] = "Некорректный email!";
    }

    // Если есть ошибки, сохраняем их в сессии и перенаправляем
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("Location: profile.php");
        exit; // Завершаем выполнение скрипта
    }

    // Если введен новый пароль, то хешируем его, иначе оставляем старый пароль
    $passwordHash = $password ? password_hash($password, PASSWORD_BCRYPT) : $user['password'];

    // Обновляем данные пользователя в базе данных
    if (updateUser($_SESSION['user_id'], $name, $phone, $email, $passwordHash)) {
        $_SESSION['success'] = "Данные обновлены!";
        header("Location: profile.php");  // Перенаправляем на страницу профиля
        exit; // Завершаем выполнение скрипта
    } else {
        $_SESSION['error'] = "Ошибка при обновлении данных!";
        header("Location: profile.php");
        exit; // Завершаем выполнение скрипта
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

<!-- Вывод ошибок из сессии -->
<?php
if (isset($_SESSION['errors'])) {
    foreach ($_SESSION['errors'] as $error) {
        echo "<p style='color: red;'>$error</p>";
    }
    unset($_SESSION['errors']);  // Очищаем ошибки после отображения
}

if (isset($_SESSION['success'])) {
    echo "<p style='color: green;'>".$_SESSION['success']."</p>";
    unset($_SESSION['success']);  // Очищаем сообщение об успехе после отображения
}

if (isset($_SESSION['error'])) {
    echo "<p style='color: red;'>".$_SESSION['error']."</p>";
    unset($_SESSION['error']);  // Очищаем сообщение об ошибке после отображения
}
?>

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
