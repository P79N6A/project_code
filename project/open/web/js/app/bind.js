// JavaScript 绑定银行卡
$(function(){
		$('#no').keydown(function(event){
				var code=event.keyCode
				if (event.shiftKey){
					return false
				}
				if((code>=96&&code<=105)||((!event.shiftKey)&&code>=48&&code<=57)||code<57){
					return true

				}
				return false
		})
		$('#no').keyup(function(){
			var lenth = $(this).val().length
			if( lenth == 4){
				//alert('长度到4了')
			}
			//$(this).val($(this).val().replace(/\s(?=\d)/g,'').replace(/(\d{4})(?=\d)/g,"$1 "))
		})
		$('#no').blur(function(){
			//alert($(this).val())
			var bankno = $(this).val()
			$.post("/app/bind/cardinfo",{bankno:bankno},function(result){
				if( result != 'error'){
				var obj = eval(result)
				var bank_name = obj[0].bank_name
				$("#bank").html(bank_name)
				$("#type").val(obj[0].card_type)
				$("#bank_abbr").val(obj[0].bank_abbr)
				$("#bank_name").val(obj[0].bank_name)
				$('#is_true').val('1') ;
				}else{
					$("#bank").html('卡号有误')
				}
			  });
		})
		
	$("#bindbutton").click(function(){
		var bankno = $("#no").val();
		if( bankno.length <15 || $('#is_true').val() == '0' ){
			alert('请输入正确的银行卡号');
			$("#no").focus();
			return false;
		}
		if( $('#user_bank-province').val() == ''){
			alert('请选择省');
			return false;
		}
		if( $('#user_bank-city').val() == '0'){
			alert('请选择市');
			return false;
		}
		if( $('#sub_bank').val() == ''){
			alert('请输入支行信息');
			return false;
		}
		$("#bind-form").submit()
		//alert($("#province").val());		
	});
});
$(document).ready(function(){
	$("select[name='User_bank[province]']").change(function(){
		$("select[name='User_bank[city]']").html("<option value='0'>市</option>");
		var v = $(this).val();
		if(!v) return  ;
		 $.getJSON("/app/bind/getcity",{'pid':v},function(json){
			 var htmlOption = "";//"<option value='0'>市</option>";
			 $.each(json,function(i,item){
				 htmlOption+="<option value='"+item.id+"'>"+item.name+"</option>";
			 })
			 $("select[name='User_bank[city]']").html(htmlOption);
		}); 
		
	});
	
});
