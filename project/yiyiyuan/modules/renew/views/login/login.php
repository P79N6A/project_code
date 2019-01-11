<script>
    var _csrf = '<?php echo $csrf; ?>';
    $(function() {
        $("#getcode_num").click(function() {
            $(this).attr("src", '/new/reg/imgcode?' + Math.random());
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
            <input class="Border" type="text" name="mobile" id="regmobile" value="" maxlength="11" placeholder="请输入手机号">
            <img src="/images/icon_remove.png" class="icon_Rem" style="width:7%; position: absolute; right: 0; bottom: 5px;height: auto;">
        </div>
        <input type="hidden" name="mark" value="0">
        <div class="form-group" id="pic" style="display: none;">
            <span ><img src="/images/addaimg.png"></span>
            <input id="pic_num" name="pic_num" type="text" class="noBorder" placeholder="请输入图形验证码" >
            <div class="hqyzm" style=" width: 23%; border: 0; background: rgba(0,0,0,0);"><img id="getcode_num" src="/new/reg/imgcode"></div>
        </div>
        <div class="form-group" >
            <span ><img src="/images/iphone_iph2.png"></span>
            <input type="text" class="noBorder" name="code" id="regcode" maxlength="4" placeholder="请输入验证码" >
            <button type="button" id="reggetcode_login" class="hqyzm">获取验证码</button>    
        </div>
    </div>
    <div class="tishi" id="reg_one_error"></div>
    <input type="hidden" name="operation_type" id="operation_type" value="">
    <button id="login_button" class="jinru">进入</button>       
    <div class="yuedty">
        <input type="checkbox" checked="checked" class="regular-checkbox">
        <label for="checkbox-1"></label>
        阅读并同意
        <a href="/new/reg/agreement" style="color:#2A6496;">《先花一亿元注册协议》</a>
    </div>                                    
</div>

<div id="overDiv" style="display: none;"></div>
<div class="tanchuceng" style="display: none;">
    <p class="login_warning">您目前尚无可展期的借款，请前往一亿元产看详情</p>
    <button class="btnsure" id="opens_new">前往一亿元</button>
</div>

<script>
    $(function() {
        $('.icon_Rem').click(function() {
            $(this).siblings('input').prop('value', '');
        });

        $('#opens_new').click(function(){
            window.location.href = "/new/loan";
        });

    });
</script>