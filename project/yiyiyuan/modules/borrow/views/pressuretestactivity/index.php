<div class="y-wrap">
    <img src="/borrow/311/images/active-top.png" alt="" class="y-share-img">
    <!-- 邀请奖励 -->
    <div class="invite-reward">
        <div class="invite-main">
            <img class="invite-img" src="/borrow/311/images/max-reward.png" alt="">
            <p class="invite-txt">邀请好友注册即可获得积分奖励积分排名前三最高可获得<span>588元</span>现金奖励哟！</p>
        </div>
        <button class="share-btn"></button>
    </div>
    <!-- 邀请奖励结束 -->
    <!-- 积分排行 -->
    <div class="score-rank">
        <img class="score-tit" src="/borrow/311/images/score-rank.png" alt="">
        <div class="score-main">
            <div class="score-statistic">
                <p>您当前累计积分为:<span class="spacial-size">
                        <?php if($data['userInfo']){ echo $data['userInfo']['integral']; }else{ echo '--';} ?>
                    </span>分</p>
                <p>排名：<span>
                        <?php if($data['userInfo']){ echo $data['userInfo']['ranking']; }else{ echo '--';} ?>
                    </span></p>
            </div>
            <div class="score-three">
                <div class="score-item">
                    <img style="margin-top:16px;" src="/borrow/311/images/rank-twice.png" alt="">
                    <div class="score-detail sd1">
                        <?php if($data['list']){ ?>
                        <p><?=$data['list'][1]['mobile']?></p>
                        <p><?=$data['list'][1]['integral']?>分</p>
                        <?php }else{ ?> <div class="rank-nothing">需以待位</div> <?php } ?>
                    </div>
                </div>
                <div class="score-item">
                    <img src="/borrow/311/images/rank-first.png" alt="">
                    <div class="score-detail sd2">
                        <?php if($data['list']){ ?>
                        <p><?=$data['list'][0]['mobile']?></p>
                        <p><?=$data['list'][0]['integral']?>分</p>
                        <?php }else{ ?> <div class="rank-nothing">需以待位</div> <?php } ?>
                    </div>
                </div>
                <div class="score-item">
                    <img style="margin-top:16px;" src="/borrow/311/images/rank-third.png" alt="">
                    <div class="score-detail sd3">
                        <?php if($data['list']){ ?>
                        <p><?=$data['list'][2]['mobile']?></p>
                        <p><?=$data['list'][2]['integral']?>分</p>
                        <?php }else{ ?> <div class="rank-nothing">需以待位</div> <?php } ?>
                    </div>
                </div>
            </div>
            <div class="score-table">
                <ul>
                    <li class="table-tit">
                        <p>排名</p>
                        <p>手机号</p>
                        <p>积分</p>
                    </li>
                    <?php if($data['list']){ foreach ($data['middle'] as $k=>$v): ?>
                    <li>
                        <p><?=$v['ranking'] ?></p>
                        <p><?=$v['mobile'] ?></p>
                        <p><?=$v['integral'] ?></p>
                    </li>
                    <?php endforeach;}else{ ?>
                        <div class="nothing1">
                            暂无排名
                        </div>
                    <?php } ?>
                </ul>
                <a class="score-more" href="#">点击查看更多>></a>
            </div>
        </div>
    </div>
    <!-- 积分排行结束 -->
    <!-- 关注有好礼 -->
    <div class="notice-gift">
        <img class="notice-tit" src="/borrow/311/images/notice.png" alt="">
        <div class="notice-main">
            <img class="wechat-code" src="/borrow/311/images/wechat-code.png" alt="">

            <div>
                <button class="copy-btn" onclick="copyUrl2()">复制并打开</button>
            </div>
            <p>点击按钮复制微信公众号</p>
            <p>首次关注后回复<span>“领取奖励”</span>可获得借款免息券！</p>
        </div>
    </div>
    <!-- 关注有好礼结束 -->
    <!-- 活动规则 -->
    <div class="active-rules">
        <h3>活动规则</h3>
        <p>1、成功邀请用户注册一亿元可累计10积分；</p>
        <p>2、积分排名每天00:00点更新；</p>
        <p>3、首次关注先花一亿元公众微信号的用户，关注后回复
            相关内容，可领取一张免息券（限时30天使用）;</p>
        <p>4、本活动最终解释权归先花一亿元所有。</p>
    </div>
    <!-- 活动规则结束 -->

    <!-- 以下全部为弹层 -->
    <!-- 遮罩背景 -->
    <div class="mask" hidden></div>
    <!-- 积分排行榜弹层 -->
    <div class="score-more-list" hidden>
        <i class="close-btn">×</i>
        <img class="score-tit1" src="/borrow/311/images/score-rank1.png" alt="">
        <div class="more-list">
            <ul id="phb">
                <li class="table-tit">
                    <p style="flex-grow: 2;">排名</p>
                    <p style="flex-grow: 5;">手机号</p>
                    <p style="flex-grow: 3;">积分</p>
                </li>
                <?php if($data['list']){ foreach ($data['list'] as $k=>$v): ?>
                    <li>
                        <p style="flex-grow: 2;"><?=$v['ranking']?></p>
                        <p style="flex-grow: 5;"><?=$v['mobile']?></p>
                        <p style="flex-grow: 3;"><?=$v['integral']?></p>
                    </li>
                <?php endforeach;}else{ ?>
                   <div class="nothing">
                       暂无排名
                   </div>
                <?php } ?>
            </ul>
        </div>
        <p class="score-tip">排行榜只展示积分前20名</p>
    </div>

    <!-- 未登录提示弹层 -->
    <div id="isAlert" class="login-error" hidden>
        <i class="close-btn">×</i>
        <p class="login-tip">您未登录，请登录后参加</p>
        <button class="go-login">立即登录</button>
    </div>

    <!-- 去分享弹层 -->
    <div class="go-share" hidden>
        <img src="/borrow/311/images/share-tips.png" alt="">
    </div>
</div>
<!--<textarea cols="20" rows="10" id="biao1" hidden>xianhuayyy</textarea>-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="http://res.wx.qq.com/open/js/jweixin-1.2.0.js"></script>
<script src="/js/clipboard.min.js?v=10001" type="text/javascript"></script>
<script>
    var invite_code = '<?=$invite_code?>';
    // 点击弹层关闭事件
    $('.mask').click(function(){
        $(this).hide();
        $('.go-share').hide();
        $('.score-more-list').hide();
        $('.login-error').hide();
        $('html,body').css("overflow", "auto");
        $('html,body').css({
            'position': 'static'
        })
    })

    // 排行榜查看更多事件
    $('.score-more').click(function(){
        var h = $(document).scrollTop();
        $('html,body').animate({scrollTop:h}, 5);
        $('.mask').show();
        $('.score-more-list').show();
        // $('body').css("overflow", "hidden");
        $('html,body').css("overflow", "hidden");
        $('html,body').css({
            'position': 'fixed',
            'top': 0,
            'left': 0
        })
    });

    // 弹窗关闭按钮事件
    $('.close-btn').click(function(){
        $('.mask').hide();
        $('.score-more-list').hide();
        $('html,body').css("overflow", "auto");
        $('.login-error').hide();
        $('html,body').css({
            'position': 'static'
        })
    });

    //复制粘贴
    function copyUrl2(){
        var ua = window.navigator.userAgent.toLowerCase();
        //关注公众号按钮点击量
        $.get('/new/st/statisticssave?type=1436');
        var clipboard = new Clipboard('.copy-btn', {
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

    $('.go-login').click(function () {
        window.location = '/borrow/reg/login?url=/borrow/pressuretestactivity&invite_code='+invite_code+'&comeFrom=5';
    });
    //分享
    var isApp = <?php echo $isapp;?>;
    $(function (){
        //活动访问量
        $.get('/new/st/statisticssave?type=1435');
        $('.share-btn').click(function () {
            //分享按钮点击量
            $.get('/new/st/statisticssave?type=1437');
            $.post("/borrow/pressuretestactivity/share", {}, function(result) {
                var data = eval("(" + result + ")");
                //等于1 未登录
                if(data.code==1){
                    $('#isAlert').show();
                    $('.mask').show();
                    $('html,body').css("overflow", "hidden");
                    $('html,body').css({
                        'position': 'fixed',
                        'top': 0,
                        'left': 0
                    })
                }else{
                    if (isApp == 1) {
                        //弹出微信和朋友圈
                        window.myObj.doShare('7');
                    }
                    if (isApp == 2) {
                        $('.mask').show();
                        $('.go-share').show();
                        $('body').css("overflow", "hidden");
                    }
                }
            });
        });
        //引导用户右上角转发
        wx.config({
            debug: false,
            appId: "<?php echo $jsinfo['appid']; ?>",
            timestamp: "<?php echo $jsinfo['timestamp']; ?>",
            nonceStr: "<?php echo $jsinfo['nonceStr']; ?>",
            signature: "<?php echo $jsinfo['signature']; ?>",
            jsApiList: [
                'onMenuShareTimeline',
                'onMenuShareAppMessage',
                'showOptionMenu',
            ]
        });

        wx.ready(function () {
            wx.showOptionMenu();
            // 2. 分享接口
            // 2.1 监听“分享给朋友”，按钮点击、自定义分享内容及分享结果接口
            wx.onMenuShareAppMessage({
                title: "您有588元现金大红包 速领",
                desc: "588元现金大礼包！助我一臂之力吧！就差你一个啦，注册即可~",
                imgUrl: "<?php echo $share_info['imgUrl']; ?>",
                link: "<?php echo $share_info['link']; ?>",
                trigger: function (res) {
                    // 不要尝试在trigger中使用ajax异步请求修改本次分享的内容，因为客户端分享操作是一个同步操作，这时候使用ajax的回包会还没有返回
                },
                success: function (res) {
                },
                cancel: function (res) {
                },
                fail: function (res) {
                }
            });
            // 2.2 监听“分享到朋友圈”按钮点击、自定义分享内容及分享结果接口
            wx.onMenuShareTimeline({
                title: "您有588元现金大红包 速领",
                desc: "588元现金大礼包！助我一臂之力吧！就差你一个啦，注册即可~",
                imgUrl: "<?php echo $share_info['imgUrl']; ?>",
                link: "<?php echo $share_info['link']; ?>",
                trigger: function (res) {
                    // 不要尝试在trigger中使用ajax异步请求修改本次分享的内容，因为客户端分享操作是一个同步操作，这时候使用ajax的回包会还没有返回
                },
                success: function (res) {
                },
                cancel: function (res) {
                },
                fail: function (res) {
                }
            });
        })
    })
</script>
