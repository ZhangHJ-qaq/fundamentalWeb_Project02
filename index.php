<?php
include_once "class/Index.class.php";
$index = new Index();

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
    <script src="js/dropdownControl.js"></script>
</head>
<body>

<?php
$index->printHeaderNoNeedLogin();
?>

<main class="pure-g">
    <div class="pure-u-2-24"></div>
    <div class="pure-u-20-24">
        <?php
        $index->printHugeImage();
        ?>

        <div id="wrapper" class="pure-u-24-24">
            <div id="box" class=" pure-g">
                <?php
                $index->printSixMostPopularImage();
                $index->closePDO();
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
