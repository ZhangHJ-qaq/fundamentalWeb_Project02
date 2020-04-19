<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>登录</title>
    <link rel="stylesheet" href="css/library/reset.css">
    <link rel="stylesheet" href="css/universal.css">
    <link rel="stylesheet" href="css/login_register.css">
    <link rel="stylesheet" href="css/library/pure-release-1.0.1/pure.css">
    <script src="js/library/jquery-3.4.1.js"></script>
    <script src="js/registerPrevalidation.js"></script>
</head>
<?php
include_once "utilities/dbconfig.php";
include_once "utilities/PDOAdapter.php";
include_once "utilities/htmlpurifier-4.12.0/library/HTMLPurifier.auto.php";
include_once "utilities/utilityFunction.php";
session_start();
if (isset($_SESSION['username'])) {//如果已登录则跳到主页
    header("location:index.php");
} else {
    if (!empty($_POST['username']) && (!empty($_POST['password1'])) && (!empty($_POST['password2'])) && (!empty($_POST['email']))) {
        //如果用户四样都有填写，则开始尝试注册流程
        try {
            $pdoAdapter = new PDOAdapter(HEADER, DBACCOUNT, DBPASSWORD, DBNAME);
            $purifier = new HTMLPurifier();
            checkInputThenCreateUser();
            $pdoAdapter->close();
        } catch (PDOException $PDOException) {
            header("location:error.php?errorCode=0");
        }
    } else if (!empty($_POST['username']) || !empty($_POST['password1']) || !empty($_POST['password2']) || !empty($_POST['email'])) {
        header("location:error.php?errorCode=14");//如果用户填写了至少一样，则提示错误，让用户补全信息，如果用户什么都不填写，重新显示注册页面
        exit();
    }
}

function checkInputThenCreateUser()
{
    global $purifier;
    $purifiedUsername = $purifier->purify($_POST['username']);
    $purifiedPassword1 = $purifier->purify($_POST['password1']);
    $purifiedPassword2 = $purifier->purify($_POST['password2']);
    $purifiedEmail = $purifier->purify($_POST['email']);//净化用户的输入
    if (!($purifiedUsername === $_POST['username'] && $purifiedPassword1 === $_POST['password1'] && $purifiedPassword2 === $_POST['password2'] && $purifiedEmail === $_POST['email'])) {
        //如果净化后用户的输入和原先的输入不相等
        header("location:error.php?errorCode=0");
        exit();
    }

    //在后端验证用户输入是否符合要求
    if (!preg_match("/^[0-9a-zA-Z]{6,18}$/", $purifiedUsername)) {
        header("location:error.php?errorCode=1");
        exit();
    }
    if (!preg_match("/^[a-z0-9]+([._\\-]*[a-z0-9])*@([a-z0-9]+[-a-z0-9]*[a-z0-9]+.){1,63}[a-z0-9]+$/", $purifiedEmail)) {
        header("location:error.php?errorCode=2");
        exit();
    }
    if (!checkPassword($purifiedPassword2, $purifiedPassword1)) {
        header("location:error.php?errorCode=3");
        exit();
    }

    //检测用户名是否已经被别人先注册
    if (checkUserExist($purifiedUsername)) {
        header("location:error.php?errorCode=4");
        exit();
    }

    //创建用户，并提示错误与成功
    if (!createUser($purifiedUsername, $purifiedPassword1, $purifiedEmail)) {
        header("location:error.php?errorCode=5");
        exit();
    } else {
        header("location:register_success.html");
        exit();
    }


}

function checkPassword($password1, $password2)
{
    if ($password1 !== $password2) {
        return false;
    } else {
        if (!preg_match("/^.{6,18}$/", $password1) || preg_match("/^[0-9]{1,}$/", $password1)) {
            return false;
        } else {
            return true;
        }
    }

}

function checkUserExist($username)
{
    global $pdoAdapter;
    if ($pdoAdapter->isRowCountZero("select * from traveluser where UserName=?", array($username))) {
        return false;
    } else {
        return true;
    }

}

function createUser($username, $password, $email)
{
    global $pdoAdapter;
    $dateJoined = date("Y-m-d h:i:sa");
    $salt = get_hash();//随机生成一个盐
    $saltedEncryptedPassword = MD5($password . $salt);//哈希加盐，将密码存储到数据库
    return $pdoAdapter->insertARow("insert into traveluser (UserName, Email, Pass, State, DateJoined, DateLastModified, salt) VALUES (?,?,?,?,?,?,?)", array($username, $email, $saltedEncryptedPassword, 1, $dateJoined, $dateJoined, $salt));
}


?>
<body>
<div class="pure-u-1-6"></div>
<form class="pure-form pure-u-2-3" method="post" action="register.php">
    <fieldset>
        <legend>注册</legend>
        <div class="pure-u-1-4"></div>
        <div class="pure-u-1-2" id="wrapper">
            <label class="pure-u-1">用户名</label>
            <input type="text" name="username" class="pure-u-1" id="usernameInput">
            <label class="pure-u-1">邮箱</label>
            <input type="email" name="email" class="pure-u-1" id="emailInput">
            <label class="pure-u-1">密码</label>
            <input type="password" name="password1" class="pure-u-1" id="password1Input">
            <label class="pure-u-1">确认密码</label>
            <input type="password" name="password2" class="pure-u-1" id="password2Input">
            <button class="pure-button pure-button-primary pure-u-1" id="submit" type="submit">注册</button>
            <label class="pure-u-1">已有账号？<a href="login.php">登录</a>！</label>
            <div class="pure-u-1" id="errorArea"></div>
        </div>
    </fieldset>
</form>

</body>
</html>
