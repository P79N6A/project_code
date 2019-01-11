<div class="font16">
    <a href = "http://a.app.qq.com/o/simple.jsp?pkgname=com.xianhuahua.yiyiyuan_1"><img src="/290/images/div_banner.jpg" width="100%" /></a>
    <ul class="nav_jk overflow">
        <li><div class="item" onclick="loanChange(1)"><a>信用借款</a></div></li>
        <li><div class="item on" onclick="loanChange(4)"><a>担保借款</a></div></li>
    </ul>
</div>
<?php if ($userinfo->status == 2): ?>
    <div style="margin:25px 10% 15px">
        <h3 style="text-align:center; padding-bottom:20px; border-bottom:2px solid #e74747; font-size: 2rem; ">资料已提交成功</h3>
        <p style="text-indent:24px; padding-top:10px; font-size: 1.1rem;"> 由于您是初次使用，需要进行身份审核，工作时间（早9点半--晚6点半）24小时内审核完成，非工作时间次日进行审核。</p>
    </div>
    <div class="main">
        <ul>
            <li class="">
                <form class="form-horizontal" role="form">
                    <button type="button" class="btn mt20" style="width: 80%; margin:0 10%;background-color: #e74747; color: #fff;font-size: 1.3rem; padding: 10px 0px; border-radius: 5px; border: none;" onclick="javascript:window.location = '/new/loan'">刷新</button>
                </form>
            </li>
        </ul>
    </div>
<?php else: ?>
    <div class="user_loan" style="display: none">
        <h3 class="font14" style="display: none">借款用途</h3>
        <div class="swiper-container swiper-container1" style="display: none">
            <div class="swiper-wrapper credit">
                <?php foreach ($amounts['goods_list'] as $key=>$val): ?>
                    <div <?php if($key == 0){echo "style = 'border-color:#CA0000;border-width:1px;border-style:solid'";}?> class="swiper-slide user_conte goods_<?php echo $val['goods_id'];?>_1" onclick="goodsChange(<?php echo $val['goods_id'];?>,1)">
                        <div class="user_imgtxt">
                            <img src="<?php echo $val['goods_pic']; ?>">
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="seiper-pagination swiper-p1"></div>
        </div>
    </div>
    <div class="user_money">
        <h3 class="font14">借款金额(元)</h3>
        <div class="swiper-containers">
            <div class="swiper-container swiper-container2 font16">
                <div class="swiper-wrapper credit">
                    <?php foreach ($amounts['amount'] as $key => $val): ?>
                        <div onclick="amountChange(<?php echo $key;?>,<?php echo $noTrem = in_array($key,$noTremAmounts) ? 1 : $canMaxTerm ?>,<?php echo count($amounts['money_list']);?>)" class="swiper-slide user_qshu new_shhu1 new_shhu_<?php echo $key;?> <?php if($key == '1500'){echo "addstyle_amount";} ?>">
                            <div><?php echo $key;?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="seiper-pagination swiper-p2"></div>
            </div>
        </div>
    </div>
    <div class="user_money">
        <h3 class="font14 pad10">借款周期(天)</h3>
        <?php foreach ($amounts['amount'] as $key => $val){ ?>
            <div class="swiper-container  font16 swiper-container3 amounts_<?php echo $key; ?>" style="display: none">
                <div class="swiper-wrapper credit" >
                    <?php foreach ($amounts['amount'][$key] as $k => $v): ?>
                        <div onclick="dayTremChange(<?php echo $key;?>,<?php echo $v['days'];?>,<?php echo $v['term']; ?>)"  class="swiper-slide user_qshu new_shhu2 cl<?php echo $v['days']; ?> amount_<?php echo $key; ?> <?php if($v['days'] == '28'){echo "addstyle_days";} ?>" >
                            <div class="liheght">
                                <span><?php echo $v['days']; ?></span>
                                <p>分<?php echo $v['term']; ?>期还</p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="seiper-pagination swiper-p3"></div>
            </div>
        <?php } ?>
    </div>

    <h3 class="jkyongtu" >借款用途(<span class="desc">消费</span>)</h3>
    <div id="demo11" ></div>
    <div class="user_loan" hidden>
        <div class="swiper-container swiper-container1">
            <div class="swiper-wrapper">
<!--                <div class="swiper-slide user_conte">-->
<!--                    <div class="user_imgtxt">-->
<!--                        <img src="/298/images/jkyt3.png">-->
<!--                        <p>旅游</p>-->
<!--                    </div>-->
<!--                </div>-->
<!--                <div class="swiper-slide user_conte">-->
<!--                    <div class="user_imgtxt">-->
<!--                        <img src="/298/images/jkyt2.png">-->
<!--                        <p>进货</p>-->
<!--                    </div>-->
<!--                </div>-->
                <div class="swiper-slide user_conte">
                    <div class="user_imgtxt">
                        <img src="/298/images/jkyt4.png">
                        <p>购买设备</p>
                    </div>
                </div>
                <div class="swiper-slide user_conte">
                    <div class="user_imgtxt">
                        <img src="/298/images/jkyt1.png">
                        <p>购买家具或家电</p>
                    </div>
                </div>
<!--                <div class="swiper-slide user_conte";>-->
<!--                    <div class="user_imgtxt">-->
<!--                        <img src="/298/images/jkyt6.png">-->
<!--                        <p>学习</p>-->
<!--                    </div>-->
<!--                </div>-->
                <div class="swiper-slide user_conte" style = "border-color:#CA0000;border-width:1px;border-style:solid">
                    <div class="user_imgtxt">
                        <img src="/298/images/jkyt5.png">
                        <p>消费</p>
                    </div>
                </div>
<!--                <div class="swiper-slide user_conte">-->
<!--                    <div class="user_imgtxt">-->
<!--                        <img src="/298/images/jkyt7.png">-->
<!--                        <p>资金周转</p>-->
<!--                    </div>-->
<!--                </div>-->
<!--                <div class="swiper-slide user_conte">-->
<!--                    <div class="user_imgtxt">-->
<!--                        <img src="/298/images/jkyt8.png">-->
<!--                        <p>租房</p>-->
<!--                    </div>-->
<!--                </div>-->
<!--                <div class="swiper-slide user_conte">-->
<!--                    <div class="user_imgtxt">-->
<!--                        <img src="/298/images/jkyt9.png">-->
<!--                        <p>其它</p>-->
<!--                    </div>-->
<!--                </div>-->
            </div>

            <div class="seiper-pagination swiper-p1"></div>
        </div>
    </div>
    <div id="demo12" hidden></div>
    <form class="form-horizontal" role="form" method="post" action="/new/loan/second">
        <input id="business_type" type="hidden" name="business_type" value="4"/>
        <input id="goods_id" type="hidden" name="goods_id" value="<?php echo $goods_id = $amounts['goods_list'][0]['goods_id']; ?>"/>
        <input id="amount" type="hidden" name="amount" value="<?php echo $canMaxAmount; ?>"/>
        <input id="days" type="hidden" name="days" value="<?php echo $canMaxDays; ?>"/>
        <input id="trem" type="hidden" name="trem" value="<?php echo $canMaxTerm; ?>"/>
        <input id="csrf" type="hidden" name="_csrf" value="<?php echo $csrf; ?>"/>
        <input id="desc" type="hidden" name="desc" value="消费"/>
        <button class="fqi_jkuan">发起借款</button>
    </form>
    <!--豆荚贷有借款-->
    <div id="canloan" style="display: none"><?php echo $canLoan; ?></div>
    <!--申请借款但还未活体认证-->
    <?php if ($userinfo['status'] != 3 && $loanInfo && $loanInfo->status == 6): ?>
        <div class="Hmask Hmask_none" ></div>
        <div class="duihsucc_new">
            <p class="xuhua_new">您的借款已通过审核！</p>
            <p>下载APP完成视频认证后立即领取借款</p>
            <button class="sureyemian_new down">下载领取</button>
        </div>
    <?php endif; ?>
    <!--驳回借款弹框-->
    <?php if ($reject_data['is_reject'] == 1 && !empty($reject_data['reject_data'])): ?>
        <div class="Hmask Hmask_none" ></div>
        <div class="duihsucc bohomeg">
            <h3>借款驳回</h3>
            <img src="/images/iconjth.png" class="iconjth">
            <p class="bhtime" style="font-size:14px">驳回时间：</p>
            <p class="sfmiao" style="font-size:14px"><?php echo $reject_data['reject_data'][0] ? $reject_data['reject_data'][0] : "暂无数据";?></p>
            <p class="bhtime" style="font-size:14px">驳回理由</p>
            <p class="sfmiao" style="font-size:14px"><?php echo $reject_data['reject_data'][1] ? $reject_data['reject_data'][1] : "暂无数据";?></p>
            <?php if (!empty($reject_data['guide_url'])): ?>
                <input type="hidden" class="guide_url" value="<?php echo $reject_data['guide_url'];?>">
                <button class="yesknow iknow guide" style="font-size:14px;margin-left: 30%;">借钱难？试试这个</button>
            <?php else: ?>
                <button class="yesknow iknow" style="font-size:14px">我知道了</button>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php endif;?>
<script src="/290/js/swiper.jquery.min.js" type="text/javascript" charset="utf-8"></script>
<script>
    var arr_amount =  '<?php echo json_encode($amounts['amount']); ?>';
    var count = '<?php echo count($amounts['amount']); ?>';
    var amount = "<?php echo end($noTremAmounts); ?>";
    var days = "<?php if($canMaxDays != 0){echo $canMaxDays;}else{echo 7;} ?>";
    var initialSlide1 = "<?php echo array_search(end($noTremAmounts), $mList); ?>";
    var initialSlide2 = "<?php echo array_search($canMaxDays, [7,14,21,28,56,84,112,140,168,196,224,252,280,308,336]); ?>";
    amountChange(amount,<?php echo $noTrem = in_array($canMaxAmount,$noTremAmounts) ? 1 : $canMaxTerm ?>, <?php echo count($amounts['money_list']);?>)
    var swiper = new Swiper('.swiper-container1', {
        pagination: '.swiper-p1',
        slidesPerView: 3,
        centeredSlides: true,
        paginationClickable: true,
        spaceBetween: 10,
        grabCursor: true,
        slideToClickedSlide:true,
        el: '.swiper-pagination',
        bulletClass : 'my-bullet',
        initialSlide :5,
        observer:true,
        observeParents: true,
    });
    var swiper2 = new Swiper('.swiper-container2', {
        pagination: '.swiper-p2',
        slidesPerView: 4,
        centeredSlides: true,
        paginationClickable: true,
        spaceBetween: 10,
        grabCursor: true,
        slideToClickedSlide:true,
        initialSlide :initialSlide1,
        observer:true,
        observeParents: true,
        el: '.swiper-pagination',
        bulletClass : 'my-bullet',
        onSlideChangeEnd: function(swiper){
            swiper.update(); //swiper更新
        }
    });
    var swiper3 = new Swiper('.swiper-container3', {
        pagination: '.swiper-p3',
        slidesPerView: 4,
        centeredSlides: true,
        paginationClickable: true,
        spaceBetween: 10,
        grabCursor: true,
        slideToClickedSlide:true,
        initialSlide :initialSlide2,
        el: '.swiper-pagination',
        observer:true,
        observeParents: true,
        bulletClass : 'my-bullet',
        init:false,
    });

    $('.user_conte').click( function () {
       var desc = $(this).children("div").children("p").html();
       $(".desc").html(desc);
       $("#desc").val(desc);
    })
</script>
