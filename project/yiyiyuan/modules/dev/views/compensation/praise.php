<script>
	$(function(){
		var wHeight = $(window).height();
		var iHeight = $('.pic_bg').height();
		if(wHeight>iHeight){
			$('.pic_bg').css('height',wHeight);
			$('.container').css('height',wHeight);
		}else{
			$('.container').css('height',iHeight);
		}
	});
	
</script>
<div class="container">
    <img src="/images/july/4.jpg" width="100%" class="pic_bg">
    <img src="/images/july/aaa.png" width="100%" class="title">
    <img src="<?php echo Yii::$app->params['back_url'].'/'.$seven_prize->pic;?>" class="upLoadImg" style="margin:auto;left: 0; right: 0;">
    <p class="txt1"><?php echo !empty($user->userwx->nickname)?htmlspecialchars(mb_substr($user->userwx->nickname,0,4,'utf-8')):$user->realname;?>获得了<?php echo $prize[$seven_prize->prize_id];?>！<br />快去秀恩爱，还有免签双人海岛游等大奖，等着你哦！</p>
    <?php if(!empty($seven_prize->speak)):?><div class="show"><?php echo $user->realname.'：'.$seven_prize->speak;?></div><?php endif;?>
    <a href="javascript:click();" id="clicks"><img src="/images/july/btn2.png" class="btn2"></a>
    <?php if($seven_click!=0):?><a href="javascript:void(0);"><img src="/images/july/btn4.png" class="btn2"></a><?php endif;?>
    <a href="<?php echo $seven_url;?>"><img src="/images/july/btn3.png" class="btn3"></a>
</div>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
    function click(){
        var user_id = '<?php echo $user->user_id;?>';
        $.post("/dev/compensation/click",{user_id:user_id},function(result){
            var data = eval("("+ result + ")" ) ;
            if( data.ret == '0' ){
                var html = '<a href="javascript:void(0);"><img src="/images/july/btn4.png" class="btn2"></a>';
                $('#clicks').append(html);
            }
        });
    }
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