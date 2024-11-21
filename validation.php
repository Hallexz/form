<?php

// Функция для валидации номера телефона
// Проверяет, что номер состоит из 10-15 цифр
function validatePhoneNumber($phone) {
    return preg_match('/^[0-9]{10,15}$/', $phone);  // Возвращает true, если номер соответствует формату
}

// Функция для валидации email
// Использует фильтр для проверки корректности email-адреса
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);  // Возвращает true, если email валиден
}

// Функция для валидации имени
// Проверяет, что длина имени не меньше 3 символов
function validateName($name) {
    return strlen($name) >= 3;  // Возвращает true, если имя состоит как минимум из 3 символов
}

// Функция для валидации пароля
// Проверяет, что длина пароля не меньше 7 символов и он содержит хотя бы одну цифру
function validatePassword($password) {
    return (strlen($password) >= 7 && preg_match('/[0-9]/', $password));  // Возвращает true, если пароль соответствует условиям
}

// Функция для валидации логина
// Проверяет, что длина логина не меньше 5 символов
function validateLogin($login) {
    return strlen($login) >= 5;  // Возвращает true, если логин состоит как минимум из 5 символов
}
