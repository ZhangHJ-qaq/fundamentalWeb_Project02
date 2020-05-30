<?php
include_once $_SERVER['DOCUMENT_ROOT'] . "/class/Page.class.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/utilities/utilityFunction.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/utilities/PDOAdapter.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/utilities/dbconfig.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/utilities/htmlpurifier-4.12.0/library/HTMLPurifier.auto.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/class/MyCaptchaBuilder.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/class/PageWithCaptcha.php";

class Register extends Page implements PageWithCaptcha
{
    private $message;
    private $htmlPurifier;
    private $myCaptchaBuilder;

    public function __construct()
    {
        parent::__construct();
        $this->htmlPurifier = new HTMLPurifier();
        $this->myCaptchaBuilder = new MyCaptchaBuilder();

    }

    function checkIfHasLoggedIn()
    {
        session_start();
        if (isset($_SESSION['username'])) {//如果已登录则跳到主页
            header("location:index.php");
            exit();
        }
    }

    function tryRegister($username, $email, $password, $confirmPassword, $userInputCaptchaAnswer)
    {


        if ((!customIsEmpty($username)) && (!customIsEmpty($email)) && (!customIsEmpty($password)) && (!customIsEmpty($confirmPassword))) {
            //如果用户四样都有填写，则开始尝试注册流程
            if (!$this->checkCaptchaInput($userInputCaptchaAnswer)) {
                $this->message = "验证码错误";
                return;
            }

            if ($this->avoidFrequentRegister()) {
                $this->message = "你的注册过于频繁。请60秒后再试";
                return;
            }
            $this->checkInputThenCreateUser($username, $email, $password, $confirmPassword);
        } else if ((!customIsEmpty($username)) && (!customIsEmpty($email)) && (!customIsEmpty($password)) && (!customIsEmpty($confirmPassword))) {
            $this->message = "你的信息填写不完整。请填写完整信息后再试";
        }

    }

    function checkInputThenCreateUser($username, $email, $password, $confirmPassword)
    {
        $purifiedUsername = $this->htmlPurifier->purify($username);
        $purifiedPassword1 = $this->htmlPurifier->purify($password);
        $purifiedPassword2 = $this->htmlPurifier->purify($confirmPassword);
        $purifiedEmail = $this->htmlPurifier->purify($email);//净化用户的输入
        if (!($purifiedUsername === $_POST['username'] && $purifiedPassword1 === $_POST['password1'] && $purifiedPassword2 === $_POST['password2'] && $purifiedEmail === $_POST['email'])) {
            //如果净化后用户的输入和原先的输入不相等
            $this->message = "不可预知的内部错误。";
        }

        //在后端验证用户输入是否符合要求
        if (!preg_match("/^[0-9a-zA-Z]{6,18}$/", $purifiedUsername)) {
            $this->message = "用户名必须是6-18位，且只能由字母和数字组成";
            return;
        }
        if (!preg_match("/^[0-9a-zA-Z][0-9a-zA-Z\-.]+@([a-zA-Z0-9]+\.)+[a-z]{2,4}$/", $purifiedEmail)) {
            $this->message = "邮箱格式错误";
            return;
        }
        if (!$this->checkPassword($purifiedPassword2, $purifiedPassword1)) {
            $this->message = "密码必须是6-18位，不能是纯数字。两次密码输入必须一致";
            return;
        }

        //检测用户名是否已经被别人先注册
        if ($this->checkUserExist($purifiedUsername)) {
            $this->message = "这个用户名已经被别人注册了。请换一个用户名";
            return;
        }

        //创建用户，并提示错误与成功
        if (!$this->createUser($purifiedUsername, $purifiedPassword1, $purifiedEmail)) {
            $this->message = "未知的错误，注册失败";
            return;
        } else {
            $this->message = "注册成功。<a href='login.php'>去登陆</a>";
        }


    }

    function printMessageIfNotEmpty()
    {
        if (!customIsEmpty($this->message)) {
            echo "<div style='color: red;font-size: 110%;'>$this->message</div>";
        }
    }


    function generateCaptcha()
    {
        $this->myCaptchaBuilder->generateCaptcha();
        $questionText = $this->myCaptchaBuilder->getCaptchaQuestionText();
        $answer = $this->myCaptchaBuilder->getCaptchaAnswer();
        echo "<label class='pure-u-1'>$questionText</label>";
        echo "<input class='pure-u-1' name='captcha' type='text'>";
        session_start();
        $_SESSION['captchaAnswer'] = $answer;
        // TODO: Implement generateCaptcha() method.
    }

    private function avoidFrequentRegister()//检测用户是否频繁注册的机制。如果返回true说明用户频繁注册，返回false说明用户没有频繁注册。
    {
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        if ($this->pdoAdapter->isRowCountZero("select * from registerip where IP=?", array($ipAddress))) {//如果这个ip从未在注册过的ip库中出现过
            $thisTimeTryRegister = time();
            $this->pdoAdapter->exec("insert into registerip (IP, LastTimeTryRegister) values (?,?)", array($ipAddress, $thisTimeTryRegister));
            return false;
        } else {
            $row = $this->pdoAdapter->selectRows("select * from registerip where IP=?", array($ipAddress))[0];
            $lastTimeTryRegister = $row['LastTimeTryRegister'];
            $thisTimeTryRegister = time();
            $timeGap = $thisTimeTryRegister - $lastTimeTryRegister;
            if ($timeGap < 60) {
                $this->pdoAdapter->exec("update registerip set LastTimeTryRegister=? where IP=?", array($thisTimeTryRegister, $ipAddress));
                return true;
            } else {
                $this->pdoAdapter->exec("update registerip set LastTimeTryRegister=? where IP=?", array($thisTimeTryRegister, $ipAddress));
                return false;
            }

        }
    }

    private function checkPassword($password1, $password2)
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

    private function checkUserExist($username)
    {
        if ($this->pdoAdapter->isRowCountZero("select * from traveluser where UserName=?", array($username))) {
            return false;
        } else {
            return true;
        }

    }

    private function createUser($username, $password, $email)
    {
        $dateJoined = date("Y-m-d h:i:sa");
        $salt = get_hash();//随机生成一个盐
        $saltedEncryptedPassword = MD5($password . $salt);//哈希加盐，将密码存储到数据库
        return $this->pdoAdapter->insertARow("insert into traveluser (UserName, Email, Pass, State, DateJoined, DateLastModified, salt,LastTimeTryLogin) VALUES (?,?,?,?,?,?,?,?)", array($username, $email, $saltedEncryptedPassword, 1, $dateJoined, $dateJoined, $salt, 0));
    }

    function checkCaptchaInput($userCaptchaInput)
    {
        session_start();
        $correctCaptchaAnswer = $_SESSION['captchaAnswer'];
        if ($userCaptchaInput != $correctCaptchaAnswer) {
            return false;
        } else {
            return true;
        }
        // TODO: Implement checkCaptchaInput() method.
    }
}