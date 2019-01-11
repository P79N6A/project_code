<?php
if (!empty($userwx) && $userwx->head) {
    $head = $userwx->head;
} else {
    $head = "/images/webunion/nav_tx.png";
}
?>
<script>
    function show() {
        document.getElementById("overDiv").style.display = "block";
        document.getElementById("diolo_warp").style.display = "block";
    }
    function closeDiv() {
        document.getElementById("overDiv").style.display = "none";
        document.getElementById("diolo_warp").style.display = "none";
    }
</script>
<div class="wrap">
    <div class="zh_nav">
        <a href="/background/default/information"><div class="nav_left">
                <span><img src="<?php echo $head ?>"></span>
                <span class="pdbtm10">您好，<?php echo!empty($userwx) ? $userwx->nickname : $user->realname; ?>	</span>
            </div></a>
        <div class="nav_right">
            <div class="nav_oneul">
                <span class="nav_lest"><img src="/images/webunion/nav_list.png"></span>
                <ul id="list" style="display:none">
                    <a href="/background/default/spread"><li>我要推广</li></a>
                    <a href="/background/default/commission"><li>佣金介绍</li></a>
                    <a href="/background/default/question"><li>常见问题</li></a>
                    <a href="/background/default/contact"><li>联系我们</li></a>
                    <a href="/background/default/opinion"><li>意见反馈</li></a>

                    <a href="/dev/loan/index"><li class="return">先花一亿元》</li></a>
                </ul>
            </div>
            <!-- <span class="nav_qda">签到</span> -->
        </div>			
    </div>

    <div class="banner_yg">
        <img src="/images/webunion/banner.png?v=20160413001">
        <div class="disitem dottengg" id='txtMarquee-left'>
            <img src="/images/webunion/index_laba.png">
            <span style="  position: absolute;">播报</span>
            <div class='bd' style="border:0; color:#000; padding-left: 13%;s">
                <ul>
                    <?php if (!empty($bobao)): ?>
                        <?php foreach ($bobao as $key => $v): ?>
                            <li>恭喜<?php echo substr_replace($v->user->realname, '**', 3, 6); ?>(<?php echo substr_replace($v->user->mobile, '*', 3, 4); ?>)获得<?php echo number_format($v->amount, 2, ".", ""); ?>元收益</li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
        <script type="text/javascript">
            jQuery("#txtMarquee-left").slide({mainCell: ".bd ul", autoPlay: true, effect: "leftMarquee", vis: 2, interTime: 50});
        </script>
    </div>
<!--	<section class="borderbottom task">
            <div id="task" class="leftes" style="padding:10px 0;">
                    <div class="zhenghu_01"><img style="width: 32px;height: 35px;margin-left: 45%;" src="/images/webunion/zhenghu_01.png"></div>
                    <div class="firstnone" style="padding-top:5px;">赚钱任务</div>
            </div>
    </section>-->

    <section class="borderbottom">
        <div id="income" class="leftss">
            <div><span>¥ </span><em><?php echo sprintf("%.2f", $shouyitotal) ?></em></div>
            <div>昨日收益</div>
        </div>
        <div class="line"></div>
        <div id="wallet" class="leftss">
            <div class="zhenghu_01"><img src="/images/webunion/zhenghu_02.png"></div>
            <div>我的钱包</div>
        </div>
    </section>
    <section class="borderbottom" style="border-bottom:0;">
        <div id="friend" class="leftss">
            <div class="zhenghu_01"><img src="/images/webunion/zhenghu_03.png"></div>
            <div>好友</div>
        </div>
        <div class="line"></div>
        <div id="notice" class="leftss">
            <div class="zhenghu_01"><img src="/images/webunion/zhenghu_04.png"></div>
            <div>消息公告</div>

        </div>
    </section>
    <div class="onclickyq" onclick="show()">点我邀请：<?php echo $user->invite_code ?></div>
</div>
<!-- 透明遮挡层 -->
<div id="overDiv_n" style="<?php if ($show == 0): ?>display:none<?php else: ?>display:block;<?php endif; ?>"></div>
<div id="overDiv" style="<?php if ($show == 0): ?>display:none<?php else: ?>display:block;<?php endif; ?>" onclick="closeDiv()"></div>
<div id="diolo_warp" class="guide_img" style="<?php if ($show == 0): ?>display:none<?php else: ?>display:block;<?php endif; ?>" onclick="closeDiv()">
    <img src="/images/guide.png">
</div> 
<script>
    $(function () {
        $('.nav_lest').click(function () {
            $('#list').show();
            $('#overDiv_n').show();
        })
        $('#overDiv_n').click(function () {
            $('#list').hide();
            $('#overDiv_n').hide();
        })

        //添加页面链接
        $('#task').click(function () {
            window.location.href = "/background/task/index";
        })
        $('#friend').click(function () {
            window.location.href = "/background/friend/index";
        })
        $('#wallet').click(function () {
            window.location.href = "/background/wallet/index";
        })
        $('#income').click(function () {
            window.location.href = "/background/wallet/yestodayincome";
        })
        $('#notice').click(function () {
            window.location.href = "/background/notice/index";
        })

    })
</script>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
    wx.config({
        debug: false,
        appId: "<?php echo $jsinfo['appid']; ?>",
        timestamp: "<?php echo $jsinfo['timestamp']; ?>",
        nonceStr: "<?php echo $jsinfo['nonceStr']; ?>",
        signature: "<?php echo $jsinfo['signature']; ?>",
        jsApiList: [
//            'onMenuShareTimeline',
            'onMenuShareAppMessage',
            'showOptionMenu'
//            'hideOptionMenu'
        ]
    });
    wx.ready(function () {
        wx.showOptionMenu();
        // 2. 分享接口
        // 2.1 监听“分享给朋友”，按钮点击、自定义分享内容及分享结果接口
        wx.onMenuShareAppMessage({
            title: '吸金妖怪来啦！别跑！',
            desc: '赚钱新技能来袭，一步跨入豪门的机会，快来参加。',
            imgUrl: "<?php echo empty($userwx['head']) ? Yii::$app->request->hostInfo.'/images/dev/face.png' : $userwx['head']; ?>",
            link: '<?php echo $shareUrl; ?>',
            trigger: function (res) {
                // 不要尝试在trigger中使用ajax异步请求修改本次分享的内容，因为客户端分享操作是一个同步操作，这时候使用ajax的回包会还没有返回
            },
            success: function (res) {
                document.getElementById("overDiv").style.display = "none";
                document.getElementById("diolo_warp").style.display = "none";
            },
            cancel: function (res) {
            },
            fail: function (res) {
            }
        });
    });
</script>