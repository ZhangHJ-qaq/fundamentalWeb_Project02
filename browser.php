<?php
include_once "utilities/PDOAdapter.php";
include_once "utilities/dbconfig.php";
include_once "utilities/utilityFunction.php";
try {
    $pdoAdapter = new PDOAdapter(HEADER, DBACCOUNT, DBPASSWORD, DBNAME);
} catch (PDOException $exception) {
    header("location:error.php?errorCode=0");
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>浏览</title>
    <link rel="stylesheet" href="css/library/reset.css">
    <link rel="stylesheet" href="css/library/pure-release-1.0.1/pure.css">
    <link rel="stylesheet" href="css/library/pure-release-1.0.1/grids-responsive.css">
    <link rel="stylesheet" href="css/universal.css">
    <link rel="stylesheet" href="css/browser.css">
    <script src="js/library/jquery-3.4.1.js"></script>
    <script src="ajax/ajax_erJiLianDong.js"></script>
</head>
<body>
<header>
    <?php
    session_start();
    if (isset($_SESSION['username'])) {//根据session判断用户有没有登录
        $uname = $_SESSION['username'];
        echo "<span>$uname</span>";
    }
    ?>
    <a href='index.php'>主页</a>
    <a href='browser.php'>浏览</a>
    <a href='search.php'>搜索</a>
    <?php
    if (isset($_SESSION['username'])) {//判断用户是否有登录，有则输出个人中心，没有则输出登录键
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
<div class="pure-g">
    <nav class="pure-u-24-24 pure-u-md-2-5 pure-u-lg-1-3">
        <div class="wrapper pure-g">
            <div class="pure-u-1-6"></div>
            <div class="pure-u-2-3">
                <form class="pure-u-1 pure-form" action="browser.php" method="get">
                    <fieldset>
                        <legend>搜索</legend>
                        <div class="wrapper pure-g">
                            <input type="text" class="pure-u-2-3" name="title">
                            <button class="pure-button pure-button-primary pure-u-1-3">搜索</button>
                        </div>
                    </fieldset>
                </form>
                <div class="list pure-u-1" id="hotContent">
                    <div class="pure-g wrapper">
                        <h1 class="pure-u-1">热门主题</h1>
                        <?php
                        //从数据库中查询条目最多的三条主题（除去其他）并输出在列表中
                        $sql = "select count(travelimage.ContentID),travelimage.ContentID, geocontents.ContentName 
                        from travelimage inner join geocontents 
                        on travelimage.ContentID=geocontents.ContentID 
                        where travelimage.ContentID!=-1 
                        group by travelimage.ContentID  
                        order by count(travelimage.ContentID) desc limit 3";
                        $hotContentList = $pdoAdapter->selectRows($sql);
                        for ($i = 0; $i <= count($hotContentList) - 1; $i++) {
                            $contentName = $hotContentList[$i]['ContentName'];
                            $contentID = $hotContentList[$i]['ContentID'];
                            echo "<a href='browser.php?content=$contentID' class='pure-u-1'>$contentName</a>";
                        }
                        ?>
                    </div>
                </div>

                <div class="list pure-u-1" id="hotCountry">
                    <div class="pure-g wrapper">
                        <h1 class="pure-u-1">热门国家</h1>
                        <?php
                        //从数据库中读出条目最多的五个国家输出在列表中
                        $sql = "select count(travelimage.CountryCodeISO),travelimage.CountryCodeISO,CountryName 
                        from travelimage inner join geocountries on travelimage.CountryCodeISO=geocountries.ISO 
                        group by CountryCodeISO 
                        order by count(CountryCodeISO) desc limit 5";
                        $hotCountryList = $pdoAdapter->selectRows($sql);
                        for ($i = 0; $i <= count($hotCountryList) - 1; $i++) {
                            $countryName = $hotCountryList[$i]['CountryName'];
                            $countryCodeISO = $hotCountryList[$i]['CountryCodeISO'];
                            echo "<a href='browser.php?countryISO=$countryCodeISO' class='pure-u-1'>$countryName</a>";
                        }

                        ?>
                    </div>
                </div>
                <div class="list pure-u-1" id="hotCity">
                    <div class="wrapper pure-g">
                        <h1 class="pure-u-1">热门城市</h1>
                        <?php
                        //从数据库中读出五个条目最多的城市，输出在列表中
                        $sql = "select count(CityCode),CityCode,AsciiName 
                        from travelimage inner join geocities on travelimage.CityCode=geocities.GeoNameID 
                        where CityCode!=-1 group by CityCode 
                        order by count(CityCode) desc limit 5";
                        $hotCityList = $pdoAdapter->selectRows($sql);
                        for ($i = 0; $i <= count($hotCityList) - 1; $i++) {
                            $cityName = $hotCityList[$i]['AsciiName'];
                            $cityCode = $hotCityList[$i]['CityCode'];
                            echo "<a href='browser.php?cityCode=$cityCode' class='pure-u-1'>$cityName</a>";
                        }
                        ?>
                    </div>

                </div>
            </div>
        </div>
    </nav>
    <main class="pure-u-24-24 pure-u-md-3-5 pure-u-lg-2-3">
        <div class="pure-g">
            <div class="pure-u-1-6 trick"></div>
            <div class="pure-u-2-3 pure-u-md-24-24">
                <form class="pure-form" action="browser.php" method="get">
                    <fieldset>
                        <legend>过滤</legend>
                        <div class="pure-g">
                            <select class="pure-u-1-4" name='content'>
                                <?php
                                //从数据库中读取出内容的条目，加入到下拉菜单中
                                echo "<option value='' >选择内容</option>";
                                $contentList = $pdoAdapter->selectRows("select ContentID,ContentName from geocontents order by ContentID desc ");
                                for ($i = 0; $i <= count($contentList) - 1; $i++) {
                                    $contentID = $contentList[$i]['ContentID'];
                                    $contentName = $contentList[$i]['ContentName'];
                                    echo "<option value=$contentID>$contentName</option>";
                                }
                                ?>
                            </select>
                            <select class="pure-u-1-4" id="countrySelect" name="countryISO">
                                <?php
                                //在数据库中读取出全部的国家，输出在下拉菜单中
                                echo "<option value=''>选择国家</option>";
                                $countryList = $pdoAdapter->selectRows("select ISO,CountryName from geocountries where ISO!=-2 order by CountryName asc ");
                                for ($i = 0; $i <= count($countryList) - 1; $i++) {
                                    $ISO = $countryList[$i]['ISO'];
                                    $countryName = $countryList[$i]['CountryName'];
                                    echo "<option value=$ISO>$countryName</option>";
                                }
                                ?>
                            </select>
                            <select class="pure-u-1-4" id="citySelect" name="cityCode">
                                <!--                               二级联动由js和ajax完成
                                -->
                                <option value=''>选择城市</option>
                            </select>
                            <button type="submit" class="pure-u-1-4 pure-button pure-button-primary">过滤</button>
                        </div>
                    </fieldset>
                </form>
                <div id="box" class="pure-g" style="text-align: center">
                    <?php
                    function printImageList($imageList)
                    {//根据从数据库中取得的信息，打印出图片的函数
                        for ($i = 0; $i <= count($imageList) - 1; $i++) {
                            $title = $imageList[$i]['Title'];
                            $imageID = $imageList[$i]['ImageID'];
                            $path = $imageList[$i]['PATH'];
                            echo "<a href='imageDetail.php?imageID=$imageID' class='pure-u-1-2 pure-u-md-1-3 pure-u-lg-1-4'><img src=img/small/$path class='thumbnail' alt=$title></a>";
                        }
                    }

                    if (isset($_GET['title'])) {//如果用户使用标题搜索
                        $title = $_GET['title'];
                        $page = isset($_GET['page']) && isPositiveNumber($_GET['page']) ? $_GET['page'] : 1;//page默认为1，净化输入
                        $sql = "select ImageID,Title,PATH from travelimage WHERE Title REGEXP ?";
                        $count = $pdoAdapter->getRowCount($sql, array($title));//先得到一共有多少符合条件的选项
                        if ($count > 0) {//如果有搜索结果
                            $maxNumOfPage = ceil($count / 16);//得到有几页
                            $page = $page > $maxNumOfPage ? $maxNumOfPage : $page;//如果用户的输入大于最后一页，则自动变成最后一页
                            $offset = ($page - 1) * 16;
                            $sql = "select ImageID,Title,PATH from travelimage where Title REGEXP ? limit 16 offset $offset";
                            $imageList = $pdoAdapter->selectRows($sql, array($title));
                            printImageList($imageList);
                            $needPagination = $maxNumOfPage > 1;//如果页面数大于1，则需要分页
                        }
                    } else if (!empty($_GET['content']) || !empty($_GET['countryISO']) || !empty($_GET['cityCode'])) {
                        $page = isset($_GET['page']) && isPositiveNumber($_GET['page']) ? $_GET['page'] : 1;

                        //下拉框有三个，一共有8种情况，穷举每种情况，生成sql
                        if (!empty($_GET['content']) && !empty($_GET['countryISO']) && !empty($_GET['cityCode'])) {
                            $sql = "select ImageID,Title,PATH from travelimage where ContentID=? and CountryCodeISO=? and CityCode=?";
                            $bindArray = array($_GET['content'], $_GET['countryISO'], $_GET['cityCode']);
                            $queryStringForPagination = "?content=" . $_GET['content'] . "&countryISO=" . $_GET['countryISO'] . "&cityCode=" . $_GET['cityCode'];
                        } else if (empty($_GET['content']) && !empty($_GET['countryISO']) && !empty($_GET['cityCode'])) {
                            $sql = "select ImageID,Title,PATH from travelimage where CountryCodeISO=? and CityCode=?";
                            $bindArray = array($_GET['countryISO'], $_GET['cityCode']);
                            $queryStringForPagination = "?countryISO=" . $_GET['countryISO'] . "&cityCode=" . $_GET['cityCode'];
                        } else if (!empty($_GET['content']) && empty($_GET['countryISO']) && !empty($_GET['cityCode'])) {
                            $sql = "select ImageID,Title,PATH from travelimage where ContentID=? and CityCode=?";
                            $bindArray = array($_GET['content'], $_GET['cityCode']);
                            $queryStringForPagination = "?content=" . $_GET['content'] . "&cityCode=" . $_GET['cityCode'];
                        } else if (!empty($_GET['content']) && !empty($_GET['countryISO']) && empty($_GET['cityCode'])) {
                            $sql = "select ImageID,Title,PATH from travelimage where ContentID=? and CountryCodeISO=?";
                            $bindArray = array($_GET['content'], $_GET['countryISO']);
                            $queryStringForPagination = "?content=" . $_GET['content'] . "&countryISO=" . $_GET['countryISO'];
                        } else if (empty($_GET['content']) && empty($_GET['countryISO']) && !empty($_GET['cityCode'])) {
                            $sql = "select ImageID,Title,PATH from travelimage where  CityCode=?";
                            $bindArray = array($_GET['cityCode']);
                            $queryStringForPagination = "?cityCode=" . $_GET['cityCode'];
                        } else if (empty($_GET['content']) && !empty($_GET['countryISO']) && empty($_GET['cityCode'])) {
                            $sql = "select ImageID,Title,PATH from travelimage where CountryCodeISO=?";
                            $bindArray = array($_GET['countryISO']);
                            $queryStringForPagination = "?countryISO=" . $_GET['countryISO'];
                        } else if (!empty($_GET['content']) && empty($_GET['countryISO']) && empty($_GET['cityCode'])) {
                            $sql = "select ImageID,Title,PATH from travelimage where ContentID=?";
                            $bindArray = array($_GET['content']);
                            $queryStringForPagination = "?content=" . $_GET['content'];
                        }

                        $count = count($pdoAdapter->selectRows($sql, $bindArray));
                        if ($count > 0) {
                            $maxNumOfPage = ceil($count / 16);
                            $page = $page > $maxNumOfPage ? $maxNumOfPage : $page;
                            $offset = ($page - 1) * 16;
                            $sql .= " limit 16 offset $offset";
                            $imageList = $pdoAdapter->selectRows($sql, $bindArray);
                            printImageList($imageList);
                            $needPagination = $maxNumOfPage > 1;
                        }


                    }
                    ?>
                    <div class="pagination pure-u-24-24">
                        <?php
                        $pdoAdapter->close();
                        if ($needPagination && isset($_GET['title'])) {//如果需要分页且用户用标题搜索
                            if ($page > 1) {//如果请求的页面大于1，则输出上一页
                                $previousPage = $page - 1;
                                echo "<a href=browser.php?title=$title&page=$previousPage>上一页</a>";
                            }
                            for ($i = 1; $i <= $maxNumOfPage; $i++) {
                                if ($page == $i) {
                                    echo "<a href='browser.php?title=$title&page=$i' style='color: red'>$i</a>";
                                } else {
                                    echo "<a href='browser.php?title=$title&page=$i'>$i</a>";
                                }
                            }
                            if ($page < $maxNumOfPage) {//如果请求的不是最后一页，则输出下一页
                                $nextPage = $page + 1;
                                echo "<a href='browser.php?title=$title&page=$nextPage'>下一页</a>";
                            }
                        } else if ($needPagination && (!empty($_GET['content']) || !empty($_GET['countryISO']) || !empty($_GET['cityCode']))) {
                            if ($page > 1) {
                                $previousPage = $page - 1;
                                $href = "browser.php" . $queryStringForPagination . "&page=$previousPage";
                                echo "<a href=$href>上一页</a>";
                            }
                            for ($i = 1; $i <= $maxNumOfPage; $i++) {
                                if ($page == $i) {
                                    $href = "browser.php" . $queryStringForPagination . "&page=$i";
                                    echo "<a href=$href style='color: red'>$i</a>";
                                } else {
                                    $href = "browser.php" . $queryStringForPagination . "&page=$i";
                                    echo "<a href=$href >$i</a>";
                                }
                            }
                            if ($page < $maxNumOfPage) {
                                $nextPage = $page + 1;
                                $href = "browser.php" . $queryStringForPagination . "&page=$nextPage";
                                echo "<a href=$href>下一页</a>";
                            }
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