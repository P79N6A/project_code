<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>欢迎登录</title>
    <link href="/newdev/css/traffic/reset.css" rel="stylesheet">
    <link rel="stylesheet" href="/newdev/css/traffic/new.min.css">
    <script src="/newdev/js/traffic/flexible.js"></script>
    <script src="/290/js/jquery-1.10.1.min.js"></script>
</head>
<style>
    input[disabled] {
        background-color:white;
        color:#666;
        opacity: 1;
    }
    html,body{width:100%;font-family: "Microsoft YaHei";    background: #d01d1c;}
    a{color:#fff; text-decoration: none; font-size: 16px;}
    .Hmask {
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,.7);
        position: fixed;
        top: 0;
        left: 0;
        z-index: 100;
    }
     /*.tsmes{height:7.8125vw;font-size:2.8125vw;text-align:left;padding:2.34375vw 9.375vw 0;color:#fff;box-sizing:border-box;}*/
    .duihsucc{width: 90%;position: fixed; top: 20%;left: 5%;border-radius: 5px; z-index: 100;background: #fff; color: #444;}
    .duihsucc button{ width: 40%; height: 35px; line-height: 35px; background: #c90000; color: #fff; border-radius: 30px; font-size: .5rem;
        border: none;}
    .duihsucc button.sureyemian{ margin: 15px 30% 20px;}
    .duihsucc button.sureyemiantest{ margin: 15px 30% 20px;}
    .duihsucc p{ text-align: center; font-size: 0.4rem;  margin-top: .5rem;}
    .duihsucc p.xuhua{ padding:30px 0 5px; font-size: .4rem;color: #c90000;}
    .duihsucc p span{ color: #c90000;}
     .bg_06{
        width: 100%;
        height: 61vw;
        background: url('/newdev/images/traffic/new/bg_06.jpg') no-repeat;
        background-size: 100% 100%;
    }
    .bg_03 .des .info {
        width: 40vw;
        text-align: center;
        font-size: 1.875vw;
        transform: scale(0.9);
    }
</style>
<body>
<div class="top_bg" id="#"></div>
<div class="bg_02">
        <input type='hidden' name="downurl" value="<?=$downurl?>">
        <input type='hidden' name="type" value="<?=$type?>"><!--页面参数-->
        <input type='hidden' name="regtype" value="<?=$regtype?>"><!--点击注册 type参数-->
        <input type='hidden' name="downtype" value="<?=$downtype?>"><!--点击去下载 type参数-->
        <input type='hidden' name="come_from" value="<?=$come_from?>">
        <input type='hidden' name="img_range" value="<?=$img_range?>">
        <input type='hidden' name="img_range_type" value="1">
    <input type="number" name="mobile" id="tel" placeholder="输入您的手机号码" oninput="if(value.length>11)value=value.slice(0,11)">
    <div class="relative">
        <input id="imgCode" maxlength="4" name="imgcode" placeholder="图形验证码" type="text">
        <img class="yzm" id="getcode_num" src="/new/traffic/getimgcode?img_range=<?=$img_range?>">
    </div>
    <div class="relative">
        <input type="number" name="code" placeholder="输入短信验证码" oninput="if(value.length>6)value=value.slice(0,6)">
        <span class="num" id="getCode" >立即获取</span>
    </div>
    <p class="tip"  id="warning"></p>
    <div id="sub" class="btn">领取额度</div>
    <div class="xieyi" >
<!--         同意-->
        <img id="isture" src="/newdev/images/traffic/new/xieyi_1.png">
<!--         未同意-->
        <img id="isfalse" src="/newdev/images/traffic/new/xieyi_0.png" style="display: none">
        <span id="tongyi">同意《先花一亿元注册协议》</span>
    </div>
<!--    <div class="main">-->
<!--        <div class="noehtyxh">-->
<!--            <input type="checkbox" checked="checked" id="checkbox-1" class="regular-checkbox">-->
<!--            <label for="checkbox-1"></label>-->
<!--            同意-->
<!--            <a href="/dev/app/registerrule" class="underL">《先花一亿元注册协议》</a>-->
<!--        </div>-->
<!--    </div>-->
</div>
<div class="Hmask" style="display: none;"></div>
<div class="duihsucc" id="regsuccess" style="display: none;">
    <p class="xuhua">恭喜您，注册成功！</p>
    <p>您已注册成功，现可在"先花一亿元"借款</p>
    <button class="sureyemian">立即借款</button>
</div>

<div class="duihsucc" id="haveReg" style="display: none;">
    <p class="xuhua">您已经注册过了！</p>
    <p>您已注册过账号，可直接去"先花一亿元"借款</p>
    <button class="sureyemian">立即借款</button>
</div>

<div class="duihsucc" id="haveRegTest" style="display: none;">
    <p class="xuhua">您已经注册过了！</p>
    <p>您已注册过账号，可直接去"先花一亿元"借款</p>
    <button class="sureyemiantest" onclick="$('#haveRegTest').css('display','none');$('.Hmask').css('display','none');$('#sub').attr('disabled', true);">确定</button>
</div>

<div class="duihsucc" id="regsuccessTest" style="display: none;">
    <p class="xuhua">恭喜您，注册成功！</p>
    <p>您已注册成功，现可在"先花一亿元"借款</p>
    <button class="sureyemiantest" onclick="$('#regsuccessTest').css('display','none');$('.Hmask').css('display','none');">确定</button>
</div>
<div class="bg_03">
    <div class="des">
        <div class="des_list">
            <div class="info">
                <img class="touxiang" src="/newdev/images/traffic/new/p1.png"></br>
                <span>A.菲</span>
            </div>
            <p>最近看朋友用的很多，下载过来试试，感觉还不错，值得推 荐给大家，没有什么门槛，下款也很及时!</p>
        </div>
        <div class="des_list">
            <div class="info">
                <img class="touxiang" src="/newdev/images/traffic/new/p2.png"></br>
                <span>刘斌</span>
            </div>
            <p>非常好用的一个平台，一直在使用，每次都及时的解决了我 的燃眉之急，会一直支持!
          <span>
            <img class="zan" src="/newdev/images/traffic/new/zan.png">
            <img class="zan" src="/newdev/images/traffic/new/zan.png">
            <img class="zan" src="/newdev/images/traffic/new/zan.png">
          </span>
            </p>
        </div>
        <div class="des_list">
            <div class="info">
                <img class="touxiang" src="/newdev/images/traffic/new/p3.png"></br>
                <span>小小快乐</span>
            </div>
            <p>是个非常好的借款APP，秒审核，秒下款，手续费透明，简 单易操作，值得我推荐给大家。
          <span>
            <img class="zan" src="/newdev/images/traffic/new/zan.png">
          </span>
            </p>
        </div>
        <div class="des_list">
            <div class="info">
                <img class="touxiang" src="/newdev/images/traffic/new/p4.png"></br>
                <span>科里装饰</span>
            </div>
            <p>非常满意，服务有耐心，打款快，没别的说的了，就是棒！ 帮我解决了燃眉之急，感谢先花一亿元这个好平台！</p>
        </div>
        <div class="des_list">
            <div class="info">
                <img class="touxiang" src="/newdev/images/traffic/new/p5.png"></br>
                <span>Mr.Zhao</span>
            </div>
            <p>说实话很棒的手机贷款软件，小额快速贷款速度挺快的，借 款很方便几天就下来了，利率也低，很好用。</p>
        </div>
        <div class="des_list">
            <div class="info">
                <img class="touxiang" src="/newdev/images/traffic/new/p6.png"></br>
                <span>@.糖豆</span>
            </div>
            <p>既快速又保险，已经用过很多次了，这样的软件能解决我们 短暂的资金问题，还款期限也是十分人性化的一点。</p>
        </div>
    </div>
</div>
<div class="bg_04"></div>
<div class="bg_05">
    <div class="comp">
        <div>
            <label for="money">借款金额: </label>
            <input id="money" type="number" placeholder="1千起">
        </div>
        <div>
            <label for="time">借款周期: </label>
            <input id="time" type="number" placeholder="56天" disabled>
        </div>
        <div>
            <label for="repay">到期应还: </label>
            <input id="repay" type="number" placeholder="0" disabled>
        </div>
    </div>
    <div class="btn">
        <a href="#">领取额度</a>
    </div>
    <p class="bottom">先花一亿元帮您提高生活品质</p>
    
</div>
<div class="bg_06"></div>

</body>
<script  src='/new/st/statisticssave?type=<?=$type?>'></script>
<script>
    $(function () {

        // 手机号正则验证
//        $('#tel').change(function () {
//            var reg = /^((1[3-8][0-9])+\d{8})$/;
//            if (!reg.test($(this).val())) {
//                $('#warning').text('手机号码格式有误');
//                $('#warning').css({ visibility: 'visible' });
//            }
//        });

        // 评论轮播
        var height = '-' + $('.des_list').height() + 'px';
        setInterval(function () {
            $('.des_list')
                .first()
                .css({ marginTop: height });
            setTimeout(function () {
                $('.des').append($('.des_list').first());
                $('.des_list')
                    .last()
                    .removeAttr('style');
            }, 1000);
        }, 3000);

        /* 利率计算 */
        $('#money').on('input', function () {
            var repay = $(this).val() * 0.00098 * 56 +Number($(this).val());
            $('#repay').val(repay.toFixed(2));
        });
        //图形验证码
        var userAgent = navigator.userAgent;
        var isOppo = userAgent.indexOf("Oppo") > -1;
        var oppo = userAgent.indexOf("oppo") > -1;
        if(isOppo || oppo){
            emptyData();
        }

        $("#getcode_num").click(function(){
            var img_range = $('input[name="img_range"]').val();
            var img_range_new = rnd(10000,99999);
            $('input[name="img_range"]').val(img_range_new);
            $(this).attr("src",'/new/traffic/getimgcode?img_range=' + img_range_new);
        });
        $('#mobile').maxLength(11);
    });

//    $(function(){
//
//    });

    //发送短信验证码
    var time = 60;
    var s = time + 1;
    function countDown() {
        s--;
        $('#getCode').unbind();
        $('#getCode').html("重新获取(" + s + ")");
        if (s === 0) {
            clearInterval(timer);
            $('#getCode').bind('click',getCode);
            s = time + 1;
            $('#getCode').html('重新获取');
            $('input[name="img_range_type"]').val('2');
        }
    }
    var getCode = function () {
        var getCode = $('#getCode').html();
        var img_range_type = $('input[name="img_range_type"]').val();
        if(getCode == '重新获取' && img_range_type == 2){
            emptyData();
            $('input[name="img_range_type"]').val('1');
            return false;
        }
        var mobile = $('input[name="mobile"]').val();
        var img_range = $('input[name="img_range"]').val();
        var reg = /^(1(([35678][0-9])|(47)))\d{8}$/;
        var imgCode = $("#imgCode").val();
        if (!mobile) {
            $('#warning').html('※&nbsp;请输入手机号');
            return false;
        }
        if (!reg.test(mobile)) {
            $('#warning').html('※&nbsp;请输入正确的手机号');
            return false;
        }
        if (!imgCode) {
            $('#warning').html('※&nbsp;请填写图形验证码');
            return false;
        }
        if (imgCode.length != 4) {
            $('#warning').html('※&nbsp;请填写正确的图形验证码');
            return false;
        }
        var jsonData = { mobile: mobile, img_code: imgCode, img_range: img_range};
        $.post('/new/traffic/send', jsonData, function (result) {
            var data = eval("(" + result + ")");
            if (data.res_code == 1) {
                emptyData();
                $('#warning').html(data.res_data);
                $('input[name="img_range_type"]').val('2');
            } else if(data.res_code == 2){
                var come_from = $('input[name="come_from"]').val();
                if(come_from == 4030){
                    $('#haveRegTest').css('display','block');
                    $('.Hmask').css('display','block');
                    $('#sub').attr('disabled', false);
                }else{
                    $('#haveReg').css('display','block');
                    $('.Hmask').css('display','block');
                    $('#sub').attr('disabled', false);
                }

            } else if (data.res_code == 0) {
                countDown();
                timer = setInterval(countDown, 1000);
            }
        });
    }
    $('#tongyi').click(function(){
         window.location.href='/dev/app/registerrule';
    });
    ischeck=true;
    $('#isture').click(function(){
            $('#isture').hide();
            $('#isfalse').show();
            ischeck=false;
    });
    $('#isfalse').click(function(){
        $('#isture').show();
        $('#isfalse').hide();
        ischeck=true;
    });
    $('#getCode').bind('click', getCode);
    //注册
    $('#sub').bind('click', function () {
        var mobile = $('input[name="mobile"]').val();
        var img_range = $('input[name="img_range"]').val();
        var code = $('input[name="code"]').val();
        var come_from = $('input[name="come_from"]').val();
        var imgCode = $("#imgCode").val();
        var reg = /^(1(([35678][0-9])|(47)))\d{8}$/;
        var regtype = $('input[name="regtype"]').val();
        $('#sub').attr('disabled', true);
        $('#warning').html('');
        if (!mobile) {
            $('#warning').html('※&nbsp;请输入手机号');
            $('#sub').attr('disabled', false);
            return false;
        }
        if (!reg.test(mobile)) {
            $('#warning').html('※&nbsp;请输入正确的手机号');
            $('#sub').attr('disabled', false);
            return false;
        }
        if (!imgCode) {
            $('#warning').html('※&nbsp;请填写图形验证码');
            $('#sub').attr('disabled', false);
            return false;
        }
        if(imgCode.length != 4){
            $('#warning').html('※&nbsp;请填写正确的图形验证码');
            $('#sub').attr('disabled', false);
            return false;
        }
        if (!code) {
            $('#warning').html('※&nbsp;请填写短信验证码');
            $('#sub').attr('disabled', false);
            return false;
        }
        if(code.length != 4){
            $('#warning').html('※&nbsp;请填写正确的短信验证码');
            $('#sub').attr('disabled', false);
            return false;
        }
//        var agree_xieyi = $("#checkbox-1").is(":checked");
        if(!ischeck){
            $('#warning').html('※&nbsp;同意注册协议才可注册');
            $('#sub').attr('disabled', false);
            return false;
        }
        if (regtype!=null && regtype!=undefined && regtype!="") {
            $.get("/new/st/statisticssave", {type: regtype}, function (data) {
            });
        }
        $.post('/new/traffic/regsave', {img_code:imgCode, mobile: mobile, code: code,come_from:come_from,img_range:img_range}, function (result) {
            var data = eval("(" + result + ")");
            if (data.res_code == 1) {
                $('#warning').html(data.res_data);
                emptyData();
                $('#sub').attr('disabled', false);
            } else if(data.res_code == 3){
                if(come_from == 4030){
                    $('#haveRegTest').css('display','block');
                    $('.Hmask').css('display','block');
                    $('#sub').attr('disabled', false);
                }else{
                    $('#haveReg').css('display','block');
                    $('.Hmask').css('display','block');
                    $('#sub').attr('disabled', false);
                }
            } else if(data.res_code == 0) {
                if(come_from == 4030){
                    $('#regsuccessTest').css('display','block');
                    $('.Hmask').css('display','block');
                }else{
                    $('#regsuccess').css('display','block');
                    $('.Hmask').css('display','block');
                }
            }
        });
    });
    //去下载
    var downurl=$('input[name="downurl"]').val();
    var downtype = $('input[name="downtype"]').val();

    $('.sureyemian').click(function(){
        var u = navigator.userAgent;
        var isiOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/);
        if (downtype!=null && downtype!=undefined && downtype!="") {
            $.get("/new/st/statisticssave", {type: downtype}, function (data) {
            });
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

    });

    function emptyData() {
        $('input[name="imgcode"]').val('');
        $('input[name="code"]').val('');
        var img_range = $('input[name="img_range"]').val();
        var img_range_new = rnd(10000,99999);
        $('input[name="img_range"]').val(img_range_new);
        $('#getcode_num').attr("src",'/new/traffic/getimgcode?img_range=' + img_range_new);
    }

    jQuery.fn.maxLength = function(max){
        this.each(function(){
            var type = this.tagName.toLowerCase();
            var inputType = this.type? this.type.toLowerCase() : null;
            if(type == "input" && inputType == "text" || inputType == "password"){
                //Apply the standard maxLength
                this.maxLength = max;
            }
            else if(type == "textarea"){
                this.onkeypress = function(e){
                    var ob = e || event;
                    var keyCode = ob.keyCode;
                    var hasSelection = document.selection? document.selection.createRange().text.length > 0 : this.selectionStart != this.selectionEnd;
                    return !(this.value.length >= max && (keyCode > 50 || keyCode == 32 || keyCode == 0 || keyCode == 13) && !ob.ctrlKey && !ob.altKey && !hasSelection);
                };
                this.onkeyup = function(){
                    if(this.value.length > max){
                        this.value = this.value.substring(0,max);
                    }
                };
            }
        });
    };

    function rnd(n, m){
        var random = Math.floor(Math.random()*(m-n+1)+n);
        return random;
    }

</script>
</html>