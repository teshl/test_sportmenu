<?php

namespace app\models;

use Yii;
use yii\helpers\Url;

/**
 * This is the model class for table "token".
 *
 * @property integer $id
 * @property string $email
 * @property string $code
 * @property integer $created_at
 * @property integer $type
 */
class Token extends \yii\db\ActiveRecord
{
    const TYPE_CONFIRMATION = 0;
    const TYPE_RECOVERY = 1;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'token';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['email', 'code', 'created_at'], 'required'],
            [['created_at'], 'integer'],
            [['email'], 'email'],
            [['email'], 'string', 'max' => 255],
            [['code'], 'string', 'max' => 32],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'email' => 'Email',
            'code' => 'Code',
            'created_at' => 'Created At',
        ];
    }

    // создаёт токен для подтверждения почты
    public static function createConfirmEmail($email)
    {
        // проверяем выдана ли ссылка для этой почты
        // если выдана пересоздаём данные
        $token = Token::find()
            ->where(['email' => $email])
            ->one();

        if(!$token) {
            $token = new Token();
            $token->email = $email;
        }

        $token->type = Token::TYPE_CONFIRMATION;
        $token->created_at = time();
        $token->setCode(Yii::$app->request->userIP . $token->email . $token->created_at);

        return $token->save() ? $token : false;
    }

    public function getUrl()
    {
        $route = '';
        switch ($this->type) {
            case self::TYPE_CONFIRMATION:
                $route = '/registration/confirm';
                break;
            case self::TYPE_RECOVERY:
                $route = '/recovery/reset';
                break;
        }

        return Url::to([$route, 'id' => $this->id, 'code' => $this->code], true);
    }

    public function setCode( $str ){
        $this->code = hash('md5', $str );
    }
}
