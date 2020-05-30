<?php
include_once $_SERVER['DOCUMENT_ROOT'] . "/class/SearchResult.class.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/utilities/PDOAdapter.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/class/SearchRequest.class.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/utilities/utilityFunction.php";

class SearchRequest//搜索请求
{
    private $numOfElementsOnOnePage;
    private $wantedPage;
    private $pdoAdapter;
    private $sql;
    private $bindArray;

    private $searchResult;

    function __construct($numOfElementsOnOnePage, $wantedPage, PDOAdapter $pdoAdapter, $sql, $bindArray = null)
    {
        $this->numOfElementsOnOnePage = $numOfElementsOnOnePage;
        $this->wantedPage = $wantedPage;
        $this->pdoAdapter = $pdoAdapter;
        $this->sql = $sql;
        $this->bindArray = $bindArray;
    }

    function search()
    {
        $count = $this->pdoAdapter->getRowCount($this->sql, $this->bindArray);//得到一共有多少条目
        $maxNumOfPage = ceil($count / $this->numOfElementsOnOnePage);
        $this->wantedPage = purifyPageInput($this->wantedPage, $maxNumOfPage);//净化用户对于page的输入
        $offset = ($this->wantedPage - 1) * $this->numOfElementsOnOnePage;
        $imageInfoList = $this->pdoAdapter->selectRows($this->sql . " limit $this->numOfElementsOnOnePage offset $offset", $this->bindArray);
        $this->searchResult = new SearchResult($imageInfoList, $maxNumOfPage, $this->wantedPage, $maxNumOfPage >= 2);
        return $this->searchResult;


    }


}