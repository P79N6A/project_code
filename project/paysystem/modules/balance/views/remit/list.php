<?php

use yii\widgets\LinkPager;
use \yii\helpers\ArrayHelper;
$this->title = "首页 > 一亿元统计管理> 出款统计";
?>
<div class="col-lg-12 ui-sortable">
    <div class="box ui-sortable-handle" style="position: relative; left: 0px; top: 0px;">
        <header>
            <h5><?=$this->title?></h5>
        </header>
        <div class="body">
            <form action="/balance/remit/list" method="get">
            <div style="margin-bottom: 10px">
               <!-- 出款方式：  <select style="margin-right: 10px; padding:5px;" name="type">
                    <option value="">请选择</option>
                    <?php /*foreach($wayOfPayment as $k => $v): */?>
                        <option <?php /*echo isset($get['wayOfPayment']) && ($get['wayOfPayment']!=='') &&$get['wayOfPayment']==$k? 'selected':'' */?> value="<?/*=$k*/?>"><?/*=$v*/?></option>
                    <?php /*endforeach; */?>
                </select>-->
                资金方：  <select style="margin-right: 10px; padding:5px;" name="capitalSide">
                    <option value="">请选择</option>
                    <?php foreach($capitalSide as $k => $v): ?>
                        <option <?php echo isset($get['capitalSide']) && ($get['capitalSide']!=='') &&$get['capitalSide']==$k? 'selected':'' ?> value="<?=$k?>"><?=$v?></option>
                    <?php endforeach; ?>
                </select>
                分期类型：  <select style="margin-right: 10px; padding:5px;" name="typesOfStages">
                    <option value="">请选择</option>
                    <?php foreach($typesOfStages as $k => $v): ?>
                        <option <?php echo isset($get['typesOfStages']) && ($get['typesOfStages']!=='') &&$get['typesOfStages']==$k? 'selected':'' ?> value="<?=$k?>"><?=$v?></option>
                    <?php endforeach; ?>
                </select>
                借款天数：  <select style="margin-right: 10px; padding:5px;" name="type">
                            <option value="">请选择</option>
                                <?php foreach($bondType as $k => $v): ?>
                                <option <?php echo isset($get['type']) && ($get['type']!=='') &&$get['type']==$k? 'selected':'' ?> value="<?=$k?>"><?=$v?></option>
                            <?php endforeach; ?>
                            </select>

            </div>
                <div style="margin-bottom: 10px">
                    账单日期：<input style="margin-right: 10px;" type="text" onclick="laydate({istime: true, format: 'YYYY-MM-DD'})" name="start_time" value="<?=isset($filter_where['start_time'])?$filter_where['start_time']:''?>"  /> ~
                    <input style="margin-left: 10px;"  type="text" onclick="laydate({istime: true, format: 'YYYY-MM-DD'})" name="end_time"  value="<?=isset($filter_where['end_time'])?$filter_where['end_time']:''?>" />
                </div>
            <div style="text-align: center">
                    <input type="reset" value="重置"  class="btn btn-primary">
                    <input style="margin-left: 20px;margin-right:20px"  type="submit" value="查询" id="search_submit"  class="btn btn-primary">
                    <!--<input type="reset" value="导出数据" id="import_data"  class="btn btn-primary">-->
                </div>
            <hr />
            </form>
            <div style="margin-bottom: 10px">
                <span>总笔数：<b style="color: red"><?=$all_num?></b>笔</span>
                <span style="margin-left: 50px">应还本金累计：<b style="color: red"><?=$money?></b> 元</span>
                <span style="margin-left: 50px">应还利息累计：<b style="color: red">￥<?=$fee?></b> 元</span>
                <span style="margin-left: 50px">应还总额累计：<b style="color: red">￥<?=$total?></b> 元</span>
            </div>
            <table class="table table-bordered table-condensed table-hover table-striped dataTable no-footer">
                <thead>
                    <tr role="row">
                        <th>序号</th>
                        <th>分期类型</th>
                        <th>资金方</th>
                       <th>出款方式</th>
                        <th>借款天数</th>
                        <th>总笔数</th>
                        <th>应还本金</th>
                        <th>应还利息</th>
                        <th>应还总金额</th>
                        <th>账单日期</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    if (!empty($res)) {
                        foreach($res as $k=>$value) {
                            ?>
                            <tr role="row">
                                <td><?=$k+1;?></td>
                                <td>单期</td>
                                <td> <?php
                                    $fund = ArrayHelper::getValue($value, "fund");
                                    echo ArrayHelper::getValue($capitalSide, $fund);
                                    ?></td>
                                <td><?=($fund==10) ? "体内" : "体外";?></td>
                                <td><?=ArrayHelper::getValue($value, 'days')?></td>
                                <td><?=ArrayHelper::getValue($value, 'all_num','0')?></td>
                                <td><?=ArrayHelper::getValue($value, 'money','0')?></td>
                                <td><?=ArrayHelper::getValue($value, 'fee','0')?></td>
                                <td><?=ArrayHelper::getValue($value, 'all_money','0')?></td>
                                <td><?=ArrayHelper::getValue($value, 'bill_date','')?></td>
                                <td>
                                    <a href="/balance/remit/export?bill_date=<?=ArrayHelper::getValue($value, 'bill_date')?>&days=<?=ArrayHelper::getValue($value, 'days')?>">导出明细</a><br>
                                </td>
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
            var start_time = $("input[name='start_time']").val();
            var end_time = $("input[name='end_time']").val();
            if (start_time > end_time){
                alert("查询时间有误 ");
                return false;
            }
        });
    });
</script>