<div class="row">
	<div class="col-md-12">
<!--
	作者：zhangfei@163.com
	时间：2017-5-05
	描述：
-->	
<style>
.testquick a{font-size:24px;text-decoration:none;color:#3c75af;}
</style>
<h1>融360-DEMO</h1><hr />

<div style="border: 2px solid #cdcdcd; padding: 10px">
	<form id="rong" name="rong" method="post" action="/api/test/testrong/test">
		姓名：<input type="text" name="name" id="name" value="<?=isset($name)?$name:'';?>"/><br /><br />
		身份证：<input type="text" name="idcard" id="idcard" value="<?=isset($idcard)?$idcard:'';?>"/><br /><br />
		手机号：</label>
		<input type="text" name="phone" id="phone" value="<?=isset($phone)?$phone:'';?>"/><br /><br />
		服务密码：</label>
		<input type="text" name="password" id="password" value="<?=isset($password)?$password:'';?>"/><br /><br />
		短信验证码：</label>
		<input type="text" name="captcha" id="captcha" value="<?=isset($captcha)?$captcha:'';?>"/><br /><br />
		图片验证码：
		<input type="text" name="pic_code" id="pic_code" value="<?=isset($pic_code)?$pic_code:'';?>"/><br /><br />

		<input type="hidden" name="requestid" value="<?=isset($requestid)?$requestid:'';?>">
		<!--<input type="hidden" name="process_code" value="<?=isset($process_code)?$process_code:0;?>">-->
		<input type="hidden" name="method" value="<?=isset($method)?$method:'';?>">
		<?php
			$type = isset($type)?$type:0;
			if($type == 1){
				echo '<p style="color: red;">请输入验证码</p>';
			}
		?>
		<input name="sub" type="submit" value="提交" />
	</form>
</div>	 
	
		
<!--end-->		
	</div>
</div>
