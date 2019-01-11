<div class="wrap Hcontainer nP">
    <div class="ed_head">
        <!-- 去掉图片 -->
        <!-- 去掉图片 -->
        <div class="ed_head_t">
            <div class="n26 grey2">授信额度:</div>
            <div class="n72 red"><?php echo number_format($userinfo['account']['amount'], 2, '.', ''); ?><span class="n24 grey2">点</span></div>
            <!-- 加一朵云 -->
            <img src="/images/clouds.png" class="ed_clouds">
            <!-- 加一朵云 --> 
        </div>
    </div>
    <!-- 加一亿元 -->
    <div style="position: relative;">
        <div style="padding:0 3.44%;">
            <div class="ed_yyy n26 mb20">
                <div class="col-xs-2"><img src="/images/placeholder.png"></div>
                <div class="col-xs-6 n26" style="color: #fff;line-height: 2.6rem;">距离一亿元还差:</div>
            </div>
        </div>
        <a href="/dev/account/remain">
            <div class="ed_text n26">
                <div class="col-xs-2"><img src="/images/icon_yyyuan.png"></div>
                <div class="col-xs-6 grey2">提额攻略</div>
                <div class="col-xs-4 text-right"><span class="red"><img src="/images/arrowRed.png" style="width:6%;margin-left: 3%;"></span></div>
            </div> 
        </a>
    </div>
    <!-- 加一亿元 --> 
    <div class="ed_cont">
        <div class="ed_item n26">
            <a href="javascript:void(0)" onclick="alert('充值业务正在施工中，敬请期待……');" class="cor_4">
                <div class="col-xs-2"><img src="/images/icon_zh.png"></div>
                <div class="col-xs-6 grey2">当前可用额度:</div>
                <div class="col-xs-4 text-right">
                    <span class="red"><?php echo sprintf("%.2f", $userinfo['account']['current_amount']); ?></span>点
                    <!-- 去掉span，加图片 -->
                    <img src="/images/arrowRed.png" style="width:6%;margin-left: 3%;">
                </div>
            </a>
        </div>
        <div class="ed_item n26">
            <a href="/dev/account/investlist?user_id=<?php echo $userinfo->user_id; ?>" class="cor_4">
                <div class="col-xs-2"><img src="/images/icon_sr.png"></div>
                <div class="col-xs-6 grey2">投资熟人的额度:</div>
                <div class="col-xs-4 text-right">
                    <span class="red"><?php echo sprintf("%.2f", $userinfo['account']['current_invest']); ?></span>点
                    <!-- 去掉span，加图片 -->
                    <img src="/images/arrowRed.png" style="width:6%;margin-left: 3%;">
                </div>
            </a>
        </div>
        <div class="ed_item n26">
            <a href="/dev/account/investxhh?user_id=<?php echo $userinfo->user_id; ?>" class="cor_4">
                <div class="col-xs-2"><img src="/images/icon_xhb.png"></div>
                <div class="col-xs-6 grey2">投资先花宝的额度:</div>
                <div class="col-xs-4 text-right">
                    <span class="red"><?php echo $total; ?></span>点
                    <!-- 去掉span，加图片 -->
                    <img src="/images/arrowRed.png" style="width:6%;margin-left: 3%;">
                </div>
            </a>
        </div>
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