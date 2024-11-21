<?php

// Определение серверного ключа для проверки капчи
define('SMARTCAPTCHA_SERVER_KEY','ysc2_9ybtZeKUOhsku9tCLSGllQ9om6Qn8bCajTwfdeKZd37b4372');

// Функция для проверки капчи с использованием Yandex Smart CAPTCHA
function check_captcha($token) {
    // Инициализация cURL для отправки запроса
    $ch = curl_init();

    // Формируем параметры запроса
    $args = http_build_query([
        "secret" => SMARTCAPTCHA_SERVER_KEY,  // Серверный ключ для валидации
        "token" => $token,                    // Токен капчи, переданный с формы
        "ip" => $_SERVER['REMOTE_ADDR'],      // IP-адрес клиента
    ]);

    // Устанавливаем URL для отправки запроса
    curl_setopt($ch, CURLOPT_URL, "https://smartcaptcha.yandexcloud.net/validate?$args");

    // Устанавливаем флаг, чтобы получить ответ в виде строки
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Устанавливаем максимальное время ожидания ответа от сервера
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    // Отправляем запрос и сохраняем результат
    $server_output = curl_exec($ch);

    // Получаем код ответа HTTP
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // Закрываем соединение с сервером
    curl_close($ch);

    // Проверка, был ли получен успешный ответ от сервера
    if ($httpcode !== 200) {
        // В случае ошибки выводим сообщение и возвращаем false
        echo "Ошибка при обращении к серверу капчи: код=$httpcode; сообщение=$server_output\n";
        return false;
    }

    // Декодируем JSON-ответ сервера
    $resp = json_decode($server_output);

    // Если статус капчи равен "ok", возвращаем true, иначе false
    return $resp->status === "ok";
}
