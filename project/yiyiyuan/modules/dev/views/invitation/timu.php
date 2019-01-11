<div class="selfmess">
	<p class="selftxt">请填写以下信息以保证认证的有效性</p>
	<div class="selftximg">
		<div class="dbk_inpL">
        	<label>公司名称</label><input placeholder="输入您的公司名称" type="text" name='company'>
    	</div>
    	<div class="dbk_inpL">
        	<label>职业</label><input placeholder="输入您的职业" type="text" name='position'>
    	</div>
    	<div class="dbk_inpL">
        	<label>关系</label>
			<select name ='relation' id='relation'>
			<option value='同事'>同事</option>
            <option value='亲戚／家人'>亲戚／家人</option>
            <option value='同学／校友'>同学／校友</option>
			<option value='朋友(其他)'>朋友(其他)</option>
			</select>
    	</div>
		<input  type="hidden" name='wid' id='wid' value=<?php echo $wid;?>>
    	<!--div class="selfjt"><img src="/images/account/selfjt.png"></div-->
    	<!--div class="selfjtt"><img src="images/selfjtt.png"></div-->
	</div>
	<!--div class="select">
		<p>同事</p>
		<p>亲戚／家人</p>
		<p>同学／校友</p>
		<p>朋友（其他）</p>
	</div-->
	<div class="button"> <button id='class_button'>提交</button></div>
	<a href='/dev/invitation/success?userid=<?php echo $wid;?>' class="ontg">跳过>></a>
<script>
$('#class_button').click(function(){
   var company = $("input[name='company']").val();
   var position = $("input[name='position']").val();
   var relation = $("#relation").val();
   var wid = $('#wid').val();
   $.post("/dev/invitation/zhisave",{company:company,position:position,relation:relation,userid:wid},function(data){
     //alert(data);
	  var data = eval("(" + data + ")");
	  if(data.ret =='0'){
        window.location = '/dev/invitation/success?userid='+wid; 
	  }else if(data.ret =='1'){
	    window.location = '/dev/invitation/success?userid='+wid; 
	  }
   })
});
</script>
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