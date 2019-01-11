<?php

use yii\widgets\LinkPager;
use \yii\helpers\ArrayHelper;

$this->title = "一亿元统计管理";

$oCoverdue = new \app\modules\balance\common\COverdue();
?>
<div class="col-lg-12 ui-sortable">
    <div class="box ui-sortable-handle" style="position: relative; left: 0px; top: 0px;">
        <header>
            <h5><?=$this->title. '>'?>未到期统计</h5>
        </header>
        <div class="body">
            <form action="?" method="get" id="post_form">
            <input name="_csrf" type="hidden" id="_csrf" value="<?= \Yii::$app->request->csrfToken ?>">
            <div style="margin-bottom: 10px">
                分期类型
                <select name="types_of_stages" style="margin-right: 20px;margin-left: 5px" id="types_of_stages" >
                    <option value="0">请选择</option>
                    <?php
                    foreach($types_of_stages as $key => $value) {
                        ?>
                        <option
                            <?php
                            if (ArrayHelper::getValue($condition, 'types_of_stages') == $key){
                                echo "selected = 'selected'";
                            }
                            ?>
                                value="<?=$key?>"><?=$value?></option>
                        <?php
                    }
                    ?>
                </select>
                资金方
                <select name="capital_side" style="margin-right: 20px;margin-left: 5px" id="capital_side" >
                    <option value="0">请选择</option>
                    <?php
                    foreach($capital_side as $key => $value) {
                        ?>
                        <option
                            <?php
                            if (ArrayHelper::getValue($condition, 'capital_side') == $key){
                                echo "selected = 'selected'";
                            }
                            ?>
                                value="<?=$key?>"><?=$value?></option>
                        <?php
                    }
                    ?>
                </select>
                借款编号 <input type="text" value="<?=ArrayHelper::getValue($condition, 'loan_id')?>" name="loan_id" style="margin-right: 20px;margin-left: 5px" />
                借款天数
                    <select name="days" id="days_select" style="margin-right: 20px;margin-left: 5px" >
                        <option value="0">请选择</option>
                        <?php
                        foreach($debtType as $key => $value) {
                            ?>
                            <option
                                <?php
                                if (ArrayHelper::getValue($condition, 'days') == $key){
                                    echo "selected = 'selected'";
                                }
                                ?>
                                value="<?=$key?>"><?=$value?>天</option>
                            <?php
                        }
                        ?>
                    </select>

            </div>
            <div style="margin-top: 20px">
                手机号<input type="text" value="<?=ArrayHelper::getValue($condition, 'mobile')?>" name="mobile" style="margin-right: 20px;margin-left: 5px"  />
                账单日期 <input style="margin-right: 10px;" type="text" onclick="laydate({istime: true, format: 'YYYY-MM-DD'})" name="start_time" value="<?=ArrayHelper::getValue($condition, 'start_time')?>"  /> ~
                          <input style="margin-left: 10px;"  type="text" onclick="laydate({istime: true, format: 'YYYY-MM-DD'})" name="end_time"  value="<?=ArrayHelper::getValue($condition, 'end_time')?>" />

            </div>
            <div style="text-align: center;margin-top: 10px">
                <input style="margin-left: 20px;"  type="reset" value="重置" class="btn btn-primary">
                <input style="margin-left: 20px;"  type="submit" value="查询" id="search_submit"  class="btn btn-primary">
                <input style="margin-left: 20px;"  type="button" value="导出" id="import_data" class="btn btn-primary">
            </div>
            <hr />
            </form>
            <div style="margin-bottom: 10px">
                <span>总笔数：<b style="color: red"><?=$total?></b>笔</span>
                <span style="margin-left: 50px">待收本金累计：<b style="color: red"><?=$amount?></b> 元</span>
                <span style="margin-left: 50px">待收利息累计：<b style="color: red">￥<?=$withdraw_fee?></b> 元</span>
            </div>
            <div style="margin-bottom: 10px">
                <span >待收总额累计：<b style="color: red ">￥<?=$total_fee?></b> 元</span>

            </div>
            
            <table class="table table-bordered table-condensed table-hover table-striped dataTable no-footer">
                <thead>
                    <tr role="row">
                        <th>序号</th>
                        <th>借款编号</th>
                        <th>分期类型</th>
                        <th>资金方</th>
                        <th>出款方式</th>
                        <th>借款天数</th>
                        <th>手机号</th>
                        <th>存款电子账户</th>
                        <!--<th>资金方</th>-->
                        <th>借款日期</th>
                        <th>应还款日期</th>
                        <th>待收本金</th>
                        <th>待收利息</th>
                        <th>待收总计</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    if (!empty($getData)) {
                        $num = 0;
                        foreach ($getData as $k => $v) {
                            $num ++;
                            $withdraw_fee = ArrayHelper::getValue($v, 'withdraw_fee'); //应还利息
                            $real_amount = ArrayHelper::getValue($v, 'real_amount'); //本金
                            $source = "花生米富";
                            ?>
                            <tr role="row">
                                <td><?=$num;?></td>
                                <td><?=ArrayHelper::getValue($v, 'loan_id');?></td>
                                <td>单期</td>
                                <td>
                                    <?php
                                    $fund = ArrayHelper::getValue($v, "fund");
                                    echo ArrayHelper::getValue($capital_side, $fund);
                                    ?>

                                </td>
                                <td>
                                    <?=($fund==10) ? "体内" : "体外";?>
                                </td>
                                <td><?=ArrayHelper::getValue($v, 'days');?></td>
                                <td><?=ArrayHelper::getValue($v, 'mobile')?></td>
                                <td><?=ArrayHelper::getValue($v, 'accountId')?></td>
                                <!--<td><?=$source?></td>-->
                                <td><?=ArrayHelper::getValue($v, 'create_time')?></td>
                                <td><?=ArrayHelper::getValue($v, 'end_date')?></td>
                                <td><?=$real_amount+0?></td>
                                <td><?=ArrayHelper::getValue($v, 'withdraw_fee')+0?></td>
                                <td><?=ArrayHelper::getValue($v, 'real_amount')+ArrayHelper::getValue($v, 'withdraw_fee')?></td>

                            </tr>
                            <?php
                        }
                    }else {
                        ?>
                        <tr role="row">
                            <td colspan="13" style="text-align: center">暂无数据！</td>
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
        //导出表格
        $("#import_data").click(function(){
            var href_url = '';
            var loan_id = $("input[name='loan_id']").val();//借款编号
            if (typeof(loan_id) != "undefined" && loan_id != ''){
                href_url += "&loan_id="+loan_id
            }
            var days = $("#days_select").change().val()// 业务类型
            if (typeof(days) != "undefined" && days != ''){
                href_url += "&days="+days
            }
            //var overdue_type = $("#overdue_type_select").change().val(); // 逾期类型
            var mobile = $("input[name='mobile']").val(); //手机号
            if (typeof(mobile) != "undefined" && mobile != ''){
                href_url += "&mobile="+mobile
            }
            var capital_side = $("input[name='capital_side']").val();
            if (typeof(capital_side) != "undefined" && capital_side != ''){
                href_url += "&capital_side="+capital_side
            }
            var start_time = $("input[name='start_time']").val(); //账单日期
            if (typeof(start_time) != "undefined" && start_time != ''){
                href_url += "&start_time="+start_time
            }
            var end_time = $("input[name='end_time']").val();//账单日期
            if (typeof(end_time) != "undefined" && end_time != ''){
                href_url += "&end_time="+end_time
            }
            location.href = '/balance/beforecount/downdata?' + href_url;
            //location.href = '/balance/beforecount/downdata?&loan_id='+ loan_id + "&days=" + days +  "&mobile=" + mobile + "&start_time=" + start_time + "&end_time=" + end_time;
        });
    });
</script>