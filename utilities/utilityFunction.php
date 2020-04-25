<?php
function get_hash()
{
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()+-';
    $random = $chars[mt_rand(0, 73)] . $chars[mt_rand(0, 73)] . $chars[mt_rand(0, 73)] . $chars[mt_rand(0, 73)] . $chars[mt_rand(0, 73)];//Random 5 times
    $content = uniqid() . $random;
    return md5($content);
}

function isPositiveNumber($input)
{
    return preg_match("/^[0-9]+$/", $input);
}

function getExt($filename)
{
    $arr = explode('.', $filename);
    return array_pop($arr);
}

function customIsEmpty($s)
{
    return $s === null || $s === '';
}

function deleteFile($path)
{
    if (file_exists($path)) {
        $result = unlink($path);
    } else {
        $result = true;
    }
    return $result;
}


function purifyPageInput($page, $maxNumOfPage)
{
    if ($page <= 0) {
        $page = 1;
    } elseif ($page > $maxNumOfPage) {
        $page = $maxNumOfPage;
    }
    if (!isPositiveNumber($page)) {
        $page = 1;
    }
    return $page;
}