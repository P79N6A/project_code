<div class="Hcontainer nP">
    <div class="padlr344 mt20">
        <div class="bWhite borRad5 overflow padlr281">
            <div class="col-xs-2 nPad">
                <img src="<?php
                if ($user['userwx']['head']) {
                    echo $user['userwx']['head'];
                } else {
                    echo '/images/face.png';
                }
                ?>" class="face" style="width:100%">
            </div>
            <div class="col-xs-6 n30 red mt2 bold" style="padding-left:10px;" id="longname">
<?php echo!empty($user->userwx) && !empty($user->userwx->nickname) ? (mb_strlen($user->userwx->nickname) > 10 ? mb_substr($user->userwx->nickname, 0, 10) . '...' : mb_substr($user->userwx->nickname, 0, 10)) : $user->realname; ?>
            </div>
            <div class="col-xs-4 text-right nPad">
                <a href="javascript:void(0);" id="get_user_headurl">
                    <img src="/images/icon_reload.png" width="20%">
                    <p class="n34 cor mt3"><?php echo $user->user_type == 1 ? '大学生' : '上班族'; ?></p>
                </a>
            </div>
            <div class="clearfix"></div>
            <div class="float-left mt3 bold n26">
                授信额度（信用点）
            </div>
            <div class="float-right">
                <a href="/dev/account/amount?user_id=<?php echo $user['user_id']; ?>">
                    <span class="n48 red"><?php echo sprintf('%.2f', $user->account->amount); ?></span>
                    <span class="pull-right" style="display:block; width:5%;margin-top: 5%;">
                        <img src="/images/arrowRed.png" width="100%">
                    </span>
                </a>
            </div>
        </div> 
    </div>
<?php if (($user->user_type == 2) && ($user->identity_valid == 2 || $user->identity_valid == 4)): ?>
        <div class="padlr344 mt20 relative">
            <div class="info_title">工作信息</div>
            <a class="info_btn n34 red" href="/dev/reg/shmodifytow?user_id=<?php echo $user['user_id']; ?>&url=<?php echo urlencode('/dev/account/personal&user_id=' . $user->user_id); ?>"><?php echo $user->status == 3 ? '更新' : '修改'; ?></a>
            <div class="clearfix"></div>
            <div class="info_cont bold">
                <div class="border_bottom_1 padtb10 overflow" >
                    <div class="col-xs-1 nPad">
                        <img src="/images/icon_industry.png" width="24">
                    </div>
                    <div class="col-xs-3 n30 grey4 pl lh24">行业</div>
                    <div class="col-xs-8 text-right n30 lh24"><?php echo $industry->name; ?></div>
                </div>
                <div class="border_bottom_1 padtb10 overflow" >
                    <div class="col-xs-1 nPad">
                        <img src="/images/icon_company.png" width="24">
                    </div>
                    <div class="col-xs-3 n30 grey4 pl lh24">公司</div>
                    <div class="col-xs-8 text-right n30 lh24">
                        <div class="divout dBlock margin0"><?php echo $user->company; ?></div>
                    </div>
                </div>
                <div class="padtb10 overflow" >
                    <div class="col-xs-1 nPad">
                        <img src="/images/icon_position.png" width="24">
                    </div>
                    <div class="col-xs-3 n30 grey4 pl lh24">职位</div>
                    <div class="col-xs-8 text-right n30 lh24"><?php echo $position->name; ?></div>
                </div>
            </div>
        </div>
<?php endif; ?>
        <?php if ((($user->user_type == 1) && ($user->school_valid == 2)) || ($user->user_type == 2) && (!empty($user->school))): ?>
        <div class="padlr344 mt20 relative">
            <div class="info_title">学历信息</div>
            <?php if (($user->user_type == 1 && $user->school_valid != 2) || ($user->user_type == 2)): ?>
                <a class="info_btn n34 red" href="/dev/reg/shthree?user_id=<?php echo $user['user_id']; ?>&url=<?php echo urlencode('/dev/account/personal'); ?>"><?php echo!empty($user->school) ? '更新' : '修改'; ?></a>
    <?php endif; ?>
            <div class="clearfix"></div>
            <div class="info_cont bold">
                <div class="border_bottom_1 padtb10 overflow" >
                    <div class="col-xs-1 nPad">
                        <img src="/images/icon_school.png" width="24">
                    </div>
                    <div class="col-xs-3 n30 grey4 pl lh24">学校</div>
                    <div class="col-xs-8 text-right n30 lh24"><?php echo $user->school; ?></div>
                </div>
                <div class="border_bottom_1 padtb10 overflow" >
                    <div class="col-xs-1 nPad">
                        <img src="/images/icon_education.png" width="24">
                    </div>
                    <div class="col-xs-3 n30 grey4 pl lh24">学历</div>
                    <div class="col-xs-8 text-right n30 lh24">
                        <?php if ($user['edu'] == '1') { ?>
                            博士
                        <?php } else if ($user['edu'] == '2') { ?>
                            硕士
                        <?php } else if ($user['edu'] == '3') { ?>
                            本科
                        <?php } else if ($user['edu'] == '4') { ?>
                            专科
    <?php } ?></div>
                </div>
                <div class="padtb10 overflow">
                    <div class="col-xs-1 nPad">
                        <img src="/images/icon_entrance.png" width="24">
                    </div>
                    <div class="col-xs-3 n30 grey4 pl lh24">入学年份</div>
                    <div class="col-xs-8 text-right n30 lh24"><?php echo $user->school_time; ?></div>
                </div>
            </div>
        </div>
            <?php endif; ?>
    <div class="padlr344 mt20 relative">
        <div class="info_cont bold borRad5">
<?php if (($user->identity_valid != 2 && $user->identity_valid != 4) && ($user->user_type == 2)): ?>
                <div class="border_bottom_1 overflow" style="padding:10px 2.81% 10px 0;">
                    <a href="/dev/reg/shtwo?user_id=<?php echo $user['user_id']; ?>&url=<?php echo urlencode('/dev/account/personal'); ?>">
                        <div class="col-xs-4 n30 grey4 nPad">行业信息</div>
                        <div class="col-xs-4 nPad tetx-center"><span class="non">未认证</span></div>
                        <div class="col-xs-4 text-right nPad">
                            <span class="red n30">去认证 
                                <img src="/images/arrowRed.png" width="5%" style="margin-top: -3px;margin-left: 5%;">
                            </span>
                        </div>
                    </a>
                </div>
                <?php endif; ?>
                <?php if ((($user->user_type == 1) && ($user->school_valid != 2)) || ($user->user_type == 2) && (empty($user->school))): ?>
                <div class="overflow" style="padding:10px 2.81% 10px 0;">
                        <?php if ($user->user_type == 2): ?>
                        <a href="/dev/reg/shthree?user_id=<?php echo $user['user_id']; ?>&url=/dev/account/personal?user_id=<?php echo $user['user_id']; ?>">
                            <?php else: ?>
                            <a href="/dev/reg/two?user_id=<?php echo $user['user_id']; ?>&url=/dev/account/personal?user_id=<?php echo $user['user_id']; ?>">
    <?php endif; ?>
                            <div class="col-xs-4 n30 grey4 nPad">学籍信息</div>
                            <div class="col-xs-4 nPad tetx-center"><span class="non" >未认证</span></div>
                            <div class="col-xs-4 text-right nPad">
                                <span class="red n30">去认证 
                                    <img src="/images/arrowRed.png" width="5%" style="margin-top: -3px;margin-left: 5%;">
                                </span>
                            </div>
                        </a>
                </div>
    <?php endif; ?>
        </div>
    </div>
<?php if ($user->status == '3' || $user->status == '2'): ?>
        <div class="padlr344 mt20 relative mb40">
            <div class="info_cont bold borRad5">
                <div class="padtb10 overflow">
                    <div class="col-xs-1 nPad">
                        <img src="/images/icon_camera.png" width="24">
                    </div>
                    <div class="col-xs-3 n30 grey4 pl lh24">自拍照</div>
                    <div class="col-xs-8 text-right n30 lh24"><?php echo $user->status == '3' ? '已通过认证' : '审核中'; ?></div>
                </div>
            </div>
        </div>
<?php else: ?>
        <div class="padlr344 mt20 relative">
            <div class="info_cont bold borRad5">
                <div class="border_bottom_1 overflow" style="padding:10px 2.81% 10px 0;">
                    <a href="/dev/reg/pic?user_id=<?php echo $user['user_id']; ?>&url=<?php echo urlencode('/dev/account/personal'); ?>">
                        <div class="col-xs-4 n30 grey4 nPad">自拍照</div>
                        <div class="col-xs-4 nPad tetx-center"><span class="non">未认证</span></div>
                        <div class="col-xs-4 text-right nPad">
                            <span class="red n30">去认证 
                                <img src="/images/arrowRed.png" width="5%" style="margin-top: -3px;margin-left: 5%;">
                            </span>
                        </div>
                    </a>
                </div>
            </div>
        </div>
<?php endif; ?>
</div>

<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>       
<script>
    $('.Hmask').click(function () {
        $('.Hmask').toggle();
        $('.layer_border').toggle();
    });
    wx.config({
        debug: false,
        appId: '<?php echo $jsinfo['appid']; ?>',
        timestamp: <?php echo $jsinfo['timestamp']; ?>,
        nonceStr: '<?php echo $jsinfo['nonceStr']; ?>',
        signature: '<?php echo $jsinfo['signature']; ?>',
        jsApiList: [
            'closeWindow',
            'hideOptionMenu'
        ]
    });

    wx.ready(function () {
        wx.hideOptionMenu();
    });



</script>