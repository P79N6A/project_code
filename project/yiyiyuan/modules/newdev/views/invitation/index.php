<div class="fInvitation">
    <div class="fInvfirst">
        <div class="first_one">
            <p>已有<?php if ($auth_count == 0): ?>0<?php else: ?><?php echo $auth_count['count']; ?><?php endif; ?></strong>名好友认证你，成功赚到 </p>
            <p class="first_one2"><em><?php echo ($auth_count['count'] * 66); ?>元</em>优惠券红包</p>
        </div>
        <div class="first_two">
            <div class="first_two1">
                <div class="first_two1_txt">
                    送你66元优惠券红包， <br/>可奖励成功认证你的好友!
                </div>
                <div class="first_two1_img"><img src="/images/account/66hb.png"></div>
            </div>
            <?php if ($userinfo->status == 3): ?>
                <button onclick="show()">邀请好友帮忙</button>
                <div id="overDiv" style="display:none;" onclick="closeDiv()"></div>
                <div id="diolo_warp" class="guide_img" style="display:none;" onclick="closeDiv()">  <img src="/images/guide.png"></div> 
            <?php else: ?>
                <button style="background: #aaa;">邀请好友帮忙</button>
                <div id="overDiv"></div>
                <div class="tanchuceng taff" id="pic_status" style="margin: 0;">
                    <p class="tchuc2" style="padding-top:15px; text-align:center; font-size:1.25rem;">由于您的持证自拍照尚未审核通过，<br/>暂不能邀请认证； </p>
                    <p class="tchuc4">
                        <a href="javascript:;" class="i_know_invitation">朕知道了</a>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="certification">
        <?php if (!empty($auth_list)): ?>
            <div class="cert_one">认证我的好友</div> 
            <?php foreach ($auth_list as $key => $value): ?>
                <div class="cert_two">
                    <img src="<?php if (!empty($value['head'])): ?><?php echo $value['head']; ?><?php else: ?><?php echo '/images/dev/face.png' ?><?php endif; ?>">
                    <div class="cert_two2"><p class="p1"><?php if (!empty($value['nickname'])): ?><?php echo mb_strwidth($value['nickname'], 'utf8') > 8 ? mb_strimwidth($value['nickname'], 0, 8, '..', 'utf-8') : $value['nickname']; ?><?php elseif (!empty($value['realname'])): ?><?php echo mb_strwidth($value['realname'], 'utf8') > 8 ? mb_strimwidth($value['realname'], 0, 8, '..', 'utf-8') : $value['realname']; ?><?php else: ?><?php echo '&nbsp;'; ?><?php endif; ?></p><p class="p2"><?php echo date('m' . '月' . 'd' . '日' . ' H:i', strtotime($value['create_time'])); ?></p></div>
                    <div class="cert_two3"><?php echo '+66'; ?>优惠券红包</div>
                    <!--<div class="cert_two4"><?php //echo intval($value['use_time']);      ?>s</div>-->
                </div>
            <?php endforeach; ?>
        <?php endif; ?>     
    </div>
    <div class="rules">
        <h3>认证规则</h3>
        <p>1.找好友答题，完成关系认证 </p>
        <p>2.认证成功，好友获得66元优惠券红包 </p>
    </div>
</div>
<script type="text/javascript">
    $(".i_know_invitation").click(function () {
        $("#overDiv").hide();
        $("#pic_status").hide();
    });

    function show() {
        document.getElementById("overDiv").style.display = "block";
        document.getElementById("diolo_warp").style.display = "block";
    }
    function closeDiv() {
        document.getElementById("overDiv").style.display = "none";
        document.getElementById("diolo_warp").style.display = "none";
    }
</script>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<?php if ($userinfo->status == 3): ?>
    <script>
        wx.config({
            debug: false,
            appId: "<?php echo $jsinfo['appid']; ?>",
            timestamp: "<?php echo $jsinfo['timestamp']; ?>",
            nonceStr: "<?php echo $jsinfo['nonceStr']; ?>",
            signature: "<?php echo $jsinfo['signature']; ?>",
            jsApiList: [
                'onMenuShareTimeline',
                'onMenuShareAppMessage',
                'showOptionMenu'
            ]
        });

        wx.ready(function () {
            wx.showOptionMenu();
            // 2. 分享接口
            // 2.1 监听“分享给朋友”，按钮点击、自定义分享内容及分享结果接口
            wx.onMenuShareAppMessage({
                title: '有人@你拆红包！',
                desc: '快来认证我，答题成功可获得66元大红包哦！',
                link: '<?php echo $shareUrl; ?>',
                imgUrl: "<?php echo empty($loanuserinfo['head']) ? '/images/dev/face.png' : $loanuserinfo['head']; ?>",
                trigger: function (res) {
                    // 不要尝试在trigger中使用ajax异步请求修改本次分享的内容，因为客户端分享操作是一个同步操作，这时候使用ajax的回包会还没有返回
                },
                success: function (res) {
                    countsharecount();
                },
                cancel: function (res) {
                },
                fail: function (res) {
                }
            });

            // 2.2 监听“分享到朋友圈”按钮点击、自定义分享内容及分享结果接口
            wx.onMenuShareTimeline({
                title: '吸金妖怪来啦！别跑！',
                desc: '赚钱新技能来袭，一步跨入豪门的机会，快来参加。',
                link: '<?php echo $shareUrl; ?>',
                imgUrl: "<?php echo empty($loanuserinfo['head']) ? '/images/dev/face.png' : $loanuserinfo['head']; ?>",
                trigger: function (res) {
                    // 不要尝试在trigger中使用ajax异步请求修改本次分享的内容，因为客户端分享操作是一个同步操作，这时候使用ajax的回包会还没有返回
                },
                success: function (res) {
                    countsharecount();
                },
                cancel: function (res) {
                },
                fail: function (res) {
                    alert(JSON.stringify(res));
                }
            });
        });
    </script>
<?php else: ?>
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
<?php endif; ?>
