<?php
$card_typ = array(
    '0' => '借记卡',
    '1' => '贷记卡',
    '2' => '预付费卡',
    '3' => '准贷记卡',
    '4' => '其他',
);
?>
<style>
    .Hmask{
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,.7);
        position: fixed;
        top: 0;
        left:0;
        z-index: 100;
    }
    .dBlock{display: block !important;}
    .layer{
        width:86%;
        position: fixed;
        top:15%;
        left:50%;
        margin-left: -43%;
        background: #fff;
        border-radius: 10px;
        z-index: 110;
    }
    img{
        width: 10%;
        vertical-align: middle;
    }
    .banksC{
        width:100%;
        position: absolute;
        top:0;
        left:0;
        z-index: 10;
        border-top:none;
        display: none;
        background: #fff;
        border-bottom-right-radius:5px;
        border-bottom-left-radius:5px;
    }
    .layer .banksC{
        border-radius: 5px;
        overflow: hidden;
    }
    .bank_sure{
        width:100%;
        padding: 5px 3%;
    }
    .banksC li{
        width:100%;
        padding: 8px 3%;
        border-bottom: 1px solid #cdcdcd;
        border-width: 0px 0px 1px;
        -webkit-border-image: url(/images/image2base64.php.png) 2 0 stretch;
        border-image: url(/images/image2base64.php.png) 2 0 stretch;
    }
    .layer .banksC li:last-child{
        border: none;
    }
    .banksC li.on{    
        background: #e7e7e7;
    }
    .banksC li input{
        visibility: hidden;
    }
    .redLight{
        padding: 0 3px;
        background:#e74747;
        border-radius: 10px;
        font-size: 0.2rem;
        color: #fff;
        margin-top: 3px;
        margin-left: 10px;
    }
    .grey4{color: #c2c2c2;}
    a{
        //text-align:right; 
        font-size:16px; color:#939ab0; 
    }
    .highlight i{
        /*width: 5%;
        height: 30%;*/
        width:8px;
        height: 8px;
        max-width: 25px;
        max-height: 25px;
        background: url(../images/triangle.png) no-repeat;
        background-size:100% 100%;
        position: absolute;
        bottom: 2px;
        right:2px;
    }
    .highlight i.on{
        width: 8px;
        height: 8px;
        background: url(/images/triangle2.png) no-repeat;
        background-size:100% 100%;
        position: absolute;
        top: 5px;
        right:5px;
        z-index: 11;
    }
</style>
<input type ="hidden" type='text' id='oint' value="<?=$userbank[0]['sign']?>">
<div class="haimoney">
    <p class="haititle">应还款金额</p>
    <p class="haitxt"><?php echo sprintf('%.2f', $loaninfo['huankuan_amount']); ?> <em>元</em></p>
    <p class="hailast">最后还款日 <em><?php echo date('m月d日', strtotime($loaninfo['end_date']) - 24 * 3600); ?></em></p>
</div>
<form action="/dev/repay/payyibao" method="post" class="form-horizontal" role="form" id="pay">
    <div class="tuika">
        <a>
            <div class="bank_nn" id="showbank"> 
                <div class="bank2"><img  src="/images/bank_logo/<?php
//                    if (!empty($userbank[0]['bank_abbr']) && in_array($userbank[0]['bank_abbr'], $bank)) {
//                        echo $userbank[0]['bank_abbr'];
//                    } else {
//                        echo 'ALL';
//                    }
                    echo!empty($userbank[0]['bank_abbr']) ? $userbank[0]['bank_abbr'] : 'ALL';
                    ?>.png" width="10%"></div>
                <div class="sendtwo">
                    <p ><?php echo $userbank[0]['bank_name']; ?><span><?php echo $userbank[0]['type'] == 0 ? '借记卡' : '信用卡'; ?></span></p>
                    <p class="weihaom">尾号<?php echo substr($userbank[0]['card'], strlen($userbank[0]['card']) - 4, 4); ?></p>
                </div>
                <img class="rightjt" src="/images/rightjt.png">
            </div>
        </a>

        <input type="hidden" name="card_id" value="<?php echo $userbank[0]['id']; ?>"/>
        <input type="hidden" name="loan_id" value="<?php echo $loaninfo['loan_id']; ?>"/>
        <input type="hidden" name="bank_type" id="bank_type" value="<?php echo $userbank[0]['type']; ?>">
        <div class="jinemes">
            <span>金额&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<em><input type="text" <?php if ($loaninfo['business_type'] == 2): ?>readonly="readonly"<?php endif; ?> name="money_order" value="<?php echo sprintf('%.2f', $loaninfo['huankuan_amount']); ?>" placeholder="请输入还款金额" style="margin-left: -10%;"></em></span>
        </div>
    </div>



    <input type = "hidden" name = "flag_confirm" value="<?= $flag ?>" />
    <div class="button"><button id="tbug">下一步</button></div>
</form>
<div class="haikfshi">
    <!--<span class="text-left">还款方式：</span>-->
    <a style="text-decoration:none;" href="/dev/loan/repay?loan_id=<?php echo $loaninfo['loan_id']; ?>" style="">线下支付>></a></div>
<!--<div class="haimoney">
    <ul class="way">
        <li>1. 通过汇款给先花花账户<br>
            &nbsp;&nbsp;&nbsp;&nbsp;户   &nbsp;&nbsp;&nbsp;名：崔毅龙<br>
            &nbsp;&nbsp;&nbsp;&nbsp;开户行：中信银行世纪城支行<br>
            &nbsp;&nbsp;&nbsp;&nbsp;帐&nbsp;&nbsp;&nbsp;号：6217680701417383
        </li>
    </ul>
</div>
<p class="haitishi">*还款成功后，请将还款凭证拍照发给先花一亿元～</p>-->

<div class="Hmask" style="display: none;"></div>
<div class="layer highlight" style="top:20%;display: none;">
    <i class="on"></i> 
    <ul class="banksC dBlock">
        <?php foreach ($userbank as $key => $val): ?>
            <?php if (!isset($val['sign'])) {
                $val['sign'] = 2;
            } ?>
            <li card_id="<?php echo $val['id']; ?>" bid="<?php echo $val['type']; ?>"  mob= "<?php echo substr_replace($val['bank_mobile'], '****', 3, 4); ?>" style="<?php if ($val['sign'] == 1): ?>background:#e7e7e7;<?php endif; ?>position: relative;">
                <img class="cardImg" src="/images/bank_logo/<?php
//                if (!empty($val['bank_abbr']) && in_array($val['bank_abbr'], $bank)) {
//                    echo $val['bank_abbr'];
//                } else {
//                    echo 'ALL';
//                }
                echo!empty($val['bank_abbr']) ? $val['bank_abbr'] : 'ALL';
                ?>.png" width="10%">
                <span class="n26 grey2 cardName"><?php echo $val['bank_name']; ?></span>
                <b class="redLight" style="margin-right: 2%; font-size: 1rem;font-weight: normal;<?php if ($val['sign'] == 1): ?>background:#c7c9d5;<?php endif; ?>"><?php echo $val['type'] == 0 ? '借记卡' : '信用卡'; ?></b>
                <span class="n22 grey4 lastNums">尾号<?php echo substr($val['card'], strlen($val['card']) - 4, 4); ?></span>
                <input type = "hidden" name = "sign_checked" class = "sign_checked" value="<?= $val['sign'] ?>" />
                <?php if ($val['sign'] == 1): ?>
                    <img style="position: absolute;width: 27%;top: 0;right: 2;" src="/images/zanbuzhichi2.png">
            <?php endif; ?>
            </li>
<?php endforeach; ?>                
        </form>
        <p style="border-top:1px solid #c2c2c2; text-align:right; font-size:16px; padding:10px 5%; color:#939ab0; "><a href="/dev/bank/addcard?url=/dev/repay/cards?loan_id=<?php echo $loaninfo['loan_id']; ?>">添加银行卡</a></p>
    </ul>
</div>
<script>
    $(window).load(function () {
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
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
    $('#tbug').bind('click', function () {
        var flag = $('input[name="flag_confirm"]').val();
        var sign = $("#oint").val();
        if (flag == 2) {
            alert("请添加其他银行卡");
            return false;
        }
        if (sign == 1) {
            alert("请选择支持银行卡");
            return false;
        }
        $('form[id="pay"]').submit();
    });


    $('.Hmask').click(function () {
        $('#Hmask').hide();
        $('#ques').hide();
        return false;
    });



</script>
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
    $(function () {
        var bank_num = <?php echo count($userbank) ?>;
//        $('.Hmask').css('display', 'none');
//        $('.layer.highlight').css('display', 'none');
        $('#showbank').click(function () {
//            if (bank_num > 1) {
            $('.Hmask').css('display', 'block');
            $('.layer').css('display', 'block');

//            }
        });
        $(".Hmask").click(function () {
            $(".Hmask").hide();
            $(".layer").hide();
        });

        $('.banksC li').click(function () {
            var cardImg = $(this).find('.cardImg').attr('src');
            var cardName = $(this).find('.cardName').text();
            var redLight = $(this).find('.redLight').text();
            var cardNums = $(this).find('.lastNums').text();
            var sign_checked = $(this).find('.sign_checked').val();
            $("#oint").val(sign_checked);
            cardNums = cardNums.substring(2, 6);
            var bankId = $(this).attr('card_id');
            var mobile = $(this).attr('mob');
            $('.Hmask').css('display', 'none');
            $('.layer').css('display', 'none');
            var html = '';
            html += '<div class="bank2"><img src="' + cardImg + '" width="10%></div>';
            html += '<div class="sendtwo">';
            html += '<p>' + cardName + '<span>' + redLight + '</span></p>';
            html += '<p class="weihaom">尾号 ' + cardNums + '</p>';
            html += '<input type = "hidden" name = "sign_checked_u" class = "sign_checked_u" val = "' + sign_checked + '"/>';
            html += '</div>';
            html += '<img class="rightjt" src="/images/rightjt.png">';
            $('.bank_nn').html(html);
            var lineH = $('.bank_bg.highlight img').height();
            var height2 = $('.col-xs-9').height();
            $('.col-xs-9').css('marginTop', (lineH - height2) / 2);
            $('input[name="card_id"]').val(bankId);
            $('#mobe').html(mobile);
            var bank_type = $(this).attr('bid');
            $("#bank_type").val(bank_type);
        });
    });
</script>