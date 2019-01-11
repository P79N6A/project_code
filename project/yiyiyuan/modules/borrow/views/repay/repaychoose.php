<style>
    .y-card-box{
        position: relative;
        height:1rem;
        overflow: hidden;
        border-bottom: 0.03rem solid #f3f3f3;
    }
    .y-change-card:after{
        content: '';
        display: block;
        clear: both;
        overflow: hidden;
        height: 0;

    }
    .y-change-card p{
        font-size: 0.32rem;
        line-height: 1rem;
        color: #666;
        float: left;
    }
    .y-change-card img{
        display: block;
        float: right;
        width: 0.27rem;
        margin-top: 0.24rem;
    }
    .y-triangle{
        background: url("/borrow/370/images/arrow.png") no-repeat;
        background-position: 0 0;
        display: block;
        height: 0.3rem;
        margin-top: -0.2rem;
    }
    .immediately{
        border: none;
        outline: none;
    }
    .help_service{
        position: static;
        margin:0.2rem auto 0.4rem;
    }
    .y-box{
        width:100%;
        height:100%;
        overflow: hidden;
        overflow-y: auto;
    }
</style>
<?php

function getImageUrl($abbr) {
    $bankAbbr = [
        'ABC',
        'BCCB',
        'BCM',
        'BOC',
        'CCB',
        'CEB',
        'CIB',
        'CMB',
        'CMBC',
        'ECITIC',
        'GDB',
        'HXB',
        'ICBC',
        'PAB',
        'PSBC',
        'SPDB'
    ];
    if (!empty($abbr) && in_array($abbr, $bankAbbr)) {
        $abbr_url = $abbr;
    } else {
        $abbr_url = 'ICON';
    }
    return '/images/bank_logo/' . $abbr_url . '.png';
}
?>
<div class="y-box">
<div class="refund_sure">
    <div class="sure_lrht">
        <div class="lrtcont">
            <p class="left">本期待还</p>
            <p class="right">¥<?php echo sprintf('%.2f', $total_amoun); ?></p>
        </div>
        <div class="lrtcont">
            <p class="left">优惠券</p>
            <a href="javascript:void(0);" <?php if ($coupon_count > 0): ?>onclick="couponList('/new/repay/hgcoupon?loan_id=<?= $loan->loan_id ?>&coupon_id=<?= $coupon_id ?>')"<?php endif; ?>>
                <p class="right rt_ticket">
                    <?php if (!empty($coupon_amount)): ?>
                        <span class="left">-<?php echo $coupon_amount; ?>元</span>
                    <?php else: ?>
                        <?php if ($coupon_count > 0): ?>
                            <span class="left"><?php echo $coupon_count; ?>张可用</span>
                        <?php else: ?>
                            <span class="left">暂无可用优惠券</span>
                        <?php endif; ?>
                    <?php endif; ?>
                    <em class="left"><img src="/290/images/right_jt.png"></em>
                </p>
            </a>
        </div>
        <div class="lrtcont">
            <p class="left lt_should">实际应还</p>
            <p class="right rt_should">¥<?= sprintf("%.2f",$actual_amount); ?></p>
        </div>
    </div>
    <div class="mode_payment">
        <h3>支付方式</h3>
        <div class="payment_cont bank" mark='online' style="border-bottom:none;">
            <p class="left payment_bank"><img src="/borrow/310/images/payment_bank.png"></p>
            <p id="checkbank" class="left">银行卡支付</p>
            <p class="right payment_chooes"><img src="/borrow/310/images/payment_chooes2.png"></p>
        </div>
        <i class="y-triangle"></i>
        <div class="y-card-box">
            <div id="editbank" class="y-change-card">
                <p>更换银行卡</p>
                <img src="/290/images/right_jt.png">
            </div>
        </div>
        <?php if($xianxia_type !=0):?>
        <div class="payment_cont none_line xianxia" mark='xianxia'>
            <p class="left payment_bank"><img src="/borrow/310/images/payment_transfer.png"></p>
            <p class="left">转账还款</p>
            <p class="right payment_chooes"><img src="/borrow/310/images/payment_chooes1.png"></p>
        </div>
        <?php endif; ?>
        <?php if ($wxpay_type != 0): ?>
        <div class="payment_cont none_line weixin" mark='weixin' style="display: none;">
            <p class="left payment_bank"><?php if ($is_support == 2): ?><img src="/images/zfufsi1.png">
                <?php elseif ($is_support == 1): ?><img src="/290/images/weixin2x.png">
                <?php endif;?>
            </p>
            <p class="left">微信支付</p><span id="support"></span>
            <p class="right payment_chooes"><img src="/borrow/310/images/payment_chooes1.png"></p>
            <input type="hidden" name="wxpay_type" id="wxpay_type" value="<?= $wxpay_type; ?>">
        </div>
        <?php endif; ?>
        <div class="arrows_moredown">
            <img src="/borrow/310/images/arrows_moredown.png">
        </div>
        <input type="hidden" name="channel" value="online" >
    </div>
    <div class="payment_money">
        <p class="left">付款金额</p>
        <p class="right"><input style="padding: 0.3rem 0rem;font-size: .4rem;text-align: right;border: none;outline: none;" <?php if(in_array($loan['business_type'], [5, 6, 11])){ echo "readonly=readonly";}?>  type="number" placeholder="请输入金额" name = "should_repay"  max="<?php echo sprintf('%.2f', $actual_amount); ?>" value="<?php echo sprintf('%.2f', $actual_amount); ?>">
        </p>
    </div>
</div>
<button class="immediately" id="submit">立即还款</button>
<div class="help_service">
    <div style="position:relative;">
        <img src="/borrow/310/images/tip.png" alt="" class="contact_service_tip">
        <a href="javascript:void(0);" onclick="doHelp('/borrow/helpcenter?user_id=<?php echo $user_info->user_id;?>')"><span class="contact_service_text">获取帮助</span></a>
    </div>
</div>

<form action="/new/repay/payyibao" method="post" class="form-horizontal" role="form" id="repay">
    <input type="hidden" value="<?php echo $loan['loan_id']; ?>" name="loan_id" />
    <input type="hidden" value="<?php echo $coupon_id; ?>" name="coupon_id" />
    <input type="hidden" value="<?php echo $csrf; ?>" name="_csrf" />
    <input type="hidden" value="<?php echo $goodbillids; ?>" name="goodbillids" />
    <div class="ttfukfsi checkcard" style="display: none;" >
        <div class="errore">
            <img src="/images/zfufsi4.png">
            <span>支付</span>
        </div>
        <div class="haimoneys">
            <p class="haitxts should_repay_money"></p>
            <input type="hidden" class="money_order" value="" name="money_order" />
        </div>
        <div class="tuika">
            <a>
                <div class="bank_nn">
                    <div class="bank2"><img id="chekbanksrc" src="<?php echo getImageUrl($banklist[0]['bank_abbr']); ?>" width="7%" ></div>
                    <div class="sendtwo" id="bk">
                        <p>
                            <?php if ($banklist[0]['bank_abbr'] == 'GDB') { ?>
                                广发银行
                            <?php } elseif (empty($banklist[0]['bank_abbr'])) { ?>
                                银行卡
                                <?php
                            } else {
                                echo $banklist[0]['bank_name'];
                            }
                            ?>
                            <span>
                                <?php if (!empty($banklist[0]['bank_abbr'])) {
                                echo $banklist[0]['type'] == 0 ? '借记卡' : '信用卡';
                                } ?>
                            </span>
                            尾号<?php echo substr($banklist[0]['card'], strlen($banklist[0]['card']) - 4, 4); ?>
                        </p>
                    </div>
                    <?php if ($banklist[0]['sign'] == 1): ?>
                        <img class="zbzchi" src="/images/zanbuzhichi2.png">
                    <?php else: ?>
                        <img class="rightjt" src="/images/rightjt.png">
                    <?php endif; ?>
                    <!--
                    此处需要做是否有可用卡判断
                    -->
                </div>
                <input type="hidden" value="<?php echo $banklist[0]['id']; ?>" name="card_id">
            </a>
        </div>
        <button class="queding" id="is_submit" style="line-height:1rem;">确认还款</button>
    </div>
</form>

<div class="ttfukfsi banklist" style="display: none;" >
    <div class="errore">
        <img src="/images/zfufsi4.png">
        <span>选择支付卡</span>
    </div>

    <div class="tuika">
        <?php foreach ($banklist as $key => $val): ?>
            <a
                <?php if ($val['sign'] == 2): ?>
                    class="check_bank"
                <?php endif; ?>
                    card_id = '<?php echo $val['id'] . "|" . getImageUrl($val['bank_abbr']) . "|" . $val['bank_name'] . "|" . substr($val['card'], strlen($val['card']) - 4, 4) . "|" . $val['type']; ?>' style="<?php if ($val['sign'] == 1): ?>background:#e7e7e7;<?php endif; ?>position: relative;">
                <div class="bank_nn">
                    <div class="bank2"><img  src="<?php echo getImageUrl($val['bank_abbr']); ?>" width="10%"></div>
                    <div class="sendtwo" >
                        <p style="display: inline-block; font-size: 0.4rem;color: #474d74;">
                            <?php if ($val['bank_abbr'] == 'GDB') { ?>
                                广发银行
                            <?php } elseif (empty($val['bank_name'])) { ?>
                                银行卡
                                <?php
                            } else {
                                echo $val['bank_name'];
                            }
                            ?>
                            <span style="<?php if ($val['sign'] == 1): ?>background:#c7c9d5;<?php endif; ?>">
                                <?php if (!empty($val['bank_name'])) {
                                    echo $val['type'] == 0 ? '借记卡' : '信用卡';
                                } ?>
                            </span>
                            <em style="font-size: 0.35rem;">尾号<?php echo substr($val['card'], strlen($val['card']) - 4, 4); ?></em>
                            <span style="background:#fff; color: #D1D1D1;">
                                <?php if (($is_support == 1 && !empty($account_bank->card) && $val['id'] != $account_bank->card) || ($is_support == 2 && (empty($val['bank_abbr']) || empty($val['bank_name'])))) echo '暂不支持'; ?>
                            </span>
                        </p>
                    </div>
                    <?php if ($val['sign'] == 1): ?>
                        <img style="position: absolute;width: 27%;top: 0;right: 2;" src="/images/zanbuzhichi2.png">
                    <?php endif; ?>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
    <a style="overflow: hidden; padding-bottom: .4rem;display: block; margin-top: 2rem" class="addbank" <?php if ($bank_count >= 10): ?> onclick="alert('绑定银行卡已超过10张卡');return false;"
                        <?php else: ?> href="/new/bank/addcard?banktype=3&orderinfo=<?php echo $orderInfo; ?>"
                        <?php endif; ?>>
        <img style="width: 4%;" src="/images/addadd.png">
        <span style="font-size:0.4rem;">添加新银行卡</span>
    </a>
</div>
</div>
<!--  弹窗-->
<div class="poppayout_mask" style="display:none;"></div>
<div class="mask_box bufen_box" style="display: none;">
    <img src="/borrow/310/images/bill-close.png" alt="" class="close_mask">
    <p class="mask_title">温馨提示</p>
    <p class="mask_text">你已选择部分还款，若部分还款，优惠券将不会生效</p>
    <span class="add_btn">确认还款</span>
</div>

<div class="mask_box xianxia_box" style="display: none;">
    <img src="/borrow/310/images/bill-close.png" alt="" class="close_mask">
    <p class="mask_title">温馨提示</p>
    <p class="poppayout_text" style="padding:0 0.53rem;box-sizing: border-box;">你已选择线下还款，<span style="color:#f01111">若部分还款优惠券将不会使用</span>，是否确认还款</p>
    <span class="resure_btn">确认还款</span>
    <span class="resureOut_btn">重新选择</span>
</div>

<div class="toast_tishi" id="toast_tishi" hidden>提交失败</div>

<!--授权弹层结束-->
<script src="/290/js/jquery-1.10.1.min.js"></script>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
<?php \app\common\PLogger::getInstance('weixin', '', $user_info->user_id); ?>
<?php $json_data = \app\common\PLogger::getJson(); ?>
    var baseInfoss = eval('(' + '<?php echo $json_data; ?>' + ')');
    var flag = <?php echo $flag; ?>;

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

    $(".arrows_moredown").click(function () {
        $(".weixin").show();
        $(".xianxia").removeClass("none_line");
        $(this).hide();
    });

    $(".payment_cont").click(function () {
        var checkmark = $(this).attr("mark");
        var is_support = '<?= $is_support ?>';
        if (is_support == 1 && checkmark === "weixin") {
            return false;
        }
        $(".payment_chooes").html('<img src="/borrow/310/images/payment_chooes1.png">');
        $(this).children('.payment_chooes').html('<img src="/borrow/310/images/payment_chooes2.png">');
        $("input[name='channel']").val(checkmark);
    });

    $('#is_submit').click(function(){
        zhuge.track('支付弹窗-确认还款按钮');
    });
    $('#editbank').click(function(){
        $('.poppayout_mask').show();
        $('.banklist').show();
    });
    $(function () {
        $('#is_submit').on("click", "button", function () {
            tongji('to_qrhg', baseInfoss);
            $("#is_submit").attr('disabled', true);
            var money_order_repay = $("input[name='should_repay']").val();
            var money_order = (Number(money_order_repay)).toFixed(2);
            if (!money_order || money_order == 0 || money_order < 0) {
                $('#toast_tishi').html('请输入大于0.00的还款金额');
                $('#toast_tishi').show();
                hideDiv('toast_tishi');
                $("#is_submit").attr('disabled', false);
                return false;
            }
            $('form[id="repay"]').submit();
        });
        var weihao='<?php echo substr($banklist[0]['card'], strlen($banklist[0]['card']) - 4, 4); ?>';
        var html1 = '<p>  银行卡支付(' + weihao + ')</p>';
        $('#checkbank').html(html1);

    });

    $('#submit').click(function () {
        zhuge.track('还款页面-立即还款按钮');
        var money_order_repay = $.trim($("input[name='should_repay']").val());
        var money = (Number(money_order_repay)).toFixed(2);
        var loan_id = <?php echo intval($loan['loan_id']) ?>;
        var csrf = '<?php echo $csrf; ?>';
        var coupon_id = '<?php echo $coupon_id; ?>';
        var coupon_amount = '<?php echo $coupon_amount; ?>';

        if (isNaN(money)) {
            $('#toast_tishi').html('请输入正确金额');
            $('#toast_tishi').show();
            hideDiv('toast_tishi');
            return false;
        }
        if (!money || money == 0) {
            $('#toast_tishi').html('请输入大于0.00的还款金额');
            $('#toast_tishi').show();
            hideDiv('toast_tishi');
            return false;
        }
        var money_str = money + "<em>元</em>";
        $('.should_repay_money').html(money_str);
        $('.money_order').val(money);
        var channel = $("input[name='channel']").attr('value');

        if (channel === 'weixin') {
            tongji('to_wxzhifu', baseInfoss);
            var wxpay_type = $("#wxpay_type").val();
            var url = '';
            if (wxpay_type == 1) {
                url = '/new/wxpay/submitorderinfo';
            } else {
                url = '/new/wxpay/wxpaynew';
            }

            $.ajax({
                type: "POST",
                url: url,
                dataType: 'json',
                data: {'_csrf': csrf, 'loan_id': loan_id, 'coupon_id': coupon_id, 'money': money},
                success: function (msg) {
                    if (msg.status == 0) {
                        location.href = msg.url;
                    } else {
                        $('#toast_tishi').html('操作失败');
                        $('#toast_tishi').show();
                        hideDiv('toast_tishi');
                        return false;
                    }
                }
            });
        } else if (channel === 'online') {
            var actual_amount = '<?= $actual_amount?>';
            if(coupon_id && (money != actual_amount)){
                $('.poppayout_mask').show();
                $('.bufen_box').show();
                return false;
            }
            onlinePay();

        } else if (channel === 'xianxia') {
            if(coupon_id){
                $('.poppayout_mask').show();
                $('.xianxia_box').show();
                return false;
            }
            offlinePay()
        }
    });

    function onlinePay() {
        tongji('to_online', baseInfoss);
        $("#submit").attr('disabled', true);
        $('form[id="repay"]').submit();

//        $('.poppayout_mask').show();
//        if (flag == 1) {
//            $('.checkcard').show();
//        } else {
//            $('.banklist').show();
//        }
    }

    function offlinePay() {
        tongji('to_xianxia', baseInfoss);
        url = "/borrow/repay/repay?loan_id=<?php echo $loan['loan_id']; ?>&coupon_id=<?php echo $coupon_id; ?>";
        location.href = url;
    }

    $('.add_btn').click(function () {
        $('.bufen_box').hide();
        onlinePay();
    });

    $('.resure_btn').click(function () {
        offlinePay();
    });
    
    $(".check_bank").on("click", function () {
        var card_id = $(this).attr('card_id');
        var arr = card_id.split('|');
        var account_bank = '<?php if (!empty($account_bank->card)) echo $account_bank->card ?>';
        var is_support = '<?php echo $is_support; ?>';
        if (is_support == 1 && account_bank != arr[0]) {
            return false;
        } else if (is_support == 2 && arr[2] == '') {
            return false;
        }
        console.dir(arr);
        $('#chekbanksrc').attr('src', arr[1]);
        var bank_type = arr[4] == 0 ? '借记卡' : '信用卡';
        var html = '<p>' + arr[2] + '<span>' + bank_type + '</span> 尾号' + arr[3] + '</p>';
        $('#bk').html(html);
        $('input[name="card_id"]').attr('value', arr[0]);
        var html1 = '<p>  银行卡支付(' + arr[3] + ')</p>';
        $('#checkbank').html(html1);
        $('.banklist').hide();
        $('.poppayout_mask').hide();
//        $('.checkcard').show();
    });
    $('.rightjt').click(function () {
        $('.checkcard').hide();
        $('.banklist').show();
    });
    $(".ttfukfsi .errore img").click(function () {
        $('.poppayout_mask').hide();
        $('.ttfukfsi').hide();
    });
    $('.close_mask').click(function () {
        $('.poppayout_mask').hide();
        $('.mask_box').hide();
    });
    $('.resureOut_btn').click(function () {
        $('.poppayout_mask').hide();
        $('.mask_box').hide();
    });

    $('.poppayout_mask').click(function () {
        $('.poppayout_mask').hide();
        $('.mask_box').hide();
        $('#errorLayer').hide();
        $('.checkcard').hide();
        $('.banklist').hide();
    });

    $(function () {
        var is_support = '<?= $is_support ?>';
        if (is_support == 1) {
            $(".payment_chooes").each(function () {
                $(this).children('span').css('color', 'gray');
                $("#support").css('margin-left', '20%');
                $("#support").html('暂不支持');
            });
        }
    });

    function couponList(url) {
        tongji('to_coupon_list', baseInfoss);
        setTimeout(function () {
            window.location.href = url;
        }, 100);
    }

    //2秒隐藏上传成功提示框
    function hideDiv(id) {
        var obj = $("#" + id);
        setTimeout(function () {
            obj.hide();
        }, 2000);
    }

    function doHelp(url) {
        tongji('do_help',baseInfoss);
        setTimeout(function(){
            window.location.href = url;
        },100);
    }

</script>
