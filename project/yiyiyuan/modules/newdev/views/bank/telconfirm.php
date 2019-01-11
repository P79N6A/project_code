<div class="jdall">
    <form action="/new/bank/codeconfirm?orderinfo=<?php echo $orderinfo; ?>" method="get" id="cards">
        <input type="hidden" name = "banktype" value="<?php echo $banktype; ?>">
        <div class="jdyyy">
            <div class="dbk_inpL">
                <label>卡类型 </label> <span> <?php echo $bank_name;?> <em><?php echo $card_type;?></em></span>
            </div>
        </div>
        <div class="pleadecard">请确认该手机号是该银行卡的银行预留手机号</div>
        <div class="jdyyy">
            <div class="dbk_inpL">
                <label>手机号</label><input value="<?php echo $user->mobile; ?>" maxlength="11" name="tel" id="tel" placeholder="请填写该卡的银行预留手机号" type="text">
            </div>
        </div>
        <input type="hidden"  name="user_id" value="<?php echo $user->user_id; ?>">
        <input type="hidden" id="card" name="card" value="<?php echo $post_data['card']; ?>">
        <input type="hidden" id="realname" name="realname" value="<?php echo $user->realname; ?>">
        <?php $csrf = \Yii::$app->request->getCsrfToken(); ?>
        <input  id="_csrf" name="_csrf" type="hidden" value="<?php echo $csrf; ?>">
        <input  id="orderinfo" name="orderinfo" type="hidden" value="<?php echo $orderinfo; ?>">
        <div class="tsmes" id="remain"></div>
        <div class="button"> <button id="sub" type="submit" >下一步</button></div>
    </form>
</div>
<script>
    window.onload = function () {
        $('#sub').click(function () {
            var mark = false;
            var tel = $('input[name="tel"]').val();
            var reg = /^(1(([35678][0-9])|(47)))\d{8}$/;
            var orderinfo = $("#orderinfo").val();
            if(tel.length == 0){
                $("#remain").html('*请填写手机号');
                return false;
            }
            if(!reg.test(tel)){
                $("#remain").html('*请填写正确的手机号');
                return false;
            }

            $.ajax({
                url: '/new/bank/codeconfirm?type=very&orderinfo='+orderinfo,
                async: false,
                type: "GET",
                dataType: "json",
                data: $('#cards').serialize(),
                error: function (result) {
                    $("#remain").html('*提交失败');
                },
                success: function (result) {
                    if (result.code == 0) {
                        $("#remain").html("");
                        mark = true;
                    } else {
                        $("#remain").html(result.message);
                    }
                }
            });
            return mark;

        });
    }
</script>