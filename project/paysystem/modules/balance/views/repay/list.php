<?php

use yii\widgets\LinkPager;
use \yii\helpers\ArrayHelper;
$this->title = "统计管理";
?>
<div class="col-lg-12 ui-sortable">
    <div class="box ui-sortable-handle" style="position: relative; left: 0px; top: 0px;">
        <header>
            <h5><?=$this->title. '>'?>一亿元统计管理>正常回款统计</h5>
        </header>
        <div class="body">
            <form action="?" method="get">
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
                债权类型：<select style="margin-right: 10px;  padding:5px;" name="days">
                                        <option value="0">请选择</option>
                                <?php
                                    foreach($bondType as $key => $value) {
                                        ?>
                                        <option
                                            <?php
                                                if ($days == $key){
                                                    echo "selected = 'selected'";
                                                }
                                            ?>
                                            value="<?=$key?>"><?=$value?></option>
                                        <?php
                                    }
                                ?>
                            </select>
                还款方式：<select style="margin-right: 10px;  padding:5px;" name="repay_type">
                    <option value="0">请选择</option>
                    <?php
                    foreach($wayOfPayment as $key => $value) {
                        ?>
                        <option
                            <?php
                            if ($repay_type == $key){
                                echo "selected = 'selected'";
                            }
                            ?>
                                value="<?=$key?>"><?=$value?></option>
                        <?php
                    }
                    ?>
                </select>
                账单日期：<input style="margin-right: 10px;" type="text" onclick="laydate({istime: true, format: 'YYYY-MM-DD'})" name="start_time" value="<?=$start_time?>"  /> ~
                          <input style="margin-left: 10px;"  type="text" onclick="laydate({istime: true, format: 'YYYY-MM-DD'})" name="end_time"  value="<?=$end_time?>" />
                            <span style="color: red">（*最大查询间隔为31天）</span>
            </div>
            <div style="text-align: center">
                <input type="reset" value="重置"  class="btn btn-primary">
                <input style="margin-left: 20px;"  type="submit" value="查询" id="search_submit"  class="btn btn-primary">
            </div>
            <hr />
            </form>
            <div style="margin-bottom: 10px">
                <span>总笔数：<b style="color: red"><?=ArrayHelper::getValue($repayTotal, 'nums',0)?></b>笔</span>
                <span style="margin-left: 50px">借款本金累计：<b style="color: red">￥<?=(ArrayHelper::getValue($repayTotal, 'money',0)==null?0:ArrayHelper::getValue($repayTotal, 'money',0))?></b> 元</span>
                <span style="margin-left: 50px">已还利息累计：<b style="color: red">￥<?=ArrayHelper::getValue($repayTotal, 'fee',0)==NULL?0:ArrayHelper::getValue($repayTotal, 'fee',0)?></b> 元</span><br>
                <span>减免累计：<b style="color: red">￥<?=(ArrayHelper::getValue($repayTotal, 'coupon',0)==null?0:ArrayHelper::getValue($repayTotal, 'coupon',0))?></b> 元</span>
                <span style="margin-left: 50px">点赞减息累计：<b style="color: red">￥<?=(ArrayHelper::getValue($repayTotal, 'likes',0)==null?0:ArrayHelper::getValue($repayTotal, 'likes',0))?></b> 元</span>
                <span style="margin-left: 50px">已还总额累计：<b style="color: red">￥<?=(ArrayHelper::getValue($repayTotal, 'money',0)==null?0:ArrayHelper::getValue($repayTotal, 'money',0))+(ArrayHelper::getValue($repayTotal, 'fee',0)==NULL?0:ArrayHelper::getValue($repayTotal, 'fee',0))+(ArrayHelper::getValue($repayTotal, 'coupon',0)==null?0:ArrayHelper::getValue($repayTotal, 'coupon',0))+(ArrayHelper::getValue($repayTotal, 'likes',0)==null?0:ArrayHelper::getValue($repayTotal, 'likes',0))?></b> 元</span>
            </div>
            <table class="table table-bordered table-condensed table-hover table-striped dataTable no-footer">
                <thead>
                    <tr role="row">
                        <th>序号</th>
                        <th>分期类型</th>
                        <th>资金方</th>
                        <th>借款天数</th>
                        <th>总笔数</th>
                        <th>借款本金</th>
                        <th>已还本金</th>
                        <th>已还利息</th>
                        <th>减免金额</th>
                        <th>点赞减息</th>
                        <th>应还总金额</th>
                        <th>还款方式</th>
                        <th>账单日期</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    if (!empty($return_data)) {
                        $num = 0;
                        foreach($return_data as $key=>$value) {
                            $num ++;
//                            $keys =explode('|',$key);
                            ?>
                            <tr role="row">
                                <td><?=$num;?></td>
                                <td>单期</td>
                                <td>
                                    <?php
                                    $fund = ArrayHelper::getValue($value, "funds");
                                    echo ArrayHelper::getValue($capital_side, $fund);
                                    ?>

                                </td>
                                <td><?=ArrayHelper::getValue($value, 'days','0')?></td>
                                <td><?=ArrayHelper::getValue($value, 'nums','')?></td>
                                <td><?=ArrayHelper::getValue($value, 'tomoney','0')?></td>
                                <td><?=ArrayHelper::getValue($value, 'actual_money','0')?></td>
                                <td><?=ArrayHelper::getValue($value, 'tofee','0')?></td>
                                <td><?=ArrayHelper::getValue($value, 'tocoupon','0')?></td>
                                <td><?=ArrayHelper::getValue($value, 'tolikes','0')?></td>
                                <td><?=( ArrayHelper::getValue($value, 'tomoney','0') + ArrayHelper::getValue($value, 'tofee','0') - ArrayHelper::getValue($value, 'coupon','0') - ArrayHelper::getValue($value, 'tolikes','0') )?></td>
                                <td><?php
                                    $platform = ArrayHelper::getValue($value, 'platform',0);
                                    if($platform == 26 || $platform==28){
                                        echo '体内';
                                    }else{
                                        echo '体外';
                                    }
                                    ?></td>
                                <td><?=ArrayHelper::getValue($value, 'datetimes','未知')?></td>
                                <td>
                                    <a href="/balance/repay/repaydown?days=<?=ArrayHelper::getValue($value, 'days','')?>&billtime=<?=ArrayHelper::getValue($value, 'datetimes')?>">导出明细</a><br>
                                </td>
                            </tr>
                            <?php
                        }
                    }else {
                        ?>
                        <tr role="row" style="text-align: center">
                            <td colspan="11">暂无数据</td>
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