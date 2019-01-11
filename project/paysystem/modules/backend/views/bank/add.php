<?php
$status = \app\models\ChannelBank::getStatus();
$limitType = \app\models\ChannelBank::getLimitType();
?>
<div class="col-lg-6">
    <div class="box dark">
        <header>
            <div class="icons"><i class="fa fa-edit"></i></div>
            <h5>添加通道银行卡</h5>
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
                    <label class="control-label col-lg-4">选择通道：</label>
                    <div class="col-lg-8">
                        <select name="channel_id" class="form-control">
                            <option <?php echo!isset($post['channel_id']) || $post['channel_id'] == '' ? 'selected' : ''; ?> value="">选择通道</option>
                            <?php if(!empty($channelinfo)): ?>
                            <?php foreach ($channelinfo as $key => $val): ?>
                            <option <?php echo isset($post['channel_id']) && $post['channel_id'] == $val['id'] ? 'selected' : ''; ?> value="<?=$val['id']?>"><?=$val['product_name']?></option>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="std_bankname" class="control-label col-lg-4">标准银行名称：</label>

                    <div class="col-lg-8">
                        <input type="text" name="std_bankname" value="<?php echo isset($post['std_bankname']) ? $post['std_bankname'] : '' ?>" id="std_bankname" placeholder="标准银行名称" class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label for="company_name" class="control-label col-lg-4">银行名称：</label>

                    <div class="col-lg-8">
                        <input type="text" name="bankname" value="<?php echo isset($post['bankname']) ? $post['bankname'] : '' ?>" id="bankname" placeholder="银行名称" class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label for="bankcode" class="control-label col-lg-4">银行编号：</label>

                    <div class="col-lg-8">
                        <input type="text" name="bankcode" value="<?php echo isset($post['bankcode']) ? $post['bankcode'] : '' ?>" id="bankcode" placeholder="银行编号" class="form-control">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="control-label col-lg-4">银行卡类型：</label>
                    <div class="col-lg-8">
                        <div class="checkbox">
                            <label>
                                <div class="radio">
                                    <span class="checked"><input class="" type="radio" name="card_type" value="1" <?php echo !isset($post['card_type']) || $post['card_type'] == 1 ? 'checked' : '' ?>></span>储蓄卡
                                </div>
                            </label>
                            <label>
                                <div class="radio">
                                    <span class="checked"><input class="" type="radio" name="card_type" value="2" <?php echo isset($post['card_type']) && $post['card_type'] == 2 ? 'checked' : '' ?>></span>信用卡
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="limit_max_amount" class="control-label col-lg-4">单笔限额：</label>

                    <div class="col-lg-8">
                        <input type="text" name="limit_max_amount" value="<?php echo isset($post['limit_max_amount']) ? $post['limit_max_amount'] : '' ?>" id="limit_max_amount" placeholder="单笔限额" class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label for="limit_day_amount" class="control-label col-lg-4">单日限额：</label>

                    <div class="col-lg-8">
                        <input type="text" name="limit_day_amount" value="<?php echo isset($post['limit_day_amount']) ? $post['limit_day_amount'] : '' ?>" id="limit_day_amount" placeholder="单日限额" class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label for="limit_day_total" class="control-label col-lg-4">单日限数：</label>

                    <div class="col-lg-8">
                        <input type="text" name="limit_day_total" value="<?php echo isset($post['limit_day_total']) ? $post['limit_day_total'] : '' ?>" id="limit_day_total" placeholder="单日限数" class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-lg-4">限制类型：</label>
                    <div class="col-lg-8">
                        <div class="checkbox">
                            <?php if(!empty($limitType)): ?>
                            <?php foreach($limitType as $lk => $lv): ?>
                                <label>
                                    <div class="radio">
                                        <span class="checked"><input class="" type="radio" name="limit_type" value="<?=$lk?>" <?php echo isset($post['limit_type']) && $post['limit_type'] == $lk ? 'checked' : '' ?>></span><?=$lv?>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                
                <div class="form-group">
                    <label for="limit_start_time" class="control-label col-lg-4">限定起始时间：</label>
                    <div class="col-lg-8">
                        <input onClick="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:ss'})" type="text" name="limit_start_time" value="<?php echo isset($post['limit_start_time']) ? $post['limit_start_time'] : '' ?>" id="limit_start_time" placeholder="限定起始时间" class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label for="limit_end_time" class="control-label col-lg-4">限定结束时间：</label>
                    <div class="col-lg-8">
                        <input onClick="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:ss'})" type="text" name="limit_end_time" value="<?php echo isset($post['limit_end_time']) ? $post['limit_end_time'] : '' ?>" id="limit_end_time" placeholder="限定结束时间" class="form-control">
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
    
    $(function(){
        var limit_type = eval(<?= isset($post['limit_type'])?:0 ?>);
        if(!limit_type){
            $("input[name='limit_type']").eq(0).attr("checked",true);
        }
        var status =eval(<?= isset($post['status'])?:0 ?>);
        if(!status){
            $("input[name='status']").eq(0).attr("checked",true);
        }
    })
</script>