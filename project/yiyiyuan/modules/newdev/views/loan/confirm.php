<link rel="stylesheet" type="text/css" href="/news/css/popup.css">
<div class="zuoyminew jk_item" >
    <div class="daihukan_cont matop0">
        <div class="daoqihk">
            <div class="xzuoym">
                <p>借款金额(元)</p>
                <p><span><?php echo $amount; ?></span></p>
            </div>
            <div class="xzuoym">
                <p>借款周期(天) </p>
                <p><span><?php echo $days; ?> </span> (分<?php echo $term; ?>期还)</p>
            </div>
        </div>
        <div class="rowym">
            <div class="corname">到账金额(元)</div>
            <div class="corliyou" ><?php echo $getamount; ?></div>
        </div>
        <?php if ($withdraw > 0): ?>
            <div class="rowym">
                <div class="corname">保险费(元)</div>
                <div class="corliyou"><?php echo $withdraw; ?></div>
            </div>
        <?php endif; ?>
        <div class="rowym">
            <div class="corname"><?php echo $term; ?>期总利息(元)</div>
            <div class="corliyou" ><?php echo $interest; ?></div>
        </div>
    </div>
    <div class="jinemes none_yhui" >
        <?php if (empty($couponlist)) { ?>
            <span class="zitibh">优惠券(元) <span>暂无可用优惠券</span></span>
        <?php } else { ?>
            <select onchange="changeCoupon(this.options[this.options.selectedIndex].value)" id="zitibh" class="zitibh" style="width: 100%;appearance: none;-moz-appearance: none; -webkit-appearance: none; background: rgba(0,0,0,0)">
                <?php foreach ($couponlist as $k => $v) { ?>
                    <option <?php
                    if ($coupon_id == $v['id']) {
                        echo "selected";
                    }
                    ?> value="<?php echo $v['id']; ?>">
                    <?php if ($v['val'] >= $interest || $v['val'] == 0): ?>
                        <span class="zitibh">优惠券 <span style="color: #c90000;font-weight:bold;font-size: 1.142rem;">-<?php echo $interest; ?>元</span></span>
                    <?php else: ?>
                        <span class="zitibh">优惠券 <span style="color: #c90000;font-weight:bold;font-size: 1.142rem;">-<?php echo $v['val']; ?>元</span></span>
                    <?php endif; ?>
                    </option>
                <?php } ?>
            </select>
        <?php } ?>
        <div class="tuzizi"><img src="/290/images/right_jt.png"></div>
    </div>
<!--    <a href="/new/loan/jgcoupon">-->
<!--        <div class="jinemes none_yhui" >-->
<!--            --><?php //if (empty($couponlist)) { ?>
<!--                <span class="zitibh">优惠券(元) <span>暂无可用优惠券</span></span>-->
<!--            --><?php //} else { ?>
<!--                --><?php //if($couponInfo){?>
<!--                    --><?php //echo $couponInfo->title; ?><!-------><?php //echo $couponInfo->val; ?><!-- ----->
<!--                --><?php //}?>
<!--                <span class="zitibh">选择优惠券</span>-->
<!---->
<!--            --><?php //} ?>
<!--            <div class="tuzizi"><img src="/290/images/right_jt.png"></div>-->
<!--        </div>-->
<!--    </a>-->
    <div class="hkxqg hkjhua">
        <h3>还款计划</h3>
        <div class="youbianjl">
            <div class="youbianjlone">
                <span class="bianjlone"><?php echo $repay_plan[0]['term']; ?>/<?php echo $term; ?>期</span>
                <span class="bianjltwo">还款金额 <em> <i><?php echo $repay_plan[0]['repay_amount']; ?></i>元</em></span>
                <span class="bianjlthree"><?php echo $repay_plan[0]['repay_date']; ?>还款</span>
            </div>
            <div id="demo11"></div>
            <?php foreach ($repay_plan as $k => $v) { ?>
                <?php if ($k > 0): ?>
                    <div class="youbianjlone dis" style="display: none">
                        <span class="bianjlone"><?php echo $v['term']; ?>/<?php echo $term; ?>期</span>
                        <span class="bianjltwo">还款金额 <em> <i><?php echo $v['repay_amount']; ?></i>元</em></span>
                        <span class="bianjlthree"><?php echo $v['repay_date']; ?>还款</span>
                    </div>
                <?php endif; ?>
            <?php } ?>
        </div>
        <div id="demo12" hidden></div>
    </div>
    <?php if (!empty($bank)) { ?>
        <div class="jinemes none_yhui">
            <span class="zitibh">收款卡</span>
            <div class="wh_bank"><img src="<?php echo $bank['bank_icon_url']; ?>"><span><?php echo $bank['type']; ?></span><em>尾号<?php echo $bank['card']; ?></em></div>
        </div>
    <?php } ?>

    <div class="xieyidejinr">
        <input type="checkbox" checked="checked" id="agree_loan_xieyi" class="regular-checkbox">
        <label for="checkbox-1"></label>同意
        <a href="/new/agreeloan/contactlist" class="aColor">《服务及相关借款协议》</a>
    </div>
    <button type="submit" class="bgrey" id="loan_confirm_new">确认借款</button>
    <!--    <div class="dutsmeg">本次借款可能由银行、信托、消费金融公司等持牌金融机构向您提供，请您珍惜个人信用。</div>-->
</div>

<?php if ($isCungan['isOpen'] != 1) { ?>
    <div id="overDiv" class="opens_new"></div>
    <div class="newchange opens_new" style="margin: 85px 5%; padding-bottom: 20px;">
        <p class="error"></p>
        <h3 style="padding: 25px 0 3px; border-bottom: 0; font-size: 16px;">开通存管账户</h3>
        <p style="text-align: center;font-size:14px;">本平台现已接入银行存管体系，为保障您的资金安全，请马上开通存管账户</p>
        <button class="btnsure" id="opens_new" style="width: 80%; padding: 8px 0; margin: 20px 10% 10px; font-size:18px; font-weight: normal;">马上开户</button>
    </div>
<?php } elseif ($isCungan['isCard'] != 1) { ?>
    <div id="overDiv" class="bank_new"></div>
    <div class="newchange bank_new" style="margin: 85px 5%; padding-bottom: 20px;">
        <p class="error"></p>
            <img class="tccicon" style="position: absolute;top: -26%;width: 9%;right: 0;" src="/images/tccicon.png" onclick="closeCard()">
        <h3 style="padding: 25px 0 3px; border-bottom: 0; font-size: 16px;">本平台现已接入银行存管体系</h3>
        <p style="text-align: center;font-size:14px;">为保证您的资金安全，请马上绑定存管卡。</p>
        <button class="btnsure" id="bank_new" style="width: 80%; padding: 8px 0; margin: 20px 10% 10px; font-size:18px; font-weight: normal;">马上绑卡</button>
    </div>
<?php } ?>

<!--存管绑卡开始-->
<div class="newchange doCard" style="display: none;margin: 85px 5%;">
    <h3>设置默认卡</h3>
    <div class="dbk_inpL">
        <input name="cardVerifyCode" id="verifyCode" class="yzmwidth" placeholder="请输入6位验证码" type="text">
        <button class="hqyzm get_bankcode" onclick="getDepositoryCode('doCard')">获取验证码</button>
    </div>
    <p class="dxinwh">短信验证码已发送至您尾号<?php echo substr($userinfo->mobile, -4); ?>的手机上 </p>
    <button class="btnsure" id="doCard">确定</button>
</div>
<!--存管绑卡结束-->
<!-- 选卡开始 -->

<div id="overDiv" class="opens_new fukfsibank" style="display: none"></div>
<div class="fukfsis fukfsibank" style="display: none">
    <div class="errore">
        <span>选择银行卡绑定存管账户</span>
    </div>
    <?php if (!empty($userBanks)) { ?>
        <?php foreach ($userBanks as $k => $v) { ?>
            <input type="hidden" id="bankId" value="<?php
            if ($v['default_card'] == 1) {
                echo $v['bank_id'];
            }
            ?>">
               <?php } ?>
               <?php foreach ($userBanks as $k => $v) { ?>
            <div class="gzmess yumendyu" onclick="chkbank(<?= $v['bank_id']; ?>)">
                <div class="ymxinxi">
                    <p><img src="<?= $v['bank_icon_url']; ?>"></p>
                    <span><?php
                        if (!empty($v['type'])) {
                            echo $v['type'];
                        } else {
                            echo "银行卡";
                        };
                        ?>(<?= $v['card']; ?>)</span>
                    <em <?php if ($v['default_card'] != 1) { ?>style="display: none" <?php } ?> class="def def_<?= $v['bank_id']; ?>">
                        <img src="/299/images/zfufsi5.png">
                    </em>
                </div>
            </div>
        <?php } ?>
    <?php } ?>
    <div class="txtxtexs">
        <div class="chakan"><img src="/299/images/bottomjt.png"><span>添加银行卡 </span></div>
    </div>
    <button class="sure">确定</button>
</div>

<!-- 选卡结束 -->
<div class="jdyyy loading" style="display: none;width: 100%; height: 100%;background: rgba(0,0,0,0.2);background-color:#454647;position: fixed; top: 0;left: 0; z-index: 100;"></div>
<div class="loading" style="display: none;z-index:100;position: fixed; width: 40%; top:30%; left: 30%; background: rgba(0,0,0,0)">
    <img style="width: 50%; margin-left: 25%;" src="/images/loadings.gif">
</div>
<div style="display: none" class="blo"></div>
<input type="hidden" id="csrfs" value="<?php echo $csrf; ?>" >
<input type="hidden" name="desc" value="<?php echo $desc; ?>">
<input type="hidden" name="days" value="<?php echo $days; ?>">
<input type="hidden" name="amount" value="<?php echo $amount; ?>">
<input type="hidden" name="coupon_id" value="<?php echo $coupon_id; ?>">
<?php if (!empty($bank)) { ?>
    <input type="hidden" name="bank_id" value="<?php echo $bank['bank_id']; ?>">
<?php } ?>
<input type="hidden" name="mobile" value="<?php echo $userinfo->mobile; ?>">
<input type="hidden" name="coupon_amount" value="<?php echo $coupon_amount; ?>">
<input type="hidden" name="flag_confirm" value="<?= $flag ?>" />
<input type="hidden" name="business_type" value="<?php echo $business_type; ?>" >
<input type="hidden" name="term" value="<?php echo $term; ?>" >
<input type="hidden" name="goods_id" value="<?php echo $goods_id; ?>" >
<input type="hidden" id="srvAuthCode" value="">
<input type="hidden" id="openAuthCode" value="" />
<input type="hidden" id="cardAuthCode" value="" />
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
                var isopenBank = <?php echo $isopenBank; ?>;
                //生成借款
                function confs() {
                    var desc = $("input[name='desc']").val();
                    var days = $("input[name='days']").val();
                    var amount = $("input[name='amount']").val();
                    var coupon_id = $("input[name='coupon_id']").val();
                    var coupon_amount = $("input[name='coupon_amount']").val();
                    var mobile = $("input[name='mobile']").val();
                    var bank_id = $("input[name='bank_id']").val();
                    var business_type = $("input[name='business_type']").val();
                    var term = $("input[name='term']").val();
                    var goods_id = $("input[name='goods_id']").val();
                    var csrf = $("#csrfs").val();
                    $.post("/new/loan/userloan", {term: term, goods_id: goods_id, desc: desc, days: days, amount: amount, coupon_id: coupon_id, coupon_amount: coupon_amount, bank_id: bank_id, business_type: business_type, _csrf: csrf}, function (result) {
                        var data = eval("(" + result + ")");
                        $("#loan_confirm_new").attr('disabled', false);
                        if (data.rsp_code != '0000') {
                            alert(data.rsp_msg);
                            window.location = data.url;
                            return false;
                        }
                        window.location = data.url;
                    });
                }

                //发起借款
                $("#loan_confirm_new").click(function () {
                    tongji('loan_confirm_new');
                    var flag = $('input[name="flag_confirm"]').val();
                    if (flag == 2) {
                        alert("请添加其他银行卡");
                        return false;
                    }
                    var agree_xieyi = $("#agree_loan_xieyi").is(":checked");
                    if (agree_xieyi) {
                        var sign = $("input[name='sign']").val();
                        if (sign == 1) {
                            alert("请选择支持银行卡");
                            return false;
                        }
                        var isCard = '<?= $isCungan['isCard']; ?>';
                        if(isCard != '1'){
                            $('.bank_new').show();
                            return;
                        }
                        var isAuth = '<?= $isCungan['isAuth']; ?>';
                        var userId = <?= $userinfo['user_id']; ?>;
                        if (isAuth != '1') {
                            window.location = '/new/depositorynew?user_id=' + userId;
                            return;
                        }

                        $("#loan_confirm_new").attr('disabled', true);
                        confs();
                    } else {
                        alert('同意借款协议才能借款');
                        return false;
                    }
                });

                //马上开户
                $("#opens").click(function () {
                    var bank_id = $("input[name='bank_id']").val();
                    var csrf = $("#csrfs").val();
                    var isOpen = <?php echo $isCungan['isOpen']; ?>;
                    var isCard = <?php echo $isCungan['isCard']; ?>;
                    var isPass = <?php echo $isCungan['isPass']; ?>;
                    if (isOpen != 1) {
                        getDepositoryCode('doOpen');
                        return true;
                    }
                    if (isCard != 1) {
                        getDepositoryCode('doCard');
                        return true;
                    }
                    if (isPass != 1) {
                        setPwd(bank_id, csrf);
                        return true;
                    }
                })

                //马上开户-新
                $("#opens_new").click(function () {
                    var user_id = <?php echo $userinfo->user_id; ?>;
                    var csrf = $("#csrfs").val();
                    $("#opens_new").attr('disabled', true);
                    $.ajax({
                        type: "post",
                        url: "/new/depositorynew/newopenwx",
                        data: {user_id: user_id, _csrf: csrf},
                        async: false,
                        success: function (res) {
                            var datas = eval("(" + res + ")");
                            if (datas.res_code == '0000') {
                                window.location = datas.res_data;
                            } else {
                                alert('开户失败')
                                $("#opens_new").attr('disabled', false);
                            }
                        }
                    });
                })

                //马上绑卡-新
                $("#bank_new").click(function () {
                    tongji('banknew');
                    setTimeout(function(){
                        if (isopenBank == 2) {
                            $(".fukfsibank").show();
                            $(".bank_new").hide();
                        } else if (isopenBank == 1) {
                            var user_id = <?php echo $userinfo->user_id; ?>;
                            var csrf = $("#csrfs").val();
                            $("#bank_new").attr('disabled', true);
                            $.ajax({
                                type: "post",
                                url: "/new/depositorynew/newbankwx",
                                data: {user_id: user_id, _csrf: csrf},
                                async: false,
                                success: function (res) {
                                    var datas = eval("(" + res + ")");
                                    if (datas.res_code == '0000') {
                                        window.location = datas.res_data;
                                    } else {
                                        alert('请求绑卡失败')
                                        $("#bank_new").attr('disabled', false);
                                    }
                                }
                            });
                        }
                    },100);
                });

                //选择绑卡
                function chkbank(bankId) {
                    $(".def").hide();
                    $('.def_' + bankId).show();
                    $("#bankId").val(bankId);
                }

                //选卡后确认
                $(".sure").click(function () {
                    var bank_id = $("#bankId").val();
                    if (!bank_id) {
                        alert('请选择需要绑定的卡！')
                        return false;
                    }
                    getDepositoryCode('doCard');
                });

                //短信开户||绑卡_获取验证码
                function getDepositoryCode(type) {
                    $(".get_bankcode").attr("disabled", true);
                    if (type == 'doOpen') {
                        var mobile = $("input[name='mobile']").val();
                        var csrf = $("#csrfs").val();
                        $.ajax({
                            type: "post",
                            url: "/new/depository/getdcode",
                            data: {mobile: mobile, _csrf: csrf},
                            async: false,
                            success: function (res) {
                                var datas = eval("(" + res + ")");
                                if (datas.ret == '0') {
                                    $('#openAuthCode').val(datas.data);
                                    count = 60;
                                    countdown = setInterval(CountDowns, 1000);
                                    $(".openAccount").show();//短信开户
                                    return true;
                                } else {
                                    alert('开户失败')
                                }
                            }
                        });
                    } else if (type == 'doCard') {
                        var csrf = $("#csrfs").val();
                        var bank_id = $("#bankId").val();

                        $.ajax({
                            type: "post",
                            url: "/new/bank/defcard?id=" + bank_id,
                            data: {_csrf: csrf},
                            async: false,
                            success: function (res) {
                                var data = eval("(" + res + ")");
                                if (data.code == '0') {
                                    return false;
                                } else if (data.code == '1' || data.code == '2' || data.code == '4' || data.code == '5') {
                                    alert('绑卡失败')
                                    $("#doCard").attr("disabled", false);
                                    $("input[name='cardVerifyCode']").val('');
                                    return false;
                                } else if (data.code == '3') {//发送存管验证码
                                    $('#cardAuthCode').val(data.data);
                                    count = 60;
                                    countdown = setInterval(CountDowns, 1000);
                                    $('.doCard').show();
                                    $('.fukfsis').hide();
                                    return true;
                                }
                            }
                        });
                    }
                }               

                //设置密码
                var setPwd = function (bank_id, csrf) {
                    $(".loading").show();
                    $.post("/new/depository/setpwd", {bank_id: bank_id, _csrf: csrf}, function (ress) {
                        var pwddata = eval("(" + ress + ")");
                        if (pwddata.ret == '0') {//接口调用成功前往设置密码页面
                            $(".loading").hide();
                            $('.blo').html(pwddata.data);
                        } else if (pwddata.ret == '4') {//已经设置过密码
                            $(".loading").hide();
                            location.href = "/new/loan/second";
                        } else {
                            $(".loading").hide();
                            alert(pwddata.msg);
                            return false;
                        }
                    });
                }

                //存管_绑卡操作
                $("#doCard").click(function () {
                    $("#doCard").attr("disabled", true);
                    var code = $("input[name='cardVerifyCode']").val();
                    var bank_id = $("#bankId").val();
                    var mobile = $("input[name='mobile']").val();
                    var srvAuthCode = $('#cardAuthCode').val();
                    var csrf = $("#csrfs").val();
                    if (code == '' || code == null) {
                        alert('请获取验证码');
                        $("#doCard").attr("disabled", false);
                        return false;
                    }
                    $.post("/new/bank/binding", {srvAuthCode: srvAuthCode, mobile: mobile, code: code, bank_id: bank_id, _csrf: csrf}, function (res) {
                        var datas = eval("(" + res + ")");
                        if (datas.ret != '0') {
                            $('.doCard').hide();
                            $(".noSensation").show();
                            alert(datas.msg)
                            window.location.reload();
                        } else {
                            $('.doCard').hide();
                            $(".noSensation").show();
                            //设置密码
                            setPwd(bank_id, csrf);
                        }
                    });
                });

                //倒计时
                var CountDowns = function () {
                    $(".get_bankcode").attr("disabled", true).addClass('dis');
                    $(".get_bankcode").html("重新获取 ( " + count + " ) ");
                    if (count <= 0) {
                        $(".get_bankcode").html("获取验证码").removeAttr("disabled").removeClass('dis');
                        clearInterval(countdown);
                    }
                    count--;
                };

                $(".error").click(function () {
                    $("#overDiv").hide();
                    $(".newchange").hide();
                    $("#loan_confirm_new").attr('disabled', false);
                });

                $(".txtxtexs").click(function () {
                    window.location = '/new/bank';
                })

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

    function tongji(event) {
        <?php \app\common\PLogger::getInstance('weixin','',$userinfo['user_id']); ?>
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
    
    function closeCard() {
        tongji('closeCard');
        $('.bank_new').hide();
    }
</script>
