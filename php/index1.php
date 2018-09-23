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
//Loop for Premium Ads and (featured = 1 or urgent = 1 or highlight = 1)

$item = get_items($config,"","active",true,1,10,"id",true);
$item2 = get_items($config,"","active",false,1,10,"id",true);

$category = get_maincategory($config,$mysqli);
$cat_dropdown = get_categories_dropdown($config,$lang);

$sortname = check_user_country($config);

$query = "SELECT * FROM ".$config['db']['pre']."catagory_main ORDER by cat_order ASC";
$query_result = mysqli_query($mysqli,$query);
while ($info = mysqli_fetch_array($query_result))
{
    if($config['lang_code'] != 'en'){
        $maincat = get_category_translation("main",$info['cat_id']);
        $info['cat_name'] = $maincat['title'];
        $info['slug'] = $maincat['slug'];
    }
    $cat[$info['cat_id']]['icon'] = $info['icon'];
    $cat[$info['cat_id']]['main_title'] = $info['cat_name'];
    $cat[$info['cat_id']]['main_id'] = $info['cat_id'];

    $cat_url = create_slug($info['cat_name']);
    $cat[$info['cat_id']]['catlink'] = $config['site_url'].'category/'.$info['cat_id'].'/'.$cat_url;

    $totalAdsMaincat = get_items_count($config,false,"active",false,null,$info['cat_id'],true);
    $cat[$info['cat_id']]['main_ads_count'] = $totalAdsMaincat;
    $count = 1;
    $query1 = "SELECT * FROM ".$config['db']['pre']."catagory_sub WHERE `main_cat_id` = '".$info['cat_id']."' ORDER by cat_order ASC LIMIT 4";
    $query_result1 = mysqli_query($mysqli,$query1);
    while ($info1 = mysqli_fetch_array($query_result1))
    {
        $totalads = get_items_count($config,false,"active",false,$info1['sub_cat_id'],null,true);

        if($config['lang_code'] != 'en'){
            $subcat = get_category_translation("sub",$info1['sub_cat_id']);
            $info1['sub_cat_name'] = $subcat['title'];
            $info1['slug'] = $subcat['slug'];
        }

        $subcatlink = $config['site_url'].'category/'.$info['slug'].'/'.$info1['slug'];

        if($count == 1)
            $cat[$info['cat_id']]['sub_title'] = '<li><a href="'.$subcatlink.'" title="'.$info1['sub_cat_name'].'">'.$info1['sub_cat_name'].'</a><figure class="count">'.$totalads.'</figure></li>';
        else
            $cat[$info['cat_id']]['sub_title'] .= '<li><a href="'.$subcatlink.'" title="'.$info1['sub_cat_name'].'">'.$info1['sub_cat_name'].'</a><figure class="count">'.$totalads.'</figure></li>';

        if($count == 4)
            $cat[$info['cat_id']]['sub_title'] .= '<li><a href="'.$link['SITEMAP'].'" style="color: #6f6f6f;text-decoration: underline;">'.$lang['VIEW-MORE'].'...</a></li>';

        $count++;
    }
}

// Output to template
$page = new HtmlTemplate ('templates/'.$config['tpl_name'].'/index.tpl');
$page->SetParameter ('OVERALL_HEADER', create_header($lang['HOME-IMAGE']));
$page->SetLoop ('ITEM', $item);
$page->SetLoop ('ITEM2', $item2);
$page->SetLoop ('CATEGORY',$category);
$page->SetParameter ('CAT_DROPDOWN',$cat_dropdown);
$page->SetLoop ('CAT',$cat);
if(checkloggedin($config)) {
    $page->SetParameter('USER_ID', $_SESSION['user']['id']);
}else{
    $page->SetParameter('USER_ID', "");
}
/*Advertisement Fetching*/
$advertise_top = get_advertise($config,"top");
$advertise_bottom = get_advertise($config,"bottom");
$advertise_left = get_advertise($config,"left_sidebar");
$advertise_right = get_advertise($config,"right_sidebar");

$page->SetParameter('TOP_ADSCODE', $advertise_top['tpl']);
$page->SetParameter('TOP_ADSTATUS', $advertise_top['status']);
$page->SetParameter('BOTTOM_ADSCODE', $advertise_bottom['tpl']);
$page->SetParameter('BOTTOM_ADSTATUS', $advertise_bottom['status']);
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
/*Advertisement Fetching*/
$page->SetParameter('BANNER_IMAGE', $config['home_banner']);
$page->SetParameter('SPECIFIC_COUNTRY', $sortname);
$page->SetParameter ('OVERALL_FOOTER', create_footer());
$page->CreatePageEcho();
?>