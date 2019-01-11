<?php

use yii\widgets\LinkPager;

$this->title = "支付系统";
$status      = \app\models\ChannelBank::getStatus();
?>
<div class="col-lg-12 ui-sortable">
    <div class="box ui-sortable-handle" style="position: relative; left: 0px; top: 0px;">
        <header>
            <h5>支付通道银行卡列表</h5>
            <a href="/backend/channel"><label style="float:right" class="btn btn-primary">返回</label></a>
        </header>
        <div class="body">
            <table class="table table-bordered table-condensed table-hover table-striped dataTable no-footer">
                <thead>
                    <tr role="row">
                        <th width='50px'>ID</th>
                        <th width='120px'>标准银行名称</th>
                        <th width='120px'>银行名称</th>
                        <th width='120px'>银行编号</th>
                        <th width='120px'>卡类型</th>
                        <th width='120px'>状态</th>
                        <th width='120px'>单笔限额</th>
                        <th width='120px'>日限额</th>
                        <th width='120px'>日限数</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($res)): ?>
                    <?php foreach ($res as $key => $val): ?>
                        <tr role="row" class="even">
                            <td><?= $val['id'] ?></td>
                            <td><?= $val['std_bankname'] ?></td>
                            <td><?= $val['bankname'] ?></td>
                            <td><?= $val['bankcode'] ?></td>
                            <td><?= $val['card_type'] ?></td>
                            <td><?php echo isset($status[$val['status']]) ? $status[$val['status']] : '未知' ?></td>
                            <td><?= $val['limit_max_amount'] ?></td>
                            <td><?= $val['limit_day_amount'] ?></td>
                            <td><?= $val['limit_day_total'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>                
            </table>
        </div>
    </div>
</div>