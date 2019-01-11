function BankSelect(){
    var me = this;

    // 选中的银行卡
	me.oBankId = $("#bank_id");// 表单
	me.oBankSelected = $("#bank_selected");// 已选中的

	// 选择框
    me.overDiv = $('#overDiv');
	me.oBankDiv = $('#bankDiv');

	// 选择列表元素列表
	me.oBankItem = me.oBankDiv.find('a');

	// 绑定事件
	this.showBankList = __bind(this.showBankList,this);
	this.selectedBankItem = __bind(this.selectedBankItem,this);
	this.addBank = __bind(this.addBank,this);

	// 添加 元素
	me.oAddBank1 = $("#addbank1");
	me.oAddBank2 = $("#addbank2");

	// 初始化
	me.init();
}
BankSelect.prototype.init = function(){
	var me = this;
	// 添加银行卡
	me.oAddBank1.click(me.addBank);
	me.oAddBank2.click(me.addBank);

	// 显示
	me.oBankSelected.click(me.showBankList);

	// 选中某个银行卡时
	me.oBankItem.click(function(){
		me.selectedBankItem($(this));
	});
};
// 添加银行卡
BankSelect.prototype.addBank = function(){
	window.location = "/dev/bank/addcard?ispay=1";
}
// 选中某个元素
BankSelect.prototype.selectedBankItem = function(_this){
	//1 更新银行卡ID
	var book_id = _this.attr('id').replace("bank_id-","");
	this.oBankId.val(book_id);

	//2 更新显示的内容
	var html = $('#tplitem-'+book_id).html();
    this.oBankSelected.html(html);

	//3  关闭选择框
	this.hideBankList();
}
/**
 * 显示银行卡选择
 */
BankSelect.prototype.showBankList = function(){
	this.oBankDiv.show();
	this.overDiv.show();

	// 选择高亮行
	this.oBankItem.removeClass('clsgray');
	var bank_id = this.oBankId.val();
	if( bank_id ){
		var o = $("#bank_id-"+bank_id);
		o.addClass('clsgray');
	}
};
/**
 * 隐藏银行卡选择
 */
BankSelect.prototype.hideBankList = function(){
	this.oBankDiv.hide();
	this.overDiv.hide();
};
