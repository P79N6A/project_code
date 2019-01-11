<?php

use yii\widgets\LinkPager;
use \yii\helpers\ArrayHelper;

$this->title = "一亿元统计管理";

$oCoverdue = new \app\modules\balance\common\COverdue();
?>
<div class="col-lg-12 ui-sortable">
    <div class="box ui-sortable-handle" style="position: relative; left: 0px; top: 0px;">
        <header>
            <h5><?=$this->title. '>'?>逾期已收统计（2017年）</h5>
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
                <span style="margin-left: 50px">
                    已收本金累计：<b style="color: red">￥<?=$collect_amount?></b> 元
                </span>
                <span style="margin-left: 50px">
                    已收利息累计：<b style="color: red">￥<?=$collect_interest?></b> 元
                </span>
            </div>
            <div style="margin-bottom: 10px">
                <span>已收滞纳金累计：<b style="color: red">￥<?=$collect_overdue?></b> 元</span>
                <span style="margin-left: 50px">
                    已收总金额累计：<b style="color: red">￥<?=$all_amount?></b> 元
                </span>
            </div>
            
            <table class="table table-bordered table-condensed table-hover table-striped dataTable no-footer">
                <thead>
                    <tr role="row">
                        <th>序号</th>
                        <th>业务名称</th>
                        <th>业务类型</th>
                        <th>总笔数</th>
                        <th>已收本金</th>
                        <th>已收利息</th>
                        <th>已收滞纳金</th>
                        <th>已收总金额</th>
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
                    foreach ($data_set as $value) {
                        $num++;
                        $repay = ArrayHelper::getValue($value, 'all_actual_money', 0); //还款金额
                        $all_amount= ArrayHelper::getValue($value, 'all_amount', 0);  //本金
                        $all_interest_fee = ArrayHelper::getValue($value, 'all_interest_fee', 0); //利息
                        $all_chase_amount = bcsub(ArrayHelper::getValue($value, 'all_chase_amount', 0), $all_amount, 4); //滞纳金
                        $all_chase_amount = $all_chase_amount < 0 ? 0 : $all_chase_amount;
                        ?>
                        <tr role="row">
                            <td><?=$num;?></td>
                            <td>先花一亿元</td>
                            <td><?=ArrayHelper::getValue($value, 'days')?></td>
                            <td><?=ArrayHelper::getValue($value, 'total')?></td>
                            <td><?php
                                //已收本金
                                $amount = $oCOverdue->receivedPrincipal($repay, $all_amount);
                                echo $amount;
                                ?>
                            </td>
                            <td>
                                <?php
                                //已收利息--还款金额-本金
                                $interest = $oCOverdue->receivedInterest($repay, $all_amount, $all_interest_fee);
                                echo $interest;
                                ?>
                            </td>
                            <td>
                                <?php
                                //滞纳金加本金
                                $overdue = $oCOverdue->receivedOverdue($repay, $all_amount, $all_interest_fee, $all_chase_amount);
                                echo $overdue;
                                ?>
                            </td>
                            <td>
                                <?php
                                //已收总金额
                                echo bcadd(bcadd($amount, $interest, 4), $overdue, 4);
                                ?>
                            </td>
                            <td><?php
                                $bill_date = date("Y-m-d", strtotime(ArrayHelper::getValue($value, 'end_date')));
                                echo $bill_date;
                                ?></td>
                            <td><?=ArrayHelper::getValue($value, 'create_time')?></td>
                            <td><a href="/balance/rlastcount/downdata?bill_data=<?=$bill_date?>&days=<?=ArrayHelper::getValue($value, 'days')?>">导出</a></td>
                        </tr>
                        <?php
                    }
                }else {
                    ?>
                    <tr role="row">
                        <td colspan="12" style="text-align: center">暂无数据</td>
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