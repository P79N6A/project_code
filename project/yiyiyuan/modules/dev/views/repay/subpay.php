<?php

use yii\helpers\Html;
?>
<style type="text/css">
    input[type='text'], input[type='password']{
        font-size: 12px;
    }
</style>

<div style="font-size: 12px; margin: 10px 15px;">

    <table style="width: 350px; padding:5px;" id="tbs">  
        <form id="pay" method="post">
        <tr>
            <td style="text-align: right;">请求源:</td>
            <td>
                &nbsp;<?= ($posts['app_request']=='1')?('wap'):($posts['app_request']=='2'?'Android':'ios'); ?>
                <input type="hidden" name="app_request" value="<?= $posts['app_request'];?>"/>
            </td>
        </tr>
        <tr>
            <td style="width: 30%;text-align: right;">请求业务类型:</td>
            <td>
                &nbsp;<?= $posts['merchant_type']=='virtual'?'虚拟':'互联网还款';?>
                <input type="hidden" name="merchant_type" value="<?= $posts['merchant_type'];?>"/>
            </td>
        </tr>
        <tr>
            <td style="width: 30%;text-align: right;">银行卡预留手机:</td>
            <td>
                &nbsp;<?= $posts['bind_mob'];?>
                <input type="hidden" name="bind_mob" value="<?= $posts['bind_mob'];?>"/>
            </td>
        </tr>
            <tr>
                <td style="text-align: right;">验证码:</td>
                <td>&nbsp;<input type="text" name="verifyCode"  style="height: 30px; width: 150px; padding: 0;" /></td>
            </tr>
            <tr>
                <td style="width: 30%;text-align: right;">是否绑定:</td>
                <td>&nbsp;绑定&nbsp;<input type="radio" name="isrecord" checked="checked" value="yes"/>&nbsp;
                    不绑定&nbsp;<input type="radio" name="isrecord" value="no"/>&nbsp;</td>
            </tr>
            <tr>
                <input type="hidden" name="pay_key" value="<?= $posts['pay_key'];?>" />
                <td style="text-align: right;"><input value="支付" type="submit" onclick="return pay()"/></td>
                <td><div id="respone"></div></td>
            </tr>            
        </form>
    </table>    
</div>
<script>
    function pay() {
        $.ajax({
            type: "POST",
            dataType: "json",
            //url:"bs",
            url: "/dev/repay/pay",
            data: $('#pay').serialize(), // 你的formid
            async: false,
            error: function(data) {
                alert('支付失败');
            },
            success: function(data) {
                if(data.rsp_code=='0000'){
                    $('input[name="pay_key"]').val(data.pay_key);
                }else{
                    $('#respone').html('<span style="color:red;">'+data.rsp_msg+'</sapn>');
                }
            }
        });
        return false;
    }
</script>