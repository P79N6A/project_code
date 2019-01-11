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
    <img src="/images/valentine/witherxin.png">
</div>
<div class="xinfengpage">
    <img src="/images/valentine/xinpage.png">
    <div class="xinjconte">
        <p><input placeholder="输入收信人" maxlength="20" id="nickname" name="nickname"></p>
        <textarea placeholder="请输入内容（120字以内）" maxlength="120" id="content" name="content"></textarea>
        <p class="wxnic"><?php echo $nickname;?></p>
    </div>
</div>
<div class="clickbutton twookok">
	<input type="hidden" id="wid" value="<?php echo $wid;?>" />
    <button class="letter_button"><img src="/images/valentine/OK.png"></button>
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
$('.letter_button').click(function(){
	var nickname = $("#nickname").val();
	var content = $("#content").val();
	var wid = $("#wid").val();

	if(nickname == '' || nickname == undefined){
		$("#nickname").focus();
		return false;
	}

	if(nickname.length > 20){
		alert('收信人昵称太长，请修改');
		return false;
	}

	if(content == '' || content == undefined){
		$("#content").focus();
		return false;
	}

	if(content.length > 120){
		alert('输入内容过长，请控制在120字以内');
		return false;
	}

	$(this).attr('disabled', true);
	$.post("/dev/valentine/lettersave", {wid : wid,nickname : nickname,content : content}, function (result) {
		var data = eval("(" + result + ")");
		if (data.ret == '0') {
			window.location = "/dev/valentine/confirm?vid="+data.vid;
		}else{
			$('.letter_button').attr('disabled', false);
			alert('系统错误');
			return false;
		}
	});
});
</script>