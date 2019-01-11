<?php

use yii\widgets\LinkPager;
use \yii\helpers\ArrayHelper;
use app\modules\balance\models\yx\Yxious;
use app\modules\balance\common\CYxpay;
use app\modules\balance\models\yx\YxUser;
use app\models\Channel;
$this->title = "订单管理";
$oCYxpay = new CYxpay();
$show = $oCYxpay->showRepaymentChannel();
$state = $oCYxpay->Status();
$object = new Channel();
$ouser = new YxUser();

//$this->title = "放款统计1";
//$colum = new Yxious();
///$list = $colum->showRepaymentChannel();

?>
<div class="col-lg-12 ui-sortable">
    <div class="box ui-sortable-handle" style="position: relative; left: 0px; top: 0px;">
        <header>
            <h5><?=$this->title?>>延期订单统计</h5>
            <!--<h5>延期订单统计</h5>-->
        </header>
        <div class="body">
            <form action="/balance/zrys/list" method="get">
                <div style="margin-bottom: 10px">

                订单号：<input style="margin-right: 6px;width:230px;" type="text"  name="order_pay_no" value="<?=$order_pay_no?>"  />

                商户订单号：<input style="margin-right: 10px;width:230px;" type="text"  name="paybill" value="<?=$paybill?>"  />
                    

              <!--  姓名：<input style="margin-right: 10px;width:230px; " type="text"  name="realname" value="<?/*=$realname*/?>"  />

                
                手机号：<input style="margin-right: 10px; width:230px;" type="text"  name="mobile" value="<?/*=$mobile*/?>"  />-->
                </div>
                <div style="margin-bottom: 10px">
                    商编号：<input style="margin-right: 10px; width:230px;" type="text"  name="channel_id" value="<?=$channel_id?>"  />

                   <!-- <span style="margin-right: 30px;"></span> 商编号：<select style="margin-right: 30px;  padding:5px;" name="platform" id='platform'>
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

                是否到期：<select style="margin-right: 10px; padding:5px; width:130px;  margin-top:10px;" name="is_end">
                        <option value="">请选择</option>
                        <option value="10"<?php if( $is_end=='10'){echo 'selected';}else{echo '';}?>>已到期</option>
                        <option value="20"<?php if( $is_end=='20'){echo 'selected';}else{echo '';}?>>未到期</option>
                     </select>

                结算状态：<select style="margin-right: 10px; padding:5px; margin-top:10px; width:130px;" name="status">
                        <option value="">请选择</option>
                            <option value="8"<?php if( $status=='8'){echo 'selected';}else{echo '';}?>>已结清</option>
                            <option value="9"<?php if( $status=='9'){echo 'selected';}else{echo '';}?>>未结清</option>

                     </select>

                付款日期：<input style="margin-right: 10px; width:130px;" type="text" onclick="laydate({istime: true, format: 'YYYY-MM-DD'})" name="repay_time" value="<?=$repay_time?>"  />
                 ~
                    <input style="margin-left: 10px; width:130px;"  type="text" onclick="laydate({istime: true, format: 'YYYY-MM-DD'})" name="end_time"  value="<?=$end_time?>" />

                </div>
                    <div style="text-align: center">
                        <input type="reset" value="重置"  class="btn btn-primary">
                        <input style="margin-left: 20px;margin-right:20px"  type="submit" value="查询" id="search_submit"  class="btn btn-primary">
                    <!--<input type="reset" value="导出数据" id="import_data"  class="btn btn-primary">-->
                </div>
                <hr />
            </form>

            <div style="margin-bottom: 10px">
                <span>总笔数：<b style="color: red"><?=$total?></b>笔</span>
                <span style="margin-left: 20px">白条总金额累计：<b style="color: red"><?=$moneySum?></b>元</span>
                <span style="margin-left: 20px">延期服务费总金额累计：<b style="color: red"><?=$yan?></b>元</span>
               <!-- <span style="margin-left: 20px">优惠券总金额累计：<b style="color: red"><?/*=$couponSum*/?></b>元</span>-->
                <span style="margin-left: 20px">应收总金额累计：<b style="color: red"><?=$actualMoneySum?></b>元</span>
            </div>


            <table class="table table-bordered table-condensed table-hover table-striped dataTable no-footer">
                <thead>
                <tr role="row">
                    <th style="width:50px; ">序号</th>
                    <th  style="width:110px; ">订单号</th>
                    <th  style="width:110px; ">商户订单号</th>
                    <th style="width:70px; ">姓名</th>
                    <th style="width:50px; ">手机号</th>
                  <!--  <th style="width:110px; ">商编号</th>
                    <th style="width:70px; ">收款通道</th>-->
                    <th style="width:50px; ">白条期限/天</th>
                    <th style="width:70px; ">是否到期</th>
                    <th style="width:50px; ">白条金额</th>
                    <th style="width:50px; ">延期服务费</th>
                   <!-- <th style="width:50px; ">优惠券</th>-->
                    <th style="width:50px; ">应收金额</th>
                    <th style="width:70px; ">结算状态</th>
                    <th style="width:150px; ">创建日期</th>
                    <th style="width:150px; ">应还款日期</th>
                    <th style="width:150px; ">付款日期</th>
                </tr>
                </thead>
                <tbody>
                <?php
                if (!empty($resultAllData)) {
                    $num = 0;
                    //var_dump($resultAllData);die;
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
                               // $mechart = $object->getMechartNum(ArrayHelper::getValue($value, 'channel_id','0'));//获取商编号
                                //$re = $object->getCompanyName(ArrayHelper::getValue($value, 'channel_id','0'));//获取付款通道

                            ?>
                           <!-- <td><?/*=$mechart*/?></td>
                            <td><?/*=$re*/?></td>-->
                            <td><?=round((strtotime(ArrayHelper::getValue($value, 'end_time','0'))-strtotime(ArrayHelper::getValue($value, 'create_time','0')))/86400)?></td>
                            <?php
                            if(time()-strtotime(ArrayHelper::getValue($value, 'end_time','0'))>0) {
                                ?>
                                <td>已到期</td>
                                <?php
                            }else{
                               ?>
                                <td>未到期</td>
                                <?php
                            }
                            ?>
                            <td><?=Number_format(ArrayHelper::getValue($value, 'money','0'),2)?></td>
                            <td><?=Number_format(ArrayHelper::getValue($value, 'chase_amount','0'),2)-Number_format(ArrayHelper::getValue($value, 'money','0'),2)?></td>
                       <!--     <td><?/*=Number_format(ArrayHelper::getValue($value, 'val','0'),2)*/?></td>-->
                            <td><?=Number_format(ArrayHelper::getValue($value, 'chase_amount','0'),2)?></td>
                            <td><?=ArrayHelper::getValue($state, ArrayHelper::getValue($value, 'status','0'))?></td>
                            <td><?=ArrayHelper::getValue($value, 'create_time','0')?></td>
                            <td><?=ArrayHelper::getValue($value, 'end_time','0')?></td>
                            <td><?=ArrayHelper::getValue($value, 'repay_time','0')?></td>

                        </tr>
                        <?php
                    }
                }else {
                    ?>
                    <tr role="row" style="text-align: center">
                        <td colspan="17">暂无数据</td>
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
            var start_time = $("input[name='repay_time']").val();
            var end_time = $("input[name='end_time']").val();
            if (start_time > end_time){
                alert("查询时间有误 ");
                return false;
            }
        });
    });
</script>