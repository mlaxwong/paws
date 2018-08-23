<?php
namespace paws\base;

use ReflectionClass;
use yii\base\Model;
use yii\base\ModelEvent;
use yii\db\ActiveRecord;
use yii\db\ActiveQueryInterface;

abstract class Entity extends Model
{
    /**
     * @var string
     */
    public $recordClass;

    /**
     * @var string
     */
    public $queryNamespace = '\\paws\\entities\\query';

    /**
     * @var string
     */
    public $queryClassSubfix = 'Query';

    /**
     * @return ActiveQueryInterface
     */
    public function find(): ActiveQueryInterface
    {
        $queryClass = $this->getQueryClass();
        $recordClass = $this->recordClass;
        return class_exists($queryClass) ?  new $queryClass($recordClass) : $recordClass::find();
    }

    /**
     * @return string
     */
    public function getQueryClass(): string
    {
        $reflect = $this->getReflect();
        $shortName = $reflect->getShortName();
        return $this->queryNamespace . '\\' . $shortName  . $this->queryClassSubfix;
    }

    /**
     * @return ReflectionClass
     */
    protected function getReflect(): ReflectionClass
    {
        return new ReflectionClass($this);
    }
}