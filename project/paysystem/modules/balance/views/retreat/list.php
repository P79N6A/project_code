<?php

use yii\widgets\LinkPager;
use \yii\helpers\ArrayHelper;
use app\modules\balance\common\CYxpay;
use app\models\Channel;
$this->title = "订单管理";
$oCYxpay = new CYxpay();
$show = $oCYxpay->showRepaymentChannel();
$object = new Channel();
?>
<div class="col-lg-12 ui-sortable">
    <div class="box ui-sortable-handle" style="position: relative; left: 0px; top: 0px;">
        <header>
            <h5><?=$this->title. '>'?>退卡订单统计</h5>
        </header>
        <div class="body">
            <form action="?" method="get">
                <div style="margin-bottom: 10px">
                    退卡订单号：<input type="text" value="<?=$id?>" name="id" style="margin-right: 10px;margin-left: 5px ;width:120px;" />
                    订单号：<input type="text" value="<?=$order_pay_no?>" name="order_pay_no" style="margin-right: 10px;margin-left: 5px;width:120px;" />
                    商户订单号：<input type="text" value="<?=$paybill?>" name="paybill" style="margin-right: 10px;margin-left: 5px;width:120px;" />
                    姓名 ：<input type="text" value="<?=$realname?>" name="realname" style="margin-right: 20px;margin-left: 5px;width:120px;" />
                    手机号 ：<input type="text" value="<?=$mobile?>" name="mobile" style="margin-right: 10px;margin-left: 5px;width:120px;" />

                </div>
                <div style="margin-bottom: 10px">
                    商编号 ：<input type="text" value="<?=$channel_id?>" name="channel_id" style="margin-right: 10px;margin-left: 5px;width:120px;" />

                    <span style="margin-left: 30px">退卡状态：</span><select name="status" style="margin-right: 10px;  padding:5px;">
                        <option value="" selected>全部</option>
                        <option value="1" <?php if( $status=='1'){echo 'selected';}else{echo '';}?>>已处理</option>
                        <option value="0" <?php if( $status=='0'){echo 'selected';}else{echo '';}?>>未处理</option>
                    </select>

                    退卡时间：<input style="margin-right: 10px;" type="text" onclick="laydate({istime: true, format: 'YYYY-MM-DD'})" name="start_time" value="<?=$start_time?>"  /> ~
                    <input style="margin-left: 10px;"  type="text" onclick="laydate({istime: true, format: 'YYYY-MM-DD'})" name="end_time"  value="<?=$end_time?>" />



                </div>

                <div style="text-align: center">
                    <input type="reset" value="重置"  class="btn btn-primary">
                    <input style="margin-left: 20px;"  type="submit" value="查询" id="search_submit"  class="btn btn-primary">
                </div>

                <hr />
            </form>
            <div style="margin-bottom: 10px">
                <span>总笔数：<b style="color: red"><?=$total?></b>笔</span>
                <span style="margin-left: 40px">实收总金额累计：<b style="color: red"><?=$moneySum?></b>元</span>
                <span style="margin-left: 40px">退卡总金额累计：<b style="color: red"><?=$actualMoneySum?></b>元</span>
            </div>
            <table class="table table-bordered table-condensed table-hover table-striped dataTable no-footer" style="table-layout: fixed;word-break:break-all">
                <thead>
                <tr role="row">
                    <th style="width: 3%">序号</th>
                    <th style="width: 6%">退卡订单号</th>
                    <th style="width: 6%">订单号</th>
                    <th style="width: 6%">商户订单号</th>
                    <th style="width: 3%">姓名</th>
                    <th style="width: 4%">手机号</th>
                    <th style="width: 6%">商编号</th>
                    <th style="width: 5%">收款通道</th>
                    <th style="width: 5%">实收金额</th>
                    <th style="width: 5%">创建日期</th>
                    <th style="width: 5%">付款时间</th>
                    <th style="width: 5%">退卡金额</th>
                    <th style="width: 5%">退卡方式</th>
                    <th style="width: 5%">退卡时间</th>
                    <th style="width: 5%">退卡状态</th>
                </tr>
                </thead>
                <tbody>
                <?php
                if (!empty($resultAllData)) {
                    $num = 0;
                    foreach($resultAllData as $value) {
                        $num ++;
                        ?>
                        <tr role="row">
                            <td><?=$num;?></td>
                            <td><?=ArrayHelper::getValue($value, 'id','0')?></td>
                            <td><?=ArrayHelper::getValue($value, 'order_pay_no','0')?></td>
                            <td><?=ArrayHelper::getValue($value, 'paybill','0')?></td>
                            <td><?=ArrayHelper::getValue($value, 'realname','未知')?></td>
                            <td><?=ArrayHelper::getValue($value, 'mobile','未知')?></td>
                            <?php
                            $mechart = $object->getMechartNum(ArrayHelper::getValue($value, 'channel_id','0'));//获取商编号
                            $re = $object->getCompanyName(ArrayHelper::getValue($value, 'channel_id','0'));//获取付款通道
                            // var_dump($re);
                            ?>
                            <td><?=$mechart?></td>
                            <td><?=$re?></td>
                            <!--<td><?/*=ArrayHelper::getValue($value, 'channel_id','0')*/?></td>
                            <td><?/*=ArrayHelper::getValue($show,ArrayHelper::getValue($value, 'platform'),'未知')*/?></td>-->

                            <td><?=Number_format(ArrayHelper::getValue($value, 'actual_money','0'),2)?></td>
                            <td><?=ArrayHelper::getValue($value, 'create_time','0')?></td>
                            <td><?=ArrayHelper::getValue($value, 'repay_time','0')?></td>
                            <td><?=Number_format(ArrayHelper::getValue($value, 'actual_money','0'),2)?></td>
                            <td>线下退回</td>
                            <td><?=ArrayHelper::getValue($value, 'last_modify_time','未知')?></td>
                            <td><?php  if(ArrayHelper::getValue($value, 'status')=='1'){echo '已处理';}else{echo '未处理';}?></td>


                        </tr>
                        <?php
                    }
                }else {
                    ?>
                    <tr role="row" style="text-align: center">
                        <td colspan="14">暂无数据</td>
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
            var start_time = $("input[name='start_time']").val();
            var end_time = $("input[name='end_time']").val();
            if (start_time > end_time){
                alert("查询时间有误 ");
                return false;
            }
        });

    });
</script>