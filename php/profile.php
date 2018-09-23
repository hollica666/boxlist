<?php
require_once('includes/config.php');
require_once('includes/classes/class.template_engine.php');
require_once('includes/classes/class.country.php');
require_once('includes/functions/func.global.php');
require_once('includes/functions/func.users.php');
require_once('includes/functions/func.sqlquery.php');
require_once('includes/lang/lang_'.$config['lang'].'.php');
require_once('includes/seo-url.php');

$mysqli = db_connect($config);
sec_session_start();
if(!isset($_GET['page']))
    $page = 1;
else
    $page = $_GET['page'];


if(!isset($_GET['sort']))
    $sort = "id";
elseif($_GET['sort'] == "title")
    $sort = "product_name";
elseif($_GET['sort'] == "price")
    $sort = "price";
elseif($_GET['sort'] == "date")
    $sort = "created_at";
else
    $sort = "id";

$limit = isset($_GET['limit']) ? $_GET['limit'] : 6;
$sorting = isset($_GET['sort']) ? $_GET['sort'] : "Newest";

if(isset($_GET['username'])){
    $get_userdata = get_user_data($config,$_GET['username']);
    if(is_array($get_userdata)){

        $user_id = $get_userdata['id'];

        update_profileview($user_id,$config);

        $user_view = $get_userdata['view'];
        $user_name = $get_userdata['name'];
        $user_email = $get_userdata['email'];
        $user_tagline = $get_userdata['tagline'];
        $user_about = $get_userdata['description'];
        $user_sex = $get_userdata['sex'];
        $user_city = $get_userdata['city'];
        $user_phone = $get_userdata['phone'];
        $user_address = $get_userdata['address'];
        $user_image = $get_userdata['image'];
        $created = date('d M Y', strtotime($get_userdata['created_at']));
        $lastactive = date('d M Y', strtotime($get_userdata['lastactive']));

        $items = get_items($config,$user_id,"active",false,$page,$limit,$sort);
        $items2 = get_items($config,$user_id,"active",false,$page,$limit,$sort);
        $total_item = get_items_count($config,$user_id,"active");
        $total_Premium_item = get_items_count($config,$user_id,"active",true);
        $pagging = pagenav($total_item,$page,$limit,$link['PROFILE'].'/'.$_GET['username'],1,true);


        // Output to template
        $page = new HtmlTemplate ('templates/' . $config['tpl_name'] . '/profile.tpl');
        $page->SetParameter ('OVERALL_HEADER', create_header($_GET['username']." ".$lang['PROFILE']));
        $page->SetParameter ('PROFILEVISIT', $user_view);
        $page->SetParameter ('FULLNAME', $user_name);
        $page->SetParameter ('EMAIL', $user_email);
        $page->SetParameter ('CITY', $user_city);
        $page->SetParameter ('TAGLINE', $user_tagline);
        $page->SetParameter ('ABOUT', $user_about);
        $page->SetParameter ('USERIMAGE', $user_image);
        $page->SetParameter ('PHONE', $user_phone);
        $page->SetParameter ('ADDRESS', $user_address);
        $page->SetParameter ('CREATED', $created);
        $page->SetParameter ('LASTACTIVE', $lastactive);
        $page->SetParameter ('USERNAME', $_GET['username']);
        $page->SetParameter('FACEBOOK', $get_userdata['facebook']);
        $page->SetParameter('TWITTER', $get_userdata['twitter']);
        $page->SetParameter('GPLUS', $get_userdata['googleplus']);
        $page->SetParameter('LINKEDIN', $get_userdata['linkedin']);
        $page->SetParameter('INSTAGRAM', $get_userdata['instagram']);
        $page->SetParameter('YOUTUBE', $get_userdata['youtube']);
        $page->SetParameter ('USERADS', $total_item);
        $page->SetParameter ('USERPREMIUMADS', $total_Premium_item);
        $page->SetLoop ('ITEM', $items);
        $page->SetLoop ('ITEM2', $items2);
        $page->SetLoop ('PAGES', $pagging);
        $page->SetParameter ('LIMIT', $limit);
        $page->SetParameter ('SORT', $sorting);
        if(isset($_SESSION['user']['id']))
        {
            $page->SetParameter('USER_ID',$_SESSION['user']['id']);
        }
        else
        {
            $page->SetParameter('USER_ID','');
        }

        $advertise_left = get_advertise($config,"left_sidebar");
        $advertise_right = get_advertise($config,"right_sidebar");

        $page->SetParameter('LEFT_ADSCODE', $advertise_left['tpl']);
        $page->SetParameter('LEFT_ADSTATUS', $advertise_left['status']);
        $page->SetParameter('RIGHT_ADSCODE', $advertise_right['tpl']);
        $page->SetParameter('RIGHT_ADSTATUS', $advertise_right['status']);
        if($advertise_left['status'] == 1 && $advertise_right['status'] == 1){
            $category_column = "col-md-8";
        }else if($advertise_left['status'] == 0 && $advertise_right['status'] == 1){
            $category_column = "col-md-10";
        }else if($advertise_left['status'] == 1 && $advertise_right['status'] == 0){
            $category_column = "col-md-10";
        }else{
            $category_column = "col-md-12";
        }
        $page->SetParameter('CATEGORY_COLUMN', $category_column);

        $page->SetParameter ('OVERALL_FOOTER', create_footer());
        $page->CreatePageEcho();
        exit();
    }
    else{
        error($lang['PAGENOTEXIST'], __LINE__, __FILE__, 1);
        exit();
    }
}
else{
    error($lang['PAGENOTEXIST'], __LINE__, __FILE__, 1);
    exit();
}
?>