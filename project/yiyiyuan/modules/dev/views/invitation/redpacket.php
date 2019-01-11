<div class="fInvitation">
	<div class="trueall" <?php if($is_yyy == 'no'):?>endtime="<?php echo $left_time;?>"<?php endif;?>>
		<p class="trueall1">全部答对!</p>
		<p class="trueall2">可获得66元优惠券红包，仅用时<?php echo $user_time;?>秒哦！</p>
		<div class="trueall4">
			<img class="trueall3" src="/images/account/firstimgdj.png">
			<div class="potiablo"><em>66</em><apsn>元</apsn></div>
		</div>
	</div>
	<?php if($is_yyy == 'no'):?><div class="endsheng"><img src="/images/account/timt.png"> <span>剩余领取时间 <a id="left_time"><?php echo $left_hour;?></a></span></div><?php endif;?>
	<?php if($is_yyy == 'no'):?>
	<?php if($hb_left_time > 0):?>
	<div class="button"> <a href="/dev/invitation/withdrawdetail?grant_id=<?php echo $grant_id;?>"><button>立即提现</button></a></div>
	<?php else:?>
	<div class="button"><button disabled="disabled" class="lijitq">立即提现</button></div>
	<?php endif;?>
	<?php else:?>
	<div class="button"> <a href="/dev/account/income?user_id=<?php echo $user_id;?>"><button>去收益查看</button></a></div>
	<?php endif;?>
	<div class="certification">
		<div class="cert_one"><img src="/images/account/fircert.png">红包榜</div>
		<?php if(!empty($red_packet_list)):?>
		<?php foreach ($red_packet_list as $key=>$value):?>
		<div class="cert_two">
			<img src="<?php echo $value['head'];?>">
			<div class="cert_two2"><p class="p1"><?php if(!empty($value['nickname'])):?><?php echo $value['nickname'];?><?php else:?><?php echo $value['realname'];?><?php endif;?></p><p class="p2"><?php echo date('m'.'月'.'d'.'日'.' H'.':'.'i', strtotime($value['create_time']));?></p></div>
			<div class="cert_two3"><?php echo sprintf("%.2f", $value['amount']);?>元</div>
		</div>
		<?php endforeach;?>
		<?php endif;?>
	</div>
</div>
<?php if($is_yyy == 'no'):?>
<script>
    setTimeout('leftEndtime()', 1000);
</script>
<script>
var leftEndtime = function () {
    var endtime = $(".trueall").attr("endtime") * 1000;//取结束日期(毫秒值)
	    var nowtime = new Date().getTime();        //今天的日期(毫秒值)
	    var youtime = endtime - nowtime;//还有多久(毫秒值)
	    var seconds = youtime / 1000;
	    var minutes = Math.floor(seconds / 60);
	    var hours = Math.floor(minutes / 60);
	    var days = Math.floor(hours / 24);
	    var CDay = days;
	    var CHour = hours % 24;
	    if (CHour < 10)
	    {
	        CHour = '0' + CHour;
	    }
	    var CMinute = minutes % 60;
	    if (CMinute < 10)
	    {
	        CMinute = '0' + CMinute;
	    }
	    var CSecond = Math.floor(seconds % 60);//"%"是取余运算，可以理解为60进一后取余数，然后只要余数。
	    if (CSecond < 10)
	    {
	        CSecond = '0' + CSecond;
	    }
	    if (endtime <= nowtime) {
	        //$(".time").html("<span class='times'>00<span class='font'>时</span></span>&nbsp;<span class='colon'>:</span>&nbsp;<span class='times'>00<span class='font'>分</span></span>&nbsp;<span class='colon'>:</span>&nbsp;<span class='times'>00<span class='font'>秒</span></span>");//如果结束日期小于当前日期就提示过期啦
	        $("#left_time").html("00:00:00");
	    } else {
	        //$(".time").html("<span class='times'>"+CHour+"<span class='font'>时</span></span>&nbsp;<span class='colon'>:</span>&nbsp;<span class='times'>"+CMinute+"<span class='font'>分</span></span>&nbsp;<span class='colon'>:</span>&nbsp;<span class='times'>"+CSecond+"<span class='font'>秒</span></span>");
	        $("#left_time").html("0:" + CMinute + ":" + CSecond);
	    } 
	    setTimeout('leftEndtime()', 1000);
};
</script>
<?php endif;?>
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
