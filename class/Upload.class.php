<?php
include_once "Page.class.php";
include_once "utilities/htmlpurifier-4.12.0/library/HTMLPurifier.auto.php";
include_once "utilities/utilityFunction.php";
include_once "utilities/imagefilter.php";
include_once "class/UploadedImageInfo.class.php";
include_once "User.class.php";

class Upload extends Page
{
    private $controlForDisplay;//用户第一次进入这个页面，判断用户想上传还是编辑，控制对应的输出
    private $request;//判断用户的请求是上传还是修改
    private $modifyID;
    private $uploadedOrModifyImageInfo;//上传得到的信息
    private $user;
    private $message;
    public $originalImageInfo;

    public function __construct($controlForDisplay, $request)
    {
        parent::__construct();
        $this->controlForDisplay = $controlForDisplay;
        $this->request = $request;
        $this->uploadedOrModifyImageInfo = new UploadedImageInfo(
            $_FILES['imageInput'],
            $_POST['titleInput'],
            $_POST['descInput'],
            $_POST['contentSelect'],
            $_POST['countrySelect'],
            $_POST['citySelect']
        );
        $this->user = new User($_SESSION['uid'], $this->pdoAdapter);
        $this->modifyID = $_POST['modifyID'];
        $this->purifyControlForDisplay();

    }

    function purifyControlForDisplay()
    {//净化
        if ($this->controlForDisplay !== "modify") {
            $this->controlForDisplay = "upload";
        }
        if ($this->controlForDisplay === 'modify' && !$this->imageExist($_GET['modifyID'])) {
            $this->controlForDisplay = "upload";
        }
    }

    function imageExist($imageID)
    {
        return count($this->pdoAdapter->selectRows("select imageID from travelimage where ImageID=?", array($imageID))) !== 0;
    }


    function conductUploadModify()
    {
        if ($this->request === 'modify' && !customIsEmpty($this->modifyID)) {
            $this->message = $this->user->modifyImage($this->uploadedOrModifyImageInfo, $this->modifyID);

        } elseif ($this->request === 'upload') {
            $this->message = $this->user->uploadImage($this->uploadedOrModifyImageInfo);
        }


    }


    function printFormTitle()
    {
        if ($this->controlForDisplay === 'modify') {//判断用户想要上传还是编辑
            echo "编辑照片";
        } else {
            echo "上传照片";
        }
    }

    function printFormHead()
    {
        if ($this->controlForDisplay === 'modify') {
            $modifyID = $_GET['modifyID'];
            echo "<form class='pure-u-20-24 pure-form' action='upload_edit.php?action=modify&modifyID=$modifyID' method='post' enctype='multipart/form-data'>";
        } else {
            echo "<form class='pure-u-20-24 pure-form' action='upload_edit.php' method='post' enctype='multipart/form-data'>";
        }
    }

    function printMessage()
    {
        if (!customIsEmpty($this->message)) {
            echo "<div class='pure-u-1' style='color: red'>$this->message</div>";
        }
    }

    function printInvisibleInput()
    {
        //这两个input是“隐形”的，功能是在提交表单之后在后端判断用户是上传图片还是修改已有的图片
        if ($this->controlForDisplay === 'modify') {
            echo "<input name='request' value='modify' style='display: none'>";
            $modifyID = $_GET['modifyID'];
            echo "<input name='modifyID' value=$modifyID style='display:none'>";
        } else {
            echo "<input name='request' value='upload' style='display: none'>";
        }
    }

    function printImage()
    {
        if ($this->controlForDisplay === 'modify') {//如果用户修改图片则从数据库中读取图片 生成图片预览
            $sql = "select Title,Description,ContentID,travelimage.CountryCodeISO,CityCode,PATH,AsciiName from travelimage inner join geocities on CityCode=GeoNameID where ImageID=? and UID=?";
            $this->originalImageInfo = $this->pdoAdapter->selectRows($sql, array($_GET['modifyID'], $_SESSION['uid']))[0];
            $path = $this->originalImageInfo['PATH'];
            echo "<img alt='' src='img/large/$path' style='max-width: 100%'>";
        }
    }

    function printImageTitle()
    {
        if ($this->controlForDisplay === "modify") {//如果用户是编辑图片，则从数据库中读出原有的数据并填充
            $title = $this->originalImageInfo['Title'];
            echo "<input name='titleInput' id='titleInput' class='pure-u-1' value=$title>";
        } else {
            echo "<input name='titleInput' id='titleInput' class='pure-u-1'>";
        }
    }

    function printImageDesc()
    {
        if ($this->controlForDisplay === "modify") {
            $desc = $this->originalImageInfo['Description'];
            echo "<textarea name='descInput' id='descInput' class='pure-u-1'>$desc</textarea>";
        } else {
            echo "<textarea name='descInput' id='descInput' class='pure-u-1'></textarea>";
        }
    }

    function printCityOption()
    {
        if ($this->controlForDisplay === 'modify') {
            $cityCode = $this->originalImageInfo['CityCode'];
            $AsciiName = $this->originalImageInfo['AsciiName'];
            echo "<option value='$cityCode' selected>$AsciiName</option>";
        }
    }

    function jumpToUploadIfUserNotHaveImage()
    {
        if ($this->controlForDisplay === 'modify' && !$this->user->hasImage($_GET['modifyID'])) {
            $this->controlForDisplay = "upload";
            $this->message = "你还没有这张图片。已为你自动跳转到上传页面";
        }

    }


}