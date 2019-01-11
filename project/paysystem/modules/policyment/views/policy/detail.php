<?php
$this->title = "支付系统";
$fund      = \app\models\policy\ZhanPolicy::getFund();
$status      = \app\models\policy\ZhanPolicy::getRemitStatus();
$paystatus      = \app\models\policy\ZhanPolicy::getPayStatus();
?>
<div class="col-lg-6">
    <div class="box dark">
        <header>
            <div class="icons"><i class="fa fa-edit"></i></div>
            <h5>保单详情</h5>
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
            <form class="form-horizontal">
                <input name="_csrf" type="hidden" id="_csrf" value="<?= \Yii::$app->request->csrfToken ?>">             
                <div class="form-group">
                    <label for="req_id" class="control-label col-lg-4">请求ID：</label>

                    <div class="col-lg-8">
                        <label for="req_id" class="control-label"><?=$data['req_id'] ?></label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="client_id" class="control-label col-lg-4">流水号：</label>

                    <div class="col-lg-8">
                        <label for="client_id" class="control-label"><?=$data['client_id'] ?></label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="guest_account" class="control-label col-lg-4">姓名：</label>

                    <div class="col-lg-8">
                        <label for="guest_account" class="control-label"><?=$data['user_name'] ?></label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="guest_account_bank" class="control-label col-lg-4">手机号：</label>

                    <div class="col-lg-8">
                        <label for="guest_account_bank" class="control-label"><?=$data['user_mobile'] ?></label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="guest_account_bank" class="control-label col-lg-4">身份证：</label>

                    <div class="col-lg-8">
                        <label for="guest_account_bank" class="control-label"><?=$data['identityid'] ?></label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="guest_account_name" class="control-label col-lg-4">保额：</label>

                    <div class="col-lg-8">
                        <label for="guest_account_name" class="control-label"><?=$data['sumInsured'] ?></label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="user_mobile" class="control-label col-lg-4">保费：</label>

                    <div class="col-lg-8">
                        <label for="user_mobile" class="control-label"><?=$data['premium'] ?></label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="user_mobile" class="control-label col-lg-4">保单期间：</label>

                    <div class="col-lg-8">
                        <label for="user_mobile" class="control-label"><?=$data['policyDate'] ?></label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="user_mobile" class="control-label col-lg-4">投保单号：</label>

                    <div class="col-lg-8">
                        <label for="user_mobile" class="control-label"><?=$data['applyNo'] ?></label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="user_mobile" class="control-label col-lg-4">保单号：</label>

                    <div class="col-lg-8">
                        <label for="user_mobile" class="control-label"><?=$data['policyNo'] ?></label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="user_mobile" class="control-label col-lg-4">受益人姓名：</label>

                    <div class="col-lg-8">
                        <label for="user_mobile" class="control-label"><?=$data['benifitName'] ?></label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="user_mobile" class="control-label col-lg-4">受益人证件号码：</label>

                    <div class="col-lg-8">
                        <label for="user_mobile" class="control-label"><?=$data['benifitCertiNo'] ?></label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="user_mobile" class="control-label col-lg-4">支付状态：</label>

                    <div class="col-lg-8">
                        <label for="user_mobile" class="control-label"><?=$paystatus[$data['pay_status']] ?></label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="user_mobile" class="control-label col-lg-4">保单状态：</label>

                    <div class="col-lg-8">
                        <label for="user_mobile" class="control-label"><?=$status[$data['remit_status']]?></label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="user_mobile" class="control-label col-lg-4">出单时间：</label>

                    <div class="col-lg-8">
                        <label for="user_mobile" class="control-label"><?=$data['policy_time']?></label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="user_mobile" class="control-label col-lg-4">响应状态：</label>

                    <div class="col-lg-8">
                        <label for="user_mobile" class="control-label"><?=$data['rsp_status']?></label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="user_mobile" class="control-label col-lg-4">响应结果：</label>

                    <div class="col-lg-8">
                        <label for="user_mobile" class="control-label"><?=$data['rsp_status_text']?></label>
                    </div>
                </div>
                </form>
        </div>
    </div>
</div>

