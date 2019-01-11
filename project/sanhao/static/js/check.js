var flag7=false;
//上传图片
var upfile = function(){
	//判断是否有图片在上传中
	if( $('#imgFile').attr('doing') != '0' ){
		return false;
	}
	$("#upload_pic_error").html( '' ) ;
	//先验证图片格式是否正确
	var filename = $('#imgFile').val();
  	
	var index1=filename.lastIndexOf(".");
	var index2=filename.length;
	var postf=filename.substring(index1,index2);
	postf = postf.toLocaleLowerCase();
	$("#upload_pic_error").html('');
	//图片后缀支持jpg、jpeg、png
	if(/.(jpg|jpeg|png)$/.test(postf) === false){
		$("#upload_pic_error").css('color','red').html( '仅支持JPG、JPEG、PNG格式\图片不应大于5M' ) ;
		$('#personal_head_error').val('error');
		flag7 = false;
	}else{
		//显示上传中的gif图片，然后将文件上传控件disabled掉
		$("#review_photo").attr('doing','1');
		//上传图片
		$.ajaxFileUpload({
			url:'/ajax/checkusername.php?action=uploadfile',
			secureuri:false,
			fileElementId:'imgFile',
			dataType:'json',
			success:function(data) {
				//1.验证大小错误
				if( data == 2 ){
					$("#upload_pic_error").css('color','red').html( '图片过小，图片尺寸需大于50*50！' ) ;
					$("#imgFile").attr('doing','0');
					$('#personal_head_error').val('error');
					flag7 = false;
				}
				else if(data == 1)
				{
					$("#upload_pic_error").css('color','red').html( '仅支持JPG、JPEG、PNG格式\图片不应大于5M！' ) ;
					$("#imgFile").attr('doing','0');
					$('#personal_head_error').val('error');
					flag7 = false;
				}
				//2.验证上传错误
				else if( data == 0 ){
					$("#upload_pic_error").css('color','red').html( '不要气馁，再来一次！' ) ;
					$("#imgFile").attr('doing','0');
					$('#personal_head_error').val('error');
					flag7 = false;
				}
				//3.上传成功处理
				else{
					//处理传递过来的信息
					var array_img = new Array();
					array_img = data.split('|');
					$("#imgFile").attr('doing','0');
					$("#artname").val(array_img[0]) ;
//					var smallwidth = array_img[1];
//					var smallheight = array_img[2];
//					if(smallwidth > smallheight)
//					{
//						var raio = 180/smallwidth;
//						var newwidth = parseInt(smallwidth*raio, 10);
//						var newheight = parseInt(smallheight*raio, 10);
//					}
//					else if(smallwidth < smallheight)
//					{
//						var raio = 180/smallheight;
//						var newwidth = parseInt(smallwidth*raio, 10);
//						var newheight = parseInt(smallheight*raio, 10);
//					}
//					else
//					{
//						var raio = 180/smallwidth;
//						var newwidth = parseInt(smallwidth*raio, 10);
//						var newheight = parseInt(smallheight*raio, 10);
//					}
					//$(".scImg img").attr('src','/'+data);
					$('#scImg').html( "<img src=\'/"+array_img[0]+"\' class='img'>" );
//					$('#scImg img').css('width',newwidth);
//					$('#scImg img').css('height',newheight);
					flag7 = true;
				}
			}
		});
	}
};
