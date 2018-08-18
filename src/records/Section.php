<?php
namespace paws\records;

use yii\db\ActiveRecord;

class Section extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%section}}';
    }
    
    public function rules(): array
    {
        return [
            [['name', 'handle'], 'required'],
            [['name', 'handle'], 'string', 'max' => 256],
        ];
    }
}