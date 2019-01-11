<?php 
use yii\widgets\LinkPager;
$this->title="支付系统";
$status = \app\models\WhiteIp::getStatus(); 
?>
<div class="col-lg-12 ui-sortable">
    <div class="box ui-sortable-handle" style="position: relative; left: 0px; top: 0px;">
        <header>
        <h5>标准化错误</h5>
        <a href="/backend/std-error/add"><label style="float:right" class="btn btn-primary">添加</label></a>
        </header>
        <div class="body">
        <table class="table table-bordered table-condensed table-hover table-striped dataTable no-footer">
        <thead>
            <tr role="row">
                <th>ID</th>
                <th>渠道</th>
                <th>错误码</th>
                <th>错误原因</th>
                <th>标准错误码</th>
                <th>标准错误原因</th>
                <th>创建时间</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php  if(!empty($res)): ?>
            <?php foreach ($res as $key => $val): ?>
                <tr role="row" class="even">
                    <td><?=$val['id']?></td>
                    <td><?=$channel[$val['channel_id']]->product_name?></td>
                    <td><?=$val['error_code']?></td>
                    <td><?=$val['error_msg']?></td>
                    <td><?=$val['res_code']?></td>
                    <td><?=$val['res_msg']?></td>
                    <td><?=$val['create_time']?></td>
                    <td>
                        <a href="/backend/std-error/update?id=<?= $val['id'] ?>">
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