<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\pay\PayBank */

$this->title = 'Create Pay Bank';
$this->params['breadcrumbs'][] = ['label' => 'Pay Banks', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pay-bank-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
