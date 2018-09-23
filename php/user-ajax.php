<?php
require_once('../includes/config.php');
require_once('../includes/classes/class.template_engine.php');
require_once('../includes/classes/class.country.php');
require_once('../includes/functions/func.global.php');
require_once('../includes/functions/func.sqlquery.php');
require_once('../includes/functions/func.users.php');
require_once('../includes/lang/lang_'.$config['lang'].'.php');

// Check if SSL enabled
$protocol = isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] && $_SERVER["HTTPS"] != "off"    ? "https://" : "http://";

// Define APPURL
$site_url = $protocol
    . $_SERVER["SERVER_NAME"]
    . (dirname($_SERVER["SCRIPT_NAME"]) == DIRECTORY_SEPARATOR ? "" : "/")
    . trim(str_replace("\\", "/", dirname($_SERVER["SCRIPT_NAME"])), "/");

define("SITEURL", $site_url);
$config['site_url'] = dirname($site_url)."/";

require_once('../includes/seo-url.php');

$con = db_connect($config);
sec_session_start();
if (isset($_GET['action'])){
    if ($_GET['action'] == "email_contact_seller") { email_contact_seller($con); }
    if ($_GET['action'] == "deleteMyAd") { deleteMyAd($con,$config); }
    if ($_GET['action'] == "deleteResumitAd") { deleteResumitAd($con,$config); }

    if ($_GET['action'] == "openlocatoionPopup") { openlocatoionPopup($con,$config); }
    if ($_GET['action'] == "getlocHomemap") { getlocHomemap($con,$config); }
    if ($_GET['action'] == "searchCityFromCountry") {searchCityFromCountry($con,$config);}
}

if(isset($_POST['action'])){
    if ($_POST['action'] == "removeImage") { removeImage(); }
    if ($_POST['action'] == "hideItem") { hideItem($con,$config); }
    if ($_POST['action'] == "removeAdImg") { removeAdImg($con,$config); }
    if ($_POST['action'] == "setFavAd") {setFavAd($con,$config);}
    if ($_POST['action'] == "removeFavAd") {removeFavAd($con,$config);}
    if ($_POST['action'] == "getsubcatbyidList") { getsubcatbyidList($con,$config); }
    if ($_POST['action'] == "getsubcatbyid") {getsubcatbyid($con,$config);}
    if ($_POST['action'] == "getCustomFieldByCatID") {getCustomFieldByCatID($con,$config);}

    if ($_POST['action'] == "getStateByCountryID") {getStateByCountryID($con,$config);}
    if ($_POST['action'] == "getCityByStateID") {getCityByStateID($con,$config);}
    if ($_POST['action'] == "ModelGetStateByCountryID") {ModelGetStateByCountryID($con,$config);}
    if ($_POST['action'] == "ModelGetCityByStateID") {ModelGetCityByStateID($con,$config);}
    if ($_POST['action'] == "searchStateCountry") {searchStateCountry($con,$config);}
    if ($_POST['action'] == "searchCityStateCountry") {searchCityStateCountry($con,$config);}
    if ($_POST['action'] == "ajaxlogin") {ajaxlogin();}
    if ($_POST['action'] == "email_verify") {email_verify();}
    if ($_POST['action'] == "listingpro_suggested_search") {listingpro_suggested_search();}
}

function ajaxlogin(){
    global $config,$lang;
    $loggedin = userlogin($config,$_POST['username'], $_POST['password']);

    if(!is_array($loggedin))
    {
        echo $lang['USERNOTFOUND'];
    }
    elseif($loggedin['status'] == 2)
    {
        echo $lang['ACCOUNTBAN'];
    }
    else
    {
        $user_browser = $_SERVER['HTTP_USER_AGENT']; // Get the user-agent string of the user.
        $user_id = preg_replace("/[^0-9]+/", "", $loggedin['id']); // XSS protection as we might print this value
        $_SESSION['user']['id']  = $user_id;
        $username = preg_replace("/[^a-zA-Z0-9_\-]+/", "", $loggedin['username']); // XSS protection as we might print this value
        $_SESSION['user']['username'] = $username;
        $_SESSION['user']['login_string'] = hash('sha512', $loggedin['password'] . $user_browser);

        update_lastactive($config);

        echo "success";
    }
    die();

}

function removeImage(){
    global $config,$lang;
    if(isset($_POST['product_id'])){
        $sql = "SELECT screen_shot FROM " . $config['db']['pre'] . "product where id = '" .validate_input($_POST['product_id']) . "' limit 1";
        $result = mysqli_query(db_connect($config), $sql);
        $info = mysqli_fetch_assoc($result);
        $screen_shot = $info['screen_shot'];
        $screnshots = explode(',',$info['screen_shot']);
        if($key = array_search($_POST['imagename'],$screnshots) != -1){
            unset($screnshots[$key]);
            $screens = implode(',',$screnshots);
            $sql = "UPDATE " . $config['db']['pre'] . "product set screen_shot='".validate_input($screens)."' where id = '" .validate_input($_POST['product_id']) . "' limit 1";
            mysqli_query(db_connect($config), $sql);
        }
    }

}

function email_verify(){
    global $config,$lang,$link;

    if(checkloggedin($config))
    {
        $get_userdata = get_user_data($config,$_SESSION['user']['username']);
        $resendconfirm = $get_userdata['confirm'];
        $resendemail = $get_userdata['email'];
        $resendname = $get_userdata['name'];

        $page = new HtmlTemplate();
        $page->html = $config['email_sub_signup_confirm'];
        $page->SetParameter ('EMAIL', $resendemail);
        $page->SetParameter ('USER_FULLNAME', $resendname);
        $email_subject = $page->CreatePageReturn($lang,$config,$link);

        $confirmation_link = $link['SIGNUP']."?confirm=".$resendconfirm."&user=".$_SESSION['user']['id'];
        $page = new HtmlTemplate();
        $page->html = $config['email_message_signup_confirm'];
        $page->SetParameter ('CONFIRMATION_LINK', $confirmation_link);
        $page->SetParameter ('EMAIL', $resendemail);
        $page->SetParameter ('USER_FULLNAME', $resendname);
        $email_body = $page->CreatePageReturn($lang,$config,$link);

        $request = email($resendemail,$resendname,$email_subject,$email_body,$config);
        if($request != true){
            $respond = $lang['SENT'];
        }else{
            $respond = $request;
        }
        echo '<a class="uiButton uiButtonLarge resend" style="box-sizing:content-box;"><span class="uiButtonText">'.$respond.'</span></a>';
        die();

    }
    else
    {
        header("Location: ".$config['site_url']."login");
        exit;
    }
}

function email_contact_seller($con){
    global $config,$lang,$link;
    if (isset($_POST['sendemail'])) {

        $item_id = $_POST['id'];
        $iteminfo = get_item_by_id($con,$item_id);

        $item_title = $iteminfo['title'];
        $item_author_name = $iteminfo['author_name'];
        $item_author_email = $iteminfo['author_email'];

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

        echo 'success';
        die();
    }else{
        echo 0;
        die();
    }
}

function getStateByCountryID($con,$config)
{
    $country_id = isset($_POST['id']) ? $_POST['id'] : 0;
    $selectid = isset($_POST['selectid']) ? $_POST['selectid'] : "";

    $query = "SELECT id,code,name FROM `".$config['db']['pre']."subadmin1` WHERE country_code = '".$country_id."' ORDER BY name";
    if ($result = $con->query($query)) {

        $list = '<option value="">Select State</option>';
        while ($row = mysqli_fetch_assoc($result)) {
            $name = $row['name'];
            $state_id = $row['id'];
            $state_code = $row['code'];
            if($selectid == $state_code){
                $selected_text = "selected";
            }
            else{
                $selected_text = "";
            }
            $list .= '<option value="'.$state_code.'" '.$selected_text.'>'.$name.'</option>';
        }

        echo $list;
    }
}

function getCityByStateID($con,$config)
{

    $state_id = isset($_POST['id']) ? $_POST['id'] : 0;
    $selectid = isset($_POST['selectid']) ? $_POST['selectid'] : "";

    $query = "SELECT id ,name FROM `".$config['db']['pre']."cities` WHERE subadmin1_code = " . $state_id;
    $result = mysqli_query($con,$query);
    $total = mysqli_num_rows($result);
    if ($result = $con->query($query)) {

        $list = '<option value="">Select City</option>';
        while ($row = mysqli_fetch_assoc($result)) {
            $name = $row['name'];
            $id = $row['id'];
            if($selectid == $id){
                $selected_text = "selected";
            }
            else{
                $selected_text = "";
            }
            $list .= '<option value="'.$id.'" '.$selected_text.'>'.$name.'</option>';
        }
        echo $list;
    }
}

function ModelGetStateByCountryID($con,$config)
{
    $country_id = isset($_POST['id']) ? $_POST['id'] : 0;
    $countryName = get_countryName_by_id($config,$country_id);

    $query = "SELECT id,code,name FROM `".$config['db']['pre']."subadmin1` WHERE country_code = '".$country_id."' ORDER BY name";
    $result = mysqli_query($con,$query);
    $total = mysqli_num_rows($result);
    $list = '<ul class="column col-md-12 col-sm-12 cities">';
    $count = 1;
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $name = $row['name'];
            $id = $row['code'];

            if($count == 1)
            {
                $list .=  '<li class="selected"><a class="selectme" data-id="'.$country_id.'" data-name="All '.$countryName.'" data-type="country"><strong>All '.$countryName.'</strong></a></li>';
            }
            $list .= '<li class=""><a id="region'.$id.'" class="statedata" data-id="'.$id.'" data-name="'.$name.'"><span>'.$name.' <i class="fa fa-angle-right"></i></span></a></li>';

            $count++;
        }
        echo $list."</ul>";
    }
}

function ModelGetCityByStateID($con,$config)
{
    $state_id = isset($_POST['id']) ? $_POST['id'] : 0;
    $stateName = get_stateName_by_id($config,$state_id);
    //$state_code = substr($state_id,3);
    $country_code = substr($state_id,0,2);
    $query = "SELECT id ,name FROM `".$config['db']['pre']."cities` WHERE subadmin1_code = '".$state_id."' and country_code = '$country_code' ORDER BY asciiname";

    $result = mysqli_query($con,$query);
    if($result){
        $total = mysqli_num_rows($result);
        $list = '<ul class="column col-md-12 col-sm-12 cities">';
        $count = 1;
        if ($total > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $name = $row['name'];
                $id = $row['id'];
                if($count == 1)
                {
                    $list .=  '<li class="selected"><a id="changeState"><strong><i class="fa fa-arrow-left"></i> Change State</strong></a></li>';
                    $list .=  '<li class="selected"><a class="selectme" data-id="'.$state_id.'" data-name="'.$stateName.', State" data-type="state"><strong>Whole '.$stateName.'</strong></a></li>';
                }

                $list .= '<li class=""><a id="region'.$id.'" class="selectme" data-id="'.$id.'" data-name="'.$name.', City" data-type="city"><span>'.$name.' <i class="fa fa-angle-right"></i></span></a></li>';
                $count++;
            }

            echo $list."</ul>";
        }

    }else{
        echo '<ul class="column col-md-12 col-sm-12 cities">
            <li class="selected"><a id="changeState"><strong><i class="fa fa-arrow-left"></i> Change State</strong></a></li>
            <li><a> No city available</a></li>
            </ul>';
    }

}

function searchCityFromCountry($con,$config)
{

    $dataString = isset($_GET['q']) ? $_GET['q'] : "";
    $sortname = check_user_country($config);

    $perPage = 10;
    $page = isset($_GET['page']) ? $_GET['page'] : "1";
    $start = ($page-1)*$perPage;
    if($start < 0) $start = 0;

    $total = mysqli_num_rows(mysqli_query($con,"select 1 from `".$config['db']['pre']."cities` where name like '$dataString%' and  country_code = '$sortname'"));

    $sql = "SELECT c.id, c.name, c.latitude, c.longitude, c.subadmin1_code, s.name AS statename
FROM `".$config['db']['pre']."cities` AS c
INNER JOIN `".$config['db']['pre']."subadmin1` AS s ON s.code = c.subadmin1_code
 WHERE c.name like '$dataString%' and c.country_code = '$sortname'
 ORDER BY
  CASE
    WHEN c.name = '$dataString' THEN 1
    WHEN c.name LIKE '$dataString%' THEN 2
    ELSE 3
  END ";
    $query =  $sql . " limit " . $start . "," . $perPage;
    $query = $con->query($query);

    if(empty($_GET["rowcount"])) {
        $_GET["rowcount"] = $rowcount = mysqli_num_rows(mysqli_query($con, $sql));
    }

    $pages  = ceil($_GET["rowcount"]/$perPage);

    $items = '';
    $i = 0;
    $MyCity = array();

    while ($row = mysqli_fetch_array($query)) {
        $cityid = $row['id'];
        $cityname = $row['name'];
        $latitude = $row['latitude'];
        $longitude = $row['longitude'];
        $statename = $row['statename'];

        $MyCity[$i]["id"]   = $cityid;
        $MyCity[$i]["text"] = $cityname.", ".$statename;
        $MyCity[$i]["latitude"]   = $latitude;
        $MyCity[$i]["longitude"]   = $longitude;
        $i++;
    }

    echo $json = '{"items" : '.json_encode($MyCity, JSON_UNESCAPED_SLASHES).',"totalEntries" : '.$total.'}';
    die();
}

function searchStateCountry($con,$config)
{
    $dataString = isset($_POST['dataString']) ? $_POST['dataString'] : "";
    $sortname = check_user_country($config);
    $query = "SELECT c.id, c.name, c.subadmin1_code, s.name AS statename
FROM `".$config['db']['pre']."cities` AS c
INNER JOIN `".$config['db']['pre']."subadmin1` AS s ON s.code = c.subadmin1_code
 WHERE c.name like '%$dataString%' and c.country_code = '$sortname'
 ORDER BY
  CASE
    WHEN c.name = '$dataString' THEN 1
    WHEN c.name LIKE '$dataString%' THEN 2
    WHEN c.name LIKE '%$dataString' THEN 4
    ELSE 3
  END
 LIMIT 20";

    $result = mysqli_query($con,$query);
    $total = mysqli_num_rows($result);
    $list = '<ul class="searchResgeo"><li><a href="#" class="title selectme" data-id="" data-name="" data-type="">Any City</span></a></li>';
    if ($total > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $cityid = $row['id'];
            $cityname = $row['name'];
            $stateid = $row['subadmin1_code'];
            $statename = $row['statename'];

            $list .= '<li><a href="#" class="title selectme" data-id="'.$cityid.'" data-name="'.$cityname.'" data-type="city">'.$cityname.', <span class="color-9">'.$statename.'</span></a></li>';
        }
        $list .= '</ul>';
        echo $list;
    }
    else{
        echo '<ul class="searchResgeo"><li><span class="noresult">No results found</span></li>';
    }
}

function searchCityStateCountry($con,$config)
{
    $dataString = isset($_POST['dataString']) ? $_POST['dataString'] : "";
    $sortname = check_user_country($config);

    $query = "SELECT c.id, c.name, c.subadmin1_code, s.name AS statename
FROM `".$config['db']['pre']."cities` AS c
INNER JOIN `".$config['db']['pre']."subadmin1` AS s ON s.code = c.subadmin1_code
 WHERE c.name like '%$dataString%' and c.country_code = '$sortname'
 ORDER BY
  CASE
    WHEN c.name = '$dataString' THEN 1
    WHEN c.name LIKE '$dataString%' THEN 2
    WHEN c.name LIKE '%$dataString' THEN 4
    ELSE 3
  END
 LIMIT 20";

    $result = mysqli_query($con,$query);
    $total = mysqli_num_rows($result);
    $list = '<ul class="searchResgeo">';
    if ($total > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $cityid = $row['id'];
            $cityname = $row['name'];
            $stateid = $row['subadmin1_code'];
            $countryid = $sortname;
            $statename = $row['statename'];

            $list .= '<li><a href="#" class="title selectme" data-cityid="'.$cityid.'" data-stateid="'.$stateid.'"data-countryid="'.$countryid.'" data-name="'.$cityname.', '.$statename.'">'.$cityname.', <span class="color-9">'.$statename.'</span></a></li>';
        }
        $list .= '</ul>';
        echo $list;
    }
    else{
        echo '<ul class="searchResgeo"><li><span class="noresult">No results found</span></li>';
    }
}

function hideItem($con,$config)
{
    $id = $_POST['id'];
    if (trim($id) != '') {
        $query = "SELECT status FROM ".$config['db']['pre']."product WHERE id='" . $id . "' LIMIT 1";
        $query_result = mysqli_query($con, $query);
        $info = mysqli_fetch_assoc($query_result);
        $status = $info['status'];
        if($status != "pending"){
            if($status != "hide"){
                $con->query("UPDATE `".$config['db']['pre']."product` set status='hide' WHERE `id` = '".$id."' and `user_id` = '".$_SESSION['user']['id']."' ");
                echo 1;
            }else{
                $con->query("UPDATE `".$config['db']['pre']."product` set status='active' WHERE `id` = '".$id."' and `user_id` = '".$_SESSION['user']['id']."' ");
                echo 2;
            }
        }else{
            echo 0;
        }
        die();
    } else {
        echo 0;
        die();
    }

}

function removeAdImg($con,$config){
    $id = $_POST['id'];
    $img = $_POST['img'];


    $sql = "SELECT screen_shot FROM `".$config['db']['pre']."product` WHERE `id` = '" . $id . "' LIMIT 1";
    if ($result = $con->query($sql)) {
        while ($row = mysqli_fetch_assoc($result)) {
            $screen = "";
            $uploaddir =  "storage/products/";
            $screen_sm = explode(',',$row['screen_shot']);
            $count = 0;
            foreach ($screen_sm as $value)
            {
                $value = trim($value);

                if($value == $img){
                    //Delete Image From Storage ----
                    $filename1 = $uploaddir.$value;
                    if(file_exists($filename1)){
                        $filename1 = $uploaddir.$value;
                        $filename2 = $uploaddir."small_".$value;
                        unlink($filename1);
                        unlink($filename2);
                    }
                }
                else{
                    if($count == 0){
                        $screen .= $value;
                    }else{
                        $screen .= ",".$value;
                    }
                    $count++;
                }
            }
        }
        $sql2 = "UPDATE `".$config['db']['pre']."product` set screen_shot='".$screen."' WHERE `id` = '" . $id . "' LIMIT 1";
        mysqli_query($con,$sql2);

        echo 1;
        die();
    }
    else{
        echo 0;
        die();
    }





}

function setFavAd($con,$config)
{
    $dupesql = "SELECT 1 FROM `".$config['db']['pre']."favads` where (user_id = '".$_POST['userId']."' and product_id = '".$_POST['id']."') limit 1";

    $duperaw = $con->query($dupesql);

    if (mysqli_num_rows($duperaw) == 0) {
        $sql = "INSERT INTO `".$config['db']['pre']."favads` set user_id = '".$_POST['userId']."', product_id = '".$_POST['id']."'";
        $result = $con->query($sql);
        if ($result)
            echo 1;
        else
            echo 0;
    }
    else{
        $sql = "DELETE FROM `".$config['db']['pre']."favads` WHERE `user_id` = '" . $_POST['userId'] . "' AND `product_id` ='" . $_POST['id'] . "'";
        $result = $con->query($sql);
        if ($result)
            echo 2;
        else
            echo 0;
    }
    die();
}

function removeFavAd($con,$config)
{
    $sql = "DELETE FROM `".$config['db']['pre']."favads` WHERE `user_id` = '" . validate_input($_POST['userId']) . "' AND `product_id` ='" . validate_input($_POST['id']) . "'";
    $result = $con->query($sql);
    if ($result)
        echo 1;
    else
        echo 0;

    die();
}

function deleteMyAd($con,$config)
{
    if(isset($_POST['id']))
    {
        $sql2 = "SELECT screen_shot FROM `".$config['db']['pre']."product` WHERE `id` = '" . $_POST['id'] . "' AND `user_id` = '" . $_SESSION['user']['id'] . "' LIMIT 1";

        if ($result = $con->query($sql2)) {
            $row = mysqli_fetch_assoc($result);

            $uploaddir =  "storage/products/";
            $screen_sm = explode(',',$row['screen_shot']);
            foreach ($screen_sm as $value)
            {
                $value = trim($value);
                //Delete Image From Storage ----
                $filename1 = $uploaddir.$value;
                if(file_exists($filename1)){
                    $filename1 = $uploaddir.$value;
                    $filename2 = $uploaddir."small_".$value;
                    unlink($filename1);
                    unlink($filename2);
                }
            }

            $sql = "DELETE FROM `".$config['db']['pre']."product` WHERE `id` = '" . $_POST['id'] . "' AND `user_id` = '" . $_SESSION['user']['id'] . "' LIMIT 1";
            mysqli_query($con,$sql);
        }

        echo 1;
        die();
    }else {
        echo 0;
        die();
    }

}

function deleteResumitAd($con,$config)
{
    if(isset($_POST['id']))
    {
        $sql = "SELECT screen_shot FROM `".$config['db']['pre']."product` WHERE `id` = '" . $_POST['id'] . "' AND `user_id` = '" . $_SESSION['user']['id'] . "' LIMIT 1";

        $sql2 = "SELECT screen_shot FROM `".$config['db']['pre']."product_resubmit` WHERE `id` = '" . $_POST['id'] . "' AND `user_id` = '" . $_SESSION['user']['id'] . "' LIMIT 1";


        if ($result = $con->query($sql)) {
            $row = mysqli_fetch_assoc($result);

            $result2 = $con->query($sql2);
            $row2 = mysqli_fetch_assoc($result2);

            $uploaddir =  "storage/products/";
            $screen_sm = explode(',',$row['screen_shot']);
            $re_screen = explode(',',$row2['screen_shot']);

            $arr = array_diff($re_screen,$screen_sm);

            foreach ($arr as $value)
            {
                $value = trim($value);

                //Delete Image From Storage ----
                $filename1 = $uploaddir.$value;
                if(file_exists($filename1)){
                    $filename1 = $uploaddir.$value;
                    $filename2 = $uploaddir."small_".$value;
                    unlink($filename1);
                    unlink($filename2);
                }
            }

            $sql = "DELETE FROM `".$config['db']['pre']."product_resubmit` WHERE `product_id` = '" . $_POST['id'] . "' AND `user_id` = '" . $_SESSION['user']['id'] . "' LIMIT 1";
            mysqli_query($con,$sql);
        }

        echo 1;
        die();
    }else {
        echo 0;
        die();
    }

}

function getsubcatbyid($con,$config)
{
    $id = isset($_POST['catid']) ? $_POST['catid'] : 0;
    $selectid = isset($_POST['selectid']) ? $_POST['selectid'] : "";

    $query = "SELECT * FROM `" . $config['db']['pre'] . "catagory_sub` WHERE main_cat_id = " . $id;
    if ($result = $con->query($query)) {

        while ($row = mysqli_fetch_assoc($result)) {
            $name = $row['sub_cat_name'];
            $sub_id = $row['sub_cat_id'];
            $photo_show = $row['photo_show'];
            $price_show = $row['price_show'];
            if($selectid == $sub_id){
                $selected_text = "selected";
            }
            else{
                $selected_text = "";
            }
            echo '<option value="'.$sub_id.'" data-photo-show="'.$photo_show.'" data-price-show="'.$price_show.'" '.$selected_text.'>'.$name.'</option>';
        }
    }else{
        echo 0;
    }
    die();
}

function getsubcatbyidList($con,$config)
{
    $id = isset($_POST['catid']) ? $_POST['catid'] : 0;
    $selectid = isset($_POST['selectid']) ? $_POST['selectid'] : "";

    $query = "SELECT * FROM `" . $config['db']['pre'] . "catagory_sub` WHERE main_cat_id = " . $id;
    if ($result = $con->query($query)) {

        while ($row = mysqli_fetch_assoc($result)) {
            $name = $row['sub_cat_name'];
            $sub_id = $row['sub_cat_id'];
            $photo_show = $row['photo_show'];
            $price_show = $row['price_show'];
            if($selectid == $sub_id){
                $selected_text = "link-active";
            }
            else{
                $selected_text = "";
            }

            if($config['lang_code'] != 'en'){
                $subcat = get_category_translation("sub",$row['sub_cat_id']);
                $name = $subcat['title'];
            }else{
                $name = $row['sub_cat_name'];
            }

            echo '<li data-ajax-subcatid="'.$sub_id.'" data-photo-show="'.$photo_show.'" data-price-show="'.$price_show.'" class="'.$selected_text.'"><a href="#">'.$name.'</a></li>';
        }

    }else{
        echo 0;
    }
    die();
}

function getCustomFieldByCatID($con,$config)
{
    global $lang;
    $maincatid = isset($_POST['catid']) ? $_POST['catid'] : 0;
    $subcatid = isset($_POST['subcatid']) ? $_POST['subcatid'] : 0;

    if ($maincatid > 0) {
        $custom_fields = get_customFields_by_catid($config,$con,$maincatid,$subcatid);
        $showCustomField = (count($custom_fields) > 0) ? 1 : 0;
    } else {
        die();
    }
    $tpl = '';
    if ($showCustomField) {
        foreach ($custom_fields as $row) {
            $id = $row['id'];
            $name = $row['title'];
            $type = $row['type'];
            $required = $row['required'];

            if($type == "text-field"){
                $lookFront = $row['textbox'];
                $tpl .= '<div class="row form-group">
                            <label class="col-sm-3 label-title">'.$name.' '.($required === "1" ? '<span class="required">*</span>' : "").'</label>
                            <div class="col-sm-9">
                                '.$lookFront.'
                            </div>
                        </div>';
            }
            elseif($type == "textarea"){
                $lookFront = $row['textarea'];
                $tpl .= '<div class="row form-group">
                                <label class="col-sm-3 label-title">'.$name.' '.($required === "1" ? '<span class="required">*</span>' : "").'</label>
                                <div class="col-sm-9">
                                    '.$lookFront.'
                                </div>
                            </div>';
            }
            elseif($type == "radio-buttons"){
                $lookFront = $row['radio'];
                $tpl .= '<div class="row form-group">
                                <label class="col-sm-3 label-title">'.$name.' '.($required === "1" ? '<span class="required">*</span>' : "").'</label>
                                <div class="col-sm-9">'.$lookFront.'</div>
                            </div>';
            }
            elseif($type == "checkboxes"){
                $lookFront = $row['checkboxBootstrap'];
                $tpl .= '<div class="row form-group">
                                <label class="col-sm-3 label-title">'.$name.' '.($required === "1" ? '<span class="required">*</span>' : "").'</label>
                                <div class="col-sm-9">'.$lookFront.'</div>
                            </div>';
            }
            elseif($type == "drop-down"){
                $lookFront = $row['selectbox'];
                $tpl .= '<div class="row form-group">
                                <label class="col-sm-3 label-title">'.$name.' '.($required === "1" ? '<span class="required">*</span>' : "").'</label>
                                <div class="col-sm-9">
                                    <select class="form-control" name="custom['.$id.']" '.$required.'>
                                        <option value="" selected>'.$lang['SELECT'].' '.$name.'</option>
                                        '.$lookFront.'
                                    </select>
                                </div>
                            </div>';
            }
        }
        echo $tpl;
        die();
    } else {
        echo 0;
        die();
    }
}

function getlocHomemap($con,$config)
{
    $appr = 'active';

    if(isset($_GET['serachStr'])){
        $serachStr = $_GET['serachStr'];
    }
    else{
        $serachStr = '';
    }
    /*if(isset($_GET['location'])){
        $location = $_GET['location'];
    }
    else{
        $location = '';
    }*/
    if(isset($_GET['country'])){
        $country = $_GET['country'];
    }
    else{
        $country = '';
    }
    if(isset($_GET['state'])){
        $state = $_GET['state'];
    }
    else{
        $state = '';
    }
    if(!empty($_GET['city'])){
        $city = $_GET['city'];
    }
    else{
        if(!empty($_GET['locality'])){
            $city = $_GET['locality'];
        }else{
            $city = '';
        }
    }
    if(isset($_GET['searchBox'])){
        $searchBox = $_GET['searchBox'];
    }
    else{
        $searchBox = '';
    }

    if(isset($_GET['catid'])){
        $catid = $_GET['catid'];
    }
    else{
        $catid = '';
    }


    $where = "";



    if ($city != '') {

        if ($serachStr != '') {
            $where .= "AND p.product_name LIKE '%$serachStr%'";
        }

        if ($searchBox != '') {
            $where .= " AND p.category = '$searchBox' ";
        }

        if ($catid != '') {
            $where .= " AND p.sub_category = '$catid' ";
        }

        $query = "SELECT p.*,c.name AS cityname, s.name AS statename, a.name AS countryname
        FROM `".$config['db']['pre']."countries` AS a
        INNER JOIN `".$config['db']['pre']."states` AS s ON s.country_id = a.id
        INNER JOIN `".$config['db']['pre']."cities` AS c ON c.state_id = s.id
        INNER JOIN `".$config['db']['pre']."product` AS p ON p.city = c.id Where c.name = '$city' and p.status = 'active' $where";
    }
    else{

        if ($serachStr != '') {
            $where .= "AND product_name LIKE '%$serachStr%'";
        }

        if ($searchBox != '') {
            $where .= " AND category = '$searchBox' ";
        }

        if ($catid != '') {
            $where .= " AND sub_category = '$catid' ";
        }

        $query = "SELECT * FROM `".$config['db']['pre']."product`  WHERE `status` = '$appr' $where ";
    }

    $query_result = mysqli_query ($con, $query);

    $data = array();
    $i = 0;
    if ($query_result->num_rows > 0) {

        while ($row = mysqli_fetch_array($query_result))
            $results[] = $row;

        foreach($results as $result){
            $id = $result['id'];
            $featured = $result['featured'];
            $urgent = $result['urgent'];
            $highlight = $result['highlight'];
            $title = $result['product_name'];
            $cat = $result['category'];
            $price = $result['price'];
            $pics = $result['screen_shot'];
            $location = $result['location'];
            $latlong = $result['latlong'];
            $desc = $result['description'];
            $url = $config['site_url'].$id;

            $caticonquery = "SELECT * FROM `".$config['db']['pre']."catagory_main`  WHERE `cat_id` = '$cat' LIMIT 1";
            $caticonres = mysqli_query ($con, $caticonquery);
            $fetch = mysqli_fetch_array($caticonres);
            $catIcon = $fetch['icon'];
            $catname = $fetch['cat_name'];

            $map = explode(',', $latlong);
            $lat = $map[0];
            $long = $map[1];

            $p = explode(',', $pics);
            $pic = $p[0];
            $pic = 'storage/products/'.$pic;

            $data[$i]['id'] = $id;
            $data[$i]['latitude'] = $lat;
            $data[$i]['longitude'] = $long;
            $data[$i]['featured'] = $featured;
            $data[$i]['title'] = $title;
            $data[$i]['location'] = $location;
            $data[$i]['category'] = $catname;
            $data[$i]['cat_icon'] = $catIcon;
            $data[$i]['marker_image'] = $pic;
            $data[$i]['url'] = $url;
            $data[$i]['description'] = $desc;


            $i++;
        }
        echo json_encode($data);
    } else {
        echo '0';
    }
    die();
}

function openlocatoionPopup($con,$config)
{
    /*$query = "SELECT a.*, b.name AS cat FROM `".$config['db']['pre']."product` AS a INNER JOIN `".$config['db']['pre']."category` AS b ON a.category = b.id WHERE a.id = '" . $_POST['id'] . "' LIMIT 1";*/
    $query = "SELECT * FROM `".$config['db']['pre']."product` WHERE id = '" . $_POST['id'] . "' LIMIT 1";
    $query_result = mysqli_query ($con, $query);
    $data = array();
    $i = 0;
    if ($query_result->num_rows > 0) {
        while ($result = mysqli_fetch_array($query_result)) {
            $id = $result['id'];
            $featured = $result['featured'];
            $urgent = $result['urgent'];
            $highlight = $result['highlight'];
            $title = $result['product_name'];
            $cat = $result['category'];
            $price = $result['price'];
            $pics = $result['screen_shot'];
            $location = $result['location'];
            $latlong = $result['latlong'];
            $desc = $result['description'];
            $url = $config['site_url']."ad/".$id;

            $caticonquery = "SELECT * FROM `".$config['db']['pre']."catagory_main`  WHERE `cat_id` = '$cat' LIMIT 1";
            $caticonres = mysqli_query ($con, $caticonquery);
            $fetch = mysqli_fetch_array($caticonres);
            $catIcon = $fetch['icon'];
            $catname = $fetch['cat_name'];

            $map = explode(',', $latlong);
            $lat = $map[0];
            $long = $map[1];

            $p = explode(',', $pics);
            $pic = $p[0];
            $pic = 'storage/products/'.$pic;


            echo '<div class="item gmapAdBox" data-id="' . $id . '" style="margin-bottom: 0px;">
                    <a href="' . $url . '" style="display: block;position: relative;">
                     <div class="card small">
                        <div class="card-image waves-effect waves-block waves-light">
                          <img class="activator" src="' . $pic . '">
                        </div>
                        <div class="card-content">
                            <div class="label label-default">' . $catname . '</div>
                          <span class="card-title activator grey-text text-darken-4 mapgmapAdBoxTitle">' . $title . '</span>
                          <p class="mapgmapAdBoxLocation">' . $location . '</p>
                        </div>
                      </div>

                    </a>
                </div>';

        }
    } else {
        echo false;
    }
    die();
}


function listingpro_suggested_search()
{
    global $config,$con,$cats;

    $searchmode = "titlematch";
    $qString      = '';
    $qString      = $_POST['tagID'];
    $qString      = strtolower($qString);
    $output       = array();
    $TAGOutput    = array();
    $CATOutput    = array();
    $TagCatOutput = array();
    $TitleOutput  = array();
    $lpsearchMode = "titlematch";
    $catIcon_type = "icon";

    if( isset($searchmode) ){
        if( !empty($searchmode) && $searchmode=="keyword" ){
            $lpsearchMode = "keyword";
        }
    }

    if (empty($qString)) {

        $categories = get_maincategory($config);
        $catIcon    = '';
        foreach ($categories as $cat) {
            $catIcon = $cat['icon'];
            if (!empty($catIcon)) {
                if($catIcon_type == "image")
                    $catIcon = '<img src="' . $catIcon . '" />';
                else
                    $catIcon = '<i class="' . $catIcon . '" ></i>';
            }
            $cats[$cat['id']] = '<li class="lp-default-cats" data-catid="' . $cat['id'] . '">' . $catIcon . '<span class="lp-s-cat">' . $cat['name'] . '</span></li>';
        }
        $output           = array(
            'tag' => '',
            'cats' => $cats,
            'tagsncats' => '',
            'titles' => '',
            'more' => ''
        );
        $query_suggestion = json_encode(array(
            "tagID" => $qString,
            "suggestions" => $output
        ));
        die($query_suggestion);
    }
    else {
        //$catTerms = get_maincategory($config);


        if( $lpsearchMode == "keyword" ){

            $sql = "SELECT DISTINCT *
FROM `".$config['db']['pre']."catagory_main`
 WHERE cat_name like '%$qString%'
 ORDER BY
  CASE
    WHEN cat_name = '$qString' THEN 1
    WHEN cat_name LIKE '$qString%' THEN 2
    ELSE 3
  END ";
        }else{

            $sql = "SELECT DISTINCT *
FROM `".$config['db']['pre']."catagory_main`
 WHERE cat_name like '$qString%'
 ORDER BY
  CASE
    WHEN cat_name = '$qString' THEN 1
    WHEN cat_name LIKE '$qString%' THEN 2
    ELSE 3
  END ";

        }

        $query_result = mysqli_query($con,$sql);
        while($info = mysqli_fetch_assoc($query_result)) {
            $catTerms[$info['cat_id']]['id'] = $info['cat_id'];
            $catTerms[$info['cat_id']]['icon'] = $info['icon'];

            if ($config['lang_code'] != 'en') {
                $maincat = get_category_translation("main", $info['cat_id']);
                $catTerms[$info['cat_id']]['name'] = $maincat['title'];
                $catTerms[$info['cat_id']]['slug'] = $maincat['slug'];
            } else {
                $catTerms[$info['cat_id']]['name'] = $info['cat_name'];
                $catTerms[$info['cat_id']]['slug'] = $info['slug'];
            }
        }


        if( $lpsearchMode == "keyword" ){

            $sql = "SELECT DISTINCT *
FROM `".$config['db']['pre']."catagory_sub`
 WHERE sub_cat_name like '%$qString%'
 ORDER BY
  CASE
    WHEN sub_cat_name = '$qString' THEN 1
    WHEN sub_cat_name LIKE '$qString%' THEN 2
    ELSE 3
  END ";
        }else{

            $sql = "SELECT DISTINCT *
FROM `".$config['db']['pre']."catagory_sub`
 WHERE sub_cat_name like '$qString%'
 ORDER BY
  CASE
    WHEN sub_cat_name = '$qString' THEN 1
    WHEN sub_cat_name LIKE '$qString%' THEN 2
    ELSE 3
  END ";

        }

        $query_result = mysqli_query($con,$sql);
        while($info = mysqli_fetch_assoc($query_result)) {
            $subcatTerms[$info['sub_cat_id']]['id'] = $info['sub_cat_id'];

            if($config['lang_code'] != 'en'){
                $subcategory = get_category_translation("sub",$info['sub_cat_id']);

                $subcatTerms[$info['sub_cat_id']]['name'] = $subcategory['title'];
                $subcatTerms[$info['sub_cat_id']]['slug'] = $subcategory['slug'];
            }else{
                $subcatTerms[$info['sub_cat_id']]['name'] = $info['sub_cat_name'];
                $subcatTerms[$info['sub_cat_id']]['slug'] =  $info['slug'];
            }

            $get_main = get_maincat_by_id($config,$info['main_cat_id']);
            $subcatTerms[$info['sub_cat_id']]['main_cat_name'] = $get_main['cat_name'];
            $subcatTerms[$info['sub_cat_id']]['main_cat_icon'] = $get_main['icon'];
            $subcatTerms[$info['sub_cat_id']]['main_cat_id'] = $info['main_cat_id'];
        }
        //$subcatTerms = get_subcategories();

        $catName  = '';
        $catIcon  = '';
        if (!empty($catTerms) && !empty($subcatTerms)) {
            foreach ($catTerms as $cat) {
                $catIcon = $cat['icon'];
                if (!empty($catIcon)) {
                    if($catIcon_type == "image")
                        $catIcon = '<img src="' . $catIcon . '" />';
                    else
                        $catIcon = '<i class="' . $catIcon . '" ></i>';
                }

                $catTermMatch = false;

                $catTernName  = $cat['name'];
                $catTernName  = strtolower($catTernName);
                if( $lpsearchMode == "keyword" ){
                    preg_match("/[$qString]/", "$catTernName", $lpMatches, PREG_OFFSET_CAPTURE);
                    $lpresCnt = count($lpMatches);
                    if( $lpresCnt > 0 ){
                        $catTermMatch = true;
                    }

                }else{
                    $catTermMatch = strpos($catTernName, $qString);
                }

                if ( $catTermMatch !== false ) {
                    $CATOutput[$cat['id']] = '<li class="lp-wrap-cats" data-catid="' . $cat['id'] . '">' . $catIcon . '<span class="lp-s-cat">' . $cat['name'] . '</span></li>';
                }
            }
            foreach ($subcatTerms as $subcat) {

                $tagTermMatch = false;
                $tagTernName  = strtolower($subcat['name']);

                if( $lpsearchMode == "keyword" ){
                    preg_match("/[$qString]/", "$tagTernName", $lpMatches, PREG_OFFSET_CAPTURE);
                    $lpresCnt = count($lpMatches);
                    if( $lpresCnt > 0 ){
                        $tagTermMatch = true;
                    }
                }else{
                    $tagTermMatch = strpos($tagTernName, $qString);
                }

                if ( $tagTermMatch !== false ) {
                    $TAGOutput[$subcat['id']]    = '<li class="lp-wrap-tags" data-tagid="' . $subcat['id'] . '"><span class="lp-s-tag">' . $subcat['name'] . '</span></li>';
                }
            }

        }
        else {

            if( !empty($catTerms) ){
                foreach ($catTerms as $cat) {

                    $catIcon = $cat['icon'];
                    if (!empty($catIcon)) {
                        if($catIcon_type == "image")
                            $catIcon = '<img src="' . $catIcon . '" />';
                        else
                            $catIcon = '<i class="' . $catIcon . '" ></i>';
                    }

                    $catTermMatch = false;

                    $catTernName  = $cat['name'];
                    $catTernName  = strtolower($catTernName);
                    if( $lpsearchMode == "keyword" ){
                        preg_match("/[$qString]/", "$catTernName", $lpMatches, PREG_OFFSET_CAPTURE);
                        $lpresCnt = count($lpMatches);
                        if( $lpresCnt > 0 ){
                            $catTermMatch = true;
                        }

                    }else{
                        $catTermMatch = strpos($catTernName, $qString);
                    }

                    if ( $catTermMatch !== false ) {
                        $CATOutput[$cat['id']] = '<li class="lp-wrap-cats" data-catid="' . $cat['id'] . '">' . $catIcon . '<span class="lp-s-cat">' . $cat['name'] . '</span></li>';
                    }
                }
            }

            if( !empty($subcatTerms) ) {

                foreach ($subcatTerms as $subcat) {

                    $catIcon = $subcat['main_cat_icon'];
                    if (!empty($catIcon)) {
                        if($catIcon_type == "image")
                            $catIcon = '<img class="lp-s-caticon" src="' . $catIcon . '" />';
                        else
                            $catIcon = '<i class="lp-s-caticon ' . $catIcon . '"  ></i>';
                    }
                    $tagTermMatch = false;
                    $tagTernName  = strtolower($subcat['name']);

                    if( $lpsearchMode == "keyword" ){
                        preg_match("/[$qString]/", "$tagTernName", $lpMatches, PREG_OFFSET_CAPTURE);
                        $lpresCnt = count($lpMatches);
                        if( $lpresCnt > 0 ){
                            $tagTermMatch = true;
                        }
                    }else{
                        $tagTermMatch = strpos($tagTernName, $qString);
                    }

                    if ( $tagTermMatch !== false ) {
                        //$TAGOutput[$subcat['id']]    = '<li class="lp-wrap-tags" data-tagid="' . $subcat['id'] . '"><span class="lp-s-tag">' . $subcat['name'] . '</span></li>';

                        $TagCatOutput[] = '<li class="lp-wrap-catsntags" data-tagid="' . $subcat['id'] . '" data-catid="' . $subcat['main_cat_id'] . '">' . $catIcon . '<span class="lp-s-tag">' . $subcat['name'] . '</span><span> in </span><span class="lp-s-cat">' . $subcat['main_cat_name'] . '</span></li>';
                    }
                }

            }
        }

        $machTitles = false;
        $country_code = check_user_country($config);

        if( $lpsearchMode == "keyword" ){

            $sql = "SELECT DISTINCT *
FROM `".$config['db']['pre']."product`
 WHERE product_name like '%$qString%' and status = 'active' and country = '".$country_code."'
 ORDER BY
  CASE
    WHEN product_name = '$qString' THEN 1
    WHEN product_name LIKE '$qString%' THEN 2
    ELSE 3
  END ";
        }else{

            $sql = "SELECT DISTINCT *
FROM `".$config['db']['pre']."product`
 WHERE product_name like '$qString%' and status = 'active' and country = '".$country_code."'
 ORDER BY
  CASE
    WHEN product_name = '$qString' THEN 1
    WHEN product_name LIKE '$qString%' THEN 2
    ELSE 3
  END ";

        }


        $result = $con->query($sql);
        if (mysqli_num_rows($result) > 0) {
            $machTitles = true;      // output data of each row
            while ($info = $result->fetch_assoc()) {
                $listTitle  = $info['product_name'];
                $listTitle  = strtolower($listTitle);
                $pro_url = create_slug($info['product_name']);
                $permalink = $config['site_url'].'ad/' . $info['id'] . '/'.$pro_url;
                $cityname = get_cityName_by_id($config,$info['city']);

                $picture =   explode(',' ,$info['screen_shot']);
                if (!empty($picture[0])) {
                    $item[$info['id']]['picture'] = $picture[0];
                }else{
                    $item[$info['id']]['picture'] = "default.png";
                }

                $listThumb = '';
                $picture =   explode(',' ,$info['screen_shot']);
                if (!empty($picture[0])) {
                    if(file_exists("../storage/products/thumb/".$picture[0])){
                        $image = $config['site_url']."storage/products/thumb/" . $picture[0];
                    }else{
                        $image = $config['site_url']."storage/products/thumb/default.png";
                    }
                    $listThumb = "<img src='".$image."' width='50' height='50'/>";
                } else {
                    $listThumb = '<img src="'.$config['site_url'].'storage/products/thumb/default.png" alt="" width="50" height="50">';
                }

                $TitleOutput[] = '<li class="lp-wrap-title" data-url="' . $permalink . '">' . $listThumb . '<span class="lp-s-title"><a href="' . $permalink . '">' . $listTitle . ' <span class="lp-loc">' . $cityname . '</span></a></span></li>';

            }
        }



        $TAGOutput    = array_unique($TAGOutput);
        $CATOutput    = array_unique($CATOutput);
        $TagCatOutput = array_unique($TagCatOutput);
        $TitleOutput  = array_unique($TitleOutput);
        if ((!empty($TAGOutput) && count($TAGOutput) > 0) || (!empty($CATOutput) && count($CATOutput) > 0) || (!empty($TagCatOutput) && count($TagCatOutput) > 0) || (!empty($TitleOutput) && count($TitleOutput) > 0)) {
            $output = array(
                'tag' => $TAGOutput,
                'cats' => $CATOutput,
                'tagsncats' => $TagCatOutput,
                'titles' => $TitleOutput,
                'more' => '',
                'matches' => $machTitles
            );
        } else {
            $moreResult = array();
            $mResults   = '<strong>More results for</strong>';
            $mResults .= $qString;
            $moreResult[] = '<li class="lp-wrap-more-results" data-moreval="' . $qString . '">' . $mResults . '</li>';
            $output       = array(
                'tag' => '',
                'cats' => '',
                'tagsncats' => '',
                'titles' => '',
                'more' => $moreResult
            );
        }
        $query_suggestion = json_encode(array(
            "tagID" => $qString,
            "suggestions" => $output
        ));
        die($query_suggestion);
    }
}
?>