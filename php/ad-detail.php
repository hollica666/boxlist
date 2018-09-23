<?php
require_once('includes/config.php');
require_once('includes/classes/class.template_engine.php');
require_once('includes/classes/class.country.php');
require_once('includes/functions/func.global.php');
require_once('includes/functions/func.users.php');
require_once('includes/functions/func.sqlquery.php');
require_once('includes/lang/lang_'.$config['lang'].'.php');
require_once('includes/seo-url.php');
sec_session_start();

$mysqli = db_connect($config);

if(checkloggedin($config)) {
    update_lastactive($config);
}


if(!isset($_GET['id']))
{
    error($lang['PAGENOTEXIST'], __LINE__, __FILE__, 1);
    exit;
}

$total[0] = mysqli_num_rows(mysqli_query($mysqli, "SELECT 1 FROM ".$config['db']['pre']."product where id = '".$_GET['id']."' limit 1"));
$sql = "SELECT * FROM ".$config['db']['pre']."product where  id = '".$_GET['id']."' limit 1";
$result = mysqli_query($mysqli, $sql);

$item_custom = array();
$item_custom_textarea = array();
$item_checkbox = array();

if (mysqli_num_rows($result) > 0) {
    // output data of each row
    update_itemview($_GET['id'],$config);

    $info = mysqli_fetch_assoc($result);

    $item_id = $info['id'];
    $item_title = $info['product_name'];
    $item_description = $info['description'];
    $item_catid = $info['category'];
    $item_featured = $info['featured'];
    $item_urgent = $info['urgent'];
    $item_highlight = $info['highlight'];
    $item_negotiable = $info['negotiable'];
    $item_tag = $info['tag'];
    $item_location = $info['location'];
    $item_city = get_cityName_by_id($config,$info['city']);
    $item_state = get_stateName_by_id($config,$info['state']);
    $item_country = get_countryName_by_id($config,$info['country']);
    $item_status = $info['status'];
    $item_view = $info['view'];
    $item_created_at = timeAgo($info['created_at']);
    $item_updated_at = date('d M Y', $info['updated_at']);

    $get_main = get_maincat_by_id($config,$info['category']);
    $get_sub = get_subcat_by_id($config,$info['sub_category']);
    $item_category = $get_main['cat_name'];
    $item_sub_category = $get_sub['sub_cat_name'];

    $item_phone = $info['phone'];
    $item_hide_phone = $info['hide_phone'];

    if($item_phone != "" && $item_hide_phone == '0'){
        $item_hide_phone = "no";
    }else{
        $item_hide_phone = "yes";
    }

    $item_contact_phone = $info['contact_phone'];
    $item_contact_email = $info['contact_email'];
    $item_contact_chat = $info['contact_chat'];


    $item_catlink = $config['site_url'].'category/'.$get_main['slug'];

    $item_subcatlink = $config['site_url'].'category/'.$get_main['slug'].'/'.$get_sub['slug'];

    $price = price_format($info['price'],$info['country']);
    $item_price = $price;

    if($item_negotiable == 1)
        $item_negotiable = $lang['NEGOTIABLE_PRICE'];
    else
        $item_negotiable = "";

    $count = 0;

    $query = "SELECT * FROM `".$config['db']['pre']."custom_data` where product_id = '".$item_id."'";
    $query_result = mysqli_query($mysqli, $query);
    $item_custom_field = mysqli_num_rows($query_result);
    while($customdata = mysqli_fetch_array($query_result)){
        $field_id = $customdata['field_id'];
        $field_type = $customdata['field_type'];
        $field_data = $customdata['field_data'];

        $custom_fields_title = get_customField_title_by_id($config,$field_id);

        if($field_type == 'checkboxes'){
            $checkbox_value2 = array();

            $checkbox_value = explode(",",$field_data);

            foreach ($checkbox_value as $val)
            {
                $val = get_customOption_by_id(trim($val));
                $checkbox_value2[] = '<div class="col-md-4 col-sm-4"><div style="line-height: 30px;"><i class="fa fa-check"></i> '.$val.'</div></div>';
            }
            if($custom_fields_title != ""){
                $item_checkbox[$field_id]['title'] = $custom_fields_title;
                $item_checkbox[$field_id]['value'] = implode('  ', $checkbox_value2);
            }

        }
        if($field_type == 'textarea') {
            $item_custom_textarea[$field_id]['title'] = $custom_fields_title;
            $item_custom_textarea[$field_id]['value'] = stripslashes($field_data);
        }

        if($field_type == 'radio-buttons' or  $field_type == 'drop-down') {
            $custom_fields_data = get_customOption_by_id($field_data);
            $item_custom[$field_id]['title'] = $custom_fields_title;
            $item_custom[$field_id]['value'] = $custom_fields_data;
        }

        if($field_type == 'text-field') {
            $custom_fields_data = stripslashes($field_data);
            $item_custom[$field_id]['title'] = $custom_fields_title;
            $item_custom[$field_id]['value'] = $custom_fields_data;
        }



    }

    $latlong = $info['latlong'];
    $map = explode(',', $latlong);
    $lat = $map[0];
    $long = $map[1];

    $item_author_id = $info['user_id'];
    $info2 = get_user_data($config,null,$item_author_id);

    $item_author_name = ucfirst($info2['name']);
    $item_author_username = ucfirst($info2['username']);
    $item_author_email = $info2['email'];
    $item_author_image = $info2['image'];
    $item_author_country = $info2['country'];
    $item_author_joined = $info2['created_at'];
    $item_author_online = $info2['online'];

    if($info2['online'] == 1){
        $item_author_online = "Online";
    }else{
        $item_author_online = "Offline";
    }

    $author_url = create_slug($info2['username']);
    $item_author_link = $config['site_url'].'profile/'.$author_url;

    $pro_url = create_slug($info['product_name']);
    $item_link = $config['site_url'].'ad/' . $info['id'] . '/'.$pro_url;
    if($info['tag'] != ""){
        $tag = explode(',', $info['tag']);
        $tag2 = array();
        foreach ($tag as $val)
        {
            //REMOVE SPACE FROM $VALUE ----
            $tagTrim = preg_replace("/[\s_]/","-", trim($val));
            $tag2[] = '<li><a href="'.$config['site_url'].'keywords/'.$tagTrim.'">'.$val.'</a> </li>';
        }
        $item_tag = implode('  ', $tag2);
        $show_tag = 1;
    }else{
        $item_tag = "";
        $show_tag = 0;
    }
    $meta_image = '';
    if($info['screen_shot'] != ""){
        $show_image_slider = 1;
        $screnshots = explode(',',$info['screen_shot']);
        $main_Screen = $screnshots[0];
        $meta_image = $config['site_url'].'storage/products/'.$main_Screen;
        $screen_sm = explode(',',$info['screen_shot']);
        foreach ($screen_sm as $value)
        {
            //REMOVE SPACE FROM $VALUE ----
            $value = trim($value);
            $screen_sm2[] = '<div class="item-sm-thumb"><div class="imgAsBg"><img src="'.$config['site_url'].'storage/products/thumb/'.$value.'" alt="'.$item_title.'"></div></div>';
        }
        $screen_sm = implode('  ', $screen_sm2);

        $screen_big = explode(',',$info['screen_shot']);
        foreach ($screen_big as $value)
        {
            //REMOVE SPACE FROM $VALUE ----
            $value = trim($value);
            $screen_big2[] = '<div class="item-lg-thumb imgAsBg"><img src="'.$config['site_url'].'storage/products/'.$value.'" alt="'.$item_title.'"></div>';
        }
        $screen_big = implode('  ', $screen_big2);

        //For Classic Theme
        $i =0;
        $screen_classicb = explode(',',$info['screen_shot']);
        foreach ($screen_classicb as $value)
        {
            //REMOVE SPACE FROM $VALUE ----
            $value = trim($value);
            $active = ($i == 0) ? "active" : "";
            $screen_classicb2[] = '<div class="item '.$active.'"><div class="carousel-image"><img src="'.$config['site_url'].'storage/products/'.$value.'" alt="'.$item_title.'" class="img-responsive"></div></div>';
            $i++;
        }
        $screen_classicb = implode('', $screen_classicb2);

        $i =0;
        $screen_classicsm = explode(',',$info['screen_shot']);
        foreach ($screen_classicsm as $value)
        {
            //REMOVE SPACE FROM $VALUE ----
            $value = trim($value);
            $screen_classicsm2[] = '<li data-target="#product-carousel" data-slide-to="'.$i.'" class="active"><img src="'.$config['site_url'].'storage/products/thumb/'.$value.'" alt="'.$item_title.'" class="img-responsive"></li>';
            $i++;
        }
        $screen_classicsm = implode('  ', $screen_classicsm2);

    }else{
        $show_image_slider = 0;
        $screen_sm = "";
        $screen_big = "";
        $screen_classicb = "";
        $screen_classicsm = "";
    }


}
else {
    error($lang['PAGENOTEXIST'], __LINE__, __FILE__, 1);
    exit;
}

$country_code = check_user_country($config);
$query = "SELECT * FROM ".$config['db']['pre']."product where status = 'active' and category = '$item_catid' AND country = '$country_code' and id != '$item_id' ORDER BY id DESC LIMIT 6";

//Loop for list view
$item = array();
$result1 = $mysqli->query($query);
if (mysqli_num_rows($result1) > 0) {
    // output data of each row
    while($info1 = mysqli_fetch_assoc($result1)) {
        $item[$info1['id']]['id'] = $info1['id'];
        $item[$info1['id']]['featured'] = $info1['featured'];
        $item[$info1['id']]['urgent'] = $info1['urgent'];
        $item[$info1['id']]['product_name'] = $info1['product_name'];
        $item[$info1['id']]['location'] = $info1['location'];
        $item[$info1['id']]['city'] = $info1['city'];
        $item[$info1['id']]['state'] = $info1['state'];
        $item[$info1['id']]['country'] = $info1['country'];
        $item[$info1['id']]['price'] = $info1['price'];
        $item[$info1['id']]['created_at'] = timeago($info1['created_at']);
        $item[$info1['id']]['author_id'] = $info['user_id'];
        $get_main = get_maincat_by_id($config,$info1['category']);
        $item[$info1['id']]['category'] = $get_main['cat_name'];

        $picture     =   explode(',' ,$info1['screen_shot']);
        $item[$info1['id']]['pic_count'] = count($picture);
        if($picture[0] != ""){
            $item[$info1['id']]['picture'] = $picture[0];
        }else{
            $item[$info1['id']]['picture'] = "default.png";
        }

        if($info1['price'] != '0'){
            $currency_info = set_user_currency($config,$info1['country']);
            $config['currency_code'] = $currency_info['code'];
            $config['currency_sign'] = $currency_info['html_entity'];
            $config['currency_pos'] = $currency_info['in_left'];

            if($currency_info['in_left'] == 1){
                $price = $currency_info['html_entity']."".$info1['price'];
            }else{
                $price = $info1['price']." ".$currency_info['html_entity'];
            }
        }else{
            $price = 0;
        }

        $item[$info1['id']]['price'] = $price;

        $item[$info1['id']]['favorite'] = check_product_favorite($config,$info1['id']);
        $userinfo = get_user_data($config,null,$info1['user_id']);

        $item[$info1['id']]['username'] = $userinfo['username'];

        $pro_url = create_slug($info1['product_name']);
        $item[$info1['id']]['link'] = $config['site_url'].'ad/' . $info1['id'] . '/'.$pro_url;

        $cat_url = create_slug($get_main['cat_name']);
        $item[$info1['id']]['catlink'] = $config['site_url'].'category/'.$info1['category'].'/'.$cat_url;

    }
}
else
{
    //echo "0 results";
}


$mailsent =0;
if (isset($_POST['sendemail'])) {

    $ad_link = $config['site_url']."ad/".$item_id;
    $page = new HtmlTemplate();
    $page->html = $config['email_sub_contact_seller'];
    $page->SetParameter ('ADTITLE', $item_title);
    $page->SetParameter ('ADLINK', $ad_link);
    $page->SetParameter ('SELLER_NAME', $item_author_name);
    $page->SetParameter ('SELLER_EMAIL', $item_author_email);
    $page->SetParameter('SENDER_NAME', $_POST['name']);
    $page->SetParameter('SENDER_EMAIL', $_POST['email']);
    $page->SetParameter('SENDER_PHONE', $_POST['phone']);
    $email_subject = $page->CreatePageReturn($lang,$config,$link);

    $page = new HtmlTemplate();
    $page->html = $config['email_message_contact_seller'];;
    $page->SetParameter ('ADTITLE', $item_title);
    $page->SetParameter ('ADLINK', $ad_link);
    $page->SetParameter ('SELLER_NAME', $item_author_name);
    $page->SetParameter ('SELLER_EMAIL', $item_author_email);
    $page->SetParameter('SENDER_NAME', $_POST['name']);
    $page->SetParameter('SENDER_EMAIL', $_POST['email']);
    $page->SetParameter('SENDER_PHONE', $_POST['phone']);
    $page->SetParameter('MESSAGE', $_POST['message']);
    $email_body = $page->CreatePageReturn($lang,$config,$link);

    email($item_author_email,$item_author_name,$email_subject,$email_body,$config);

    message($lang['SUCCESS'],$lang['MAILSENTTOSELLER']);
}

$GetCategory = get_maincategory($config);
$cat_dropdown = get_categories_dropdown($config,$lang);


$meta_desc = substr(strip_tags($item_description),0,150);
$meta_desc = trim(preg_replace('/\s\s+/', ' ', $meta_desc));
// Output to template
$page = new HtmlTemplate ('templates/' . $config['tpl_name'] . '/ad-detail.tpl');
$page->SetParameter ('OVERALL_HEADER', create_header($item_title,$meta_desc,$meta_image,true));
$page->SetLoop ('ITEM', $item);
$page->SetParameter ('CAT_DROPDOWN',$cat_dropdown);
$page->SetLoop ('CATEGORY',$GetCategory);
$page->SetParameter ('ITEM_CUSTOMFIELD', $item_custom_field);
$page->SetLoop ('ITEM_CUSTOM', $item_custom);
$page->SetLoop ('ITEM_CUSTOM_TEXTAREA', $item_custom_textarea);
$page->SetLoop ('ITEM_CUSTOM_CHECKBOX', $item_checkbox);
$page->SetParameter ('ITEM_FAVORITE', check_product_favorite($config,$item_id));
$page->SetParameter ('ITEM_ID', $item_id);
$page->SetParameter ('ITEM_TITLE', $item_title);
$page->SetParameter ('ITEM_FEATURED', $item_featured);
$page->SetParameter ('ITEM_URGENT', $item_urgent);
$page->SetParameter ('ITEM_HIGHLIGHT', $item_highlight);
$page->SetParameter ('ITEM_AUTHORID', $item_author_id);
$page->SetParameter ('ITEM_AUTHORLINK', $item_author_link);
$page->SetParameter ('ITEM_AUTHORUEMAIL', $item_author_email);
$page->SetParameter ('ITEM_AUTHORUNAME', $item_author_username);
$page->SetParameter ('ITEM_AUTHORIMG', $item_author_image);
$page->SetParameter ('ITEM_AUTHORONLINE', $item_author_online);
$page->SetParameter ('ITEM_AUTHORCOUNTRY', $item_author_country);
$page->SetParameter ('ITEM_AUTHORJOINED', $item_author_joined);
$page->SetParameter ('ITEM_CATEGORY', $item_category);
$page->SetParameter ('ITEM_SUB_CATEGORY', $item_sub_category);
$page->SetParameter ('ITEM_CATLINK', $item_catlink);
$page->SetParameter ('ITEM_SUBCATLINK', $item_subcatlink);
$page->SetParameter ('ITEM_LOCATION', $item_location);
$page->SetParameter ('ITEM_CITY', $item_city);
$page->SetParameter ('ITEM_STATE', $item_state);
$page->SetParameter ('ITEM_COUNTRY', $item_country);
$page->SetParameter ('ITEM_LAT', $lat);
$page->SetParameter ('ITEM_LONG', $long);
$page->SetParameter ('ITEM_CREATED', $item_created_at);
$page->SetParameter ('ITEM_DESC', $item_description);
$page->SetParameter ('ITEM_PRICE', $item_price);
$page->SetParameter ('ITEM_NEGOTIATE', $item_negotiable);
$page->SetParameter ('ITEM_PHONE', $item_phone);
$page->SetParameter ('ITEM_HIDE_PHONE', $item_hide_phone);
$page->SetParameter ('ITEM_SCREENS_SM', $screen_sm);
$page->SetParameter ('ITEM_SCREENS_BIG', $screen_big);
$page->SetParameter ('ITEM_SCREENS_CLASSB', $screen_classicb);
$page->SetParameter ('ITEM_SCREENS_CLASSSM', $screen_classicsm);
$page->SetParameter ('SHOW_IMAGE_SLIDER', $show_image_slider);
$page->SetParameter ('ITEM_STATUS', $item_status);
$page->SetParameter ('ITEM_TAG', $item_tag);
$page->SetParameter ('SHOW_TAG', $show_tag);
$page->SetParameter ('ITEM_CONTACT_PHONE', $item_contact_phone);
$page->SetParameter ('ITEM_CONTACT_EMAIL', $item_contact_email);
$page->SetParameter ('ITEM_CONTACT_CHAT', $item_contact_chat);
$page->SetParameter ('ITEM_UPDATED', $item_updated_at);
$page->SetParameter ('ITEM_VIEW', $item_view);
$page->SetParameter ('MAILSENT', $mailsent);
$page->SetParameter ('ITEMREVIEW', count_product_review($config,$item_id));
$page->SetParameter('ZECHAT', get_option($config,"zechat_on_off"));
$page->SetParameter('MAP_COLOR', get_option($config,"map_color"));
$right_ad = get_advertise($config,"right_sidebar");
$page->SetParameter('RIGHT_ADSCODE', $right_ad['tpl']);
$page->SetParameter('RIGHT_ADSTATUS', $right_ad['status']);
$page->SetParameter ('OVERALL_FOOTER', create_footer());
$page->CreatePageEcho();
?>