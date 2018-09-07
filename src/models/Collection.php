<?php
namespace paws\models;

use paws\db\Collection as BaseCollection;
use paws\records\Collection as CollectionRecord;

class Collection extends BaseCollection
{
    public static function collectionRecord()
    {
        return CollectionRecord::class;
    }
}