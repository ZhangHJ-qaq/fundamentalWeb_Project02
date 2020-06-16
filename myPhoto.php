<?php
session_start();
include_once $_SERVER['DOCUMENT_ROOT'] . "/class/MyPhoto.class.php";
$myPhoto = new MyPhoto();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ZHJ的旅游图片分享站-我的照片</title>
    <link rel="stylesheet" href="css/library/reset.css">
    <link rel="stylesheet" href="css/library/pure-release-1.0.1/pure-min.css">
    <link rel="stylesheet" href="css/library/pure-release-1.0.1/grids-responsive-min.css">
    <link rel="stylesheet" href="css/universal.css">
    <link rel="stylesheet" href="css/myFavor_myPhoto.css">
    <script src="js/library/jquery-3.4.1.js"></script>
    <script src="js/dropdownControl.js"></script>
</head>
<body>

<?php
$myPhoto->printHeaderNeedLogin();
?>

<?php

$myPhoto->deleteImage($_GET['deleteID']);
$myPhoto->purifyTitleInput();

?>
<div class="wrapper pure-g">
    <div class="pure-u-2-24"></div>
    <main class="pure-u-20-24 hasShadow" id="panel">
        <h1 class="title">我的照片</h1>
        <div class="wrapper pure-g">
            <div class="pure-u-2-24"></div>
            <form class="pure-u-20-24 pure-form" action="myPhoto.php">
                <fieldset>
                    <legend>搜索</legend>
                    <div class="pure-g">
                        <?php
                        $myPhoto->printTitleInput($_GET['title'])
                        ?>
                        <button type="submit" class="pure-button pure-button-active pure-u-6-24">搜索</button>
                    </div>
                </fieldset>
            </form>
        </div>
        <div class="wrapper pure-g">
            <div class="pure-u-2-24"></div>
            <div class="box pure-u-20-24">
                <?php
                //打印出删除照片结果的信息
                $myPhoto->printDeleteMessage();

                $myPhoto->searchMyPhoto($_GET['page'], $_GET['title']);
                $myPhoto->printSearchResult();

                //如果用户没有照片，打印出没有照片的提示
                $myPhoto->printMessageWhileEmpty();
                ?>
            </div>
            <div class="pagination pure-u-1">
                <?php
                $myPhoto->closePDO();
                $myPhoto->printPagination();
                ?>
            </div>
        </div>
    </main>
</div>


</body>
</html>