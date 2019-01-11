<div class="wape">
    <script  src='/dev/st/statisticssave?type=51'></script>
    <img src="/images/coupon/wap21.png">
    <div class="wap3">
        <img src="/images/coupon/wap23.png">
        <p>请下载app后登陆<em><?php echo $mobile; ?></em>的账号查看优惠券</p>
    </div>
    <img src="/images/coupon/wapall4.png?v=20160715001">
    <p class="freemfei">优惠券有效期为首次注册后30天内</p>
    <div class="button xzapp"> 
        <a id="to-down-btn" href="#">
            <button>下载APP</button>
        </a>
    </div>
    <p class="yesguzhu">还可以关注“先花一亿元”公众号进行借款</p>
    <img class="wap5" src="/images/coupon/wap15.png">
    <img src="/images/coupon/wap16.png">
    <img src="/images/coupon/wap17.png">
    <img src="/images/coupon/wap18.png">
    <img src="/images/coupon/wap19.png">
</div>
<script>
    $('#to-down-btn').on('click', function () {
        $.get("/dev/st/statisticssave", {type: 53}, function (data) {
            window.location = "/dev/ds/download";
        });
    });
</script>