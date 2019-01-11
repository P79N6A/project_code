<?php $status = \app\models\Manager::getStatus(); ?>
<?php $types = \app\models\Manager::getTypeStatus(); ?>
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
            <form id="post_form" method="post"  action="<?php echo isset($doType) && $doType ? $doType : 'add' ?>" class="form-horizontal">
                <input name="_csrf" type="hidden" id="_csrf" value="<?= \Yii::$app->request->csrfToken ?>">
                <input name="id" type="hidden"  value="<?php echo isset($post['id']) && $post['id'] > 0 ? $post['id'] : '' ?>">
                
                <div class="form-group">
                    <label for="username" class="control-label col-lg-4">用户名：</label>

                    <div class="col-lg-8">
                        <input type="text" name="username" value="<?php echo isset($post['username']) ? $post['username'] : '' ?>" id="username" placeholder="用户名" class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label for="password" class="control-label col-lg-4">密码：</label>

                    <div class="col-lg-8">
                        <input type="password" name="password" value="" id="password" placeholder="密码" class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label for="realname" class="control-label col-lg-4">真实姓名：</label>

                    <div class="col-lg-8">
                        <input type="text" name="realname" value="<?php echo isset($post['realname']) ? $post['realname'] : '' ?>" id="realname" placeholder="真实姓名" class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label for="ip" class="control-label col-lg-4">IP地址：</label>

                    <div class="col-lg-8">
                        <input type="text" name="ip" value="<?php echo isset($post['ip']) ? $post['ip'] : '' ?>" id="realname" placeholder="IP地址" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label col-lg-4">类型：</label>
                    <div class="col-lg-8">
                        <div class="checkbox">
                            <?php if(!empty($types)): ?>
                            <?php foreach($types as $tk => $tv): ?>
                                <label>
                                    <div class="radio">
                                        <span class="checked"><input class="" type="radio" name="type" value="<?=$tk?>" <?php echo isset($post['type']) && $post['type'] == $tk ? 'checked' : '' ?>></span><?=$tv?>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
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