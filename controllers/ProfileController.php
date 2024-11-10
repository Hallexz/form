<?php

namespace controllers;

use repositories\UserRepository;
use models\User;

class ProfileController {

    private $userRepository;

    public function __construct() {
        $this->userRepository = new UserRepository();
    }
    public function showProfileForm() {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header("Location: login.php");
            exit;
        }
        $user = $this->userRepository->findById($_SESSION['user_id']);
        if (!$user) {
            echo "User not found.";
            exit;
        }

        // Include the profile view
        require_once 'views/profile.php';
    }

    public function updateProfile() {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header("Location: login.php");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name']);
            $phone = trim($_POST['phone']);
            $email = trim($_POST['email']);
            $password = $_POST['password'] ? password_hash($_POST['password'], PASSWORD_BCRYPT) : null;

            $existingUser = $this->userRepository->findByLogin($email);
            if ($existingUser && $existingUser->id !== $_SESSION['user_id']) {
                echo "Email already in use!";
                return;
            }

            $existingUser = $this->userRepository->findByLogin($phone);
            if ($existingUser && $existingUser->id !== $_SESSION['user_id']) {
                echo "Phone already in use!";
                return;
            }

            // Update user data
            $user = new User($_SESSION['user_id'], $name, $phone, $email, $password);
            $this->userRepository->save($user);
            echo "Profile updated successfully!";
        } else {
            echo "Invalid request!";
        }
    }
}
