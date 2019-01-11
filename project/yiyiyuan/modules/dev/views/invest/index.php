<div class="Investment_record  dontknh">
    <script  src='/dev/st/statisticssave?type=13'></script>
    <div class="record_title">
        <p class="record_titimg"></p>
        <div class="recotit_left">
            <div>
                <p class="red" id="today_income"><span><?php echo sprintf("%.2f", $expectprofit); ?></span>点<a><img src="/images/licai_list.png"></a></p>
                <p>今日预期收益</p>
            </div>
        </div>
        <div class="recotit_right">
            <div>
                <p class="red" id="total_income"><span><?php echo sprintf("%.2f", $total_income); ?></span>点<a><img src="/images/licai_list.png"></a></p>
                <p>累计收益</p>
            </div>
        </div>
    </div>
    <div class="invest_sy">
        <div class="xhb_kyed">
            <p class="kyed_left">可用额度:<em><?php echo sprintf("%.2f", $investinfo['account']['current_amount']); ?></em>点</p>
        </div>
        <div class="xhb_kyeddo">
            <span class="xhhb_title"><img src="/images/xhhb_title.png"><em>先花宝</em></span>
            <span class="xhhb_nlyl">年利率<em>5.00%</em></span>
            <a href="/dev/investxhb/hxhb" class="xhhb_touzi">投资</a>
        </div>
    </div>

</div>
<div style="height:120px;"></div>
<div class="add_friend">
    <div class="addfriend_left">
        <?php if ($auth_num == 0): ?>
            <p>在先花花你还<em>没熟人,</em></p>
            <p>邀请就<em>+100</em>点额度</p>
        <?php else : ?>
            <p style="line-height:48px;">
                <em>邀请熟人+100</em>
                点额度
            </p>
        <?php endif; ?>
    </div>
    <a href="/dev/share/share?open_id=<?php echo $user_id; ?>"><div class="addfriend_right"></div></a>  
</div>
<!--导航栏-->
<?= $this->render('/layouts/_menu', ['page' => 'invest']) ?>

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

    wx.ready(function () {
        wx.hideOptionMenu();
    });
</script>