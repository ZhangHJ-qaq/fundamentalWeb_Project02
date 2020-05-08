<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>错误</title>
    <link rel="stylesheet" href="css/library/reset.css">
    <link rel="stylesheet" href="css/library/pure-release-1.0.1/pure-min.css">
    <link rel="stylesheet" href="css/error.css">
</head>
<body>
<div class="pure-u-1-2">
    <h1>呃....似乎你遇到了一些错误</h1>
    <?php
    $index=$_GET['errorCode'];
    $referer=$_SERVER['HTTP_REFERER'];
    if(empty($index)){
        $index=0;
    }
    if(empty($referer)){
        $referer="index.php";
    }
    $errorCause=array(
        "未知的内部错误",//0
        "用户名不符合要求",//1
        "邮箱不符合要求",//2
        "密码不符合要求或两次密码不一致",//3
        "用户名已经存在",//4
        "未知原因，注册失败",//5
        "用户名或密码错误",//6
        "没有找到相关图片",//7
        "图片上传不成功，或忘记上传图片",//8
        "图片过大，最多只能上传10M大小的图片",//9
        "只支持上传png,jpg和gif格式的图片",//10
        "上传/修改图片的信息填写不完整",//11
        "这张图片不存在，或这张图片不属于你",//12
        "这张图片不存在，或这张图片不属于你",//13
        "注册信息填写不完整"
    );
    $errorSolution=array(
        "重试。如果问题未解决，请联系我们，等待我们处理此问题。",
        "用户名必须是6-18位，且只能由字母和数字组成",
        "检查邮箱格式",
        "密码必须是6-18位，且不能是纯数字，两次密码必须一致",
        "换个用户名再试",
        "重试。如果问题未解决，请联系我们，等待我们处理此问题",
        "检查您的用户名或密码是否输入正确以后再尝试登录",
        "重试",
        "重试",
        "换个图片再试，或者压缩这张图片后再试",
        "尝试在本地更改文件的类型为支持的类型",
        "重新填写完整信息后再试",
        "请只修改自己的图片",
        "请只删除自己的图片",
        "请完整填写信息后再尝试注册"
    );

    echo "<p>错误原因:$errorCause[$index]</p>";
    echo "<p>简易的解决方法:$errorSolution[$index]</p>";
    echo "<a href=$referer>返回</a>";
    ?>
</div>
</body>
</html>
