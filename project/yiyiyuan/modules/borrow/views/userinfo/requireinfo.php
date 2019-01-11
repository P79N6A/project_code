<style>
    .help_service{
        position: absolute;
        width: 100%;
        left: 0;
        bottom: -0.9rem;
        height: 0.37rem;
        text-align: center;
    }
    .contact_service_tip{
        width: 0.40rem;
        height: 0.43rem;
        position: absolute;
        left: 3.97rem;
        top: 0;
    }
    .contact_service_text{
        height: 0.37rem;
        position: absolute;
        left:4.59rem;
        font-family: "微软雅黑";
        font-size: 0.37rem;
        color: #3D81FF;
        letter-spacing: 0;
        line-height: 0.43rem;
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
<div class="wraper">
    <header  class="xyjk-head">
        <p class="xy-head-title">个人资料安全认证</p>
        <p class="xy-head-txt">为保障您的资金安全，需要验证身份信息</p>
    </header>
    <section class="xy-content">
        <div class="xy-con-list">
            <img src="/borrow/310/images/shenfenz.png" alt="">
            <div class="xy-con-txt">
                <p class="tp">身份信息</p>
                <p class="btm">仅用于核实身份</p>
                
            </div>
            <?php if($identify_valid == 1):?>
            <span class="xy-rz" onclick="do_identify_valid()">去认证</span>
            <?php elseif( $identify_valid == 2 ):?>
            <span class="xy-rz" onclick="do_identify_valid()">修改</span>
            <?php endif;?>

            <div class="line"></div>
        </div>
        <div class="xy-con-list">
            <img src="/borrow/310/images/fav.png" alt="">
            <div class="xy-con-txt">
                <p class="tp">联系人信息</p>
                <p class="btm">正常情况下不会致电联系人</p>
            </div>
            <?php if($contacts_valid == 1 && ($identify_valid == 2 )):?>
                <span class="xy-rz" onclick="do_contacts_valid()">去认证</span>
            <?php elseif($contacts_valid == 1 && $identify_valid == 1 ):?>
                 <span class="xy-qrz">去认证</span>
            <?php elseif( $contacts_valid == 2 ):?>
                 <?php if( $identify_valid == 1 ):?>
                    <span class="xy-qrz">修改</span>
                 <?php else:?> 
                    <span class="xy-rz" onclick="do_contacts_valid()">修改</span>
                 <?php endif;?>
            <?php else:?>
                 <span class="xy-wc">已完成</span>
            <?php endif;?>
            <div class="line"></div>
        </div>
        <div class="xy-con-list">
            <img src="/borrow/310/images/video.png" alt="">
            <div class="xy-con-txt">
                <p class="tp">视频认证</p>
                <p class="btm">创建面部识别，保障资金安全</p>
            </div>
            <?php if( ($pic_valid == 1 || $pic_valid == 3 ) && ( $contacts_valid ==2 && $identify_valid == 2 )):?>
                    <span class="xy-rz" onclick="do_viedo_valid()" >去认证</span>
            <?php elseif( ($pic_valid == 1 || $pic_valid == 3 ) && ( $contacts_valid ==1  ||  $contacts_valid ==3 || $identify_valid == 1 )):?>
                   <span class="xy-qrz">去认证</span>
            <?php elseif( $pic_valid == 2 ):?>
                 <span class="xy-wc">已完成</span>
            <?php elseif( $pic_valid == 4 ):?>
                 <span class="xy-wc">认证中</span>
            <?php else:?>
                 <span class="xy-qrz">去认证</span>
            <?php endif;?>
            <div class="line"></div>
        </div>
        <div class="xy-con-list">
            <img src="/borrow/310/images/juxinli.png" alt="">
            <div class="xy-con-txt">
                <p class="tp">手机运营商认证</p>
                <p class="btm">用于确认手机号属于您本人</p>
            </div>
            <?php if( ( $juxinli_valid == 1 || $juxinli_valid == 3) && ($contacts_valid ==2 && $identify_valid == 2 && $pic_valid== 2 )  ):?>
                   <?php if($juxinli_valid == 1):?>
                    <span class="xy-rz" onclick="do_juxinli_valid()" >去认证</span>
                   <?php elseif($juxinli_valid == 3):?>
                    <span class="xy-rz" onclick="do_juxinli_valid()" >已过期</span>
                   <?php endif;?>
            <?php elseif( ( $juxinli_valid == 1 || $juxinli_valid == 3) && ($contacts_valid ==1 || $identify_valid == 1  || in_array($pic_valid, [1,3,4])) ):?>
                   <?php if($juxinli_valid == 1):?>
                    <span class="xy-qrz">去认证</span>
                   <?php elseif($juxinli_valid == 3):?>
                    <span class="xy-qrz">已过期</span>
                   <?php endif;?>
            <?php elseif( $juxinli_valid == 2 ):?>
                 <span class="xy-wc">已完成</span>
            <?php endif;?>
            <div class="line"></div>
        </div>
        <div class="xy-con-list">
            <img src="/borrow/310/images/xinyongka.png" alt="">
            <div class="xy-con-txt">
                <p class="tp">信用卡认证（选填）</p>
                <p class="btm">完成认证可提高借款额度</p>
            </div>
      
                <?php if($bank_valid == 1 && ($contacts_valid ==2 && $identify_valid == 2 && $pic_valid== 2  && $juxinli_valid == 2)):?>
                        <span class="xy-rz" onclick="do_card_valid()">去认证</span>
                <?php elseif($bank_valid == 1 && ($contacts_valid ==1 || $identify_valid == 1 || in_array($pic_valid, [1,3,4]) || $juxinli_valid == 1 || $juxinli_valid == 3)):?>
                     <span class="xy-qrz">去认证</span>
                <?php elseif( $bank_valid == 2 ):?>
                     <span class="xy-wc">已完成</span>
                <?php endif;?>
            
        </div>
            <?php if($source_mall == 1) :?>
                <?php if($can_credit == 1):?>
                    <button class="big345-button rz-filed" id="getdo_credit" onclick="do_mall()">完成</button>
                <?php else:?>
                    <button class="big345-button xy-btn">完成</button>
                <?php endif;?>
            <?php else:?>
                <?php if($can_credit == 1):?>
                    <button class="big345-button rz-filed" id="getdo_credit" onclick="do_credit()">立即获取额度</button>
                <?php else:?>
                    <button class="big345-button xy-btn">立即获取额度</button>
                <?php endif;?>
            <?php endif;?>
                
        <div class="help_service">
    <img src="/borrow/310/images/tip.png" alt="" class="contact_service_tip">
    <a href="/borrow/helpcenter/list?position=3&user_id=<?php echo $user_id;?>"><span class="contact_service_text">获取帮助</span></a>
</div>
</section>
    <div id='backError' class="toast_tishi" hidden>网络错误</div>
</div>
<!--    黑名单弹窗-->
<div class="poppay_mask" id="toast"  hidden></div>
<div class="mask_box" id="toast_tixian_fail" hidden   >
    <p class="mask_title" style="">你暂时不能操作此功能</p>  
    <span class="add_btn" onclick="black_to()" style="">确定</span>
</div>


<script>
    var user_id = '<?php echo $user_id;?>';
    var can_credit = '<?php echo $can_credit;?>';
    var csrf = '<?php echo $csrf;?>';
    var shop_reback_url = '<?php echo $shop_reback_url; ?>';
    var times = '<?php echo $times; ?>';
    var black_user = '<?php echo $black_user;?>';
    <?php \app\common\PLogger::getInstance('weixin','',$user_id); ?>
    <?php $json_data = \app\common\PLogger::getJson();?>
    var baseInfoss = eval( '('  + '<?php echo $json_data; ?>' + ')' );
    var isApp = <?php
        if( strpos($_SERVER['HTTP_USER_AGENT'], 'YYY_ANDROID') || strpos($_SERVER['HTTP_USER_AGENT'], 'yyyIOS')){
            echo 1;  //app端
        }else {
            echo 2;  //h5端
        }
        ?>;
    var u = navigator.userAgent, app = navigator.appVersion;
    var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Linux') > -1;
    var isiOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/);
    var ios_identify = 'IdentityVerificationViewController';
    var ios_pic = 'SelfieViewController';
    var android_identify = "com.business.userinfo.UserIdentityActivity";
    var position2 = '{"params":[{"param_name":"","param_value":""}]}';
    var android_vedio = "com.business.userinfo.UserAliveActivity";//只能视频认证
    if(times>=3 && times<5){
         android_vedio = "com.business.userinfo.UserAliveFailedActivity";
         position2= '{"params":[{"param_name":"artificialState","param_value":"alive_both"}]}';//失败且可以人工认证(失败超3次)
    }else if(times>=5){
        android_vedio = "com.business.userinfo.UserAliveFailedActivity";
        position2= '{"params":[{"param_name":"artificialState","param_value":"alive_only_artificial"}]}';//失败且只能人工认证（失败超5次）
    }else{
        android_vedio = "com.business.userinfo.UserAliveActivity";//只能视频认证
    }
    var position = 1;
    
    var identify_valid = '<?php echo $identify_valid; ?>';
    var contacts_valid = '<?php echo $contacts_valid; ?>';
    var pic_valid = '<?php echo $pic_valid; ?>';
    var juxinli_valid = '<?php echo $juxinli_valid; ?>';
    var bank_valid = '<?php echo $bank_valid; ?>';
    var identify = '完成';
    var contacts = '完成';
    var pic = '完成';
    var juxinli = '完成';
    var bank = '完成';
    
    $(function(){
        //重写返回按钮
        pushHistory();
            var bool=false;
            setTimeout(function(){
                bool=true;
            },1500);
            window.addEventListener("popstate", function(e) {
            tongji('requireinfo_list_reback_btn',baseInfoss);
            if(bool){
               //根据自己的需求返回到不同页面
                setTimeout(function(){
                    if(shop_reback_url){
                         window.location.href= shop_reback_url;
                         return false;
                    }
                    window.location.href= '/borrow/loan';
                 },100);
            }
                pushHistory();
            }, false);
        function pushHistory() {
            var state = {
                url: "#"
            };
            window.history.pushState(state,  "#");
        }
        if(black_user){
            $('#toast').show();
            $('#toast_tixian_fail').show();
        }
        if(identify_valid == 1){
            identify = '未完成';
        }
        if(contacts_valid == 1){
            contacts = '未完成';
        }
        if(pic_valid == 1){
            pic = '未完成';
        }
        if(juxinli_valid == 1){
            juxinli = '未完成';
        }
        if(bank_valid == 1){
            bank = '未完成';
        }
        //诸葛埋点-身份认证列表页PV/UV
        zhuge.track('身份认证列表页', {
            '用户ID': user_id,
            '身份信息认证': identify,
            '联系人认证': contacts,
            '视频认证': pic,
            '运营商认证': juxinli,
            '信用卡认证': bank,
            
        });
     });
    //黑名单跳转
    function black_to(){
        if(shop_reback_url){
            window.location.href = shop_reback_url;
            return false;
        }
        window.location.href = '/borrow/loan';
        return false;
    }
       
    //去身份认证
    function do_identify_valid(){
        zhuge.track('身份信息-去认证', {
                '来源': '身份信息认证',
            });
        tongji('requireinfo_do_identifyvalid',baseInfoss);
        setTimeout(function(){
                if(isApp == 1){
                    if (isAndroid) {
                        window.myObj.toPage(android_identify, position);
                    }else if(isiOS){
                        window.myObj.toPage(ios_identify);
                    }else{
                        window.location.href = '/borrow/userauth/index';
                    }  
                }else{
                    window.location.href = '/borrow/userauth/index';
                }
                
        },100);
    }
    
    //去联系人认证
    function do_contacts_valid(){
        zhuge.track('联系人信息-去认证', {
              '来源': '身份信息认证',
        });
        tongji('requireinfo_do_contactsvalid',baseInfoss);
        setTimeout(function(){
            window.location.href = '/borrow/userauth/contacts';
        },100);
        
    }
    
    //去视频认证
    function do_viedo_valid(){
        zhuge.track('视频认证-去认证', {
            '来源': '身份信息认证',
        });
        tongji('requireinfo_do_picvalid',baseInfoss);
        setTimeout(function(){
            if(isApp == 1){
                if (isAndroid) {
                    window.myObj.openPage(android_vedio, position2);
                }else if(isiOS){
                    window.myObj.toPage(ios_pic);
                }else{
                    window.location.href = '/borrow/userauth/pic';
                } 
            }else{
                window.location.href = '/borrow/userauth/pic';
            }
            
        },100);
        
    }


    //去运营商手机认证
    function do_juxinli_valid(){
        zhuge.track('运营商认证-去认证', {
            '来源': '身份信息认证',
        });
        tongji('requireinfo_do_juxinlivalid',baseInfoss);
        $('#loadings').show();
        $('.loading').show();
        $.ajax({
            type: "POST",
            dataType: "json",
            data:{'_csrf':csrf},
            url: "/borrow/juxinliauth/phoneajax",
            async: true,
            error: function(result) {
                $("#backError").text('*网络出错');
                hideDiv('backError');
                return false;
            },
            success: function(result) {
                zhuge.identify(user_id, {
                    '运营商已认证': 1,  // 0表示false，1表示ture，下同
                });
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
    function message(result){
        if (result.res_code == 0 && result.res_data.status == 0){//跳转至开放平台，开始认证
            location.href = result.res_data.url;
        }else if(result.res_code == 0 && result.res_data.status == 1){//采集成功
            location.href = '/borrow/userinfo/requireinfo';
        }else if(result.res_code == 0 && result.res_data.status == 4){//采集拉取中
            location.href = '/borrow/userinfo/requireinfo';
        }else if(result.res_code == 0 && result.res_data.status == 3){//失败
            $("#backError").text(result.res_data);
            hideDiv('backError');
            return false;
        }else{
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
    function do_card_valid(){
        zhuge.track('信用卡认证-去认证',{
            '来源': '身份信息认证',
        });
        tongji('requireinfo_do_cardvalid',baseInfoss);
        setTimeout(function(){
            window.location.href = '/borrow/userauth/card?type=1'; //type：1从必填资料项去填写信用卡认证 2：从选填资料
        },100);
    }
    
    //获取额度
    function do_credit(){
        zhuge.track('身份信息认证-立即获取额度');
        if($('#getdo_credit').hasClass('dis')){
            return false;
        }
        $('#getdo_credit').addClass('dis');
        tongji('requireinfo_do_credit',baseInfoss);
        setTimeout(function(){
            getcanloan();
        },2000);
    }
    function getcanloan(){
        var black_box = _fmOpt.getinfo();//获取同盾指纹
        $.ajax({
            url: "/borrow/loan/getcanloan",
            type: 'post',
            async: false,
            data: {_csrf: csrf,type:2,black_box:black_box},
            success: function (json) {
                json = eval('(' + json + ')');
                console.log(json);
                if (json.rsp_code == '0000') {
                    if (json.is_change == 1) { //有待完善资料 跳转到认证页
                        window.location.href = '/borrow/userinfo/requireinfo';
                    } else if (json.is_change == 2) { //跳转到额度审核中页面
                        if(json.shop_mark == 1){ //跳到先花商城页面
                            window.location.href = json.shop_url;
                            return false;
                        }
                        window.location.href = '/borrow/loan';
                    }
                } else {
                    alert(json.rsp_msg);
                }
            },
            error: function (json) {
                alert('请十分钟后发起借款');
            }
        });
    }

    //返回商城
    function do_mall(){
        if($('#getdo_credit').hasClass('dis')){
            return false;
        }
        $('#getdo_credit').addClass('dis');
        window.location.href = '/mall/store/index';
        return false;
    }
</script>
