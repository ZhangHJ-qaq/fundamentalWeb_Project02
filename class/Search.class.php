<?php
include_once "Page.class.php";
include_once "SearchRequest.class.php";
include_once "SearchResult.class.php";
include_once "PageWithPagination.interface.php";

class Search extends Page implements PageWithPagination
{
    private $searchRequest;
    private $searchResult;
    private $queryStringForPagination;

    function __construct()
    {
        parent::__construct();
    }


    function printPagination()
    {
        if ($this->searchResult->needPagination) {
            $currentPage = ($this->searchResult)->currentPage;
            $maxNumOfPage = ($this->searchResult)->maxNumOfPage;

            if ($currentPage > 1) {
                $previousPage = $currentPage - 1;
                $href = "search.php" . $this->queryStringForPagination . "&page=$previousPage";
                echo "<a href=$href>上一页</a>";
            }
            for ($i = 1; $i <= $maxNumOfPage; $i++) {
                if ($currentPage == $i) {
                    $href = "search.php" . $this->queryStringForPagination . "&page=$i";
                    echo "<a href=$href style='color: red'>$i</a>";
                } else {
                    $href = "search.php" . $this->queryStringForPagination . "&page=$i";
                    echo "<a href=$href >$i</a>";
                }
            }
            if ($currentPage < $maxNumOfPage) {
                $nextPage = $currentPage + 1;
                $href = "search.php" . $this->queryStringForPagination . "&page=$nextPage";
                echo "<a href=$href>下一页</a>";
            }
        }
        // TODO: Implement printPagination() method.
    }

    function searchByTitle($title, $wantedPage)
    {
        if (!customIsEmpty($title)) {
            $this->searchRequest = new SearchRequest(
                5,
                $wantedPage,
                $this->pdoAdapter,
                "select ImageID,Title,PATH,Description from travelimage where Title REGEXP ?",
                array($title)
            );
            $this->searchResult=$this->searchRequest->search();
            $this->queryStringForPagination="?searchWay=title&titleInput=$title";
        }

    }

    function searchByDesc($desc,$wantedPage){
        if (!customIsEmpty($desc)) {
            $this->searchRequest = new SearchRequest(
                5,
                $wantedPage,
                $this->pdoAdapter,
                "select ImageID,Title,PATH,Description from travelimage where Description REGEXP ?",
                array($desc)
            );
            $this->searchResult=$this->searchRequest->search();
            $this->queryStringForPagination="?searchWay=desc&descInput=$desc";
        }

    }




    function printSearchResult(){
        $imageInfoList=$this->searchResult->imageInfoList;
        for ($i = 0; $i <= count($imageInfoList) - 1; $i++) {
            $imageID = $imageInfoList[$i]['ImageID'];
            $title = $imageInfoList[$i]['Title'];
            $description = $imageInfoList[$i]['Description'];
            $path = $imageInfoList[$i]['PATH'];
            echo "<div class='imageCard'>";
            echo "<a href=imageDetail.php?imageID=$imageID><img src=img/small/$path class='thumbnail' alt=$title></a>";
            echo "<h1>$title</h1>";
            echo "<p>$description</p>";
            echo "</div>";
        }
    }
}