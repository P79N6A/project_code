// JavaScript Document
$(function(){
	$(".coupon_apply_audit").bind('click',function(){
		var cid = $(this).attr('cid');
		var audittype = $(this).attr('atype');
		if( audittype == 'pass' ){
			var tiptext = "确认审核通过？";
		}else{
			var tiptext = "确认审核驳回？";
		}
		$.Zebra_Dialog(tiptext, {
		    'type':     'question',
		    'title':    '审核',
		    'buttons':  [
		                    {caption: '取消', callback: function() {}},
		                    {caption: '确定', callback: function() {
		                    	$.post("/backend/coupon/audit",{cid:cid,type:audittype},function(result){
			                    	var data = eval('('+result+')');
			                    	if( data.ret == '0' ){
										window.location = '/backend/coupon';
			                    	}else if( data.ret == '1'){
			                    		window.location = '/backend/coupon';
			                    	}else {
			                    		window.location = '/backend/coupon';
			                    	}
		                    	});
			                }},
		                ]
		});
	});
	$(".coupon_income_apply_audit").bind('click',function(){
		var cid = $(this).attr('cid');
		var audittype = $(this).attr('atype');
		if( audittype == 'pass' ){
			var tiptext = "确认审核通过？";
		}else{
			var tiptext = "确认审核驳回？";
		}
		$.Zebra_Dialog( tiptext, {
		    'type':     'question',
		    'title':    '审核',
		    'buttons':  [
		                    {caption: '取消', callback: function() {}},
		                    {caption: '确定', callback: function() {
		                    	$.post("/backend/coupon/ic_audit",{cid:cid,type:audittype},function(result){
			                    	var data = eval('('+result+')');
			                    	if( data.ret == '0' ){
										window.location = '/backend/coupon/ic';
			                    	}else if( data.ret == '1'){
			                    		window.location = '/backend/coupon/ic';
			                    	}else {
			                    		window.location = '/backend/coupon/ic';
			                    	}
		                    	});
			                }},
		                ]
		});
	});
});