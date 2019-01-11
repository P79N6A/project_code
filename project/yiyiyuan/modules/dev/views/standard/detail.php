    <div class="Investment_record">
        <div class="jisdbir">
            <div class="list_xylc" onclick="show()"><span>信用理财,借鸡生蛋</span><img src="/images/licai_list.png"></div>
            <div style="clear:both"></div>
            <div class="title_bdxqcon">
                <div class="xhb_bgbg"><img src="/images/xhb_bgbg.png"></div>
                <div class="xhhb_yuan">
                    <span class="yuand_left"><img src="/images/title_xhhb.png"><em><?php echo $standard_information->name;?></em></span>
                    <span class="yuand_right">剩余时间:<?php echo (round((strtotime($standard_information->open_enddate) - time()) / 3600) > 0) ? round((strtotime($standard_information->open_enddate) - time()) / 3600) : 0;?>h</span>
                </div>
                <div class="xhhb_table">
                    <p class="xhhb_tablone"><span>总额度</span><span>预期年收益</span><span style="width:35%;">期限<em>(<?php echo date('n'.'.'.'j', strtotime($standard_information->start_date));?>-<?php echo date('n'.'.'.'j', strtotime($standard_information->end_date));?>)</em></span></p>
                    <p class="xhhb_tabtwo"><span><em><?php echo ($standard_information->progress->total_amount/10000)?></em>万</span><span><em><?php echo sprintf('%.2f',$standard_information->yield); ?>%</em></span><span><em><?php echo $standard_information->cycle;?></em>天</span></p>
                </div>
                <div class="xhhb_threeb">
                    <div class="therrb_left"><p style="width:<?php echo (intval($standard_information->progress->total_invested_amount) / intval($standard_information->progress->total_amount)) * 100 >= 100 ? 100 : (intval($standard_information->progress->total_invested_amount) / intval($standard_information->progress->total_amount)) * 100;?>%;"></p><?php if($standard_information->status == 'SUCCEED'):?><span class="manbian">＊标满</span><?php else:?><span>＊剩余<?php echo number_format(($standard_information->progress->total_amount-$standard_information->progress->total_invested_amount),2)>0 ? number_format(($standard_information->progress->total_amount-$standard_information->progress->total_invested_amount),2) : 0?>元</span><?php endif;?></div>
                    <div class="therrb_right"><?php if($standard_information->status == 'SUCCEED'):?><a class="c2co">售  罄</a><?php else:?><a href="/dev/standard/invest?standard_id=<?php echo $standard_information->id;?>">投资</a><?php endif;?></div>
                </div>
            </div>
        </div>
        <div class="xujs_xmjs">
            <h3><em></em>项目介绍</h3>
            <p><?php echo $standard_information->desc;?></p>
        </div>
        <?php if(!empty($invest_list)):?>
        <div class="xujs_xmjs tzrjb">
            <h3><em></em>投资人列表</h3>
            <?php foreach ($invest_list as $key=>$value):?>
            <p><span class="oneone"><?php if($value->buy_type == 'GENE'):?><?php if($value->user_id <=0):?><?php echo \Common::truncate_utf8_string(\Mobile::getusername($value->user_id)['name'], 1, '').'**';?><?php else:?><?php echo \Common::truncate_utf8_string($value->user->realname, 1, '').'**';?><?php endif;?><?php else:?><?php if($value->user_id <=0):?><?php echo \Common::truncate_utf8_string(\Mobile::getusername($value->user_id)['name'], 1, '').'**';?><?php else:?><?php echo \Common::truncate_utf8_string($value->user->realname, 1, '').'**';?><?php endif;?><?php endif;?></span><span class="two"><?php echo number_format(($value->buy_share),2)?></span><span class="three"><?php echo date('H'.':'.'i'.','.'m'.'月'.'d'.'日', strtotime($value->last_modify_time));?></span></p>
            <?php endforeach;?>
        </div>
        <?php endif;?>
        <a href="/dev/standard/history" class="histo_lishi">历史标的>></a>
    </div>
    

    <div id="overDiv" style="display:none;"></div>
    <div id="diolo_warp" class="diolo_warp heightchan" style="display:none;">
        <h3 class="diolaxk"><em></em>信用理财,借鸡生蛋</h3>
        <div class="xylc">
            <p>1.购买担保卡，使用担保额度投资“园丁计划”标的；</p>
            <p>2.投资“园丁计划”标的,期满后,额度返还,坐等收益；</p>
            <p>3.投资期间，可提前发起赎回，赎回投资没有收益；</p>
            <p>4.使用优惠券获得双倍收益(系统发放+分享即得)；</p>
            <p>5.最终解释权归先花一亿元所有。</p>  
        </div>
        <p class="radious_img"></p>
        <p class="go_on"></p>
        <div class="true_flase">
            <a class="true_qr" onclick="closeDiv()">朕知道了</a>
        </div>
    </div>  

<script type="text/javascript">
            function show(){
                document.getElementById("overDiv").style.display = "block" ;
                document.getElementById("diolo_warp").style.display = "block" ;
            }
            function closeDiv(){
                document.getElementById("overDiv").style.display = "none" ;
                 document.getElementById("diolo_warp").style.display = "none" ;
            }
</script>
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