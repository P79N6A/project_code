    <div class="Investment_record">
        <div class="jisdbir">
            
            <div style="clear:both"></div>
            <div class="title_bdxqcon channgehei">
                <div class="history_bdxq">
                    <p>投资: <em><?php if(!empty($standard_statistics)):?><?php echo $standard_statistics->total_onInvested_share;?><?php else:?>0<?php endif;?>点</em></p>
                    <p>收益: <em><?php if(!empty($standard_statistics)):?><?php echo sprintf('%.2f',$standard_statistics->achieving_interest);?><?php else:?>0<?php endif;?>元</em></p>
                </div>
                <div class="xhhb_yuan">
                    <span class="yuand_left"><img src="/images/title_xhhb.png"><em><?php echo $standard_information->name;?></em></span>
                    <span class="yuand_right"><?php echo date('n'.'月'.'j'.'日'.','.'Y'.'年', strtotime($standard_information->online_date));?></span>
                </div>
                <div class="xhhb_table">
                    <p class="xhhb_tablone"><span>总额</span><span>年利率</span><span>期限</span></p>
                    <p class="xhhb_tabtwo"><span><em><?php echo ($standard_information->progress->total_amount/10000)?></em>万</span><span><em><?php echo sprintf('%.2f',$standard_information->yield); ?>%</em></span><span><em><?php echo $standard_information->cycle;?></em>天</span></p>
                </div>
                <div class="xhhb_threeb">
                    <div class="therrb_left konwlens"><p style="width:100%;"></p></div>
                    <div class="therrb_right konwrigh"><a>满标</a></div>
                </div>
            </div>
        </div>
        <div class="xujs_xmjs margintop220">
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
