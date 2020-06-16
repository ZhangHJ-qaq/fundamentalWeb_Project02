<?php
include_once $_SERVER['DOCUMENT_ROOT'] . "/utilities/PDOAdapter.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/utilities/dbconfig.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/utilities/utilityFunction.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/class/User.class.php";

class Page//所有页面的基类
{
    public $pdoAdapter;
    public $user;

    function __construct()
    {
        session_start();

        $this->pdoAdapter = new PDOAdapter(HEADER, DBACCOUNT, DBPASSWORD, DBNAME);
        $this->user = new User($_SESSION['uid'], $this->pdoAdapter);
    }

    function printHeaderNoNeedLogin()//打印出不需要登陆的header
    {
        echo "<header>";
        session_start();
        if ($this->user->userExists) {
            $purifiedUsername = htmlspecialchars($this->user->getUsername(), ENT_QUOTES);
            echo "<span>{$purifiedUsername}</span>";
        }
        echo " <a href='index.php' id='headerHome'>主页</a>";
        echo "<a href='browser.php' id='headerBrowse'>浏览</a>";
        echo "<a href='search.php' id='headerSearch'>搜索</a>";

        if ($this->user->userExists) {
            echo "<div id='personalCenter'>
                个人中心
        <div id='headerDropdownMenu'>
            <a href='upload_edit.php' id='headerUpload'>上传照片</a>
            <a href='myPhoto.php' id='headerMyPhoto'>我的照片</a>
            <a href='myFavor.php' id='headerMyFavor'>我的收藏</a>
            <a href='changePassword.php' id='headerChangePassword'>修改密码</a>
            <a href='logout.php' id='headerLogout'>登出</a>
        </div>
    </div>";
        } else {
            echo "<a href='login.php' id='login'>登录</a>";
        }
        echo "</header>";
        return $this->user->userExists;
    }

    function printHeaderNeedLogin()
    {//打印出需要登陆的header 如果用户不登陆，会被赶去登陆
        echo "<header>";
        session_start();
        if (!$this->user->userExists) {
            header("location:login.php");
            exit();
        }
        if ($this->user->userExists) {
            $purifiedUsername = htmlspecialchars($this->user->getUsername(), ENT_QUOTES);
            echo "<span>{$purifiedUsername}</span>";
        }
        echo " <a href='index.php' id='headerHome'>主页</a>";
        echo "<a href='browser.php' id='headerBrowse'>浏览</a>";
        echo "<a href='search.php' id='headerSearch'>搜索</a>";

        if ($this->user->userExists) {
            echo "<div id='personalCenter'>
                个人中心
        <div id='headerDropdownMenu'>
            <a href='upload_edit.php' id='headerUpload'>上传照片</a>
            <a href='myPhoto.php' id='headerMyPhoto'>我的照片</a>
            <a href='myFavor.php' id='headerMyFavor'>我的收藏</a>
            <a href='changePassword.php' id='headerChangePassword'>修改密码</a>
            <a href='logout.php' id='headerLogout'>登出</a>
        </div>
    </div>";
        } else {
            echo "<a href='login.php' id='login'>登录</a>";
        }
        echo "</header>";
        return $this->user->userExists;
    }

    function printContentOptions($defaultContentID = null)
    {//打印出内容选择的下拉菜单里所有的option
        echo "<option value='' >选择内容</option>";
        $contentList = $this->pdoAdapter->selectRows("select ContentID,ContentName from geocontents order by ContentID desc ");
        for ($i = 0; $i <= count($contentList) - 1; $i++) {
            $contentID = htmlspecialchars($contentList[$i]['ContentID'], ENT_QUOTES);
            $contentName = htmlspecialchars($contentList[$i]['ContentName'], ENT_QUOTES);
            if ($contentID === $defaultContentID && !customIsEmpty($defaultContentID)) {
                echo "<option value='$contentID' selected>$contentName</option>";
            } else {
                echo "<option value=$contentID>$contentName</option>";
            }

        }

    }

    function printCountryOptions($defaultCountryISO = null)
    {//打印出国家选择下拉菜单中全部的option
        echo "<option value=''>选择国家</option>";
        $countryList = $this->pdoAdapter->selectRows("select ISO,CountryName from geocountries where ISO!=-2 order by CountryName asc ");
        for ($i = 0; $i <= count($countryList) - 1; $i++) {
            $ISO = htmlspecialchars($countryList[$i]['ISO'], ENT_QUOTES);
            $countryName = htmlspecialchars($countryList[$i]['CountryName'], ENT_QUOTES);
            if ($defaultCountryISO === $ISO && !customIsEmpty($defaultCountryISO)) {
                echo "<option value=$ISO selected>$countryName</option>";
            } else {
                echo "<option value=$ISO>$countryName</option>";
            }


        }

    }

    function closePDO()
    {//关闭pdo
        $this->pdoAdapter = null;
    }

}