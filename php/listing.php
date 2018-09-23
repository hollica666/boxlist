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
    $page_number = 1;
else{
    $page_number = $_GET['page'];
}

if(!isset($_GET['order']))
    $order = "DESC";
else{
    if($_GET['order'] == ""){
        $order = "DESC";
    }else{
        $order = $_GET['order'];
    }
}


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

$limit = isset($_GET['limit']) ? $_GET['limit'] : 9;
$filter = isset($_GET['filter']) ? $_GET['filter'] : "";
$sorting = isset($_GET['sort']) ? $_GET['sort'] : "Newest";
$budget = isset($_GET['budget']) ? $_GET['budget'] : "";
$keywords = isset($_GET['keywords']) ? str_replace("-"," ",$_GET['keywords']) : "";

$category = "";
$subcat = "";

if(isset($_GET['subcat']) && !empty($_GET['subcat'])){

    if(is_numeric($_GET['subcat'])){
        if(check_sub_category_exists($_GET['subcat'])){
            $subcat = $_GET['subcat'];
        }
    }else{
        $subcat = get_subcategory_id_by_slug($config,$_GET['subcat']);
    }
}elseif(isset($_GET['cat']) && !empty($_GET['cat'])){
    if(is_numeric($_GET['cat'])){
        if(check_category_exists($_GET['cat'])){
            $category = $_GET['cat'];
        }
    }else{
        $category = get_category_id_by_slug($config,$_GET['cat']);
    }

}




if($subcat != ''){
    $custom_fields = get_customFields_by_catid($config,$mysqli,'',$subcat,false);
}else if($category != ''){
    $custom_fields = get_customFields_by_catid($config,$mysqli,$category,'',false);
}else{
    $custom_fields = get_customFields_by_catid($config,$mysqli,'','',false);
}

$custom = array();
if(isset($_GET['custom']) && !empty($_GET['custom'])){
    $custom = $_GET['custom'];
}


if(isset($_GET['city']) && !empty($_GET['city'])){
    $city = $_GET['city'];
}else{
    $city = "";
}


$total = 0;

$where = '';

if(isset($_GET['keywords']) && !empty($_GET['keywords'])){
    $where.= "AND (p.product_name LIKE '%$keywords%' or p.tag LIKE '%$keywords%') ";
}

if(isset($category) && !empty($category)){
    $where.= "AND (p.category = '$category') ";
}

if(isset($_GET['subcat']) && !empty($_GET['subcat'])){
    $where.= "AND (p.sub_category = '$subcat') ";
}


if (isset($_GET['range1']) && $_GET['range1'] != '') {
    $range1 = str_replace('.', '', $_GET['range1']);
    $range2 = str_replace('.', '', $_GET['range2']);
    $where.= ' AND (p.price BETWEEN '.$range1.' AND '.$range2.')';
} else {
    $range1 = "";
    $range2 = "";
}

if(isset($_GET['city']) && !empty($_GET['city']))
{
    $where.= "AND (p.city = '".$_GET['city']."') ";
}
elseif(isset($_GET['location']) && !empty($_GET['location']))
{
    $placetype = $_GET['placetype'];
    $placeid = $_GET['placeid'];

    if($placetype == "country"){
        $where.= "AND (p.country = '$placeid') ";
    }elseif($placetype == "state"){
        $where.= "AND (p.state = '$placeid') ";
    }else{
        $where.= "AND (p.city = '$placeid') ";
    }
}
else{
    $country_code = check_user_country($config);
    $where.= "AND (p.country = '$country_code') ";
}

if(isset($_GET['custom'])) {

    $whr_count = 0;
    $custom_where = "";
    foreach ($_GET['custom'] as $key => $value) {
        if (empty($value)) {
            unset($_GET['custom'][$key]);
        }
        if (!empty($_GET['custom'])) {
            // custom value is not empty.
            $cond = "";
            if (is_array($value)) {
                $cond = "(";
                $cond_count = 0;
                foreach ($value as $val) {
                    if ($cond_count == 0) {
                        $cond .= " find_in_set('$val',c.field_data) <> 0 ";
                    } else {
                        $cond .= " or find_in_set('$val',c.field_data) <> 0 ";
                    }
                    $cond_count++;
                }
                $cond .= ")";
                $case = $cond;
            }
            else {
                $case = "CASE
    WHEN (c.field_type = 'text-field' or c.field_type = 'textarea') THEN c.field_data LIKE '%$value%'
    ELSE c.field_data = '$value'
  END";
            }

            if ($key != "" && $value != "") {

                if ($whr_count == 0) {
                    $custom_where .= " AND ( ( c.field_id = '$key' and $case )";
                } else {
                    $custom_where .= " OR ( c.field_id = '$key' and $case )";
                }
                $whr_count++;
            }
        }

    }
    if($custom_where != "")
        $where .= $custom_where.")";

    if (!empty($_GET['custom'])) {
        $sql = "SELECT DISTINCT p.*
FROM `".$config['db']['pre']."product` AS p
JOIN `".$config['db']['pre']."custom_data` AS c ON c.product_id = p.id
 WHERE status = 'active' ";
    }else{
        $sql = "SELECT DISTINCT p.*
FROM `".$config['db']['pre']."product` AS p
 WHERE p.status = 'active' ";
    }

    $totalWithoutFilter = mysqli_num_rows(mysqli_query($mysqli, "$sql $where"));
}
else{
    $totalWithoutFilter = mysqli_num_rows(mysqli_query($mysqli, "SELECT 1 FROM ".$config['db']['pre']."product as p where status = 'active' $where"));
}




if(isset($_GET['filter'])){
    if($_GET['filter'] == 'free')
    {
        $where.= "AND (p.urgent='0' AND p.featured='0' AND p.highlight='0') ";
    }
    elseif($_GET['filter'] == 'featured')
    {
        $where.= "AND (p.featured='1') ";
    }
    elseif($_GET['filter'] == 'urgent')
    {
        $where.= "AND (p.urgent='1') ";
    }
    elseif($_GET['filter'] == 'highlight')
    {
        $where.= "AND (p.highlight='1') ";
    }
}

if(isset($_GET['custom']))
{
    if (!empty($_GET['custom'])) {
        $sql = "SELECT DISTINCT p.*
FROM `".$config['db']['pre']."product` AS p
JOIN `".$config['db']['pre']."custom_data` AS c ON c.product_id = p.id
 WHERE status = 'active' ";
    }else{
        $sql = "SELECT DISTINCT p.*
FROM `".$config['db']['pre']."product` AS p
 WHERE p.status = 'active' ";
    }

    $query =  $sql . " $where ORDER BY $sort $order LIMIT ".($page_number-1)*$limit.",$limit";

    $total = mysqli_num_rows(mysqli_query($mysqli, "$sql $where"));
    $featuredAds = mysqli_num_rows(mysqli_query($mysqli, "$sql and (p.featured='1') $where"));
    $urgentAds = mysqli_num_rows(mysqli_query($mysqli, "$sql and (p.urgent='1') $where"));

}else{
    $total = mysqli_num_rows(mysqli_query($mysqli, "SELECT 1 FROM ".$config['db']['pre']."product as p where status = 'active' $where"));
    $featuredAds = mysqli_num_rows(mysqli_query($mysqli, "SELECT 1 FROM ".$config['db']['pre']."product as p where status = 'active' and featured='1' $where"));
    $urgentAds = mysqli_num_rows(mysqli_query($mysqli, "SELECT 1 FROM ".$config['db']['pre']."product as p where status = 'active' and urgent='1' $where"));

    $query = "SELECT * FROM ".$config['db']['pre']."product as p where status = 'active' $where ORDER BY $sort $order LIMIT ".($page_number-1)*$limit.",$limit";
}

$count = 0;





//Loop for list view
$item = array();
$result = $mysqli->query($query);
if (mysqli_num_rows($result) > 0) {
    // output data of each row
    while($info = mysqli_fetch_assoc($result)) {
        $item[$info['id']]['id'] = $info['id'];
        $item[$info['id']]['featured'] = $info['featured'];
        $item[$info['id']]['urgent'] = $info['urgent'];
        $item[$info['id']]['highlight'] = $info['highlight'];
        $item[$info['id']]['product_name'] = $info['product_name'];
        $item[$info['id']]['description'] = $info['description'];
        $item[$info['id']]['category'] = $info['category'];
        $item[$info['id']]['price'] = $info['price'];
        $item[$info['id']]['phone'] = $info['phone'];
        $item[$info['id']]['address'] = strlimiter($info['location'],20);
        $cityname = get_cityName_by_id($config,$info['city']);
        $item[$info['id']]['location'] = $cityname;
        $item[$info['id']]['city'] = $cityname;
        $item[$info['id']]['state'] = get_stateName_by_id($config,$info['state']);
        $item[$info['id']]['country'] = get_countryName_by_id($config,$info['country']);
        $item[$info['id']]['latlong'] = $info['latlong'];

        $item[$info['id']]['tag'] = $info['tag'];
        $item[$info['id']]['status'] = $info['status'];
        $item[$info['id']]['view'] = $info['view'];
        $item[$info['id']]['created_at'] = timeAgo($info['created_at']);
        $item[$info['id']]['updated_at'] = date('d M Y', $info['updated_at']);

        $item[$info['id']]['cat_id'] = $info['category'];
        $item[$info['id']]['sub_cat_id'] = $info['sub_category'];
        $get_main = get_maincat_by_id($config,$info['category']);
        $get_sub = get_subcat_by_id($config,$info['sub_category']);
        $item[$info['id']]['category'] = $get_main['cat_name'];
        $item[$info['id']]['sub_category'] = $get_sub['sub_cat_name'];

        $item[$info['id']]['favorite'] = check_product_favorite($config,$info['id']);

        $picture     =   explode(',' ,$info['screen_shot']);
        $item[$info['id']]['pic_count'] = count($picture);
        if($picture[0] != ""){
            $item[$info['id']]['picture'] = $picture[0];
        }else{
            $item[$info['id']]['picture'] = "default.png";
        }

        $price = price_format($info['price'],$info['country']);
        $item[$info['id']]['price'] = $price;

        if($info['tag'] != ''){
            $item[$info['id']]['showtag'] = "1";
            $tag = explode(',', $info['tag']);
            $tag2 = array();
            foreach ($tag as $val)
            {
                //REMOVE SPACE FROM $VALUE ----
                $val = preg_replace("/[\s_]/","-", trim($val));
                $tag2[] = '<li><a href="'.$config['site_url'].'keywords/'.$val.'">'.$val.'</a> </li>';
            }
            $item[$info['id']]['tag'] = implode('  ', $tag2);
        }else{
            $item[$info['id']]['tag'] = "";
            $item[$info['id']]['showtag'] = "0";
        }



        $user = "SELECT username FROM ".$config['db']['pre']."user where id='".$info['user_id']."'";
        $userresult = mysqli_query(db_connect($config), $user);
        $userinfo = mysqli_fetch_assoc($userresult);

        $item[$info['id']]['username'] = $userinfo['username'];

        $author_url = create_slug($userinfo['username']);

        $item[$info['id']]['author_link'] = $config['site_url'].'profile/'.$author_url;

        $pro_url = create_slug($info['product_name']);

        $item[$info['id']]['link'] = $config['site_url'].'ad/' . $info['id'] . '/'.$pro_url;

        $item[$info['id']]['catlink'] = $config['site_url'].'category/'.$get_main['slug'];

        $item[$info['id']]['subcatlink'] = $config['site_url'].'category/'.$get_main['slug'].'/'.$get_sub['slug'];

        $city = create_slug($item[$info['id']]['city']);
        $item[$info['id']]['citylink'] = $config['site_url'].'city/'.$info['city'].'/'.$city;

    }
}
else
{
    //echo "0 results";
}

//Again make loop for grid view
$item2 = array();
$item2 = $item;


$selected = "";
if(isset($_GET['cat']) && !empty($_GET['cat'])){
    $selected = $_GET['cat'];
}
// Check Settings For quotes
$GetCategory = get_maincategory($config,$selected);
$cat_dropdown = get_categories_dropdown($config,$lang);

$maincatname = get_maincat_by_id($config,$category);
$maincatname = $maincatname['cat_name'];
$mainCategory = isset($category) ? $maincatname : "";
$subcatname = get_subcat_by_id($config,$subcat);
$subcatname = $subcatname['sub_cat_name'];
$subCategory = isset($subcat) ? $subcatname : "";

if(isset($category) && !empty($category)){
    $Pagetitle = $mainCategory;
}
elseif(isset($subcat) && !empty($subcat)){
    $Pagetitle = $subCategory;
}
elseif(!empty($keywords)){
    $Pagetitle = ucfirst($keywords);
}
else{
    $Pagetitle = $lang['ADS-LISTINGS'];
}

if(!empty($_GET['location'])){
    $locTitle        =   explode(',' ,$_GET['location']);
    $locTitle     =   $locTitle[0];
    $Pagetitle .= " ".$locTitle;
}
else{
    $sortname = check_user_country($config);
    $countryName = get_countryName_by_sortname($config,$sortname);
    $Pagetitle .= " ".$countryName;
}

if(isset($_GET['city']) && !empty($_GET['city']))
{
    $cityName = get_cityName_by_id($config,$_GET['city']);
    $Pagetitle = $lang['ADS-LISTINGS']." ".$lang['IN']." ".$cityName;
}

// Output to template
$page = new HtmlTemplate ('templates/' . $config['tpl_name'] . '/ad-listing.tpl');
$page->SetParameter ('OVERALL_HEADER', create_header($Pagetitle));
$page->SetParameter ('PAGETITLE', $Pagetitle);
$page->SetLoop ('ITEM', $item);
$page->SetLoop ('ITEM2', $item2);
$page->SetLoop ('CATEGORY',$GetCategory);
$page->SetParameter ('CAT_DROPDOWN',$cat_dropdown);
$page->SetParameter ('SERKEY', $keywords);
$page->SetParameter ('MAINCAT', $category);
$page->SetParameter ('SUBCAT', $subcat);
$page->SetParameter ('MAINCATEGORY', $mainCategory);
$page->SetParameter ('SUBCATEGORY', $subCategory);
$page->SetParameter ('BUDGET', $budget);
$page->SetParameter ('KEYWORDS', $keywords);
$page->SetParameter ('RANGE1', $range1);
$page->SetParameter ('RANGE2', $range2);
$page->SetParameter ('ADSFOUND', $total);
$page->SetParameter ('TOTALADSFOUND', $totalWithoutFilter);
$page->SetParameter ('FEATUREDFOUND', $featuredAds);
$page->SetParameter ('URGENTFOUND', $urgentAds);
$page->SetParameter ('LIMIT', $limit);
$page->SetParameter ('FILTER', $filter);
$page->SetParameter ('SORT', $sorting);
$page->SetParameter ('ORDER', $order);
if(isset($_SESSION['user']['id']))
{
    $page->SetParameter('USER_ID',$_SESSION['user']['id']);
    $page->SetParameter('LOGGED_IN', 1);
}
else
{
    $page->SetParameter('USER_ID','');
    $page->SetParameter('LOGGED_IN', 0);
}

if(isset($category) && !empty($category)) {
    $SubCatList = get_subcat_of_maincat($config, $category, true);
    $page->SetLoop ('SUBCATLIST',$SubCatList);
}else{
    $page->SetLoop ('SUBCATLIST',"");
}

$Pagelink = "";
if(count($_GET) >= 1){
    $get = http_build_query($_GET);
    $Pagelink .= "?".$get;

    $page->SetLoop ('PAGES', pagenav($total,$page_number,$limit,$link['LISTING'].$Pagelink,1));
}else{
    $page->SetLoop ('PAGES', pagenav($total,$page_number,$limit,$link['LISTING']));
}
$page->SetLoop ('CUSTOMFIELDS',$custom_fields);
$page->SetParameter ('SHOWCUSTOMFIELD', (count($custom_fields) > 0) ? 1 : 0);
$page->SetParameter ('CATEGORY', "Ads Listing");
$page->SetParameter ('OVERALL_FOOTER', create_footer());
$page->CreatePageEcho();
?>