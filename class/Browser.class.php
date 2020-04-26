<?php
include_once "Page.class.php";
include_once "SearchRequest.class.php";
include_once "SearchResult.class.php";
include_once "PageWithPagination.interface.php";

class Browser extends Page implements PageWithPagination
{
    private $searchRequest;
    private $searchResult;
    private $queryStringForPagination;

    function __construct()
    {
        parent::__construct();
    }

    function printHotContents()
    {
        $sql = "select count(travelimage.ContentID),travelimage.ContentID, geocontents.ContentName 
                        from travelimage inner join geocontents 
                        on travelimage.ContentID=geocontents.ContentID 
                        where travelimage.ContentID!=-1 
                        group by travelimage.ContentID  
                        order by count(travelimage.ContentID) desc limit 3";
        $hotContentList = $this->pdoAdapter->selectRows($sql);
        for ($i = 0; $i <= count($hotContentList) - 1; $i++) {
            $contentName = $hotContentList[$i]['ContentName'];
            $contentID = $hotContentList[$i]['ContentID'];
            echo "<a href='browser.php?content=$contentID' class='pure-u-1'>$contentName</a>";
        }
    }//打印热门内容

    function printHotCountries()
    {
        $sql = "select count(travelimage.CountryCodeISO),travelimage.CountryCodeISO,CountryName 
                        from travelimage inner join geocountries on travelimage.CountryCodeISO=geocountries.ISO 
                        group by CountryCodeISO 
                        order by count(CountryCodeISO) desc limit 5";
        $hotCountryList = $this->pdoAdapter->selectRows($sql);
        for ($i = 0; $i <= count($hotCountryList) - 1; $i++) {
            $countryName = $hotCountryList[$i]['CountryName'];
            $countryCodeISO = $hotCountryList[$i]['CountryCodeISO'];
            echo "<a href='browser.php?countryISO=$countryCodeISO' class='pure-u-1'>$countryName</a>";
        }
    }//打印热门国家

    function printHotCities()
    {
        $sql = "select count(CityCode),CityCode,AsciiName 
                        from travelimage inner join geocities on travelimage.CityCode=geocities.GeoNameID 
                        where CityCode!=-1 group by CityCode 
                        order by count(CityCode) desc limit 5";
        $hotCityList = $this->pdoAdapter->selectRows($sql);
        for ($i = 0; $i <= count($hotCityList) - 1; $i++) {
            $cityName = $hotCityList[$i]['AsciiName'];
            $cityCode = $hotCityList[$i]['CityCode'];
            echo "<a href='browser.php?cityCode=$cityCode' class='pure-u-1'>$cityName</a>";
        }
    }//打印热门城市


    function searchByTitle($title, $wantedPage)//以标题进行搜索
    {
        if (!customIsEmpty($title)) {
            $this->queryStringForPagination = "?title=$title";
            $this->searchRequest = new SearchRequest(
                12,
                $wantedPage,
                $this->pdoAdapter,
                "select Title,PATH,ImageID from travelimage where Title REGEXP ?",
                array($title)
            );
            $this->searchResult = $this->searchRequest->search();
        }
    }

    function searchByOthers($content, $countryISO, $cityCode, $wantedPage)
    {
        if (!customIsEmpty($content) && !customIsEmpty($countryISO) && !customIsEmpty($cityCode)) {
            $sql = "select ImageID,Title,PATH from travelimage where ContentID=? and CountryCodeISO=? and CityCode=?";
            $bindArray = array($content, $countryISO, $cityCode);
            $this->queryStringForPagination = "?content=" . $content . "&countryISO=" . $countryISO . "&cityCode=" . $cityCode;
        } else if (empty($content) && !empty($countryISO) && !empty($cityCode)) {
            $sql = "select ImageID,Title,PATH from travelimage where CountryCodeISO=? and CityCode=?";
            $bindArray = array($countryISO, $cityCode);
            $this->queryStringForPagination = "?countryISO=" . $countryISO . "&cityCode=" . $cityCode;
        } else if (!empty($content) && empty($countryISO) && !empty($cityCode)) {
            $sql = "select ImageID,Title,PATH from travelimage where ContentID=? and CityCode=?";
            $bindArray = array($content, $cityCode);
            $this->queryStringForPagination = "?content=" . $content . "&cityCode=" . $cityCode;
        } else if (!empty($content) && !empty($countryISO) && empty($cityCode)) {
            $sql = "select ImageID,Title,PATH from travelimage where ContentID=? and CountryCodeISO=?";
            $bindArray = array($content, $countryISO);
            $this->queryStringForPagination = "?content=" . $content . "&countryISO=" . $countryISO;
        } else if (empty($content) && empty($countryISO) && !empty($cityCode)) {
            $sql = "select ImageID,Title,PATH from travelimage where  CityCode=?";
            $bindArray = array($cityCode);
            $this->queryStringForPagination = "?cityCode=" . $cityCode;
        } else if (empty($content) && !empty($countryISO) && empty($cityCode)) {
            $sql = "select ImageID,Title,PATH from travelimage where CountryCodeISO=?";
            $bindArray = array($countryISO);
            $this->queryStringForPagination = "?countryISO=" . $countryISO;
        } else if (!empty($content) && empty($countryISO) && empty($cityCode)) {
            $sql = "select ImageID,Title,PATH from travelimage where ContentID=?";
            $bindArray = array($content);
            $this->queryStringForPagination = "?content=" . $content;
        }

        $this->searchRequest = new SearchRequest(
            12,
            $wantedPage,
            $this->pdoAdapter,
            $sql,
            $bindArray
        );
        $this->searchResult = $this->searchRequest->search();

    }//另一种联动搜索方式


    function printSearchResult()
    {
        $imageInfoList = $this->searchResult->imageInfoList;
        if (count($imageInfoList) !== 0) {
            for ($i = 0; $i <= count($imageInfoList) - 1; $i++) {
                $title = $imageInfoList[$i]['Title'];
                $imageID = $imageInfoList[$i]['ImageID'];
                $path = $imageInfoList[$i]['PATH'];
                echo "<a href='imageDetail.php?imageID=$imageID' class='pure-u-1-2 pure-u-md-1-3 pure-u-lg-1-4'><img src=img/small/$path class='thumbnail' alt=$title></a>";
            }
        } else {
            echo "<div class='pure-u-1'>没有找到任何结果</div>";
        }
    }//打印搜索结果

    function printPagination()//打印分页
    {
        if (($this->searchResult !== null) && $this->searchResult->needPagination) {
            $currentPage = ($this->searchResult)->currentPage;
            $maxNumOfPage = ($this->searchResult)->maxNumOfPage;

            if ($currentPage > 1) {
                $previousPage = $currentPage - 1;
                $href = "browser.php" . $this->queryStringForPagination . "&page=$previousPage";
                echo "<a href=$href>上一页</a>";
            }
            for ($i = 1; $i <= $maxNumOfPage; $i++) {
                if ($currentPage == $i) {
                    $href = "browser.php" . $this->queryStringForPagination . "&page=$i";
                    echo "<a href=$href style='color: red'>$i</a>";
                } else {
                    $href = "browser.php" . $this->queryStringForPagination . "&page=$i";
                    echo "<a href=$href >$i</a>";
                }
            }
            if ($currentPage < $maxNumOfPage) {
                $nextPage = $currentPage + 1;
                $href = "browser.php" . $this->queryStringForPagination . "&page=$nextPage";
                echo "<a href=$href>下一页</a>";
            }
        }
    }


}