<?php
use yii\helpers\Html;
?>
<div>
    <p>Ссылка для подтверждения регистрации:</p>

    <p><?= Html::a(Html::encode($registerLink), $registerLink) ?></p>
</div>