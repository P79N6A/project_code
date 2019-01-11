
    <!-- #menu -->
    <ul id="menu" class="bg-dark dker">
    <li class="nav-header">保险管理</li>

    <li class="nav-divider"></li>

    <?php

    $menus = [
        ['name' => '对账列表', 'url' => '/policyment/bill',],
        ['name' => '待支付列表', 'url' => '/policyment/bill',],
        ['name' => '完成保单列表', 'url' => '/policyment/bill',],
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
