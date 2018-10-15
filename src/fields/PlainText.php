<?php
namespace paws\fields;

use Paws;
use paws\base\Field;

class PlainText extends Field
{
    public $minLength = null;
    public $maxLength = null;

    public static function displayName()
    {
        return Paws::t('app', 'Plain Text');
    }

    public function rules()
    {
        return [
            [['minLength', 'maxLength'], 'integer'],
        ];
    }

    public function getInputHtml()
    {
        
    }
}