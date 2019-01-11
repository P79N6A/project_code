<script>
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
<div class="Hcontainer nP">
    <div class="main">
        <form action="/dev/guarantee/savecard" method="post" id="cards">
            <div class="cardNum mt20">
                <label>银行卡号</label><input type="text" placeholder="请输入银行卡号" name="cards" maxlength="23"  id="input_card" onkeyup="change()">
                <input type="hidden" name="guarantee_id" value="<?php echo $post_data['card_id'];?>">
                <input type="hidden" name="guatantee_num" value="<?php echo $post_data['num'];?>">
            </div>
            <span id="remain" style="color: red;"></span>
            <button class="btn mt20 mb40" id="sub" style="width:100%">确定</button>
        </form>
    </div>                            
</div>
<script>
    window.onload = function() {
        var oBtn = document.getElementById('sub');
        oBtn.onclick = function() {
            var data_card = $('input[name="cards"]').val();
            data_card = data_card.replace(/\s+/g, "");
            if (!/^\d{15,19}$/.test(data_card)) {
                $("#remain").html('请输入正确的银行卡号');
                return false;
            }
            $("#remain").html('');
            var data = $('#cards').serialize();
            var mark = false;
            $.ajax({
                type: "POST",
                dataType: "json",
                url: "/dev/guarantee/savecard?type=very",
                data: data,
                async: false,
                error: function(result) {
                    $("#remain").html(result.message);
                    return false;
                },
                success: function(result) {
                    if (result.code == 0) {
                        $('#cards').append('<input type="hidden" name="card_type" value="'+result.card_type+'">');
                        mark = true;
                    } else {
                        $("#remain").html(result.message);
                    }
                }

            });
            return mark;
        }
    }
</script>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
  wx.config({
	debug: false,
	appId: '<?php echo $jsinfo['appid'];?>',
	timestamp: <?php echo $jsinfo['timestamp'];?>,
	nonceStr: '<?php echo $jsinfo['nonceStr'];?>',
	signature: '<?php echo $jsinfo['signature'];?>',
	jsApiList: [
		'hideOptionMenu'
	  ]
  });
  
  wx.ready(function(){
	  wx.hideOptionMenu();
	});
</script>