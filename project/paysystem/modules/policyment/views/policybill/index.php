<?php

use yii\widgets\LinkPager;
use \yii\helpers\ArrayHelper;
$this->title = "对账列表";
?>
<div class="col-lg-12 ui-sortable">
    <div class="box ui-sortable-handle" style="position: relative; left: 0px; top: 0px;">
        <header>
            <h5><?=$this->title?></h5>
        </header>
        <div class="body">
            <form action="/policyment/policybill/index" method="get">
            <div style="margin-bottom: 10px">
            <div class="col-lg-2" style="width: auto">
                        <input size="15" class="form-control" value="<?=isset($get['client_id'])?$get['client_id']:''?>" name="client_id" placeholder="流水号" type="text">
                    </div>
                    <div class="col-lg-2" style="width: auto">
                        <input size="15" class="form-control" value="<?=isset($get['policyNo'])?$get['policyNo']:''?>" name="policyNo" placeholder="保单号" type="text">
                    </div>
                类型：  <select style="margin-right: 10px; padding:5px;" name="status">
                            <option value="">请选择</option>
                                <?php foreach($policyStatus as $k => $v): ?>
                                <option <?php echo isset($get['status']) && ($get['status']!=='') &&$get['status']==$k? 'selected':'' ?> value="<?=$k?>"><?=$v?></option>
                            <?php endforeach; ?>
                            </select>
                      
                账单日期：<input style="margin-right: 10px;" type="text" onclick="laydate({istime: true, format: 'YYYY-MM-DD'})" name="start_time" value="<?=isset($get['start_time'])?$get['start_time']:''?>"  /> ~
                          <input style="margin-left: 10px;"  type="text" onclick="laydate({istime: true, format: 'YYYY-MM-DD'})" name="end_time"  value="<?=isset($get['end_time'])?$get['end_time']:''?>" />

            </div>
            <div style="text-align: center">
                    <input type="reset" value="重置"  class="btn btn-primary">
                    <input style="margin-left: 20px;margin-right:20px"  type="submit" value="查询" id="search_submit"  class="btn btn-primary">
                    <!--<input type="reset" value="导出数据" id="import_data"  class="btn btn-primary">-->
                </div>
            <hr />
            </form>
            
            <table class="table table-bordered table-condensed table-hover table-striped dataTable no-footer">
                <thead>
                    <tr role="row">
                        <th>ID</th>
                        <th>流水号</th>
                        <th>保单号</th>
                        <th>出保时间</th>
                        <th>开始时间</th>
                        <th>结束时间</th>
                        <th>姓名</th>
                        <th>保费</th>
                        <th>状态</th>
                        <th>创建时间</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    if (!empty($res)) {
                        foreach($res as $k=>$value) {
                            ?>
                            <tr role="row">
                                <td><?=$value['id']?></td>
                                <td><?=$value['channelOrderNo']?></td>
                                <td><?=$value['policyNo']?></td>
                                <td><?=$value['applyDate']?></td>
                                <td><?=$value['policyBeginDate']?></td>
                                <td><?=$value['policyEndDate']?></td>
                                <td><?=$value['policyHolderUserName']?></td>
                                <td><?=$value['premium']?></td>
                                <td><?=$value['policyStatus']?></td>
                                <td><?=$value['create_time']?></td>
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
    });
</script>