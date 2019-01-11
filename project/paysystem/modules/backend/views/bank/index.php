<?php

use yii\widgets\LinkPager;

$this->title = "支付系统";
$status      = \app\models\ChannelBank::getStatus();
?>
<div class="col-lg-12 ui-sortable">
    <div class="box ui-sortable-handle" style="position: relative; left: 0px; top: 0px;">
        <header>
            <h5>银行列表</h5>
            <a href="/backend/bank/add"><label style="float:right" class="btn btn-primary">添加</label></a>
        </header>
        <div class="body">
         <form action="/backend/bank/index" method="GET" class="form-inline">
        <div class="row form-group">
                    <div class="col-lg-6">
                        <select class="form-control" name="pay_chan" tabindex="4">
                            <option value="">选择通道</option>
                            <?php foreach($pay_chanlist as $val): ?>
                                <option value="<?= $val['id'] ?>" <?php if($pay_chan==$val['id']){?> selected <?php } ?>><?=$val['product_name']?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-6">
                        <select class="form-control" name="bankname" tabindex="4">
                            <option value="">选择银行</option>
                            <?php foreach($banklist as $val): ?>
                                <option value="<?= $val['std_bankname'] ?>" <?php if($bankname==$val['std_bankname']){?> selected <?php } ?>><?=$val['std_bankname']?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                 <button type="submit" class="btn btn-primary">搜索</button>
                 </form>
                 <br>
            <table class="table table-bordered table-condensed table-hover table-striped dataTable no-footer">
                <thead>
                    <tr role="row">
                        <th>ID</th>
                        <th>渠道</th>
                        <th>标准银行名称</th>
                        <th>银行名称</th>
                        <th>银行编号</th>
                        <th>卡类型</th>
                        <th>状态</th>
                        <th>单笔限额</th>
                        <th>日限额</th>
                        <th>日限数</th>
                        <th>修改</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($res)): ?>
                    <?php foreach ($res as $key => $val): ?>
                        <tr role="row" class="even">
                            <td><?= $val['id'] ?></td>
                            <td><?= $val->channel->product_name ?></td>
                            <td><?= $val['std_bankname'] ?></td>
                            <td><?= $val['bankname'] ?></td>
                            <td><?= $val['bankcode'] ?></td>
                            <td><?php if($val['card_type']==1){echo '储蓄卡';}else if($val['card_type']==2){echo '信用卡';} ?></td>
                            <td><?php echo isset($status[$val['status']]) ? $status[$val['status']] : '未知' ?></td>
                            <td><?= $val['limit_max_amount'] ?></td>
                            <td><?= $val['limit_day_amount'] ?></td>
                            <td><?= $val['limit_day_total'] ?></td>
                            <td>
                                <a href="/backend/bank/update?id=<?= $val['id'] ?>">
                                    <label class="btn btn-primary">修改</label>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>              
            </table>
            <div class="panel_pager">
            <?php echo LinkPager::widget(['pagination' => $pages]); ?>
            </div>
        </div>
    </div>
</div>

<script src="/bootstrap/js/bootstrap.min.js" type="text/javascript" charset="utf-8"></script>
<script>
    $('.showtip').popover();
    function query(){
        var pay_chan = $("#pay_chan").val();
        window.location.href = '/backend/bank/index?pay_chan='+pay_chan;
    }
</script>