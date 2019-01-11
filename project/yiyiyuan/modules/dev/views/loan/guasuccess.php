<script>
    $(window).load(function() {
        var lineH = $('.col-xs-2').height();
        $('.col-xs-10').css('lineHeight', lineH + 24 + 'px');
    });
</script>
<div class="Hcontainer">
    <div class="text-center bWhite border_bottom_grey2 overflow" style="padding:40px 0 20px;">
        <p class="n40 grey2 bold">借款请求已发送成功</p>
        <p class="n30 grey4 mt10 bold">请等待担保人确认投资</p>
    </div>
    <div class="col-xs-12 bWhite" style="padding:8px 0;padding-right:6.25%;">
        <div class="col-xs-10 n30 grey2 bold" style="padding-left:6.25%">短信通知担保人</div>
        <input type="hidden" id="user_id" value="<?php echo $user_id;?>"/>
        <div class="col-xs-2 nPad border_left_grey" style="padding:12px 0;"><a href="javascript:sendmobile(<?php echo $loan->loan_id; ?>);"><img src="/images/icon_text.png" width="60%" class="float-right icon_text" style="max-width:52px;"></a></div>
    </div>
    <div class="clearfix"></div>
    <div class="main mt10">
        <div class="col-xs-12 n26 nPad">
            <img src="/images/icon_gth.png" width="5%" style="margin-top: -4px;margin-right:10px;">
            <span class="n26 red">提前与担保人进行联系，借款成功率更高哦！</span>
        </div>
        <a href="/dev/loan/succ?l=<?php echo $loan->loan_id; ?>" class="btn mt40 mb40" style="width:100%">查看借款</a>
    </div>                                         
</div>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script src="/js/zebra_dialog.js"></script>
<script>
    function sendmobile(n) {
    	var user_id = $("#user_id").val();
        $.get("/dev/st/statisticssave", { type: 29,user_id:user_id },function(data){
        	
        });
        $.post('/dev/loan/sendmobile', {loan_id: n}, function(result) {
            var data = eval("(" + result + ")");
            $.Zebra_Dialog(data.msg, {
                'type': data.ret == 0 ? 'information' : 'error',
                'title': '给担保人发送短信',
                'buttons': [
                    {caption: '确定', callback: function() {
                            if (data.url != '') {
                                window.location = data.url;
                            }
                        }},
                ]
            });
            if (data.url != '') {
                window.setTimeout("window.location='" + data.url + "'", 2000);
            }
        });
    }
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