<div class="wrap">
<div class="symx_boxb">
	<div class="symx_jycont" id="detailcontent">
	        	
	    <?php if (!empty($details)): ?> 
		<?php foreach ($details as $key => $v): ?>
		<div class="disitem jycon_cont">
			<div class="contone"><?php echo $v['reason'];?> 
				<p><?php echo $v['create_time']; ?></p>
			</div>
			<div class="contwo"></div>
			<div class="conthree">获佣金<em><?php echo number_format($v['profit_amount'], 2, ".", ""); ?></em>RMB</div>
		</div>
		<?php endforeach; ?>
		<?php endif; ?> 
			
		<?php if($nextpage){?>
		<div style="text-align: center;margin:0 auto;" onclick="return getMore();" id="getmore">加载更多</div>	
		<?php }?>	
			
				
	</div>

</div>
</div>
<script type="text/javascript">
	var page_num = 0;
	var detailcontent = $('#detailcontent');
	function getMore(){
		var month = '<?php echo $month;?>';
		page_num++;
		$.get("/background/profit/details",
			{ page:page_num,month:month	},
			function(html){
				$('#getmore').remove();
				detailcontent.append(html);
			}
		);
		return true;
	}
</script>
