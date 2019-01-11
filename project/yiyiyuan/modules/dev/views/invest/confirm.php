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
            <header class="header-info">
                <div class="h-info">
                	<div class="b22">预计收益（点）</div>
                    <p class="n60 red" id="yuji_profit">0.00</p>
                </div>
            </header>
            <div class="main bgf">
            	<div class="row n22">
                    <div class="col-xs-8">可投资金额：<span class="red"><?php echo sprintf("%.2f", $investamount);?>点</span></div>
                    <div class="col-xs-4 text-right">年化收益：<span class="red"><?php echo Yii::$app->params['rate'];?></span>%</div>
               </div>
            </div>
            <div class="main">
                <input class="mb40 mt20" id="input_amount" type="text" maxlength="9" style="width:100%;" placeholder="输入投资金额"/>
                <p class="mb20 text-center">借款人筹资完成，收益生效～</p>
                <input type="hidden" id="invest_amount" value="<?php echo sprintf("%.2f", $investamount);?>"/>
                <input type="hidden" id="loan_id" value="<?php echo $loan_id;?>"/>
                <input type="hidden" id="rate" value="<?php echo Yii::$app->params['rate'];?>"/>
                <input type="hidden" id="invest_day" value="<?php echo $days;?>"/>
                <button type="button" id="invest_confirm" class="btn" style="width:100%">确定</button>
                <p class="text-right n20"><input type="checkbox" id="agree_invest_xieyi" checked="checked"/>阅读并同意<a href="/dev/invest/agreement?loan_id=<?php echo $loan_id;?>">《先花一亿元居间服务及借款协议》</a></p>
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