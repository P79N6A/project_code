
<!-- #menu -->
<ul id="menu" class="bg-dark dker">

    <li class="nav-header">正常还款统计管理</li>

    <li class="nav-divider"></li>
    <?php

    $menus = [
        //['name' =>'财务核算管理', 'url'=>'/balance/purchase/list'],
        ['name' =>'账目日统计', 'url'=>'/balance/account/list'],
        ['name' =>'账目月统计', 'url'=>'/balance/account/mouth'],
        //['name' =>'退卡订单', 'url'=>'/balance/retreat/list'],

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


    <li class="nav-header">线下还款统计管理</li>

    <li class="nav-divider"></li>
    <?php

    $menus = [
        //['name' =>'财务核算管理', 'url'=>'/balance/purchase/list'],
        ['name' =>'账目日统计-线下', 'url'=>'/balance/under/list'],
        //['name' =>'退卡订单', 'url'=>'/balance/retreat/list'],

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
