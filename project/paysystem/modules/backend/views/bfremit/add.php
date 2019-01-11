<?php
$this->title = "支付系统";
$status      = \app\models\open\BfRemit::getStatus();
$limitType = \app\models\ChannelBank::getLimitType();
?>
<div class="col-lg-6">
    <div class="box dark">
        <header>
            <div class="icons"><i class="fa fa-edit"></i></div>
            <h5>业务通道排序</h5>
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
                    <label class="control-label col-lg-4">选择应用：</label>
                    <div class="col-lg-8">
                            <?php if (!empty($app)): ?>
                                <?php foreach ($app as $key => $val): ?>
                                    <?php if(isset($post['aid']) && $post['aid'] == $val['id']) echo "<label for='channel_id' class='control-label'>{$val['name']}</label>";?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-lg-4">选择通道：</label>
                    <div class="col-lg-8">
                        <?php if (!empty($channel)): ?>
                            <?php foreach ($channel as $key => $val): ?>
                                <?php if(isset($post['channel_id']) && $post['channel_id'] == $val['id']) echo "<label for='channel_id' class='control-label'>{$val['product_name']}</label>";?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="req_id" class="control-label col-lg-4">请求ID：</label>

                    <div class="col-lg-8">
                        <label for="req_id" class="control-label"><?php echo isset($post['req_id']) ? $post['req_id'] : '' ?></label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="client_id" class="control-label col-lg-4">流水号：</label>

                    <div class="col-lg-8">
                        <label for="client_id" class="control-label"><?php echo isset($post['client_id']) ? $post['client_id'] : '' ?></label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="guest_account" class="control-label col-lg-4">银行卡号：</label>

                    <div class="col-lg-8">
                        <label for="guest_account" class="control-label"><?php echo isset($post['guest_account']) ? $post['guest_account'] : '' ?></label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="guest_account_bank" class="control-label col-lg-4">银行名称：</label>

                    <div class="col-lg-8">
                        <label for="guest_account_bank" class="control-label"><?php echo isset($post['guest_account_bank']) ? $post['guest_account_bank'] : '' ?></label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="guest_account_name" class="control-label col-lg-4">姓名：</label>

                    <div class="col-lg-8">
                        <label for="guest_account_name" class="control-label"><?php echo isset($post['guest_account_name']) ? $post['guest_account_name'] : '' ?></label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="user_mobile" class="control-label col-lg-4">电话：</label>

                    <div class="col-lg-8">
                        <label for="user_mobile" class="control-label"><?php echo isset($post['user_mobile']) ? $post['user_mobile'] : '' ?></label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="settle_amount" class="control-label col-lg-4">交易金额：</label>

                    <div class="col-lg-8">
                        <label for="settle_amount" class="control-label"><?php echo isset($post['settle_amount']) ? $post['settle_amount'] : '' ?></label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="settle_fee" class="control-label col-lg-4">手续费：</label>

                    <div class="col-lg-8">
                        <label for="settle_fee" class="control-label"><?php echo isset($post['settle_fee']) ? $post['settle_fee'] : '' ?></label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="real_amount" class="control-label col-lg-4">实际划款金额：</label>

                    <div class="col-lg-8">
                        <label for="real_amount" class="control-label"><?php echo isset($post['real_amount']) ? $post['real_amount'] : '' ?></label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="query_time" class="control-label col-lg-4">下次查询时间：</label>

                    <div class="col-lg-8">
                        <input type="text" name="query_time" value="<?php echo isset($post['query_time']) ? $post['query_time'] : '' ?>" id="query_num" placeholder="下次查询时间" class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label for="query_num" class="control-label col-lg-4">查询次数：</label>

                    <div class="col-lg-8">
                        <input type="text" name="query_num" value="<?php echo isset($post['query_num']) ? $post['query_num'] : '' ?>" id="query_num" placeholder="查询次数" class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-lg-4">状态：</label>
                    <div class="col-lg-8">
                        <div class="checkbox">
                            <?php if (!empty($status)): ?>
                                <?php foreach ($status as $sk => $sv): ?>
                                    <label>
                                        <div class="radio">
                                            <span class="checked"><input class="" type="radio" name="remit_status" value="<?= $sk ?>" <?php echo isset($post['remit_status']) && $post['remit_status'] == $sk ? 'checked' : '' ?>></span><?= $sv ?>
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