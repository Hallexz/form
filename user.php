<?php

// Подключаем файл с функцией получения подключения к базе данных
require_once 'db.php';

// Функция получения пользователя по его ID
function getUserById($userId) {
    $conn = getDatabaseConnection();  // Получаем подключение к базе данных
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");  // Подготовка запроса
    $stmt->bind_param("i", $userId);  // Привязка параметра ID пользователя
    $stmt->execute();  // Выполнение запроса
    $user = $stmt->get_result()->fetch_assoc();  // Получаем результат
    $stmt->close();  // Закрываем подготовленный запрос
    $conn->close();  // Закрываем соединение с базой данных
    return $user;  // Возвращаем данные пользователя
}

// Функция обновления данных пользователя
function updateUser($userId, $name, $phone, $email, $passwordHash) {
    $conn = getDatabaseConnection();  // Получаем подключение к базе данных
    $stmt = $conn->prepare("UPDATE users SET name = ?, phone = ?, email = ?, password = ? WHERE id = ?");  // Подготовка запроса для обновления данных
    $stmt->bind_param("ssssi", $name, $phone, $email, $passwordHash, $userId);  // Привязка параметров
    $success = $stmt->execute();  // Выполнение запроса
    $stmt->close();  // Закрываем подготовленный запрос
    $conn->close();  // Закрываем соединение с базой данных
    return $success;  // Возвращаем успех выполнения запроса
}

// Функция проверки уникальности контакта (email или phone)
function isContactUnique($contact, $type, $userId = null) {
    $conn = getDatabaseConnection();  // Получаем подключение к базе данных
    $query = $userId ?  // Если userId передан, исключаем его из поиска
        "SELECT id FROM users WHERE $type = ? AND id != ?" :
        "SELECT id FROM users WHERE $type = ?";  // Запрос для проверки уникальности контакта

    $stmt = $conn->prepare($query);  // Подготовка запроса
    if ($userId) {
        $stmt->bind_param("si", $contact, $userId);  // Привязка параметров с исключением текущего пользователя
    } else {
        $stmt->bind_param("s", $contact);  // Привязка только одного параметра (контакта)
    }
    $stmt->execute();  // Выполнение запроса
    $isUnique = $stmt->get_result()->num_rows === 0;  // Проверка, если такой контакт не существует
    $stmt->close();  // Закрываем подготовленный запрос
    $conn->close();  // Закрываем соединение с базой данных
    return $isUnique;  // Возвращаем true, если контакт уникален
}

// Функция регистрации нового пользователя
function registerUser($name, $contact, $passwordHash, $contactType) {
    $conn = getDatabaseConnection();  // Получаем подключение к базе данных

    // Если контакт — телефон, то email будет пустым, и наоборот
    if ($contactType === 'phone') {
        $phone = $contact;
        $email = null;  // Очистить email, если это телефон
    } else {
        $phone = null;  // Очистить телефон, если это email
        $email = $contact;
    }

    // Подготовка SQL-запроса для регистрации
    $stmt = $conn->prepare("INSERT INTO users (name, phone, email, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $phone, $email, $passwordHash);  // Привязка параметров

    // Выполнение запроса
    if ($stmt->execute()) {
        $stmt->close();  // Закрытие запроса
        $conn->close();  // Закрытие соединения с базой данных
        return true;  // Возвращаем true, если регистрация успешна
    } else {
        $stmt->close();  // Закрытие запроса в случае ошибки
        $conn->close();  // Закрытие соединения
        return false;  // Возвращаем false, если произошла ошибка
    }
}

// Функция получения пользователя по логину (email или phone)
function getUserByLogin($login, $loginType) {
    $conn = getDatabaseConnection();  // Получаем подключение к базе данных

    // Определяем, какой столбец (phone или email) будем использовать в запросе
    $column = $loginType === 'phone' ? 'phone' : 'email';

    // Подготовка SQL-запроса для поиска пользователя по логину
    $stmt = $conn->prepare("SELECT * FROM users WHERE $column = ?");
    $stmt->bind_param("s", $login);  // Привязка параметра логина

    // Выполнение запроса
    $stmt->execute();
    $result = $stmt->get_result();

    // Если пользователь найден, возвращаем его данные
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        $user = null;  // Если пользователь не найден, возвращаем null
    }

    $stmt->close();  // Закрываем подготовленный запрос
    $conn->close();  // Закрываем соединение с базой данных

    return $user;  // Возвращаем данные пользователя (или null)
}

// Функция проверки, существует ли уже пользователь с таким контактом
function isUserExists($contact, $contactType) {
    $conn = getDatabaseConnection();  // Получаем подключение к базе данных

    // Подготовка запроса в зависимости от типа контакта (email или phone)
    if ($contactType === 'email') {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    } else {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE phone = ?");
    }

    // Привязываем параметр контакта и выполняем запрос
    $stmt->bind_param("s", $contact);
    $stmt->execute();

    // Получаем результат
    $result = $stmt->get_result();
    $count = $result->fetch_row()[0];  // Количество записей с таким контактом

    // Закрываем соединение и возвращаем результат
    $stmt->close();  // Закрываем подготовленный запрос
    $conn->close();  // Закрываем соединение с базой данных

    return $count > 0;  // Возвращаем true, если контакт существует, иначе false
}
