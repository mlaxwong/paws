<?php
namespace paws\models\query;

use yii\db\ActiveQuery;
use yii\db\Query;
use yii\db\ActiveQueryInterface;
use yii\db\Expression;
use paws\records\EntryType;
use paws\records\EntryValue;

class EntryQuery extends ActiveQuery implements ActiveQueryInterface
{
    public $primarySelect = ['id', 'created_at', 'updated_at', 'entry_type_id'];

    public $modelClass;

    protected $_entryType;

    // public $primaryModel;

    // public $sql;

    public function type($entryType)
    {
        if ($entryType instanceof EntryType) {
            $this->_entryType = $entryType;
        } elseif (is_integer($entryType)) {
            $this->_entryType = EntryType::findOne((int) $entryType);
        } elseif (is_string($entryType)) {
            $this->_entryType = EntryType::find()
                ->andWhere(['handle' => $entryType])
                ->one();
        }
        return $this;
    }

    public function getEntryType(): EntryType
    {
        return $this->_entryType;
    }

    public function asArray($value = true)
    {

    }

    public function with()
    {

    }

    public function via($relationName, ?callable $callable = NULL)
    {

    }

    public function findFor($name, $model)
    {

    }

    public function one($db = null)
    {
        $row = parent::one($db);
        if ($row !== false) {
            $models = $this->populate([$row]);
            return reset($models) ?: null;
        }

        return null;
    }

    public function createCommand($db = null)
    {
        $modelClass = $this->modelClass;
        if ($db === null) {
            $db = $modelClass::getDb();
        }

        if ($this->sql === null) {
            list($sql, $params) = $db->getQueryBuilder()->build($this);
        } else {
            $sql = $this->sql;
            $params = $this->params;
        }
        
        $command = $db->createCommand($sql, $params);
        $this->setCommandCache($command);

        return $command;
    }

    public function prepare($builder)
    {
        if (!empty($this->joinWith)) {
            $this->buildJoinWith();
            $this->joinWith = null;
        }

        if (empty($this->from)) {
            $this->from = [$this->getPrimaryTableName()];
        }

        if (empty($this->select) && !empty($this->join)) {
            list(, $alias) = $this->getTableNameAndAlias();
            $this->select = ["$alias.*"];
        }

        if (empty($this->select))
        {
            $modelClass = $this->modelClass;

            foreach ($this->primarySelect as $select)
            {
                $this->select[] = $modelClass::tableName() . '.' . $select;
            }

            $entryType = $this->getEntryType();
            foreach ($entryType->fields as $field)
            {
                $this->select[] = new Expression("(
                    SELECT `value` 
                    FROM " . EntryValue::tableName() . " 
                    WHERE `field_id` = '" . $field->id . "'
                    AND `entry_id` = " . $modelClass::tableName() . ".id
                ) AS `" . $field->handle . "`");
            }
        }
        
        if ($this->primaryModel === null) {
            // eager loading
            $query = Query::create($this);
        } else {
            // lazy loading of a relation
            $where = $this->where;

            if ($this->via instanceof self) {
                // via junction table
                $viaModels = $this->via->findJunctionRows([$this->primaryModel]);
                $this->filterByModels($viaModels);
            } elseif (is_array($this->via)) {
                // via relation
                /* @var $viaQuery ActiveQuery */
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

    protected function getPrimaryTableName()
    {
        $modelClass = $this->modelClass;
        return $modelClass::tableName();
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

    // private function buildJoinWith()
    // {
    //     $join = $this->join;
    //     $this->join = [];

    //     /* @var $modelClass ActiveRecordInterface */
    //     $modelClass = $this->modelClass;
    //     $model = $modelClass::instance();

    //     foreach ($this->joinWith as $config) {
    //         list($with, $eagerLoading, $joinType) = $config;
    //         $this->joinWithRelations($model, $with, $joinType);

    //         if (is_array($eagerLoading)) {
    //             foreach ($with as $name => $callback) {
    //                 if (is_int($name)) {
    //                     if (!in_array($callback, $eagerLoading, true)) {
    //                         unset($with[$name]);
    //                     }
    //                 } elseif (!in_array($name, $eagerLoading, true)) {
    //                     unset($with[$name]);
    //                 }
    //             }
    //         } elseif (!$eagerLoading) {
    //             $with = [];
    //         }

    //         $this->with($with);
    //     }

    //     // remove duplicated joins added by joinWithRelations that may be added
    //     // e.g. when joining a relation and a via relation at the same time
    //     $uniqueJoins = [];
    //     foreach ($this->join as $j) {
    //         $uniqueJoins[serialize($j)] = $j;
    //     }
    //     $this->join = array_values($uniqueJoins);

    //     if (!empty($join)) {
    //         // append explicit join to joinWith()
    //         // https://github.com/yiisoft/yii2/issues/2880
    //         $this->join = empty($this->join) ? $join : array_merge($this->join, $join);
    //     }
    // }

    // private function joinWithRelations($model, $with, $joinType)
    // {
    //     $relations = [];

    //     foreach ($with as $name => $callback) {
    //         if (is_int($name)) {
    //             $name = $callback;
    //             $callback = null;
    //         }

    //         $primaryModel = $model;
    //         $parent = $this;
    //         $prefix = '';
    //         while (($pos = strpos($name, '.')) !== false) {
    //             $childName = substr($name, $pos + 1);
    //             $name = substr($name, 0, $pos);
    //             $fullName = $prefix === '' ? $name : "$prefix.$name";
    //             if (!isset($relations[$fullName])) {
    //                 $relations[$fullName] = $relation = $primaryModel->getRelation($name);
    //                 $this->joinWithRelation($parent, $relation, $this->getJoinType($joinType, $fullName));
    //             } else {
    //                 $relation = $relations[$fullName];
    //             }
    //             /* @var $relationModelClass ActiveRecordInterface */
    //             $relationModelClass = $relation->modelClass;
    //             $primaryModel = $relationModelClass::instance();
    //             $parent = $relation;
    //             $prefix = $fullName;
    //             $name = $childName;
    //         }

    //         $fullName = $prefix === '' ? $name : "$prefix.$name";
    //         if (!isset($relations[$fullName])) {
    //             $relations[$fullName] = $relation = $primaryModel->getRelation($name);
    //             if ($callback !== null) {
    //                 call_user_func($callback, $relation);
    //             }
    //             if (!empty($relation->joinWith)) {
    //                 $relation->buildJoinWith();
    //             }
    //             $this->joinWithRelation($parent, $relation, $this->getJoinType($joinType, $fullName));
    //         }
    //     }
    // }

    // private function getJoinType($joinType, $name)
    // {
    //     if (is_array($joinType) && isset($joinType[$name])) {
    //         return $joinType[$name];
    //     }

    //     return is_string($joinType) ? $joinType : 'INNER JOIN';
    // }

    // private function joinWithRelation($parent, $child, $joinType)
    // {
    //     $via = $child->via;
    //     $child->via = null;
    //     if ($via instanceof self) {
    //         // via table
    //         $this->joinWithRelation($parent, $via, $joinType);
    //         $this->joinWithRelation($via, $child, $joinType);
    //         return;
    //     } elseif (is_array($via)) {
    //         // via relation
    //         $this->joinWithRelation($parent, $via[1], $joinType);
    //         $this->joinWithRelation($via[1], $child, $joinType);
    //         return;
    //     }

    //     list($parentTable, $parentAlias) = $parent->getTableNameAndAlias();

    //     $childReflect = new \ReflectionClass($child);
    //     $method = $childReflect->getMethod('getTableNameAndAlias');
    //     $method->setAccessible(true);
    //     list($childTable, $childAlias) = $method->invokeArgs($child, []);

    //     if (!empty($child->link)) {
    //         if (strpos($parentAlias, '{{') === false) {
    //             $parentAlias = '{{' . $parentAlias . '}}';
    //         }
    //         if (strpos($childAlias, '{{') === false) {
    //             $childAlias = '{{' . $childAlias . '}}';
    //         }

    //         $on = [];
    //         foreach ($child->link as $childColumn => $parentColumn) {
    //             $on[] = "$parentAlias.[[$parentColumn]] = $childAlias.[[$childColumn]]";
    //         }
    //         $on = implode(' AND ', $on);
    //         if (!empty($child->on)) {
    //             $on = ['and', $on, $child->on];
    //         }
    //     } else {
    //         $on = $child->on;
    //     }
    //     $this->join($joinType, empty($child->from) ? $childTable : $child->from, $on);

    //     if (!empty($child->where)) {
    //         $this->andWhere($child->where);
    //     }
    //     if (!empty($child->having)) {
    //         $this->andHaving($child->having);
    //     }
    //     if (!empty($child->orderBy)) {
    //         $this->addOrderBy($child->orderBy);
    //     }
    //     if (!empty($child->groupBy)) {
    //         $this->addGroupBy($child->groupBy);
    //     }
    //     if (!empty($child->params)) {
    //         $this->addParams($child->params);
    //     }
    //     if (!empty($child->join)) {
    //         foreach ($child->join as $join) {
    //             $this->join[] = $join;
    //         }
    //     }
    //     if (!empty($child->union)) {
    //         foreach ($child->union as $union) {
    //             $this->union[] = $union;
    //         }
    //     }
    // }

    // private function getTableNameAndAlias()
    // {
    //     if (empty($this->from)) {
    //         $tableName = $this->getPrimaryTableName();
    //     } else {
    //         $tableName = '';
    //         foreach ($this->from as $alias => $tableName) {
    //             if (is_string($alias)) {
    //                 return [$tableName, $alias];
    //             }
    //             break;
    //         }
    //     }

    //     if (preg_match('/^(.*?)\s+({{\w+}}|\w+)$/', $tableName, $matches)) {
    //         $alias = $matches[2];
    //     } else {
    //         $alias = $tableName;
    //     }

    //     return [$tableName, $alias];
    // }
}