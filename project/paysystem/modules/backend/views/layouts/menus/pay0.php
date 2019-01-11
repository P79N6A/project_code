
    <!-- #menu -->
    <ul id="menu" class="bg-dark dker">
              <li class="nav-header">支付系统</li>

              <li class="nav-divider"></li>
              <?php

              $menus = [
                  ['name' => '支付订单管理', 'url' => '/backend/order',],
                  ['name' => '项目管理', 'url' => '/backend/app',],
                  ['name' => '业务管理', 'url' => '/backend/business',],
                  ['name' => '通知管理', 'url' => '/backend/notify',],
                  ['name' => 'IP白名单（支付系统）', 'url' => '/backend/white-ip',],
                  ['name' => 'IP白名单（开放平台）', 'url' => '/backend/whiteip-open',],
                  ['name' => 'IP黑名单', 'url' => '/backend/black-ip',],
                  ['name' => '标准化错误', 'url' => '/backend/std-error',],
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
              <li class="nav-header">出款系统</li>

              <li class="nav-divider"></li>
              <?php

              $menus = [
                  ['name' => '畅捷出款', 'url' => '/backend/cjremit',],
                  ['name' => '融宝出款', 'url' => '/backend/rbremit',],
                  ['name' => '宝付出款', 'url' => '/backend/bfremit',],
                  ['name' => '玖富出款', 'url' => '/backend/jfremit',],
                  ['name' => '小诺出款', 'url' => '/backend/xnremit',],
                  ['name' => '微神马出款', 'url' => '/backend/wsmremit',],
                  ['name' => '畅捷通知', 'url' => '/backend/cjnotify',],
                  ['name' => '融宝通知', 'url' => '/backend/rbnotify',],
                  ['name' => '宝付通知', 'url' => '/backend/bfnotify',],
                  ['name' => '出款限额', 'url' => '/backend/limit',],
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
            <li class="nav-header">运营商</li>

              <li class="nav-divider"></li>
              <?php

              $menus = [
                  ['name' => '聚信立请求', 'url' => '/backend/jxlrequest',],
                  ['name' => '蚁盾请求', 'url' => '/backend/yidunrequest',],
                  ['name' => '聚信立结果', 'url' => '/backend/jxlstat',],
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

              <li class="nav-header">账户管理</li>

              <li class="nav-divider"></li>
              <?php
              $menus = [
                    ['name' => '账户管理', 'url' => '/backend/manager',],
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
