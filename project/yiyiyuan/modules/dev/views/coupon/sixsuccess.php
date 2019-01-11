<div class="wape">
    <script  src='/dev/st/statisticssave?type=47'></script>
    <img src="/images/coupon/wap21.png">
    <div class="wap3">
        <img src="/images/coupon/wap23.png">
        <p>请到 <em><?php echo substr($mobile, 0, 3)." ".substr($mobile, 3, 4)." ".substr($mobile, 7, 4); ?></em> 的账户查看</p>
    </div>
    <img src="/images/coupon/wap24.png">
    <p class="freemfei">优惠券有效期为首次注册后30天内</p>
   
   <div class="bigtuzi">
        <p class="tuzi1ma"><img src="/images/coupon/tuzi1.png"></p>
        <p class="tuzi2ma"><img src="/images/coupon/erweimaym.png"></p>
       <p class="tuzi3ma"><img src="/images/coupon/tuzi2.png"></p>
   </div>
   <p class="centeym">长按二维码关注“先花一亿元” </p>
   <p class="centeym">微信公众号即可马上借款</p>

</div>
<!--<div class="wape">
    <script  src='/dev/st/statisticssave?type=47'></script>
    <img src="/images/coupon/wap21.png">
    <div class="wap3">
        <img src="/images/coupon/wap23.png">
        <p>请下载app后登陆<em><?php echo $mobile; ?></em>的账号查看优惠券</p>
    </div>
    <img src="/images/coupon/wap24.png">
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
</div>-->
<script>
    $('#to-down-btn').on('click', function () {
        $.get("/dev/st/statisticssave", {type: 49}, function (data) {
            window.location = "/dev/ds/download";
        });
    });
</script>