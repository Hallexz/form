<?php if (isset($user)): ?>
    <form method="POST" action="profile.php">
        <input type="text" name="name" value="<?php echo htmlspecialchars($user->name); ?>" required>
        <input type="text" name="phone" value="<?php echo htmlspecialchars($user->phone); ?>" required>
        <input type="email" name="email" value="<?php echo htmlspecialchars($user->email); ?>" required>
        <input type="password" name="password" placeholder="Новый пароль">
        <button type="submit">Сохранить изменения</button>
    </form>
<?php else: ?>
    <p>Не удалось загрузить данные профиля.</p>
<?php endif; ?>
