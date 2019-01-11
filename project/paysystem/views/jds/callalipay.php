<div class="paybg">
    <img src="/dev/images/loading.png">
    <p class="paying">支付中...</p>
    <p class="zfbhk">你正在通过支付宝进行支付</p>
    <a onclick="back()"><button>查看支付结果</button></a>
</div>
<!--<script src="http://jh.yizhibank.com/js/callalipay.js"></script>-->
<script src="/js/callalipay.js"></script>
<script type="text/javascript">
    var aliPayURL = "<?php echo $aliPayURL; ?>";
    callappjs.callAlipay(aliPayURL);

    function back() {
        setTimeout(function () {
            window.myObj.payResult();
            function payResult() {
            }
        });
    }
</script>
