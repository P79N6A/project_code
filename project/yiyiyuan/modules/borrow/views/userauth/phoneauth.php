<!--<div class="jdall">-->
<!--	<div class="jdyyy">-->
<!--        <div class="dbk_inpL">-->
<!--            <label>手机号 </label> <span> --><?php //echo $mobile?><!--</span>-->
<!--            <input type="hidden" name="type" value="1">-->
<!--        </div>-->
<!--        <div class="dbk_inpL">-->
<!--            <label>手机服务密码</label><input placeholder="请输入手机服务密码" type="text" name='phoneserverpasswd'>-->
<!--        </div>-->
<!--        <div class="dbk_inpL" id="send_text" hidden>-->
<!--            <label>验证码</label>-->
<!--            <input placeholder="请填写验证码" type="text" name='code' maxlength=10>-->
<!--            <span class="Obtain" >获取验证码</span>-->
<!--        </div>-->
<!--	</div>-->
<!--    -->
<!--	 <div class="tsmes" id="tsmes1"></div>-->
<!--	<div class="button" id="button_next"> <button>下一步</button></div>-->
<!--</div>-->

<div class="jdall">
    <div class="jdyyy">
        <div class="jdimg">
            <img style="width:20%; margin-left:40%;" src="/images/studyimg3.png">
        </div>
        <div class="dbk_inpL">
            <label>姓名：</label><span class="mingzi"><?php echo $user->realname; ?></span>
        </div>
        <div class="dbk_inpL">
            <label>电话：</label><span class="mingzi"><?php echo $user->mobile; ?></span>
            <input type="hidden" name="type" value="1">
        </div>
        <div class="dbk_inpL">
            <label>密码：</label>
            <input class="phonefu" name="phoneserverpasswd" placeholder="输入手机服务密码" type="text">
        </div>
        <div class="dbk_inpL" id="send_text" hidden>
            <label>验证码：</label><input placeholder="输入手机短信验证码" class="yzmwidth" type="text" name="code">
            <button style="width: 26%" class="hqyzm" id="code">获取验证码</button>
        </div>
    </div>
    <div class="tsmes" id="tsmes1"></div>
    <div class="button button_next" id="button_next" > <button>提交认证</button></div>
</div>

<!--验证码弹层-->
<div class="Hmask" hidden></div>
 <div class="duihsucc" id='duihsucccode' hidden>
     <p class="xuhua"> 短信验证码</p>
    <p>短信验证码已发送到您尾号<span><?php echo $last_mobile?></span>的手机</p>
    <div class="jdyyy margbor">
        <div class="dbk_inpL">
            <label>验证码</label>
            <input placeholder="请输入最新获取的短信验证码" type="text" name="code_code" maxlength=10>

        </div>
	</div>
	<div style="color: #c90000;text-align: center;margin-top: 0.7rem; font-size: 0.916rem;" class="tsmes" id="tsmes2"></div>
    <button class="sureyemian button_next" id='location'>提交</button>
</div> 


<div class="duihsucc" id="duihsuccdown" hidden>
    <p class="xuhua">认证失败!</p>
    <p>详情请下载APP咨询线上客服</p>
    <button class="sureyemian">下载APP</button>
</div>

<!--请稍候蒙层-->
<div style="width: 100%; height: 100%;background: rgba(0,0,0,.7); position: fixed;top: 0;left: 0; z-index: 100;" id ="loadings" hidden></div>
<div class="loading" hidden>
    <img src="/images/loadings.gif">
    <p class="pleasesh">请稍后...</p>
</div>

<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script type="text/javascript">

    var csrf = '<?php echo $csrf; ?>';
    //输入服务密码时点击下一步
    $('.button_next').click(function (){
        var phoneserverpasswd = $("input[name='phoneserverpasswd']").val();
        var temp=$("#duihsucccode").is(":hidden");
        if(temp){
            var code = $("input[name='code']").val();
        }else{
            var code = $("input[name='code_code']").val();
        }

        var type = $("input[name='type']").val();
        //判断密码格式
        var pass_reg = /^[0-9A-Za-z]{6,8}$/;
        if (!pass_reg.test(phoneserverpasswd)){
            $("#tsmes1").text('*密码由6-8字母或数字组成');
            return false;
        }
        if (type == 2 && code == ''){
            $("#tsmes1").text('*请填写短信验证码');
            return false;
        }
        $("#tsmes1").text('');
        $('#loadings').show();
        $('.loading').show();
        $.ajax({
            type: "POST",
            dataType: "json",
            data:{'captcha':code, 'type':type, 'password':phoneserverpasswd, '_csrf':csrf},
            url: "/new/userauth/phoneajax",
            async: true,
            error: function(result) {
                $('#loadings').hide();
                $('.loading').hide();
                $("#tsmes1").text('*网络出错');
                return false;
            },
            success: function(result) {
                message(result);
            }
        });
    });

    //获取验证码
    $('.hqyzm').click(function(){

        $("input[name='type']").val(1);
        if (countdown != 60){
              return false;
        };
        var phoneserverpasswd = $("input[name='phoneserverpasswd']").val();
        //判断密码格式
        var pass_reg = /^[0-9A-Za-z]{6,8}$/;
        if (!pass_reg.test(phoneserverpasswd)){
            $("#tsmes1").text('*密码由6-8字母和数字组成');
            return false;
        }
        $("#tsmes1").text('');
        $('#loadings').show();
        $('.loading').show();
        $.ajax({
            type: "POST",
            dataType: "json",
            data:{'type':1, 'password':phoneserverpasswd, '_csrf':csrf},
            url: "/new/userauth/phoneajax",
            async: true,
            error: function(result) {
                $('#loadings').hide();
                $('.loading').hide();
                $("#tsmes1").text('*网络出错');
                return false;
            },
            success: function(result) {
                settime();
                message(result)
            }
        });
    });

    /**
     * 信息处理
     * @params array result {"res_code":res_code, "res_data":res_data}
     * @resutl null
     */
    function message(result){
        //step == 0成功跳转到nextPage
        if (result.res_data.step == 0){
            var location_href = "<?php echo $redirect_info['nextPage']."?".$redirect_info['orderinfo']?>";
            location.href = location_href;
        }
        //step == 1重走第一步
        if (result.res_data.step == 1){
            $('#loadings').hide();
            $('.loading').hide();
            //隐藏短信验证码输入框
            $('.Hmask').hide();
            $('#duihsucccode').hide();
            $("#send_text").hide();
            $("input[name='type']").val(result.res_data.step);//type改为1
            $("#tsmes1").text(result.res_data.process_msg);
            return false;
        }
        //step == 2执行第二步
        if (result.res_data.step == 2){
            $('#loadings').hide();
            $('.loading').hide();
            var type = $("input[name='type']").val();
            if(type == 1){
                settime();
                $("#send_text").show();
                $("input[name='type']").val(result.res_data.step);
                return false;
            }else if(type == 2){
                $('#loadings').hide();
                $('.loading').hide();
                $('.Hmask').show();
                $('#duihsucccode').show();
                $("#tsmes2").text(result.res_data.process_msg);
                $("input[name='type']").val(result.res_data.step);
                return false;
            }
        }
        //step == 3直接结束
        if (result.res_data.step == 3){
            $('#loadings').hide();
            $('.loading').hide();
            $('.Hmask').hide();
            $('#duihsucccode').hide();
            $("#send_text").hide();
            $("#tsmes1").text(result.res_data.process_msg);
            return false;
        }
    }
    //倒计时代码段
    var countdown=60;
    function settime() {
        if (countdown == 0) {
            $('.hqyzm').text("获取验证码");
            countdown = 60;
            return;
        } else {
            $('.hqyzm').text("重新发送("+countdown+")");
            countdown--;
        }
        setTimeout(function() {
                settime() }
            ,1000)
    }


    //微信参数
    wx.config({
        debug: false,
        appId: '<?php echo $jsinfo['appid']; ?>',
        timestamp: <?php echo $jsinfo['timestamp']; ?>,
        nonceStr: '<?php echo $jsinfo['nonceStr']; ?>',
        signature: '<?php echo $jsinfo['signature']; ?>',
        jsApiList: [
            'hideOptionMenu'
        ]
    });

    wx.ready(function () {
        wx.hideOptionMenu();
    });
</script>
