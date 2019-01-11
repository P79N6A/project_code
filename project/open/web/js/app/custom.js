// JavaScript Document
var count, counthour;
var _mobileRex = /^(1(([3578][0-9])|(47)))\d{8}$/;
var _numberRex = /^[0-9]*[1-9][0-9]*$/;
$(function() {
    var flagdesc = flagdays = flagamount = false;
    $('#loan_desc').keyup(function() {
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
    $("#loan_days").bind('keyup', function() {
        var days = $("#loan_days").val();
        var amount = $("#loan_amount").val();
        var date_rate = $("#day_rate").val();
        var coupon_amount = $("#coupon_amount").val();
        if (days == '' || !(_numberRex.test(days))) {

            $("#loan_error_tip").html("请输入7~21天");
            $('#day_col').css('color', '#e74747');
            flagdays = false;
        } else {
            days = parseInt(days);
            if (days < 7 || days > 21) {
                $("#loan_error_tip").html("请输入7~21天");
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
                    //通道费
                    var withdraw_amount = parseFloat(amount * 0.01);
                    if (withdraw_amount < 5)
                    {
                        withdraw_amount = 5;
                    }
                    var coupon_amount = $("#coupon_amount").val();
                    //计算收益
                    if (coupon_amount == '')
                    {
                        var repayVal = parseFloat(amount) + parseFloat(amount * date_rate * days) + withdraw_amount;
                    }
                    else
                    {
                        //判断优惠券的金额是否大于借款的服务费，如果优惠券的金额大于借款的服务费，则优惠券只能优惠服务费的金额，多余的金额作废
                        if ((parseFloat(amount * date_rate * days) + withdraw_amount) >= coupon_amount)
                        {
                            var repayVal = parseFloat(amount) + parseFloat(amount * date_rate * days) + withdraw_amount - coupon_amount;
                        }
                        else
                        {
                            var repayVal = parseFloat(amount);
                        }
                    }
                    $('#loan_repay_amount').html(repayVal);
                    flagdays = true;
                }
            }
            return flagdays;
        }

    });
    $("#loan_amount").bind('keyup', function() {
        var days = $("#loan_days").val();
        var amount = $("#loan_amount").val();
        var date_rate = $("#day_rate").val();
        var coupon_amount = $("#coupon_amount").val();
        var leastnum = 100;
        if (amount == '' || !(_numberRex.test(amount))) {

            $("#loan_error_tip").html("请输入300~5000的整数");
            $('#mon_col').css('color', '#e74747');
            flagamount = false;
        } else {
//			$("#loan_error_tip").html("");
//			flagamount = true;
            amount = parseInt(amount);
            if (amount < 300 || amount > 5000) {
                $("#loan_error_tip").html("请输入300~5000的整数");
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
                        //通道费
                        var withdraw_amount = parseFloat(amount * 0.01);
                        if (withdraw_amount < 5)
                        {
                            withdraw_amount = 5;
                        }
                        var coupon_amount = $("#coupon_amount").val();
                        //计算收益
                        if (coupon_amount == '')
                        {
                            var repayVal = parseFloat(amount) + parseFloat(amount * date_rate * days) + withdraw_amount;
                        }
                        else
                        {
                            //判断优惠券的金额是否大于借款的服务费，如果优惠券的金额大于借款的服务费，则优惠券只能优惠服务费的金额，多余的金额作废
                            if ((parseFloat(amount * date_rate * days) + withdraw_amount) >= coupon_amount)
                            {
                                var repayVal = parseFloat(amount) + parseFloat(amount * date_rate * days) + withdraw_amount - coupon_amount;
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


    $(".close").click(function() {
        $(".Hmask").hide();
        $(".layer").hide();
    });

    $("#use_rules").click(function() {
        $(".Hmask").css('display', 'block');
        $(".layer").css('display', 'block');
    });

    $("#account_coupon_i_know").click(function() {
        $(".Hmask").hide();
        $(".layer").hide();
    });

    //提现
    $("#withdraw_button").click(function() {
        var coupon = $("#coupon_exist").val();
        var loan_id = $("#loan_id").val();
        if (coupon == 'yes')
        {
            $(".Hmask").css('display', 'block');
            $("#coupon_error").css('display', 'block');
        }
        else
        {
            window.location = "/app/loan/conwd?l=" + loan_id;
        }
    });

    $("#withdraw_error_button").click(function() {
        $(".Hmask").hide();
        $("#coupon_error").hide();
        var loan_id = $("#loan_id").val();
        window.location = "/app/loan/conwd?l=" + loan_id;
    });

    //使用优惠券
    $("#use_loan_coupon").click(function() {
        var coupon_amount = $('input:radio:checked').val();
        var coupon_id = $('input:radio:checked').attr('cid');
        if(coupon_amount == undefined || coupon_id == undefined)
    	{
            $(".Hmask").hide();
            $(".layer").hide();
            return false;
    	}
        var amount = $("#loan_amount").val();
        var date_rate = $("#day_rate").val();
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
                        var withdraw_amount = parseFloat(amount * 0.01);
                        if (withdraw_amount < 5)
                        {
                            withdraw_amount = 5;
                        }
                        //判断优惠券的金额是否大于借款的服务费，如果优惠券的金额大于借款的服务费，则优惠券只能优惠服务费的金额，多余的金额作废
                        if ((parseFloat(amount * date_rate * days) + withdraw_amount) >= coupon_amount)
                        {
                            var repayVal = parseFloat(amount) + parseFloat(amount * date_rate * days) + withdraw_amount - coupon_amount;
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
    $("#loan_button").bind('click', function() {
        var desc = $("#loan_des").val();
        var days = $("#loan_days").val();
        var amount = $("#loan_amount").val();
        var coupon_amount = $("#coupon_limit").val();
        var leastnum = 100;
        if (desc == '' || days == '' || amount == '') {
            $("#loan_error_tip").html("借款金额不符合要求，请重新填写");
            if (desc == '') {
                $('#desc_col').css('color', '#e74747');
            }
            if (days == '') {
                $('#day_col').css('color', '#e74747');
            }
            if (amount == '') {
                $('#mon_col').css('color', '#e74747');
            }
            return false;
        }
        $("#loan_desc").keyup();
        $("#loan_days").keyup();
        $("#loan_amount").keyup();
        if (!flagdesc || !flagdays || !flagamount)
        {
            if (!flagdesc)
            {
                $("#loan_error_tip").html("请输入5~25个字符");
                $('#desc_col').css('color', '#e74747');
                return false;
            }
            if (!flagdays)
            {
                $("#loan_error_tip").html("请输入7~21天");
                $('#day_col').css('color', '#e74747');
                return false;
            }
            if (!flagamount)
            {
                if (amount < 300 || amount > 5000) {
                    $("#loan_error_tip").html("请输入300~5000的整数");
                    $('#mon_col').css('color', '#e74747');
                    return false;
                } else {
                    $("#loan_error_tip").html("金额只能是100的整数倍");
                    $('#mon_col').css('color', '#e74747');
                    return false;
                }
            }
        }
        else
        {
            if (coupon_amount != 0)
            {
                if (parseInt(amount) < parseInt(coupon_amount))
                {
                    $("#loan_error_tip").html("借款金额过低，优惠券无法使用");
                    return false;
                }
            }
            $("#loan_form").submit();
        }

    });
    $("#loan_refresh").click(function() {
        var loan_id = $(this).attr('t');
        if (loan_id != '') {
            $.post("/app/loan/refresh", {loan_id: loan_id}, function(result) {
                window.location = "/app/loan/succ?l=" + loan_id;
            });
        }
    });
    //点赞减息
    $("#loan_like_stat_button").click(function() {
        var loan_id = $(this).attr('loan');
        var user_id = $(this).attr('login');
        $.post("/app/share/loanlikestat", {loan_id: loan_id, user_id: user_id}, function(result) {
            var data = eval("(" + result + ")");
            if (data.ret == '0') {
                $.Zebra_Dialog('给你一亿元信用额度，投资还享受收益，还不快抢？', {
                    'type': 'question',
                    'title': '帮他减了 ' + data.amt + ' 元',
                    'buttons': [
                        {caption: '就帮到这了', callback: function() {
                            }},
                        {caption: '立刻去抢', callback: function() {
                                window.location = "/app/account?atten=1";
                            }},
                    ]
                });
            } else if (data.ret == '1') {
                $.Zebra_Dialog('给你一亿元信用额度，投资还享受收益，还不快抢？', {
                    'type': 'question',
                    'title': '你已经帮过了',
                    'buttons': [
                        {caption: '立刻去抢', callback: function() {
                                window.location = "/app/account?atten=1";
                            }},
                    ]
                });
            } else if (data.ret == '4')
            {
                alert('您提交的信息不符合规则，该账户已被冻结');
                return false;
            }
            else {
                $.Zebra_Dialog('给你一亿元信用额度，投资还享受收益，还不快抢？', {
                    'type': 'question',
                    'title': '你来晚了，下次早点哦!',
                    'buttons': [
                        {caption: '立刻去抢', callback: function() {
                                window.location = "/app/account?atten=1";
                            }},
                    ]
                });
            }
        });
    });
    //筹款中送额度
    $("#loan_ing_stat_button").click(function() {
        var loan_id = $(this).attr('loan');
        var user_id = $(this).attr('login');
//		alert('adf');
        $.post("/app/share/loaningstat", {loan_id: loan_id, user_id: user_id}, function(result) {
            var data = eval("(" + result + ")");
            if (data.ret == '0') {
                $.Zebra_Dialog('给你一亿元信用额度，投资还享受收益，还不快抢？', {
                    'type': 'question',
                    'title': '帮他筹到了 ' + data.amt + ' 元',
                    'buttons': [
                        {caption: '继续帮忙', callback: function() {
                                window.location = "/app/invest/detail?loan_id=" + loan_id + "&atten=1";
                            }},
                        {caption: '立刻去抢', callback: function() {
                                window.location = "/app/account?atten=1";
                            }},
                    ]
                });
            } else if (data.ret == '1') {
                $.Zebra_Dialog('给你一亿元信用额度，投资还享受收益，还不快抢？', {
                    'type': 'question',
                    'title': '你已经帮过了',
                    'buttons': [
                        {caption: '继续帮忙', callback: function() {
                                window.location = "/app/invest/detail?loan_id=" + loan_id + "&atten=1";
                            }},
                        {caption: '立刻去抢', callback: function() {
                                window.location = "/app/account?atten=1";
                            }},
                    ]
                });
            } else if (data.ret == '4') {
                alert('您提交的信息不符合规则，该账户已被冻结');
                return false;
            }
            else {
                $.Zebra_Dialog('给你一亿元信用额度，投资还享受收益，还不快抢？', {
                    'type': 'question',
                    'title': '你来晚了，下次早点哦！',
                    'buttons': [
                        {caption: '再看看', callback: function() {
                                window.location = "/app/invest?atten=1";
                            }},
                        {caption: '立刻去抢', callback: function() {
                                window.location = "/app/account?atten=1";
                            }},
                    ]
                });
            }
        });
    });
});
var countHour = function() {
    $("#count_hour").html(count);
    if (count == 0) {
        clearInterval(counthour);
    }
    count--;
}
