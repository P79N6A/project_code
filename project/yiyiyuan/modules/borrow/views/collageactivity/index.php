<div class="y-wrap">
    <img src="/borrow/311/images/active2-top.jpg" alt="" class="y-share-img">
    <!-- 拼团福利 -->
    <div class="invite-reward">
        <div class="active2-type">
            <img src="/borrow/311/images/active2-fl1.png" alt="">
            <img src="/borrow/311/images/active2-fl2.png" alt="">
            <img src="/borrow/311/images/active2-fl3.png" alt="">
        </div>
        <p class="active2-txt">活动期间由用户发起拼团享集体享受专属下款通道，高通过率，评测通过后还有借款团购价!</p>
    </div>
    <!-- 拼团福利结束 -->

    <!-- 热拼中 -->
    <div class="score-rank">
        <img class="score-tit" src="/borrow/311/images/active2-tit2.png" alt="">
        <div class="score-main active2-rp">
            <?php foreach ($data['list'] as $k=>$v): ?>
            <div class="active2-rpitem">
                <img class="active2-yyy" src="/borrow/311/images/active2-yyy.png" alt="">
                <div class="active2-rpmessage">
                    <div class="rpmessage-left">
                        <p><?=$v['mobile']?></p>
                        <span><strong id="h_<?=$k?>"></strong>:<strong id="m_<?=$k?>"></strong>:<strong id="s_<?=$k?>"></strong></span>
                    </div>
                    <p class="rpmessage-team">还差<span><?=$v['rand']?></span>人成团</p>
                </div>
                <button class="participate-btn"></button>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <!-- 热拼中结束 -->

    <!-- 关注有好礼 -->
    <div class="notice-gift">
        <img class="notice-tit" src="/borrow/311/images/active2-tit3.png" alt="">
        <div class="notice-main">
            <img class="wechat-code" src="/borrow/311/images/wechat-code.png" alt="">
            <button class="copy-btn" id="copy_btn">复制并打开</button>
            <p>点击按钮复制微信公众号</p>
            <p>首次关注并回复<span>“领取奖励”</span>可获得借款免息券！</p>
        </div>
    </div>
    <!-- 关注有好礼结束 -->

    <!-- 遮罩背景 -->
    <div class="mask" hidden></div>

    <div class="login-error" hidden>

        <i class="close-btn" onclick="close_btn()"></i>

        <!-- 未登录 -->
        <div id="NoSignIn" hidden>
        <p class="login-tip">您未登录，请登录后参加</p>
        <button class="go-login" id="go_login">立即登录</button></div>

        <!-- 重复发起 -->
        <div id="repeat" hidden>
        <p class="login-tip">您有进行中拼团，不可重复发起</p>
        <button class="go-login" onclick="close_btn()">知道了</button></div>

    </div>


    <div class="login_error" hidden style=" width: 498px;height: 268px;background: #fef6ee;position: fixed;border-radius: 8px;left: 71px;top: 388px;">
        <i class="close-btn" onclick="close_btn()"></i>
    <!-- 已有借款 -->
        <p class="login-tip">您已有借款，不可参加此活动</p>
        <button class="go-login" onclick="close_btn()">知道了</button>
    </div>


    <!-- 拼团成功 -->
    <div class="active2-ptsuccess" id="collage" hidden>
        <i class="close-btn" onclick="close_btn()"></i>
        <h3 class="active2-success">恭喜您，拼团成功！</h3>
        <p class="login-tip1">成功获得<span id="val"></span>元还款券</p>
        <p class="login-tip1">限今日借款使用</p>
        <p class="login-tip2">请到先花一亿元APP优惠券中查看</p>
        <button class="go-login" id="access_quotas">立即获取额度</button>
    </div>

    <!-- 活动规则 -->
    <div class="active-rules">
        <h3>活动规则</h3>
        <p>1、3人拼团成功可各获得一张还款券(有效期7日，需当日发起
            借款可使用);</p>
        <p>2、参加拼团可提高审核通过率;</p>
        <p>3、首次关注先花一亿元公众微信号的用户，关注后回复相关内容，可领取一张免息券（限时30天使用）;</p>
        <p>4、本活动最终解释权归先花一亿元所有。</p>
    </div>
    <!-- 活动规则结束 -->
</div>
<script src="/292/js/jquery-1.10.1.min.js"></script>
<script src="/js/clipboard.min.js?v=10001" type="text/javascript"></script>
<script>
    var is_app = '<?=$is_app?>';
    var user_id = '<?=$uid?>';
    //页面访问量
    $.get('/new/st/statisticssave?type=1439&user_id='+user_id);
    // 弹窗关闭按钮事件
    function close_btn() {
        $(".participate-btn").attr("disabled", false);
        $('.mask').hide();
        $('html,body').css("overflow", "auto");
        $('.login-error').hide();
        $('.login_error').hide();
        $('#collage').hide();
        $('html,body').css({
            'position': 'static'
        });
    }
    //去登陆
    $('#go_login').click(function () {
        window.location = '/borrow/reg/login?url=/borrow/collageactivity';
    });

    //立即获取额度
    $('#access_quotas').click(function () {
        //统计立即获取额度按钮
        $.get('/new/st/statisticssave?type=1440&user_id='+user_id);
        if(is_app==1)
        {
            closeHtml();
        }else{
            window.location = '/borrow/loan';
        }
    });

    //跳原生app借款页面
    function closeHtml() {
            var u = navigator.userAgent, app = navigator.appVersion;
            var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Linux') > -1;
            var isiOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/);
            var android = "com.business.main.MainActivity";
            var ios = "loanViewController";
            var position = "-1";
            if (isiOS) {
                window.myObj.toPage(ios);
            } else if (isAndroid) {
                window.myObj.toPage(android, position);
            }
    }

    //倒计时
    var times = '<?=$data['time']?>';
    var xqo = eval('(' + times + ')');
    var runtimes = 0;
    window.onload=GetRTime;
    function GetRTime(){
        var i = 0;
        for(i=0;i<4;i++){
            var num = xqo[i];
            var nMS = num*1000-runtimes*1000;
            var nH=Math.floor(nMS/(1000*60*60))%24;
            var nM=Math.floor(nMS/(1000*60)) % 60;
            var nS=Math.floor(nMS/1000) % 60;
            if (nH <= 9) nH = '0' + nH;
            if (nM <= 9) nM = '0' + nM;
            if (nS <= 9) nS = '0' + nS;
            if(nH == '00' && nM == '00' && nS == '00'){
                xqo[i] = '86400';
            }
            $('#h_'+i).html(nH);
            $('#m_'+i).html(nM);
            $('#s_'+i).html(nS);
        }
        runtimes++;
        setTimeout("GetRTime()",1000);
    }

    //立即参团
   $('.participate-btn').click(function () {
       $(".participate-btn").attr("disabled", true);
       //统计立即参团人数
       $.get('/new/st/statisticssave?type=1441&user_id='+user_id);
       $.get("/borrow/collageactivity/judge", {}, function(result) {
           var data =  eval("(" + result + ")");
           if(data.code==1){
               $('.mask').show();
               $('.login-error').show();
               $('#NoSignIn').show();
               $('html,body').css({
                   'position': 'fixed',
                   'top':0,
                   'left':0
               });
           }else if(data.code==2){
               $('.mask').show();
               $('.login-error').show();
               $('#repeat').show();
               $('html,body').css({
                   'position': 'fixed',
                   'top':0,
                   'left':0
               });
           }else if(data.code==3){
               $('.mask').show();
               $('.login_error').show();
               $('html,body').css({
                   'position': 'fixed',
                   'top':0,
                   'left':0
               });
           }else if(data.code==0){
               $('#val').html(data.data.val);
               $('.mask').show();
               $('#collage').show();
               $('html,body').css({
                   'position': 'fixed',
                   'top':0,
                   'left':0
               });
           }else{
               alert('网络超时');
           }
       });
   });

    //复制粘贴
   $('#copy_btn').click(function(){
       var ua = window.navigator.userAgent.toLowerCase();
       //关注公众号按钮点击量
       $.get('/new/st/statisticssave?type=1442&user_id='+user_id);
       var clipboard = new Clipboard('.copy-btn', {
           text: function() {
               return 'xianhuayyy';
           }
       });
       clipboard.on('success', function(e) {
           if(ua.match(/MicroMessenger/i) == 'micromessenger'){
               alert('复制成功');
               return false;
           }
           window.location.href='weixin://';
       });
   });

</script>
