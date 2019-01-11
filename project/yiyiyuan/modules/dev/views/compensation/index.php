<style>
    body{background:url(/images/blue_bg.jpg);}
</style>  
<div class="Hcontainer nP">
<script  src='/dev/st/statisticssave?type=5'></script> 
    <div class="shareH">
        <img src="/images/edbc2.jpg" width="100%" id="img"/>
        <div class="confirm">
            <div class="col-xs-6 green n26">
                <img src="/images/ybrz.png" width="60%">
                <div class="con_times" style="top:20%;left:10%;"><span class="n42"><?php echo count($user_auth); ?></span>次</div>
            </div>
            <div class="col-xs-6 text-right n26 green">
                <img src="/images/rzhy.png" width="60%">
                <div class="con_times" style="top:20%;right:10%;"><span class="n42"><?php echo count($user_authed); ?></span>次</div>
            </div>
            <div class="col-xs-12 text-center n26 yellow2 mf10">
                <img src="/images/bced2.png" width="100%">
                <div class="con_times" style="top:20%;left:30%;"><span class="n58"><?php echo (count($user_auth)+count($user_authed)) * 70; ?></span>点</div>
            </div>
        </div>

        
        <div class="get text-center">
            <div class="col-xs-12">
                <button class="btn_get box_btn" style="width:70%;" onclick="eject(<?php echo (count($user_auth)+count($user_authed)) * 70; ?>)">马上领取</button>
            </div>                    
            <div class="col-xs-12">
                <button class="giveUp mt20" onclick="eject('no')"></button>
            </div>

        </div>
    </div>
    <div class="Hmask showsdo" style="display: none;"></div>
    <div class="layer_border text-center donot" style="display: none;">
        <p class="n30 mb30">整整<span class="red"><?php echo (count($user_auth)+count($user_authed)) * 70; ?>点</span>额度说不要就不要了？</p>
        <div class="border_top_2">
            <a href="javascript:location.href='/dev/invest';" class="n30 boder_right_1"><span class="cor">说啥也不要了</span></a>
            <a href="javascript:eject('no');" class="n30 red"><span class="red">再考虑一下</span></a>
        </div>
    </div>
    <div class="layer_border text-center doit" style="display: none;">
        <p class="n30 mb30">成功领取<span class="red"><?php echo (count($user_auth)+count($user_authed)) * 70; ?>点</span>额度，投资去喽！</p>
        <div class="border_top_2">
            <a href="javascript:location.href='/dev/invest';" class="n30 boder_right_1"><span class="red">立刻去赚收益</span></a>
            <a href="javascript:location.href='/dev/auth/beginshare';" class="n30 red"><span class="red">获取更多额度</span></a>
        </div>
    </div>
    <div class="layer_border text-center already" style="display: none;">
        <p class="n30 mb30" id="remind">您已经领取过了！</p>
        <div class="border_top_2">
            <a href="javascript:location.href='/dev/auth/beginshare';" class="n30 boder_right_1" style="width:100%"><span class="red">获取更多额度</span></a>
        </div>
    </div>
</div>
<script>
    function eject(n) {
        if (n == 'no') {
            $('.showsdo').toggle();
            $('.donot').toggle();
        } else if (n == 0) {
            $('.showsdo').toggle();
            $('.already').show();
        } else {
            $.ajax({
                type: "POST",
                dataType: "json",
                //url:"bs",
                url: "/dev/compensation/amount",
                data: $('#pay').serialize(), // 你的formid
                async: false,
                success: function(data) {
//                    alert(data.code);
                    if (data.code == '0000') {
                        $('.showsdo').toggle();
                        $('.doit').show();
                    } else {
                        $('#remind').html(data.msg);
                        $('.showsdo').toggle();
                        $('.already').show();
                    }
                }
            });
        }
    }
</script>