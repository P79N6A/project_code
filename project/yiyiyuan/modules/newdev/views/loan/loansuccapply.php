<div class="zuoyminew jk_item">
    <div class="amountyem">
        <span style="">通过APP借款额度更高哦～</span>
        <a href="/dev/ds/down">
            <button style="">去下载</button>
        </a>
    </div>
    <div class="shezhiminay">
        <?php if ($loanstatus == 5){ ?>
            <img  style="width:30%;" src="/images/daihk4.png">
            <div class="imgimgnew"><img src="/images/daihaik3.png"></div>
        <?php }elseif($loanstatus == 6){ ?>
            <img  style="width:30%;" src="/images/daihk2.png">
            <div class="imgimgnew"><img src="/images/daihaik1.png"></div>
        <?php } ?>
        <div class="txtsty">
            <div >筹款已完成</div>
            <?php if ($loanstatus == 5){ ?>
                <div class="bse">借款审核中</div>
                <div>等待打款</div>
            <?php }elseif($loanstatus == 6){ ?>
                <div>审核已通过</div>
                <div class="bse">等待打款</div>
            <?php } ?>
        </div>
    </div>
    <div class="daihukan_cont">
        <div class="daoqihk">到期应还（元） <span><?php echo sprintf('%.2f', $loaninfo['huankuan_amount']); ?></span></div>
        <div class="rowym">
            <div class="corname">借款金额（元）</div>
            <div class="corliyou" ><?php echo sprintf('%.2f', $loaninfo->amount); ?></div>
        </div>
        <div class="rowym">
            <div class="corname">到账金额（元）</div>
            <div class="corliyou"><?php echo $loaninfo->is_calculation == 1 ? sprintf('%.2f', $loaninfo->amount - $loaninfo->withdraw_fee) : sprintf('%.2f', $loaninfo->amount); ?></div>
        </div>
        <div class="rowym">
            <div class="corname">保险费（元）  </div>
            <div class="corliyou" ><?php echo sprintf('%.2f', $loaninfo->withdraw_fee) ?></div>
        </div>
        <div class="rowym">
            <div class="corname">利息（元）</div>
            <div class="corliyou" ><?php echo sprintf('%.2f', $loaninfo->interest_fee); ?></div>
        </div>
        <div class="rowym">
            <div class="corname">点赞减息（元）</div>
            <div class="corliyou" ><?php if ($loaninfo->like_amount > 0): ?>-<?php echo sprintf('%.2f', $loaninfo->like_amount); ?><?php else: ?>0.00<?php endif; ?></div>
        </div>
        <div class="rowym">
            <div class="corname">优惠券减免（元）</div>
            <div class="corliyou" >
                <?php if (empty($loan_coupon)): ?>0.00<?php else: ?>
                    <?php if ($loan_coupon->couponList->val == 0): ?>
                        <?php if (($loan_coupon->couponList['limit'] == 0) || ($loan_coupon->couponList['limit'] <= $loaninfo->current_amount)): ?>
                            全免<?php else: ?><?php echo sprintf('%.2f', $loaninfo->coupon_amount); ?><?php endif; ?>
                    <?php else: ?>
                        <?php if($loaninfo->coupon_amount >=  $loaninfo->interest_fee): ?>
                            -<?php echo sprintf('%.2f', $loaninfo->interest_fee); ?>
                        <?php else: ?>
                            -<?php echo sprintf('%.2f', $loaninfo->coupon_amount); ?>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="rowym">
            <div class="corname">借款期限（天）</div>
            <div class="corliyou" ><?php echo $loaninfo->days; ?></div>
        </div>
    </div>
    <div class="marbot100"></div>
</div>
<?= $this->render('/layouts/_page', ['page' => 'loan']) ?>

<!--申请借款但还未活体认证-->
<?php if($user->status != 3 && $loanstatus == 6): ?>
    <div class="Hmask Hmask_none"></div>
    <div class="duihsucc">
        <p class="xuhua">您的借款已通过审核！</p>
        <p>下载APP完成视频认证后立即领取借款</p>
        <button class="sureyemian" id = "loansuccapply_down">下载领取</button>
    </div>
<?php endif; ?>


<div class="Hmask Hmask_none" style="display: none"></div>
<div class="duihsucc" style="width:80%; left:10%;display: none">
        <h3>设置支付密码</h3>
        <p class="xuhua">通过江西银行提现需要支付密码，</p>
        <p>请前往江西银行设置支付密码。</p>
        <button  style="width:90%; height: auto; border-top:1px solid #c2c2c2; background:#fff; margin: 1rem 5%; color: #e74747;border-radius: 0; padding: 1rem 0 0;" id = "set_password">确定</button>
<!--            <h3>设置失败</h3>-->
<!--            <button  style="width:90%; height: auto; border-top:1px solid #c2c2c2; background:#fff; margin: 1rem 5%; color: #e74747;border-radius: 0; padding: 1rem 0 0;" id = "get_money">已为您切换优先出款通道</button>-->
</div>
<!--<div class="duihsucc" style="width:55%;left:22%;top:30%;display: none ">-->
<!--    <p style="padding: 1rem;font-size: 1.2rem;display: none">正在跳转至提现页面...</p>-->
<!--</div>-->
<div id="forms"></div>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
    //设置密码
    $("#set_password").click(function () {
        var csrf = '<?php echo $csrf; ?>';
        $.post("/new/loan/pwdset", {_csrf: csrf}, function(result) {
            var data = eval("(" + result + ")");
            if(data.ret != '0'){
                alert(data.msg);
            }
            $("#forms").html(data.data);
        });
    });

    //领取借款
    $(".qinlingqv").click(function () {
        var csrf = '<?php echo $csrf; ?>';
        var loan_id = '<?php echo $loaninfo->loan_id; ?>';
        $.post("/new/loan/getmoney", {_csrf: csrf, loan_id:loan_id}, function(result) {
            var data = eval("(" + result + ")");
            if(data.ret == '0'){
                $("#forms").html(data.data);
            }else if (data.ret == '4'){//未设置密码
                $(".Hmask").show();
                $(".duihsucc").show();
                return false;
            }else{
                alert(data.data); return false;
            }
        });
    });


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
    //点击下载app统计
    $('#loansuccapply_down').bind('click', function () {
        $.get("/wap/st/statisticssave", {type: 88}, function () {
            window.location = '/wap/st/down';
            return false;
        })
    })
</script>