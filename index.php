<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html"); // Перенаправление на главную, если пользователь не авторизован
    exit;
}

$conn = new mysqli('localhost', 'username', 'password', 'user_database');
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<form method="POST" action="profile.php">
    <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
    <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
    <input type="password" name="password" placeholder="Новый пароль">
    <button type="submit">Сохранить изменения</button>
</form>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $password = $_POST['password'] ? password_hash($_POST['password'], PASSWORD_BCRYPT) : $user['password'];

    // Проверка уникальности почты и телефона
    $stmt = $conn->prepare("SELECT id FROM users WHERE (email = ? OR phone = ?) AND id != ?");
    $stmt->bind_param("ssi", $email, $phone, $_SESSION['user_id']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo "Email или телефон уже заняты!";
    } else {
        $stmt = $conn->prepare("UPDATE users SET name = ?, phone = ?, email = ?, password = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $name, $phone, $email, $password, $_SESSION['user_id']);
        if ($stmt->execute()) {
            echo "Изменения сохранены!";
        } else {
            echo "Ошибка сохранения!";
        }
    }
}
?>

