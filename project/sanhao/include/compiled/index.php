<?php include template("header");?>

<div class="blank108"></div>
<div class="blank10"></div>
<div class="imain">
	<div class="w3">
      	<ul class="pulist">
      	<?php if(is_array($aproductlist)){foreach($aproductlist AS $index=>$one) { ?>
              <li>
              	<div class="img"><a href="/account/productdetail.php?id=<?php echo $one['id']; ?>"><img src="/<?php echo $one['pic']; ?>" alt="<?php echo $one['pname']; ?>" /></a></div>
                  <div class="text">
                  	<div class="name"><a href="/account/productdetail.php?id=<?php echo $one['id']; ?>"><?php echo $one['pname']; ?></a></div>
                  	<div class="fl">
                      	<div class="t">
                          	<strong>￥<?php echo $one['price']; ?></strong>
                             	<?php if($one['old_price'] != ''){?><span><?php echo $one['old_price']; ?></span><?php }?>
                          </div>
                          <div class="b">
                          	<span>库存：<?php if($one['max_number'] != ''){?><?php echo $one['max_number']-$one['sale_number']; ?>件<?php } else { ?>不限<?php }?></span>
                          </div>
                      </div>
                      <div class="fr">
                      	<div class="t">
                          	<span>运费：<?php if($one['express_price'] != ''){?><?php echo $one['express_price']; ?><?php } else { ?>免运费<?php }?></span>
                          </div>
                          <div class="b">
                          	<span><a href="/account/productdetail.php?id=<?php echo $one['id']; ?>">去看看</a></span>
                          </div>
                      </div>
                  </div>
                  <?php if($one['today'] == 'y'){?><div class="tag xindan">今日新单</div><?php }?>
              </li>
              <?php }}?>      
          </ul>
        <?php if($count > 50){?>
        <?php echo $pagestring; ?>
        <?php }?>
    </div>
</div>
<div class="blank40"></div>
<?php include template("footer");?>
