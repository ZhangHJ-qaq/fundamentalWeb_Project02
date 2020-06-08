<?php
include_once $_SERVER['DOCUMENT_ROOT'] . "/class/Page.class.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/class/User.class.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/utilities/utilityFunction.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/class/MyCaptchaBuilder.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/class/PageWithCaptcha.php";

class ChangePassword extends Page implements PageWithCaptcha
{
    private $message;
    private $myCaptchaBuilder;

    public function __construct()
    {
        parent::__construct();
        session_start();
        $this->myCaptchaBuilder = new MyCaptchaBuilder();


    }

    function changePassword($originalUnsaltedPasswordInput, $newPassword1, $newPassword2, $userInputCaptcha)
    {
        if (!customIsEmpty($originalUnsaltedPasswordInput) && !empty($newPassword1) && !empty($newPassword2)) {
            if (!$this->checkCaptchaInput($userInputCaptcha)) {
                $this->message = "验证码错误";
                return;
            }

            $this->message = $this->user->changePassword($originalUnsaltedPasswordInput, $newPassword1, $newPassword2);
        } else if (!customIsEmpty($originalUnsaltedPasswordInput) || !empty($newPassword1) || !empty($newPassword2)) {
            $this->message = "信息填写不完整。请填写完整信息后再试";
        }
    }

    function printMessage()
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