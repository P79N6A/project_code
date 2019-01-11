<?php 
$_csrf = \Yii::$app->request->getCsrfToken();
 ?>
<header class="g-header">
	<div class="l-container clearfix">
		<a href="<?php echo Yii::$app->request->hostInfo ?>" class="g-header-logo" style="overflow:hidden">
			<img src="/images/web/mifulogo.png">
		</a>
		<span class="denglu">注册</span>
	</div>
</header> 

<div class="loading">
	<div class="l-container">
		<div class="loadbanner">
			<div class="l-left main">
			    <div class="jcbd jcbdes">
			    	<input type="hidden" id="_csrf" value="<?php echo $_csrf ?>">
			        <ul>
			            <li class="noBorder">
			                <div class="col-xs-3">手机号:</div>
			                <div class="col-xs-8">
			                    <input type="text" id="mobile" placeholder="请输入手机号">
			                </div>
			            </li>
			            <li class="noBorder">
			                <div class="col-xs-3">验证码：</div>
			                <div class="col-xs-9">
			                    <input type="text" id="code" placeholder="请输入验证码">
			                    <button type="button" id="getCode" class="btn">获取</button>
			                    <button type="button" id="getCode2" class="btn" style="color:#dcdcdc;border:1px solid #dcdcdc;display:none">获取</button>
			                </div>
			            </li>
			            <li class="noBorder">
			                <div class="col-xs-3">邀请码：</div>
			                <div class="col-xs-8">
			                    <input type="text" id="from_code" placeholder="请输入邀请码(非必填)">
			                </div>
			            </li>
			            <li>
			                <div class="col-xs-12 showError">&nbsp;</div>
			            </li>
			        </ul>
			    </div>
			    <button class="jebangding">下一步</button>
			    <div class="noehtyxh">
	                <input type="checkbox" checked="checked" readonly id="checkbox-1" value="1" class="regular-checkbox">
	                <label for="checkbox-1"></label>
	                阅读并同意
	                <a href="<?php echo Yii::$app->request->hostInfo.'/aboutus/register' ?>" target="_blank" class="underL">《花生米富注册协议》</a>
	            </div>
			</div>
			<img class="l-right"  src="/images/web/loading3.png">
		</div>		
	</div>	
</div>
<script>
	var isreg = "<?php echo Yii::$app->request->hostInfo;?>/default/isreg";
	var getcode = "<?php echo Yii::$app->request->hostInfo;?>/default/getcode";
	var reg = "<?php echo Yii::$app->request->hostInfo;?>/default/reg";
	var index = "<?php echo Yii::$app->request->hostInfo;?>/site/index";
</script>
<script type="text/javascript" src="/js/web/reg.js?v=20161104001"></script>