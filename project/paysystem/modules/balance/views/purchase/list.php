<?php

use yii\widgets\LinkPager;
use \yii\helpers\ArrayHelper;
use app\modules\balance\common\CYxpay;
use app\modules\balance\models\yx\YxUser;
use app\models\Channel;
$this->title = "订单管理";
$oCYxpay = new CYxpay();
$show = $oCYxpay->showRepaymentChannel();
$object = new Channel();
$ouser = new YxUser();
?>
<div class="col-lg-12 ui-sortable">
    <div class="box ui-sortable-handle" style="position: relative; left: 0px; top: 0px;">
        <header>
            <h5><?=$this->title. '>'?>购卡订单统计</h5>
        </header>
        <div class="body">
            <form action="?" method="get">
                <div style="margin-bottom: 10px">
                    订单号：<input type="text" value="<?=$order_pay_no?>" name="order_pay_no" style="margin-right: 20px;margin-left: 5px" />
                    商户订单号：<input type="text" value="<?=$paybill?>" name="paybill" style="margin-right: 20px;margin-left: 5px" />
<!--                    商编号：<input type="text" value="--><?//=$channel_id?><!--" name="channel_id" style="margin-right: 20px;margin-left: 5px" />-->
<!--                    姓名 ：<input type="text" value="--><?//=$realname?><!--" name="realname" style="margin-right: 20px;margin-left: 5px" />-->

                </div>
                <div style="margin-bottom: 10px">
<!--                    手机号 ：<input type="text" value="--><?//=$mobile?><!--" name="mobile" style="margin-right: 20px;margin-left: 5px" />-->
                    付款时间：<input style="margin-right: 10px; width:110px;" type="text" onclick="laydate({istime: true, format: 'YYYY-MM-DD'})" name="repay_start_time" value="<?=$repay_start_time?>"  /> ~
                    <input style="margin-left: 10px;  width:110px;"  type="text" onclick="laydate({istime: true, format: 'YYYY-MM-DD'})" name="repay_end_time"  value="<?=$repay_end_time?>" />


                    <span style="margin-left: 30px; ">创建日期：</span><input style="margin-right: 10px;width:110px;" type="text" onclick="laydate({istime: true, format: 'YYYY-MM-DD'})" name="create_start_time" value="<?=$create_start_time?>"  /> ~
                    <input style="margin-left: 10px; width:110px;"  type="text" onclick="laydate({istime: true, format: 'YYYY-MM-DD'})" name="create_end_time"  value="<?=$create_end_time?>" />
                   <!--<span style="margin-right: 30px;"></span> 商编号：<select style="margin-right: 30px;  padding:5px;" name="platform" id='platform'>
                        <option value="0">请选择</option>
                        <?php
/*                        foreach($show as $key => $value) {
                            */?>
                            <option
                                <?php
/*                                if ($platform == $key){
                                    echo "selected = 'selected'";
                                }
                                */?>
                                value="<?/*=$key*/?>"><?/*=$value*/?></option>
                            <?php
/*                        }
                        */?>
                    </select>-->
                </div>
               <!-- <div style="margin-bottom: 10px">
                    商编号：<select style="margin-right: 10px;  padding:5px;" name="platform" id='platform'>
                        <option value="0">请选择</option>
                        <?php
/*                        foreach($show as $key => $value) {
                            */?>
                            <option
                                <?php
/*                                if ($platform == $key){
                                    echo "selected = 'selected'";
                                }
                                */?>
                                value="<?/*=$key*/?>"><?/*=$value*/?></option>
                            <?php
/*                        }
                        */?>
                    </select>
                    <span style="margin-left: 30px">每页显示条数：</span><select name="pageSize" style="margin-right: 10px;  padding:5px">
                        <option value="30" <?php /*if(empty($pageSize)||$pageSize==30){echo 'selected';}else{echo '';}*/?>>30</option>
                        <option value="50" <?php /*if( $pageSize==50){echo 'selected';}else{echo '';}*/?>>50</option>
                        <option value="100" <?php /*if($pageSize==100){echo 'selected';}else{echo '';}*/?>>100</option>
                        <option value="500" <?php /*if($pageSize==500){echo 'selected';}else{echo '';}*/?>>500</option>
                    </select>
                </div>-->
                <div style="text-align: center">
                    <input type="reset" value="重置"  class="btn btn-primary">
                    <input style="margin-left: 20px;"  type="submit" value="查询" id="search_submit"  class="btn btn-primary">
                </div>

                <hr />
            </form>
            <div style="margin-bottom: 10px">
                <span>总笔数：<b style="color: red"><?=$total?></b>笔</span>
                <span style="margin-left: 20px">购卡总金额累计：<b style="color: red"><?=$moneySum?></b>元</span>
<!--                <span style="margin-left: 20px">优惠券总金额累计：<b style="color: red">--><?//=$couponSum?><!--</b>元</span>-->
                <span style="margin-left: 20px">实收总金额累计：<b style="color: red"><?=$actualMoneySum?></b>元</span>
            </div>
            <table class="table table-bordered table-condensed table-hover table-striped dataTable no-footer" style="table-layout: fixed;word-break:break-all">
                <thead>
                <tr role="row">
                    <th style="width: 5%">序号</th>
                    <th style="width: 6%">订单号</th>
                    <th style="width: 8%">商户订单号</th>
                    <th style="width: 6%">姓名</th>
                    <th style="width: 8%">手机号</th>
<!--                    <th style="width: 6%">商编号</th>-->
<!--                    <th style="width: 8%">收款通道</th>-->
                    <th style="width: 8%">购卡金额</th>
<!--                    <th style="width: 4%">优惠卷</th>-->
                    <th style="width: 8%">实收金额</th>
                    <th style="width: 8%">创建时间</th>
                    <th style="width: 8%">付款时间</th>
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
                            <td><?=ArrayHelper::getValue($value, 'order_pay_no','0')?></td>
                            <td><?=ArrayHelper::getValue($value, 'paybill','0')?></td>
                            <?php
                            $data = $ouser->getOne(ArrayHelper::getValue($value, 'user_id','0'));
                            $realname = ArrayHelper::getValue($data, 'realname','0');
                            $mobile = ArrayHelper::getValue($data, 'mobile','0');
                            ?>
                            <td><?=$realname?></td>
                            <td><?=$mobile?></td>
                            <?php
//                            $mechart = $object->getMechartNum(ArrayHelper::getValue($value, 'channel_id','0'));//获取商编号
//                            $re = $object->getCompanyName(ArrayHelper::getValue($value, 'channel_id','0'));//获取付款通道
//                            // var_dump($re);
//                            ?>
<!--                            <td>--><?//=$mechart?><!--</td>-->
<!--                            <td>--><?//=$re?><!--</td>-->
                            <!--<td><?/*=ArrayHelper::getValue($value, 'channel_id','0')*/?></td>
                            <td><?/*=ArrayHelper::getValue($show,ArrayHelper::getValue($value, 'platform'),'未知')*/?></td>-->


                            <td><?=Number_format(ArrayHelper::getValue($value, 'money','0'),2)?></td>
<!--                            <td>--><?//=ArrayHelper::getValue($value, 'val','0')?><!--</td>-->
                            <td><?=Number_format(ArrayHelper::getValue($value, 'actual_money','0'),2)?></td>
                            <td><?=ArrayHelper::getValue($value, 'create_time','0')?></td>
                            <td><?=ArrayHelper::getValue($value, 'repay_time','0')?></td>
                        </tr>
                        <?php
                    }
                }else {
                    ?>
                    <tr role="row" style="text-align: center">
                        <td colspan="9">暂无数据</td>
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
            var create_start_time = $("input[name='create_start_time']").val();
            var create_end_time = $("input[name='create_end_time']").val();
            var repay_start_time = $("input[name='repay_start_time']").val();
            var repay_end_time = $("input[name='repay_end_time']").val();
            if (create_start_time > create_end_time){
                alert("查询时间有误 ");
                return false;
            }
            if (repay_start_time > repay_end_time){
                alert("查询时间有误 ");
                return false;
            }
        });

    });
</script>