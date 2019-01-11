<div class="actvete">
    <div class="bannerimg" >
        <img src="/news/images/activity/newforce/tebanner.jpg">
    </div>
    <div class="buttxt">
        <div class="xzapp">
            <h3>激活成功！</h3>
            <p>马上下载<span>先花一亿元APP</span>领取吧~</p>
            <p>请使用手机号<?php echo $phone?>注册登陆领取</p>
        </div>
        <button class="dwlhbao parem33"> 下载APP领取</button>
    </div>
</div>
<script>
    $(function(){
        $('.tancymia .tcerror').click(function(){
            $('#overDivs').hide();
            $('.tancymia img').hide();
        });
    });

    $(".dwlhbao").click(function(){
        window.location.href = '/wap/st/down';
    })
</script>