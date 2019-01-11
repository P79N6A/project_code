
<!-- #menu -->
<ul id="menu" class="bg-dark dker">

    <li class="nav-header">智融钥匙订单管理</li>

    <li class="nav-divider"></li>
    <?php

    $menus = [
        ['name' =>'购卡订单', 'url'=>'/balance/purchase/list'],
        ['name' =>'延期订单', 'url'=>'/balance/zrys/list'],
        ['name' =>'退卡订单', 'url'=>'/balance/retreat/list'],

    ];

    foreach ($menus as $menu) {
        $active = $pathInfo == $menu['url'];
        $target = isset($menu['target']) ? $menu['target'] : '_self';
        ?>

        <li <?php echo $active ? 'class="active"' : ""; ?>>
            <a target="<?=$target?>" href="<?=$menu['url']?>">
                <i class="fa fa-dashboard"></i>
                <span class="link-title">&nbsp;<?=$menu['name']?></span> </a>
        </li>

    <?php }?>





</ul>
<!-- /#menu -->
