<?php


interface PageWithCaptcha
{
    function generateCaptcha();
    function checkCaptchaInput($userCaptchaInput);



}