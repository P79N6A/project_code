<script type="text/javascript">
    (function () {
        _fmOpt = {
            partner: 'xianhuahua',
            appName: 'xianhh_web',
            token: '<?php echo $phpsessid; ?>',
        };
        var cimg = new Image(1, 1);
        cimg.onload = function () {
            _fmOpt.imgLoaded = true;
        };
        cimg.src = "https://fp.fraudmetrix.cn/fp/clear.png?partnerCode=xianhuahua&appName=xianhh_web&tokenId=" + _fmOpt.token;
        var fm = document.createElement('script');
        fm.type = 'text/javascript';
        fm.async = true;
        fm.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'static.fraudmetrix.cn/fm.js?ver=0.1&t=' + (new Date().getTime() / 3600000).toFixed(0);
        var s = document.getElementsByTagName('script')[0];
        s.parentNode.insertBefore(fm, s);
    })();
</script>
<div class="Hcontainer nP">
    <script  src='/dev/st/statisticssave?type=21'></script>
    <div class="relative">
        <img src="/images/ckfx_head.png" alt="" width="100%">
        <div class="time shareTime" endtime="<?php echo strtotime($loaninfo['open_end_date']); ?>">
            <span><?php echo $lefthour; ?></span>:<span><?php echo $leftminute; ?></span>:<span><?php echo $leftseconds; ?></span>
        </div>
    </div>
    <div class="main" style="position: absolute;top:0;left:0;width:100%;">
        <img src="<?php echo empty($loanuserinfo['head']) ? '/images/dev/face.png' : $loanuserinfo['head']; ?>" width="15%" class="mr2 borRad5">
        <span class="n30"><?php echo $loanuserinfo['nickname']; ?></span>
    </div>
    <div class="main">
        <div class="proWrap2 mb10">
            <progress max="<?php echo $loaninfo['amount']; ?>" value="<?php echo sprintf('%.2f', $loaninfo['current_amount']); ?>" style="width:100%" id="progress1"></progress>
            <div class="probg"></div>
            <span class="proBar"></span>
            <i id="proYuan3"></i>
        </div> 
        <div class="row n26">
            <div class="col-xs-6 white" style="padding-left: 4%">已筹集<?php echo sprintf('%.2f', $loaninfo['current_amount']); ?>点</div>
            <div class="col-xs-6 text-right white" style="padding-right: 4%">还剩<?php echo sprintf('%.2f', (floatval($loaninfo['amount']) - floatval($loaninfo['current_amount']))); ?>点</div>
        </div>
        <?php if ($loaninfo['user_id'] == $logininfo['user_id']): ?>
            <script src="/js/dev/shareWin.js?v=2015061511"></script>
            <button class="btnNew mt20 mb40"  onClick="shareTip();" style="width:100%">分享</button>
        <?php else: ?>
            <?php if ($loaninfo->status != 2): ?>
                <button class="btnNew mt20 mb40" id="loan_ing_stat_button" disabled style="width:100%"><?php if ($loaninfo['amount'] - $loaninfo['current_amount'] == 0): ?>已筹满 <?php else: ?>未筹满<?php endif; ?></button>
            <?php else: ?>
                <?php if ($loaninfo['open_end_date'] <= date('Y-m-d H:i:s')): ?>
                    <button id="loan_ing_stat_button" class="btnNew mt20 mb40" disabled>已过期</button>
                <?php else: ?>
                    <a href="/dev/invest/detail?loan_id=<?php echo $loaninfo->loan_id; ?>&atten=1" id="loan_ing_stat_button_todo" loan="<?php echo $loaninfo->loan_id; ?>" login="<?php echo $logininfo['id']; ?>" class="btnNew mt20 mb40">投资Ta</a>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>
        <input type="hidden" id="loan_id" value="<?php echo $loaninfo['loan_id']; ?>" />
        <input type="hidden" id="user_id" value="<?php echo $loaninfo['user_id']; ?>" />
            <div class="col-xs-12 nPad">
                <div class="col-xs-4 nPad">
                    <img src="/images/33333.png" width="100%" class="line3">
                </div>
                <div class="col-xs-4" style="padding: 0 7px;">
                    <?php if ($loaninfo['user_id'] == $logininfo['user_id']): ?>
                        <i class="share_line">谁投资了我</i>
                    <?php else: ?>
                        <i class="share_line">谁投资了Ta</i>
                    <?php endif; ?>
                </div>
                <div class="col-xs-4 nPad">
                    <img src="/images/44444.png" width="100%" class="line3">
                </div>
            </div>
            <div class="clearfix"></div>
            <?php if (!empty($investlist) || ($loaninfo->credit_amount > 0) ): ?>
            <ul class="shareUl">
            	<?php if($loaninfo->credit_amount > 0):?>
            	<li>
                        <img class="borRad50 float-left mr2" src="<?php echo empty($loanuserinfo['head']) ? '/images/dev/face.png' : $loanuserinfo['head']; ?>" width="46">
                        <div class="float-left">
                            <p class="blue1 n30 mt5"><?php if (!empty($loanuserinfo['nickname'])): ?><?php echo $loanuserinfo['nickname']; ?><?php else: ?><?php echo $loanuserinfo['realname']; ?><?php endif; ?></p>
                            <p class="blue2 n24"><?php echo date('m', strtotime($loaninfo['create_time'])); ?>月<?php echo date('d', strtotime($loaninfo['create_time'])); ?>日 <?php echo date('H:i', strtotime($loaninfo['create_time'])); ?></p>
                        </div>
                        <div class="float-right">
                            <span class="blue3 n30"><?php echo sprintf("%.2f", $loaninfo['credit_amount']); ?> 点</span>
                        </div>
                </li>
            	<?php endif;?>
                <?php foreach ($investlist as $key => $value): ?>
                    <li>
                        <img class="borRad50 float-left mr2" src="<?php echo empty($value['head']) ? '/images/dev/face.png' : $value['head']; ?>" width="46">
                        <div class="float-left">
                            <p class="blue1 n30 mt5"><?php if (!empty($value['nickname'])): ?><?php echo $value['nickname']; ?><?php else: ?><?php echo $value['realname']; ?><?php endif; ?></p>
                            <p class="blue2 n24"><?php echo date('m', strtotime($value['create_time'])); ?>月<?php echo date('d', strtotime($value['create_time'])); ?>日 <?php echo date('H:i', strtotime($value['create_time'])); ?></p>
                        </div>
                        <div class="float-right">
                            <span class="blue3 n30"><?php echo sprintf("%.2f", $value['amount']); ?> 点</span>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>
<script>
    setTimeout('lxfEndtime()', 1000);
</script>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script src="/js/zebra_dialog.js"></script>
<script>
    $(function () {
        var height = $('.line3').parent().height();
        $('.line3').parent().css('marginTop', (26 - height) / 2);
        var width = $('.shareTime').width();
        $('.shareTime').css('marginLeft', -width / 2 + 'px');

        var proValue = $('#progress1').attr('value');
        var proMax = $('#progress1').attr('max');
        var proPercent = (proValue / proMax) * 100;
        $('.Hcontainer .proWrap2 .proBar').animate({'width': proPercent + '%'});
        $('#proYuan3').animate({'left': (proPercent - 3) + '%'});



        if (proPercent <= 4) {
            $('#proYuan3').css('display', 'none');
        }

    });

    function countsharecount()
    {
        var loan_id = $("#loan_id").val();
        var user_id = $("#user_id").val();
        $.get("/dev/st/statisticssave", {loan_id: loan_id, user_id: user_id, type: 24}, function (data) {
            return true;
        });
    }

    wx.config({
        debug: false,
        appId: '<?php echo $jsinfo['appid']; ?>',
        timestamp: <?php echo $jsinfo['timestamp']; ?>,
        nonceStr: '<?php echo $jsinfo['nonceStr']; ?>',
        signature: '<?php echo $jsinfo['signature']; ?>',
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
            title: '<?php echo $template["title"]; ?>',
            desc: '<?php echo $template["desc"]; ?>',
            link: '<?php echo $shareUrl; ?>',
            imgUrl: '<?php echo empty($loanuserinfo['head']) ? '/images/dev/face.png' : $loanuserinfo['head']; ?>',
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
            title: '<?php echo $template["title"]; ?>',
            desc: '<?php echo $template["desc"]; ?>',
            link: '<?php echo $shareUrl; ?>',
            imgUrl: '<?php echo empty($loanuserinfo['head']) ? '/images/dev/face.png' : $loanuserinfo['head']; ?>',
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
