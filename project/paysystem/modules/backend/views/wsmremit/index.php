<?php

use yii\widgets\LinkPager;

$this->title = "支付系统";
$status      = \app\models\wsm\WsmRemit::getStatus();

?>
<div class="col-lg-12 ui-sortable">
    <div class="box ui-sortable-handle" style="position: relative; left: 0px; top: 0px;">
        <header>
            <h5>微神马出款</h5>
        </header>
        <div class="body">
            <form action="/backend/wsmremit/index" method="GET" class="form-inline">
                <div class="row form-group">
                    <div class="col-lg-2" style="width: auto">
                        <input size="15" class="form-control" value="<?=isset($get['req_id'])?$get['req_id']:''?>" name="req_id" placeholder="订单号"  type="text">
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
                    <!--
                    <div class="col-lg-2" style="width: auto">
                        <select class="form-control" name="aid" tabindex="15" style="width: 160px;">
                            <option value="">选择业务</option>
                            <?php foreach($business as $k => $v): ?>
                                <option <?php echo isset($get['aid']) && $get['aid']==$v['id']? 'selected':'' ?> value="<?=$v['id']?>"><?=$v['name']?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    -->
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
                        <th width="10%">通道</th>
                        <th width="10%">订单号</th>
                        <th width="17%">银行信息</th>
                        <th width="5%">电话</th>
                        <th width="6%">交易金额</th>
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
                                <td>微神马</td>
                                <td><?= $val['req_id'] ?></td>
                                <td><?php
                                        $tip = json_decode($val['tip'],true);
                                        echo $val['guest_account']."<br />".$val['realname']
                                    ?></td>
                                <td><?= $val['user_mobile'] ?></td>
                                <td><?= $val['settle_amount'] ?></td>
                                <td><?= $val['query_time'] ?></td>
                                <td><?= $val['query_num'] ?></td>
                                <td><?php echo isset($status[$val['remit_status']]) ? $status[$val['remit_status']] : '未知' ?></td>
                                <td>
                                    <a href="/backend/wsmremit/update?id=<?= $val['id'] ?>">
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