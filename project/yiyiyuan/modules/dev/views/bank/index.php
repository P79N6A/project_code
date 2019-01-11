<script>
    $(window).load(function () {
        var lineH = $('.icon_bank').height();
        $('.col-xs-2').css('lineHeight', lineH + 'px');
        //$('.col-xs-2').css('marginLeft', '10px');
        var height2 = $('.col-xs-7').height();
        $('.col-xs-7').css('marginTop', (lineH - height2) / 2)
    });
</script>
<?php
$bank = array('ABC', 'ALL', 'BCCB', 'BCM', 'BOC', 'CCB', 'CEB', 'CIB', 'CMB', 'CMBC', 'GDB', 'HXB', 'ICBC', 'PAB', 'PSBC', 'SPDB', '中信');
?>
<div class="Hcontainer">
    <script  src='/dev/st/statisticssave?type=19'></script>
    <div class="main">
        <p class="grey2 n30">信用借款仅支持绑定借记卡</p>
    </div>
    <?php if (count($banks) > 0): ?>
        <div class="banks">
            <ul>
                <?php foreach ($banks as $key => $val): ?>
                    <li class="border_top_2">
                        <a href="/dev/bank/delbank?id=<?php echo $val['id']; ?>">
                            <div class="col-xs-3"><img src="/images/bank_logo/<?php
                                if (!empty($val['bank_abbr']) && in_array($val['bank_abbr'], $bank)) {
                                    echo $val['bank_abbr'];
                                } else {
                                    echo 'ALL';
                                }
                                ?>.png" class="icon_bank"></div>
                            <div class="col-xs-7 bank_cont"><span class="n36 bankN"><?php echo $val['bank_name']; ?></span><i class="redLight"><?php echo $val['type'] == 0 ? '借记卡' : '信用卡'; ?></i>
                                <div class="clearfix"></div>
                                <p class="n24 grey4 mt10">尾号<?php echo substr($val['card'], strlen($val['card']) - 4, 4) ?></p></div>
                            <div class="col-xs-2 text-right"><img src="/images/arrowGrey.png" class="arrowGrey"></div>
                        </a>
                    </li>
                <?php endforeach; ?>                
            </ul>
        </div>
    <?php endif; ?>
    <div class="main">
        <?php if (count($banks) < 10): ?>
            <div class="addCard mt20 <?php echo count($banks) > 0 ? 'nBackg' : '' ?>">
                <a href="/dev/bank/addcard?user_id=<?php echo $user_id; ?>"><span>添加银行卡</span></a>
            </div>
        <?php else : ?>
            <div class="addCard mt20 nBackg">
                <span>添加银行卡</span>
            </div>
            <span style="color: red;">很抱歉，目前最多只能绑定十张卡</span>
        <?php endif; ?>
        <a href="/dev/bank/quota" class="n22 float-right aColor mt20">支持银行卡及限额说明</a>
    </div>
    <div class="bottomBtn n36 red text-center"><a href="/dev/account"><span>返回账户页</span></a></div>                          
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