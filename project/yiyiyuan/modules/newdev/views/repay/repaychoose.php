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
<head xmlns="http://www.w3.org/1999/html">
    <meta charset="utf-8">
        <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
        <title></title>
        <link rel="stylesheet" type="text/css" href="/290/css/reset.css"/>
        <link rel="stylesheet" type="text/css" href="/290/css/inv.css"/>
        <script src="/290/js/jquery-1.10.1.min.js"></script>

</head>
<style>
    .fukfsies {width: 90%;position: fixed;top: 15%;left: 5%;border-radius: 5px;z-index: 100;background: #fff;}
    .fukfsies .adderr { position: relative; top:0; left:0; border-bottom:1px #c2c2c2 solid; width: 100%; color: #444;  }
    .fukfsies .adderr  img{position: absolute; right:5%; top: 18px; width: 5%;}
    .fukfsies .payerror{display: block; width: 100%; text-align: center; font-size: 1.5rem; padding:30px  0 20px;}
</style>

<div class="haimoney" style="margin-top: 1.5rem;">
    <div class="hkje">
        <p class="haititle">还款金额(元)</p>
        <p class="haitxt"><?php echo sprintf('%.2f', $total_amoun); ?> </p>
        <p class="hailast" style="text-align:center">最后还款日 <em><?php echo $end_date; ?></em></p>
    </div>
    <div id="demo11"></div>
    <div class="hkxqg" style="display:none">
        <h3>还款详情</h3>
        <div class="youbianjl  ">
            <?php foreach ($repay_plan as $val): ?>
                <div data_status  ='<?php echo $val['status'] ?>'  class='youbianjlone  <?php echo $val['status'] == 8 ? 'over_end' : '' ?>'>
                    <span class="bianjlone"><?php echo $val['now_term']; ?>/<?php echo $val['total_term']; ?>期</span>
                    <span class="bianjltwo"><em class="<?php echo $val['status'] != 8 ? 'changec' : '' ?>"><?php echo sprintf('%.2f', $val['amount']); ?></em>元</span>
                    <span class="bianjlthree"><?php echo $val['days']; ?></span>
                    <?php if ($val['status'] == 8): ?>
                        <span  class="bianjlfour">已还款</span>
                    <?php else: ?>
                        <span data_money ='<?php echo sprintf('%.2f', $val['amount']); ?>' class="bianjlfour notclear "><img src="/290/images/no_gx.png"></span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <div id="demo12" style=" float: initial; display: block;"></div>
    </div>

</div>
<a href="javascript:void(0);" <?php if ($coupon_count > 0): ?>onclick="couponList('/new/repay/hgcoupon?loan_id=<?= $loan->loan_id ?>&coupon_id=<?= $coupon_id ?>')"<?php endif; ?>>
    <div class="jinemes none_yhui" >
        <?php if (!empty($coupon_amount)): ?>
            <span class="zitibh">优惠券 <span style="color: #c90000;font-weight:bold;font-size: 1.142rem;">-<?php echo $coupon_amount; ?>元</span></span>
        <?php else: ?>
            <?php if ($coupon_count > 0): ?>
                <span class="zitibh">优惠券 <span style="color: #c90000;font-weight:bold;font-size: 1.142rem;"><?php echo $coupon_count; ?>张可用</span></span>
            <?php else: ?>
                <span class="zitibh">优惠券 <span>暂无可用优惠券</span></span>
            <?php endif; ?>
        <?php endif; ?>
        <div class="tuzizi"><img src="/290/images/right_jt.png"></div>
    </div>
</a>
<div class="fukfsi">
    <div class="errore">
        <span>支付方式选择</span>
    </div>
    <div class="gzmess yumendyu" data-type="1">
        <div class="ymxinxi" mark='online'>
            <p><img src="/images/zfufsi2.png"></p>
            <span class="bankzf">银行卡支付</span>
            <em><img src="/images/zfufsi5.png"></em>
        </div>
    </div>
    <?php if ($wxpay_type != 0): ?>
        <div class="gzmess" data-type="2" style="display: none;">
            <div class="ymxinxi" mark='weixin'>
                <p> <?php if ($is_support == 2): ?><img src="/images/zfufsi1.png">
                    <?php elseif ($is_support == 1): ?><img src="/290/images/weixin2x.png">
                    <?php endif;?>
                </p>
                <span>微信支付</span><span id="support"></span>
                <em></em>
                <input type="hidden" name="wxpay_type" id="wxpay_type" value="<?= $wxpay_type; ?>">
            </div>
        </div>
    <?php endif; ?>
    <!--判断是否是分期，分期没有线下支付-->
    <?php if (\app\commonapi\Keywords::inspectOpen() != 2): ?>
    <?php if (isset($loan) && !in_array($loan['business_type'], [5, 6])): ?>
        <div class="gzmess yumendyu xianxia" data-type="3" data-url="/borrow/repay/repay?loan_id=<?php echo $loan['loan_id']; ?>&coupon_id=<?php echo $coupon_id; ?>" style="display: none;">
            <div class="ymxinxi" mark="xianxia">
                <p><img src="/images/zfufsi3.png"></p>
                <span class="xinxiazf">转账还款</span>
                <em class="showImg"></em>
            </div>
        </div>
    <?php else: ?>
        <div></div>
    <?php endif; ?>
    <?php endif; ?>
    <input type="hidden" name="channel" value="online" >
    <div class="txtxtexs">
        <div  class="chakan">
            <span>查看更多还款方式 </span>
            <div id="demo12" style="margin: 1px 0 0 5px;"></div>
        </div>
    </div>
</div>
<div style="text-align: right;padding-top: 15px;padding-right: 5%;">
    <span><em id="newval"style="font-size: 13px;">已优惠¥<?=$coupon_amount;?></em>实际应还<em id="newactual_amount" style="color:#c90000;">¥<?=$actual_amount?></em></span>
</div>
<div class="jinemes">
    <span>金额</span><input type="number" placeholder="请输入金额" name = "should_repay"  max="<?php echo sprintf('%.2f', $actual_amount); ?>" value="<?php echo sprintf('%.2f', $actual_amount); ?>" <?php if (!empty($coupon_id)): ?>readonly<?php endif; ?>>
</div>
<div class="button"><button id="submit">确认还款</button></div>
<?php if (!empty($user_allow) && $user_allow->type == 3): ?>
    <div class="haikfshi"><a href="/renew/renewal/index?loan_id=<?php echo $loan['loan_id']; ?>" id="demo16">续期还款</a></div>
<?php elseif (!empty($user_allow) && $user_allow->type != 3): ?>
    <div class="haikfshi"><a href="/new/renewal/index?loan_id=<?php echo $loan['loan_id']; ?>" id="demo16">续期还款</a></div>
<?php endif; ?>
<div class="Hmask" style="display: none;"></div>
<form action="/new/repay/payyibao" method="post" class="form-horizontal" role="form" id="repay">
<input type="hidden" value="<?php echo $loan['loan_id']; ?>" name="loan_id" />
<input type="hidden" value="<?php echo $coupon_id; ?>" name="coupon_id" />
<input type="hidden" value="<?php echo $csrf; ?>" name="_csrf" />
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

<div class="ttfukfsi banklist" hidden>
    <div class="errore">
        <img src="/images/zfufsi4.png">
            <span>选择支付卡</span>
    </div>

    <div class="tuika" style="height: 14rem;overflow:auto">
            <?php foreach ($banklist as $key => $val): ?>
            <a
<?php if ($val['sign'] == 2): ?>
                    class="check_bank"
                            <?php endif; ?>
                card_id = '<?php echo $val['id'] . "|" . getImageUrl($val['bank_abbr']) . "|" . $val['bank_name'] . "|" . substr($val['card'], strlen($val['card']) - 4, 4) . "|" . $val['type']; ?>' style="<?php if ($val['sign'] == 1): ?>background:#e7e7e7;<?php endif; ?>position: relative;">
                <div class="bank_nn">
                    <div class="bank2"><img  src="<?php echo getImageUrl($val['bank_abbr']); ?>" width="10%"></div>
                    <div class="sendtwo"><p>
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
                            </span> <em>尾号<?php echo substr($val['card'], strlen($val['card']) - 4, 4); ?></em><span><?php if (($is_support == 1 && !empty($account_bank->card) && $val['id'] != $account_bank->card) || ($is_support == 2 && (empty($val['bank_abbr']) || empty($val['bank_name'])))) echo '暂不支持'; ?></span></p> </div>
           <?php if ($val['sign'] == 1): ?>
                        <img style="position: absolute;width: 27%;top: 0;right: 2;" src="/images/zanbuzhichi2.png">
<?php endif; ?>
                </div>
            </a>
<?php endforeach; ?>
        </form>
    </div>
    <a class="addbank" <?php if ($bank_count >= 10): ?>onclick="alert('绑定银行卡已超过10张卡');
                return false;"<?php else: ?>href="/new/bank/addcard?banktype=3&orderinfo=<?php echo $orderInfo; ?>"<?php endif; ?>><img src="/images/addadd.png"> <span>添加新银行卡</span></a>
</div>
<!--支付失败弹层-->
<div style="display:none"  id="errorLayer" class="fukfsies">
    <div class="adderr close_error_layer">
        <img src="/images/zfufsi4.png">
    </div>
    <p class="payerror">支付失败！</p>
</div>
<!--授权弹层开始-->
<div style="display:none"  id="fundauth" class="fukfsies">
    <div class="adderr close_error_layer">
        <img src="/images/zfufsi4.png">
    </div>
    <a href="javascript:void(0);"><p class="payerror">为保证资金安全，点击去授权</p></a>
</div>
<!--授权弹层结束-->
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script src="/newdev/js/log.js" type="text/javascript" charset="utf-8"></script>
<script>
<?php \app\common\PLogger::getInstance('weixin', '', $user_info->user_id); ?>
<?php $json_data = \app\common\PLogger::getJson(); ?>
        var baseInfoss = eval('(' + '<?php echo $json_data; ?>' + ')');

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
        $(function () {
            var loan_id = <?php echo $loan['loan_id']; ?>;
            $('#is_submit').on("click", "button", function () {
                tongji('to_qrhg', baseInfoss);
                $("#is_submit").attr('disabled', true);
                var card_id = $('input[name="card_id"]').attr('value');
                var money_order_repay = $("input[name='should_repay']").val();
                var money_order = (Number(money_order_repay)).toFixed(2);

                if (!money_order || money_order == 0 || money_order < 0) {
                    alert("请输入大于0.00的还款金额");
                    $("#is_submit").attr('disabled', false);
                    return false;
                }
                $('form[id="repay"]').submit();
            });

        });
        $(".chakan").click(function () {
            $(".gzmess").show();
            $(".txtxtexs").hide();
        });
        $(".gzmess").click(function () {
            var checkmark = $(this).children().attr("mark");
            var is_support = '<?= $is_support ?>';
            if (is_support == 1 && checkmark === "weixin") {
                return false;
            }
            $(".ymxinxi").each(function () {
                if ($(this).attr("mark") === checkmark) {
                    $(this).children('em').html('<img src="/images/zfufsi5.png">');
                    $("input[name='channel']").attr('value', checkmark);
                } else {
                    $(this).children('em').html('');
                }
            });
        });
        $('#submit').click(function () {
            var flag = <?php echo $flag; ?>;
            var money_order_repay = $.trim($("input[name='should_repay']").val());
            var money = (Number(money_order_repay)).toFixed(2);
            if (isNaN(money)) {
                alert("请输入正确金额");
                return false;
            }
            if (!money || money == 0) {
                alert("请输入大于0.00的还款金额");
                return false;
            }
            var money_str = money + "<em>元</em>";
            $('.should_repay_money').html(money_str);
            $('.money_order').val(money);
            var channel = $("input[name='channel']").attr('value');

            //判断选中和提交是否是相同还款方式
            var checkmark = $(this).children().attr("mark");
            var choose = "";
            $(".ymxinxi").each(function () {
                var em_info = $(this).children('em').html();
                if (em_info != "") {
                    choose = $(this).attr("mark");
                }
            });
            if (choose != "" && choose != channel) {
                channel = choose;
            }
            if (channel === 'weixin') {
                tongji('to_wxzhifu', baseInfoss);
                var wxpay_type = $("#wxpay_type").val();
                var url = '';
                if (wxpay_type == 1) {
                    url = '/new/wxpay/submitorderinfo';
                } else {
                    url = '/new/wxpay/wxpaynew';
                }
                var loan_id = <?php echo intval($loan['loan_id']) ?>;
                var csrf = '<?php echo $csrf; ?>';
                var coupon_id = '<?php echo $coupon_id; ?>';
                var coupon_amount = '<?php echo $coupon_amount; ?>';
                $.ajax({
                    type: "POST",
                    url: url,
                    dataType: 'json',
                    data: {'_csrf': csrf, 'loan_id': loan_id, 'coupon_id': coupon_id, 'money': money},
                    success: function (msg) {
                        if (msg.status == 0) {
                            location.href = msg.url;
                        } else {
                            alert("操作失败");
                            return false;
                        }
                    }
                });
            } else if (channel === 'online') {
                tongji('to_online', baseInfoss);
                $('.Hmask').show();
                if (flag == 1) {
                    $('.checkcard').show();
                } else {
                    $('.banklist').show();
                }
            } else if (channel === 'xianxia') {
                tongji('to_xianxia', baseInfoss);
                var url = $('.xianxia').attr('data-url');
                location.href = url;
            }
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
            $('.banklist').hide();
            $('.checkcard').show();
        });
        $('.rightjt').click(function () {
            $('.checkcard').hide();
            $('.banklist').show();
        });
        $(".ttfukfsi .errore img").click(function () {
            $('.Hmask').hide();
            $('.ttfukfsi').hide();
        });
        $('.Hmask').click(function () {
            $('.Hmask').hide();
            $('.ttfukfsi').hide();
            $("#errorLayer").hide();
            $("#fundauth").hide();
        });
        //关闭支付失败弹层
        $(".close_error_layer").click(function () {
            $(".Hmask").hide();
            $("#errorLayer").hide();
            $(".payerror").html("支付失败！");
        });

        $(function () {
            $(".ttfukfsi .errore img").click(function () {
                $('.Hmask').hide();
                $('.ttfukfsi').hide();
            });
            $("#demo12").click(function () {
                $(".hkxqg").hide();
                $(this).hide();
                $("#demo11").show();
            });
            $("#demo11").click(function () {
                $(".hkxqg").show();
                $(this).hide();
                $("#demo12").show();
            });
            //金额输入判断
            //        $(document).on("input propertychange", ".jinemes input", function () {
            //            console.log("inputchange");
            //            if ($(this).val() < valresult) {
            //                if ($(".youbianjl .youbianjlone").hasClass("over_end")) {
            //                    $(".over_end").next().addClass("chk").siblings().removeClass("chk");
            //                    $('.youbianjlone').find("em").removeClass("changec");
            //                    $('.chk').find("em").addClass("changec");
            //                    $('.youbianjlone').find("img").attr("src", "/290/images/yes_gx.png");
            //                    $('.chk').find("img").attr("src", "/290/images/no_gx.png");
            //                } else {
            //                    $(".youbianjl .youbianjlone").eq(0).addClass("chk").siblings().removeClass("chk");
            //                    $('.youbianjlone').find("em").removeClass("changec");
            //                    $('.chk').find("em").addClass("changec");
            //                    $('.youbianjlone').find("img").attr("src", "/290/images/yes_gx.png");
            //                    $('.chk').find("img").attr("src", "/290/images/no_gx.png");
            //                }
            //            }
            //        })
        });


        $(function () {
            $(".youbianjlone").click(function () {
                var thisStatus = $(this).attr("data_status");
                if (thisStatus == 8 || thisStatus == 12) {
                    return false;
                }
                var amountLenth = $(".notclear");
                var overEnd = $(".over_end").length;
                var thisIndex = ($(this).index());
                var nowIndex = parseInt(thisIndex - overEnd);
                var totalAmount = '0';
                var src = $(this).find('img').attr("src");
                $.each(amountLenth, function (i, obj) {
                    if (src == '/290/images/no_gx.png') {
                        if (i < nowIndex) {
                            var thisVal = parseFloat($(obj).attr('data_money'));
                            totalAmount = parseFloat(totalAmount) + thisVal;
                            $(obj).find("img").attr("src", "/290/images/no_gx.png");

                            $(obj).parents(".youbianjlone").find("em").addClass("changec");
                        } else {
                            $(obj).find("img").attr("src", "/290/images/yes_gx.png");

                            $(obj).parents(".youbianjlone").find("em").removeClass("changec");
                        }
                    } else {
                        //                    nowIndex = parseInt(nowIndex) + 1;
                        if (i <= nowIndex) {
                            var thisVal = parseFloat($(obj).attr('data_money'));
                            totalAmount = parseFloat(totalAmount) + thisVal;
                            $(obj).find("img").attr("src", "/290/images/no_gx.png");

                            $(obj).parents(".youbianjlone").find("em").addClass("changec");

                        } else {
                            $(obj).find("img").attr("src", "/290/images/yes_gx.png");

                            $(obj).parents(".youbianjlone").find("em").removeClass("changec");
                        }
                    }
                    //                if (i < nowIndex) {
                    //                    var thisVal = parseFloat($(obj).attr('data_money'));
                    //                    totalAmount = parseFloat(totalAmount) + thisVal;
                    //                    $(obj).find("img").attr("src", "/290/images/no_gx.png");
                    //
                    //                    $(obj).parents(".youbianjlone").find("em").addClass("changec");
                    //                } else {
                    //                    $(obj).find("img").attr("src", "/290/images/yes_gx.png");
                    //
                    //                    $(obj).parents(".youbianjlone").find("em").removeClass("changec");
                    //                }
                })
                $(".jinemes input").val(parseFloat(totalAmount).toFixed(2));
            })
        });

        $(function () {
            var is_support = '<?= $is_support ?>';
            if (is_support == 1) {
                $(".ymxinxi").each(function () {
                    $(this).children('span').css('color', 'gray');
                    $("#support").css('margin-left', '170px');
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
</script>
