<?php
namespace paws\entities;

use paws\base\Entity;
use paws\records\Field as FieldRecord;

class Field extends Entity
{
    public static function recordClass(): string
    {
        return FieldRecord::class;
    }
}