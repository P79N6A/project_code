/**
 * 借款
 */
function LoanAdd(config){
	// 初始化配置
	config = config || {};
	this.rates = config.rates ? config.rates : [];

	this.oForm = $("#loanForm");
	this.oSubmit = $("#loanSubmit");
	this.oStages = $("#stages");
	this.oBankId = $("#bank_id");
	this.oAmount = $("#amount");
	this.oMonth1 = $("#month1");
	this.oMonth3 = $("#month3");
	this.oMonth6 = $("#month6");
	this.oStageMoney = $("#stage_money");

	this._csrf = $("#_csrf").val();

	this.oErr = $("#submit-err");
	this.postData = {};

	this.formSubmit = __bind(this.formSubmit,this);
	this.formChk = __bind(this.formChk,this);
	this.formDone = __bind(this.formDone,this);
	this.showErr = __bind(this.showErr,this);

	// 执行初始化
	this.init();
};
/**
 * 绑定事件初始化
 * @param  {[type]} err [description]
 * @return {[type]}     [description]
 */
LoanAdd.prototype.init = function(){
	var me = this;
	me.oSubmit.click(me.formSubmit);
	me.oMonth1.click(function(){me.changeStage(1)} );
	me.oMonth3.click(function(){me.changeStage(3)} );
	me.oMonth6.click(function(){me.changeStage(6)} );

	me.oAmount.blur(function(){
		var amount = $(this).val();
		if( !me.chkAmount(amount) ){
			return false;
		}
		me.showStageAmount();
	});

};
// 状态改变触发操作
LoanAdd.prototype.changeStage = function(stage){
	var me = this;
	$('.active').removeClass("active");
	me["oMonth"+stage].addClass("active");

	if(!stage){
		return false;
	}
	me.oStages.val(stage);

	// 计算获取利率
	me.showStageAmount();
};
/**
 * 检测金额是否整百
 * @param  str amount
 * @return bool
 */
LoanAdd.prototype.chkAmount = function(amount){
	var me = this;
	amount = $.trim(amount);
	if(!amount){
		me.showErr('金额不能为空');
		return false;
	}

	amount = parseInt(amount,10);
	if(isNaN(amount)){
		me.showErr('金额必须是数字');
		return false;
	}

	if( amount < 10000 ){
		me.showErr('金额必须大于1万');
		return false;
	}

	if( amount > 50000 ){
		me.showErr('金额必须小于5万');
		return false;
	}
	if( amount % 100 !== 0 ){
		me.showErr('金额必须整百');
		return false;
	}

	// 检测是否是整百
	return true;
};
/**
 * 检测阶段是否设置
 * @param  int stage 阶段
 * @return bool
 */
LoanAdd.prototype.chkStage = function(stage){
	// 检测 stage是否合法
	stage = parseInt(stage,10);
	var index =  $.inArray(stage,[1,3,6]);
	var result = index != -1;
	return result;
};
/**
 * 显示利率
 */
LoanAdd.prototype.showStageAmount = function(){
	var me = this;
	var amount = me.oAmount.val();
	var stage  = me.oStages.val();
	var money  = me.getStageAmount(amount,stage);
	me.oStageMoney.val(money);
}
/**
 * 应付的利息
 * @param int amount
 * @param int stage
 * @return number 利息
 */
LoanAdd.prototype.getStageAmount = function(amount, stage){
	//1 检测金额是否整百
	amount = parseInt(amount,10);
	stage = parseInt(stage,10);
	if(!this.chkAmount(amount)){
		return '';
	}

	//2 检测 stage是否合法
	if(!this.chkStage(stage)){
		return '';
	}

	//3 返回计算的利息
	var rate = this.rates[stage];
	if( !rate ){
		return '';
	}

	//4 返回计算结果
	var interest = rate * amount * stage; // 总利息
	var money = amount + interest;
	var stage_money = money = money / stage + 0.004;// 加0.004是为了始终进位
	return stage_money.toFixed(2);
};
// 展示错误信息
LoanAdd.prototype.showErr = function(err){
	this.oErr.html(err);
};
// 检测
LoanAdd.prototype.formChk = function(){
	var me = this;
	this.showErr('&nbsp;');

	//1 检测金额是否整百
	var amount = me.oAmount.val();
	if(!this.chkAmount(amount)){
	    return false;
	}

	//4 检测 stage是否合法
	var stages = me.oStages.val();
	if(!this.chkStage(stages)){
		this.showErr('请选择期数');
		return false;
	}

	//3 检测 利率是否计算
	if(!this.oStageMoney.val()){
		this.showErr('请完善信息');
		return false;
	}

	//3 检测 stage是否合法
	var bank_id = me.oBankId.val();
	if(!bank_id){
		this.showErr('请选择提现卡');
		return false;
	}



	return true;
};
LoanAdd.prototype.formSubmit = function(){
	var me = this;
	// 验证参数
	if(!me.formChk()){
		return false;
	}

	var data = {
		'bank_id' : me.oBankId.val(),
		'amount' : me.oAmount.val(),
		'stages' : me.oStages.val(),
		'stage_money' : me.oStageMoney.val(),
		'_csrf' : me._csrf,
	};
	$.ajax({
		type : "POST",
		url  : "/dev/loan/saveloan",
		data : data,
		dataType : "json",
		async    : false,
		success  : this.formDone,
	});
	return true;
};
LoanAdd.prototype.formDone = function(data){
	if(data && parseInt(data.res_code,10) === 0){
		window.location="/dev/loandetail/?id="+data.res_data;
	}else{
		this.showErr(data.res_data);
	}
};
