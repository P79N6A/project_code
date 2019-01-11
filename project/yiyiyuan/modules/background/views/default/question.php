<div class="usere_cont">
    <div class="user_boxb">
        <div class="disitem user_gzgz ">
            <div class="gzgz_left">● 赚钱妖怪如何赚钱？</div>
            <div class="gzgz_rights two" click='1'></div>
        </div>
        <div class="user_txtxdts">
            邀请好友注册：将邀请链接或者邀请码分享给好友，好友通过你的邀请码完成手机号注册。<br/>
            好友实名过审：邀请好友完成实名注册，审核通过后，你将获得10元现金收益。<br/>
            好友首次借款过审：邀请的好友完成借款必填资料发起借款并审核通过，你将获得10元现金收益。<br/>
            好友首次全额还款：邀请好友借款到期后全额还款成功，你将获得10元现金收益。<br/>
            好友借款：好友发起借款后按照比例进行现金奖励。
        </div>
    </div>
    <div class="user_boxb">
        <div class="disitem user_gzgz ">
            <div class="gzgz_left ">● 什么是实名注册？</div>
            <div class="gzgz_rights"></div>
        </div>
        <div class="user_txtxdts" style="display:none;">
            实名注册是指用户提交身份证自拍照，且自拍照审核通过即可；
        </div>
    </div>
    <div class="user_boxb">
        <div class="disitem user_gzgz ">
            <div class="gzgz_left ">● 收益何时到账？</div>
            <div class="gzgz_rights"></div>
        </div>
        <div class="user_txtxdts" style="display:none;">
            邀请收益：当日可获得昨日的邀请收益。收益提现一般2小时到账；
        </div>
    </div>
    <div class="user_boxb">
        <div class="disitem user_gzgz ">
            <div class="gzgz_left ">● 收益何时可以提现？</div>
            <div class="gzgz_rights"></div>
        </div>
        <div class="user_txtxdts" style="display:none;">
            提现时间：每天10：30至18:00，如遇特殊情况另行通知；
        </div>
    </div>
    <div class="user_boxb">
        <div class="disitem user_gzgz ">
            <div class="gzgz_left ">● 什么是冻结收益？</div>
            <div class="gzgz_rights"></div>
        </div>
        <div class="user_txtxdts" style="display:none;">
            冻结收益是指好友通过审核、发起借款通过审核后你预计得到的收益，如好友按时全额还款成功，冻结收益将成为可提现收益，若好友借款未成功或者发生逾期，冻结收益将自动清除。
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