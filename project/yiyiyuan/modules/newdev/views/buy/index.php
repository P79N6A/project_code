<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title><?= $this->title; ?></title>
    <link rel="stylesheet" type="text/css" href="/296/css/reset.css"/>
    <link rel="stylesheet" type="text/css" href="/296/css/inv.css"/>
    <script src="/296/js/jquery-1.10.1.min.js"></script>
    <style>
    	body{ background: #fff;}
    </style>
</head>
<body>
    <div class="allnewjkuan">
        <img src="/296/images/bgbgbuy.png" width="100%">
    </div>
    <div class="zuoyminew jk_item" style="position: relative;top: -21rem; width: 90%; left: 5%;">
       <p style="font-size: 2.3rem; text-align: center; color: #fff; padding-bottom: 2rem;">个人意外险</p>
       <p style="width: 30%;float: right; padding-bottom: 5px;"><img src="/296/images/zabx.png"></p>
       <div class="daihukan_cont" style="border-radius: 10px; clear: both;">
            <div class="daoqihk">
            	<p style="font-weight: bold;"><span>50万保额</span></p>
            	<p style="color: #ccc;">￥150/份</p>
            </div>
            <div class="rowym">
                <div class="corname">被保人为本人</div>
                <div class="corliyou" id="name"><?= $userInfo->realname ;?></div>
            </div>
            <div class="rowym">
                <div class="corname">保障期限</div>
                <div class="corliyou" id="days">56天</div>
            </div>
            <div class="rowym">
                <div class="corname">生效日期</div>
                <div class="corliyou" ><?= date('Y-m-d',strtotime( '+1 day' )) ;?></div>
            </div>
        </div>
        <p style="padding: 10px 2%;">
            <input class="chk" type="checkbox" checked="checked" style="-webkit-appearance:checkbox;" />
            本人承诺投保信息的真实性，理解并同意<a href="<?php echo Yii::$app->request->hostInfo . '/new/agreeloan/toubao';?>" style="color: #007AFF;">《借款人意外伤害保险条款》</a>的全部内容
        </p>
        <button type="submit" class="bgrey" >我要投保</button>
        <div class="marbot100"></div>
    </div>
    <div class="jiebangcg" style="display: none">您暂时不能购买，请下次尝试</div>
</body>
<script>
    $(".bgrey").click(function() {
        var chk = $(".chk").is(":checked");
        if (chk) {
            $(".bgrey").attr('disabled', true);
            $(".bgrey").css('background','#BBBABA');
            var days = 56;
            var amount = 150;
            var name = $("#name").html();
            var source = <?= $source;?>;
            var user_id = <?= $userInfo->user_id ;?>;

            $.post("/new/buy/policy", {days: days,amount: amount, name: name, user_id:user_id}, function(result) {
                var data = eval("(" + result + ")");
                if(data.code != '0000'){
                    $(".jiebangcg").show().html('您暂时不能购买，请下次尝试')
                    setTimeout(function () {
                        window.myObj.closeHtml();
                        function closeHtml() {
                        }
                    }, 2000);
                    $(".bgrey").attr('disabled', false);
                    return false;
                }else{
                    $.post("/new/buy/buy", {insuranceId: data.data, source:source}, function(results) {
                        var datas = eval("(" + results + ")");
                        if(data.code != '0000'){
                            $(".jiebangcg").show().html('您暂时不能购买，请下次尝试')
                            setTimeout(function () {
                                window.myObj.closeHtml();
                                function closeHtml() {
                                }
                            }, 2000);
                            $(".bgrey").attr('disabled', false);
                            return false;
                        }
                        console.log(datas.url)
                        window.location = datas.url;
                    });
                }
            });
        }else {
            $(".jiebangcg").show().html('同意借款协议才能投保')
            setTimeout(function(){$(".jiebangcg").hide()}, 2000);
            $(".bgrey").attr('disabled', false);
            return false;
        }
    });
</script>
</html>