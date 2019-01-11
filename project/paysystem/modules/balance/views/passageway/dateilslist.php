<?php

use yii\widgets\LinkPager;
use \yii\helpers\ArrayHelper;

$this->title = "米富逾期对账管理";
$status      = \app\models\Business::getStatus();

$source = ['初始', '已上传', '已下载'];
?>
<div class="col-lg-12 ui-sortable">
    <div class="box ui-sortable-handle" style="position: relative; left: 0px; top: 0px;">
        <header>
            <h5><?=$this->title. '>'?>通道账单管理</h5>
        </header>
        <div class="body">
            <form action="?" method="get">
                <div>
                    商户订单号<input type="text" name="client_id" value="<?=$client_id?>" style="margin-right: 20px;margin-left: 10px" />
                    姓名<input type="text" name="name" value="<?=$name?>" style="margin-left: 10px" />
                    <input type="hidden" name="id" value="<?=$id?>" style="margin-left: 10px" />
                    <input type="hidden" name="return_channel" value="<?=$return_channel?>" style="margin-left: 10px" />
                </div>
                <div style="margin-bottom: 10px; text-align: center; margin-top: 10px;">
                    <input type="reset" value="重置" class="btn btn-primary">
                    <input style="margin-left: 5px;"  type="submit" value="查询" id="search_submit"  class="btn btn-primary">
                </div>
                <hr />
            </form>
            <div style="margin-bottom: 10px">
                <span style="margin-right: 30px;">收款通道名称：<?=$return_channel_name?></span>
                <span style="margin-right: 30px;">总笔数：<b style="color: red"><?=$total?></b>笔</span>
                <span style="margin-right: 30px;">总金额：<b style="color: red">￥<?=Number_format($money,2)?></b> 元</span>
                <span style="margin-right: 30px;">总手续费：<b style="color: red">￥<?=Number_format($fee,2)?></b> 元</span>
            </div>
            <hr />
            <table class="table table-bordered table-condensed table-hover table-striped dataTable no-footer">
                <thead>
                <tr role="row">
                    <th>序号</th>
                    <th>商户订单号</th>
                    <th>姓名</th>
                    <th>银行卡号</th>
                    <th>订单金额/元</th>
                    <th>手续费/元</th>
                    <th>状态</th>
                    <th>账单日期</th>
                    <th>创建时间</th>
                </tr>
                </thead>
                <tbody>
                <?php
                if (!empty($getFileData)) {
                    $num = 0;
                    foreach($getFileData as $value) {
                        $num ++;
                        ?>
                        <tr role="row">
                            <td><?=$num?></td>
                            <td><?=ArrayHelper::getValue($value, 'client_id')?></td>
                            <td><?=ArrayHelper::getValue($value, 'name')?></td>
                            <td><?=ArrayHelper::getValue($value, 'guest_account')?></td>
                            <td><?=ArrayHelper::getValue($value, 'amount')?>/元</td>
                            <td><?=ArrayHelper::getValue($value, 'settle_fee')?>/元</td>
                            <td>
                                <?php
                                $passageway_type = ArrayHelper::getValue($value, 'passageway_type');
                                if ($passageway_type == 1){
                                    $passageway_status = '第三方成功';
                                }elseif($passageway_type == 2){
                                    $passageway_status = '平台成功';
                                }elseif($passageway_type & 3){
                                    $passageway_status = "双方成功";
                                }
                                echo $passageway_status;
                                ?>
                            </td>
                            <td><?=ArrayHelper::getValue($value, 'payment_date')?></td>
                            <td><?=ArrayHelper::getValue($value, 'create_time')?></td>
                        </tr>
                        <?php
                    }
                }else {
                    ?>
                    <tr role="row" style="text-align: center">
                        <td colspan="9">暂无数据！</td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>
            <div class="panel_pager">
                <?php echo LinkPager::widget(['pagination' => $pages]); ?>
            </div>
        </div>
    </div>
</div>
<script src="/bootstrap/js/bootstrap.min.js" type="text/javascript" charset="utf-8"></script>
<script src="/laydate/laydate.dev.js" type="text/javascript" charset="utf-8"></script>
<script>
    $(function(){
        $("#search_submit").click(function(){
            var client_id = $("input[name='client_id']").val();
            var name = $("input[name='name']").val();
            if (client_id == '' && name ==''){
                //alert("请输入查询条件");
                //return false;
            }
        });
    });

    //跳转到上传页面
    function jump_url(){
        location.href="/backstage/passageway/upbill";
    }
</script>