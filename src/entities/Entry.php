<?php
namespace paws\entities;

use paws\base\Entity;
use paws\records\Entry as EntryRecord;

class Entry extends Entity
{
    public $recordClass = EntryRecord::class;
}