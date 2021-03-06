<?php
include_once $_SERVER['DOCUMENT_ROOT'] . "/class/Page.class.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/class/SearchRequest.class.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/class/SearchResult.class.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/class/PageWithPagination.interface.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/utilities/htmlpurifier-4.12.0/library/HTMLPurifier.auto.php";

class Search extends Page implements PageWithPagination
{
    private $searchRequest;
    private $searchResult;
    private $queryStringForPagination;
    private $htmlPurifier;

    function __construct()
    {
        parent::__construct();
        $this->htmlPurifier = new HTMLPurifier();
    }


    function printPagination()//打印分页
    {
        if ($this->searchResult !== null && $this->searchResult->needPagination) {
            $currentPage = ($this->searchResult)->currentPage;
            $maxNumOfPage = ($this->searchResult)->maxNumOfPage;

            if ($currentPage > 1) {
                $previousPage = $currentPage - 1;
                $href = "search.php" . $this->queryStringForPagination . "&page=$previousPage";
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
                    $href = "search.php" . $this->queryStringForPagination . "&page=$i";
                    echo "<a href='$href' style='color: red'>$i</a>";
                } else {
                    $href = "search.php" . $this->queryStringForPagination . "&page=$i";
                    echo "<a href='$href' >$i</a>";
                }
            }
            if ($currentPage < $maxNumOfPage) {
                $nextPage = $currentPage + 1;
                $href = "search.php" . $this->queryStringForPagination . "&page=$nextPage";
                echo "<a href='$href'>下一页</a>";
            }
            echo "<span>共{$maxNumOfPage}页</span>";

        }
        // TODO: Implement printPagination() method.
    }

    function searchByTitle($title, $wantedPage)//按标题搜索
    {
        if (!customIsEmpty($title)) {
            $this->searchRequest = new SearchRequest(
                5,
                $wantedPage,
                $this->pdoAdapter,
                "select ImageID,Title,PATH,Description from travelimage where Title REGEXP ?",
                array($title)
            );
            $this->searchResult = $this->searchRequest->search();
            $this->queryStringForPagination = "?searchWay=title&titleInput=$title";
        }

    }

    function searchByDesc($desc, $wantedPage)
    {//按描述搜索
        if (!customIsEmpty($desc)) {
            $this->searchRequest = new SearchRequest(
                5,
                $wantedPage,
                $this->pdoAdapter,
                "select ImageID,Title,PATH,Description from travelimage where Description REGEXP ?",
                array($desc)
            );
            $this->searchResult = $this->searchRequest->search();
            $this->queryStringForPagination = "?searchWay=desc&descInput=$desc";
        }

    }


    function printSearchResult()
    {//打印搜索结果
        $imageInfoList = $this->searchResult->imageInfoList;
        if ($imageInfoList !== null && count($imageInfoList) !== 0) {
            for ($i = 0; $i <= count($imageInfoList) - 1; $i++) {
                $imageID = htmlspecialchars($imageInfoList[$i]['ImageID'], ENT_QUOTES);
                $title = htmlspecialchars($imageInfoList[$i]['Title'], ENT_QUOTES);
                $description = htmlspecialchars($imageInfoList[$i]['Description'], ENT_QUOTES);
                $path = htmlspecialchars($imageInfoList[$i]['PATH'], ENT_QUOTES);
                echo "<div class='imageCard'>";
                echo "<a href=imageDetail.php?imageID=$imageID><img src=img/small/$path class='thumbnail' alt=$title></a>";
                echo "<h1>$title</h1>";

                //搜索页描述最多显示100个字符
                $description = mb_substr($description, 0, 100);
                echo "<p>$description</p>";
                echo "</div>";
            }
        } else {
            echo "<div>没有搜索到任何内容</div>";
        }

    }

    function printSearchByTitle()
    {
        if ($_GET['searchWay'] === 'title') {
            echo "<input type='radio' name='searchWay' value='title' id='titleSearchRadio' checked>";
        } else {
            echo "<input type='radio' name='searchWay' value='title' id='titleSearchRadio'>";
        }
        echo "<label>按标题搜索</label>";
        if ($_GET['searchWay'] === 'title') {
            $titleInput = $_GET['titleInput'];
            echo "<input type='text' name='titleInput' id='titleInput' value='$titleInput'>";
        } else {
            echo "<input type='text' name='titleInput' id='titleInput'>";
        }

    }

    function printSearchByDesc()
    {
        if ($_GET['searchWay'] === 'desc') {
            echo "<input type='radio' name='searchWay' value='desc' id='descSearchRadio' checked>";
        } else {
            echo "<input type='radio' name='searchWay' value='desc' id='descSearchRadio'>";
        }
        echo "<label>按内容搜索</label>";
        if ($_GET['searchWay'] === 'desc') {
            $descInput = $_GET['descInput'];
            echo "<input type='text' name='descInput' id='descInput' value='$descInput'>";
        } else {
            echo "<input type='text' name='descInput' id='descInput'>";
        }
    }

    function purifyInput()
    {
        $_GET['titleInput'] = $this->htmlPurifier->purify($_GET['titleInput']);
        $_GET['descInput'] = $this->htmlPurifier->purify($_GET['descInput']);

    }
}