<div class="faqtx">
    <div class="disitems disste">
        <div class="disitem_first">提现现金：</div>
        <div class="disitem_two"><input type="text" name="outincome" id="outincome"></div>
        <div class="disitem_three">RMB</div>
    </div>
    <div class="disitems disitem_txt">*100元起提，单日限额2500元、单月限额5000元</div>
    <div class="disitems">
        <div class="disitem_first">银行卡：</div>
        <div class="disitem disitem_two">
            <?php if (!empty($user_bank)): ?> 
                <select style='flex:1;-webkit-box-flex: 1;-webkit-flex: 1; height:30px;' id='outbank'>
                    <?php foreach ($user_bank as $key => $v): ?>
                        <option value='<?php echo $v->id; ?>'><?php echo $v->card; ?></option>
                    <?php endforeach; ?>
                </select>
            <?php else: ?>
                <a href="/dev/bank/addcard" style="color:red">请先绑定银行卡</a>
            <?php endif; ?> 
        </div>
    </div>
    <input type="hidden" name="userId" value="<?php echo $user_id; ?>" id="userId" />
    <div class="disitems">
        <div class="disitem_first">手续费：</div>
        <div class="disitem_four"></div>
        <div class="disitem_five"><span id='hlz'>0.00</span>RMB</div>
    </div>
</div>
<div class="disitem txijl_jikl">
    <div class="kongde"></div>
    <div class="disitem txijl_txjl ">
        <img src="/images/txjl.png">
        <span><a style="padding-right:3px;" href='/background/receive/withlist'>提现记录</a></span>
    </div>

</div>
<div style="height:20px;color:#e74747; margin-left:3%;">
    <p class='sytx_txt'></p>
</div>


<div class="disitem" style="margin-top:20px;">
    <button class="button_anniu" id='incomewd' obst="<?php echo $limitStatus; ?>" obig="<?php echo!empty($accountinfo) ? sprintf('%.2f', intval(($accountinfo->total_history_interest - $accountinfo->total_on_interest) * 100) / 100) : number_format(0.00, 2, ".", ""); ?>" >确 认</button>

</div>
<script>
    $('.nav_right').click(function () {
        window.location.href = '<?php echo $returnUrl ?>';
    })
</script>
<script>
    $('#incomewd').click(function () {
        var outincome = $("#outincome").val();
        var outbig = $(this).attr("obig");
        var outstatus = $(this).attr("obst");
        var user_id = $("#userId").val();
        var bank_id = $("#outbank").val();
        var shouxf = parseInt($("#hlz").html());
        var chkamount = /^(([1-9]\d*)|0)(\.\d{1,2})?$/;
        if (parseFloat(outbig) > 2500) {
            outbig = 2500.00;
        }
        if (outincome == null || outincome == '') {
            $(".sytx_txt").text("请输入要提现的额度");
            return false;
        } else if (outincome < 100) {
            $(".sytx_txt").text("当收益满100.00元后，即可提现！");
            return false;
        } else if (parseFloat(outincome) > parseFloat(outbig)) {
            $(".sytx_txt").text("最多可提现" + outbig);
            return false;
        }
        if (!chkamount.test(outincome)) {
            $(".sytx_txt").text("请输入正确的提现金额");
            return false;
        }
        ;
        if (!bank_id) {
            $(".sytx_txt").text("请添加银行卡");
            return false;
        }

        if (outstatus == '1' || outstatus == '3') {

            $(".sytx_txt").html("由于您的征信记录有瑕疵，暂不可申请提现；");

            return false;
        } else if (outstatus == '2') {

            $(".sytx_txt").html("由于您当前有逾期借款，暂不可申请提现；");

            return false;
        } else if (outstatus == '4') {
            $(".sytx_txt").html("受春节期间（2月5日－2月15日）银行系统影响，收益提现功能暂停服务，敬请谅解；");
            $("#withdraw_error").html('');
            return false;
        }

        $(this).attr('disabled', true);
        $.post("/background/receive/outincome", {user_id: user_id, outincome: outincome, bank_id: bank_id, shouxf: shouxf}, function (result) {
            var data = eval("(" + result + ")");
            //alert(data.ret);exit;
            $("#incomewd").attr('disabled', false);
            if (data.ret == 0) {
                alert('提现成功');
                window.location.href = "/background/wallet/";
            } else {
                $(".sytx_txt").text(data.msg);
            }
        });

    });

    $("#outincome").keyup(function () {
        var input_amount = $(this).val();
        var regamount = /^[1-9]*[1-9][0-9]*$/;
        input_amount = Math.ceil(input_amount * 10) / 10;
        var profit = Math.ceil(input_amount) * 0.01;
        if (profit <= 2) {
            profit = 2.00;
        } else if (profit >= 20) {
            profit = 20.00;
        }
        $("#hlz").html(profit.toFixed(2));
    });
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
            'closeWindow',
            'hideOptionMenu'
        ]
    });

    wx.ready(function () {
        wx.hideOptionMenu();
    });
</script>