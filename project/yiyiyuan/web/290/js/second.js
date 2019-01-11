    /**
     * 选择优惠卷获取数据
     * @param url
     */
    var changeCoupon = function (couponId) {
        window.location.href = '?coupon_id='+couponId;
    };
    $(document).ready(function () {
        $("#demo12").click(function () {
            $(".dis").hide();
            $(this).hide();
            $("#demo11").show();
        });
        $("#demo11").click(function () {
            $(".dis").show();
            $(this).hide();
            $("#demo12").show();
        });
    })
