<?php
namespace paws\helpers;

class Json extends \yii\helpers\Json
{
    public static function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}