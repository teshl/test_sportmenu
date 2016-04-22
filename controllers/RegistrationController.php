<?php

namespace app\controllers;

use app\models\User;
use Yii;
use app\models\RegisterForm;
use yii\filters\AccessControl;
use yii\web\HttpException;

class RegistrationController extends \yii\web\Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['confirm'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['register','confirm'],
                        'allow' => true,
                        'roles' => ['?'],
                    ]
                ],
            ]
        ];
    }
    
    public function actionRegister()
    {
        $model = new RegisterForm();

        if ($model->load(Yii::$app->request->post()) && $model->register()) {
            
            return $this->render('/message', [
                'message'  => Yii::t('app','Check your email. We send you a link to confirm.')
            ]);
        }

        return $this->render('register',[
            'model'  => $model
        ]);
    }

    public function actionConfirm()
    {
        $tokenId = Yii::$app->request->get('id',0);
        $tokenCode = Yii::$app->request->get('code','');

        if(!empty($tokenId) && !empty($tokenCode)) {
            $user = new User();
            if ($user->create($tokenId, $tokenCode)) {

                Yii::$app->session->setFlash('success', Yii::t('app', 'The password has been sent to your email.'));
                
                return $this->redirect('/profile/index', 302);
            } else {
                return $this->render('/message', [
                    'message' => Yii::t('app', 'You already have an account.')
                ]);
            }
        }else{
            throw new HttpException(404 ,'Incorrect link');
        }
    }
}
