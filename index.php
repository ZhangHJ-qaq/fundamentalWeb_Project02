<?php
include_once "utilities/dbconfig.php";
include_once "utilities/PDOAdapter.php";
try{
    $adapter = new PDOAdapter(HEADER, DBACCOUNT, DBPASSWORD, DBNAME);

}catch (PDOException $PDOException){
    header("location:error.php?errorCode=0");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>首页</title>
    <link rel="stylesheet" href="css/library/reset.css">
    <link rel="stylesheet" href="css/library/pure-release-1.0.1/pure.css">
    <link rel="stylesheet" href="css/library/pure-release-1.0.1/grids-responsive.css">
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="css/universal.css">
    <script src="js/library/jquery-3.4.1.js"></script>
    <script src="ajax/ajax_homeImageRefresh.js"></script>
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
        echo "<a href='login.php' id='login'>登录</a>";
        $hasLoggedIn = false;
    }
    ?>
</header>
<main class="pure-g">
    <div class="pure-u-2-24"></div>
    <div class="pure-u-20-24">
        <?php
        //从数据库中随机读取一张高清大图展示
        $image = $adapter->selectRows("select Title,PATH,ImageID from travelimage order by rand() limit 1")[0];
        $path = $image['PATH'];
        $title = $image['Title'];
        $imageID = $image['ImageID'];
        echo "<a class='pure-u-1' href=imageDetail.php?imageID=$imageID>";
        echo "<img src=img/large/$path alt='$title' class='pure-u-1'>";
        echo "</a>";
        ?>

        <div id="wrapper" class="pure-u-24-24">
            <div id="box" class=" pure-g">
                <?php
                //从数据库中读取收藏最多的六张图片并展示
                $sql = "select travelimagefavor.ImageID,Title,Description,PATH,count(travelimagefavor.ImageID) 
                        as count 
                        from travelimagefavor 
                        inner join travelimage on travelimage.ImageID=travelimagefavor.ImageID 
                        group by ImageID 
                        order by count desc limit 6";
                $imageArray = $adapter->selectRows($sql);
                $adapter->close();
                for ($i = 0; $i <= count($imageArray) - 1; $i++) {
                    $imageID = $imageArray[$i]['ImageID'];
                    $title = $imageArray[$i]['Title'];
                    $description = $imageArray[$i]['Description'];
                    $path = $imageArray[$i]['PATH'];
                    echo "<div class='card pure-u-1-1 pure-u-md-1-2 pure-u-lg-1-3'>";
                    echo "<a href=imageDetail.php?imageID=$imageID><img src=img/small/$path alt=$title class='thumbnail pure-u-1-2'></a>";
                    echo "<h1>$title</h1>";
                    echo "<p>$description</p>";
                    echo "</div>";
                }
                ?>
            </div>
        </div>
    </div>

</main>
<footer>
    ZHJ制作 19302010021 本网站由<a href="https://purecss.net">Pure.css</a>驱动
</footer>
<div id="fixedButton">
    <button class="pure-button-primary pure-button" id="refresh">刷新页面</button>
    <br>
    <button class="pure-button pure-button-active" onclick=" document.body.scrollTop = 0;
    document.documentElement.scrollTop = 0;">返回顶部
    </button>
</div>
</body>
</html>
