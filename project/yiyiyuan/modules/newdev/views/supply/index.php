<?php
use yii\helpers\Url;
use app\commonapi\ImageHandler;
$uploadurl = ImageHandler::$img_upload;

?>

<div class="bczil">
	<form target="_self" id="supplyForm" action="<?php echo Url::to('/new/supply')?>" method="post">
	<h3 class="renybt">＊请根据工作人员的要求上传照片</h3>
	<div class="bczil_photo">
			
		<span  class="cardUpload">
			<?php 
			$path = isset($imgList[0]) ? $imgList[0]['img'] : '';
			$url = $path ?  ImageHandler::getUrl($path)  : $imgDefault;
			?>
			<input type="hidden" id="supply1Id" name="supplyId[1]" value="<?=isset($imgList[0]) ? $imgList[0]['id'] : '';?>" />
			<input  type="hidden" id="supply1Url" name="supplyUrl[1]" value="<?=$path?>"/>
			<img id="supply1" src="<?=$url?>" width="100%">
		</span>
		
		<span  class="cardUpload">
			<?php 
			$path = isset($imgList[1]) ? $imgList[1]['img'] : '';
			$url = $path ?  ImageHandler::getUrl($path) : $imgDefault;
			?>
			<input type="hidden" id="supply2Id" name="supplyId[2]" value="<?=isset($imgList[1]) ? $imgList[1]['id'] : '';?>" />
			<input  type="hidden" id="supply2Url" name="supplyUrl[2]" value="<?=$path?>"/>
			<img id="supply2" src="<?=$url?>" width="100%">
		</span>
		
		<span  class="cardUpload">
			<?php 
			$path = isset($imgList[2]) ? $imgList[2]['img'] : '';
			$url = $path ? ImageHandler::getUrl($path) : $imgDefault;
			?>
			<input type="hidden" id="supply3Id" name="supplyId[3]" value="<?=isset($imgList[2]) ? $imgList[2]['id'] : '';?>" />
			<input  type="hidden" id="supply3Url" name="supplyUrl[3]" value="<?=$path?>"/>
			<img id="supply3" src="<?=$url?>" width="100%">
		</span>
		
	</div>
	
	<p id="submit-err" style="color:red;text-align:center;"><?=$saveMsg?></p>
		
	<p class="photopx">※ 上传身份证正反面有助于加速借款审批！<br/>※ 请务必上传清晰的照片，以保证顺利借款。</p>

</div>

<div class="button" id="btokdiv" style="display:none;"> 
	<button type="button" id="btok">确定</button>
</div>
</form>

<script src="<?=$uploadurl?>/js/imgupload.js?m=v7" type="text/javascript"></script>
<script src="<?=$uploadurl?>/js/resizeimg/dist/lrz.bundle.js" type="text/javascript"></script>
<script type="text/javascript">

var showErr = function(id,msg){
	$("#submit-err").html(msg);
}
var fnAfter = function(data){
	// 验证回调结果
	var ok = data && parseInt(data.res_code,10) === 0;
	if( !ok ){
		$("#btok").html("确定");
		showErr(data.res_code, data.res_data);
		return null;
	}
	
	// 写入到本地表单中
	var urls =  data.res_data;
	for(var id in urls ){
		$("#"+id + 'Url').val(urls[id]);
	}
	
	$("#supplyForm").submit();
}
$(function(){
	var oUpload = new ImageUpload({
		"formid" : "uploadImgForm",
		'action':"<?=$uploadurl?>/upload",
		"encrypt" : "<?=$encrypt?>",
		"error" : showErr,
		'afterSave' : fnAfter,
		'onupload': function(){
			$("#btok").html("正在上传中");
		}
	});
	
	var i, img,id, url;
	for(var i=1; i<=3; i++){
		img = 'supply'+i;
		id = document.getElementById(img+'Id').value;
		
		// 只可添加,不可修改
		if( !id ){
			url =  document.getElementById(img+'Url').value;
			oUpload.add(img, url,function(id,rst){
				document.getElementById(id).src = rst.base64;
				$("#btokdiv").show();
			});
		}
	}
	
	// 图片上传绑定
	$("#btok").click(function(){
		oUpload.save();
	});
	
});

</script>
<!-- end 图片上传 -->
