<?php
include_once "Page.class.php";
include_once "SearchRequest.class.php";
include_once "SearchResult.class.php";
include_once "PageWithPagination.interface.php";
include_once "User.class.php";
include_once "utilities/utilityFunction.php";

class MyPhoto extends Page implements PageWithPagination
{
    private $searchResult;
    private $searchRequest;
    private $queryStringForPagination;
    private $message;
    private $user;
    private $searchTitle;

    function __construct()
    {
        parent::__construct();
        $this->user = new User($_SESSION['uid'], $this->pdoAdapter);
    }


    function deleteImage($deleteID)
    {
        if (!customIsEmpty($deleteID)) {
            $this->message = $this->user->deleteImage($deleteID);
        }

    }


    function printDeleteMessage()
    {
        echo "<div class='pure-u-1' style='color: red;font-size: 120%'>$this->message</div>";
    }


    function searchMyPhoto($uid, $wantedPage, $title)
    {
        if (customIsEmpty($title)) {
            $sql = "select ImageID,Title,Description,PATH from travelimage where UID=?";
            $this->searchRequest = new SearchRequest(
                5,
                $wantedPage,
                $this->pdoAdapter,
                $sql,
                array($uid)
            );
            $this->queryStringForPagination = "?";
        } else {
            $sql = "select ImageID,Title,Description,PATH from travelimage where UID=? and Title regexp ?";
            $this->searchRequest = new SearchRequest(
                5,
                $wantedPage,
                $this->pdoAdapter,
                $sql,
                array($uid, $title)
            );
            $this->queryStringForPagination = "?title=$title";
            $this->searchTitle = $title;
        }

        $this->searchResult = $this->searchRequest->search();
    }

    function printSearchResult()
    {
        $imageInfoList = $this->searchResult->imageInfoList;
        for ($i = 0; $i <= count($imageInfoList) - 1; $i++) {
            $title = $imageInfoList[$i]['Title'];
            $imageID = $imageInfoList[$i]['ImageID'];
            $desc = $imageInfoList[$i]['Description'];
            $path = $imageInfoList[$i]['PATH'];
            echo "<div class='imageCard'>";
            echo "<a href='imageDetail.php?imageID=$imageID'><img src=img/small/$path class='thumbnail' alt=$title></a>";
            echo "<h1>$title</h1>";
            echo "<p>$desc</p>";
            echo "<button class='pure-button pure-button-primary' onclick=window.open('upload_edit.php?action=modify&modifyID=$imageID')>修改</button>";
            $currentPage = $this->searchResult->currentPage;
            if (!customIsEmpty($this->searchTitle)) {
                echo "<button class='pure-button pure-button-primary'
            onclick=if(confirm('你真的要删除这张图片吗？删除后这张图片将永远消失，其他用户也不能访问这张图片')){window.open('myPhoto.php?deleteID=$imageID&page=$currentPage&title=$this->searchTitle')}>删除</button>";

            } else {
                echo "<button class='pure-button pure-button-primary'
            onclick=if(confirm('你真的要删除这张图片吗？删除后这张图片将永远消失，其他用户也不能访问这张图片')){window.open('myPhoto.php?deleteID=$imageID&page=$currentPage')}>删除</button>";

            }
            echo "</div>";
        }

    }

    function printMessageWhileEmpty()
    {
        if (count($this->searchResult->imageInfoList) === 0) {
            echo "<div class='pure-u-1 message'>找不到你上传的任何图片</div>";
        }
    }

    function printPagination()
    {
        // TODO: Implement printPagination() method.
        if ($this->searchResult->needPagination) {
            $currentPage = ($this->searchResult)->currentPage;
            $maxNumOfPage = ($this->searchResult)->maxNumOfPage;

            if ($currentPage > 1) {
                $previousPage = $currentPage - 1;
                $href = "myPhoto.php" . $this->queryStringForPagination . "&page=$previousPage";
                echo "<a href=$href>上一页</a>";
            }
            for ($i = 1; $i <= $maxNumOfPage; $i++) {
                if ($currentPage == $i) {
                    $href = "myPhoto.php" . $this->queryStringForPagination . "&page=$i";
                    echo "<a href=$href style='color: red'>$i</a>";
                } else {
                    $href = "myPhoto.php" . $this->queryStringForPagination . "&page=$i";
                    echo "<a href=$href >$i</a>";
                }
            }
            if ($currentPage < $maxNumOfPage) {
                $nextPage = $currentPage + 1;
                $href = "myPhoto.php" . $this->queryStringForPagination . "&page=$nextPage";
                echo "<a href=$href>下一页</a>";
            }
        }
        // TODO: Implement printPagination() method.
    }

    function printTitleInput($title){
        if(!customIsEmpty($title)){
            echo "<input name='title' type='text' class=\"pure-u-18-24\" value='$title'>";
        }else{
            echo "<input name='title' type='text' class=\"pure-u-18-24\">";
        }
    }
}