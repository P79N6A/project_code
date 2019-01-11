<style>
    html,body{width:100%;height:100%; font-family: "Microsoft YaHei";  position: relative;}
    a{color:#fff}
    .logotop{position: absolute; top:5%; width: 30%; left: 5%;}
    .wape {background: url(/newdev/images/traffic/bgbg.png) no-repeat; width: 100%; height: 100%; background-size:100% 100%;}
    .wape .wap5{ padding: 40px 0 10px;}
    .wape img { width: 100%;padding: 0;display: block;margin-top: -1px;}
    .wape .addreeb{ text-align: center; font-size: 0.85rem;color: #fff; padding-top: 1rem;  position: fixed; bottom: 1%; width: 100%;}
    .freemfei{ text-align: center; font-size: 13px; color: #4d5983;padding:5px 0;}
    .selftximg{position: relative;top:41%; margin:0 4%;border-radius: 0 0 5px 5px; }
    .selftximg .dbk_inpL {border-radius: 5px;padding: 10px 5% 0;overflow: hidden;}
    .selftximg .dbk_inpL input {padding:10px 0;width:96%;float: left;padding-left: 10px;font-size: 1.1rem;    background: #fff;border-radius:5px; border:1px solid #fff;}
    .selftximg .dbk_inpL  input.yzmwidth { width: 45%;}
    .selftximg .tsmes{color: #fff;margin:10px 0 5px; text-align: center;  }
    .selftximg .button{margin: 0 5%; margin-top: 12px;}
    .selftximg .button button {width: 100%;background:rgba(0,0,0,0);}
    .selftximg .dbk_inpL.dxyznmes{ position: relative;}
    .selftximg .dbk_inpL.dxyznmes .dxyzn{position: absolute;width: 35%;right: 5%; top: 1.6rem;font-size: 14px; background: rgba(0,0,0,0); /*color: #d01d1c;*/ color: #5a8dcf; border-left: 1px solid #9d9d9d;}
    .selftximg .dbk_inpL.dxyznmes .dxyzn.txyz{border:0;width: 28%; top: 1.1rem;}
    .main .noehtyxh{ display: table; margin: 10px auto;color:#fff }
    .main .regular-checkbox{display: none;}
    .main .regular-radio:checked + label {color: #fff;}
    .main .regular-radio + label {-webkit-appearance: none;padding: 7px;border-radius: 50px;display: inline-block;position: relative;color: #fff;}
    .main .regular-checkbox + label{
        position: relative;
        float: left;
        margin-right: 6px;
        width: 16px;
        height: 16px;
        background: #fff;
        border-radius: 50px;
        margin-top: 1px;
        color: #fff;
    }
    @media screen and (max-width: 365px) {
        .main .regular-checkbox + label{ width: 15px;
            height: 15px;
            border-radius: 50px;
            margin-top: 2px;
        }
    }
    .main .regular-radio:checked + label:after {
        content: ' ';
        width: 12px;
        height: 12px;
        border-radius: 50px;
        position: absolute;
        top: 3px;
        text-shadow: 0px;
        left: 2px;
        font-size: 32px;
    }
    .main .regular-checkbox:checked + label:after{
        content: ' ';
        width: 100%;
        height: 100%;
        background: url(/newdev/images/traffic/xyihand.png);
        background-size: 100% 100%;
        position: absolute;
        top: 0px;
        left: 0;
    }
    .Hmask {
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,.7);
        position: fixed;
        top: 0;
        left: 0;
        z-index: 100;
    }
    .duihsucc{width: 90%;position: fixed; top: 20%;left: 5%;border-radius: 5px; z-index: 100;background: #fff; color: #444;}
    .duihsucc button{ width: 40%; height: 35px; line-height: 35px; background: #c90000; color: #fff; font-size: 1.1rem;border-radius: 30px;}
    .duihsucc button.sureyemian{ margin: 15px 30% 20px;}
    .duihsucc p{ text-align: center; font-size: 1rem;}
    .duihsucc p.xuhua{ padding:30px 0 5px; font-size: 1.25rem;color: #c90000;}
    .duihsucc p span{ color: #c90000;}
</style>
<!--页面加载统计 type参数-->
<script  src='/new/st/statisticssave?type=<?=$type?>'></script>
<div class="wape">
    <div class="selftximg">
        <input type='hidden' name="downurl" value="<?=$downurl?>">
        <input type='hidden' name="type" value="<?=$type?>"><!--页面参数-->
        <input type='hidden' name="regtype" value="<?=$regtype?>"><!--点击注册 type参数-->
        <input type='hidden' name="downtype" value="<?=$downtype?>"><!--点击去下载 type参数-->
        <input type='hidden' name="come_from" value="<?=$come_from?>">
        <input type='hidden' name="img_range" value="<?=$img_range?>">
        <input type='hidden' name="img_range_type" value="1">
        <div class="dbk_inpL">
            <input name="mobile" placeholder="输入您的手机号码" type="text" id="mobile">
        </div>
        <div class="dbk_inpL dxyznmes">
            <input id="imgCode" maxlength="4" name="imgcode" placeholder="图形验证码" type="text">
            <button class="dxyzn txyz"><img id="getcode_num" src="/new/traffic/getimgcode?img_range=<?=$img_range?>"></button>
        </div>
        <div class="dbk_inpL dxyznmes">
            <input maxlength="4" name="code" placeholder="输入短信验证码" type="text">
            <button class="dxyzn" id="getCode">获取验证码</button>
        </div>
        <div class="tsmes"  id="warning"></div>
        <div class="button"> <button id="sub"><img src="/newdev/images/traffic/ljzhuce.png"></button></div>
        <div class="main">
            <div class="noehtyxh">
                <input type="checkbox" checked="checked" id="checkbox-1" class="regular-checkbox">
                <label for="checkbox-1"></label>
                同意
                <a href="/dev/app/registerrule" class="underL">《先花一亿元注册协议》</a>
            </div>
        </div>
        <p class="addreeb"><?=$com_name?></p>
    </div>
</div>
<div class="Hmask" hidden></div>
<div class="duihsucc" id="regsuccess" hidden>
    <p class="xuhua">恭喜您，注册成功！</p>
    <p>您已注册成功，现可在"先花一亿元"借款</p>
    <button class="sureyemian">立即借款</button>
</div>
<div class="duihsucc" id="haveReg" hidden>
    <p class="xuhua">您已经注册过了！</p>
    <p>您已注册过账号，可直接去"先花一亿元"借款</p>
    <button class="sureyemian">立即借款</button>
</div>
<script>
    //图形验证码
    $(function(){
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
        var reg = /^(1(([3578][0-9])|(47)))\d{8}$/;
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
                $('#haveReg').css('display','block');
                $('.Hmask').css('display','block');
                $('#sub').attr('disabled', false);
            } else if (data.res_code == 0) {
                countDown();
                timer = setInterval(countDown, 1000);
            }
        });
    }

    $('#getCode').bind('click', getCode);
    //注册
    $('#sub').bind('click', function () {
        var mobile = $('input[name="mobile"]').val();
        var img_range = $('input[name="img_range"]').val();
        var code = $('input[name="code"]').val();
        var come_from = $('input[name="come_from"]').val();
        var imgCode = $("#imgCode").val();
        var reg = /^(1(([3578][0-9])|(47)))\d{8}$/;
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
        var agree_xieyi = $("#checkbox-1").is(":checked");
        if(!agree_xieyi){
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
                $('#haveReg').css('display','block');
                $('#sub').attr('disabled', false);
            } else if(data.res_code == 0) {
                $('#regsuccess').css('display','block');
                $('.Hmask').css('display','block');
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
