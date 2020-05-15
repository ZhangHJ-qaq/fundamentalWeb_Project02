<?php
include_once "class/Page.class.php";
include_once "utilities/utilityFunction.php";
include_once "utilities/PDOAdapter.php";
include_once "utilities/dbconfig.php";
include_once "class/PageWithCaptcha.php";
include_once "class/MyCaptchaBuilder.php";


class Login extends Page implements PageWithCaptcha
{
    private $message;
    private $myCaptchaBuilder;

    public function __construct()
    {
        parent::__construct();
        $this->myCaptchaBuilder = new MyCaptchaBuilder();
    }

    public function tryLogin($username, $password, $userInputCaptcha)
    {
        if (!customIsEmpty($username) && !customIsEmpty($password)) {//如果用户用户名和密码都有输入

            //检测验证码
            if (!$this->checkCaptchaInput($userInputCaptcha)) {
                $this->message = "验证码错误";
                return;
            }

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