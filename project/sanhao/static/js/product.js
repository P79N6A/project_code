var flagimage = false;
var productstr = {};
productstr.property = [] ;
productstr.image = [] ;
$(function(){	
/*******************商品发布页面JS效果**********************/
	//商品名称
	
	$('#product_name').artTxtCount($('#product_name_ts'), 30);
	$('#product_name').focus(function(){
		$('#product_name').addClass('selected');
	}).keyup(function(){
		var productname = $('#product_name').val();
		$('#product_name_beta').html(productname);
	}).blur(function(){
		$('#product_name').removeClass('selected');
		var productname = $('#product_name').val();
		//将商品名称放入数组中
		productstr.productname = productname;
	});
	
	
	//商品描述
	$('#product_content').artTxtCount($('#product_content_ts'), 300);
	$('#product_content').focus(function(){
		$('#product_content').addClass('selected');
	}).keyup(function(){
		var productcontent = $('#product_content').val();
		$('#product_content_beta').html(productcontent);
	}).blur(function(){
		$('#product_content').removeClass('selected');
		var productcontent = $('#product_content').val();
		//将商品名称放入数组中
		productstr.productdescription = productcontent;
	});
	
	//商品价格
	var record = { num:"" };
	$('#product_price').focus(function(){
		$('#product_price').addClass('selected');
	}).keyup(function(){
		var decimalReg=/^\d{0,8}\.{0,1}(\d{1,2})?$/;
		var product_price = $('#product_price').val();
		if(product_price != '' && decimalReg.test(product_price))
		{
			var firstnumber = product_price.substr(0,1);
			if(firstnumber == 0 || firstnumber == '.')
			{
				$('#product_price').val('');
				$('#product_price_ts').html("<font color='red'>不得低于1.00元</font>");
			}
			else
			{
				record.num = product_price;
				$('#product_price').val(record.num);
				$('#product_price_ts').html('');
			}
		}
		else
		{
			if(product_price != '')
			{
				product_price = record.num;
				$('#product_price').val(product_price);
			}
			else
			{
				$('#product_price').val('');
			}
		}
		if(product_price != '')
		{
			var price = '￥'+product_price;
			$('#product_price_beta').html(price);
		}
		else
		{
			$('#product_price_beta').html('');
		}
	}).blur(function(){
		$('#product_price').removeClass('selected');
		var product_price = $('#product_price').val();
		//将商品价格放入数组中
		productstr.price = product_price;
	});
	

	//删除图片
	$('.delete').die().live('click',function(){
		var id = $(this).attr('pid');
		var del = confirm('确定要删除该图片吗？');
		if(del == true)
		{
			var temppic = [];
			for(var i=0;i<productstr.image.length;i++){
				if( productstr.image[i].id != parseInt( id , 10 ) ){
					temppic.push(productstr.image[i]);
				}
			}
			productstr.image = temppic;
			$('#del_'+id).remove();
			var i=0;
			var flag = false ;
			for(i=0;i<productstr.image.length;i++){
				if(productstr.image[i].type == 1)
				{
					flag = true ;
				}
			}
			if( flag ){
				showimage(productstr.image);
			}else{
				showimage(productstr.image, productstr.image[productstr.image.length-1].id);
			}
			
		}
	});
	
	//点击小预览图设为主图
	$('.span_beta').die().live('click',function(){
		var id = parseInt( $(this).attr('pid') , 10 );
		if( $('#del_'+id).attr('pid') == undefined || $('#del_'+id).attr('pid') == null ) return ;
		var temppic = [];
		var  newid=0;
		for(var i=0;i<productstr.image.length;i++){
			if( productstr.image[i].id != id ){
				temppic.push(productstr.image[i]);
			}else{
				newid = i;
			}
		}
		temppic.push(productstr.image[newid]);
		productstr.image = temppic;
		showimage(productstr.image, id);
	});
	
	
	//点击更多选项
	//设置商品可售数量
	$('#product_num_checkbox').die().click(function(){
		var num_check = $('#product_num_checkbox').attr('checked');
		if(num_check == 'checked')
		{
			$('#product_property_num').val('');
			$('#product_num').show();
		}
		else
		{
			$('#product_num').hide();
			$('#product_max_number_beta').html('');
			productstr.max_number = '';
		}
	});
	
	//商品数量输入框判断
	$('#product_property_num').focus(function(){
		$('#product_property_num').addClass('selected');
	}).keyup(function(){
		var Regnum = /^[0-9]*[1-9][0-9]*$/;
		var num = $('#product_property_num').val();
		if(Regnum.test(num))
		{
			$('#product_max_number_beta').html(num);
			return true;
		}
		else
		{
			$('#product_property_num').val('');
			return false;
		}
	}).blur(function(){
		$('#product_property_num').removeClass('selected');
		var num = $('#product_property_num').val();
		//将商品可售数量放入数组
		productstr.max_number = num;
		
	});
	
	//设置商品原价
	$('#product_oldprice_checkbox').die().click(function(){
		var oldprice_check = $('#product_oldprice_checkbox').attr('checked');
		if(oldprice_check == 'checked')
		{
			$('#product_property_oldprice').val('');
			$('#product_oldprice').show();
		}
		else
		{
			$('#product_oldprice').hide();
			productstr.old_price = '';
		}
	});
	
	//商品原价判断
	var oldprice = { old_price:"" };
	$('#product_property_oldprice').focus(function(){
		$('#product_property_oldprice').addClass('selected');
	}).keyup(function(){
		var Regprice = /^\d{0,8}\.{0,1}(\d{1,2})?$/;
		var product_oldprice = $('#product_property_oldprice').val();
		if(product_oldprice != '' && Regprice.test(product_oldprice))
		{
			var firstnumber = product_oldprice.substr(0,1);
			var secondnumber = product_oldprice.substr(1,1);
			if((firstnumber == 0 || firstnumber == '.') && secondnumber != '.' && product_oldprice.length > 1)
			{
				$('#product_property_oldprice').val('');
			}
			else
			{
				oldprice.old_price = product_oldprice;
				$('#product_property_oldprice').val(oldprice.old_price);
			}
		}
		else
		{
			if(product_oldprice != '')
			{
				product_oldprie = oldprice.old_price;
				$('#product_property_oldprice').val(product_oldprie);
			}
			else
			{
				$('#product_property_oldprice').val('');
			}
		}
	}).blur(function(){
		$('#product_property_oldprice').removeClass('selected');
		var product_oldprice = $('#product_property_oldprice').val();
		//将商品原价写入数组
		productstr.old_price = product_oldprice;
	});
	
	
	//设置物流快递费用
	$('#product_express_price_checkbox').die().click(function(){
		var expressprice_check = $('#product_express_price_checkbox').attr('checked');
		if(expressprice_check == 'checked')
		{
			$('#product_property_express_price').val('');
			$('#product_express_price').show();
		}
		else
		{
			$('#product_express_price').hide();
			$('#express_price_beta').html('');
			productstr.express_price = '';
		}
	});
	
	//物流快递费用判断
	var expressprice = { express_price:"" };
	$('#product_property_express_price').focus(function(){
		$('#product_property_express_price').addClass('selected');
	}).keyup(function(){
		var Regprice = /^\d{0,8}\.{0,1}(\d{1,2})?$/;
		var product_expressprice = $('#product_property_express_price').val();
		if(product_expressprice != '' && Regprice.test(product_expressprice))
		{
			var firstnumber = product_expressprice.substr(0,1);
			var secondnumber = product_expressprice.substr(1,1);
			if((firstnumber == 0 || firstnumber == '.') && secondnumber != '.' && product_expressprice.length > 1)
			{
				$('#product_property_express_price').val('');
			}
			else
			{
				expressprice.express_price = product_expressprice;
				$('#product_property_express_price').val(expressprice.express_price);
			}
			if(expressprice.express_price != '')
			{
				$('#express_price_beta').html('￥'+expressprice.express_price);
			}
			else
			{
				$('#express_price_beta').html('');
			}
		}
		else
		{
			if(product_expressprice != '')
			{
				product_expressprice = expressprice.express_price;
				$('#product_property_express_price').val(product_expressprice);
			}
			else
			{
				$('#product_property_express_price').val('');
			}
			if(product_expressprice != '')
			{
				$('#express_price_beta').html('￥'+product_expressprice);
			}
			else
			{
				$('#express_price_beta').html('');
			}
		}
	}).blur(function(){
		$('#product_property_express_price').removeClass('selected');
		var product_expressprice = $('#product_property_express_price').val();
		//将快递费用数据写入数组
		productstr.express_price = product_expressprice;
	});
	
	
	//设置售卖截止日期
	$('#product_end_time_check').die().click(function(){
		var endtime_check = $('#product_end_time_check').attr('checked');
		if(endtime_check == 'checked')
		{
			$('#product_property_end_time').val('');
			$('#product_end_time').show();
		}
		else
		{
			$('#product_end_time').hide();
			$('#product_end_time_beta').html('');
			productstr.end_time = '';
		}
	});
	
	$('#product_property_end_time').focus(function(){
		$('#product_property_end_time').addClass('selected');
	}).focus(function(){
		var endtime = $('#product_property_end_time').val();
		//已经选中了时间
		if(endtime != '')
		{
			time_str = endtime+' 23:59:59';
			time_str = time_str.replace(/:/g,'-');
			time_str = time_str.replace(/ /g,'-');
			var time_arr = time_str.split('-');
			var t = new Date(Date.UTC(time_arr[0],time_arr[1]-1,time_arr[2],time_arr[3]-8,time_arr[4],time_arr[5]));
			//var inttime = Date.parse(new Date(endtime+' 23:59:59')) / 1000;
			getLastTime(parseInt((t.getTime())/1000));
		}
	}).blur(function(){
		$('#product_property_end_time').removeClass('selected');
		var endtime = $('#product_property_end_time').val();
		if(endtime != '')
		{
			time_str = endtime+' 23:59:59';
			time_str = time_str.replace(/:/g,'-');
			time_str = time_str.replace(/ /g,'-');
			var time_arr = time_str.split('-');
			var t = new Date(Date.UTC(time_arr[0],time_arr[1]-1,time_arr[2],time_arr[3]-8,time_arr[4],time_arr[5]));
			//将截止时间放入数组
			productstr.end_time = parseInt((t.getTime())/1000);
		}
	});
	
	
	//设置商品属性
	$('#product_property_check').die().click(function(){
		var property_check = $('#product_property_check').attr('checked');
		if(property_check == 'checked')
		{
			var count = $('.sitem2 .dd').size();
			if(count == 2)
			{
				$('.sitem2 .dd').show();
			}
			else
			{
				$('.sitem2 .dd').show();
				$('.sitem2 .dd2').show();
			}
		}
		else
		{
			$('.sitem2 .dd').hide();
			$('.sitem2 .dd2').hide();
			var count = $('.sitem2 .dd').size();
			//如果有两组商品属性输入框，且两组框里面都有内容，则都删除
			if(count == 2)
			{
				//两组商品属性输入框内的内容都清空
				$('#product_property_beforeone').val('');
				$('#product_property_afterone').val('');
				$('#product_property_beforesecond').val('');
				$('#product_property_aftersecond').val('');
				if(productstr.property.length > 0)
				{
					for( var i = 0 ; i < productstr.property.length ; i++ )
					{
						productstr.property.splice(i,2);
					}
				}
			}
			//如果有一组商品属性输入框，则删除一组
			else
			{
				var tid = $('.sitem2 .dd .btn').attr('tid');
				if(tid == 'one')
				{
					$('#product_property_beforeone').val('');
					$('#product_property_afterone').val('');
				}
				else
				{
					$('#product_property_beforesecond').val('');
					$('#product_property_aftersecond').val('');
				}
				if(productstr.property.length > 0)
				{
					for( var i = 0 ; i < productstr.property.length ; i++ ){
						if( productstr.property[i].id == tid ){
							productstr.property.splice(i,1);
						}
					}
				}
			}
		}
	});
	
	$('.sitem2 .dd2').die().live('click',function(){
		var count = $('.sitem2 .dd').attr('class');
		var firststr = '<div class="dd"><a style="cursor:pointer;" tid="one" class="btn"></a><input maxlength="4" id="product_property_beforeone" tid="one" type="text" class="text o1" /><input id="product_property_afterone" tid="one" type="text" class="text o2" /></div>';
		var str = '<div class="dd"><a style="cursor:pointer;" tid="two" class="btn"></a><input maxlength="4" id="product_property_beforesecond" tid="two" type="text" class="text o1" /><input id="product_property_aftersecond" tid="two" type="text" class="text o2" /></div>';
		//如果至少有一个商品属性输入框
		if(count == 'dd')
		{	
			var tid = $('.sitem2 .dd .btn').attr('tid');
			if(tid == 'one')
			{
				$('.sitem2 .dd').after(str);
				$('.sitem2 .dd2').hide();
			}
			else
			{
				$('.sitem2 .dd').before(firststr);
				$('.sitem2 .dd2').hide();
			}
		}
		else
		{
			$('.sitem2 .dt').after(firststr);
		}
	});
	
	$('.sitem2 .dd .btn').die().live('click',function(){
		var tid = $(this).attr('tid');
		$(this).parent().remove();
		var add_class = $('.sitem2 .dd2').attr('class');
		if(add_class == undefined)
		{
			$('.sitem2 .dd').after('<div class="dd2"><a style="cursor:pointer;" id="product_add_property">添加属性</a></div>');
		}
		else
		{
			$('.sitem2 .dd2').show();
		}
		var count = $('.sitem2 .dd').size();
		if(productstr.property.length > 0)
		{
			for( var i = 0 ; i < productstr.property.length ; i++ ){
				if( productstr.property[i].id == tid ){
					productstr.property.splice(i,1);
				}
			}
		}
		if(count == 0)
		{
			$('#product_property_check').attr('checked',false);
			$('.sitem2 .dd2').hide();
		}
	});
	
	//获取商品属性内容
//	var propertyArr = [];
//	var properstr = {};
	$('#product_property_beforeone').die().live({focus:function(){
		$('#product_property_beforeone').removeClass('error').addClass('selected');
	},blur:function(){
		$('#product_property_beforeone').removeClass('selected');
		var content_beforeone = $('#product_property_beforeone').val();
		var tid = $('#product_property_beforeone').attr('tid');
		var flag = false;
		var aa = {} ;
		if( !flag &&  productstr.property.length > 0 ){
			for( var i = 0 ; i < productstr.property.length ; i++ ){
				if( productstr.property[i].id == tid ){
					flag = true ;
					productstr.property[i].name = content_beforeone ;
				}
			}
			if( !flag ){
				aa.id = tid;
				aa.name = content_beforeone ;
				productstr.property.push( aa ) ;
			}
		}else{
			aa.id = tid ;
			aa.name = content_beforeone ;
			productstr.property.push( aa ) ;
		}
	}
	});
	
	$('#product_property_afterone').die().live({focus:function(){
		$('#product_property_afterone').removeClass('error').addClass('selected');
	},blur:function(){
		$('#product_property_afterone').removeClass('selected');
		var content_afterone = $('#product_property_afterone').val();
		var tid = $('#product_property_afterone').attr('tid');
		var flag = false;
		var aa = {} ;
		if( !flag &&  productstr.property.length > 0 ){
			for( var i = 0 ; i < productstr.property.length ; i++ ){
				if( productstr.property[i].id == tid ){
					flag = true ;
					productstr.property[i].content = content_afterone ;
				}
			}
			if( !flag ){
				aa.id = tid;
				aa.content = content_afterone ;
				productstr.property.push( aa ) ;
			}
		}else{
			aa.id = tid ;
			aa.content = content_afterone ;
			productstr.property.push( aa ) ;
		}
	}
	});
	
	$('#product_property_beforesecond').die().live({focus:function(){
		$('#product_property_beforesecond').removeClass('error').addClass('selected');
	},blur:function(){
		$('#product_property_beforesecond').removeClass('selected');
		var content_beforesecond = $('#product_property_beforesecond').val();
		var tid = $('#product_property_beforesecond').attr('tid');
		var flag = false;
		var aa = {} ;
		if( !flag &&  productstr.property.length > 0 ){
			for( var i = 0 ; i < productstr.property.length ; i++ ){
				if( productstr.property[i].id == tid ){
					flag = true ;
					productstr.property[i].name = content_beforesecond ;
				}
			}
			if( !flag ){
				aa.id = tid;
				aa.name = content_beforesecond ;
				productstr.property.push( aa ) ;
			}
		}else{
			aa.id = tid ;
			aa.name = content_beforesecond ;
			productstr.property.push( aa ) ;
		}
	}
	});

	$('#product_property_aftersecond').die().live({focus:function(){
		$('#product_property_aftersecond').removeClass('error').addClass('selected');
	},blur:function(){
		$('#product_property_aftersecond').removeClass('selected');
		var content_aftersecond = $('#product_property_aftersecond').val();
		var tid = $('#product_property_aftersecond').attr('tid');
		var flag = false;
		var aa = {} ;
		if( !flag &&  productstr.property.length > 0 ){
			for( var i = 0 ; i < productstr.property.length ; i++ ){
				if( productstr.property[i].id == tid ){
					flag = true ;
					productstr.property[i].content = content_aftersecond ;
				}
			}
			if( !flag ){
				aa.id = tid;
				aa.content = content_aftersecond ;
				productstr.property.push( aa ) ;
			}
		}else{
			aa.id = tid ;
			aa.content = content_aftersecond ;
			productstr.property.push( aa ) ;
		}
	}
	});
	
	//商品详情页分享商品
	$('#share_product').click(function(){
		var pid = $(this).attr('pid');
		X.get('/ajax/checkusername.php?action=dialogshareproduct&id='+pid);
	});
	
	//商品详情页点击添加
	$('.btn-add').die().click(function(){
		var num = $('#buy_num').val();
		var add_num = parseInt(num)+1;
		var max_num = $('#max_num').attr('num');
		if(max_num != '不限')
		{
			if(add_num > parseInt(max_num) && parseInt(max_num) > 0)
			{
				add_num = parseInt(max_num);
			}
		}
		$('#buy_num').val(add_num);
		
	});
	
	//商品详情页点击减少
	$('.btn-reduce').die().click(function(){
		var num = $('#buy_num').val();
		var reduce_num = parseInt(num)-1;
		if(reduce_num == 0)
		{
			reduce_num = 1;
		}
		$('#buy_num').val(reduce_num);
	});
	
	//商品详情页输入数量
//	$('#buy_num').bind("keyup", function(){
//		var num = parseInt($(this).val());
//		//可以购买的最大数量
//		var max_num = $('#max_num').attr('num');
//		//可以购买的最小数量
//		var min_num = 1;
//		num = isNaN(num) ? 1 : num;
//		if(max_num != '不限')
//		{
//			max_num = isNaN(parseInt(max_num)) ? 1 : parseInt(max_num);
//			if(num > parseInt(max_num) && parseInt(max_num) > 0)
//			{
//				num = parseInt(max_num);
//			}
//		}
//		if(num < min_num)
//		{
//			num = min_num;
//		}
//		$(this).val(num);
//		
//	});
	
	//商品列表页删除
	$('.fore4 ul li .delproduct').die().click(function(){
		var pid = $(this).attr('pid');
		var del = confirm('确定要删除该商品吗？');
		if(del == true)
		{
			$.post('/ajax/checkusername.php?action=delproduct',{id:pid},function(data){
				if(data == 'success')
				{
					$('#delproduct_'+pid).remove();
				}
				else
				{
					alert('删除失败');
					return false;
				}
			});
		}
	});
	
	//商品列表页下架
	$('.fore4 ul li .shelvesproduct').die().click(function(){
		var shelve = $(this);
		var pid = $(this).attr('pid');
		var del = confirm('确定要下架该商品吗？');
		if(del == true)
		{
			$.post('/ajax/checkusername.php?action=shelvesproduct',{id:pid},function(data){
				if(data == 'success')
				{
					shelve.parent().parent().parent().parent().children().children('em').parent().children('em').removeClass('m2').addClass('m1').text('未上架');
					shelve.parent().remove();
				}
				else
				{
					alert('下架失败');
					return false;
				}
			});
		}
	});
	//点击发布商品时判断登录用户是否绑定支付通，如果没有绑定，则不能发布商品
	$('#release_product').die().click(function(){
		window.location = '/account/productrelease.php';
	});
	
	//用户点击商品发布页面先存为草稿的链接
	$('#product_add_draft').click(function(){
		$(this).addClass('productadddraft');
		checkdraft();
	});

	$('#product_image').uploadify({
			'height'   : 50,
			'width'    : 350,
			'buttonClass'   : 'file',           //按钮辅助class   
			'fileTypeDesc'  : 'All Files',       //图片选择描述   
            'fileTypeExts'  : '*.jpg; *jpeg; *.png',//文件后缀限制 默认：'*.*'  
           	'multi': false,							//设置是否允许一次选择多个文件，true为允许，false不允许
            'queueSizeLimit': 1,                  //一个队列上传文件数限制 
           // 'uploadLimit'	: 999,				//限制总上传文件数,默认是999。指同一时间，如果关闭浏览器后重新打开又可上传。
            'successTimeout'  : 30,                  //上传超时  
            'fileSizeLimit' : '5120KB',				//上传图片最大限制
            'removeCompleted' : true, 				//完成时是否清除队列 默认true 
			'swf'      : '/static/js/uploadify.swf',
			'uploader' : '/ajax/checkusername.php?action=uploadimage',
			//上传成功
			'onUploadSuccess' : function(file, data, response) {   
				var imagecount = $('.slist span').size();
        		if( data == 2 )
				{
					$('#'+file.id).remove();
					alert('图片过小，图片尺寸需大于200*150');
					flagimage = false;
				}
				else if(data == 1)
				{
					$('#'+file.id).remove();
					alert('图片大小不能大于5M');
					flagimage = false;
				}
				//2.验证上传错误
				else if( data == 0 ){
					$('#'+file.id).remove();
					alert('上传失败，不要气馁，再来一次');
					flagimage = false;
				}
				//3.上传成功处理
				else
				{
					data = eval(data);
					var pic = {};
					pic.id = imagecount;
					pic.picurl = data;
					productstr.image.push(pic);
					flagimage = true;
					$('.slist').append( "<span class=\'span_beta\' pid=\'"+imagecount+"\' id=\'del_"+imagecount+"\'><img src=\'/"+data+"\'><em><a class=\'delete\' pid=\'"+imagecount+"\' href=\'javascript:void(0);\'>X</a></em></span><input type=\'hidden\' name=\'artname\' value=\'"+data+"\' id=\'artname_"+imagecount+"\' />" );
				}
				showimage(productstr.image, imagecount);
            }
	});
	
	$('#sdsm').focus(function(){
		var pid = $('#product_buy').attr('pid');
		$.post('/ajax/checkusername.php?action=checkislogin',{},function(data){
			if(data == 'nologin')
			{
				X.get('/ajax/checkusername.php?action=dialoglogin&id='+pid+'&detail=detail');
			}
		});
	});
	
	//商品详情页回复
	$('#shopping_detail_comment_button').click(function(){
		var pid = $('#product_buy').attr('pid');
		var comment = $('#sdsm').val();
		//此处屏蔽内容为回复评论信息时提交部分内容
//		var cid = $('#shopping_detail_comment_button').attr('pid');
//		if(cid != 'undefined' && cid != undefined && cid > 0){
//			if(comment.length < 5 || comment.length > 70){
//				$('#comment_reset').text('评论内容5-70个字！');
//			}else{
//				$.post('/ajax/checkusername.php?action=reply_comments',{cid:cid,comment:comment},function(data){
//					if(data == 'failure'){
//						$('#comment_reset').text('回复失败');
//					}
//					else{
//						$('#sdsm').val('');
//						msg = data.split('|');
//						var str = '';
//						str += '<dl class="commentlist">';
//						str += '<dt><a href="javascript:void(0)"><img src="'+msg[0]+'" alt="" /></a></dt>';
//						str += '<dd>';
//						str +='<p class="p1"><span>'+msg[2]+'</span>'+msg[1];
//						if(msg[3] != ''){
//							str += '（'+msg[3]+'）';
//						}
//						str += '</p>';
//						str +='<p class="p2">'+msg[4]+'<a></a></p>';
//						str +='</dd>';
//						str += '</dl>';
//						$('.comment .detail_comments').prepend(str);
//					}
//				});
//			}
//		}else{
			if(comment.length > 1 && comment != undefined && comment != 'undefined'){
				if(comment.length < 5 || comment.length > 70){
					$('#comment_reset').text('评论内容5-70个字！');
				}else{
					$.post('/ajax/checkusername.php?action=detail_cmments',{pid:pid,comment:comment},function(data){
						if(data == 'failure' || data == 'nocomment'){
							$('#comment_reset').text('评论失败');
						}
						else if(data == 'onself'){
							$('#comment_reset').text('不能评论自己的商品');
						}
						else if(data == 'nologin')
						{
							X.get('/ajax/checkusername.php?action=dialoglogin&id='+pid+'&detail=detail');
						}else{
							$('#sdsm').val('');
							msg = data.split('|');
							var str = '';
							str += '<dl class="commentlist">';
							str += '<dt><a href="javascript:void(0)"><img src="'+msg[0]+'" alt="" /></a></dt>';
							str += '<dd>';
							str +='<p class="p1"><span>'+msg[2]+'</span>'+msg[1];
							if(msg[3] != ''){
								str += '（'+msg[3]+'）';
							}
							str += '</p>';
							str +='<p class="p2">'+msg[4]+'</p>';
							str +='</dd>';
							str += '</dl>';
							$('.comment .detail_comments').prepend(str);
							
						}
					});
				}
			}else{
				$('#sdsm').val('');
				$('#comment_reset').text('评论内容不能为空');
			}
//		}
		
	});
	//回复评论
//	$('.reply_comment').click(function(){
//		var cid = $(this).attr('id');
//		var nick = $(this).attr('pid');
//		$('#shopping_detail_comment_button').attr('pid',cid);
//		$('#sdsm').val('#回复#@'+nick+':').keydown();
//	});
	$(".nshare ul li a").toggle(function(){
		$(".slink").show();
	},function(){
		$(".slink").hide();	
	});
});

//	var checkimage = function(){
//	//判断是否有图片在上传中
//	if( $('#product_image').attr('doing') != '0' ){
//		return false;
//	}
//	//先验证图片格式是否正确
//	var filename = $('#product_image').val();
//  	
//	var index1=filename.lastIndexOf(".");
//	var index2=filename.length;
//	var postf=filename.substring(index1,index2);
//	postf = postf.toLocaleLowerCase();
//	//图片后缀支持jpg、jpeg、png
//	if(/.(jpg|jpeg|png)$/.test(postf) === false){
//		alert('仅支持JPG、JPEG和PNG格式的图片');
//		flagimage = false;
//	}else{
//		//判断上传了几张图片
//		var imagecount = $('.slist span').size();
//		if(imagecount < 4)
//		{
//			//上传图片
//			$.ajaxFileUpload({
//				url:'/ajax/checkusername.php?action=uploadimage',
//				secureuri:false,
//				fileElementId:'product_image',
//				dataType:'json',
//				success:function(data) {
//					//1.验证大小错误
//					if( data == 2 )
//					{
//						alert('图片过小，图片尺寸需大于200*150');
//						flagimage = false;
//					}
//					else if(data == 1)
//					{
//						alert('图片大小不能大于5M');
//						flagimage = false;
//					}
//					//2.验证上传错误
//					else if( data == 0 ){
//						alert('上传失败，不要气馁，再来一次');
//						flagimage = false;
//					}
//					//3.上传成功处理
//					else
//					{
//						var pic = {};
//						pic.id = imagecount;
//						pic.picurl = data;
//						productstr.image.push(pic);
//						flagimage = true;
//						$('.slist').append( "<span class=\'span_beta\' pid=\'"+imagecount+"\' id=\'del_"+imagecount+"\'><img src=\'/"+data+"\'><em><a class=\'delete\' pid=\'"+imagecount+"\' href=\'javascript:void(0);\'>X</a></em></span><input type=\'hidden\' name=\'artname\' value=\'"+data+"\' id=\'artname_"+imagecount+"\' />" );
//					}
//					showimage(productstr.image, imagecount);
//				}
//			});
//		}
//		else
//		{
//			alert('最多可上传四张图片');
//			flagimage = false;
//		}
//	}
//	};
	
	var showimage = function(picarr, count)
	{
		$('#slider').html('');
		$('#paginate-slider').html('');
		var i;
		if(picarr.length > 0)
		{
			for(i=picarr.length-1; i>=0; i--)
			{
				if( count != undefined )
				{
					if(picarr[i].id == count)
					{
						productstr.image[i].type = 1;
					}
					else
					{
						productstr.image[i].type = 0;
					}
				}
				$('#paginate-slider').append("<a href='javascript:void(0);' class='toc' id=\'delsmall_"+picarr[i].id+"\'><img src=\'/"+picarr[i].picurl+"\' width='80' height='60' /></a>");
			}
			$('#slider').append("<div class='contentimg' id=\'delbig_"+picarr[picarr.length-1].id+"\'><img src=\'/"+picarr[picarr.length-1].picurl+"\' width='500' height='375' alt='' /></div>");
		}
	};
	
	var getLastTime = function(endtime)
	{
		nowtime = Date.parse(new Date()) / 1000;
		lasttime = endtime - nowtime;
		if(lasttime > 0)
		{
			days = lasttime / 60 / 60 / 24;
			daysRound = Math.floor(days);
			if(daysRound <= 30)
			{
				hours = lasttime / 60 / 60 - (24 * daysRound);
				hoursRound = Math.floor(hours);
				minutes = lasttime /60 - (24 * 60 * daysRound) - (60 * hoursRound);
				minutesRound = Math.floor(minutes);
				strtime = daysRound + '' + '天' + '' + hoursRound + '' + '时' + '' + minutesRound + '' + '分';
			}
			else
			{
				strtime = '大于30天';
			}
			$("#product_end_time_beta").html(strtime);
		}
	};
	
	/****************商品详情页剩余时间************************/
	var getproductlasttime = function ()
	{
		var a = parseInt($('#lasttime').attr('diff'));
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
				if (ld>0) {
					var html = ld+'天'+lh+'时'+lm+'分'+ls+'秒';
				} else {
					var html = ld+'天'+lh+'时'+lm+'分'+ls+'秒';
				}
				$('#difftime').html(html);
			} else {
				$(".item #difftime").stopTime('difftime');
				$('.item #difftime').html('end');
				window.location.reload();
			}
		};
		$(".item #difftime").everyTime(996, 'difftime', e);
	};
	
	//商品发布页面，显示上半部隐藏下半部
	var showproductup = function ()
	{
		$('#box_up').show();
		$('#box_down').hide();
		$('#mt_up').addClass('cur');
		$('#mt_down').removeClass('cur');
	};
	
	//商品发布页面，隐藏下半部显示上半部
	var showproductdown = function ()
	{
		$('#box_up').hide();
		$('#box_down').show();
		$('#mt_up').removeClass('cur');
		$('#mt_down').addClass('cur');
	};
	
	//商品提交
	var checkproduct = function ()
	{
		//商品价格
		var productname = productstr.productname;
		//商品描述
		var productdescription = productstr.productdescription;
		//商品价格
		var productprice = productstr.price;
		//上传图片
		var productimage = productstr.image;
		//商品可售数量
		var max_number = productstr.max_number;
		//商品原价
		var old_price = productstr.old_price;
		//物流快递费用
		var express_price = productstr.express_price;
		//售卖截止日期
		var end_time = productstr.end_time;
		//设置商品属性
		var property = productstr.property;
		//如果商品名称为空，提示
		if(productname == undefined || productname == '')
		{
			showproductup();
			$('#product_name').addClass('error');
			$('#product_name').focus();
			return false;
		}
		else
		{
			$('#product_name').removeClass('error');
		}
		//如果商品描述为空，提示
		if(productdescription == undefined || productdescription == '')
		{
			showproductup();
			$('#product_content').addClass('error');
			$('#product_content').focus();
			return false;
		}
		else if(productdescription.length < 20)
		{
			showproductup();
			$('#product_content').addClass('error');
			$('#product_content_ts').html("<font color='red'>商品描述不得少于20个字</font>");
			$('#product_content').focus();
			return false;
		}
		else
		{
			$('#product_content').removeClass('error');
		}
		//如果价格为空，提示
		if(productprice == undefined || productprice == '')
		{
			showproductup();
			$('#product_price').addClass('error');
			$('#product_price').focus();
			return false;
		}
		else
		{
			$('#product_price').removeClass('error');
		}
		//如果没有上传图片，提示
		if(productimage == undefined || productimage == '')
		{
			showproductup();
			alert('请至少上传一张图片');
			return false;
		}
		var num_check = $('#product_num_checkbox').attr('checked');
		//如果设置商品可售数量复选框已选,则商品可售数量不能为空
		if(num_check == 'checked')
		{
			if(max_number == undefined || max_number == '')
			{
				showproductdown();
				$('#product_property_num').addClass('error');
				$('#product_property_num').focus();
				return false;
			}
			else
			{
				$('#product_property_num').removeClass('error');
			}
		}
		var oldprice_check = $('#product_oldprice_checkbox').attr('checked');
		//如果设置商品原价复选框已选,则商品原价不能为空
		if(oldprice_check == 'checked')
		{
			if(old_price == undefined || old_price == '')
			{
				showproductdown();
				$('#product_property_oldprice').addClass('error');
				$('#product_property_oldprice').focus();
				return false;
			}
			else
			{
				$('#product_property_oldprice').removeClass('error');
			}
		}
		var expressprice_check = $('#product_express_price_checkbox').attr('checked');
		//如果物流快递费用已选，则物流快递费用不能为空
		if(expressprice_check == 'checked')
		{
			if(express_price == undefined || express_price == '')
			{
				showproductdown();
				$('#product_property_express_price').addClass('error');
				$('#product_property_express_price').focus();
				return false;
			}
			else
			{
				$('#product_property_express_price').removeClass('error');
			}
		}
		var endtime_check = $('#product_end_time_check').attr('checked');
		//如果售卖截止日期已选，则截止日期不能为空
		if(endtime_check == 'checked')
		{
			if(end_time == undefined || end_time == '' || end_time == null)
			{
				showproductdown();
				$('#product_property_end_time').addClass('error');
				$('#product_property_end_time').focus();
				return false;
			}
			else
			{
				$('#product_property_end_time').removeClass('error');
			}
		}
		//如果商品属性已选，则商品属性不能为空
		var product_property = $('#product_property_check').attr('checked');
		if(product_property == 'checked')
		{
			if(property == undefined || property == '')
			{
				//如果商品属性框内都为空
				var tid = $('.sitem2 .dd .btn').attr('tid');
				//如果第一排商品属性框为空,否则就是第二排商品属性框为空
				if(tid == 'one')
				{
					showproductdown();
					$('#product_property_beforeone').addClass('error');
					$('#product_property_afterone').addClass('error');
					return false;
				}
				else
				{
					showproductdown();
					$('#product_property_beforesecond').addClass('error');
					$('#product_property_aftersecond').addClass('error');
					return false;
				}
			}
			else
			{
				//如果商品属性有一组内容
				if(property.length == 1)
				{
					//判断是哪组商品属性框，且属性名称和属性内容是否都有值
					var id = property[0].id;
					var name = property[0].name;
					var content = property[0].content;
					if(name == '' || name == undefined)
					{
						if(id == 'one')
						{
							showproductdown();
							$('#product_property_beforeone').addClass('error');
							$('#product_property_afterone').removeClass('error');
						}
						else
						{
							showproductdown();
							$('#product_property_beforesecond').addClass('error');
							$('#product_property_aftersecond').removeClass('error');
						}
						return false;
					}
					else if(content == '' || content == undefined)
					{
						if(id == 'one')
						{
							showproductdown();
							$('#product_property_beforeone').removeClass('error');
							$('#product_property_afterone').addClass('error');
						}
						else
						{
							showproductdown();
							$('#product_property_beforesecond').removeClass('error');
							$('#product_property_aftersecond').addClass('error');
						}
						return false;
					}
					else
					{
						if(id == 'one')
						{
							$('#product_property_beforeone').removeClass('error');
							$('#product_property_afterone').removeClass('error');
						}
						else
						{
							$('#product_property_beforesecond').removeClass('error');
							$('#product_property_aftersecond').removeClass('error');
						}
					}
				}
				else
				//如果商品属性有两组内容
				{
					var firstid = property[0].id;
					if(firstid == 'one')
					{
						var firstname = property[0].name;
						var firstcontent = property[0].content;
					}
					else
					{
						var firstname = property[1].name;
						var firstcontent = property[1].content;
					}
					var secondid = property[1].id;
					if(secondid == 'two')
					{
						var secondname = property[1].name;
						var secondcontent = property[1].content;
					}
					else
					{
						var secondname = property[0].name;
						var secondcontent = property[0].content;
					}
					if(firstname == '' || firstname == undefined)
					{
						showproductdown();
						$('#product_property_beforeone').addClass('error');
						return false;
					}
					else
					{
						$('#product_property_beforeone').removeClass('error');
					}
					if(firstcontent == '' || firstcontent == undefined)
					{
						showproductdown();
						$('#product_property_afterone').addClass('error');
						return false;
					}
					else
					{
						$('#product_property_afterone').removeClass('error');
					}
					if(secondname == '' || secondname == undefined)
					{
						showproductdown();
						$('#product_property_beforesecond').addClass('error');
						return false;
					}
					else
					{
						$('#product_property_beforesecond').removeClass('error');
					}
					if(secondcontent == '' || secondcontent == undefined)
					{
						showproductdown();
						$('#product_property_aftersecond').addClass('error');
						return false;
					}
					else
					{
						$('#product_property_aftersecond').removeClass('error');
					}
				}
			}
		}
		var productinformation = $.toJSON(productstr);
		var productid = $('#product_id').val();
		if(productid == undefined || productid == '')
		{
			//将数据提交至php文件中(添加)
			$.post('/account/productrelease.php',{product:productinformation},function(data){
				if(data == 'fail')
				{
					window.location = '/account/productrelease.php';	
				}
				else
				{
					window.location = '/account/productdetail.php?id='+data;	
				}
			});
		}
		else
		{
			//编辑商品
			$.post('/account/productmodify.php',{id:productid,product:productinformation},function(data){
				if(data == 'success')
				{
					window.location = '/account/productdetail.php?id='+productid;	
				}
				else
				{
					window.location = '/account/productmodify.php?id='+productid;	
				}
			});
		}
	};
	
	//存为草稿
	var checkdraft = function ()
	{
		//商品价格
		var productname = productstr.productname;
		//商品描述
		var productdescription = productstr.productdescription;
		//商品价格
		var productprice = productstr.price;
		//上传图片
		var productimage = productstr.image;
		//商品可售数量
		var max_number = productstr.max_number;
		//商品原价
		var old_price = productstr.old_price;
		//物流快递费用
		var express_price = productstr.express_price;
		//售卖截止日期
		var end_time = productstr.end_time;
		//设置商品属性
		var property = productstr.property;
		var productinformation = $.toJSON(productstr);
		if((productname == undefined || productname == '') && (productdescription == undefined || productdescription == '') && (productprice == undefined || productprice == '') && (productimage == ''))
		{
			showproductup();
			$('#product_name').addClass('error');
			$('#product_name').focus();
			return false;
		}
		else
		{
			var productid = $('#product_id').val();
			var product_class = $('#product_add_draft').attr('class');
			if(productid == undefined || productid == '')
			{
				//将数据提交至php文件中(添加商品)
				$.post('/ajax/checkusername.php?action=addproductdraft',{product:productinformation},function(data){
					if(data == 'fail')
					{
						//window.location = '/account/productrelease.php';	
						return false;
					}
					else
					{
						//说明为点击存为草稿的链接
						if(product_class == 'productadddraft')
						{
							window.location = '/account/productlist.php';
						}
						else
						{
							var array_product = new Array();
							array_product = data.split('|');
							var strproduct = '已于'+array_product[1]+'自动保存';
							$('#product_id').val(array_product[0]);
							$('#automaticsave').html(strproduct);
							setTimeout('checkdraft()',2*60*1000);
						}
					}
				});
			}
			else
			{
				//编辑商品
				$.post('/ajax/checkusername.php?action=updateproduct',{id:productid,product:productinformation},function(data){
					if(data == 'fail')
					{
						window.location = '/account/productmodify.php?id='+productid;		
					}
					else
					{
						if(product_class == 'productadddraft')
						{
							window.location = '/account/productlist.php';
						}
						var array_product = new Array();
						array_product = data.split('|');
						var strproduct = '已于'+array_product[1]+'自动保存';
						$('#automaticsave').html(strproduct);
						setTimeout('checkdraft()',2*60*1000);
					}
				});
			}
		}
	};
	
	//此函数为像商品编辑页面写入信息
	var	getproductinformation = function(data)
	{
		$('#product_name').val(data.productname);
		$('#product_content').val(data.productdescription);
		$('#product_price').val(data.price);
		for(var i=data.image.length-1; i>=0; i--)
		{
			$('.slist').append( "<span class=\'span_beta\' pid=\'"+data.image[i].id+"\' id=\'del_"+data.image[i].id+"\'><img src=\'/"+data.image[i].picurl+"\'><em><a class=\'delete\' pid=\'"+data.image[i].id+"\' href=\'javascript:void(0);\'>X</a></em></span><input type=\'hidden\' name=\'artname\' value=\'"+data.image[i].picurl+"\' id=\'artname_"+data.image[i].id+"\' />" );
		}
		showimage(data.image);
		if(data.max_number != '' && data.max_number != undefined && data.max_number != 0)
		{
			$('#product_num_checkbox').attr('checked',true);
			$('#product_num').show();
			$('#product_property_num').val(data.max_number);
		}
		if(data.old_price != '' && data.old_price != undefined)
		{
			$('#product_oldprice_checkbox').attr('checked',true);
			$('#product_oldprice').show();
			$('#product_property_oldprice').val(data.old_price);
		}
		if(data.express_price != '' && data.express_price != undefined)
		{
			$('#product_express_price_checkbox').attr('checked',true);
			$('#product_express_price').show();
			$('#product_property_express_price').val(data.express_price);
		}
//		if(data.end_time != '' && data.end_time != undefined)
//		{
//			$('#product_end_time_check').attr('checked',true);
//			$('#product_end_time').show();
//			$('#product_property_end_time').val(data.end_time);
//		}
		if(data.property.length > 0)
		{
			$('#product_property_check').attr('checked',true);
			if(data.property.length == 1)
			{
				$('.sitem2 .dd').show();
				$('.sitem2 .dd2').show();
				$('#product_property_beforeone').val(data.property[0].name);
				$('#product_property_afterone').val(data.property[0].content);
			}
			else
			{
				$('.sitem2 .dd').show();
				var str = '<div class="dd"><a style="cursor:pointer;" tid="two" class="btn"></a><input maxlength="4" id="product_property_beforesecond" tid="two" type="text" class="text" /><input id="product_property_aftersecond" tid="two" type="text" class="text" /></div>';
				$('.sitem2 .dd').after(str);
				$('#product_property_beforeone').val(data.property[0].name);
				$('#product_property_afterone').val(data.property[0].content);
				$('#product_property_beforesecond').val(data.property[1].name);
				$('#product_property_aftersecond').val(data.property[1].content);
			}
		}
		
	};
	//美食详情页面评论
	var findexpress = function(node){
		//从服务器获取表情
		$.post('/ajax/checkusername.php?action=getphiz', null, function(data){
			data = eval('('+data+')');
			if( data.length > 0 ){
				var html = '<div class="lineq"><span><b>表情</b></span><div class="closesign" onclick="closeTip($(this).parent().parent())">×</div></div>';
				html += '<ul>';
				for( var i=0; i<data.length; i++ ){
					html += '<li id="1" class="smiley"><img src="'+data[i].url+'" title="'+data[i].title+'" onclick="selectphiz($(this))" /> </li>' ;
				}
				html += '</ul>' ;
				$('.smileysbox').html( html ) ;
				$('.smileysbox').show();
				out = setTimeout( function(){closeTip(node.parent().parent().find('.smileysbox'));} , 5000 );
			}
		});
	};

	var selectphiz = function(node){
		var str = node.attr('title') ;
		var value = $("#sdsm").val() ;
		$("#sdsm").val(value+str).keydown();
		
		closeTip(node.parent().parent().parent());
	};

	var closeTip = function( node ){
		clearInterval(out);
		node.hide();
	};
	var copyText = function(txt)  
	{
		if(window.clipboardData)
		{
			window.clipboardData.clearData();
			if(window.clipboardData.setData("Text",txt))
			{
				document.getElementById('shareproduct_copy').className = 'ntxt';
				$('#shareproduct_copy').select();
			}
		}
		else
		{
			alert('请按Ctrl+C复制');
			$('#shareproduct_copy').select();
		}
	}; 