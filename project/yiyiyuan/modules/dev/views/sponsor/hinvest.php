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
    <div class="Hcontainer">
		<div class="dbr_head2">
    		<img src="/images/sxed_head.jpg" width="100%">
    		<div class="col-xs-6">
    			<p class="n26 grey2 bold">预计收益（元）<img src="/images/icon_ques3.png" class="icon_ques3"></p>
    			<p class="n90 red" style="margin-top:-5px;"><?php echo number_format($loan_info['amount']*0.01,2,'.','');?></p>
    		</div>
        </div>
        <div class="main bWhite overflow">
        		<div class="float-left n26">借款金额(元)：<span class="red n40"><?php echo number_format($loan_info['amount'],2,'.','');?></span></div>
        		<div class="float-right n26">年化收益：<span class="red n40"><?php echo round(0.01/$loan_info['days']*365*100,1);?>%</span></div>
        </div>
        <p class="text-center grey2 n26 mt40">借款人还款后，收益生效～</p>
        <div class="main">
		        <input type='hidden' name='loan_id' id='loan_id' value="<?php echo $loan_info['loan_id'];?>">
				<input type='hidden' name='amount' id='amount' value="<?php echo number_format($loan_info['amount'],0,'.','');?>">
        		<button type="submit" class="btn" id='qued'>确定投资</button>
        		<div class="n26 mt20">
                <input type="checkbox" checked="checked" id="checkbox-1" class="regular-checkbox">
                <label for="checkbox-1"></label>
                阅读并同意
                <a href="#" class="underL aColor">《先花一亿元居间协议及借款协议》</a>
            </div> 
            <p class="n26 grey2 mt30">1.担保借款发生逾期后会冻结您的担保账户。</p>
			<p class="n26 grey2">2.冻结担保账户的同时会影响已获得收益的提现。</p>
			<p class="n26 grey2">3.请谨慎投资，注意风险，维护个人利益。</p>
        </div>

        <!-- 黑色遮罩 -->
        <div class="Hmask" style="display: none;"></div>
        <!-- 弹层1，预计收益 -->
        <div class="xhb_layer pad" style="display: none;" id="ques">
            <img src="/images/icon_wt.png" style="width:30%;position: absolute;top:-84px;left:-5px;width:100px;">
            <p class="n28 mt40"><span class="red">预计收益：</span>借款人成功还款后，收益到账。</p>
            <button class="btn_red">朕知道了</button>
        </div>

        <!-- 弹层2，投资成功 -->
        <div class="xhb_layer" style="display: none;" id="succ">
            <p class="n30 grey2 text-center">投资<span class="n48 red">成功</span>！等待审核</p>
            <div class="border_bottom_red mt20"></div>
            <div class="col-xs-6">
                <a href='/dev/sponsor/index'><button type="submit" class="btn mt20">继续投资</button></a>
            </div>
            <div class="col-xs-6">
                <a href='/dev/guarantoraccount/index'><button type="submit" class="bgrey btn mt20">查看收益</button></a>
            </div>
        </div>
   </div>
<script>
    $(function(){
        $('.Hmask').click(function(){
            $('.xhb_layer').hide();
            $('.Hmask').hide();
        });
        $('.icon_ques3').click(function(){
            $('.Hmask').show();
            $('#ques').show();
        });
        $('.btn_red').click(function(){
            $('.Hmask').hide();
            $('.xhb_layer').hide();
        });
    });
</script>

<script>
$('#qued').click(function(){
	var loan_id = $('#loan_id').val();
	var amount = $('#amount').val();
	
	if (!$("#checkbox-1").is(':checked')) {
		alert('必须同意借款协议');
		return false;
	}
	
    $('#qued').attr("disabled","disabled");
	//alert(loan_id);
	$.post("/dev/sponsor/addinvest",{loan_id:loan_id,amount:amount},function(result){
	    //var data = eval("("+ result + ")" );
		var data = result;
		//alert(data);
		if(data!=0){
			$('.Hmask').show();
            $('#succ').show();
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

    wx.ready(function() {
        wx.hideOptionMenu();
    });
</script>
