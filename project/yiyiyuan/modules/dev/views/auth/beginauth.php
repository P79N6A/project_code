<div class="Hcontainer nP" style="background: #f8e98a;">
    <div class="mHeader">
        <div class="main">
            <div class="row">
                <a href="javascript:history.go(-1);" class="col-xs-3"><img src="/images/logo.png" class="logo" alt="" /></a>
                <div class="info col-xs-9">
                    <h3 class="n30"><span class="col-xs-3"><img src="/images/icon_logo.png" class="icon-logo " alt="" /></span><span class="col-xs-6 text-center white">谁来谁得便宜</span><span class="col-xs-3 text-right"><img src="/images/icon_logo.png" class="icon-logo " alt="" /></span></h3>
                    <p class="n22 col-xs-12 pt">您的好友"<strong><?php echo $userinfowx['nickname'];?></strong>"邀请您帮助Ta算人品值，同时领取我的理财基金～ </p>
                </div>
            </div>
        </div>    
    </div>
    <img src="/images/renpincs.png" style="width:100%">
    <div class="main">
        <div class="row mf10">
            <p class="col-xs-8 n58 white text-center text_shadow">当前信用值</p>
            <p class="col-xs-12 n58 white text-center text_shadow"><strong class="n115 yellow"><?php echo sprintf("%.2f", $auth_count['amount']); ?></strong>点</p>
        </div>
        <div class="row">
            <div class="col-xs-10 text-right mt40"><img src="/images/rpT.png" width="60%"></div> 
        </div>
        <div class="red n26 mt10 mb30 text-center"> *帮助"<strong class="black"><?php echo $userinfowx['nickname']; ?></strong>"积攒信用值，你的好友就可以获得更多理财金，关系铁不铁，看此一举~ </div>

        <a href="/dev/auth/first?wid=<?php echo $wid; ?>" wid="<?php echo $wid; ?>" class="aButton"><button class="btn mt20" style="width:100%">Ta是谁</button></a>
        <?php if (!empty($userinfo)): ?><a href="/dev/auth/index" class="aButton aButton-2"><button class="btn1 mt20" style="width:100%">我也要测试</button></a><?php endif; ?>
        
        <?php if (!empty($auth_list)): ?>
        <p class="text-center n26 mt20">帮忙的小伙伴</p>
            <?php foreach ($auth_list as $key => $value): ?>
                <div class="border_bottom padtb">
                    <img class="face" src="<?php if(!empty($value['head'])):?><?php echo $value['head'];?><?php else:?><?php echo '/images/dev/face.png'?><?php endif;?>"/>
                    <div class="info_list">
                        <div class="row n28">
                            <div class="col-xs-12"><?php if(!empty($value['nickname'])):?><?php echo $value['nickname'];?><?php else:?><?php echo $value['realname'];?><?php endif;?></div>
                        </div>
                        <div class="row n22">
                            <div class="col-xs-12"><?php echo date('m'.'月'.'d'.'日'.' H:i', strtotime($value['create_time']));?></div>
                        </div>
                    </div>
                    <div class="money">
                        <img src="/images/icon_bang.png" width="35%" class="float-left" style="vertical-align:text-bottom;">
                        <div class="float-right mt10"><span class="red"><?php echo intval($value['amount']);?></span> 点</div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>             
    </div>
</div>

<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
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