<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * @property string $username
 */
class ProfileForm extends Model
{
    public $username;

    public function rules()
    {
        return [
            ['username', 'filter', 'filter' => 'trim'],
            ['username', 'required'],
            ['username', 'match', 'pattern' => '/^[-a-zA-Z0-9_А-Яа-яЁё]+$/'],
            ['username', 'string', 'min' => 3, 'max' => 255],
        ];
    }

    /** @inheritdoc */
    public function attributeLabels()
    {
        return [
            'username' => Yii::t('app', 'Name'),
        ];
    }
    
    /** @inheritdoc */
    public function formName()
    {
        return 'profile-form';
    }

    public function __construct()
    {
        parent::__construct();

        $this->username = Yii::$app->user->identity->username;
    }

    public function save()
    {
        if (!$this->validate()) {
            return false;
        }

        $user = Yii::$app->user->identity;

        $user->username = $this->username;

        if($user->save()) {
            return true;
        }

        return false;
    }
}
