<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>确认支付</title>
    <link rel="stylesheet" type="text/css" href="/css/pay/reset.css"/>
    <link rel="stylesheet" type="text/css" href="/css/pay/index.css"/>
</head>
<body>
<div class="selfmess">
	<div class="selftximg">
		<!--请稍候蒙层-->
		<div style="width: 100%; height: 100%;background: rgba(0,0,0,.7); position: fixed;top: 0;left: 0; z-index: 100;" id ="loadings" hidden></div>
		<div class="loading" style='left: 45%; position: absolute; text-align: center; top: 30%; width: 15%;z-index: 100;' hidden>
			<img src="/images/load.gif" style="width:100%;" >
			<p class="pleasesh">请稍后...</p >
		</div>


		<div style="height:20px; background:#e7edf0;"></div>
		<div class="dbk_inpL">
        	<label>支付金额</label>
            <div class="input">￥<?=($oPayorder->amount)/100?></div>
            <!--input placeholder="" type="text"-->
    	</div>
        <div style="height:20px; background:#e7edf0;"></div>
    	<div class="dbk_inpL">
        	<label>姓名</label>
            <div class="input"><?=\app\common\Func::strProtected($oPayorder->name)?></div>
    	</div>
    	<div class="dbk_inpL">
        	<label>身份证</label>
            <div class="input"><?=substr_replace($oPayorder->idcard,"****",4,10)?></div>
    	</div>
        <div class="dbk_inpL">
            <label>手机号</label>
            <div class="input"><?=substr_replace($oPayorder->phone,"****",3,4)?></div>
        </div>
        <div class="dbk_inpL">
            <label>银行卡</label>
            <div class="input"><?=substr_replace($oPayorder->cardno,"****",0,-4)?></div>
		</div>
		<?php
			if($oPayorder->aid == '1' || $oPayorder->aid == '9'){
				$yzm = 'hqyzm';
				$button = 'button';
			}elseif($oPayorder->aid == '8'){
				$yzm = 'hqyzm_9';
				$button = 'button_9';
			}elseif($oPayorder->aid == '10'){
				$yzm = 'hqyzm_10';
				$button = 'button_10';
			}else{
				$yzm = 'hqyzm';
				$button = 'button';
			}
		?>
        <div class="dbk_inpL">
            <label>验证码</label>
            <input id="validatecode" class="yzmwidth" maxlength="6" placeholder="填写验证码" type="text">
            <span  id="getsmscode" class="<?=$yzm?>">获取验证码</span>
            <span  id="getsmscode2" style="display:none;" class="<?=$yzm?>">&nbsp;</span>
        </div>
	</div>
	<div class="tsmes" id="showmessage"> &nbsp;</div>
	<div class="<?=$button?>"> <button id="comfirmpay">确定支付</button></div>
	<div class="tips"></div>
	
	<input type="hidden" id="host" value="<?=\Yii::$app->request->hostInfo?>" />	
	<input type="hidden" id="_csrf" value="<?=\Yii::$app->request->getCsrfToken()?>" />	
	<input type="hidden" id="xhhorderid" value="<?=$xhhorderid?>" />
	<input type="hidden" id="smsseq" value="" />
	
</div>

<script src="/bootstrap/js/jquery.min.js"></script>
<script type="text/javascript">
var smsurl = "<?php echo $smsurl?>";
function Order(){
	var me = this;
	me._csrf = '';
	me.xhhorderid = '';
	me.oValidatecode = '';
	me.oShowmessage = '';
	me.oGetsmscode2 = $("#getsmscode2");
	me.oGetsmscode = $("#getsmscode");
	me.nexturl = '';
	me.requestid = '';
	me.smsseq = '';
	
	me.init = function(){
		me.oValidatecode = $("#validatecode");
		me.oShowmessage = $("#showmessage");
		
		me.xhhorderid = $("#xhhorderid").val();
		me.smsseq = $("#smsseq").val();
		me._csrf  = $("#_csrf").val();
		
		me.oGetsmscode.click(me.timedec);
		me.oGetsmscode.click(me.getsmscode);
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
	 * 请求绑定
	 */
	me.getsmscode = function(){
		$.post(
				smsurl,
				{
					xhhorderid: me.xhhorderid,
					_csrf : me._csrf
				},
				function(data){
					if(data.res_code){
						me.showMessage(data.res_data);
					}else{
						isbind = data.res_data.isbind;
						if(isbind){
							me.requestid = data.res_data.requestid;
							if(!me.requestid){
								me.showMessage("请求失败，可能是系统错误");
								return false;
							}
						}
						me.nexturl = data.res_data.nexturl;
						return true;
					}
				},
				'json'
		);
	};
	/**
	 * 确认绑定并支付
	 */
	me.comfirmpay = function(){
		//1 验证是否有回调地址
		me.requestid = me.requestid || '';
		if(!me.nexturl){
			me.showMessage("请先获取验证码");
			return false;
		}
		
		//2 短信验证码
		var validatecode = me.oValidatecode.val();
		if(!validatecode){
			me.showMessage("请填写验证码");
			return false;
		}
		//3 验证按钮是否正在提交
		if($("#comfirmpay").prop('disabled') == true){
			return false;
		}
		$("#comfirmpay").attr('disabled',true);

		$('#loadings').show();
		$('.loading').show();
		// 4 Ajax提交请求
		// 因为是同步请求会导致主线程页面渲染阻塞
		// 故需要加定时器开一个新线程
		setTimeout(function(){
			$.ajax({
    			type : "POST",
    			url  : me.nexturl,
    			data : {
						xhhorderid   : me.xhhorderid,
						_csrf        : me._csrf,
						
						requestid    : me.requestid,
						validatecode : validatecode,
						smsseq       : me.smsseq
					},
    			dataType : "json",
    			async    : false,
    			success  : function(data){
					$('#loadings').hide();
					$('.loading').hide();
					$("#comfirmpay").attr('disabled',false);
					if(data.res_code){
						me.showMessage(data.res_data);
					}else{
						var url = data.res_data.callbackurl;
						window.location = url;

					}
				}
    		});
		}, 100);
	};
}
// 创建对象
var orderModel = new Order();
orderModel.init();
</script>

</body>
</html>