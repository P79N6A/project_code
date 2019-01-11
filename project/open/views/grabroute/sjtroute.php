<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>确认提交</title>
    <link rel="stylesheet" type="text/css" href="/css/pay/reset.css"/>
    <link rel="stylesheet" type="text/css" href="/css/pay/<?=$css?>.css"/>
</head>
<body>
<div class="selfmess">
	<div class="selftximg">
		<div style="height:20px; background:#e7edf0;"></div>

        <div class="dbk_inpL">
            <label>手机号</label>
            <div class="input"><?=substr_replace($phone,"****",3,4)?></div>
        </div>
		<?php if ($is_smscode): ?>
        <div class="dbk_inpL">
			<label>验证码</label>
            <input id="validatecode"  style="font-size: 14px; padding: 5px;width: 100px;" placeholder="填写验证码" type="text" value=""> 
			<button id="getsmscode" style="font-size: 14px;padding:5px;">刷新验证码</button>
			<span  id="getsmscode2" style="display:none; position:inherit; font-size:14px; padding:6px 15px;" class="hqyzm">&nbsp;</span>
        </div>
		<?php endif; ?>

		<?php if ($is_imgcode): ?>
        <div class="dbk_inpL">
			<label>验证码</label>
			<input id="imgcode"  style="font-size: 14px; padding: 5px;width: 100px;" placeholder="填写验证码" type="text" value="">
			<img id='imgcodeurl' src="<?=$captcha_path;?>" style="width:100px;height:25px;margin-bottom:-8px;">
        </div>
		<?php endif; ?>

		

	</div>
	<div class="tsmes" id="showmessage"> &nbsp;</div>
	<div class="button"> <button id="comfirmpay">确定提交</button></div>
	<div class="tips"></div>

	<input type="hidden" id="aid" name="aid" value="<?=$aid?>">
	<input type="hidden" id="requestid" name="requestid" value="<?=isset($user_id)?$user_id:'';?>">
	<input type="hidden" id="source" name="source" value="<?=isset($source)?$source:'';?>">
	<input type="hidden" id="from" name="from" value="<?=isset($from)?$from:0;?>">
	<input type="hidden" id="_csrf" value="<?=\Yii::$app->request->getCsrfToken()?>" />

</div>

<script type="text/javascript" src="/bootstrap/js/jquery.min.js"></script>
<script type="text/javascript">
	function Order(){
		var me = this;
		me._csrf = '';
		me.oValidatecode = '';
		me.oShowmessage = '';
		me.oGetsmscode2 = $("#getsmscode2");
		me.oGetsmscode = $("#getsmscode");
		me.oimgcode = $("#imgcodeurl");
		me.requestid = '';
		me.imgcode = '';
		me.source = '';
		me.phone = "<?=$phone?>";


		me.init = function(){
			me.oValidatecode = $("#validatecode");
			me.oShowmessage = $("#showmessage");
			me.requestid = $("#requestid").val();
			me.source  = $("#source").val();
			me._csrf  = $("#_csrf").val();
			me.imgcode = $("#imgcode").val();

			me.oGetsmscode.click(me.timedec);
			me.oGetsmscode.click(me.getsmscode);
			me.oimgcode.click(me.imgcodeurl);
			$("#comfirmpay").click(me.comfirmpay);
		};
		/**
		 * 显示错误信息
		 */
		me.showMessage = function(content){
			content = content || '';
			me.oShowmessage.html(content);
		};
		me.timedec = function(){
			me.oGetsmscode.hide();
			me.oGetsmscode2.show();
			var t = 60;
			var txt = '';
			var run = function(){
				t--;
				txt = '还剩' + t + 's';
				me.oGetsmscode2.html(txt);
				if(t>0){
					setTimeout(function(){
						run();
					}, 1000 );
				}else{
					me.timedone();
				}
			};
			run();
		};
		// 时间倒计时完成后操作
		me.timedone = function(){
			me.oGetsmscode2.hide();
			me.oGetsmscode.show();
		};
		/**
			 * 刷新验证码
		 */
		me.getsmscode = function(){
			$.post(
					"<?=$refreshurl?>",
					{
						phone : me.phone,
						requestid : me.requestid,
						source : me.source,
						_csrf : me._csrf
					},
					function(data){
						me.showMessage(data.res_data);
						var aid = $('#aid').val()
						if(aid == 8){
							$('#comfirmpay').attr("style","background: #32DAC3");
						}
						if(aid == 10){
							$('#comfirmpay').attr("style","background: #BF974D");
						}else{
							$('#comfirmpay').attr("style","background: #e74747");
						}
//						$('#comfirmpay').attr("style","background: #e74747");
						$('#comfirmpay').removeAttr("disabled");
					},
					'json'
			);
		};
		/**
			* 刷新图片验证码
		 */
		me.imgcodeurl = function(){
			$.post(
					"<?=$rhimgurl?>",
					{
						phone : me.phone,
						requestid : me.requestid,
						source : me.source,
						_csrf : me._csrf
					},
					function(data){
						$("#imgcodeurl").attr("src",data.res_data.imgurl);
						var aid = $('#aid').val()
						if(aid == 8){
							$('#comfirmpay').attr("style","background: #32DAC3");
						}
						if(aid == 10){
							$('#comfirmpay').attr("style","background: #BF974D");
						}else{
							$('#comfirmpay').attr("style","background: #e74747");
						}
//						$('#comfirmpay').attr("style","background: #e74747");
						$('#comfirmpay').removeAttr("disabled");
						me.showMessage();
					},
					'json'
			);
		};
		/**
		 * 确认提交
		 */
		
		me.comfirmpay = function(){
			var validatecode = me.oValidatecode.val();
			var imgcodes = $("#imgcode").val();
			var from = $("#from").val();
			<?php if ($is_smscode): ?>
				if(validatecode == ''){
					me.showMessage("请填写验证码");
					return false;
				}
			<?php endif; ?>

			<?php if ($is_imgcode): ?>
				if(imgcodes == ''){
					me.showMessage("请填写验证码");
					return false;
				}
			<?php endif; ?>
			$('#comfirmpay').attr("style","background: #cdcdcd");
			$('#comfirmpay').attr('disabled',"true");
			// 3 同步请求
			$.ajax({
				type : "POST",
				url  : "<?=$commiturl?>",
				data : {
					<?php if ($is_imgcode): ?>
						imgcode : imgcodes,
					<?php endif; ?>
					<?php if ($is_smscode): ?>
						captcha : validatecode,
					<?php endif; ?>
					requestid : me.requestid,
					source : me.source,
					_csrf : me._csrf
				},
				dataType : "json",
				async    : true,
				success  : function(data){
					if(data.res_code){//失败
						if(data.res_code==101||data.res_code==105||data.res_code==123){
							me.showMessage(data.res_data.msg);
							window.location = window.location.href;
						}
						if(data.res_data.callbackurl){
							// alert(data.res_data.msg);
							me.showMessage(data.res_data.msg);
							if(from == 2){
								window.myObj.routeFail(data.res_data.msg);
							}
							var url = data.res_data.callbackurl;
							window.location = url;
						}else{
							var aid = $('#aid').val()
							if(aid == 8){
								$('#comfirmpay').attr("style","background: #32DAC3");
							}
							if(aid == 10){
								$('#comfirmpay').attr("style","background: #BF974D");
							}else{
								$('#comfirmpay').attr("style","background: #e74747");
							}
//							$('#comfirmpay').attr("style","background: #e74747");
							$('#comfirmpay').removeAttr("disabled");
							me.showMessage(data.res_data);
						}
					}else{
						if(data.res_data.res == 'y'){//成功
							if(from == 2){
								window.myObj.routeSuccess();
							}
							var url = data.res_data.callbackurl;
							window.location = url;
						}else{//还需要走下一个流程
							me.showMessage(data.res_data.msg);
							//window.location = window.location.href;
							var url = data.res_data.callbackurl;
							setTimeout(function(){
								window.location = url;
							},2000);
							
							
						}
					}
				}
			});
		};
	}
	// 创建对象
	
	var orderModel = new Order();
	orderModel.init();
	orderModel.timedec();
</script>

</body>
</html>