<?php

use app\commonapi\ImageHandler;

$oImage = new ImageHandler();
$uploadurl = $oImage->img_upload_url;
$imgurl = $oImage->img_domain_url;
?>

<?php \app\common\PLogger::getInstance('weixin', '', $user->user_id); ?>
<?php $json_data = \app\common\PLogger::getJson(); ?>
<script>
    var baseInfoss = eval('(' + '<?php echo $json_data; ?>' + ')');
</script>
<!-- 进度条 -->
<div class="progress"></div>

<!-- 照片 -->
<div class="photo">
    <!-- 上传图片界面 -->
    <div>
        <h4>请上传您的身份证照片</h4>
        <div class="photo_info">
            <div class="info zhengmian" onclick="click_title('正面','zhengmian')">
                <div class="base bg_z">
                    <img id="i_zheng" class="paishe_icon" src="/borrow/310/images/paishe_icon.png">
                </div>
                <span>身份证正面(人像面)</span>
            </div>
            <div class="info fanmian" onclick="click_title('反面','fanmian')">
                <div class="base bg_f">
                    <img id="i_fan" class="paishe_icon" src="/borrow/310/images/paishe_icon.png">
                </div>
                <span>身份证反面(国徽面)</span>
            </div>
        </div>
    </div>

    <!-- 成功上传 -->
    <div class="success_upload" hidden>
        <img src="/borrow/310/images/success_upload_icon.png">
        <p>身份证照片已上传</p>
    </div>

    <div class="photo_input">
        <div>
            <label>姓名</label>
            <input type="text" name="name" value="">
        </div>
        <div class="shenfenzheng">
            <label>身份证号</label>
            <input type="text" name="idcard" value="">
        </div>
        <input type="hidden" name="pic_identity" value="">
        <input type="hidden" name="nation" value="">
        <input type="hidden" name="iden_address" value="">
    </div>
</div>

<div class="hr"></div>
<div class="per_info">
    <div>
        <label>职业</label>
        <p id="job">请选择职业信息</p>
        <img src="/borrow/310/images/left_icon.png">
        <input type="hidden" name="profession" value="">
    </div>
    <div>
        <label>月收入</label>
        <p id="money">请选择收入水平</p>
        <img src="/borrow/310/images/left_icon.png">
        <input type="hidden" name="money" value="">
    </div>
    <div class="com_reg">
        <label>公司名称</label>
        <input type="text" placeholder="请输入您所在公司名称" name="company">
        <img src="/borrow/310/images/left_icon.png" style="visibility: hidden;">
    </div>
    <div class="com_reg" style="border-top: solid 0.04rem #f7f7f7;">
        <label>邮箱</label>
        <p id="email_error" style="left: 2.05rem;" hidden>*请填写正确的电子邮箱</p>
        <input type="text" placeholder="请输入您的常用邮箱" name="email" value="">
        <img src="/borrow/310/images/left_icon.png" style="visibility: hidden;">
    </div>
    <p id="iden_error" style="color: red;" class=""></p>
</div>
<div class="progress_btn ok_btn" id="submit_content">提交</div>

<div class="help">
    <img src="/borrow/310/images/help_deng.png">
    <a href="/borrow/helpcenter/list?position=3&user_id=<?php echo $user->user_id; ?>"
       style="text-decoration:none;color:#3D81FF;"><span>获取帮助</span></a>
</div>

<div id="shade" class="shade" hidden>
    <!-- 点击拍摄弹窗 -->
    <div id="photo_alert" class="click_pro" hidden>
        <div class="top">
            <img id="i_close" src="/borrow/310/images/close.png" alt="">
            <span>拍摄示例</span>
        </div>
        <div id="model" class="zhengmian"></div>
        <p class="tips">
            <span>边框缺失</span>
            <span>照片模糊</span>
            <span>散光强烈</span>
        </p>
        <div class="click_btn">
            <input id="file" class="file" type="file" accept="image/*" capture="camera"
                   onclick="saomiao_title('file')"> 开始扫描
        </div>
    </div>

    <!-- 身份证上传结果反馈 -->
    <div class="result" hidden>
        <p id="close">
            <img src="/borrow/310/images/close_ccc.png">
        </p>
        <!-- 上传失败,重新上传 -->
        <div class="err_msg err_font" hidden>
            <p class="tips_1 front">身份证正面(人像面)上传失败</p>
            <p class="tips_2">请重新上传</p>
            <!--<div class="result_btn">重新上传</div>-->
            <div class="result_btn click_btn" id="repeat1" style="position: relative" hidden>
                <input id="file1" class="file" type="file" accept="image/*" capture="camera"
                       onclick="tongji('file1', baseInfoss)"> 重新上传
            </div>
        </div>
        <!-- 上传成功，提示下一步 -->
        <div class="err_msg success_font" hidden>
            <p class="tips_1 success_msg_tip">身份证正面（人像面）上传成功</p>
            <p class="tips_2 success_details">请上传身份证反面（国徽面）</p>
            <div class="result_btn click_btn" id="success_repeat" style="position: relative" hidden>
                <input id="success_file" class="file" type="file" accept="image/*" capture="camera"
                       onclick="tongji('success_file', baseInfoss)"> <span class="success_btn">上传反面</span>
            </div>
        </div>
        <!-- 确认提交信息 -->
        <div class="success_msg verfiy_msg" id="success_msg" hidden>
            <p class="tips_1">请仔细确认身份证信息</p>
            <p class="tips_2">身份证信息一经提交，无法修改！</p>
            <div class="name">
                <p>姓名：<span id="name"></span></p>
                <p>身份证号：<span id="idcard"></span></p>
            </div>
            <div class="result_btn" id="submit_second">确认提交</div>
        </div>
        <!-- 确认提交信息 -->
    </div>
    <!--身份认证限制弹窗-->
    <!--        <div class="mask" id="alert_shade" hidden style="height: 100%;width: 100%;background: rgba(0, 0, 0, .6);position: fixed;left: 0;top: 0;z-index: 800;"></div>-->
    <div class="tccontents" id="iden_msg" hidden style=" position: fixed;top: 28%;left: 10%;z-index: 999;background: #fff;width: 80%;height:165px;border-radius: 8px;">
        <img src="/borrow/310/images/bill-close.png" style="width:15px;height: 15px;position: absolute;right: 15px;top: 10px;padding: 0;" onclick="close_toast()" >
        <p class="mask_title" style=" padding-top: 25px;text-align: center;font-size: 16px;font-weight: bold;">温馨提示</p >
        <p class="mask_text" style="width: 80%;margin: 10px auto 0; text-align: left;font-size: 12px;line-height: 16px;">此身份证号码暂不支持认证，如有问题请联系客服，给您带来的不便，敬请谅解。</p >
        <span onclick="lxkf()" class="add_btn go_pwd_list"  id="wait_order" style="background-image: linear-gradient(-90deg, #F00D0D 0%, #FF4B17 100%);border-radius: 5px;height: 20px;width: 100px;position: absolute;left: 50%;    margin-left: -50px;margin-top: 22px;text-align: center;padding-top: 17px;font-size: 14px;color: #FFFFFF; line-height: 4px;" >联系客服</span>
    </div>
    <!--身份认证限制弹窗-->
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
    <div class="read" id="read" hidden>
        <div>
            <img onclick="close_read()" src="/borrow/310/images/close_cnh.png" style="left: 0.2rem;margin-top: 0.2rem;height: 0.5rem;">
            <h4>阅读并同意承诺函</h4>
            <p>根据监管部门要求，本平台不为在校学生提供借贷撮合服务。进入平台请您确认并承诺在先花一亿元平台填写信息的真实性，本人非在校学生、无还款来源或不具备还款能力的借款人。如填写信息与实际情况不符，由本人承担由此产生的一切法律后台，与本平台无关。</p>
        </div>
        <div class="progress_btn ok_btn" onclick="do_read()"  id="submit_read" style="margin-top:7vw">我已阅读并承诺</div>
    </div>
</div>
<div class="shade" hidden>
    <!-- 照片上传中 -->
    <div class="upload_ing" hidden>
        照片上传中...
    </div>
</div>
<script src="/borrow/310/js/picker.js"></script>
<?php if (SYSTEM_ENV == 'prod'): ?>
    <script src="/js/upload/imgupload.js?m=v10" type="text/javascript"></script>
<?php else: ?>
    <script src="/js/upload/imguploadnew.js?m=v10" type="text/javascript"></script>
<?php endif; ?>
<script src="/newdev/js/log.js" type="text/javascript" charset="utf-8"></script>
<input type="hidden" id="positive" value="0">
<input type="hidden" id="behind" value="0">
<script>
    var type = 1;
    var csrf = '<?php echo $csrf; ?>';
    var pic_identity, pic_self, nation, iden_address, name, idcard, profession, money, company, email, success_msg, success_details, next_step;
    $("input[name='name']").focus(function () {
        tongji('name', baseInfoss);
        zhuge.track('身份信息-姓名输入');
    });
    $("input[name='idcard']").focus(function () {
        tongji('idcard', baseInfoss);
        zhuge.track('身份信息-身份证号码输入');
    });
    $("input[name='company']").focus(function () {
        tongji('company', baseInfoss);
        zhuge.track('身份信息-填写公司名称');
    });
    $("input[name='email']").focus(function () {
        tongji('email', baseInfoss);
        zhuge.track('身份信息-填写邮箱');
    });
    $("#submit_content").click(function () {
        tongji('submit_content', baseInfoss);
        zhuge.track('身份信息填写页面-提交按钮');
        $('#iden_error').html('');
        company = $("input[name='company']").val();
        email = $.trim($("input[name='email']").val());
        name = $("input[name='name']").val();
        if (!pic_identity) {
            $('#iden_error').html('*请上传身份证正面照片');
            return false;
        }
        if (!pic_self) {
            $('#iden_error').html('*请上传身份证反面照片');
            return false;
        }
        if (name == '') {
            $('#iden_error').html('*请填写你的真实姓名');
            return false;
        }
        idcard = $("input[name='idcard']").val();
        if (!checkregisteridentity(idcard)) {
            $('#iden_error').html('*请填写正确的身份证号码');
            return false;
        }
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
        } else {
            if (!is_email(email)) {
                $('#email_error').show();
                return false;
            }
        }
        $.post("/borrow/userauth/mifuidentity", {
            _csrf: csrf,
            identity: idcard,
        }, function (result) {
            var data = eval("(" + result + ")");
            if (data.res_code != 0) {
                do_read_one();
                return false;
            }else{
                $('#read').show();
            }
        });
        $('#email_error').hide();
        $("#name").html(name);
        $("#idcard").html(idcard);
        $('#shade').show();
//        $('.result').show();
        $('.result').hide();
//        $('#read').show();
//        $('#success_msg').show();
    });
    function do_read(){
        zhuge.track('身份信息承诺函-点击阅读并同意按钮');
        $('.success_font').hide();
        $('.err_font').hide();
        $('.result').show();
        $('#success_msg').show();
        $('#read').hide();
    }
    function do_read_one(){
        $('#iden_msg').show();
    }
    function lxkf(){
        window.location = 'https://www.sobot.com/chat/h5/index.html?sysNum=f0af5952377b4331a3499999b77867c2&robotFlag=1&partnerId='+<?=  $user->user_id;?>;
    }
    $("#submit_second").click(function () {
        tongji('submit_second', baseInfoss);
        zhuge.track('身份信息身份确认弹窗-点击确认按钮');
        $.post("/borrow/userauth/nameauthajax", {
            _csrf: csrf,
            realname: name,
            identity: idcard,
            iden_url: pic_identity,
            pic_self: pic_self,
            iden_address: iden_address,
            company: company,
            nation: nation,
            profession: profession,
            money: money,
            email: email
        }, function (result) {
            var data = eval("(" + result + ")");
            if (data.res_code == 0) {
                var location_href = "<?php echo $redirect_info; ?>";
                window.location = location_href;
            } else if (data.res_code == 3) {
                $('#errormsg').html(data.res_data);
                var location_href = data.res_data.url;
                window.location = location_href;
            } else {
                $('#errormsg').html(data.res_data);
                return false;
            }
        });
    });
    var pro = '<?php echo $profession ?>';
    var profess = eval("(" + pro + ")");
    console.dir(profess);
    $.scrEvent({
        data: profess, // 数据
        evEle: '#job', // 选择器
        title: '选择职业', // 标题
        defValue: profess[1], // 默认值
        afterAction: function (data) {   //  点击确定按钮后,执行的动作
            console.dir(data);
            $('#job').val(data);
            tongji('job', baseInfoss);
            zhuge.track('身份信息-选择职业', {
                '选项内容' : data,
            });
            $("input[name='profession']").val(data);
            profession = data;
        }
    });
    $.scrEvent({
        data: ['2000以下', '2000-2999', '3000-3999', '4000-4999', '5000以上'], // 数据
        evEle: '#money', // 选择器
        title: '收入', // 标题
        defValue: '2000以下', // 默认值
        afterAction: function (data) {   //  点击确定按钮后,执行的动作
            $('#money').val(data);
            tongji('money', baseInfoss);
            zhuge.track('身份信息-选择月收入', {
                '选项内容' : data,
            });
            $("input[name='money']").val(data);
            money = data;
        }
    });
    var showErr = function () {
        $('#errormsg').html('*请选择常住地址');
    }
    //正面上传成功回调
    var fnAfter = function (data) {
        var ok = data && parseInt(data.res_code, 10) === 0;
        var urls = data.res_data.supply1;
        $.post("/borrow/userauth/idenfontajax", {_csrf: csrf, urls: urls, type: type}, function (result) {
            $(".shade").hide();
            $(".upload_ing").hide();
            var datas = eval("(" + result + ")");
            if (datas.res_code == 0) {
                if (type == 1) {
                    pic_identity = urls;
                    nation = datas.res_data.nation;
                    iden_address = datas.res_data.iden_address;
                    name = datas.res_data.realname;
                    idcard = datas.res_data.identity;
                    $("input[name='pic_identity']").val(urls);
                    $("input[name='nation']").val(nation);  //民族赋值
                    $("input[name='iden_address']").val(iden_address); //身份证地址赋值
                    $('input[name="name"]').val(name) //姓名赋值
                    $('input[name="idcard"]').val(idcard) //身份证赋值
                    $('#positive').val(1);
                    success_msg = '身份证正面（人像面）上传成功';
                    success_details = '请上传身份证反面（国徽面）';
                    success_btn = '上传反面';
                    next_step = 1;
                } else if (type == 2) {
                    $('#behind').val(1);
                    success_msg = '身份证反面（国徽面）上传成功';
                    success_details = '请上传身份证正面（人像面）';
                    success_btn = '上传正面';
                    next_step = 0;
                    pic_self = urls;
                    $("input[name='pic_self']").val(urls);
                }
                var positive = $('#positive').val();
                var behind = $('#behind').val();
                if(positive == 0 || behind == 0){
                    $('.success_msg_tip').html(success_msg);
                    $('.success_details').html(success_details);
                    $('.success_btn').html(success_btn);
                    $("#success_repeat").hide();
                    $(".err_font").hide();
                    $("#repeat1").hide();
                    $("#repeat2").hide();
                    $('#shade').show();
                    $(".result").show();
                    $(".success_font").show();
                    $("#file").attr('info', next_step);
                    $("#success_repeat").show();
                }
                return false;
            } else {
                if (type == 1) {
                    $('#positive').val(0);
                    msg = '正面';
                    pic_identity = '';
                } else {
                    $('#behind').val(0);
                    msg = '反面';
                    pic_self = '';
                }
                $("#repeat1").hide();
                $("#repeat2").hide();
                $(".success_font").hide();
                $("#success_repeat").hide();
                $('.front').html(msg + datas.res_data.msg);
                $('#shade').show();
                $(".result").show();
                $(".err_font").show();
                $("#file").attr('info', type - 1);
                $("#repeat1").show();
                return false;
            }
        });
        $("#close").on('click', function () {
            $("#shade").hide();
            $(".err_msg").hide();
            $(".result").hide();
            $('#email_error').hide();
            $('#success_msg').hide();
            $(".err_font").hide();
            $("#repeat1").hide();
            $("#repeat2").hide();
            $("#success_repeat").hide();
            $(".success_font").hide();
        });
    }
    var oUpload = new ImageUpload({
        "formid": "uploadImgForm",
        'action': "<?= $uploadurl ?>/upload",
        "encrypt": "<?= $encrypt ?>",
        "error": 'error',
        'afterSave': fnAfter,
        'onupload': function () {
        }
    });
    function createForm(file) {
        var reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = function (e) {
            var html = '<div id="supply1_group"><input id="supply1_url" name="supply1[url]" type="hidden" value=""><input id="supply1_base64" name="supply1[base64]" type="hidden" value="' + e.target.result + '"><input id="supply1_file" name="supply1[file]" type="file" style="display:none;"></div>';
            $("#supply1_group").remove();
            $("#uploadImgForm").append(html);
            $(".shade").show();
            $(".upload_ing").show();
            oUpload.save();
        };
    }
    /**
     * 上传图片前操作
     */
    ImageUpload.prototype.beforeSave = function () {
        return true;
    }

    //邮箱验证
    function is_email(email) {
        if (email == "") {
            return false;
        } else {
            if (!/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/.test(email)) {
                return false;
            }
        }
        return true;
    }
    function close_read(){
        $('#shade').hide();
        $('#read').hide();
    }
    function close_toast() {
        $('#shade').hide();
        $('#iden_msg').hide();

    }
    function click_title(title,maidian){
        tongji(maidian, baseInfoss);
        zhuge.track('身份信息-上传', {
            '按钮名称' : title,
        });
    }
    function saomiao_title(maidian){
        tongji(maidian, baseInfoss);
        var info=$('#file').attr('info');
        if(info==0){
            var title='正面';
        }else{
            var title='反面';
        }

        zhuge.track('身份信息-扫描身份证照片', {
            '按钮名称' : title,
        });
    }
</script>