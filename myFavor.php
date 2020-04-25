<?php
session_start();
include_once "class/MyFavor.class.php";
$myFavor = new MyFavor();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>我的收藏</title>
    <link rel="stylesheet" href="css/library/reset.css">
    <link rel="stylesheet" href="css/library/pure-release-1.0.1/pure.css">
    <link rel="stylesheet" href="css/universal.css">
    <link rel="stylesheet" href="css/myFavor_myPhoto.css">
    <script src="js/library/jquery-3.4.1.js"></script>
    <script src="js/dropdownControl.js"></script>
</head>
<body>
<?php
$myFavor->printHeaderNeedLogin();
?>
<div class="wrapper pure-g">
    <div class="pure-u-2-24"></div>
    <main class="pure-u-20-24 hasShadow" id="panel">
        <h1 class="title">我的收藏</h1>
        <div class="wrapper pure-g">
            <div class="pure-u-2-24"></div>
            <div class="box pure-u-20-24">
                <?php

                $myFavor->unlike($_GET['unlikeImageId']);
                $myFavor->printUnlikeInfo();
                $myFavor->searchFavoredImage($_GET['page'], $_SESSION['uid']);
                $myFavor->printSearchResult();
                $myFavor->printMessageWhileEmpty();

                ?>
            </div>
            <div class="pagination pure-u-1">
                <?php
                $myFavor->printPagination();
                ?>
            </div>
        </div>
    </main>
</div>

<footer>
    ZHJ制作 19302010021 本网站由<a href="https://purecss.net">Pure.css</a>驱动
</footer>
</body>
</html>