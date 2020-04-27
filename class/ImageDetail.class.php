<?php
include_once "Page.class.php";
include_once "SearchRequest.class.php";
include_once "SearchResult.class.php";
include_once "PageWithPagination.interface.php";

class ImageDetail extends Page
{
    private $message;
    private $imageInfo;
    private $user;
    private $getImage = false;

    function __construct()
    {
        parent::__construct();
        $this->user = new User($_SESSION['uid'], $this->pdoAdapter);
    }


    function likeUnlike($imageID, $action, $hasLoggedIn)
    {
        if ($this->imageExist($imageID)) {
            if ($action === 'like') {//判断用户是否有给出收藏请求
                if ($hasLoggedIn) {//如果用户已经登录
                    $this->message = $this->user->likeImage($imageID);
                } else {
                    $this->message = "未登录的用户不可以收藏图片";
                }

            } elseif ($action === 'unlike') {//判断用户是否给出取消收藏请求
                if ($hasLoggedIn) {//如果用户已经登录
                    $this->message = $this->user->unlikeImage($imageID);
                } else {
                    $this->message = "未登录的用户不可以取消收藏图片";
                }
            }
        }else{
            $this->message="这张图片不存在";
        }
    }//收藏和取消收藏的逻辑


    function getNumOfFavor($imageID)
    {
        $sql = "select * from travelimagefavor where ImageID=?";
        $result = $this->pdoAdapter->selectRows($sql, array($imageID));
        return count($result);
    }//得到这张照片的收藏数


    function searchImage($imageID)
    {
        if (isset($imageID)) {
            $sql = "select Title,UserName,Description,AsciiName,PATH,CountryName,ContentName,ImageID 
                    from ((travelimage inner join traveluser on travelimage.UID = traveluser.UID) 
                    inner join geocities on geocities.GeoNameID=travelimage.CityCode)
                    inner join geocountries on travelimage.CountryCodeISO=geocountries.ISO 
                    inner join geocontents on geocontents.ContentID=travelimage.ContentID
                    where ImageID=?";
            $this->imageInfo = $this->pdoAdapter->selectRows($sql, array($imageID));
            $this->getImage = count($this->imageInfo) === 1;
        }
    }//搜索图片


    function imageExist($imageID)
    {
        return count($this->pdoAdapter->selectRows("select imageID from travelimage where ImageID=?", array($imageID))) !== 0;
    }

    function printBigImage()
    {
        if ($this->getImage) {
            $path = $this->imageInfo[0]['PATH'];
            $title = $this->imageInfo[0]['Title'];
            echo "<img src=img/large/$path alt='$title' style='max-width: 100%'>";
        } else {
            header("location:error.php?errorCode=7");
            exit();
        }

    }//打印图片

    function printImageInfo()
    {
        if ($this->getImage) {
            $title = $this->imageInfo[0]['Title'];
            $username = $this->imageInfo[0]['UserName'];
            $desc = $this->imageInfo[0]['Description'];
            $cityName = $this->imageInfo[0]['AsciiName'];
            $content = $this->imageInfo[0]['ContentName'];
            $countryName = $this->imageInfo[0]['CountryName'];
            $imageID = $this->imageInfo[0]['ImageID'];
            $numOfFavor = $this->getNumOfFavor($imageID);
            echo "<h1 class='pure-u-1'>题目:$title</h1>";
            echo "<div class='pure-u-1'>作者:$username</div>";
            echo "<div class='pure-u-1'>内容:$content</div>";
            echo "<div class='pure-u-1'>城市:$cityName</div>";
            echo "<div class='pure-u-1'>国家:$countryName</div>";
            echo "<div class='pure-u-1'>描述:$desc</div>";
            echo "<div class='pure-u-1'>收藏量:$numOfFavor</div>";
        }
    }//打印图片的信息

    function printButtonAndMessage($hasLoggedIn)
    {
        if ($this->getImage) {
            if (!$hasLoggedIn) {//如果用户没有登录，则提示用户登录以后才能收藏
                echo "<button class='pure-u-1 pure-button pure-button-primary'><a class='pure-u-1' href='login.php'>想要收藏这张照片？登录！</a></button>";
            } else {
                $imageID = $this->imageInfo[0]['ImageID'];
                if ($this->user->hasLikedImage($this->imageInfo[0]['ImageID'])) {//如果用户收藏了图片，显示取消收藏的按钮
                    echo "<button class='pure-u-1 pure-button pure-button-primary' onclick=window.open('imageDetail.php?imageID=$imageID&action=unlike')>取消收藏</button>";
                } else {//反之，显示收藏的按钮
                    echo "<button class='pure-u-1 pure-button pure-button-primary' onclick=window.open('imageDetail.php?imageID=$imageID&action=like')>收藏</button>";
                }
            }
            if (isset($this->message)) {//如果有信息，则输出
                echo "<h2 style='color: red'>$this->message</h2>";
            }
        }

    }//打印收藏/取消收藏的按钮和收藏/取消收藏的结果信息


}