<?php
$url = '/new/account/distribute';
$newurl = '/new/account/renzheng';
?>
<div class="newzilaio">
    <div class="newmyzo1"><img src="/images/newmyzo1.png"></div>
    <div class="newself">
        <a href="javascript:void(0);" <?php if ($pinfo == '修改'): ?>onclick="basicInfo(1, '<?php echo '/borrow/userauth/modify'; ?>')"<?php else: ?>onclick="basicInfo(1, '<?php echo '/borrow/userauth/index'; ?>')"<?php endif; ?>>
            <div class="dbk_inpL">
                <label>身份信息</label>
                <p class="allws <?php echo $pinfo == '修改' ? 'yellow' : ''; ?>">
                    <?php echo $pinfo; ?>
                </p>
            </div>
        </a>
<!--        <a href="javascript:void(0);" <?php if ($cinfo == '修改'): ?>onclick="basicInfo(2, '<?php echo $url . '?type=2&from=workinfo'; ?>')"<?php else: ?>onclick="basicInfo(2, '<?php echo $url . '?type=1&from=workinfo'; ?>')"<?php endif; ?>>
            <div class="dbk_inpL">
                <label>工作信息</label>
                <p class="allws <?php echo $cinfo == '修改' ? 'yellow' : ''; ?>"><?php echo $cinfo; ?></p>
            </div>
        </a>-->
        <a href="javascript:void(0);" <?php if ($contacts == 1): ?>onclick="basicInfo(4, '<?php echo '/borrow/userauth/contacts'; ?>')"<?php else: ?>onclick="basicInfo(4, '<?php echo '/borrow/userauth/contacts'; ?>')"<?php endif; ?>>
            <div class="dbk_inpL">
                <label>联系人信息</label>
                <p class="allws <?php echo $contacts == 1 ? 'yellow' : ''; ?>">
                    <?php echo $contacts == 2 ? '未认证' : '修改'; ?>
                </p>
            </div>
        </a>
        <a href="javascript:void(0);" <?php if ($userinfo->status == 4 || $userinfo->status == 1): ?>onclick="basicInfo(3, '<?php echo '/borrow/userauth/pic'; ?>')"<?php endif; ?>>
            <div class="dbk_inpL">
                <label>视频认证</label>
                <p class="allws <?php echo $userinfo->status == 3 || $userinfo->status == 2 ? 'blue' : ($userinfo->status == 4 ? 'reds' : ''); ?>">
                    <?php echo $userinfo->status == 3 ? '已认证' : ($userinfo->status == 4 ? '未通过' : ($userinfo->status == 2 ? '审核中' : '未认证')); ?>
                </p>
            </div>
        </a>
   
        <?php if ($userinfo['status'] == 3): ?>
        <a href="javascript:void(0);" <?php if ($juli == 1): ?>onclick="basicInfo(5,'<?php echo '';?>')"<?php endif; ?>>            <?php else: ?>
                <a href="javascript:{$('.Hmask').show();$('.duihsucc2').show();}">
                <?php endif; ?>
                <div class="dbk_inpL">
                    <label>手机运营商认证</label>
                    <p class="allws <?php echo $juli == 1 ? '' : 'blue'; ?>">
                        <?php echo $juli == 1 ? '未认证' : '已认证'; ?>
                    </p>
                </div>
            </a>
    </div>
    <div class="newmyzo1"><img src="/images/newmyzo2.png"></div>
    <div class="newself">
        <?php if ($xyk == 3) { ?>
            <a href="javascript:{$('.Hmask').show();$('.duihsucc2').show();}">
            <?php } else { ?>
                <a href="javascript:void(0);" <?php if ($xyk == 1): ?>onclick="doBank()"<?php endif; ?>>
                <?php } ?>
                <div class="dbk_inpL">
                    <label>信用卡认证</label>
                    <p class="allws <?php echo $xyk == 1 || $xyk == 3 ? '' : 'blue'; ?>"><?php if ($xyk == 1 || $xyk == 3) {
                    echo '未认证';
                } else {
                    echo '已认证';
                } ?></p>
                </div>
            </a>
    </div>
    <div class="newself" <?php if ($gongjijin == 1): ?>onclick="toAuth('<?php echo $userinfo->id ?>', 3, this)"<?php endif; ?>>
        <a href="javascript:void(0);">
            <div class="dbk_inpL">
                <label>公积金认证</label>
                <p class="allws <?php echo $gongjijin == 2 ? 'blue' : ''; ?>"><?php echo $gongjijin_name; ?></p>
            </div>
        </a>
    </div>
    <div class="newself" <?php if ($shebao == 1): ?>onclick="toAuth('<?php echo $userinfo->id ?>', 2, this)"<?php endif; ?>>
        <a href="javascript:void(0);">
            <div class="dbk_inpL">
                <label>社保认证</label>
                <p class="allws <?php echo $shebao == 2 ? 'blue' : ''; ?>"><?php echo $shebao_name; ?></p>
            </div>
        </a>
    </div>
    <div class="newself" <?php if ($xueli == 1): ?>onclick="toAuth('<?php echo $userinfo->id ?>', 1, this)"<?php endif; ?>>
        <a href="javascript:void(0);">
            <div class="dbk_inpL">
                <label>学历认证</label>
                <p class="allws <?php echo $xueli == 2 ? 'blue' : ''; ?>"><?php echo $xueli_name; ?></p>
            </div>
        </a>
    </div>

 
    
    <div style="text-align: center;margin-top: 20px;">完善选填资料提升可借额度，加速审核速度</div>
</div>
<div class="Hmask" style="display:none;"></div>
<div class="duihsucc2" style="display:none;">
    <p class="errore"><img src="/images/closed.png"></p>
    <p class="xuhua">请完善基本信息</p>
    <button type="button" class="sureyemian">确定</button>
</div>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script src="/newdev/js/log.js" type="text/javascript" charset="utf-8"></script>
<script>
<?php \app\common\PLogger::getInstance('weixin', '', $userinfo->user_id); ?>
<?php $json_data = \app\common\PLogger::getJson(); ?>
                var baseInfoss = eval('(' + '<?php echo $json_data; ?>' + ')');

                var csrf = '<?php echo $csrf; ?>';
                $('.errore').click(function () {
                    $('.duihsucc2').hide();
                    $('.Hmask').hide();
                });
                $('.sureyemian').click(function () {
                    $('.duihsucc2').hide();
                    $('.Hmask').hide();
                });
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

                function basicInfo(type, url) {
                    if (type == 1) {
                        tongji('realname_auth', baseInfoss);
                    } else if (type == 2) {
                        tongji('work_auth', baseInfoss);
                    } else if (type == 3) {
                        tongji('photo_auth', baseInfoss);
                    } else if (type == 4) {
                        tongji('contacts_auth', baseInfoss);
                    } else if (type == 5) {
                        tongji('phone_auth', baseInfoss);
                        do_juxinli_valid();
                        return false;
                    }
                    setTimeout(function () {
                        window.location.href = url;
                    }, 100);
                }

                //1学历、2社保、3公积金
                function toAuth(userId, type, obj) {
                    if ($(obj).hasClass('lock')) {
                        return false;
                    }
                    if (type == 1) {
                        tongji('xuexin', baseInfoss);
                    } else if (type == 2) {
                        tongji('shebao', baseInfoss);
                    } else if (type == 3) {
                        tongji('gongjijin', baseInfoss);
                    }
                    $(obj).addClass("lock");
                    $.ajax({
                        type: "POST",
                        url: "/borrow/selection",
                        data: {_csrf: csrf, user_id: userId, type: type},
                        datatype: "json",
                        async: true,
                        success: function (data) {
                            data = eval('(' + data + ')');
                            if (data.code == 1) {
                                setTimeout(function () {
                                    window.location.href = data.data;
                                }, 100);
                            } else {
                                $(obj).removeClass('lock');
                                alert(data.msg);
                            }
                        }
                    });
                }
                
                

                //信用卡
                function doBank() {
                    tongji('do_bank', baseInfoss);
                    setTimeout(function () {
                        window.location.href = '/borrow/userauth/card?type=2';
                    }, 100);
                }
                
                
               //去运营商手机认证
    function do_juxinli_valid(){
        tongji('requireinfo_do_juxinlivalid',baseInfoss);
//        $('#loadings').show();
//        $('.loading').show();
        $.ajax({
            type: "POST",
            dataType: "json",
            data:{'_csrf':csrf},
            url: "/borrow/juxinliauth/phoneajax",
            async: true,
            error: function(result) {
                   alert('*网络出错');
//                $("#backError").text('*网络出错');
//                hideDiv('backError');
                return false;
            },
            success: function(result) {
                console.log(result);
                message(result);
            }
        });
    }
    
        function message(result){
        if (result.res_code == 0 && result.res_data.status == 0){//跳转至开放平台，开始认证
            location.href = result.res_data.url;
        }else if(result.res_code == 0 && result.res_data.status == 1){//采集成功
            location.href = '/borrow/userinfo/requireinfo';
        }else if(result.res_code == 0 && result.res_data.status == 4){//采集拉取中
            location.href = '/borrow/userinfo/requireinfo';
        }else if(result.res_code == 0 && result.res_data.status == 3){//失败
//            $("#backError").text(result.res_data);
//            hideDiv('backError');
            return false;
        }else{
//            $("#backError").text(result.res_data);
//            $("#backError").show();
//            hideDiv('backError');
            return false;
        }

    }
</script>