// JavaScript Document
var count, counthour, countdown;
var _mobileRex = /^(1(([35678][0-9])|(47)))\d{8}$/;
var _numberRex = /^[0-9]*[1-9][0-9]*$/;
$(function () {
    var flagdesc = flagdays = flagamount = false;
    $("#loan_amounts").bind('keyup', function () {
        var days = $("#loan_days").val();
        var amount = $("#loan_amounts").val();
        var rateStr = $("#day_rate").val();
        var rateArr = eval('(' + rateStr + ')');
        var date_rate = 0;
        if (rateArr[days] != undefined) {
            var date_rate = rateArr[days];
        }
        var leastnum = 100;
        if (amount == '' || !(_numberRex.test(amount))) {
            $("#loan_error_tip").html("请输入300~1500的整数");
            $('#mon_col').css('color', '#e74747');
            flagamount = false;
        } else {
            amount = parseInt(amount);
            if (amount < 300 || amount > 1500) {
                $("#loan_error_tip").html("请输入300~1500的整数");
                $('#mon_col').css('color', '#e74747');
                flagamount = false;
            } else {
                if ((amount % leastnum) == 0)
                {
                    $("#loan_error_tip").html("");
                    $('#mon_col').css('color', '#444444');
                    //通道费
                    var withdraw_amounts = 0;
                    var withdraw_amount = parseFloat(amount * 0.01);
                    if (withdraw_amount < 5)
                    {
                        withdraw_amounts = parseFloat(5);
                    } else {
                        withdraw_amounts = withdraw_amount;
                    }
//计算收益
                    var repayVal = parseFloat(amount) + parseFloat(amount * date_rate * days) + withdraw_amount + withdraw_amounts;
                    $('#loan_repay_amount').html(repayVal);
                    flagamount = true;
                }
                else
                {
                    $("#loan_error_tip").html("金额只能是100的整数倍");
                    $('#mon_col').css('color', '#e74747');
                    flagamount = false;
                }
            }
            return flagamount;
        }
    });
    $('#loan_desc').keyup(function () {
        var desc = $("#loan_desc").val();
        if (desc.length < 5 || desc.length > 25) {
            $("#loan_error_tip").html("请输入5~25个字符");
            $('#desc_col').css('color', '#e74747');
            flagdesc = false;
        } else {
            $("#loan_error_tip").html("");
            $('#desc_col').css('color', '#444444');
            flagdesc = true;
        }
        return flagdesc;
    });
    $("#loan_days").bind('keyup', function () {
        var days = $("#loan_days").val();
        var amount = $("#loan_amount").val();
        var rateStr = $("#day_rate").val();
//        var rateArr = eval('(' + rateStr + ')');
        var date_rate = rateStr;
//        if (rateArr[days] != undefined) {
//            date_rate = rateArr[days];
//        }
        var coupon_amount = $("#coupon_amount").val();
        if (days == '' || !(_numberRex.test(days))) {

            $("#loan_error_tip").html("请输入7~31天");
            $('#day_col').css('color', '#e74747');
            flagdays = false;
        } else {
            days = parseInt(days);
            if (days < 7 || days > 31) {
                $("#loan_error_tip").html("请输入7~31天");
                $('#day_col').css('color', '#e74747');
                flagdays = false;
            } else {
                if (coupon_amount != '' && coupon_amount == 0)
                {
                    $("#loan_error_tip").html("");
                    $('#day_col').css('color', '#444444');
                    var repayVal = parseFloat(amount);
                    $('#loan_repay_amount').html(repayVal);
                    flagdays = true;
                }
                else
                {
                    $("#loan_error_tip").html("");
                    $('#day_col').css('color', '#444444');
                    var coupon_amount = $("#coupon_amount").val();
                    //计算收益
                    if (coupon_amount == '')
                    {
                        var repayVal = parseFloat(amount) + parseFloat(amount * date_rate * days);
                    }
                    else
                    {
//判断优惠券的金额是否大于借款的服务费，如果优惠券的金额大于借款的服务费，则优惠券只能优惠服务费的金额，多余的金额作废
                        if (parseFloat(amount * date_rate * days) >= coupon_amount)
                        {
                            var repayVal = parseFloat(amount) + parseFloat(amount * date_rate * days) - coupon_amount;
                        }
                        else
                        {
                            var repayVal = parseFloat(amount);
                        }
//var repayVal = parseFloat(amount)+parseFloat(amount * 0.002 * days)+withdraw_amount-coupon_amount ;
                    }
                    $('#loan_repay_amount').html(repayVal);
                    flagdays = true;
                }
            }

            //今天的日期(毫秒值)
            var nowtime = new Date().getTime();
            //开始时间
            var start_time = $("#start_time").val();
            //结束时间
            var end_time = $("#end_time").val();
            //还款时间
            var repay_time = nowtime + days * 24 * 3600 * 1000;
            if ((repay_time >= start_time) && (repay_time <= end_time)) {
                $("#loan_error_tip").html("请重新输入借款天数，确保还款日在2月15日之后");
                $('#day_col').css('color', '#e74747');
                flagdays = false;
            }
            return flagdays;
        }

    });
    $("#loan_amount").bind('keyup', function () {
        var days = $("#loan_days").val();
        var amount = $("#loan_amount").val();
        var rateStr = $("#day_rate").val();
//        var rateArr = eval('('+rateStr+')');
        var date_rate = rateStr;
//        if( rateArr[days] != undefined ){
//        	date_rate = rateArr[days];
//        }
        var coupon_amount = $("#coupon_amount").val();
        var leastnum = 100;
        if (amount == '' || !(_numberRex.test(amount))) {

            $("#loan_error_tip").html("请输入500~10000的整数");
            $('#mon_col').css('color', '#e74747');
            flagamount = false;
        } else {
//			$("#loan_error_tip").html("");
//			flagamount = true;
            amount = parseInt(amount);
            if (amount < 500 || amount > 10000) {
                $("#loan_error_tip").html("请输入500~10000的整数");
                $('#mon_col').css('color', '#e74747');
                flagamount = false;
            } else {
                if ((amount % leastnum) == 0)
                {
                    if (coupon_amount != '' && coupon_amount == 0)
                    {
                        $("#loan_error_tip").html("");
                        $('#mon_col').css('color', '#444444');
                        var repayVal = parseFloat(amount)
                        $('#loan_repay_amount').html(repayVal);
                        flagamount = true;
                    }
                    else
                    {
                        $("#loan_error_tip").html("");
                        $('#mon_col').css('color', '#444444');
//                        //通道费
//                        var withdraw_amount = parseFloat(amount * 0.01);
//                        if (withdraw_amount < 5)
//                        {
//                            withdraw_amount = 5;
//                        }
//计算收益
                        if (coupon_amount == '')
                        {
                            var repayVal = parseFloat(amount) + parseFloat(amount * date_rate * days);
                        }
                        else
                        {
//判断优惠券的金额是否大于借款的服务费，如果优惠券的金额大于借款的服务费，则优惠券只能优惠服务费的金额，多余的金额作废
                            if (parseFloat(amount * date_rate * days) >= coupon_amount)
                            {
                                var repayVal = parseFloat(amount) + parseFloat(amount * date_rate * days) - coupon_amount;
                            }
                            else
                            {
                                var repayVal = parseFloat(amount);
                            }
                        }
                        $('#loan_repay_amount').html(repayVal);
                        flagamount = true;
                    }
                }
                else
                {
                    $("#loan_error_tip").html("金额只能是100的整数倍");
                    $('#mon_col').css('color', '#e74747');
                    flagamount = false;
                }
            }
            return flagamount;
        }
    });
    //优惠券


    $(".close").click(function () {
        $(".Hmask").hide();
        $(".layer").hide();
    });
    $("#use_rules").click(function () {
        $(".Hmask").css('display', 'block');
        $(".layer").css('display', 'block');
    });
    $("#account_coupon_i_know").click(function () {
        $(".Hmask").hide();
        $(".layer").hide();
    });
    //分享
    $("#freecoupon_share").click(function () {
        $(".Hmask").css('display', 'block');
        $(".guide_share").css('display', 'block');
    });
    $(".Hmask").click(function () {
        $(".Hmask").hide();
        $(".guide_share").hide();
    });
    $(".guide_share").click(function () {
        $(".Hmask").hide();
        $(".guide_share").hide();
    });
    //提现
    $("#withdraw_button").click(function () {
        var coupon = $("#coupon_exist").val();
        var loan_id = $("#loan_id").val();
        if (coupon == 'yes')
        {
            $(".Hmask").css('display', 'block');
            $("#coupon_error").css('display', 'block');
        }
        else
        {
            window.location = "/dev/loan/conwd?l=" + loan_id;
        }
    });
    $("#withdraw_error_button").click(function () {
        $(".Hmask").hide();
        $("#coupon_error").hide();
        var loan_id = $("#loan_id").val();
        window.location = "/dev/loan/conwd?l=" + loan_id;
    });
    //使用优惠券
    $("#use_loan_coupon").click(function () {
        var coupon_amount = $('input:radio:checked').val();
        var coupon_id = $('input:radio:checked').attr('cid');
        if (coupon_amount == undefined || coupon_id == undefined)
        {
            $(".Hmask").hide();
            $(".layer").hide();
            return false;
        }
        var amount = $("#loan_amount").val();
        var limit = $('input:radio:checked').attr('min');
        if ((_numberRex.test(amount)))
        {
            amount = parseInt(amount);
            if ((limit != 0) && (amount < limit) && (coupon_amount != 0))
            {
                $("#coupon_amount").val(coupon_amount);
                $("#coupon_limit").val(limit);
                $("#loan_error_tip").html("金额满" + limit + "才可使用");
                return false;
            }
            else if ((limit != 0) && (amount < limit) && (coupon_amount == 0))
            {
                $("#coupon_amount").val(coupon_amount);
                $("#coupon_limit").val(limit);
                $("#loan_error_tip").html("金额满" + limit + "才可使用");
                return false;
            }
            else
            {
                $(".Hmask").hide();
                $(".layer").hide();
                if (coupon_amount != 0)
                {
                    var html = "优惠券" + coupon_amount + "元";
                }
                else
                {
                    var html = "全免";
                }
                $("#use_coupon").html(html).removeClass('col-xs-8 cor').addClass('col-xs-8 red');
                var loan_repay_amount = $("#loan_repay_amount").html();
                var days = $("#loan_days").val();
                var amount = $("#loan_amount").val();
                var rateStr = $("#day_rate").val();
//                var rateArr = eval('(' + rateStr + ')');
                var date_rate = rateStr;
//                if (rateArr[days] != undefined) {
//                    var date_rate = rateArr[days];
//                }
                $("#coupon_id").val(coupon_id);
                $("#coupon_amount").val(coupon_amount);
                $("#coupon_limit").val(limit);
                if ((days != ''))
                {
//通道费
                    if (coupon_amount == 0)
                    {
                        var repayVal = parseFloat(amount);
                        $('#loan_repay_amount').html(repayVal);
                    }
                    else
                    {
//判断优惠券的金额是否大于借款的服务费，如果优惠券的金额大于借款的服务费，则优惠券只能优惠服务费的金额，多余的金额作废
                        if (parseFloat(amount * date_rate * days) >= coupon_amount)
                        {
                            var repayVal = parseFloat(amount) + parseFloat(amount * date_rate * days) - coupon_amount;
                            $('#loan_repay_amount').html(repayVal);
                        }
                        else
                        {
                            var repayVal = parseFloat(amount);
                            $('#loan_repay_amount').html(repayVal);
                        }
                    }
                }
            }
        }
        else
        {
            $(".Hmask").hide();
            $(".layer").hide();
        }
    });
    //借款
    $("#loan_button").bind('click', function () {
        $(this).attr('disabled', true);
        var desc = $('#desc option:selected').val();
        var days = $('input[name="day"]').val();
        var amount = $('input[name="amount"]').val();
        var coupon_amount = $('input[name="coupon_limit"]').val();
        var canloan = $("#canloan").html();
        if(canloan != '1'){
            alert("今日借款已满，明天再来吧");
            return false;
        }
        if (amount == '') {
            $("#loan_error_tip").html("借款金额不符合要求，请重新填写");
            $("#loan_button").attr('disabled', false);
            return false;
        }
        if (days == '') {
            $("#loan_error_tip").html("借款期限不符合要求，请重新填写");
            $("#loan_button").attr('disabled', false);
            return false;
        }
        if (desc == '') {
            $("#loan_error_tip").html("借款用途不符合要求，请重新填写");
            $("#loan_button").attr('disabled', false);
            return false;
        } else {
            $("#loan_error_tip").html("");
        }

        if (coupon_amount != 0)
        {
            if (parseInt(amount) < parseInt(coupon_amount))
            {
                $("#loan_error_tip").html("借款金额过低，优惠券无法使用");
                $("#loan_button").attr('disabled', false);
                return false;
            }
        }
        $("#loan_form").submit();

    });
    //担保人借款
    $("#loan_buttons").bind('click', function () {
        var desc = $("#loan_des").val();
        var days = $("#loan_days").val();
        var amount = parseInt($("#loan_amounts").val());
        var user_id = $("#user_id").val();
        $.get("/dev/st/statisticssave", {type: 27, user_id: user_id}, function (data) {

        });

        var leastnum = 100;
        if (desc == '' || days == '' || amount == '') {
            $("#loan_error_tip").html("借款金额和条件不符合要求，请重新填写");
            return false;
        }
        $("#loan_desc").keyup();
        $("#loan_days").keyup();
        $("#loan_amounts").keyup();
        console.dir(amount);
        if (!flagdesc || !flagdays || !flagamount)
        {
            if (!flagdesc)
            {
                $("#loan_error_tip").html("请输入5~25个字符");
                return false;
            }
            if (!flagdays)
            {
                $("#loan_error_tip").html("请输入7~21天");
                return false;
            }
            if (!flagamount)
            {
                if (amount < 300 || amount > 1500) {
                    $("#loan_error_tip").html("请输入300~1500的整数");
                    return false;
                } else {
                    $("#loan_error_tip").html("金额只能是100的整数倍");
                    return false;
                }
            }
        }
        else
        {
            $("#loan_form").submit();
        }

    });
    $("#loan_refresh").click(function () {
        var loan_id = $(this).attr('t');
        if (loan_id != '') {
            $.post("/dev/loan/refresh", {loan_id: loan_id}, function (result) {
                if (result == 'success') {
                    window.location = "/dev/loan/succ?l=" + loan_id;
                }
            });
        }
    });
    //踩踩 无限踩
    $("loan_like_stat_buttons").click(function () {
        //alert(1111);
        var loan_id = $(this).attr('loan');
        var user_id = $(this).attr('login');
        //alert(user_id);
        $.post("/dev/share/loanlikestats", {loan_id: loan_id, user_id: user_id}, function (result) {
            var data = eval("(" + result + ")");
            if (data.ret == '1') {
                var num = parseInt($('#cai').val());
                $(this).find('img').attr('src', 'images/upRed.png');
                $(this).find('.hand_value').val(num + 1);
            }
        })
    });
    //点赞减息
    $("#loan_like_stat_button").click(function () {
        var loan_id = $(this).attr('loan');
        var user_id = $(this).attr('login');
        //alert(user_id);
        $.post("/dev/share/loanlikestat", {loan_id: loan_id, user_id: user_id}, function (result) {
            var data = eval("(" + result + ")");
            if (data.ret == '0') {
                var num = parseInt($('#dz').val());
                $('#limg').attr('src', '/images/upRed.png');
                $('#dz').val(num + 1);
                $('#lzh').html('<div class="txt_one"><p class="txt_one_one twotwo_one">谢大爷的</p><p class="txt_one_two twotwo_two">赞,帮我减免</p><p class="txt_one_three twotwo_three"><span> ' + data.amt + ' </span>元服务费</p></div>');

            } else if (data.ret == '1') {
                $('#lzh').html('<div class="txt_one"><p class="txt_one_one three_one">您已赞</p><p class="txt_one_two three_two">过了，可以</p><p class="txt_one_three three_three">踩我呀!</p></div>');

            } else if (data.ret == '12')
            {
                $('#lzh').html('<div class="txt_one"><p class="txt_one_one three_one">借款已</p><p class="txt_one_two three_two">还，不用</p><p class="txt_one_three three_three">点啦!</p></div>');
            } else if (data.ret == '13') {
                $('#lzh').html('<div class="txt_one"><p class="txt_one_one three_one">借款已</p><p class="txt_one_two three_two">逾期，点赞</p><p class="txt_one_three three_three">无效了哦！</p></div>');
            } else {
                $('#lzh').html('<div class="txt_one"><p class="txt_one_one one_one">赞满啦!</p><p class="txt_one_two one_two">可以踩</p><p class="txt_one_three one_three">我啊！</p></div>');

            }
        });
    });

    //筹款中送额度
    $("#loan_ing_stat_button").click(function () {
        var loan_id = $(this).attr('loan');
        var user_id = $(this).attr('login');

        $.post("/dev/share/loaningstat", {loan_id: loan_id, user_id: user_id}, function (result) {
            var data = eval("(" + result + ")");
            if (data.ret == '0') {
                $.Zebra_Dialog('给你一亿元信用额度，投资还享受收益，还不快抢？', {
                    'type': 'question',
                    'title': '帮他筹到了 ' + data.amt + ' 元',
                    'buttons': [
                        {caption: '继续帮忙', callback: function () {
                                window.location = "/dev/invest/detail?loan_id=" + loan_id + "&atten=1";
                            }},
                        {caption: '立刻去抢', callback: function () {
                                window.location = "/dev/account?atten=1";
                            }},
                    ]
                });
            } else if (data.ret == '1') {
                $.Zebra_Dialog('给你一亿元信用额度，投资还享受收益，还不快抢？', {
                    'type': 'question',
                    'title': '你已经帮过了',
                    'buttons': [
                        {caption: '继续帮忙', callback: function () {
                                window.location = "/dev/invest/detail?loan_id=" + loan_id + "&atten=1";
                            }},
                        {caption: '立刻去抢', callback: function () {
                                window.location = "/dev/account?atten=1";
                            }},
                    ]
                });
            } else if (data.ret == '4') {
                alert('您提交的信息不符合规则，该账户已被冻结');
                return false;
            } else {
                $.Zebra_Dialog('给你一亿元信用额度，投资还享受收益，还不快抢？', {
                    'type': 'question',
                    'title': '你来晚了，下次早点哦！',
                    'buttons': [
                        {caption: '再看看', callback: function () {
                                window.location = "/dev/invest?atten=1";
                            }},
                        {caption: '立刻去抢', callback: function () {
                                window.location = "/dev/account?atten=1";
                            }},
                    ]
                });
            }
        });
    });
    $('#verifyGuater').click(function () {
        $.get("/dev/st/statisticssave", {type: 22, user_id: user_id}, function (data) {
        });
        $.post("/dev/loan/verifys", '', function (result) {
            var data = eval("(" + result + ")");
            if (data.ret == 0) {
                window.location = data.url;
            } else if (data.ret == 3) {
                $('.Hmask').toggle();
                $('.yhj').toggle();
            } else {
                $.Zebra_Dialog(data.msg, {
                    'type': 'information',
                    'buttons': [
                        {caption: '确定', callback: function () {
                                if (data.url != '') {
                                    window.location = data.url;
                                } else {
                                    $('.ZebraDialogOverlay').toggle();
                                }
                            }},
                    ]
                });
                if (data.url != '') {
                    window.setTimeout("window.location='" + data.url + "'", 2000);
                }
            }
        });
    });
    //账户页优惠券管理 ====================================  
    $('.layer .item').click(function () {
        var node = $(this);
        var tp = node.attr('tp');
        var ids = node.attr('ids');
        $('.user_goon').attr('tp', tp);
        $('.layer .item').each(function () {
            var tp_child = $(this).attr('tp');
            if (tp_child == '2') {
                if (ids == $(this).attr('ids')) {
                    $(this).find('img').attr('src', '/images/choose.png');
                    $(this).find($('p.black')).addClass("white");
                    $(this).find($('p.green')).addClass("basise");
                    $('.user_goon').removeClass("change_gray");
                } else {
                    $(this).find('img').attr('src', '/images/unchoose.png');
                    $(this).find($('p.black')).removeClass("white");
                    $(this).find($('p.green ')).removeClass("basise");
                }
            } else {
                if (ids == $(this).attr('ids')) {
                    $(this).find('img').attr('src', '/images/choosered.png');
                    $(this).find($('p.black')).addClass("white");
                    $(this).find($('p.green')).addClass("basise");
                    $('.user_goon').removeClass("change_gray");
                } else {
                    $(this).find('img').attr('src', '/images/unchoosered.png');
                    $(this).find($('p.black')).removeClass("white");
                    $(this).find($('p.green ')).removeClass("basise");
                }
            }
        });
    });
    $('.user_goon').click(function () {
        var tp = $(this).attr('tp');
        if (tp == '2') {
            window.location = '/dev/invest';
        } else if (tp == '1') {
            window.location = '/new/loan';
        }
    });
    //收益提现=======================================
    $('#incomewd').click(function () {
        var outincome = $("#outincome").val();
        var outbig = $(this).attr("obig");
        var outstatus = $(this).attr("obst");
        var user_id = $("#userId").val();
        var bank_id = $("#outbank").val();
        if (outincome == null || outincome == '') {
            $(".sytx_txt").text("请输入要提现的额度");
            return false;
        } else if (outincome < 10) {
            $(".sytx_txt").text("当收益满10.00点后，即可提现！");
            return false;
        } else if (parseFloat(outincome) > parseFloat(outbig)) {
            $(".sytx_txt").text("最多可提现" + outbig);
            return false;
        }
        if (!bank_id) {
            $(".sytx_txt").text("请添加银行卡");
            return false;
        }
        if (outstatus == '1' || outstatus == '3') {
            $(".title_cz").html("申请提现失败");
            $(".bankinfotip").html("由于您的征信记录有瑕疵，暂不可申请提现；");
            show();
            return false;
        } else if (outstatus == '2') {
            $(".title_cz").html("申请提现失败");
            $(".bankinfotip").html("由于您当前有逾期借款，暂不可申请提现；");
            show();
            return false;
        } else if (outstatus == '4') {
            $(".title_cz").html("申请提现失败");
            $(".bankinfotip").html("受春节期间（2月5日－2月15日）银行系统影响，收益提现功能暂停服务，敬请谅解；");
            show();
            return false;
        }
        $(this).attr('disabled', true);
        $.post("/dev/account/outincome", {user_id: user_id, outincome: outincome, bank_id: bank_id}, function (result) {
            var data = eval("(" + result + ")");
            $("#incomewd").attr('disabled', false);
            if (data.ret == 0) {
                show();
            } else {
                $(".sytx_txt").text(data.msg);
            }
        });
//    	$.Zebra_Dialog("确定要提现"+outincome+"收益吗？", {
//            'type': 'information',
//            'buttons': [
//						{caption: '取消', callback: function () {}},
//						{caption: '确定', callback: function () {
//							$.post("/dev/account/outincome", {user_id:user_id, outincome:outincome,bank_id:bank_id}, function (result) {
//					            var data = eval("(" + result + ")");
//					            if (data.ret == 0) {
//					            	show();
//					            } else {
//					            	$(".sytx_txt").text(data.msg);
//					            }
//					        });
//						}},
//            ]
//        });

    });
    //我要退卡=================================================
    $('#backcardGetcode').click(function () {
        var lt = $(this).attr('lt');
        var remainAmount = $(this).attr('ra');
        if (lt == '1') {
            show();
            return false;
        }

        var mobile = $(this).attr('mb');
        $("#backcardGetcode").attr('disabled', true);
        $.post("/dev/guarantee/backcardsms", {mobile: mobile, remain_mount: remainAmount}, function (result) {
            var data = eval("(" + result + ")");
            if (data.ret == '0') {
                //发送成功
                count = 60;
                countdown = setInterval(backCountDown, 1000);
            } else if (data.ret == '2') {
                $.Zebra_Dialog('该手机号码已超出每日短信最大获取次数', {
                    'type': 'question',
                    'title': '退卡',
                    'buttons': [
//				                    {caption: '取消', callback: function() {}},
                        {caption: '确定', callback: function () {
                            }},
                    ]
                });
                $("#backcardGetcode").attr('disabled', false);
                return false;
            }
            else {
                $.Zebra_Dialog('手机号有误，请联系客服！', {
                    'type': 'question',
                    'title': '退卡',
                    'buttons': [
//				                    {caption: '取消', callback: function() {}},
                        {caption: '确定', callback: function () {
                            }},
                    ]
                });
                $("#backcardGetcode").attr('disabled', false);
            }
        });

    });
    //退卡
    $("#backcard").click(function () {
        var lt = $(this).attr('lt');
        var code = $("#bccode").val();
        var coid = $("#coid").val();

        if (lt == '4') {
            $("#txt14_one").html('受春节期间（2月5日－2月15日）银行系统影响，担保卡退卡功能暂停服务，敬请谅解');
            $("#txt14_second").html('');
            show();
            return false;
        }

        if (code == '' || !(_numberRex.test(code))) {
            $.Zebra_Dialog('请输入验证码', {
                'type': 'question',
                'title': '退卡',
                'buttons': [
//			                    {caption: '取消', callback: function() {}},
                    {caption: '确定', callback: function () {
                        }},
                ]
            });
            return false;
        }
        if (lt == '1') {
            show();
            return false;
        }

        $("#backcard").attr('disabled', true);
        $.post("/dev/guarantee/backcardconfirm", {code: code, coid: coid}, function (result) {
            var data = eval("(" + result + ")");
            if (data.ret == '0') {
                window.location = "/dev/guarantee/backcardret?ret=success&coid=" + coid;
            } else if (data.ret == '1') {
                $.Zebra_Dialog('验证码错误', {
                    'type': 'question',
                    'title': '退卡',
                    'buttons': [
//				                    {caption: '取消', callback: function() {}},
                        {caption: '确定', callback: function () {
                            }},
                    ]
                });
                $("#backcard").attr('disabled', false);
                return false;
            } else if (data.ret == '2') {
                $.Zebra_Dialog(data.msg, {
                    'type': 'question',
                    'title': '退卡',
                    'buttons': [
//				                    {caption: '取消', callback: function() {}},
                        {caption: '确定', callback: function () {
                            }},
                    ]
                });
                $("#backcard").attr('disabled', false);
                return false;
            } else if (data.ret == '5') {
                show();
                return false;
            } else {
                window.location = "/dev/guarantee/backcardret?ret=fail";
            }
        });
    });
});
var countHour = function () {
    $("#count_hour").html(count);
    if (count == 0) {
        clearInterval(counthour);
    }
    count--;
};
//倒计时
var backCountDown = function () {
    $("#backcardGetcode").attr("disabled", true);
    $("#backcardGetcode").html("重新获取 ( " + count + " ) ");
    if (count <= 0) {
        $("#backcardGetcode").html("获取验证码");
        $("#backcardGetcode").attr('disabled', false);
        clearInterval(countdown);
    }
    count--;
};