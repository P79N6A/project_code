<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>还款券</title>
    <link rel="stylesheet" type="text/css" href="/newdev/css/coupon/reset.css"/>
    <link rel="stylesheet" type="text/css" href="/newdev/css/coupon/style302.css"/>
</head>
<body>

<div id="wsy" class="yyhuqmeg">
    <?php if(!empty($couponlist)) {?>
        <?php foreach ($couponlist as $key=>$value): ?>
            <div class="vipquan hkquan"  onclick="changeCoupon('<?=$value['id'];?>',this)">
                <div class="vipqzuo">
                    <h3><?=$value['title']?></h3>
                    <p class="yxioqi">有效期：<?=date('Y-m-d',strtotime($value['start_date']))?>至<?=date('Y-m-d',strtotime($value['end_date'])-24*3600)?></p>
                </div>
                <div class="vipqyou">
                    ¥<em><?=$value['val']?></em>
                </div>
                <div class="main">
                    <div class="noehtyxh">
                        <input type="checkbox" id="checkbox-<?=$key+1?>" class="regular-checkbox" <?php if ($coupon_id == $value['id']): ?>checked="checked"<?php endif; ?>>
                        <label id="checkbox-<?=$key+1?>" for="checkbox-<?=$key+1?>" class="check_<?=$value['id'];?>"></label>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php }else{ ?>
        <div id="znawu1" class="bdfalse">
            <img src="/newdev/images/yyy302/none.png">
            <p>暂无可用优惠券</p>
        </div>
    <?php }?>
</div>
</body>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
    $('.errore').click(function () {
        $('.duihsucc2').hide();
        $('.Hmask').hide();
    });
    $('.sureyemian').click(function () {
        $('.duihsucc2').hide();
        $('.Hmask').hide();
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
<script>
    function changeCoupon(couponId,obj){
        var loan_id = <?php echo $loan['loan_id']; ?>;
        window.location.href = '/borrow/repay/repaychoose?loan_id='+loan_id+'&coupon_id='+couponId;
    }
</script>
</html>