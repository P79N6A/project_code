
    <!-- #menu -->
    <ul id="menu" class="bg-dark dker">
              <li class="nav-header">出款账单管理</li>

              <li class="nav-divider"></li>
              <?php

              $menus = [
                  ['name' =>'出款通道统计', 'url'=>'/settlement/channelcount/list'],
                  ['name' =>'上传账单列表', 'url'=>'/settlement/upbill'],
                  ['name' =>'上游通道出款列表', 'url'=>'/settlement/upperlist/list'],
                  ['name' =>'对账成功列表', 'url'=>'/settlement/reconciliation/list'],
                  ['name' =>'差错账列表', 'url'=>'/settlement/errorlist/list'],
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
