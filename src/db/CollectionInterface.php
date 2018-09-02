<?php
namespace paws\db;

use paws\db\RecordInterface;

interface CollectionInterface extends RecordInterface
{
    public static function collectionRecord();

    public static function collectionTypeRecord();

    public static function collectionValueRecord();

    public static function typeAttribute();
}