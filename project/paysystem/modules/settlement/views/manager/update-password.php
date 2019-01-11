<?php $status = \app\models\Manager::getStatus(); ?>
<div class="col-lg-6">
    <div class="box dark">
        <header>
            <div class="icons"><i class="fa fa-edit"></i></div>
            <h5>账户管理</h5>
            <div class="toolbar">
                <nav style="padding: 8px;">
                    <a href="javascript:;" class="btn btn-default btn-xs collapse-box">
                        <i class="fa fa-minus"></i>
                    </a>
                    <a href="javascript:;" class="btn btn-default btn-xs full-box">
                        <i class="fa fa-expand"></i>
                    </a>
                    <a href="javascript:;" class="btn btn-danger btn-xs close-box">
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
            <form id="post_form" method="post"  action="update-password" class="form-horizontal">
                <input name="_csrf" type="hidden" id="_csrf" value="<?= \Yii::$app->request->csrfToken ?>">
                <div class="form-group">
                    <label for="old_password" class="control-label col-lg-4">原密码：</label>
                    <div class="col-lg-8">
                        <input type="text" autocomplete="off" name="old_password" value="" id="old_password" placeholder="原密码" class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label for="password" class="control-label col-lg-4">新密码：</label>
                    <div class="col-lg-8">
                        <input type="password" autocomplete="off" name="password" value="" id="password" placeholder="新密码" class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label for="re_password" class="control-label col-lg-4">确认密码：</label>
                    <div class="col-lg-8">
                        <input type="password" autocomplete="off" name="re_password" value="" id="re_password" placeholder="确认密码" class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label for="ip" class="control-label col-lg-4"></label>
                    <div class="col-lg-8">
                        <button id="dosubmit" type="button" class="btn btn-primary">保存</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
    $("#dosubmit").click(function () {
        var options = {
            dataType: 'json',
            data: $("#post_form").formToArray(),
            success: function (data) {
                if (data.res_code == '0') {
                    $("#errorClass").addClass('alert-success').removeClass("alert-danger")
                } else {
                    $("#errorClass").addClass('alert-danger').removeClass("alert-success")
                }
                $("#errotmsg").html(data.res_data);
                $("#showError").fadeIn();
                if (data.res_code == '0') {
                    window.location.href = 'index';
                }
            }
        };

        $("#post_form").ajaxSubmit(options);
        return false;
    });
    
    $("#closeErrorMsg").click(function(){
        $("#showError").fadeOut();
    })
</script>