<?php
include_once "Page.class.php";

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
            $imageID = $imageInfoList[$i]['ImageID'];
            $title = $imageInfoList[$i]['Title'];
            $description = $imageInfoList[$i]['Description'];
            $path = $imageInfoList[$i]['PATH'];
            echo "<div class='card pure-u-1-1 pure-u-md-1-2 pure-u-lg-1-3'>";
            echo "<a href=imageDetail.php?imageID=$imageID><img src=img/small/$path alt=$title class='thumbnail pure-u-1-2'></a>";
            echo "<h1>$title</h1>";
            echo "<p>$description</p>";
            echo "</div>";


        }


    }
    function printHugeImage()
    {
        $imageInfo = $this->pdoAdapter->selectRows("select Title,PATH,ImageID from travelimage order by rand() limit 1 ");

        //->selectRows();
        $imageID = $imageInfo[0]['ImageID'];
        $title = $imageInfo[0]['Title'];
        $path = $imageInfo[0]['PATH'];
        echo "<a class='pure-u-1 hasShadow' href=imageDetail.php?imageID=$imageID>";
        echo "<img src=img/large/$path alt='$title' class='pure-u-1'>";
        echo "</a>";

    }//打印大图
}