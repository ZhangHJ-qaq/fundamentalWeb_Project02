<?php
include_once "class/Page.class.php";
include_once "class/User.class.php";
include_once "utilities/utilityFunction.php";

class ChangePassword extends Page
{
    private $user;
    private $message;

    public function __construct()
    {
        parent::__construct();
        session_start();
        $this->user = new User($_SESSION['uid'], $this->pdoAdapter);


    }

    function changePassword($originalUnsaltedPasswordInput, $newPassword1, $newPassword2)
    {
        if (!customIsEmpty($originalUnsaltedPasswordInput) && !empty($newPassword1) && !empty($newPassword2)) {
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

}