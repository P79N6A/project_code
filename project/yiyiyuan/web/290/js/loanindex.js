var loanChange = function (business_type) {
    window.location.href = '?business_type=' + business_type;
}
var amountChange = function (amount, trem, count) {
    var arr = eval("(" + arr_amount + ")");
    var day = trem == 1 ? 56 : 56;
    var mark = 0;
    $.each(arr, function (index) {
        var j = 0;
        if (index == amount) {
            $.each($(this), function (ii) {
                if ($(this)[0].enabled == 1) {
                    initialSlide2 = ii;
                    $(".new_shhu1").removeClass("addstyle_amount");
                    $(".new_shhu2").removeClass("addstyle_amount");
                    $(".new_shhu2").removeClass("addstyle_days");
                    $(dayName).addClass("addstyle_days");
                    $(amountBusName).addClass('addstyle_amount');
                    day = $(this)[0].days;
                    j = 1;
                    mark = 1;
                    return false;
                }
            })
        } else {
            initialSlide2 = 0;
        }
        if (j == 1) {
            return false;
        }
    })
    if (mark == 0) {
        //置灰提交按钮
        $(".fqi_jkuan").attr('disabled', true);
        $(".fqi_jkuan").css('background', '#BBBABA');
    } else if (mark == 1) {
        $(".fqi_jkuan").attr('disabled', false);
        $(".fqi_jkuan").css('background', '#C90000');
    }
    $("#amount").val(amount);
    var amountBusName = ".new_shhu_" + amount;
    var amountsShow = ".amounts_" + amount;
    var dayName = ".cl" + day;
    $('.swiper-container3').hide();
    $(".new_shhu1").removeClass("addstyle_amount");
    $(".new_shhu2").removeClass("addstyle_amount");
    $(".new_shhu2").removeClass("addstyle_days");
    $(dayName).addClass("addstyle_days");
    $(amountBusName).addClass('addstyle_amount');
    $(amountsShow).show();
    dayTremChange(amount, day, trem)
    if (swiper3) {
        for (var i = 0; i < count; i++) {
            swiper3[i].slideTo(initialSlide2, 0, false);
        }
    }
}
var goodsChange = function (goods_id, business_type) {
    $(".user_conte").css({"border-color": "#e1e1e1", "border-width": "1px", "border-style": "solid"})
    var calssName = ".goods_" + goods_id + "_" + business_type;
    $(calssName).css({"border-color": "#CA0000", "border-width": "1px", "border-style": "solid"});
    $("#goods_id").val(goods_id);
}

var dayTremChange = function (amount, days, trem) {
    var arr = eval("(" + arr_amount + ")");
    var mark = 0;
    $.each(arr, function (index) {
        if (index == amount) {
            $.each($(this), function (ii) {
                if (this.enabled == 1 && this.days == days) {
                    $("#days").val(days);
                    $("#trem").val(trem);
                    mark = 1
                }
            })
        }
    })

    if (mark == 0) {
        //置灰提交按钮
        $(".fqi_jkuan").attr('disabled', true);
        $(".fqi_jkuan").css('background', '#BBBABA');
    } else if (mark == 1) {
        $(".fqi_jkuan").attr('disabled', false);
        $(".fqi_jkuan").css('background', '#C90000');
    }
    if (eval(trem) > 1) {
        $(".fqi_jkuan").attr({onClick: "location.href='http://a.app.qq.com/o/simple.jsp?pkgname=com.xianhuahua.yiyiyuan_1'", type: "button"}).html("下载APP,发起分期借款");
    } else {
        $(".fqi_jkuan").removeAttr("onClick");
        $(".fqi_jkuan").removeAttr("type").html("发起借款");
    }

}

$(function () {
    $(".fqi_jkuan").bind('click', function () {
        var canloan = $("#canloan").html();
        if (canloan != '1') {
            alert("今日借款已满，明天再来吧");
            return false;
        }
        var amount = $('input[name="amount"]').val();
        var csrf = $('input[name="_csrf"]').val();
        var mark = false;
        $.ajax({
            url: "/new/loan/getcanloan",
            type: 'post',
            async: false,
            data: {amount: amount, _csrf: csrf},
            success: function (json) {
                json = eval('(' + json + ')');
                if (json.rsp_code == '0000') {
                    mark = true;
                } else {
                    alert(json.rsp_msg);
                }
            },
            error: function (json) {
                alert('请十分钟后发起借款');
            }
        });
        return mark;
    })
    //点击蒙层
    $('.iknow , .Hmask').on('click', function () {
        $(".Hmask").hide();
        if ($('.duihsucc_new').is(":visible")) {
            $('.duihsucc_new').hide();
        }
        ;
        if ($('.iknow').is(":visible")) {
            $('.iknow').hide();
        }
        ;
        //借款驳回弹框
        if ($('.bohomeg').is(":visible")) {
            $('.bohomeg').hide();
        }
    });
    $('.guide').bind('click', function () {
        var guideUrl = $('.guide_url').val();
        $.get("/wap/st/statisticssave", {type: 99}, function () {
            window.location = guideUrl;
            return false;
        })
    })
    $('.down').bind('click', function () {
        $.get("/wap/st/statisticssave", {type: 1010, source: 'newdev'}, function () {
            window.location = 'http://a.app.qq.com/o/simple.jsp?pkgname=com.xianhuahua.yiyiyuan_1';
            return false;
        })
    });
    $(document).ready(function () {
        //好友借款、担保借款切换
        $('.nav_jk .item').each(function (index) {
            $(this).click(function () {
                $('.nav_jk .item').removeClass('on');
                $(this).addClass('on');
                $('.jk_item').removeClass('on');
                $('.jk_item').eq(index).addClass('on');
            });
        });
        //商品切换
        $(".user_conte").click(function () {
            $(this).css('border', '1px solid #c90000').siblings().css('border', '1px solid #e1e1e1');
        });
        //金额切换
        $(".new_shhu1").click(function () {
            $(this).addClass('addstyle_amount').siblings().removeClass('addstyle_amount');
        });
        //周期切换
        $(".new_shhu2").click(function () {
            $(this).addClass('addstyle_days').siblings().removeClass('addstyle_days');
        });
        $("#demo12").click(function () {
            $(".user_loan").hide();
            $(this).hide();
            $("#demo11").show();
        });
        $("#demo11").click(function () {
            $(".user_loan").show();
            $(this).hide();
            $("#demo12").show();
        });
    })
})

