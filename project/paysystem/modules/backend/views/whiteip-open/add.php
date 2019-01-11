<?php $status = \app\models\open\WhiteIpOpen::getStatus(); ?>
<div class="col-lg-6">
    <div class="box dark">
        <header>
            <div class="icons"><i class="fa fa-edit"></i></div>
            <h5>添加IP白名单</h5>
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
                <div class="form-group">
                    <label class="control-label col-lg-4"></label>
                    <div style="color: red" class="col-lg-8"><?php echo isset($msg) ? $msg : '' ?></div>
                </div>
                <input name="_csrf" type="hidden" id="_csrf" value="<?= \Yii::$app->request->csrfToken ?>">
                <input name="id" type="hidden"  value="<?php echo isset($post['id']) && $post['id'] > 0 ? $post['id'] : '' ?>">
                <div class="form-group">
                    <label class="control-label col-lg-4">选择应用：</label>
                    <div class="col-lg-8">
                        <select name="aid" class="form-control">
                            <option <?php echo!isset($post['aid']) || $post['aid'] == '' ? 'selected' : ''; ?> value="">选择应用</option>
                            <?php if(!empty($appinfo)): ?>
                            <?php foreach ($appinfo as $key => $val): ?>
                            <option <?php echo isset($post['aid']) && $post['aid'] == $val['id'] ? 'selected' : ''; ?> value="<?=$val['id']?>"><?=$val['name']?></option>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="ip" class="control-label col-lg-4">IP地址：</label>

                    <div class="col-lg-8">
                        <input type="text" name="ip" value="<?php echo isset($post['ip']) ? $post['ip'] : '' ?>" id="ip" placeholder="输入IP地址" class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-lg-4">状态：</label>
                    <div class="col-lg-8">
                        <div class="checkbox">
                            <?php if(!empty($status)): ?>
                            <?php foreach($status as $sk => $sv): ?>
                                <label>
                                    <div class="radio">
                                        <span class="checked"><input class="" type="radio" name="status" value="<?=$sk?>" <?php echo isset($post['status']) && $post['status'] == $sk ? 'checked' : '' ?>></span><?=$sv?>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
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
    
    $("#closeErrorMsg").click(function(){
        $("#showError").fadeOut();
    })
</script>