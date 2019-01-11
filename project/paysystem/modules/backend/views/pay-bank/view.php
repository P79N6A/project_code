<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\pay\PayBank */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Pay Banks', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pay-bank-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'channel_id',
            'std_bankname',
            'bankname',
            'bankcode',
            'card_type',
            'status',
            'limit_max_amount',
            'limit_day_amount',
            'limit_day_total',
            'limit_date',
            'limit_start_hour',
            'limit_end_hour',
            'create_time',
        ],
    ]) ?>

</div>
