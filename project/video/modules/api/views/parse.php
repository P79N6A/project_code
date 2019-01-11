<?php   
use yii\helpers\Html;
?>
<div class="row">
	<div class="col-md-8">	
		<form action="parse" method="post" target="_self">
		<div>解析内容输入到下方</div>
		<textarea rows="20" placeholder="<?php echo Html::encode($jsonData);?>" cols="100"  type="input" name="jsonData"></textarea>
		<br />
		<input type="submit" name="提交" value="提交" />
		</form>	
		
	</div>

	<div class="col-md-4">
		<div>解析内容输入到下方</div>
		<pre><?php 
			var_export($res);
			?>
		</pre>	
	</div>
</div>

