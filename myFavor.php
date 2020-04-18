<?php
include_once "utilities/PDOAdapter.php";
include_once "utilities/dbconfig.php";
include_once "utilities/utilityFunction.php";
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
</head>
<body>
<header>
    <?php
    session_start();
    if (isset($_SESSION['username'])) {
        $uname = $_SESSION['username'];
        echo "<span>$uname</span>";
    }
    ?>
    <a href='index.php'>主页</a>
    <a href='browser.php'>浏览</a>
    <a href='search.php'>搜索</a>
    <?php
    if (isset($_SESSION['username'])) {
        echo "<div id=\"personalCenter\">
        个人中心
        <div id=\"headerDropdownMenu\">
            <a href=\"upload_edit.php\">上传照片</a>
            <a href=\"myPhoto.php\">我的照片</a>
            <a href=\"myFavor.php\">我的收藏</a>
            <a href=\"logout.php\">登出</a>
        </div>
    </div>";
        $hasLoggedIn = true;
    } else {
        header("location:login.php");
        exit();
    }
    ?>
</header>
<div class="wrapper pure-g">
    <div class="pure-u-2-24"></div>
    <main class="pure-u-20-24" id="panel">
        <h1 class="title">我的收藏</h1>
        <div class="wrapper pure-g">
            <div class="pure-u-2-24"></div>
            <div class="box pure-u-20-24">
                <?php
                try {
                    $pdoAdapter = new PDOAdapter(HEADER, DBACCOUNT, DBPASSWORD, DBNAME);
                } catch (PDOException $exception) {
                    header("location:error.php?errorCode=0");
                }
                if (isset($_GET['unlikeImageId'])) {//如果用户有取消收藏的请求
                    if (isPositiveNumber($_GET['unlikeImageId'])) {
                        $unlikeImageId = $_GET['unlikeImageId'];
                        $sql = "delete from travelimagefavor where ImageID=? &&UID=?";
                        $success = $pdoAdapter->deleteRows($sql, array($unlikeImageId, $_SESSION['uid']));
                        if ($success) {
                            echo "<h2  class='message'>取消收藏成功</h2>";
                        } else {
                            echo "<h2  class='message'>取消收藏失败</h2>";
                        }
                    } else {
                        echo "<h2 class='message'>取消收藏的请求不合法</h2>";
                    }

                }
                $page = isset($_GET['page']) ? $_GET['page'] : 1;
                $page = isPositiveNumber($page) ? $page : 1;
                $page = $page >= 1 ? $page : 1;//过滤掉不符合的输入
                $sql = "select travelimage.ImageID,Title,Description,PATH from travelimagefavor inner join travelimage on travelimage.ImageID=travelimagefavor.ImageID where travelimagefavor.UID=?";
                $imageArray = $pdoAdapter->selectRows($sql, array($_SESSION['uid']));
                if (count($imageArray) !== 0) {
                    $counts = count($imageArray);
                    $maxNumOfPage = ceil($counts / 5);
                    $page = $page > $maxNumOfPage ? $maxNumOfPage : $page;
                    $offset = ($page - 1) * 5;
                    $sql = "select travelimage.ImageID,Title,Description,PATH from travelimage inner join travelimagefavor on travelimage.ImageID=travelimagefavor.ImageID where travelimagefavor.UID=? limit 5 offset $offset";
                    $imageArray = $pdoAdapter->selectRows($sql, array($_SESSION['uid']));
                    printImage($imageArray);
                    $needPagination = $maxNumOfPage>1;
                } else {
                    echo "<h2 class='message'>你还没有收藏图片</h2>";
                }


                function printImage($imageArray)
                {
                    global $page;
                    for ($i = 0; $i <= count($imageArray) - 1; $i++) {
                        $imageID = $imageArray[$i]['ImageID'];
                        $path = $imageArray[$i]['PATH'];
                        $title = $imageArray[$i]['Title'];
                        $desc = $imageArray[$i]['Description'];
                        echo "<div class='imageCard'>";
                        echo "<a href=imageDetail.php?imageID=$imageID><img src=img/small/$path alt=$title class='thumbnail'></a>";
                        echo "<h1>$title</h1>";
                        echo "<p>$desc</p>";
                        echo "<button class='pure-button-primary pure-button'><a href='myFavor.php?unlikeImageId=$imageID&page=$page'>取消收藏</a></button>";
                        echo "</div>";
                    }
                }

                ?>
            </div>
            <div class="pagination pure-u-1">
                <?php
                if ($needPagination) {
                    if ($page != 1) {
                        $previousPage = $page - 1;
                        echo "<a href='myFavor.php?page=$previousPage'>上一页</a>";
                    }
                    for ($i = 1; $i <= $maxNumOfPage; $i++) {
                        if ($i == $page) {
                            echo "<a style='color: red;font-weight: bold' href='search.php?page=$i'>$i</a>";
                        } else {
                            echo "<a href='myFavor.php?page=$i'>$i</a>";
                        }
                    }
                    if ($page != $maxNumOfPage) {
                        $nextPage = $page + 1;
                        echo "<a href='myFavor.php?page=$nextPage'>下一页</a>";
                    }
                }
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