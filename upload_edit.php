<?php
include_once "utilities/PDOAdapter.php";
include_once "utilities/dbconfig.php";
include_once "utilities/htmlpurifier-4.12.0/library/HTMLPurifier.auto.php";
include_once "utilities/utilityFunction.php";
include_once "utilities/imagefilter.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>上传照片</title>
    <link rel="stylesheet" href="css/library/reset.css">
    <link rel="stylesheet" href="css/library/pure-release-1.0.1/pure.css">
    <link rel="stylesheet" href="css/universal.css">
    <link rel="stylesheet" href="css/upload.css">
    <script src="js/library/jquery-3.4.1.js"></script>
    <script src="js/uploadImagePreview.js"></script>
    <script src="js/uploadPrevalidation.js"></script>
    <script src="ajax/ajax_erJiLianDong.js"></script>
</head>


<body>


<header>
    <?php
    session_start();
    if (isset($_SESSION['username'])) {
        $uname = $_SESSION['username'];
        echo "<span>$uname</span>";
    }
    ?>
    <a href='index.php'>主页</a>
    <a href='browser.php'>浏览</a>
    <a href='search.php'>搜索</a>
    <?php
    if (isset($_SESSION['username'])) {
        echo "<div id=\"personalCenter\">
        个人中心
        <div id=\"headerDropdownMenu\">
            <a href=\"upload_edit.php\">上传照片</a>
            <a href=\"myPhoto.php\">我的照片</a>
            <a href=\"myFavor.php\">我的收藏</a>
            <a href=\"logout.php\">登出</a>
        </div>
    </div>";
        $hasLoggedIn = true;
    } else {
        header("location:login.php");
        exit();
    }
    ?>
</header>

<?php
$pdoAdapter = new PDOAdapter(HEADER, DBACCOUNT, DBPASSWORD, DBNAME);
if (isset($_GET['action']) && $_GET['action'] === 'modify' && !customIsEmpty($_GET['modifyID'])) {
    if (!userHasTheImage($_SESSION['uid'], $_GET['modifyID'])) {
        header("location:error.php?errorCode=12");
        exit();
    }
    $action = "modify";
} else {
    $action = "upload";
}

if ($_POST['request'] === 'upload') {//用户请求新上传

    checkFileInput();
    checkUserTextInput();

    $processedName = getProcessedFileName($_FILES['imageInput']['name']);
    //净化用户的输入
    purifyUserInput();

    $pdoAdapter->beginTransaction();
    $resultOfInsertRow = $pdoAdapter->insertARow("insert into travelimage (Title, Description, UID, PATH, ContentID,CityCode,CountryCodeISO) values (?,?,?,?,?,?,?)",
        array($_POST['titleInput'], $_POST['descInput'], $_SESSION['uid'], $processedName, $_POST['contentSelect'], $_POST['citySelect'], $_POST['countrySelect']));
    $resultOfCopy = copy($_FILES['imageInput']['tmp_name'], "img/large/" . $processedName);
    $compressedImage = new ImageFilter("img/large/$processedName", array('scaling' => ['size' => "150,150"]), "img/small/$processedName");
    $resultOfCompress = $compressedImage->outimage();
    if ($resultOfInsertRow && $resultOfCopy && ($resultOfCompress !== false)) {
        $pdoAdapter->commit();
        $message = "上传成功!";
    } else {
        $pdoAdapter->rollBack();
        deleteFile("img/small/$processedName");
        deleteFile("img/large/$processedName");
        $message = "上传失败!";
    }

} elseif ($_POST['request'] === 'modify') {//用户请求修改
    $photoChanged = !customIsEmpty($_FILES['imageInput']['name']);
    if ($photoChanged) {//用户有修改图片内容
        checkFileInput();
        checkUserTextInput();
        purifyUserInput();
        if (!userHasTheImage($_SESSION['uid'], $_POST['modifyID'])) {
            header("location:error.php?error.php?errorCode=12");
            exit();
        }
        $processedName = getProcessedFileName($_FILES['imageInput']['name']);

        $previousFileName = $pdoAdapter->selectRows("select PATH from travelimage where ImageID=?", array($_POST['modifyID']))[0]['PATH'];

        $pdoAdapter->beginTransaction();
        $sql = "update travelimage set Title=?,Description=?,ContentID=?,CountryCodeISO=?,CityCode=?,PATH=? where ImageID=?";
        $resultOfUpdate = $pdoAdapter->exec($sql, array($_POST['titleInput'], $_POST['descInput'], $_POST['contentSelect'], $_POST['countrySelect'], $_POST['citySelect'], $processedName, $_POST['modifyID']));
        $resultOfCopy = copy($_FILES['imageInput']['tmp_name'], "img/large/" . $processedName);
        $compressedImage = new ImageFilter("img/large/$processedName", array('scaling' => ['size' => "150,150"]), "img/small/$processedName");
        $resultOfCompress = $compressedImage->outimage();
        if ($resultOfCopy && $resultOfUpdate && $resultOfCompress !== false) {
            $pdoAdapter->commit();
            deleteFile("img/large/$previousFileName");
            deleteFile("img/small/$previousFileName");
            $message = "修改成功！";
        } else {
            $pdoAdapter->rollBack();
            deleteFile("img/large/$processedName");
            deleteFile("img/small/$processedName");
            $message = "修改成功!";
        }


    } else {//用户没有修改图片内容
        checkUserTextInput();
        purifyUserInput();
        if (!userHasTheImage($_SESSION['uid'], $_POST['modifyID'])) {
            header("location:error.php?errorCode=12");
            exit();
        }
        $sql = "update travelimage set Title=?,Description=?,ContentID=?,CountryCodeISO=?,CityCode=? where ImageID=?";
        $resultOfUpdate = $pdoAdapter->exec($sql, array($_POST['titleInput'], $_POST['descInput'], $_POST['contentSelect'], $_POST['countrySelect'], $_POST['citySelect'], $_POST['modifyID']));
        if ($resultOfUpdate) {
            $message = "修改成功";
        } else {
            $message = "修改失败";
        }

    }

}
function getProcessedFileName($originalFileName)
{
    global $pdoAdapter;
    $extName = getExt($originalFileName);
    while (true) {
        $processedName = get_hash() . "." . $extName;
        if ($pdoAdapter->getRowCount("select PATH from travelimage where PATH=?", array($processedName)) === 0) {
            break;
        }
    }
    return $processedName;
}

function userHasTheImage($uid, $imageID)
{
    global $pdoAdapter;
    $sql = "select imageID from travelimage where UID=? and $imageID=?";
    $count = $pdoAdapter->getRowCount($sql, array($uid, $imageID));
    return $count === 1;
}

function purifyUserInput()
{
    $purifier = new HTMLPurifier();
    $_POST['titleInput'] = $purifier->purify($_POST['titleInput']);
    $_POST['descInput'] = $purifier->purify($_POST['descInput']);
    $_POST['contentSelect'] = $purifier->purify($_POST['contentSelect']);
    $_POST['citySelect'] = $purifier->purify($_POST['citySelect']);
    $_POST['countrySelect'] = $purifier->purify($_POST['countrySelect']);
}

function checkFileInput()
{
    if ($_FILES['imageInput']['error'] !== 0) {
        header("location:error.php?errorCode=8");
        exit();
    }
    if ($_FILES['imageInput']['size'] > 1024 * 1024 * 10) {
        header("location:error.php?errorCode=9");
        exit();
    }
    if ($_FILES['imageInput']['type'] !== "image/png" && $_FILES['imageInput']['type'] !== "image/jpeg" && $_FILES['imageInput']['type'] !== "image/gif") {
        header("location:error.php?errorCode=10");
        exit();
    }
}

function checkUserTextInput()
{
    if (customIsEmpty($_POST['titleInput']) || customIsEmpty($_POST['descInput']) || customIsEmpty("countrySelect") || customIsEmpty("contentSelect") || customIsEmpty("citySelect")) {
        header("loaction:error.php?errorCode=11");
        exit();
    }
}

?>
<div class="wrapper pure-g">
    <div class="pure-u-2-24"></div>
    <main class="pure-u-20-24" id="panel">
        <h1 id="title">
            <?php
            if ($action === 'modify') {
                echo "编辑照片";
            } else {
                echo "上传照片";
            }
            ?>
        </h1>
        <div class="wrapper pure-g">
            <div class="pure-u-2-24"></div>
            <?php
            if ($action === 'modify') {
                $modifyID = $_GET['modifyID'];
                echo "<form class='pure-u-20-24 pure-form' action='upload_edit.php?action=modify&modifyID=$modifyID' method='post' enctype='multipart/form-data'>";
            } else {
                echo "<form class='pure-u-20-24 pure-form' action='upload_edit.php' method='post' enctype='multipart/form-data'>";
            }
            ?>
            <!--            <form class="pure-u-20-24 pure-form" action="upload.php" method="post" enctype="multipart/form-data">-->
            <fieldset>
                <legend>请填写照片的信息</legend>
                <?php
                if (!customIsEmpty($message)) {
                    echo "<div class='pure-u-1' style='color: red'>$message</div>";
                }
                ?>
                <div class="pure-g" id="box">
                    <?php
                    if ($action === 'modify') {
                        echo "<input name='request' value='modify' style='display: none'>";
                        $modifyID = $_GET['modifyID'];
                        echo "<input name='modifyID' value=$modifyID style='display:none'>";
                    } else {
                        echo "<input name='request' value='upload' style='display: none'>";
                    }
                    ?>
                    <div class="pure-u-1" id="imagePreview">
                        <?php
                        if ($action === 'modify') {
                            $sql = "select Title,Description,ContentID,travelimage.CountryCodeISO,CityCode,PATH,AsciiName from travelimage inner join geocities on CityCode=GeoNameID where ImageID=? and UID=?";
                            $image = $pdoAdapter->selectRows($sql, array($_GET['modifyID'], $_SESSION['uid']));
                            $path = $image[0]['PATH'];
                            echo "<img alt='' src='img/large/$path' style='max-width: 100%'>";
                        }

                        ?>
                    </div>
                    <input type="file" name="imageInput" class="pure-u-1" id="imageInput" accept="image/*">
                    <label class="pure-u-1">图片标题</label>
                    <?php
                    if ($action === "modify") {
                        $title = $image[0]['Title'];
                        echo "<input name='titleInput' id='titleInput' class='pure-u-1' value=$title>";
                    } else {
                        echo "<input name='titleInput' id='titleInput' class='pure-u-1'>";
                    }

                    ?>
                    <label class="pure-u-1">图片描述</label>
                    <?php
                    if ($action === "modify") {
                        $desc = $image[0]['Description'];
                        echo "<textarea name='descInput' id='descInput' class='pure-u-1'>$desc</textarea>";
                    } else {
                        echo "<textarea name='descInput' id='descInput' class='pure-u-1'></textarea>";
                    }
                    ?>
                    <!--                        <textarea name="descInput" id="descInput" class="pure-u-1"></textarea>-->
                    <label class="pure-u-1">主题，国家与城市</label>
                    <div id="selectBox" class="pure-u-1">
                        <div class="wrapper pure-g">
                            <select name="contentSelect" class="pure-u-1-3" id="contentSelect">
                                <?php
                                echo "<option value=''>选择主题</option>";
                                $contentList = $pdoAdapter->selectRows("select ContentID,ContentName from geocontents order by ContentID desc ");
                                for ($i = 0; $i <= count($contentList) - 1; $i++) {
                                    $contentID = $contentList[$i]['ContentID'];
                                    $contentName = $contentList[$i]['ContentName'];
                                    if ($action === 'modify' && $contentID === $image[0]['ContentID']) {

                                        echo "<option value='$contentID' selected>$contentName</option>";

                                    } else {
                                        echo "<option value=$contentID>$contentName</option>";

                                    }

                                }
                                ?>
                            </select>

                            <select name="countrySelect" class="pure-u-1-3" id="countrySelect">
                                <?php
                                echo "<option value=''>选择国家</option>";
                                $countryList = $pdoAdapter->selectRows("select ISO,CountryName from geocountries where ISO!=-2");
                                for ($i = 0; $i <= count($countryList) - 1; $i++) {
                                    $ISO = $countryList[$i]['ISO'];
                                    $countryName = $countryList[$i]['CountryName'];
                                    if ($action === 'modify' && $ISO === $image[0]['CountryCodeISO']) {
                                        echo "<option value=$ISO selected>$countryName</option>";
                                    } else {
                                        echo "<option value=$ISO>$countryName</option>";
                                    }
                                }
                                echo "<option value='-2'>其他国家</option>"
                                ?>
                            </select>
                            <select name="citySelect" class="pure-u-1-3" id="citySelect">
                                <option value=''>选择城市</option>
                                <?php
                                if ($action === 'modify') {
                                    $cityCode = $image[0]['CityCode'];
                                    $AsciiName = $image[0]['AsciiName'];
                                    echo "<option value='$cityCode' selected>$AsciiName</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="pure-u-1" id="errorArea">
                    </div>
                    <button class="pure-button pure-button-primary pure-u-1" type="submit" id="submit">提交</button>
                </div>
            </fieldset>
            </form>
        </div>

    </main>
</div>
<footer>
    ZHJ制作 19302010021 本网站由<a href="https://purecss.net">Pure.css</a>驱动
</footer>
</body>
</html>