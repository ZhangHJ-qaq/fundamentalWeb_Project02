<?php


class SearchResult
{
    public $imageInfoList;
    public $maxNumOfPage;
    public $currentPage;
    public $needPagination;

    function __construct($imageInfoList, $maxNumOfPage, $currentPage, $needPagination)
    {
        $this->imageInfoList = $imageInfoList;
        $this->maxNumOfPage = $maxNumOfPage;
        $this->currentPage = $currentPage;
        $this->needPagination = $needPagination;
    }

}