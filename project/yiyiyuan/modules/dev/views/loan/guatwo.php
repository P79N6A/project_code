<div class="Hcontainer nP">
    <header class="header white">
        <p class="n26">状态：</p>
        <p class="n36 mb20 text-center mt20">已成功为您匹配一名同校担保人</p>
    </header>
    <img src="/images/title.png" width="100%"/>
    <div class="con mb40">
            <div class="details">
                <div class="adver1">
                    <div class="row mb30">
                        <div class="col-xs-4 cor n26 nPad">借款用途：</div>
                        <div class="col-xs-8 text-right n30 nPad bold"><?php echo $desc; ?></div>
                        <input type="hidden" name="desc" value="<?php echo $desc; ?>" />
                        <input type="hidden" name="guater_id" value="<?php echo $guater_id; ?>"/>
                    </div>
                    <div class="row mb20">
                        <div class="col-xs-4 cor n26 nPad">借款期限(天)：</div>
                        <div class="col-xs-8 text-right n30 nPad"><span class="red"><?php echo $days; ?></span>天</div>
                        <input type="hidden" name="days" value="<?php echo $days; ?>"/>
                    </div>
                    <div class="row mb10">
                        <div class="col-xs-4 cor n26 nPad">借款金额(元)：</div>
                        <div class="col-xs-8 text-right n30 nPad"><span class="red n44">&yen;<?php echo $amount; ?></span></div>
                        <input type="hidden" name="amount" value="<?php echo $amount; ?>" />
                    </div>
                    <div class="row mb30">
                        <div class="col-xs-4 cor n26 nPad">应还金额(元)：</div>
                        <div class="col-xs-8 text-right n30 nPad"><span class="red">&yen;<?php echo sprintf('%.2f', $money); ?></span></div>
                    </div>
                    <div class="row mb30">
                        <div class="col-xs-4 cor n26 nPad">出款卡：</div>
                        <div class="col-xs-8 text-right n30 nPad"><?php echo substr($card->card, 0, 4); ?>********<?php echo substr($card->card, strlen($card->card) - 4, 4); ?></div>
                        <input type="hidden" name="card_id" value="<?php echo $card->id; ?>" />
                    </div>
                </div>
            </div>
            <img src="/images/bottom.png" width="100%" style="vertical-align:top"/>
            <input type="hidden" id="user_id" value="<?php echo $user_id;?>"/>
            <button class="btn mt20" id="loan_gua" style="width:100%">发起借款</button>
    </div>       
</div>

<script>
    $("#loan_gua").click(function() {
        var desc = $("input[name='desc']").val();
        var days = $("input[name='days']").val();
        var amount = $("input[name='amount']").val();
        var card_id = $("input[name='card_id']").val();
        var guater_id = $("input[name='guater_id']").val();
        var user_id = $("#user_id").val();
        $("#loan_confirm").attr('disabled', true);
        $.get("/dev/st/statisticssave", { type: 28,user_id:user_id },function(data){
        	
        });
        $.post("/dev/loan/guaconfirm", {desc: desc, days: days, amount: amount, card_id: card_id, guater_id: guater_id}, function(result) {
            var data = eval("(" + result + ")");
            //alert(data);
            if (data.ret == '3')
            {
                $("#loan_confirm").attr('disabled', false);
            }
            else if (data.ret == '4')
            {
                $("#loan_confirm").attr('disabled', false);
                alert('您不能重复借款');
            }
            else if (data.ret == '5')
            {
                $("#loan_confirm").attr('disabled', false);
                alert('您已被驳回，请先去上传自拍照');
            }
            else if (data.ret == '6')
            {
                $("#loan_confirm").attr('disabled', false);
                alert('您提交的信息不符合规则，该账户已被冻结');
            }
            window.location = data.url;
        });
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
            'hideOptionMenu'
        ]
    });

    wx.ready(function() {
        wx.hideOptionMenu();
    });
</script>