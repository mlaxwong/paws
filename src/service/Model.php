<?php 
namespace paws\service;

use yii\base\Component;
use yii\base\UnknownMethodException;
use yii\helpers\Inflector;
use yii\db\ActiveRecord;
use yii\db\ActiveQuery;

class Model extends Component
{
    public $classNamespaces = [
        'paws\\models',
        'paws\\records',
    ];

    public function __call($name, $params)
    {
        try {
            parent::__call($name, $params);
        } catch (UnknownMethodException $ex) {
            if ($class = $this->getClass($name)) return $this->getQuery($class);
            throw $ex;
        }
    }

    protected function getClass($name)
    {
        foreach ($this->classNamespaces as $classNamespace)
        {
            $class = $classNamespace . '\\' . Inflector::classify($name);
            if (class_exists($class) && (new $class) instanceof ActiveRecord) return $class;
        }
        return null;
    }

    protected function getQuery(string $class)
    {
        return $class::find();
    }
}