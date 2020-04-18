<?php
include_once "utilities/PDOAdapter.php";
include_once "utilities/dbconfig.php";
include_once "utilities/utilityFunction.php";
$pdoAdapter = new PDOAdapter(HEADER, DBACCOUNT, DBPASSWORD, DBNAME);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>我的照片</title>
    <link rel="stylesheet" href="css/library/reset.css">
    <link rel="stylesheet" href="css/library/pure-release-1.0.1/pure.css">
    <link rel="stylesheet" href="css/library/pure-release-1.0.1/grids-responsive.css">
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
    if (isset($_SESSION['username']) && isset($_SESSION['uid'])) {
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
        header("location:login.php");
        exit();
    }
    ?>
</header>
<?php
if (!customIsEmpty($_GET['deleteID'])) {
    if (!userHasTheImage($_SESSION['uid'], $_GET['deleteID'])) {
        header("location:error.php?errorCode=12");
        exit();
    }
    $imagePath = $pdoAdapter->selectRows("select PATH from travelimage where ImageID=?", array($_GET['deleteID']))[0]['PATH'];
    $pdoAdapter->beginTransaction();
    $sql = "delete from travelimage where ImageID=?";
    $resultOfDeleteImage = $pdoAdapter->exec($sql, array($_GET['deleteID']));
    $sql = "delete from travelimagefavor where ImageID=?";
    $resultOfDeleteFavor = $pdoAdapter->exec($sql, array($_GET['deleteID']));
    $resultOfDeleteSmallFile=deleteFile("img/small/$imagePath");
    $resultOfDeleteBigFile=deleteFile("img/large/$imagePath");

    if ($resultOfDeleteFavor && $resultOfDeleteImage && $resultOfDeleteBigFile && $resultOfDeleteSmallFile) {
        $message = "删除成功";
        $pdoAdapter->commit();
    } else {
        $message = "删除失败";
        $pdoAdapter->rollBack();
    }
}

function userHasTheImage($uid, $imageID)
{
    global $pdoAdapter;
    $sql = "select imageID from travelimage where UID=? and ImageID=?";
    $count = $pdoAdapter->getRowCount($sql, array($uid, $imageID));
    return $count === 1;
}

?>
<div class="wrapper pure-g">
    <div class="pure-u-2-24"></div>
    <main class="pure-u-20-24" id="panel">
        <h1 class="title">我的照片</h1>
        <div class="wrapper pure-g">
            <div class="pure-u-2-24"></div>
            <div class="box pure-u-20-24">
                <?php
                echo "<div class='pure-u-1' style='color: red;font-size: 120%'>$message</div>";
                function printImageList($imageList)
                {
                    for ($i = 0; $i <= count($imageList) - 1; $i++) {
                        $title = $imageList[$i]['Title'];
                        $imageID = $imageList[$i]['ImageID'];
                        $desc = $imageList[$i]['Description'];
                        $path = $imageList[$i]['PATH'];
                        echo "<div class='imageCard'>";
                        echo "<a href='imageDetail.php?imageID=$imageID'><img src=img/small/$path class='thumbnail' alt=$title></a>";
                        echo "<h1>$title</h1>";
                        echo "<p>$desc</p>";
                        echo "<button class='pure-button pure-button-primary' onclick=window.location='upload_edit.php?action=modify&modifyID=$imageID'>修改</button>";
                        echo "<button class='pure-button pure-button-primary' onclick=window.location='myPhoto.php?deleteID=$imageID'>删除</button>";
                        echo "</div>";
                    }
                }

                if ($hasLoggedIn) {
                    $page = isset($_GET['page']) && isPositiveNumber($_GET['page']) ? $_GET['page'] : 1;
                    $count = $pdoAdapter->getRowCount("select ImageID,Title,Description,PATH from travelimage where UID=?", array($_SESSION['uid']));
                    if ($count > 0) {
                        $maxNumOfPage = ceil($count / 16);
                        $page = $page > $maxNumOfPage ? $maxNumOfPage : $page;
                        $offset = ($page - 1) * 5;
                        $sql = "select ImageID,Title,Description,PATH from travelimage where UID=? limit 5 offset $offset";
                        $imageList = $pdoAdapter->selectRows($sql, array($_SESSION['uid']));
                        printImageList($imageList);
                        $needPagination = $maxNumOfPage > 1;
                    } else {
                        echo "<h2 class='message'>你还没有上传过任何图片</h2>";
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