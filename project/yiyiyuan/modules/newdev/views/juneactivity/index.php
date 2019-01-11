<!--<script src="/newdev/js/jquery-1.10.1.min.js?v=20180301030"></script>-->
<!--<script src="/newdev/js/jquery1.8.3.min.js?v=20180301030"></script>-->
<div class="warp">
    <div class="header">
        <img src="/newdev/images/juneactivity/niu-top.png" alt="">

        <div class="word" id="share"><img src="/newdev/images/juneactivity/Arrow.png" alt=""></div>
        <div class="worder-bottom">
            抽奖开放时间6月17日0:00-18日23:00,6月25日0:00-26日23:00，敬请关注！
        </div>
    </div>
    <!--扭蛋-->
    <div class="niu_danji">
        <!--球-->
        <img class="niudanji" src="/newdev/images/juneactivity/niudanji.png">
        <div class="dan_gund">
            <span  class="qiu_1 diaol_1"></span>
            <span  class="qiu_2 diaol_2"></span>
            <span  class="qiu_3 diaol_3"></span>
            <span  class="qiu_4 diaol_4"></span>
            <span  class="qiu_5 diaol_5"></span>
            <span  class="qiu_6 diaol_6"></span>
            <span  class="qiu_7 diaol_7"></span>
            <span  class="qiu_8 diaol_8"></span>
            <span  class="qiu_9 diaol_9"></span>
            <span  class="qiu_10 diaol_10"></span>
            <!--<span  class="qiu_11 diaol_11"></span>-->
        </div>
        <!--机器-->
        <div class="game_qu">
            <!--go-->
            <?php if (($time > $starttime17 && $time < $endtime18) || ($time > $starttime25 && $time < $endtime26)): ?>
                <button class="game_go" id="game_go"></button>
            <?php else: ?>
                <div class="game_go2"></div>
            <?php endif; ?>
            <div class="wdjifen"><?= $num ?></div>
        </div>

        <!--中奖掉落-->
        <div class="medon"><img src="/newdev/images/juneactivity/mendong.png"/></div>
        <div class="zjdl ">
            <span></span>
        </div>


        <!--剩余抽奖币-->
        <div class="currency">
            <img src="/newdev/images/juneactivity/bi.png" alt="">
        </div>

        <!--签到领币-->
        <div class="collar">
            <?php if (empty($is)): ?>
                <img id="lend" src="/newdev/images/juneactivity/collar.png" alt="">
            <?php else: ?>
                <img class="imge2" id="already" src="/newdev/images/juneactivity/yiqiandao.png" alt="">
            <?php endif; ?>
            <img class="imge2" id="already" src="/newdev/images/juneactivity/yiqiandao.png" alt=""
                 style="display: none">
            <img id="lend" src="/newdev/images/juneactivity/collar.png" alt="" style="display: none">
        </div>
    </div>
    <!--底部说明-->
    <div class="bottom">
        <h1>活动日期：</h1>
        <p>2018年6月13日-6月26日</p>
        <h1>活动规则:</h1>
        <p>先花一亿元注册用户活动期间可在活动页面每天领取一枚抽奖币,每枚抽奖币可以扭动扭蛋机一次，丰厚大奖等你来拿。</p>
        <h1>奖品发放说明:</h1>
        <p>1.券发放形式为:中奖后5分钟内自动发放到【优惠券】中。</p>
        <p>2.实物奖品发放形式为:在APP及微信公众号内参与活动,获得实物奖品的用户,我们的客服人员会在3个工作日内和您取得联系,奖品由平台统一邮寄,请保持手机畅通,如果个人拒接电话等原因导致不能取得联系,无法安排奖品发放,将视为主动放弃活动奖品。</p>
        <h1>抵用券使用说明:</h1>
        <p>1.参与17、18两日扭蛋获得的抵用券仅限6月17日、18日两天使用,过期作废。</p>
        <p>2.参与25、26两日扭蛋获得的抵用券仅限6月25日、26日两天使用,过期作废。</p>
        <div>本活动最终解释权归先花一亿元所有。</div>
    </div>

    <!--中奖 获得15offset-roll.png等奖-->
    <div class="zonj_zezc none" id="jianpin_one">
        <div class="jpzs aiqiyi tc_anima">
            <div><img src="/newdev/images/juneactivity/zjl15.png" alt=""></div>
            <div class="element">￥15</div>
            <p>恭喜您获得15元抵用券！</p>
            <button>确定</button>
        </div>
    </div>

    <!--中奖 获得25offset-roll.png等奖-->
    <div class="zonj_zezc none"  id="jianpin_two">
        <div class="jpzs aiqiyi tc_anima">
            <div><img src="/newdev/images/juneactivity/zjl25.png" alt=""></div>
            <div class="element">￥25</div>
            <p>恭喜您获得25元抵用券！</p>
            <button>确定</button>
        </div>
    </div>

    <!--中奖 获得35offset-roll.png等奖-->
    <div class="zonj_zezc none"  id="jianpin_three">
        <div class="jpzs newzjl">
            <div><img src="/newdev/images/juneactivity/zjl35.png" alt=""></div>
            <p>恭喜您获得35元抵用券！</p>
            <button>确定</button>
        </div>
    </div>

    <!--中奖 获得45offset-roll.png等奖-->
    <div class="zonj_zezc none"  id="jianpin_four">
        <div class="jpzs aiqiyi tc_anima">
            <div><img src="/newdev/images/juneactivity/zjl45.png" alt=""></div>
            <div class="element">￥45</div>
            <p>恭喜您获得45元抵用券！</p>
            <button>确定</button>
        </div>
    </div>

    <!--中奖 获得50JD.png等奖-->
    <div class="zonj_zezc none"  id="jianpin_five">
        <div class="jpzs aiqiyi tc_anima">
            <div><img src="/newdev/images/juneactivity/thinks.png" alt=""></div>
            <div class="element2">50元</div>
            <p>恭喜您获得50元抵用券！</p>
            <button>确定</button>
        </div>
    </div>

    <!--中奖 获得60offset-roll.png等奖-->
    <div class="zonj_zezc none"  id="jianpin_six">
        <div class="jpzs aiqiyi tc_anima">
            <div><img src="/newdev/images/juneactivity/zjl60.png" alt=""></div>
            <div class="element">￥60</div>
            <p>恭喜您获得60元抵用券！</p>
            <button>确定</button>
        </div>
    </div>

    <!--中奖 获得100JD.png等奖-->
    <div class="zonj_zezc none"  id="jianpin_seven">
        <div class="jpzs aiqiyi tc_anima">
            <div><img src="/newdev/images/juneactivity/thinks.png" alt=""></div>
            <div class="element3">100元</div>
            <p>恭喜您获得100元京东卡！</p>
            <button>确定</button>
        </div>
    </div>

    <!--中奖 获得200JD.png等奖-->
    <div class="zonj_zezc none"  id="jianpin_eight">
        <div class="jpzs aiqiyi tc_anima">
            <div><img src="/newdev/images/juneactivity/thinks.png" alt=""></div>
            <div class="element4">200元</div>
            <p>恭喜您获得200元抵用券！</p>
            <button>确定</button>
        </div>
    </div>

    <!--中奖 获得500JD.png等奖-->
    <div class="zonj_zezc none"  id="jianpin_nine">
        <div class="jpzs newzjl">
            <div><img src="/newdev/images/juneactivity/zjl35.png" alt=""></div>
            <p>恭喜您获得35元抵用券！</p>
            <button>确定</button>
        </div>
    </div>

    <div class="zonj_zezc none"  id="jianpin_ten">
        <div class="jpzs">
            <div><img src="/newdev/images/juneactivity/thinks2.png" alt=""></div>
            <button>确定</button>
        </div>
    </div>

    <!--积分不足-->
    <div class="zonj_zezc none"  id="no_jifeng">
        <div class="jpzs">
            <div><img src="/newdev/images/juneactivity/nonecs.png" alt=""></div>
            <p>抱歉，您当前没有抽奖次数~</p>
            <button>确定</button>
        </div>
    </div>
    <!--扭蛋end-->
</div>
<script type="text/javascript" src="/newdev/js/jquery-1.11.0.min.js"></script>
<script src="http://res.wx.qq.com/open/js/jweixin-1.2.0.js"></script>
<script type="text/javascript">
    $(document).ready(function (e) {
        //一等奖 关闭
        $(".zonj_zezc button").click(function () {
                $(".zonj_zezc").hide();
            }
        );

        //签到领币
        $('#lend').click(function () {
            tongji('sign_in');
            $.get('/new/st/statisticssave?type=1349');
            var score = $(".wdjifen").html();
            var a = $(".wdjifen").text();
            $.ajax({
                type: "get",
                url: "/new/juneactivity/signin",
                data: "",
                dataType: "json",
                success: function (data) {
                    if (data.error_code == 1) {
                        $('.imge2').show();
                        $('#lend').remove();
                        $(".wdjifen").text(a * 1 + 1);
                    } else if (data.error_code == 2) {
                        alert('签到失败');
                    } else if (data.error_code == 5) {
                        alert('今天已经签过到');
                    } else {
                        alert('抱歉！活动未开启');
                    }
                }
            });
        });

        //扭蛋机抽奖
        $("#game_go").click(function () {
            $(".game_go").attr("disabled", true);
            tongji('luck_draw');
            $.get('/new/st/statisticssave?type=1350');
            var score = $(".wdjifen").html();
            score = score - 1;
            if (score < 0) {
                for (i = 1; i <= 10; i++) {
                    $(".qiu_" + i).removeClass("wieyi_" + i);
                }
                $("#no_jifeng").show();
                $(".game_go").attr("disabled", false);
            } else {
                $.ajax({
                    type: "get",
                    url: "/new/juneactivity/lotteryjudgment",
                    data: "",
                    dataType: "json",
                    success: function (data) {
                        if (data.error_code == 1) {
                            if (data.result.id == 3) {
                                draw(3);
                            } else if (data.result.id == 2) {
                                draw(2);
                            } else if (data.result.id == 1) {
                                draw(1);
                            } else {
                                draw();
                            }
                            $(".wdjifen").html(score);
                        } else if (data.error_code == 7) {
                            alert('网络超时');
                        }
                    }
                })
            }
        });

        function draw(number = 10){
            // var number =Math.floor(10*Math.random()+1);

            for (i = 1; i <= 10; i++) {
                $(".qiu_" + i).removeClass("diaol_" + i);
                $(".qiu_" + i).addClass("wieyi_" + i);
            }
            ;

            setTimeout(function () {
                for (i = 1; i <= 10; i++) {
                    $(".qiu_" + i).removeClass("wieyi_" + i);
                }
            }, 1100);
            // alert(number);
            setTimeout(function () {
                switch (number) {
                    case 1:
                        $(".zjdl").children("span").addClass("diaL_one");
                        break;
                    case 2:
                        $(".zjdl").children("span").addClass("diaL_two");
                        break;
                    case 3:
                        $(".zjdl").children("span").addClass("diaL_three");
                        break;
                    case 4:
                        $(".zjdl").children("span").addClass("diaL_four");
                        break;
                    case 5:
                        $(".zjdl").children("span").addClass("diaL_five");
                        break;
                    case 6:
                        $(".zjdl").children("span").addClass("diaL_six");
                        break;
                    case 7:
                        $(".zjdl").children("span").addClass("diaL_seven");
                        break;
                    case 8:
                        $(".zjdl").children("span").addClass("diaL_eight");
                        break;
                    case 9:
                        $(".zjdl").children("span").addClass("diaL_nine");
                        break;
                    case 10:
                        $(".zjdl").children("span").addClass("diaL_ten");
                        break;
                }
                $(".zjdl").removeClass("none").addClass("dila_Y");
                setTimeout(function () {
                    switch (number) {
                        case 1:
                            $("#jianpin_one").show();
                            break;
                        case 2:
                            $("#jianpin_two").show();
                            break;
                        case 3:
                            $("#jianpin_three").show();
                            break;
                        case 4:
                            $("#jianpin_four").show();
                            break;
                        case 5:
                            $("#jianpin_five").show();
                            break;
                        case 6:
                            $("#jianpin_six").show();
                            break;
                        case 7:
                            $("#jianpin_seven").show();
                            break;
                        case 8:
                            $("#jianpin_eight").show();
                            break;
                        case 9:
                            $("#jianpin_nine").show();
                            break;
                        case 10:
                            $("#jianpin_ten").show();
                            break;
                    }
                    $(".game_go").attr("disabled", false);

                    $(".zjdl").children("span").removeAttr('class');
                }, 900);
            }, 1100)

            //取消动画
            setTimeout(function () {
                $(".zjdl").addClass("none").removeClass("dila_Y");
                $(".wdjifen").html(score);
                $(".zjdl").children("span").removeAttr('class');

            }, 2500)

        }
    });
    //统计埋点
    function tongji(event) {
        <?php \app\common\PLogger::getInstance('weixin', '', $uid); ?>
        <?php $json_data = \app\common\PLogger::getJson(); ?>
        var baseInfoss = eval('(' + '<?php echo $json_data; ?>' + ')');
        baseInfoss.url = baseInfoss.url + '&event=' + event;
        console.log(baseInfoss);
        var ortherInfo = {
            screen_height: window.screen.height, //分辨率高
            screen_width: window.screen.width, //分辨率宽
            user_agent: navigator.userAgent,
            height: document.documentElement.clientHeight || document.body.clientHeight, //网页可见区域宽
            width: document.documentElement.clientWidth || document.body.clientWidth, //网页可见区域高
        };
        var baseInfos = Object.assign(baseInfoss, ortherInfo);
        var turnForm = document.createElement("form");
        turnForm.id = "uploadImgForm";
        turnForm.name = "uploadImgForm";
        document.body.appendChild(turnForm);
        turnForm.method = 'post';
        turnForm.action = baseInfoss.log_url + 'weixin';
        //创建隐藏表单
        for (var i in baseInfos) {
            var newElement = document.createElement("input");
            newElement.setAttribute("name", i);
            newElement.setAttribute("type", "hidden");
            newElement.setAttribute("value", baseInfos[i]);
            turnForm.appendChild(newElement);
        }
        var iframeid = 'if' + Math.floor(Math.random(999) * 100 + 100) + (new Date().getTime() + '').substr(5, 8);
        var iframe = document.createElement('iframe');
        iframe.style.display = 'none';
        iframe.id = iframeid;
        iframe.name = iframeid;
        iframe.src = "about:blank";
        document.body.appendChild(iframe);
        turnForm.setAttribute("target", iframeid);
        turnForm.submit();
    }

    //分享
    var isApp = <?php echo $isapp;?>;
    $(function () {
        $('#share').click(function () {
            $.get('/new/st/statisticssave?type=1351');
            if (isApp == 1) {
                tongji('share_app');
                //弹出微信和朋友圈
                window.myObj.doShare('6');
            }
            if (isApp == 2) {
                tongji('share_wx');
            }
        })
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
                title: "扭蛋赢奖励，好礼赚不停",
                desc: "夏至未至，年中大回馈,扭蛋赢奖励，好礼赚不停",
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
                title: "扭蛋赢奖励，好礼赚不停",
                desc: "夏至未至，年中大回馈,扭蛋赢奖励，好礼赚不停",
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

