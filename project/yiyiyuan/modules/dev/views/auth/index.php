<body class="g3">
<script  src='/dev/st/statisticssave?type=4'></script> 
<div class="mHeader fCf"> <a href="javascript:history.go(-1);"><img src="/img/logo.png" class="logo" alt="" /></a>
    <div class="info">
        <h3><img src="/img/icon_logo.png" class="icon-logo" alt="" /> 信用即财富，圈子即价值 <img src="/img/icon_logo.png" class="icon-logo" alt="" /></h3>
        <p>好想知道自己在朋友中的知名度如何！快来答题提升我的人气吧！完成后您和好友都可获得信用额度哦 </p>
    </div>
</div>
<div class="mTips">邀请熟人认证我，可以获得更多信用额度！</div>
<div class="mRenzheng">
    <div class="value-wrap">
        <div class="value"><strong><?php if($auth_count == 0):?>0<?php else:?><?php echo $auth_count['count'];?><?php endif;?></strong>次</div>
        <div class="value2">+<?php echo $auth_count['count']*100;?>点</div>
    </div>
    <div class="help-wrap">
        <div class="help">好友帮助你时，是会看到您的自拍哦~<br>
            每个好友只有一次答题机会，提醒他要谨慎答题哦~</div>
    </div>
    <div class="mButton"> 
    <?php if($user_exist == 'no'):?>
    <a href="<?php echo Yii::$app->params['app_url'];?>/dev/invest" class="aButton">去注册</a> 
    <?php else:?>
    <?php if(empty($userinfo['realname']) && empty($userinfo['identity'])):?>
    <a href="<?php echo Yii::$app->params['app_url'];?>/dev/reg/company" class="aButton">去完善</a>
    <?php elseif($userinfo['status'] == 2):?>
     <a href="javascript:window.location.reload();" class="aButton">刷新</a> 
    <?php elseif(($userinfo['status'] == 1) || ($userinfo['status'] == 4)):?>
    <a href="<?php echo Yii::$app->params['app_url'];?>/dev/reg/pic" class="aButton">去拍照</a> 
    <?php else:?>
    <a href="<?php echo Yii::$app->params['app_url'];?>/dev/auth/beginshare" class="aButton">去寻找熟人认证我</a> 
     <?php endif;?>
    <?php endif;?>
    </div>
</div>
<div class="mHelpPartner">
    <div class="title">认证我的小伙伴</div>
    <ul class="mHelpList">
    <?php if(!empty($auth_list)):?>
    <?php foreach ($auth_list as $key=>$value):?>
        <li> <img src="<?php if(!empty($value['head'])):?><?php echo $value['head'];?><?php else:?><?php echo '/images/dev/face.png'?><?php endif;?>" class="avatar" alt="" />
            <div class="info"> <strong class="name"><?php if(!empty($value['nickname'])):?><?php echo $value['nickname'];?><?php else:?><?php echo $value['realname'];?><?php endif;?></strong> <span class="date"><?php echo date('m'.'月'.'d'.'日'.' H:i', strtotime($value['create_time']));?></span> </div>
            <img src="/img/icon_bang.png" class="icon-type" alt="" /> <span class="value"><strong><?php echo intval($value['amount']);?></strong> 点</span>
             </li>
	<?php endforeach;?>
      <?php endif;?>      
    </ul>
</div>
</body>
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