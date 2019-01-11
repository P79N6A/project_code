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
<div class="haimoney">
    <div class="addawh"><img src="/images/addawh.png">什么是续期还款？</div>
    <p class="haititle">应还款金额</p>
    <p class="haitxt"><?php echo sprintf('%.2f', $money); ?> <em>元</em></p>
    <p class="hailast">最后还款日 <em><?php echo $end_date; ?></em></p>
</div>
<div class="fukfsi">
    <div class="errore">
        <span>支付方式选择</span>
    </div>
    <div class="gzmess yumendyu">
        <div class="ymxinxi" mark='online'>
            <p><img src="/images/zfufsi2.png"></p>
            <span class="bankzf">银行卡支付</span>
            <em><img src="/images/zfufsi5.png"></em>
        </div>
    </div>
    <!--    <div class="gzmess" style="display: none;">-->
    <!--        <div class="ymxinxi" mark='weixin'>-->
    <!--            <p><img src="/images/zfufsi1.png"></p>-->
    <!--            <span>微信支付</span>-->
    <!--            <em></em>-->
    <!--        </div>-->
    <!--    </div>-->
    <input type="hidden" name="channel" value="online" >
    <div class="txtxtexs">
        <div  class="chakan">
            <span>查看更多还款方式 </span>
            <div id="demo12"></div>
        </div>
    </div>
</div>
<div class="button"><button id="submit">确认续期</button></div>
<div class="haikfshi"><a href="/borrow/repay/repaychoose?loan_id=<?php echo $loan['loan_id']; ?>" id="demo16">全额还款</a></div>

<div class="Hmask" <?php if ($is_show != 1): ?>style="display: none;" <?php endif; ?>></div>
<div class="xuqhk" <?php if ($is_show != 1): ?>style="display: none;" <?php endif; ?>>
    <h3>什么是续期还款？</h3>
    <p>续期还款是用户通过支付一定续期费用延长最后还款日的操作。</p>
    <h3>续期还款是否收费？</h3>
    <p>续期还款会收取一定费用，具体以页面展示为准。</p>
    <h3>续期还款可将还款日延长多久？</h3>
    <p>续期还款最长可延长一个借款周期。</p>
    <button>知道了</button>
</div>

<div class="ttfukfsi checkcard" style="display: none;" >
    <div class="errore">
        <img src="/images/zfufsi4.png">
        <span>支付</span>
    </div>
    <div class="haimoneys">
        <p class="haitxts"><?php echo sprintf('%.2f', $money); ?><em>元</em></p>
    </div>
    <div class="tuika">
        <a <?php if ($mark == 0): ?> class="jianzl"<?php endif; ?>>
            <?php if ($mark == 1): ?>
                <div class="bank_nn">
                    <div class="bank2"><img id="chekbanksrc" src="<?php echo getImageUrl($banklist[0]['bank_abbr']); ?>" width="10%"></div>
                    <div class="sendtwo" id="bk"><p><?php
                            if ($banklist[0]['bank_abbr'] == 'GDB'): echo "广发银行";
                            else: echo $banklist[0]['bank_name'];
                            endif;
                            ?><span><?php echo $banklist[0]['type'] == 0 ? '借记卡' : '信用卡'; ?></span> 尾号<?php echo substr($banklist[0]['card'], strlen($banklist[0]['card']) - 4, 4); ?></p></div>
                    <?php if ($banklist[0]['sign'] == 1 || empty($banklist[0]['bank_name']) || empty($banklist[0]['bank_abbr'])): ?>
                        <img class="zbzchi" src="/images/zanbuzhichi2.png">
                    <?php else: ?>
                        <img class="rightjt" src="/images/rightjt.png">
                    <?php endif; ?>
                    <!--
                    此处需要做是否有可用卡判断
                    -->
                </div>
            <?php endif; ?>
            <input type="hidden" value="<?php echo $mark == 1 ? $banklist[0]['id'] : ''; ?>" name="bank_id">
        </a>
    </div>
    <button class="queding" id="is_submit">确认续期</button>
</div>


<div class="ttfukfsi banklist" hidden>
    <div class="errore">
        <img src="/images/zfufsi4.png">
        <span>选择还款卡</span>
    </div>

    <div class="tuika" style="height: 14rem;overflow:auto">
        <?php foreach ($banklist as $key => $val): ?>
            <a <?php if ($val['sign'] == 2): ?>class="check_bank"<?php endif; ?> bank_id='<?php echo $val['id'] . "|" . getImageUrl($val['bank_abbr']) . "|" . $val['bank_name'] . "|" . substr($val['card'], strlen($val['card']) - 4, 4) . "|" . $val['type']; ?>' style="<?php if ($val['sign'] == 1): ?>background:#e7e7e7;<?php endif; ?>position: relative;">
                <div class="bank_nn">
                    <div class="bank2"><img  src="<?php echo getImageUrl($val['bank_abbr']); ?>" width="10%"></div>
                    <div class="sendtwo"><p><?php
                            if ($val['bank_abbr'] == 'GDB'): echo "广发银行";
                            else: echo $val['bank_name'];
                            endif;
                            ?> <span><?php echo $val['type'] == 0 ? '借记卡' : '信用卡'; ?></span> <em>尾号<?php echo substr($val['card'], strlen($val['card']) - 4, 4); ?></em></p> </div>
                    <?php if ($val['sign'] == 1 || empty($val['bank_name']) || empty($val['bank_abbr'])): ?>
                        <img class="zbzchi" src="/images/zanbuzhichi2.png">
                    <?php else: ?>
                        <img class="rightjt" src="/images/rightjt.png">
                    <?php endif; ?>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
    <!-- <a class="addbank" <?php if ($bank_count >= 10): ?>onclick="alert('绑定银行卡已超过10张卡');return false;"<?php else: ?> href="/dev/bank/addcard?user_id=<?php echo $loan->user_id; ?>"<?php endif; ?>><img src="/images/addadd.png"> <span>添加新银行卡</span></a> -->
</div>
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
    $(function () {
        var sign = <?php echo $mark; ?>;
        var loan_id = <?php echo $loan['loan_id']; ?>;
        var money = <?php echo $money; ?>;
        var csrf = '<?php echo $csrf; ?>';
        var user_id = <?php echo $loan['user_id']; ?>;
        $('#is_submit').click(function () {
            zhuge.track('支付弹窗-确认续期按钮');
            $("#is_submit").attr('disabled', true);
            var bank_id = $('input[name="bank_id"]').attr('value');
            $.post("/new/renewal/subpaynew", {bank_id: bank_id, loan_id: loan_id}, function (result) {
                
                var data = eval("(" + result + ")");
                console.log(data);
                if (data.res_code == '0') {
                    window.location = data.url;
                }
//                else if (data.res_code == '8') {//存管开户
//                    $.ajax({
//                        type: "POST",
//                        url: "/borrow/custody/newopenwx",
//                        data: {user_id: user_id, type: 10, _csrf: csrf},
//                        success: function (data) {
//                            datas = eval('(' + data + ')');
//                            if (datas.res_code == '0000') {
//                                //window.location.href = '/borrow/custody/waiting?user_id='+user_id+'&type='+open_type;
//                                window.location = datas.res_data;
//                            } else {
//                                alert(data.res_msg);
//                            }
//                        }
//                    });
//                }
                else if (data.res_code == '7') {//存管开户
                    window.location = '/borrow/custody/list?type=10&list_type=1';
                } else {
                    alert(data.res_msg);
                    $("#is_submit").attr('disabled', false);
                    return false;
                }
            });
        });
        $(".chakan").on('click', function () {
            $(".gzmess").show();
            $(".txtxtexs").hide();
        });
        $(".gzmess").click(function () {
            var checkmark = $(this).children().attr("mark");
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
            zhuge.track('续期还款-确认续期按钮');
            var channel = $("input[name='channel']").attr('value');
            var loan_id = <?php echo $loan['loan_id']; ?>;
            if (channel === 'weixin') {
                $("#submit").attr('disabled', true);
                $.post("/new/renewal/weixinsubpay", {loan_id: loan_id}, function (result) {
                    if (result.status == '0') {
                        window.location = result.url;
                    } else {
                        alert("您暂时不能申请续期");
                        $("#submit").attr('disabled', false);
                        return false;
                    }
                }, "json");
            } else if (channel === 'online') {
                $('.Hmask').show();
                if (sign == 1) {
                    $('.checkcard').show();
                } else {
                    $('.banklist').show();
                }
            }
        });
        $(".check_bank").click(function () {
            var bank_id = $(this).attr('bank_id');
            arr = bank_id.split('|');
            console.dir(arr);
            $('#chekbanksrc').attr('src', arr[1]);
            bank_type = arr[4] == 0 ? '借记卡' : '信用卡';
            html = '<p>' + arr[2] + '<span>' + bank_type + '</span> 尾号' + arr[3] + '</p>';
            $('#bk').html(html);
//            $('#chekbankwei').html(arr[3]); 
            $('input[name="bank_id"]').attr('value', arr[0]);
//            $('#chekbanktype').html(arr[4] == 0 ? '借记卡' : '信用卡');
            $('.banklist').hide();
            $('.checkcard').show();
        });
        $('.rightjt').click(function () {
            $('.checkcard').hide();
            $('.banklist').show();
        });
        //什么是续期还款
        $('.haimoney .addawh').click(function () {
            $('.Hmask').show();
            $('.xuqhk').show();
        });
        $(".xuqhk button").click(function () {
            $('.Hmask').hide();
            $('.xuqhk').hide();
        });
        $(".ttfukfsi .errore img").click(function () {
            $('.Hmask').hide();
            $('.ttfukfsi').hide();
        });
    });
</script>