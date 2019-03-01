<?php
namespace paws\records;

use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use Paws;
use paws\behaviors\TimestampBehavior;

class User extends ActiveRecord implements IdentityInterface
{
    public $password;

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    public static function tableName()
    {
        return '{{%user}}';
    }

    public function rules()
    {
        return
        [
            [['username', 'email'], 'required', 'on' => 'default'],
            [['username', 'password_hash', 'email'], 'string', 'min' => 1, 'max' => 255],
            [['auth_key'], 'string', 'max' => 32],
        ];
    }

    public static function findByLogin($login)
    {
        return static::find()
            ->andWhere(['or', ['username' => $login], ['email' => $login]])
            ->one();
    }

    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
		return static::findOne(['auth_key' => $token]);
    }

    public function getId()
    {
        return $this->getPrimaryKey();
    }

    public function getAuthKey()
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    public function validatePassword($password)
    {
        return Paws::$app->security->validatePassword($password, $this->password_hash);
    }

    public function setPassword($password)
    {
        $this->password_hash = Paws::$app->security->generatePasswordHash($password);
    }

    public function generateAuthKey()
    {
        $this->auth_key = Paws::$app->security->generateRandomString();
    }
}