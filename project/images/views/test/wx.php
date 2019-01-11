<?php
use yii\widgets\ActiveForm;
?>

<form method="post" id="saveData" action="/downwx">
	<div>显示区域</div>
	
	<input type="text" name="access_token" id="" value="<?php echo $access_token;?>" />
	<input type="text" name="media_id" id="" value="<?php echo $media_id;?>" />
	<input type="text" name="encrypt" id="" value="<?php echo $encrypt;?>" />
	<input type="text" name="url" id="" value="<?php echo $url;?>" />
	
	<button type="submit" id="saveImg">保存图片</button>
</form>