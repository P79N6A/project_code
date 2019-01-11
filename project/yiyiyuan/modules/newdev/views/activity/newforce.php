<style type="text/css">
    body,ul,li,a,p,div{padding:0px; margin:0px; font-size:14px;}
    ul,li{list-style:none;}
    a{ text-decoration:none; color:#333;}
</style>
<div class="actvete">
    <div class="bannerimg" >
        <img src="/news/images/activity/newforce/tebanner.jpg">
        <p  class="laba"><img src="/news/images/activity/newforce/laba.png"></p>
        <div id="scroll_div" class="fl">
            <div id="scroll_begin">
                <?php foreach ($phoneArr as $val): ?>
                    <span class="pad_right">恭喜<?php echo $val ?> 获得免还款名额！</span>
                <?php endforeach; ?>
            </div>
            <div id="scroll_end"></div>
        </div>


    </div>

    <div class="buttxt">
        <div class="contnewym"><img src="/news/images/activity/newforce/contnewym.png"></div>
        <button class="dwlhbao">领取免还款特权</button>
        <div class="certifn hdguize">
            <div class="bortop"></div>
            <h3>活动规则</h3>
            <p>1. 活动时间：2017年8月3日--2017年8月23日（含）</p>
            <p>2. 点击活动页中按钮开始参与活动，绑定信用卡即可领取198元免息礼包，发起担保借款并成功下款，即有机会抽取当次借款免还款特权；</p>
            <p>3. 活动所获得的优惠券系统会自动发送到用户的账户中，优惠券只有在优惠券标示的有效期内使用才会获得相应金额的优惠；</p>
            <p>4. 活动期间，系统将在当天发起担保借款并成功下款的用户中随机抽取100名幸运用户获得免还款特权；</p>
            <p>5. 获得免还款特权的用户会在被系统选中后后收到系统发送的通知短信（转发无效）；获得免还款特权后，当次借款，不论金额多少，均不需再进行还款；</p>
            <p>6. 本次活动最终解释权归先花一亿元所有。</p>

        </div>
    </div>

</div>


<div class="Hmask" hidden></div>
<div class="duihsucc3" hidden>
    <a class="errory"><img src="/news/images/activity/newforce/img.png"></a>
    <h3>恭喜您！</h3>
    <p>获得<span>198元</span>免息礼包</p>
    <div class="zymxx">
        <p class="yiyyuan">发起担保借款即可抽取<span>免还款特权</span><br/>分享次数越多，获奖概率越大！</p>
        <button class="sureyemian">分享赢奖</button>
    </div>
</div>
<div class="faqdbjk" hidden>
    <h3>发起担保借款<span>马上参与活动</span></h3>
    <div class="zymxxss">
        <p><img src="/news/images/activity/newforce/tctu.png"></p>
        <button class="sureyemian">我知道了</button>
    </div>
</div>
<script>
    $(function(){
        $('.duihsucc3 .errory').click(function(){
            $('.Hmask').hide();
            $('.duihsucc3').hide();
        });

        $('.faqdbjk button').click(function(){
            $('.Hmask').hide();
            $('.faqdbjk').hide();
        });
    });

    function ScrollImgLeft(){
        var speed=50;
        var MyMar = null;
        var scroll_begin = document.getElementById("scroll_begin");
        var scroll_end = document.getElementById("scroll_end");
        var scroll_div = document.getElementById("scroll_div");
        scroll_end.innerHTML=scroll_begin.innerHTML;
        function Marquee(){
            if(scroll_end.offsetWidth-scroll_div.scrollLeft<=0)
                scroll_div.scrollLeft-=scroll_begin.offsetWidth;
            else
                scroll_div.scrollLeft++;
        }
        MyMar=setInterval(Marquee,speed);
        scroll_div.onmouseover = function(){
            clearInterval(MyMar);
        }
        scroll_div.onmouseout = function(){
            MyMar = setInterval(Marquee,speed);
        }
    }
    ScrollImgLeft();

    //领卷
    var bankCount = "<?php echo $bankCount;?>";
    $('.dwlhbao').click(function () {
        if(bankCount > 0){
            var userId = GetQueryString("user_id");
            $.ajax({
                type: "POST",
                url: "/new/activity/sendcoupon",
                data: {userId:userId},
                success: function(data){

                }
            });
            $('.Hmask').show();
            $('.duihsucc3').show();
        }else{
            $('.Hmask').show();
            $('.faqdbjk').show();
        }
    });

    function GetQueryString(name)
    {
        var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
        var r = window.location.search.substr(1).match(reg);
        if(r!=null)return  unescape(r[2]); return null;
    }

    //返回app
    var u = navigator.userAgent, app = navigator.appVersion;
    var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Linux') > -1;
    var isiOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/);
    var android = "<?php echo $android;?>";
    var ios = "<?php echo $ios;?>";
    var position = "<?php echo $position;?>";
    $('.faqdbjk .sureyemian').click(function () {
        if (isiOS) {
            window.myObj.toPage(ios);
        } else if(isAndroid) {
            window.myObj.toPage(android, position);
        }
    });
    function toPage(activityName, position) {

    }

    //分享
    $('.duihsucc3 .sureyemian').click(function () {
        window.myObj.bannerShare();
    });
    function bannershare() {

    }
</script>