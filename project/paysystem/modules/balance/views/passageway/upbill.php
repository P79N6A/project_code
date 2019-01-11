<?php
use \app\models\Business;
$this->title = '米富逾期对账管理';
$status      = Business::getStatus();

//url地址
$action_url = '/balance/passageway/upbill';
$jump_url = '/balance/passageway/list';
$down_model = '/balance/passageway/downmodel';
?>
<div class="col-lg-12 ui-sortable">
    <div class="box ui-sortable-handle" style="position: relative; left: 0px; top: 0px;">
        <header>
            <h5><?php echo $this->title. '>'?> 通道账单管理>上传对账单</h5>
            <span id="click_href_up"  class="btn btn-primary" style="margin: 2px 10px 0 0;float: right;">返回</span>
        </header>
        <div class="body">
            <form enctype="multipart/form-data" id="post_form" method="post" action="<?=$action_url?>" >
                <input name="_csrf" type="hidden" id="_csrf" value="<?= \Yii::$app->request->csrfToken ?>">
                <table class="table table-bordered" style="width: 70%;">
                    <tr>
                        <td style="width: 30%; height: 50px; text-align:center;vertical-align:middle;">回款通道名称</td>
                        <td colspan="2">
                            <select name="channel_id" style="margin-right: 20px;">
                                <option value="0">请选择</option>
                                <?php
                                foreach ($return_channel as $k=>$v){
                                    ?>
                                    <option value="<?=$k?>"><?=$v?></option>
                                <?php }?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 30%; height: 50px; text-align:center;vertical-align:middle;">上传文件</td>
                        <td>
                            <input type="file" name="file_name" />
                        </td>
                        <td style="text-align:center;vertical-align:middle; width: 150px;"><a href="<?=$down_model?>">下载模版文件</a></td>
                    </tr>
                    <tr>
                        <td style="width: 30%; height: 50px; text-align:center;vertical-align:middle;"></td>
                        <td colspan="2">
                            <input id="button_cancel" type="button" class="btn btn-primary" style="background-color: white; color: black; margin-right: 10px" value="取消" />
                            <input id="button_submit" type="submit" class="btn btn-primary" value="确认">
                        </td>
                    </tr>
                </table>
            </form>
        </div>
    </div>
</div>
<script src="/bootstrap/js/bootstrap.min.js" type="text/javascript" charset="utf-8"></script>
<script>
    var  url = '<?=$jump_url?>';
    $(function(){
        //返回
        $("#click_href_up").click(function(){
            location.href = url;
        });
        //取消
        $("#button_cancel").click(function(){
            location.href = url;
        })
        //提交
        $("#button_submit").click(function(){
            //出款通道
            var channel_id = $('select').change().val();
            if (channel_id == 0){
                alert("请选择出款通道！");
                return false;
            }
            //上传文件
            var file_name = $("input[name='file_name']").val();
            if (file_name == ''){
                alert("请选择上传文件！");return false;
            }
            var options = {
                dataType: 'json',
                data: $("#post_form").formToArray(),
                success: function (data) {
                    if (data.msg=='上传成功'){
                        alert(data.msg);
                        location.href= url;
                        return false;
                    }
                    alert(data.msg);
                    return false;
                }
            };
            $("#post_form").ajaxSubmit(options);
            return false;

        })
    })
</script>