<div class="paybg">
    <img src="/images/iconpay.png">
    <p class="paying">支付中...</p>
    <p class="zfbhk">你正在通过开放平台支付宝进行还款</p>
    <button id="butt">查看还款结果</button>
</div>
<!--<script src="http://jh.yizhibank.com/js/callalipay.js"></script>-->
<script src="/js/alipay/callalipay.js"></script>
<script type="text/javascript">
    var aliPayURL = "<?php echo $aliPayURL; ?>";
    callappjs.callAlipay(aliPayURL);

    $('#butt').click(function () {
        window.myObj.payResult();
    });
    function payResult() {

    }
</script>
