<?php

use yii\widgets\LinkPager;

$this->title = "支付系统";
$status      = \app\models\Channel::getStatus();
?>
<div class="col-lg-12 ui-sortable">
    <div class="box ui-sortable-handle" style="position: relative; left: 0px; top: 0px;">
        <header>
            <h5>支付通道</h5>
            <a href="/backend/channel/add"><label style="float:right" class="btn btn-primary">添加</label></a>
        </header>
        <div class="body">
        <form action="/backend/channel/index" method="GET" class="form-inline">
                <div class="row form-group">
                    <div class="col-lg-5">
                        <input size="15" class="form-control" value="<?=isset($get['company_name'])?$get['company_name']:''?>" name="company_name" placeholder="通道名称"  type="text">
                    </div>
                   
                    <div class="col-lg-5">
                        <select class="form-control" name="status" tabindex="14" style="width: 150px;">
                            <option value="" >通道状态</option>
<option value="0" <?php echo isset($get['status'])&&$get['status']=="0"?'selected':''?>>未开通</option>
                            <option value="1" <?php echo isset($get['status'])&&$get['status']=="1"?'selected':''?>>已开通</option>
                            
                        </select>
                    </div>
                    
                    <div class="col-lg-2">
                        <button type="submit" class="btn btn-primary">搜索</button>
                    </div>
                </div>

            </form>
            <br>
            <table class="table table-bordered table-condensed table-hover table-striped dataTable no-footer">
                <thead>
                    <tr role="row">
                        <th width='50px'>ID</th>
                        <th width='120px'>通道名称</th>
                        <th width='120px'>产品名称</th>
                        <th width='120px'>商编</th>
                        <th width='120px'>银行卡</th>
                        <th width='120px'>支付说明</th>
                        <th width='120px'>状态</th>
                        <th width='120px'>操作</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($res)): ?>
                    <?php foreach ($res as $key => $val): ?>
                        <tr role="row" class="even">
                            <td><?= $val['id'] ?></td>
                            <td><?= $val['company_name'] ?></td>
                            <td><?= $val['product_name'] ?></td>
                            <td><?= $val['mechart_num'] ?></td>
                            <td><a href="/backend/bank?pay_chan=<?=$val['id']?>">查看</a></td>
                            <td><a class="showtip" tabindex="10" class="btn btn-lg btn-danger" role="button" data-toggle="popover" data-trigger="focus" title="说明" data-content="<?php echo $val['tip'] ?>">查看</a></td>
                            <td><?php echo isset($status[$val['status']]) ? $status[$val['status']] : '未知' ?></td>
                            <td>
                                <a href="/backend/channel/update?id=<?= $val['id'] ?>">
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