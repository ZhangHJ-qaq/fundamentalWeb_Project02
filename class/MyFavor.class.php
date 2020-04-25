<?php
include_once "Page.class.php";
include_once "SearchRequest.class.php";
include_once "SearchResult.class.php";
include_once "PageWithPagination.interface.php";
include_once "User.class.php";

class MyFavor extends Page implements PageWithPagination
{
    private $searchResult;
    private $searchRequest;
    private $queryStringForPagination;
    private $user;
    private $unlikeMessage;

    function __construct()
    {
        parent::__construct();
        $this->user = new User($_SESSION['uid'], $this->pdoAdapter);
    }


    function printPagination()
    {
        if ($this->searchResult->needPagination) {
            $currentPage = ($this->searchResult)->currentPage;
            $maxNumOfPage = ($this->searchResult)->maxNumOfPage;

            if ($currentPage > 1) {
                $previousPage = $currentPage - 1;
                $href = "myFavor.php" . $this->queryStringForPagination . "&page=$previousPage";
                echo "<a href=$href>上一页</a>";
            }
            for ($i = 1; $i <= $maxNumOfPage; $i++) {
                if ($currentPage == $i) {
                    $href = "myFavor.php" . $this->queryStringForPagination . "&page=$i";
                    echo "<a href=$href style='color: red'>$i</a>";
                } else {
                    $href = "myFavor.php" . $this->queryStringForPagination . "&page=$i";
                    echo "<a href=$href >$i</a>";
                }
            }
            if ($currentPage < $maxNumOfPage) {
                $nextPage = $currentPage + 1;
                $href = "myFavor.php" . $this->queryStringForPagination . "&page=$nextPage";
                echo "<a href=$href>下一页</a>";
            }
        }
        // TODO: Implement printPagination() method.
    }


    function printUnlikeInfo()
    {
        if(!empty($this->unlikeMessage)){
            echo "<div class='pure-u-1' style='color: red'>$this->unlikeMessage</div>";
        }

    }

    function unlike($unlikeImageID)
    {
        $this->unlikeMessage = '';
        if (isset($unlikeImageID)) {
            $this->unlikeMessage = $this->user->unlikeImage($unlikeImageID);
        }
    }

    function searchFavoredImage($wantedPage, $uid)
    {
        $sql = "select travelimage.ImageID,Title,Description,PATH 
                    from travelimage inner join travelimagefavor on travelimage.ImageID=travelimagefavor.ImageID 
                    where travelimagefavor.UID=? ";
        $this->searchRequest = new SearchRequest(
            5,
            $wantedPage,
            $this->pdoAdapter,
            $sql,
            array($uid)
        );
        $this->searchResult = $this->searchRequest->search();
        $this->queryStringForPagination = "?";
    }

    function printSearchResult()
    {
        $imageInfoList = $this->searchResult->imageInfoList;
        $currentPage = $this->searchResult->currentPage;
        for ($i = 0; $i <= count($imageInfoList) - 1; $i++) {
            $imageID = $imageInfoList[$i]['ImageID'];
            $path = $imageInfoList[$i]['PATH'];
            $title = $imageInfoList[$i]['Title'];
            $desc = $imageInfoList[$i]['Description'];
            echo "<div class='imageCard'>";
            echo "<a href=imageDetail.php?imageID=$imageID><img src=img/small/$path alt=$title class='thumbnail'></a>";
            echo "<h1>$title</h1>";
            echo "<p>$desc</p>";
            echo "<button class='pure-button-primary pure-button'><a href='myFavor.php?unlikeImageId=$imageID&page=$currentPage'>取消收藏</a></button>";
            echo "</div>";
        }
    }

    function printMessageWhileEmpty()
    {
        if (count($this->searchResult->imageInfoList) === 0) {
            echo "<div class='pure-u-1'>找不到你收藏过的任何图片</div>";
        }
    }
}