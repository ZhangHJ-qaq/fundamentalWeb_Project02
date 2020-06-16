<?php
include_once $_SERVER['DOCUMENT_ROOT'] . "/class/Page.class.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/utilities/htmlpurifier-4.12.0/library/HTMLPurifier.auto.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/utilities/utilityFunction.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/utilities/imagefilter.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/class/UploadedImageInfo.class.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/class/User.class.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/class/PageWithCaptcha.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/class/MyCaptchaBuilder.php";

class Upload extends Page implements PageWithCaptcha
{
    private $controlForDisplay;//用户第一次进入这个页面，判断用户想上传还是编辑，控制对应的输出
    private $request;//判断用户的请求是上传还是修改
    private $modifyID;
    private $uploadedOrModifyImageInfo;//上传得到的信息
    private $message;
    public $originalImageInfo;
    private $myCaptchaBuilder;

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
        $this->modifyID = $_POST['modifyID'];
        $this->purifyControlForDisplay();
        $this->myCaptchaBuilder = new MyCaptchaBuilder();

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


    function conductUploadModify($userCaptchaInput)
    {
        if ($this->request === 'modify' && !customIsEmpty($this->modifyID)) {
            if (!$this->checkCaptchaInput($userCaptchaInput)) {
                $this->message = "验证码错误";
                return;
            }
            $this->message = $this->user->modifyImage($this->uploadedOrModifyImageInfo, $this->modifyID);

        } elseif ($this->request === 'upload') {
            if (!$this->checkCaptchaInput($userCaptchaInput)) {
                $this->message = "验证码错误";
                return;
            }
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
            $modifyID = htmlspecialchars($_GET['modifyID'], ENT_QUOTES);
            echo "<form class='pure-u-20-24 pure-form' action='upload_edit.php?control=modify&modifyID=$modifyID' method='post' enctype='multipart/form-data'>";
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
            $modifyID = htmlspecialchars($_GET['modifyID'], ENT_QUOTES);
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
            $path = htmlspecialchars($this->originalImageInfo['PATH'], ENT_QUOTES);
            echo "<img alt='' src='img/medium/$path' style='max-width: 100%'>";
        }
    }

    function printImageTitle()
    {
        if ($this->controlForDisplay === "modify") {//如果用户是编辑图片，则从数据库中读出原有的数据并填充
            $title = htmlspecialchars($this->originalImageInfo['Title'], ENT_QUOTES);
            echo "<input name='titleInput' id='titleInput' class='pure-u-1' value=$title>";
        } else {
            echo "<input name='titleInput' id='titleInput' class='pure-u-1'>";
        }
    }

    function printImageDesc()
    {
        if ($this->controlForDisplay === "modify") {
            $desc = htmlspecialchars($this->originalImageInfo['Description'], ENT_QUOTES);
            echo "<textarea name='descInput' id='descInput' class='pure-u-1'>$desc</textarea>";
        } else {
            echo "<textarea name='descInput' id='descInput' class='pure-u-1'></textarea>";
        }
    }

    function printCityOption()
    {
        if ($this->controlForDisplay === 'modify') {
            $cityCode = htmlspecialchars($this->originalImageInfo['CityCode'], ENT_QUOTES);
            $AsciiName = htmlspecialchars($this->originalImageInfo['AsciiName'], ENT_QUOTES);
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


    function generateCaptcha()
    {
        $this->myCaptchaBuilder->generateCaptcha();
        $questionText = $this->myCaptchaBuilder->getCaptchaQuestionText();
        $answer = $this->myCaptchaBuilder->getCaptchaAnswer();
        echo "<label class='pure-u-1'>$questionText</label>";
        echo "<input class='pure-u-1' name='captcha' type='text'>";
        session_start();
        $_SESSION['captchaAnswer'] = $answer;
        // TODO: Implement generateCaptcha() method.
    }


    function checkCaptchaInput($userCaptchaInput)
    {
        session_start();
        $correctCaptchaAnswer = $_SESSION['captchaAnswer'];
        if ($userCaptchaInput != $correctCaptchaAnswer) {
            return false;
        } else {
            return true;
        }

        // TODO: Implement checkCaptchaInput() method.
    }
}