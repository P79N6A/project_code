<!--<script>
    window.onload = function() {
        var oBtn = document.getElementById('code');
        var timer = null;
        var time = 60;
        var s = time + 1;
        oBtn.onclick = function() {
            var preg = /^1\d{10}$/;
            var mobile = $('input[name="mobile"]').val();
            var month = $('input[name="month"]').val();
            var year = $('input[name="year"]').val();
            var cvv2 = $('input[name="cvv2"]').val();

            if (mobile.length == 0 || !preg.test(mobile)) {
                alert('请输入正确格式的手机号!');
                return false;
            }
            var two = /^\d{2}$/;
            var three = /^\d{3}$/;
            if (!two.test(month) || !two.test(year) || !three.test(cvv2)) {
                $('#remain').html('请输入正确的信用卡信息');
                return false;
            }
            countDown();
            timer = setInterval(countDown, 1000);
            oBtn.disabled = true;
            $.ajax({
                type: "POST",
                dataType: "json",
                url: "/dev/bank/paylian",
                data: $('#pay').serialize(), // 你的formid
                async: false,
                error: function(data) {
                    alert('系统错误!');
                },
                success: function(data) {
                    if (data.rsp_code == '0000' && ("pay_key" in data)) {
                        $('input[name="pay_key"]').val(data.pay_key);
                        $('input[name="biancard_id"]').val(data.biancard_id);
                        $('#remain').html('');
                    } else {
                        $('#remain').html(data.rsp_msg);
                    }
                }
            });
        };

        function countDown() {
            s--;
            oBtn.innerHTML = s + '秒后重新获取';
            if (s == 0) {
                clearInterval(timer);
                oBtn.disabled = false;
                s = time + 1;
                oBtn.innerHTML = '重新获取验证码';
            }
        }
    };
    function subpay() {
        if (!$("input[name='pay_key']").val() || !$("input[name='biancard_id']").val()) {
            alert('请先获取验证码');
            return false;
        }
        var reg = /^\d{6}$/;
        if (!$("input[name='verifyCode']").val() || !reg.test($("input[name='verifyCode']").val())) {
            $('#remain').html('请输入正确的验证码');
            return false;
        }
        var tBug = document.getElementById('tbug');
        tBug.disabled = true;
        var mark = false;
        $.ajax({
            type: "POST",
            dataType: "json",
            //url:"bs",
            url: "/dev/bank/pay",
            data: $('#pay').serialize(), // 你的formid
            async: false,
            error: function(data) {
                tBug.disabled = false;
                alert('参数错误，请退出重新进入');
            },
            success: function(data) {
                tBug.disabled = false;
                if (data.rsp_code == '0000') {
                    $('#remain').html('');
                    if ($('input[name="f"]').val() == undefined) {
                        location.href = "/dev/bank/success";
                    } else {
                        location.href = "/dev/loan/second";
                    }
                } else {
                    $('.loading').toggle();
                    if (data.rsp_code == '1116') {
                        var start = data.rsp_msg.indexOf('[');
                        var end = data.rsp_msg.indexOf(']');
                        var str = data.rsp_msg.substr(start + 1, end - start - 1);
                        if (str == '短信校验码错误') {
                            $('#remain').html('短信校验码错误');
                        } else {
                            $('#remain').html('');
                            $('#remain').html(data.rsp_msg);
                        }
                    } else {
                        $('#remain').html('');
                        $('#remain').html(data.rsp_msg);
                    }
                }
            }
        });
        return mark;
    }
</script>-->
<div class="Hcontainer nP">
    <div class="main">
    <form action="/dev/bank/payyibao" method="post" class="form-horizontal" id="order-pay-form">
         <!--<form action="/dev/bank/paylian" method="post" class="form-horizontal" id="order-pay-form">-->
            <div class="border1 jcbd">
                <ul>
                    <li class="noBorder">
                        <div class="col-xs-3 text-right n26 grey2">姓名</div>
                        <div class="col-xs-8 n26 grey4"><?php echo $user['realname']; ?></div>
                        <input type="hidden" name="userid" value="<?php echo $user['user_id']; ?>">
                    </li>
                    <li class="noBorder">
                        <div class="col-xs-3 text-right n26 grey2">银行卡号</div>
                        <div class="col-xs-8 n26 grey4"><?php echo $post_data['cards']; ?></div>
                        <input type="hidden" name="card" value="<?php echo str_replace(' ', '', $post_data['cards']); ?>">
                        <input type="hidden" name="pay_type" value="3">
                        <?php if (isset($post_data['f']) && !empty($post_data['f'])): ?>
                            <input type="hidden" name="f" value="<?php echo $post_data['f']; ?>">
                        <?php endif; ?>
                    </li>
                    <li class="noBorder">
                        <div class="col-xs-3 text-right n26 grey2">身份证号</div>
                        <div class="col-xs-8 n26 grey4"><?php echo substr($user['identity'], 0, 4) . '**********' . substr($user['identity'], 14, 4); ?></div>                    
                        <input type="hidden" name="identity" value="<?php echo $user['identity']; ?>">
                        <input type="hidden" name="pay_key" value="">
                        <input type="hidden" name="biancard_id" value="">
                    </li>
                    <!-- 
                    <li class="noBorder">
                        <div class="col-xs-3 text-right n26 grey2" style="line-height:36px;">手机号码</div>
                        <div class="col-xs-9 n26"><input type="text" name="mobile" placeholder="银行卡留存号码"></div>
                    </li>
                    <li class="noBorder">
                        <div class="col-xs-3 text-right n26 grey2" style="line-height:36px;">有效期</div>
                        <div class="col-xs-9 n26">
                            <div class="col-xs-4"><input name="month" type="text"></div><div class="col-xs-2">月</div>
                            <div class="col-xs-4"><input name="year" type="text"></div><div class="col-xs-2">年</div>
                        </div>
                    </li>
                    <li class="noBorder">
                        <div class="col-xs-3 text-right n26 grey2" style="line-height:36px;">卡后3位数</div>
                        <div class="col-xs-9 n26"><input name="cvv2" type="text" placeholder="卡后3位数"></div>
                    </li>
                    <li class="noBorder">
                        <div class="col-xs-3 text-right n26 grey2" style="line-height:36px;">验证码</div>
                        <div class="col-xs-9 n26 grey4">
                            <div class="col-xs-6" style="padding-right:5px">
                                <input type="text" name="verifyCode" maxlength="6" class="form-control" id="pwd">
                            </div>
                            <div class="col-xs-6">
                                <button type="submit" class="btn" style="width:100%;font-size:2.6rem;height:36px;line-height:0;text-align:center;padding:0" id="code">获取验证码</button>
                            </div>

                        </div>
                    </li>
                    -->
                </ul>
            </div>
            <span id="remain" style="color: red;"></span>
            <p class="btn mt40" style="width:100%;" id="lzh">确定</p>
            
            
        <div class="Hmask" style="display:none;"></div>    
        <div class="xhb_layer pad" id="ques" style="display:none;">
            <img src="/images/kelianren.png" style="width:30%;position: absolute;top:-85px;left:-5px;width:70px;">
            <h4 style="text-align:center;margin-top:10px;">支付／还款失败</h4>
            <p class="n28 mt40" style="margin-top:10px;"><span class="red">非常抱歉～(╥﹏╥)～</span><br/>由于第三方连连支付系统维护，信用卡支付功能暂时关闭，给您带来的不便还望多多谅解！</p>
            <button class="btn_red" id="credit_know">朕知道了</button>
        </div>
            
            <div id="overDiv" style="display:none;"></div>
        <div id="diolo_warp" class="diolo_warp" style="display:none;">
        <p class="title_cz">您正在发起绑定银行卡操作</p>
        <p class="pay_bank">将跳转至第三方"易宝支付"进行银行卡扣款验证</p>
        <p class="radious_img"></p>
        <!--<p class="go_on"><span>＊连连支付：</span>支持182家银行无卡支付.</p>-->
        <div class="true_flase">
            <button class="flase_qx" id='hlz'>取消</button>
            <button class="true_qr" id='tbug'>确定</button>
        </div>
        </div>    
        </form>
    </div>                            
</div>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
$('#lzh').click(function(){
	$('#diolo_warp').show();
	$('#overDiv').show();
	return false;
});

$('.Hmask').click(function(){
    $('#Hmask').hide();
	$('#ques').hide();
	return false;
});

$('#hlz').click(function(){
    $('#diolo_warp').hide();
	$('#overDiv').hide();
	return false;
});

$('#tbug').bind('click',function(){
	$('form[id="tbug"]').submit();
});
</script>
<script>
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

            wx.ready(function() {
                wx.hideOptionMenu();
            });
</script>