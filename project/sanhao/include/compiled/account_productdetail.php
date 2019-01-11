<?php include template("header");?>

<div class="blank108"></div>
<div class="blank60"></div>
<div class="w2 clearfix">
	<div class="commodityDetail clearfix">
    	<div class="detailMain">
        	<div class="name"><?php echo $product['pname']; ?></div>
            <div class="silder-new fl">
                <div id="slider" class="slider-box">
                <?php if(is_array($image)){foreach($image AS $index=>$one) { ?>
                    <div class="contentimg"><img src="/<?php echo $one['image']; ?>" width="500" height="375" alt="" /></div>
               <?php }}?>
                </div>
                <img src="/account/count.php?id=<?php echo $product['id']; ?>" width="1" height="1" />
                <div class="pagination-box">
                    <div id="paginate-slider" class="paginate-slider">
                    <?php if(is_array($image)){foreach($image AS $index=>$one) { ?>
                        <a href="javascript:void(0);" class="toc"><img src="/<?php echo $one['image']; ?>" width="80" height="60" alt="" /></a>
                   <?php }}?>
                    </div>
                </div>
                <?php if($product['end_time'] != '' && $product['max_number'] != ''){?>
                <?php if(($product['end_time'] < $now) || ($product['max_number']-$product['sale_number'] <= 0)){?>
                <div class="silder-mask">
                	<p>已下架</p>
                </div>
                <?php }?>
                <?php }?>
                <?php if($product['end_time'] != '' && $product['max_number'] == ''){?>
                <?php if($product['end_time'] < $now){?>
                <div class="silder-mask">
                	<p>已下架</p>
                </div>
                <?php }?>
                <?php }?>
                <?php if($product['end_time'] == '' && $product['max_number'] != ''){?>
                <?php if(($product['max_number']-$product['sale_number'] <= 0)){?>
                 <div class="silder-mask">
                	<p>已下架</p>
                </div>
                <?php }?>
                <?php }?>
            </div>
            <div class="clear"></div>
            <div class="nshare clearfix">
            	<span>分享：</span>
                <ul class="clearfix">
		            <li class="item1"><a href="javascript:void((function(s,d,e){try{}catch(e){}var f='http://v.t.sina.com.cn/share/share.php?',pname='<?php echo $productweibo; ?>',u=d.location.href,p=['url=',e(u),'&title=',e(pname),'&appkey=2051999832'].join('');function a(){if(!window.open([f,p].join(''),'mb',['toolbar=0,status=0,resizable=1,width=620,height=450,left=',(s.width-620)/2,',top=',(s.height-450)/2].join('')))u.href=[f,p].join('');};if(/Firefox/.test(navigator.userAgent)){setTimeout(a,0)}else{a()}})(screen,document,encodeURIComponent));">分享到</a></li>
		            <li class="item2"><a href="javascript:void(0);" onclick="window.open('http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url='+encodeURIComponent(document.location.href)+'&desc=<?php echo $productqone; ?>');return false;">分享到</a></li>
		            <li class="item3"><a href="javascript:void(0);" onclick="{ var _t = '<?php echo $productweibo; ?>';  var _url = encodeURI(window.location); var _appkey = 'ac191e7394d68f793435034125d4443e'; var _site = encodeURI; var _pic = ''; var _u = 'http://v.t.qq.com/share/share.php?title='+_t+'&url='+_url+'&appkey='+_appkey+'&site='+_site+'&pic='+_pic; window.open( _u,'转播到腾讯微博', 'width=700, height=580, top=180, left=320, toolbar=no, menubar=no, scrollbars=no, location=yes, resizable=no, status=no' );  };" >分享到</a></li>
		            <li class="item4"><a href="javascript:void(0);" onclick="window.open('http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url='+encodeURIComponent(document.location.href)+'&desc=<?php echo $productqone; ?>');return false;">分享到</a></li>
		            <li class="item5"><a href="javascript:window.open('http://www.douban.com/recommend/?url='+encodeURIComponent(location.href)+'&title=<?php echo $productqone; ?>','_blank','scrollbars=no,width=600,height=450,left=75,top=20,status=no,resizable=yes'); void 0">分享到</a></li>
		            <li class="item6"><a href="javascript:void(0);" onclick="javascript:void((function(s,d,e){var f='http://share.renren.com/share/buttonshare?link=',u=location.href,l='<?php echo $productqone; ?>',p=[e(u),'&title=',e(l)].join('');function a(){if(!window.open([f,p].join(''),'xnshare',['toolbar=0,status=0,resizable=1,width=626,height=436,left=',(s.width-626)/2,',top=',(s.height-436)/2].join('')))u.href=[f,p].join('');};if(/Firefox/.test(navigator.userAgent))setTimeout(a,0);else a();})(screen,document,encodeURIComponent));">分享到</a></li>
		            <li class="item7"><a href="javascript:window.open('http://www.kaixin001.com/repaste/share.php?rurl='+encodeURIComponent(location.href)+'&rcontent=<?php echo $productqone; ?>','_blank','scrollbars=no,width=600,height=450,left=75,top=20,status=no,resizable=yes'); void 0">分享到</a></li>
		            <li class="item8"><a href="javascript:void(0);" onclick="window.open('http://connect.qq.com/widget/shareqq/index.html?url='+encodeURIComponent(document.location.href)+'&title=<?php echo $product['pname']; ?>&desc=<?php echo $productqone; ?>');return false;">分享到</a></li>
		        	<li class="item9" id="share_detail"><a href="javascript:void(0)">分享到</a>
		        		<div class="slink" id="share_detail_div">
		        			<input  id="shareproduct_copy" type="text" class="ntxt" value="<?php echo $INI['system']['wwwprefix']; ?>/account/productdetail.php?id=<?php echo $id; ?>"></span>
		        			<input type="button" class="nbtn"  onclick="copyText('<?php echo $INI['system']['wwwprefix']; ?>/account/productdetail.php?id=<?php echo $id; ?>');" id="product_url" value="点击复制" />
		        		</div>
                    </li>
		        </ul>
            </div>
            <div class="clear"></div>
            <div class="intro">
            	<div class="fore1"><strong>商品描述</strong></div>
                <div class="fore2"><?php echo $product['description']; ?></div>
            </div>
            <div class="clear"></div>
            <div class="comment">
                <div class="textarea"><textarea id="sdsm" name="comment"></textarea><input type="button" value="提交" id="shopping_detail_comment_button" pid="" /><label for="sdsm">您可以在这说点什么...</label></div>
                <div class="bqzs clearfix">
					<div class="smileysbox">
				        
				    </div>                
                    <span class="zsxz" id="comment_reset"></span><span class="xzbq"  onclick="findexpress($(this))" id="choice_img">选择表情</span>
                </div>
                <div class="detail_comments">
				<?php if(is_array($procomments)){foreach($procomments AS $cval) { ?>
                <dl class="commentlist">
                   	<?php if(is_array($userlists)){foreach($userlists AS $uval) { ?>
                	<dt><a href="javascript:void(0)"><img src="<?php echo $uval['headerurl']; ?>" alt="" /></a></dt>
                    <dd>
                    	<p class="p1"><span><?php echo $cval['created']; ?></span><?php if($cval['uid'] == $uval['id'] ){?><?php echo $uval['mobile']; ?><?php if($uval['nickname'] != ''){?>（<?php echo $uval['nickname']; ?>）<?php }?><?php }?></p>
                        <p class="p2"><?php echo $cval['comment']; ?></p>
                    </dd>
                   	<?php }}?>
                </dl>
				<?php }}?>
				<?php if($count > 10){?>
		        <?php echo $pagestring; ?>
		        <?php }?>
		        </div>
            </div>
        </div>
        <div class="detailSide">
            <div class="buy">
                <h3><span>￥<?php echo $product['price']; ?></span></h3>
                <dl class="item">
                    <dt>数量</dt>
                    <dd>
                        <div class="wrap-input">
                            <a href="javascript:void(0);" class="btn-reduce">-</a>
                            <input type="text" value="1" name="buy_num" maxlength="5" id="buy_num" class="text" />
                            <a href="javascript:void(0);" class="btn-add">+</a>
                        </div>
                        <div class="store-prompt"></div>
                    </dd>
                </dl>
                <?php if(is_array($property)){foreach($property AS $index=>$one) { ?>
                <dl class="item">
                    <dt><?php echo $one['name']; ?></dt>
                    <dd><select class="select">
                    <?php if(is_array($one['size'])){foreach($one['size'] AS $key=>$value) { ?>
                    <?php if($value != ''){?>
                    <option value="<?php echo $value; ?>"><?php echo $value; ?></option>
                    <?php }?>
                    <?php }}?>
                    </select></dd>
                </dl>
                <?php }}?>
                
                <div class="buybtn">
                <?php if($product['end_time'] != '' && $product['max_number'] != ''){?>
                <?php if(($product['end_time'] < $now) || ($product['max_number']-$product['sale_number']<=0)){?>
                <a class="disabled">购买</a>
                <?php } else { ?>
                <a href="javascript:void(0);" id="product_buy" pid="<?php echo $product['id']; ?>">购买</a>
                <?php }?>
                <?php }?>
                <?php if($product['end_time'] != '' && $product['max_number'] == ''){?>
                <?php if($product['end_time'] < $now){?>
                <a class="disabled">购买</a>
                <?php } else { ?>
                <a href="javascript:void(0);" id="product_buy" pid="<?php echo $product['id']; ?>">购买</a>
                <?php }?>
                <?php }?>
                <?php if($product['end_time'] == '' && $product['max_number'] != ''){?>
                <?php if(($product['max_number']-$product['sale_number'] <= 0)){?>
                <a class="disabled">购买</a>
               	<?php } else { ?>
                <a href="javascript:void(0);" id="product_buy" pid="<?php echo $product['id']; ?>">购买</a>
                <?php }?>
                <?php }?>
                <?php if($product['end_time'] == '' && $product['max_number'] == ''){?>
                <a href="javascript:void(0);" id="product_buy" pid="<?php echo $product['id']; ?>">购买</a>
                <?php }?>
                </div>
            </div>
            <div class="buyt">
                <dl class="item">
                    <dt>运费</dt>
                    <?php if($product['express_price'] != ''){?>
                    <dd>￥<?php echo $product['express_price']; ?></dd>
                 	<?php } else { ?>
                    <dd>￥0.00</dd>
                 	<?php }?>  
                </dl>
                <dl class="item">
                    <dt>库存</dt>
                    <?php if($product['max_number'] != ''){?>
                    <?php if($product['sale_number'] != ''){?>
                    <dd id="max_num" num="<?php echo $product['max_number']-$product['sale_number']; ?>"><?php if(($product['max_number']-$product['sale_number']) == 0){?>商品已卖光<?php } else { ?><?php echo $product['max_number']-$product['sale_number']; ?><?php }?></dd>
                    <?php }?>
                    <?php } else { ?>
                    <dd id="max_num" num="不限">不限</dd>
                    <?php }?>
                </dl>
                <dl class="item">
                	<?php if($product['end_time'] != ''){?>
                	<?php if($product['end_time'] > $now){?>
                    <dt id="lasttime" curtime="<?php echo $now; ?>000" diff="<?php echo $diff_time; ?>000">剩余时间</dt>
                    <?php if($left_day>0){?>
                    <dd id="difftime"><?php echo $left_day; ?>天<?php echo $left_hour; ?>时<?php echo $left_minute; ?>分<?php echo $left_time; ?>秒</dd>
                    <?php } else { ?>
                    <dd id="difftime">0天<?php echo $left_hour; ?>时<?php echo $left_minute; ?>分<?php echo $left_time; ?>秒</dd>
                    <?php }?>
                    <?php } else { ?>
                    <dt>剩余时间</dt>
                    <dd>已结束</dd>
                    <?php }?>
                    <?php }?>
                </dl>
            </div>
            <div class="abseller">
            	<h3><span>关于卖家</span></h3>
                <dl class="item"><input type="hidden" id="shopping_merchants_id" value="<?php echo $user['id']; ?>" />
                <?php if($user['headerurl'] != ''){?>
                	<dt><img src="<?php echo $user['headerurl']; ?>" alt="" /></dt>
                <?php } else { ?>
                	<dt><img src="/static/images/50.png" alt="" /></dt>
                <?php }?>
                    <dd>
                    <?php if($user['type'] == 1){?>
                    	<p><?php echo $user['email']; ?><?php if($user['nickname'] != ''){?>（<?php echo $user['nickname']; ?>）<?php }?></p>
                    <?php } else { ?>
                    	<p><?php echo $user['mobile']; ?><?php if($user['nickname'] != ''){?>（<?php echo $user['nickname']; ?>）<?php }?></p>
                    <?php }?>
                    <?php if($user['website'] != ''){?>
                        <p><a href="<?php echo $user['website']; ?>" target="_blank"><?php echo $user['website']; ?></a></p>
                    <?php }?>
                    </dd>
                </dl>
                <div class="text">
                <?php echo $user['description']; ?>
                </div>
                <?php if($user['qq'] != ''){?>
                <dl class="item2">
                	<dt>联系卖家：</dt>
                    <dd><a target="_blank" href="http://wpa.qq.com/msgrd?v=3&uin=<?php echo $user['qq']; ?>=&site=qq&menu=yes"><img border="0" src="http://wpa.qq.com/pa?p=2:<?php echo $user['qq']; ?>:51" alt="点击这里给我发消息" title="点击这里给我发消息"/></a></dd>
                </dl>
                <?php }?>
            </div>
            <div class="mrseller">
            	<?php if($user['type'] == 1){?>
            	<h3><span><?php echo $user['email']; ?><?php if($user['nickname'] != ''){?>（<?php echo $user['nickname']; ?>）<?php }?>的其他商品</span></h3>
            	<?php } else { ?>
            	<h3><span><?php echo $user['mobile']; ?><?php if($user['nickname'] != ''){?>（<?php echo $user['nickname']; ?>）<?php }?>的其他商品</span></h3>
            	<?php }?>
                <ul class="item">
                <!--{if(!empty($other))}-->
                 <?php if(is_array($other)){foreach($other AS $index=>$one) { ?>
                	<li>
                    	<a href="/account/productdetail.php?id=<?php echo $one['id']; ?>"><img src="/<?php echo $one['image']; ?>" alt="<?php echo $one['pname']; ?>" /></a>
                        <p class="fore1"><a href="/account/productdetail.php?id=<?php echo $one['id']; ?>"><?php echo $one['title']; ?></a></p>
                        <p class="fore2">￥<?php echo $one['price']; ?></p>
                    </li>
                 <?php }}?>
                <!--{/if}-->  
                </ul>
            </div>
        </div>
    </div>
</div>
<div class="blank40"></div>
<script>
setTimeout('getproductlasttime()',1000);
</script>
<script type="text/javascript">
featuredcontentslider.init({
	id: "slider",  
	contentsource: ["inline", ""],  
	toc: "markup",  
	nextprev: ["prev", "next"],  
	revealtype: "click",
	enablefade: [true, 0.1], 
	autorotate: [false, 3000], 
	onChange: function(previndex, curindex){ 
	}
})
</script>

<?php include template("footer");?>