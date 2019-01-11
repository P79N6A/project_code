<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, minimal-ui;">
    <meta name="format-detection" content="telephone=no">
    <title></title>   
    <link rel="stylesheet" type="text/css" href="/css/reset.css"/>
    <script src="/js/dev/activityjs.js"></script>
    <style>
    	html,body{width:100%;height:100%; background: #cc1a1d; font-family: "Microsoft YaHei"; position: relative;}
		a{color:#000}
    	.yiyyn img{ width: 100%; display: block; margin-top: -1px;}
    	.yiyyn .yract5{position: relative;}
    .yiyyn .yract5 button{position: absolute;width: 38%; height: 4rem;margin: 20% 31%;bottom: 0; background: rgba(0,0,0,0);}
    .yiyyn .yract5 input{position: absolute; color: #fff;  background: rgba(0,0,0,0);bottom: 1rem; margin: 0 16%;width: 67%;font-size: 14px;}
    .yiyyn .yract5 p{position: absolute; top:1rem;text-align: center;width: 100%;color: #fff;}
    #overDiv {
    background-color: #000;
    width: 100%;
    height: 100%;
    left: 0;
    top: 0;
    filter: alpha(opacity=70);
    opacity: 0.7;
    z-index: 11;
    position: fixed!important;
    position: absolute;
    _top: expression(eval(document.compatMode &&
 document.compatMode=='CSS1Compat') ?
 documentElement.scrollTop + (document.documentElement.clientHeight-this.offsetHeight)/2 :/*IE6*/
 document.body.scrollTop + (document.body.clientHeight - this.clientHeight)/2);
}
.tancgg{ position: fixed; top:20%; z-index: 100; margin: 0 5%;}
.tancgg .buttonbu{position: absolute;bottom: 1.1rem;left: 23%;width: 54%;height: 47px;background: rgba(0,0,0,0);}
    </style>
</head>
<body>

<input type="hidden" name = 'type' value="<?=$type?>" />
<input type='hidden' name="downurl" value="<?=$downurl?>" />
<div class="yiyyn">
	<img src="/images/lqvy1.jpg">
	<img src="/images/lqvy2.jpg">
	<img src="/images/lqvy3.jpg">
	<div class="yract5">
		<img src="/images/lqvy4.jpg">
                <input type="number" name = "phone"/>
	</div>
	<div class="yract5">
            <p class="err_msg"></p>
		<img src="/images/lqvy5.jpg">
                <button class="butt"></button>
	</div>
</div>	

<div id="overDiv" style="display:none;"></div>
<!--成功获得弹框-->
<div class="tancgg succ_tcc" style="display:none;">
	<img src="/images/tanc1.png">
	<button class="buttonbu toPage"></button>
</div>
<!--不是首贷弹框-->
<div class="tancgg not_first" style="display:none;">
	<img src="/images/tanc2.png">
	<button class="buttonbu no_first_butt"></button>
</div>
</body>
</html>
<script>
$(".butt").click(function () {
    $.get("/new/st/statisticssave", {type: 1000}, function (data) {
            });
    $('.err_msg').html("");
    var phone = $('input[name="phone"]').val();
    $.post("/new/activity/dofoolsdayinfo",{phone: phone},function(res){
        var datas = eval("(" + res + ")");
        console.log(datas);
            if(datas.res_code == 1 || datas.res_code == 3){
                $('.err_msg').html(datas.res_data);
                return false;
            }else if(datas.res_code == 2){
                $('#overDiv').css('display', 'block');
                $('.not_first').css('display', 'block');
                return false;
            }else if(datas.res_code == 0){
                $('#overDiv').css('display','block');
                $('.succ_tcc').css('display','block');
                return false;
            };
    });
});

$(".no_first_butt").click(function(){
    $('#overDiv').css('display','none');
    $('.not_first').css('display','none');
});
$("#overDiv").click(function(){
    $('#overDiv').css('display','none');
    $('.not_first').css('display','none');
    $('.succ_tcc').css('display','none');
})
var type = $('input[name="type"]').val();
var u = navigator.userAgent, app = navigator.appVersion;
var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Linux') > -1;
var isiOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/);
var downurl=$('input[name="downurl"]').val();
$(".toPage").click(function(){
        //app跳转到借款
        if(type != ""){
            var android = "<?php echo $android;?>";
            var ios = "<?php echo $ios;?>";
            var position = "<?php echo $position;?>";
            if (isiOS) {
//                window.myObj.toPage(ios,position);
                window.myObj.closeHtml();
                return false;
            } else if(isAndroid) {
                window.myObj.toPage(android, position);
                return false;
            }

        }
        function toPage(activityName, position) {

        }
        var ua = window.navigator.userAgent.toLowerCase(); 
        if( ua.match(/MicroMessenger/i) == 'micromessenger'){
            window.location = 'http://a.app.qq.com/o/simple.jsp?pkgname=com.xianhuahua.yiyiyuan_1';
            return false;
        }
        if(isiOS){
            window.location = "https://itunes.apple.com/cn/app/xian-hua-yi-yi-yuan/id986683563?mt=8"; 
            return false;
        }
        window.location.href = downurl;
})
</script>