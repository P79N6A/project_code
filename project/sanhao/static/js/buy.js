var flagname = flagmobile = flagphone = flagarea = flagstreet = flagzip = false;
var buystr = {};
$(function(){
	//商品详情页点击购买按钮
	$('#product_buy').click(function(){
		var pid = $(this).attr('pid');
		//获取商品详情页购买的数量
		var buynum = $('#buy_num').val();
		//获取选中的商品属性
		var propertyfirst = $('.select').eq(0).find('option:selected').text();
		var propertysecond = $('.select').eq(1).find('option:selected').text();

		//在保存之前先将以前在COOKIE中的信息清空
		$.cookie('buynum', null, { path: '/' });
		$.cookie('propertyfirst', null, { path: '/' });
		$.cookie('propertysecond', null, { path: '/' });
		//将商品的属性和购买的数量保存在COOKIE中
		var date = new Date();
      	date.setTime(date.getTime() + (60*60*1000));
        $.cookie('buynum', buynum, { path: '/', expires: date });
        if(propertyfirst != '' && propertyfirst != undefined)
        {
        	$.cookie('propertyfirst', propertyfirst, { path: '/', expires: date });
        }
        if(propertysecond != '' && propertysecond != undefined)
        {
        	$.cookie('propertysecond', propertysecond, { path: '/', expires: date });
        }
		$.post('/ajax/checkusername.php?action=checkislogin', {}, function(data){
			if(data == 'yeslogin')
			{
				//判断该商品是否是点击者发布的商品
				$.post('/ajax/checkusername.php?action=checkauthor', {id:pid}, function(json){
					if(json == 'isauthor')
					{
						alert('这是您自己的商品哦');
						return false;
					}
					else
					{
						window.location = '/order/buy.php?id='+pid;
					}
				});
			}
			else if(data == 'nologin')
			{
				X.get('/ajax/checkusername.php?action=dialoglogin&id='+pid);
			}
			else
			{
				window.location = '/account/register.php?id='+data;
			}
		});
	});
	
	
	//登录弹窗登录
	$('#login_dialog_submit').die().live('click',function(){
		var username = $("input[name=username]").val();
		var password = $("input[name=password]").val();
		var pid = $('#product_id').val();
		var pdetail = $('#product_detail').val();
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
		$.post('/ajax/checkusername.php?action=checklogin', {username:username,password:password}, function(data){
			if(data == 'noexist')
			{
				$('.cont').html('该邮箱\手机号码尚未注册');
			}
			else if(data == 'fail')
			{
				$('.cont').html('账号或密码错误，请重新输入');
			}
			else
			{
				//判断该商品是否是点击者发布的商品
				$.post('/ajax/checkusername.php?action=checkauthor', {id:pid}, function(json){
					if(json == 'isauthor')
					{
						window.location = '/account/productdetail.php?id='+pid;
					}
					else if(json == 'nobindicardpay')
					{
						window.location = '/account/productdetail.php?id='+pid;
					}
					else
					{
						if(pdetail != null && pdetail != undefined && pdetail != '' && pdetail == 'detail'){
							window.location = '/account/productdetail.php?id='+pid;
						}else{							
							window.location = '/order/buy.php?id='+pid;
						}
					}
				})
			}
		});
		
	});
	
	//点击左侧的减少
	$('#leftLessen').click(function(){
		//获取输入框内的数量
		var buynum = $('#buy_product_num').val();
		//获取商品的单价
		var price = parseFloat($('#product_price').html());
		//获取运费价格
		var express = $('#product_express').html();
		var productexpress = parseFloat(express.substr(1));
		reduce_num = parseInt(buynum)-1;
		if(reduce_num == 0)
		{
			reduce_num = 1;
		}
		//计算总价格
		var totalprice = price*reduce_num+productexpress;
		//将商品数量保存在COOKIE中
		var date = new Date();
      	date.setTime(date.getTime() + (60*60*1000));
        $.cookie('buynum', reduce_num, { path: '/', expires: date });
		$('#buy_product_num').val(reduce_num);
		$('#product_prompt').html('');
		$('#total_price').html('￥'+totalprice.toFixed(2));
	});
	
	//点击右侧的添加
	$('#rightAdd').click(function(){
		//获取输入框内的数量
		var buynum = $('#buy_product_num').val();
		var add_num = parseInt(buynum)+1;
		var max_num = $('#product_max_num').attr('num');
		if(max_num != '不限')
		{
			if(add_num > parseInt(max_num) && parseInt(max_num) > 0)
			{
				add_num = parseInt(max_num);
				$('#product_prompt').html('库存仅有'+add_num+'件');
			}
		}
		//获取商品的单价
		var price = parseFloat($('#product_price').html());
		//获取运费价格
		var express = $('#product_express').html();
		var productexpress = parseFloat(express.substr(1));
		//计算总价格
		var totalprice = price*add_num+productexpress;
		//将商品数量保存在COOKIE中
		var date = new Date();
      	date.setTime(date.getTime() + (60*60*1000));
        $.cookie('buynum', add_num, { path: '/', expires: date });
		$('#buy_product_num').val(add_num);
		$('#total_price').html('￥'+totalprice.toFixed(2));
	});
	
	//输入框内输入购买的数量
	$('#buy_product_num').bind("keyup", function(){
		var buynum = parseInt($(this).val());
		$('#product_prompt').html('');
		//可以购买的最大数量
		var max_num = $('#product_max_num').attr('num');
		//可以购买的最小数量
		var min_num = 1;
		buynum = isNaN(buynum) ? 1 : buynum;
		if(max_num != '不限')
		{
			max_num = isNaN(parseInt(max_num)) ? 1 : parseInt(max_num);
			if(buynum > parseInt(max_num) && parseInt(max_num) > 0)
			{
				buynum = parseInt(max_num);
				$('#product_prompt').html('库存仅有'+max_num+'件');
			}
		}
		if(buynum < min_num)
		{
			buynum = min_num;
		}
		//获取商品的单价
		var price = parseFloat($('#product_price').html());
		//获取运费价格
		var express = $('#product_express').html();
		var productexpress = parseFloat(express.substr(1));
		//计算总价格
		var totalprice = price*buynum+productexpress;
		//将商品数量保存在COOKIE中
		var date = new Date();
      	date.setTime(date.getTime() + (60*60*1000));
        $.cookie('buynum', buynum, { path: '/', expires: date });
		$(this).val(buynum);
		$('#total_price').html('￥'+totalprice.toFixed(2));
	});
	
	//购买页面点击商品属性第一个下拉菜单
	$('#product_property_first').change(function(){
		var property_first = $(this).val();
		var property_second = $('#product_property_second').val();
		if(property_second != undefined)
		{
			var property = property_first+'，'+property_second;
		}
		else
		{
			var property = property_first;
		}
		//将商品的属性保存在COOKIE中
		var date = new Date();
      	date.setTime(date.getTime() + (60*60*1000));
        $.cookie('propertyfirst', property_first, { path: '/', expires: date });
		$('#product_property').html(property);
	});
	
	//购买页面点击商品属性第二个下拉菜单
	$('#product_property_second').change(function(){
		var property_second = $(this).val();
		var property_first = $('#product_property_first').val();
		if(property_first != undefined)
		{
			var property = property_first+'，'+property_second;
		}
		else
		{
			var property = property_second;
		}
		//将商品的属性保存在COOKIE中
		var date = new Date();
      	date.setTime(date.getTime() + (60*60*1000));
        $.cookie('propertysecond', property_second, { path: '/', expires: date });
		$('#product_property').html(property);
	});
	
	//收货地址页面获取省份下对应的城市
	$('#showprovince').change(function(){
		var id = $(this).val();
		$('#showcity').html('');
		$('#showarea').html("<option value='0'>请选择地区</option>");
		$('#checkprovince').removeClass('error').removeClass('valid');
		flagarea = false;
		$.get('/ajax/checkusername.php?action=getcity' , {id : id} , function(data){
			if(id != 0)
			{
				$('#checkprovince').html('');
				$('#checkprovince').removeClass('error');
			}
			else
			{
				$('#showcity').html("<option value='0'>请选择城市</option>");
			}
			var json = eval( '(' + data + ')') ;
			var city = $('#showcity');
			if(json.error == 0)
			{
				var choosecity = "<option value='0'>请选择城市</option>";
				var option = '';
				$.each(json.data,function(key,name){
					option = option + "<option value='"+name['id']+"'>"+name['name']+"</option>";
				});
				city.append(choosecity + option);
			}
			else
			{
				$('#checkprovince').addClass('error').html('请选择省份');
				flagarea = false;
			}
		});
	});
	
	//收货地址页面获取对应城市下的地区
	$('#showcity').die().live('change',function(){
		var id = $(this).val();
		$('#showarea').html('');
		$('#checkprovince').removeClass('error').removeClass('valid');
		flagarea = false;
		$.get('/ajax/checkusername.php?action=getarea' , {id : id} , function(data){
			if(id != 0)
			{
				$('#checkprovince').html('');
				$('#checkprovince').removeClass('error');
			}
			else
			{
				$('#showarea').html("<option value='0'>请选择地区</option>");
			}
			var json = eval( '(' + data + ')') ;
			var area = $('#showarea');
			if(json.error == 0)
			{
				var choosearea = "<option value='0'>请选择地区</option>";
				var option = '';
				$.each(json.data,function(key,name){
					option = option + "<option value='"+name['id']+"'>"+name['name']+"</option>";
				});
				area.append(choosearea + option);
			}
			else
			{
				$('#checkprovince').addClass('error').html('请选择城市');
				flagarea = false;
			}
		});
	});
	
	//收货地址页面地区选择
	$('#showarea').die().live('change',function(){
		flagarea = true;
		$('#checkprovince').removeClass('error').html('').addClass('valid');
	});
	
	//收货人姓名
	$('#address_name').focus(function(){
		$(this).addClass('selected');
		$('#address_name_check').removeClass('error').removeClass('valid').html('');
	}).blur(function(){
		var addressname = $(this).val();
		if(addressname.length > 0 && addressname != undefined && addressname != 'undefined')
		{
			var len = 0;
			for (var i = 0; i < addressname.length; i++)
			{
                if (addressname.substring(i,i+1).match(/[^\x00-\xff]/ig) != null) //全角
                    len += 2;
                else
                    len += 1;
            }
            if(len < 4 || len > 30)
			{
				$('#address_name_check').addClass('error').html('收货人姓名需控制在2-15个字以内');
				flagname = false;
			}
			else
			{
				$(this).removeClass('selected');
				$('#address_name_check').removeClass('error').html('').addClass('valid');
				flagname = true;
			}
		}
		else
		{
			$('#address_name_check').addClass('error').html('请填写收货人姓名');
			flagname = false;
		}
	});
	
	//手机号码
	$('#address_mobile').focus(function(){
		$(this).addClass('selected');
		$('#address_mobile_check').removeClass('error').removeClass('valid').addClass('prompt').html('手机号码应为11位');
	}).blur(function(){
		var mobile = $(this).val();
		if(mobile.length > 0 && mobile != undefined && mobile != 'undefined')
		{
			var RegExpmobile = /^(1(([358][0-9])|(47)))\d{8}$/;
			if(RegExpmobile.test(mobile))
			{
				$(this).removeClass('selected');
				$('#address_mobile_check').removeClass('error').removeClass('prompt').html('').addClass('valid');
				flagmobile = true;
			}
			else
			{
				$('#address_mobile_check').addClass('error').html('格式有误');
				flagmobile = false;
			}
		}
		else
		{
				if(!flagmobile && !flagphone)
				{
					$(this).removeClass('selected');
					$('#address_mobile_check').addClass('error').html('手机和固定电话请至少填写一项');
					flagmobile = false;
				}
				else if(flagmobile && !flagphone)
				{
					$(this).removeClass('selected');
					$('#address_mobile_check').addClass('error').html('手机和固定电话请至少填写一项');
					flagmobile = false;
				}
				else
				{
					$(this).removeClass('selected');
					$('#address_mobile_check').removeClass('error').removeClass('prompt').html('');
					flagmobile = false;
				}
		}
	});
	
	//固定电话
	$('#address_phone').focus(function(){
		$(this).addClass('selected');
		$('#address_phone_check').removeClass('error').removeClass('valid').addClass('prompt').html('固定电话格式示例：010-68688888');
	}).blur(function(){
		var phone = $(this).val();
		if(phone.length > 0 && phone != undefined && phone != 'undefined')
		{
			var Regtelphone = /^0\d{2,3}\-\d{7,8}$/;
			if(Regtelphone.test(phone))
			{
				$(this).removeClass('selected');
				$('#address_mobile_check').removeClass('error').removeClass('prompt').html('');
				$('#address_phone_check').removeClass('error').removeClass('prompt').html('').addClass('valid');
				flagphone = true;
			}
			else
			{
				$('#address_phone_check').addClass('error').html('格式有误');
				flagphone = false;
			}
		}
		else
		{
				if(!flagmobile && !flagphone)
				{
					$(this).removeClass('selected');
					$('#address_mobile_check').addClass('error').html('手机和固定电话请至少填写一项');
					$('#address_phone_check').removeClass('error').removeClass('prompt').html('');
					flagphone = false;
				}
				else if(!flagmobile && flagphone)
				{
					$(this).removeClass('selected');
					$('#address_mobile_check').addClass('error').html('手机和固定电话请至少填写一项');
					$('#address_phone_check').removeClass('error').removeClass('prompt').html('');
					flagphone = false;
				}
				else
				{
					$(this).removeClass('selected');
					$('#address_phone_check').removeClass('error').removeClass('prompt').html('');
					flagphone = false;
				}
		}
	});
	
	//街道号
	$('#address_street').focus(function(){
		$(this).addClass('selected');
		$('#address_street_check').html('');
	}).blur(function(){
		var street = $(this).val();
		if(street.length > 0 && street != undefined && street != 'undefined')
		{
			var len = 0;
			for (var i = 0; i < street.length; i++)
			{
                if (street.substring(i,i+1).match(/[^\x00-\xff]/ig) != null) //全角
                    len += 2;
                else
                    len += 1;
            }
            if(len < 4 || len > 120)
            {
            	$('#address_street_check').html('街道号长度需控制在2-60个字以内');
				flagstreet = false;
            }
            else
            {
            	$(this).removeClass('selected');
				$('#address_street_check').html('');
				flagstreet = true;
            }
		}
		else
		{
			$('#address_street_check').html('请填写收货地址');
			flagstreet = false;
		}
	});
	
	//邮政编码
	$('#address_zipcode').focus(function(){
		$(this).addClass('selected');
		$('#address_zipcode_check').removeClass('error').removeClass('valid').html('');
	}).blur(function(){
		var zipcode = $(this).val();
		if(zipcode.length > 0 && zipcode != undefined && zipcode != 'undefined')
		{
			var Regzip = /^\d{6}$/;
			if(Regzip.test(zipcode))
			{
				$(this).removeClass('selected');
				$('#address_zipcode_check').removeClass('error').html('').addClass('valid');
				flagzip = true;
			}
			else
			{
				$('#address_zipcode_check').addClass('error').html('格式有误');
				flagzip = false;
			}
		}
		else
		{
			$('#address_zipcode_check').addClass('error').html('请填写邮政编码');
			flagzip = false;
		}
	});
	
	//收货地址信息提交
	$('#address_submit').click(function(){
		var addressid = $('#address_id').val();
		//如果为空，则添加，否则修改
		if(addressid != '')
		{
			$('#address_name').blur();
			$('#address_mobile').blur();
			$('#address_phone').blur();
			$('#address_street').blur();
			$('#address_zipcode').blur();
			var addressprovince = $('#showprovince').val();
			var addresscity = $('#showcity').val();
			var addressarea = $('#showarea').val();
			if(addressprovince != 0 && addresscity != 0 && addressarea != 0)
			{
				flagarea = true;
				$('#checkprovince').removeClass('error').addClass('valid').html('');
			}
			else
			{
				flagarea = false;
			}
		}
		if(!flagname)
		{
			$('#address_name').blur();
			return false;
		}
		else
		{
			$('#address_name_check').removeClass('error');
		}
		if(!flagmobile && !flagphone)
		{
			$('#address_mobile').blur();
			return false;
		}
		else if(flagmobile && !flagphone)
		{
			if($('#address_phone').val() != '')
			{
				$('#address_phone').blur();
				return false;
			}
		}
		else if(!flagmobile && flagphone)
		{
			if($('#address_mobile').val() != '')
			{
				$('#address_mobile').blur();
				return false;
			}
		}
		else
		{
			$('#address_mobile_check').removeClass('prompt').removeClass('error').html('');
		}
		if(!flagarea)
		{
			$('#checkprovince').addClass('error').html('请选择省市区');
			return false;
		}
		else
		{
			$('#checkprovince').removeClass('error');
		}
		if(!flagstreet)
		{
			$('#address_street').blur();
			return false;
		}
		else
		{
			$('#address_street_check').html('');
		}
		if(!flagzip)
		{
			$('#address_zipcode').blur();
			return false;
		}
		else
		{
			$('#address_zipcode_check').removeClass('error');
		}
		var addressname = $('#address_name').val();
		var addressmobile = $('#address_mobile').val();
		var addressphone = $('#address_phone').val();
		var addressprovince = $('#showprovince').val();
		var addresscity = $('#showcity').val();
		var addressarea = $('#showarea').val();
		var addressstreet = $('#address_street').val();
		var addresszip = $('#address_zipcode').val();
		if(addressmobile == '请填写您的手机号码')
		{
			addressmobile = '';
		}
		if(addressphone == '请填写您的固定电话')
		{
			addressphone = '';
		}
		$.post('/ajax/checkusername.php?action=updatepersonaladdress',{id:addressid,name:addressname,mobile:addressmobile,phone:addressphone,province:addressprovince,city:addresscity,area:addressarea,street:addressstreet,zip:addresszip},function(data){
			if(data == 'success')
			{
				$('#address_action').html('<span>保存成功</span>');
				$('#address_name_check').removeClass('valid');
				$('#address_mobile_check').removeClass('valid');
				$('#address_phone_check').removeClass('valid');
				$('#checkprovince').removeClass('valid');
				$('#address_zipcode_check').removeClass('valid');
			}
			else
			{
				$('#address_action').html('<span>保存失败</span>');
			}
		});
	});
	
	$('#address_buyer').artTxtCount($('#buyer_prompt'), 200);
	$('#address_buyer').blur(function(){
		$('#buyer_prompt').html('');
	});
	
	//商品购买页面点击购买按钮
	$('#product_buy_submit').click(function(){
		//地址的ID
		var address_id = $('#address_id').val();
		//判断用户是否有地址
		if(address_id != '')
		{
			$('#address_name').blur();
			$('#address_mobile').blur();
			$('#address_phone').blur();
			$('#address_street').blur();
			$('#address_zipcode').blur();
			var addressprovince = $('#showprovince').val();
			var addresscity = $('#showcity').val();
			var addressarea = $('#showarea').val();
			if(addressprovince != 0 && addresscity != 0 && addressarea != 0)
			{
				flagarea = true;
				$('#checkprovince').removeClass('error').addClass('valid').html('');
			}
			else
			{
				flagarea = false;
			}
		}
		if(!flagname)
		{
			$('#address_name').blur();
			return false;
		}
		else
		{
			$('#address_name_check').removeClass('error');
		}
		if(!flagmobile && !flagphone)
		{
			$('#address_mobile').blur();
			return false;
		}
		else
		{
			$('#address_mobile_check').removeClass('prompt').removeClass('error').html('');
		}
		if(!flagarea)
		{
			$('#checkprovince').addClass('error').html('请选择省市区');
			return false;
		}
		else
		{
			$('#checkprovince').removeClass('error');
		}
		if(!flagstreet)
		{
			$('#address_street').blur();
			return false;
		}
		else
		{
			$('#address_street_check').html('');
		}
		if(!flagzip)
		{
			$('#address_zipcode').blur();
			return false;
		}
		else
		{
			$('#address_zipcode_check').removeClass('error');
		}
		//获取商品的ID
		var product_id = $('#product_id').val();
		//获取卖家的ID
		var saler_id = $('#saler_id').val();
		//获取订单那的ID
		var order_id = $('#order_id').val();
		//获取购买的数量
		var buy_num = $('#buy_product_num').val();
		//获取商品价格
		var product_price = parseFloat($('#product_price').html());
		//获取商品的属性
		var product_property = $('#product_property').html();
		//获取快递的费用
		var express = $('#product_express').html();
		var product_express = parseFloat(express.substr(1));
		//获取总费用
		var total_price = $('#total_price').html().substr(1);
		//获取地址信息
		var address_name = $('#address_name').val();
		var address_mobile = $('#address_mobile').val();
		var address_phone = $('#address_phone').val();
		var address_province = $('#showprovince').val();
		var address_city = $('#showcity').val();
		var address_area = $('#showarea').val();
		var address_street = $('#address_street').val();
		var address_zip = $('#address_zipcode').val();
		//获取买家留言
		var address_buyer = $('#address_buyer').val();
		//将所有获取的数据组成一个数组，然后以JSON格式的数据传给PHP文件
		buystr.product_id = product_id;
		buystr.address_id = address_id;
		buystr.saler_id = saler_id;
		buystr.order_id = order_id;
		buystr.buy_num = buy_num;
		buystr.product_price = product_price;
		buystr.product_property = product_property;
		buystr.product_express = product_express;
		buystr.total_price = total_price;
		buystr.address_name = address_name;
		buystr.address_mobile = address_mobile;
		buystr.address_phone = address_phone;
		buystr.address_province = address_province;
		buystr.address_city = address_city;
		buystr.address_area = address_area;
		buystr.address_street = address_street;
		buystr.address_zip = address_zip;
		buystr.address_buyer = address_buyer;
		var buystrinformation = $.toJSON(buystr);
		$.post('/ajax/checkusername.php?action=addorder', {buystrinformation:buystrinformation}, function(data){
			if(data == 'fail')
			{
				alert('商品购买失败');
				return false;
			}
			else
			{
				//此时将COOKIE中的信息清除
				$.cookie('buynum', null, { path: '/' });
				$.cookie('propertyfirst', null, { path: '/' });
				$.cookie('propertysecond', null, { path: '/' });
				window.location = '/order/check.php?id='+data;
			}
		});
	});
	
	$('#jxk_pay_mobile').blur(function(){
		$('#jxk_pay_mobile_error').removeClass('error').html('');
	});
	
	//点击支付按钮
	$('#order-pay-button').bind('click',function(){
		var paytype = $("input[name='paytype']:checked").val() ;
		if( paytype == undefined || paytype == null || paytype == "" ){
			alert("请选择支付方式！");
			return false;
		}
		else
		{
			if(paytype == 'jxk')
			{
				//判断是否填写手机号以及手机号格式是否正确	
				var jxk_pay_mobile = $('#jxk_pay_mobile').val();
				if(jxk_pay_mobile == '' || jxk_pay_mobile == null)
				{
					$('#jxk_pay_mobile').focus();
					$('#jxk_pay_mobile_error').addClass('error').html('请输入手机号码');
					return false;
				}
				else
				{
					var RegExpmobile = /^(1(([358][0-9])|(47)))\d{8}$/;
					if(RegExpmobile.test(jxk_pay_mobile))
					{
						$('form[id="order-pay-form"]').attr( 'target' , '_blank' );
						X.get('/ajax/order.php?action=dialog&id=' + $('form[id="order-pay-form"]').attr('sid'));
						$('form[id="order-pay-button"]').submit();
					}
					else
					{
						$('#jxk_pay_mobile').focus();
						$('#jxk_pay_mobile_error').addClass('error').html('手机号格式不正确');
						return false;
					}
				}
			}
			else
			{
				$('form[id="order-pay-form"]').attr( 'target' , '_blank' );
				X.get('/ajax/order.php?action=dialog&id=' + $('form[id="order-pay-form"]').attr('sid'));
				$('form[id="order-pay-button"]').submit();
			}
		}
	});
	
	//订单列表页删除
	$('.poperat .delorderlist').die().click(function(){
		var oid = $(this).attr('oid');
		var del = confirm('确定要删除该订单吗？');
		if(del == true)
		{
			$.post('/ajax/checkusername.php?action=delorder',{id:oid},function(data){
				if(data == 'success')
				{
					$('#delorderlist_'+oid).remove();
				}
				else
				{
					alert('删除失败');
					return false;
				}
			});
		}
	});
	
	//订单详情页删除
	$('.delorder').die().click(function(){
		var oid = $(this).attr('oid');
		var del = confirm('确定要删除该订单吗？');
		if(del == true)
		{
			$.post('/ajax/checkusername.php?action=delorder',{id:oid},function(data){
				if(data == 'success')
				{
					window.location = '/order/index.php?action=buy';	
				}
				else
				{
					alert('删除失败');
					return false;
				}
			});
		}
	});
	
	//订单详情页点击发货按钮 
	$('#devlerry').click(function(){
		$('#hidden_express').hide();
		$('#show_express').show();
	});
	
	//订单详情页点击提交按钮
	$('#express_button').die().live('click',function(){
		var order_id = $(this).attr('oid');
		var express_name = $('#express_name').val();
		var express_id = $('#express_id').val();
		if(express_name == '' || express_id == '')
		{
			return false;
		}
		else
		{
			$.post('/ajax/checkusername.php?action=addexpress', {order_id:order_id,express_name:express_name,express_id:express_id}, function(data){
				if(data == 'success')
				{
					var orderstr = '<div class="clearfix">当前订单状态：<span>交易成功</span></div><div class="clearfix">卖家已发货。</div>';
					var expressstr = '<div class="dd"><div class="clearfix">快递公司：'+express_name+'</div><div class="clearfix">物流单号：'+express_id+'</div></div>';
					$('#wuliuinformation .dd').remove();
					$('.ordersArea1').html(orderstr);
					$('#wuliuinformation .dt').after(expressstr);
				}
				else
				{
					alert('提交失败');
					return false;
				}
			});
		}
	});
	$.each($('.order_lasttime'),function(i,val){	
		getorderlasttime( $(this) );
	});
	
	//解除第三方SNS网站的绑定
	$('#unbindqq').die().live('click',function(){
		X.get('/ajax/checkusername.php?action=dialogunbindqq');
	});
	
	
	$('#dialog_unbindqq').die().live('click',function(){
		$.post('/ajax/checkusername.php?action=unbindqq', {}, function(data){
			if(data == 'success')
			{
				$('.forediv2').html();
				$('.forediv2').html('<span><img src="/static/images/weibangdingqq.png" alt="" /></span><a href="/account/login.php?action=qqlogin"><img src="/static/images/bangding.png" alt="" /></a>');
				return X.boxClose();
			}
			else
			{
				return X.boxClose();
			}
		});
	});
	
	
	$('#unbindweibo').die().live('click',function(){
		X.get('/ajax/checkusername.php?action=dialogunbindweibo');
	});
	
	$('#dialog_unbindweibo').die().live('click',function(){
		$.post('/ajax/checkusername.php?action=unbindweibo', {}, function(data){
			if(data == 'success')
			{
				$('.forediv3').html();
				$('.forediv3').html('<span><img src="/static/images/weibangdingweibo.png" alt="" /></span><a href="/account/login.php?action=sinalogin"><img src="/static/images/bangding.png" alt="" /></a>');
				return X.boxClose();
			}
			else
			{
				return X.boxClose();
			}
		});
	});
	
	$('#order_question_close').die().click(function(){
		$(this).parent().remove();
	});
	
	//解除绑定的支付通账号
	$('#unbindingicardpay').die().click(function(){
		$.post('/ajax/checkusername.php?action=unbindicardpay', {}, function(data){
			if(data == 'havesaleorder')
			{
				//有成功的售卖交易，故账户永久无法解绑
				$('#unbindtip').html('<div class="leiben4"><div class="leiben4-2"><div class="leiben4-3"><div class="leiben4-4"></div><p>解除绑定支付通操作失败！</p><p>您的支付通账户目前无法解绑，可能的原因有：</p><p>您有成功的售卖交易，故账户永久无法解绑。</p></div></div></div>');
			}
			else if(data == 'havegoingorder')
			{
				//有进行中的交易，需要交易结束后或关闭后再进行解绑支付通操作
				$('#unbindtip').html('<div class="leiben4"><div class="leiben4-2"><div class="leiben4-3"><div class="leiben4-4"></div><p>解除绑定支付通操作失败！</p><p>您的支付通账户目前无法解绑，可能的原因有：</p><p>有进行中的交易，需要关闭交易后再进行解绑支付通操作。</p></div></div></div>');
			}
			else if(data == 'haveproduct')
			{
				//有商品在售中，需要下架商品才能进行解绑操作
				$('#unbindtip').html('<div class="leiben4"><div class="leiben4-2"><div class="leiben4-3"><div class="leiben4-4"></div><p>解除绑定支付通操作失败！</p><p>您的支付通账户目前无法解绑，可能的原因有：</p><p>有商品在售中，需要下架商品才能进行解绑操作。</p></div></div></div>');
			}
			else
			{
				//可以解除绑定
				$.post('/account/unbind.php', {}, function(data){
					if(data == 'unbindsuccess')
					{
						//解绑成功,跳转至未绑定页面
						window.location = '/account/receivableaccount.php';	
					}
					else
					{
						//解绑失败
						$('#unbindtip').html('<div class="leiben4"><div class="leiben4-2"><div class="leiben4-3"><div class="leiben4-4"></div><p>解除绑定支付通操作失败！</p><p>您的支付通账户目前无法解绑，可能的原因有：</p><p>（1）有进行中的交易，需要交易结束后或关闭后再进行解绑支付通操作。</p><p>（2）有商品在售中，需要下架商品才能进行解绑操作。</p><p>（3）您有成功的售卖交易，故账户永久无法解绑。</p></div></div></div>');
					}
				});
			}
		});
	});
	
	
});

var showbuyproductinfo = function ()
{
	if($.cookie('buynum') != null)
	{
		var buynum = $.cookie('buynum');
	}
	else 
	{
		var buynum = 1;
	}
	var propertyfirst = $.cookie('propertyfirst');
	var propertysecond = $.cookie('propertysecond');
	var property = '';
	if(propertyfirst != null)
	{
		property = propertyfirst;
	}
	if(propertysecond != null)
	{
		property = property +'，'+ propertysecond;
	}
	//商品运费
	var express = $('#product_express').html();
	var productexpress = parseFloat(express.substr(1));
	//商品价格
	var price = parseFloat($('#product_price').html());
	//计算出应付总额
	var totalprice = price*buynum+productexpress;
	$('#buy_product_num').val(buynum);
	$('#product_property').html(property);
	$('#total_price').html('￥'+totalprice.toFixed(2));
};


/****************订单那列表页剩余时间************************/
var getorderlasttime = function ( obj )
{
	var oid = obj.attr('oid') ;
	var a = parseInt(obj.attr('order_diff_'+oid));
	if (!a>0) return;
	var b = (new Date()).getTime();	
	var e = function() {
		var c = (new Date()).getTime();
		var ls = a + b - c;
		if ( ls > 0 ) {
			var ld = parseInt( ls/86400000 , 10 ) ; ls = ls % 86400000;
			var lh = parseInt( ls/3600000 , 10 ) ; ls = ls % 3600000;
			var lm = parseInt( ls/60000 , 10 ) ; 
			var ls = parseInt( Math.round(ls%60000)/1000 , 10 );
			if (lm == 0 && ls == 0) {
				var html = '订单失效';
				$('#order_list_pay_'+oid).remove();
				$('#order_difftime_'+oid).remove();
				$(".poperat #order_difftime_"+oid).stopTime('order_difftime_'+oid);
				obj.html(html);
			} else {
				var html = '00'+':'+lm+':'+ls;
				$('#order_difftime_'+oid).html(html);
			}
		} else {
			$(".poperat #order_difftime_"+oid).stopTime('order_difftime_'+oid);
			$('.poperat #order_difftime_'+oid).html('订单失效');
			window.location.reload();
		}
	};
	$(".poperat #order_difftime_"+oid).everyTime(996, 'order_difftime_'+oid, e);
};


/****************订单详情页剩余时间************************/
var getorderdetaillasttime = function ()
{
	var a = parseInt($('#order_single_lasttime').attr('diff'));
	if (!a>0) return;
	var b = (new Date()).getTime();	
	var e = function() {
		var c = (new Date()).getTime();
		var ls = a + b - c;
		if ( ls > 0 ) {
			var ld = parseInt( ls/86400000 , 10 ) ; ls = ls % 86400000;
			var lh = parseInt( ls/3600000 , 10 ) ; ls = ls % 3600000;
			var lm = parseInt( ls/60000 , 10 ) ; 
			var ls = parseInt( Math.round(ls%60000)/1000 , 10 );
			if (lm == 0 && ls == 0) {
				var html = '订单失效';
				$('#order_single_lasttime').remove();
				$('#order_detail_pay').remove();
				$(".clearfix #order_single_difftime").stopTime('order_single_difftime');
			} else {
				var html = '00'+':'+lm+':'+ls;
			}
			$('#order_single_difftime').html(html);
		} else {
			$(".clearfix #order_single_difftime").stopTime('order_single_difftime');
			$('.clearfix #order_single_difftime').html('订单失效');
			window.location.reload();
		}
	};
	$(".clearfix #order_single_difftime").everyTime(996, 'order_single_difftime', e);
};