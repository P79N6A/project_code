<style>
    body{background: #030707;}
    .share4 .button_img{width: 72%; height: 5rem;position: absolute; bottom: 5%; right: 14%; top: 71%; background: rgba(0,0,0,0)}
</style>
<div class="anniversary">
        <img src="/images/activity/share1.jpg">
        <img src="/images/activity/share2.jpg">
        <img src="/images/activity/share3.jpg">
        <div class="share4" id="butt_zn">
                <button sty class="button_img"></button>
                <img src="/images/activity/share4.jpg">
<!--                <a class="button_img" onclick="fn()" ></a>-->
        </div>
</div>
<script>
    var type = "<?php echo $type; ?>";
    $('#butt_zn').click(function () {
        if (type == "app") {
            window.myObj.bannerShare();
        } 
    });
    function bannershare() {
//        alert("fff");
    }
</script>