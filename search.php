<?php
include_once "utilities/PDOAdapter.php";
include_once "utilities/dbconfig.php";
include_once "utilities/utilityFunction.php";
try {
    $pdoAdapter = new PDOAdapter(HEADER, DBACCOUNT, DBPASSWORD, DBNAME);
} catch (PDOException $PDOException) {
    header("location:error.php?errorCode=0");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>搜索</title>
    <link rel="stylesheet" href="css/library/reset.css">
    <link rel="stylesheet" href="css/library/pure-release-1.0.1/pure.css">
    <link rel="stylesheet" href="css/library/pure-release-1.0.1/grids-responsive.css">
    <link rel="stylesheet" href="css/universal.css">
    <link rel="stylesheet" href="css/search.css">
    <script src="js/library/jquery-3.4.1.js"></script>
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
<div class="wrapper pure-g">
    <div class="pure-u-1-6"></div>
    <main class="pure-u-2-3">
        <form class="pure-form" method="get" action="search.php" id="searchForm">
            <fieldset>
                <legend>搜索</legend>
                <div class="wrapper pure-g">
                    <div class="pure-u-1 pure-u-md-11-24">
                        <input type="radio" name="searchWay" value="title" id="titleSearchRatio">
                        <label>按标题搜索</label>
                        <input type="text" name="titleInput" id="titleInput">
                    </div>
                    <div class="pure-u-1 pure-u-md-11-24">
                        <input type="radio" name="searchWay" value="desc" id="descSearchRatio">
                        <label>按描述搜索</label>
                        <input type="text" name="descInput" id="descInput">
                    </div>
                    <button id="searchButton" type="submit"
                            class="pure-u-1 pure-u-md-2-24 pure-button pure-button-primary">搜索
                    </button>
                </div>
            </fieldset>
        </form>
        <div id="searchResultBox">
            <?php

            function printSearchResult($imageArray)
            {
                for ($i = 0; $i <= count($imageArray) - 1; $i++) {
                    $imageID = $imageArray[$i]['imageID'];
                    $title = $imageArray[$i]['title'];
                    $description = $imageArray[$i]['description'];
                    $path = $imageArray[$i]['path'];
                    echo "<div class='imageCard'>";
                    echo "<a href=imageDetail.php?imageID=$imageID><img src=img/small/$path class='thumbnail' alt=$title></a>";
                    echo "<h1>$title</h1>";
                    echo "<p>$description</p>";
                    echo "</div>";
                }
            }

            $searchWay = $_GET['searchWay'];//得到用户的搜搜方法
            $titleInput = $_GET['titleInput'];
            $descInput = $_GET['descInput'];
            $page = isset($_GET['page']) && isPositiveNumber($_GET['page']) && $_GET['page'] >= 1 ? $_GET['page'] : 1;
            $hasSearched = false;
            if ($searchWay === 'title') {//如果用户用标题搜索
                if (isset($titleInput) && $titleInput !== '') {
                    $sql = "select * from travelimage where Title REGEXP ?";
                    $rowCount = $pdoAdapter->getRowCount($sql, array($titleInput));
                    if ($rowCount === 0) {
                        echo "<h1>没有找到相关内容</h1>";
                    } else {
                        $maxNumOfPage = ceil($rowCount / 5);
                        $page = $page > $maxNumOfPage ? $maxNumOfPage : $page;
                        $offset = ($page - 1) * 5;
                        $newsql = "select imageID,title,description,path from travelimage where Title REGEXP ? limit 5 offset $offset";
                        $imageArray = $pdoAdapter->selectRows($newsql, array($titleInput));
                        printSearchResult($imageArray);
                        $needPagination = $maxNumOfPage > 1;
                    }
                }
            } else if ($searchWay === 'desc') {//如果用户用内容搜索
                if (isset($descInput) && $descInput !== '') {
                    $sql = "select * from travelimage where Description REGEXP ?";
                    $rowCount = $pdoAdapter->getRowCount($sql, array($descInput));
                    if ($rowCount === 0) {
                        echo "<h1>没有找到相关内容</h1>";
                    } else {
                        $maxNumOfPage = (int)($rowCount / 5 + 1);
                        $page = $page > $maxNumOfPage ? $maxNumOfPage : $page;
                        $offset = ($page - 1) * 5;
                        $newsql = "select imageID,title,description,path from travelimage where Description REGEXP ? limit 5 offset $offset";
                        $imageArray = $pdoAdapter->selectRows($newsql, array($descInput));
                        printSearchResult($imageArray);
                        $needPagination = $maxNumOfPage > 1;
                    }
                }
            }
            ?>
        </div>
        <div class="pagination">
            <?php
            $pdoAdapter->close();
            if ($needPagination) {
                if ($page != 1) {
                    $previousPage = $page - 1;
                    echo "<a href='search.php?searchWay=$searchWay&titleInput=$titleInput&descInput=$descInput&page=$previousPage'>上一页</a>";
                }
                for ($i = 1; $i <= $maxNumOfPage; $i++) {
                    if ($i == $page) {
                        echo "<a style='color: red;font-weight: bold' href='search.php?searchWay=$searchWay&titleInput=$titleInput&descInput=$descInput&page=$i'>$i</a>";
                    } else {
                        echo "<a href='search.php?searchWay=$searchWay&titleInput=$titleInput&descInput=$descInput&page=$i'>$i</a>";
                    }
                }
                if ($page != $maxNumOfPage) {
                    $nextPage = $page + 1;
                    echo "<a href='search.php?searchWay=$searchWay&titleInput=$titleInput&descInput=$descInput&page=$nextPage'>下一页</a>";
                }
            }
            ?>
        </div>
    </main>
</div>
<footer>
    ZHJ制作 19302010021 本网站由<a href="https://purecss.net">Pure.css</a>驱动
</footer>

</body>
</html>