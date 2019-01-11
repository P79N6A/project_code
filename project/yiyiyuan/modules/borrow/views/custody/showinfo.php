<?php
$step_text = [
    1 => '开户',
    2 => '设置',
    3 => '授权',
    4 => '解绑卡',
    7 => '绑卡'
];
$zhuge_step_text = [
    1 => '存管开户',
    2 => '设置密码',
    3 => '操作授权',
    7 => '存管绑卡'
];
$go_where_text = [
    1 => '首页',
    2 => '开户列表',
    3 => '银行卡列表',
];
?>
<div class="y-vertify">
    <div class="vertify-main">
        <?php if ($is_success == 1): ?>
            <!-- 成功 -->
            <img class="y-vertify-error" src="/borrow/330/images/success_upload_icon.png" alt="">
            <h3 class="error-tit"><?php echo isset($step_text[$is_step]) ? $step_text[$is_step] : '操作'; ?>成功</h3>
            <p class="vertify-goto"><span class="timer">5</span>s跳转至<?php echo isset($go_where_text[$go_where]) ? $go_where_text[$go_where] : '操作'; ?></p>
        <?php else: ?>
            <!-- 失败 -->
            <img class="y-vertify-error" src="/borrow/330/images/rz-faile.png" alt="">
            <h3 class="error-tit"><?php echo isset($step_text[$is_step]) ? $step_text[$is_step] : '操作'; ?>失败</h3>
            <p class="y-vertify-mark">失败原因：<?php echo isset($error_msg) ? $error_msg : '操作失败，请重试。';?></p>
            <p class="vertify-goto"><span class="timer">5</span>s跳转至<?php echo isset($go_where_text[$go_where]) ? $go_where_text[$go_where] : '操作'; ?></p>
        <?php endif; ?>
    </div>
    <?php if ($is_success == 2 && isset($step_text[$is_step]) && $is_step != 4): ?>
    <button class="y-vertify-btn" onabort="doCunguan('<?php echo $is_step;?>')">重新<?php echo $step_text[$is_step]; ?></button>
    <?php endif; ?>
    <div class="contact_service">
        <img src="/borrow/330/images/tip.png" alt="" class="contact_service_tip">
        <a href="https://www.sobot.com/chat/h5/index.html?sysNum=f0af5952377b4331a3499999b77867c2&robotFlag=1&partnerId=<?php echo $user_id;?>">
            <span class="contact_service_text">联系客服</span>
        </a>
    </div>
</div>
<script>
    var count, countdown;
    var go_where = '<?php echo $go_where?>';
    var userId = '<?php echo $user_id?>';
    var csrf = '<?php echo $csrf?>';
    var shop_url = '<?php echo $shop_url?>';
    var isApp = <?php
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'YYY_ANDROID') || strpos($_SERVER['HTTP_USER_AGENT'], 'yyyIOS')) {
            echo 1;
        } else {
            echo 2;
        }
        ?>;
    $(function () {
        count = 5;
        countdown = setInterval(CountDown, 1000);
    });
    var CountDown = function () {
        $(".timer").html(count);
        if (count <= 0) {
            $(".timer").html(0);
            if (go_where == 1) {
                if(isApp == 1){
                    setTimeout(function () {
                        window.myObj.closeHtml();
                        function closeHtml() {
                        }
                    });
                    return false;
                }
                location.href = "/borrow/loan";
            } else if (go_where == 2) {
                location.href = "/borrow/custody/list?user_id=" + userId;
            }else if(go_where == 4){//跳回先花商城
                 location.href = shop_url;
            } else {
                if(isApp == 1){
                    setTimeout(function () {
                        window.myObj.closeHtml();
                        function closeHtml() {
                        }
                    });
                    return false;
                }
                location.href = "/new/bank";
            }
            clearInterval(countdown);
        }
        count--;
    };
    var step = "<?php echo isset($zhuge_step_text[$is_step]) ? $zhuge_step_text[$is_step] : ''; ?>";
    $(function(){
        var is_success = "<?php echo $is_success; ?>";
        if(step && is_success != 1){
            zhuge.track('开户失败页面', {
                '操作类型': step,
            });
        }
    });
    $('.y-vertify-btn').click(function(){
        var info = $(this).html();
        zhuge.track('开户失败页面-按钮点击', {
            '按钮名称': info,
        });
    })
    function doCunguan(type){
        if(type == 1){
            go_open();
            return false;
        }else if(type == 2){
            go_pwd();
            return false;
        }else if(type == 3){
            go_auth();
            return false;
        }else if(type == 7){
            go_bank_card();
            return false;
        }
    }
    
    //去设置密码
    function go_pwd() {
        setTimeout(function () {
            $.ajax({
                type: "POST",
                url: "/borrow/custody/setpwdnew",
                data: {user_id: userId, _csrf: csrf,pwType:1},
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
        setTimeout(function () {
            $.ajax({
                type: "POST",
                url: "/borrow/custody/newopenwx",
                data: {user_id: userId, _csrf: csrf},
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
        setTimeout(function () {
            $.ajax({
                type: "POST",
                url: "/borrow/custody/newbankwx",
                data: {user_id: userId, _csrf: csrf, come_from: 8},
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

    //去授权
    function go_auth() {
        setTimeout(function () {
            $.ajax({
                type: "POST",
                url: "/borrow/custody/authforinone",
                data: {user_id: userId, _csrf: csrf, is_repay: 1},
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
</script>