<style>
    body{ background: #f3f3f3;}
    .tuika{margin-top:10px; background: #fff; padding: 5px 0; position: relative;}
    .tuika .cguanb{position: absolute; top:0; right:0; width: 5%;}
    .tuika a{ display: block; overflow: hidden; position: relative; padding: 5px 0;}
    .bank_nn .bank2 img{ float: left; width:19%;padding: 0 5px 0 5%;}
    .bank_nn .sendtwo{ float: left; margin-top:0.5rem;}
    .bank_nn p{font-size: 1.1rem;color: #444;}
    .bank_nn p.whao{ color: #999;font-size: 0.9rem; padding-top: 3px;}
    .bank_nn p span{ color: #444; font-size: 1rem; padding: 0 3px; border-radius: 50px; margin: 0 5px;}
    .bank_nn p em{ font-size: 1rem;}
    .bank_nn p.weihaom{color: #939ab0; font-size: 1rem; display: block;}
    .bank_nn img.rightjt{ position: absolute;width: 3%;right: 5%;top: 1.6rem;}
    .bank_nn  p.qszhi{position: absolute;right: 9%;top: 1.66rem; font-size: 0.8rem; color: #999;}

    .ymxinxi{ background: #fff; margin-top: 10px; overflow: hidden;}
    .ymxinxi p{float: left; width: 5%;  margin: 12px 8px 15px 8%;}
    .ymxinxi span{ float: left; font-size: 0.9rem; margin-top: 15px; color: #444; color: #c90000;}

    .suppot{ text-align: right; margin-right: 5%; margin-top: 10px; font-size: 0.9rem;}

    /*付款方式弹窗*/
    .Hmask {width: 100%; height: 100%;background: rgba(0,0,0,.7); position: fixed;top: 0;left: 0; z-index: 100;}
    .fukfsi {width: 100%;position: fixed;bottom: 0;left:0;z-index: 100;background: #fff;}

    .fukfsi .qvxiao {
        width: 100%;
        padding: 10px 0;
        text-align: center;
        display: block;
    }
    .mask {
        height: 100%;
        width: 100%;
        background: rgba(0, 0, 0, .5);
        position: fixed;
        left: 0;
        top: 0;
    }

    .tccontent {
        position: fixed;
        top: 20%;
        left: 11%;
        z-index: 999;
        background: #fff;
        width: 78%;
        border-radius: 18px;
        height: 190px;
    }

    .tccontent p {
        margin-top: 12px;
        font-size: 18px;
        color: #444444;
        font-weight: 700;
        text-align: center;
    }

    .tccontent img {
        width: 16px;
        height: 16px;
        position: absolute;
        right: 10px;
        top: 10px;
    }

    .tccontent span {
        margin-top: 20px;
        font-size: 14px;
        color: #444444;
        padding: 0 20px;
        display: inline-block;
    }

    .tccontent button {
        display: table;
        margin: 14px auto 10px;
        outline: none;
        border: 0px;
        width: 118px;
        height: 40px;
        line-height: 40px;
        text-align: center;
        font-size: 16px;
        color: #fff;
        background-image: linear-gradient(-90deg, #F00D0D 0%, #FF4B17 100%);
        border-radius: 6px;
    }
    .tishi_success{position: fixed; top:30%; width: 100%;text-align: center; color: #fff; font-size: 14px; padding: 5px 0;}
    .tishi_success a{text-align: center; color: #fff;background: rgba(0,0,0,.8);border-radius: 5px;padding: 10px 20px; }
</style>
<?php
$bank = array('ABC', 'ALL', 'BCCB', 'BCM', 'BOC', 'CCB', 'CEB', 'CIB', 'CMB', 'CMBC', 'GDB', 'HXB', 'ICBC', 'PAB', 'PSBC', 'SPDB', 'ECITIC');
?>
<div class="ttfukfsi">
    <div class="tuika <?php if (!empty($cunguan) && $cunguan['activate_result'] == 1 && !empty($cunguan['card'])): ?>hidden<?php endif; ?>" id="setbank">
        <a>
            <div class="bank_nn"> 
                <div class="bank2" ><img style="margin-top:7px;" src="/images/szcgxx.png"></div>
                <div class="sendtwo">
                    <p>设置存管卡</p> 
                    <p class="whao">为保障您的资金安全及实现借款</p>
                </div>
                <p class="qszhi">去设置</p>
                <img class="rightjt" src="/images/rightjt.png">
            </div>
        </a>
    </div>
    <?php if (count($banks) > 0): ?>
        <?php foreach ($banks as $key => $val): ?>
            <div class="tuika" onclick="showdelbank(<?php echo $val->id; ?>)">
                <?php if (!empty($cunguan) && $val->id == $cunguan->card): ?>
                    <img class="cguanb" src="/images/cguanb.png" style="width: 10%">
                <?php endif; ?>
                <a>
                    <div class="bank_nn"> 
                        <div class="bank2"><img src="/images/bank_logo/<?php
                            if (!empty($val['bank_abbr']) && in_array($val['bank_abbr'], $bank)) {
                                echo $val['bank_abbr'];
                            } else {
                                echo 'ICON';
                            }
                            ?>.png" width="10%"></div>
                        <div class="sendtwo">
                            <p><?php echo empty($val['bank_name']) ? '银行卡' : $val['bank_name']; ?> <span>借记卡</span></p> 
                            <p class="whao">尾号<?php echo substr($val['card'], strlen($val['card']) - 4, 4) ?></p>
                        </div>
                        <img class="rightjt" src="/images/rightjt.png">
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>    
    <?php if (count($banks) < 10): ?>
        <div class="ymxinxi">
            <a href="javascript:void(0);" onclick="addcard('<?php echo urlencode($orderInfo); ?>')">
                <p><img src="/images/zfufsi3.png"></p>
                <span class="xinxiazf">添加银行卡付款</span>
            </a>
        </div>
    <?php endif; ?>
    <a href="javascript:void(0);" onclick="banklist()">
        <p class="suppot">*支持银行卡列表</p>
    </a>
</div>
<input type="hidden" id="csrfs" value="<?php echo $csrf; ?>" >
<div class="Hmask" style="display:none;"></div>
<div class="fukfsi" id="delbank" bank_val="0" style="display:none;">
    <a class="qvxiao" id="delbanks">解除绑定</a> 
    <div style="height: 10px; background: #f3f3f3;"></div>
    <a class="qvxiao" onclick="hidedelbank()">取消</a> 
</div>
<div class="fukfsi" id="showPayAccount" bank_val="0" style="display:none;">
    <a class="qvxiao" id="showPayAccounts">查看存管详情</a>
    <div style="height: 10px; background: #f3f3f3;"></div>
    <a class="qvxiao" onclick="hidedelbank()">取消</a>
</div>
<div class="mask" id="toast_cg" hidden></div>
<div class="tccontent" id="toast_cg_pwd" hidden>
    <p>温馨提示</p>
    <img src="/borrow/310/images/bill-close.png" alt="" onclick="close_toast()">
    <span>很抱歉，您未绑定存管银行卡无法发起借款，请立即绑定，绑卡前需要先设置交易密码</span>
    <button onclick="dopwd()" id="dopwd">立即设置</button>
</div>
<div class="tishi_success" hidden><a>请先完成实名认证</a></div>
<script>
    var isApp = <?php
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'YYY_ANDROID') || strpos($_SERVER['HTTP_USER_AGENT'], 'yyyIOS')) {
            echo 1;
        } else {
            echo 2;
        }
    ?>

    pushHistory();
    var bool=false;
    setTimeout(function(){
        bool=true;
    },16);
    window.addEventListener("popstate", function(e) {
//        if(bool){
            setTimeout(function () {
                if (isApp == 1) {
                    window.myObj.closeHtml();
                    function closeHtml() {}
                }else{
                    window.location.href = "/borrow/account";
                    return false;
                }

            },16);
//            goApp();
//            //根据自己的需求返回到不同页面
//            setTimeout(function(){
//                window.location.href= '/borrow/account';
//            },100);
//        }
        pushHistory();
    }, false);
    function pushHistory() {
        var state = {
            url: "#"
        };
        window.history.pushState(state,  "#");
    }

    var user_id = <?php echo $user_id; ?>;
    var csrf = $("#csrfs").val();
    $('#setbank').click(function () {
        tongji('setbank');
        setTimeout(function(){
            location.href = '/borrow/custody/list?type=7';
        },100);
    });
    $("#showPayAccounts").click(function(){
        var userId = "<?php echo $user_id; ?>";
        window.location.href='/new/bank/cgdetail?user_id='+userId;
    })
    $("#delbanks").click(function () {
        tongji('delbanks');
        setTimeout(function(){
            var bank_id = $("#delbank").attr('bank_val');
            $.ajax({
                type: "POST",
                dataType: "json",
                url: "/new/bank/delcard?id=" + bank_id,
                async: false,
                data: {'_csrf': csrf},
                error: function (data) {
                },
                success: function (data) {
                    if (data.code == '0') {
                       if(data.data != ''){
                            location.href = data.data; 
                       }else{
                            alert(data.message);
                            location.href = '/new/bank';
                       }
                        
                    } else if (data.code == '2') {
                        alert(data.message);
                    } else {
                        alert(data.message);
                        location.href = '/new/bank';
                    }
                }
            });
        },100);
    });

    $(".Hmask").click(function () {
        $('#delbank').hide();
        $('#showPayAccount').hide();
    });

    //取消蒙层
    function close_toast(){
        $('#toast_cg').hide();
        $('#toast_cg_pwd').hide();
    }
    
    function dopwd() {
        $("#dopwd").attr('disabled', true);
        tongji('cunguan_setpwd');
        setTimeout(function(){
            location.href = '/borrow/custody/list';
        },100);
    }

    function tongji(event) {
        <?php \app\common\PLogger::getInstance('weixin','',$user_id); ?>
        <?php $json_data = \app\common\PLogger::getJson();?>
        var baseInfoss = eval( '('  + '<?php echo $json_data; ?>' + ')' );
        baseInfoss.url = baseInfoss.url+'&event='+event;
        console.log(baseInfoss);
        var ortherInfo = {
            screen_height: window.screen.height,//分辨率高
            screen_width: window.screen.width,  //分辨率宽
            user_agent: navigator.userAgent,
            height: document.documentElement.clientHeight || document.body.clientHeight,  //网页可见区域宽
            width: document.documentElement.clientWidth || document.body.clientWidth,//网页可见区域高
        };
        var baseInfos = Object.assign(baseInfoss, ortherInfo);
        var turnForm = document.createElement("form");
        turnForm.id = "uploadImgForm";
        turnForm.name = "uploadImgForm";
        document.body.appendChild(turnForm);
        turnForm.method = 'post';
        turnForm.action = baseInfoss.log_url+'weixin';
        //创建隐藏表单
        for (var i in baseInfos) {
            var newElement = document.createElement("input");
            newElement.setAttribute("name",i);
            newElement.setAttribute("type","hidden");
            newElement.setAttribute("value",baseInfos[i]);
            turnForm.appendChild(newElement);
        }
        var iframeid = 'if' + Math.floor(Math.random( 999 )*100 + 100) + (new Date().getTime() + '').substr(5,8);
        var iframe = document.createElement('iframe');
        iframe.style.display = 'none';
        iframe.id = iframeid;
        iframe.name = iframeid;
        iframe.src = "about:blank";
        document.body.appendChild( iframe );
        turnForm.setAttribute("target",iframeid);
        turnForm.submit();
    }

    function showdelbank(bank_id) {
        var cgCardId = "<?php echo $cgCardId; ?>";
        $('#delbank').attr('bank_val', bank_id);
        if(cgCardId == bank_id){
            $('#showPayAccount').toggle();
        }else {
            $('#delbank').toggle();
        }
        $('.Hmask').toggle();
    }

    function hidedelbank() {
        $('#delbank').hide();
        $('#showPayAccount').hide();
        $('.Hmask').hide();
    }

    function banklist() {
        tongji('banklist');
        setTimeout(function(){
            location.href = '/new/bank/quota';
        },100);
    }

    function addcard(orderinfo) {
        tongji('addcard');
        setTimeout(function(){
                $.ajax({
                    url: "/new/bank/addcardjump",
                    type: 'post',
                    async: false,
                    data: {_csrf: csrf},
                    success: function (json) {
                        json = eval('(' + json + ')');
                        console.log(json);
//                        return false;
                        if(json.res_code == '0000'){
                            location.href = '/new/bank/addcard?banktype=3&orderinfo='+orderinfo; 
                            return false;
                        }else if(json.res_code == '0001'){ //提示：请先完成实名认证
                            $(".tishi_success").show().delay(1000).hide(0);
                        }
                    },
                    error: function (json) {
                        console.log('请求失败');
                    }
                });
        },100);
     
//        setTimeout(function(){
//            location.href = '/new/bank/addcard?banktype=3&orderinfo='+orderinfo;
//        },100);
    }

    function goApp() {
        if (isApp == 1) {
            setTimeout(function () {
                window.myObj.closeHtml();
                function closeHtml() {
                }
            });
        }
    }

    //缩放比例
    if (/Android (\d+\.\d+)/.test(navigator.userAgent)) {
        var version = parseFloat(RegExp.$1);
        if (version > 2.3) {
            var phoneScale = parseInt(window.screen.width) / 375;
            document.write('<meta name="viewport" content="width=375, minimum-scale = ' + phoneScale +
                ', maximum-scale = ' + phoneScale + ', target-densitydpi=device-dpi">');
        } else {
            document.write('<meta name="viewport" content="width=375, target-densitydpi=device-dpi">');
        }
    } else {
        document.write('<meta name="viewport" content="width=375, user-scalable=no, target-densitydpi=device-dpi">');
    }
</script>