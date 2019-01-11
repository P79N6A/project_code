<div class="jdall">
    <div class="jdyyy">
        <div class="jdimg">
            <img style="width:20%; margin-left:40%;" src="/images/studyimg3.png">
        </div>

        <div class="dbk_inpL">
            <label>姓名：</label><span class="mingzi"><?php echo $user->realname; ?></span>
        </div>
        <div class="dbk_inpL">
            <label>电话：</label><span class="mingzi"><?php echo $user->mobile; ?></span>
        </div>
    </div>
    <div class="tsmes" id="tsmes1"></div>
    <div class="zyzsty" style="color: #52587c; padding-left:5%;">
        <input type="checkbox" checked="checked" class="zhima">
        同意并授权<a href="/new/agreeloan/wapsesaauth" style="color: #52587c;">《芝麻信用授权协议》</a>
    </div>
    <div class="button button_next" id="button_next" > <button>开始认证</button></div>

    <div style="padding: 10px 5%; font-size: 1rem;  color: #444;">
        <p>移动用户请拨打中国移动客服热线10086转人工服务重置密码</p>
        <p>联通用户请拨打中国联通客服热线10010转人工服务重置密码</p>
        <p> 电信用户请拨打中国电信客服热线10000转人工服务重置密码</p>
    </div>
</div>

<div class="duihsucc" id="duihsuccdown" hidden>
    <p class="xuhua">认证失败!</p>
    <p>详情请下载APP咨询线上客服</p>
    <button class="sureyemian">下载APP</button>
</div>

<!--请稍候蒙层-->
<div style="width: 100%; height: 100%;background: rgba(0,0,0,.7); position: fixed;top: 0;left: 0; z-index: 100;" id ="loadings" hidden></div>
<div class="loading" hidden>
    <img src="/images/loadings.gif">
    <p class="pleasesh">请稍后...</p>
</div>

<div class="duihsucc" id="zhimawindow" hidden>
    <p class="xuhua"></p>
    <p>请勾选协议</p>
    <button class="sureyemian" onclick="closewindow()">确认</button>
</div>

<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script type="text/javascript">

    var csrf = '<?php echo $csrf; ?>';
    //输入服务密码时点击下一步
    $('.button_next').click(function (){
        //监测协议勾选
        if(!$('.zhima').prop('checked')){
            $('#loadings').show();
            $('#zhimawindow').show();
            return false;
        }
        $("#tsmes1").text('');
        $('#loadings').show();
        $('.loading').show();
        $.ajax({
            type: "POST",
            dataType: "json",
            data:{'_csrf':csrf},
            url: "/new/mobileauth/phoneajax",
            async: true,
            error: function(result) {
                $('#loadings').hide();
                $('.loading').hide();
                $("#tsmes1").text('*网络出错');
                return false;
            },
            success: function(result) {
                console.log(result);
                message(result);
            }
        });
    });

    /**
     * 信息处理
     * @params array result {"res_code":res_code, "res_data":res_data}
     * @resutl null
     */
    function message(result){
        if (result.res_code == 0 && result.res_data.status == 0){//跳转至开放平台，开始认证
            var location_href = result.res_data.url;
            location.href = location_href;
        }else if(result.res_code == 0 && result.res_data.status == 1){//采集成功
            location.href = result.res_data.nextUrl;
        }else if(result.res_code == 0 && result.res_data.status == 4){//采集拉取中
            location.href = result.res_data.nextUrl;
        }else if(result.res_code == 0 && result.res_data.status == 3){//失败
            $('#loadings').hide();
            $('.loading').hide();
            $("#tsmes1").text(result.res_data);
            return false;
        }else{
            $('#loadings').hide();
            $('.loading').hide();
            $("#tsmes1").text(result.res_data);
            return false;
        }

    }

    //关闭协议弹窗
    function closewindow() {
        $('.zhima').prop('checked', true);
        $('#loadings').hide();
        $('#zhimawindow').hide();
    }

    //微信参数
    wx.config({
        debug: false,
        appId: '<?php echo $jsinfo['appid']; ?>',
        timestamp: <?php echo $jsinfo['timestamp']; ?>,
        nonceStr: '<?php echo $jsinfo['nonceStr']; ?>',
        signature: '<?php echo $jsinfo['signature']; ?>',
        jsApiList: [
            'hideOptionMenu'
        ]
    });

    wx.ready(function () {
        wx.hideOptionMenu();
    });
</script>
