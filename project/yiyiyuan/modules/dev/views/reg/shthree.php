<script>
	function schoolClosePage(){
		$('#bodyframe').hide();
		$('#bodyframe').html('');
	}
	
	function schoolShowPage(){
	    $('#bodyframe').show();
		$('#bodyframe').html(schoolCreate());
	}

	function schoolCreate(){
		var iframe = $('<iframe id="child" name="child" src="/html/school.html?v=1" class="iframe" scrolling="scrolling"></iframe>');

	    var wWidth = $(window).width();
        var iWidth = iframe.width();
        var wHeight = $(window).height();
        var iHeight = iframe.height();
        if (wHeight > iHeight) {
        	iframe.css('height', wHeight);
//			$('.container').css('height',wHeight);
        	iframe.css('width', wWidth);
//			$('.container').css('width',iWidth);
        } else {
//			$('.container').css('height',iHeight);
//                        $('.container').css('width',iWidth);
    	}
    	return iframe;
	}

    function showSchool() {
    	schoolShowPage();
        /*var UA = navigator.userAgent;
        if (UA.match(/iPad/) || UA.match(/iPhone/) || UA.match(/iPod/)) {
            window.frames[0].pinyin = true;
            window.frames[0].ClientInit();
            window.frames[0].showArea();
        }else{
       	 	
            var o= document.getElementById('child');
           	o.contentDocument.showArea();
        }*/
    }

</script>
<div class="container">
    <div class="content">
        <form class="form-horizontal" role="form">
            <p class="mb20">
                <input type="hidden" name="user_id" value="<?php echo $userinfo['user_id'];?>" />
                <input type="text" readonly="readonly" value="<?php echo $userinfo->school != '' ? $userinfo->school : ''; ?>" name="school_name" id="reg_school_name" maxlength="20" placeholder="学校" onclick="showSchool()"  class="form-control"/> 
                <input type="hidden" name="school" id="reg_school" value="<?php echo $userinfo->school_id != 0 ? $userinfo->school_id : 0; ?>"/>
            </p>
            <p class="p_ipt mb20">
                <select name="edu" id="reg_edu" class="form-control">
                    <option value="0">最高学历</option>
                    <option value="1" <?php if ($userinfo->edu == 1): ?>selected="selected"<?php endif; ?>>博士</option>
                    <option value="2" <?php if ($userinfo->edu == 2): ?>selected="selected"<?php endif; ?>>硕士</option>
                    <option value="3" <?php if ($userinfo->edu == 3): ?>selected="selected"<?php endif; ?>>本科</option>
                    <option value="4" <?php if ($userinfo->edu == 4): ?>selected="selected"<?php endif; ?>>专科</option>
                </select>
            </p>
            <p class="p_ipt mb20">
                <select name="school_time" id="reg_school_time" class="form-control">
                    <option value="0">入学年份</option>
                    <option value="2015" <?php if ($userinfo->school_time == 2015): ?>selected="selected"<?php endif; ?>>2015</option>
                    <option value="2014" <?php if ($userinfo->school_time == 2014): ?>selected="selected"<?php endif; ?>>2014</option>
                    <option value="2013" <?php if ($userinfo->school_time == 2013): ?>selected="selected"<?php endif; ?>>2013</option>
                    <option value="2012" <?php if ($userinfo->school_time == 2012): ?>selected="selected"<?php endif; ?>>2012</option>
                    <option value="2011" <?php if ($userinfo->school_time == 2011): ?>selected="selected"<?php endif; ?>>2011</option>
                    <option value="2010" <?php if ($userinfo->school_time == 2010): ?>selected="selected"<?php endif; ?>>2010</option>
                    <option value="2009" <?php if ($userinfo->school_time == 2009): ?>selected="selected"<?php endif; ?>>2009</option>
                    <option value="2008" <?php if ($userinfo->school_time == 2008): ?>selected="selected"<?php endif; ?>>2008</option>
                    <option value="2007" <?php if ($userinfo->school_time == 2007): ?>selected="selected"<?php endif; ?>>2007</option>
                    <option value="2006" <?php if ($userinfo->school_time == 2006): ?>selected="selected"<?php endif; ?>>2006</option>
                    <option value="2005" <?php if ($userinfo->school_time == 2005): ?>selected="selected"<?php endif; ?>>2005</option>
                </select>
            </p>
            <button type="button" id="reg_shthree_form" class="btn mb20" style="width:100%;" ><?php if ($userinfo->status == '3' || $userinfo->status == '2'): ?>确定<?php else: ?>下一步<?php endif; ?></button>
            <!--<p class="text-right mb20"><a href="<?php //echo $redirurl; ?>">点击跳过</a></p>-->
            <img src="/images/dev/line.png" width="100%" class="mb20"/>
            <p class="p">*完成以上信息可以把您的筹款需求匿名发布到您的校友中。</p>
        </form>
    </div>
    <div id="bodyframe" style="display: none; position: fixed;top:0;left: 0;">
        <iframe id='child' name='child' src="/html/school.html?v=1" class="iframe" scrolling="scrolling"></iframe>
    </div> 
</div>

<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
                    wx.config({
                        debug: false,
                        appId: '<?php echo $jsinfo['appid']; ?>',
                        timestamp: <?php echo $jsinfo['timestamp']; ?>,
                        nonceStr: '<?php echo $jsinfo['nonceStr']; ?>',
                        signature: '<?php echo $jsinfo['signature']; ?>',
                        jsApiList: [
                            'hideOptionMenu'
                        ]
                    });

                    wx.ready(function() {
                        wx.hideOptionMenu();
                    });
</script>