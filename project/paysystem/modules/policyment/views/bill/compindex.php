<?php

use yii\widgets\LinkPager;

$this->title = "保险管理";
$status      = \app\models\policy\PolicyCheckbill::getStatus();
?>
<div class="col-lg-12 ui-sortable">
    <div class="box ui-sortable-handle" style="position: relative; left: 0px; top: 0px;">
        <header>
            <h5>对账完成列表</h5>
        </header>
        <div class="body">
            <form action="/policyment/bill/compindex" method="GET" class="form-inline">
                <div class="row form-group">
                    <div class="col-lg-2" style="width: auto">
                        <input size="15" class="form-control" value="<?=isset($get['billDate'])?$get['billDate']:''?>" name="billDate" placeholder="对账日期"  type="text" onclick="laydate({istime: true, format: 'YYYY-MM-DD'})">
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
                        <th>账单日期</th>
                        <th>保单笔数</th>
                        <th>保单总金额</th>
                        <th>账单笔数</th>
                        <th>账单总金额</th>
                        <th>状态</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($res)): ?>
                        <?php foreach ($res as $key => $val): ?>
                            <tr role="row" class="even">
                                <td><?= $val['billDate'] ?></td>
                                <td><?= $val['policy_number']?></td>
                                <td><?= $val['policy_premium'] ?></td>
                                <td><?= $val['bill_number'] ?></td>
                                <td><?= $val['bill_premium'] ?></td>                             
                                <td><span style="color:red"><?php echo isset($status[$val['billStatus']]) ? $status[$val['billStatus']] : '未知' ?></span></td>
                                <td><a href="/policyment/bill/index?billDate=<?=$val['billDate']?>&billStatus=<?=$val['billStatus']?>">查看</a></td>
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
<script src="/laydate/laydate.dev.js" type="text/javascript" charset="utf-8"></script>
