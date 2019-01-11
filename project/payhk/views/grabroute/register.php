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
<!--请稍候蒙层-->
<div style="width: 100%; height: 100%;background: rgba(0,0,0,.7); position: fixed;top: 0;left: 0; z-index: 100;" id ="loadings" hidden></div>
<div class="loading" style='left: 45%; position: absolute; text-align: center; top: 30%; width: 15%;z-index: 100;' hidden>
    <img src="/images/load.gif" style="width:100%;" >
    <p class="pleasesh">请稍后...</p >
</div>
<div class="selfmess">
	<div class="selftximg">
		<div style="height:20px; background:#e7edf0;"></div>

        <div class="dbk_inpL">
            <label>手机号</label>
            <div class="input"><?=substr_replace($phone,"****",3,4)?></div>
        </div>

        <div class="dbk_inpL">
			<label>服务密码</label>
            <input id="password"  style="font-size: 14px; padding: 5px;width: 100px;" placeholder="请填写服务密码" type="text" value=""> 
        </div>
	</div>
	<div class="tsmes" id="showmessage"> &nbsp;</div>
	<div class="button"> <button id="comfirmpay">确定提交</button></div>
	<div class="tips"></div>
	<input type="hidden" id="aid" name="aid" value="<?=$aid?>">
	<input type="hidden" id="requestid" name="requestid" value="<?=isset($userId)?$userId:'';?>">
	<input type="hidden" id="from" name="from" value="<?=isset($from)?$from:0;?>">
	<input type="hidden" id="_csrf" value="<?=\Yii::$app->request->getCsrfToken()?>" />
	<div style='text-align:center;margin:90% 20px; font-size:14px;'>如需重置密码，请拨打对应运营商客服热线<br>移动10086；联通10010；电信10000</div>
</div>

<script type="text/javascript" src="/bootstrap/js/jquery.min.js"></script>
<script type="text/javascript">
	function Order(){
		var me = this;
		me._csrf = '';
		me.oShowmessage = '';
		me.password = $("#password");
		me.requestid = '';
		me.phone = "<?=$phone?>";


		me.init = function(){
			me.oShowmessage = $("#showmessage");
			me.requestid = $("#requestid").val();
			me._csrf  = $("#_csrf").val();

			$("#comfirmpay").click(me.comfirmpay);
		};
		/**
		 * 显示错误信息
		 */
		me.showMessage = function(content){
			content = content || '';
			me.oShowmessage.html(content);
		};


		/**
		 * 确认提交
		 */
	    function routeResult() {}
		me.comfirmpay = function(){
			var password = me.password.val();
			var from = $("#from").val();
			if(!password){
				me.showMessage("请填写服务密码");
				return false;
			}
			$('#comfirmpay').attr("style","background: #cdcdcd");
			$('#comfirmpay').attr('disabled',"true");
			$('#loadings').show();
			$('.loading').show();
			// 3 同步请求
			$.ajax({
				type : "POST",
				url  : "<?=$commiturl?>",
				data : {
					requestid : me.requestid,
					password : password,
					_csrf : me._csrf
				},
				dataType : "json",
				async    : true,
				success  : function(data){
					if(data.res_code){//失败
						// if(data.res_code == 10006 || data.res_code == 10004){
						// 	$('#comfirmpay').attr("style","background: #e74747");
						// 	$('#comfirmpay').removeAttr("disabled");
						// 	me.showMessage(data.res_data.msg);
						// }else{
						// 	if(from == 2){
						// 		window.myObj.routeFail(data.res_data.msg);
						// 	}
						// 	var url = data.res_data.callbackurl;
						// 	window.location = url;
						// }
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

							$('#comfirmpay').removeAttr("disabled");
							me.showMessage(data.res_data);
							$('#loadings').hide();
							$('.loading').hide();
						}
					}else{
						if(data.res_data.res == 'y'){//成功
							if(from == 2){
								window.myObj.routeSuccess();
							}
							var url = data.res_data.callbackurl;
							window.location = url;
						}else{//还需要走下一个流程
							var url = data.res_data.callbackurl;
							window.location = url;
						}
					}
				}
			});
		};
	}
	// 创建对象
	var orderModel = new Order();
	orderModel.init();
	
</script>


<!--埋点-->


<script>
    <?php
    $json_data = \app\common\PLogger::getJson();
    $post_log_url = 'http://kafka.xianhuahua.com/webs';
    ?>
	var baseInfoss = eval( '('  + '<?php echo $json_data; ?>' + ')' );
</script>

<script type="text/javascript">
    var ortherInfo = {
        screen_height: window.screen.height,//分辨率高
        screen_width: window.screen.width,  //分辨率宽
        user_agent: navigator.userAgent,
        height: document.documentElement.clientHeight || document.body.clientHeight,  //网页可见区域宽
        width: document.documentElement.clientWidth || document.body.clientWidth,//网页可见区域高

    };
    var baseInfos = Object.assign(baseInfoss, ortherInfo);

    iframepost();
    function iframepost( ){
        var turnForm = document.createElement("form");
        turnForm.id = "uploadImgForm";
        turnForm.name = "uploadImgForm";
        document.body.appendChild(turnForm);
        turnForm.method = 'post';
        turnForm.action = '<?php echo $post_log_url;?>';
        //创建隐藏表单
        for (var i in baseInfos) {
            var newElement = document.createElement("input");
            newElement.setAttribute("name",i);
            newElement.setAttribute("type","hidden");
            newElement.setAttribute("value",baseInfos[i]);
            turnForm.appendChild(newElement);
        }
        var iframeid = 'if' + Math.floor(Math.random( 999 )*100 + 100) + (new Date().getTime() + '').substr(5,8);
        var iframe = document.createElement('iframe');
        iframe.style.display = 'none';
        iframe.id = iframeid;
        iframe.name = iframeid;
        iframe.src = "about:blank";
        document.body.appendChild( iframe );
        turnForm.setAttribute("target",iframeid);
        turnForm.submit();
    }
</script>

</body>
</html>