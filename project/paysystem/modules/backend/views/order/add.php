<?php
$status    = \app\models\Payorder::getStatus();
?>
<div class="col-lg-6">
    <div class="box dark">
        <header>
            <div class="icons"><i class="fa fa-edit"></i></div>
            <h5>订单详情</h5>
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
                    <label class="control-label col-lg-4">应用id：</label>
                    <div class="col-lg-8">
                        <?=$post['aid']?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-lg-4">业务id：</label>
                    <div class="col-lg-8">
                    <?=$post['business_id']?>
                        <!-- <select id="selectBusiness" name="business_id" class="form-control">
                            <option <?php echo!isset($post['business_id']) || $post['business_id'] == '' ? 'selected' : ''; ?> value="">选择应用</option>
                            <?php if (!empty($business)): ?>
                                <?php foreach ($business as $key => $val): ?>
                            <option class="businessOption business_<?=$val['aid']?>" style="display:<?php echo isset($post['channel_id']) && $post['channel_id'] == $val['id'] ? 'block' : 'none'; ?>" <?php echo isset($post['business_id']) && $post['business_id'] == $val['id'] ? 'selected' : ''; ?> value="<?= $val['id'] ?>"><?= $val['name'] ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select> -->
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-lg-4">通道id：</label>
                    <div class="col-lg-8">
                    <?=$post['channel_id']?>
                        <!-- <select id="selectChannel" name="channel_id" class="form-control">
                            <option <?php echo!isset($post['channel_id']) || $post['channel_id'] == '' ? 'selected' : ''; ?> value="">选择通道</option>
                            <?php if (!empty($channel)): ?>
                                <?php foreach ($channel as $key => $val): ?>
                            <option <?php echo isset($post['channel_id']) && $post['channel_id'] == $val['id'] ? 'selected' : ''; ?> value="<?= $val['id'] ?>"><?= $val['product_name'] ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select> -->
                    </div>
                </div>
                <div class="form-group">
                    <label for="identityid" class="control-label col-lg-4">用户唯一用户id：</label>

                    <div class="col-lg-8">
                       <?php echo $post['identityid']?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="orderid" class="control-label col-lg-4">绑卡请求号：</label>

                    <div class="col-lg-8">
                       <?=$post['orderid'] ?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="other_orderid" class="control-label col-lg-4">第三方支付订单：</label>

                    <div class="col-lg-8">
                       <?php echo $post['other_orderid'] ?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="bankname" class="control-label col-lg-4">银行名称：</label>

                    <div class="col-lg-8">
                        <?php echo $post['bankname'] ?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="cardno" class="control-label col-lg-4">银行卡号：</label>

                    <div class="col-lg-8">
                     <?php echo $post['cardno']?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="idcard" class="control-label col-lg-4">身份证号：</label>

                    <div class="col-lg-8">
                        <?php echo $post['idcard']?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="name" class="control-label col-lg-4">姓名：</label>

                    <div class="col-lg-8">
                        <?php echo $post['name']?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="phone" class="control-label col-lg-4">电话：</label>

                    <div class="col-lg-8">
                       <?php echo $post['phone']?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="productcatalog" class="control-label col-lg-4">商品类别码：</label>

                    <div class="col-lg-8">
                        <?php echo $post['productcatalog']  ?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="productname" class="control-label col-lg-4">商品名称：</label>

                    <div class="col-lg-8">
                        <?php echo $post['productname'] ?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="productdesc" class="control-label col-lg-4">商品描述：</label>

                    <div class="col-lg-8">
                       <?php echo $post['productdesc']?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="amount" class="control-label col-lg-4">交易金额：元：</label>

                    <div class="col-lg-8">
                       <?php echo $post['amount']?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="orderexpdate" class="control-label col-lg-4">订单有效期：</label>

                    <div class="col-lg-8">
                       <?php echo $post['orderexpdate']?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="create_time" class="control-label col-lg-4">创建时间：</label>

                    <div class="col-lg-8">
                        <?= $post['create_time'] ?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="modify_time" class="control-label col-lg-4">最后修改时间：</label>

                    <div class="col-lg-8">
                       <?=$post['modify_time']?>
                       </div>
                </div>
                <div class="form-group">
                    <label for="res_code" class="control-label col-lg-4">响应码：</label>

                    <div class="col-lg-8">
                        <?php echo $post['res_code']?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="res_msg" class="control-label col-lg-4">响应原因：</label>

                    <div class="col-lg-8">
                        <?php echo $post['res_msg'] ?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="callbackurl" class="control-label col-lg-4">回调地址：</label>

                    <div class="col-lg-8">
                        <?php echo $post['callbackurl'] ?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="userip" class="control-label col-lg-4">用户ip地址：</label>

                    <div class="col-lg-8">
                        <?php echo $post['userip']?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="smscode" class="control-label col-lg-4">短信验证码：</label>

                    <div class="col-lg-8">
                        <?php echo $post['smscode']?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="client_status" class="control-label col-lg-4">客户端状态：</label>

                    <div class="col-lg-8">
                        <?php echo $post['client_status']?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-lg-4">状态：<?=$post['status']?></label>
                    <div class="col-lg-8">
                        <div class="checkbox">
                            <?php if(!empty($status)): ?>
                                <?php foreach($status as $sk => $sv): ?>
                                    <label>
                                        <div class="radio">
                                            <span class="checked">
                                                <?php
                                                    echo '<input class="" type="radio" name="status" value="'.$sk.'" ';
                                                    echo isset($post['status']) && $post['status'] == $sk ? 'checked ' : '';
                                                    echo !in_array(intval($sk),[0,2,11]) ? 'disabled>' : '>';
                                                ?>
                                            </span>
                                            <?=$sv?>
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
        var status = eval(<?=$post['status']?>);
        if(!status){
            $("input[name='status']").eq(0).attr("checked",true);
        }
    })
</script>