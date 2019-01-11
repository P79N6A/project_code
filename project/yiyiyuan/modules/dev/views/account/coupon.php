<div class="youhuiju">
        <div class="user_rule">
            <span class="rule_one">可使用</span>
            <a href="/dev/account/usehelp" class="rule_two"><img src="/images/rule_gz.png">使用规则</a>
        </div>
        <div class="layer" style="position:absolute;">
            <div class="content padlr">
            	<?php if(!empty($couponlist)):?>
                <?php foreach ($couponlist as $key=>$value):?>
                <?php if( $value['@type:=1'] == '2'){?>
                <div class="item" tp='2' ids="<?php echo $value['id'];?>">
                    <img src="/images/unchoose.png" id="tz_img" class="available2">
                    <div class="price_left">
                        <p class="black" id="tz_p1"><?php echo $value['limit'];?>倍收益<span>券</span></p>
                        <p class="green" id="tz_p2">信用投资</p>
                    </div>
                    <div class="price_right">
                        <p class="one_one"><?php echo $value['val'];?>天<?php echo $value['limit'];?>倍收益券</p>
                        <p class="one_two">自计息日起<?php echo $value['val'];?>天收益双倍</p>
                        <p class="one_three">有效期：<?php echo date('Y'.'年'.'m'.'月'.'d'.'日', (strtotime($value['end_date'])-24*3600));?></p>
                    </div>
                </div>
                
                <?php }else{?>
                <div class="item" tp='1' ids="<?php echo $value['id'];?>">
                    <img src="/images/unchoosered.png" id="fr_img" class="available2">
                    <div class="price_left">
                        <p class="black" id="fr_p1"><?php if($value['val'] == 0):?>全免<span></span><?php else:?><?php echo $value['val'];?>元<?php endif;?><span>券</span></p>
                        <p class="green" id="fr_p2">好友借款</p>
                    </div>
                    <div class="price_right">
                        <p class="one_one">免息卷</p>
                        <p class="redred">不限金额</p>
                        <p class="one_three">有效期：<?php echo date('Y'.'年'.'m'.'月'.'d'.'日', (strtotime($value['end_date'])-24*3600));?></p>
                    </div>
                </div>
                <?php }?>
                <?php endforeach;?>
                <?php endif;?>
            </div>                    
        </div>
        <button class="user_goon change_gray" tp="0">立即使用</button>
    </div>
<script  src='/dev/st/statisticssave?type=20'></script>       
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