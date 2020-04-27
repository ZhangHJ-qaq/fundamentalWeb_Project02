<?php
include_once "utilities/PDOAdapter.php";
include_once "utilities/dbconfig.php";
include_once "utilities/utilityFunction.php";
include_once "UploadedImageInfo.class.php";
include_once "utilities/htmlpurifier-4.12.0/library/HTMLPurifier.auto.php";

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

            if ($resultOfDeleteFavor && $resultOfDeleteImage && $resultOfDeleteBigFile && $resultOfDeleteSmallFile) {//如果删除记录，删除大小文件都成功
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
        if (!$this->hasImage($modifyID)) {
            $message = "编辑失败。你还没有这张图片，不可以编辑，建议尝试直接上传图片。";
            return $message;
        }

        $photoChanged = !customIsEmpty($uploadedImageInfo->fileArray['name']);
        if ($photoChanged) {//如果用户此次上传操作改变了图片内容
            $originalFileName = $this->pdoAdapter->selectRows("select PATH from travelimage where ImageID=?", array($modifyID))[0]['PATH'];
            $newFileName = $this->getUnusedFileName($uploadedImageInfo);

            $this->pdoAdapter->beginTransaction();
            $sql = "update travelimage set Title=?,Description=?,ContentID=?,CountryCodeISO=?,CityCode=?,PATH=? where ImageID=?";

            $resultOfUpdate = $this->pdoAdapter->exec($sql, array($_POST['titleInput'], $_POST['descInput'], $_POST['contentSelect'], $_POST['countrySelect'], $_POST['citySelect'], $newFileName, $_POST['modifyID']));

            $resultOfCopy = copy($_FILES['imageInput']['tmp_name'], "img/large/" . $newFileName);

            $compressedImage = new ImageFilter("img/large/$newFileName", array('scaling' => ['size' => "150,150"]), "img/small/$newFileName");
            $resultOfCompress = $compressedImage->outimage();

            if ($resultOfCopy && $resultOfUpdate && $resultOfCompress !== false) {//如果三者都成功
                $this->pdoAdapter->commit();
                deleteFile("img/large/$originalFileName");//删掉数据库里的旧照片
                deleteFile("img/small/$originalFileName");
                $message = "修改成功！";
            } else {//如果不成功
                $this->pdoAdapter->rollBack();
                deleteFile("img/large/$newFileName");//删掉数据库里的新照片，此时旧照片不动
                deleteFile("img/small/$newFileName");
                $message = "修改失败!";
            }

        } elseif (!$photoChanged) {

            $sql = "update travelimage set Title=?,Description=?,ContentID=?,CountryCodeISO=?,CityCode=? where ImageID=?";
            $resultOfUpdate = $this->pdoAdapter->exec($sql, array($_POST['titleInput'], $_POST['descInput'], $_POST['contentSelect'], $_POST['countrySelect'], $_POST['citySelect'], $_POST['modifyID']));
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
        $this->checkFileInput($uploadedImageInfo->fileArray);

        $imageID = $this->getUnusedImageID();
        $newFileName = $this->getUnusedFileName($uploadedImageInfo);


        $this->pdoAdapter->beginTransaction();
        $sql = "insert into travelimage 
        (ImageID,Title, Description, UID, PATH, ContentID,CityCode,CountryCodeISO)
        values (?,?,?,?,?,?,?,?)";
        $resultOfInsertRow = $this->pdoAdapter->insertARow($sql,
            array($imageID, $_POST['titleInput'], $_POST['descInput'], $this->uid, $newFileName, $_POST['contentSelect'], $_POST['citySelect'], $_POST['countrySelect']));

        $resultOfCopy = copy($_FILES['imageInput']['tmp_name'], "img/large/" . $newFileName);//将图片原封不动地拷贝到大图片的文件夹内


        $compressedImage = new ImageFilter("img/large/$newFileName", array('scaling' => ['size' => "150,150"]), "img/small/$newFileName");
        $resultOfCompress = $compressedImage->outimage();//压缩图片，输出到小图片文件夹


        if ($resultOfInsertRow && $resultOfCopy && ($resultOfCompress !== false)) {//如果插入行，拷贝到大图片文件夹，输出到小文件夹都成功
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

    function checkAndPurifyImageInfo(UploadedImageInfo &$uploadedImageInfo)
    {
        if (customIsEmpty($uploadedImageInfo->titleInput) ||
            customIsEmpty($uploadedImageInfo->descInput) ||
            customIsEmpty($uploadedImageInfo->citySelect) ||
            customIsEmpty($uploadedImageInfo->countrySelect) ||
            customIsEmpty($uploadedImageInfo->contentSelect)
        ) {
            header("location:error.php?errorCode=11");
            exit();
        }
        $purifier = new HTMLPurifier();
        $uploadedImageInfo->titleInput = $purifier->purify($uploadedImageInfo->titleInput);
        $uploadedImageInfo->descInput = $purifier->purify($uploadedImageInfo->descInput);
        $uploadedImageInfo->contentSelect = $purifier->purify($uploadedImageInfo->contentSelect);
        $uploadedImageInfo->citySelect = $purifier->purify($uploadedImageInfo->citySelect);
        $uploadedImageInfo->countrySelect = $purifier->purify($uploadedImageInfo->countrySelect);

        return $uploadedImageInfo;

    }

    function checkFileInput(array $fileInput)
    {
        if ($fileInput['error'] !== 0) {//是否上传成功
            header("location:error.php?errorCode=8");
            exit();
        }
        if ($fileInput['size'] > 1024 * 1024 * 10) {//文件是否过大
            header("location:error.php?errorCode=9");
            exit();
        }
        if ($fileInput['type'] !== "image/png" && $_FILES['imageInput']['type'] !== "image/jpeg" && $_FILES['imageInput']['type'] !== "image/gif") {
            header("location:error.php?errorCode=10");
            exit();//检查文件类型符不符合要求
        }
    }

    function getUnusedImageID()
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

    function getUnusedFileName(UploadedImageInfo $uploadedImageInfo)
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
        if($this->imageExist($imageID)){
            if ($this->hasLikedImage($imageID)) {
                $message = "你已经收藏过这个图片了！";
            } else {
                $sql = "insert into travelimagefavor (UID, ImageID) VALUES (?,?)";
                $success = $this->pdoAdapter->insertARow($sql, array($this->uid, $imageID));
                $message = $success ? "收藏成功" : "收藏失败";
            }
        }else{
            $message="这个图片不存在";
        }

        return $message;
    }

    function imageExist($imageID)
    {
        return count($this->pdoAdapter->selectRows("select imageID from travelimage where ImageID=?", array($imageID))) !== 0;
    }



}