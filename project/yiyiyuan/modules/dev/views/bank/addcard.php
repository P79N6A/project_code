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

    function change() {
        var card = $('#input_card').val();
        if (card.length > 4) {
            if (card[card.length - 1] != ' ') {
                card = card.replace(/\s+/g, "");
                if (card.length % 4 == 1) {
                    var ncard = '';
                    for (var n = 0; n < card.length; n++) {
                        if (n % 4 == 3)
                            ncard += card.substring(n, n + 1) + " ";
                        else
                            ncard += card.substring(n, n + 1);
                    }
                    $('#input_card').val(ncard);
                }
            }
        }
    }
</script>
<div class="jdall">
    <form action="/dev/bank/savecard" method="post" id="cards">
        <h3 class="qingbingd">请绑定该持卡人的银行卡</h3>
        <div class="jdyyy danweimes">
            <div class="dbk_inpL">
                <label>持卡人</label><?php echo $user->realname; ?>
            </div>
            <div class="dbk_inpL">
                <label>卡号</label><input type="text" placeholder="请输入银行卡号" maxlength="23" name="card" id="input_card" onkeyup="change()">
                <img src="/images/icon_remove.png" class="icon_Rem">
            </div>
        </div>
        <?php if ($f != ''): ?>
            <input type="hidden" name="f" value="<?php echo $f; ?>">
        <?php endif; ?>
        <input type="hidden" id="url" name="url" value="<?php echo $url; ?>">
        <input type="hidden" id="num" name="num" value="<?php echo $num; ?>">
        <input type="hidden" id="card_id" name="card_id" value="<?php echo $card_id; ?>">
        <input type="hidden"  name="user_id" value="<?php echo $user->user_id; ?>">
        <div class="tsmes" id="remain"></div>
        <div class="button"> <button id="sub" type="submit">提交</button></div>
    </form>
</div>
<script>
    window.onload = function () {
        $('#sub').click(function () {
            var card = $('input[name="card"]').val();
            card = card.replace(/\s+/g, "");
            var url = $("#url").val();
            var num = $("#num").val();
            var card_id = $("#card_id").val();
            var user_id = $('input[name="user_id"]').val();
            if(card.length == 0) {
                $("#remain").html('*请填写银行卡号');
                return false;
            }
            var mark = false;
            if (!/^\d{15,19}$/.test(card)) {
                $("#remain").html('请输入正确的银行卡号');
                return mark;
            }
            $.ajax({
                url: '/dev/bank/savecard?type=very',
                async: false, // 注意此处需要同步，因为返回完数据后，下面才能让结果的第一条selected  
                type: "POST",
                dataType: "json",
                data: $('#cards').serialize(),
                error: function (result) {
                    alert('绑卡失败，请重新绑定！');
                },
                success: function (result) {
                    if (result.code == 0) {
                        $('#cards').append('<input type="hidden" name="card_type" value="' + result.card_type + '">');
                        $("#remain").html("");
                        mark = true;
                    } else if (result.code == 3) {
                        if (url == "") {
                            location.href = "/dev/bank/success?old=1";
                        } else {
                            location.href = url + '?' + 'num=' + num + '&card_id=' + card_id;
                        }
                    } else {
                        $("#remain").html(result.message);
                    }
                }
            });
            return mark;
        });
//        var oBtn = document.getElementById('sub');
//        oBtn.onclick = function (e) {
//        }
    }
</script>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
    wx.config({
        debug: false,
        appId: '<?php echo $jsinfo['appid']; ?>',
        timestamp: <?php echo $jsinfo['timestamp']; ?>,
        nonceStr: '<?php echo $jsinfo['nonceStr']; ?>',
        signature: '<?php echo $jsinfo['signature']; ?>',
        jsApiList: [
            'hideOptionMenu'
        ]
    });

    wx.ready(function () {
        wx.hideOptionMenu();
    });
</script>