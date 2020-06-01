<?php
include_once $_SERVER['DOCUMENT_ROOT'] . "/utilities/PDOAdapter.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/utilities/dbconfig.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/utilities/utilityFunction.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/class/UploadedImageInfo.class.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/utilities/htmlpurifier-4.12.0/library/HTMLPurifier.auto.php";

class User
{
    private $uid;
    private $pdoAdapter;


    function __construct($uid, PDOAdapter $pdoAdapter)
    {
        $this->uid = $uid;
        $this->pdoAdapter = $pdoAdapter;
    }

    function hasImage($imageID)//判断用户是否有这张图片
    {
        $count = $this->pdoAdapter->getRowCount("select ImageID from travelimage where UID=? and ImageID=?", array($this->uid, $imageID));
        if ($count === 1) {
            return true;
        }
        return false;
    }

    function deleteImage($deleteID)//删除图片的逻辑
    {
        if ($this->hasImage($deleteID)) {
            $imagePath = $this->pdoAdapter->selectRows("select PATH from travelimage where ImageID=?", array($deleteID))[0]['PATH'];//先得到图像的路径，后面在数据库中删除图片要用
            $this->pdoAdapter->beginTransaction();
            $sql = "delete from travelimage where ImageID=?";
            $resultOfDeleteImage = $this->pdoAdapter->exec($sql, array($deleteID));
            $sql = "delete from travelimagefavor where ImageID=?";
            $resultOfDeleteFavor = $this->pdoAdapter->exec($sql, array($deleteID));
            $resultOfDeleteSmallFile = deleteFile("img/small/$imagePath");
            $resultOfDeleteBigFile = deleteFile("img/large/$imagePath");
            $resultOfDeleteMediumFile = deleteFile("img/medium/$imagePath");


            if ($resultOfDeleteFavor && $resultOfDeleteImage && $resultOfDeleteBigFile && $resultOfDeleteSmallFile && $resultOfDeleteMediumFile) {//如果删除记录，删除大小文件都成功
                $message = "删除成功";
                $this->pdoAdapter->commit();
            } else {//反之回滚
                $message = "删除失败";
                $this->pdoAdapter->rollBack();
            }
        } else {
            $message = "你不能删除自己没有拥有的图片";
        }
        return $message;

    }

    function modifyImage(UploadedImageInfo $uploadedImageInfo, $modifyID)//修改图片的逻辑
    {
        $message = '';
        $uploadedImageInfo = $this->checkAndPurifyImageInfo($uploadedImageInfo);
        if ($uploadedImageInfo === false) {
            $message = "图片的信息填写不完整或部分信息不合法。请整改再尝试上传";
            return $message;
        }
        if (!$this->hasImage($modifyID)) {
            $message = "编辑失败。你还没有这张图片，不可以编辑，建议尝试直接上传图片。";
            return $message;
        }

        $photoChanged = !customIsEmpty($uploadedImageInfo->fileArray['name']);
        if ($photoChanged) {//如果用户此次上传操作改变了图片内容
            $fileErrorInfo = $this->checkFileInput($uploadedImageInfo->fileArray);
            if ($fileErrorInfo !== false) {
                return $fileErrorInfo;
            }

            $originalFileName = $this->pdoAdapter->selectRows("select PATH from travelimage where ImageID=?", array($modifyID))[0]['PATH'];
            $newFileName = $this->getUnusedFileName($uploadedImageInfo);

            $this->pdoAdapter->beginTransaction();
            $sql = "update travelimage set Title=?,Description=?,ContentID=?,CountryCodeISO=?,CityCode=?,PATH=? where ImageID=?";

            $resultOfUpdate = $this->pdoAdapter->exec($sql, array($uploadedImageInfo->titleInput, $uploadedImageInfo->descInput, $uploadedImageInfo->contentSelect, $uploadedImageInfo->countrySelect, $uploadedImageInfo->citySelect, $newFileName, $modifyID));

            $resultOfCopy = copy($_FILES['imageInput']['tmp_name'], "img/large/" . $newFileName);

            $imageInfo = getimagesize($_FILES['imageInput']['tmp_name']);
            $width = $imageInfo[0];

            $mediumWidth = min(768, $width);
            $smallWidth = min(250, $width);

            $compressedSmallImage = new ImageFilter("img/large/$newFileName", array('scaling' => ['size' => "$smallWidth"]), "img/small/$newFileName");
            $resultOfCompress = $compressedSmallImage->outimage();

            $compressedMediumImage = new ImageFilter("img/large/$newFileName", array('scaling' => ['size' => "$mediumWidth"]), "img/medium/$newFileName");
            $resultOfCompressToMedium = $compressedMediumImage->outimage();


            if ($resultOfCopy && $resultOfUpdate && ($resultOfCompress !== false) && ($resultOfCompressToMedium !== false)) {//如果三者都成功
                $this->pdoAdapter->commit();
                deleteFile("img/large/$originalFileName");//删掉数据库里的旧照片
                deleteFile("img/small/$originalFileName");
                deleteFile("img/medium/$originalFileName");
                $message = "修改成功！";
            } else {//如果不成功
                $this->pdoAdapter->rollBack();
                deleteFile("img/large/$newFileName");//删掉数据库里的新照片，此时旧照片不动
                deleteFile("img/small/$newFileName");
                deleteFile("img/medium/$newFileName");
                $message = "修改失败!";
            }

        } elseif (!$photoChanged) {

            $sql = "update travelimage set Title=?,Description=?,ContentID=?,CountryCodeISO=?,CityCode=? where ImageID=?";
            $resultOfUpdate = $this->pdoAdapter->exec($sql, array($uploadedImageInfo->titleInput, $uploadedImageInfo->descInput, $uploadedImageInfo->contentSelect, $uploadedImageInfo->countrySelect, $uploadedImageInfo->citySelect, $modifyID));
            if ($resultOfUpdate) {
                $message = "修改成功";
            } else {
                $message = "修改失败";
            }
        }


        return $message;
    }


    function uploadImage(UploadedImageInfo $uploadedImageInfo)//上传图片的逻辑
    {
        $message = '';

        $uploadedImageInfo = $this->checkAndPurifyImageInfo($uploadedImageInfo);
        if ($uploadedImageInfo === false) {
            $message = "图片的信息填写不完整或部分信息不合法。请整改再尝试上传";
            return $message;
        }

        $fileErrorInfo = $this->checkFileInput($uploadedImageInfo->fileArray);
        if ($fileErrorInfo !== false) {
            return $fileErrorInfo;
        }

        $imageID = $this->getUnusedImageID();
        $newFileName = $this->getUnusedFileName($uploadedImageInfo);


        $this->pdoAdapter->beginTransaction();
        $sql = "insert into travelimage 
        (ImageID,Title, Description, UID, PATH, ContentID,CityCode,CountryCodeISO)
        values (?,?,?,?,?,?,?,?)";
        $resultOfInsertRow = $this->pdoAdapter->insertARow($sql,
            array($imageID, $_POST['titleInput'], $_POST['descInput'], $this->uid, $newFileName, $_POST['contentSelect'], $_POST['citySelect'], $_POST['countrySelect']));

        $resultOfCopy = copy($_FILES['imageInput']['tmp_name'], "img/large/" . $newFileName);//将图片原封不动地拷贝到大图片的文件夹内

        $imageInfo = getimagesize($_FILES['imageInput']['tmp_name']);
        $width = $imageInfo[0];

        $mediumWidth = min(768, $width);
        $smallWidth = min(250, $width);


        $compressedSmallImage = new ImageFilter("img/large/$newFileName", array('scaling' => ['size' => "$smallWidth"]), "img/small/$newFileName");
        $resultOfCompressToSmall = $compressedSmallImage->outimage();//压缩图片，输出到小图片文件夹


        $compressedMediumImage = new ImageFilter("img/large/$newFileName", array('scaling' => ['size' => "$mediumWidth"]), "img/medium/$newFileName");
        $resultOfCompressToMedium = $compressedMediumImage->outimage();


        if ($resultOfInsertRow && $resultOfCopy && ($resultOfCompressToSmall !== false) && ($resultOfCompressToMedium !== false)) {//如果插入行，拷贝到大图片文件夹，输出到小文件夹都成功
            $this->pdoAdapter->commit();
            $message = "上传成功!";
        } else {//否则回滚
            $this->pdoAdapter->rollBack();
            deleteFile("img/small/$newFileName");
            deleteFile("img/large/$newFileName");
            $message = "上传失败!";
        }

        return $message;

    }

    private function checkAndPurifyImageInfo(UploadedImageInfo &$uploadedImageInfo)
    {
        if (customIsEmpty($uploadedImageInfo->titleInput) ||
            customIsEmpty($uploadedImageInfo->descInput) ||
            customIsEmpty($uploadedImageInfo->citySelect) ||
            customIsEmpty($uploadedImageInfo->countrySelect) ||
            customIsEmpty($uploadedImageInfo->contentSelect)
        ) {
            return false;
        }//检测每个信息是否为空
        $purifier = new HTMLPurifier();
        $uploadedImageInfo->titleInput = $purifier->purify($uploadedImageInfo->titleInput);
        $uploadedImageInfo->descInput = $purifier->purify($uploadedImageInfo->descInput);
        $uploadedImageInfo->contentSelect = $purifier->purify($uploadedImageInfo->contentSelect);
        $uploadedImageInfo->citySelect = $purifier->purify($uploadedImageInfo->citySelect);
        $uploadedImageInfo->countrySelect = $purifier->purify($uploadedImageInfo->countrySelect);

        //标题限制最多50个字符，描述最多限制500个字符
        $uploadedImageInfo->titleInput = substr($uploadedImageInfo->titleInput, 0, 50);
        $uploadedImageInfo->descInput = substr($uploadedImageInfo->descInput, 0, 500);

        $sql = "select ContentID from geocontents where ContentID=?";//在后台检测用户从前端发来的内容，城市，和国家的选项是否合法
        if ($this->pdoAdapter->isRowCountZero($sql, array($uploadedImageInfo->contentSelect))) {
            return false;
        }
        $sql = "select ISO from geocountries where ISO=?";
        if ($this->pdoAdapter->isRowCountZero($sql, array($uploadedImageInfo->countrySelect))) {
            return false;
        }
        $sql = "select GeoNameID from geocities where GeoNameID=? and CountryCodeISO=?";
        if ($this->pdoAdapter->isRowCountZero($sql, array($uploadedImageInfo->citySelect, $uploadedImageInfo->countrySelect)) && $uploadedImageInfo->citySelect != -1) {
            return false;
        }

        return $uploadedImageInfo;

    }

    private function checkFileInput(array $fileInput)
    {
        if ($fileInput['error'] !== 0) {//是否上传成功
            return "文件上传失败，请重试";
        }
        if ($fileInput['size'] > 1024 * 1024 * 10) {//文件是否过大
            return "文件尺寸过大。最多只能上传10MB的图片";
        }
        if ($fileInput['type'] !== "image/png" && $_FILES['imageInput']['type'] !== "image/jpeg" && $_FILES['imageInput']['type'] !== "image/gif") {
            return "文件格式不符合要求。只支持png，jpg，jpeg和gif格式的图片";
        }
        return false;
    }

    private function getUnusedImageID()
    {
        $needToContiune = true;
        while (true) {
            $id = get_hash();
            $sql = "select * from travelimage where ImageID=?";
            $needToContiune = $this->pdoAdapter->getRowCount($sql, array($id)) !== 0;
            if (!$needToContiune) {
                return $id;
                break;
            }
        }
        return "";

    }

    private function getUnusedFileName(UploadedImageInfo $uploadedImageInfo)
    {
        $needToContiune = true;
        while (true) {
            $extName = getExt($uploadedImageInfo->fileArray['name']);
            $newFileName = get_hash() . "." . $extName;
            $sql = "select * from travelimage where PATH=?";
            $needToContiune = $this->pdoAdapter->getRowCount($sql, array($newFileName)) !== 0;
            if (!$needToContiune) {
                return $newFileName;
                break;
            }
        }
        return "";
    }


    function hasLikedImage($imageID)//用户是否已经喜欢了这张图片
    {
        $sql = "select * from travelimagefavor where UID=? and ImageID=?";
        $result = $this->pdoAdapter->selectRows($sql, array($this->uid, $imageID));
        return count($result) !== 0;
    }

    function unlikeImage($imageID)//取消收藏的逻辑
    {
        $message = '';
        if (!$this->hasLikedImage($imageID)) {
            $message = "你还没有收藏这个图片，不能取消收藏";
        } else {
            $sql = "delete from travelimagefavor where UID=? and ImageID=?";
            $success = $this->pdoAdapter->deleteRows($sql, array($this->uid, $imageID));
            $message = $success ? "取消收藏成功" : "取消收藏失败";
        }
        return $message;
    }

    function likeImage($imageID)//收藏的逻辑
    {
        $message = '';
        if ($this->imageExist($imageID)) {
            if ($this->hasLikedImage($imageID)) {
                $message = "你已经收藏过这个图片了！";
            } else {
                $sql = "insert into travelimagefavor (UID, ImageID) VALUES (?,?)";
                $success = $this->pdoAdapter->insertARow($sql, array($this->uid, $imageID));
                $message = $success ? "收藏成功" : "收藏失败";
            }
        } else {
            $message = "这个图片不存在";
        }

        return $message;
    }

    function imageExist($imageID)
    {
        return count($this->pdoAdapter->selectRows("select imageID from travelimage where ImageID=?", array($imageID))) !== 0;
    }

    /**
     * @return mixed
     */
    public function getUid()
    {
        return $this->uid;
    }

    public function changePassword($originalUnsaltedPasswordInput, $newPassword1, $newPassword2)
    {
        if ($newPassword1 !== $newPassword2) {
            return "两次输入密码不一致";
        }
        if (!preg_match("/^.{6,18}$/", $newPassword1) || preg_match("/^[0-9]{1,}$/", $newPassword1)) {
            return "密码必须是6-18位";
        }
        $purifier = new HTMLPurifier();
        if ($newPassword1 !== $purifier->purify($newPassword1)) {
            return "修改失败";
        }
        $info = $this->getOriginalSaltAndPassword();
        $originalSaltedPassword = $info['originalSaltedPassword'];
        $salt = $info['salt'];
        if (MD5($originalUnsaltedPasswordInput . $salt) !== $originalSaltedPassword) {
            return "原密码错误，密码修改失败";
        }
        $newSaltedPassword = MD5($newPassword1 . $salt);
        $sql = "update traveluser set Pass=? where UID=?";
        $result = $this->pdoAdapter->exec($sql, array($newSaltedPassword, $this->getUid()));
        if ($result === true) {
            return "密码修改成功";
        } else {
            return "密码修改失败";
        }

    }

    private function getOriginalSaltAndPassword()
    {
        $sql = "select Pass,salt from traveluser where UID=?";
        $info = $this->pdoAdapter->selectRows($sql, array($this->getUid()))[0];
        $originalSaltedPassword = $info['Pass'];
        $salt = $info['salt'];
        return array("originalSaltedPassword" => $originalSaltedPassword, "salt" => $salt);

    }


}