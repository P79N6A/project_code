<!doctype html>
<html>
    <?php
// 路径
    $pathInfo   = '/' . \Yii::$app->request->pathInfo;
    $controller = $this->context->id;
    $action     = $this->context->action->id;

    $vvars       = $this->context->vvars;
    $current_nav = $vvars['nav'];
    $left_menu   = $vvars['nav'];
    $right_menu  = null;
    ?>
    <head>
        <meta charset="UTF-8">
        <!--IE Compatibility modes-->
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <!--Mobile first-->
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
        <title>清结算-<?= $this->title ?></title>

        <meta name="description" content="Free Admin Template Based On Twitter Bootstrap 3.x">
        <meta name="author" content="">

        <meta name="msapplication-TileColor" content="#5bc0de" />
        <meta name="msapplication-TileImage" content="/static/img/metis-tile.png" />

        <!-- Bootstrap -->
        <link rel="stylesheet" href="/static/lib/bootstrap/css/bootstrap.css">

        <!-- Font Awesome -->
        <link rel="stylesheet" href="/static/lib/font-awesome/css/font-awesome.css">

        <!-- Metis core stylesheet -->
        <link rel="stylesheet" href="/static/css/main.css">

        <!-- metisMenu stylesheet -->
        <link rel="stylesheet" href="/static/lib/metismenu/metisMenu.css">

        <!-- animate.css stylesheet -->
        <link rel="stylesheet" href="/static/lib/animate.css/animate.css">


        <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
            <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->

        <!--For Development Only. Not required -->
        <script>
            less = {
                env: "development",
                relativeUrls: false,
                rootpath: "//static/"
            };
        </script>
        <link rel="stylesheet" href="/static/css/style-switcher.css">
        <link rel="stylesheet/less" type="text/css" href="/static/less/theme.less">

        <script src="/static/js/less.js"></script>

        <!--jQuery -->
        <script src="/static/lib/jquery/jquery.js"></script>
        <script src="/static/js/jquery-form.js" type="text/javascript" charset="utf-8"></script>
    </head>

    <body class="bg-dark dk">
        <div class="bg-dark dk" id="wrap">
            <div id="top">
                <!-- .navbar -->
                <nav class="navbar navbar-inverse <?= isset($fix) ? $fix : 'navbar-static-top'; ?>">
                    <div class="container-fluid">

                        <!-- Brand and toggle get grouped for better mobile display -->
                        <header class="navbar-header">
                            <img class="logo-default" src="/static/img/paylogo.png" alt="logo" style="margin: 10px;">
                        </header>

                        <div class="topnav">

                            <div class="btn-group">
                                <a data-placement="bottom" data-original-title="Fullscreen" data-toggle="tooltip"
                                   class="btn btn-default btn-sm" id="toggleFullScreen">
                                    <i class="glyphicon glyphicon-fullscreen"></i>
                                </a>
                            </div>

                            <!--
                            <div class="btn-group">
                                <a href="/settlement/manager/update-password" data-placement="bottom" data-original-title="update password" data-toggle="tooltip" class="btn btn-default btn-sm" id="toggleFullScreen">
                                    <i class="glyphicon glyphicon-wrench"></i>
                                </a>
                            </div>
                            <div class="btn-group">
                                <a href="/settlement/manager/reset-password"  data-placement="bottom" data-original-title="reset password" data-toggle="tooltip" class="btn btn-default btn-sm" id="toggleFullScreen">
                                    <i class="glyphicon glyphicon-wrench"></i>
                                </a>
                            </div>
                            -->
                            <div class="btn-group">
                                <a href="/settlement/default/logout" data-toggle="tooltip" data-original-title="Logout" data-placement="bottom" class="btn btn-metis-1 btn-sm">
                                    <i class="fa fa-power-off"></i>
                                </a>
                            </div>
                            <div class="btn-group">
                                <a data-placement="bottom" data-original-title="show/hide " data-toggle="tooltip"
                                   class="btn btn-primary btn-sm toggle-left" id="menu-toggle">
                                    <i class="fa fa-bars"></i>
                                </a>
                                <a data-placement="bottom" data-original-title="Show / Hide Right" data-toggle="tooltip"
                                   class="btn btn-default btn-sm toggle-right">
                                    <span class="glyphicon glyphicon-comment"></span>
                                </a>
                            </div>
                        </div>

                        <div class="collapse navbar-collapse navbar-ex1-collapse">

                            <!-- .nav -->
                            <ul class="nav navbar-nav">
                                <?php
                                $navs        = [
                                    ['name' => '主页', 'url' => '/settlement/channelcount/list', 'nav' => 'pay0'],
                                    //['name' => '一亿元', 'url' => '/backend/pay?aid=1', 'nav' => 'pay'],
                                    //['name' => '花生米富', 'url' => '/backend/pay?aid=4', 'nav' => 'pay4'],
                                    //['name' => '7-14项目', 'url' => '/backend/pay?aid=8', 'nav' => 'pay8'],
                                ];

                                foreach ($navs as $nav) {
                                    $active = $current_nav == $nav['nav'];
                                    ?>
                                    <li <?php echo $active ? 'class="active"' : ""; ?>>
                                        <a target="<?= isset($nav['target']) ? $nav['target'] : '_self' ?>" href="<?= $nav['url'] ?>"><?= $nav['name'] ?></a>
                                    </li>
                                <?php } ?>

                            </ul>
                            <!-- /.nav -->
                        </div>
                    </div>
                    <!-- /.container-fluid -->
                </nav>
                <!-- /.navbar -->
                <!-- <header class="head">
                    <div class="main-bar">
                        <h3>
                            <i class="fa fa-home"></i>&nbsp;
                            <?= $this->title; ?>
                        </h3>
                    </div>
                    <!-- /.main-bar -->
                <!-- </header> -->
                <!-- /.head -->
            </div>
            <!-- /#top -->

            <?php if ($left_menu): ?>
                <div id="left">
                    <?php include __dir__ . '/menus/' . $left_menu . '.php'; ?>
                </div>
                <!-- /#left -->
            <?php endif; ?>


            <div id="content">
                <div class="outer">
                    <div class="inner bg-light lter">
                        <div id="showError" style="display:none" class="bs-example bs-example-standalone" data-example-id="dismissible-alert-js">
                            <div id="errorClass" class="alert alert-danger alert-dismissible fade in" role="alert">
                                <button type="button" class="close" data-dismiss="" aria-label="Close"><span id="closeErrorMsg" aria-hidden="true">×</span></button>
                                <strong style="display: block;text-align: center" id="errotmsg"></strong>
                            </div>
                        </div>
                        <?= $content ?>
                    </div>
                    <!-- /.inner -->
                </div>
                <!-- /.outer -->
            </div>
            <!-- /#content -->

            <?php if ($right_menu): ?>
                <div id="right" class="bg-light lter">
                    <?php include __dir__ . '/menus/' . $right_menu . '.php'; ?>
                </div>
                <!-- /#right -->
            <?php endif; ?>


        </div>
        <!-- /#wrap -->
        <footer class="Footer bg-dark dker">
            <p>&copy;<?= date('Y') ?>  先花信息技术（北京）有限公司 京ICP备14028045号</p>
        </footer>
        <!-- /#footer -->
        <!-- #helpModal -->
        <div id="helpModal" class="modal fade">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Modal title</h4>
                    </div>
                    <div class="modal-body">
                        <p>
                            Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore
                            et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut
                            aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
                            cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in
                            culpa qui officia deserunt mollit anim id est laborum.
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>
        <!-- /.modal -->
        <!-- /#helpModal -->


        <!--Bootstrap -->
        <script src="/static/lib/bootstrap/js/bootstrap.js"></script>
        <!-- MetisMenu -->
        <script src="/static/lib/metismenu/metisMenu.js"></script>
        <!-- Screenfull -->
        <script src="/static/lib/screenfull/screenfull.js"></script>


        <!-- Metis core scripts -->
        <script src="/static/js/core.js"></script>
        <!-- Metis demo scripts -->
        <!-- <script src="/static/js/app.js"></script> -->

        <script src="/static/js/style-switcher.js"></script>

    </body>

</html>