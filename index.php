<?php
require_once 'controllers/AuthController.php';

$controller = new controllers\ProfileController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->updateProfile();  // Update profile data
} else {
    $controller->showProfileForm();  // Show profile form
}
