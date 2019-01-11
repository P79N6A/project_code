<div class="sd">
	<div class="tt">功能菜单</div>
    <?php if(is_array($aHome)){foreach($aHome AS $index=>$one) { ?>
    <dl class="itm">
        <dt class="cur"><?php echo $one['name']; ?></dt>
        <dd>
         <?php if(is_array($sMenu)){foreach($sMenu AS $skey=>$sval) { ?>
         <?php if($one['id'] == $sval['pid']){?>
            <a <?php if($menucolor == $sval['name'] ){?> class='hover' <?php }?> href="<?php echo $sval['url']; ?>"><?php echo $sval['name']; ?></a>
            <?php }?>
        <?php }}?>
        </dd>
    </dl>
    <?php }}?>
</div>