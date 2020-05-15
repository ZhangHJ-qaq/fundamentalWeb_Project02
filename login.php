<?php
include_once "class/Login.php";
$login = new Login();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ZHJ的旅游图片分享站-登录</title>
    <link rel="stylesheet" href="css/library/reset.css">
    <link rel="stylesheet" href="css/universal.css">
    <link rel="stylesheet" href="css/login_register.css">
    <link rel="stylesheet" href="css/library/pure-release-1.0.1/pure-min.css">
    <script src="js/library/jquery-3.4.1.js"></script>
    <script src="js/loginPrevalidation.js"></script>
</head>
<?php

$login->tryLogin($_POST['username'], $_POST['password'],$_POST['captcha']);


?>
<body>
<div class="pure-u-1-6"></div>
<form class="pure-form pure-u-2-3 hasShadow" action="login.php" method="post">
    <fieldset>
        <legend>登录</legend>
        <div class="pure-u-1-4"></div>
        <div class="pure-u-1-2" id="wrapper">
            <?php
            $login->printMessageIfNotEmpty();
            ?>
            <label class="pure-u-1">用户名</label>
            <input type="text" name="username" class="pure-u-1" id="usernameInput">
            <label class="pure-u-1">密码</label>
            <input type="password" name="password" class="pure-u-1" id="passwordInput">
            <?php
            $login->generateCaptcha();
            ?>
            <button class="pure-button pure-button-primary pure-u-1" id="submit">登录</button>
            <label class="pure-u-1">没有账号？<a href="register.php">注册</a>！</label>
            <div class="pure-u-1" id="errorArea"></div>
        </div>
    </fieldset>
</form>

</body>
</html>