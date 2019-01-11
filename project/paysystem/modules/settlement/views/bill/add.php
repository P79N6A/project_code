<?php $status = \app\models\Business::getStatus(); ?>
<div class="col-lg-6">
    <div class="box dark">
        <header>
            <div class="icons"><i class="fa fa-edit"></i></div>
            <h5>上传 <?=$channel_name?>  对账单 </h5>
            <div class="toolbar">
                <nav style="padding: 8px;">
                    <a href="/settlement/bill/downmodule" class="btn btn-default btn-xs ">
                        下载模板
                    </a>
                    <a href="javascript:;" class="btn btn-default btn-xs collapse-box">
                        <i class="fa fa-minus"></i>
                    </a>
                    <a href="javascript:;" class="btn btn-default btn-xs full-box">
                        <i class="fa fa-expand"></i>
                    </a>
                    <a href="/settlement/bill/index" class="btn btn-danger btn-xs close-box">
                        <i class="fa fa-times"></i>
                    </a>
                </nav>
            </div>
        </header>

        <div id="div-1" class="body collapse in" aria-expanded="true">
            <div id="showError11" style="display:none" class="bs-example bs-example-standalone" data-example-id="dismissible-alert-js">
                <div id="errorClass" class="alert alert-danger alert-dismissible fade in" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                    <strong id="errotmsg"></strong>
                </div>
            </div>
            <form enctype="multipart/form-data" id="post_form" method="post"  action="/settlement/bill/add" class="form-horizontal">
                <input name="_csrf" type="hidden" id="_csrf" value="<?= \Yii::$app->request->csrfToken ?>">
                <input name="channel_type" type="hidden" id="channel_type" value="<?= $channel_type ?>">
                <input name="channel_date" type="hidden" id="channel_date" value="<?= $channel_date ?>">
                <div class="form-group" >
                    <label for="ip" class="control-label col-lg-4">上传文件：</label>

                    <div class="col-lg-8">
                        <input type="file" name="file_name" id="file_name" />
                    </div>
                    <div class="col-lg-8" style="margin-top: 20px">
                        <!--<input type="submit" id="dosubmit" class="btn btn-primary" value="保存" />-->
                        <button type="button" id="dosubmit" class="btn btn-primary" >保存</button>
                        &nbsp;&nbsp;&nbsp;&nbsp;
                        <button type="button" id="bill_index" style="background-color:grey;border-color:grey" class="btn btn-primary">取消</button>
                    </div>
                </div>
            </div>
            </form>
        </div>
    </div>
</div>

<script src="/static/js/jquery-form.js" type="text/javascript" charset="utf-8"></script>
<script>
$(function(){
    $("#dosubmit").click(function(){
        var options = {
            dataType: 'json',
            data: $("#post_form").formToArray(),
            success: function (data) {
                if (data.msg=='上传成功'){
                    alert(data.msg);
                    location.href='/settlement/bill/index';
                    return false;
                }
                alert(data.msg);
                return false;
            }
        };
        $("#post_form").ajaxSubmit(options);
        return false;

    });

    //取消
   $("#bill_index").click(function(){
       location.href='/settlement/bill/index';
   });
});
</script>