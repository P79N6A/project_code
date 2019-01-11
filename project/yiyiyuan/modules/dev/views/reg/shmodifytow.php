<script type="text/javascript">
    (function() {
        _fmOpt = {
            partner: 'xianhuahua',
            appName: 'xianhh_web',
            token: '<?php echo $_COOKIE['PHPSESSID'] ?>',
        };
        var cimg = new Image(1, 1);
        cimg.onload = function() {
            _fmOpt.imgLoaded = true;
        };
        cimg.src = "https://fp.fraudmetrix.cn/fp/clear.png?partnerCode=xianhuahua&appName=xianhh_web&tokenId=" + _fmOpt.token;
        var fm = document.createElement('script');
        fm.type = 'text/javascript';
        fm.async = true;
        fm.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'static.fraudmetrix.cn/fm.js?ver=0.1&t=' + (new Date().getTime() / 3600000).toFixed(0);
        var s = document.getElementsByTagName('script')[0];
        s.parentNode.insertBefore(fm, s);
    })();
</script>
<div class="container">
	<div class="content">
                <form class="form-horizontal" role="form">
                	<!--<a href="#" class="cor_4">-->
                        <p class="p_ipt mb20">
                            <input type="hidden" name="user_id" value="<?php echo $user->user_id;?>" />
                            <select name="industry" id="reg_industry" class="form-control">
	                    	  <option value="0">请选择行业</option>
							   
	                    	  <?php foreach ( $indus as $ind ){ ?>
							  <option value="<?php echo $ind['number']?>"><?php echo $ind['name'];?></option>
							  <?php }?>
							</select>
                        </p>
                    <!--</a>-->
                    <a href="#" class="cor_4">
                        <p class=" mb20">
                            <input type="text" name="company" id="reg_company" placeholder="公司全称"  class="form-control"/>
                        </p>
                    </a>
                    <!--<a href="#" class="cor_4">-->
                        <p class="p_ipt mb20">
                            <select name="position" id="reg_position" class="form-control">
	                    	  <option value="0">请选择职位</option>
							  <?php foreach ( $posi as $pos ){ ?>
							  <option value="<?php echo $pos['number']?>"><?php echo $pos['name'];?></option>
							  <?php }?>
							</select>
                        </p>
                    <!--</a>-->
                      <input type="hidden" id="from_url" value="<?php echo $from;?>" />
                      <input type="hidden" id="f_url" value="<?php echo $f;?>" />
                     <button type="button" id="reg_shmodifytow_form" class="btn mb20" style="width:100%;" >下一步</button>
                </form>
            </div>
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