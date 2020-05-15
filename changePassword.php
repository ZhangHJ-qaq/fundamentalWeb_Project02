<?php
include_once "class/ChangePassword.php";
$changePassword = new ChangePassword();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>密码修改</title>
    <link rel="stylesheet" href="css/library/reset.css">
    <link rel="stylesheet" href="css/library/pure-release-1.0.1/pure-min.css">
    <link rel="stylesheet" href="css/universal.css">
    <link rel="stylesheet" href="css/changePassword.css">
    <script src="js/library/jquery-3.4.1.js"></script>
    <script src="js/dropdownControl.js"></script>
    <script src="js/changePasswordPrevalidation.js"></script>
</head>
<body>
<?php
$changePassword->printHeaderNeedLogin();
$changePassword->changePassword($_POST['originalPassword'], $_POST['newPassword'], $_POST['newPasswordConfirm'],$_POST['captcha']);


?>

<div class="wrapper pure-g">
    <div class="pure-u-1-6"></div>
    <form class="pure-u-2-3 pure-form hasShadow" method="post" action="changePassword.php">
        <fieldset>
            <legend>修改密码</legend>
            <div class="pure-g wrapper">
                <div class="pure-u-1-6"></div>
                <div class="pure-u-2-3">
                    <?php
                    $changePassword->printMessage();
                    ?>
                    <div class="wrapper pure-g" id="foo">
                        <label class="pure-u-1">原密码:</label>
                        <input class="pure-u-1" name="originalPassword" type="password">
                        <label class="pure-u-1">新密码</label>
                        <input class="pure-u-1" name="newPassword" type="password" id="newPassword">
                        <label class="pure-u-1">确认新密码</label>
                        <input class="pure-u-1" name="newPasswordConfirm" type="password" id="newPasswordConfirm">
                        <?php
                        $changePassword->generateCaptcha();
                        ?>
                        <button class="pure-u-1 pure-button-primary pure-button" id="changePasswordButton"
                                type="submit">修改密码
                        </button>
                        <div id="errorArea" class="pure-u-1"></div>
                    </div>
                </div>
            </div>
        </fieldset>
    </form>
</div>

</body>
</html>