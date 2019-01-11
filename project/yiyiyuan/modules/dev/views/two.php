

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
        <form class="form-horizontal" role="form" method="POST" action="/dev/reg/twosave" id="reg_student_form">
            <p class="mb20">
                <input type="text" class="form-control" name="school_name" id="reg_school_name" readonly="readonly" maxlength="20" placeholder="学校" onclick="showSchool()"/>
                <input type="hidden" name="school" id="reg_school" value="0"/>
            </p>
            <p class="p_ipt mb20">
                <select name="edu" id="reg_edu" class="form-control">
                    <option value="0">请选择学历</option>
                    <option value="1">博士</option>
                    <option value="2">硕士</option>
                    <option value="3">本科</option>
                    <option value="4">专科</option>
                </select>
            </p>
            <p class="p_ipt mb20">
                <select name="school_time" id="reg_school_time" class="form-control">
                    <option value="0">请选择入学年份</option>
                    <option value="2015">2015</option>
                    <option value="2014">2014</option>
                    <option value="2013">2013</option>
                    <option value="2012">2012</option>
                    <option value="2011">2011</option>
                    <option value="2010">2010</option>
                    <option value="2009">2009</option>
                    <option value="2008">2008</option>
                    <option value="2007">2007</option>
                    <option value="2006">2006</option>
                    <option value="2005">2005</option>
                </select>
            </p>
            <p class="mb20"><input type="text" name="realname" id="reg_realname" maxlength="10" placeholder="姓名" value="<?php echo $users['realname']; ?>" class="form-control"/></p>
            <p class="mb40"><input type="text" name="identity" id="reg_identity" maxlength="18" is_real='<?php echo!empty($users['identity']) ? 1 : 0; ?>' placeholder="身份证号" value="<?php echo $users['identity']; ?>" class="form-control"/></p>
            <input type="hidden" id="from_url" value="<?php echo $from; ?>" />
            <input type="hidden" id="f_url" value="<?php echo $f; ?>" />
            <button type="button" class="btn mb20" id="reg_two_form" style="width:100%;" >确定</button>

        </form>
    </div>
    <div id="bodyframe" style="display: none; position: fixed;top:0;left: 0;">
        <iframe id='child' name='child' src="/html/school.html?v=1" class="iframe" scrolling="scrolling"></iframe>
        <!--<iframe name="child" src="./child.html" ></iframe>-->
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