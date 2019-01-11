<div class="hdgz">
        <div class="hegthh">
                <div class="youhuhb">
                        <h3>1、活动介绍:</h3>
                        <p>在2016年12月22日-2017年01月22日，按照一定概率每个发起过借款的用户有机会获得“免还款特权、提额、免审、借款优惠券”等奖品奖励。</p>
                        <h3>2、奖品说明:</h3>
                        <p>1）免还款特权</p>
                        <p>用户在获得砸蛋机会后，获得“免还款特权”的奖品后，可全额免除当前已经借款的还款金额。</p>
                        <p>活动期间此奖品每天有500个获奖名额。</p>
                        <p>有效期截止到中奖后31天。</p>
                        <p>2）1000元提额券</p>
                        <p>用户在获得砸蛋机会后，获得“1000元提额券”有效期31天，可在下次发起的借款中使用，并获得相应金额的提额（提额券为临时提额券，仅可使用一次）。</p>
                        <p>活动期间此奖品共计17000个获奖名额，发完即止。</p>
                        <p>有效期截止到中奖后31天。</p>
                        <p>3）免审核特权</p>
                        <p>用户在获得砸蛋机会后，获得“免审核特权”有效期31天，可在下次发起的借款中使用，可享受免审核5分钟内下款（仅可使用一次）。</p>
                        <p>活动期间此奖品共计17000个获奖名额，发完即止</p>
                        <p>有效期截止到中奖后31天。</p>
                        <p>4）58元优惠券</p>
                        <p>用户在获得抽奖机会后，抽取到“58元优惠券“有效期31天，可在下次发起的借款中使用，免除相应的借款利息。</p>
                        <p>活动期间此奖品共计170000个获奖名额，发完即止。</p>
                        <p>有效期截止到中奖后31天。</p>
                        <p>4.仅限微信关注先花一亿元，且未逾期的用户参与。</p>
                        <p>5.通过恶意提供虚假信息或其他舞弊手段参与本获得者，查证后均视为无效邀请，先花一亿元有权要求其返还本金、利息、手续费及活动奖励。</p>
                </div>

        </div>
        <p class="zuzjsqh">最终解释权归先花一亿元所属。</p>
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