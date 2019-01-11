<?php
$bank = array('ABC', 'ALL', 'BCCB', 'BCM', 'BOC', 'CCB', 'CEB', 'CIB', 'CMB', 'CMBC', 'GDB', 'HXB', 'ICBC', 'PAB', 'PSBC', 'SPDB', 'ECITIC');
?>
<style>

    .alert-box{
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,.4);
        position: fixed;
        top: 0;
        left: 0;
        z-index: 9;
    }
    .err-box{
        width: 70%;
        background: #fff;
        margin: 50% auto;
        border-radius: 5px;
        padding: 4vw;
    }
    .err-box h4{
        text-align: center;
        font-size: 4vw;
        font-weight: bold;
        padding: 0 3vw 2vw;
    }
    .err-box p{
        font-size: 3.6vw;
        color: #444;
    }
</style>

<div class="alert-box" style="display:none;">    
    <div class="err-box">
        <h4>提醒</h4>
        <p class="txtext">反馈错误，请重新点击反馈错误，请重新点击反馈错误，请重新点击反馈错误，请重新点击反馈错误，请重新点击</p>
    </div>
</div>

<div class="touzita">
    <div class="bjys">
        <label>借款金额</label>
        <span><em><?php echo $amount; ?>元</em></span>
    </div>
    <div class="bjys">
        <label>借款周期</label>
        <span><em><?php echo $day; ?>天</em></span>
    </div>
    <div class="bjys">
        <label>实际到账</label>
        <span><?php echo $actual_amount; ?></span>
    </div>
    <div class="bjys">
        <label>服务费</label>
        <span><?php echo $withdraw_fee; ?>元</span>
    </div>
    <div class="bjys">
        <label>利息</label>
        <span><?php echo $interest_fee; ?>元</span>
    </div>
    <div class="bjys">
        <label>优惠券减免</label>
        <span><?php echo $coupon_amount; ?>元</span>
    </div>
</div>

<div class="banks">
    <ul>
        <a href="javascript:void(0);" onclick="selectBank()">
            <li class="border_top_2">
                <div class="icon_bank"><img src="/images/bank_logo/<?php
                    if (!empty($user_bank['bank_abbr']) && in_array($user_bank['bank_abbr'], $bank)) {
                        echo $user_bank['bank_abbr'];
                    } else {
                        echo 'ICON';
                    }
                    ?>.png"></div>
                <div class="bank_cont">
                    <span class="bankN"><?php echo!empty($user_bank['bank_name']) ? $user_bank['bank_name'] : '银行卡'; ?> <i class="redLight">借记卡</i></span>
                    <p class="grey4">尾号<?php echo substr($user_bank['card'], strlen($user_bank['card']) - 4, 4) ?></p>
                </div>
                <div class="text-right"><img src="/sevenday/images/arrowGrey.png"></div>
            </li>
        </a>
    </ul>
</div>
<div class="argeehd">
    <input type="checkbox" checked="checked" id="checkbox-1" disabled="" class="regular-checkbox">
    <label for="checkbox-1"></label>我已阅读并同意 <a href="javascript:void(0);" class="underL" onclick="doAgreement()">《借款协议》</a>
</div>
<div class="buttonyi"> <button onclick="doLoan()" id="do_loan">立即借款</button></div>
<div class="Hmask" hidden></div>
<div class="ttfukfsi" hidden>
    <div class="errore">
        <span>选择出款卡</span>
    </div>
    <div class="tuika" >
        <?php if (!empty($user_bank_arr)): ?>
            <?php foreach ($user_bank_arr as $key => $val): ?>
                <a href="javascript:void(0);" onclick="closeBank('<?php echo $val->id; ?>')">
                    <div class="bank_nn">
                        <div class="bank2"><img  src="/images/bank_logo/<?php if (!empty($val['bank_abbr']) && in_array($val['bank_abbr'], $bank)) {
            echo $val['bank_abbr'];
        } else {
            echo 'ICON';
        } ?>.png" width="10%"></div>
                        <div class="sendtwo"><?php echo!empty($val['bank_name']) ? $val['bank_name'] : ''; ?>储蓄卡(<?php echo substr($val['card'], strlen($val['card']) - 4, 4) ?>) </div>
                        <div class="yes"><img src="/sevenday/images/<?php if ($user_bank->id == $val->id): ?>yes.png<?php else: ?>nonexz.png<?php endif; ?>"></div>
                    </div>
                </a>
    <?php endforeach; ?>
<?php endif; ?>
    </div>
    <button class="addbank" onclick="addBank()"><img src="/sevenday/images/addbank.png"><span>添加银行卡</span></button>
</div>
<input type="hidden" id="csrf" value="<?php echo $csrf; ?>">
<input type="hidden" id="bank_id" value="<?php echo $user_bank['id']; ?>">
<script type="text/javascript">
    var csrf = $('#csrf').val();
    //选择银行卡
    function selectBank() {
        $('.Hmask').show();
        $('.ttfukfsi').show();
    }

    //关闭银行卡弹窗
    function closeBank(bank_id) {
        location.href = '/day/loan/confirm?bank_id=' + bank_id;
    }

    //借款协议
    function doAgreement() {
        location.href = '/day/agreeloan/loan';
    }

    //新增银行卡
    function addBank() {
        location.href = '/day/userbank/index';
    }
    $('.alert-box').click(function(){
        $('.alert-box').hide();
    });

    //提交借款
    function doLoan() {
        $("#do_loan").attr('disabled', true);
        var bank_id = $("#bank_id").val();
        $.ajax({
            type: "POST",
            url: "/day/loan/createloan",
            data: {_csrf: csrf, bank_id: bank_id},
            success: function (result) {
                result = eval('(' + result + ')');
                if (result.rsp_code == '0000') {
                    zhuge.identify(<?php echo $user_id; ?>, {//用户ID
                        已申请借款: 1,
                    });
                    location.href = result.url;
                } else {
                    $("#do_loan").attr('disabled', false);
                    $('.txtext').html(result.rsp_msg);
                    $('.alert-box').show();
                }
            }
        });
    }
</script>
