$(function(){
	var flag=flagmsg=flagrepass=flagrepasscode=flagretpass=flagretrepass=flagcode=flag1=flag2=flag3=flag4=flag5=flag6=flag7=flag8=flag9=false;
	//账号判断
	$('#username').focus(function(){
		$('#username').addClass('selected');
		$('#username_check').removeClass('error').removeClass('valid').addClass('prompt').html('请输入手机号，以便于找回密码');
	});
	
	$('#username').blur(function(){
		var username = $('#username').val();
		if(username.length > 0 && username != undefined && username != 'undefined')
		{
			//对注册的账号进行判断，如果注册的账号包括@符号，则为邮箱，进行邮箱判断，如果不包括@符号，则为手机号码，进行手机号码格式判断
			var RegExpmobile = /^(1(([358][0-9])|(47)))\d{8}$/;
			//如果为手机号码
			if(RegExpmobile.test(username))
			{
				$.ajax({
					type   : 'POST',
					async  : true,			//设置同步/异步访问
					url    : '/ajax/checkusername.php?action=checkusername',
					data   : 'username='+username,
					success:function(data)
					{
						if(data == 'success')
						{
							$('#username').removeClass('selected');
							$('#username_check').removeClass('prompt').removeClass('error').html('').addClass('valid');
							flag = true;
						}
						else if(data == 'morethree')
						{
							$('#username').removeClass('selected');
							$('#username_check').removeClass('prompt').removeClass('error').html('').addClass('valid');
							flag = true;
						}
						else
						{
							$('#username_check').addClass('error').html('该手机号已注册，请使用该账号直接<a class="lgi" href="/account/login.php">登录</a>');
							flag = false;
						}
					}
				});	
			}
			//不是手机号码
			else
			{
				$('#username_check').addClass('error').html('手机号码格式错误');
				flag = false;
			}
		}
		else
		{
			$('#username_check').addClass('error').html('请输入手机号，以便于找回密码');
			flag = false;
		}
		return flag;
	});
	
	//点击获取短信验证码的按钮，弹出发送短信验证码的弹窗
	$('#sms_button').die().live('click',function(){
		var username = $('#username').val();
		var type = 'register';
		if(username.length > 0 && username != undefined && username != 'undefined')
		{
			//对注册的账号进行判断，如果注册的账号包括@符号，则为邮箱，进行邮箱判断，如果不包括@符号，则为手机号码，进行手机号码格式判断
			var RegExpmobile = /^(1(([358][0-9])|(47)))\d{8}$/;
			//如果为手机号码
			if(RegExpmobile.test(username))
			{
				$.ajax({
					type   : 'POST',
					async  : true,			//设置同步/异步访问
					url    : '/ajax/checkusername.php?action=checkusername',
					data   : 'username='+username,
					success:function(data)
					{
						if(data == 'success')
						{
							$('#username').removeClass('selected');
							$('#username_check').removeClass('prompt').removeClass('error').html('').addClass('valid');
							flag = true;
							X.get('/ajax/checkusername.php?action=dialogsms&type='+type+'&mobile='+username);
						}
						else if(data == 'morethree')
						{
							$('#username_check').addClass('error').html('抱歉，该手机号今天尝试的次数过多，请1天后再试');
							flag = false;
						}
						else
						{
							$('#username_check').addClass('error').html('该手机号已注册，请使用该账号直接<a class="lgi" href="/account/login.php">登录</a>');
							flag = false;
						}
					}
				});	
			}
			//不是手机号码
			else
			{
				$('#username_check').addClass('error').html('手机号码格式错误');
				flag = false;
			}
		}
		else
		{
			$('#username_check').addClass('error').html('请输入手机号，以便于找回密码');
			flag = false;
		}
		return flag;
	});
	
	$('#vcaptcha').die().live('click',function(){
		$('#vcaptcha_error').html('');
	});
	
	//点击获取短信验证码弹窗的提交按钮
	$('#dialog_sms_button').die().live('click',function(){
		var mobile = $('#mobile').val();
		var vcaptcha = $('#vcaptcha').val();
		var type = $('#type').val();
		if(vcaptcha.length > 0 && vcaptcha != undefined && vcaptcha != 'undefined')
		{
			//检验验证码
			$.post('/account/checkcaptcha.php',{vcaptcha:vcaptcha},function(data){
				if(data == 'success')
				{
					if(type == 'register')
					{
						//注册发送短信
						$.post('/account/sendsms.php',{mobile:mobile},function(data){
							if(data == 'success')
							{
								time(this);
								return X.boxClose();
							}
							else
							{
								return false;
							}
						});	
					}
					else
					{
						//忘记密码发送短信
						$.post('/account/repasssendsms.php',{mobile:mobile},function(data){
							if(data == 'success')
							{
								repasstime(this);
								return X.boxClose();
							}
							else
							{
								return false;
							}
						});	
					}
				}
				else
				{
					$('#vcaptcha_error').html('<font style="color:red;">验证码错误，请重新输入</font>');
				}
			});	
		}
		else
		{
			$('#vcaptcha_error').html('<font style="color:red;">请输入验证码</font>');
		}
	});
	
	//短信验证码判断
	$('#smscode').focus(function(){
		$('#smscode').addClass('selected');
		$('#smscode_check').removeClass('error').removeClass('valid').addClass('prompt').html('请输入短信验证码');
	});
	
	$('#smscode').blur(function(){
		var mobile = $('#username').val();
		var smscode = $('#smscode').val();
		if(smscode.length > 0 && smscode != undefined && smscode != 'undefined')
		{
			$.ajax({
					type   : 'POST',
					async  : true,			//设置同步/异步访问
					url    : '/ajax/checkusername.php?action=checksmscode',
					data   : 'mobile='+mobile+'&code='+smscode,
					success:function(data)
					{
						if(data == 'success')
						{
							$('#smscode').removeClass('selected');
							$('#smscode_check').removeClass('prompt').removeClass('error').html('').addClass('valid');
							flagmsg = true;
						}
						else if(data == 'later')
						{
							$('#smscode_check').addClass('error').html('短信验证码的有效期为30分钟');
							flagmsg = false;
						}
						else
						{
							$('#smscode_check').addClass('error').html('验证码错误');
							flagmsg = false;
						}
					}
			});	
		}
		else
		{
			$('#smscode_check').addClass('error').html('请输入短信验证码');
			flagmsg = false;
		}
		return flagmsg;
	});
	
	//密码判断
	$('#password').focus(function(){
		$('#password').addClass('selected');
		$('#repeatpassword').show();
		$('#password_check').removeClass('error').removeClass('valid').addClass('prompt').html('6-16个字符，可使用字母、数字及符号的任意组合');
	});
	
	$('#password').blur(function(){
		var password = $('#password').val();
		if(password.length > 0 && password != undefined && password != 'undefined')
		{
			if(password.length < 6 || password.length > 16)
			{
				$('#password_check').addClass('error').html('密码的长度应为6-16个字符');
				flag1 = false;
			}
			else
			{
				var RegExppwd = /^(.){6,16}$/;
				if(RegExppwd.test(password))
				{
					$('#password').removeClass('selected');
					$('#password_check').removeClass('prompt').removeClass('error').html('').addClass('valid');
					flag1 = true;
				}
				else
				{
					$('#password_check').addClass('error').html('请输入正确的密码');
					flag1 = false;
				}
			}
		}
		else
		{
			$('#password_check').addClass('error').html('请输入密码');
			flag1 = false;
		}
		return flag1;
	});
	
	//确认密码判断
	$('#repassword').focus(function(){
		$('#repassword').addClass('selected');
		$('#repassword_check').removeClass('error').removeClass('valid').addClass('prompt').html('请再一次输入密码');
	});
	
	$('#repassword').blur(function(){
		var repassword = $('#repassword').val();
		var password = $('#password').val();
		if(repassword != '')
		{
			if(repassword != password)
			{
				$('#repassword_check').addClass('error').html('两次输入的密码不一致');
				flag2 = false;
			}
			else
			{
				$('#repassword').removeClass('selected');
				$('#repassword_check').removeClass('prompt').removeClass('error').html('').addClass('valid');
				flag2 = true;
			}
		}
		else
		{
			$('#repassword_check').addClass('error').html('请再一次输入密码');
			flag2 = false;
		}
		return flag2;
	});
	
	//若不同意协议，则注册按钮为不可点击状态
	$('#agree').click(function(){
		var agree = $('#agree').attr('checked');
		if(!agree)
		{
			$('#register_submit').addClass('disabled');
			$('#register_submit').attr('disabled',true);
		}
		else
		{
			$('#register_submit').removeClass('disabled');
			$('#register_submit').attr('disabled',false);
		}
	});
	
	
	$('#repassword').keydown(function(e) {
		var ev = (e || event);
		var keycode = ev.which;
		if(keycode == 13) {
			$('#register_submit').click();
		}
	});
	
	$('#vcaptcha').die().live('keydown',function(e){
		var ev = (e || event);
		var keycode = ev.which;
		if(keycode == 13) {
			$('#dialog_sms_button').click();
		}
	});
	
	//注册按钮提交
	$('#register_submit').click(function(){
		setTimeout(function(){
		var username = $('#username').val();
		var password = $('#password').val();
		var repeatpassword = $('#repeatpassword').css('display');
		//确认密码隐藏，没有显示
		if(repeatpassword == 'none')
		{
			if(!flag && !flag1)
			{
				if(!flag)
				{
					$('#username').blur();
				}
				return false;
			}
			else if(flag && !flag1)
			{
				if(!flag1)
				{
					$('#password').blur();
				}
				return false;
			}
			else if(!flag && flag1)
			{
				if(!flag)
				{
					$('#username').blur();
				}
				return false;
			}
			else if(!flag && !flag1)
			{
				if(!flag)
				{
					$('#username').blur();
				}
				return false;
			}
		}
		else
		{
			$('#username').blur();
			$('#password').blur();
			$('#repassword').blur();
			if(!flag)
			{
				if(!flag)
				{
					$('#username').blur();
				}
				return false;
			}
			else if(!flag1)
			{
				if(password == '')
				{
					$('#password').blur();
				}
				else
				{
					$('#password').focus();
					$('#password').blur();
				}
				return false;
			}
			else if(!flag2)
			{
				var repassword = $('#repassword').val();
				if(repassword == '' || repassword == '请再一次输入密码')
				{
					$('#repassword').blur();
				}
				else
				{
					$('#repassword').blur();
				}
			}
			else
			{
				var agree = $('#agree').attr('checked');
				if(!agree)
				{
					return false;
				}
				else
				{
					$('#register_submit').attr('disabled',true);
					var type = $('#username_type').val();
					var loginsns = $('#loginsns').val();
					var user_sns_id = $('#user_sns_id').val();
					$.post('/account/signup.php',{username:username,password:password,type:type,loginsns:loginsns,user_sns_id:user_sns_id},function(data){
						if(data == 'success')
						{
							window.location = '/account/signuped.php?mobile='+username;	
						}
						else
						{
							return false;
						}
					});
				}
			}
		}
		}, 100);
		
	});
	
	//注册绑定页面绑定
	$('#binding_mobile').focus(function(){
		$(this).addClass('selected');
		$('#binding_mobile_check').removeClass('error').removeClass('valid').addClass('prompt').html('请输入正确手机号');
		$('.cont').html('');
	});
	
	$('#binding_mobile').blur(function(){
		$(this).removeClass('selected');
		$('#binding_mobile_check').removeClass('prompt').html('');
	});
	
	$('#binding_password').focus(function(){
		$(this).addClass('selected');
		$('#binding_password_check').removeClass('error').removeClass('valid').addClass('prompt').html('请输入密码');
		$('.cont').html('');
	});
	
	$('#binding_password').blur(function(){
		$(this).removeClass('selected');
		$('#binding_password_check').removeClass('prompt').html('');
	});
	
	//登录判断
	$('#login_submit').click(function(){
		var username = $("input[name=username]").val();
		var password = $("input[name=password]").val();
		if(username == '' && password == '')
		{
			$('.cont').html('请输入账号和密码');
			return false;
		}
		if(username != '' && password == '')
		{
			$('.cont').html('请输入密码');
			return false;
		}
		if(username == '' && password != '')
		{
			$('.cont').html('请输入账户');
			return false;
		}
		
	});
	
	
	$('#binding_login_submit').click(function(){
		var username = $("#binding_mobile").val();
		var password = $("#binding_password").val();
		var loginsns = $('#loginsns').val();
		var user_sns_id = $('#user_sns_id').val();
		if(username == '' && password == '')
		{
			$('.cont').html('请输入手机号码和密码');
			return false;
		}
		if(username != '' && password == '')
		{
			$('.cont').html('请输入密码');
			return false;
		}
		if(username == '' && password != '')
		{
			$('.cont').html('请输入手机号码');
			return false;
		}
		$.post('/account/binding.php',{mobile:username,password:password,loginsns:loginsns,user_sns_id:user_sns_id},function(data){
		if(data == 'success')
		{
			window.location = '/account/productlist.php';
		}
		else if(data == 'noexist')
		{
			$('.cont').html('该手机号码尚未注册');
			return false;
		}
		else if(data == 'bdfail')
		{
			$('.cont').html('绑定失败');
			return false;
		}
		else
		{
			$('.cont').html('手机号码或密码错误，请重新输入');
			return false;
		}
		});
	});
	
	
	/****************个人资料页面****************/
	//昵称
	$('#personal_nickname').focus(function(){
		$('#personal_nickname').addClass('selected');
		$('#personal_nickname_check').removeClass('error').removeClass('valid').addClass('prompt').html('4-30个字符，中英文、数字、下划线和减号');
	}).blur(function(){
		var nickname = $('#personal_nickname').val();
		var uid = $('#uid').val();
		if(nickname.length > 0 && nickname != undefined && nickname != 'undefined')
		{	
			var len = 0;
			for (var i = 0; i < nickname.length; i++) {
                if (nickname.substring(i,i+1).match(/[^\x00-\xff]/ig) != null){ //全角
                    len += 2;
                }else{
                    len += 1;
                }
            }
			if(len < 4 || len > 30)
			{
				$('#personal_nickname_check').addClass('error').html('昵称长度为4-30个字符');
				$('#personal_nickname_error').val('error');
				flag3 = false;
			}
			else
			{
				var Regnick = /^([\u4E00-\uFA29]|[\uE7C7-\uE7F3]|[a-zA-Z0-9_]){2,30}$/;
				if(Regnick.test(nickname))
				{
					var nicknamehidden = $('#nickname_hidden').val();
					if(nickname != nicknamehidden)
					{
						$.ajax({
							type   : 'POST',
							async  : true,			//设置同步/异步访问
							url    : '/ajax/checkusername.php?action=checknickname',
							data   : 'nickname='+nickname+'&uid='+uid,
							success:function(data)
							{
								if(data == 'noexist'){
									$('#personal_nickname').removeClass('selected');
									$('#personal_nickname_check').removeClass('prompt').removeClass('error').html('').addClass('valid');
									flag3 = true;
								}else{
									$('#personal_nickname_check').addClass('error').html('该昵称已存在');
									$('#personal_nickname_error').val('error');
									flag3 = false;
								}
							}
						});	
					}
					else
					{
						$('#personal_nickname').removeClass('selected');
						$('#personal_nickname_check').removeClass('prompt').removeClass('error').html('').addClass('valid');
						flag3 = true;
					}
				}
				else
				{
					$('#personal_nickname_check').addClass('error').html('仅支持中文、字母、数字、下划线和减号');
					$('#personal_nickname_error').val('error');
					flag3 = false;
				}
			}
		}
		else
		{
			$('#personal_nickname').removeClass('selected');
			$('#personal_nickname_check').removeClass('prompt').removeClass('error').html('').addClass('valid');
			flag3 = true;
		}
	});
	
	//邮箱
	$('#personal_email').focus(function(){
		$('#personal_email').addClass('selected');
		$('#personal_email_check').removeClass('error').removeClass('valid').addClass('prompt').html('该邮箱用于接受订单通知，使用手机号注册的用户请务必填写，以防错过订单');
	}).blur(function(){
		var mobile = $('#personal_email').val();
		if(mobile.length > 0 && mobile != undefined && mobile != 'undefined')
		{
			var RegExpmobile = /^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/;
			if(RegExpmobile.test(mobile))
			{
				$('#personal_email').removeClass('selected');
				$('#personal_email_check').removeClass('prompt').removeClass('error').html('').addClass('valid');
				flag6 = true;
			}
			else
			{
				$('#personal_email_check').addClass('error').html('邮箱格式错误');
				$('#personal_email_error').val('error');
				flag6 = false;
			}
		}
		else
		{
			$('#personal_email').removeClass('selected');
			$('#personal_email_check').removeClass('prompt').removeClass('error').html('').addClass('valid');
			flag6 = true;
		}
	});
	
	//手机号
	$('#personal_mobile').focus(function(){
		$('#personal_mobile').addClass('selected');
		$('#personal_mobile_check').removeClass('error').removeClass('valid').addClass('prompt').html('请输入您常用的手机号码');
	}).blur(function(){
		var mobile = $('#personal_mobile').val();
		if(mobile.length > 0 && mobile != undefined && mobile != 'undefined')
		{
			var RegExpmobile = /^(1(([358][0-9])|(47)))\d{8}$/;
			if(RegExpmobile.test(mobile))
			{
				$('#personal_mobile').removeClass('selected');
				$('#personal_mobile_check').removeClass('prompt').removeClass('error').html('').addClass('valid');
				flag4 = true;
			}
			else
			{
				$('#personal_mobile_check').addClass('error').html('手机号码格式错误');
				$('#personal_mobile_error').val('error');
				flag4 = false;
			}
		}
		else
		{
			$('#personal_mobile').removeClass('selected');
			$('#personal_mobile_check').removeClass('prompt').removeClass('error').html('').addClass('valid');
			flag4 = true;
		}
	});
	//qq号
	$('#personal_qq').focus(function(){
		$('#personal_qq').addClass('selected');
		$('#personal_qq_check').removeClass('error').removeClass('valid').addClass('prompt').html('请输入您常用的qq号码');
	}).blur(function(){
		var qq = $('#personal_qq').val();
		if(qq.length > 0 && qq != undefined && qq != 'undefined')
		{
			var RegExpqq = /^\d{5,11}$/;
			if(RegExpqq.test(qq))
			{
				$('#personal_qq').removeClass('selected');
				$('#personal_qq_check').removeClass('prompt').removeClass('error').html('').addClass('valid');
				flag9 = true;
			}
			else
			{
				$('#personal_qq_check').addClass('error').html('qq号码格式错误');
				$('#personal_qq_error').val('error');
				flag9 = false;
			}
		}
		else
		{
			$('#personal_qq').removeClass('selected');
			$('#personal_qq_check').removeClass('prompt').removeClass('error').html('').addClass('valid');
			flag9 = true;
		}
	});
	
	//个人网站
	$('#personal_website').focus(function(){
		$('#personal_website').addClass('selected');
		$('#personal_website_check').removeClass('error').removeClass('valid').addClass('prompt').html('请输入您的个人网站');
	}).blur(function(){
		var website = $('#personal_website').val();
		if(website.length > 0 && website != undefined && website != 'undefined')
		{
			var RegExwebsite = /^((http|https|ftp):\/\/)?(\w(\:\w)?@)?([0-9a-z_-]+\.)*?([a-z0-9-]+\.[a-z]{2,6}(\.[a-z]{2})?(\:[0-9]{2,6})?)((\/[^?#<>\/\\*":]*)+(\?[^#]*)?(#.*)?)?$/i;
			if(RegExwebsite.test(website))
			{
				$('#personal_website').removeClass('selected');
				$('#personal_website_check').removeClass('prompt').removeClass('error').html('').addClass('valid');
				flag5 = true;
			}
			else
			{
				$('#personal_website_check').addClass('error').html('网址格式有误');
				$('#personal_website_error').val('error');
				flag5 = false;
			}
		}
		else
		{
			$('#personal_website').removeClass('selected');
			$('#personal_website_check').removeClass('prompt').removeClass('error').html('').addClass('valid');
			flag5 = true;
		}
	});
	
	$('#personal_description').artTxtCount($('.prompt'), 140);
	
	$('#personal_submit').click(function(){
		var head_error = $('#personal_head_error').val(); 
		var nickname_error = $('#personal_nickname_error').val();
		var email_error = $('#personal_email_error').val();
		var mobile_error = $('#personal_mobile_error').val();
		var qq_error = $('#personal_qq_error').val();
		var website_error = $('#personal_website_error').val();
		if(head_error == 'error')
		{
			return false;
		}
		else if(nickname_error == 'error')
		{
			$('#personal_nickname').focus();
		}
		else if(email_error == 'error')
		{
			$('#personal_email').focus();
		}
		else if(mobile_error == 'error')
		{
			$('#personal_mobile').focus();
		}
		else if(website_error == 'error')
		{
			$('#personal_website_error').focus();
		}else if(qq_error == 'error'){
			$('#personal_qq').focus();
		}
		else
		{
			var headurl = $('#artname').val();
			var nickname = $('#personal_nickname').val();
			var email = $('#personal_email').val();
			var mobile = $('#personal_mobile').val();
			var qq = $('#personal_qq').val();
			var website = $('#personal_website').val();
			var description = $('#personal_description').val();
			var uid = $('#uid').val();
			var usertype = $('#user_type').val();
			$.post('/ajax/checkusername.php?action=updatepersonal',{id:uid,headurl:headurl,nickname:nickname,email:email,mobile:mobile,website:website,description:description,usertype:usertype,qq:qq},function(data){
				if(data == 'success')
				{
					$('#submit_action').html('<span>保存成功</span>');
				}
				else
				{
					$('#submit_action').html('<span>修改失败</span>');
				}
			});
		}
	});

	/**************************更改密码******************/
	$('#ret_password').focus(function(){
		$('#ret_password').addClass('selected');
		$('#reset_password_check').removeClass('error').removeClass('valid').addClass('prompt').html('6-16个字符，可使用字母、数字及符号的任意组合');
	}).blur(function(){
		var password = $('#ret_password').val();
		if(password.length > 0 && password != undefined && password != 'undefined')
		{
			if(password.length < 6 || password.length > 16)
			{
				$('#reset_password_check').addClass('error').html('密码的长度应为6-16个字符');
				flag7 = false;
			}
			else
			{
				var RegExppwd = /^(.){6,16}$/;
				if(RegExppwd.test(password))
				{
					$('#ret_password').removeClass('selected');
					$('#reset_password_check').removeClass('prompt').removeClass('error').html('').addClass('valid');
					flag7 = true;
				}
				else
				{
					$('#reset_password_check').addClass('error').html('请输入正确的密码');
					flag7 = false;
				}
			}
		}
		else
		{
			$('#reset_password_check').addClass('error').html('请输入密码');
			flag7 = false;
		}
		return flag7;
	});
	
	$('#ret_repassword').focus(function(){
		$('#ret_repassword').addClass('selected');
		$('#reset_repassword_check').removeClass('error').removeClass('valid').addClass('prompt').html('请再一次输入密码');
	}).blur(function(){
		var repassword = $('#ret_repassword').val();
		var password = $('#ret_password').val();
		if(repassword != '')
		{
			if(repassword != password)
			{
				$('#reset_repassword_check').addClass('error').html('两次输入的密码不一致');
				flag8 = false;
			}
			else
			{
				$('#ret_repassword').removeClass('selected');
				$('#reset_repassword_check').removeClass('prompt').removeClass('error').html('').addClass('valid');
				flag8 = true;
			}
		}
		else
		{
			$('#reset_repassword_check').addClass('error').html('请再一次输入密码');
			flag8 = false;
		}
		return flag8;
	});
	
	$('#retpassword_submit').click(function(){
		$('#ret_password').blur();
		$('#ret_repassword').blur();
		if(!flag7 || !flag8)
		{
			if(!flag7)
			{
				$('#ret_password').blur();
			}
			if(!flag8)
			{
				$('#ret_repassword').blur();
			}
			return false;
		}
		else
		{
			var uid = $('#uid').val();
			var password = $('#ret_password').val();
			$.post('/ajax/checkusername.php?action=updatepassword',{id:uid,password:password},function(data){
				if(data == 'success')
				{
					$('#ret_password').val('');
					$('#ret_repassword').val('');
					$('#reset_password_check').removeClass('valid');
					$('#reset_repassword_check').removeClass('valid');
					$('#retpassword_action').html('<span>保存成功</span>');
				}
				else
				{
					$('#retpassword_action').html('<span>修改失败</span>');
				}
			});	
		}
	});
	
	//首页注册
//	$('#index_submit').click(function(){
//		var username = $('#index_username').val();
//		var password = $('#index_password').val();
//		if(username == '' || password == '')
//		{
//			window.location = '/account/login.php';
//		}
//		else
//		{
//			$.post('/ajax/checkusername.php?action=checklogin', {username:username,password:password}, function(data){
//				if(data == 'success')
//				{
//					window.location = '/account/productlist.php';
//				}
//				else
//				{
//					window.location = '/account/login.php';
//				}
//			});
//		}
//	});
	
	//意见反馈弹窗
	$('#feedback').click(function(){
		X.get('/ajax/checkusername.php?action=dialogfeedback');
	});
	
	$('#email').die().live('focus',function(){
		$('#email').addClass('selected');
		$('#email_error').html('');
	}).live('blur',function(){
		var email = $('#email').val();
		if(email.length > 0 && email != undefined && email != 'undefined')
		{
			//判断邮箱格式是否正确
			var RegExpemail = /^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/;
			if(RegExpemail.test(email))
			{
				$('#email').removeClass('selected');
				$('#email_error').removeClass('error').html('');
			}
			else
			{
				$('#email_error').addClass('error').html('邮箱格式错误');
			}
		}
		else
		{
			$('#email_error').addClass('error').html('请输入邮箱');
		}
	});
	
	
	
	
	//意见反馈信息提交
	$('#feedback_dialog_submit').die().live('click',function(){
		var islogin = $('#feedback_id').val();
		//如果没有登录，则邮箱必填，姓名选填，内容必填；如果已经登录，则内容必填
		if(islogin == 'nologin')
		{
			var email = $('#email').val();
			if(email.length > 0 && email != undefined && email != 'undefined')
			{
				//判断邮箱格式是否正确
				var RegExpemail = /^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/;
				if(RegExpemail.test(email))
				{
					$('#email').removeClass('selected');
					$('#email_error').removeClass('error').html('');
				}
				else
				{
					$('#email_error').addClass('error').html('邮箱格式错误');
					return false;
				}
			}
			else
			{
				$('#email_error').addClass('error').html('请输入邮箱');
				return false;
			}
			//判断内容
			var content = $('#content').val();
			if(content.length > 0 && content != undefined && content != 'undefined')
			{
				if(content.length > 500)
				{
					$('#feedback_ts').html('<font style="color:red;">意见内容最多500字</font>');
					return false;
				}
				else
				{
					$('#feedback_ts').html('');
				}
			}
			else
			{
				$('#feedback_ts').html('<font style="color:red;">请填写内容</font>');
				return false;
			}
			//姓名
			var username = $('#username').val();
			$.post('/help/feedback.php',{email:email,name:username,content:content,islogin:islogin},function(data){
				if(data == 'success')
				{
					return X.boxClose();	
				}
				else
				{
					return false;
				}
			});
			
		}
		else
		{
			//判断内容
			var content = $('#content').val();
			if(content.length > 0 && content != undefined && content != 'undefined')
			{
				if(content.length > 500)
				{
					$('#feedback_ts').html('<font style="color:red;">意见内容最多500字</font>');
					return false;
				}
				else
				{
					$('#feedback_ts').html('');
				}
			}
			else
			{
				$('#feedback_ts').html('<font style="color:red;">请填写内容</font>');
				return false;
			}
			$.post('/help/feedback.php',{content:content,islogin:islogin},function(data){
				if(data == 'success')
				{
					return X.boxClose();	
				}
				else
				{
					return false;
				}
			});
		}
	});
	
	//忘记密码功能
	$('#mobile').focus(function(){
		$('#mobile').addClass('selected');
		$('#mobile_check').removeClass('error').removeClass('valid').addClass('prompt').html('请输入您注册时使用的手机号');
	});
	
	$('#mobile').blur(function(){
		var mobile = $('#mobile').val();
		if(mobile.length > 0 && mobile != undefined && mobile != 'undefined')
		{
			var RegExpmobile = /^(1(([358][0-9])|(47)))\d{8}$/;
			//如果为手机号码
			if(RegExpmobile.test(mobile))
			{
				$.ajax({
					type   : 'POST',
					async  : true,			//设置同步/异步访问
					url    : '/ajax/checkusername.php?action=checkrepassmobile',
					data   : 'username='+mobile,
					success:function(data)
					{
						if(data == 'success')
						{
							$('#mobile').removeClass('selected');
							$('#mobile_check').removeClass('prompt').removeClass('error').html('').addClass('valid');
							flagrepass = true;
						}
						else if(data == 'morethree')
						{
							$('#mobile_check').addClass('error').html('抱歉，该手机号今天尝试的次数过多，请1天后再试');
							flagrepass = false;
						}
						else
						{
							$('#mobile_check').addClass('error').html('该手机号尚未注册');
							flagrepass = false;
						}
					}
				});	
			}
			else
			{
				$('#mobile_check').addClass('error').html('手机号码格式错误');
				flagrepass = false;
			}
		}
		else
		{
			$('#mobile_check').addClass('error').html('请输入您注册时使用的手机号');
			flagrepass = false;
		}
		return flagrepass;
	});
	
	$('#repass_code').blur(function(){
		var code = $('#repass_code').val();
		if(code.length > 0 && code != undefined && code != 'undefined')
		{
			$.post('/account/checkcaptcha.php',{vcaptcha:code},function(data){
				if(data == 'success')
				{
					$('#code_error').removeClass('prompt').removeClass('error').html('').addClass('valid');	
					flagcode = true;
				}
				else
				{
					$('#code_error').addClass('error').html('验证码错误，请重新输入');
					flagcode = false;
				}
			});
		}
		else
		{
			$('#code_error').addClass('error').html('请输入验证码');
			flagcode = false;
		}
		return flagcode;
	});
	
	$('#repass_button1').click(function(){
		if(!flagrepass && !flagcode)
		{
			if(!flagrepass)
			{
				$('#mobile').blur();
			}
			return false;
		}
		else if(!flagrepass && flagcode)
		{
			if(!flagrepass)
			{
				$('#mobile').blur();
			}
			return false;
		}
		else if(flagrepass && !flagcode)
		{
			if(!flagcode)
			{
				$('#repass_code').blur();
			}
			return false;
		}
		else
		{
			var mobile = $('#mobile').val();
			//发送短信
			$.post('/account/repasssendsms.php',{mobile:mobile},function(data){
				if(data == 'success')
				{
					window.location = '/account/repass2.php?mobile='+mobile;	
				}
				else
				{
					return false;
				}
			});	
		}
	});
	
	$('#repass_sms_button').die().live('click',function(){
		var mobile = $('#mobile').val();
		var type = 'repass';
		$.post('/ajax/checkusername.php?action=checkrepassmobile',{username:mobile},function(data){
			if(data == 'success')
			{
				X.get('/ajax/checkusername.php?action=dialogsms&type='+type+'&mobile='+mobile);
			}
			else if(data == 'morethree')
			{
				X.get('/ajax/checkusername.php?action=dialogrepass&mobile='+mobile);
			}
			else
			{
				return false;
			}
		});	
	});
	
	$('#repasssmscode').focus(function(){
		$('#repasssmscode').addClass('selected');
		$('#repasssmscode_check').removeClass('error').removeClass('valid').addClass('prompt').html('请填写短信验证码');
	});

	$('#repasssmscode').blur(function(){
		var mobile = $('#mobile').val();
		var smscode = $('#repasssmscode').val();
		if(smscode.length > 0 && smscode != undefined && smscode != 'undefined')
		{
			$.ajax({
					type   : 'POST',
					async  : true,			//设置同步/异步访问
					url    : '/ajax/checkusername.php?action=checkrepasssmscode',
					data   : 'mobile='+mobile+'&code='+smscode,
					success:function(data)
					{
						if(data == 'success')
						{
							$('#repasssmscode').removeClass('selected');
							$('#repasssmscode_check').removeClass('prompt').removeClass('error').html('').addClass('valid');
							flagrepasscode = true;
						}
						else if(data == 'later')
						{
							$('#repasssmscode_check').addClass('error').html('短信验证码的有效期为30分钟');
							flagrepasscode = false;
						}
						else
						{
							$('#repasssmscode_check').addClass('error').html('短信验证码错误');
							flagrepasscode = false;
						}
					}
			});	
		}
		else
		{
			$('#repasssmscode_check').addClass('error').html('请输入短信验证码');
			flagrepasscode = false;
		}
		return flagrepasscode;
	});
	
	$('#repass_button2').click(function(){
		if(!flagrepasscode)
		{
			$('#repasssmscode').blur();
		}
		else
		{
			var mobile = $('#mobile').val();
			window.location = '/account/repass3.php?mobile='+mobile;
		}
	});
	
	
	/**************************找回密码******************/
	$('#repass_password').focus(function(){
		$('#repass_password').addClass('selected');
		$('#repass_password_check').removeClass('error').removeClass('valid').addClass('prompt').html('6-16个字符，可使用字母、数字及符号的任意组合');
	}).blur(function(){
		var password = $('#repass_password').val();
		if(password.length > 0 && password != undefined && password != 'undefined')
		{
			if(password.length < 6 || password.length > 16)
			{
				$('#repass_password_check').addClass('error').html('密码的长度应为6-16个字符');
				flagretpass = false;
			}
			else
			{
				var RegExppwd = /^(.){6,16}$/;
				if(RegExppwd.test(password))
				{
					$('#repass_password').removeClass('selected');
					$('#repass_password_check').removeClass('prompt').removeClass('error').html('').addClass('valid');
					flagretpass = true;
				}
				else
				{
					$('#repass_password_check').addClass('error').html('请输入正确的密码');
					flagretpass = false;
				}
			}
		}
		else
		{
			$('#repass_password_check').addClass('error').html('请输入密码');
			flagretpass = false;
		}
		return flagretpass;
	});
	
	$('#repass_repassword').focus(function(){
		$('#repass_repassword').addClass('selected');
		$('#repass_repassword_check').removeClass('error').removeClass('valid').addClass('prompt').html('请再一次输入密码');
	}).blur(function(){
		var repassword = $('#repass_repassword').val();
		var password = $('#repass_password').val();
		if(repassword != '')
		{
			if(repassword != password)
			{
				$('#repass_repassword_check').addClass('error').html('两次输入的密码不一致');
				flagretrepass = false;
			}
			else
			{
				$('#repass_repassword').removeClass('selected');
				$('#repass_repassword_check').removeClass('prompt').removeClass('error').html('').addClass('valid');
				flagretrepass = true;
			}
		}
		else
		{
			$('#repass_repassword_check').addClass('error').html('请再一次输入密码');
			flagretrepass = false;
		}
		return flagretrepass;
	});
	
	$('#repass_button3').click(function(){
		$('#repass_password').blur();
		$('#repass_repassword').blur();
		if(!flagretpass || !flagretrepass)
		{
			if(!flagretpass)
			{
				$('#repass_password').blur();
			}
			if(!flagretrepass)
			{
				$('#repass_repassword').blur();
			}
			return false;
		}
		else
		{
			var mobile = $('#mobile').val();
			var password = $('#repass_password').val();
			$.post('/ajax/checkusername.php?action=updaterepassword',{mobile:mobile,password:password},function(data){
				if(data == 'success')
				{
					window.location = '/account/repass4.php';
				}
				else
				{
					return false;
				}
			});	
		}
	});
	
	
});

var wait=60;
var time = function(o) {
		if (wait == 0) {	
			$('#sms_vocide').html('');
			$('#sms_vocide').html('<dt><input type="button" id="sms_button" class="hqyzm" /></dt>');
			wait = 60;
		} else {
			$('#sms_button').parent().remove();
			$('#sms_vocide').html('<dd><span class="cxhq">重新获取<b>('+wait+'秒)</b></span></dd>');
			wait--;
			setTimeout(function() {
				time(o);
			},1000);
		}
	};
	
var waiter=60;
var repasstime = function(o) {
		if (waiter == 0) {	
			$('#repasscode_div').html('<input type="button" id="repass_sms_button" class="hqyzm fl" />');
			waiter = 60;
		} else {
			$('#repass_sms_button').remove();
			$('#repasscode_div').html('<span class="cxhq">重新获取<b>('+waiter+'秒)</b></span>');
			waiter--;
			setTimeout(function() {
				repasstime(o);
			},1000);
		}
	};