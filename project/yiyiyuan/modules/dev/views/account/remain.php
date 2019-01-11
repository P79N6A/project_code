<div class="jdyu">
    <script  src='/dev/st/statisticssave?type=8'></script> 
    <div class="index_banner">
        <a href="/html/jiedong.html"><p class="ppz"><img src="/images/index_one.png"></p></a>
        <p class="pone">我的授信额度：</p>
        <p class="ptwo"><?php echo sprintf("%.2f", $userinfo['account']['amount']); ?><em>点</em></p>
        <p class="pthree">距离一亿元还差：</p>
        <p class="pfour"> <?php echo sprintf("%.2f", $userinfo['account']['remain_amount']); ?></p>
    </div>	
    <div class="message">
        <div class="jbmess"><span>◆</span>   <em>基本信息</em></div>
        <div class="mesone">
            <div class="mesone1"><img src="/images/mess.png"></div>
            <div class="mesone2">
                <h3>完善我的资料</h3>
                <p>注册为先花花一亿元会员, <br/>并完善个人资料, <br/>获得<em>600点</em>授信额度</p>
            </div>
            <?php if (empty($userinfo->company)): ?>
                <a href="/dev/reg/personals?url=<?php echo urlencode('/dev/account/remain'); ?>&from=remain"><div class="mesone3 redzt">去完善</div></a>
            <?php else: ?>
                <div class="mesone3">已完善</div>
            <?php endif; ?>
        </div>

        <div class="mesone">
            <div class="mesone1"><img src="/images/mess3_1.png"></div>
            <div class="mesone2">
                <h3>熟人认证</h3>
                <p>每成功认证熟人或被熟人认证<br/>都可获得<em>100点</em>授信额度</p>
            </div>
            <a href="/dev/invitation/index"><div class="mesone3 redzt">去认证</div></a>
        </div>
        <div class="mesone">
            <div class="mesone1"><img src="/images/mess3_2.png"></div>
            <div class="mesone2">
                <h3>邀请好友</h3>
                <p>通过邀请码邀请好友来先花<br/> 一亿元赚钱，每邀请一位好友<br/> 就可以获得<em>30点</em>授信额度</p>
            </div>
            <a href="/dev/share/invite"><div class="mesone3 redzt">去邀请</div></a>
        </div>
        <div class="mesone">
            <div class="mesone1"><img src="/images/mess7.png"></div>
            <div class="mesone2">
                <h3>运营商验证</h3>
                <p>上传手机近三个月通话详单， <br/>获得<em>500点</em>授信额度</p>
            </div>
            <?php if ($juli == 1): ?>
                <a href="/dev/account/juxinli?url=<?php echo urlencode('/dev/account/remain'); ?>"><div class="mesone3 redzt">去验证</div></a>
            <?php else: ?>
                <div class="mesone3">已解冻</div>
            <?php endif; ?>
        </div>
        <div class="mesone nonebottom">
            <div class="mesone1"><img src="/images/mess4_5.png"></div>
            <div class="mesone2">
                <h3>按时还款</h3>
                <p>借款成功并按时还款，按照借款 <br/>金额<em>10:1</em>获得授信额度。</p>
            </div>
            <?php if ($loan_count == 0): ?>
                <a href="/dev/loan"><div class="mesone3 redzt">去借款</div></a>
            <?php else: ?>
                <div class="mesone3">已解冻</div>
            <?php endif; ?>
        </div>

    </div>
    <!--image src="images/restate.gif"-->
    <div class="morejd"><image src="/images/restate.gif">更多解冻方式，敬请期待～</div>
</div>
