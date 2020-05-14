<?php
include_once "class/Page.class.php";
include_once "utilities/utilityFunction.php";
include_once "utilities/PDOAdapter.php";
include_once "utilities/dbconfig.php";


class Login extends Page
{
    private $message;

    public function __construct()
    {
        parent::__construct();
    }

    public function tryLogin($username, $password)
    {
        if (!customIsEmpty($username) && !customIsEmpty($password)) {//如果用户用户名和密码都有输入
            $row = $this->pdoAdapter->selectRows("select * from traveluser where UserName=?", array($_POST['username']));
            $uid = $row[0]['UID'];
            $correctPassword = $row[0]['Pass'];
            $salt = $row[0]['salt'];
            $calculatedPassword = MD5($_POST['password'] . $salt);
            $lastTimeTryLogin = $row[0]['LastTimeTryLogin'];
            $thisTimeTryLogin = time();
            $timeGap = $thisTimeTryLogin - $lastTimeTryLogin;
            if ($timeGap >= 30) {
                if ($calculatedPassword === $correctPassword) {//如果加盐后的密码和数据库中密码一致则登陆成功
                    session_start();
                    $_SESSION['username'] = $_POST['username'];
                    $_SESSION['uid'] = $uid;
                    $this->pdoAdapter->exec("update traveluser set LastTimeTryLogin=? where UID=?", array($thisTimeTryLogin, $uid));
                    header("location:index.php");
                    exit();
                } else {//否则登陆失败，提示用户密码错误
                    $this->message = "用户名或密码错误。";
                    $this->pdoAdapter->exec("update traveluser set LastTimeTryLogin=? where UID=?", array($thisTimeTryLogin, $uid));
                }
            } else {
                $this->message = "你的登陆过于频繁，请30秒后再来尝试登陆";
            }


        }


    }

    public function printMessageIfNotEmpty()
    {
        if (!customIsEmpty($this->message)) {
            echo "<div style='color: red;font-size: 110%'>$this->message</div>";
        }
    }


}