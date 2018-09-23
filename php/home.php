<?php
require_once('includes/config.php');
require_once('includes/classes/class.template_engine.php');
require_once('includes/classes/class.country.php');
require_once('includes/functions/func.global.php');
require_once('includes/functions/func.users.php');
require_once('includes/functions/func.sqlquery.php');
if(isset($_GET['lang'])) {
    if ($_GET['lang'] != ""){
        change_user_lang($_GET['lang']);
    }
}
require_once('includes/lang/lang_'.$config['lang'].'.php');
require_once('includes/seo-url.php');
sec_session_start();
$mysqli = db_connect($config);


if(isset($match['params']['country'])) {
    if ($match['params']['country'] != ""){
        change_user_country($config,$match['params']['country']);
    }
}
$sortname = check_user_country($config);

if($latlong = get_lat_long_of_country($config,$sortname)){
    $mapLat     =  $latlong['latitude'];
    $mapLong    =  $latlong['longitude'];
}else{
    $mapLat     =  get_option($config,"home_map_latitude");
    $mapLong    =  get_option($config,"home_map_longitude");
}

$mapLat     =  get_option($config,"home_map_latitude");
$mapLong    =  get_option($config,"home_map_longitude");
//Loop for Premium Ads and (featured = 1 or urgent = 1 or highlight = 1)

$item = get_items($config,"","active",true,1,10,"id",true);
$item2 = get_items($config,"","active",false,1,10,"id",true);

$category = get_maincategory($config,$mysqli);
$cat_dropdown = get_categories_dropdown($config,$lang);

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

    $cat[$info['cat_id']]['catlink'] = $config['site_url'].'category/'.$info['slug'];

    $totalAdsMaincat = get_items_count($config,false,"active",false,null,$info['cat_id'],true);
    $cat[$info['cat_id']]['main_ads_count'] = $totalAdsMaincat;
    $count = 1;
    $query1 = "SELECT * FROM ".$config['db']['pre']."catagory_sub WHERE `main_cat_id` = '".$info['cat_id']."' LIMIT 4";
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
            $cat[$info['cat_id']]['sub_title'] = '<li><a href="'.$subcatlink.'" title="'.$info1['sub_cat_name'].'">'.$info1['sub_cat_name'].'</a></li>';
        else
            $cat[$info['cat_id']]['sub_title'] .= '<li><a href="'.$subcatlink.'" title="'.$info1['sub_cat_name'].'">'.$info1['sub_cat_name'].'</a></li>';

        if($count == 4)
            $cat[$info['cat_id']]['sub_title'] .= '<li><a href="'.$link['SITEMAP'].'" style="color: #6f6f6f;text-decoration: underline;">'.$lang['VIEW-MORE'].'...</a>('.$totalAdsMaincat.')</li>';
        $count++;
    }
}
// Output to template

if($config['home_page'] == "home-map"){
    $page = new HtmlTemplate ('templates/'.$config['tpl_name'].'/home-map.tpl');
}
else{
    $page = new HtmlTemplate ('templates/'.$config['tpl_name'].'/index.tpl');
}


$page->SetParameter ('OVERALL_HEADER', create_header($lang['HOME']));
$page->SetLoop ('ITEM', $item);
$page->SetLoop ('ITEM2', $item2);
$page->SetLoop ('CATEGORY',$category);
$page->SetParameter ('CAT_DROPDOWN',$cat_dropdown);
$page->SetLoop ('CAT',$cat);
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
$page->SetParameter('LATITUDE', $mapLat);
$page->SetParameter('LONGITUDE', $mapLong);
$page->SetParameter('MAP_COLOR', $config['map_color']);
$page->SetParameter('ZOOM', $config['home_map_zoom']);
$page->SetParameter('DEFAULT_COUNTRY', get_countryName_by_sortname($config,$sortname));
$page->SetParameter('SPECIFIC_COUNTRY', $sortname);
$page->SetParameter ('OVERALL_FOOTER', create_footer());
$page->CreatePageEcho();
?>