<?php include template("header");?>

<?php if($count==0){?>
<div class="blank108"></div>
<div class="blank60"></div>
<div class="pnihility">
	<div class="mc"><img src="/static/images/k.png" alt="" /></div>
    <div class="mb"><a href="javascript:void(0);" id="release_product" class="pre">发布新商品</a></div>
</div>
<div class="blank120"></div>
<?php } else { ?>
<div class="blank108"></div>
<div class="blank60"></div>
<div class="w2 clearfix">
	<div class="commodityList">
    	<div class="mt">
        	<strong>商品列表</strong>
            <a href="javascript:void(0);" id="release_product">发布新商品</a>
        </div>
        <div class="mc">
        <?php if(is_array($product)){foreach($product AS $index=>$one) { ?>
        	<div class="list" id="delproduct_<?php echo $one['id']; ?>">
        		<?php if($one['image'] != ''){?>
                <div class="fore1"><?php if($one['status'] == 2 || $one['status'] == 3){?><img src="/<?php echo $one['image']; ?>" alt="<?php echo $one['pname']; ?>" /><em class="m1">未上架</em><?php }?><?php if($one['status'] == 1){?><a href="/account/productdetail.php?id=<?php echo $one['id']; ?>"><img src="/<?php echo $one['image']; ?>" alt="<?php echo $one['pname']; ?>" /></a>
                
                <?php if($one['end_time'] != '' && $one['max_number'] != ''){?>
                <?php if(($one['end_time'] < $now) || ($one['max_number']-$one['sale_number']<=0)){?>
                <em class="m3">已下架</em>
                <?php } else { ?>
                <em class="m2">出售中</em>
                <?php }?>
                <?php }?>
                <?php if($one['end_time'] != '' && $one['max_number'] == ''){?>
                <?php if($one['end_time'] < $now){?>
                <em class="m3">已下架</em>
                <?php } else { ?>
                <em class="m2">出售中</em>
                <?php }?>
                <?php }?>
               	<?php if($one['end_time'] == '' && $one['max_number'] != ''){?>
                <?php if(($one['max_number']-$one['sale_number'] <= 0)){?>
                <em class="m3">已下架</em>
                <?php } else { ?>
                <em class="m2">出售中</em>
                <?php }?>
                <?php }?>
                <?php if($one['end_time'] == '' && $one['max_number'] == ''){?>
                <em class="m2">出售中</em>
                <?php }?>
                <?php }?></div>
                <?php } else { ?>
                <div class="fore1"><?php if($one['status'] == 2){?><img src="/static/images/img1.jpg" alt="<?php echo $one['pname']; ?>" /><em class="m1">未上架</em><?php }?><?php if($one['status'] == 1){?><a href="/account/productdetail.php?id=<?php echo $one['id']; ?>"><img src="/static/images/img1.jpg" alt="<?php echo $one['pname']; ?>" /></a>
                
              	<?php if($one['end_time'] != '' && $one['max_number'] != ''){?>
                <?php if(($one['end_time'] < $now) || ($one['max_number']-$one['sale_number']<=0)){?>
                <em class="m3">已下架</em>
                <?php } else { ?>
                <em class="m2">出售中</em>
                <?php }?>
                <?php }?>
                <?php if($one['end_time'] != '' && $one['max_number'] == ''){?>
                <?php if($one['end_time'] < $now){?>
                <em class="m3">已下架</em>
                <?php } else { ?>
                <em class="m2">出售中</em>
                <?php }?>
                <?php }?>
               	<?php if($one['end_time'] == '' && $one['max_number'] != ''){?>
                <?php if(($one['max_number']-$one['sale_number'] <= 0)){?>
                <em class="m3">已下架</em>
                <?php } else { ?>
                <em class="m2">出售中</em>
                <?php }?>
                <?php }?>
                <?php if($one['end_time'] == '' && $one['max_number'] == ''){?>
                <em class="m2">出售中</em>
                <?php }?>
                
                
                <?php }?></div>
                <?php }?>
                <div class="fore2">
                	<h2><?php if($one['status'] == 1){?><a href="/account/productdetail.php?id=<?php echo $one['id']; ?>"><?php echo $one['pname']; ?></a><?php } else { ?><?php echo $one['pname']; ?><?php }?></h2>
                    <h4>售价：<span><?php echo $one['price']; ?></span>元</h4>
                    <p><?php echo $one['info']; ?></p>
                </div>
                <div class="fore3">
                    <dl class="item1">
                    <?php if($one['sale_number'] != 0){?>
                        <dt>￥<?php echo $one['totalorigin']; ?></dt>
                   <?php } else { ?>
                   		<dt>0</dt>
                   <?php }?>
                        <dd>销售额</dd>
                    </dl>
                    <dl class="item2">
                    	<?php if($one['sale_number'] != ''){?>
                        <dt><?php echo $one['sale_number']; ?></dt>
                        <?php } else { ?>
                        <dt>0</dt>
                        <?php }?>
                        <dd>已成交订单</dd>
                    </dl>
                    <dl class="item3">
                    	<?php if($one['pageview'] != ''){?>
                        <dt><?php echo $one['pageview']; ?></dt>
                        <?php } else { ?>
                        <dt>0</dt>
                        <?php }?>
                        <dd>浏览数</dd>
                    </dl>
                    <dl class="item4">
                    	<?php if($one['max_number'] != ''){?>
                        <dt><?php echo $one['max_number']-$one['sale_number']; ?></dt>
                        <?php } else { ?>
                        <dt>无限</dt>
                        <?php }?>
                        <dd>库存</dd>
                    </dl>
                </div>
                <div class="fore4">
                    <ul>
                        <li class="item1"><a href="/account/productmodify.php?id=<?php echo $one['id']; ?>">编辑</a></li>
                        <?php if($one['status'] == 1 || $one['status'] == 3){?>
                        <li class="item2"><a href="/account/productdetail.php?id=<?php echo $one['id']; ?>">查看</a></li>
                        <?php }?>
                        <?php if($one['status'] == 1){?>
                        <li class="item3"><a href="javascript:void(0);" class="shelvesproduct" pid="<?php echo $one['id']; ?>">下架</a></li>
                        <?php }?>
                        <?php if($one['status'] == 2){?><li class="item3"><a href="javascript:void(0);" class="delproduct" pid="<?php echo $one['id']; ?>">删除</a></li><?php }?>
                    </ul>
                </div>
            </div>
            <?php }}?> 
            
        </div>
        <?php if($count > 10){?>
        <?php echo $pagestring; ?>
        <?php }?>
    </div>
</div>
<div class="blank40"></div>
<?php }?>

<?php include template("footer");?>