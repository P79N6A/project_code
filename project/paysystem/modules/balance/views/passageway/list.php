<?php

use yii\widgets\LinkPager;
use \yii\helpers\ArrayHelper;

$this->title = "米富逾期对账管理";
$status      = \app\models\Business::getStatus();

$source = ['初始', '已上传', '已下载'];
?>
<div class="col-lg-12 ui-sortable">
    <div class="box ui-sortable-handle" style="position: relative; left: 0px; top: 0px;">
        <header>
            <h5><?=$this->title. '>'?>通道账单管理</h5>
        </header>
        <div class="body">
            <form action="?" method="get">
            <div style="margin-bottom: 10px">
                回款通道：  <select style="margin-right: 10px" name="channel_id">
                                        <option value="0">请选择</option>
                                <?php
                                    foreach($return_channel as $key => $value) {
                                        ?>
                                        <option
                                            <?php
                                                if ($channel_id == $key) {
                                                    echo "selected = 'selected'";
                                                }
                                            ?>
                                            value="<?=$key?>"><?=$value?></option>
                                        <?php
                                    }
                                ?>
                            </select>
                账单日期：<input style="margin-right: 10px;" type="text" onclick="laydate({istime: true, format: 'YYYY-MM-DD'})" name="start_time" value="<?=$start_time?>"  /> ~
                          <input style="margin-left: 10px;margin-right: 20px"  type="text" onclick="laydate({istime: true, format: 'YYYY-MM-DD'})" name="end_time"  value="<?=$end_time?>" />

                <input type="reset" value="重置"  class="btn btn-primary">
                <input style="margin-left: 5px;"  type="submit" value="查询" id="search_submit"  class="btn btn-primary">
            </div>
            <hr />
            </form>
            <div style="margin-bottom: 10px">
                <input type="button" value="上传对账单" onclick="jump_url()"  class="btn btn-primary">
            </div>
            <table class="table table-bordered table-condensed table-hover table-striped dataTable no-footer">
                <thead>
                    <tr role="row">
                        <th style="text-align: center;" width="20%">序号</th>
                        <th style="text-align: center;" width="20%">回款通道</th>
                        <th style="text-align: center;" width="20%">来源</th>
                        <th style="text-align: center;" width="20%">创建时间</th>
                        <th style="text-align: center;" width="20%">操作</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    if (!empty($getAllData)) {
                        $num = 0;
                        foreach($getAllData as $value) {
                            $num ++;
                            ?>
                            <tr role="row">
                                <td style="text-align: center;" width="20%"><?=$num?></td>
                                <td style="text-align: center;" width="20%"><?=ArrayHelper::getValue($return_channel, ArrayHelper::getValue($value, 'channel_id'), 0)?></td>
                                <td style="text-align: center;" width="20%"><?=ArrayHelper::getValue($source, ArrayHelper::getValue($value, 'source', 0), '')?></td>
                                <td style="text-align: center;" width="20%"><?=ArrayHelper::getValue($value, 'create_time', '')?></td>
                                <td style="text-align: center;" width="20%"><a href="/balance/passageway/dateilslist?id=<?=ArrayHelper::getValue($value, 'id')?>&return_channel=<?=ArrayHelper::getValue($value, 'channel_id')?>&source=<?=ArrayHelper::getValue($value, 'source')?>">详情</a></td>
                            </tr>
                            <?php
                        }
                    }else {
                        ?>
                        <tr role="row" style="text-align: center">
                            <td colspan="5">暂无数据！</td>
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

//跳转到上传页面
function jump_url(){
    location.href="/balance/passageway/upbill";
}
</script>