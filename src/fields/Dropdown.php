<?php
namespace paws\fields;

use Paws;
use paws\base\Field;

class Dropdown extends Field
{
    public static function displayName(): string
    {
        return Paws::t('app', 'Dropdown');
    }

    public function getInputHtml()
    {
        
    }
}