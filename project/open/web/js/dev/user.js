// JavaScript Document
var count, countdown;
var _mobileRex = /^(1(([3578][0-9])|(47)))\d{8}$/;
var _numberRex = /^[0-9]*[1-9][0-9]*$/;
$(function () {

    //注册发送短信验证码
    $('#reggetcode').click(function () {
        var mobile = $("#regmobile").val();

        if (mobile == '' || !(_mobileRex.test(mobile))) {
            $("#reg_one_error").html('请输入正确的手机号码');
            $("#regmobile").focus();
            return false;
        }
        $("#reggetcode").attr('disabled', true);
        var appid = 'wx476bb3649401c450';
        var app_url = 'http://weixin.xianhuahua.com';
        $.post("/dev/reg/onesend", {mobile: mobile}, function (result) {
//			debugger;
            var data = eval("(" + result + ")");
            if (data.ret == '0') {
                //发送成功
                count = 60;
                countdown = setInterval(CountDown, 1000);
            } else if (data.ret == '2')
            {
                $("#reg_one_error").html('该手机号码已超出每日短信最大获取次数');
                $("#regmobile").focus();
                $("#reggetcode").attr('disabled', false);
                return false;
            }
            else {
                $.Zebra_Dialog('该手机号已注册，请直接<a href="https://open.weixin.qq.com/connect/oauth2/authorize?appid=' + appid + '&redirect_uri=' + app_url + '/dev/reg/login&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect">登录</a>', {
                    'type': 'question',
                    'title': '手机验证',
                    'buttons': [
//				                    {caption: '取消', callback: function() {}},
                        {caption: '确定', callback: function () {
                            }},
                    ]
                });
                $("#reggetcode").attr('disabled', false);
            }
        });

    });

    //登录发送短信验证码
    $('#reggetcode_login').click(function () {
        var mobile = $("#regmobile").val();

        if (mobile == '' || !(_mobileRex.test(mobile))) {
            $("#reg_one_error").html('请输入正确的手机号码');
            $("#regmobile").focus();
            return false;
        }

        $("#reggetcode_login").attr('disabled', true);
        var appid = 'wx476bb3649401c450';
        var app_url = 'http://weixin.xianhuahua.com';
        $.post("/dev/reg/loginsend", {mobile: mobile}, function (result) {
            var data = eval("(" + result + ")");
            //var data = result;
            if (data.ret == '0') {
                //发送成功
                count = 60;
                countdown = setInterval(CountDown_login, 1000);
                $("#reggetcode_login").attr('disabled', false);
            } else if (data.ret == '2')
            {
                $("#reg_one_error").html('该手机号码已超出每日短信最大获取次数');
                $("#regmobile").focus();
                $("#reggetcode_login").attr('disabled', false);
                return false;
            }
            else {
                $.Zebra_Dialog('手机号不存在，去<a href="https://open.weixin.qq.com/connect/oauth2/authorize?appid=' + appid + '&redirect_uri=' + app_url + '/dev/reg/one&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect">注册</a>', {
                    'type': 'question',
                    'title': '手机验证',
                    'buttons': [
//				                    {caption: '取消', callback: function() {}},
                        {caption: '确定', callback: function () {
                            }},
                    ]
                });
                $("#reggetcode_login").attr('disabled', false);
            }
        });

    });

    $("#login_button").click(function () {
        var mobile = $("#regmobile").val();
        if (mobile == '' || !(_mobileRex.test(mobile))) {
            $("#reg_one_error").html('请输入正确的手机号码');
            $("#regmobile").focus();
            return false;
        }
        var code = $("#regcode").val();
        if (code == '' || !(_numberRex.test(code))) {
            $("#reg_one_error").html('请输入正确的验证码');
            $("#regcode").focus();
            return false;
        }
        var appid = 'wx476bb3649401c450';
        var app_url = 'http://weixin.xianhuahua.com';
        $.post("/dev/reg/loginsave", {mobile: mobile, code: code}, function (result) {
            var data = eval("(" + result + ")");
            if (data.ret == '0') {
                if (data.url != '') {
                    window.location = data.url;
                } else {
                    $("#reg_one_error").html('提交失败');
                }
            } else if (data.ret == '2') {
                $("#reg_one_error").html('手机号不存在，去<a href="https://open.weixin.qq.com/connect/oauth2/authorize?appid=' + appid + '&redirect_uri=' + app_url + '/dev/reg/one&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect">注册</a>');
            } else if (data.ret == '4') {
                $("#reg_one_error").html('对不起，您输入的手机号码有误，请用注册时的手机号码登录！');
            } else if (data.ret == '5') {
                $("#reg_one_error").html('系统错误');
            }
            else {
                $("#reg_one_error").html('验证码错误');
            }

        });
    });

    //选择学生
    $("#regtypestudent").bind('click', function () {
        //设置高亮//////////////////////////////////
        $("#user_type").val('1');
        $("#regtypeshehui").attr('src', '/images/staff_un.png');
        $(this).attr('src', '/images/student.png');
        $('.Hmask').show();
//                                   alert('sss');
        $('#identitys').show();
    });
    //选择社会人
    $("#regtypeshehui").bind('click', function () {
        //设置高亮//////////////////////////////////
        $("#user_type").val('2');
        $("#regtypestudent").attr('src', '/images/student_un.png');
        $(this).attr('src', '/images/staff.png');
        $('.Hmask').show();
//                                   alert('sss');
        $('#identity').show();
    });
    //手机验证提交
    $("#regonebutton").click(function () {
        var mobile = $("#regmobile").val();
        //alert(mobile);exit;
        if (mobile == '' || !(_mobileRex.test(mobile))) {
            $("#reg_one_error").html('请输入正确的手机号码');
            $("#regmobile").focus();
            return false;
        }
        var code = $("#regcode").val();
        if (code == '' || !(_numberRex.test(code))) {
            $("#reg_one_error").html('请输入正确的验证码');
            $("#regcode").focus();
            return false;
        }
        var from_code = $("#from_code").val();
        if (from_code != '' && !(_numberRex.test(from_code))) {
            $("#reg_one_error").html('请输入正确的邀请码');
            $("#regcode").focus();
            return false;
        }
        //var user_type = $("#user_type").val();
        //if( user_type == '' ){
        //	$("#reg_one_error").html('请选择你的身份');
        //	return false;
        //}
        var appid = 'wx476bb3649401c450';
        var app_url = 'http://weixin.xianhuahua.com';
        var url_type = $("#url_type").val();
        $.post("/dev/reg/onesave", {mobile: mobile, code: code, url_type: url_type, from_code: from_code}, function (result) {
            var data = eval("(" + result + ")");
            //var data = result;
            //alert(data);
            if (data.ret == '0') {
                if (data.url != '') {
                    window.location = data.url;
                } else {
                    $("#reg_one_error").html('提交失败');
                }
            } else if (data.ret == '3') {
                $.Zebra_Dialog('先花花用户，请直接登录', {
                    'type': 'question',
                    'title': '手机验证',
                    'buttons': [
//				                    {caption: '取消', callback: function() {}},
                        {caption: '确定', callback: function () {
                                if (data.url != '') {
                                    window.location = data.url;
                                } else {
                                    alert('请返回重新进入');
                                }
                            }},
                    ]
                });
            } else if (data.ret == '2') {
                $("#reg_one_error").html('该手机已注册，请直接<a href="https://open.weixin.qq.com/connect/oauth2/authorize?appid=' + appid + '&redirect_uri=' + app_url + '/dev/reg/login&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect">登录</a>');
            } else if (data.ret == '4') {
                $("#reg_one_error").html('您不是先花花用户，请返回上级页面输入邀请码进入');
            } else if (data.ret == '5') {
                $("#reg_one_error").html('该微信已绑定其它的手机号');
            } else if (data.ret == '6') {
                $("#reg_one_error").html('您使用的邀请码不符合规则，请从其他渠道获取');
            } else if (data.ret == '7') {
                $("#reg_one_error").html('邀请码错误');
            } else {
                $("#reg_one_error").html('请输入正确的验证码');
            }

        });
    });
    $("#reg_school").change(function () {
        $("#reg_school_name").val($('#reg_school option:selected').text());
    });
    //身份证验证
    $("#reg_identity").blur(function () {
        var identity = $(this).val();
        if (identity == '') {
            alert('请填写姓名/身份证号码');
            return false;
        }

        if (!checkregisteridentity(identity)) {
            alert('请填写正确的身份证号码');
            return false;
        } else {
            $("#reg_identity").attr('is_real', '1');
        }

        return true;
    });

    $("#loan_confirm").click(function () {
        var agree_xieyi = $("#agree_loan_xieyi").is(":checked");
        if (agree_xieyi)
        {
            var desc = $("input[name='desc']").val();
            var days = $("input[name='days']").val();
            var amount = $("input[name='amount']").val();
            var coupon_id = $("input[name='coupon_id']").val();
            var coupon_amount = $("input[name='coupon_amount']").val();
            var bank_id = $("input[name='bank_id']").val();
            //alert(bank_id);
            $("#loan_confirm").attr('disabled', true);
            $.post("/dev/loan/confirm", {desc: desc, days: days, amount: amount, coupon_id: coupon_id, coupon_amount: coupon_amount, bank_id: bank_id}, function (result) {
                var data = eval("(" + result + ")");
                //alert(data);
                if (data.ret == '3')
                {
                    $("#loan_confirm").attr('disabled', false);
                }
                else if (data.ret == '4')
                {
                    $("#loan_confirm").attr('disabled', false);
                    alert('您不能重复借款');
                }
                else if (data.ret == '5')
                {
                    $("#loan_confirm").attr('disabled', false);
                    alert('您已被驳回，请先去上传自拍照');
                }
                else if (data.ret == '6')
                {
                    $("#loan_confirm").attr('disabled', false);
                    alert('您提交的信息不符合规则，该账户已被冻结');
                }
                window.location = data.url;
            });
        }
        else
        {
            alert('同意借款协议才能借款');
            return false;
        }
    });

    $("#reg_fromcode_button").click(function () {
        var code = $('#reg_from_code').val();
        if (code == '' || !(_numberRex.test(code))) {
            $("#reg_code_tip").html('请输入正确的邀请码');
            return false;
        }
        $.post("/dev/invite/invitesave", {code: code}, function (result) {
            var data = eval("(" + result + ")");
            if (data.ret == '0') {
                window.location = data.url;
            } else if (data.ret == '1') {
                $("#reg_code_tip").html('邀请码错误');

            } else if (data.ret == '3') {
                $("#reg_code_tip").html('您使用的邀请码不符合规则，请从其他渠道获取');
            }
            else if (data.ret == '2') {
                $("#reg_code_tip").html('网络失败，请重试');
            }
        });
    });

    //点击去找熟人按钮，跳转至新页面
    $("#find_friend").click(function () {
        var user_id = $('#open_id').val();
        window.location = "/dev/share/share?open_id=" + user_id;
    });

    $("#invest_friend").click(function () {
        window.location = "/dev/invest/detail";
    });

    $("#invest_detail").click(function () {
        window.location = "/dev/invest/confirm";
    });

    $("#input_amount").keyup(function () {
        var input_amount = $(this).val();
        var regamount = /^[1-9]*[1-9][0-9]*$/;
        if (!regamount.test(input_amount))
        {
            return false;
        }
        //获取年化利率
        var rate = $("#rate").val();
        //获取投资天数
        var invest_days = $("#invest_day").val();
        //计算预计收益
        var profit = (input_amount * (rate / 100) / 365) * invest_days;
        $("#yuji_profit").html(profit.toFixed(2));
    });

    $("#invest_amount").keyup(function () {
        var input_amount = $(this).val();
        var regamount = /^[1-9]*[1-9][0-9]*$/;
        if (!regamount.test(input_amount))
        {
            return false;
        }
        //获取年化利率
        var rate = 5;
        //获取投资天数
        var invest_days = 1;
        //计算预计收益
        var profit = (input_amount * (rate / 100) / 365) * invest_days;
        $("#yuji_xhb_profit").html(profit.toFixed(2));
    });

    $("#invest_confirm").click(function () {
        var invest_amount = $('#invest_amount').val();
        var input_amount = $('#input_amount').val();
        var loan_id = $('#loan_id').val();
        var agree_xieyi = $("#agree_invest_xieyi").is(":checked");
        var regamount = /^[1-9]*[1-9][0-9]*$/;
        if (agree_xieyi)
        {
            if (input_amount == '' || input_amount == null)
            {
                alert('请输入投资金额');
                return false;
            }
            if (!regamount.test(input_amount))
            {
                alert('投资金额必须是整数');
                return false;
            }
            if (parseInt(input_amount) > parseInt(invest_amount))
            {
                alert('输入的投资金额不能大于可投资金额');
                return false;
            }
            $("#invest_confirm").attr('disabled', true);
            $.post("/dev/invest/addsave", {loan_id: loan_id, input_amount: input_amount}, function (data) {
                if (data == 'fail')
                {
                    alert('投资失败')
                    $("#invest_confirm").attr('disabled', false);
                    return false;
                }
                else if (data == 'moreamount')
                {
                    alert('输入的投资金额多于未筹满的额度');
                    $("#invest_confirm").attr('disabled', false);
                    return false;
                }
                else if (data == 'morethree')
                {
                    alert('投资金额过大');
                    $("#invest_confirm").attr('disabled', false);
                    return false;
                }
                else if (data == 'moresecond')
                {
                    alert('投资金额过大');
                    $("#invest_confirm").attr('disabled', false);
                    return false;
                }
                else
                {
                    window.location = "/dev/invest/success?invest_id=" + data;
                }
            });
        }
        else
        {
            alert('您需要同意投资协议才能投资')
            return false;
        }
        //window.location = "/dev/invest/success";
    });

    $("#invest_again").click(function () {
        window.location = "/dev/invest";
    });

    $("#confirm_repay").click(function () {
        var pic_repay1 = $("input[name='pic_repay1']").val();
        if (pic_repay1.length < 1)
        {
            alert("请添加还款凭证");
            return false;
        }
        $(this).attr('disabled', true);
        $("#form_repay").submit();
    });

    $("#reg_two_form").click(function () {
        var school = $('#reg_school').val();
        var school_name = $('#reg_school_name').val();
        var edu = $('#reg_edu').val();
        var school_time = $('#reg_school_time').val();
        var realname = $('#reg_realname').val();
        var identity = $('#reg_identity').val();
        var is_real = $('#reg_identity').attr('is_real');
        var from_url = $('#from_url').val();
        var f_url = $('#f_url').val();
        if (school == 0) {
            alert("请选择学校");
            return false;
        }
        if (school_time == '0') {
            alert("请选择入学年份");
            return false;
        }
        if (edu == '0') {
            alert("请选择学历");
            return false;
        }
        /*if (realname == '') {
            alert("请添写你的真实姓名");
            return false;
        }*/
       /* if (identity == '0' || is_real == '0') {
            //alert("请填写姓名/身份证号码");
            //return false;
        }*/
        $.post("/dev/reg/twosave", {school: school, school_name: school_name, edu: edu, school_time: school_time, realname: realname, identity: identity, from_url: from_url, f_url: f_url}, function (result) {
            var data = eval("(" + result + ")");
            if (data.ret == '0') {
                if (data.url != '') {

                    window.location = data.url;
                } else {
                    alert('提交失败');
                }

            } else if (data.ret == '2')
            {
                alert('该身份证号已存在，请更换');
            }else if(data.ret == '11'){
                alert("请填写姓名/身份证号码");
            }
            else if (data.ret == '3')
            {
                window.location = '/dev/account/black';
            }
            else {
                alert('学籍认证失败，请重新修改');

            }

        });
    });
    $("#reg_shtwo_form").click(function () {
        var industry = $('#reg_industry').val();
        var company = $('#reg_company').val();
        var position = $('#reg_position').val();
        var realname = $('#reg_realname').val();
        var identity = $('#reg_identity').val();
        var is_real = $('#reg_identity').attr('is_real');
        var from_url = $('#from_url').val();
        var f_url = $('#f_url').val();
        if (industry == '0') {
            alert("请选择行业");
            return false;
        }
        if (company == '') {
            alert("请输入公司名称");
            return false;
        }
        if (position == '0') {
            alert("请选择公司职位");
            return false;
        }
        /*if (realname == '') {
            alert("请选择你的真实姓名");
            return false;
        }
        if (identity == '0' || is_real == '0') {
            alert("请填写姓名/身份证号码");
            return false;
        }*/
        $.post("/dev/reg/shtwosave", {industry: industry, company: company, position: position, realname: realname, identity: identity, from_url: from_url, f_url: f_url}, function (result) {
//			alert(result);
//			debugger;
            var data = eval("(" + result + ")");
            if (data.ret == '0') {
                if (data.url != '') {

                    window.location = data.url;
                } else {
                    alert('提交失败');
                }

            } else if (data.ret == '2')
            {
                alert('该身份证号已存在，请更换');
            }else if(data.ret == '11'){
                  alert("请填写姓名/身份证号码");
            }
            else if (data.ret == '3')
            {
                window.location = '/dev/account/black';
            }
            else if (data.ret == '4')
            {
                alert('身份认证失败，请重新修改');
            }
            else {
                alert('提交失败，请重新提交');

            }

        });
    });
    $("#reg_shmodifytow_form").click(function () {
        var industry = $('#reg_industry').val();
        //alert(industry);
        var company = $('#reg_company').val();
        //alert(company);
        var position = $('#reg_position').val();
        var is_real = $('#reg_identity').attr('is_real');
        var from_url = $('#from_url').val();
        var f_url = $('#f_url').val();
        if (industry == '0') {
            alert("请选择行业");
            return false;
        }
        if (company == '') {
            alert("请输入公司名称");
            return false;
        }
        if (position == '0') {
            alert("请选择公司职位");
            return false;
        }
        $.post("/dev/reg/shmodifytowsave", {industry: industry, company: company, position: position, from_url: from_url, f_url: f_url}, function (result) {
//			alert(result);
            var data = eval("(" + result + ")");
            if (data.ret == '0') {
                if (data.url != '') {

                    window.location = data.url;
                } else {
                    alert('提交失败');
                }

            }
            else if (data.ret == '3')
            {
                window.location = '/dev/account/black';
            }
            else {
                var data = eval("(" + result + ")");
                window.location = data.url;

            }

        });
    });
    $("#get_user_headurl").click(function () {
        $.post("/dev/account/getuserinfo", {}, function (data) {
            window.location = "/dev/account/personal";
        });
    });
    $("#reg_shthree_form").click(function () {
        var school = $('#reg_school').val();
        var school_name = $('#reg_school_name').val();
        var edu = $('#reg_edu').val();
        var school_time = $('#reg_school_time').val();
        var f_url = $('#f_url').val();
        if (school == 0) {
            alert("请选择学校");
            return false;
        }
        if (school_time == '0') {
            alert("请选择入学年份");
            return false;
        }
        if (edu == '0') {
            alert("请选择学历");
            return false;
        }
        $.post("/dev/reg/shthreesave", {school: school, school_name: school_name, edu: edu, school_time: school_time, f_url: f_url}, function (result) {
//			alert(result);
//			debugger;
            var data = eval("(" + result + ")");
            if (data.ret == '0') {
                if (data.url != '') {

                    window.location = data.url;
                } else {
                    alert('提交失败');
                }

            } else {
                alert('学籍认证失败，请重新修改');

            }

        });
    });
    //上传照片
    $("#reg_pic_button").click(function () {
        if ($("#reg_serverid").val() == '') {
            alert('请按照标准上传证件照');
            return false;
        }

        $("#reg_pic_form").submit();
    });
});

var lxfEndtime = function () {
    var endtime = $(".time").attr("endtime") * 1000;//取结束日期(毫秒值)
    var nowtime = new Date().getTime();        //今天的日期(毫秒值)
    var youtime = endtime - nowtime;//还有多久(毫秒值)
    var seconds = youtime / 1000;
    var minutes = Math.floor(seconds / 60);
    var hours = Math.floor(minutes / 60);
    var days = Math.floor(hours / 24);
    var CDay = days;
    var CHour = hours % 24;
    if (CHour < 10)
    {
        CHour = '0' + CHour;
    }
    var CMinute = minutes % 60;
    if (CMinute < 10)
    {
        CMinute = '0' + CMinute;
    }
    var CSecond = Math.floor(seconds % 60);//"%"是取余运算，可以理解为60进一后取余数，然后只要余数。
    if (CSecond < 10)
    {
        CSecond = '0' + CSecond;
    }
    if (endtime <= nowtime) {
        //$(".time").html("<span class='times'>00<span class='font'>时</span></span>&nbsp;<span class='colon'>:</span>&nbsp;<span class='times'>00<span class='font'>分</span></span>&nbsp;<span class='colon'>:</span>&nbsp;<span class='times'>00<span class='font'>秒</span></span>");//如果结束日期小于当前日期就提示过期啦
        $(".time").html("<span>" + 00 + "</span>:<span>" + 00 + "</span>:<span>" + 00 + "</span>");
    } else {
        //$(".time").html("<span class='times'>"+CHour+"<span class='font'>时</span></span>&nbsp;<span class='colon'>:</span>&nbsp;<span class='times'>"+CMinute+"<span class='font'>分</span></span>&nbsp;<span class='colon'>:</span>&nbsp;<span class='times'>"+CSecond+"<span class='font'>秒</span></span>");
        $(".time").html("<span>" + CHour + "</span>:<span>" + CMinute + "</span>:<span>" + CSecond + "</span>");
    }
    setTimeout('lxfEndtime()', 1000);
}

//登录倒计时
var CountDown_login = function () {

    $("#reggetcode_login").attr("disabled", true).addClass('dis');
    $("#reggetcode_login").html("重新获取 ( " + count + " ) ");
    if (count <= 0) {
        $("#reggetcode_login").html("获取验证码").removeAttr("disabled").removeClass('dis');
        clearInterval(countdown);
    }
    count--;
};

//倒计时
var CountDown = function () {

    $("#reggetcode").attr("disabled", true).addClass('dis');
    $("#reggetcode").html("重新获取 ( " + count + " ) ");
    if (count <= 0) {
        $("#reggetcode").html("获取验证码").removeAttr("disabled").removeClass('dis');
        clearInterval(countdown);
    }
    count--;
};
var checkregisteridentity = function (idcard) {
    var area = {11: "北京", 12: "天津", 13: "河北", 14: "山西", 15: "内蒙古", 21: "辽宁", 22: "吉林", 23: "黑龙江", 31: "上海", 32: "江苏", 33: "浙江", 34: "安徽", 35: "福建", 36: "江西", 37: "山东", 41: "河南", 42: "湖北", 43: "湖南", 44: "广东", 45: "广西", 46: "海南", 50: "重庆", 51: "四川", 52: "贵州", 53: "云南", 54: "西藏", 61: "陕西", 62: "甘肃", 63: "青海", 64: "宁夏", 65: "新疆", 71: "台湾", 81: "香港", 82: "澳门", 91: "国外"}
    var idcard, Y, JYM;
    var S, M;
    var idcard_array = new Array();
    idcard_array = idcard.split("");
    //地区检验 
    if (area[parseInt(idcard.substr(0, 2))] == null)
        return false;
    //身份号码位数及格式检验 
    switch (idcard.length) {
        case 15:
            if ((parseInt(idcard.substr(6, 2)) + 1900) % 4 == 0 || ((parseInt(idcard.substr(6, 2)) + 1900) % 100 == 0 && (parseInt(idcard.substr(6, 2)) + 1900) % 4 == 0)) {
                ereg = /^[1-9][0-9]{5}[0-9]{2}((01|03|05|07|08|10|12)(0[1-9]|[1-2][0-9]|3[0-1])|(04|06|09|11)(0[1-9]|[1-2][0-9]|30)|02(0[1-9]|[1-2][0-9]))[0-9]{3}$/; //测试出生日期的合法性 
            } else {
                ereg = /^[1-9][0-9]{5}[0-9]{2}((01|03|05|07|08|10|12)(0[1-9]|[1-2][0-9]|3[0-1])|(04|06|09|11)(0[1-9]|[1-2][0-9]|30)|02(0[1-9]|1[0-9]|2[0-8]))[0-9]{3}$/; //测试出生日期的合法性 
            }
            if (ereg.test(idcard))
                return true;
            else
                return false;
            break;
        case 18:
            //18位身份号码检测 
            //出生日期的合法性检查 
            //闰年月日:((01|03|05|07|08|10|12)(0[1-9]|[1-2][0-9]|3[0-1])|(04|06|09|11)(0[1-9]|[1-2][0-9]|30)|02(0[1-9]|[1-2][0-9])) 
            //平年月日:((01|03|05|07|08|10|12)(0[1-9]|[1-2][0-9]|3[0-1])|(04|06|09|11)(0[1-9]|[1-2][0-9]|30)|02(0[1-9]|1[0-9]|2[0-8])) 
            if (parseInt(idcard.substr(6, 4)) % 4 == 0 || (parseInt(idcard.substr(6, 4)) % 100 == 0 && parseInt(idcard.substr(6, 4)) % 4 == 0)) {
                ereg = /^[1-9][0-9]{5}19[0-9]{2}((01|03|05|07|08|10|12)(0[1-9]|[1-2][0-9]|3[0-1])|(04|06|09|11)(0[1-9]|[1-2][0-9]|30)|02(0[1-9]|[1-2][0-9]))[0-9]{3}[0-9|x|X]$/i; //闰年出生日期的合法性正则表达式 
            } else {
                ereg = /^[1-9][0-9]{5}19[0-9]{2}((01|03|05|07|08|10|12)(0[1-9]|[1-2][0-9]|3[0-1])|(04|06|09|11)(0[1-9]|[1-2][0-9]|30)|02(0[1-9]|1[0-9]|2[0-8]))[0-9]{3}[0-9|x|X]$/i; //平年出生日期的合法性正则表达式 
            }
            if (ereg.test(idcard)) {//测试出生日期的合法性 
                //计算校验位 
                S = (parseInt(idcard_array[0]) + parseInt(idcard_array[10])) * 7
                        + (parseInt(idcard_array[1]) + parseInt(idcard_array[11])) * 9
                        + (parseInt(idcard_array[2]) + parseInt(idcard_array[12])) * 10
                        + (parseInt(idcard_array[3]) + parseInt(idcard_array[13])) * 5
                        + (parseInt(idcard_array[4]) + parseInt(idcard_array[14])) * 8
                        + (parseInt(idcard_array[5]) + parseInt(idcard_array[15])) * 4
                        + (parseInt(idcard_array[6]) + parseInt(idcard_array[16])) * 2
                        + parseInt(idcard_array[7]) * 1
                        + parseInt(idcard_array[8]) * 6
                        + parseInt(idcard_array[9]) * 3;
                Y = S % 11;
                M = "F";
                JYM = "10X98765432";
                M = JYM.substr(Y, 1); //判断校验位 
                if (M == idcard_array[17])
                    return true; //检测ID的校验位 
                else
                    return false;
            }
            else
                return false;
            break;
        default:
            return false;
            break;
    }
};
var log = function (event, data, formatted) {
    $("#reg_school_id").val(data.name);
}

function IdentityCodeValid(code) {
    var city = {11: "北京", 12: "天津", 13: "河北", 14: "山西", 15: "内蒙古", 21: "辽宁", 22: "吉林", 23: "黑龙江 ", 31: "上海", 32: "江苏", 33: "浙江", 34: "安徽", 35: "福建", 36: "江西", 37: "山东", 41: "河南", 42: "湖北 ", 43: "湖南", 44: "广东", 45: "广西", 46: "海南", 50: "重庆", 51: "四川", 52: "贵州", 53: "云南", 54: "西藏 ", 61: "陕西", 62: "甘肃", 63: "青海", 64: "宁夏", 65: "新疆", 71: "台湾", 81: "香港", 82: "澳门", 91: "国外 "};
    var tip = "";
    var pass = true;
    if (!code || !/^\d{6}(18|19|20)?\d{2}(0[1-9]|1[12])(0[1-9]|[12]\d|3[01])\d{3}(\d|X)$/i.test(code)) {
        tip = "身份证号格式错误";
        pass = false;
    }
    else if (!city[code.substr(0, 2)]) {
        tip = "地址编码错误";
        pass = false;
    }
    else {
        //18位身份证需要验证最后一位校验位
        if (code.length == 18) {
            code = code.split('');
            //∑(ai×Wi)(mod 11)
            //加权因子
            var factor = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];
            //校验位
            var parity = [1, 0, 'X', 9, 8, 7, 6, 5, 4, 3, 2];
            var sum = 0;
            var ai = 0;
            var wi = 0;
            for (var i = 0; i < 17; i++)
            {
                ai = code[i];
                wi = factor[i];
                sum += ai * wi;
            }
            var last = parity[sum % 11];
           /* if (parity[sum % 11] !=code[17]) {
                tip = "校验位错误";
                pass = false;
            }*/
        }
    }
    if (!pass)
        //alert(tip);
    return pass;
}
