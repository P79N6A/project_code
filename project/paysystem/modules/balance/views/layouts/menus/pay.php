
    <!-- #menu -->
    <ul id="menu" class="bg-dark dker">

        <li class="nav-header">一亿元统计管理</li>

        <li class="nav-divider"></li>
        <?php

        $menus = [
            ['name' =>'出款统计', 'url'=>'/balance/remit/list'],
            ['name' =>'未到期统计', 'url'=>'/balance/beforecount/list'],
            ['name' =>'正常回款统计', 'url'=>'/balance/repay/list'],
            ['name' =>'逾期待收统计', 'url'=>'/balance/collectcount/list'],
            ['name' =>'逾期已收统计', 'url'=>'/balance/receivedcount/list'],
            ['name' =>'逾期待收统计（2017年）', 'url'=>'/balance/clastcount/list'],
            ['name' =>'逾期已收统计（2017年）', 'url'=>'/balance/rlastcount/list'],
            ['name' =>'保险统计', 'url'=>'/balance/policy/list'],
            ['name' =>'展期服务费统计', 'url'=>'/balance/renewal/list'],
            ['name' =>'一亿元前置手续费', 'url'=>'/balance/service/list'],
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

        <li class="nav-header">财务数据分析</li>

        <li class="nav-divider"></li>
        <?php

        $menus = [
            ['name' =>'汇总统计', 'url'=>'/balance/summary/list'],
        ];

       /* foreach ($menus as $menu) {
            $active = $pathInfo == $menu['url'];
            $target = isset($menu['target']) ? $menu['target'] : '_self';
            */?><!--

            <li <?php /*echo $active ? 'class="active"' : ""; */?>>
                <a target="<?/*=$target*/?>" href="<?/*=$menu['url']*/?>">
                    <i class="fa fa-dashboard"></i>
                    <span class="link-title">&nbsp;<?/*=$menu['name']*/?></span> </a>
            </li>

        --><?php /*}*/?>

       <!-- <li class="nav-header">体外支付系统对账</li>-->

      <!--  <li class="nav-divider"></li>-->

        <?php

       /* $menus = [
            ['name' =>'回款通道统计', 'url'=>'/balance/channelcount/list'],
            ['name' =>'通道账单', 'url'=>'/balance/passageway/list'],
            ['name' =>'差错账', 'url'=>'/balance/mistake/list'],
        ];*/

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
