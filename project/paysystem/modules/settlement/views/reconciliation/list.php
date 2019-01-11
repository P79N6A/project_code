<?php

use yii\widgets\LinkPager;
use \yii\helpers\ArrayHelper;
$this->title = "出款账单管理";
$status      = \app\models\Business::getStatus();
?>
<div class="col-lg-12 ui-sortable">
    <div class="box ui-sortable-handle" style="position: relative; left: 0px; top: 0px;">
        <header>
            <h5><?=$this->title. '>'?>对账成功列表</h5>
           <span style="float: right;margin-right: 20px; margin-top: 2px; "><input style="margin-left: 10px;"  type="submit" value="导出" id="down_url"  class="btn btn-primary"></span>
        </header>
        <div class="body">
            <form action="?" method="get">
            <div style="margin-bottom: 10px">
                <div>
                商户订单号：  <input size="10" name="client_id" type="text"  style="margin-right: 10px;margin-left:10px" value="<?=ArrayHelper::getValue($getData, 'client_id', '')?>"/>
                收款人：      <input size="10" name="guest_account_name" type="text" style="margin-right: 10px;" value="<?=ArrayHelper::getValue($getData, 'guest_account_name', '')?>" />
                账单日期：    <input size="10" name="start_bill_time" type="text" onclick="laydate({istime: true, format: 'YYYY-MM-DD'})"
                                value="<?php
                                            if (empty(ArrayHelper::getValue($getData, 'start_bill_time', ''))){
                                                echo date("Y-m-01", time());
                                            }else{
                                                echo ArrayHelper::getValue($getData, 'start_bill_time', '');
                                            }
                                       ?>"
                                /> ~
                              <input size="10" name="end_bill_time" type="text" onclick="laydate({istime: true, format: 'YYYY-MM-DD'})"
                                     value="<?php
                                            if (empty(ArrayHelper::getValue($getData, 'end_bill_time', ''))){
                                                echo date("Y-m-d", time());
                                            }else{
                                                echo ArrayHelper::getValue($getData, 'end_bill_time', '');
                                            }
                                            ?>"
                                     style="margin-right: 10px" />

                出款通道名称：<select name="channl_id" style="margin-right: 10px;">
                                    <option value="0">请选择</option>
                                <?php
                                foreach($passageOfMoney as $key=>$value) {
                                    ?>
                                    <option value="<?=$key?>" <?=(ArrayHelper::getValue($getData, 'channl_id', 0)==$key) ? "selected=selected" : ""?>  ><?=$value?></option>
                                    <?php
                                }
                                ?>
                              </select>
                通道商编号：<input size="10" name="client_number" type="text"  style="margin-right: 10px;margin-left:10px" value="<?=ArrayHelper::getValue($getData, 'client_number', '')?>"/>
                 <span style="float: right;margin-right: 20px; "><input style="margin-left: 10px;"  type="submit" value="查询" id="search_submit"  class="btn btn-primary"></span>

                </div>
                <hr />
            </div>
            </form>
            <div style="margin-bottom: 10px">
                <span style="margin-right: 15px">对账成功总笔数：<b style="color: red"><?=$total?></b>笔</span>
                <span style="margin-right: 15px">总金额：<b style="color: red">￥<?=$total_money?></b> 元</span>
                <span style="margin-right: 15px">总手续费：<b style="color: red">￥<?=$total_fee?></b> 元</span>
                <span style="margin-right: 15px">差错账总笔数：<b style="color: red"><?=$total_bill_error?></b> 笔</span>
            </div>
            <table class="table table-bordered table-condensed table-hover table-striped dataTable no-footer">
                <thead>
                    <tr role="row">
                        <th>序号</th>
                        <th>出款通道</th>
                        <th>通道商编号</th>
                        <th>商户订单号</th>
                        <th>收款人</th>
                        <th>收款人银行账号</th>
                        <th>订单金额/元</th>
                        <th>手续费/元</th>
                        <th>账单日期</th>
                        <th>创建时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                if (!empty($result)){
                    $i = 0;
                    foreach($result as $value){
                    $i++;
                ?>
                <tr role="row">
                    <td><?=$i?></td>
                    <td><?=ArrayHelper::getValue($passageOfMoney, $value->channel_id, '')?></td>
                    <td><?=ArrayHelper::getValue($value, 'client_number', '')?></td>
                    <td><?=ArrayHelper::getValue($value, 'client_id', '')?></td>
                    <td><?=ArrayHelper::getValue($value, 'guest_account_name', '')?></td>
                    <td><?=ArrayHelper::getValue($value, 'guest_account', '')?></td>
                    <td><?=ArrayHelper::getValue($value, 'settle_amount', '')?>/元</td>
                    <td><?=ArrayHelper::getValue($value, 'settle_fee', '')?>/元</td>
                    <td><?=date("Y-m-d", strtotime(ArrayHelper::getValue($value, 'bill_number', '')))?></td>
                    <td><?=ArrayHelper::getValue($value, 'create_time', '')?></td>
                    <td><a href="/settlement/reconciliation/details?id=<?=ArrayHelper::getValue($value, 'id', '')?>">详情</a></td>
                </tr>
                <?php
                    }
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
var url_params = "<?= $url_params?>";
$(function(){
    $("#search_submit").click(function(){
        var start_bill_time = $("input[name='start_bill_time']").val();
        var end_bill_time = $("input[name='end_bill_time']").val();
        if (start_bill_time > end_bill_time){
            alert("查询时间有误");
            return false;
        }
    });
    $("#down_url").click(function(){
        location.href="/settlement/reconciliation/down?"+url_params;
    });
})
</script>