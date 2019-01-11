<script src="/js/bankC.js?v=2015092901"></script>
<?php
$bank = array('ABC', 'ALL', 'BCCB', 'BCM', 'BOC', 'CCB', 'CEB', 'CIB', 'CMB', 'CMBC', 'GDB', 'HXB', 'ICBC', 'PAB', 'PSBC', 'SPDB', '中信');
?>
<div class="Hcontainer nP">
    <div class="main mt20">
        <p class="n30 grey2">购买<span class="n42 red"><?php echo $guarantee->var*$post_data['num'] ?></span>元担保卡</p>
         <form action="/dev/guarantee/payyibao" method="post" id="pay">
        <!--<form action="/dev/guarantee/paylian" method="post" id="pay">-->
            <div class="bank_choose highlight mt20">
                <div class="bank_sure">
                    <img src="/images/bank_logo/<?php
                    if (!empty($card[0]['bank_abbr']) && in_array($card[0]['bank_abbr'], $bank)) {
                        echo $card[0]['bank_abbr'];
                    } else {
                        echo 'ALL';
                    }
                    ?>.png" width="10%">
                    <span class="n26 grey2"><?php echo $card[0]->bank_name; ?></span><b class="redLight" style="margin-right: 2%;"><?php echo $card[0]->type == 0 ? '借记卡' : '信用卡'; ?></b><span class="n22 grey4">尾号<?php echo substr($card[0]->card, strlen($card[0]->card) - 4, 4) ?></span>
                </div>
                <input type="hidden" name="card_id" value="<?php echo $card[0]->id; ?>" />    
                <i class=""></i>               
            </div>
            <div class="wrap22">
                <ul class="banksC">
                    <?php foreach ($card as $key => $val): ?>
                        <li class="on" id="<?php echo $val->id; ?>" bid="<?php echo $val->type;?>" mob="<?php echo $val->bank_mobile; ?>">
                            <img src="/images/bank_logo/<?php
                            if (!empty($val['bank_abbr']) && in_array($val['bank_abbr'], $bank)) {
                                echo $val['bank_abbr'];
                            } else {
                                echo 'ALL';
                            }
                            ?>.png" width="10%">
                            <span class="n26 grey2"><?php echo $val->bank_name; ?></span><b class="redLight" style="margin-right: 2%;"><?php echo $val->type == 0 ? '借记卡' : '信用卡'; ?></b><span class="n22 grey4">尾号<?php echo substr($val->card, strlen($val->card) - 4, 4) ?></span>
                        </li>
                    <?php endforeach; ?>
                 <?php ?>
                    <a href="/dev/bank/addcard?card_id=<?php echo $post_data['card_id']; ?>&num=<?php echo $post_data['num']; ?>&url=<?php echo $_SERVER['REQUEST_URI'];?>" class="addbankC">添加银行卡</a>
                </ul> 
            </div>
            <!-- 
            <div class="form-group p_ipt mt20" style="padding-left: 3%;">
                <div class="col-xs-4" style="padding-left: 0">开户手机号</div>
                <div class="col-xs-8" style="padding-left: 0">
                    <input type="text" name="mobile" value="<?php echo $card[0]->bank_mobile; ?>" class="ipt">
                    <img src="/images/icon_remove.png" class="icon_Rem">
                </div>
            </div>


            <input type="hidden" name="pay_key" value="">
            <input type="hidden" name="order_id" value="">
            <div class="col-xs-6" style="padding-right: 2%;">
                <div class="dbk_inpS" style="padding-left: 6%;">
                    <input type="text" name="verifyCode" maxlength="6" style="border:none;width:100%;text-align:left;" placeholder="短信验证码">
                </div>
            </div>
            <div class="col-xs-6" style="padding-left: 2%;">
                <button type="submit" class="btn borderRed" style="vertical-align:top;width:100%;font-size:2.6rem;padding:10px 0;" id="code">获取验证码</button>
            </div>
            <div class="mt20 float-left" style="width:100%">
                <span id="respone" style="color: red;"></span>
            </div>
            -->
            <input type="hidden" name="guarantee" value="<?php echo $post_data['card_id']; ?>">
            <input type="hidden" name="guarantee_num" value="<?php echo $post_data['num']; ?>">
            <input type="hidden" name="bank_type" id="bank_type" value="<?php echo $card[0]->type; ?>">
            <div class="clearfix"></div>
            <!--<input type="submit" class="btn mt20 mb40" style="width:100%" value="确认购买" id="tbug">-->
			<p class="btn mt20 mb40" style="width:100%" id="lzh">确认购买</p>
             <div style="font-size: 14px;">注：受银行支付通道影响，交行卡，农行卡支付业务暂时暂停，请选择（绑定）其他银行卡支付。</div>
        
        <div class="Hmask" style="display:none;"></div>    
        <div class="xhb_layer pad" id="ques" style="display:none;">
            <img src="/images/kelianren.png" style="width:30%;position: absolute;top:-85px;left:-5px;width:70px;">
            <h4 style="text-align:center;margin-top:10px;">支付／还款失败</h4>
            <p class="n28 mt40" style="margin-top:10px;"><span class="red">非常抱歉～(╥﹏╥)～</span><br/>由于第三方连连支付系统维护，信用卡支付功能暂时关闭，给您带来的不便还望多多谅解！</p>
            <button class="btn_red" id="credit_know">朕知道了</button>
        </div>
              
			  <div id="overDiv"  style="display:none;"></div>
			  <div id="diolo_warp" class="diolo_warp" style="display:none;">
        <p class="title_cz">您正在发起购买<span><?php echo $guarantee->var*$post_data['num'] ?></span>元的担保卡操作</p>
        <p class="pay_bank">将跳转至第三方'易宝支付'进行银行卡扣款验证</p>
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

<!--
<script>
    $(function(){
       var height = $('.form-group.p_ipt ').height();
       $('.bank_choose').css('height',height+22);
    });
    window.onload = function() {
        var oBtn = document.getElementById('code');
        var timer = null;
        var time = 60;
        var s = time + 1;
        oBtn.onclick = function() {
            countDown();
            timer = setInterval(countDown, 1000);
            oBtn.disabled = true;
            $.ajax({
                type: "POST",
                dataType: "json",
                //url:"bs",
                url: "/dev/guarantee/paylian",
                data: $('#pay').serialize(), // 你的formid
                async: false,
                error: function(data) {
                    alert('参数错误，请退出重新进入');
                },
                success: function(data) {
                    if (data.rsp_code == '0000' && ("pay_key" in data) ) {
                        $('input[name="pay_key"]').val(data.pay_key);
                        $('input[name="order_id"]').val(data.order_id);
                        $('#respone').html('');
                    } else {
                        $('#respone').html(data.rsp_msg);
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
        if (!$("input[name='pay_key']").val() || !$("input[name='order_id']").val()) {
            $('#respone').html('请先获取验证码');
            return false;
        }
        var reg = /^\d{6}$/;
        if (!$("input[name='verifyCode']").val() || !reg.test($("input[name='verifyCode']").val())) {
            $('#respone').html('请输入正确的验证码');
            return false;
        }
        var tBug = document.getElementById('tbug');
        tBug.disabled = true;
        var mark = false;
        $.ajax({
            type: "POST",
            dataType: "json",
            //url:"bs",
            url: "/dev/guarantee/pay",
            data: $('#pay').serialize(), // 你的formid
            async: false,
            error: function(data) {
                tBug.disabled = false;
                $('#respone').html('');
                location.href = "/dev/guarantee/error";
            },
            success: function(data) {
                tBug.disabled = false;
                if (data.rsp_code == '0000') {
                    location.href = "/dev/guarantee/success?money="+data.money_order;
                } else {
                    $('#respone').html(data.rsp_msg);
                    if (data.rsp_code == '1116') {
                        var start = data.rsp_msg.indexOf('[');
                        var end = data.rsp_msg.indexOf(']');
                        var str = data.rsp_msg.substr(start + 1, end - start - 1);
                        if (str == '短信校验码错误') {
                            $('#respone').html('短信校验码错误');
                        } else {
                            $('#respone').html('');
                            location.href = "/dev/guarantee/error";
                        }
                    } else {
                        $('#respone').html('');
                        location.href = "/dev/guarantee/error";
                    }
                }
            }
        });
        return mark;
    }
</script>
-->
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
	appId: '<?php echo $jsinfo['appid'];?>',
	timestamp: <?php echo $jsinfo['timestamp'];?>,
	nonceStr: '<?php echo $jsinfo['nonceStr'];?>',
	signature: '<?php echo $jsinfo['signature'];?>',
	jsApiList: [
		'hideOptionMenu'
	  ]
  });
  
  wx.ready(function(){
	  wx.hideOptionMenu();
	});
</script>