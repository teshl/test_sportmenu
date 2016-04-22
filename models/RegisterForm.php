<?php

namespace app\models;

use Yii;
use yii\base\Model;
use app\models\User;

class RegisterForm extends Model
{
    public $email;

    public function rules()
    {
        return [
            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => '\app\models\User'],
        ];
    }

    /** @inheritdoc */
    public function formName()
    {
        return 'Register-form';
    }

    public function register()
    {
        if (!$this->validate()) {
            return false;
        }

        // создаём токен для подтверждения почты
        $token = Token::createConfirmEmail($this->email);

        if ($token) {
            // отправка почты
            Yii::$app->mailer
                ->compose('register', [
                    'registerLink' => $token->getUrl()
                ])
                ->setFrom('teshl117@gmail.com')
                ->setTo($this->email)
                ->setSubject('Регистрация - подтвердите email')
                ->send();

            return true;
        }
        
        return false;
    }
}
