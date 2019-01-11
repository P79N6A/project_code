<style type="text/css">
    html,body{width:100%;background: #fafafa; font-family: "Microsoft YaHei";}
    .box img{ width: 100%; display: block; margin-top: -2px; }
    .box div.woyaojlk{ position: relative; }
    .box div.woyaojlk a{display: block;position: absolute; color: #fff;top: 0; width: 80%; margin: 0 10%;text-align: center;font-size: 4rem;height: 10rem; line-height: 10rem;}
    #checkyes { position: absolute; bottom:6rem;left: 18%;}
    #checkyes span { position: relative;    display: inline-block; float: left;}
    #checkyes .input_check { position: absolute; visibility: hidden;}
    #checkyes .input_check+label {    display: inline-block;width: 40px;height: 40px;background: #f64d2e;border-radius: 30px;border: 4px solid #fff;}
    #checkyes .input_check+label.check4{ background: #c2c2c2;}
    #checkyes .input_check:checked+label:after {content: ""; position: absolute;left: 9px;bottom:29px; width: 21px;height: 19px;border: 4px solid #fff; border-top-color: transparent;border-right-color: transparent; -ms-transform: rotate(-60deg);-moz-transform: rotate(-60deg);-webkit-transform: rotate(-60deg);transform: rotate(-45deg);}
    .surexq{ font-size: 1.7rem; color: #fff; margin-left: 5px; line-height: 2.2rem; }

    #overDivs{background: #000;width: 100%;height: 100%;left: 0;top: 0;filter: alpha(opacity=7);opacity: 0.7;z-index: 11;position: fixed!important; position: absolute;_top: expression(eval(document.compatMode &&document.compatMode=='CSS1Compat') ?documentElement.scrollTop + (document.documentElement.clientHeight-this.offsetHeight)/2 :/*IE6*/document.body.scrollTop + (document.body.clientHeight - this.clientHeight)/2);}
    .tanchuceng{position: fixed;top: 35%;left: 25%; border-radius: 5px; z-index: 100; width: 50%;}
    .tanchuceng img{ width: 100%;}
    .tchuxqing{position: fixed;top: 35%;left: 10%; border-radius: 5px; z-index: 100; width: 80%;}
    .tchuxqing a{display: block; position: absolute; bottom: 4rem; height: 3rem;left: 24%;width: 51%;}
</style>
<script src="/js/jquery-1.10.1.min.js"></script>
<div class="box">
    <img src="/images/activity/yiyi01.jpg">
    <img src="/images/activity/yiyi02.jpg">
    <img src="/images/activity/yiyi03.jpg">
    <div class="woyaojlk">
        <img src="/images/activity/yiyi04.jpg">
        <a class="yiyi5"><img src="/images/activity/yiyi05.png"></a>
        <div id="checkyes">
	            <span>
	            	<input type="checkbox" class="input_check" id="check3" checked="">
	            	<label for="check3" class="after"><span></span></label>
	            </span>
            <span class="surexq">确认领取保险点击查看详情规则</span>
        </div>
    </div>
</div>
<div id="overDivs" hidden></div>
<div class="tanchuceng tchuok" hidden>
    <img src="/images/activity/tchu1.png">
</div>
<div class="tanchuceng tchure" hidden>
    <img src="/images/activity/tchu3.png">
</div>

<div class="tchuxqing" hidden>
    <img src="/images/activity/tchu2.png">
    <a></a>
</div>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
$(function(){
    $(".surexq").click(function () {
        window.location = "/dev/activity/agreement";
    })
    $('#overDivs').click(function () {
        $('#overDivs').hide();
        $('.indextc').hide();
        $('.tchuxqing').hide();
    });
    $('.indextc').click(function () {
        $('#overDivs').hide();
        $('.indextc').hide();
    });
    $('.yiyi5').click(function(){
        var ischeck = $('#check3').is(":checked");
        if(!ischeck){
            alert("必须同意条款才能领取");
            return false;
        }
        $.post("/dev/activity/receive", {}, function(data) {
            var result = eval("("+data+")");
            if(result.code == 0){//领取成功
                $('#overDivs').show();
                $('.tchuok').show();
                setTimeout(function(){
                    window.location = '/dev/loan' ;
                },1500);
                return false;
            }else if(result.code == 1){//领取失败tchure
                alert("领取失败")
                return false;
            }else if(result.code == 2){//您已经领取过了
                $('#overDivs').show();
                $('.tchure').show();
                setTimeout(function(){
                    window.location = '/dev/loan' ;
                },1500);
                return false;
            }else if(result.code == 3){//请先完成我的资料中的实名认证信息
                $('#overDivs').show();
                $('.tchuxqing').show();
                $('.tchuxqing a').attr('href',result.url);
                return false;
            }
        });
    });
})
wx.config({
    debug: false,
    appId: '<?php echo $jsinfo['appid']; ?>',
    timestamp: <?php echo $jsinfo['timestamp']; ?>,
    nonceStr: '<?php echo $jsinfo['nonceStr']; ?>',
    signature: '<?php echo $jsinfo['signature']; ?>',
    jsApiList: [
        'hideOptionMenu',
        'onMenuShareAppMessage',
        'showOptionMenu'
    ]
});
</script>
