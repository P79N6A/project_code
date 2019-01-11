<?php
$this->title = "支付系统";
$status = app\models\open\CjClientNotify::getStatus();
?>
<div class="col-lg-6">
    <div class="box dark">
        <header>
            <div class="icons"><i class="fa fa-edit"></i></div>
            <h5>畅捷通知</h5>
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
            <form id="post_form" method="post"  action="<?php echo isset($doType) && $doType ? $doType : 'add' ?>" class="form-horizontal">
                <input name="_csrf" type="hidden" id="_csrf" value="<?= \Yii::$app->request->csrfToken ?>">
                <input name="id" type="hidden"  value="<?php echo isset($post['id']) && $post['id'] > 0 ? $post['id'] : '' ?>">
                <div class="form-group">
                    <label for="remit_id" class="control-label col-lg-4">AID：</label>

                    <div class="col-lg-8">
                        <label for="remit_id" class="control-label"><?php echo isset($post['aid']) ? $post['aid'] : '' ?></label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="notify_num" class="control-label col-lg-4">额度：</label>

                    <div class="col-lg-4">
                        <input type="text" name="day_max_mount" value="<?php echo isset($post['day_max_mount']) ? $post['day_max_mount'] : '' ?>" id="notify_num" placeholder="额度" class="form-control">
                    </div>
                </div>



                <div class="form-group">
                    <label for="create_time" class="control-label col-lg-4">创建时间：</label>

                    <div class="col-lg-8">
                        <label for="create_time" class="control-label"><?php echo isset($post['create_time']) ? $post['create_time'] : '' ?></label>
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

<script src="/static/js/jquery-form.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript" src="/js/datePicker/WdatePicker.js"></script>
<script>
    $("#dosubmit").click(function () {
        if(confirm('确认进行操作吗？')){
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
        }
    });
</script>