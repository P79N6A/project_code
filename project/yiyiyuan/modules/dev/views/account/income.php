<div class="Investment_record">
    <div class="record_title">
        <p class="record_titimg"></p>
        <div class="recotit_left">
            <div>
                <p class="red"><span><?php echo sprintf("%.2f", $remainIncome); ?></span>元</p>
                <p>可兑换收益</p>
            </div>
        </div>
        <div class="recotit_right">
            <div>
                <p class="red"><span><?php echo sprintf("%.2f", $totalIncome); ?></span>元</p>
                <p>累计总收益</p>
            </div>
        </div>
    </div>
    <div class="record_title money_tx" id="change" >兑换</div>
    <div class="txt_content">
        <a class="link_click" href="/dev/account/incomest?user_id=<?php echo $userId; ?>"><div class="record_content">
                <div class="content_left">
                    <dl>
                        <dt><img src="/images/tx_one.png"></dt>
                        <dd>信用理财收益</dd>
                    </dl>
                </div>
                <div class="content_right"><img src="/images/jiantou.png"></div>
            </div></a>
        <a class="link_click" href="/dev/account/incomefr?user_id=<?php echo $userId; ?>"><div class="record_content">
                <div class="content_left">
                    <dl>
                        <dt><img src="/images/tx_two.png"></dt>
                        <dd>投资好友收益</dd>
                    </dl>
                </div>
                <div class="content_right"><img src="/images/jiantou.png"></div>
            </div></a>
        <a class="link_click" href="/dev/account/incomexh?user_id=<?php echo $userId; ?>"><div class="record_content">
                <div class="content_left">
                    <dl>
                        <dt class="tx_three"><img src="/images/tx_three.png"></dt>
                        <dd>投资先花宝收益</dd>
                    </dl>
                </div>
                <div class="content_right"><img src="/images/jiantou.png"></div>
            </div>
        </a>
        <a class="link_click" href="/dev/account/redpackets?user_id=<?php echo $userId; ?>"><div class="record_content">
                <div class="content_left">
                    <dl>
                        <dt class="tx_three"><img src="/images/tx_three.png"></dt>
                        <dd>认证红包收益</dd>
                    </dl>
                </div>
                <div class="content_right"><img src="/images/jiantou.png"></div>
            </div>
        </a>
    </div>
</div>
<div class="Hmask" style="display: none;"></div>
<div class="duihsucc3" style="display: none;" id="one">
    <p class="errore" id="errore1"><img src="/images/closed.png"></p>
    <p class="yiyyuan">一亿元收益<span><?php echo sprintf("%.2f", $remainIncome); ?></span>可兑换花生米富</p>
    <p> <span>现金红包<?php echo $repacket_num; ?>元现金红包，</span>确认兑换？</p>
    <button class="sureyemian" id="chek" val='<?php echo $repacket_num; ?>' user='<?php echo $userId; ?>'>确认兑换</button>
</div>
<div class="duihsucc3" style="display: none;" id="two">
    <p class="errore" id="errore2"><img src="/images/closed.png"></p>
    <p class="yiyyuan"> <span id="errormsg"></span></p>
    <button class="sureyemian" id="close" >确认</button>
</div>
<div class="duihsucc" id="three" style="display: none;">
    <p class="errore" id="succclosed"><img src="/images/closed.png"></p>
    <h3>兑换成功</h3>
    <p>请前往花生米富领取红包</p>
    <p class="erweima"><img src="/images/mifu.png"></p>
    <p class="smshib">扫描识别花生米富二维码即可关注</p>
    <button class="sureyemian" id="threeclose">确定</button>
</div>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
    var userId = $('#chek').attr('user');
    $('#threeclose').click(function () {
        window.location = '/dev/account/income?user_id=' + userId;
    });
    $('#change').click(function () {
        var mon = <?php echo $repacket_num; ?>;
        if (parseInt(mon) > 0) {
            $('.Hmask').show();
            $('#one').show();
        }
    });
    $('#errore1').click(function () {
        $('#one').hide();
        $('.Hmask').hide();
    });
    $('#errore2').click(function () {
        $('#two').hide();
        $('.Hmask').hide();
    });
    $('#close').click(function () {
        $('#two').hide();
        $('.Hmask').hide();
    });
    $(".Hmask").click(function () {
        $(".Hmask").hide();
        $('#one').hide();
        $('#two').hide();
        $('#three').hide();
    });
    $('#succclosed').click(function () {
        window.location = '/dev/account/income?user_id=' + userId;
    });
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