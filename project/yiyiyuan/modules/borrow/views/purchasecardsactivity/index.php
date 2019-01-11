<?php
 $bank = array('ABC', 'ALL', 'BCCB', 'BCM', 'BOC', 'CCB', 'CEB', 'CIB', 'CMB', 'CMBC', 'GDB', 'HXB', 'ICBC', 'PAB', 'PSBC', 'SPDB', 'ECITIC');
?>
<div class="y-wrap">
    <div class="active3-top"></div>
    <div class="active3-main">
        <button class="active3-btn"></button>
    </div>
    <div class="active3-bottom">
        <button class="active3-copy" onclick="copyUrl2()"></button>
        <p class="active3-notice">点击按钮复制微信公众号</p>
        <p class="active3-notice">首次关注并回复<span>“领取奖励”</span>可获得借款免息券！</p>
        <div class="active3-rules">
            <h3>活动规则：</h3>
            <p>1、购买礼包需先获取购买资格；</p>
            <p>2、还款券有效期40天；</p>
            <p>3、此活动最终解释权归先花一亿元所有。</p>
        </div>
    </div>

    <!-- 遮罩层 -->
    <div class="mask" hidden></div>
    <div class="masks" hidden></div>

    <!-- 绑定银行卡弹层 -->
    <div class="active3-addcard" hidden>
        <i class="close-btn" onclick="CloseAlert()"></i>
        <p>请前往个人中心绑定银行卡</p>
        <button class="active3-popup-btn" onclick="CloseAlert()">知道了</button>
    </div>

    <!-- 测评未通过弹层 -->
    <div class="active3-buy" hidden>
        <i class="close-btn" onclick="CloseAlert()"></i>
        <p>您暂无资格购买此礼包</p>
        <button class="active3-popup-btn" onclick="CloseAlert()">确认</button>
    </div>

    <!-- 测评中未评测弹层 -->
    <div class="active3-test" hidden>
        <i class="close-btn" onclick="CloseAlert()"></i>
        <p>购买礼包请先获取购买资格</p>
        <button class="active3-popup-btn" id="get-buy">获得购买资格</button>
    </div>

    <!-- 多次购买弹层 -->
    <div class="active3-buymore" hidden>
        <i class="close-btn" onclick="CloseAlert()"></i>
        <p>乐享大礼包每人只能购买一次</p>
        <button class="active3-popup-btn" onclick="CloseAlert()">确认</button>
    </div>

    <!-- 购买成功弹层 -->
    <div class="active3-buysuccess" hidden>
        <i class="close-btn" onclick="CloseAlert()"></i>
        <p>您已成功购买5张20元还款券</p>
        <button class="active3-popup-btn" id="go-app">前往APP查看</button>
    </div>

    <!-- 未登录弹层 -->
    <div class="active3-nologin" hidden>
        <i class="close-btn" onclick="CloseAlert()"></i>
        <p>您未登录，请登录后参加</p>
        <button class="active3-popup-btn" id="active3-popup-btn">立即登录</button>
    </div>

    <!-- 支付失败弹层 -->
    <div class="active3-payfaile" hidden>
        <i class="close-btn" onclick="CloseAlert()"></i>
        <p>支付失败，请重新支付</p>
        <button class="active3-popup-btn" id="go_loan">立即支付</button>
    </div>

    <!-- 选择银行卡弹层 -->
    <div class="active3-selectcard">
        <div class="select-tit">选择银行卡</div>
            <div class="card-box">
<!--                <div style="heihgt:auto;">-->
                    <?php foreach ($banklist as $k=>$v): ?>
                        <div class="card-item" ipt="<?=$v['id']?>">
                             <img class="bank-logo" src="/images/bank_logo/<?php if(!empty($v['bank_abbr']) && in_array($v['bank_abbr'], $bank)) {echo $v['bank_abbr'];} else {echo 'ICON';}?>.png" alt="">
                            <p><?=$v['bank_name']?>(<span><?=$v['card']?></span>)</p>
                            <?php if($k==0){ ?>
                                <img class="select" src="/borrow/activity3/images/select-ok.png" alt="">
                           <?php }else{ ?>
                                <img class="select" src="/borrow/activity3/images/select-ok.png" alt="" hidden>
                          <?php  } ?>
                        </div>
                    <?php endforeach; ?>
<!--                </div>-->
            </div>
    </div>
</div>
<script src="/borrow/activity3/js/jquery-3.3.1.min.js"></script>
<script src="/js/clipboard.min.js?v=10001" type="text/javascript"></script>
<script>
    var is_app = '<?=$is_app?>';
    var type = '<?=$type?>';
    var user_id = '<?=$user_id?>';
    var is_alert = '<?=$is_alert?>';
    
    //活动访问量
    $.get('/new/st/statisticssave?type=1444&user_id='+user_id);
    //判断显示按钮状态
    if(type==-1){
        $(".active3-btn").attr("disabled", true);
        $('.active3-btn').css('background','url(/borrow/activity3/images/active3-waitpay.png) no-repeat');
    }else if(type==2 && is_alert==1){
        $('.mask').show();
        $('.active3-payfaile').show();
        $('html,body').css("overflow", "hidden");
        $('html,body').css({
            'position': 'fixed',
            'top': 0,
            'left': 0
        })
    }else if(type==1  && is_alert==1){
        $(".active3-btn").attr("disabled", true);
        $('.mask').show();
        $('.active3-buysuccess').show();
        $('html,body').css("overflow", "hidden");
        $('html,body').css({
            'position': 'fixed',
            'top': 0,
            'left': 0
        })
    }
    //支付失败-点击重新支付按钮
    $('#go_loan').click(function () {
        $('.mask').show();
        $('.active3-selectcard').slideDown(520);
        $('.card-box').slideDown(400);
        // $('.active3-selectcard').addClass('bank-active');
        $('.active3-payfaile').hide();
        $('html,body').css("overflow", "hidden");
        $('html,body').css({
            'position': 'fixed',
            'top': 0,
            'left': 0
        })
    });

    // 点击按钮购买出现弹层
    $('.active3-btn').click(function() {
        //点击购买的人数
        $.get('/new/st/statisticssave?type=1445&user_id='+user_id);
        $.post("/borrow/purchasecardsactivity/judge", {}, function(result) {
            var data = eval("(" + result + ")");
            if(data.code==1){
                $('.mask').show();
                $('.active3-nologin').show();
                $('html,body').css("overflow", "hidden");
                $('html,body').css({
                    'position': 'fixed',
                    'top': 0,
                    'left': 0
                })
            }else if(data.code==2){
                $('.mask').show();
                $('.active3-buymore').show();
                $('html,body').css("overflow", "hidden");
                $('html,body').css({
                    'position': 'fixed',
                    'top': 0,
                    'left': 0
                })
            }else if(data.code==3){
                $('.mask').show();
                $('.active3-buy').show();
                $('html,body').css("overflow", "hidden");
                $('html,body').css({
                    'position': 'fixed',
                    'top': 0,
                    'left': 0
                })
            }else if(data.code==4){
                $('.mask').show();
                $('.active3-test').show();
                $('html,body').css("overflow", "hidden");
                $('html,body').css({
                    'position': 'fixed',
                    'top': 0,
                    'left': 0
                })
            }else if(data.code==5){
                $('.mask').show();
                $('.active3-addcard').show();
                $('html,body').css("overflow", "hidden");
                $('html,body').css({
                    'position': 'fixed',
                    'top': 0,
                    'left': 0
                })
            }else if(data.code==0){
                $('.masks').show();
                $('.active3-selectcard').slideDown(400);
                $('.card-box').slideDown(400);
                // $('.active3-selectcard').addClass('bank-active');
                // $('.active3-selectcard').show();
                // $('html,body').css("overflow", "hidden");
                $("body,html").css({ "overflow":"hidden" });
                $('html,body').css({
                    'position': 'fixed',
                    'top': 0,
                    'left': 0
                })
            }else{
                alert('网络超时');
            }
        });
    });

    $('.masks').click(function () {
        $('.masks').hide();
        $('.active3-selectcard').slideUp(520);
        $('.card-box').slideUp(400);
        $('html,body').css("overflow", "auto");
        $('html,body').css({
            'position': 'static'
        });

    });

    //前往app
    $('#go-app').click(function () {
        var u = navigator.userAgent, app = navigator.appVersion;
        var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Linux') > -1;
        var isiOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/);
        if(is_app==1){
            window.location = '/new/coupon/couponlist';
            return false;
        }
        if(isiOS){
            window.location = 'https://itunes.apple.com/cn/app/xian-hua-yi-yi-yuan/id986683563?mt=8';
            return false;
        }
        window.location = 'https://sj.qq.com/myapp/detail.htm?apkName=com.xianhuahua.yiyiyuan_1';
    });

    //跳转登录
    $('#active3-popup-btn').click(function () {
        window.location = '/borrow/reg/login?url=/borrow/purchasecardsactivity';
    });
    //获取购买资格
    $('#get-buy').click(function () {
        if(is_app==1){
          closeHtml();
        }else {
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

    // 弹层的关闭按钮
    function  CloseAlert() {
        $('.mask').hide();
        $('.active3-nologin').hide();
        $('.active3-buymore').hide();
        $('.active3-buy').hide();
        $('.active3-test').hide();
        $('.active3-addcard').hide();
        $('.active3-payfaile').hide();
        $('.active3-buysuccess').hide();
        $('html,body').css("overflow", "auto");
        $('html,body').css({
            'position': 'static'
        });
        window.location = '/borrow/purchasecardsactivity';
    }

    // 点击按钮弹出选择银行卡选项
    // $('.active3-btn').click(function(){
    // 	$('.mask').show();
    // 	$('.active3-selectcard').addClass('bank-active');
    // 	$('html,body').css("overflow", "hidden");
    // 	$('html,body').css({
    //         'position': 'fixed',
    //         'top': 0,
    //         'left': 0
    //     })
    // });

    // 点击选择银行卡，随后隐藏弹层
    $('.card-item').click(function(){
        $(this).find('.select').show();
        $(this).siblings('.card-item').find('.select').hide();
        var bank_id = $(this).attr('ipt');
        $.post("/borrow/purchasecardsactivity/paybankcard", {bank_id:bank_id}, function(result) {
            var data = eval("(" + result + ")");
            if(data.code==0){
                window.location = data.url;
            }else if(data.code==7) {
                 alert('网络超时');
            }else if(data.code==8){
                alert('订单正在支付中,请稍等');
            }else{
                $('.mask').show();
                $('.active3-buy').show();
                $('html,body').css("overflow", "hidden");
                $('.active3-selectcard').hide();
                $('.card-box').hide();
                $('html,body').css({
                    'position': 'fixed',
                    'top': 0,
                    'left': 0
                })
            }
        });
    });
    //复制粘贴
    function copyUrl2(){
        var ua = window.navigator.userAgent.toLowerCase();
        //关注公众号按钮点击量
        $.get('/new/st/statisticssave?type=1446&user_id='+user_id);
        var clipboard = new Clipboard('.active3-copy', {
            text: function() {
                return 'xianhuayyy';
            }
        });
        clipboard.on('success', function(e) {
            if(ua.match(/MicroMessenger/i) == 'micromessenger'){
                alert('复制成功');
            }
            window.location.href='weixin://';
        });
    }
</script>
