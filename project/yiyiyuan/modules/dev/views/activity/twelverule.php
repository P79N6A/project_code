<div class="huodallbh">
    <div class="actve818">
        <div class="bannerimg">
            <img src="/images/activity/banner12.jpg">
        </div>

        <div class="certifn">
            <h3>活动奖励</h3>
            <img src="/images/activity/heihei12.png">
        </div>

        <div class="certifn">
            <h3>活动规则</h3>
            <p>1、活动时间：2016年12月10日-2016年12月20日（含） </p>
            <p>2.历史邀请成绩不计入此次活动；</p>
            <p>3.活动期间内，赚钱妖怪流量及现金收益正常开放，活动奖励为额外奖励，根据个人邀请完成时间依次排名（邀请12人及以上均完成成功注册+通过申请+借款到账视作活动全部邀请完成，以第12人完成时间计算最终时间）；</p>
            <p>4.现金红包将返入赚钱妖怪的账户余额中；500元提额券为活动专属提额券，不可与其他优惠券一并使用且为临时提额券（使用次数仅限一次），提额券有效期截止至2017年1月18日24:00，凡有效期内发起的借款申请，均可享受临时额度服务；</p>
            <p>5.全免券有效期截止至2017年1月18日24:00；</p>
            <p>6.仅限微信关注先花一亿元，且未逾期的用户参与；</p>
            <p>7.通过恶意提供虚假信息或其他舞弊手段参与本获得者，查证后均视为无效邀请，先花一亿元有权要求其返还本金、利息、手续费及活动奖励；</p>
            <p class="zhgxhh">最终解释权归先花一亿元所属。</p>
        </div>
    </div>
</div>

<!--<div id="overDiv"></div>
<div class="tancgg">
    <img src="/images/activity/tancgg.png">
    <a class="error"></a>
    <button class="buttonbu"></button>
</div>-->
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