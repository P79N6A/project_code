<?php

use yii\widgets\LinkPager;

$this->title = "支付系统";
$fund      = \app\models\policy\ZhanPolicy::getFund();
$status      = \app\models\policy\ZhanPolicy::getRemitStatus();
$paystatus      = \app\models\policy\ZhanPolicy::getPayStatus();

?>
<div class="col-lg-12 ui-sortable">
    <div class="box ui-sortable-handle" style="position: relative; left: 0px; top: 0px;">
        <header>
            <h5>保单列表</h5>
        </header>
        <div class="body">
            <form action="/policyment/policy/index1" method="GET" class="form-inline">
                <div class="row form-group">
                    <div class="col-lg-2" style="width: auto">
                        <input size="15" class="form-control" value="<?=isset($get['req_id'])?$get['req_id']:''?>" name="req_id" placeholder="订单号"  type="text">
                    </div>
                    <div class="col-lg-2" style="width: auto">
                        <input size="15" class="form-control" value="<?=isset($get['client_id'])?$get['client_id']:''?>" name="client_id" placeholder="流水号" type="text">
                    </div>
                    <div class="col-lg-2" style="width: auto">
                        <input  size="15" class="form-control" value="<?=isset($get['user_mobile'])?$get['user_mobile']:''?>" name="user_mobile" placeholder="电话" type="text">
                    </div>
                    <div class="col-lg-2" style="width: auto">
                        <select class="form-control" name="remit_status" tabindex="14" style="width: 150px;">
                            <option value="">保单状态</option>
                            <?php foreach($status as $k => $v): ?>
                                <option <?php echo isset($get['remit_status']) && $get['remit_status']!=='' && $get['remit_status']==$k? 'selected':'' ?> value="<?=$k?>"><?=$v?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-2" style="width: auto">
                        <select class="form-control" name="pay_status" tabindex="14" style="width: 100px;">
                            <option value="">支付状态</option>
                            <?php foreach($paystatus as $k => $v): ?>
                                <option <?php echo isset($get['pay_status']) && $get['pay_status']!=='' && $get['pay_status']==$k? 'selected':'' ?> value="<?=$k?>"><?=$v?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-2" style="width: auto">
                        <select class="form-control" name="fund" tabindex="14" style="width: 100px;">
                            <option value="">资金方</option>
                            <?php foreach($fund as $k => $v): ?>
                                <option <?php echo isset($get['fund']) && $get['fund']!=='' && $get['fund']==$k? 'selected':'' ?> value="<?=$k?>"><?=$v?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-lg-1" style="width: auto">
                        <button type="submit" class="btn btn-primary">搜索</button>
                    </div>
                </div>

            </form>
            <br>
            <table class="table table-bordered table-condensed table-hover table-striped dataTable no-footer">
                <thead>
                    <tr role="row">
                        <th width="3%">ID</th>
                        <th width="3%">Aid</th>
                        <th width="10%">订单号</th>
                        <th width="10%">流水号</th>
                        <th width="7%">姓名</th>
                        <th width="5%">手机号</th>
                        <th width="5%">保额</th>
                        <th width="5%">保费</th>
                        <th width="8%">保险期间</th>
                        <th width="7%">资金方</th>
                        <th width="10%">创建时间</th>
                        <th width="10%">保单状态</th>
                        <th width="10%">支付状态</th>
                        <th width="7%">操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($res)): ?>
                        <?php foreach ($res as $key => $val): ?>
                            <tr role="row" class="even">
                                <td><?= $val['id'] ?></td>
                                <td><?= $val['aid']?></td>
                                <td><?= $val['req_id'] ?></td>
                                <td><?= $val['client_id'] ?></td>
                                <td><?= $val['user_name'] ?></td>
                                <td><?= $val['user_mobile'] ?></td>
                                <td><?= $val['sumInsured'] ?></td>
                                <td><?= $val['premium'] ?></td>
                                <td><?= $val['policyDate'] ?></td>
                                <td><?php echo isset($fund[$val['fund']]) ? $fund[$val['fund']] : '未知' ?></td>
                                <td><?= $val['create_time'] ?></td>
                                <td><?php echo isset($status[$val['remit_status']]) ? $status[$val['remit_status']] : '未知' ?></td>
                                <td><?php echo isset($paystatus[$val['pay_status']]) ? $paystatus[$val['pay_status']] : '未知' ?></td>
                                <td><a href="/policyment/policy/detail?id=<?= $val['id']?>">详情</a></td>
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