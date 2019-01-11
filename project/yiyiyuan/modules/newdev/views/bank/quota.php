<?php
$list = [];
foreach ($credit_limit as $key=>$val){
    $list[$key]['a'] = '';
    if(!empty($debit_limit)){
        $list[$key]['a'] = array_shift($debit_limit);
    }
    $list[$key]['b'] = $val;
}
if(!empty($debit_limit)){
    foreach ($debit_limit as $key=>$val){
        $k = 100+$key;
        $list[$k]['a'] = $val;
        $list[$k]['b'] = '';
    }
}
?>
<script>
    $(function(){
        $('.Hcontainer .cxxy').each(function(index){
            $(this).click(function(){
                $('.Hcontainer .cxxy').removeClass('on');
                $(this).addClass('on');
                $('.main').css('display','none');
                $('.main').eq(index).css('display','block');
            });
        });
    });
</script>
<div class="Hcontainer n26 grey2 nP">
    <div class="main on" style="position:static;display:block;">
        <table class="formList" border="0" cellpadding="0" cellspacing="0" border="1">
            <tr>
                <th width="52%" height="30px">信用卡</th>
                <th width="52%"  height="30px">借记卡</th>
            </tr>
            <?php foreach ($list as $val): ?>
                <tr>
                    <td><?php echo !empty($val['a']) ? $val['a']['card_name'] : '';?></td>
                    <td><?php echo !empty($val['b']) ? $val['b']['card_name'] : '';?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <!--        <p style="line-height:22px; text-align:center; margin:10px 5%;">提示：由于银行通道问题，出款卡和还款卡可能存在不同时支持的情况，具体情况请根据页面显示为准。</p>-->
    <!--        <p style="line-height:22px; text-align:center; margin:10px 5%;">支持限额:单笔5000元(招商借记卡单笔1000元)、单日10000元、单月20000元</p>
            <p style="font-size: 14px;">注：受银行支付通道影响，交行卡，农行卡支付业务暂时暂停，给您带来的不便敬请谅解。</p>    -->
</div>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
    wx.config({
        debug: false,
        appId: '<?php echo $jsinfo['appid'];?>',
        timestamp: <?php echo $jsinfo['timestamp'];?>,
        nonceStr: '<?php echo $jsinfo['nonceStr'];?>',
        signature: '<?php echo $jsinfo['signature'];?>',
        jsApiList: [
            'hideOptionMenu'
        ]
    });

    wx.ready(function(){
        wx.hideOptionMenu();
    });
</script>