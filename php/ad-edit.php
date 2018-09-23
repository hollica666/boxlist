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

require_once('plugins/watermark/watermark.php');

$mysqli = db_connect($config);
sec_session_start();

if (isset($_GET['action'])) {
    if ($_GET['action'] == "edit_ad") {
        ajax_edit_advertise($mysqli);
    }
}

function ajax_edit_advertise($mysqli)
{
    global $config, $lang, $link;
    $item_screen = "";
    if (!checkloggedin($config)) {
        return false;
    }

    if(!check_valid_author($config,$_POST['product_id'])){
        return false;
    }

    if(isset($_POST['submit'])) {
        $errors = array();

        if (empty($_POST['subcatid']) or empty($_POST['catid'])) {
            $errors[]['message'] = $lang['CAT_REQ'];
        }
        if (empty($_POST['title'])) {
            $errors[]['message'] = $lang['ADTITLE_REQ'];
        }
        if (empty($_POST['content'])) {
            $errors[]['message'] = $lang['DESC_REQ'];
        }
        if (empty($_POST['city'])) {
            $errors[]['message'] = $lang['CITY_REQ'];
        }
        if (!empty($_POST['price'])) {
            if (!is_numeric($_POST['price'])) {
                $errors[]['message'] = $lang['PRICE_MUST_NO'];
            }
        }

        /*IF : USER GO TO PEMIUM POST*/
        $urgent = isset($_POST['urgent']) ? 1 : 0;
        $featured = isset($_POST['featured']) ? 1 : 0;
        $highlight = isset($_POST['highlight']) ? 1 : 0;

        $payment_req = "";
        if (isset($_POST['urgent'])) {
            if (!isset($_POST['payment_id'])) {
                $payment_req = $lang['PAYMENT_METHOD_REQ'];
            }
        }
        if (isset($_POST['featured'])) {
            if (!isset($_POST['payment_id'])) {
                $payment_req = $lang['PAYMENT_METHOD_REQ'];
            }
        }
        if (isset($_POST['highlight'])) {
            if (!isset($_POST['payment_id'])) {
                $payment_req = $lang['PAYMENT_METHOD_REQ'];
            }
        }
        if (!empty($payment_req))
            $errors[]['message'] = $payment_req;

        /*IF : USER GO TO PEMIUM POST*/

        if (!count($errors) > 0) {
            if (isset($_POST['item_screen']) && count($_POST['item_screen']) > 0) {
                $valid_formats = array("jpg", "jpeg", "png"); // Valid image formats
                $countScreen = 0;
                $item_screen = "";
                foreach ($_POST['item_screen'] as $name) {
                    $filename = stripslashes($name);
                    $ext = getExtension($filename);
                    $ext = strtolower($ext);
                    if (!empty($filename)) {
                        //File extension check
                        if (in_array($ext, $valid_formats)) {
                            //Valid File extension check

                        } else {
                            $errors[]['message'] = $lang['ONLY_JPG_ALLOW'];
                        }
                        if ($countScreen == 0)
                            $item_screen = $filename;
                        elseif ($countScreen >= 1)
                            $item_screen = $item_screen . "," . $filename;
                        $countScreen++;
                    }
                }
            }
        }


        if (!count($errors) > 0) {

            if (checkloggedin($config)) {
                $price = $_POST['price'];
                $phone = $_POST['phone'];
                $price = isset($_POST['price']) ? $_POST['price'] : 0;
                $phone = isset($_POST['phone']) ? $_POST['phone'] : 0;

                if(empty($_POST['price'])){
                    $price = 0;
                }
                $negotiable = isset($_POST['negotiable']) ? 1 : 0;
                $hide_phone = isset($_POST['hide_phone']) ? 1 : 0;
                $cityid = $_POST['city'];

                if($config['post_desc_editor'] == 1)
                    $description = addslashes($_POST['content']);
                else
                    $description = validate_input($_POST['content'],true);

                $timenow = date('Y-m-d H:i:s');
                $citydata = get_cityDetail_by_id($config, $cityid);
                $country = $citydata['country_code'];
                $state = $country . "." . $citydata['subadmin1_code'];
                if(isset($_POST['tags'])){
                    $tags = $_POST['tags'];
                }else{
                    $tags = '';
                }
                if(isset($_POST['location'])){
                    $location = $_POST['location'];
                }else{
                    $location = '';
                }
                $mapLat = $_POST['latitude'];
                $mapLong = $_POST['longitude'];
                $latlong = $mapLat . "," . $mapLong;
                $slug = create_post_slug($config,$_POST['title']);

                $sql = "SELECT status,screen_shot FROM " . $config['db']['pre'] . "product where id = '" . $_POST['product_id'] . "' limit 1";
                $result = mysqli_query(db_connect($config), $sql);
                $info = mysqli_fetch_assoc($result);


                $item_status = $info['status'];
                $screen_shot = $info['screen_shot'];

                if($item_status == "pending" or $config['post_auto_approve'] == 1){

                    $sql = "UPDATE " . $config['db']['pre'] . "product set
                    user_id = '" . $_SESSION['user']['id'] . "',
                    product_name = '" . validate_input($_POST['title']) . "',
                    slug = '" . validate_input($slug) . "',
                    category = '" . validate_input($_POST['catid']) . "',
                    sub_category = '" . validate_input($_POST['subcatid']) . "',
                    description = '" . $description . "',
                    price = '" . validate_input($price) . "',
                    negotiable = '" . validate_input($negotiable) . "',
                    phone = '" . validate_input($phone) . "',
                    hide_phone = '" . validate_input($hide_phone) . "',
                    location = '" . validate_input($location) . "',
                    city = '" . validate_input($_POST['city']) . "',
                    state = '" . validate_input($state) . "',
                    country = '" . validate_input($country) . "',
                    latlong = '$latlong',
                    screen_shot = '".$item_screen."',
                    tag = '" . validate_input($tags) . "',
                    updated_at = '$timenow'
                    WHERE id = '".validate_input($_POST['product_id'])."'
                    ";
                }
                elseif($item_status == "active" or $item_status == "softreject" or $item_status == "hide")
                {
                    $sql = "INSERT into ".$config['db']['pre']."product_resubmit set
                    product_id = '".$_POST['product_id']."',
                    user_id = '".$_SESSION['user']['id']."',
                    product_name = '".$_POST['title']."',
                    category = '".$_POST['catid']."',
                    sub_category = '".$_POST['subcatid']."',
                    description = '".$description."',
                    price = '".$price."',
                    negotiable = '".$negotiable."',
                    phone = '".$phone."',
                    hide_phone = '".$hide_phone."',
                    location = '".$location."',
                    city = '".$_POST['city']."',
                    state = '" . $state . "',
                    country = '" . $country . "',
                    latlong = '$latlong',
                    screen_shot = '".$item_screen."',
                    tag = '".$tags."',
                    created_at = '$timenow',
                    comments = '".$_POST['comments']."'
                    ";
                }

                $mysqli->query($sql) or $mysqli->error;
                $product_id = $_POST['product_id'];

                add_post_customField_data($_POST['catid'], $_POST['subcatid'],$product_id);

                $amount = 0;
                $trans_desc = "Make Ad ";
                $urgent_project_fee = $config['urgent_fee'];
                $featured_project_fee = $config['featured_fee'];
                $highlight_project_fee = $config['highlight_fee'];
                $premium_tpl = "";

                if ($featured == 1) {
                    $amount = $featured_project_fee;
                    $trans_desc = $trans_desc . " Featured ";
                    $premium_tpl .= ' <div class="ModalPayment-paymentDetails">
                                            <div class="ModalPayment-label">'.$lang['FEATURED'].'</div>
                                            <div class="ModalPayment-price">
                                                <span class="ModalPayment-totalCost-price">'.$config['currency_sign'].$featured_project_fee.'</span>
                                            </div>
                                        </div>';
                }
                if ($urgent == 1) {
                    $amount = $amount + $urgent_project_fee;
                    $trans_desc = $trans_desc . " Urgent ";
                    $premium_tpl .= ' <div class="ModalPayment-paymentDetails">
                                            <div class="ModalPayment-label">'.$lang['URGENT'].'</div>
                                            <div class="ModalPayment-price">
                                                <span class="ModalPayment-totalCost-price">'.$config['currency_sign'].$urgent_project_fee.'</span>
                                            </div>
                                        </div>';
                }
                if ($highlight == 1) {
                    $amount = $amount + $highlight_project_fee;
                    $trans_desc = $trans_desc . " Highlight ";
                    $premium_tpl .= ' <div class="ModalPayment-paymentDetails">
                                            <div class="ModalPayment-label">'.$lang['HIGHLIGHT'].'</div>
                                            <div class="ModalPayment-price">
                                                <span class="ModalPayment-totalCost-price">'.$config['currency_sign'].$highlight_project_fee.'</span>
                                            </div>
                                        </div>';
                }

                if ($amount > 0) {
                    if (isset($_POST['payment_id'])) {
                        $query1 = "SELECT * FROM `" . $config['db']['pre'] . "payments` WHERE payment_id='" . $_POST['payment_id'] . "' AND payment_install='1' LIMIT 1";
                        $query_result1 = @mysqli_query($mysqli, $query1) OR error(mysqli_error($mysqli));
                        while ($info1 = @mysqli_fetch_array($query_result1)) {
                            $title = $info1['payment_title'];
                            $folder = $info1['payment_folder'];

                        }

                        $query = "INSERT INTO " . $config['db']['pre'] . "transaction set
                product_name = '" . $_POST['title'] . "',
                product_id = '" . $product_id . "',
                seller_id = '" . $_SESSION['user']['id'] . "',
                amount = '$amount',
                featured = '$featured',
                urgent = '$urgent',
                highlight = '$highlight',
                transaction_time = '" . time() . "',
                status = 'pending',
                transaction_gatway = '$folder',
                transaction_ip = '" . encode_ip($_SERVER, $_ENV) . "',
                transaction_description = '$trans_desc',
                transaction_method = 'Premium Ad'
                ";
                        $mysqli->query($query);

                        $transaction_id = $mysqli->insert_id;

                        $premium_tpl .= ' <div class="ModalPayment-totalCost">
                                            <span class="ModalPayment-totalCost-label">'.$lang['TOTAL'].': </span>
                                            <span class="ModalPayment-totalCost-price">'.$config['currency_sign'].$amount." ".$config['currency_code'].'</span>
                                        </div>';

                        $url = $link['PAYMENT']."/?i=" . $folder . "&id=" . $transaction_id;
                        $response = array();
                        $response['status'] = "success";
                        $response['ad_type'] = "package";
                        $response['redirect'] = $url;
                        $response['tpl'] = $premium_tpl;

                        echo json_encode($response, JSON_UNESCAPED_SLASHES);
                        die();
                    }
                } else {
                    unset($_POST);
                    $ad_link = $link['AD-DETAIL'] . "/" . $product_id;

                    $json = '{"status" : "success","ad_type" : "free","redirect" : "' . $ad_link . '"}';
                    echo $json;
                    die();
                }
            } else {
                $status = "error";
                $errors[]['message'] = $lang['POST_SAVE_ERROR'];
            }


        } else {
            $status = "error";
        }

        $json = '{"status" : "' . $status . '","errors" : ' . json_encode($errors, JSON_UNESCAPED_SLASHES) . '}';
        echo $json;
        die();
    }
}

if(checkloggedin($config)) {

    $status = check_item_status($config,$_GET['id']);

    $header_text = "";
    $header_note = "";
    $resubmit = "";
    if($status == "pending"){
        $header_text = $lang['EDIT-AD'];
        $resubmit = 0;
    }
    elseif($status == "active" or $status == "softreject" or $status == "hide")
    {
        if(check_valid_resubmission($config,$_GET['id'])){
            $header_text = $lang['RE-SUBISSION'];
            $header_note = $lang['RE-SUBISSION-TEXT'];
            $resubmit = 1;
        }else{
            message($lang['ALREADY-EXIST'],$lang['RESUMIT-EXIST-TEXT'],'',false);
            exit;
        }

    }else {
        error($lang['PAGENOTEXIST'], __LINE__, __FILE__, 1);
        exit;
    }


    if(check_valid_author($config,$_GET['id'])){

        global $errors, $custom_fields, $catid,$catName, $subcatid,$subcatName, $title, $description, $price, $negotiable, $phone, $hide_phone, $tags, $cityid, $mapLat, $mapLong, $seller_name, $seller_email;

        if(isset($_GET['country'])) {
            if ($_GET['country'] != ""){
                change_user_country($config,$_GET['country']);
            }
        }

        $country_code = check_user_country($config);

        $currency_info = set_user_currency($config,$country_code);
        $currency_sign = $currency_info['html_entity'];

        $query = "SELECT * FROM `".$config['db']['pre']."payments` WHERE payment_install='1' ORDER BY  payment_id";
        $query_result = @mysqli_query ($mysqli,$query) OR error(mysqli_error($mysqli));
        while ($info = @mysqli_fetch_array($query_result))
        {
            $payment_types[$info['payment_id']]['id'] = $info['payment_id'];
            $payment_types[$info['payment_id']]['title'] = $info['payment_title'];
            $payment_types[$info['payment_id']]['folder'] = $info['payment_folder'];
        }

        $total[0] = mysqli_num_rows(mysqli_query(db_connect($config), "SELECT 1 FROM " . $config['db']['pre'] . "product where id = '" . $_GET['id'] . "' limit 1"));
        $sql = "SELECT * FROM " . $config['db']['pre'] . "product where  id = '" . $_GET['id'] . "' limit 1";
        $result = mysqli_query(db_connect($config), $sql);


        if (mysqli_num_rows($result) > 0) {
            // output data of each row

            $info = mysqli_fetch_assoc($result);

            $item_id = $info['id'];
            $item_featured = $info['featured'];
            $item_urgent = $info['urgent'];
            $item_highlight = $info['highlight'];
            $catid          = $info['category'];
            $subcatid       = $info['sub_category'];
            $title          = $info['product_name'];
            $description    = de_sanitize($info['description']);
            $price          = $info['price'];
            $phone          = $info['phone'];
            $negotiable     = $info['negotiable'];
            $hide_phone     = $info['hide_phone'];
            $tags           = $info['tag'];
            $cityid         = $info['city'];

            $latlong = $info['latlong'];
            $map = explode(',', $latlong);
            $mapLat = $map[0];
            $mapLong = $map[1];

            $item_featured = $info['featured'];
            $item_urgent = $info['urgent'];
            $item_highlight = $info['highlight'];

            $maincat = get_maincat_by_id($config,$catid);
            $catName = $maincat['cat_name'];
            $subcat = get_subcat_by_id($config,$subcatid);
            $subcatName = $subcat['sub_cat_name'];

            $custom_fields = array();
            $custom_data = array();
            $customdata = array();

            $query = "SELECT field_id,field_data FROM `".$config['db']['pre']."custom_data` where product_id = '".$item_id."'";
            $query_result = mysqli_query($mysqli, $query);
            while ($row = mysqli_fetch_array($query_result))
                $customdata[] = $row;

            foreach ($customdata as $array){
                $custom_fields[] = $array['field_id'];
                $custom_data[] = $array['field_data'];
            }

            $custom_fields = get_customFields_by_catid($config, $mysqli, $catid, $subcatid,false, $custom_fields, $custom_data);

            foreach ($custom_fields as $key => $value) {
                if ($value['userent']) {
                    $custom_db_fields[$value['id']] = $value['title'];
                    $custom_db_data[$value['id']] = str_replace(',', '&#44;', $value['default']);
                }
            }

            $imagesCount = 0;
            $maxImgLength = 5;
            $screen = "";
            if($info['screen_shot'] != "")
            {
                $screen = explode(',', $info['screen_shot']);

                foreach ($screen as $value) {
                    //REMOVE SPACE FROM $VALUE ----
                    $value = trim($value);
                    if($imagesCount == 0)
                        $screen2[] = "'$value'";
                    else
                        $screen2[] = ",'$value'";
                    $imagesCount++;
                }
                $maxImgLength = 5 - $imagesCount;
                $screen = implode(' ', $screen2);
            }


            // Output to template
            $page = new HtmlTemplate ('templates/' . $config['tpl_name'] . '/ad-edit.tpl');
            $page->SetParameter ('OVERALL_HEADER', create_header($header_text));
            $page->SetParameter('ITEM_ID', $item_id);
            $page->SetLoop ('HTMLPAGE', get_html_pages($config));
            $page->SetLoop ('PAYMENT_TYPES', $payment_types);
            $page->SetLoop ('COUNTRYLIST',get_country_list($config));
            $page->SetLoop ('CATEGORY',get_maincategory($config,$catid));
            $page->SetLoop ('SUBCATEGORY',get_subcat_of_maincat($config,$catid,false,$subcatid));
            $page->SetLoop ('CUSTOMFIELDS',$custom_fields);
            $page->SetParameter ('SHOWCUSTOMFIELD', (count($custom_fields) > 0) ? 1 : 0);
            $page->SetParameter ('CATID', $catid);
            $page->SetParameter ('SUBCATID', $subcatid);
            $page->SetParameter ('CATEGORY', $catName);
            $page->SetParameter ('SUBCATEGORY', $subcatName);
            $page->SetParameter ('TITLE', $title);
            $page->SetParameter ('DESCRIPTION', $description);
            $page->SetParameter ('PRICE', $price);
            $page->SetParameter ('PHONE', $phone);
            $page->SetParameter ('NEGOTIABLE', $negotiable);
            $page->SetParameter ('HIDEPHONE', $hide_phone);
            $page->SetParameter ('TAGS', $tags);
            $page->SetParameter ('CITY', $cityid);
            $page->SetParameter ('CITYNAME', get_cityName_by_id($config,$cityid));
            $page->SetParameter ('LATITUDE', $mapLat);
            $page->SetParameter ('LONGITUDE', $mapLong);
            $page->SetParameter ('USER_COUNTRY', strtolower($country_code));
            $page->SetParameter ('SELLER_NAME', $seller_name);
            $page->SetParameter ('SELLER_EMAIL', $seller_email);
            $page->SetParameter ('USER_CURRENCY_SIGN', $currency_sign);
            $page->SetParameter('ITEM_SCREENS', $screen);
            $page->SetParameter('IMGCOUNT', $imagesCount);
            $page->SetParameter('MAXIMGLNT', $maxImgLength);
            $page->SetParameter('HEADER_TEXT', $header_text);
            $page->SetParameter('HEADER_NOTE', $header_note);
            $page->SetParameter('RESUBMIT', $resubmit);
            $page->SetParameter('FEATURED', $item_featured);
            $page->SetParameter('URGENT', $item_urgent);
            $page->SetParameter('HIGHLIGHT', $item_highlight);
            $page->SetParameter ('PAGE_TITLE', $lang['POST-AD']);
            $page->SetParameter('LANGUAGE_DIRECTION', get_current_lang_direction($config));
            $page->SetParameter ('OVERALL_FOOTER', create_footer());
            $page->CreatePageEcho();
        }
        else {
            error($lang['PAGENOTEXIST'], __LINE__, __FILE__, 1);
            exit;
        }


    }
    else{
        error($lang['PAGENOTEXIST'], __LINE__, __FILE__, 1);
        exit;
    }
}
else{
    header("Location: ".$config['site_url']."login?ref=dashboard");
    exit();
}
?>