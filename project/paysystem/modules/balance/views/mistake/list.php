<?php

use yii\widgets\LinkPager;
use \yii\helpers\ArrayHelper;
$this->title = "米富逾期对账管理";
$status      = \app\models\Business::getStatus();
?>
<div class="col-lg-12 ui-sortable">
    <div class="box ui-sortable-handle" style="position: relative; left: 0px; top: 0px;">
        <header>
            <h5><?=$this->title. '>'?>差错账管理</h5>
        </header>
        <div class="body">
            <form action="?" method="get">
                <div style="margin-bottom: 20px">

                    回款通道：  <select style="margin-right: 10px" name="return_channel">
                        <option value="0">请选择</option>
                        <?php
                        foreach($return_channel as $key => $value) {
                            ?>
                            <option
                                <?php
                                if (ArrayHelper::getValue($filter_where, 'return_channel', 0) == $key){
                                    echo "selected = 'selected'";
                                }
                                ?>
                                value="<?=$key?>"><?=$value?></option>
                            <?php
                        }
                        ?>
                    </select>
                    商户订单号：<input type="text" name="client_id" value="<?=ArrayHelper::getValue($filter_where, 'client_id')?>" style="margin-right: 10px"/>
                    姓名：<input type="text" name="name" value="<?=ArrayHelper::getValue($filter_where, 'name')?>" style="margin-right: 10px"/>
                </div>
                <div style="margin-bottom:20px">
                    差错类型：<select style="margin-right: 10px" name="error_types">
                        <option value="0">请选择</option>
                        <?php
                        foreach($errorStatus as $key => $value) {
                            ?>
                            <option
                                <?php
                                if (ArrayHelper::getValue($filter_where, 'error_types', 0) == $key){
                                    echo "selected = 'selected'";
                                }
                                ?>
                                value="<?=$key?>"><?=$value?></option>
                            <?php
                        }
                        ?>
                    </select>

                    审核状态：
                    <select style="margin-right: 10px" name="auditing_status">
                        <option value="0">请选择</option>
                        <?php
                        foreach($auditingStatus as $key => $value) {
                            ?>
                            <option
                                <?php
                                if (ArrayHelper::getValue($filter_where, 'auditing_status', 0) == $key){
                                    echo "selected = 'selected'";
                                }
                                ?>
                                value="<?=$key?>"><?=$value?></option>
                            <?php
                        }
                        ?>
                    </select>

                    账单日期：<input style="margin-right: 10px;" type="text" onclick="laydate({istime: true, format: 'YYYY-MM-DD'})" name="start_time" value="<?=ArrayHelper::getValue($filter_where, 'start_time')?>"  /> ~
                    <input style="margin-left: 10px;"  type="text" onclick="laydate({istime: true, format: 'YYYY-MM-DD'})" name="end_time"  value="<?=ArrayHelper::getValue($filter_where, 'end_time')?>" />

                </div>
                <div style="text-align: center">
                    <input type="reset" value="重置"  class="btn btn-primary">
                    <input style="margin-left: 20px;margin-right:20px"  type="submit" value="查询" id="search_submit"  class="btn btn-primary">
                    <!--<input type="reset" value="导出数据" id="import_data"  class="btn btn-primary">-->
                </div>
                <hr />
            </form>
            <div style="margin-bottom: 20px">
                <span style="margin-right: 40px">总笔数：<b style="color: red;"><?=$total?></b>笔</span>
                <span style="margin-right: 40px">总金额：<b style="color: red;">￥<?=Number_format($amount,2)?> </b>元</span>
                <span style="margin-right: 40px">总手续费：<b style="color: red;">￥<?=Number_format($fee,2)?> </b>元</span>
            </div>
            <table class="table table-bordered table-condensed table-hover table-striped dataTable no-footer">
                <thead>
                <tr role="row">
                    <th style="text-align: center">序号</th>
                    <th style="text-align: center">项目名称</th>
                    <th style="text-align: center">回款通道</th>
                    <th style="text-align: center">订单号</th>
                    <th style="text-align: center">电子账户</th>
                    <th style="text-align: center">姓名</th>
                    <th style="text-align: center">第三方金额/米富金额</th>
                    <th style="text-align: center">手续费</th>

                    <th style="text-align: center">差错类型</th>
                    <th style="text-align: center">审核状态</th>
                    <th style="text-align: center">账单日期</th>
                    <th style="text-align: center">创建时间</th>
                    <th style="text-align: center">操作</th>
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
                            <td style="text-align: center"><?=$num?></td>
                            <td style="text-align: center"><?=ArrayHelper::getValue($aid, ArrayHelper::getValue($value, 'aid'))?></td>
                            <td style="text-align: center"><?=ArrayHelper::getValue($return_channel, ArrayHelper::getValue($value, 'return_channel'))?></td>
                            <td style="text-align: center"><?=ArrayHelper::getValue($value, 'client_id')?></td>
                            <td style="text-align: center"><?=ArrayHelper::getValue($value, 'guest_account')?></td>
                            <td style="text-align: center"><?=ArrayHelper::getValue($value, 'name')?></td>
                            <td style="text-align: center"><?=ArrayHelper::getValue($value, 'amount')?>/<?=ArrayHelper::getValue($value, 'payment_amount')?></td>
                            <td style="text-align: center"><?=ArrayHelper::getValue($value, 'settle_fee')?></td>
                            <!--<td><?/*=ArrayHelper::getValue($errorStatus, 3)*/?> </td>-->
                            <td style="text-align: center"><?=ArrayHelper::getValue($errorStatus, ArrayHelper::getValue($value, 'error_types'))?></td>
                            <td style="text-align: center"><?=ArrayHelper::getValue($auditingStatus, ArrayHelper::getValue($value, 'auditing_status'))?></td>
                            <td style="text-align: center"><?=ArrayHelper::getValue($value, 'payment_date')?></td>
                            <td style="text-align: center"><?=ArrayHelper::getValue($value, 'create_time')?></td>
                            <td style="text-align: center"><a href="/balance/mistake/details?id=<?=ArrayHelper::getValue($value, 'id', 0)?>">详情</a></td>
                        </tr>
                        <?php
                    }
                }else {
                    ?>
                    <tr role="row" style="text-align: center">
                        <td colspan="13">暂无数据</td>
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