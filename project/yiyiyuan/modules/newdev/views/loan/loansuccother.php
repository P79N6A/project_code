<div class="zuoyminew jk_item">
    <div class="amountyem">
        <span style="">通过APP借款额度更高哦～</span>
        <a href="/dev/ds/down">
            <button style="">去下载</button>
        </a>
    </div>

    <div class="shezhiminay">
        <img style="width:20%;" src="/images/daihk5.png">
        <div class="wenzicont">你的逾期行为已经严重影响了你的信用评级，同时让你朋友待利益遭受损失，请马上还清借款。</div>
    </div>
    <div class="daihukan_cont">
        <div class="daoqihk">逾期应还（元） <span><?php echo sprintf('%.2f', $loaninfo->huankuan_amount); ?></span></div>
        <div class="rowym">
            <div class="corname">借款金额（元）</div>
            <div class="corliyou" ><?php echo sprintf('%.2f', $loaninfo->amount); ?></div>
        </div>
        <div class="rowym">
            <div class="corname">到账金额（元）</div>
            <div class="corliyou"><?php echo $loaninfo->is_calculation == 1 ? sprintf('%.2f', $loaninfo->amount - $loaninfo->withdraw_fee) : sprintf('%.2f', $loaninfo->amount); ?></div>
        </div>
        <div class="rowym">
            <div class="corname">保险费（元）  </div>
            <div class="corliyou" ><?php echo sprintf('%.2f', $loaninfo->withdraw_fee); ?></div>
        </div>
        <div class="rowym">
            <div class="corname">利息（元）</div>
            <div class="corliyou" ><?php echo  sprintf('%.2f', $loaninfo->interest_fee); ?></div>
        </div>
        <div class="rowym">
            <div class="corname">点赞减息（元）</div>
            <div class="corliyou" ><?php if ($loaninfo->like_amount > 0): ?>-<?php echo sprintf('%.2f', $loaninfo->like_amount); ?><?php else: ?>0.00<?php endif; ?></div>
        </div>
        <div class="rowym">
            <div class="corname">逾期罚息（元）</div>
            <div class="corliyou" ><?php echo sprintf('%.2f', $punishment); ?></div>
        </div>
        <div class="rowym">
            <div class="corname">借款期限（天）</div>
            <div class="corliyou" ><?php echo $loaninfo->days; ?></div>
        </div>
        <div class="rowym">
            <div class="corname">应还款时间</div>
            <div class="corliyou" ><?php if (!empty($loaninfo->end_date)): ?><?php echo date('Y年m月d日', (strtotime($loaninfo->end_date) - 24 * 3600)); ?><?php else: ?>以短信推送时间为准<?php endif; ?></div>
        </div>
        <div class="rowym">
            <div class="corname">逾期天数(天)</div>
            <div class="corliyou" ><?php echo ceil(abs(strtotime("now") - strtotime(date('Y-m-d', strtotime($loaninfo->end_date)))) / 86400); ?></div>
        </div>
    </div>
    <a href="/new/repay/repaychoose?loan_id=<?php echo $loaninfo['loan_id']; ?>"><button type="button" class="bgrey">我要还款</button></a>
    <div class="marbot100"></div>
</div>
<?= $this->render('/layouts/_page', ['page' => 'loan']) ?>

<!--申请借款但还未活体认证-->
<?php if($user->status != 3 && $loaninfo->status == 6): ?>
<div class="Hmask Hmask_none" ></div>
<div class="duihsucc">
    <p class="xuhua">您的借款已通过审核！</p>
    <p>下载APP完成视频认证后立即领取借款</p>
    <button class="sureyemian" id = "loansuccok_down">下载领取</button>
</div> 
<?php endif; ?>
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
