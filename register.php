<?php
include_once "class/Register.php";
$register = new Register();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ZHJ的旅游图片分享站-注册</title>
    <link rel="stylesheet" href="css/library/reset.css">
    <link rel="stylesheet" href="css/universal.css">
    <link rel="stylesheet" href="css/login_register.css">
    <link rel="stylesheet" href="css/library/pure-release-1.0.1/pure-min.css">
    <script src="js/library/jquery-3.4.1.js"></script>
    <script src="js/registerPrevalidation.js"></script>
</head>
<?php
$register->checkIfHasLoggedIn();
$register->tryRegister($_POST['username'], $_POST['email'], $_POST['password1'], $_POST['password2'],$_POST['captcha']);


?>
<body>
<div class="pure-u-1-6"></div>
<form class="pure-form pure-u-2-3 hasShadow" method="post" action="register.php">
    <fieldset>
        <legend>注册</legend>
        <div class="pure-u-1-4"></div>
        <div class="pure-u-1-2" id="wrapper">
            <?php
            $register->printMessageIfNotEmpty();
            ?>
            <label class="pure-u-1">用户名</label>
            <input type="text" name="username" class="pure-u-1" id="usernameInput">
            <label class="pure-u-1">邮箱</label>
            <input type="email" name="email" class="pure-u-1" id="emailInput">
            <label class="pure-u-1">密码</label>
            <input type="password" name="password1" class="pure-u-1" id="password1Input">
            <label class="pure-u-1">确认密码</label>
            <input type="password" name="password2" class="pure-u-1" id="password2Input">
            <?php
            $register->generateCaptcha();
            ?>
            <button class="pure-button pure-button-primary pure-u-1" id="submit" type="submit">注册</button>
            <label class="pure-u-1">已有账号？<a href="login.php">登录</a>！</label>
            <div class="pure-u-1" id="errorArea"></div>
        </div>
    </fieldset>
</form>

</body>
</html>
