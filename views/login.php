<form method="POST" action="/index.php?action=login">
    <input type="text" name="login" required placeholder="Телефон или Почта">
    <input type="password" name="password" required placeholder="Пароль">
    <div id="captcha-container" data-sitekey="your_site_key"></div>
    <button type="submit">Войти</button>
</form>
<script src="https://smartcaptcha.yandexcloud.net/captcha.js" defer></script>