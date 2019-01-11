<?php

use yii\widgets\LinkPager;

$this->title = "支付系统";
$status      = \app\models\BusinessChan::getStatus();
?>
<div class="col-lg-12 ui-sortable">
    <div class="box ui-sortable-handle" style="position: relative; left: 0px; top: 0px;">
        <header>
            <h5>业务通道排序</h5>
            <a href="/backend/business-chan/add"><label style="float:right" class="btn btn-primary">添加</label></a>
        </header>
        <div class="body">
        <form action="/backend/business-chan/index" method="GET" class="form-inline">
        <div class="row form-group">
                    <div class="col-lg-6">
                        <select class="form-control" name="pay_buss" tabindex="4">
                            <option value="">选择业务</option>
                            <?php foreach($pay_busslist as $val): ?>
                                <option value="<?= $val['id'] ?>" <?php if($pay_buss==$val['id']){?> selected <?php } ?>><?=$val['name']?></option>
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
                        <th width='50px'>ID</th>
                        <th width='100px'>应用ID</th>
                        <th>业务</th>
                        <th width='60px'>通道ID</th>
                        <th>通道名称</th>
                        <th width='70px'>排序</th>
                        <th width='80px'>状态</th>
                        <th width=''>添加时间</th>
                        <th width=''>修改</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($res)): ?>
                    <?php foreach ($res as $key => $val): ?>
                        <tr role="row" class="even">
                            <td><?= $val['id'] ?></td>
                            <td><?= $val->app->name ?></td>
                            <td><?= $val->business->name ?></td>
                            <td><?= $val['channel_id'] ?></td>
                            <td><?= $val->channel->product_name ?></td>
                            <td><?= $val['sort_num'] ?></td>
                            <td><?php echo isset($status[$val['status']]) ? $status[$val['status']] : '未知' ?></td>
                            <td><?= $val['create_time'] ?></td>
                            <td>
                                <a href="/backend/business-chan/update?id=<?= $val['id'] ?>">
                                    <label class="btn btn-primary">修改</label>
                                </a>
                                <a href="/backend/business-chan/sort?business_id=<?= $val['business_id'] ?>">
                                    <label class="btn btn-primary">排序</label>
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