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
    $_GET['page'] = 1;

$limit = 4;
$page = $_GET['page'];

if(checkloggedin($config)) {
    $item = array();
    $ses_userdata = get_user_data($config,$_SESSION['user']['username']);
    $author_image = $ses_userdata['image'];

    $pagelimit = "";
    if($page != null && $limit != null){
        $pagelimit = "LIMIT  ".($page-1)*$limit.",".$limit;
    }

    $total_item = mysqli_num_rows(mysqli_query($mysqli, "SELECT 1 FROM ".$config['db']['pre']."favads where `user_id` = '".$_SESSION['user']['id']."'"));

    $queryFav = "SELECT product_id FROM ".$config['db']['pre']."favads WHERE `user_id` = '".$_SESSION['user']['id']."' $pagelimit";
    $query_result = mysqli_query($mysqli,$queryFav);
    if (mysqli_num_rows($query_result) > 0) {
        while ($fav = mysqli_fetch_assoc($query_result)) {

            $query = "SELECT * FROM `".$config['db']['pre']."product` where id = '".$fav['product_id']."'  ORDER BY id DESC LIMIT $limit";
            $result = $mysqli->query($query);
            if (mysqli_num_rows($result) > 0) {
                while ($info = mysqli_fetch_assoc($result)) {
                    $item[$info['id']]['id'] = $info['id'];
                    $item[$info['id']]['product_name'] = $info['product_name'];
                    $item[$info['id']]['featured'] = $info['featured'];
                    $item[$info['id']]['urgent'] = $info['urgent'];
                    $item[$info['id']]['highlight'] = $info['highlight'];
                    $item[$info['id']]['address'] = strlimiter($info['location'],20);
                    $item[$info['id']]['location'] = get_cityName_by_id($config,$info['city']);
                    $item[$info['id']]['city'] = get_cityName_by_id($config,$info['city']);
                    $item[$info['id']]['state'] = get_stateName_by_id($config,$info['state']);
                    $item[$info['id']]['country'] = get_countryName_by_id($config,$info['country']);
                    $item[$info['id']]['status'] = $info['status'];
                    $item[$info['id']]['created_at'] = timeago($info['created_at']);

                    $item[$info['id']]['cat_id'] = $info['category'];
                    $item[$info['id']]['sub_cat_id'] = $info['sub_category'];

                    $get_main = get_maincat_by_id($config,$info['category']);
                    $get_sub = get_subcat_by_id($config,$info['sub_category']);
                    $item[$info['id']]['category'] = $get_main['cat_name'];
                    $item[$info['id']]['sub_category'] = $get_sub['sub_cat_name'];

                    $item[$info['id']]['favorite'] = check_product_favorite($config,$info['id']);

                    $tag = explode(',', $info['tag']);
                    $tag2 = array();
                    foreach ($tag as $val)
                    {
                        //REMOVE SPACE FROM $VALUE ----
                        $val = trim($val);
                        $tag2[] = '<li><a href="listing?keywords='.$val.'">'.$val.'</a> </li>';
                    }
                    $item[$info['id']]['tag'] = implode('  ', $tag2);

                    $picture     =   explode(',' ,$info['screen_shot']);
                    $picture     =   $picture[0];
                    $item[$info['id']]['picture'] = $picture;

                    if($info['price'] != '0'){
                        $currency_info = set_user_currency($config,$info['country']);
                        $config['currency_code'] = $currency_info['code'];
                        $config['currency_sign'] = $currency_info['html_entity'];
                        $config['currency_pos'] = $currency_info['in_left'];

                        if($currency_info['in_left'] == 1){
                            $price = $currency_info['html_entity']." ".$info['price'];
                        }else{
                            $price = $info['price']." ".$currency_info['html_entity'];
                        }
                    }else{
                        $price = 0;
                    }


                    $item[$info['id']]['price'] = $price;

                    $userinfo = get_user_data($config,null,$info['user_id']);

                    $item[$info['id']]['username'] = $userinfo['username'];
                    $author_url = create_slug($userinfo['username']);

                    $item[$info['id']]['author_link'] = $config['site_url'].'profile/'.$author_url;

                    $pro_url = create_slug($info['product_name']);
                    $item[$info['id']]['link'] = $config['site_url'].'ad/' . $info['id'] . '/'.$pro_url;

                    $cat_slug = create_slug($get_main['cat_name']);
                    $item[$info['id']]['catlink'] = $config['site_url'].'category/'.$info['category'].'/'.$cat_slug;

                    $subcat_slug = create_slug($get_sub['sub_cat_name']);
                    $item[$info['id']]['subcatlink'] = $config['site_url'].'subcategory/'.$info['sub_category'].'/'.$subcat_slug;

                    $city = create_slug($item[$info['id']]['city']);
                    $item[$info['id']]['citylink'] = $config['site_url'].'city/'.$info['city'].'/'.$city;
                }
            }


        }
    }


    $pagging = pagenav($total_item,$page,$limit,$config['site_url'].'favourite-ads');
    // Output to template
    $page = new HtmlTemplate ('templates/' . $config['tpl_name'] . '/ad-favourite.tpl');
    $page->SetParameter ('OVERALL_HEADER', create_header($lang['FAVOURITE-ADS']));
    $page->SetParameter ('RESUBMITADS', resubmited_ads_count($config,$_SESSION['user']['id']));
    $page->SetParameter ('HIDDENADS', hidden_ads_count($config,$_SESSION['user']['id']));
    $page->SetParameter ('PENDINGADS', pending_ads_count($config,$_SESSION['user']['id']));
    $page->SetParameter ('FAVORITEADS', favorite_ads_count($config,$_SESSION['user']['id']));
    $page->SetParameter ('MYADS', myads_count($config,$_SESSION['user']['id']));
    $page->SetLoop ('ITEM', $item);
    $page->SetLoop ('PAGES', $pagging);
    $page->SetParameter ('TOTALITEM', $total_item);
    $page->SetParameter ('AUTHORUNAME', ucfirst($ses_userdata['username']));
    $page->SetParameter ('AUTHORNAME', ucfirst($ses_userdata['name']));
    $page->SetParameter ('AUTHORIMG', $author_image);
    $page->SetLoop ('HTMLPAGE', get_html_pages($config));
    $page->SetParameter('COPYRIGHT_TEXT', get_option($config,"copyright_text"));
    $page->SetParameter ('OVERALL_FOOTER', create_footer());
    $page->CreatePageEcho();
}
else{
    error($lang['PAGENOTEXIST'], __LINE__, __FILE__, 1);
    exit();
}
?>