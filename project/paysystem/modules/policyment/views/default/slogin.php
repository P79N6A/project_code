<?php 
$_csrf = \Yii::$app->request->getCsrfToken();
 ?>
<header class="g-header">
	<div class="l-container clearfix">
		<a href="<?php echo Yii::$app->request->hostInfo ?>" class="g-header-logo" style="overflow:hidden">
			<img src="/images/web/mifulogo.png">
		</a>
		<span class="denglu">登录</span>
	</div>
</header> 
<div class="loading">
	<div class="l-container">
		<div class="loadbanner">
			<div class="l-left main">
			    <div class="jcbd jcbdes">
			        <ul>
			            <li class="noBorder">
			                <div class="col-xs-3">手机号:</div>
			                <div class="col-xs-8">
			                    <input type="text" id="mobile" name="mobile" placeholder="请输入注册手机号">
			                </div>
			            </li>
			            <li class="noBorder">
			                <div class="col-xs-3">验证码：</div>
			                <div class="col-xs-9">
			                    <input type="text" id="code" name="code" placeholder="请输入验证码">
			                    <button type="button" class="btn" id='getCode'>获取</button>
			                    <button type="button" class="btn" id='getCode2' style="color:#dcdcdc;border:1px solid #dcdcdc;display:none">获取</button>
			                </div>
			            </li>
			            <input type="hidden" id="_csrf" value="<?php echo $_csrf ?>">
			            <li class="zhaomima">
			                <div class="col-xs-12 showError">&nbsp;</div>
			            </li>
			        </ul>
			    </div>
			    <button class="jebangding" id="makeSure">下一步</button>
			</div>
			<img class="l-right"  src="/images/web/loading3.png">
		</div>		
	</div>	
</div>

<script>
$(function(){
	chkTel = /^(((13[0-9]{1})|(14[0-9]{1})|(15[0-35-9]{1})|(17[0-9]{1})|(18[0-9]{1}))+\d{8})$/;
	function showErr(text){
		$(".showError").html(text);
	}
	/**
     * 倒计时功能
     */
    var timedec = function(){
        $('#getCode').hide();
        $('#getCode2').show();
        var t = 60;
        var txt = '';
        
        // 倒计时
        var run = function(){
            t--;
            txt = '还剩' + t + 's';   
            $('#getCode2').html(txt); 
            if(t>0){
                setTimeout(function(){
                    run();
                }, 1000 );
            }else{
                $('#getCode2').hide();
                $('#getCode').show();
            }
        };
        // 立即执行
        run();
    };
	$('#getCode').click(function(){
		var mobile = $('#mobile').val();
		var _csrf = $('#_csrf').val();
		if (!mobile) {
			showErr('手机号码不能为空');
			return false;
		};
		if (!chkTel.test(mobile)) {
			showErr('请输入正确的手机号');
			return false;
		};
		$.post('/default/isreg',{mobile:mobile,_csrf:_csrf},function(data){
			if (data && data.res_code == 1) {
				showErr(data.res_data);
				return false;
			}else if (data.res_code == 2){
				showErr(data.res_data);
				return false;
			}
			showErr('&nbsp;');
			timedec();
			$.post('/default/getcode',{mobile:mobile,_csrf:_csrf,type:'setpwd'},function(data){
				if (data.res_code == 0) {
					// alert(1);
				}else{
					showErr(' ＊'+data.res_data);
					return false;
				}
			},'json')
		},'json');
	})
	var iscode = /^\d{1,10}$/;
	$('.jebangding').click(function(){
		var mobile = $('#mobile').val();
		var code = $('#code').val();
		var _csrf = $('#_csrf').val();
		var from_code = $('#from_code').val();
		if (!mobile) {
			showErr(' ＊手机号码不能为空');
			return false;
		};
		if (!chkTel.test(mobile)) {
			showErr(' ＊请输入正确的手机号');
			return false;
		};
		if (!code) {
			showErr(' ＊请输入动态密码');
			return false;
		};
		showErr('&nbsp;');
		$.post('/default/slogin',{mobile:mobile,code:code,from_code:from_code,_csrf:_csrf},function(data){
			if (data.res_code == 0) {
				window.location.href = '/default/setpassword';
			}else{
				showErr(' ＊'+data.res_data);
				return false;
			}
		},'json')
	})
})
</script>
