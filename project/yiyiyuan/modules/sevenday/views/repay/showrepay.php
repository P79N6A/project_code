<?php
$bank = array('ABC', 'ALL', 'BCCB', 'BCM', 'BOC', 'CCB', 'CEB', 'CIB', 'CMB', 'CMBC', 'GDB', 'HXB', 'ICBC', 'PAB', 'PSBC', 'SPDB', 'ECITIC');
?>
<div class="hluan_bg ">
    <img class="bgbg_dwen" src="/sevenday/images/bgbg_dwen.png">
    <div class="xinxiye">
        <h3>应还款金额</h3>
        <p><span>¥</span><?php echo sprintf('%.2f', $repayment); ?></p>
        <div class="zhkrq"><span>最后还款日期</span><i><?php echo date('Y-m-d', strtotime($user_loan->end_date) - 86400); ?></i></div>
    </div>
</div>
<div class="zhifufshichoess">
    <span>支付方式选择</span>
</div>
<div class="fukfsi">
    <div class="gzmess yumendyu" onclick="opBank()">
        <div class="ymxinxi">
            <p><img src="/sevenday/images/zfufsi2.png"></p>
            <span>银行卡支付</span>
            <em><img id="select_bank" src="/sevenday/images/nonexz.png"></em>
        </div>
    </div>
    <!--    <div class="gzmess yumendyu" onclick="opAlipay()">-->
    <!--        <div class="ymxinxi">-->
    <!--            <p><img src="/sevenday/images/zfufsi3.png"></p>-->
    <!--            <span>支付宝</span>-->
    <!--            <em><img id="select_alipay" src="/sevenday/images/nonexz2.png"></em>-->
    <!--        </div>-->
    <!--    </div>-->
    <div class="gzmess yumendyu" onclick="underPay()">
        <div class="ymxinxi">
            <p><img src="/sevenday/images/underpay.png"></p>
            <span>线下还款</span>
            <em><img id="select_underpay" src="/sevenday/images/nonexz.png"></em>
        </div>
    </div>
</div>
<div class="buttonyi">
    <button onclick="doRepay()" id="do_repay">立即还款</button>
</div>
<div class="Hmask" hidden></div>
<div class="ttfukfsi" id="bankbox" hidden>
    <img class="errorxx" src="/sevenday/images/errorxx.png" onclick="closeBox()" style="z-index: 999;">
    <div class="errore">
        <span>选择出款卡</span>
    </div>
    <div class="tuika">
        <?php if (!empty($user_bank_arr)): ?>
            <?php foreach ($user_bank_arr as $key => $val): ?>
                <a href="javascript:void(0);" onclick="closeBank('<?php echo $val->id; ?>')">
                    <div class="bank_nn">
                        <div class="bank2"><img  src="/images/bank_logo/<?php
                            if (!empty($val['bank_abbr']) && in_array($val['bank_abbr'], $bank)) {
                                echo $val['bank_abbr'];
                            } else {
                                echo 'ICON';
                            }
                            ?>.png" width="10%"></div>
                        <div class="sendtwo"><?php echo!empty($val['bank_name']) ? $val['bank_name'] : ''; ?>储蓄卡(<?php echo substr($val['card'], strlen($val['card']) - 4, 4) ?>) </div>

                        <div class="yes"><img src="/sevenday/images/<?php if ($user_bank['id'] == $val->id): ?>yes.png<?php else: ?>nonexz.png<?php endif; ?>"></div>
                    </div>
                </a>
                <a><?php echo $user_bank->id; ?></a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <button class="addbank" onclick="addBank()"><img src="/sevenday/images/addbank.png"><span>添加银行卡</span></button>
</div>
<div class="ttfukfsi" id="alipaybox" hidden>
    <div class="errore">
        <img src="/sevenday/images/errorxx.png" style="z-index: 999;" onclick="closeBox()">
        <span>支付</span>
    </div>
    <div class="haimoneys">
        <p class="haitxts"><?php echo sprintf('%.2f', $repayment); ?> <em>元</em></p>
    </div>
    <div class="tuikas">
        <a href="javascript:void(0);">
            <div class="bank_nn">
                <div class="bank2"><img  src="/images/bank_logo/<?php
                    if (!empty($user_bank['bank_abbr']) && in_array($user_bank['bank_abbr'], $bank)) {
                        echo $user_bank['bank_abbr'];
                    } else {
                        echo 'ICON';
                    }
                    ?>.png" width="10%"></div>
                <div class="sendtwo"><?php echo!empty($user_bank['bank_name']) ? $user_bank['bank_name'] : ''; ?>储蓄卡(<?php echo substr($user_bank['card'], strlen($user_bank['card']) - 4, 4) ?>) </div>
            </div>
        </a>
    </div>
    <div class="buttonyi" style="margin-bottom: 20px;">
        <button onclick="doBandRepay()" id="do_bank_repay">确定</button>
    </div>
</div>
<div class="tishi_success" id="divbox" style="display: none;"><a class="tishi_text">获取额度失败</a></div>
<input type="hidden" id="csrf" value="<?php echo $csrf; ?>">
<input type="hidden" id="bank_id" value="<?php echo $user_bank['id']; ?>">
<input type="hidden" id="loan_id" value="<?php echo $user_loan->loan_id; ?>">
<input type="hidden" id="type" value="1">
<script src="/js/alipay/callalipay.js"></script>
<script type="text/javascript">
            var csrf = $('#csrf').val();
            var type = $("#type").val();
            if (type == 1) {
                $('#select_bank').attr('src', '/sevenday/images/nonexz2.png');
                $('#select_alipay').attr('src', '/sevenday/images/nonexz.png');
                $('#select_underpay').attr('src', '/sevenday/images/nonexz.png');
            } else if (type = 3) {
                $('#select_underpay').attr('src', '/sevenday/images/nonexz2.png');
                $('#select_alipay').attr('src', '/sevenday/images/nonexz.png');
                $('#select_bank').attr('src', '/sevenday/images/nonexz.png');
            } else {
                $('#select_bank').attr('src', '/sevenday/images/nonexz.png');
                $('#select_underpay').attr('src', '/sevenday/images/nonexz.png');
                $('#select_alipay').attr('src', '/sevenday/images/nonexz2.png');
            }

            //还款
            function doRepay() {
                var loan_id = $("#loan_id").val();
                $("#do_repay").attr('disabled', true);
                var type = $("#type").val();
                if (type == 1) {
                    $("#do_repay").attr('disabled', false);
                    $('.Hmask').show();
                    $('#alipaybox').show();
                } else if (type == 3) {
                    location.href = '/day/repay/underline';
                } else {
                    var bank_id = $("#bank_id").val();
                    $.ajax({
                        type: "POST",
                        url: "/day/repay/dorepay",
                        data: {_csrf: csrf, bank_id: bank_id, type: type, loan_id: loan_id},
                        success: function (result) {
                            result = eval('(' + result + ')');
                            if (result.rsp_code == '0000') {
                                callappjs.callAlipay(result.url);
//                        location.href = result.url;
                            } else {
                                $("#do_repay").attr('disabled', false);
                                $('.tishi_text').html(result.rsp_msg);
                                $('.tishi_success').show();
                            }
                        }
                    });
                }
            }

            //银行卡还款
            function doBandRepay() {
                $("#do_bank_repay").attr('disabled', true);
                var type = $("#type").val();
                var bank_id = $("#bank_id").val();
                var loan_id = $("#loan_id").val();
                $.ajax({
                    type: "POST",
                    url: "/day/repay/dorepay",
                    data: {_csrf: csrf, bank_id: bank_id, type: type, loan_id: loan_id},
                    success: function (result) {
                        result = eval('(' + result + ')');
                        if (result.rsp_code == '0000') {
                            location.href = result.url;
                        } else {
                            $("#do_bank_repay").attr('disabled', false);
                            $('.tishi_text').html(result.rsp_msg);
                            $('.tishi_success').show();
                        }
                    }
                });
            }

            //选择银行卡
            function opBank() {
                $("#type").val(1);
                $('#select_bank').attr('src', '/sevenday/images/nonexz2.png');
                $('#select_alipay').attr('src', '/sevenday/images/nonexz.png');
                $('#select_underpay').attr('src', '/sevenday/images/nonexz.png');
                $('.Hmask').show();
                $('#bankbox').show();
            }

            //关闭银行卡弹窗
            function closeBank(bank_id) {
                location.href = '/day/repay/showrepay?bank_id=' + bank_id;
            }

            //新增银行卡
            function addBank() {
                location.href = '/day/userbank/index';
            }

            //选择支付宝支付
            function opAlipay() {
                $("#type").val(2);
                $('#select_bank').attr('src', '/sevenday/images/nonexz.png');
                $('#select_alipay').attr('src', '/sevenday/images/nonexz2.png');
                $('#select_underpay').attr('src', '/sevenday/images/nonexz.png');
            }

            function underPay() {
                $("#type").val(3);
                $('#select_bank').attr('src', '/sevenday/images/nonexz.png');
                $('#select_alipay').attr('src', '/sevenday/images/nonexz.png');
                $('#select_underpay').attr('src', '/sevenday/images/nonexz2.png');
            }

            //关闭弹窗
            function closeBox() {
                $('.Hmask').hide();
                $('#bankbox').hide();
                $('#alipaybox').hide();
            }
</script>