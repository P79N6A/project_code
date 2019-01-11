<script type="text/javascript">
    $(function () {
        //点击切换
        $('.nav_jk .item').each(function (index) {
            $(this).click(function () {
                $('.nav_jk .item').removeClass('on');
                $(this).addClass('on');
                $('.jk_item').css('display', 'none');
                $('.jk_item').eq(index).css('display', 'block');
            });
        });

        $('.onoffswitch-checkbox').click(function () {
            if ($('.onoffswitch-checkbox').prop('checked') == true) {
                //隔夜还
                setTimeout(function () {
                    $('#qx .dis_mask').css('display', 'block');
                }, 300);
                $('#qx').find('input').attr("disabled", true);
            } else {
                //期限
                setTimeout(function () {
                    $('#qx .dis_mask').css('display', 'none');
                }, 300);
                $('#qx').find('input').attr("disabled", false);
            }
        });

        $('.tchucye .yhqv').click(function () {
            $('.Hmask').css('display', 'none');
            $('.tchucye').css('display', 'none');
        });
    })
</script>
<!--
<div class="allnewjkuan">
    <img src="/images/banner2.png" width="100%">
    <div class="nav_jk">
        <div class="item on">好友借款</div>
        <div class="item ">担保借款</div>
    </div>
</div>
-->
<div class="zuoyminew jk_item">
    <div class="amountyem">
        <span style="">通过APP借款额度更高哦～</span>
        <a href="/dev/ds/down">
            <button style="">去下载</button>
        </a>
    </div>
    <div class="shezhiminay">
        <img  style="width:37%;" src="/images/daihk3.png">
        <div class="imgimgnew"><img src="/images/daihaik3.png"></div>
        <div class="txtsty">
            <div >待还款</div>
            <div class="bse">还款确认中</div>
            <div >还款成功</div>
        </div>
    </div>
    <div class="daihukan_cont">
        <div class="daoqihk">到期应还（元） <span><?php echo sprintf('%.2f', $loaninfo->huankuan_amount); ?></span></div>
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
            <div class="corliyou" ><?php echo $loaninfo->is_calculation == 1 ? sprintf('%.2f', $loaninfo->withdraw_fee) : sprintf('%.2f', $service_amount); ?></div>
        </div>
        <div class="rowym">
            <div class="corname">利息（元）</div>
            <div class="corliyou" ><?php echo $loaninfo->is_calculation == 1 ? sprintf('%.2f', $service_amount) : '0.00'; ?></div>
        </div>
        <div class="rowym">
            <div class="corname">优惠券减免（元）</div>
            <div class="corliyou" >
                <?php if (empty($loan_coupon)): ?>0.00<?php else: ?>
                    <?php if ($loan_coupon['val'] == 0): ?>
                        <?php if (($loan_coupon->couponList['limit'] == 0) || ($loan_coupon->couponList['limit'] <= $loaninfo->current_amount)): ?>
                            全免<?php else: ?>
                            <?php echo sprintf('%.2f', $loaninfo->coupon_amount); ?>
                        <?php endif; ?><?php else: ?>
                        <!--sss-->
                        <?php if($loaninfo->coupon_amount >=  $loaninfo->interest_fee): ?>
                            -<?php echo sprintf('%.2f', $loaninfo->interest_fee); ?>
                        <?php else: ?>

                            <!--eee-->
                            -<?php echo sprintf('%.2f', $loaninfo->coupon_amount); ?>
                            <!--sss-->
                        <?php endif; ?>
                        <!--eee-->
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="rowym">
            <div class="corname">点赞减息（元）</div>
            <div class="corliyou" ><?php if ($loaninfo->like_amount > 0): ?>-<?php echo sprintf('%.2f', $loaninfo->like_amount); ?><?php else: ?>0.00<?php endif; ?></div>
        </div>
        <div class="rowym">
            <div class="corname">借款期限（天）</div>
            <div class="corliyou" ><?php echo $loaninfo->days; ?></div>
        </div>
        <div class="rowym">
            <div class="corname">最后还款日</div>
            <div class="corliyou" ><?php if (!empty($loaninfo->end_date)): ?><?php echo date('Y-m-d', (strtotime($loaninfo->end_date) - 24 * 3600)); ?><?php else: ?>以短信推送时间为准<?php endif; ?></div>
        </div>
    </div>
    <!--<a href="/dev/repay/cards?loan_id=<?php echo $loaninfo['loan_id']; ?>"><button type="button" class="bgrey" >我要还款</button></a>-->
    <div class="marbot100"></div>
</div>

<!--申请借款但还未活体认证@TODO 更换成去下载APP的弹框-->
<?php if($user_status != 3 && $loaninfo->status == 6): ?>
    <div class="Hmask Hmask_none" ></div>
    <div class="duihsucc">
        <p class="xuhua">您的借款已通过审核！</p>
        <p>完成视频认证后立即领取借款</p>
        <button class="sureyemian" id = "loansuccok_down">立即领取</button>
    </div>
<?php endif; ?>
<div class="ydabaiye jk_item" hidden>
    <?php if ($exist == '1'): ?>
        <a href="/dev/loan/borrowing"><img src="/images/dbk.png"  style="width:70%;margin:25px 15% 0;"></a>
    <?php else: ?>
        <a href="/dev/loan/mdbk"><img src="/images/dbk.png"  style="width:70%;margin:25px 15% 0;"></a>
    <?php endif; ?>
</div>
