<?php

use yii\widgets\LinkPager;
use app\models\open\JfRemit;

$this->title = "支付系统";
$Cjf = new JfRemit();
$status = $Cjf->getStatus();

?>
<div class="col-lg-12 ui-sortable">
    <div class="box ui-sortable-handle" style="position: relative; left: 0px; top: 0px;">
        <header>
            <h5>玖富出款</h5>
        </header>
        <div class="body">
            <form action="/backend/jfremit/index" method="GET" class="form-inline">
                <div class="row form-group">
                    <div class="col-lg-2" style="width: auto">
                        <input size="15" class="form-control" value="<?=isset($get['req_id'])?$get['req_id']:''?>" name="req_id" placeholder="订单号"  type="text">
                    </div>
                    <div class="col-lg-2" style="width: auto">
                        <input size="15" class="form-control" value="<?=isset($get['order_id'])?$get['order_id']:''?>" name="order_id" placeholder="工单号" type="text">
                    </div>
                    <div class="col-lg-2" style="width: auto">
                        <input  size="15" class="form-control" value="<?=isset($get['user_mobile'])?$get['user_mobile']:''?>" name="user_mobile" placeholder="电话" type="text">
                    </div>
                    <div class="col-lg-2" style="width: auto">
                        <select class="form-control" name="remit_status" tabindex="15" style="width: 160px;">
                            <option value="">出款状态</option>
                            <?php foreach($status as $k => $v): ?>
                                    <option <?php echo isset($get['remit_status']) && ($get['remit_status']!=='') &&$get['remit_status']==$k? 'selected':'' ?> value="<?=$k?>"><?=$v?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-lg-2" style="width: auto">
                        <button type="submit" class="btn btn-primary">搜索</button>
                    </div>
                </div>

            </form>
            <br>
            <table class="table table-bordered table-condensed table-hover table-striped dataTable no-footer">
                <thead>
                    <tr role="row">
                        <th width="5%">ID</th>
                        <th width="10%">业务</th>
                        <th width="10%">订单号</th>
                        <th width="6%">工单号</th>
                        <th width="17%">银行信息</th>
                        <th width="5%">电话</th>
                        <th width="6%">交易金额</th>
                        <th width="12%">创建时间</th>
                        <th width="12%">查询时间</th>
                        <th width="5%">查询次数</th>
                        <th width="5%">状态</th>
                        <th width="5%">操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($res)): ?>
                        <?php foreach ($res as $key => $val): ?>
                            <tr role="row" class="even">
                                <td><?= $val['id'] ?></td>
                                <td><?= $business[$val['aid']]->name ?></td>
                                <td><?= $val['req_id'] ?></td>
                                <td><?= $val['order_id'] ?></td>
                                <td><?= $val['guest_account'] ?><br><?= $val['guest_account_bank'] ?><br><?= $val['guest_account_name'] ?></td>
                                <td><?= $val['user_mobile'] ?></td>
                                <td><?= $val['settle_amount'] ?></td>
                                <td><?= $val['create_time'] ?></td>
                                <td><?= $val['query_time'] ?></td>
                                <td><?= $val['query_num'] ?></td>
                                <td><?php echo isset($status[$val['remit_status']]) ? $status[$val['remit_status']] : '未知' ?></td>
                                <td>
                                    <a href="/backend/jfremit/update?id=<?= $val['id'] ?>">
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