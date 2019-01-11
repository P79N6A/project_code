<div class="clickwebg">
	<img src="/images/valentine/myxcdx.png">
</div>
<div class="topdszhi">
    <p id="show_code" style="display: none;"><?php echo $code;?></p>
</div>
<div class="renjiah">
    <button class="renjiathree" id="search_mycode"><img src="/images/valentine/ckwdah.png"></button>
    <a href="/dev/valentine/letterdetail?vid=<?php echo $vid;?>"><button class="renjiafour"><img src="/images/valentine/zindex.png"></button></a>
</div>


<div class="dldtdd">
<?php if(!empty($valentine_list)):?>
<?php foreach ($valentine_list as $v):?>
    <dl>
        <dt><img src="<?php echo $v['head'];?>"></dt>
        <dd><?php echo $v['nickname'];?></dd>
    </dl>
  <?php endforeach;?>
 <?php endif;?>
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
$('#search_mycode').click(function(){
	$("#show_code").show();
});
</script>