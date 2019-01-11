<?php

use app\commonapi\Keywords;

//$out_bank = Keywords::getOutBankAbbr()[0];

?>
<script>
    $(window).load(function () {
        var lineH = $('.highlight img').height();
        $('.bank_cont').css('lineHeight', lineH + 'px');
    });
    $(function () {
        $('.banksC li').each(function () {
            $(this).click(function () {
                //点击相对应的radio变为checked
                $('input[type="radio"]').prop('checked', false);
                $('input[type="radio"]').removeAttr('checked');
                $(this).find('input[type="radio"]').prop('checked', true);
                $(this).find('input[type="radio"]').attr('checked', 'checked');
            });
        });
    });
</script> 
<div class="Hcontainer">
    <img src="/images/title.png" width="100%"/>
    <div class="con">
        <div class="details">
            <div class="adver1 border_bottom_1">
                <div class="row mb30">
                    <div class="col-xs-3 cor n26">借款金额</div>
                    <div class="col-xs-9 text-right n26"><span class="red">&yen;<?php echo $amount; ?></span></div>
                </div>
                <div class="row mb30">
                    <div class="col-xs-3 cor n26">到账金额</div>
                    <div class="col-xs-9 text-right n26"><span class="red">&yen;<?php echo $amount_due; ?></span></div>
                </div>
                <div class="row mb30">
                    <div class="col-xs-3 cor n26">借款期限</div>
                    <div class="col-xs-9 text-right n26"><span class="red"><?php echo $days; ?></span>天</div>
                </div>
                <div class="row mb30">
                    <div class="col-xs-3 cor n26">借款用途</div>
                    <div class="col-xs-9 text-right n26"><?php echo $desc; ?></div>
                </div>
                <div class="row mb30">
                    <div class="col-xs-3 cor n26">保险费</div>
                    <div class="col-xs-9 text-right n26"><span class="red">&yen;<?php echo $withdraw_fee; ?></span></div>
                </div>
                <div class="row mb30">
                    <div class="col-xs-3 cor n26">利息</div>
                    <div class="col-xs-9 text-right n26"><span class="red">&yen;<?php echo $interest_fee; ?></span></div>
                </div>
                <div class="row mb30">
                    <div class="col-xs-3 cor n26">优惠44券</div>
                    <div class="col-xs-9 text-right n26"><span class="red"><?php if (empty($coupon_id) || !isset($coupon_amount)): ?>&yen;0.00<?php else: ?><?php if ($coupon_amount == 0): ?>-&yen;<?php echo $interest_fee; ?><?php else: ?>-&yen;<?php echo $coupon_amount; ?><?php endif; ?><?php endif; ?></span></div>
                </div>
                <div class="row mb30">
                    <div class="col-xs-3 cor n26">到期应还</div>
                    <div class="col-xs-9 text-right n26"><span class="red">&yen;<?php echo $repay_amount; ?></span></div>
                </div>
                <input type="hidden" id="csrfs" value="<?php echo $csrf; ?>" >
            </div>
            <div class="col-xs-12 nPad mt20 highlight" id="bankChoose">
                <div class="col-xs-2 nPad"><img src="/images/bank_logo/<?php echo $user_bankinfo1[0]['bank_abbr']; ?>.png" width="100%" style="max-width:100px;" id='yhtp'></div>
                <div class="col-xs-10 bank_cont" style="padding-left: 5px;">
                    <span class="n30" id='yhm'><?php if($user_bankinfo1[0]['bank_abbr'] == 'GDB'): echo "广发银行";  else: echo $user_bankinfo1[0]['bank_name']; endif; ?></span>
                    <span class="n26 grey2" id='yhk'><?php echo '尾号' . substr($user_bankinfo1[0]['card'], -4); ?></span>
                    <input type='hidden' name='bank_id' value=<?php echo $user_bankinfo1[0]['id']; ?>
                           id='bank_ids'>
                    <input type="hidden" name ='sign' id='sign' value=<?php echo $user_bankinfo1[0]['sign'] ? $user_bankinfo1[0]['sign'] : 1; ?>>
                    <input type='hidden' name='user_ids' value=0 id='user_ids'>
                </div>
                <i></i> 
            </div>
        </div>
        <input type="hidden" name="desc" value="<?php echo $desc; ?>">
        <input type="hidden" name="days" value="<?php echo $days; ?>">
        <input type="hidden" name="amount" value="<?php echo $amount; ?>">
        <input type="hidden" name="coupon_id" value="<?php echo $coupon_id; ?>">
        <input type="hidden" name="coupon_amount" value="<?php echo $coupon_amount; ?>">
<!--        <input type="hidden" name="bank_id" value="--><?php //echo $userbank->id; ?><!--">-->
        <img src="/images/bottom.png" width="100%" style="vertical-align:top"/>
        <p class="mb10 n26 mt10">
            <input type="checkbox" checked="checked" id="agree_loan_xieyi" class="regular-checkbox">
            <label for="agree_loan_xieyi"></label>
            勾选并同意签署
            <a href="/dev/loan/agreeloan?type=loan&loan_type=friend&desc=<?php echo urlencode($desc); ?>&days=<?php echo $days; ?>&amount=<?php echo $amount; ?>&repay_amount=<?php echo $repay_amount; ?>" target="_blank" class="underL">《先花一亿元借款协议》</a></p>
        <p class="mb10 n26 mt10">
            <input type="checkbox" checked="checked" id="agree_loan_xieyi" class="regular-checkbox">
            <label for="agree_loan_xieyi"></label>
            勾选并同意授权签署
            <a href="/dev/loan/jiufu?come_from=web" target="_blank" class="underL">《融资文件》</a></p>
        <input type="hidden" name = "flag_confirm" value="<?=$flag?>" />
        <button type="button" id="loan_confirm_new" class="btn" style="width:100%;" >确定</button>
    </div>
    <div class="Hmask"></div>
    <div class="layer highlight" style="top:5%">
        <ul class="banksC dBlock">
            <p style="border-bottom:1px solid #c2c2c2; text-align:center; font-size:15px; padding:10px 0; font-weight:bold;">选择出款卡</p>
            <?php if (!empty($user_bankinfo1)): ?>
                <?php foreach ($user_bankinfo1 as $key => $v): ?>
                    <?php if(!isset($v['sign'])){$v['sign']=2;} ?>
                    <li <?php if ($v['sign'] == 2): ?>onclick='ajax(<?php echo $v['id']; ?>,<?php echo $v['user_id']; ?>)' <?php else: ?>  style="background:#e7e7e7;position: relative;"<?php endif; ?>>
                        <img src="/images/bank_logo/<?php echo!empty($v['bank_abbr']) ? $v['bank_abbr'] : 'ALL'; ?>.png" width="10%" id='loan_img_<?php echo $v['id']; ?>'>
                        <span class="n26 grey2" id='loan_bank_name_<?php echo $v['id']; ?>'><?php if($v['bank_abbr'] == 'GDB'): echo "广发银行";  else: echo $v['bank_name']; endif; ?></span>
                        <b class="redLight" style="margin-right: 2%;padding: 1px 10px 2px;<?php if ($v['sign'] == 1): ?>background:#c7c9d5;<?php endif; ?>"><?php echo $v['type'] == 0 ? '储蓄卡' : '信用卡'; ?></b>
                        <span class="n22 grey4" id="loan_card_<?php echo $v['id']; ?>">尾号<?php echo substr($v['card'], -4); ?></span>
                        <input type = "hidden" id = "sign_checked_<?php echo $v['id']; ?>" value = "<?=$v['sign']?>" />
                        <?php if ($v['sign'] == 1): ?>
                            <img style="position: absolute;width: 27%;top: 0;right: 0;" src="/images/zanbuzhichi2.png">
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
            <p style="border-top:1px solid #c2c2c2; text-align:right; font-size:16px; padding:10px 5%; color:#939ab0; "><a <?php if($bank_count >=10 ): ?>onclick="alert('绑定银行卡已超过10张卡');return false;"<?php else: ?> href="/new/bank/addcard?banktype=1&orderinfo=<?php echo $orderinfo; ?>"<?php endif; ?>>添加银行卡</a></p>
        </ul>
    </div>
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
</script>
<script>
    $(window).load(function () {
        var lineH = $('.highlight img').height();
        $('.bank_cont').css('lineHeight', lineH + 'px');
    });
    $(function () {
        $('.Hmask').css('display', 'none');
        $('.layer').css('display', 'none');
        $('#bankChoose').click(function () {
            $('.Hmask').css('display', 'block');
            $('.layer').css('display', 'block');
        });
        $('.Hmask').click(function () {
            $('.layer').css('display', 'none');
        });
    });

    function ajax(num, user_id) {
        //alert(user_id);

        var img = $('#loan_img_' + num).attr('src');
        //alert(img);
        var bank_name = $('#loan_bank_name_' + num).html();
        var sign_checked = $('#sign_checked_' + num).val();
        var card = $('#loan_card_' + num).html();
        $('#yhm').html(bank_name);
        $('#yhk').html(card);
        $('#bank_ids').attr('value', num);
        $('#user_ids').attr('value', user_id);
        $('#yhtp').attr('src', img);
        $('#sign').attr('value', sign_checked);
        //alert(card);
        $('.Hmask').hide();
        $('.layer').hide();

    }
</script> 