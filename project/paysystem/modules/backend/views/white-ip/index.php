<?php 
use yii\widgets\LinkPager;
$this->title="支付系统";
$status = \app\models\WhiteIp::getStatus(); 
?>
<div class="col-lg-12 ui-sortable">
    <div class="box ui-sortable-handle" style="position: relative; left: 0px; top: 0px;">
        <header>
        <h5>IP白名单</h5>
        <a href="/backend/white-ip/add"><label style="float:right" class="btn btn-primary">添加</label></a>
        </header>
        <div class="body">
            <div style="padding-bottom:50px">
                <form action="?" method="GET" class="form-inline">
                    <div class="col-lg-2" style="width: auto">
                        <input size="15" class="form-control" value="<?=$aid?>" name="aid" placeholder="aid" type="text">
                    </div>
                    <div class="col-lg-2" style="width: auto">
                        <input size="15" class="form-control" value="<?=$ip?>" name="ip" placeholder="ip" type="text">
                    </div>

                    <div class="col-lg-1" style="width: auto">
                        <button type="submit" class="btn btn-primary">搜索</button>
                    </div>
                </form>
            </div>
        <table class="table table-bordered table-condensed table-hover table-striped dataTable no-footer">
        <thead>
            <tr role="row">
                <th width='50px'>ID</th>
                <th width='60px'>aid</th>
                <th width='120px'>IP</th>
                <th width='60px'>状态</th>
                <th width='100px'>创建时间</th>
                <th width='120px'>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php  if(!empty($res)): ?>
            <?php foreach ($res as $key => $val): ?>
                <tr role="row" class="even">
                    <td><?=$val['id']?></td>
                    <td><?=$val['aid']?></td>
                    <td><?=$val['ip']?></td>
                    <td>
                        <?php
                            echo \yii\helpers\ArrayHelper::getValue($val, 'status') ? "启动" : "未启用";
                        ?>
                    </td>
                    <td><?=$val['create_time']?></td>
                    <td>
                        <!-- <a href="/backend/white-ip/status?id=<?=$val['id']?>">
                            <label class="btn <?php echo  $val['status']==1 ? 'btn-success' :'btn-danger' ?>">
                                <?php echo isset($status[$val['status']]) ? $status[$val['status']] : '未知' ?>
                            </label>
                        </a> -->
                        <a href="/backend/white-ip/update?id=<?=$val['id']?>">
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