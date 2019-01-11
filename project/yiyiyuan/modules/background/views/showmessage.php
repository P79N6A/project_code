<?php $timeout=3; ?>

<style>
.box2{
	padding:20px 10px;
	margin:0 auto;
}
.well2 {
    background-color: #f5f5f5;
    border: 1px solid #e3e3e3;
    border-radius: 4px;
    box-shadow: 0 1px 1px rgba(0, 0, 0, 0.05) inset;
    margin-bottom: 20px;
    min-height: 20px;
    padding: 19px;
}

.hr2 {
    -moz-border-bottom-colors: none;
    -moz-border-left-colors: none;
    -moz-border-right-colors: none;
    -moz-border-top-colors: none;
    border-color: #eee -moz-use-text-color -moz-use-text-color;
    border-image: none;
    border-right: 0 none;
    border-style: solid none none;
    border-width: 1px 0 0;
    margin-bottom: 20px;
    margin-top: 20px;
    clear:both;
}
.alert2 {
    border: 1px solid transparent;
    border-radius: 4px;
    margin-bottom: 20px;
    padding: 15px;
}
.alert-success2 {
    background-color: #dff0d8;
    border-color: #d6e9c6;
    color: #3c763d;
}
.text-danger2 {
    color: #a94442;
}
.strong2 {
    font-weight: 700;
    line-height: 30px;
}
.c2{color:#428bca;}
.well2 .title img{
	width:30px;float:left;height:30px;margin-bottom: 5px;
}
.well2 .title div{
	height:30px;float:left;margin-bottom: 5px;line-height: 30px;font-weight: bold;font-size:16px;
}
</style>
<div class="property">
	<div class="general-width horizontal-center box2">
		<div class="well2" >
			<div class="alert2 alert-success2">
				<?=$res_data?>
			</div>
			<?php if($timeout && $redirect):?>
			<p>
				系统在 <strong class="text-danger2 strong2" id="time"><?=$timeout?></strong> 秒后自动跳转，如果不想等待，
				<a class="c2" href="<?=$redirect?>">点击这里跳转</a>
			</p>
			<?php endif;?>
		</div>
</div>
</div>

<?php if($timeout):?>	
<script type="text/javascript">
function delayURL(url) {
   var delay = document.getElementById("time").innerHTML;
   if(delay > 0){
    	document.getElementById("time").innerHTML = --delay;
    	setTimeout(function(){
    		delayURL(url);
    	}, 1000);
	} else {
		window.location.href = url;
	}
}
window.onload = function(){
	delayURL("<?=$redirect?>");
}
</script>
<?php endif;?>
	