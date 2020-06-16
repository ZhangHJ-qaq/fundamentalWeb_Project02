<?php
include_once $_SERVER['DOCUMENT_ROOT'] . "/class/Page.class.php";

class Index extends Page
{

    function __construct()
    {
        parent::__construct();

        //do nothing
    }

    function printSixMostPopularImage()//打印六个最流行的图片
    {
        $sql = "select count(travelimagefavor.ImageID),Title,Description,PATH,travelimage.ImageID from travelimagefavor inner join travelimage on travelimage.ImageID=travelimagefavor.ImageID group by travelimagefavor.ImageID,Title,PATH,Description,travelimagefavor.ImageID order by count(travelimagefavor.ImageID) desc limit 6";
        $imageInfoList = $this->pdoAdapter->selectRows($sql);
        $this->pdoAdapter->close();
        for ($i = 0; $i <= count($imageInfoList) - 1; $i++) {
            $imageID = htmlspecialchars($imageInfoList[$i]['ImageID'], ENT_QUOTES);
            $title = htmlspecialchars($imageInfoList[$i]['Title'], ENT_QUOTES);
            $description = htmlspecialchars($imageInfoList[$i]['Description'], ENT_QUOTES);
            $path = htmlspecialchars($imageInfoList[$i]['PATH'], ENT_QUOTES);
            echo "<div class='card pure-u-1-1 pure-u-md-1-2 pure-u-lg-1-3'>";
            echo "<a href=imageDetail.php?imageID=$imageID><img src=img/small/$path alt=$title class='thumbnail pure-u-1-2'></a>";
            echo "<h1>$title</h1>";

            //首页描述最多显示100个字符
            $description = mb_substr($description, 0, 100);
            echo "<p>$description</p>";
            echo "</div>";


        }


    }

    function printHugeImage()
    {
        $imageInfo = $this->pdoAdapter->selectRows("select Title,PATH,ImageID from travelimage order by rand() limit 1 ");

        //->selectRows();
        $imageID = htmlspecialchars($imageInfo[0]['ImageID'], ENT_QUOTES);
        $title = htmlspecialchars($imageInfo[0]['Title'], ENT_QUOTES);
        $path = htmlspecialchars($imageInfo[0]['PATH'], ENT_QUOTES);
        echo "<a class='pure-u-1 hasShadow' href=imageDetail.php?imageID=$imageID>";
        echo "<img src=img/medium/$path alt='$title' class='pure-u-1'>";
        echo "</a>";

    }//打印大图
}