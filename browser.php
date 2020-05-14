<?php

include_once "class/Browser.class.php";
$browser = new Browser();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ZHJ的旅游图片分享站-浏览</title>
    <link rel="stylesheet" href="css/library/reset.css">
    <link rel="stylesheet" href="css/library/pure-release-1.0.1/pure-min.css">
    <link rel="stylesheet" href="css/library/pure-release-1.0.1/grids-responsive-min.css">
    <link rel="stylesheet" href="css/universal.css">
    <link rel="stylesheet" href="css/browser.css">
    <script src="js/library/jquery-3.4.1.js"></script>
    <script src="ajax/ajax_erJiLianDong.js"></script>
    <script src="js/dropdownControl.js"></script>
</head>
<body>

<?php
//打印不需要登陆的header
$browser->printHeaderNoNeedLogin();

?>

<div class="pure-g">
    <nav class="pure-u-24-24 pure-u-md-2-5 pure-u-lg-1-3">
        <div class="wrapper pure-g">
            <div class="pure-u-1-6"></div>
            <div class="pure-u-2-3">
                <form class="pure-u-1 pure-form hasShadow" action="browser.php" method="get">
                    <fieldset>
                        <legend>搜索</legend>
                        <div class="wrapper pure-g">
                            <!--                            <input type="text" class="pure-u-2-3" name="title">-->
                            <?php
                            $browser->printTitleInput($_GET['title'])
                            ?>
                            <button class="pure-button pure-button-primary pure-u-1-3">搜索</button>
                        </div>
                    </fieldset>
                </form>
                <div class="list pure-u-1 hasShadow" id="hotContent">
                    <div class="pure-g wrapper">
                        <h1 class="pure-u-1">热门主题</h1>
                        <?php
                        //从数据库中查询条目最多的三条主题（除去其他）并输出在列表中
                        $browser->printHotContents();
                        ?>
                    </div>
                </div>

                <div class="list pure-u-1 hasShadow" id="hotCountry">
                    <div class="pure-g wrapper">
                        <h1 class="pure-u-1">热门国家</h1>
                        <?php
                        //从数据库中读出条目最多的五个国家输出在列表中
                        $browser->printHotCountries();
                        ?>
                    </div>
                </div>
                <div class="list pure-u-1 hasShadow" id="hotCity">
                    <div class="wrapper pure-g">
                        <h1 class="pure-u-1">热门城市</h1>
                        <?php
                        //从数据库中读出五个条目最多的城市，输出在列表中
                        $browser->printHotCities()
                        ?>
                    </div>

                </div>
            </div>
        </div>
    </nav>
    <main class="pure-u-24-24 pure-u-md-3-5 pure-u-lg-2-3">
        <div class="pure-g">
            <div class="pure-u-1-6 trick"></div>
            <div class="pure-u-2-3 pure-u-md-22-24">
                <form class="pure-form hasShadow" action="browser.php" method="get">
                    <fieldset>
                        <legend>过滤</legend>
                        <div class="pure-g">
                            <select class="pure-u-1-4" name='content'>
                                <?php
                                //从数据库中读取出内容的条目，加入到下拉菜单中
                                $browser->printContentOptions($_GET['content']);
                                ?>
                            </select>
                            <select class="pure-u-1-4" id="countrySelect" name="countryISO">
                                <?php
                                //在数据库中读取出全部的国家，输出在下拉菜单中
                                $browser->printCountryOptions($_GET['countryISO']);

                                ?>
                            </select>
                            <select class="pure-u-1-4" id="citySelect" name="cityCode">
                                <!--                               二级联动由js和ajax完成
                                -->
                                <?php
                                $browser->printCityOption($_GET['cityCode']);
                                ?>

                                <option value=''>选择城市</option>
                            </select>
                            <button type="submit" class="pure-u-1-4 pure-button pure-button-primary">过滤</button>
                        </div>
                    </fieldset>
                </form>
                <div id="box" class="pure-g hasShadow" style="text-align: center">
                    <?php
                    if (isset($_GET['title'])) {//如果用户使用标题搜索
                        $browser->searchByTitle($_GET['title'], $_GET['page']);
                        $browser->printSearchResult();
                    } else if (!empty($_GET['content']) || !empty($_GET['countryISO']) || !empty($_GET['cityCode'])) {
                        $browser->searchByOthers($_GET['content'], $_GET['countryISO'], $_GET['cityCode'], $_GET['page']);
                        $browser->printSearchResult();
                    }
                    ?>
                    <div class="pagination pure-u-24-24">
                        <?php
                        $browser->closePDO();
                        $browser->printPagination();
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