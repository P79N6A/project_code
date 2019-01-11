<style>
    .y-pop-win{
        /*height: 8.8rem;*/
        width: 7.8rem;
        position: fixed;
        left: 50%;
        top: 50%;
        background: #fff;
        transform: translate(-50%,-50%);
        border-radius: 0.13rem;
    }
    .plan-tit{
        font-size: 0.4rem;
        font-family: "微软雅黑";
        font-size: 0.48rem;
        color: #444444;
        line-height: 0.48rem;
        margin-top:1.2rem;
        text-align: center;
        font-weight: bold;
    }
    .y-plan{
        width: 6.2rem;
        margin:0.8rem auto 0.4rem;
    }
    .plan-item{
        margin-bottom:0.4rem;
    }
    .plan-item:after{
        content: '';
        display: block;
        clear: both;
        overflow: hidden;
        height: 0;
    }
    .plan-item p{
        float: left;
        font-size: 0.36rem;
        color: #444;
        font-family: "微软雅黑";
    }
    .plan-item span{
        display: block;
        float: right;
        font-size: 0.36rem;
        color: #444;
        font-family: "微软雅黑";
    }
    .plan-button{
        width: 2.8rem;
        height: 0.88rem;
        background-image: linear-gradient(90deg, #F00D0D 0%, #FF4B17 100%);
        border-radius: 0.13rem;
        font-family: "微软雅黑";
        font-size: 0.43rem;
        line-height: 0.88rem;
        margin: 0.6rem auto;
        color:#fff;
        text-align: center;
    }
    .y-close-btn{
        width: 0.36rem;
        height: 0.36rem;
        position: absolute;
        right: 0.3rem;
        top: 0.3rem;
    }
    .y-popup {
        height: 100%;
        width: 100%;
        background: #000;
        opacity: .5;
        position: fixed;
        left: 0;
        top: 0;
    }
</style>
<div class="payout_list">
    <div class="list_top">
        <p class="list_title">最多可借<?php echo $can_max_money ?>元</p>
        <?php if (!$can_input_amount): ?>
            <input type="text" name="amount" value="<?php echo $amount; ?>" placeholder="请输入500的整数倍" onkeyup="value=value.replace(/[^\d]/g,'')" readonly="readonly" class="write_count">
            <span class="cashIcon">￥</span>
        <?php else: ?>
            <?php if (!empty($amount)): ?>
                <input type="text" name="amount" value="<?php echo $amount; ?>" placeholder="请输入500的整数倍" onkeyup="value=value.replace(/[^\d]/g,'')" class="write_count" id="amount">
            <?php else: ?>
                <input type="text" name="amount" placeholder="请输入500的整数倍" onkeyup="value=value.replace(/[^\d]/g,'')" class="write_count" id="amount">
            <?php endif; ?>
            <span class="cashIcon">￥</span>
            <img src="/borrow/310/images/clearIcon.png" class="clearIcon" id="cancel_amount">
        <?php endif; ?>
        <!--  验证-->
        <span class="reg_text reg_display" id="tip_amount">*借款金额应该为500的整数倍</span>
        <span class="hengxian" style="height: 1px;opacity: .6;"></span>
        <p class="count_date">额度有效期至<?php echo date('Y-m-d H:i', strtotime($invalid_time)) ?>，剩余<span class="red"><?php echo $time_hours ?></span>小时</p>
    </div>
    <div class="item_box">
        <img src="/borrow/310/images/payoutDate.png" alt="" class="item_payoutDate">
        <span class="item_left" >借多久</span>
        <img src="/borrow/310/images/arrow2.png" alt="" class="arrow_right" style="margin-right: -0.3rem;">
        <span class="item_right" id="qixian"><?php echo $days; ?>天x<?php echo $period; ?>期</span>
    </div>
    <div class="item_box">
        <img src="/borrow/310/images/use.png" alt="" class="item_payoutDate">
        <span class="item_left">怎么用</span>
        <img src="/borrow/310/images/arrow2.png" alt="" class="arrow_right" style="margin-right: -0.3rem;">
        <?php if (!empty($desc)): ?>
            <span class="item_right" id="desc"><?php echo $desc; ?></span>
        <?php else: ?>
            <span class="item_right" id="desc">消费</span>
        <?php endif; ?>
    </div>
    <div class="item_box">
        <img src="/borrow/310/images/paybackIcon.png" alt="" class="item_payoutDate">
        <span class="item_left">还款计划</span>
        <?php if ($is_installment): ?>
            <img src="/borrow/310/images/arrow2.png" alt="" class="arrow_right" style="margin-right: -0.3rem;" onclick="openRepayPlan()">
            <span class="item_right" id="repay_money">首期应还 <?php echo $repay_plan[0]['repay_amount']; ?>元
                <span id="repay_date" class="item_right_item" style="color:#999 !important;">(<?php echo date('m月d日', strtotime($repay_plan[0]['repay_date'])); ?>还)
            </span>
        <?php else: ?>
            <span class="item_right" id="repay_money">应还金额 <?php echo $repay_plan[0]['repay_amount']; ?>
                <span id="repay_date" class="item_right_item" style="color:#999 !important;">(<?php echo date('m月d日', strtotime($repay_plan[0]['repay_date'])); ?>还)
            </span>
        <?php endif; ?>
        </span>
    </div>
    <div class="item_box">
        <img src="/borrow/310/images/bankCard.png" alt="" class="item_payoutDate">
        <span class="item_left">收款卡</span>
        <span class="item_right"><?php echo empty($bank['type']) ? '银行卡' : $bank['type']; ?><?php echo !empty($bank['card']) ? '(' . $bank['card'] . ')' : ''; ?></span>
    </div>
    <div class="item_box" style="border-bottom:none;">
        <img src="/borrow/310/images/coupon.png" alt="" class="item_payoutDate">
        <span class="item_left">优惠券</span>
        <img src="/borrow/310/images/arrow2.png" alt="" class="arrow_right" style="margin-right: -0.3rem;">
        <?php if ($coupon_count == 0): ?>
            <span class="item_right font_color">暂无可用优惠券</span>
        <?php else: ?>
<!--            <img src="/borrow/310/images/arrow2.png" id="choose_coupon" alt="" class="arrow_right coupon_click">-->
            <?php if (!empty($coupon_amount)): ?>
                <span class="item_right font_color coupon_click" id="choose_coupon "
                      style="color:#F33232 !important;">-
                    <?php if (\app\commonapi\Keywords::inspectOpen() == 2): ?>
                        <?php echo ($surplus_fee <= $coupon_amount) ? $surplus_fee : $coupon_amount; ?>
                    <?php else: ?>
                        <?php echo ($interest_fee <= $coupon_amount) ? $interest_fee : $coupon_amount; ?>
                    <?php endif; ?>
                </span>
            <?php else: ?>
                <span class="item_right font_color coupon_click" id="choose_coupon "
                      style="color:#F33232 !important;"><?php echo $coupon_count; ?>个优惠券可用</span>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
<div class="agree_item">
    <!-- target -->
    <?php if (!empty($agreement)): ?>
        <img src="/borrow/310/images/target.png" alt="" class="check_target" id="agreement_choose">
    <?php else: ?>
        <img src="/borrow/310/images/ontarget.png" alt="" class="check_target" id="agreement_choose">
    <?php endif; ?>
    <p class="target_txt">勾选即代表阅读并同意<a href="/borrow/agreeloan/contactlist" class="item_clause">《居间服务及借款协议（四方）》</a>
    </p>
</div>
<div class="paycount_btn" onclick="get_loaning()" id="loaning">立即借款</div>
<div class="w_borrowText" style="
font-size: 0.32rem;line-height:0.43rem;margin:0.4rem 0.3rem 0 0.4rem;;font-family: 微软雅黑;color:#999;">
    *请填写真实的借款用途，平台将对您的借款用途进行验证，若用
    途变更，我们将对您该笔借款启动贷中预警流程，提前终止借款，
    相关责任由客户本人承担，如有疑问请咨询先花一亿元客服。
</div>
<div class="help_service" style="position: initial;width: 100%;margin-top: 1rem;height: 0.37rem;text-align:center;bottom: 0;">
    <img src="/borrow/310/images/tip.png" alt="" class="contact_service_tip" style="top: auto">
    <a href="javascript:void(0);" onclick="doHelp('/borrow/helpcenter/list?position=11&user_id=<?php echo $user_id; ?>')"><span class="contact_service_text">获取帮助</span></a>
</div>
<!--  弹窗-->
<div class="poppayout_mask" id="reback1" style="display:none;"></div>
<div class="mask_box maskDis" id="reback2" hidden>
    <img src="/borrow/310/images/bill-close.png" alt="" class="close_mask" onclick="cancel_img()">
    <p class="mask_title">温馨提示</p>
    <p class="poppayout_text">还未完成借款，请三思而后行</p>
    <span class="resure_btn" onclick="confirm()">确认</span>
    <span class="resureOut_btn" onclick="cancel()">继续借款</span>
</div>

<!--弹窗1218-->
<div class="y-popup" style="display:none;"></div>
<div class="y-pop-win" style="display: none;">
    <img src="/borrow/310/images/bill-close.png" alt="" class="y-close-btn" onclick="closeRepayPlan()">
    <h3 class="plan-tit">还款计划</h3>
    <div class="y-plan">
        <?php foreach ($repay_plan as $val): ?>
            <div class="plan-item">
                <p><?php echo $val['repay_date']; ?></p>
                <span><?php echo $val['repay_amount']; ?>元</span>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="plan-button" onclick="closeRepayPlan()">确认</div>
</div>
<script src="/290/js/jquery-1.10.1.min.js"></script>
<script src="/borrow/310/js/renzheng.js"></script>
<script src="/borrow/310/js/picker.js"></script>
<script>

    var csrf = '<?php echo $csrf;?>';
    <?php \app\common\PLogger::getInstance('weixin', '', $user_id); ?>
    <?php $json_data = \app\common\PLogger::getJson();?>
    var baseInfoss = eval('(' + '<?php echo $json_data; ?>' + ')');
    $.scrEvent({
        data: ['购买设备', '购买家具或家电', '购买服饰', '购买生活用品', '购买电子产品', '购买食品', '消费'],   // 数据
        //data: desc_lists,   // 数据
        evEle: '#desc',            // 选择器
        title: '怎么用',            // 标题
        defValue: '购买设备',             // 默认值
        afterAction: function (data) {   //  点击确定按钮后,执行的动作
            tongji('zenmyong', baseInfoss);
            $('#desc').val(data);
            $.ajax({
                url: "/borrow/loan/setdesc",
                type: 'post',
                async: false,
                data: {desc: data, _csrf: csrf},
                success: function (json) {
                    json = eval('(' + json + ')');
                    console.log(json);
//                    if ( json.rsp_code == '0000' ) { //金额输入无误，更新还款计划
//
//                    }
                },
                error: function (json) {
                    can_loan = 2;
                    alert('请十分钟后发起借款');
                }
            });
        }
    });
</script>
<script>
    //var amount;
    var amount = '<?php echo $amount;?>';
    var user_id = '<?php echo $user_id;?>';
    var csrf = '<?php echo $csrf;?>';
    var coupon_id = '<?php echo $coupon_id;?>';
    var agreement = '<?php echo $agreement;?>';
    var days = '<?php echo $days;?>';
    var desc = '<?php echo $desc;?>';
    var can_max_money = '<?php echo $can_max_money;?>';
    var agreement_img;
    var agreement_img_path;
    var can_loan = 1;
    var isIphone = navigator.appVersion.match(/iphone/gi);
    if (isIphone) {
        $('.cashIcon').css({
            top: '1.80rem'
        })
    }


    $(function () {

        //隐藏金额提示
        cancel_amount();

        //输入金额校验
        choose_amount();

        //选择优惠券
        choose_coupon();

        //选择是否同意协议
        agreement_choose();

        //重写返回按钮
        pushHistory();
        var bool = false;
        setTimeout(function () {
            bool = true;
        }, 1500);
        window.addEventListener("popstate", function (e) {
            tongji('loan_write_list_reback_btn', baseInfoss);
            if (bool) {
                //根据自己的需求返回到不同页面
                $('#reback1').show();
                $('#reback2').show();
            }
            pushHistory();
        }, false);

        function pushHistory() {
            var state = {
                url: "#"
            };
            window.history.pushState(state, "#");
        }


    });

    //确认回退
    function confirm() {
        tongji('confirm_reback_loan', baseInfoss);
        setTimeout(function () {
            window.location.href = '/borrow/loan';
        }, 100);
    }

    //取消回退
    function cancel() {
        tongji('cancel_reback_loan', baseInfoss);
        $('#reback1').hide();
        $('#reback2').hide();

    }

    //取消回退
    function cancel_img() {
        tongji('cancel_reback_loan_img', baseInfoss);
        $('#reback1').hide();
        $('#reback2').hide();

    }

    //取消输入的金额
    function cancel_amount() {
        $('#cancel_amount').click(function () {
            //取消input输入框里的值
            $('input[name="amount"]').val('');
            $('#tip_amount').hide();
        });
    }

    //输入完金额之后的金额判断和还款计划
    function choose_amount() {
        tongji('choose_input_amount', baseInfoss);
        $('#amount').bind({
            blur: function () { //输入框失去焦点时
                console.log('失去焦点');
                amount = $('input[name="amount"]').val();
                desc = $('#desc').val();
                if (amount != '') {
                    $.ajax({
                        url: "/borrow/loan/amountjudge",
                        type: 'post',
                        async: false,
                        data: {
                            user_id: user_id,
                            _csrf: csrf,
                            amount: amount,
                            days: days,
                            coupon_id: coupon_id,
                            desc: desc
                        },
                        success: function (json) {
                            json = eval('(' + json + ')');
                            console.log(json);
                            if (json.rsp_code == '0000') { //金额输入无误，更新还款计划
                                can_loan = 1;
                                $('#repay_money').html('¥' + json.rsp_data.repay_amount);
                                $('#repay_date').html('(' + json.rsp_data.repay_date + '还)');
                                $('input[name="amount"]').val(json.rsp_data.amount);
                                $('#tip_amount').hide();

                            } else if (json.rsp_code == '2000') { //金额输入有误，只更新金额提醒
                                can_loan = 2;
                                $('#tip_amount').show();
                                $('#tip_amount').html(json.rsp_msg);

                            } else {
                                can_loan = 2;
                                alert(json.rsp_msg);
                            }
                        },
                        error: function (json) {
                            can_loan = 2;
                            alert('请十分钟后发起借款');
                        }
                    });
                }
            }
        });
    }


    //选择优惠券
    function choose_coupon() {
        $('.coupon_click').bind('click', function () {
            tongji('start_loan_coupon_choose', baseInfoss);
            setTimeout(function () {
                window.location.href = '/borrow/loan/getloancoupon?user_id=' + user_id + '&coupon_id=' + coupon_id;
            }, 100);

        });
    }

    //选择是否同意协议
    function agreement_choose() {
        $('#agreement_choose').bind('click', function () {
            tongji('start_loan_agreement_choose', baseInfoss);
            agreement_img = $('#agreement_choose')[0].src;
            agreement_img_path = agreement_img.substring(agreement_img.lastIndexOf("/") + 1);
            if (agreement_img_path === 'ontarget.png') { //未选
                $("#agreement_choose").attr('src', '/borrow/310/images/target.png');
                setTimeout(function () {
                    window.location.href = '/borrow/loan/startloan?agreement=1';
                }, 100);

            } else if (agreement_img_path === 'target.png') { //已选
                $("#agreement_choose").attr('src', '/borrow/310/images/ontarget.png');
                setTimeout(function () {
                    window.location.href = '/borrow/loan/startloan?agreement=0';
                }, 100);

            } else {
                $("#agreement_choose").attr('src', '/borrow/310/images/target.png');
                setTimeout(function () {
                    window.location.href = '/borrow/loan/startloan?agreement=1';
                }, 100);
            }
        });

    }

    //发起借款
    function get_loaning() {
        tongji('start_loan', baseInfoss);
        zhuge.track('信用借款-立即借款');
        if (agreement != 1) {
            alert('请阅读并同意《居间服务及借款协议（四方）》');
        } else if (can_loan == 2) { //金额有误
            $("#loaning").attr("disabled", true);
            //alert('输入金额有误，请重新填写');
        } else {
            desc = $('#desc').val();
            setTimeout(function () {
                window.location = '/borrow/loan/confirmloan?desc=' + desc;
            }, 1000);

        }
    }

    function doHelp(url) {
        tongji('do_help', baseInfoss);
        setTimeout(function () {
            window.location.href = url;
        }, 100);
    }
    
    function openRepayPlan() {
        $('.y-popup').show();
        $('.y-pop-win').show();
    }
    
    function closeRepayPlan() {
        $('.y-popup').hide();
        $('.y-pop-win').hide();
    }
    var qixian_day = '<?php echo $days?>';
    var qixian_period = '<?php echo $period?>';
    var qixian_data = qixian_day+'天x'+qixian_period+'期';
    $.scrEvent({
        data: [qixian_data],   // 数据
        //data: desc_lists,   // 数据
        evEle: '#qixian',            // 选择器
        title: '选择期限',            // 标题
        defValue: qixian_data,             // 默认值
        afterAction: function(data) { 
            console.log(data)//  点击确定按钮后,执行的动作
//             $('#qixian').html(data);
          
        }
    });
</script>
