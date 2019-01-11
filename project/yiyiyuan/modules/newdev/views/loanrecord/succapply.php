
<div class="Hcontainer">
    <img src="/images/title.png" width="100%"/>
    <div class="con">
        <div class="details">
            <p class="mb20 n30"><span class="icons redSucc"></span>提交成功，审核中</p>
            <p class="text-right cor n26 mb30">提交时间：<?php echo $loaninfo->last_modify_time; ?></p>
            <div class="border_bottom_1"></div>
            <div class="adver border_bottom_1 border_top_1">
                <div class="row mb30">
                    <div class="col-xs-4 cor n26">借款金额</div>
                    <div class="col-xs-8 text-right n26"><span class="red">&yen;<?php echo sprintf('%.2f', $loaninfo->amount); ?></span></div>
                </div>
                <div class="row mb30">
                    <div class="col-xs-5 cor n26">到账金额</div>
                    <div class="col-xs-7 text-right n26"><span class="red">&yen;<?php echo $loaninfo->is_calculation == 1 ? sprintf('%.2f', $loaninfo->amount - $loaninfo->withdraw_fee) : sprintf('%.2f', $loaninfo->amount); ?></span></div>
                </div>
                <div class="row mb30">
                    <div class="col-xs-4 cor n26">保险费</div>
                    <div class="col-xs-8 text-right n26"><span class="red">&yen;<?php echo $loaninfo->is_calculation == 1 ? sprintf('%.2f', $loaninfo->withdraw_fee) : sprintf('%.2f', $service_amount); ?></span></div>
                </div>
                <div class="row mb30">
                    <div class="col-xs-4 cor n26">利息</div>
                    <div class="col-xs-8 text-right n26"><span class="red">&yen;<?php echo $loaninfo->is_calculation == 1 ? sprintf('%.2f', $service_amount) : '0.00'; ?></span></div>
                </div>
                <div class="row mb30">
                    <div class="col-xs-5 cor n26">优惠券减免</div>
                    <div class="col-xs-7 text-right n26"><span class="red">
                        <?php if (empty($loan_coupon)): ?>&yen;0.00
                            <?php else: ?>
                                <?php if ($loan_coupon['val'] == 0): ?>
                                    <?php if (($loan_coupon->couponList['limit'] == 0) || ($loan_coupon->couponList['limit'] <= $loaninfo->current_amount)): ?>
                                        全免
                                    <?php else: ?>
                                        <?php echo sprintf('%.2f', $loaninfo->coupon_amount); ?>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <!--sss-->
                                    <?php if($loaninfo->coupon_amount >=  $loaninfo->interest_fee): ?>
                                        -&yen;<?php echo sprintf('%.2f', $loaninfo->interest_fee); ?>
                                    <?php else: ?>
                                        <!--eee-->
                                        -&yen;<?php echo sprintf('%.2f', $loaninfo->coupon_amount); ?>
                                        <!--sss-->
                                    <?php endif; ?>
                                    <!--eee-->
                                <?php endif; ?>
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
                <div class="row mb30">
                    <div class="col-xs-4 cor n26">到期应还</div>
                    <div class="col-xs-8 text-right n26"><span class="red">&yen;<?php echo sprintf('%.2f', $loaninfo['huankuan_amount']); ?></span></div>
                </div>
                <div class="row mb30">
                    <div class="col-xs-4 cor n26">借款期限</div>
                    <div class="col-xs-8 text-right n26"><span class="red"><?php echo $loaninfo->days; ?></span>天</div>
                </div>
            </div>
        </div>
        <img src="/images/bottom.png" width="100%" style="vertical-align:top"/>
        <!-- <div id="cancle_loan" style="color: #279cff;font-size: 15px;height: 2rem;font-weight: bold;width: 100%;text-align: right;padding-top: 7px;">取消借款》 </div> -->
        <div class="pTxt n26 cor mt40">
            <p>借款正在审核中，审核通过后我们会以最快的速度给您出款</p>
            <p>出款时间为每日18:00前</p>
            <p>如果审核通过，您当日未收到借款，我们会在次日给您出款</p>
        </div>
    </div>

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
            console.dir(data.ret);
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
</script>