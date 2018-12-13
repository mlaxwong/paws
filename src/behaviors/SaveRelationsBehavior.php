<?php
namespace paws\behaviors;

use yii\db\BaseActiveRecord;
use paws\helpers\Json;
use paws\behaviors\BaseSaveRelationsBehavior;

class SaveRelationsBehavior extends BaseSaveRelationsBehavior
{
    public function __set($name, $value)
    {
        if (in_array($name, $this->relations) && Json::isJson($value)) $value = Json::decode($value);
        parent::__set($name, $value);
    }

    protected function _prepareHasManyRelation(BaseActiveRecord $model, $relationName)
    {
        $errors = [];
        foreach ($model->{$relationName} as $i => $relationModel) 
        {
            $this->validateRelationModel(self::prettyRelationName($relationName, $i), $relationName, $relationModel);
            if ($relationModel->errors) 
            {
                $error = [];
                foreach ($relationModel->errors as $key => $relationModelErrors)
                {
                    if (isset($relationModelErrors[0])) $error[$key] = $relationModelErrors[0];
                }
                if ($error) $errors[$i] = ['index' => $i, 'errors' => $error];
            }
        }
        if ($errors) 
        {
            $model->clearErrors($relationName);
            $model->addError($relationName, Json::encode($errors));
        }
        
    }
}