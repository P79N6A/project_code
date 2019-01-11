$(function(){
	var flag = flag2 = flag3 = flag4 = flag5 = flag6 = flag7 = flag8 = flag9 = flag10 = false;
	// z-sel
	$('#username').focus(function(){
		$('#username').addClass('z-sel');
	}).blur(function(){
		var username = $('#username').val();
		if(username == '' || username == null || username == undefined)
		{
			$('#admin_login_reset').text('用户名不能为空');
			flag = false;
		}
		else
		{
			$.post('/manage/login.php?action=checkusername',{username:username},function(data){
				if(data == 'exist'){					
					$('#admin_login_reset').html('');
					$('#username').removeClass('z-sel');
					flag = true;
				}else{
					$('#admin_login_reset').text('用户名不存在');
					flag = false
				}
			});
		}
		return flag;
	});
	$('#pwd').focus(function(){
		$('#pwd').addClass('z-sel');
		}).blur(function(){
		var pwd = this.value;
		if(pwd == '' || pwd == null || pwd == undefined)
		{
			$('#admin_login_reset').text('密码不能为空');
			flag2 = false;
		}
		else
		{
			$('#admin_login_reset').html('');
			$('#pwd').removeClass('z-sel');
			flag2 = true;
		}
		return flag2;
	});
	$('#checkcode').focus(function(){
		$('#checkcode').addClass('z-sel');
	}).blur(function(){
		var checkcode = this.value;
		if(checkcode == '' || checkcode == null || checkcode == undefined)
		{
			$('#admin_login_reset').text('验证码不能为空');
			flag3 = false;
		}
		else
		{
//			if(checkcode.length > 0 && checkcode != undefined && checkcode != 'undefined')
//			{
//				$.post('/manage/login.php?action=checkcode',{vcaptcha:checkcode},function(msg){
//					if(msg == 'success'){
						$('#admin_login_reset').html('');
						$('#checkcode').removeClass('z-sel');
						flag3 = true;
//					}else{
//						$('#admin_login_reset').html('验证码输入有误');
//						$('#checkcode').removeClass('z-sel');
//						flag3 = false;
//					}
//				});
//			}
			
		}
		return flag3;
	});
	$('#checkcode').die().live('keydown',function(e) {
		var ev = e || event;
		var checkco = ev.which;
		if(checkco == 13) {
			$('#checkcode').blur();
			$('#admin_login_submit').click();
		}
	});
	$('#admin_login_submit').click(function(){
		setTimeout(function(){	
			$('#username').blur();
			$('#pwd').blur();
			$('#checkcode').blur();
			if(!flag)
			{
				return false;
			}
			else if(!flag2)
			{
				return false;
			}
			else if(!flag3)
			{
				return false;
			}
			else
			{
				$('#admin_login_form').submit();
			}
		}, 100);
	});
	$('#export_excel_button').click(function(){
		$('#excel_form_button').attr('action','/manage/order.php?action=list&download=download');
	});
	$('#export_simple_button').click(function(){
		$('#excel_form_button').attr('action','/manage/order.php?action=list');
	});
	$('#export_finance_button').click(function(){
		$('#excel_form_button').attr('action','/manage/finance.php?action=list&download=download');
	});
	$('#search_finance_button').click(function(){
		$('#excel_form_button').attr('action','/manage/finance.php?action=list');
	});
	$('#admin_username').blur(function(){
		var admin_username = $('#admin_username').val();
		if(admin_username == '' || admin_username == null || admin_username == undefined)
		{
			flag4 = false;
		}
		else
		{
			flag4 = true;
		}
		return flag4;
	});
	$('#admin_realname').blur(function(){
		var admin_realname = $('#admin_realname').val();
		if(admin_realname == '' || admin_realname == null || admin_realname == undefined)
		{
			flag5 = false;
		}
		else
		{
			flag5 = true;
		}
		return flag5;
	});
	$('#admin_mobile').blur(function(){
		var admin_mobile = $('#admin_mobile').val();
		if(admin_mobile == '' || admin_mobile == null || admin_mobile == undefined)
		{
			flag6 = false;
		}
		else
		{
			flag6 = true;
		}
		return flag6;
	});
	$('#admin_email').blur(function(){
		var admin_email = $('#admin_email').val();
		if(admin_email == '' || admin_email == null || admin_email == undefined)
		{
			flag7 = false;
		}
		else
		{
			flag7 = true;
		}
		return flag7;
	});
	$('#admin_password').blur(function(){
		var username = $('#admin_password').val();
		if(username == '' || username == null || username == undefined)
		{
			flag8 = false;
		}
		else
		{
			flag8 = true;
		}
		return flag8;
	});
	$('#admin_password2').focus(function(){
	}).blur(function(){
		var admin_password2 = $('#admin_password2').val();
		if(admin_password2 == '' || admin_password2 == null || admin_password2 == undefined)
		{
			flag4 = false;
		}
		else
		{
			var pwd = $('#admin_password').val();
			if(admin_password2 == pwd){				
				flag9 = true;
			}else{
				flag9 = false;
			}
		}
		return flag9;
	});
	$('#admin_manage_add_submit').click(function(){
		$('#admin_username').blur();
		$('#admin_realname').blur();
		$('#admin_mobile').blur();
		$('#admin_email').blur();
		$('#admin_password').blur();
		$('#admin_password2').focus();
		if(!flag9 || !flag8 || !flag7 || !flag6 || !flag5 || !flag4){
			if(!flag9){
				return false;
			}
			if(!flag8){
				return false;
			}
			if(!flag7){
				return false;
			}
			if(!flag6){
				return false;
			}
			if(!flag5){
				return false;
			}
			if(!flag4){
				return false;
			}
		}else{
			$('#admin_manage_form_submit').submit();
		}
	});
	$('#admin_manage_update_submit').click(function(){
		$('#admin_username').blur();
		$('#admin_realname').blur();
		$('#admin_mobile').blur();
		$('#admin_email').blur();
		if(!flag7 || !flag6 || !flag5 || !flag4){
			if(!flag7){
				return false;
			}
			if(!flag6){
				return false;
			}
			if(!flag5){
				return false;
			}
			if(!flag4){
				return false;
			}
		}else{
			var pwd = $('#admin_password').val();
			var pwd2 = $('#admin_password2').val();
			if(pwd.length > 0 || pwd2.length > 0){
				if(pwd == pwd2){					
					$('#admin_manage_update_form').submit();
				}else{
					return false;
				}
			}else{
				$('#admin_manage_update_form').submit();
			}
		}
	});
	$('#admin_info_update_submit').click(function(){
		$('#admin_username').blur();
		$('#admin_realname').blur();
		$('#admin_mobile').blur();
		$('#admin_email').blur();
		if(!flag7 || !flag6 || !flag5 || !flag4){
			if(!flag7){
				return false;
			}
			if(!flag6){
				return false;
			}
			if(!flag5){
				return false;
			}
			if(!flag4){
				return false;
			}
		}else{
			var pwd = $('#admin_password').val();
			var pwd2 = $('#admin_password2').val();
			if(pwd.length > 0 || pwd2.length > 0){
				if(pwd == pwd2){					
					$('#admin_info_update_form').submit();
				}else{
					return false;
				}
			}else{
				$('#admin_info_update_form').submit();
			}
		}
	});
	$('#changebannersort').live('dblclick',function(){
		var id = $(this).parent().children().eq(1).html() ;
		var td = $(this);
		var textval = td.text();
		var input=$('<input type="text" maxlength="3" class="input40" value="'+textval+'"/>');
        td.html( input );  
        input.click(function(){  
            return false;  
        });
        input.trigger("focus").trigger("select");
        input.blur(function(){
        	
            var input_blur=$(this);
            var newText=input_blur.val();
            if(textval == newText){
	            td.html(textval);
	            return;
            }
            else
	            updatebannersort(id, newText, td);
             
        });
	});
	
	var updatebannersort = function(id, newText, node ){
		$.post('/manage/product.php?action=updatesort', {id:id, mealnumber:newText}, function(data){
			if(data=="success")
				node.html(newText);
			else
				return false;
		});
	};
	$('#product_del_button').click(function(){
		var chk = "";
		$("input:checkbox[name=chk]:checked").each(function(i){
			if(0==i){
				chk = $(this).val();
			}else{
				chk += (","+$(this).val());
			}
		});
		if(chk.length > 0)
		{
			if(confirm('您确定要将选中的商品放入回收站吗?')){				
				$.post('/manage/product.php?action=placedrecycle',{chk:chk},function(data){
					if(data == 'success'){
						window.location.href = window.location.href;
					}else{
						return false;
					}
				});
			}
		}
	});
	
	$('#product_ban_button').click(function(){
		var chk = "";
		$("input:checkbox[name=chk]:checked").each(function(i){
			if(0==i){
				chk = $(this).val();
			}else{
				chk += (","+$(this).val());
			}
		});
		if(chk.length > 0)
		{
			if(confirm('您确定要将选中的商品删除吗?')){				
				$.post('/manage/product.php?action=productban',{chk:chk},function(data){
					if(data == 'success'){
						window.location.href = window.location.href;
					}else{
						return false;
					}
				});
			}
		}
	});
	$('#product_shelves_button').click(function(){
		var chk = "";
		$("input:checkbox[name=chk]:checked").each(function(i){
			if(0==i){
				chk = $(this).val();
			}else{
				chk += (","+$(this).val());
			}
		});
		if(chk.length > 0)
		{
			if(confirm('您确定要将选中的商品下架吗?')){					
				$.post('/manage/product.php?action=offproduct',{chk:chk},function(data){
					if(data == 'success'){
						window.location.href = '/manage/product.php?action=list';
					}else{
						return false;
					}
				});
			}
		}
	});
	$('#product_recyclelist_del_button').click(function(){
		var chk = "";
		$("input:checkbox[name=chk]:checked").each(function(i){
			if(0==i){
				chk = $(this).val();
			}else{
				chk += (","+$(this).val());
			}
		});
		if(chk.length > 0)
		{
			if(confirm('您确定要将选中的商品彻底删除吗?')){					
				$.post('/manage/product.php?action=delproduct',{chk:chk},function(data){
					if(data == 'success'){
						window.location.href = '/manage/product.php?action=list';
					}else{
						return false;
					}
				});
			}
		}
	});
	$('#product_recycle_shelves_button').click(function(){
		var chk = "";
		$("input:checkbox[name=chk]:checked").each(function(i){
			if(0==i){
				chk = $(this).val();
			}else{
				chk += (","+$(this).val());
			}
		});
		if(chk.length > 0)
		{
			if(confirm('您确定要将选中的商品上架吗?')){				
				$.post('/manage/product.php?action=reduction',{chk:chk},function(data){
					if(data == 'success'){
						window.location.href = '/manage/product.php?action=list';
					}else{
						return false;
					}
				});
			}
		}
	});
//	$('.user_prohibited_opera').die().live('click',function(){
//		var id = $(this).parent().parent().children().eq(0).text();
//		if(confirm('您确定要禁用该用户吗？')){
//			$.post('/manage/usersmanage.php?action=prohibited',{id:id},function(data){
//				if(data == 'success'){
//					window.location.href = '/manage/usersmanage.php?action=list';
//				}else{
//					return false;
//				}
//			});
//		}
//	});
//	$('.user_enable_opera').die().live('click',function(){
//		var id = $(this).parent().parent().children().eq(0).text();
//		if(confirm('您确定要启用该用户吗？')){
//			$.post('/manage/usersmanage.php?action=enable',{id:id},function(data){
//				if(data == 'success'){
//					window.location.href = '/manage/usersmanage.php?action=recycle';
//				}else{
//					return false;
//				}
//			});
//		}
//	});
	$("#chkAll").bind("click", function () {
	    var oThis = $(this);
	    if (oThis.prop("checked") == true) {
	        $("input[name='chkpoint']").attr("checked", "checked");
	    } else {
	        $("input[name='chkpoint']").removeAttr("checked");
	    }
	});
	$("#prochkAll").bind("click", function () {
	    var oThis = $(this);
	    if (oThis.prop("checked") == true) {
	        $("input[name='chk']").attr("checked", "checked");
	    } else {
	        $("input[name='chk']").removeAttr("checked");
	    }
	});
	$('#users_recycle_button').click(function(){
		var chk = "";
		$("input:checkbox[name=chkpoint]:checked").each(function(i){
			if(0==i){
				chk = $(this).val();
			}else{
				chk += (","+$(this).val());
			}
		});
		if(chk.length > 0)
		{
			if(confirm('您确定要禁用选中的用户吗?')){				
				$.post('/manage/usersmanage.php?action=del',{chk:chk},function(data){
					if(data == 'failure'){
						return false;
					}else{
						window.location.href = '/manage/usersmanage.php?action='+data;
					}
				});
			}
		}
	});
	$('#users_enable_button').click(function(){
		var chk = "";
		$("input:checkbox[name=chkpoint]:checked").each(function(i){
			if(0==i){
				chk = $(this).val();
			}else{
				chk += (","+$(this).val());
			}
		});
		if(chk.length > 0)
		{
			if(confirm('您确定要启用选中的用户吗?')){				
				$.post('/manage/usersmanage.php?action=reenable',{chk:chk},function(data){
					if(data == 'failure'){
						return false;
					}else{
						window.location.href = '/manage/usersmanage.php?action='+data;
					}
				});
			}
		}
	});
	$('#page_url_button').click(function(){
		var url = $('#page_url_jump').val();
		var jumpurl = window.location.href;
		if(url.length > 0){
			window.location.href = jumpurl+'&page='+url;
		}
	});
	$('.models_del_button').die().live('click',function(){
		var mod = $(this);
		var id = $(this).parent().siblings().eq(0).text();
		if(confirm('确定要删除该模版吗？')){			
			$.post('/manage/modelsmanage.php?action=del',{id:id},function(data){
				if(data == 'success'){
					mod.parent().parent().remove();
				}else{
					alert('不能删除该模版');
				}
			});
		}
	});
	$('#cards_printing_button').click(function(){
		var chk = "";
		$("input:checkbox[name=chk]:checked").each(function(i){
			if(0==i){
				chk = $(this).val();
			}else{
				chk += (","+$(this).val());
			}
		});
		if(chk.length > 0)
		{
			if(confirm('您确定要印刷选中的卡吗?')){	
				$.post('/manage/cardslist.php?action=print_status',{chk:chk},function(data){
					if(data == 'success'){
						window.location.href = window.location.href;
					}else{
						return false;
					}
				});
			}
		}
	});
	$('#pring_form_button').click(function(){
		var chk = "";
		$("input:checkbox[name=chk]:checked").each(function(i){
			if(0==i){
				chk = $(this).val();
			}else{
				chk += (","+$(this).val());
			}
		});
		if(chk.length > 0)
		{
			if(confirm('您确定要购买选中的卡片吗?')){	
				$('#print_form_buy_id').val(chk);
				$('#print_form_buy').submit();
			}
		}
	});
	$('#cards_excel_button').click(function(){
		var chk = "";
		$(".table2 input:checkbox[name=chk]:checked").each(function(i){
			if(0==i){
				chk = $(this).val();
			}else{
				chk += (","+$(this).val());
			}
		});
		if(chk.length > 0)
		{
			if(confirm('您确定要导出选中的卡吗？')){
				$('#cards_excel_chk').val(chk);
				$('#cards_excel_form').attr('action','/manage/cardslist.php?action=list&download=download').submit();
			}
		}
	});
	$('#cards_excel_total_button').click(function(){
		if(confirm('您确定要导出全部卡号吗？')){
			$('#cards_excel_form').attr('action','/manage/cardslist.php?action=cards_excel_total').submit();
		}
	});
	$('#cards_excel_totalcno_button').click(function(){
		if(confirm('您确定要导出全部卡信息吗？')){
			$('#cards_excel_form').attr('action','/manage/cardslist.php?action=cards_excel_total&download=download').submit();
		}
	});
	$('#cards_check_info_button').click(function(){
		$('#cards_excel_form').attr('action','/manage/cardslist.php?action=list').submit();
	});
	
	$('#cards_search_button').click(function(){
		$('#cards_excel_form').attr('action','/manage/cardslist.php?action=purchase').submit();
	});
	$('#cards_offline_button').click(function(){
		
		var cid = $('#cards_offline_id').val();
		if(cid == '' || cid == undefined){
			alert('请确认该订单是否有效');
		}else{
			if(confirm('您确定要支付该订单吗？')){
				$.post('/manage/cardslist.php?action=offline',{id:cid},function(data){
					if(data == 'success'){
						window.location.href='/manage/cardslist.php?action=purchase';
					}else{
						alert('订单支付失败');
					}
				});
			}
		}
	});
	$('#order-pay-button').bind('click',function(){
		$('form[id="order-pay-form"]').attr( 'target' , '_blank' );
		X.get('/ajax/order.php?action=dialog&id=' + $('form[id="order-pay-form"]').attr('sid'));
		$('form[id="order-pay-button"]').submit();
	});
	$('#check_validity_button').focus(function(){
		$('#check_validity_button').click(function(){});
	});
});