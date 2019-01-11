<div class="Hcontainer nP">
    <div class="main mt20">
        <div class="bWhite borRad5 padtb">
            <div class="main">
                <p class="n42 text-center">还款失败！</p>
                <p class="n26 red mt20">失败原因</p>
                <p class="n22 mt20">1. 余额不足</p>
                <p class="n22 mt20" style="line-height:3rem;">2.超出您银行卡的单笔消费额度，您可以尝试绑定其他银行卡</p>
                <p class="n22 mt20" style="line-height:3rem;">3.用户取消</p>
            </div>
        </div>
        <?php if($source!='app'):?>
            <a href="/new/account"><button class="btn mt40 mb40" style="width:100%">返回账户页面</button></a>
        <?php endif;?>
    </div>
</div>
<?= $this->render('/layouts/footer_new', ['page' => 'person','log_user_id'=>$user_id]) ?>
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