<div class="usere_cont">
        <div class="user_boxb">
            <div class="disitem user_gzgz ">
                <div class="gzgz_left">● 邀请好友</div>
                <div class="gzgz_right"></div>
            </div>
            <div class="user_txtxdt" style="display:none;">
                1，成功推荐用户并完成注册即可获得5M流量奖励，推荐用户越多获取流量越多；<br/>
                2，推荐用户认证通过即可获得10元佣金奖励，最高奖励不封顶；<br/>
                
            </div>
        </div>
        <div class="user_boxb">
            <div class="disitem user_gzgz ">
                <div class="gzgz_left ">● 好友借款抽成</div>
                <div class="gzgz_rights"></div>
            </div>
            <div class="user_txtxdts" style="display:none;">
            	好友借款成功，大家都有分润提成哦。提成比例：5‰:3‰:1‰抽成。如，4级用户借款1000元，3级用户抽成5元，2级用户抽成3元，1级用户抽成1元。（好友级别关系可通过我的好友查看）

            </div>
        </div>
		<div class="user_boxb">
            <div class="disitem user_gzgz ">
                <div class="gzgz_left ">● 好友积分制度</div>
                <div class="gzgz_rightsss"></div>
            </div>
            <div class="user_txtxdtsss" style="display:none;">
                积分一路狂跟。推荐好友注册奖励1分；推荐的好友审核通过，奖励3分；好友借款成功，奖励10分；好友还款成功，奖励20分；好友投资成功，奖励10分。
            
            </div>
        </div>
        <div class="user_boxb">
            <div class="disitem user_gzgz ">
                <div class="gzgz_left ">● 好友投资制度</div>
                <div class="gzgz_rightss"></div>
            </div>
            <div class="user_txtxdtss" style="display:none;">
			好友投资成功后，即可获取5M流量
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
            'closeWindow',
            'hideOptionMenu'
        ]
    });

    wx.ready(function() {
        wx.hideOptionMenu();
    });
</script>