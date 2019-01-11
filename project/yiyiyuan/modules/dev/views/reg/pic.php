<?php    
use app\commonapi\ImageHandler;
$uploadurl = ImageHandler::$img_upload;
?>
<div class="container">
          <div class="content text-center">
           <form id="reg_pic_form" name="reg_pic_form" action="/dev/reg/picsave" method="POST" enctype="multipart/form-data" >
          	<p class="b30 text-center mb20">持证自拍照</p>
            <img src="/images/dev/00.png" width="40%" id="chooseImage">
            <p class="cor_b2 text-left n20 mt40"><img src="/images/dev/tip.png" width="3.5%"/> 请严格按照要求进行拍照，否则将不能完成此次借款。</p>
            <p class="red text-center b38 border-red"><?php echo $pictype['title'];?></p>
            <input type="hidden" name="serverid" value="" id="reg_serverid">
            <input type="hidden" name="user_id" value="<?php echo $userinfo['user_id'];?>" />
            <input type="hidden" name="pic_type" id="reg_pic_type" value="<?php echo $pictype['id'];?>"> 
            <input type="hidden" name="pic_identity" id="pic_identity" value="<?php echo $pic_identity;?>" /> 
          	<input type="button" id="reg_button" disabled="disabled" value="提交认证"  class="btn dis" style="width:100%;" >
            <hr style="border-color:#e74747;"/>
            <p class="text-left b30">示例照片:</p>
            <p class="text-center mt20">
            	<img src="<?php echo $pictype['pic'];?>" width="54%"/>
            </p>
            </form>
          </div>
       </div>
<script type="text/javascript">
/**
 * 微信图片保存到图片服务器下载. 然后将链接保存到本地
 */
function WxImag(){
	var me = this;
	me.oButton = $("#reg_button");
	me.oForm = $("#reg_pic_form");
	// 保存图片请求操作
	me.ajaxSave = function(url, data){
			// 禁用
			$.ajax({
					type : "POST",
					url  : url,
					data : data,
					dataType : "jsonp",
					async    : false,
					success  : me.success,
					error:function(d){
					 console.log(d);
					}
			});
	};
	// 成功回调函数
	me.success  = function(data){
			if( data && parseInt(data.res_code,10) === 0){
					var url = data.res_data;
					$("#pic_identity").val(url);
					me.oForm.submit();
			}else{
					alert(data.res_data);
					me.oButton.removeAttr("disabled"); 
			}
	};

	// 进行提交操作
	me.imgSave = function(){
		// 参数验证
			var media_id = $("#reg_serverid").val();
			if(!media_id){
				  alert("请选择一张图片");
					return false;
			}
			var data = {
				encrypt:"<?php echo $encrypt;?>",
				access_token:"<?php echo $access_token;?>",
				media_id : media_id,
				url:  $("#pic_identity").val(),		
			};
			
			me.ajaxSave( "<?=$uploadurl?>/downwx", data);
		  me.oButton.attr("disabled","disabled");
	}
	
	// 初始化操作
	me.init  = function(){
			me.oButton.click(me.imgSave);
	};
	
	me.init();
}
$(function(){
	new WxImag();
});
</script>

<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
  wx.config({
	debug: false,
	appId: '<?php echo $jssdkparam['appid'];?>',
	timestamp: <?php echo $jssdkparam['timestamp'];?>,
	nonceStr: '<?php echo $jssdkparam['nonceStr'];?>',
	signature: '<?php echo $jssdkparam['signature'];?>',
	jsApiList: [
		'chooseImage',
		'previewImage',
		'uploadImage',
		'downloadImage',
		'hideOptionMenu'
		'startRecord'
	  ]
  });
  
  wx.ready(function(){
	  wx.hideOptionMenu();
    // config信息验证后会执行ready方法，所有接口调用都必须在config接口获得结果之后，config是一个客户端的异步操作，所以如果需要在页面加载时就调用相关接口，则须把相关接口放在ready函数中调用来确保正确执行。对于用户触发时才调用的接口，则可以直接调用，不需要放在ready函数中。
	});
	wx.error(function (res) {
	  alert(res.errMsg);
	});


  var images = {
    localId: [],
    serverId: []
  };
  document.querySelector('#chooseImage').onclick = function () {
    wx.chooseImage({
      success: function (res) {
        images.localId = res.localIds;
		$("#chooseImage").attr("src",res.localIds[0]);
		var upload = function() {
			  wx.uploadImage({
					localId: images.localId[0],
					success: function (ret) {
						var serverId = ret.serverId; // 返回图片的服务器端ID
						$("#reg_serverid").val(serverId);
						$("#reg_button").removeClass('dis');
						$("#reg_button").removeAttr('disabled');
				}
			  });
		};
		upload();
      }
    });
  };
</script>
