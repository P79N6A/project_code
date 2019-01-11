<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\pay\PayBank */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="pay-bank-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'channel_id')->textInput() ?>

    <?= $form->field($model, 'std_bankname')->textInput(['maxlength' => 30]) ?>

    <?= $form->field($model, 'bankname')->textInput(['maxlength' => 30]) ?>

    <?= $form->field($model, 'bankcode')->textInput(['maxlength' => 30]) ?>

    <?= $form->field($model, 'card_type')->textInput() ?>

    <?= $form->field($model, 'status')->textInput() ?>

    <?= $form->field($model, 'limit_max_amount')->textInput(['maxlength' => 12]) ?>

    <?= $form->field($model, 'limit_day_amount')->textInput(['maxlength' => 12]) ?>

    <?= $form->field($model, 'limit_day_total')->textInput() ?>

    <?= $form->field($model, 'limit_date')->textInput(['maxlength' => 10]) ?>

    <?= $form->field($model, 'limit_start_hour')->textInput() ?>

    <?= $form->field($model, 'limit_end_hour')->textInput() ?>

    <?= $form->field($model, 'create_time')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
