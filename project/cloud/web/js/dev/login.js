/**
 * 登录form
 */
function LoginForm(){
	this.formName = 'loginForm';
	this.oMobile = $("#mobile");
	this.oCode = $("#code");
	this.oGetCode = $("#getCode");
	this.oGetCode2 = $("#getCode2");
	this.oFromMobile = $("#from_mobile");
	this.oErr = $("#login-err");
	this.postData = {};
	this._csrf = $("#_csrf").val();
	
	this.formSubmit = __bind(this.formSubmit,this);
	this.formChk = __bind(this.formChk,this);
	this.formDone = __bind(this.formDone,this);
	this.showErr = __bind(this.showErr,this);
	this.getCode = __bind(this.getCode,this);
	this.timedec = __bind(this.timedec,this);
	
	// 执行初始化
	this.init();
};
LoginForm.prototype.init = function(err){
	$("#loginSubmit").click(this.formSubmit);
	this.oGetCode.click(this.getCode);
};
LoginForm.prototype.showErr = function(err){
	err = err || '&nbsp;';
	this.oErr.html(err);
};
LoginForm.prototype.formChk = function(){
	this.showErr();
	// 手机验证
	var mobile = $.trim(this.oMobile.val());
	if( !mobile ){
		this.showErr('请填写手机号');
	    return false;
	}
	if(!isMobile(mobile)){
		this.showErr('手机号不合法');
		return false;
	}
	
	// 短信验证码
	var code = $.trim(this.oCode.val());
	if( !code ){
		this.showErr('请填写验证码');
	    return false;
	}
	
	// 邀请人
	var from_mobile = $.trim(this.oFromMobile.val());
	if( from_mobile ){
		if(!isMobile(from_mobile)){
			this.showErr('邀请人必须是手机号');
			 return false;
		}
		if(from_mobile == mobile ){
			this.showErr('邀请人不能是自己');
			 return false;
		}
	}
	
	// 设置提交的数据
	this.postData = {
	  	"mobile":mobile,
	  	"from_mobile":from_mobile,
	  	"code":code,
	};
	return true;
};
LoginForm.prototype.formSubmit = function(){
	// 验证参数
	if(!this.formChk()){
		return false;
	}
	
	var data = this.postData;
	data._csrf = this._csrf;
	$.ajax({
		type : "POST",
		url  : "",
		data : data,
		dataType : "json",
		async    : false,
		success  : this.formDone,
	});
	return true;
};
LoginForm.prototype.formDone = function(data){
	if(data && parseInt(data.res_code,10) === 0){
		window.location="/dev/user";
	}else{
		this.showErr(data.res_data);
	}	
};
/**
 * 获取验证码
 */
LoginForm.prototype.getCode = function(){
	var me = this;
	// 手机验证
	var mobile = $.trim(this.oMobile.val());
	if( !mobile ){
		this.showErr('请填写手机号');
	    return false;
	}
	if(!isMobile(mobile)){
		this.showErr('手机号不合法');
		return false;
	}
	
	// 倒计时开始
	this.timedec();
	
	// 发送ajax请求
	var data = {
		mobile:mobile,
		_csrf : me._csrf
	};
	$.ajax({
		type : "POST",
		url  : "code",
		data : data,
		dataType : "json",
		async    : false,
		success  : function(data){
			if(data && parseInt(data.res_code,10) === 0){
			}else{
				me.showErr(data.res_data);
			}
		},
	});
	return true;
};
/**
 * 倒计时功能
 */
LoginForm.prototype.timedec = function(){
	var me = this;
	me.oGetCode.hide();
	me.oGetCode2.show();
	var t = 60;
	var txt = '';
	
	// 倒计时
	var run = function(){
		t--;
		txt = '还剩' + t + 's';	
		me.oGetCode2.html(txt);	
		if(t>0){
			setTimeout(function(){
				run();
			}, 1000 );
		}else{
			me.oGetCode2.hide();
			me.oGetCode.show();
		}
	};
	
	// 立即执行
	run();
};

$(function(){
	new LoginForm();
});