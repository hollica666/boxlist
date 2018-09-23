<?php
// Check if SSL enabled
$protocol = isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] && $_SERVER["HTTPS"] != "off"    ? "https://" : "http://";

// Define APPURL
$site_url = $protocol
    . $_SERVER["SERVER_NAME"]
    . (dirname($_SERVER["SCRIPT_NAME"]) == DIRECTORY_SEPARATOR ? "" : "/")
    . trim(str_replace("\\", "/", dirname($_SERVER["SCRIPT_NAME"])), "/");

define("SITEURL", $site_url);
$config['site_url'] = dirname($site_url)."/";

if(isset($_SESSION['admin']['id'])){
    $query1 = "SELECT * FROM `".$config['db']['pre']."admins` where id = '".validate_input($_SESSION['admin']['id'])."'";
    $result1 = $mysqli->query($query1);
    $getcount = mysqli_num_rows($result1);
    $username = "";
    $adminname = "";
    $sesuserpic = "";
    if($getcount > 0){
        $row1 = mysqli_fetch_assoc($result1);
        $username = $row1['username'];
        $adminname = $row1['name'];
        $sesuserpic = $row1['image'];
    }

    if($sesuserpic == "")
        $sesuserpic = "default_user.png";
}

?>

<!DOCTYPE html>

<html class="app-ui">

<head>
    <!-- Meta -->
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />

    <!-- Document title -->
    <title><?php echo $config['site_title'] ?> - Admin Panel</title>

    <meta name="description" content="<?php echo $config['site_title'] ?> - Admin Dashboard" />
    <meta name="author" content="Bylancer" />
    <meta name="robots" content="noindex, nofollow" />

    <!-- Favicons -->
    <link rel="icon" type="image/png" sizes="16x16" href="../storage/logo/<?php echo $config['site_favicon']?>">


    <!-- Google fonts -->
    <link rel="stylesheet" href="//fonts.googleapis.com/css?family=Roboto:300,400,400italic,500,900%7CRoboto+Slab:300,400%7CRoboto+Mono:400" />

    <!-- Page JS Plugins CSS -->
    <link rel="stylesheet" href="assets/js/plugins/slick/slick.min.css" />
    <link rel="stylesheet" href="assets/js/plugins/slick/slick-theme.min.css" />
    <!-- css select2 -->
    <link rel="stylesheet" href="assets/js/plugins/select2/select2.min.css" />
    <link rel="stylesheet" href="assets/js/plugins/select2/select2-bootstrap.css" />
    <!-- Zeunix CSS stylesheets -->
    <link rel="stylesheet" id="css-font-awesome" href="assets/css/font-awesome.css" />
    <link rel="stylesheet" id="css-ionicons" href="assets/css/ionicons.css" />
    <link rel="stylesheet" id="css-bootstrap" href="assets/css/bootstrap.css" />
    <link rel="stylesheet" id="css-app" href="assets/css/app.css" />
    <link rel="stylesheet" id="css-app-custom" href="assets/css/app-custom.css" />
    <link rel="stylesheet" id="css-app-animation" href="assets/css/animation.css" />
    <!-- End Stylesheets -->
    <link rel="stylesheet" href="assets/css/category.css" />

    <link rel="stylesheet" href="assets/js/plugins/asscrollable/asScrollable.min.css">
    <link rel="stylesheet" href="assets/js/plugins/slidepanel/slidePanel.min.css">
    <link rel="stylesheet" href="assets/js/plugins/datatables/jquery.dataTables.min.css" />


    <!--alerts CSS -->
    <link href="assets/js/plugins/sweetalert/sweetalert.css" rel="stylesheet" type="text/css">
    <link href="assets/js/plugins/alertify/alertify.min.css" rel="stylesheet" type="text/css">

    <?php
    if(isset($config['quickad_secret_file']) && $config['quickad_secret_file'] != ""){
        ?>
        <script>
            var ajaxurl = '<?php echo SITEURL."/".$config['quickad_secret_file'].'.php'; ?>';
        </script>
    <?php
    }
    ?>
    <script>
        var sidepanel_ajaxurl = '<?php echo SITEURL ?>/'+'ajax_sidepanel.php';
    </script>
</head>

<body class="app-ui layout-has-drawer layout-has-fixed-header">
<div class="app-layout-canvas">
    <div class="app-layout-container">

        <!-- Drawer -->
        <aside class="app-layout-drawer">

            <!-- Drawer scroll area -->
            <div class="app-layout-drawer-scroll">
                <!-- Drawer logo -->
                <div id="logo" class="drawer-header">
                    <a href="index.php">
                        <img class="img-responsive" src="../storage/logo/<?php echo $config['site_admin_logo']?>" title="admin" alt="admin" /></a>
                </div>

                <!-- Drawer navigation -->
                <nav class="drawer-main">
                    <ul class="nav nav-drawer">

                        <li class="nav-item nav-drawer-header">Apps</li>

                        <li class="nav-item">
                            <a href="index.php"><i class="ion-ios-speedometer-outline"></i> Dashboard</a>
                        </li>

                        <li class="nav-item nav-drawer-header">Management</li>

                        <li class="nav-item nav-item-has-subnav">
                            <a href="#"><i class="ion-image"></i> Ads</a>
                            <ul class="nav nav-subnav">
                                <li><a href="post_active.php">Active Ads</a></li>
                                <li><a href="post_pending.php">Pending Ads</a></li>
                                <li><a href="post_hidden.php">Hidden by user</a></li>
                                <li><a href="post_resubmit.php">Resubmited Ads</a></li>
                                <li><a href="posts.php">All Ads List</a></li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a href="category.php"><i class="ion-ios-list-outline"></i> Category</a>
                        </li>
                        <li class="nav-item">
                            <a href="custom_field.php"><i class="ion-android-options"></i> Custom Fields</a>
                        </li>
                        <li class="nav-item">
                            <a href="review.php"><i class="ion-android-star-half"></i> Review</a>
                        </li>
                        <li class="nav-item">
                            <a href="chating.php"><i class="fa fa-weixin"></i> Chat Messages</a>
                        </li>
                        <li class="nav-item">
                            <a href="transactions.php"><i class="ion-arrow-graph-up-right"></i> Transactions</a>
                        </li>
                        <li class="nav-item">
                            <a href="advertising.php"><i class="ion-ios-monitor-outline"></i> Advertising</a>
                        </li>
                        <li class="nav-item">
                            <a href="email-template.php"><i class="ion-ios-email"></i> Email Template</a>
                        </li>
                        <li class="nav-item nav-drawer-header">International</li>
                        <li class="nav-item">
                            <a href="languages.php"><i class="fa fa-language"></i> Languages</a>
                        </li>
                        <li class="nav-item">
                            <a href="currency.php"><i class="fa fa-money"></i> Currencies</a>
                        </li>
                        <li class="nav-item">
                            <a href="loc_countries.php"><i class="ion-ios-location-outline"></i> Countries</a>
                        </li>
                        <li class="nav-item">
                            <a href="timezones.php"><i class="ion-clock"></i> Time Zones</a>
                        </li>

                        <li class="nav-item nav-drawer-header">Content</li>
                        <li class="nav-item">
                            <a href="pages.php"><i class="ion-ios-browsers-outline"></i> Pages</a>
                        </li>
                        <li class="nav-item">
                            <a href="faq_entries.php"><i class="ion-clipboard"></i> FAQ</a>
                        </li>

                        <li class="nav-item nav-drawer-header">Settings</li>


                        <li class="nav-item nav-item-has-subnav">
                            <a href="#"><i class="ion-android-settings"></i> Setting</a>
                            <ul class="nav nav-subnav">
                                <li><a href="setting.php">Site Setting</a></li>
                                <li><a href="payment_methods.php">Payment Methods</a></li>
                                <li><a href="chat_setting.php">Chat Setting</a></li>
                                <li><a href="xml_manage.php">XML Manage</a></li>
                                <li><a href="themes.php">Change Theme</a></li>
                            </ul>
                        </li>
                        <li class="nav-item nav-drawer-header">Account</li>
                        <li class="nav-item">
                            <a href="users.php"><i class="ion-ios-people"></i> Users</a>
                        </li>
                        <li class="nav-item">
                            <a href="admins.php"><i class="ion-android-contact"></i> Admin</a>
                        </li>
                        <li class="nav-item">
                            <a href="update.php"><i class="ion-ios-list-outline"></i>Update</a>
                        </li>
                        <li class="nav-item">
                            <a href="logout.php"><i class="ion-ios-people-outline"></i> Logout</a>
                        </li>
                    </ul>
                </nav>
                <!-- End drawer navigation -->

                <div class="drawer-footer">
                    <p class="copyright">Bylancer &copy;</p>
                    <p class="copyright">Version : <?php echo $config['version']; ?></p>
                </div>
            </div>
            <!-- End drawer scroll area -->
        </aside>
        <!-- End drawer -->

        <!-- Header -->
        <header class="app-layout-header">
            <nav class="navbar navbar-default">
                <div class="container-fluid">
                    <div class="navbar-header">
                        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#header-navbar-collapse" aria-expanded="false">
                            <span class="sr-only">Toggle navigation</span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </button>
                        <button class="pull-left hidden-lg hidden-md navbar-toggle" type="button" data-toggle="layout" data-action="sidebar_toggle">
                            <span class="sr-only">Toggle drawer</span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </button>
                        <span class="navbar-page-title">Admin Panel</span>
                    </div>
                    <div class="collapse navbar-collapse" id="header-navbar-collapse">
                        <ul id="main-menu" class="nav navbar-nav navbar-left">
                            <li class="dropdown">
                                <a href="#" data-toggle="dropdown">Pages <span class="caret"></span></a>
                                <ul class="dropdown-menu">
                                    <li><a href="https://bylancer.com/products/demo/classified/Documentation/index.html" target="_blank">Documentation</a></li>
                                    <li><a href="https://codecanyon.net/item/facebook-style-php-ajax-chat-zechat/16491266" target="_blank">Purchase Zechat license</a></li>
                                    <li><a href="https://codecanyon.net/item/wchat-fully-responsive-phpajax-chat/18047319" target="_blank">Purchase Wchat license</a></li>
                                </ul>
                            </li>
                        </ul>
                        <!-- .navbar-left -->

                        <ul class="nav navbar-nav navbar-right navbar-toolbar hidden-sm hidden-xs">


                            <li class="dropdown dropdown-profile">
                                <a href="#" data-toggle="dropdown">
                                    <span class="m-r-sm"><?php echo $adminname;?> <span class="caret"></span></span>
                                    <img class="img-avatar img-avatar-48" src="../storage/profile/<?php echo $sesuserpic;?>" alt="<?php echo $adminname;?>" />
                                </a>
                                <ul class="dropdown-menu dropdown-menu-right">
                                    <li><a href="logout.php">Logout</a></li>
                                </ul>
                            </li>
                        </ul>
                        <!-- .navbar-right -->
                    </div>
                </div>
                <!-- .container-fluid -->
            </nav>
            <!-- .navbar-default -->
        </header>
        <!-- End header -->

        <?php
        if(!isset($config['purchase_key']) && $config['purchase_key'] == ""){
            ?>
            <div class="app-layout-content">
                    <div class="alert alert-warning" style="margin: 10px 10px 0 10px">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <strong>Important!</strong> Please verify purchase code to use admin feature.
                        <a class="text-info" style="cursor: pointer" href="setting.php#quickad_purchase_code"><strong>Click here</strong></a>
                    </div>
                </div>
        <?php
        }
        ?>