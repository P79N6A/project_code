<script>
    $(function () {
        $('.icon_Rem').click(function () {
            $(this).siblings('input').prop('value', '');
        });

        $(".button button").click(function () {
            $('.Hmask').show();
            $('.layer_border').show();
        });
        //点击关闭按钮
        $('.layer_border .border_top').click(function () {
            $('.Hmask').hide();
            $('.layer_border').hide();
        });
    });

    var fun = function () {
        this.value =this.value.replace(/\s/g,'').replace(/(\d{4})(?=\d)/g,"$1 ");;
    };
    $(document).ready(function(){
        $('#input_card').bind('input propertychange',fun);
    });


</script>

<div class="jdall">
    <form action="/new/bank/savecard?orderinfo=<?php echo $orderinfo; ?>" method="get" id="cards">
        <input type="hidden" name = "banktype" value="<?php echo $banktype ?>">
        <p class="pleadecard">请认证该持卡人的<?php if($banktype == 1){ ?>储蓄卡<?php }elseif($banktype == 2){ ?>信用卡<?php }elseif($banktype == 3){ ?>银行卡<?php } ?></p>
        <div class="jdyyy">
            <div class="dbk_inpL">
                <label>持卡人 </label> <span> <?php echo $user->realname; ?></span>
            </div>
            <div class="dbk_inpL">
                <label>卡号</label><input style="width: 80%"
                    <?php if($banktype == 1){ ?>
                        placeholder="请输入储蓄卡号"
                    <?php }elseif($banktype == 2){ ?>
                        placeholder="请输入信用卡号"
                    <?php }elseif($banktype == 3){ ?>
                        placeholder="请输入银行卡号"
                    <?php } ?> type="text"  maxlength="23" name="card" id="input_card" >
            </div>
            <input type="hidden"  name="user_id" value="<?php echo $user->user_id; ?>">
            <?php $csrf = \Yii::$app->request->getCsrfToken(); ?>
            <input  id="_csrf" name="_csrf" type="hidden" value="<?php echo $csrf; ?>">
            <input  id="orderinfo" name="orderinfo" type="hidden" value="<?php echo $orderinfo; ?>">
        </div>
        <div class="tsmes" id="remain"></div>
        <div class="button"> <button id="sub" type="submit">下一步</button></div>
    </form>
</div>

<div style="display: none" id="shadow">
    <div class="Hmask"></div>
    <div class="duihsucc hkaapp">
        <p class="xuhua"> 该卡当前已被认证！</p>
        <p>如非您本人操作请下载先花一亿元app咨询客服</p>
        <button class="hkrz">换卡认证</button><button class="xzai">下载APP</button>
    </div>
</div>

<script>
    window.onload = function () {
        $('#sub').click(function () {
            var card = $('input[name="card"]').val();
            card = card.replace(/\s+/g, "");
            var num = $("#num").val();
            var card_id = $("#card_id").val();
            var user_id = $('input[name="user_id"]').val();
            var orderinfo = $('#orderinfo').val();
            var mark = false;
            if(card.length == 0) {
                $("#remain").html('*请填写银行卡号');
                return mark;
            }
            if (!/^\d{15,19}$/.test(card)) {
                $("#remain").html('*请填写正确的银行卡号');
                return mark;
            }

            $.ajax({
                url: '/new/bank/savecard?type=very&orderinfo='+orderinfo,
                async: false, // 注意此处需要同步，因为返回完数据后，下面才能让结果的第一条selected  
                type: "GET",
                dataType: "json",
                data: $('#cards').serialize(),
                error: function (result) {
                    alert("绑卡失败请重试");
                },
                success: function (result) {
                    if (result.code == 0) {//绑卡（该状态）成功
                        location.href = result.nextPage;
                    } else if (result.res_code == 1) {//错误
                        $("#remain").html(result.res_data);
                    } else if (result.res_code == 2) {//此卡已绑定
                        $("#shadow").css('display','block');
                    } else if (result.res_code == 3) {//下一步
                        mark = true;
                    }
                }
            });
            return mark;
        });
        $('.hkrz').click(function () {
            window.location.reload();
        });
        $('.xzai').bind('click', function () {
            $.get("/wap/st/statisticssave", {type: 99, source:'h5'}, function () {
                window.location = '/wap/st/down';
                return false;
            })
        })
    }
</script>
