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
        $abbr_url = 'ALL';
    }
    return '/images/bank_logo/' . $abbr . '.png';
}
?>
<style>
    .fukfsies {width: 90%;position: fixed;top: 15%;left: 5%;border-radius: 5px;z-index: 100;background: #fff;}
    .fukfsies .adderr { position: relative; top:0; left:0; border-bottom:1px #c2c2c2 solid; width: 100%; color: #444;  }
    .fukfsies .adderr  img{position: absolute; right:5%; top: 18px; width: 5%;}
    .fukfsies .payerror{display: block; width: 100%; text-align: center; font-size: 1.5rem; padding:30px  0 20px;}
</style>
<div class="haimoney">
    <p class="haititle">应还款金额</p>
    <p class="haitxt"><?php echo sprintf('%.2f', $loan->huankuan_amount); ?> <em>元</em></p>
    <p class="hailast">最后还款日 <em><?php echo $end_date; ?></em></p>
</div>
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
    <div class="gzmess" data-type="2" style="display: none;">
        <div class="ymxinxi" mark='weixin'>
            <p><img src="/images/zfufsi1.png"></p>
            <span>微信支付</span>
            <em></em>
        </div>
    </div>
    <div class="gzmess yumendyu xianxia" data-type="3" data-url="/dev/loan/repay?loan_id=<?php echo $loan['loan_id']; ?>" style="display: none;">
        <div class="ymxinxi" mark="xianxia">
            <p><img src="/images/zfufsi3.png"></p>
            <span class="xinxiazf">线下支付</span>
            <em class="showImg"></em>
        </div>
    </div>
    <input type="hidden" name="channel" value="online" >
    <div class="txtxtexs">
        <div  class="chakan">
            <span>查看更多还款方式 </span>
            <div id="demo12"></div>
        </div>
    </div>
</div>
<div class="jinemes">
    <span>金额<input type="text" placeholder="请输入金额" name = "should_repay" value="<?php echo $loan->huankuan_amount; ?>"></span>
</div>
<div class="button"><button id="submit">确认还款</button></div>
<?php if($user_allow): ?>
    <div class="haikfshi"><a href="/dev/renewal/index?loan_id=<?php echo $loan['loan_id']; ?>" id="demo16">续期还款</a></div>
<?php endif; ?>



<div class="Hmask" style="display: none;"></div>
<form action="/new/repay/payyibao" method="post" class="form-horizontal" role="form" id="repay">
    <input type="hidden" value="<?php echo $loan['loan_id']; ?>" name="loan_id" />
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
                    <div class="bank2"><img id="chekbanksrc" src="<?php echo getImageUrl($banklist[0]['bank_abbr']); ?>" width="10%"></div>
                    <div class="sendtwo" id="bk"><p><?php if($banklist[0]['bank_abbr'] == 'GDB'): echo "广发银行";  else: echo $banklist[0]['bank_name']; endif; ?><span><?php echo $banklist[0]['type'] == 0 ? '借记卡' : '信用卡'; ?></span> 尾号<?php echo substr($banklist[0]['card'], strlen($banklist[0]['card']) - 4, 4); ?></p></div>
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
    <button class="queding" id="is_submit">确认还款</button>
</div>
</form>

<div class="ttfukfsi banklist" hidden>
    <div class="errore">
        <img src="/images/zfufsi4.png">
        <span>选择还款卡</span>
    </div>

    <div class="tuika" style="height: 14rem;overflow:auto">
        <?php foreach ($banklist as $key => $val): ?>
            <a <?php if($val['sign'] == 2): ?>class="check_bank"<?php endif; ?> card_id='<?php echo $val['id'] . "|" . getImageUrl($val['bank_abbr']) . "|" . $val['bank_name'] . "|" . substr($val['card'], strlen($val['card']) - 4, 4) . "|" . $val['type']; ?>' style="<?php if ($val['sign'] == 1): ?>background:#e7e7e7;<?php endif; ?>position: relative;">
                <div class="bank_nn"> 
                    <div class="bank2"><img  src="<?php echo getImageUrl($val['bank_abbr']); ?>" width="10%"></div>
                    <div class="sendtwo"><p><?php if($val['bank_abbr'] == 'GDB'): echo "广发银行";  else: echo $val['bank_name']; endif; ?> <span style="<?php if ($val['sign'] == 1): ?>background:#c7c9d5;<?php endif; ?>"><?php echo $val['type'] == 0 ? '借记卡' : '信用卡'; ?></span> <em>尾号<?php echo substr($val['card'], strlen($val['card']) - 4, 4); ?></em></p> </div>
                    <?php if ($val['sign'] == 1): ?>
                        <img style="position: absolute;width: 27%;top: 0;right: 2;" src="/images/zanbuzhichi2.png">
                    <?php endif; ?>
                </div>
            </a>
        <?php endforeach; ?> 
        </form>
    </div>
    <a class="addbank" <?php if($bank_count >= 10): ?>onclick="alert('绑定银行卡已超过10张卡');return false;"<?php else: ?>href="/dev/bank/addcard?user_id=<?php echo $loan->user_id; ?>"<?php endif; ?>><img src="/images/addadd.png"> <span>添加新银行卡</span></a>
</div>
<!--支付失败弹层-->
    <div style="display:none"  id="errorLayer" class="fukfsies">
        <div class="adderr close_error_layer">
            <img src="/images/zfufsi4.png">
        </div>
        <p class="payerror">支付失败！</p>
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

    wx.ready(function() {
        wx.hideOptionMenu();
    });
    $(function() {
        var loan_id = <?php echo $loan['loan_id']; ?>;
            $('#is_submit').on("click","button",function() {
                $("#is_submit").attr('disabled', true);
                var card_id = $('input[name="card_id"]').attr('value');
                var money_order_repay = $("input[name='should_repay']").val();
                var money_order = (Number(money_order_repay)).toFixed(2);

                if(!money_order || money_order == 0 || money_order < 0){
                    alert("请输入大于0.00的还款金额");
                    $("#is_submit").attr('disabled', false);
                    return false;
                }
                $('form[id="repay"]').submit();
            });

    });
    $(".chakan").click(function() {
        $(".gzmess").show();
        $(".txtxtexs").hide();
    });
    $(".gzmess").click(function() {
        var checkmark = $(this).children().attr("mark");
        $(".ymxinxi").each(function() {
            if ($(this).attr("mark") === checkmark) {
                $(this).children('em').html('<img src="/images/zfufsi5.png">');
                $("input[name='channel']").attr('value', checkmark);
            } else {
                $(this).children('em').html('');
            }
        });
    });
    $('#submit').click(function() {
        var flag = <?php echo $flag; ?>;
        var money_order_repay = $.trim($("input[name='should_repay']").val());
        var money = (Number(money_order_repay)).toFixed(2);
        if(isNaN(money)){
            alert("请输入正确金额");
            return false;
        }
        if(!money || money == 0){
            alert("请输入大于0.00的还款金额");
            return false;
        }
        var money_str = money+"<em>元</em>";
        $('.should_repay_money').html(money_str);
        $('.money_order').val(money);
        var channel = $("input[name='channel']").attr('value');
        if (channel === 'weixin') {
            var url = '/new/yyygzhpay/submitorderinfo';
            var loan_id = <?php echo intval($loan['loan_id']) ?>;
            var csrf = '<?php echo $csrf;?>';
            $.ajax({
                type: "POST",
                url: url,
                dataType: 'json',
                data: {_csrf:csrf, 'loan_id':loan_id, 'money':money},
                success: function(msg){
//                    alert(msg);
//                    return false;
                    if(msg.status==0){
                        location.href=msg.url;
                    }else{
//                        self.attr("disabled" , false);
                        alert("操作失败");
//                        $(".payerror").html(msg.msg);
//                        $(".Hmask").show();
//                        $("#errorLayer").show();
                        return false;
                    }
                },
            });
        } else if (channel === 'online') {
            $('.Hmask').show();
            if(flag == 1){
                $('.checkcard').show();
            } else {
                $('.banklist').show();
            }
        } else if (channel === 'xianxia'){
            var url = $('.xianxia').attr('data-url');
            location.href = url;
        }
    });
    $(".check_bank").on("click",function() {
        var card_id = $(this).attr('card_id');
        var arr = card_id.split('|');
        console.dir(arr);
        $('#chekbanksrc').attr('src', arr[1]);
        var bank_type = arr[4] == 0 ? '借记卡' : '信用卡';
        var html = '<p>' + arr[2] + '<span>' + bank_type + '</span> 尾号' + arr[3] + '</p>';
        $('#bk').html(html);
        $('input[name="card_id"]').attr('value', arr[0]);
        $('.banklist').hide();
        $('.checkcard').show();
    });
    $('.rightjt').click(function() {
        $('.checkcard').hide();
        $('.banklist').show();
    });
    $(".ttfukfsi .errore img").click(function() {
        $('.Hmask').hide();
        $('.ttfukfsi').hide();
    });
    $('.Hmask').click(function(){
        $('.Hmask').hide();
        $('.ttfukfsi').hide();
        $("#errorLayer").hide();
    });
    //关闭支付失败弹层
    $(".close_error_layer").click(function(){
        $(".Hmask").hide();
        $("#errorLayer").hide();
        $(".payerror").html("支付失败！");
    })
</script>
