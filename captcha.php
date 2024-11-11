<?php

define('SMARTCAPTCHA_SERVER_KEY','ysc2_9ybtZeKUOhsku9tCLSGllQ9om6Qn8bCajTwfdeKZd37b4372');
function check_captcha($token) {
    $ch = curl_init();
    $args = http_build_query([
        "secret" => SMARTCAPTCHA_SERVER_KEY,
        "token" => $token,
        "ip" => $_SERVER['REMOTE_ADDR'], // Нужно передать IP пользователя.
    ]);
    curl_setopt($ch, CURLOPT_URL, "https://smartcaptcha.yandexcloud.net/validate?$args");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Увеличиваем таймаут
    $server_output = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($httpcode !== 200) {
        echo "Ошибка при обращении к серверу капчи: код=$httpcode; сообщение=$server_output\n";
        return false;
    }
    $resp = json_decode($server_output);
    return $resp->status === "ok";
}