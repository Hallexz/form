<?php

namespace controllers;

require_once 'services/CaptchaService.php';
require_once 'repositories/UserRepository.php';

class AuthController {
    private $userRepository;

    public function __construct() {
        $this->userRepository = new UserRepository();
    }

    public function login($login, $password, $captchaToken) {
        if (!CaptchaService::checkCaptcha($captchaToken)) {
            echo "Проверка капчи не пройдена!";
            return;
        }

        $user = $this->userRepository->findByLogin($login);
        if ($user && $user->verifyPassword($password)) {
            session_start();
            $_SESSION['user_id'] = $user->id;
            header("Location: /index.php");
            exit;
        } else {
            echo "Неправильные данные!";
        }
    }

    public function register($name, $phone, $email, $password, $confirm_password, $captchaToken) {
        if (!CaptchaService::checkCaptcha($captchaToken)) {
            echo "Проверка капчи не пройдена!";
            return;
        }

        if ($password !== $confirm_password) {
            echo "Пароли не совпадают!";
            return;
        }

        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $user = new User(null, $name, $phone, $email, $hashed_password);
        if ($this->userRepository->save($user)) {
            echo "Регистрация успешна!";
        } else {
            echo "Ошибка регистрации!";
        }
    }
}