<body class="g1">
<div class="mHeader fCf"> <a href="javascript:history.go(-1);"><img src="/img/logo.png" class="logo" alt="" /></a>
    <div class="info">
        <h3><img src="/img/icon_logo.png" class="icon-logo" alt="" /> 信用即财富，圈子即价值 <img src="/img/icon_logo.png" class="icon-logo" alt="" /></h3>
        <p>好想知道自己在朋友中的知名度如何！快来答题提升我的人气吧！完成后您和好友都可获得信用额度哦</p>
    </div>
</div>
<div class="mCredit">
    <div class="value-wrap">
        <div class="value"> 当前信用值<br>
            <strong><?php if(!empty($userinfo)):?><?php echo sprintf("%.2f", $auth_count['amount']);?><?php else:?>0.00<?php endif;?></strong>点 </div>
    </div>
    <div class="help-wrap">
        <div class="help"> *帮助"<strong><?php echo $userinfowx['nickname'];?></strong>"积攒信用值，你的好友就可以获得更多理财金，关系铁不铁，看此一举~ </div>
    </div>
</div>
<div class="mButton"> <a href="/dev/auth/first?wid=<?php echo $wid;?>" class="aButton">Ta是谁</a> <?php if(!empty($userinfo)):?><a href="/dev/auth/index" class="aButton aButton-2">我也要测试</a><?php endif;?> </div>
<div class="mHelpPartner">
    <div class="title">帮忙的小伙伴</div>
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
<div class="mLayerMask">
    <div class="mLayer"> <a href="javascript:void;" id="close_window" class="close"></a>
        <div class="info">您已经认证过该好友，不能再进行此次认证，是否直接借给他额度？</div>
        <div class="button fCf"><a href="javascript:void;" id="close_window_button" class="aButton fFl">关系一般般</a><a href="https://open.weixin.qq.com/connect/oauth2/authorize?appid=<?php echo Yii::$app->params['AppID'];?>&redirect_uri=<?php echo Yii::$app->params['app_url'];?>/dev/auth/help&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect" class="aButton fFr">决意帮到底</a></div>
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