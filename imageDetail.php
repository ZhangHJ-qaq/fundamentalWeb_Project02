<?php
session_start();
include_once $_SERVER['DOCUMENT_ROOT'] . "/class/ImageDetail.class.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/class/User.class.php";
$imageDetail = new ImageDetail();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>ZHJ的旅游图片分享站-图片详情</title>
<link rel="stylesheet" href="css/library/reset.css">
<link rel="stylesheet" href="css/library/pure-release-1.0.1/pure-min.css">
<link rel="stylesheet" href="css/library/pure-release-1.0.1/grids-responsive-min.css">
<link rel="stylesheet" href="css/universal.css">
<link rel="stylesheet" href="css/imageDetail.css">
<script src="js/library/jquery-3.4.1.js"></script>
<script src="js/dropdownControl.js"></script>
</head>
<body>
<?php
$imageDetail->printHeaderNoNeedLogin();
?>
<div class="wrapper pure-g">
    <div class="pure-u-2-24"></div>
    <main class="pure-u-20-24 hasShadow" id="panel">
        <div class="wrapper pure-g">
            <div class="pure-u-1-2">
                <div class="wrapper pure-g" id="imageWrapper">
                <?php
                //收藏/取消收藏的逻辑（请求是否合法在user类中完成）
                $imageDetail->likeUnlike($_GET['imageID'], $_GET['action']);

                $imageDetail->searchImage($_GET['imageID']);
                $imageDetail->printBigImage();
                ?>
                </div>
            </div>
            <div class="pure-u-1-2">
                <div class="wrapper pure-g">
                    <div class="pure-u-1-6"></div>
                    <div class="pure-u-2-3" id="infoArea">
                        <?php

                        $imageDetail->printImageInfo();
                        $imageDetail->printButtonAndMessage();


                        ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

</body>
</html>