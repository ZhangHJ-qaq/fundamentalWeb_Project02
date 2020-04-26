<?php
include_once "utilities/PDOAdapter.php";
include_once "utilities/dbconfig.php";
include_once "utilities/utilityFunction.php";
include_once "class/Search.class.php";
$search = new Search();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>喵喵喵旅游图片分享站-搜索</title>
    <link rel="stylesheet" href="css/library/reset.css">
    <link rel="stylesheet" href="css/library/pure-release-1.0.1/pure.css">
    <link rel="stylesheet" href="css/library/pure-release-1.0.1/grids-responsive.css">
    <link rel="stylesheet" href="css/universal.css">
    <link rel="stylesheet" href="css/search.css">
    <script src="js/library/jquery-3.4.1.js"></script>
    <script src="js/dropdownControl.js"></script>
</head>
<body>

<?php
$search->printHeaderNoNeedLogin();
?>

<div class="wrapper pure-g">
    <div class="pure-u-1-6"></div>
    <main class="pure-u-2-3">
        <form class="pure-form hasShadow" method="get" action="search.php" id="searchForm">
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
        <div id="searchResultBox" class="hasShadow">
            <?php
            $searchWay = $_GET['searchWay'];//得到用户的搜搜方法
            $titleInput = $_GET['titleInput'];
            $descInput = $_GET['descInput'];
            $hasSearched = false;
            if ($searchWay === 'title') {//如果用户用标题搜索
                $search->searchByTitle($titleInput, $_GET['page']);
                $search->printSearchResult();

            } else if ($searchWay === 'desc') {//如果用户用内容搜索
                $search->searchByDesc($descInput, $_GET['page']);
                $search->printSearchResult();
            } else {
                echo "<div class='pure-u-1'>你还没有搜索任何内容</div>";
            }
            ?>
        </div>
        <div class="pagination">
            <?php
            $search->printPagination();
            ?>
        </div>
    </main>
</div>
<footer>
    ZHJ制作 19302010021 本网站由<a href="https://purecss.net">Pure.css</a>驱动
</footer>

</body>
</html>