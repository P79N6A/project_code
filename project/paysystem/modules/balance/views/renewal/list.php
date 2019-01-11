<?php

use yii\widgets\LinkPager;
use \yii\helpers\ArrayHelper;
use app\modules\balance\models\yyy\User;
use app\modules\balance\common\CRepay;
$this->title = "统计管理";
?>
<div class="col-lg-12 ui-sortable">
    <div class="box ui-sortable-handle" style="position: relative; left: 0px; top: 0px;">
        <header>
            <h5><?=$this->title. '>'?>一亿元统计管理>展期服务费统计</h5>
        </header>
        <div class="body">
            <form action="?" method="get">
            <div style="margin-bottom: 10px">
                借款编号 <input type="text" value="<?=$loan_id?>" name="loan_id" style="margin-right: 20px;margin-left: 5px" />
                展期订单 <input type="text" value="<?=$order_id?>" name="order_id" style="margin-right: 20px;margin-left: 5px" />
                债权类型：<select style="margin-right: 10px;  padding:5px;" name="days" id='days'>
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
                账单日期：<input style="margin-right: 10px;" type="text" onclick="laydate({istime: true, format: 'YYYY-MM-DD'})" name="start_time" value="<?=$start_time?>"  /> ~
                          <input style="margin-left: 10px;"  type="text" onclick="laydate({istime: true, format: 'YYYY-MM-DD'})" name="end_time"  value="<?=$end_time?>" />
                          <input name="_csrf" type="hidden" id="_csrf" value="<?= \Yii::$app->request->csrfToken ?>">
            </div>
            <div style="text-align: center">
                <input type="reset" value="重置"  class="btn btn-primary">
                <input style="margin-left: 20px;"  type="submit" value="查询" id="search_submit"  class="btn btn-primary">
                <input style="margin-left: 20px;" id='explod' type="button" value="导出"  class="btn btn-primary">
            </div>
            <hr />
            </form>
            <div style="margin-bottom: 10px">
                <span>总笔数：<b style="color: red"><?=$totleCount?></b>笔</span>
                <span style="margin-left: 50px">展期本金总金额：<b style="color: red"><?=$amount?></b>元</span>
            </div>
            <div style="margin-bottom: 10px">
                <span>展期服务费总金额：<b style="color: red"><?=$actual_money?></b>元</span>
                <span style="margin-left: 50px">展期利息总金额：<b style="color: red"><?=$interest_fee?></b>元</span>
            </div>
            <table class="table table-bordered table-condensed table-hover table-striped dataTable no-footer">
                <thead>
                    <tr role="row">
                        <th>序号</th>
                        <th>借款编号</th>
                        <th>展期订单号</th>
                        <th>债券类型</th>
                        <th>逾期类型</th>
                        <th>手机号</th>
                        <th>展期类型</th>
                        <th>本金</th>
                        <th>服务费金额</th>
                        <th>创建时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    if (!empty($return_data)) {
                        $num = 0;
                        $oUser = new User();
                        $oCrepay = new CRepay();
                        foreach($return_data as $value) {
                            $num ++;
                            $userInfo = $oUser->getUserInfo(ArrayHelper::getValue($value, 'user_id','0'));
                            $phone = ArrayHelper::getValue($userInfo, 'mobile','');
                            ?>
                            <tr role="row">
                                <td><?=$num;?></td>
                                <td><?=ArrayHelper::getValue($value, 'loan_id','0')?></td>
                                <td><?=ArrayHelper::getValue($value, 'order_id','0')?></td>
                                <td><?=ArrayHelper::getValue($value, 'days','')?></td>
                                <td><?=$oCrepay->getRenewalType(ArrayHelper::getValue($value, 'end_date',''),ArrayHelper::getValue($value, 'create_time',''),ArrayHelper::getValue($value, 'loan_status',''))?></td>
                                <td><?=$phone?></td>
                                <td><?=$oCrepay->getIsyq(ArrayHelper::getValue($value, 'end_date',''),ArrayHelper::getValue($value, 'create_time',''),ArrayHelper::getValue($value, 'loan_status',''))?></td>
                                <td><?=ArrayHelper::getValue($value, 'amount','0')?></td>
                                <td><?=ArrayHelper::getValue($value, 'money','0')?></td>
                                <td><?=ArrayHelper::getValue($value, 'create_time','0')?></td>
                                <td>
                                    <a href="/balance/renewal/details?order_id=<?=ArrayHelper::getValue($value, 'order_id','')?>&loan_id=<?=ArrayHelper::getValue($value, 'loan_id',0)?>">详情</a><br>
                                </td>
                            </tr>
                            <?php
                        }
                    }else {
                        ?>
                        <tr role="row" style="text-align: center">
                            <td colspan="10">暂无数据</td>
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
        
        $("#explod").click(function(){
            var days = $("#days").val();
            var start_time = $("input[name='start_time']").val();
            var end_time = $("input[name='end_time']").val();
            var _csrf = $("#_csrf").val();
            alert('正在导出，请耐心等待。。。');
            location.href = '/balance/renewal/renewaldown?days='+ days + '&start_time='+ start_time + '&end_time=' + end_time
        });
    });
</script>