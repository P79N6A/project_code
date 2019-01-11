<?php
use yii\widgets\ActiveForm;
?>
<script src="/js/imgupload.js" type="text/javascript"></script>
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
		alert(data[id]);
	}
	
}
</script>

<form style="display:block;" id="uploadImgForm" name="uploadImgForm" action="/imageupload" method="POST"  enctype="multipart/form-data" >								
<input name="encrypt" value="<?=$encrypt?>" type="hidden">							

<div id="img1_group">

<input id="img1_ext" name="img1[ext]" value="" type="hidden">	
<input id="img1_url" name="img1[url]" value="" type="hidden">	
<input id="img1_filename" name="img1[file]"  type="file">
<input type="button" onclick="iframepost(this.form,fnAfter)" value="提交" name="提交" />
</div></form>

