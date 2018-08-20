<?php
namespace paws\behaviors;

use yii\base\Behavior;
use yii\base\InvalidConfigException;
use Paws;
use paws\records\Group;

class GroupAbleBehavior extends Behavior
{
    /**
     * @return array|\yii\db\Query
     */
    public function getGroups()
    {
        $owner = $this->owner;
        if ($owner && $owner instanceof \yii\db\ActiveRecord) {
            return $owner->hasMany(Group::class, ['id' => 'group_id'])
                ->viaTable('{{%group_map}}', ['model_id' => 'id'])
                ->andOnCondition(['model_class' => get_class($owner)]);
        } else {
            return [];
        }
    }
}