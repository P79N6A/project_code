   <style type="text/css">
        html{position: absolute;height: 100%;}
        body{
            height: 100%;}
    </style>
    <script type="text/javascript">
        $(function(){
            var bodyHeight = $(document).height();
                $("body").height(bodyHeight);
        });
    </script>

<div class="clickwebg">
	<img src="/images/valentine/shuruanhao.png">
</div>
<div class="shezhianhao shurahs">
    <p><input placeholder="请输入暗号" maxlength="6" id="code" name="code"></p>
</div>
<div class="clickbutton twookok">
<input type="hidden" id="vid" value="<?php echo $vid;?>" />
    <button class="search_code"><img src="/images/valentine/OK.png"></button>
</div>


<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
  wx.config({
	debug: false,
	appId: '<?php echo $jsinfo['appid'];?>',
	timestamp: <?php echo $jsinfo['timestamp'];?>,
	nonceStr: '<?php echo $jsinfo['nonceStr'];?>',
	signature: '<?php echo $jsinfo['signature'];?>',
	jsApiList: [
		'hideOptionMenu'
	  ]
  });
  
  wx.ready(function(){
	  wx.hideOptionMenu();
	});
</script> 
<script>
$('.search_code').click(function(){
	var code = $("#code").val();
	var vid = $("#vid").val();
	if(code == '' || code == undefined){
		alert("请输入暗号");
		return false;
	}

	$(this).attr('disabled', true);
	$.post("/dev/valentine/searchcode", {vid : vid,code : code}, function (result) {
		var data = eval("(" + result + ")");
		if (data.ret == '0') {
			window.location = "/dev/valentine/success?vid="+vid;
		}else{
			window.location = "/dev/valentine/error?vid="+vid;
		}
	});
});

</script>