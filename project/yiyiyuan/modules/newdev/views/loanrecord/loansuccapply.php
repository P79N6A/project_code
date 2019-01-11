<style>
    .Hmask { width: 100%;height: 100%;background: rgba(0,0,0,.7); position: fixed;top: 0;left: 0; z-index: 100;}
    /*提额活动弹层*/
    .tancgg{ position: fixed; top:5%; z-index: 100; width:90%; margin:0 5%;}
    .tancgg img{width:100%;}
    .tancgg .tcerror{ position: absolute; top: 0;right: 5%; width: 10%;height: 3rem;}
    .tancgg .buttonbu{ position: absolute; bottom: 4%;left: 9%; width: 82%; height: 4rem; background: rgba(0,0,0,0);border: 0;}
    .tancgg .tanconess{ position: absolute; bottom: 9%;left: 10%; width: 80%;  height: 4rem; background: rgba(0,0,0,0);border: 0;}
    .indextc{position: fixed;top: 20%;left: 7%; border-radius: 5px; z-index: 1000; width: 86%;}
    .indextc button{ position: absolute; bottom: 5%; left:25%;width: 50%; background: rgba(0,0,0,0);}
    #overDivs{background: #000;width: 100%;height: 100%;left: 0;top: 0;filter: alpha(opacity=7);opacity: 0.7;z-index: 900;position: fixed!important; position: absolute;_top: expression(eval(document.compatMode &&document.compatMode=='CSS1Compat') ?documentElement.scrollTop + (document.documentElement.clientHeight-this.offsetHeight)/2 :/*IE6*/document.body.scrollTop + (document.body.clientHeight - this.clientHeight)/2);}
    /*已借款为认证弹框*/
    .duihsucc{width: 90%;position: fixed; top: 20%;left: 5%;border-radius: 5px; z-index: 100;background: #fff; color: #444;}
    .duihsucc button{ width: 80%; height: 40px; background: #c90000; color: #fff; font-size: 1.2rem;border-radius: 30px;}
    .duihsucc button.sureyemian{ margin: 15px 10% 20px;}
    .duihsucc p{ text-align: center; font-size: 1rem;}
    .duihsucc p.xuhua{ padding:30px 0 5px; font-size: 1.25rem;}
    .duihsucc p span{ color: #c90000;}
    .duihsucc.hkaapp button{ width: 40%; margin: 15px 0 15px 7%;  height: 35px; line-height: 33px; font-size: 1.1rem; border:1px solid #c90000;}
    .duihsucc.hkaapp button.xzai{ border:1px solid #c90000; color: #c90000; background: #fff;}
    .duihsucc .margbor{ margin-top: 10px; }
    .duihsucc .margbor .dbk_inpL{ border-bottom: 1px solid #e7edf0;}

</style>
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
        <img  style="width:30%;" src="/images/daihk4.png">
        <div class="imgimgnew"><img src="/images/daihaik3.png"></div>
        <div class="txtsty">
            <div >筹款已完成</div>
            <div class="bse">借款审核中</div>
            <div >等待打款</div>
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
                            全免<?php else: ?><?php echo sprintf('%.2f', $loaninfo->coupon_amount); ?><?php endif; ?>
                    <?php else: ?>
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
            <div class="corname">借款期限（天）</div>
            <div class="corliyou" ><?php echo $loaninfo->days; ?></div>
        </div>
    </div>
    <div class="marbot100"></div>
</div>

<div class="ydabaiye jk_item" hidden>
    <?php if ($exist == '1'): ?>
        <a href="/dev/loan/borrowing"><img src="/images/dbk.png"  style="width:70%;margin:25px 15% 0;"></a>
    <?php else: ?>
        <a href="/dev/loan/mdbk"><img src="/images/dbk.png"  style="width:70%;margin:25px 15% 0;"></a>
    <?php endif; ?>
</div>
<?php  //if ($is_show == 1): ?>
<!--<div class="Hmask Hmask_ok"></div>-->
<!--<div class="tancgg" style="top:25%;">
        <img src="/images/activity/tantan.png">
        <button class="tanconess" onclick="location.href='/dev/activity/upamount'"></button>
        <a class="tcerror"></a>
</div>-->
<?php  //endif; ?>


<?php if ($active_show == 1): ?>
    <div id="overDivs"></div>
    <div class="indextc">
        <img src="/images/activity/indextc.jpg">
        <button class="indextcbutton" onclick="location.href='/dev/activity/pinganactivity'">
            <img src="/images/activity/indextcbutton.png">
        </button>
    </div>
<?php endif; ?>


<!--申请借款但还未活体认证-->
<?php if($user_status != 3 && $loaninfo->status == 6): ?>
    <div class="Hmask Hmask_none"></div>
    <div class="duihsucc">
        <p class="xuhua">您的借款已通过审核！</p>
        <p>下载APP完成视频认证后立即领取借款</p>
        <button class="sureyemian" id = "loansuccapply_down">下载领取</button>
    </div>
<?php endif; ?>

<script type="text/javascript">
    $(function() {
        $('.Hmask_ok').click(function() {
            $('.Hmask_ok').hide();
            $('.tchucye').hide();
            $(".tancgg").hide();
        });
        $('.tcerror').click(function() {
            $(".Hmask_ok").hide();
            $(".tancgg").hide();
        });
        $('#overDivs').click(function () {
            $('#overDivs').hide();
            $('.indextc').hide();
        });
        $('.indextc').click(function () {
            $('#overDivs').hide();
            $('.indextc').hide();
        });
        //点击切换
        $('.nav_jk .item').each(function(index) {
            $(this).click(function() {
                $('.nav_jk .item').removeClass('on');
                $(this).addClass('on');
                $('.jk_item').css('display', 'none');
                $('.jk_item').eq(index).css('display', 'block');
            });
        });

        $('.onoffswitch-checkbox').click(function() {
            if ($('.onoffswitch-checkbox').prop('checked') == true) {
                //隔夜还
                setTimeout(function() {
                    $('#qx .dis_mask').css('display', 'block');
                }, 300);
                $('#qx').find('input').attr("disabled", true);
            } else {
                //期限
                setTimeout(function() {
                    $('#qx .dis_mask').css('display', 'none');
                }, 300);
                $('#qx').find('input').attr("disabled", false);
            }
        });

        $('.tchucye .yhqv').click(function() {
            $('.Hmask_ok').css('display', 'none');
            $('.tchucye').css('display', 'none');
        });



    })
</script>
<script>


    //点击下载app统计
    $('#loansuccapply_down').bind('click', function () {
        $.get("/wap/st/statisticssave", {type: 88}, function () {
            window.location = '/wap/st/down';
            return false;
        })
    })
</script>