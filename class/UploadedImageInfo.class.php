<?php


class UploadedImageInfo//对上传图像的信息的组合
{
    public $fileArray;
    public $titleInput;
    public $descInput;
    public $contentSelect;
    public $countrySelect;
    public $citySelect;

    function __construct($fileArray, $titleInput, $descInput, $contentSelect, $countrySelect, $citySelect)
    {
        $this->fileArray = $fileArray;
        $this->titleInput = $titleInput;
        $this->descInput = $descInput;
        $this->contentSelect = $contentSelect;
        $this->countrySelect = $countrySelect;
        $this->citySelect = $citySelect;
    }

}