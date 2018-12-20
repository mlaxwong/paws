<?php
namespace paws\helpers;

class StringHelper extends \yii\helpers\StringHelper
{
    public static function strtr($message, $params = [])
    {
        $placeholders = [];
        foreach ((array) $params as $name => $value) 
        {
            $placeholders['{' . $name . '}'] = $value;
        }
        return ($placeholders === []) ? $message : strtr($message, $placeholders);
    }
}