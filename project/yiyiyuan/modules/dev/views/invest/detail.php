<script language="javascript">
    function codefans() {
        var box = document.getElementById("investaccount");
        if (box != null) {
            box.style.display = "none";
        }
    }
    var current_amount = <?php echo $current_amount; ?>;
    if (parseFloat(current_amount) > 0) {
        setTimeout("codefans()", 3000);//3秒 
    }
    var canInvest = <?php echo intval($canInvest); ?>;
    var rate = <?php echo Yii::$app->params['rate']; ?>;
    var invest_days = <?php echo $loaninfo['days']; ?>;
    var loan_id = <?php echo $loaninfo['loan_id']; ?>;
    $(function () {
        $("#inAll").click(function () {
            $('#input_amount').val(canInvest);
            if (Number(canInvest) > 0) {
                $('#invest_confirm').attr('disabled', false);
                //计算预计收益
                var profit = (Number(canInvest) * (rate / 100) / 365) * invest_days;
                $("#profit").html(profit.toFixed(2));
            }
        });
        $('#input_amount').keyup(function () {
            var input_amount = Number($('#input_amount').val());
            if (input_amount > Number(canInvest)) {
                $('#input_amount').val(canInvest);
                input_amount = Number($('#input_amount').val());
            }
            if (input_amount > 0) {
                $('#invest_confirm').attr('disabled', false);
                //计算预计收益
                var profit = (input_amount * (rate / 100) / 365) * invest_days;
                $("#profit").html(profit.toFixed(2));
            } else {
                $('#invest_confirm').attr('disabled', true);
            }

        });

        $("#invest_confirm").click(function () {
            var invest_amount = $('#invest_amount').val();
            var input_amount = $('#input_amount').val();
            var regamount = /^[1-9]*[1-9][0-9]*$/;
            if (input_amount == '' || input_amount == null)
            {
                alert('请输入投资金额');
                return false;
            }
            if (!regamount.test(input_amount))
            {
                alert('投资金额必须是整数');
                return false;
            }
            if (parseInt(input_amount) > parseInt(invest_amount))
            {
                alert('输入的投资金额不能大于可投资金额');
                return false;
            }
            $("#invest_confirm").attr('disabled', true);
            $.post("/dev/invest/addsave", {loan_id: loan_id, input_amount: input_amount}, function (data) {
                if (data == 'fail')
                {
                    alert('投资失败')
                    $("#invest_confirm").attr('disabled', false);
                    return false;
                }
                else if (data == 'moreamount')
                {
                    alert('输入的投资金额多于未筹满的额度');
                    $("#invest_confirm").attr('disabled', false);
                    return false;
                }
                else if (data == 'morethree')
                {
                    alert('投资金额过大');
                    $("#invest_confirm").attr('disabled', false);
                    return false;
                }
                else if (data == 'moresecond')
                {
                    alert('投资金额过大');
                    $("#invest_confirm").attr('disabled', false);
                    return false;
                }
                else
                {
                    window.location = "/dev/invest/success?invest_id=" + data;
                }
            });
            //window.location = "/dev/invest/success";
        });
        $('#invest_xianhua').click(function () {
            $.post("/dev/invest/click", {loan_id: loan_id}, function (data) {
                var result = eval('(' + data + ')');
                
                if (result['num'] != 0)
                {
                    $('.firendhelp').html('<span  id="investaccount">已帮好友筹款'+result['num']+'元</span>');
                    $("#invest_xianhua").attr('disabled', true);
                }
            });
        });
    })
</script> 
<div class="touzita">
    <div class="taone">预计收益 <span id="profit"> 0 </span>元</div>
    <p class="tatwo">可投资金额：<?php echo intval($canInvest); ?>点</p>
    <div class="dbk_inpL bjys">
        <label>投资金额</label>
        <input id="input_amount" type="text" maxlength="9" placeholder="输入投资金额"/>
        <span id="inAll">全部投资</span>
    </div>
    <p class="firendhelp" style="height: 2rem;">
        <?php if ($investaccount > 0): ?>
            <span  id="investaccount">已帮好友筹款<?php echo round($investaccount, 2); ?>元</span>
        <?php endif; ?>
    </p>
    <div class="tathree"><img src="/images/account/duidui.png"><span>同意<em><a href="/dev/invest/agreement?loan_id=<?php echo $loaninfo['loan_id']; ?>">《先花一亿元居间协议》</a></em></span></div>
    <!--div class="button"> <button>点赞帮他</button></div-->
    <div class="button"> 
        <?php if ($current_amount > 0): ?>
            <button class="btn dis" id="invest_confirm" disabled="disabled">投资</button>
        <?php else: ?>
            <button class="btn dis" id="invest_xianhua" <?php if ($investaccount > 0): ?>disabled="disabled"<?php endif; ?>>点赞帮他</button>
        <?php endif; ?>
    </div>
    <div class="tafour">
        <!--h3>详情</h3-->
        <div class="tafour1">
            <span class="tafour1_one">借款：<?php echo sprintf("%.2f", $loaninfo['amount']); ?> 元</span>
            <span class="tafour1_two">还差：<?php echo sprintf("%.2f", ($loaninfo['amount'] - $loaninfo['current_amount'])); ?> 元</span>
        </div>
        <div class="tafour2">
            <p class="tafour2_one"><span>借款周期</span><br/><?php echo $loaninfo['days']; ?>天</p>
            <p class="tafour2_two"><span>年化收益率</span><br/><?php echo Yii::$app->params['rate']; ?>%</p>
            <p class="tafour2_three"><span>熟人关系</span><br/><?php echo $relation; ?></p>
        </div>
        <div class="tafour3">
            <p class="redred">借款理由</p>
            <p><?php echo $loaninfo['desc']; ?></p>
        </div>
        <div class="tafour3">
            <p class="redred">借款人信息</p>            
            <?php if (!empty($loaninfo['school'])): ?><p>学校：<?php echo $loaninfo['school']; ?></p><?php endif; ?>
            <?php if (!empty($loaninfo['edu'])): ?><p>学历：<?php if ($loaninfo['edu'] == 1): ?>博士研究生<?php elseif ($loaninfo['edu'] == 2): ?>硕士研究生<?php elseif ($loaninfo['edu'] == 3): ?>本科<?php else: ?>专科<?php endif; ?></p><?php endif; ?>
            <p>年龄：<?php echo $age; ?>岁</p>
            <?php if (!empty($loaninfo['company'])): ?><p>公司：<?php echo $loaninfo['company']; ?></p><?php endif; ?>
            <?php if (!empty($loaninfo['telephone'])): ?><p>电话：<?php echo $loaninfo['telephone']; ?></p><?php endif; ?>
            <?php if (!empty($loaninfo['address'])): ?><p>地址：<?php echo $loaninfo['address']; ?></p><?php endif; ?>
        </div>
        <div class="tafour4">
            <div class="redred">认证信息</div>
            <p class="tafour2_one"><img src="/images/account/fafour1.png"><br/>手机认证</p>
            <p class="tafour2_two"><img src="/images/account/fafour2.png"><br/>身份认证</p>
            <p class="tafour2_three"><img src="/images/account/fafour3.png"><br/>学籍认证</p>
            <p class="tafour2_four"><img src="/images/account/fafour4.png"><br/>关系认证</p>

        </div>
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