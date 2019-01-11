<?php

use yii\widgets\LinkPager;
use \yii\helpers\ArrayHelper;

$this->title = "一亿元统计管理";

$oCoverdue = new \app\modules\balance\common\COverdue();
?>
<div class="col-lg-12 ui-sortable">
    <div class="box ui-sortable-handle" style="position: relative; left: 0px; top: 0px;">
        <header>
            <h5><?=$this->title. '>'?>逾期待收统计（2017年）</h5>
        </header>
        <div class="body">
            <form action="?" method="get">
            <div style="margin-bottom: 10px">
                业务类型
                    <select style="margin-right: 20px" name="days" style="margin-right: 20px;margin-left: 5px" >
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
                账单日期 <input style="margin-right: 10px;" type="text" onclick="laydate({istime: true, format: 'YYYY-MM-DD'})" name="start_time" value="<?=ArrayHelper::getValue($condition, 'start_time')?>"  /> ~
                          <input style="margin-left: 10px;"  type="text" onclick="laydate({istime: true, format: 'YYYY-MM-DD'})" name="end_time"  value="<?=ArrayHelper::getValue($condition, 'end_time')?>" />

            </div>
            <div style="text-align: center;margin-top: 10px">
                <input style="margin-left: 20px;"  type="reset" value="重置" class="btn btn-primary">
                <input style="margin-left: 20px;"  type="submit" value="查询" id="search_submit"  class="btn btn-primary">
            </div>
            <hr />
            </form>
            <div style="margin-bottom: 10px">
                <span>总笔数：<b style="color: red"><?=$total?></b>笔</span>
                <span style="margin-left: 50px">应还本金累计：<b style="color: red">￥<?=$should_money?></b> 元</span>
                <span style="margin-left: 50px">应还利息累计：<b style="color: red">￥<?=$should_interest?></b> 元</span>
            </div>
            <div style="margin-bottom: 10px">
                <span>滞纳金累计：<b style="color: red">￥<?=$should_overdue?></b> 元</span>
                <span style="margin-left: 50px">应还总额累计：<b style="color: red">￥<?=$all_should?></b> 元</span>
            </div>
            
            <table class="table table-bordered table-condensed table-hover table-striped dataTable no-footer">
                <thead>
                    <tr role="row">
                        <th>序号</th>
                        <th>业务名称</th>
                        <th>业务类型</th>
                        <th>总笔数</th>
                        <th>应还本金</th>
                        <th>应还利息</th>
                        <th>滞纳金</th>
                        <th>应还总金额</th>
                        <th>账单日期</th>
                        <th>创建时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    if (!empty($data_set)) {
                        $oCOverdue = new \app\modules\balance\common\COverdue();
                        $num = 0;
                        foreach ($data_set as $key => $value){
                            $num ++;
                            //利息
                            $all_interest_fee = ArrayHelper::getValue($value, 'all_interest_fee', 0);
                            //本金
                            $all_amount = ArrayHelper::getValue($value, 'all_amount', 0);
                            //实际还款
                            $actual_money = ArrayHelper::getValue($value, 'all_actual_money', 0);
                            //滞纳金
                            $all_chase_amount = bcsub(ArrayHelper::getValue($value, 'all_chase_amount', 0), $all_amount,4);
                            $chase_amount = $all_chase_amount < 0 ? 0 : $all_chase_amount;
                            ?>
                            <tr>
                                <td><?=$num; //序号?></td>
                                <td>先花一亿元 <!--业务名称--></td>
                                <td><?=ArrayHelper::getValue($value, 'days'); //业务类型?></td>
                                <td>
                                    <?=number_format(ArrayHelper::getValue($value, 'total', 0)); //总笔数?>
                                    <?//=ArrayHelper::getValue($value, 'total_loan_id')?>
                                </td>
                                <td>
                                    <?php
                                    //应还本金
                                    $should_money = $oCOverdue->shouldPrincipal($actual_money, $all_amount);
                                    echo number_format($should_money, 2);
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                    //应还利息
                                    //$should_interest = $oCOverdue->shouldInterest($actual_money, $all_amount , $all_interest_fee);
                                    echo $all_interest_fee;//number_format($should_interest, 2);
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    //滞纳金
                                    $should_overdue = $oCOverdue->shouldOverdue($actual_money, $all_amount , $all_interest_fee, $chase_amount);
                                    echo number_format($should_overdue, 2);
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    //应还总金额
                                    $all_money = bcadd(bcadd($should_money, $should_interest, 4), $should_overdue, 4);
                                    echo number_format($should_money+$all_interest_fee+$should_overdue,2);//number_format($all_money, 2);
                                    ?>
                                </td>
                                <td>
                                    <?php
                                        $bill_data = date("Y-m-d", strtotime(ArrayHelper::getValue($value, 'end_date'))); //账单日期
                                        echo $bill_data;
                                    ?></td>
                                <td><?=ArrayHelper::getValue($value, 'create_time'); //创建时间?></td>
                                <td><a href="/balance/clastcount/downdata?bill_data=<?=$bill_data?>&days=<?=ArrayHelper::getValue($value, 'days')?>">导出</a></td>
                            </tr>
                            <?php
                    }
                }else{
                    ?>
                        <tr role="row">
                            <td colspan="11" style="text-align: center">暂无数据！</td>
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
            var time = '2018-01-01';//Date.parse(new Date('2018-01-01'));
            //alert(start_time);
            if (start_time > end_time){
                alert("查询时间有误 ");
                return false;
            }
            if(start_time > time){
                alert("查询时间不能大于2018-01-01");
                return false;
            }
            if(end_time > time){
                alert("查询时间不能大于2018-01-01");
                return false;
            }
        });
    });
</script>