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
		<div style="height:20px; background:#e7edf0;"></div>
		<div class="dbk_inpL">
        	<label>支付金额</label>
            <div class="input">￥<?=($oLianOrder->amount)/100?></div>
            <!--input placeholder="" type="text"-->
    	</div>
        <div style="height:20px; background:#e7edf0;"></div>
    	<div class="dbk_inpL">
        	<label>姓名</label>
            <div class="input"><?=\app\common\Func::strProtected($oBind->user_name)?></div>
    	</div>
    	<div class="dbk_inpL">
        	<label>身份证</label>
            <div class="input"><?=substr_replace($oBind->idcardno,"****",4,10)?></div>
    	</div>
        <div class="dbk_inpL">
            <label>手机号</label>
            <div class="input"><?=substr_replace($oBind->bank_mobile,"****",3,4)?></div>
        </div>
        <div class="dbk_inpL">
            <label>银行卡</label>
            <div class="input"><?=substr_replace($oBind->cardno,"****",0,-4)?></div>
        </div>
        <div class="dbk_inpL">
            <label>验证码</label>
            <input id="validatecode" class="yzmwidth" maxlength="6" placeholder="填写验证码" type="text">
            <span  id="getsmscode" class="hqyzm">获取验证码</span>
            <span  id="getsmscode2" style="display:none;" class="hqyzm">&nbsp;</span>
        </div>
	</div>
	<div class="tsmes" id="showmessage"> &nbsp;</div>
	<div class="button"> <button id="comfirmpay">确定支付</button></div>
	<div class="tips"></div>
	
	<input type="hidden" id="host" value="<?=\Yii::$app->request->hostInfo?>" />	
	<input type="hidden" id="_csrf" value="<?=\Yii::$app->request->getCsrfToken()?>" />	
	<input type="hidden" id="xhhorderid" value="<?=$xhhorderid?>" />	
	
</div>

<script src="/bootstrap/js/jquery.min.js"></script>
<script type="text/javascript">
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
	
	me.init = function(){
		me.oValidatecode = $("#validatecode");
		me.oShowmessage = $("#showmessage");
		
		me.xhhorderid = $("#xhhorderid").val();
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
				"/lianpay/getsmscode",
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
		
		// 3 同步请求
		$.ajax({
    			type : "POST",
    			url  : me.nexturl,
    			data : {
						xhhorderid   : me.xhhorderid,
						_csrf        : me._csrf,
						
						requestid    : me.requestid,
						validatecode : validatecode
					},
    			dataType : "json",
    			async    : false,
    			success  : function(data){
					if(data.res_code){
						me.showMessage(data.res_data);
					}else{
						var url = data.res_data.callbackurl;
						window.location = url;
					}
				}
    		});
	};
}
// 创建对象
var orderModel = new Order();
orderModel.init();
</script>

</body>
</html>