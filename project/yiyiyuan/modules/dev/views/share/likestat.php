<style>
    .Hmask { width: 100%;height: 100%;background: rgba(0,0,0,.7); position: fixed;top: 0; left: 0; z-index: 10;}
    .buttonan{position: fixed;top: 20%; left: 0; margin:0 5%;z-index: 100; background: #fff; width: 90%; padding-bottom: 20px;}
    .buttonan p{text-align: center; color: #444; font-size: 16px; padding: 20px 0;}
    .buttonan .error{display: block;width: 50%;padding: 7px 0; font-size: 14px; margin-left: 25%; text-align: center; background: #f72727; color: #fff; }
    .buttonan .errorclose{ width: 9%;height: 2rem;position: absolute; right: 1%;top: 10px;}
</style>


<div class="Hcontainer nP">
    <div class="bgimg_txt">
        <div class="bgimg_txtbor">
             <?php if ($loaninfo['user_id'] == $logininfo['user_id']): ?>
                <div class="txt_one">
                    <p class="txt_one_one">求点赞</p>
                    <p class="txt_one_two">帮我减免</p>
                    <p class="txt_one_three">服务费</p>
                </div>
                 <div class="txt_one">
                     <p class="txt_one_four fighting">F i g h t i n g</p>
                 </div>
              <?php else: ?>
                <div class="txt_one" id='lzh'>
                    <p class="txt_one_one">求点赞</p>
                    <p class="txt_one_two">帮我减免</p>
                    <p class="txt_one_three">服务费</p>
                </div>
                 <div class="txt_one">
                     <p class="txt_one_four">求点赞帮我减免服务费</p>
                 </div>
             <?php endif; ?>
        </div>
    </div>
    <div class="main" style="position: absolute;top:0;left:0;width:100%;">
        <img src="<?php echo empty($head) ? '/images/dev/face.png' : $head; ?>" width="15%" class="mr2 borRad5">
        <span class="n30 white"><?php echo $loanuserinfo['realname']; ?></span>
    </div>
    <?php if (!$logininfo){ ?>
        <div class="col-xs-6 mt15">
            <div class="hand up toloanin">
                <img src="/images/upTrans.png">
                <div class="clearfix"></div>
                <input type="text" value="<?php echo $num;?>" class="hand_value" readonly="readonly">
            </div>
        </div>
        <div class="col-xs-6 mt15">
            <div class="hand down toloanin">
                <img src="/images/downTrans.png">
                <div class="clearfix"></div>
                <input type="text" value="<?php echo $nums;?>" class="hand_value" readonly="readonly">
            </div>
        </div>
    <?php }elseif($loaninfo['user_id'] == $logininfo['user_id']){ ?>
        <div class="col-xs-6 mt15">
            <div class="hand up">
                <img src="/images/upTrans.png">
                <div class="clearfix"></div>
                <input type="text" value="<?php echo $num;?>" class="hand_value" readonly="readonly">
            </div>
        </div>
        <div class="col-xs-6 mt15">
            <div class="hand down">
                <img src="/images/downTrans.png">
                <div class="clearfix"></div>
                <input type="text" value="<?php echo $nums;?>" class="hand_value" readonly="readonly">
            </div>
        </div>
    <?php }else{  ?>
        <div class="col-xs-6 mt15">
            <div class="hand up" id="loan_like_stat_button" loan="<?php echo $loaninfo->loan_id; ?>" login="<?php echo $logininfo['id']; ?>">
                 <?php if ($stats==0): ?>
                 <img src="/images/upTrans.png" id='limg'>
                 <?php else: ?>
                 <img src="/images/upRed.png">
                 <?php endif; ?>
                <div class="clearfix"></div>
                <input type="text" value="<?php echo $num;?>" class="hand_value" readonly="readonly" id='dz'>
            </div>
        </div>
        <div class="col-xs-6 mt15">
            <div class="hand down" id="loan_like_stat_hlz" loan="<?php echo $loaninfo->loan_id; ?>" login="<?php echo $logininfo['id']; ?>">
                <?php if ($stats1==0): ?>
                    <img src="/images/downTrans.png" id='himg'>
                <?php else: ?>
                    <img src="/images/downRed.png" id='himg'>
                <?php endif; ?>
                <div class="clearfix"></div>
                <input type="text" value="<?php echo $nums;?>" class="hand_value" readonly="readonly" id='cai'>
            </div>
        </div>
    <?php } ?>

    <div class="clearfix"></div>
    <div class="main">
        <?php if ($loaninfo['user_id'] == $logininfo['user_id']): ?>
            <?php if ($num !=0): ?>
                <p class="white n30 text-center mt20"><?php echo $num;?>个赞，减免<?php echo sprintf('%.2f', $loaninfo->like_amount); ?>元。
                    <?php if (($loaninfo->interest_fee / 2)-($loaninfo->like_amount)>0): ?>还可继续减免哦！
            <?php else: ?>
                快谢谢小伙们吧!
            <?php endif; ?>
                </p>
        <?php endif; ?>
        <?php else: ?>
            <?php if ($num !=0): ?>
                <p class="white n30 text-center mt20"><?php echo $num;?>个好友赞了Ta,帮Ta减免<?php echo sprintf('%.2f', $loaninfo->like_amount); ?>元服务费。</p>
            <?php endif; ?>
        <?php endif; ?>
        <?php if ($loaninfo['user_id'] == $logininfo['user_id']): ?>
            <script src="/js/dev/shareWin.js?v=2015061511"></script>
            <button class="btnNew mt40 mb40" onClick="shareTip();" style="width:100%">分享</button>
        <?php else: ?>
            <a href="/new/loan"><button class="btnWhite mt40 mb40" style="width:100%">我也要借钱</button></a>
        <?php endif; ?>
        <?php if (!empty($likelist)): ?>
        <div class="col-xs-12 nPad">
            <div class="col-xs-4 nPad">
                <img src="/images/11111.png" width="100%" class="line3">
            </div>
            <div class="col-xs-4" style="padding: 0 7px;">
             <?php if ($loaninfo['user_id'] == $logininfo['user_id']): ?>
                <i class="share_line">谁赞了我</i>
             <?php else: ?>
                <i class="share_line">谁赞了Ta</i>
             <?php endif; ?>
            </div>
            <div class="col-xs-4 nPad">
                <img src="/images/22222.png" width="100%" class="line3">
            </div>
        </div>
        <div class="clearfix"></div>
        <ul class="shareUl">
         <?php foreach ($likelist as $key => $value): ?>
            <li>
                <img class="borRad50 float-left mr2" src="<?php echo empty($value['head']) ? '/images/dev/face.png' : $value['head']; ?>" width="46">
                <div class="float-left">
                    <p class="blue1 n30 mt5"><?php echo $value['realname']; ?></p>
                    <p class="blue2 n24"><?php echo date('m', strtotime($value['create_time'])); ?>月<?php echo date('d', strtotime($value['create_time'])); ?>日 <?php echo date('H:i', strtotime($value['create_time'])); ?></p>
                </div>
                <div class="float-right">
                    <span class="blue3 n30"><?php echo sprintf("%.2f", $value['amount']); ?> 点</span>
                </div>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>
</div>

<div style="display:none" class="Hmask"></div>
<div style="display:none" class="buttonan" >
    <p>登录后才能点赞！</p>
    <a href="/new/reg?url=<?php echo urlencode(Yii::$app->request->url); ?>" class="error">去登陆</a>
    <div class="errorclose"><img src="/images/icon_close2.png"></div>
</div>

<script>
	  $("#loan_like_stat_hlz").click(function(){
		 var loan_id = $(this).attr('loan');
		 var user_id = $(this).attr('login');
		 $.post("/dev/share/loanlikestat_hlz", {loan_id: loan_id, user_id: user_id}, function(result) {
		   var data = eval("(" + result + ")");
		   //alert(data.ret);
		  if(data.ret == '1'){
			   var num = parseInt($('#cai').val());
			   $('#himg').attr('src','/images/downRed.png');
			   $('#cai').val(num+1);
			   $('#lzh').html('<div class="txt_one"><p class="txt_one_one three_one">谁又踩</p><p class="txt_one_two three_two">我了，人品</p><p class="txt_one_three three_three">败光了!</p></div>');
		   }
	  });
	  });
      $('.toloanin').on('click',function(){
          $('.Hmask').show();
          $('.buttonan').show();
      })
      $('.buttonan .errorclose').click(function(){
          $('.Hmask').hide();
          $('.buttonan').hide();
      });
      $('.Hmask').click(function(){
          $('.Hmask').hide();
          $('.buttonan').hide();
      });
</script>

<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script src="/js/zebra_dialog.js"></script>
<script>
    $(document).ready(function(){
          var proValue = $('#progress1').attr('value');
          var proMax = $('#progress1').attr('max');
          var proPercent = (proValue/proMax)*100;
          $('.Hcontainer .proWrap .proBar').css('width',proPercent + '%')
          $('#proYuan').css('left',(proPercent-2) + '%');
    });

    wx.config({
        debug: false,
        appId: '<?php echo $jsinfo['appid']; ?>',
        timestamp: '<?php echo $jsinfo['timestamp']; ?>',
        nonceStr: '<?php echo $jsinfo['nonceStr']; ?>',
        signature: '<?php echo $jsinfo['signature']; ?>',
        jsApiList: [
            'onMenuShareTimeline',
            'onMenuShareAppMessage',
            'showOptionMenu'
        ]
    });

    wx.ready(function() {
        wx.showOptionMenu();
        // 2. 分享接口
        // 2.1 监听“分享给朋友”，按钮点击、自定义分享内容及分享结果接口
        wx.onMenuShareAppMessage({
            title: '帮我点一下，现金收益送给你',
            desc: '银行又降息，股市风险大，来这里不花钱赚收益',
            link: '<?php echo $shareUrl; ?>',
            imgUrl: '<?php echo empty($loanuserinfo['head']) ? '/images/dev/face.png' : $loanuserinfo['head']; ?>',
            trigger: function(res) {
                // 不要尝试在trigger中使用ajax异步请求修改本次分享的内容，因为客户端分享操作是一个同步操作，这时候使用ajax的回包会还没有返回
            },
            success: function(res) {
// 	    	  window.location = "/dev/invest";
            },
            cancel: function(res) {
            },
            fail: function(res) {
            }
        });

        // 2.2 监听“分享到朋友圈”按钮点击、自定义分享内容及分享结果接口
        wx.onMenuShareTimeline({
            title: '帮我点一下，现金收益送给你',
            desc: '银行又降息，股市风险大，来这里不花钱赚收益',
            link: '<?php echo $shareUrl; ?>',
            imgUrl: '<?php echo empty($loanuserinfo['head']) ? '/images/dev/face.png' : $loanuserinfo['head']; ?>',
            trigger: function(res) {
                // 不要尝试在trigger中使用ajax异步请求修改本次分享的内容，因为客户端分享操作是一个同步操作，这时候使用ajax的回包会还没有返回
            },
            success: function(res) {
// 	    	  window.location = "/dev/invest";
            },
            cancel: function(res) {
            },
            fail: function(res) {
                alert(JSON.stringify(res));
            }
        });
    });
</script>
