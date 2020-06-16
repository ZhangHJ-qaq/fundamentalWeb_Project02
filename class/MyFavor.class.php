<?php
include_once $_SERVER['DOCUMENT_ROOT'] . "/class/Page.class.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/class/SearchRequest.class.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/class/SearchResult.class.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/class/PageWithPagination.interface.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/class/User.class.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/utilities/utilityFunction.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/utilities/htmlpurifier-4.12.0/library/HTMLPurifier.auto.php";

class MyFavor extends Page implements PageWithPagination
{
    private $searchResult;
    private $searchRequest;
    private $queryStringForPagination;
    private $unlikeMessage;
    private $searchTitle;
    private $htmlPurifier;


    function __construct()
    {
        parent::__construct();
        $this->htmlPurifier = new HTMLPurifier();
    }


    function printPagination()
    {
        if ($this->searchResult->needPagination) {
            $currentPage = ($this->searchResult)->currentPage;
            $maxNumOfPage = ($this->searchResult)->maxNumOfPage;

            //得到页码打印的起始页
            $startPage = max(1, $currentPage - 5);//该页前面显示的页码数目不超过5页

            $distance1 = $currentPage - $startPage;//该页前面显示的页码数目
            $distance2 = 10 - 1 - $distance1;//该页后面显示的最大页码数目

            //得到页码打印的中止页
            $endPage = min($currentPage + $distance2, $maxNumOfPage);

            if ($currentPage > 1) {
                $previousPage = $currentPage - 1;
                $href = "myFavor.php" . $this->queryStringForPagination . "&page=$previousPage";
                echo "<a href='$href'>上一页</a>";
            }
            for ($i = $startPage; $i <= $endPage; $i++) {
                if ($currentPage == $i) {
                    $href = "myFavor.php" . $this->queryStringForPagination . "&page=$i";
                    echo "<a href='$href' style='color: red'>$i</a>";
                } else {
                    $href = "myFavor.php" . $this->queryStringForPagination . "&page=$i";
                    echo "<a href='$href' >$i</a>";
                }
            }
            if ($currentPage < $maxNumOfPage) {
                $nextPage = $currentPage + 1;
                $href = "myFavor.php" . $this->queryStringForPagination . "&page=$nextPage";
                echo "<a href='$href'>下一页</a>";
            }
            echo "<span>共{$maxNumOfPage}页</span>";

        }
        // TODO: Implement printPagination() method.
    }//打印分页


    function printUnlikeInfo()
    {//打印取消收藏是否成功的信息
        if (!empty($this->unlikeMessage)) {
            echo "<div class='pure-u-1' style='color: red'>$this->unlikeMessage</div>";
        }

    }

    function unlike($unlikeImageID)//取消收藏的逻辑
    {
        $this->unlikeMessage = '';
        if (isset($unlikeImageID)) {
            $this->unlikeMessage = $this->user->unlikeImage($unlikeImageID);
        }
    }

    function searchFavoredImage($wantedPage, $title)//搜索我收藏的照片
    {
        if (!customIsEmpty($title)) {
            $sql = "select travelimage.ImageID,Title,Description,PATH 
                    from travelimage inner join travelimagefavor on travelimage.ImageID=travelimagefavor.ImageID 
                    where travelimagefavor.UID=? and travelimage.Title regexp ?";
            $this->searchRequest = new SearchRequest(
                5,
                $wantedPage,
                $this->pdoAdapter,
                $sql,
                array($this->user->getUid(), $title)
            );
            $this->queryStringForPagination = "?title=$title";
            $this->searchTitle = $title;

        } else {
            $sql = "select travelimage.ImageID,Title,Description,PATH 
                    from travelimage inner join travelimagefavor on travelimage.ImageID=travelimagefavor.ImageID 
                    where travelimagefavor.UID=? ";
            $this->searchRequest = new SearchRequest(
                5,
                $wantedPage,
                $this->pdoAdapter,
                $sql,
                array($this->user->getUid())
            );
            $this->queryStringForPagination = "?";

        }
        $this->searchResult = $this->searchRequest->search();
    }

    function printSearchResult()
    {
        $imageInfoList = $this->searchResult->imageInfoList;
        $currentPage = $this->searchResult->currentPage;
        for ($i = 0; $i <= count($imageInfoList) - 1; $i++) {
            $imageID = htmlspecialchars($imageInfoList[$i]['ImageID'], ENT_QUOTES);
            $path = htmlspecialchars($imageInfoList[$i]['PATH'], ENT_QUOTES);
            $title = htmlspecialchars($imageInfoList[$i]['Title'], ENT_QUOTES);
            $desc = htmlspecialchars($imageInfoList[$i]['Description'], ENT_QUOTES);
            echo "<div class='imageCard'>";
            echo "<a href=imageDetail.php?imageID=$imageID><img src=img/small/$path alt=$title class='thumbnail'></a>";
            echo "<h1>$title</h1>";

            //描述最多显示100个字符
            $desc = mb_substr($desc, 0, 100);
            echo "<p>$desc</p>";

            if (!customIsEmpty($this->searchTitle)) {
                echo "<button class='pure-button-primary pure-button'><a href='myFavor.php?unlikeImageId=$imageID&page=$currentPage&title=$this->searchTitle'>取消收藏</a></button>";
            } else {
                echo "<button class='pure-button-primary pure-button'><a href='myFavor.php?unlikeImageId=$imageID&page=$currentPage'>取消收藏</a></button>";

            }
            echo "</div>";
        }
    }

    function printMessageWhileEmpty()
    {
        if (count($this->searchResult->imageInfoList) === 0) {
            echo "<div class='pure-u-1'>找不到你收藏过的任何图片</div>";
        }
    }

    function printTitleInput($title)
    {
        if (!customIsEmpty($title)) {
            echo "<input name='title' type='text' class=\"pure-u-18-24\" value='$title'>";
        } else {
            echo "<input name='title' type='text' class=\"pure-u-18-24\">";
        }
    }

    function purifyTitleInput()
    {
        if (!customIsEmpty($_GET['title'])) {
            $_GET['title'] = $this->htmlPurifier->purify($_GET['title']);
        }
    }
}