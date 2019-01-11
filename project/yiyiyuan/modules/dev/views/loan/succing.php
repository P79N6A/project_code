<div class="Hcontainer nP">
    <div class="relative">
        <img src="/images/ck_bg.png" width="100%">
        <div class="green_bg">
            <!-- <img src="images/green_bg.png" width="100%"> -->
            <div class="cont green1 n30">
                <div class="col-xs-6 nPad">筹款中</div>
                <div class="col-xs-6 nPad text-right">剩余<?php echo $remaintime; ?>小时</div>
                <div class="col-xs-6 nPad mt2">借<span class="pink2"><?php echo sprintf("%.2f", $loaninfo['amount']); ?></span>点</div>
                <div class="col-xs-6 nPad mt2">
                    <div class="divout margin0 text-right">
                        <?php echo $loaninfo['desc']; ?>
                    </div>
                </div>
                <div class="clearfix"></div>
                <div class="proWrap2 mt2">
                    <progress max="<?php echo sprintf("%.2f", $loaninfo['amount']); ?>" value="<?php echo sprintf("%.2f", $loaninfo['current_amount']); ?>" style="width:100%" id="progress1"></progress>
                    <div class="probg"></div>
                    <span class="proBar" style="background: #f97a87;"></span>
                    <i id="proYuan3"></i>
                </div>
                <div class="col-xs-6 nPad mt2">已筹到<span class="pink2"><?php echo sprintf("%.2f", $loaninfo['current_amount']); ?></span>点</div>
                <div class="col-xs-6 nPad text-right mt2">还剩<?php echo number_format($loaninfo['amount'] - $loaninfo['current_amount'], 2, '.', '') ?>点</div>
            </div>
        </div>
    </div>
    <div class="main" style="position: absolute;top:0;left:0;width:100%;">
        <img src="<?php echo empty($userinfo['head']) ? '/images/dev/face.png' : $userinfo['head']; ?>" width="15%" class="mr2 borRad5">
        <span class="n30 white"><?php echo $userinfo['nickname']; ?></span>
    </div>
    <div class="main">
        <!--<a href="javascript:;" class="btnNew mt20 mb40" style="width:100%">分享</a>-->
        <?php if ($loaninfo['business_type'] == 1 || $loaninfo['business_type'] == 4): ?>
            <a href="<?php echo $shareurl; ?>"><button type="button" class="btn mb20" style="width:100%">分享</button></a>
            <div id="cancle_loan" style="color:#279cff;font-size:15px; float:right; height:2.5rem; font-weight:bold;">取消借款》 </div>
        <?php endif; ?>
        <img src="/images/bbb.png" width="100%" class="mb20">
        <p class="n26 grey2">1、找朋友投资自己；</p>
        <p class="n26 grey2">2、6小时内筹满，将自动打款到您的银行卡中；</p>
        <p class="n26 grey2">3、6小时后筹款结束，若未筹满，可在12小时内手动提现已筹金额。</p>
    </div>

    <?php if ($share_count == 0): ?>
        <input type="hidden" id="share_url" value="<?php echo $shareurl; ?>" />
        <div id="overDiv"  style="display:block;"></div>
        <div id="diolo_warp" class="diolo_warp hdtz">
            <p class="title_cz touzi">您的筹款需要找朋友投资哦！</p>
            <p class="pay_bank friend">快找朋友来帮忙吧～</p>
            <p class="radious_img tupiantz"></p>
            <p class="go_on emsx"><span>*如何筹款:</span>
                <span class="dontko">找朋友投资自己》6小时筹满自动到款》6小时未筹满，手动提现</span></p>
            <div class="true_flase">
                <button class="flase_qx" loan_id="<?php echo $loaninfo['loan_id']; ?>" user_id="<?php echo $loaninfo['user_id']; ?>" id="succing_cancle">取消</button>
                <button class="true_qr" loan_id="<?php echo $loaninfo['loan_id']; ?>" user_id="<?php echo $loaninfo['user_id']; ?>" id="succing_share">分享</button>
            </div>
        </div>  
    <?php endif; ?>
    <!-- 分享弹层 -->
    <!--<div class="Hmask" style="display:none;"></div>
    <img src="/images/guide.png" alt="" class="guide_share" style="display:none;">-->

    <div class="Hmask" style="display: none;"></div>
    <div class="layer_border overflow noBorder" style="display: none;">
        <p class="n28 padlr625" style="text-align:center;">确定取消借款吗？</p>
        <p class="n28 mb30 padlr625" style="text-align:center; color:#aaa; padding-top:10px;">已筹集<?php echo sprintf('%.2f', $loaninfo->current_amount); ?>元，借款总额<?php echo sprintf('%.2f', $loaninfo->amount); ?>元</p>
        <p class="n28 mb30 padlr625" id="cancle_error" style="text-align:center; color:#aaa;color:red;"></p>
        <div class="border_top_2 nPad overflow">
            <a href="javascript:;" id="close_cancle" class="n30 boder_right_1 text-center"><span class="grey2">取消</span></a>
            <a href="javascript:;" id="cancle_button" lid="<?php echo $loaninfo->loan_id; ?>" class="n30 red text-center"><span style="color:#e74747;">确定</span></a>
        </div>
    </div> 
</div>
<script>
    $(function () {
        var proValue = $('#progress1').attr('value');
        var proMax = $('#progress1').attr('max');
        var proPercent = (proValue / proMax) * 100;
        $('.Hcontainer .proWrap2 .proBar').css({'width': proPercent + '%'});
        $('#proYuan3').css({'left': (proPercent - 4) + '%'});

        if (proPercent <= 4) {
            $('#proYuan3').css('display', 'none');
        }

        $('.btnNew').click(function () {
            $('.Hmask').show();
            $('.guide_share').show();
        });
        $('.Hmask').click(function () {
            $('.Hmask').hide();
            $('.guide_share').hide();
        });

        $("#succing_cancle").click(function () {
            var loan_id = $(this).attr('loan_id');
            var user_id = $(this).attr('user_id');
            var share_url = $("#share_url").val();
            //alert(share_url);
            $.get("/dev/st/statisticssave", {loan_id: loan_id, user_id: user_id, type: 26}, function (data) {
                $("#overDiv").hide();
                $("#diolo_warp").hide();
            });
        });


        $("#succing_share").click(function () {
            var loan_id = $(this).attr('loan_id');
            var user_id = $(this).attr('user_id');
            var share_url = $("#share_url").val();
            $.get("/dev/st/statisticssave", {loan_id: loan_id, user_id: user_id, type: 25}, function (data) {
                window.location = share_url;
            });
        });

        $("#cancle_loan").click(function () {
            $(".Hmask").show();
            $(".layer_border").show();
        });

        $("#close_cancle").click(function () {
            $(".Hmask").hide();
            $(".layer_border").hide();
        });

        $("#cancle_button").click(function () {
            var loan_id = $(this).attr('lid');
            $("#cancle_button").attr('disabled', true);
            $.post("/dev/loan/cancleloan", {loan_id: loan_id}, function (result) {
                var data = eval("(" + result + ")");
                if (data.ret == '1') {
                    $("#cancle_error").html('参数错误');
                    $("#cancle_button").attr('disabled', false);
                    return false;
                } else if (data.ret == '2') {
                    $("#cancle_error").html('取消借款失败');
                    $("#cancle_button").attr('disabled', false);
                    return false;
                } else if (data.ret == '3') {
                    $("#cancle_error").html('暂时不能取消');
                    $("#cancle_button").attr('disabled', false);
                    return false;
                } else {
                    window.location = "/dev/loan/succ?l=" + loan_id;
                }
            });
        });

    });
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
            'hideOptionMenu'
        ]
    });

    wx.ready(function () {
        wx.hideOptionMenu();
    });
</script>