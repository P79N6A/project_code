function showErr(id,err){
	var idstr = "#" + id + '-err';
	$(idstr).removeClass('blue-txt').addClass('blue-error-txt').html(err);
}
__bind = function(fn, me){ return function(){ return fn.apply(me, arguments); }; }
/**
 * 中英文验证
 * @param {Object} str
 */
function isValidName(str){
	var partten =/^[a-zA-Z_\u4e00-\u9fa5]+$/;
	return partten.test(str);
}
function isIdcard(str){
	str = str.toUpperCase();
	var pattern = /^\d{17}(\d|X)$/;
	if (pattern.test(str)) {
		return true;
	}

	pattern = /^\d{14}(\d|X)$/;
	if (pattern.test(str)) {
		return true;
	}

	return false;
}
function isInt(oNum){
	if(!oNum) return false;
	var strP=/^[1-9][0-9]*$/;
	if(!strP.test(oNum)) return false;
	try{
		if(parseInt(oNum)!=oNum) return false;
	}catch(ex){
	return false;
	}
	return true;
}
function isMobile( mobile ){
    if( !mobile ){
        return false;
    }
    var reg_mobile = /^(1)[0-9]{10}$/;
    if( !reg_mobile.test(mobile) ){
        return false;
    }
    return true;
}
// 对话框封装
function myDialog(config){
	config = config || {};
	var title = config.title ? config.title : '';
	var content = config.content ? config.content : '';
	var showCancel = config.cancel === false ? false : true;
	var overDiv,oHtml;

	var maskHide = function(){
		myDialog.total--;
		if( myDialog.total > 0 == false ){
			overDiv.hide();
		}
		oHtml.remove();
	}
	var maskShow = function(){
		myDialog.total++;
		overDiv.show();
		oHtml.show();
	}
    var fnOkCall = function(){
		if( config.okCall ){
			config.okCall();
		}
		if(config.autoClose){
			maskHide();
		}
	};

	// 总遮罩层
	var overDiv = $('#overDiv');
	if( !overDiv[0] ){
		overDiv = $('<div id="overDiv"></div>');
		overDiv.appendTo('body');
	}

    var html= '<div class="diolo_warp" style="display:block;">\
        <p class="title_cz">'+title+'</p>\
        <p class="tianzhun">'+content+'</p>\
        <p class="radious_img"></p>\
        <p class="go_on"></p>\
        <div class="true_flase">';
	if( showCancel ){
    	html+= '<a class="flase_qx cancel">取消</a>\
            	<button class="true_qr ok">确定</button>';
	}else{
		html+= '<button style="float:none;text-align:center;display:block;margin:0 auto;" class="true_qr ok">确定</button>';
	}

    html+=  '</div></div>';

	var oHtml = $(html);
	oHtml.appendTo('body');
	oHtml.find('.ok').click(fnOkCall);
	oHtml.find('.cancel').click(maskHide);

	maskShow();
}
myDialog.total=0;


// 弹层设置
function aAlert(title,okCall){
	myDialog({
		title:title,
		okCall: okCall ? okCall : function(){},
		autoClose:true,
		cancel:false
	});
}
function aConfirm(title,okCall){
	myDialog({
		title:title,
		okCall: okCall ? okCall : false,
		autoClose:true
	});
}
// 通用jquery.form.提交
function formSubmit(){
	var options = { 
	    beforeSubmit: this.formChk,  //提交前处理
	    success: this.formDone,  //处理完成
	    resetForm: false, 
	    type:'POST',
	    dataType:'json',
	    async:false
	}; 
	this.oForm.ajaxSubmit(options);
}
function ShowError(oErr){
	return function(err){
		err = err || '&nbsp;';
		oErr.html(err);
	};
}
