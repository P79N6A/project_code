<div class="usere_cont">
    <div class="user_boxb">
        <div class="disitem user_gzgz ">
            <div class="gzgz_left">● 好友借款抽成</div>
            <div class="gzgz_rights two" click="1"></div>
        </div>
        <div class="user_txtxdts">
            好友首次借款成功，大家都有分润提成哦。提成比例：5‰:3‰:1‰抽成。如，4级用户借款1000元，3级用户抽成5元，2级用户抽成3元，1级用户抽成1元。（好友级别关系可通过我的好友查看）。
        </div>
    </div>
    <div class="user_boxb">
        <div class="disitem user_gzgz ">
            <div class="gzgz_left ">● 好友积分制度</div>
            <div class="gzgz_rights"></div>
        </div>
        <div class="user_txtxdts" style="display:none;">
            积分一路狂跟。推荐好友注册奖励1分；推荐的好友审核通过，奖励3分；好友借款成功，奖励10分；好友还款成功，奖励20分。
        </div>
    </div>
    <div class="user_boxb">
        <div class="disitem user_gzgz ">
            <div class="gzgz_left ">● 一级好友注册过审</div>
            <div class="gzgz_rights"></div>
        </div>
        <div class="user_txtxdts" style="display:none;">
            成功邀请一名好友注册并通过审核即可获得10元现金奖励，最高奖励不封顶，在此名好友发起借款并还款前此收益暂时冻结。
        </div>
    </div>
    <div class="user_boxb">
        <div class="disitem user_gzgz ">
            <div class="gzgz_left ">● 一级好友首次借款</div>
            <div class="gzgz_rights"></div>
        </div>
        <div class="user_txtxdts" style="display:none;">
        成功邀请一名好友通过审核并首次发起借款成功下款再获得10元现金奖励，最高奖励不封顶，在此名好友此笔借款还款前此收益暂时冻结。
        </div>
    </div>
    <div class="user_boxb">
        <div class="disitem user_gzgz ">
            <div class="gzgz_left ">● 一级好友首次还款</div>
            <div class="gzgz_rights"></div>
        </div>
        <div class="user_txtxdts" style="display:none;">
        成功邀请一名好友首次借款并还款成功再获得10元现金奖励，最高奖励不封顶，此好友还款成功后解锁此好友冻结的过审奖励10元和首次借款收益10元，共30元。
        </div>
    </div>
</div>
<script>
    $('.nav_right').click(function(){
        window.location.href = '<?php echo $returnUrl ?>';
    })
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
            'closeWindow',
            'hideOptionMenu'
        ]
    });

    wx.ready(function() {
        wx.hideOptionMenu();
    });
</script>