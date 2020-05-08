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
include_once "utilities/PDOAdapter.php";
include_once "utilities/dbconfig.php";
if (!empty($_POST['username']) && !empty($_POST['password'])) {//如果用户用户名和密码都有输入
    try {
        $pdoAdapter = new PDOAdapter(HEADER, DBACCOUNT, DBPASSWORD, DBNAME);
        $row = $pdoAdapter->selectRows("select * from traveluser where UserName=?", array($_POST['username']));
        $uid = $row[0]['UID'];
        $correctPassword = $row[0]['Pass'];
        $salt = $row[0]['salt'];
        $calculatedPassword = MD5($_POST['password'] . $salt);
        if ($calculatedPassword === $correctPassword) {//如果加盐后的密码和数据库中密码一致则登陆成功
            session_start();
            $_SESSION['username'] = $_POST['username'];
            $_SESSION['uid'] = $uid;
            header("location:index.php");
            exit();
        } else {//否则登陆失败，提示用户密码错误
            header("location:error.php?errorCode=6");
            exit();
        }
    } catch (PDOException $PDOException) {
        header("location:error.php?errorCode=0");
        exit();
    }

}

?>
<body>
<div class="pure-u-1-6"></div>
<form class="pure-form pure-u-2-3 hasShadow" action="login.php" method="post">
    <fieldset>
        <legend>登录</legend>
        <div class="pure-u-1-4"></div>
        <div class="pure-u-1-2" id="wrapper">
            <label class="pure-u-1">用户名</label>
            <input type="text" name="username" class="pure-u-1" id="usernameInput">
            <label class="pure-u-1">密码</label>
            <input type="password" name="password" class="pure-u-1" id="passwordInput">
            <button class="pure-button pure-button-primary pure-u-1" id="submit">登录</button>
            <label class="pure-u-1">没有账号？<a href="register.php">注册</a>！</label>
            <div class="pure-u-1" id="errorArea"></div>
        </div>
    </fieldset>
</form>

</body>
</html>