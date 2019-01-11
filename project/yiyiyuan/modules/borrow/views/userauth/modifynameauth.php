<?php

use app\commonapi\ImageHandler;

$uploadurl = ImageHandler::$img_upload;
$imgurl = ImageHandler::$img_domain;

$pros = json_decode($profession, TRUE);
$pro = json_encode(array_values($pros));
if(!isset($user->extend) || !isset($user->extend->profession)){
   $defaultJob = '一般职业'; 
}else{
    $defaultJob = $pros[$user->extend->profession];
}
?>
<!-- 进度条 -->
<div class="progress"></div>

<!-- 照片 -->
<div class="photo">
    <!-- 成功上传 -->
    <div class="success_upload">
        <img src="/borrow/310/images/success_upload_icon.png">
        <p>身份证照片已上传</p>
    </div>

    <div class="photo_input">
        <div>
            <label>姓名</label>
            <input type="text" name="name" value="<?php echo $user->realname; ?>" readonly="readonly">
        </div>
        <div class="shenfenzheng">
            <label>身份证号</label>
            <input type="text" name="idcard" value="<?php echo $user->identity; ?>" readonly="readonly">
        </div>
    </div>
</div>

<div class="hr"></div>
<div class="per_info">
    <div>
        <label>职业</label>
        <p id="job"><?php echo $defaultJob; ?></p>
        <img src="/borrow/310/images/left_icon.png">
        <input type="hidden" name="profession" value="<?php echo $defaultJob; ?>">
    </div>
    <div>
        <label>月收入</label>
        <p id="money"><?php echo  isset($user->extend)?$user->extend->income:'2000以下'; ?></p>
        <img src="/borrow/310/images/left_icon.png">
        <input type="hidden" name="money" value="<?php echo isset($user->extend)?$user->extend->income:'2000以下'; ?>">
    </div>
    <div class="com_reg">
        <label>公司名称</label>
        <input type="text" placeholder="请输入您所在公司名称" name="company" value="<?php echo isset($user->extend)?$user->extend->company:''; ?>">
        <img src="/borrow/310/images/left_icon.png" style="visibility: hidden;">
    </div>
    <div class="com_reg">
        <label>邮箱</label>
        <p id="email_error" style="left: 2.05rem;" hidden>*请填写正确的电子邮箱</p>
        <input type="text" placeholder="请输入您的常用邮箱" name="email" value="<?php echo isset($user->extend)?$user->extend->email:'';  ?>">
        <img src="/borrow/310/images/left_icon.png" style="visibility: hidden;">
    </div>
    <p id="iden_error" style="color: red;" class=""></p>
</div>
<div id="errormsg" hidden style="color: red; font-size: 0.32rem; margin-left: 0.4rem;"></div>
<div class="progress_btn ok_btn" id="submit_content">提交</div>
   <style>
        .read{
            background: #fff;
            padding: 0 5vw .1vw;
            color: #444444;
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            box-sizing: border-box;
        }
        .read h4{
            text-align: center;
            font-size: 4vw;
            font-weight: bold;
            padding: 4vw 0;
        }
        .read p{
            font-size: 3.7vw;
            line-height: 1.4;
        }
    </style>
    
<!--    <div class="result" hidden></div>-->
<div class="shade" hidden ></div>
    <div class="read" id="read" hidden>
        <div>
            <img onclick="cancle_toast()" src="/borrow/310/images/close_cnh.png" style="left: 0.2rem;margin-top: 0.2rem;height: 0.5rem;">
            <h4>阅读并同意承诺函</h4>
            <p>根据监管部门要求，本平台不为在校学生提供借贷撮合服务。进入平台请您确认并承诺在先花一亿元平台填写信息的真实性，本人非在校学生、无还款来源或不具备还款能力的借款人。如填写信息与实际情况不符，由本人承担由此产生的一切法律后台，与本平台无关。</p>
        </div>
        <div class="progress_btn ok_btn" onclick="do_submits()"  id="submit_read" style="margin-top:7vw">我已阅读并承诺</div>
    </div>
<!--<div class="help">
    <img src="/borrow/310/images/help_deng.png">
    <span>获取帮助</span>
</div>-->
<script src="/borrow/310/js/picker.js"></script>
<script>
    var csrf = '<?php echo $csrf; ?>';
    var profession = '<?php echo $defaultJob; ?>', money = '<?php echo isset($user->extend)?$user->extend->income:'2000以下'; ?>', company, email;
    $("#submit_content").click(function () {
        zhuge.track('身份信息填写页面-提交按钮');
        $('#iden_error').html('');
        company = $("input[name='company']").val();
        email = $.trim($("input[name='email']").val());
        
        if (profession == '') {
            $('#iden_error').html('*请选择职业');
            return false;
        }
        if (money == '') {
            $('#iden_error').html('*请选择收入');
            return false;
        }
        if (company == '') {
            $('#iden_error').html('*请填写公司信息');
            return false;
        }
        if (email == '') {
            $('#email_error').show();
            return false;
        }else{
            if(!is_email(email)){
                $('#email_error').show();
                return false;
            }
        }
        do_read();
     
    });
    var pro = '<?php echo $pro; ?>';
    var profess = eval("(" + pro + ")");
    $.scrEvent({
        data: profess, // 数据
        evEle: '#job', // 选择器
        title: '选择职业', // 标题
        defValue: '<?php echo $defaultJob; ?>', // 默认值
        afterAction: function (data) {   //  点击确定按钮后,执行的动作
            $('#job').val(data);
            $("input[name='profession']").val(data);
            profession = data;
        }
    });
    $.scrEvent({
        data: ['2000以下', '2000-2999', '3000-3999', '4000-4999', '5000以上'], // 数据
        evEle: '#money', // 选择器
        title: '收入', // 标题
        defValue: '<?php echo isset($user->extend)?$user->extend->income:'2000以下'; ?>', // 默认值
        afterAction: function (data) {   //  点击确定按钮后,执行的动作
            $('#money').val(data);
            $("input[name='money']").val(data);
            money = data;
        }
    });

    //邮箱验证
    function is_email(email) {
        if ( email == "") {
            return false;
        } else {
            if (! /^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/.test(email)) {
                return false;
            }
        }
        return true;
    }
    function do_read(){ //承诺函弹层
        zhuge.track('身份信息承诺函-点击阅读并同意按钮');
        $('.shade').show();
        $('.read').show();
    }
    function do_submits(){
           $.post("/borrow/userauth/nameauthmodify", {_csrf: csrf, money: money, company: company, profession: profession, email:email}, function (result) {
            $('#email_error').hide();
            var data = eval("(" + result + ")");
            if (data.res_code == 0) {
                var location_href = "<?php echo $redirect_info; ?>";
                window.location = location_href;
            } else {
                $('.shade').hide();
                $('.read').hide();
                $('#errormsg').show();
                $('#errormsg').html(data.res_data);
                return false;
            }
        });
    }
    function cancle_toast(){
         $('.shade').hide();
        $('.read').hide();
    }
</script>