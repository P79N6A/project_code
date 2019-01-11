<?php $csrf = \Yii::$app->request->getCsrfToken(); ?>
<div class="login">
    <img src="/h5/images/loginbanner.png">
    <div class="loginmeg">
        <div class="dbk_inpL">
            <label>手机号</label>
            <input  name="mobile" maxlength="11" placeholder="请输入注册手机号" type="text">
        </div>
        <div class="dbk_inpL">
            <label>图形码</label>
            <input id="imgCode" maxlength="4" name="imgcode" placeholder="请输入图形验证码" type="text">
            <img class="gracode" id="getcode_num"  src="/new/regactivity/getimgcode">
        </div>
        <div class="dbk_inpL">
            <label>验证码</label>
            <input maxlength="4" name="code" placeholder="请输入短信验证码" type="text">
            <input type="hidden" value="<?php echo $come_from; ?>" name="come_from">
            <input  id="_csrf" name="_csrf" type="hidden" value="<?php echo $csrf; ?>">
            <span id="getCode" class="Obtain">获取验证码</span>
        </div>
    </div>
    <div class="tsmes" id="warning"></div>
    <button class="loginbtn" id="sub" >注册</button>
    <div style="display:none" id="haveReg" class="tsmes promptsuc">您已注册成功请直接进行<a class="tologin" >登录</a></div>
    <button class="loginbtn registerbtn tologin" >登录</button>
</div>
<div style="display:none" class="loginsuc">注册成功！</div>
<script>
     $(function(){ 
 	    $("#getcode_num").click(function(){ 
 	        $(this).attr("src",'/new/regactivity/getimgcode?' + Math.random());
 	    }); 
 	}); 
</script>
<script>
    var time = 60;
    var s = time + 1;
    function countDown() {
        s--;
        $('#getCode').unbind();
        $('#getCode').html("重新获取(" + s + ")");
        if (s === 0) {
            clearInterval(timer);
            $('#getCode').bind('click',getCode);
            s = time + 1;
            $('#getCode').html('重新获取');
        }
    }
    $('#sub').bind('click', function () {
        var mobile = $('input[name="mobile"]').val();
        var code = $('input[name="code"]').val();
        var come_from = $('input[name="come_from"]').val();
        var from_code = $('input[name="from_code"]').val();
        var imgCode = $("#imgCode").val();
        var reg = /^(1(([35678][0-9])|(47)))\d{8}$/;
        var csrf = $("#_csrf").val();
        $('#sub').attr('disabled', true);
        $('#warning').html('');
        if (!mobile) {
            $('#warning').html('※&nbsp;请输入手机号');
            $('#sub').attr('disabled', false);
            return false;
        }
        if (!reg.test(mobile)) {
            $('#warning').html('※&nbsp;请输入正确的手机号');
            $('#sub').attr('disabled', false);
            return false;
        }
        if (!imgCode) {
            $('#warning').html('※&nbsp;请填写图形验证码');
            $('#sub').attr('disabled', false);
            return false;
        }
        if(imgCode.length != 4){
            $('#warning').html('※&nbsp;请填写正确的图形验证码');
            $('#sub').attr('disabled', false);
            return false;
        }
        if (!code) {
            $('#warning').html('※&nbsp;请填写短信验证码');
            $('#sub').attr('disabled', false);
            return false;
        }
        if(code.length != 4){
            $('#warning').html('※&nbsp;请填写正确的短信验证码');
            $('#sub').attr('disabled', false);
            return false;
        }
        $.post('/new/regactivity/regsave', {_csrf:csrf, img_code:imgCode, mobile: mobile, code: code, come_from: come_from}, function (result) {
            var data = eval("(" + result + ")");
            if (data.res_code == 1) {
                $('#warning').html(data.res_data);
                $('#sub').attr('disabled', false);
            } else if(data.res_code == 2){
                $('#haveReg').css('display','block');
                $('#sub').attr('disabled', false);
            } else if(data.res_code == 0) {
                $('.loginsuc').css('display','block');
                    setTimeout(function(){window.location.href = '/new/loan';},1000);
            }
        });
    });

    $.ajaxSetup({
        async : false //取消异步
    });
    var getCode = function () {
        var mobile = $('input[name="mobile"]').val();
        var reg = /^(1(([35678][0-9])|(47)))\d{8}$/;
        var imgCode = $("#imgCode").val();
        var csrf = $("#_csrf").val();
        if (!mobile) {
            $('#warning').html('※&nbsp;请输入手机号');
            return false;
        }
        if (!reg.test(mobile)) {
            $('#warning').html('※&nbsp;请输入正确的手机号');
            return false;
        }
        if (!imgCode) {
            $('#warning').html('※&nbsp;请填写图形验证码');
            return false;
        }
        if (imgCode.length != 4) {
            $('#warning').html('※&nbsp;请填写正确的图形验证码');
            return false;
        }
        var jsonData = {_csrf:csrf, mobile: mobile, val: 66, img_code: imgCode};
        $.post('/new/regactivity/send', jsonData, function (result) {
            var data = eval("(" + result + ")");
            if (data.res_code == 1) {
                $('#warning').html(data.res_data);
            } else if(data.res_code == 2){
                $('#haveReg').css('display','block');
                $('#sub').attr('disabled', false);
            } else if (data.res_code == 0) {
                countDown();
                timer = setInterval(countDown, 1000);
            }
        });
    }

    $('#getCode').bind('click', getCode);

    $('.tologin').bind('click', function () {
        window.location.href='/wap/login/login';
    });
</script>

