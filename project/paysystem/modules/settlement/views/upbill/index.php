<?php
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use yii\helpers\Html;
$this->title = '出款账单管理';
$source = ['初始', '已上传', '已下载'];
?>
<div class="col-lg-12 ui-sortable">
    <div class="box ui-sortable-handle" style="position: relative; left: 0px; top: 0px;">
        <header>
            <h5><?php echo $this->title. '>'?>上传账单列表</h5>
            <span id="click_href_up"  class="btn btn-primary" style="margin: 2px 10px 0 0;float: right;">上传对账单</span>
        </header>
        <div class="body">
            <div style="margin-bottom: 10px">
                  <form action="?" method="get">
                      出款通道名称：
                      <select name="channel_id" style="margin-right: 20px;">
                          <option value="0">请选择</option>
                          <?php
                            foreach ($passageOfMoney as $k=>$v){
                          ?>
                          <option
                              <?php
                                if ($k == ArrayHelper::getValue($getData, 'channel_id', 0)){
                                    echo 'selected = "selected"';
                                }
                              ?> value="<?=$k?>"><?=$v?></option>
                          <?php }?>
                      </select>
                      创建时间：<input type="text" onclick="laydate({istime: true, format: 'YYYY-MM-DD'})" name="start_time" style="margin-right: 10px;"
                                  value="<?php
                                    if (empty(ArrayHelper::getValue($getData, 'start_time'))){
                                        echo date("Y-m-01", time());
                                    }else{
                                        echo ArrayHelper::getValue($getData, 'start_time');
                                    }
                                  ?>
                                  "> ~
                                <input type="text" onclick="laydate({istime: true, format: 'YYYY-MM-DD'})" name="end_time" style="margin: 0 10px 0 10px;"
                                       value="<?php
                                        if (empty(ArrayHelper::getValue($getData, 'end_time'))){
                                            echo date("Y-m-d", time());
                                        }else{
                                            echo ArrayHelper::getValue($getData, 'end_time');
                                        }
                                       ?>
                                       ">
                      <input type="submit" id="search_submit" value="查询" class="btn btn-primary" />
                  </form>
            </div>
            <table class="table table-bordered table-condensed table-hover table-striped dataTable no-footer">
                <tbody>
                <tr role="row">
                    <th>序号</th>
                    <th>出款通道名称</th>
                    <th>来源</th>
                    <th>创建时间</th>
                    <th>操作</th>
                </tr>
                <?php
                    if (!empty($billFileData)){
                        $i = 0;
                        foreach($billFileData as $key=>$value){
                            $i++
                ?>
                <tr>
                    <td align="center"><?=$i?></td>
                    <td align="center">
                        <?php
                            echo ArrayHelper::getValue($passageOfMoney, $value->channel_id, '');
                        ?>
                    </td>
                    <td align="center">
                        <?php
                            echo ArrayHelper::getValue($source, $value->source, '');
                        ?>
                    </td>
                    <td align="center"><?=$value->create_time?></td>
                    <td align="center"><a href="/settlement/upbill/datalist?channel_id=<?=$value->channel_id?>&billtime=<?=date('YmdHis', strtotime($value->create_time))?>" >查看</a></td>
                </tr>
                <?php
                        }
                    }else{
                ?>
                  <td align="center" colspan="5"> 暂时无数据 </td>
                <?php }?>

                </tbody>                
            </table>
            <div class="panel_pager">
                <?php echo \yii\widgets\LinkPager::widget(['pagination' => $pages]);?>
            </div>
        </div>
    </div>
</div>
<script src="/bootstrap/js/bootstrap.min.js" type="text/javascript" charset="utf-8"></script>
<script src="/laydate/laydate.dev.js" type="text/javascript" charset="utf-8"></script>
<script>
    $(function(){
        $("#click_href_up").click(function(){
            location.href = "/settlement/upbill/upfile";
        });
        //查找
        $("#search_submit").click(function(){
            var channel_id = $("select").change().val();
            var start_time = $("input[name='start_time']").val();
            var end_time = $("input[name='end_time']").val();
            if (channel_id == 0 && start_time == ''){
                alert("请选择出款通道名称或创建时间");
                return false;
            }
            if (start_time > end_time){
                alert("时间区间存在区误");
                return false;
            }
        });
    })
</script>