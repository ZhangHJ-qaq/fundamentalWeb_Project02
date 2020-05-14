<?php
session_start();
include_once "class/MyFavor.class.php";
$myFavor = new MyFavor();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ZHJ的旅游图片分享站-我的收藏</title>
    <link rel="stylesheet" href="css/library/reset.css">
    <link rel="stylesheet" href="css/library/pure-release-1.0.1/pure-min.css">
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
            <form class="pure-u-20-24 pure-form" action="myFavor.php">
                <fieldset>
                    <legend>搜索</legend>
                    <div class="pure-g">
                        <?php
                        $myFavor->printTitleInput($_GET['title']);
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

                //取消收藏（是否合法的逻辑在user类中完成)
                $myFavor->unlike($_GET['unlikeImageId']);

                //打印出取消收藏是否成功的结果
                $myFavor->printUnlikeInfo();


                $myFavor->searchFavoredImage($_GET['page'], $_SESSION['uid'], $_GET['title']);
                $myFavor->printSearchResult();

                //当用户的照片为空时，打印出空照片的提示
                $myFavor->printMessageWhileEmpty();

                ?>
            </div>
            <div class="pagination pure-u-1">
                <?php
                $myFavor->closePDO();
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