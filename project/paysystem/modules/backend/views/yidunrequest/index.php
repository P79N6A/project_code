<?php 
use yii\widgets\LinkPager;
$this->title="运营商";
$resultstatus = \app\models\open\YidunRequest::getResultStatus(); 
$clientstatus = \app\models\open\YidunRequest::getClientStatus(); 
?>
<div class="col-lg-12 ui-sortable">
    <div class="box ui-sortable-handle" style="position: relative; left: 0px; top: 0px;">
        <header>
        <h5>蚁盾请求</h5>
        </header>
        <div class="body">
        <form action="/backend/yidunrequest/index" method="GET" class="form-inline">
        <div class="row form-group">
            <div class="col-lg-6">
                <input size="18" class="form-control" value="<?=isset($get['name'])?$get['name']:''?>" name="name" placeholder="姓名"  type="text">
            </div>
            <div class="col-lg-6">
                <input size="18" class="form-control" value="<?=isset($get['phone'])?$get['phone']:''?>" name="phone" placeholder="手机号" type="text">
            </div>
        </div>
               
                <button type="submit" class="btn btn-primary">搜索</button>
            </form>
            <br>
        <table class="table table-bordered table-condensed table-hover table-striped dataTable no-footer">
        <thead>
            <tr role="row">
                <th width='50px'>ID</th>
                <th width='80px'>请求ID</th>
                <th width='50px'>AID</th>
                <th width='80px'>姓名</th>
                <th width='120px'>卡号</th>
                <th width='120px'>电话</th>
                <th width='100px'>创建时间</th>
                <th width='120px'>流水号</th>
                <th width='100px'>流程码</th>
                <th width='100px'>采集状态</th>
            </tr>
        </thead>
        <tbody>
            <?php  if(!empty($res)): ?>
            <?php foreach ($res as $key => $val): ?>
                <tr role="row" class="even">
                    <td><?=$val['id']?></td>
                    <td><?=$val['requestid']?></td>
                    <td><?=$val['aid']?></td>
                    <td><?=$val['name']?></td>
                    <td><?=$val['idcard']?></td>
                    <td><?=$val['phone']?></td>
                    <td><?=date('Y-m-d H:i:s',$val['create_time'])?></td>
                    <td><?=$val['bizno']?></td>
                    
                    <td><?=$val['process_code']?></td>
                    <td>
                    <?php echo isset($resultstatus[$val['result_status']]) ? $resultstatus[$val['result_status']] : '未知' ?>
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