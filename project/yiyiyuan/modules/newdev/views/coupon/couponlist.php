<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>优惠券</title>
    <link rel="stylesheet" type="text/css" href="/newdev/css/coupon/reset.css"/>
    <link rel="stylesheet" type="text/css" href="/newdev/css/coupon/style302.css"/>
</head>
<body>
<div class="tab_xtmeg">
    <div id="left" class="xtong_left hover">可使用
        <div class="addcont">
            <p class="weidu_meage yhquan"></p>
        </div>
    </div>
    <div id="right" class="xtong_left">已过期
        <div class="addcont">
            <p class="weidu_meage yhquan" style=""></p>
        </div>
    </div>
</div>
<?php if(!empty($coupon_wsy)){?>
<div id="wsy" class="yyhuqmeg">
    <?php foreach ($coupon_wsy as $key=>$value): ?>
        <div class="vipquan <?php if(in_array($value->type,[1,2,3,4])){echo '';}else{echo 'hkquan';}?>">
            <div class="vipqzuo">
                <h3><?=$value->title?></h3>
                <p class="yxioqi">有效期：<?=date('Y-m-d',strtotime($value->start_date))?>至<?=date('Y-m-d',strtotime($value->end_date)-24*3600)?></p>
            </div>
            <div class="vipqyou">
                ¥<em><?=$value->val?></em>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php }else{ ?>
<div id="znawu1" class="bdfalse">
    <img src="/newdev/images/yyy302/none.png">
    <p>暂无可用优惠券</p>
</div>
<?php } ?>
<?php if(!empty($coupon_ygq)){?>
<div id="ygq" class="yyhuqmeg" style="display: none">
    <?php foreach ($coupon_ygq as $key=>$value): ?>
        <div class="vipquan <?php if(in_array($value->type,[1,2,3,4])){echo '';}else{echo 'hkquan';}?>">
            <div class="finshend"></div>
            <div class="vipqzuo">
                <h3><?=$value->title?></h3>
                <p class="yxioqi">有效期：<?=date('Y-m-d',strtotime($value->start_date))?>至<?=date('Y-m-d',strtotime($value->end_date)-24*3600)?></p>
            </div>
            <div class="vipqyou">
                ¥<em><?=$value->val?></em>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php }else{ ?>
    <div id="znawu2" class="bdfalse" style="display: none">
        <img src="/newdev/images/yyy302/none.png">
        <p>暂无可用优惠券</p>
    </div>
<?php } ?>
<div class="userrule" onclick="look_coupon_tip()">优惠券使用规则</div>
<div class="alert" hidden >
    <div class="box">
        <h4 style="text-align:center">优惠券使用规则</h4> 
        <img class="close-icon" onclick="close_tip()"  src="/borrow/310/images/bill-close.png">
        <p><span class="boxNum" >1、</span><span class="boxText">同类优惠券不可叠加使用</span></p>
        <p><span class="boxNum">2、</span><span class="boxText">借款优惠券只可用户抵息，多余部分不予退还</span></p>
        <p><span class="boxNum">3、</span><span class="boxText">还款优惠券仅限全额还款时使用，部分还款优惠券不会生效</span></p>
        <p><span class="boxNum">4、</span><span class="boxText">所有优惠券不可找零、兑换及提现 </span></p>
        <p><span class="boxNum">5、</span><span class="boxText">优惠券最终解释权归先花一亿元所有</span></p>
    </div>
</div>
<style>
    .userrule{
        font-size: 14px;
        color: #3D81FF;
        text-align: center;
        position: fixed;
        bottom: 0.27rem;
        width: 100%;
    }
    .alert{
        position: fixed;
        width: 100vw;
        height: 100vh;
        background: rgba(0,0,0,.3);
        z-index: 999;
        top: 0;
        left: 0;
    }
    .box{
        width: 80vw;
        background: #ffffff;
        margin: 50vw auto 0;
        border-radius: 8px;
        color: #4d4c4c;
        position: relative;
        padding-bottom: 4vw;
        box-sizing: border-box;
    }
    .box .close-icon{
        width: 15px;
        position: absolute;
        top: 12px;
        right: 12px;
    }
    .box h4{
        font-size: 18px;
        padding: 20px;
        font-weight: bold;
    }
    .box p{
        font-size: 14px;
        margin-left: 30px;
        padding: 4px 0;
        margin-right: 30px;
        margin-bottom: 5px;
    }
    .box p span{
        font-size: 14px;
        line-height: 20px;
        color: #000;
    }
    .boxNum{
        float: left;
    }
    .boxText{
        margin-left: 20px;
        display: block;
    }
</style>
</body>
<script type="text/javascript" src="/newdev/js/jquery-1.11.0.min.js"></script>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script type="text/javascript">
    wx.config({
        debug: false,
        appId: "<?php echo $jsinfo['appid']; ?>",
        timestamp: "<?php echo $jsinfo['timestamp']; ?>",
        nonceStr: "<?php echo $jsinfo['nonceStr']; ?>",
        signature: "<?php echo $jsinfo['signature']; ?>",
        jsApiList: [
            'hideOptionMenu'
        ]
    });

    wx.ready(function () {
        wx.hideOptionMenu();
    });

    $('#left').click(function () {
        $('#wsy').show();
        $('#ygq').hide();
        $('#znawu1').show();
        $('#znawu2').hide();
        var me = $(this);
        me.addClass('hover')
        me.siblings().removeClass('hover');
        $('#right').removeClass('hover');
    });
    $('#right').click(function () {
        $('#ygq').show();
        $('#wsy').hide();
        $('#znawu1').hide();
        $('#znawu2').show();
        var me = $(this);
        me.addClass('hover');
        $('#left').removeClass('hover');

    });
    //关闭优惠券规则
    function close_tip(){
        $('.alert').hide();
    }
    //查看优惠券规则
    function look_coupon_tip(){
        $('.alert').show();
    }
</script>
</html>