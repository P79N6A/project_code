<div class="faqtx">

    <div class="disitems disste">
		<div class="disitem_first">手机号：</div>
		<div class="disitem_two"><input class="gray" type="text" name='mobile' id='mobile'></div>
	</div>

	<div class="disitems">
		<div class="disitem_first">充值流量：</div>
		<div class="disitem disitem_two">
			<select name='flow_amount' style="width:100%" id='flow_amount'>
			   <!-- <option value=''>--请选择--</option>
			   <option value=''>30</option>
			   <option value=''>50</option>
			   <option value=''>100</option> -->
			</select>
		</div>
		<div class="disitem_three">M</div>
	</div>
</div>
<div class="disitem txijl_jikl">
	<div class="kongde"></div>
	<div class="disitem txijl_txjl ">
		<img src="/images/txjl.png">
		<span><a href='/background/receive/flowlist'>领取记录</a></span>
	</div>

</div>
<div style="height:20px;color:#e74747; margin-left:3%;">
	<p class='sytx_txt'></p>
</div>
<input type="hidden" name="userId" value="<?php echo $user_id;?>" id="userId" />
<div class="disitem" style="margin-top:20px;">
    <button class="button_anniu" id='incomewd' obst="<?php echo $limitStatus;?>" obig="<?php echo !empty($accountinfo)?number_format($accountinfo->total_history_flow-$accountinfo->total_on_flow, 2, ".", ""):number_format(0.00, 2,".", "");?>">确 认</button>
</div>
<script>
    $('.nav_right').click(function(){
        window.location.href = '<?php echo $returnUrl ?>';
    })
</script>
<script>
 $('#mobile').keyup(function () {
	 //blur
   var mobile = $('#mobile').val();
    $.post("/background/receive/flowsave",{mobile:mobile},function(data){
		$('#flow_amount').html();
		var data = eval("(" + data + ")");
        $('#flow_amount').html('<option value='+data[0]+'>'+data[0]+'</option><option value='+data[1]+'>'+data[1]+'</option><option value='+data[2]+'>'+data[2]+'</option>');
	})
 })

$('#incomewd').click(function () {
		var flow_amount = $('#flow_amount').val();
		//alert(flow_amount);exit;
    	var outbig = $(this).attr("obig");
    	var outstatus = $(this).attr("obst");
    	var user_id = $("#userId").val();
    	var mobile = $('#mobile').val();

		if( !mobile ){
    		$(".sytx_txt").text("请添写手机号！");
    		return false;
    	}
		
    	if(flow_amount==null || flow_amount == '0'){
    		$(".sytx_txt").text("请输入流量的额度");
    		return false;
    	}else if( flow_amount < 1 ){
    		$(".sytx_txt").text("当流量满1.00点后，即可提取！");
    		return false;
    	}else if( parseFloat(flow_amount) > parseFloat(outbig) ){
    		$(".sytx_txt").text("最多可提取"+outbig);
    		return false;
    	}


    	if( outstatus == '1' || outstatus == '3'){
    		
			$(".sytx_txt").html("由于您的征信记录有瑕疵，暂不可申请提现；");
			
			return false;
    	}else if( outstatus == '2' ){
    		
    		$(".sytx_txt").html("由于您当前有逾期借款，暂不可申请提现；");
    	
    		return false;
    	}
		
    	$(this).attr('disabled', true);
    	$.post("/background/receive/flowincome", {user_id:user_id,flow_amount:flow_amount,mobile:mobile}, function (result) {
            var data = eval("(" + result + ")");
			//alert(data.ret);exit;
            $("#incomewd").attr('disabled', false);
            if (data.ret == 0) {
				alert('处理中！！！');
				window.location.href="/background/wallet/"; 
            } else {
            	$(".sytx_txt").text(data.msg);
            }
        });

    });
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
            'closeWindow',
            'hideOptionMenu'
        ]
    });

    wx.ready(function() {
        wx.hideOptionMenu();
    });
</script>