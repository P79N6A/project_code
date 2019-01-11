<style>
    html, body {
        overflow: auto;
    }
    .poppay_mask{
        position: fixed;top: 0;left: 0;z-index: 1;height: 100%;width: 100%;background: #000;opacity: 0.5;
    }
    .mask_box{
        top: 38%;z-index: 2;height:2.65rem; width: 6.89rem;
        position: absolute;
        text-align: center;
        left: 50%;
        top: 50%;
        background: #fff;
        transform: translate(-50%,-50%);
        border-radius: 0.13rem;
    }
    .mask_title{
        top:1.6rem;
        font-size: 0.35rem;
        color: #444444;
        line-height: 0.48rem;
        width: 100%;
        position: absolute;
        left: 0;
        text-align: center;
        top: 0.5rem;
        font-weight: bold;
    }
    .add_btn{
        background-image: linear-gradient(-90deg, #F00D0D 0%, #FF4B17 100%);
        border-radius: 0.13rem;
        height: 0.9rem;
        width: 3rem;
        position: absolute;
        left: 50%;
        top:1.35rem;
        text-align: center;
        margin-left: -1.5rem;
        font-size: 0.43rem;
        color: #FFFFFF;
        line-height: 0.9rem;
    }
</style>
<section class="xy-content816">
    <div class="xy-con-list">
        <img src="/borrow/310/images/shenfenz.png" alt="">
        <div class="xy-con-txt">
            <p class="tp">身份信息</p>
        </div>
        <?php if ($identify_valid == 1): ?>
            <span class="xy-rz" onclick="do_identify_valid()">去认证</span>
        <?php elseif ($identify_valid == 2): ?>
            <span class="xy-rz" onclick="do_identify_valid()">修改</span>
        <?php endif; ?>
        <div class="line"></div>
    </div>
    <div class="xy-con-list">
        <img src="/borrow/310/images/fav.png" alt="">
        <div class="xy-con-txt">
            <p class="tp">联系人信息</p>
        </div>
        <?php if ($contacts_valid == 1 && ($identify_valid == 2)): ?>
            <span class="xy-rz" onclick="do_contacts_valid()">去认证</span>
        <?php elseif ($contacts_valid == 1 && $identify_valid == 1): ?>
            <span class="xy-qrz">去认证</span>
        <?php elseif ($contacts_valid == 2): ?>
            <?php if( $identify_valid == 1 ):?>
                    <span class="xy-qrz">修改</span>
            <?php else:?> 
               <span class="xy-rz" onclick="do_contacts_valid()">修改</span>
            <?php endif;?>
        <?php else: ?>
            <span class="xy-wc">已完成</span>
        <?php endif; ?>
        <div class="line"></div>
    </div>
    <div class="xy-con-list">
        <img src="/borrow/310/images/video.png" alt="">
        <div class="xy-con-txt">
            <p class="tp">视频认证</p>
        </div>
        <?php if (($pic_valid == 1 || $pic_valid == 3) && ($contacts_valid == 2 && $identify_valid == 2)): ?>
            <span class="xy-rz" onclick="do_viedo_valid()">去认证</span>
        <?php elseif (($pic_valid == 1 || $pic_valid == 3) && ($contacts_valid == 1 || $contacts_valid == 3 || $identify_valid == 1)): ?>
            <span class="xy-qrz">去认证</span>
        <?php elseif ($pic_valid == 2): ?>
            <span class="xy-wc">已完成</span>
        <?php elseif ($pic_valid == 4): ?>
            <span class="xy-wc">认证中</span>
        <?php else: ?>
            <span class="xy-qrz">去认证</span>
        <?php endif; ?>
        <div class="line"></div>
    </div>
    <div class="xy-con-list">
        <img src="/borrow/310/images/juxinli.png" alt="">
        <div class="xy-con-txt">
            <p class="tp">手机运营商认证</p>
        </div>
        <?php if (($juxinli_valid == 1 ||$juxinli_valid == 3 ) && ($contacts_valid == 2 && $identify_valid == 2 && $pic_valid == 2)): ?>
            <?php if($juxinli_valid == 1):?>
                <span class="xy-rz" onclick="do_juxinli_valid()">去认证</span>
            <?php elseif($juxinli_valid == 3):?>
                <span class="xy-rz" onclick="do_juxinli_valid()">已过期</span>
            <?php endif;?>
        <?php elseif (($juxinli_valid == 1 || $juxinli_valid == 3) && ($contacts_valid == 1 || $identify_valid == 1 || in_array($pic_valid, [1, 3, 4]))): ?>
            <?php if($juxinli_valid == 1):?>
                 <span class="xy-qrz">去认证</span>
            <?php elseif($juxinli_valid == 3):?>
                <span class="xy-qrz">已过期</span>
            <?php endif;?>
        <?php elseif ($juxinli_valid == 2): ?>
            <span class="xy-wc">已完成</span>
        <?php elseif ($juxinli_valid == 3): ?>
            <span class="xy-rz" onclick="do_juxinli_valid()">已过期</span>
        <?php endif; ?>
    </div>
</section>

<section class="xy-content816 xy-margin">
    <div class="xy-con-list">
        <img src="/borrow/310/images/xinyongka.png" alt="">
        <div class="xy-con-txt">
            <p class="tp">信用卡认证</p>
        </div>
        <?php if ($bank_valid == 1 && ($contacts_valid == 2 && $identify_valid == 2 && $pic_valid == 2 && in_array($juxinli_valid, [2, 3]))): ?>
            <span class="xy-rz" onclick="do_card_valid()">去认证</span>
        <?php elseif ($bank_valid == 2): ?>
            <span class="xy-wc">已完成</span>
        <?php elseif ($bank_valid == 1 && ($contacts_valid == 1 || $identify_valid == 1 || in_array($pic_valid, [1, 3, 4]) || $juxinli_valid == 1)): ?>
            <span class="xy-qrz">去认证</span>
        <?php endif; ?>
        <div class="line"></div>
    </div>
    <div class="xy-con-list">
        <img src="/borrow/310/images/gongjijin.png" alt="">
        <div class="xy-con-txt">
            <p class="tp">公积金认证</p>
        </div>
        <?php if( ($fund_valid == 1 || $fund_valid == 4)&&($contacts_valid ==2 && $identify_valid == 2 &&  $pic_valid== 2 && in_array($juxinli_valid, [2,3])) ):?>
            <span class="xy-rz" onclick="toAuth('<?php echo $user_id ?>', 3, this)">
                    <?php if ($fund_valid == 1): ?>
                        去认证
                    <?php elseif ($fund_valid == 4): ?>
                        已过期
                    <?php endif; ?>
            </span>
        <?php elseif($fund_valid == 1 && ($contacts_valid == 1 || $identify_valid == 1 || in_array($pic_valid, [1, 3, 4]) || $juxinli_valid == 1)):?>
            <span class="xy-qrz">去认证</span>
        <?php elseif ($fund_valid == 2): ?>
            <span class="xy-wc">已完成</span>
        <?php elseif ($fund_valid == 3): ?>
            <span class="xy-wc">认证中</span>
        <?php endif; ?>
        <div class="line"></div>
    </div>
    <div class="xy-con-list">
        <img src="/borrow/310/images/shebao.png" alt="">
        <div class="xy-con-txt">
            <p class="tp">社保认证</p>
        </div>
        <?php if( ($social_valid == 1 || $social_valid == 4) && ($contacts_valid ==2 && $identify_valid == 2 &&  $pic_valid== 2 && in_array($juxinli_valid, [2,3])) ):?>
            <span class="xy-rz" onclick="toAuth('<?php echo $user_id ?>', 2, this)">
                    <?php if ($social_valid == 1): ?>
                        去认证
                    <?php elseif ($social_valid == 4): ?>
                        已过期
                    <?php endif; ?>
                </span>
        <?php elseif($social_valid == 1 && ($contacts_valid == 1 || $identify_valid == 1 || in_array($pic_valid, [1, 3, 4]) || $juxinli_valid == 1)):?>
            <span class="xy-qrz">去认证</span>
        <?php elseif ($social_valid == 2): ?>
            <span class="xy-wc">已完成</span>
        <?php elseif ($social_valid == 3): ?>
            <span class="xy-wc">认证中</span>
        <?php endif; ?>
        <div class="line"></div>
    </div>
    <div class="xy-con-list">
        <img src="/borrow/310/images/edu.png" alt="">
        <div class="xy-con-txt">
            <p class="tp">学历认证</p>
        </div>
         <div class="line"></div>
        <?php if( $edu_valid == 1 && ($contacts_valid ==2 && $identify_valid == 2 &&  $pic_valid== 2 && in_array($juxinli_valid, [2,3]))):?>
            <span class="xy-rz" onclick="toAuth('<?php echo $user_id ?>', 1, this)">去认证</span>
        <?php elseif($edu_valid == 1 && ($contacts_valid == 1 || $identify_valid == 1 || in_array($pic_valid, [1, 3, 4]) || $juxinli_valid == 1)):?>
            <span class="xy-qrz">去认证</span>    
        <?php elseif ($edu_valid == 2): ?>
            <span class="xy-wc">已完成</span>
        <?php elseif ($edu_valid == 3): ?>
            <span class="xy-wc">认证中</span>
        <?php elseif ($edu_valid == 4): ?>
            <span class="xy-rz" onclick="toAuth('<?php echo $user_id ?>', 1, this)">已过期</span>
        <?php endif; ?>
    </div>
    <div class="xy-con-list">
        <img src="/borrow/310/images/bankflow.png" alt="">
        <div class="xy-con-txt">
            <p class="tp">银行卡认证</p>
        </div>
         <div class="line"></div>
        <?php if( $bankflow_valid == 1 && ($contacts_valid ==2 && $identify_valid == 2 &&  $pic_valid== 2 && in_array($juxinli_valid, [2,3]))):?>
            <span class="xy-rz" onclick="toAuth('<?php echo $user_id ?>', 7, this)">去认证</span>
        <?php elseif($bankflow_valid == 1 && ($contacts_valid == 1 || $identify_valid == 1 || in_array($pic_valid, [1, 3, 4]) || $juxinli_valid == 1)):?>
            <span class="xy-qrz">去认证</span>      
        <?php elseif ($bankflow_valid == 2): ?>
            <span class="xy-wc">已完成</span>
        <?php elseif ($bankflow_valid == 3): ?>
            <span class="xy-wc">认证中</span>
        <?php elseif ($bankflow_valid == 4): ?>
            <span class="xy-rz" onclick="toAuth('<?php echo $user_id ?>', 7, this)">已过期</span>
        <?php endif; ?>
    </div>
    <div class="xy-con-list">
        <img src="/borrow/310/images/jd.png" alt="">
        <div class="xy-con-txt">
            <p class="tp">京东认证</p>
        </div>
        <?php if( $jd_valid == 1 && ($contacts_valid ==2 && $identify_valid == 2 &&  $pic_valid== 2 && in_array($juxinli_valid, [2,3]))):?>
            <span class="xy-rz" onclick="toAuth('<?php echo $user_id ?>', 4, this)">去认证</span>
        <?php elseif($jd_valid == 1 && ($contacts_valid == 1 || $identify_valid == 1 || in_array($pic_valid, [1, 3, 4]) || $juxinli_valid == 1)):?>
            <span class="xy-qrz">去认证</span>      
        <?php elseif ($jd_valid == 2): ?>
            <span class="xy-wc">已完成</span>
        <?php elseif ($jd_valid == 3): ?>
            <span class="xy-wc">认证中</span>
        <?php elseif ($jd_valid == 4): ?>
            <span class="xy-rz" onclick="toAuth('<?php echo $user_id ?>', 4, this)">已过期</span>
        <?php endif; ?>
    </div>
    <div class="xy-con-list">
        <img src="/borrow/310/images/taobao.png" alt="">
        <div class="xy-con-txt">
            <p class="tp">淘宝认证</p>
        </div>
        <?php if( $taobao_valid == 1 && ($contacts_valid ==2 && $identify_valid == 2 &&  $pic_valid== 2 && in_array($juxinli_valid, [2,3]))):?>
            <span class="xy-rz" onclick="toAuth('<?php echo $user_id ?>', 6, this)">去认证</span>
        <?php elseif($taobao_valid == 1 && ($contacts_valid == 1 || $identify_valid == 1 || in_array($pic_valid, [1, 3, 4]) || $juxinli_valid == 1)):?>
            <span class="xy-qrz">去认证</span>      
        <?php elseif ($taobao_valid == 2): ?>
            <span class="xy-wc">已完成</span>
        <?php elseif ($taobao_valid == 3): ?>
            <span class="xy-wc">认证中</span>
        <?php elseif ($taobao_valid == 4): ?>
            <span class="xy-rz" onclick="toAuth('<?php echo $user_id ?>', 6, this)">已过期</span>
        <?php endif; ?>
    </div>
</section>
<div style="position:relative">
    <a style="position: absolute;right: 3.8rem;margin: .5rem auto;top: 0;" class="xyjk-help" href="/borrow/helpcenter?user_id=<?php echo $user_id;?>"><span class="rz-foot-txt" >获取帮助</span></a> 
</div>

<div id='backError' class="toast_tishi" hidden>网络错误</div>
<!--    黑名单弹窗-->
<div class="poppay_mask" id="toast"  hidden></div>
<div class="mask_box" id="toast_tixian_fail" hidden   >
    <p class="mask_title" style="">你暂时不能操作此功能</p>  
    <span class="add_btn" onclick="black_to()" style="">确定</span>
</div>

<script>
    var user_id = '<?php echo $user_id;?>';
    var can_credit = '<?php echo 1?>';
    var csrf = '<?php echo $csrf;?>';
    var black_user = '<?php echo $black_user;?>';
    <?php \app\common\PLogger::getInstance('weixin', '', $user_id); ?>
    <?php $json_data = \app\common\PLogger::getJson();?>
    var baseInfoss = eval('(' + '<?php echo $json_data; ?>' + ')');
    var location_href = "<?php echo $redirect_info; ?>";
    var page_type = 1;  //page_type=1个人资料页
    
    $(function(){
        if(black_user){
            $('#toast').show();
            $('#toast_tixian_fail').show();
        }
    })
    function black_to(){
        window.location.href = '/borrow/loan';
        return false;
    }
    
    //去身份认证 page_type=1个人资料页
    function do_identify_valid() {
         zhuge.track('身份信息-去认证', {
                '来源': '个人资料',
            });
        tongji('requireinfo_do_identifyvalid', baseInfoss);
        setTimeout(function () {
            window.location.href = '/borrow/userauth/index';
        }, 100);
    }

    //去联系人认证
    function do_contacts_valid() {
        zhuge.track('联系人信息-去认证', {
              '来源': '个人资料',
        });
        tongji('requireinfo_do_contactsvalid', baseInfoss);
        setTimeout(function () {
            window.location.href = '/borrow/userauth/contacts';
        }, 100);

    }

    //去视频认证
    function do_viedo_valid() {
        zhuge.track('视频认证-去认证', {
            '来源': '个人资料',
        });
        tongji('requireinfo_do_picvalid', baseInfoss);
        setTimeout(function () {
            window.location.href = '/borrow/userauth/pic';
        }, 100);

    }


    //去运营商手机认证
    function do_juxinli_valid() {
        zhuge.track('运营商认证-去认证', {
            '来源': '个人资料',
        });
        tongji('requireinfo_do_juxinlivalid', baseInfoss);
        $('#loadings').show();
        $('.loading').show();
        $.ajax({
            type: "POST",
            dataType: "json",
            data: {'_csrf': csrf},
            url: "/borrow/juxinliauth/phoneajax",
            async: true,
            error: function (result) {
                $("#backError").text('*网络出错');
                hideDiv('backError');
                return false;
            },
            success: function (result) {
                console.log(result);
                message(result);
            }
        });
    }


    /**
     * 信息处理
     * @params array result {"res_code":res_code, "res_data":res_data}
     * @resutl null
     */
    function message(result) {
        if (result.res_code == 0 && result.res_data.status == 0) {//跳转至开放平台，开始认证
            location.href = result.res_data.url;
        } else if (result.res_code == 0 && result.res_data.status == 1) {//采集成功
            window.location = location_href;
        } else if (result.res_code == 0 && result.res_data.status == 4) {//采集拉取中
            window.location = location_href;
        } else if (result.res_code == 0 && result.res_data.status == 3) {//失败
            $("#backError").text(result.res_data);
            hideDiv('backError');
            return false;
        } else {
            $("#backError").text(result.res_data);
            $("#backError").show();
            hideDiv('backError');
            return false;
        }

    }
    //2秒隐藏上传成功提示框
    function hideDiv(id) {
        var obj = $("#" + id);
        setTimeout(function () {
            obj.hide();
        }, 2000);

    }
    //去信用卡认证
    function do_card_valid() {
        zhuge.track('信用卡认证-去认证',{
            '来源': '个人资料',
        });
        tongji('requireinfo_do_cardvalid', baseInfoss);
        setTimeout(function () {
            window.location.href = '/borrow/userauth/card'; //type：1从必填资料项去填写信用卡认证 2：从选填资料
        }, 100);
    }
    //1学历、2社保、3公积金、4京东认证 6淘宝 7银行流水
    function toAuth(userId, type, obj) {
        console.log(type);
        tongji('selectioninfo_do_' + type, baseInfoss);
        if ($(obj).hasClass('lock')) {
            return false;
        }
        $(obj).addClass("lock");
        setTimeout(function () {
            $.ajax({
                type: "POST",
                url: "/borrow/selection",
                data: {_csrf: csrf, user_id: userId, type: type, page_type: page_type},
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
        }, 100);
    }

</script>
