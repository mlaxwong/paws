<?php
namespace paws\behaviors;

use yii\base\Behavior;
use yii\db\ActiveRecord;

class RelationLinkerBehavior extends Behavior
{
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'saveRelations',
            ActiveRecord::EVENT_AFTER_UPDATE => 'saveRelations',
        ];
    }

    public function saveRelations()
    {
        $primaryModel = $this->owner;
        $primaryModelPk = $primaryModel->getPrimaryKey();

    }
}