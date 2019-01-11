
    <!-- #menu -->
    <ul id="menu" class="bg-dark dker">
              <li class="nav-header">保险管理</li>

              <li class="nav-divider"></li>
        
              <?php

              $menus = [
                //   ['name' => '对账差异列表', 'url' => '/policyment/bill/diffindex',],
                //   ['name' => '对账完成列表', 'url' => '/policyment/bill/compindex',],
                //   ['name' => '对账明细列表', 'url' => '/policyment/bill/index',],
                //   ['name' => '待支付列表', 'url' => '/policyment/policy',],
                  ['name' => '保单列表', 'url' => '/policyment/policy/index1',],
                  ['name' => '对账列表', 'url' => '/policyment/policybill/index',],
                  ['name' => '对账费用', 'url' => '/policyment/policybill/list',],
                  
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
