<?php
namespace paws\base;

use ReflectionClass;
use yii\base\Model;
use yii\db\ActiveQueryInterface;
use paws\entities\query\EntityQuery;

abstract class Entity extends Model
{
    /**
     * @return string
     */
    abstract static function recordClass(): string;

    /**
     * @return ActiveQueryInterface
     */
    public static function find(): ActiveQueryInterface
    {
        $reflect = new ReflectionClass(static::class);
        if (!$reflect->isAbstract()) {
            $recordClass = static::recordClass();
            $queryClass = class_exists(static::getQueryClass()) ? static::getQueryClass() : $recordClass::find();
        } else {
            $queryClass = EntityQuery::class;
        }
        return new $queryClass(static::class);
    }

    /**
     * @return string
     */
    public static function getQueryClass(): string
    {
        $reflect = new ReflectionClass(static::class);
        return 'paws\\entities\query\\' . $reflect->getShortName() . 'Query';
    }
}