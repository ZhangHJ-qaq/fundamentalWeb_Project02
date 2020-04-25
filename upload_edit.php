<?php
session_start();
include_once "class/Upload.class.php";
$upload = new Upload($_GET['action'], $_POST['request']);
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
    <script src="js/dropdownControl.js"></script>
</head>
<body>


<?php
$upload->printHeaderNeedLogin();
?>

<?php
$upload->conductUploadModify();

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
            $upload->printFormHead();
            ?>
            <fieldset>
                <legend>请填写照片的信息</legend>
                <?php
                $upload->printMessage();
                ?>
                <div class="pure-g" id="box">
                    <?php
                    $upload->printInvisibleInput();
                    ?>
                    <div class="pure-u-1" id="imagePreview">
                        <?php
                        $upload->printImage();
                        ?>
                    </div>
                    <input type="file" name="imageInput" class="pure-u-1" id="imageInput" accept="image/*">
                    <label class="pure-u-1">图片标题</label>
                    <?php
                    $upload->printImageTitle();
                    ?>
                    <label class="pure-u-1">图片描述</label>
                    <?php //如果用户是编辑图片，则从数据库中读出原有的数据并填充
                    $upload->printImageDesc();
                    ?>
                    <!--                        <textarea name="descInput" id="descInput" class="pure-u-1"></textarea>-->
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