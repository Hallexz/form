<?php

namespace models;

class User {
    public $id;
    public $name;
    public $phone;
    public $email;
    public $password;

    public function __construct($id, $name, $phone, $email, $password) {
        $this->id = $id;
        $this->name = $name;
        $this->phone = $phone;
        $this->email = $email;
        $this->password = $password;
    }

    public function verifyPassword($password) {
        return password_verify($password, $this->password);
    }
}