<div class="banner">
    <img src="/images/coupon/bannerym.jpg" width="100%" />
</div>
<div class="main">
    <ul>
        <li class="jk_item">
            <form class="form-horizontal" method="post" action="/new/coupon/personal">
                <div class="p_ipt">
                    <div class="colxs3">期限金额：</div>
                    <div class="colxs4 magrht" id="amount_500">500元
                        <img src="/images/coupon/imgimg.png">
                    </div>
                    <div class="colxs4 photodw" id="amount_1000">1000元
                        <img src="/images/coupon/imgimg.png">
                    </div>
                </div>
                <input type="hidden" name="amount" value="1000">

                <div class="p_ipt">
                    <div class="colxs3">借款周期：</div>
                    <div class="colxs2" id="days_7">7天
                        <img src="/images/coupon/imgimg.png" style="display: none;">
                    </div>
                    <div class="colxs2" id="days_14">14天
                        <img src="/images/coupon/imgimg.png" style="display: none;">
                    </div>
                    <div class="colxs2 " id="days_21">21天
                        <img src="/images/coupon/imgimg.png" style="display: none;">
                    </div>
                    <div class="colxs2 photodw" id="days_28">28天
                        <img src="/images/coupon/imgimg.png">
                    </div>
                </div>
                <input type="hidden" name="day" value="28">
                <input type="hidden" name="day_rate" id="day_rate" value='<?php echo $dayratestr; ?>'>
                <div class="p_ipt">
                    <div class="colxs3">借款理由：</div>
                    <div class="colxs9">
                        <select class="seltxt" name="desc">
                            <div>
                                <?php foreach ($desc as $key => $val): ?>
                                    <option><?php echo $val[0]; ?></option>
                                <?php endforeach; ?>
                            </div>
                        </select>
                    </div>
                </div>
                <input type="hidden" name="coupon_id" id="coupon_id" value="<?php echo $couponlist['id']; ?>">
                <input type="hidden" name="coupon_amount" id="coupon_amount" value="<?php echo $couponlist['val']; ?>">
                <input type="hidden" name="coupon_limit" id="coupon_limit" value="<?php echo $couponlist['limit']; ?>">
                <input type="hidden" name="mobile" id="coupon_limit" value="<?php echo $mobile; ?>">
                <div class="p_ipt highlight">
                    <div class="colxs3">优惠券：</div>
                    <div class="colxs9 cor imgtuzi" style="color:#e74747;">
                        <?php echo $couponlist['title']; ?>
                    </div>
                </div>
                <p class="reder"></p>
                <p class="teright">到期应还款：<label id="loan_repay_amount">9999.00</label>元</p>
                <button type="submit" class="bgrey btn mt20">确定</button>

            </form>
        </li>
    </ul>
</div>
<script>
    $('.colxs2').click(function () {
        var id = this.id;
        $('.colxs2').each(function () {
            $('#' + this.id).removeClass('photodw');
            $('#' + this.id + '>img').css('display', 'none');
        });
        $('#' + id).addClass('photodw');
        $('#' + id + '>img').css('display', 'block');
        var day = id.split("_")[1];
        $('input[name="day"]').val(day);
        getRepay();
    });

    $('.colxs4').click(function () {
        var id = this.id;
        $('.colxs4').each(function () {
            $('#' + this.id).removeClass('photodw');
//            $('#' + this.id).removeClass('imgimg');
            $('#' + this.id + '>img').css('display', 'none');
        });
        var amount = id.split("_")[1];
        if (amount == 1000) {
            $('#' + id).addClass('photodw');
        } else {
            $('#' + id).addClass('photodw magrht');
        }
        $('#' + id + '>img').css('display', 'block');
        $('input[name="amount"]').val(amount);
        getRepay();
    });

    var getRepay = function () {
        var days = $('input[name="day"]').val();
        var amount = $('input[name="amount"]').val();
        var rateStr = $('input[name="day_rate"]').val();
        var coupon_amount = $("#coupon_amount").val();
        if (coupon_amount != '' && coupon_amount == 0) {
            $("#loan_error_tip").html("");
            $('#mon_col').css('color', '#444444');
            $('#loan_repay_amount').html(parseFloat(amount));
            flagamount = true;
        } else {
            $("#loan_error_tip").html("");
            $('#mon_col').css('color', '#444444');
            if (coupon_amount == '') {
                var repayVal = parseFloat(amount) + parseFloat(amount * rateStr * days);
            } else {
                //判断优惠券的金额是否大于借款的服务费，如果优惠券的金额大于借款的服务费，则优惠券只能优惠服务费的金额，多余的金额作废
                if (parseFloat(amount * rateStr * days) >= coupon_amount) {
                    var repayVal = parseFloat(amount) + parseFloat(amount * rateStr * days) - coupon_amount;
                } else {
                    var repayVal = parseFloat(amount);
                }
            }
            $('#loan_repay_amount').html(repayVal);
            flagamount = true;
        }
    };

    getRepay();
</script>