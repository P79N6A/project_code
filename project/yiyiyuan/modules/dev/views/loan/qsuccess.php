
<!--<body>
    <div class="Hcontainer nP">
        <div class="padtb50 bWhite border_bottom_grey">
            <p class="n46 text-center"><span class="red"><?php echo $amount;?>元</span>担保借款成功！</p>
            <p class="grey4 mt20 n26 text-center">资金即将到账，请注意查看您尾号为<?php echo substr($user_bankinfo->card,-4);?>的<?php echo $user_bankinfo->bank_name?>卡</p>
        </div>
        <p class="grey2 n26 text-center mt30">您的剩余担保额度为<?php echo $gu;?>，看看其他人都干了什么？</p>
        <div class="text-center mt50">
            <a href="/dev/guarantee/index"><img src="/images/dbk2.png" width="70%"></a>
            <a href="/dev/investxhb/xhb"><img src="/images/xhb.png" width="70%" class="mt40"></a>
        </div>                         
   </div>-->

   <div class="Hcontainer nP">
        <div class="wrapBg" style="padding-top:0px;margin: 20px 5% 0;border: 1px solid #c2c2c2; border-radius:5px; background-size: 100% 100%;">
            <div style="height:170px;">
            	<div style=" padding-top:8%;">
            		<img src="/images/lvse.png" width="60" height="60" style="float:left; margin-left:25%;margin-top: 15px; ">
            		<span style="line-height:22px;margin-left:10px; display:inline-block;float:left; text-align:left;margin-top: 20px;">
            			<em style="font-size:24px; color:#e74747;"><?php echo $amount;?>元</em><br/>
            			<em style="font-size:20px; color:#444；">担保借款成功！</em>
            		</span>
            	</div>
           		<p style="clear:both;text-align:center; width:100%; line-height:30px; font-size:12px; color:#858585; padding-bottom:10px;">＊钱来了！请注意查看您尾号<?php echo substr($user_bankinfo->card,-4);?>的<?php echo $user_bankinfo->bank_name?>储蓄卡</p>

            </div>

            <img src="/images/heg.png" style="width:46%;margin-left: 20%">
        </div>
        <div style=" font-size:14px; text-align:center; margin-top:20px;">亲，您没担保额度啦～但是可以做以下投资哟～</div>
        <div class="main succ">
            <div class="col-xs-6 text-center"><a href="/dev/loan/borrowing" class="cor_4"><img src="/images/touzi_true3.png" width="50%" class="mb20"><p class="n26">担保卡</p></a></div>
            <div class="col-xs-6 text-center"><a href="/dev/loan/index" class="cor_4"><img src="/images/touzi_true4.png" width="50%" class="mb20"><p class="n26">投资好友</p></a></div>
            
        </div>                           
   </div>

   <script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
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
