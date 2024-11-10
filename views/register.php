<form method="POST" action="/index.php?action=register">
    <input type="text" name="name" required placeholder="Имя">
    <input type="text" name="phone" required placeholder="Телефон">
    <input type="email" name="email" required placeholder="Почта">
    <input type="password" name="password" required placeholder="Пароль">
    <input type="password" name="confirm_password" required placeholder="Повтор пароля">
    <div id="captcha-container" data-sitekey="your_site_key"></div>
    <button type="submit">Зарегистрироваться</button>
</form>
<script src="https://smartcaptcha.yandexcloud.net/captcha.js" defer></script>