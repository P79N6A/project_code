
    <!-- #menu -->
    <ul id="menu" class="bg-dark dker">
              <li class="nav-header">菜单</li>

              <li class="nav-divider"></li>
              <?php

              $menus = [
//                  ['name' => '支付', 'url' => '/backend/pay',],
                  ['name' => '支付通道', 'url' => '/backend/channel',],
                  ['name' => '业务通道排序', 'url' => '/backend/business-chan',],
                  ['name' => '银行列表', 'url' => '/backend/bank',],
                //   ['name' => '支付订单', 'url' => '/backend/order',],
  
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
