<textarea class="keyword" placeholder="请在这里输入你的意见或建议,我们会认真阅读，并且会认真改进我们的产品......" name='content' id='content'></textarea>
    <div class="disitem">
        <button class="button_anniu">发 送</button>
    </div>
<script>
$('.button_anniu').click(function(){
	var content = $('#content').val();
	$(this).attr('disabled', true);
	   $.post("/background/webunion/method", {content: content}, function (result) {

            var data = eval("(" + result + ")");
            if(data.ret==1){
				alert('提交成功');
				window.location.href="/background/webunion/index"; 
			}
        });
});
</script>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
wx.config({
        debug: false,
        appId: '<?php echo $jsinfo['appid']; ?>',
        timestamp: <?php echo $jsinfo['timestamp']; ?>,
        nonceStr: '<?php echo $jsinfo['nonceStr']; ?>',
        signature: '<?php echo $jsinfo['signature']; ?>',
        jsApiList: [
            'closeWindow',
            'hideOptionMenu'
        ]
    });

    wx.ready(function() {
        wx.hideOptionMenu();
    });
</script>
