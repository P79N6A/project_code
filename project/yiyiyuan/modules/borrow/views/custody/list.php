<div class="depository_operation">
    <?php if($list_type ==1):?>
        <img src="/borrow/310/images/cuguanxq_bg.png" alt="" class="operation_bg">
    <?php else:?>
        <img src="/borrow/310/images/operation_bg.png" alt="" class="operation_bg">
    <?php endif;?>
    
        <div class="system_operation" style="clear: both;overflow: hidden;height: auto;">
        <?php if($list_type ==1):?>
             <?php if ($isPass != 1): ?>
            <img src="/borrow/310/images/cg_progress_bar_2_1.png" class="progressBar" style="width:auto;height: 2.8rem;">
            <?php else: ?>
                <img src="/borrow/310/images/cg_progress_bar_2_2.png" class="progressBar" style="width:auto;height: 2.8rem;">
            <?php endif; ?>
            <div class="operation_step">
                
                    <div class="operation_step_password">
                        <span class="operation_title">设置密码</span>
                        <span class="operation_step_describe">在支付时使用</span>
                        <?php if ( $isPass != 1): ?>
                            <div class="operation_step_set" id="go_pwd" onclick="go_pwd()">去设置</div>
                            <div class="operation_mask" style="display:none;"></div>
                        <?php else: ?>
                            <div class="operation_step_set" style="background: #E1E1E1;">已完成</div>
                            <div class="operation_mask" style="display:none;"></div>
                        <?php endif; ?>
                    </div>
                
                    <div class="operation_step_payback">
                    <span class="operation_title">操作授权</span>
                    <span class="operation_step_describe">授权在江西银行操作</span>
                    <?php if ($isAuth != 1 && $isPass != 1): ?>
                        <div class="operation_step_set">去设置</div>
                        <div class="operation_mask"></div>
                    <?php elseif ($isAuth != 1 && $isPass == 1): ?>
                        <div class="operation_step_set goauthnew" onclick="go_auth()">去设置</div>
                        <div class="operation_mask" style="display:none;"></div>
                    <?php elseif ($isAuth == 1): ?>
                        <div class="operation_step_set" style="background: #E1E1E1;">已完成</div>
                        <div class="operation_mask" style="display:none;"></div>
                    <?php else: ?>
                        <div class="operation_step_set">去设置</div>
                        <div class="operation_mask"></div>
                    <?php endif; ?>
                </div>
            </div>
        <?php else:?>
             <?php if ($isOpen != 1): ?>
            <img src="/borrow/310/images/cg_progress_bar_2_1.png" class="progressBar" style="width:auto;height: 2.8rem;">
        <?php elseif ($isOpen == 1 && $is_open_new == 1 && $isCard == 1 && $isPass == 1): ?>
            <img src="/borrow/310/images/cg_progress_bar_2_2.png" class="progressBar" style="width:auto;height: 2.8rem;">
        <?php elseif ($isOpen == 1 && $isPass != 1): ?>
            <img src="/borrow/310/images/cg_progress_bar_3_1.png" class="progressBar" style="width:auto;height: 4.7rem;">
        <?php elseif ($isOpen == 1 && $isPass == 1 &&$isCard != 1): ?>
            <img src="/borrow/310/images/cg_progress_bar_3_2.png" class="progressBar" style="width:auto;height: 4.7rem;">
        <?php elseif ($isOpen == 1 && $is_open_new == 0 && $isCard == 1 && $isPass == 1): ?>
            <img src="/borrow/310/images/cg_progress_bar_3_3.png" class="progressBar" style="width:auto;height: 4.7rem;">
        <?php else: ?>
            <img src="/borrow/310/images/cg_progress_bar_3_3.png" class="progressBar" style="width:auto;height: 4.7rem;">
        <?php endif; ?>
            <div class="operation_step">
            <?php if ($isOpen != 1 || ($isOpen == 1 && $is_open_new == 1 && $isCard == 1 && $isPass == 1)): ?>
                <div class="operation_step_card">
                    <span class="operation_title">存管开户</span>
                    <span class="check_support_card">查看支持银行卡</span>
                    <span class="operation_step_describe">在江西银行开户并绑定银行卡</span>
                    <?php if ($isOpen != 1): ?>
                        <div class="operation_step_set" id="go_open" onclick="go_open()">去设置</div>
                        <div class="operation_mask" style="display:none;"></div>
                    <?php else: ?>
                        <div class="operation_step_set" style="background: #E1E1E1;">已完成</div>
                        <div class="operation_mask" style="display:none;"></div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="operation_step_password">
                    <span class="operation_title">设置支付密码</span>
                    <span class="operation_step_describe">在支付时使用</span>
                    <?php if ($isOpen == 1 && $isPass != 1): ?>
                        <div class="operation_step_set" id="go_pwd" onclick="go_pwd()">去设置</div>
                        <div class="operation_mask" style="display:none;"></div>
                    <?php else: ?>
                        <div class="operation_step_set" style="background: #E1E1E1;">已完成</div>
                        <div class="operation_mask" style="display:none;"></div>
                    <?php endif; ?>
                </div>
                <div class="operation_step_card">
                    <span class="operation_title">绑定银行卡</span>
                    <span class="check_support_card">查看支持银行卡</span>
                    <span class="operation_step_describe">在江西银行开户并绑定银行卡</span>
                    <?php if ($isOpen == 1 && $isPass == 1 && $isCard != 1): ?>
                        <div class="operation_step_set" id="go_bank_card" onclick="go_bank_card()">去设置</div>
                        <div class="operation_mask" style="display:none;"></div>
                    <?php elseif ($isCard == 1): ?>
                        <div class="operation_step_set" style="background: #E1E1E1;">已完成</div>
                        <div class="operation_mask" style="display:none;"></div>
                    <?php else: ?>
                        <div class="operation_step_set">去设置</div>
                        <div class="operation_mask"></div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
                <div class="operation_step_payback">
                    <span class="operation_title">操作授权</span>
                    <span class="operation_step_describe">授权在江西银行操作</span>
                    <?php if ($isAuth != 1 && ($isOpen != 1 || $isCard != 1 || $isPass != 1)): ?>
                        <div class="operation_step_set">去设置</div>
                        <div class="operation_mask"></div>
                    <?php elseif ($isAuth != 1 && $isOpen == 1 && $isCard == 1 && $isPass == 1): ?>
                        <div class="operation_step_set goauthnew" onclick="go_auth()">去设置</div>
                        <div class="operation_mask" style="display:none;"></div>
                    <?php elseif ($isAuth == 1): ?>
                        <div class="operation_step_set" style="background: #E1E1E1;">已完成</div>
                        <div class="operation_mask" style="display:none;"></div>
                    <?php else: ?>
                        <div class="operation_step_set">去设置</div>
                        <div class="operation_mask"></div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif;?>    
    </div>
    <div class="support_card_content" style="max-height: 14rem; overflow-y: auto;width:8.4rem;">
        <div class="support_card_title">
            支持的银行卡
            <img src="/borrow/310/images/close_ccc.png" id="close_yhk"
                 style="width: 0.32rem;height: 0.32rem; position: absolute ;right: 0.3rem;top: 0.3rem;">
        </div>
        <div class="card_content_box" style="width:96%;padding-left: 4%;">
            <?php foreach($cardLimt as $item):?>
             <div class="card_content">
                 <img src="/backstage/images/bankimg/<?php echo !empty($item['bank_name'])?$item['bank_name']:'';?>.png" alt="" class="card_logo" style="    height: 1.25rem;width: 1.25rem;">
                <p class="card_name"><?php echo (!empty($item->card_name))?$item->card_name:''; ?></p>
            </div>
            <?php  endforeach;?>
        </div>
    </div>
    <div class="support_card_mask"></div>
</div>
<style>
    .o-btn {
        background-image: linear-gradient(-90deg, #F00D0D 0%, #FF4B17 100%);
        border-radius: 5px;
        height: 1.28rem;
        text-align: center;
        line-height: 1.28rem;
        color: #fff;
        font-size: 0.48rem;
        width: 9rem;
        margin: .4rem auto;
    }

    .n-btn {
        opacity: .3
    }

</style>
<div class="poppay_mask" id="toast_cg" style="position: fixed;top: 0;left: 0;z-index: 1;" hidden></div>
<div class="mask_box " id="toast_cg_auth" style="top: 38%;z-index: 2;height:5.75rem" hidden>
    <img src="/borrow/310/images/bill-close.png" alt="" onclick="close_toast()" class="close_mask">
    <img src="/borrow/310/images/failIcon.png" style="    width: 0.7rem;
    position: absolute;
    left: 3.6rem;
    top: 0.7rem;">
    <p class="mask_title" style="top:1.65rem;">操作授权失败</p>
    <p class="mask_text" style="width: 7.1rem;top:2.5rem;">由于您授权金额或时间设置不满足要求，导致授权失败！授权金额应≥5000，授权时间应≥1年，请重新授权。</p>
    <span class="add_btn go_pwd_list" onclick="go_auth()" style="top: 4.2rem;">重新授权</span>
</div>
<!--
更改.progressBar的src切换进度;
遮罩:更改.operation_mask的display;
已完成:更改.operation_step_set的innerHTML值,并去掉.operation_mask遮罩
-->
<script type="text/javascript">
    <?php \app\common\PLogger::getInstance('weixin', '', $user_id); ?>
    <?php $json_data = \app\common\PLogger::getJson(); ?>
    var baseInfoss = eval('(' + '<?php echo $json_data; ?>' + ')');

    var support_card_mask = document.getElementsByClassName('support_card_mask')[0];
    var support_card_content = document.getElementsByClassName('support_card_content')[0];
    var check_support_card = document.getElementsByClassName('check_support_card')[0];
    check_support_card.onclick = function () {
        support_card_mask.style.display = 'block';
        support_card_content.style.display = 'block';
    }
    support_card_mask.onclick = function () {
        this.style.display = 'none';
        support_card_content.style.display = 'none';
    }
    $('#close_yhk').click(function () {
        support_card_mask.style.display = 'none';
        support_card_content.style.display = 'none';
    })
</script>
<script>
    var type = "<?php echo $type; ?>";
    var user_id = "<?php echo $user_id; ?>";
    var auth_error = "<?php echo $auth_error; ?>";
    var csrf = "<?php echo $csrf; ?>";
    var shop_reback_url = '<?php echo $shop_reback_url; ?>';
    var isApp = <?php
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'YYY_ANDROID') || strpos($_SERVER['HTTP_USER_AGENT'], 'yyyIOS')) {
        echo 1;
    } else {
        echo 2;
    }
    ?>

    if(auth_error == 1){
        $('#toast_cg').show();
        $('#toast_cg_auth').show();
    }

    //重写返回按钮
    pushHistory();
    var bool = false;
    setTimeout(function () {
        bool = true;
    }, 500);
    window.addEventListener("popstate", function (e) {
        tongji('cunguan_list_reback_btn', baseInfoss);
        if (bool) {
            setTimeout(function () {
                if(shop_reback_url){
                    window.location.href = shop_reback_url;
                    return false;
                }else{
                    if (isApp == 1) {
                        window.myObj.closeHtml();
                        function closeHtml() {}
                    }else{
                        if(type == 7){
                            window.location.href = '/new/bank';
                            return false;
                        }
                        window.location.href = '/borrow/loan';
                    }
                }
               
            }, 1000);
        }
        pushHistory();
    }, false);
    function pushHistory() {
        var state = {
            url: "#"
        };
        window.history.pushState(state, "#");
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
    $(function(){
        var isOpen = "<?php echo $isOpen; ?>";
        var isAuth = "<?php echo $isAuth; ?>";
        var isCard  = "<?php echo $isCard; ?>";
        var isPass  = "<?php echo $isPass; ?>";
        var authIsTimeOut = "<?php echo $authIsTimeOut; ?>";
        var openRes = "未完成";
        var passRes = "未完成";
        var cardRes = "未完成";
        var authRes = "未完成";
        var timeOutRes = "未过期";
        if(isPass == 1){
            passRes = "完成";
        }
        if(isCard == 1){
            cardRes = "完成";
        }
        if(isAuth == 1){
            authRes = "完成";
        }
        if(authIsTimeOut == 1){
            authRes = "已过期";
        }
        if(isOpen == 1){
            openRes = "完成";
        }
//        var pbcount = $('.operation_title').length;
        zhuge.track('存管开户页面', {
            '存管开户': openRes,
            '设置密码': passRes,
            '存管绑卡': cardRes,
            '操作授权': authRes,
//            '展示项' : pbcount,
        });
    });
    //去设置密码
    function go_pwd() {
        tongji('cunguan_setpwd', baseInfoss);
        zhuge.track('设置密码去认证按钮');
        setTimeout(function () {
            $.ajax({
                type: "POST",
                url: "/borrow/custody/setpwdnew",
                data: {user_id: user_id, _csrf: csrf,pwType:1},
                success: function (data) {
                    data = eval('(' + data + ')');
                    console.log(data);
                    if (data.res_code == '0000') {
                        location.href = data.res_data;
                    } else {
                        alert(data.res_msg);

                    }
                }
            });
        }, 100);
    }

    //去开户
    function go_open() {
        tongji('cunguan_open', baseInfoss);
        zhuge.track('存管开户去认证按钮');
        setTimeout(function () {
            $.ajax({
                type: "POST",
                url: "/borrow/custody/newopenwx",
                data: {user_id: user_id, _csrf: csrf},
                success: function (data) {
                    data = eval('(' + data + ')');
                    console.log(data);
                    if (data.res_code == '0000') {
                        window.location = data.res_data;
                    } else {
                        alert(data.res_msg);
                    }
                }
            });
        }, 100);
    }

    //去绑卡
    function go_bank_card() {
        var come_from = 8;
        if(type == 7){
            come_from = 7;
        }
        tongji('cunguan_bank_card', baseInfoss);
        zhuge.track('存管绑卡去认证按钮');
        setTimeout(function () {
            $.ajax({
                type: "POST",
                url: "/borrow/custody/newbankwx",
                data: {user_id: user_id, _csrf: csrf, come_from: come_from},
                success: function (data) {
                    data = eval('(' + data + ')');
                    if (data.res_code == '0000') {
                        window.location = data.res_data;
                    } else {
                        alert(data.res_msg);

                    }
                }
            });
        }, 100);
    }
    $('.goauthnew').click(function(){
        zhuge.track('操作授权去认证按钮');
    })

    //去授权
    function go_auth() {
        tongji('cunguan_auth', baseInfoss);
        setTimeout(function () {
            $.ajax({
                type: "POST",
                url: "/borrow/custody/authforinone",
                data: {user_id: user_id, _csrf: csrf, is_repay: 1},
                success: function (data) {
                    data = eval('(' + data + ')');
                    if (data.res_code == '0000') {
                        window.location.href = data.res_data;
                    } else {
                        alert(data.res_msg);
                    }
                }
            });
        }, 100);
    }

    function close_toast() {
        $('#toast_cg').hide();
        $('#toast_cg_auth').hide();
    }
</script>


