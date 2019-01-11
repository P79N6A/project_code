<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title><?= $this->title; ?></title>
    <link rel="stylesheet" type="text/css" href="/css/reset_new.css"/>
    <script src="/js/jquery-1.10.1.min.js"></script>
    <script src="/298/js/leftTime.min.js"></script>
    <style>
        body{background: #f3f3f3;}
        .zdan_list{ margin-top: 10px; background: #fff; padding: 10px 0 20px;}
        .zdan_list .zalst_title{overflow: hidden; border-bottom: 1px #f3f3f3 solid; padding: 5px 5% 15px; position: relative;}
        .zdan_list .zalst_title h3{color: #999; font-size: 1rem; }
        .zdan_list .zalst_title .xajsmeg{ position: absolute; right: 0%; top:8px}
        .zdan_list .zalst_title .xajsmeg img{ width: 10%; float: left;}
        .zdan_list .zalst_title .xajsmeg span{ float: left;padding-left: 5px; font-size: 0.8rem;}
        .zdan_list .zdan_cont h4{ color: #444; text-align: center; font-size: 1.35rem; padding: 20px 0 5px;}
        .zdan_list .zdan_cont h4 em{color: #c90000;}
        .zdan_list .zdan_cont p{color: #999; text-align: center; font-size: 1rem; padding-bottom: 10px;}
        .zdan_list  .red{ color: #c90000;}
        .zdan_list .zdan_cont p.red{color: #c90000;}
        .zdan_list .but_ljhk{width: 100%;}
        .zdan_list .but_ljhk button {background: #c90000;padding:3px 10px;color: #fff;font-size: 1rem;border-radius: 50px;text-align: center;margin: 0 auto; display: block;}

        .Hmask {width: 100%; height: 100%;background: rgba(0,0,0,.7); position: fixed;top: 0;left: 0; z-index: 100;}
        .dl_tcym{position: fixed;top: 25%; width: 50%;background: #fff; z-index: 110;margin: 0 25%;border-radius: 10px;padding:20px 0;}
        .dl_tcym img{width: 40%;display: block;margin: 0 auto;}
        .dl_tcym p{ text-align: center; color: #999; font-size: 1rem;}
    </style>
</head>
<body>
<?php if(!empty($hasIousing)){ ?>
    <!-- 还款确认中 -->
    <?php if(isset($hasIousing['status']) &&  $hasIousing['status']== 11){ ?>
        <div class="zdan_list">
            <div class="zalst_title">
                <h3>延期支付账单</h3>
                <div class="xajsmeg">
                    <img src="/images/xdjsicon.png">
                    <span >预计24小时内完成确认</span>
                </div>
            </div>
            <div class="zdan_cont">
                <h4><em><?php echo round($hasIousing['chase_amount'],2);?></em>元延期支付账单还款确认中...</h4>
                <p>请稍后，确认成功后，延期支付账单结清</p>
            </div>
        </div>
    <?php }else{ ?>
        <!-- 逾期 -->
        <?php if(isset($hasIousing['status']) && in_array($hasIousing['status'],[12])){ ?>
            <div class="zdan_list">
                <div class="zalst_title">
                    <h3>延期支付账单</h3>
                    <div class="xajsmeg">
                        <img src="/images/yuqicon.png">
                        <span class="red">已逾期</span>
                        <span class="red" id="dateShowBt">
                        <span class="d">0天</span>
                        <span class="h">00小时</span>
                        <span class="m">00分</span>
                        <span class="s">00秒</span>
                    </span>
                    </div>
                </div>
                <div class="zdan_cont">
                    <h4>您当前有<em><?php echo round($hasIousing['chase_amount'],2);?></em>元延期支付账单待支付</h4>
                    <p>逾期将产生额外费用，且会影响您的信用</p>
                </div>
                <div class="but_ljhk bt"> <button>立即还款</button></div>
            </div>
        <?php }?>
        <!-- 待支付 -->
        <?php if(isset($hasIousing['status']) && in_array($hasIousing['status'],[9])){ ?>
            <div class="zdan_list">
                <div class="zalst_title">
                    <h3>延期支付账单</h3>
                    <div class="xajsmeg"><img src="/images/xdjsicon.png"><span>
                    <span id="dateShowRejBt">
                        <span class="d">0天</span>
                        <span class="h">00小时</span>
                        <span class="m">00分</span>
                        <span class="s">00秒</span>
                    </span>
                    </div>
                </div>
                <div class="zdan_cont">
                    <h4>您当前有<em><?php echo round($hasIousing['chase_amount'],2);?></em>元延期支付账单待支付</h4>
                    <p>到期未还，将降低您的信用</p>
                </div>
                <div class="but_ljhk bt"> <button>立即还款</button></div>
            </div>
        <?php }?>
    <?php } ?>
<?php } ?>

<?php if(!empty($hasRepayingLoan)){ ?>
    <!-- 还款确认中 -->
    <?php if($hasRepayIng == 1){ ?>
        <div class="zdan_list">
            <div class="zalst_title">
                <h3>借款账单</h3>
                <div class="xajsmeg"><img src="/images/xdjsicon.png">
                <span>预计24小时内完成确认</span>
                </div>
            </div>
            <div class="zdan_cont">
                <h4><em><?php echo round($hasRepayingLoan->getRepayment(),2);?></em>元借款账单还款确认中...</h4>
                <p>请稍后，确认成功后，借款账单结清</p>
            </div>
        </div>
    <?php }else{ ?>
        <?php if(in_array($hasRepayingLoan->status,[12,13])){ ?>
            <!-- 逾期 -->
            <div class="zdan_list">
                <div class="zalst_title">
                    <h3>借款账单</h3>
                    <div class="xajsmeg">
                        <img src="/images/yuqicon.png">
                        <span class="red">已逾期</span>
                    <span class="red" id="dateShow">
                        <span class="d">0天</span>
                        <span class="h">00小时</span>
                        <span class="m">00分</span>
                        <span class="s">00秒</span>
                    </span>
                    </div>
                </div>
                <div class="zdan_cont">
                    <h4>您当前有<em><?php echo round($hasRepayingLoan->getRepayment(),2);?></em>元借款账单待支付</h4>
                    <p>逾期将产生额外费用，且会影响您的信用</p>
                </div>
                <div class="but_ljhk jk"> <button>立即还款</button></div>
            </div>
        <?php }?>
        <?php if(in_array($hasRepayingLoan->status,[9])){ ?>
            <!-- 待支付 -->
            <div class="zdan_list">
                <div class="zalst_title">
                    <h3>借款账单</h3>
                    <div class="xajsmeg"><img src="/images/xdjsicon.png"><span>
                    <span id="dateShowRej">
                        <span class="d">0天</span>
                        <span class="h">00小时</span>
                        <span class="m">00分</span>
                        <span class="s">00秒</span>
                    </span>
                    </div>
                </div>
                <div class="zdan_cont">
                    <h4>您当前有<em><?php echo round($hasRepayingLoan->getRepayment(),2);?></em>元借款账单待支付</h4>
                    <p>到期未还，将降低您的信用</p>
                </div>
                <div class="but_ljhk jk"> <button>立即还款</button></div>
            </div>
        <?php }?>
    <?php } ?>
<?php } ?>

<div class="Hmask mc" style="display: none"></div>
<div class="dl_tcym mc" style="display: none">
    <img src="/images/loding.gif">
    <p>将前往智融钥匙</p>
</div>

</body>
<script>
    $(".jk").click (function () {
        var from = '<?php echo $_GET['from'];?>';
        if(from == 'weixin'){
            window.location = '/new/loan?from=repay_list';
            return true;
        }else{
            var u = navigator.userAgent, app = navigator.appVersion;
            var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Linux') > -1;
            var isiOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/);
            var android = "com.business.main.MainActivity";
            var ios = "loanViewController";
            var position = "-1";

            if (isiOS) {
                window.myObj.toPage(ios);
            } else if(isAndroid) {
                window.myObj.toPage(android, position);
            }
        }
    });

    $(".bt").click (function () {
        tongji('doRepay');
        <?php $youxinDomain = Yii::$app->params['youxin_url'];?>
        var youxinDomain = '<?php echo $youxinDomain;?>';
        var ious_id = '<?php echo isset($hasIousing['ious_id'])?$hasIousing['ious_id']:'';?>';
        var mobile = '<?php echo $mobile;?>';
        var url = '<?php echo urlencode('/dev/iousdetails/index?ious_id='.isset($hasIousing['ious_id'])?$hasIousing['ious_id']:'');?>';
        $(".mc").show();
        setTimeout(function () {
            window.location = youxinDomain+'dev/iousdetails/index?ious_id='+ious_id+'&userToken='+mobile+'&url='+url
        }, 3000);
    });

    $(function(){
        var dateShowRej = "<?php echo $dateShow = date('Y/m/d H:i:s',$syLoanTime+time());?>";
        $.leftTime(dateShowRej,function(d){
            if(d.status){
                var $dateShow1=$("#dateShowRej");
                $dateShow1.find(".d").html(d.d+'天');
                $dateShow1.find(".h").html(d.h+'小时');
                $dateShow1.find(".m").html(d.m+'分');
                $dateShow1.find(".s").html(d.s+'秒');
            }
        });
    });

//    $(function(){
//        var dateShow = "<?php //echo $dateShow = date('Y/m/d H:i:s',abs($syLoanTime)+time());?>//";
//        $.leftTime(dateShow,function(d){
//            if(d.status){
//                var $dateShow1=$("#dateShow");
//                $dateShow1.find(".d").html(d.d+'天');
//                $dateShow1.find(".h").html(d.h+'小时');
//                $dateShow1.find(".m").html(d.m+'分');
//                $dateShow1.find(".s").html(d.s+'秒');
//            }
//        });
//    });

    $(function(){
        countTimel();
    })
    function countTimel() {
        //获取当前时间
        var date = new Date();
        var now = date.getTime();
        //设置截止时间
        var end =  new Date("<?php echo $userLoanInfo->end_date;?>".replace(/-/g,'/')).getTime();
        var leftTime = end-now;

        //定义变量 d,h,m,s保存倒计时的时间
        var d,h,m,s;
        d = Math.floor(leftTime/1000/60/60/24);
        h = Math.floor(leftTime/1000/60/60%24);
        m = Math.floor(leftTime/1000/60%60);
        s = Math.floor(leftTime/1000%60);

        var $dateShow1=$("#dateShow");
        $dateShow1.find(".d").html(-d-1+'天');
        $dateShow1.find(".h").html(-h-1+'小时');
        $dateShow1.find(".m").html(-m+'分');
        $dateShow1.find(".s").html(-s+'秒');

        //递归每秒调用countTime方法，显示动态时间效果
        setTimeout(countTimel,1000);
    }

    $(function(){
        var dateShowRejBt = "<?php echo $dateShow = date('Y/m/d H:i:s',$syIousTime+time());?>";
        $.leftTime(dateShowRejBt,function(d){
            if(d.status){
                var $dateShow1=$("#dateShowRejBt");
                $dateShow1.find(".d").html(d.d+'天');
                $dateShow1.find(".h").html(d.h+'小时');
                $dateShow1.find(".m").html(d.m+'分');
                $dateShow1.find(".s").html(d.s+'秒');
            }
        });
    });

//    $(function(){
//        var dateShowBt = "<?php //echo $dateShow = date('Y/m/d H:i:s',abs($syIousTime)+time());?>//";
//        $.leftTime(dateShowBt,function(d){
//            if(d.status){
//                var $dateShow1=$("#dateShowBt");
//                $dateShow1.find(".d").html(d.d+'天');
//                $dateShow1.find(".h").html(d.h+'小时');
//                $dateShow1.find(".m").html(d.m+'分');
//                $dateShow1.find(".s").html(d.s+'秒');
//            }
//        });
//    });

    $(function(){
        countTime();
    })
    function countTime() {
        //获取当前时间
        var date = new Date();
        var now = date.getTime();
        //设置截止时间
        var end =  new Date("<?php echo isset($hasIousing['end_time'])?$hasIousing['end_time']:'00-00-00 00:00:00';?>".replace(/-/g,'/')).getTime();
        var leftTime = end-now;

        //定义变量 d,h,m,s保存倒计时的时间
        var d,h,m,s;
        d = Math.floor(leftTime/1000/60/60/24);
        h = Math.floor(leftTime/1000/60/60%24);
        m = Math.floor(leftTime/1000/60%60);
        s = Math.floor(leftTime/1000%60);

        var $dateShow1=$("#dateShowBt");
        $dateShow1.find(".d").html(-d-1+'天');
        $dateShow1.find(".h").html(-h-1+'小时');
        $dateShow1.find(".m").html(-m+'分');
        $dateShow1.find(".s").html(-s+'秒');

        //递归每秒调用countTime方法，显示动态时间效果
        setTimeout(countTime,1000);
    }

    function tongji(event) {
        <?php \app\common\PLogger::getInstance('weixin','',$user_id); ?>
        <?php $json_data = \app\common\PLogger::getJson();?>
        var baseInfoss = eval( '('  + '<?php echo $json_data; ?>' + ')' );
        baseInfoss.url = baseInfoss.url+'&event='+event;
        console.log(baseInfoss);
        var ortherInfo = {
            screen_height: window.screen.height,//分辨率高
            screen_width: window.screen.width,  //分辨率宽
            user_agent: navigator.userAgent,
            height: document.documentElement.clientHeight || document.body.clientHeight,  //网页可见区域宽
            width: document.documentElement.clientWidth || document.body.clientWidth,//网页可见区域高
        };
        var baseInfos = Object.assign(baseInfoss, ortherInfo);

        var turnForm = document.createElement("form");
        turnForm.id = "uploadImgForm";
        turnForm.name = "uploadImgForm";
        document.body.appendChild(turnForm);
        turnForm.method = 'post';
        turnForm.action = baseInfoss.log_url+'weixin';
        //创建隐藏表单
        for (var i in baseInfos) {
            var newElement = document.createElement("input");
            newElement.setAttribute("name",i);
            newElement.setAttribute("type","hidden");
            newElement.setAttribute("value",baseInfos[i]);
            turnForm.appendChild(newElement);
        }
        var iframeid = 'if' + Math.floor(Math.random( 999 )*100 + 100) + (new Date().getTime() + '').substr(5,8);
        var iframe = document.createElement('iframe');
        iframe.style.display = 'none';
        iframe.id = iframeid;
        iframe.name = iframeid;
        iframe.src = "about:blank";
        document.body.appendChild( iframe );
        turnForm.setAttribute("target",iframeid);
        turnForm.submit();
    }
</script>
</html>