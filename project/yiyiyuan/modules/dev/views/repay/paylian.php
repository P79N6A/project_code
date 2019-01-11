<style type="text/css">
    input[type='text'], input[type='password']{
        font-size: 12px;
    }
    .stars{
        color: red;
    }
</style>

<div style="font-size: 12px; margin: 10px 15px;">
    <form id="pay" method="post" action="/dev/repay/subpay" onsubmit="return sub()">
        <table style="width: 350px;" id="tbs">
            <tr>
                <td style="width: 30%"><span class="stars">*</span>请求业务类型</td>
                <td>虚拟&nbsp;<input type="radio" name="merchant_type" checked="checked" value="virtual"/> &nbsp;互联网还款&nbsp;<input type="radio" name="merchant_type" value="entity"/></td>
            </tr>
            <tr>
                <td style=""><span class="stars">*</span>请求源</td>
                <td>
                    wap&nbsp;<input type="radio" name="app_request" checked="checked" value="1"/>&nbsp;
                    Android&nbsp;<input type="radio" name="app_request" value="2"/>&nbsp;
                    ios&nbsp;<input type="radio" name="app_request" value="3"/>&nbsp;
                </td>
            </tr>
            <tr>
                <td style=""><span class="stars">*</span>订单号</td>
                <td><input type="text" name="no_order" value="<?php echo $no_order; ?>" style="height: 30px; width: 150px; padding: 0"/></td>
            </tr>
            <tr>
                <td style=""><span class="stars">*</span>商品名称</td>
                <td><input type="text" name="name_goods" value="goods_name" style="height: 30px; width: 150px; padding: 0;"/></td>
            </tr>
            <tr>
                <td style=""><span class="stars">*</span>交易金额</td>
                <td><input type="text" name="money_order" value="0.01" style="height: 30px; width: 150px; padding: 0;" /></td>
            </tr>
            <tr>
                <td style=""><span class="stars">*</span>支付方式</td>
                <td>储蓄卡&nbsp;<input type="radio" name="pay_type" onchange="add()" checked="checked" value="2"/>&nbsp;
                    信用卡&nbsp;<input type="radio" name="pay_type" value="3" onchange="add()"/>&nbsp;</td>
            </tr>
            <tr>
                <td><span class="stars">*</span>预留手机号</td>
                <td><input type="text" name="bind_mob" value="18911240687" style="height: 30px; width: 150px; padding: 0;" /></td>
            </tr>
            <tr>
                <td><span class="stars">*</span>证件号码</td>
                <td><input type="text" name="id_no" value="130121199012130214" style="height: 30px; width: 150px; padding: 0;" /></td>
            </tr>
            <tr>
                <td><span class="stars">*</span>姓名</td>
                <td><input type="text" name="acct_name" value="刘哲辉" style="height: 30px; width: 150px; padding: 0;" /></td>
            </tr>
            <tr>
                <td><span class="stars">*</span>银行卡号</td>
                <td><input type="text" name="card_no" value="130121199012130214" style="height: 30px; width: 150px; padding: 0;" /></td>
            </tr>
            <tr>
                <td>商户业务类型</td>
                <td>
                    虚拟商品&nbsp;<input type="radio" name="busi_partner" checked="checked" value="101001"/>&nbsp;
                    实物商品&nbsp;<input type="radio" name="busi_partner" value="109001"/>&nbsp;
                    账户充值&nbsp;<input type="radio" name="busi_partner" value="108001"/>&nbsp;
                </td>
            </tr>
            <tr>
                <td>订单有效时间</td>
                <td><input type="text" name="valid_order" value="10" style="height: 30px; width: 150px; padding: 0;" /></td>
            </tr>
            <tr>
                <td>修改标记</td>
                <td>
                    可以&nbsp;<input type="radio" name="flag_modify" checked="checked" value="0"/>&nbsp;
                    不可以&nbsp;<input type="radio" name="flag_modify" value="1"/>&nbsp;
                </td>
            </tr>
        </table>
        <input type="hidden" name="pay_key">
        <input type="submit" value="提交"><div id="respone"></div>

    </form>

</div>
<script>
    function add() {
        if ($("input[name='pay_type']:checked").val() == 3) {
            $("#tbs").append("<tr class='pay_type'><td><span class='stars'>*</span>卡背面后3位</td><td><input type=\"text\" name=\"cvv2\" style=\"height: 30px; width: 150px; padding: 0;\" /></td></tr>");
            $("#tbs").append("<tr class='pay_type'><td><span class='stars'>*</span>信用卡有效期</td><td><input type=\"text\" name=\"validate\" style=\"height: 30px; width: 150px; padding: 0;\" /></td></tr>");
        } else if ($("input[name='pay_type']:checked").val() == 2) {
            $(".pay_type").remove();
        }
    }
    function sub() {
        var mark = false;
        $.ajax({
            type: "POST",
            dataType: "json",
            //url:"bs",
            url: "/dev/repay/payment",
            data: $('#pay').serialize(), // 你的formid
            async: false,
            error: function(data) {
                alert('请求失败！');
            },
            success: function(data) {
                if (data.rsp_code == '0000') {
                    $('#respone').html('<span style="color:red;">' + data.rsp_msg + '</sapn>');
                    $('input[name="pay_key"]').val(data.pay_key); 
                    mark =true;
                } else {
                    $('#respone').html('<span style="color:red;">' + data.rsp_msg + '</sapn>');
                }
            }
        });
        return mark;
    }    
</script>