<?php
namespace paws\service;

use yii\base\Component;
use yii\base\UnknownMethodException;
use yii\helpers\Inflector;
use yii\db\ActiveRecord;
use yii\db\ActiveQuery;

class Record extends Component
{
    public $recordNamespace = 'paws\\records';

    public function __call($name, $params)
    {
        try {
            parent::__call($name, $params);
        } catch (UnknownMethodException $ex) {
            if ($recordClass = $this->getRecordClass($name)) return $this->getRecordQuery($recordClass);
            throw $ex;
        }
    }

    protected function getRecordClass($name)
    {
        $recordClass = $this->recordNamespace . '\\' . Inflector::classify($name);
        if (!class_exists($recordClass)) return null;
        if (! new $recordClass instanceof ActiveRecord) return null;
        return $recordClass;
    }

    protected function getRecordQuery(string $recordClass)
    {
        return $recordClass::find();
    }
}