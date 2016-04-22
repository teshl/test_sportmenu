<?php

namespace app\models;

use helpers\Password;
use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use yii\base\NotSupportedException;

/**
 * This is the model class for table "user".
 *
 * @property integer $id
 * @property string $username
 * @property string $email
 * @property string $password_hash
 * @property string $auth_key
 * @property integer $created_at
 */
class User extends ActiveRecord implements IdentityInterface
{
    /** @inheritdoc */
    public static function tableName()
    {
        return 'user';
    }

    /** @inheritdoc */
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
            'username' => Yii::t('app','Name'),
            'email' => Yii::t('app','Email'),
        ];
    }
    
    public function create($tokenId, $tokenCode)
    {
        $token = Token::find()
            ->where(['id' =>$tokenId, 'code' => $tokenCode, 'type'=>Token::TYPE_CONFIRMATION])
            ->one();

        if(!$token){
            return false;
        }

        $this->username = explode ('@', $token->email )[0];
        $password = Yii::$app->security->generateRandomString(8);

        $this->email = $token->email;
        $this->created_at = time();
        $this->setPassword($password);
        $this->generateAuthKey();

        if (!$this->save()) {
            return false;
        }

        //удаляем данные по токену
        $token->delete();

        // отправка почты о успешной регистрации
        Yii::$app->mailer
            ->compose('create-user',[
                'password' => $password
            ])
            ->setFrom('teshl117@gmail.com')
            ->setTo($this->email)
            ->setSubject('Завершение регистрации')
            ->send();

        // залогиним пользователя
        Yii::$app->user->login($this, 0);
        
        return true;
    }

    /** установить пароль */
    public function setPassword($password){
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /** проверить пароль */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /** генерирует "Запомнить меня" ключ аутентификации */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /** @inheritdoc */
    public static function findIdentity($id){
        return static::findOne($id);
    }

    /** @inheritdoc */
    public static function findIdentityByAccessToken($token, $type = null){
        throw new NotSupportedException('Method "' . __CLASS__ . '::' . __METHOD__ . '" is not implemented.');
    }

    /** @inheritdoc */
    public function getId(){
        return $this->id;
    }

    /** @inheritdoc */
    public function getAuthKey(){
        return $this->getAttribute('auth_key');
    }

    /** @inheritdoc */
    public function validateAuthKey($authKey){
        return $this->getAttribute('auth_key') === $authKey;
    }
}
