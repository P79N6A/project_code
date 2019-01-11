<?php

use yii\widgets\LinkPager;
use yii\helpers\ArrayHelper;

$this->title = "支付系统";
$status      = \app\models\Payorder::getStatus();

?>
<div class="col-lg-12 ui-sortable">
    <div class="box ui-sortable-handle" style="position: relative; left: 0px; top: 0px;">
        <header>
            <h5>支付订单</h5>
        </header>
        <div class="body">
            <form action="/backend/order/index" method="GET" class="form-inline">
                <div class="row form-group">
                    <!-- <div class="col-lg-4">
                        <input size="18" class="form-control" value="<?=isset($get['other_orderid'])?$get['other_orderid']:''?>" name="other_orderid" placeholder="支付订单号"  type="text">
                    </div> -->
                    <div class="col-lg-2" style="width: auto">
                        <input size="15" class="form-control" value="<?=isset($get['orderid'])?$get['orderid']:''?>" name="orderid" placeholder="商户订单" type="text">
                    </div>
                    <div class="col-lg-2" style="width: auto">
                        <input  size="15" class="form-control" value="<?=isset($get['phone'])?$get['phone']:''?>" name="phone" placeholder="电话" type="text">
                    </div>
                    <div class="col-lg-2" style="width: auto">
                        <input size="15" class="form-control" value="<?=isset($get['channel_ids'])?$get['channel_ids']:''?>" name="channel_ids" placeholder="支付通道:108,107" type="text">
                    </div>
                    <div class="col-lg-2" style="width: auto">
                        <select class="form-control" name="channel_id" tabindex="14" style="width: 150px;">
                            <option value="">选择通道</option>
                            <?php foreach($channel as $k => $v): ?>
                                <option <?php echo isset($get['channel_id']) && $get['channel_id']==$v['id']? 'selected':'' ?> value="<?=$v['id']?>"><?=$v['product_name']?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <!-- /.col-lg-6 -->
                    <div class="col-lg-2" style="width: auto">
                        <select class="form-control" name="business_id" tabindex="14" style="width: 150px;">
                            <option value="">选择业务</option>
                            <?php foreach($business as $k => $v): ?>
                                <option <?php echo isset($get['business_id']) && $get['business_id']==$v['id']? 'selected':'' ?> value="<?=$v['id']?>"><?=$v['name']?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-2" style="width: auto">
                        <select class="form-control" name="status" tabindex="14" style="width: 100px;">
                            <option value="">选择状态</option>
                            <?php foreach($status as $k => $v): ?>
                                <option <?php echo isset($get['status']) && $get['status']!=='' && $get['status']==$k? 'selected':'' ?> value="<?=$k?>"><?=$v?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-2" style="width: auto">
                        <input size="15" class="form-control" onclick="laydate({istime: true, format: 'YYYY-MM-DD'})" value="<?=isset($get['start_date'])?$get['start_date']:''?>" name="start_date" placeholder="开始时间" type="text">
                        ~
                        <input size="15" class="form-control" onclick="laydate({istime: true, format: 'YYYY-MM-DD'})" value="<?=isset($get['end_date'])?$get['end_date']:''?>" name="end_date" placeholder="结束时间" type="text">
                    </div>
                    <div class="col-lg-1" style="width: auto">
                        <button type="submit" class="btn btn-primary">搜索</button>
                    </div>
                </div>
            </form>
            <br>
            <div>总条数：<?=number_format($totalAll)?></div>
            <table class="table table-bordered table-condensed table-hover table-striped dataTable no-footer">
                <thead>
                    <tr role="row">
                        <th>ID</th>
                        <th width='90px'>业务</th>
<!--                        <th width='60px'>通道</th>-->
                        <th width='100px'>通道（ID）</th>
                        <th width='90px'>商户订单</th>

                        <th>银行信息</th>
<!--                        <th>银行名称</th>-->
<!--                        <th>姓名</th>-->
                        <th>电话</th>
                        <th>交易金额</th>
                        <th>状态</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($res)): ?>
                        <?php foreach ($res as $key => $val): ?>
                            <tr role="row" class="even">
                                <td><?= $val['id'] ?></td>
                                <td><?= $business[$val['business_id']]->name ?>(<?=$val['business_id']?>)</td>
<!--                                <td></td>-->
                                <td><?= $channel[$val['channel_id']]->product_name ?>（<?= $val['channel_id']?>）</td>
                                <td><?= $val['orderid'] ?></td>
                              
                                <td><?= $val['cardno'] ?><br><?= $val['bankname'] ?><br><?= $val['name'] ?></td>
<!--                                <td></td>-->
<!--                                <td></td>-->
                                <td><?= $val['phone'] ?></td>
                                <td><?= $val['amount'] ?></td>
                                <td>
                                    <?=ArrayHelper::getValue($status, ArrayHelper::getValue($val, 'status'))?>
                                    <?php //echo isset($status[$val['status']]) ? $status[$val['status']] : '未知' ?>
                                </td>
                                <td>
                                    <a href="/backend/order/update?id=<?= $val['id'] ?>">
                                        <label class="btn btn-primary">详情</label>
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
<script src="/laydate/laydate.dev.js" type="text/javascript" charset="utf-8"></script>