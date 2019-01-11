
<div class="Hcontainer">
    <div class="padlr344 bRed overflow">
        <div class="col-xs-12 border_bottom_white padtb10">
            <div class="col-xs-6 n24 white text-center relative">
                <p><span class="n60"><?php echo $user_counts ;?></span>笔</p>
                <p>投资中的借款</p>
                <img src="/images/icon_ques4.png" class="icon_ques4">
            </div>
            <div class="col-xs-6 n24 white text-center border_left_white">
                <p><span class="n60"><?php echo number_format($total*0.01,2,'.','');?></span>点</p>
                <p>累计收益</p>
            </div>
        </div>
        <div class="clearfix"></div>
        <div style="padding:10px 10px;">
            <p class="n24 white">可用担保额度 <span class="n40"><?php echo $current_amount ;?></span> 点</p>
        </div>
    </div>
    <!-- 投资列表 -->
    <img src="/images/dtz.png" style="width:44%;max-width:284px;margin: 10px auto;display:block;">
    <div class="padlr344">
        <?php if( $user_info ){
		foreach ( $user_info as $v ){
	   ?>

        <?php if($v['user']['xs'] == 2):?>
        <div class="dbr_inv mt20">
            <div style="border-bottom: 1px dashed #c2c2c2;padding-bottom: 10px" class="overflow">
                <div class="col-xs-2 nPad">
                    <!--<img src="<?php echo $v['user']['head'];?>" style="width:90%;max-width:80px;">-->
					<span class="gender_wrap" style="width:90%;max-width:80px;">
                        <img class="face2" src="<?php echo $v['user']['head'];?>">
                         <?php if($v['user']['sex']==1){?>
                        <img src="/images/icon_boy.png" class="gender2">
						<?php } else {?>
						<img src="/images/icon_girl.png" class="gender2">
						<?php } ?>
                    </span>
                </div>
                <div class="col-xs-3" style="padding-left:5px;">
                    <p class="n24 mt10"><?php echo $v['user']['realname'];?></p><p class="n20">剩余<?php echo floor((strtotime($v['loan']['open_end_date'])-time())/3600);?>小时</p>
                </div>
                <div class="col-xs-7 float-right nPad mt10">
                    <div class="col-xs-4 nPad n22 text-center">
                        <p class="bRed white" style="width:96%;border-radius:5px;padding:3px 0;">借款周期</p>
                        <div class="clearfix"></div>
                        <p class="mt10"><?php echo $v['loan']['days'];?>天</p>
                    </div>
                    <div class="col-xs-4 nPad n22 text-center">
                        <p class="bRed white" style="width:96%;border-radius:5px;padding:3px 0;margin:0 auto;">年化收益</p>
                        <div class="clearfix"></div>
                        <p class="mt10"><?php echo round(0.01/$v['loan']['days']*365*100,1);?>%</p>
                    </div>
                    <div class="col-xs-4 nPad n22 text-center">
                        <p class="bRed white float-right" style="width:96%;border-radius:5px;padding:3px 0;">熟人关系</p>
                        <div class="clearfix"></div>
                        <p class="mt10">同校担保</p>
                    </div>
                </div>
            </div>
            <div class="mt30">
                <p class="n30">借款用途：<?php echo $v['loan']['desc'];?>。</p>
                <a href="/dev/sponsor/detial?loan_id=<?php echo $v['loan_id'];?>&user_id=<?php echo $v['user_id'];?>" style="padding:7px 15px;float:right;" class="white bRed borRad5">查看详情</a>
            </div>            
        </div>
        <?php endif;?>

    <?php } }else{ ?>
    <!-- 没有借款请求 -->
    <div class="text-center" style="margin-top: 40px;">
        <img src="/images/dbr_borrow.png" width='20%'>
        <p class="n30 mt40 grey4">还没有人向你发起担保借款请求</p>
    </div>
    <?php } ?>
	</div>
    <footer class="redline">
        <ul class="text-center">
            <li style="margin-left: 16%;">
                <a href="https://open.weixin.qq.com/connect/oauth2/authorize?appid=<?php echo Yii::$app->params['AppID'];?>&redirect_uri=<?php echo Yii::$app->params['app_url'];?>/dev/sponsor/index&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect"><img src="/images/01.png" width="33%"/><div class="red n26">投资</div></a>
            </li>
            <li style="float:right;margin-right:16%;">
                <a href="https://open.weixin.qq.com/connect/oauth2/authorize?appid=<?php echo Yii::$app->params['AppID'];?>&redirect_uri=<?php echo Yii::$app->params['app_url'];?>/dev/guarantoraccount/index&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect"><img src="/images/033.png" width="33%"/><div class="cor n26">账户</div></a>
            </li>
        </ul>
    </footer>
    <!-- 弹层 -->
    <div class="Hmask" style="display: none;"></div>
    <div class="xhb_layer pad" style="display: none;">
        <img src="/images/icon_wt.png" style="width:30%;position: absolute;top:-84px;left:-5px;width:100px;">
        <p class="n26 mt20"><span class="red">投资中的借款：</span>已投资还没有获得收益的担保借款都属于投资中借款。</p>
        <button class="btn_red">朕知道了</button>
    </div>
</div>
<script>
    $(function(){
        $('.Hmask').click(function(){
            $('.xhb_layer').hide();
            $('.Hmask').hide();
        });
        $('.icon_ques4').click(function(){
            $('.Hmask').show();
            $('.xhb_layer').show();
        });
        $('.btn_red').click(function(){
            $('.Hmask').hide();
            $('.xhb_layer').hide();
        });
    });
</script>
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