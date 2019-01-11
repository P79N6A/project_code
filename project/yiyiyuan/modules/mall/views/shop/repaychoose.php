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
<head xmlns="http://www.w3.org/1999/html">
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title></title>
    <link rel="stylesheet" type="text/css" href="/290/css/reset.css"/>
    <link rel="stylesheet" type="text/css" href="/290/css/inv.css"/>
    <script src="/290/js/jquery-1.10.1.min.js"></script>
</head>

<div class="haimoney">
    <div class="hkje">
        <p class="haititle">支付金额(元)</p>
        <p id="money" class="haitxt"><?php echo sprintf('%.2f', $amount); ?> </p>
        <p id="orderId" style="display: none"><?php echo $orderId; ?> </p>
    </div>
    <div id="demo11"></div>
</div>

<div class="fukfsi">
    <div class="errore">
        <span>支付银行卡选择</span>
    </div>
    <div style="overflow:hidden; padding: 10px">
        <?php foreach ($banklist as $k=>$v){ ?>
            <p style="    float: left; display: block; width: 100%; padding-bottom: 15px">
                <img style="height: 30px;width: 30px;float: left;margin-right: 3%; padding-left: 2%" src="<?php echo getImageUrl($v['bank_abbr']); ?>" >
                <?php if($v['bank_abbr'] == 'GDB'): echo "广发银行";  else: echo $v['bank_name']; endif; ?><span style="padding:0 3%;">
                <?php echo $v['type'] == 0 ? '借记卡' : '信用卡'; ?></span>
                尾号<?php echo substr($v['card'], strlen($v['card']) - 4, 4); ?>
                <input style="margin-left: 5%;" <?php if($k == 0){ ?>checked<?php } ?> name="chk" type="radio" class = 'chk' onclick="chk(<?php echo $v['id']; ?>)" >
            </p>
        <?php } ?>
    </div>
</div>
<div class="button">
    <button value="<?=$banklist[0]['id'];?>" id="submit">确认支付</button>
</div>
</div>

<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
    function chk(bid) {
        $('#submit').val(bid)
    }
    $('#submit').click(function () {
       var bank_id = $('#submit').val();
       var money = $('#money').html();
       var orderId = $('#orderId').html();
       var csrf = '<?php echo $csrf; ?>';
        $.post("/mall/shop/payyibao", {_csrf:csrf,bank_id:bank_id, money: money, orderId: orderId}, function(result) {
            var data = eval("(" + result + ")");
            if (data.res_code == 0) {
                var location_href = data.res_data;
                window.location = location_href;
            } else {
                alert(data.res_data);
                return false;
            }
        });
    });

</script>
