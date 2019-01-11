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
			                <div class="col-xs-3">设置密码: </div>
			                <div class="col-xs-8">
			                    <input type="password" id="password" name="password" placeholder="6-12位数字和字母组合">
			                </div>
			            </li>
			            <li class="noBorder">
			                <div class="col-xs-3">确认密码：</div>
			                <div class="col-xs-8">
			                    <input type="password" id="password_re" name="password_re" placeholder="6-12位数字和字母组合">
			                </div>
			            </li>
			            <input type="hidden" id="mobile" value="<?php echo $mobile ?>">
    					<input type="hidden" id="csrf" value="<?php echo $_csrf ?>">
			            <li class="zhaomima">
			                <div class="col-xs-12 showError">&nbsp;</div>
			            </li>
			        </ul>
			    </div>
			    <button class="jebangding" type="submit" id="next">完成</button>
			</div>
			<img class="l-right"  src="/images/web/loading3.png">
		</div>		
	</div>	
</div>


<script>
	function showErr(text){
        $('.showError').html(text);
    }
    $(function(){
        var passwd = /^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{6,12}$/;
        $('input[name=password]').blur(function(){
            var password = $('input[name=password]').val();
            if(!passwd.test(password)){
                showErr('密码必须为6-12位数字和字母组合，如abc123等。');
                return false;
            }
            showErr('&nbsp;');
        })

        $('input[name=password_re]').blur(function(){
            var password_re = $('input[name=password_re]').val();
            var password = $('input[name=password]').val();
            if (password != password_re) {
                showErr('两次密码输入不一致');
                return false;
            };
            showErr('&nbsp;');
        })

        $('#next').click(function(){
            var password = $('input[name=password]').val();
            if(!passwd.test(password)){
                showErr('密码必须为6-12位数字和字母组合，如abc123等。');
                return false;
            };
            var password_re = $('input[name=password_re]').val();
            if (password != password_re) {
                showErr('两次密码输入不一致');
                return false;
            };
            showErr('&nbsp;');
            var _csrf = $('#csrf').val();
            var mobile = $('#mobile').val();
            $.post('/default/setpassword',{_csrf:_csrf,mobile:mobile,password:password,password_re:password_re},function(data){
                if (data && data.res_code == 0) {
                	alert('设置密码成功');
                    window.location.href = "/default/login";
                }else{
                    showErr(data.res_data);
                }
            },'json')
        })
    })
</script>