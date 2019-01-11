<?php

use yii\widgets\LinkPager;
use \yii\helpers\ArrayHelper;
use app\modules\balance\common\PaymentCommon;
use app\models\Channel;
$this->title = "财务核算";
$oPaymentCommon = new PaymentCommon();


?>
<div class="col-lg-12 ui-sortable">
    <div class="box ui-sortable-handle" style="position: relative; left: 0px; top: 0px;">
        <header>
            <h5>你所在的位置：首页><?=$this->title?>>账目月统计</h5>
            <!--<h5>延期订单统计</h5>-->
        </header>
        <div class="body">
            <form action="/balance/account/mouth" method="get">

                <div style="margin-bottom: 10px">
                主体名称： <select style="margin-right: 10px" id="main_body" name="main_body">
                        <option value="">请选择</option>
                        <?php
                        foreach($main_body as $key => $value) {
                            ?>
                            <option
                                <?php
                                if ($main_body_id == $key){
                                    echo "selected = 'selected'";
                                }
                                ?>
                                value="<?=$key?>"><?=$value?></option>
                            <?php
                        }
                        ?>
                    </select>
                商编号：<input style="margin-right: 10px; width:220px;" type="text"  name="mechart_num" value="<?=$mechart_num?>"  />

                账单日期：<input style="margin-right: 10px; width:130px;" type="text" onclick="laydate({istime: true, format: 'YYYY-MM-DD'})" name="repay_time" value="<?=$start_time?>"  />
                 ~
                    <input style="margin-left: 10px; width:130px;"  type="text" onclick="laydate({istime: true, format: 'YYYY-MM-DD'})" name="end_time"  value="<?=$end_time?>" />

                </div>
                    <div style="text-align: center">
                        <input type="reset" value="重置"  class="btn btn-primary">
                        <input style="margin-left: 20px;margin-right:20px"  type="submit" value="查询" id="search_submit"  class="btn btn-primary">
                        <input style="margin-left: 220px;margin-right:20px"  type="submit" value="导出" id="derive"  class="btn btn-primary">
                      <!--  <td style="margin-left: 20px;margin-right:20px"><a href="<?/*=$down_model*/?>">下载模版文件</a></td>-->
                </div>
                <hr />
            </form>

            <div style="margin-bottom: 10px">
                <span style="margin-left: 20px">本金总金额：<b style="color: red">¥<?=Number_format($split_principal,2)?></b>元</span>
                <span style="margin-left: 20px">利息总金额：<b style="color: red">¥<?=Number_format($split_interest,2)?></b>元</span>
                <span style="margin-left: 20px">滞纳金总金额：<b style="color: red">¥<?=Number_format($split_fine,2)?></b>元</span>
                <span style="margin-left: 20px">展期服务费总金额：<b style="color: red">¥<?=Number_format($renewal_money,2)?></b>元</span>
            </div>
            <div style="margin-bottom: 10px">
                <span style="margin-left: 20px">减免总金额：<b style="color: red">¥0</b>元</span>
                <span style="margin-left: 20px">手续费总金额：<b style="color: red">¥<?=Number_format($total_service,2)?></b>元</span>
                <span style="margin-left: 20px">总金额：<b style="color: red">¥<?=Number_format($total_money,2)?></b>元</span>
            </div>
            <hr />
            <div style="margin-bottom: 10px">
                <span style="margin-left: 20px">智融钥匙手续费总金额：<b style="color: red">¥<?=Number_format($zrys_service,2)?></b>元</span>
                <span style="margin-left: 20px">智融钥匙总金额：<b style="color: red">¥<?=Number_format($zrys_money,2)?></b>元</span>
            </div>
            <hr />


            <table class="table table-bordered table-condensed table-hover table-striped dataTable no-footer">
                <thead>
                <tr role="row">
                    <th>序号</th>
                    <th>类型</th>
                    <th>主体名称</th>
                    <th>通道商编</th>
                    <th>通道名称</th>
                    <th>本金</th>
                    <th>利息</th>
                    <th>滞纳金</th>
                    <th>展期服务费</th>
                    <th>减免金额</th>
                    <th>手续费</th>
                    <th>总金额（<span style="color: red">手续费除外</span>）</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $num = 0;
                if (!empty($resultAllData)) {

                    foreach($resultAllData as $value) {

                        $num ++;
                        ?>
                        <tr role="row">
                            <td><?=$num;?></td>
                            <td><?=ArrayHelper::getValue($value, 'days','0')?></td>
                            <td>

                                <?php
                                $passageway_status = ArrayHelper::getValue($main_body, ArrayHelper::getValue($value, 'party','999'),'<span style="color: #CC9999">未知</span>');//公司主体
                                echo $passageway_status;
                                ?>
                            </td>
                            <td><?=ArrayHelper::getValue($value, 'mechart_num','999')?></td>
                            <td><?=ArrayHelper::getValue($return_channel, ArrayHelper::getValue($value, 'return_channel','0'),'<span style="color: #CC9999">未知</span>')?></td>

                            <td><?=Number_format(ArrayHelper::getValue($value, 'principal','0'),2)?></td>
                            <td><?=Number_format(ArrayHelper::getValue($value, 'interest','0'),2)?></td>
                            <td><?=Number_format(ArrayHelper::getValue($value, 'fine','0'),2)?></td>
                            <td>0</td>
                            <td>0</td>
                            <td><?=Number_format(ArrayHelper::getValue($value, 'service','0'),2)?></td>
                            <td><?=Number_format(ArrayHelper::getValue($value, 'money','0'),2)?></td>
                        </tr>
                        <?php
                    }
                }else {
                    ?>
                    <tr role="row" style="text-align: center">
                        <td colspan="12">暂无数据</td>
                    </tr>
                    <?php

                }
                ?>


                <?php
                if (!empty($renewalList)) {
//                    $num = 0;
                    foreach($renewalList as $value) {

                        $num ++;
                        ?>
                        <tr role="row">
                            <td><?=$num;?></td>
                            <td><?=ArrayHelper::getValue($value, 'days','0')?></td>
                            <td>
                                <?php
                                $passageway_status = ArrayHelper::getValue($main_body, ArrayHelper::getValue($value, 'party','999'),'<span style="color: #CC9999">未知</span>');//公司主体
                                echo $passageway_status;
                                ?>
                            </td>
                            <td>0</td>
                            <td>0</td>
                            <td>0</td>
                            <td>0</td>
                            <td>0</td>
                            <td><?=Number_format(ArrayHelper::getValue($value, 'total_money','0'),2)?></td>
                            <td>0</td>
                            <td>0</td>
                            <td><?=Number_format(ArrayHelper::getValue($value, 'total_money','0'),2)?></td>
                        </tr>
                        <?php
                    }
                }else {
                    ?>
                    <tr role="row" style="text-align: center">
                        <td colspan="12">暂无数据</td>
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

        //导出表格
        $("#derive").click(function(){
            var main_body = $("#main_body").val();//主体名称 id
            //alert(main_body)
            var mechart_num = $("input[name='mechart_num']").val();//通道商编
            var start_time = $("input[name='repay_time']").val(); //账单日期
            var end_time = $("input[name='end_time']").val();//账单日期
            window.location.href = '/balance/account/downdata?&main_body='+ main_body + "&mechart_num=" + mechart_num  + "&start_time=" + start_time + "&end_time=" + end_time;
            return false;
        });

    });
</script>