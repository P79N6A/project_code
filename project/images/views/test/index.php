<?php
use yii\widgets\ActiveForm;
?>

<form method="post" id="saveData" action="http://www.test.com/image/post.php">
	<div>显示区域</div><div id="showscale"></div>
	<img style="max-width: 200px;" src="http://uploads.xianhuahua.com/img/icon_wt.png" id="img1">
	<img style="max-width: 200px;"  src="http://uploads.xianhuahua.com/img/icon_wt.png" id="img2">
	<img style="max-width: 200px;"  src="http://uploads.xianhuahua.com/img/icon_wt.png" id="img3">
	<button type="button" id="saveImg">保存图片</button>
	
	<input type="hidden" id="img1_pt" name="img1_ipt" />
	<input type="hidden" id="img2_pt" name="img2_ipt" />
	<input type="hidden" id="img3_pt" name="img3_ipt" />

	
	<img style="max-width: 200px;"  src="" id="preview">
</form>

<script src="/js/imgupload.js" type="text/javascript"></script>
<script src="/js/resizeimg/dist/lrz.bundle.js" type="text/javascript"></script>
<script type="text/javascript">

var parseData = function(data){
	if( !data ){
		alert("没有上传成功");
		return null;
	}
	if( parseInt(data.res_code,10) === 0){
		alert('上传成功');
		return data.res_data;
	}else{
		alert("上传失败" +  data.res_data);
		return null;
	}
}
var fnAfter = function(data){
	// 验证回调结果
	data = parseData(data);
	if(!data){
		return null;
	}
	
	// 写入到本地表单中
	for(var id in data ){
		$("#"+id + '_pt').val(data[id]);
	}
	
	$("#saveData").submit();
}
$(function(){
	var oUpload = new ImageUpload({
		"formid" : "uploadImgForm",
		'action' : "/upload",
		"encrypt" : "<?=$encrypt?>",
		'afterSave' : fnAfter,
	});
	
	oUpload.add('img1','/yiyiyuan/test/2016/03/24/1.jpg',function(id, rst, original){
		$('#'+id)[0].src = rst.base64;
		
		// 压缩前后
		var scale = (rst.fileLen / original.size).toFixed(2);
		
		$("#showscale").html( "压缩图/原图比:" + scale  );
	});
	oUpload.add('img2','',function(id, rst, original){
		$('#'+id)[0].src = rst.base64;
		$('#preview')[0].src = rst.base64;
	});
	oUpload.add('img3','');
	
	// 图片上传绑定
	$("#saveImg").click(function(){
		oUpload.save();
	});
	
});

</script>
<!-- end 图片上传 -->