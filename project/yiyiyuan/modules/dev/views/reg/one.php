<script>
    $(function () {
        $('.icon_Rem').click(function () {
            $(this).siblings('input').prop('value', '');
        });

    });
</script>
<div class="whitebg">
    <div class="bannerbanner">
        <img src="/images/bannerer.png">
    </div>
    <div class="mainin">
        <div class="form-group icon_Rem">
            <span class="iphone_iph" ><img src="/images/iphone_iph.png"></span>
            <input class="Border" type="text" name="mobile" id="regmobile" <?php if (!empty($userinfo)): ?>value="<?php echo $userinfo->mobile; ?>"<?php else: ?>value=""<?php endif; ?> maxlength="11" placeholder="请输入手机号">
            <img src="/images/icon_remove.png" class="icon_Rem" style="width:7%">
        </div>
        <div class="form-group" >
            <span ><img src="/images/iphone_iph2.png"></span>
            <input type="text" class="noBorder" name="code" id="regcode" maxlength="4" placeholder="请输入验证码" >
            <button type="button" id="reggetcode_login" class="hqyzm">获取验证码</button>    
        </div>
    </div>
    <input type="hidden" name="url_type" id="url_type" value="<?php echo $type; ?>">
    <div class="tishi" id="reg_one_error"></div>
    <input type="hidden" name="operation_type" id="operation_type" value="">
    <button id="login_button" class="jinru">进入</button>         
    <div class="yuedty">
        <input type="checkbox" checked="checked" class="regular-checkbox">
        <label for="checkbox-1"></label>
        阅读并同意
        <a href="/dev/reg/agreement" style="color:#2A6496;">《先花一亿元注册协议》</a>
    </div>
</div>

<div id="overDiv" style="display: none;"></div>
<div class="tanchuceng" style="display: none;">
    <p class="shuyai1">请输入邀请码，<em>可提额30点！</em></p>
    <div class="inputshus">
        <input type="text" name="from_code" id="from_code" value="<?php echo $invite_code; ?>" maxlength="8">
    </div>
    <p class="shuyai2" id="trip_reg_error"></p>
    <p class="shuyai3">
        <button id="reg_cancle" class="tiaoguo">跳过</button>
        <button id="regonebutton">确定</button>
    </p>
</div>

<script>
    $(function () {
        $('.icon_Rem').click(function () {
            $(this).siblings('input').prop('value', '');
        });
    });
</script>