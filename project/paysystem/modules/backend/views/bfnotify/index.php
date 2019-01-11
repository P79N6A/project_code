<?php

use yii\widgets\LinkPager;

$this->title = "支付系统";
$status      = app\models\open\BfClientNotify::getStatus();
?>
<div class="col-lg-12 ui-sortable">
    <div class="box ui-sortable-handle" style="position: relative; left: 0px; top: 0px;">
        <header>
            <h5>宝付通知管理</h5>
        </header>
        <div class="body">
        <form action="/backend/bfnotify/index" method="GET" class="form-inline">
                <div class="row form-group">
                    <div class="col-lg-4">
                        <input size="18" class="form-control" value="<?=isset($remit_id)?$remit_id:''?>" name="remit_id" placeholder="出款ID"  type="text">
                    </div>
                    
                </div>
               
                <button type="submit" class="btn btn-primary">搜索</button>
            </form>
            <br>
            <table class="table table-bordered table-condensed table-hover table-striped dataTable no-footer">
                <thead>
                    <tr role="row">
                        <th width='50px'>ID</th>
                        <th width='120px'>出款ID</th>
                        <th width='120px'>通知次数</th>
                        <th width='120px'>通知状态</th>
                        <th width='120px'>下次通知时间</th>
                        <th width='120px'>通知失败原因</th>
                        <th width='120px'>创建时间</th>
                        <th width='120px'>操作</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($res)): ?>
                    <?php foreach ($res as $key => $val): ?>
                        <tr role="row" class="even">
                            <td><?= $val['id'] ?></td>
                            <td><?= $val['remit_id'] ?></td>
                            <td><?= $val['notify_num'] ?></td>
                            <td><?php echo isset($status[$val['notify_status']]) ? $status[$val['notify_status']] : '未知' ?></td>
                            <td><?= $val['notify_time']?></td>
                            <td><?= $val['reason']?></td>
                            <td><?= $val['create_time']?></td>
                            <td>
                                <a href="/backend/bfnotify/update?id=<?= $val['id'] ?>">
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
</script>