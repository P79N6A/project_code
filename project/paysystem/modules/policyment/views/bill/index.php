<?php
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use yii\helpers\Html;
$this->title = '保险管理';
$status    = \app\models\policy\PolicyCheckbill::getStatus();
$fund      = \app\models\policy\ZhanPolicy::getFund();
?>
<style type="text/css">
    /*显示浮层*/
    .mask-show{
        width: 100%;
        transition-duration: 1s;
    }
</style>
<div class="col-lg-12 ui-sortable">
    <div class="box ui-sortable-handle" style="position: relative; left: 0px; top: 0px;">
        <header>
            <h5>对账列表</h5>
        </header>
        <div class="body">
            <form action="/policyment/bill/index" method="GET" class="form-inline">
                <div class="row form-group">
                    <div class="col-lg-2" style="width: auto">
                        <input size="15" class="form-control" value="<?=isset($get['billDate'])?$get['billDate']:''?>" name="billDate" placeholder="对账日期"  type="text" onclick="laydate({istime: true, format: 'YYYY-MM-DD'})">
                    </div>

                    <div class="col-lg-2" style="width: auto">
                        <input  size="15" class="form-control" value="<?=isset($get['user_mobile'])?$get['user_mobile']:''?>" name="user_mobile" placeholder="电话" type="text">
                    </div>
                    <div class="col-lg-2" style="width: auto">
                        <select class="form-control" name="billStatus" tabindex="14" style="width: 100px;">
                            <option value="">对账状态</option>
                            <?php foreach($status as $k => $v): ?>
                                <option <?php echo isset($get['billStatus']) && $get['billStatus']!=='' && $get['billStatus']==$k? 'selected':'' ?> value="<?=$k?>"><?=$v?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-2" style="width: auto">
                        <select class="form-control" name="fund" tabindex="14" style="width: 100px;">
                            <option value="">资金方</option>
                            <?php foreach($fund as $k => $v): ?>
                                <option <?php echo isset($get['fund']) && $get['fund']!=='' && $get['fund']==$k? 'selected':'' ?> value="<?=$k?>"><?=$v?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                   
                    
                    <div class="col-lg-1" style="width: auto">
                        <button type="submit" class="btn btn-primary">搜索</button>
                    </div>
                </div>

            </form>
            <br>
            <table class="table table-bordered table-condensed table-hover table-striped dataTable no-footer">
                <thead>
                <tr role="row">
                    <th>ID</th>
                    <th>AID</th>
                    <th>姓名</th>
                    <th>手机号</th>
                    <th>单号</th>
                    <th>金额</th>
                    <th>保费金额</th>
                    <th>对账时间</th>
                    <th>资金方</th>
                    <th>状态</th>
                    <th>创建时间</th>
                    <th>操作</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($res)): ?>
                        <?php foreach ($res as $key => $val): ?>
                            <tr role="row" class="even">
                                <td><?= $val['id'] ?></td>
                                <td><?= $val['aid'] ?></td>
                                <td><?= $val['user_name']?></td>
                                <td><?= $val['user_mobile'] ?></td>
                                <td>订单号：<?= $val['channelOrderNo'] ?><br/>
                                    保单号：<?= $val['policyNo'] ?><br/>
                                    支付单号：<?= $val['orderId'] ?>
                                </td>
                                <td><?= $val['premium'] ?></td>
                                <td><?= $val['policy_premium'] ?></td>
                                <td><?= $val['billDate'] ?></td>
                                <td><?php echo isset($fund[$val['fund']]) ? $fund[$val['fund']] : '未知' ?></td>
                                <td><?php echo isset($status[$val['billStatus']]) ? $status[$val['billStatus']] : '未知' ?></td>
                                <td><?= $val['create_time'] ?></td>
                                <td>
                                <?php
                            if ($val['billStatus']==1&&!empty($val['remark'])){?>
                                <a href="javascript:void(0)" class="btn btn-primary" onclick="showRemark('<?=$val['remark']?>')">查看</a>
                            <?php }else if($val['billStatus']==2){?>
                                <a href="javascript:void(0)" onclick="msgShow('<?=$val['id']?>','<?=$val['remark']?>')" class="btn btn-primary">备注</a>
                                <a href="javascript:void(0)" onclick="completeBill('<?=$val['id']?>','<?=$val['remark']?>')" class="btn btn-primary">完成</a>
                            <?php } ?>
                     
                        </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>

                </tbody>                
            </table>
        </div>
    </div>
</div>
<div id="remark_div" style="display:none;float:left; left: 40px;  position: relative;top: -300px; background-color: #dddddd; width: 70%;padding-bottom: 10px">
<span style="font-size:18px">对账备注</span>
<textarea name="show_remark" id="show_remark" style="width:100%;height:150px"></textarea><br/>
<button type="submit" style="margin-left:40%"class="btn btn-primary" onclick="closeRemark()">关闭</button>
</div>
<div id="msg_div" style="display:none;float:left; left: 40px;  position: relative;top: -300px; background-color: #dddddd; width: 70%;padding-bottom: 10px">
<span style="font-size:18px">对账备注</span>
<input name="bill_id" id="bill_id" type="hidden" value="">
<input name="_csrf" type="hidden" id="_csrf" value="<?= \Yii::$app->request->csrfToken ?>">
<textarea name="remark" id="remark" style="width:100%;height:150px"></textarea><br/>
<button type="submit" style="margin-left:40%"class="btn btn-primary" onclick="submitRemark()">确定</button>
<button type="submit" style="margin-left:20px"class="btn btn-primary" onclick="resetRemark()">取消</button>
</div>
<script src="/bootstrap/js/bootstrap.min.js" type="text/javascript" charset="utf-8"></script>
<script src="/laydate/laydate.dev.js" type="text/javascript" charset="utf-8"></script>
<script>
function showRemark(remark){
    $("#show_remark").val(remark);
    $("#remark_div").show();
}
function msgShow(id,remark){
    $("#bill_id").val(id);
    $("#remark").val(remark);
    $("#msg_div").show();
}
function completeBill(id,remark){
    if(remark==''){
        alert('请先做账单备注');
        return false;
    }
    var _csrf = $("#_csrf").val();
    $.ajax({
        url:"/policyment/bill/completebill",
        type:"POST",
        dataType:"JSON",
        data:{id:id,_csrf:_csrf},
        success:function(data){
            alert(data.res_msg);
            if(data.res_code=='0'){
                window.location.reload();
            }
        }
        
    })
}
function submitRemark(){
    var id = $("#bill_id").val();
    var remark = $("#remark").val();
    var _csrf = $("#_csrf").val();
    if(id==""){
        alert("参数错误");
        $("#msg_div").hide();
        return false;
    }
    if(remark==""){
        alert("请输入备注信息");
        return false;
    }
    $.ajax({
        url:"/policyment/bill/saveremark",
        type:"POST",
        dataType:"JSON",
        data:{id:id,remark:remark,_csrf:_csrf},
        success:function(data){
            alert(data.res_msg);
            if(data.res_code=='0'){
                window.location.reload();
            }
        }
        
    })
}
function resetRemark(){
    $("#bill_id").val('');
    $("#remark").val('');
    $("#msg_div").hide();
}
function closeRemark(){
    $("#show_remark").val('');
    $("#remark_div").hide();
}
</script>