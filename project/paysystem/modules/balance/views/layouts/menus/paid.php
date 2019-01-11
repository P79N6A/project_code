
    <!-- #menu -->
    <ul id="menu" class="bg-dark dker">

        <li class="nav-header">体外对账管理</li>


        <li class="nav-divider"></li>

        <?php

        $menus = [
            ['name' =>'代付统计', 'url'=>'/balance/paidcount/list'],
           ['name' =>'通道账单', 'url'=>'/balance/uploadchannel/list'],
           // ['name' =>'差错账', 'url'=>'/balance/mistake/list'],
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
