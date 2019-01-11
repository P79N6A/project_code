<link rel="stylesheet" type="text/css" href="/news/css/popup.css">
<div class="Hcontainer nP">
    <div class="main">
        <div class="border1 jcbd">
            <ul>
                <li>
                    <div class="col-xs-3 text-right n26 grey2">姓名</div>
                    <div class="col-xs-8 n26 grey4"><?php echo $userbank->user->realname; ?></div>
                </li>
                <li>
                    <div class="col-xs-3 text-right n26 grey2">银行卡号</div>
                    <div class="col-xs-8 n26 grey4"><?php echo substr($userbank->card, 0, 4) . '*******' . substr($userbank->card, strlen($userbank->card) - 4, 4); ?></div>
                </li>
                <li>
                    <div class="col-xs-3 text-right n26 grey2">身份证号</div>
                    <div class="col-xs-8 n26 grey4"><?php echo substr($userbank->user->identity, 0, 4) . '**********' . substr($userbank->user->identity, strlen($userbank->user->identity) - 4, 4); ?></div>
                </li>
                <li>
                    <div class="col-xs-3 text-right n26 grey2">手机号码</div>
                    <div class="col-xs-8 n26 grey4"><?php echo substr($userbank->user->mobile, 0, 3) . '********'; ?></div>
                </li>
                <?php if ($userbank->type == 1): ?>
                    <li>
                        <div class="col-xs-3 text-right n26 grey2">有效期</div>
                        <div class="col-xs-8 n26 grey4"><?php echo substr($userbank->validate, 0, 2) . '月' . substr($userbank->validate, 2) . '年'; ?></div>
                    </li>
                    <li>
                        <div class="col-xs-3 text-right n26 grey2">卡验证码</div>
                        <div class="col-xs-8 n26 grey4"><?php echo $userbank->cvv2; ?> </div>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
        <?php if($userbank->default_bank == 0 && $userbank->type == 0 && !$hasDefault){ ?>
            <button class="btn mt40" style="width:100%;" onclick="defaults()">设为默认卡</button>
        <?php } ?>
        <button class="btn mt40" style="width:100%;" onclick="tishi()">解除绑定</button>
    </div>
    <div class="Hmask" style="display: none;"></div>
    <div class="layer_border overflow noBorder" style="display: none;">
        <p class="n28 mb30 padlr625">确定解绑该银行卡？</p>
        <div class="border_top_2 nPad overflow">
            <a href="javascript:{$('.layerDiv').hide();$('.layer_border').hide();};" class="n30 boder_right_1 text-center"><span class="grey2">取消</span></a>
            <a href="javascript:delbank();" class="n30 red text-center bRed"><span class="white ">确定</span></a>
        </div>
    </div>
    <div class="layer_border_def overflow noBorder " style="display: none;">
        <p class="n28 mb30 padlr625">确定设为默认银行卡？</p>
        <div class="border_top_2 nPad overflow">
            <a href="javascript:{$('.layerDiv').hide();$('.layer_border_def').hide();};" class="n30 boder_right_1 text-center"><span class="grey2">取消</span></a>
            <a href="javascript:defbank();" class="n30 red text-center bRed"><span class="white ">确定</span></a>
        </div>
    </div>

    <!--存管绑卡开始-->
    <div id="overDiv" class="doCard" style="display: none"></div>
    <div class="newchange doCard" style="display: none">
        <p class="error"><img src="/news/images/error.png"></p>
        <h3>设置默认卡</h3>
        <div class="dbk_inpL">
            <input name="verifyCode" id="verifyCode" class="yzmwidth" placeholder="请输入6位验证码" type="text">
            <button class="hqyzm get_bankcode" id="get_bankcode">获取验证码</button>
        </div>
        <p class="dxinwh">短信验证码已发送至您尾号<?php echo substr($userbank->user->mobile,-4);?>的手机上 </p>
        <button class="btnsure" id="bank">确定</button>
    </div>
    <!--存管绑卡失败-->
    <div class="srv" style="display: none"></div>
    <div id="overDiv" class="layerDiv" style="display: none"></div>
</div>
<script>
    var csrf = '<?php echo $csrf; ?>';
    function tishi() {
        $(".layerDiv").show();
        $('.layer_border').show();
    }
    function defaults() {
        $(".layerDiv").show();
        $('.layer_border_def').show();
    }
    function delbank() {
        $('.layerDiv').hide();
        $('.layer_border').hide();
        $.ajax({
            type: "POST",
            dataType: "json",
            url: "/new/bank/delcard?id=<?php echo $userbank->id; ?>",
            async: false,
            data: {'_csrf':csrf},
            error: function(data) {
            },
            success: function(data) {
                if (data.code == '0') {
                    alert(data.message);
                    location.href = '/new/bank';
                } else if (data.code == '2') {
                    alert(data.message);
                } else {
                    alert(data.message);
                    location.href = '/new/bank';
                }
            }
        });
    }
    function defbank() {
        $('.layerDiv').hide();
        $('.layer_border_def').hide();
        $.ajax({
            type: "POST",
            dataType: "json",
            url: "/new/bank/defcard?id=<?php echo $userbank->id; ?>",
            async: false,
            data: {'_csrf':csrf},
            error: function(data) {
            },
            success: function(data) {
                if (data.code == '0') {
                    alert(data.message);
                    location.href = '/new/bank';
                } else if (data.code == '1' || data.code == '2' || data.code == '4' || data.code == '5') {
                    alert(data.message);
                } else if (data.code == '3') {//发送存管验证码
                    $('.srv').val(data.data);
                    count = 60;
                    countdown = setInterval(CountDowns, 1000);
                    $('.doCard').show();
                }
            }
        });
    }
    var CountDowns = function() {
        $("#get_bankcode").attr("disabled", true).addClass('dis');
        $("#get_bankcode").html("重新获取 ( " + count + " ) ");
        if (count <= 0) {
            $("#get_bankcode").html("获取验证码").removeAttr("disabled").removeClass('dis');
            clearInterval(countdown);
        }
        count--;
    };
    $(".error").click(function () {
        $(".doCard").hide();
    });
    $("#get_bankcode").click(function() {
        var mobile = '<?php echo $userbank->user->mobile; ?>';
        $.get("/new/bank/getbcode", {mobile: mobile, _csrf:csrf}, function(result) {
            var data = eval("(" + result + ")");
            $("#get_bankcode").attr('disabled', true);
            if (data.ret == '0') {//成功
                count = 60;
                countdown = setInterval(CountDowns, 1000);
            }else if (data.ret == '1'){//失败
                alert(data.msg);
                $("#get_bankcode").attr('disabled', false);
            }
        })
    })
    $("#bank").click(function() {
        var code = $("input[name='verifyCode']").val();
        var bank_id = '<?php echo $userbank->id; ?>';
        var mobile = '<?php echo $userbank->user->mobile; ?>';
        var srvAuthCode = $(".srv").val();
        $.post("/new/bank/binding",{srvAuthCode: srvAuthCode,mobile: mobile,code: code,bank_id: bank_id,_csrf:csrf}, function (res) {
            var datas = eval("(" + res + ")");
            if(datas.ret != '0'){
                alert(datas.msg);
                //$(".doCard").hide();
            }else{
                alert("设置成功");
                location.href = '/new/bank';
            }
        });
    });

    //蒙层点击关闭窗口
    $(".layerDiv").click(function () {
        $(".layerDiv").hide();
        $('.layer_border').hide();
        $('.layer_border_def').hide();
    });
</script>