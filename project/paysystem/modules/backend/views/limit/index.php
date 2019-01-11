<?php

use yii\widgets\LinkPager;

$this->title = "支付系统";
$status      = app\models\open\BfClientNotify::getStatus();
?>
<div class="col-lg-12 ui-sortable">
    <div class="box ui-sortable-handle" style="position: relative; left: 0px; top: 0px;">
        <header>
            <h5>出款限额</h5>
        </header>
        <div class="body">
        <form action="/backend/limit/index" method="GET" class="form-inline">
                <div class="row form-group">
                    <div class="col-lg-4">
                        <input size="18" class="form-control" value="<?=isset($aid)?$aid:''?>" name="aid" placeholder="Aid"  type="text">
                    </div>
                    
                </div>
               
                <button type="submit" class="btn btn-primary">搜索</button>
            </form>
            <br>
            <table class="table table-bordered table-condensed table-hover table-striped dataTable no-footer">
                <thead>
                    <tr role="row">
                        <th width='150px'>ID</th>
                        <th width='180px'>AID</th>
                        <th width='180px'>额度</th>
                        <th width='180px'>创建时间</th>
                        <th width='180px'>操作</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($res)): ?>
                    <?php foreach ($res as $key => $val): ?>
                        <tr role="row" class="even">
                            <td><?= $val['id'] ?></td>
                            <td><?= $val['aid'] ?></td>
                            <td><?= $val['day_max_mount'] ?></td>
                            <td><?= $val['create_time']?></td>
                            <td>
                                <a href="/backend/limit/update?id=<?= $val['id'] ?>">
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