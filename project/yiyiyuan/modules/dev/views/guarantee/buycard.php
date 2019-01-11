<?php 
    $startTime = Yii::$app->params['newyear_start_time'];
    $endTime   = Yii::$app->params['newyear_end_time'];
    $time = time();
    if($time >= $startTime && $time <= $endTime){
        include '../modules/dev/views/loan/newyear.php';
    }
?>
<script>
	$(function(){
        //春节放假期间担保卡借款界面进来弹层
        var startTime = parseInt(<?php echo  Yii::$app->params['newyear_start_time'] ?>);
        var endTime = parseInt(<?php echo  Yii::$app->params['newyear_end_time'] ?>)
        var time = parseInt(<?php echo  time() ?>)
        if(time >= startTime && time <= endTime){
            $(".show_new_year").show();
        }else{
            $(".show_new_year").hide();
        }
        $(".sureyemian").click(function(){
            window.location.href='/dev/loan';
        })
    })
</script>
<script>
    $(function() {
        var money = 0;
        var height2 = $('.title2 .price').height();
        //$('.title2 .dbk_title').css('lineHeight', height2 + 'px');
        $('.dbk_ticket').each(function() {
            $(this).click(function() {
                money = $(this).attr('val');
                var num = $('input[name="num"]').val();
                //点击改变样式
                $('.dbk_ticket').find('img').attr('src', '/images/dbk_unchoose.png');
                $('.dbk_ticket').find('.dbk_title').css('color', '#c2c2c2');
                $('.dbk_ticket').find('.title2 .dbk_title').css('color', '#595959');
                $('.dbk_ticket').find('.title2 .price').css('color', '#595959');
                $(this).find('img').attr('src', '/images/dbk_choose.png');
                $(this).find('.dbk_title').css('color', '#fff');
                $(this).find('.title2 .price').css('color', '#e74747');
                //点击相对应的radio变为checked
                $('input[type="radio"]').prop('checked', false);
                $('input[type="radio"]').removeAttr('checked');
                $(this).find('input[type="radio"]').prop('checked', true);
                $(this).find('input[type="radio"]').attr('checked', 'checked');

                $('#money').html(money * num);
                $('input[name="amount"]').val(money * num);
            });
        });
        $('#plus').click(function() {
            var num = $('input[name="num"]').val();
            $('input[name="num"]').val(parseInt(num) + 1);
            $('#money').html(money * $('input[name="num"]').val());
            $('input[name="amount"]').val(money * $('input[name="num"]').val());

        });
        $('#reduce').click(function() {
            var num = $('input[name="num"]').val();
            if (parseInt(num) > 1) {
                $('input[name="num"]').val(parseInt(num) - 1);
                $('#money').html(money * $('input[name="num"]').val());
                $('input[name="amount"]').val(money * $('input[name="num"]').val());
            }
        });
    });
    function sub() {
        var card_id = $(":radio:checked");
        if (card_id.length == 0) {
            $('#remain').html('担保卡必须选一张');
            return false;
        }
        if (parseInt($('input[name="num"]').val()) < 1) {
            $('#remain').html('购买数量最少一张');
            return false;
        }
        $('#remain').html('');
        return true;
    }
</script>
<div class="Hcontainer nP">
    <div class="main">
        <form action="/dev/guarantee/buy" method="post" onsubmit="return sub()">
            <?php foreach ($guaranteeCard as $key => $val): ?>
                <div class="dbk_ticket" val="<?php echo intval($val->var); ?>">
                    <img src="/images/dbk_unchoose.png" width="100%">
                    <div class="wrap">
                        <div class="col-xs-12 title2">
                            <div class="dbk_title n36 grey6" style="line-height: 25px;">担保卡</div>
                            <div class="price n40 grey6">¥<span class="n60 bold"><?php echo intval($val->var); ?></span></div>
                        </div>
                        <div class="col-xs-12">
                            <div class="dbk_title n22 grey4" style="margin-top:-15px;">可用于担保借款<br/>和"园丁计划"</div>
							
                            <div class="price n22 grey4">等同于<?php echo intval($val->var); ?>担保额度</div>
                        </div>
                    </div>
                    <input type="radio" name="card_id" value="<?php echo $val->id; ?>">                
                </div>
            <?php endforeach; ?>       

            <div class="quantity">
                <div class="col-xs-4" id="reduce"><span>-</span></div>
                <div class="col-xs-4"><input type="text" class="q_num" name="num" value="1" readonly="readonly"></div>
                <div class="col-xs-4" id="plus"><span>+</span></div>
            </div>

            <div class="clearfix"></div>
            <p class="text-center mt20 n26">购买<span class="red" id="money">0</span>元的担保卡</p>
            <input type="hidden" name="amount" value="0">
            <?php if($now_time >= $start_time && $now_time <= $end_time):?>
        	<div style="padding: 10px 5%;color:red;">春节期间（2月5日－2月15日）担保投资正常，担保借款暂停服务，敬请谅解</div>
        	<?php endif;?>
            <button class="btn mt20 mb40" style="width:100%" id="lzh" obst="<?php echo $limitStatus;?>" disabled>确定</button>
            <span id="remain" style="color: red;"></span>
            
	        <div id="overDiv" style="display:none;"></div>
			<div id="diolo_warp" class="diolo_warp" style="display:none;">
	        <p class="title_cz">春节期间(2月5日－2月15日)担保投资正常<br/>担保借款暂停服务</p>
	        <p class="pay_bank"></p>
	        <p class="radious_img"></p>
	        <div class="true_flase">
	            <button class="flase_qx" id='hlz'>取消</button>
	            <button class="true_qr" id='tbug'>确定</button>
	        </div>
            
        </form>
    </div>                         
</div>
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
<script>
    $(function(){
        alert("担保卡业务即将升级，已暂停服务");
        return false;
    });
$('#lzh').click(function(){
	var obst_status = $(this).attr('obst');
	if(obst_status == 0){
		$('form[id="lzh"]').submit();
	}else{
        var card_id = $(":radio:checked");
        if (card_id.length == 0) {
            $('#remain').html('担保卡必须选一张');
            return false;
        }
        if (parseInt($('input[name="num"]').val()) < 1) {
            $('#remain').html('购买数量最少一张');
            return false;
        }
        $('#remain').html('');
		$('#diolo_warp').show();
		$('#overDiv').show();
		return false;
	}
});
$('#hlz').click(function(){
    $('#diolo_warp').hide();
	$('#overDiv').hide();
	return false;
});

$('#tbug').bind('click',function(){
	$('form[id="tbug"]').submit();
});
</script>