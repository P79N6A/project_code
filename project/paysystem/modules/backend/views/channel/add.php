<?php $status = \app\models\Channel::getStatus(); ?>
<div class="col-lg-6">
    <div class="box dark">
        <header>
            <div class="icons"><i class="fa fa-edit"></i></div>
            <h5>添加通道</h5>
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
                    <label for="company_name" class="control-label col-lg-4">通道名称：</label>

                    <div class="col-lg-8">
                        <input type="text" name="company_name" value="<?php echo isset($post['company_name']) ? $post['company_name'] : '' ?>" id="company_name" placeholder="通道名称" class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label for="product_name" class="control-label col-lg-4">产品名称：</label>

                    <div class="col-lg-8">
                        <input type="text" name="product_name" value="<?php echo isset($post['product_name']) ? $post['product_name'] : '' ?>" id="product_name" placeholder="产品名称" class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label for="mechart_num" class="control-label col-lg-4">商编：</label>

                    <div class="col-lg-8">
                        <input type="text" name="mechart_num" value="<?php echo isset($post['mechart_num']) ? $post['mechart_num'] : '' ?>" id="mechart_num" placeholder="商编" class="form-control">
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
                    <label for="ip" class="control-label col-lg-4">说明：</label>
                    <div class="col-lg-8">
                        <textarea rows="8" id="tip" name="tip"  class="form-control"><?php echo isset($post['tip']) ? $post['tip'] : '' ?></textarea>
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