<?php
include_once "utilities/dbconfig.php";
include_once "utilities/PDOAdapter.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>图片详情</title>
    <link rel="stylesheet" href="css/library/reset.css">
    <link rel="stylesheet" href="css/library/pure-release-1.0.1/pure.css">
    <link rel="stylesheet" href="css/library/pure-release-1.0.1/grids-responsive.css">
    <link rel="stylesheet" href="css/universal.css">
    <link rel="stylesheet" href="css/imageDetail.css">
</head>
<body>
<header>
    <?php
    session_start();
    if (isset($_SESSION['username'])) {//如果用户已经登录，则在header的左边打出用户名
        $uname = $_SESSION['username'];
        echo "<span>$uname</span>";
    }
    ?>
    <a href='index.php'>主页</a>
    <a href='browser.php'>浏览</a>
    <a href='search.php'>搜索</a>
    <?php
    if (isset($_SESSION['username'])) {//如果用户登录，显示个人中心
        echo "<div id=\"personalCenter\">
        个人中心
        <div id=\"headerDropdownMenu\">
            <a href=\"upload.php\">上传照片</a>
            <a href=\"myPhoto.php\">我的照片</a>
            <a href=\"myFavor.php\">我的收藏</a>
            <a href=\"logout.php\">登出</a>
        </div>
    </div>";
        $hasLoggedIn = true;
    } else {//反之显示登录
        echo "<a href='login.php' id='login'>登录</a>";
        $hasLoggedIn = false;
    }
    ?>
</header>
<div class="wrapper pure-g">
    <div class="pure-u-2-24"></div>
    <main class="pure-u-20-24" id="panel">
        <div class="wrapper pure-g">
            <div class="pure-u-1-2">
                <?php
                if (isset($_GET['imageID'])) {
                    try {
                        $pdoAdapter = new PDOAdapter(HEADER, DBACCOUNT, DBPASSWORD, DBNAME);
                    } catch (PDOException $exception) {
                        header("location:error.php?errorCode=0");
                    }
                    $sql = "select Title,UserName,Description,AsciiName,path,CountryName,ContentName,ImageID 
                    from ((travelimage inner join traveluser on travelimage.UID = traveluser.UID) 
                    inner join geocities on geocities.GeoNameID=travelimage.CityCode)
                    inner join geocountries on travelimage.CountryCodeISO=geocountries.ISO 
                    inner join geocontents on geocontents.ContentID=travelimage.ContentID
                    where ImageID=?";
                    $imageArray = $pdoAdapter->selectRows($sql, array($_GET['imageID']));
                    $getImage = count($imageArray) === 1;//判断是否得到了图像
                    if ($getImage) {//如果得到图像则输出图像
                        $title = $imageArray[0]['Title'];
                        $username = $imageArray[0]['UserName'];
                        $desc = $imageArray[0]['Description'];
                        $cityName = $imageArray[0]['AsciiName'];
                        $path = $imageArray[0]['path'];
                        $content = $imageArray[0]['ContentName'];
                        $countryName = $imageArray[0]['CountryName'];
                        $imageID = $imageArray[0]['ImageID'];
                        echo "<img src=img/large/$path alt='$title' style='max-width: 100%'>";
                    } else {//反之则跳转到错误页提示输入图像不存在
                        header("location:error.php?errorCode=7");
                        exit();
                    }
                }
                ?>
            </div>
            <div class="pure-u-1-2">
                <div class="wrapper pure-g">
                    <div class="pure-u-1-6"></div>
                    <div class="pure-u-2-3" id="infoArea">
                        <?php
                        if ($getImage) {
                            function getNumOfFavor($imageID)
                            {
                                global $pdoAdapter;
                                $sql = "select * from travelimagefavor where ImageID=?";
                                $result = $pdoAdapter->selectRows($sql, array($imageID));
                                return count($result);
                            }

                            function userLikedTheImage($uid, $imageID)
                            {
                                global $pdoAdapter;
                                $sql = "select * from travelimagefavor where UID=? and ImageID=?";
                                $result = $pdoAdapter->selectRows($sql, array($uid, $imageID));
                                return count($result) !== 0;
                            }

                            function doLogicOfLikeAndUnlike()
                            {//收藏和取消收藏的逻辑
                                global $pdoAdapter;
                                global $imageID;
                                global $hasLoggedIn;
                                if (isset($_GET['like']) && $_GET['like'] === 'true') {//判断用户是否有给出收藏请求
                                    if ($hasLoggedIn) {//如果用户已经登录
                                        if (userLikedTheImage($_SESSION['uid'], $imageID)) {
                                            $message = "你已经收藏过这个图片了！";
                                        } else {
                                            $sql = "insert into travelimagefavor (UID, ImageID) VALUES (?,?)";
                                            $success = $pdoAdapter->insertARow($sql, array($_SESSION['uid'], $imageID));
                                            $message = $success ? "收藏成功" : "收藏失败";
                                        }

                                    } else {
                                        $message = "未登录的用户不可以收藏图片";
                                    }

                                } elseif (isset($_GET['unlike']) && $_GET['unlike'] === 'true') {//判断用户是否给出取消收藏请求
                                    if ($hasLoggedIn) {//如果用户已经登录
                                        if (!userLikedTheImage($_SESSION['uid'], $imageID)) {
                                            $message = "你还没有收藏图片，不能取消收藏";
                                        } else {
                                            $sql = "delete from travelimagefavor where UID=? and ImageID=?";
                                            $success = $pdoAdapter->deleteRows($sql, array($_SESSION['uid'], $imageID));
                                            $message = $success ? "取消收藏成功" : "取消收藏失败";
                                        }

                                    } else {
                                        $message = "未登录的用户不可以取消收藏图片";
                                    }
                                }
                                return $message;
                            }

                            $message = doLogicOfLikeAndUnlike();
                            echo "<h1 class='pure-u-1'>题目:$title</h1>";
                            echo "<div class='pure-u-1'>作者:$username</div>";
                            echo "<div class='pure-u-1'>内容:$content</div>";
                            echo "<div class='pure-u-1'>城市:$cityName</div>";
                            echo "<div class='pure-u-1'>国家:$countryName</div>";
                            echo "<div class='pure-u-1'>描述:$desc</div>";

                            $numOfFavor = getNumOfFavor($_GET['imageID']);
                            echo "<div class='pure-u-1'>收藏量:$numOfFavor</div>";

                            if (!$hasLoggedIn) {//如果用户没有登录，则提示用户登录以后才能收藏
                                echo "<button class='pure-u-1 pure-button pure-button-primary'><a class='pure-u-1' href='login.php'>想要收藏这张照片？登录！</a></button>";
                            } else {
                                if (userLikedTheImage($_SESSION['uid'], $imageID)) {//如果用户收藏了图片，显示取消收藏的按钮
                                    echo "<button class='pure-u-1 pure-button pure-button-primary' onclick=window.open('imageDetail.php?imageID=$imageID&unlike=true')>取消收藏</button>";
                                } else {//反之，显示收藏的按钮
                                    echo "<button class='pure-u-1 pure-button pure-button-primary' onclick=window.open('imageDetail.php?imageID=$imageID&like=true')>收藏</button>";
                                }
                            }
                            if (isset($message)) {//如果有信息，则输出
                                echo "<h2 style='color: red'>$message</h2>";
                            }
                            $pdoAdapter->close();

                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
<footer>
    ZHJ制作 19302010021 本网站由<a href="https://purecss.net">Pure.css</a>驱动
</footer>
</body>
</html>