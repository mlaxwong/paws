<?php 
namespace paws\base;

use yii\base\Component;

abstract class Field extends Component
{
    abstract static function displayName(): string;
}