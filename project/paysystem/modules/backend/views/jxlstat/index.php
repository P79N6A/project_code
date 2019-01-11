<?php 
use yii\widgets\LinkPager;
$this->title="运营商";
?>
<div class="col-lg-12 ui-sortable">
    <div class="box ui-sortable-handle" style="position: relative; left: 0px; top: 0px;">
        <header>
        <h5>请求结果</h5>
        </header>
        <div class="body">
        <form action="/backend/jxlstat/index" method="GET" class="form-inline">
        <div class="row form-group">
            <div class="col-lg-4">
                <input size="18" class="form-control" value="<?=isset($get['name'])?$get['name']:''?>" name="name" placeholder="姓名"  type="text">
            </div>
            <div class="col-lg-4">
                <input size="18" class="form-control" value="<?=isset($get['phone'])?$get['phone']:''?>" name="phone" placeholder="手机号" type="text">
            </div>
            <div class="col-lg-4">
                <input size="18" class="form-control" value="<?=isset($get['source'])?$get['source']:''?>" name="source" placeholder="来源" type="text">
            </div>
        </div>
               
                <button type="submit" class="btn btn-primary">搜索</button>
            </form>
            <br>
        <table class="table table-bordered table-condensed table-hover table-striped dataTable no-footer">
        <thead>
            <tr role="row">
                <th width='50px'>ID</th>
                <th width='30px'>AID</th>
                <th width='50px'>请求ID</th>
                <th width='80px'>姓名</th>
                <th width='120px'>身份证</th>
                <th width='100px'>电话</th>
                <th width='80px'>结果</th>
                <th width='80px'>URL</th>
                <th width='50px'>来源</th>
                <th width='150px'>创建时间</th>
            </tr>
        </thead>
        <tbody>
            <?php  if(!empty($res)): ?>
            <?php foreach ($res as $key => $val): ?>
                <tr role="row" class="even">
                    <td><?=$val['id']?></td>
                    <td><?=$val['aid']?></td>
                    <td><?=$val['requestid']?></td>
                    <td><?=$val['name']?></td>
                    <td><?=$val['idcard']?></td>
                    <td><?=$val['phone']?></td>
                    <td><?= isset($isValid[$val['is_valid']]) ? $isValid[$val['is_valid']] : '未知'; ?></td>
                    <td><?=$val['url']?></td>
                    <td><?= isset($fromList[$val['source']]) ? $fromList[$val['source']] : '未知'; ?></td>
                    
                    <td><?=$val['create_time']?></td>
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