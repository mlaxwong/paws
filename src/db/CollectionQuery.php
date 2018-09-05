<?php
namespace paws\db;;

use yii\db\ActiveQuery;
use yii\db\ActiveQueryInterface;
use yii\db\Query;
use yii\db\Expression;

class CollectionQuery extends ActiveQuery implements ActiveQueryInterface
{
    protected $_type;

    public function type($type)
    {
        $modelClass = $this->modelClass;
        $typeClass = $modelClass::collectionTypeRecord();

        if ($type instanceof $typeClass) {
            $this->_type = $type;
        } elseif (is_integer($type)) {
            $this->_type = $typeClass::findOne((int) $type);
        } elseif (is_string($type)) {
            $this->_type = $typeClass::find()
                ->andWhere(['handle' => $type])
                ->one();
        }
        return $this;
    }

    public function prepare($builder)
    {
        if (!empty($this->joinWith)) {
            $this->buildJoinWith();
            $this->joinWith = null;
        }

        if (empty($this->from)) $this->from = [$this->getPrimaryTableName()];

        if (empty($this->select) && !empty($this->join)) {
            list(, $alias) = $this->getTableNameAndAlias();
            $this->select = ["$alias.*"];
        }

        if (empty($this->select))
        {
            $modelClass = $this->modelClass;
            $collectionClass = $modelClass::collectionRecord();
            $valueClass = $modelClass::collectionValueRecord();
            $baseAttributes = (new $modelClass)->getBaseAttributes(); 
            foreach ($baseAttributes as $select)
            {
                $this->select[] = $collectionClass::tableName() . '.' . $select;
            }

            $type = $this->getType();
            if ($type)
            {
                foreach ($type->fields as $field)
                {
                    $this->select[] = new Expression("(SELECT `value` FROM " . $valueClass::tableName() . " WHERE `" . $modelClass::fkFieldId() . "` = '" . $field->id . "' AND `" . $modelClass::fkCollectionId() . "` = " . $collectionClass::tableName() . ".id) AS `" . $field->handle . "`");
                }
            }
        }
        
        if ($this->primaryModel === null) {
            $query = Query::create($this);
        } else {
            $where = $this->where;

            if ($this->via instanceof self) {
                $viaModels = $this->via->findJunctionRows([$this->primaryModel]);
                $this->filterByModels($viaModels);
            } elseif (is_array($this->via)) {
                list($viaName, $viaQuery) = $this->via;
                if ($viaQuery->multiple) {
                    $viaModels = $viaQuery->all();
                    $this->primaryModel->populateRelation($viaName, $viaModels);
                } else {
                    $model = $viaQuery->one();
                    $this->primaryModel->populateRelation($viaName, $model);
                    $viaModels = $model === null ? [] : [$model];
                }
                $this->filterByModels($viaModels);
            } else {
                $this->filterByModels([$this->primaryModel]);
            }

            $query = Query::create($this);
            $this->where = $where;
        }

        if (!empty($this->on)) {
            $query->andWhere($this->on);
        }

        return $query;
    }

    protected function createModels($rows)
    {
        if ($this->asArray) {
            return $rows;
        } else {
            $models = [];
            /* @var $class ActiveRecord */
            $class = $this->modelClass;
            foreach ($rows as $row) {
                $model = $class::instantiate($row);
                $modelClass = get_class($model);
                $modelClass::populateRecord($model, $row);
                $models[] = $model;
            }
            return $models;
        }
    }

    protected function getPrimaryTableName()
    {
        $modelClass = $this->modelClass;
        $collectionClass = $modelClass::collectionRecord();
        return $collectionClass::tableName();
    }

    public function getType()
    {
        return $this->_type;
    }
}  