<script type="text/javascript">
    (function () {
        _fmOpt = {
            partner: 'xianhuahua',
            appName: 'xianhh_web',
            token: '<?php echo $_COOKIE['PHPSESSID'] ?>',
        };
        var cimg = new Image(1, 1);
        cimg.onload = function () {
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

    function schoolClosePage() {
        $('#bodyframe').hide();
        $('#bodyframe').html('');
    }
    function schoolShowPage() {
        $('#bodyframe').show();
        $('#bodyframe').html(schoolCreate());
    }

    function schoolCreate() {
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
<div class="selfmess">
    <!--div class="endmess">
            <div class="endmessimg"><img src="images/timez2.png"></div>
            <p class="reds">个人信息<br/> 200/200 </p>
            <p class="img2 reds">公司信息<br/> 200/200 </p>
            <p class="img3">信用信息<br/> 200/200 </p>
    </div-->
    <div class="selftximg">
        <div class="dbk_inpL">
            <label>学校</label>
            <input type="text" readonly="readonly" value="<?php echo $userinfo->school != '' ? $userinfo->school : ''; ?>" name="school_name" id="reg_school_name" maxlength="20" placeholder="学校" onclick="showSchool()"  class="form-control"/> 
            <input type="hidden" name="school" id="reg_school" value="<?php echo $userinfo->school_id != 0 ? $userinfo->school_id : 0; ?>"/>
        </div>
        <div class="dbk_inpL">
            <label>学历</label>
            <select name="edu" id="reg_edu" class="form-control">
                <option value="0">最高学历</option>
                <option value="1" <?php if ($userinfo->edu == 1): ?>selected="selected"<?php endif; ?>>博士</option>
                <option value="2" <?php if ($userinfo->edu == 2): ?>selected="selected"<?php endif; ?>>硕士</option>
                <option value="3" <?php if ($userinfo->edu == 3): ?>selected="selected"<?php endif; ?>>本科</option>
                <option value="4" <?php if ($userinfo->edu == 4): ?>selected="selected"<?php endif; ?>>专科</option>
            </select>
        </div>
        <div class="dbk_inpL">
            <label>入学年份</label>
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
        </div>
    </div>

    <div class="button"> <button id="reg_two_form">下一步</button></div>
    <div style="color:#279cff;font-size:15px; float:right; height:2.5rem; font-weight:bold; margin-right:6.3%;"><a href='/dev/account/company' style="color:#279cff;">社会人可点击跳过》</a></div>
</div>
<div id="bodyframe" style="display: none; position: fixed;top:0;left: 0;">
    <iframe id='child' name='child' src="/html/school.html?v=1" class="iframe" scrolling="scrolling"></iframe>
    <!--<iframe name="child" src="./child.html" ></iframe>-->
</div> 
<script>
    var user_id = <?php echo $userinfo['user_id']; ?>;
    function showSchool() {
        $('#bodyframe').show();
        var UA = navigator.userAgent;
        if (UA.match(/iPad/) || UA.match(/iPhone/) || UA.match(/iPod/)) {
            window.frames[0].pinyin = true;
            window.frames[0].ClientInit();
        }
//                        window.frames[0].ClientInit()
    }
    $("#reg_two_form").click(function () {
        var school = $('#reg_school').val();
        var school_name = $('#reg_school_name').val();
        var edu = $('#reg_edu').val();
        var school_time = $('#reg_school_time').val();
        var realname = $('#reg_realname').val();
        var identity = $('#reg_identity').val();
        var is_real = $('#reg_identity').attr('is_real');
        var from_url = $('#from_url').val();
        var f_url = $('#f_url').val();
        if (school == 0) {
            alert("请选择学校");
            return false;
        }
        if (school_time == '0') {
            alert("请选择入学年份");
            return false;
        }
        if (edu == '0') {
            alert("请选择学历");
            return false;
        }

        $.post("/dev/reg/twosaves", {user_id: user_id, school: school, school_name: school_name, edu: edu, school_time: school_time}, function (result) {
            var data = eval("(" + result + ")");
            if (data.ret == '0') {
                window.location = '/dev/reg/company';
            } else if (data.ret == '2')
            {
                alert('该身份证号已存在，请更换');
            } else if (data.ret == '11') {
                alert("请填写姓名/身份证号码");
            }
            else if (data.ret == '3')
            {
                window.location = '/dev/account/black';
            } else if (data.ret == '12') {
                window.location = '/dev/account/personals';
            }
            else {
                alert('学籍认证失败，请重新修改');

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
            'hideOptionMenu'
        ]
    });

    wx.ready(function () {
        wx.hideOptionMenu();
    });
</script>