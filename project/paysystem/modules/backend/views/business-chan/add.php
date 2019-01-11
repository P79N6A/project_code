<?php
$status    = \app\models\ChannelBank::getStatus();
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
                        <select id="selectAid" name="aid" class="form-control">
                            <option <?php echo!isset($post['aid']) || $post['aid'] == '' ? 'selected' : ''; ?> value="">选择业务</option>
                            <?php if (!empty($app)): ?>
                                <?php foreach ($app as $key => $val): ?>
                                    <option <?php echo isset($post['aid']) && $post['aid'] == $val['id'] ? 'selected' : ''; ?> value="<?= $val['id'] ?>"><?= $val['name'] ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-lg-4">选择业务：</label>
                    <div class="col-lg-8">
                        <select id="selectBusiness" name="business_id" class="form-control">
                            <option <?php echo!isset($post['business_id']) || $post['business_id'] == '' ? 'selected' : ''; ?> value="">选择应用</option>
                            <?php if (!empty($business)): ?>
                                <?php foreach ($business as $key => $val): ?>
                            <option class="businessOption business_<?=$val['aid']?>" style="display:<?php echo isset($post['channel_id']) && $post['channel_id'] == $val['id'] ? 'block' : 'none'; ?>" <?php echo isset($post['business_id']) && $post['business_id'] == $val['id'] ? 'selected' : ''; ?> value="<?= $val['id'] ?>"><?= $val['name'] ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-lg-4">选择通道：</label>
                    <div class="col-lg-8">
                        <select id="selectChannel" name="channel_id" class="form-control">
                            <option <?php echo!isset($post['channel_id']) || $post['channel_id'] == '' ? 'selected' : ''; ?> value="">选择通道</option>
                            <?php if (!empty($channel)): ?>
                                <?php foreach ($channel as $key => $val): ?>
                            <option <?php echo isset($post['channel_id']) && $post['channel_id'] == $val['id'] ? 'selected' : ''; ?> value="<?= $val['id'] ?>"><?= $val['id'].'：'.$val['product_name'] ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="sort_num" class="control-label col-lg-4">排序：</label>

                    <div class="col-lg-8">
                        <input type="text" name="sort_num" value="<?php echo isset($post['sort_num']) ? $post['sort_num'] : '' ?>" id="std_bankname" placeholder="排序" class="form-control">
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
                                            <span class="checked"><input class="" type="radio" name="status" value="<?= $sk ?>" <?php echo isset($post['status']) && $post['status'] == $sk ? 'checked' : '' ?>></span><?= $sv ?>
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

    $(function () {
        //选择应用时 业务数据联动
        $("#selectAid").change(function(){
            var aid = $(this).val();
            if(aid > 0 ){
                $(".businessOption").hide();
                $(".business_"+aid).show();
            }
        })
        
        
        //操作为添加时，状态默认禁用选中
        var status = eval(<?= $post['status']?1:0 ?>);
        if(!status){
            $("input[name='status']").eq(0).attr("checked",true);
        }
    })
</script>