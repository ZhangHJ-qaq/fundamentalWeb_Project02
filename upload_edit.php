<?php
session_start();
include_once "class/Upload.class.php";
$upload = new Upload($_GET['action'], $_POST['request']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ZHJ的旅游图片分享站-上传照片</title>
    <link rel="stylesheet" href="css/library/reset.css">
    <link rel="stylesheet" href="css/library/pure-release-1.0.1/pure-min.css">
    <link rel="stylesheet" href="css/universal.css">
    <link rel="stylesheet" href="css/upload.css">
    <script src="js/library/jquery-3.4.1.js"></script>
    <script src="js/uploadImagePreview.js"></script>
    <script src="js/uploadPrevalidation.js"></script>
    <script src="ajax/ajax_erJiLianDong.js"></script>
    <script src="js/dropdownControl.js"></script>
</head>
<body>


<?php
//打印出需要登陆的标题（如果用户不登录会跳转到登录页面）
$upload->printHeaderNeedLogin();
?>

<?php
//上传/修改的逻辑
$upload->conductUploadModify($_POST['captcha']);

$upload->jumpToUploadIfUserNotHaveImage();//用户第一次进入本页面时根据其id和查询字符串中的modifyID，判断其是否有这个图片，如果没有则跳转成登陆

?>
<div class="wrapper pure-g">
    <div class="pure-u-2-24"></div>
    <main class="pure-u-20-24 hasShadow" id="panel">
        <h1 id="title">
            <?php
            $upload->printFormTitle();
            ?>
        </h1>
        <div class="wrapper pure-g">
            <div class="pure-u-2-24"></div>
            <?php
            //打印出表单的head
            $upload->printFormHead();
            ?>
            <fieldset>
                <legend>请填写照片的信息</legend>
                <?php
                $upload->printMessage();
                ?>
                <div class="pure-g" id="box">
                    <?php
                    //打印出“隐形”的输入框，用于判断用户是要上传还是修改
                    $upload->printInvisibleInput();
                    ?>
                    <div class="pure-u-1" id="imagePreview">
                        <?php
                        //如果用户想要修改图片，打印出图片预览
                        $upload->printImage();
                        ?>
                    </div>
                    <input type="file" name="imageInput" class="pure-u-1" id="imageInput" accept="image/*">
                    <label class="pure-u-1">图片标题</label>
                    <?php
                    //如果编辑，读出原有图片标题并填充
                    $upload->printImageTitle();
                    ?>
                    <label class="pure-u-1">图片描述</label>
                    <?php //如果用户是编辑图片，则从数据库中读出原有的数据并填充
                    $upload->printImageDesc();
                    ?>
                    <label class="pure-u-1">主题，国家与城市</label>
                    <div id="selectBox" class="pure-u-1">
                        <div class="wrapper pure-g">
                            <select name="contentSelect" class="pure-u-1-3" id="contentSelect">
                                <?php
                                //生成内容的列表
                                $upload->printContentOptions($upload->originalImageInfo['ContentID']);
                                ?>
                            </select>

                            <select name="countrySelect" class="pure-u-1-3" id="countrySelect">
                                <?php
                                //生成国家的列表
                                $upload->printCountryOptions($upload->originalImageInfo['CountryCodeISO']);
                                ?>
                            </select>
                            <select name="citySelect" class="pure-u-1-3" id="citySelect">
                                <option value=''>选择城市</option>
                                <?php
                                //生成城市的下拉
                                $upload->printCityOption();
                                ?>
                            </select>
                        </div>
                    </div>
                    <?php
                    $upload->generateCaptcha();

                    ?>
                    <div class="pure-u-1" id="errorArea">
                    </div>
                    <button class="pure-button pure-button-primary pure-u-1" type="submit" id="submit">提交</button>
                </div>
            </fieldset>
            </form>
        </div>

    </main>
</div>

</body>
</html>