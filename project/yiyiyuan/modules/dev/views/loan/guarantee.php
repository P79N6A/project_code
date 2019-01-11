<?php
$bank = array('ABC', 'ALL', 'BCCB', 'BCM', 'BOC', 'CCB', 'CEB', 'CIB', 'CMB', 'CMBC', 'GDB', 'HXB', 'ICBC', 'PAB', 'PSBC', 'SPDB', '中信');
?>
<div class="Hcontainer">
    <div class="main mt50">
        <form class="form-horizontal" role="form" method="post" action="/dev/loan/guatwo" id="loan_form">
            <div class="form-group p_ipt">
                <div class="col-xs-4 grey2"><span id="desc_col">借款用途</span></div>
                <div class="col-xs-8">
                    <input type="text" class="ipt" maxlength="25" name="desc" value="<?php echo $loan_desc; ?>" id="loan_desc" placeholder="请输入5~25个字"/>
                </div>
            </div>
            <div class="form-group p_ipt">
                <div class="col-xs-4 grey2"><span id="day_col">期限（天）</span></div>
                <div class="col-xs-8">
                    <input type="text" class="ipt" id="loan_days" value="<?php echo $loan_days; ?>" name="days" placeholder="7~21天"/>
                </div>
            </div>
            <div class="form-group p_ipt">
                <div class="col-xs-4 grey2"><span id="mon_col">金额（元）</span></div>
                <div class="col-xs-8">
                    <input type="text" class="ipt" name="amount" value="<?php echo $loan_amount; ?>" id="loan_amounts" placeholder="300~1500整"/>
                </div>
            </div>
            <div class="form-group p_ipt highlight2" id="dbr_btn">
                <div class="col-xs-4 grey2">出款卡</div>
                <div class="col-xs-8">
                    <div class="col-xs-10 bank_cont"><span class="n26"><?php echo $card['bank_name']; ?></span><span class="n26 grey2"> 尾号 <?php echo substr($card['card'], strlen($card['card']) - 4, 4) ?></span></div>
                </div>
                <input type="hidden" name="card_id" value="<?php echo $card['id']; ?>">
                <i></i>
            </div>
            <p class="red mb20 n22" id="loan_error_tip"></p>
            <p class="n26 text-right">到期应还款：<label class="red n30" id="loan_repay_amount"></label>元</p>
            <input type="hidden" id="user_id" value="<?php echo $user_id;?>"/>
            <button id="loan_buttons" type="submit"<?php if (count($loaninfo) > 0): ?>class="bgrey btn mt20" disabled="disabled"<?php else: ?>class="btn mt20"<?php endif; ?>><?php if (count($loaninfo) > 0) { ?>您有未完成的借款<?php } else { ?>下一步<?php } ?></button>
            <?php if (count($loaninfo) > 0): ?><a href="/dev/loan/succ?l=<?php echo $loaninfo['loan_id']; ?>"><button type="button" class="btn1 mt20" style="width:100%;" >查看当前借款</button></a><?php endif; ?>
        </form>
    </div>
    <?= $this->render('/layouts/_page', ['page' => 'loan']) ?>
    <?php if (!empty($userbank)): ?>
        <!-- 银行卡弹层 -->
        <div class="Hmask" style="display:none;"></div>
        <div class="layer highlight" style="top:20%;display:none;">
            <i class="on"></i> 
            <ul class="banksC dBlock">
                <?php foreach ($userbank as $key => $val): ?>
                    <li id="<?php echo $val['id']; ?>">
                        <img src="/images/bank_logo/<?php
                        if (!empty($val['bank_abbr']) && in_array($val['bank_abbr'], $bank)) {
                            echo $val['bank_abbr'];
                        } else {
                            echo 'ALL';
                        }
                        ?>.png" width="10%" class="cardImg">
                        <span class="n26 grey2 cardName"><?php echo $val['bank_name']; ?></span><b class="redLight" style="margin-right: 2%;"><?php echo $val->type == 0 ? '借记卡' : '信用卡'; ?></b><span class="n22 grey4 lastNums">尾号<?php echo substr($val['card'], strlen($val['card']) - 4, 4) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
</div>

<script type="text/javascript">
    window.onload = function() {
        var days = parseInt($('input[name="days"]').val());
        var amount = parseInt($("#loan_amounts").val());
        var withdraw_amount = parseFloat(amount * 0.01);
        var withdraw_amounts = 0;
        if (withdraw_amount < 5)
        {
            withdraw_amounts = 5;
        }else{
            withdraw_amounts = withdraw_amount;
        }
        var repayVal = parseFloat(amount) + parseFloat(amount * 0.002 * days) + withdraw_amount + withdraw_amounts;
        $('#loan_repay_amount').html(repayVal);
    };
    $(".Hmask").click(function() {
        $(".Hmask").hide();
        $(".layer").hide();
    });
    $(document).ready(function() {
        //底部导航
        $('input').focus(function() {
            $('footer').css('display', 'none');
        });
        $('input').blur(function() {
            $('footer').css('display', 'block');
        });

        //点击出款卡
        $('#dbr_btn').click(function() {
            $('.Hmask').css('display', 'block');
            $('.layer').css('display', 'block');
            $('.banksC li').each(function() {
                $(this).click(function() {
                    $('.banksC li').removeClass('on');
                    $(this).addClass('on');
                    var cardName = $(this).find('.cardName').text();
                    var cardNums = $(this).find('.lastNums').text();
                    cardNums = cardNums.substring(2, 6);
                    var bankId = $(this).attr('id');
                    $('input[name="card_id"]').attr('value', bankId);
                    $('.Hmask').css('display', 'none');
                    $('.layer').css('display', 'none');
                    var html = '';
                    html += '<div class="col-xs-10 bank_cont">';
                    html += '<span class="n26">' + cardName + '</span>';
                    html += '<span class="n26 grey2"> 尾号 ' + cardNums + '</span>';
                    html += '</div>';
                    $('#dbr_btn .col-xs-8').html(html);
                    var lineH = $('.icon_bank').height();
                    $('.bank_cont').css('lineHeight', lineH + 'px');
                });
            });
        });
    })
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

    wx.ready(function() {
        wx.hideOptionMenu();
    });
</script>