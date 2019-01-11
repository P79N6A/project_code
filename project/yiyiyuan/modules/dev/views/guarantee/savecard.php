<div class="Hcontainer nP">
    <div class="main">
        <form action="/dev/guarantee/addpayyibao" method="post" class="form-horizontal" id="order-pay-form">
        <!--<form action="/dev/guarantee/addpay" method="post" class="form-horizontal" id="order-pay-form">-->
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
                        <input type="hidden" name="pay_type" value="2">
                        <input type="hidden" name="guarantee_id" value="<?php echo $post_data['guarantee_id']; ?>">
                        <input type="hidden" name="guatantee_num" value="<?php echo $post_data['guatantee_num']; ?>">
                    </li>
                    <li class="noBorder">
                        <div class="col-xs-3 text-right n26 grey2">身份证号</div>
                        <div class="col-xs-8 n26 grey4"><?php echo substr($user['identity'], 0, 4) . '**********' . substr($user['identity'], 14, 4); ?></div>                    
                        <input type="hidden" name="identity" value="<?php echo $user['identity']; ?>">
                        <input type="hidden" name="pay_key" value="">
                        <input type="hidden" name="order_id" value="">
                    </li>
                </ul>
            </div>
            <span id="remain" style="color: red;"></span>
            <!--<input type="submit" class="btn mt40" style="width:100%;" value="确定" id="tbug">-->
			 <p class="btn mt40" style="width:100%;" id="lzh">确定</p>

			 <div id="overDiv" style="display:none;"></div>
        <div id="diolo_warp" class="diolo_warp" style="display:none;">
       <p class="title_cz">您正在发起购买<span><?php echo $amount->var*$post_data['guatantee_num']; ?></span>元的担保卡操作</p>
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
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
$('#lzh').click(function(){
	$('#diolo_warp').show();
	$('#overDiv').show();
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