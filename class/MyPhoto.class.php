<?php
include_once $_SERVER['DOCUMENT_ROOT'] . "/class/Page.class.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/class/SearchRequest.class.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/class/SearchResult.class.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/class/PageWithPagination.interface.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/class/User.class.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/utilities/utilityFunction.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/utilities/htmlpurifier-4.12.0/library/HTMLPurifier.auto.php";

class MyPhoto extends Page implements PageWithPagination
{
    private $searchResult;
    private $searchRequest;
    private $queryStringForPagination;
    private $message;
    private $searchTitle;
    private $htmlPurifier;

    function __construct()
    {
        parent::__construct();
        $this->htmlPurifier = new HTMLPurifier();
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


    function searchMyPhoto($wantedPage, $title)
    {
        if (customIsEmpty($title)) {
            $sql = "select ImageID,Title,Description,PATH from travelimage where UID=?";
            $this->searchRequest = new SearchRequest(
                5,
                $wantedPage,
                $this->pdoAdapter,
                $sql,
                array($this->user->getUid())
            );
            $this->queryStringForPagination = "?";
        } else {
            $sql = "select ImageID,Title,Description,PATH from travelimage where UID=? and Title regexp ?";
            $this->searchRequest = new SearchRequest(
                5,
                $wantedPage,
                $this->pdoAdapter,
                $sql,
                array($this->user->getUid(), $title)
            );
            $this->queryStringForPagination = "?title=$title";
            $this->searchTitle = $title;
        }

        $this->searchResult = $this->searchRequest->search();
    }

    function printSearchResult()
    {
        $imageInfoList = $this->searchResult->imageInfoList;
        if ($imageInfoList !== null && count($imageInfoList) !== 0) {
            for ($i = 0; $i <= count($imageInfoList) - 1; $i++) {
                $title = htmlspecialchars($imageInfoList[$i]['Title'], ENT_QUOTES);
                $imageID = htmlspecialchars($imageInfoList[$i]['ImageID'], ENT_QUOTES);
                $desc = htmlspecialchars($imageInfoList[$i]['Description'], ENT_QUOTES);
                $path = htmlspecialchars($imageInfoList[$i]['PATH'], ENT_QUOTES);
                echo "<div class='imageCard'>";
                echo "<a href='imageDetail.php?imageID=$imageID'><img src=img/small/$path class='thumbnail' alt=$title></a>";
                echo "<h1>$title</h1>";

                //描述最多显示100个字符
                $desc = mb_substr($desc, 0, 100);
                echo "<p>$desc</p>";
                echo "<button class='pure-button pure-button-primary' onclick=window.open('upload_edit.php?control=modify&modifyID=$imageID')>修改</button>";
                $currentPage = $this->searchResult->currentPage;
                if (!customIsEmpty($this->searchTitle)) {
                    echo "<button class='pure-button pure-button-primary'
            onclick=if(confirm('你真的要删除这张图片吗？删除后这张图片将永远消失，其他用户也不能访问这张图片')){window.open('myPhoto.php?deleteID=$imageID&page=$currentPage&title=$this->searchTitle');window.close()}>删除</button>";

                } else {
                    echo "<button class='pure-button pure-button-primary'
            onclick=if(confirm('你真的要删除这张图片吗？删除后这张图片将永远消失，其他用户也不能访问这张图片')){window.open('myPhoto.php?deleteID=$imageID&page=$currentPage');window.close()}>删除</button>";

                }
                echo "</div>";
            }
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
                echo "<a href='$href'>上一页</a>";
            }

            //得到页码打印的起始页
            $startPage = max(1, $currentPage - 5);//该页前面显示的页码数目不超过5页

            $distance1 = $currentPage - $startPage;//该页前面显示的页码数目
            $distance2 = 10 - 1 - $distance1;//该页后面显示的最大页码数目

            //得到页码打印的中止页
            $endPage = min($currentPage + $distance2, $maxNumOfPage);

            for ($i = $startPage; $i <= $endPage; $i++) {
                if ($currentPage == $i) {
                    $href = "myPhoto.php" . $this->queryStringForPagination . "&page=$i";
                    echo "<a href='$href' style='color: red'>$i</a>";
                } else {
                    $href = "myPhoto.php" . $this->queryStringForPagination . "&page=$i";
                    echo "<a href='$href' >$i</a>";
                }
            }
            if ($currentPage < $maxNumOfPage) {
                $nextPage = $currentPage + 1;
                $href = "myPhoto.php" . $this->queryStringForPagination . "&page=$nextPage";
                echo "<a href='$href'>下一页</a>";
            }
            echo "<span>共{$maxNumOfPage}页</span>";

        }
        // TODO: Implement printPagination() method.
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