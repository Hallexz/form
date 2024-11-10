<?php

namespace repositories;

require_once 'config/database.php';
require_once 'models/User.php';

class UserRepository {
    private $conn;

    public function __construct() {
        $this->conn = getDatabaseConnection();
    }

    public function findByLogin($login) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE phone = ? OR email = ?");
        $stmt->bind_param("ss", $login, $login);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($user_data = $result->fetch_assoc()) {
            return new \models\User($user_data['id'], $user_data['name'], $user_data['phone'], $user_data['email'], $user_data['password']);
        }
        return null;
    }

    public function findById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($user_data = $result->fetch_assoc()) {
            return new \models\User($user_data['id'], $user_data['name'], $user_data['phone'], $user_data['email'], $user_data['password']);
        }
        return null;
    }

    public function save(\models\User $user) {
        $stmt = $this->conn->prepare("UPDATE users SET name = ?, phone = ?, email = ?, password = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $user->name, $user->phone, $user->email, $user->password, $user->id);
        return $stmt->execute();
    }
}
