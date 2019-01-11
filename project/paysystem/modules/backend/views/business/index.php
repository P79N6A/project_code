<?php

use yii\widgets\LinkPager;

$this->title = "支付系统";
$status      = \app\models\Business::getStatus();
?>
<div class="col-lg-12 ui-sortable">
    <div class="box ui-sortable-handle" style="position: relative; left: 0px; top: 0px;">
        <header>
            <h5>业务管理</h5>
            <a href="/backend/business/add"><label style="float:right" class="btn btn-primary">添加</label></a>
        </header>
        <div class="body">
            <table class="table table-bordered table-condensed table-hover table-striped dataTable no-footer">
                <thead>
                    <tr role="row">
                        <th>ID</th>
                        <th>aid</th>
                        <th>名称</th>
                        <th>业务号</th>
                        <th>说明</th>
                        <th>状态</th>
                        <th>创建时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($res)): ?>
                        <?php foreach ($res as $key => $val): ?>
                            <tr role="row" class="even">
                                <td><?= $val['id'] ?></td>
                                <td><?= $val['aid'] ?></td>
                                <td><?= $val['name'] ?></td>
                                <td><?= $val['business_code'] ?></td>
                                <td><a class="showtip" tabindex="10" class="btn btn-lg btn-danger" role="button" data-toggle="popover" data-trigger="focus" title="说明" data-content="<?php echo $val['tip'] ?>">查看</a></td>
                                <td><?php echo isset($status[$val['status']]) ? $status[$val['status']] : '未知' ?></td>
                                <td><?= $val['create_time'] ?></td>
                                <td>
                                    <a href="/backend/business/update?id=<?= $val['id'] ?>">
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