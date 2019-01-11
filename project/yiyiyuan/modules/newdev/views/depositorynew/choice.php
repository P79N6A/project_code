<style>
    .poppay_mask{
        height: 100%;
        width: 100%;
        background: #000;
        opacity: 0.5;
    }
    .mask_box{
        height: 230px;
        width: 315px;
        position: absolute;
        left: 50%;
        top: 50%;
        background: #fff;
        transform: translate(-50%,-50%);
        border-radius: 10px;
    }
    .mask_title {
        font-family: "微软雅黑";
        font-size: 14px;
        color: #444444;
        line-height: 14px;
        width: 100%;
        position: absolute;
        left: 0;
        text-align: center;
        top: 66px;
        font-weight: bold;
    }
    .mask_text {
        font-family: "微软雅黑";
        font-size: 14px;
        color: #444444;
        line-height: 20px;
        position: absolute;
        left: 32px;
        top: 93px;
    }
    .add_btn {
        background-image: linear-gradient(-90deg, #F00D0D 0%, #FF4B17 100%);
        border-radius: 12px;
        height: 45px;
        width: 133px;
        position: absolute;
        left: 94px;
        top: 175px;
        text-align: center;
        font-family: "微软雅黑";
        font-size: 16px;
        color: #FFFFFF;
        line-height: 45px;
    }
</style>
<div class="depository_set">
    <img class="depository_set_img" src="/borrow/325/images/czsq.png">
    <p class="depository_set_text">操作授权</p>
    <p class="depository_set_tip">还款需要完成操作授权</p>
</div>
<div class="depository_reset" onclick="hkymt()">去授权</div>

<div class="poppay_mask" id="toast_cg" style="position: fixed;top: 0;left: 0;z-index: 1;" hidden></div>
<div class="mask_box " id="toast_cg_auth" style="top: 38%;z-index: 2;" hidden>
    <img src="/borrow/310/images/bill-close.png" alt="" onclick="close_toast()" class="close_mask" style="position:absolute;height: 17px;width:17px;    right: 10px;
    top: 10px;">
    <img src="/borrow/310/images/failIcon.png" style="    width: 28px;
    position: absolute;
    left: 143px;
    top: 30px;">
    <p class="mask_title" style="top:66px;">操作授权失败</p>
    <p class="mask_text" style="width: 260px;">由于您授权金额或时间设置不满足要求，导致授权失败！授权金额应≥5000，授权时间应≥1年，请重新授权。</p>
    <span class="add_btn go_pwd_list" onclick="hkymt()">重新授权</span>
</div>
<script type="text/javascript">
    var csrf = '<?php echo $csrf;?>';
    var userId = '<?php echo $userId;?>';
    var auth_result = '<?php echo $auth_result;?>';
    var from = '<?php echo $from;?>';
    var auth_error = '<?php echo $auth_error;?>';
    if(auth_error == 1){
        $('#toast_cg').show();
        $('#toast_cg_auth').show();
    }
    if(auth_result == 1 && from == 'app'){
        setTimeout(function(){
            clos();
        },100);
    }
    function hkymt(){
        if($('.depository_reset').hasClass('lock')){
           return false;
        }
        $('.depository_reset').addClass('lock');
        $.ajax({
            type: "POST",
            url: "/new/depositorynew/authorize",
            data: {user_id: userId,_csrf:csrf,is_repay:2},
            success: function (data) {
                data = eval('('+data+')');
                if (data.res_code == '0000') {
                    window.location.href = data.res_data;
                } else {
                    alert(data.res_msg);
                    $(".$('.depository_reset')").removeClass('lock');
                }
            }
        });
    }

    function clos() {
        window.myObj.closeHtml();
        function closeHtml() {
        }
    }
    
    function close_toast() {
        $('#toast_cg').hide();
        $('#toast_cg_auth').hide();
    }
</script>
