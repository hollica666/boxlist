<?php
/**
 * Created by PhpStorm.
 * User: Bylancer
 * Date: 4/1/2017
 * Time: 10:26 AM
 */
function check_product_favorite($config,$product_id){
    if(checkloggedin($config)) {
        $query = "SELECT id FROM ".$config['db']['pre']."favads WHERE product_id='" . $product_id . "' and user_id='" . $_SESSION['user']['id'] . "' LIMIT 1";
        $query_result = mysqli_query(db_connect($config), $query);
        $num_rows = mysqli_num_rows($query_result);
        if($num_rows == 1)
            return true;
        else
            return false;

    }else{
        return false;
    }

}

function check_valid_author($config,$product_id){
    if(checkloggedin($config)) {
        $query = "SELECT 1 FROM ".$config['db']['pre']."product WHERE id='" . $product_id . "' and user_id='" . $_SESSION['user']['id'] . "' LIMIT 1";
        $query_result = mysqli_query(db_connect($config), $query);
        $num_rows = mysqli_num_rows($query_result);
        if($num_rows == 1)
            return true;
        else
            return false;

    }else{
        return false;
    }
}

function check_item_status($config,$product_id){
    $query = "SELECT status FROM ".$config['db']['pre']."product WHERE id='" . $product_id . "' LIMIT 1";
    $query_result = mysqli_query(db_connect($config), $query);
    $info = mysqli_fetch_assoc($query_result);
    return $info['status'];
}

function check_valid_resubmission($config,$product_id){
    if(checkloggedin($config)) {
        $query = "SELECT 1 FROM ".$config['db']['pre']."product_resubmit WHERE product_id='" . $product_id . "' and user_id='" . $_SESSION['user']['id'] . "' LIMIT 1";
        $query_result = mysqli_query(db_connect($config), $query);
        $num_rows = mysqli_num_rows($query_result);
        if($num_rows == 1)
            return false;
        else
            return true;

    }else{
        return false;
    }
}

function get_html_pages($config){
    $htmlPages = array();

    $query = "select * from ".$config['db']['pre']."pages where translation_lang = '".$config['lang_code']."'";
    $result = db_connect($config)->query($query);
    if (@mysqli_num_rows($result) > 0) {
        while($info = mysqli_fetch_assoc($result))
        {
            $htmlPages[$info['id']]['id'] = $info['id'];
            $htmlPages[$info['id']]['title'] = $info['title'];

            $htmlPages[$info['id']]['link'] = $config['site_url'].'page/'.$info['slug'];

        }
    }
    return $htmlPages;
}

function get_advertise($config,$slug){
    $response = array();
    $query = "SELECT * FROM ".$config['db']['pre']."adsense WHERE `slug` = '".$slug."' LIMIT 1";
    $query_result = mysqli_query(db_connect($config),$query);
    $info = mysqli_fetch_assoc($query_result);

    $status = $info['status'];
    $large_track_code = $info['large_track_code'];
    $tablet_track_code = $info['tablet_track_code'];
    $phone_track_code = $info['phone_track_code'];
    $advertise_tpl = "";

    if($status=='1'){
        $advertise_tpl = '<div class="text-center visible-md visible-lg">'.$large_track_code.'</div>
        <div class="text-center visible-sm">'.$tablet_track_code.'</div>
        <div class="text-center visible-xs">'.$phone_track_code.'</div>';
    }
    $response['tpl'] = $advertise_tpl;
    $response['status'] = $status;
    return $response;
}


/***********************************NEW*****************************/

function get_countryName_by_code($config,$code){
    $query = "SELECT asciiname FROM ".$config['db']['pre']."countries WHERE code='" . $code . "' LIMIT 1";
    $query_result = mysqli_query(db_connect($config), $query);
    $info = mysqli_fetch_assoc($query_result);
    return $info['asciiname'];
}
function get_stateName_by_code($config,$code){
    $query = "SELECT asciiname FROM ".$config['db']['pre']."subadmin1 WHERE code='" . $code . "' LIMIT 1";
    $query_result = mysqli_query(db_connect($config), $query);
    $info = mysqli_fetch_assoc($query_result);
    return $info['asciiname'];
}
function get_district_by_code($config,$code){
    $query = "SELECT asciiname FROM ".$config['db']['pre']."subadmin2 WHERE code='" . $code . "' LIMIT 1";
    $query_result = mysqli_query(db_connect($config), $query);
    $info = mysqli_fetch_assoc($query_result);
    return $info['asciiname'];
}

function get_countryCurrecny_by_code($config,$code){
    $query = "SELECT currency_code FROM ".$config['db']['pre']."countries WHERE code='" . $code . "' LIMIT 1";
    $query_result = mysqli_query(db_connect($config), $query);
    $info = mysqli_fetch_assoc($query_result);
    return $info['currency_code'];
}

function get_currency_by_id($config,$id){
    $query = "SELECT * FROM ".$config['db']['pre']."currencies WHERE id='" . $id . "' LIMIT 1";
    $query_result = mysqli_query(db_connect($config), $query);
    $info = mysqli_fetch_assoc($query_result);
    return $info;
}

function get_currency_by_code($config,$code){
    $query = "SELECT * FROM ".$config['db']['pre']."currencies WHERE code='" . $code . "' LIMIT 1";
    $query_result = mysqli_query(db_connect($config), $query);
    $info = mysqli_fetch_assoc($query_result);
    return $info;
}

function price_format($number,$country_code)
{
    global $config;

    if($number == '0')
        return $number;

    // Convert string to numeric
    $number = rawFormat($number);

    $currency = set_user_currency($config,$country_code);

    // Currency format - Ex: USD 100,234.56 | EUR 100 234,56
    $number = number_format($number, (int) $currency['decimal_places'], $currency['decimal_separator'], $currency['thousand_separator']);

    $tmp = explode($currency['decimal_places'], $number);

    if ($currency['in_left'] == 1) {
        $number = $currency['html_entity'] . $number;
    } else {
        $number = $number . ' ' . $currency['html_entity'];
    }

    // Remove decimal value if it's null
    $defaultDecimal = str_pad('', (int) $currency['decimal_places'], '0');
    $number = str_replace($currency['decimal_separator'] . $defaultDecimal, '', $number);

    return $number;
}

function get_currency_list($config,$selected="",$selected_text='selected')
{
    $currencies = array();
    $count = 0;

    $query = "SELECT * FROM ".$config['db']['pre']."currencies ORDER BY name";
    $query_result = mysqli_query(db_connect($config),$query);
    while ($info = mysqli_fetch_array($query_result))
    {
        $currencies[$count]['id'] = $info['id'];
        $currencies[$count]['code'] = $info['code'];
        $currencies[$count]['name'] = $info['name'];
        $currencies[$count]['html_entity'] = $info['html_entity'];
        $currencies[$count]['in_left'] = $info['in_left'];
        if($selected!="")
        {
            if($selected==$info['id'] or $selected==$info['code'])
            {
                $currencies[$count]['selected'] = $selected_text;
            }
            else
            {
                $currencies[$count]['selected'] = "";
            }
        }
        $count++;
    }

    return $currencies;
}

function get_timezone_list($config,$selected="",$selected_text='selected')
{
    $timezones = array();
    $count = 0;

    $query = "SELECT * FROM ".$config['db']['pre']."time_zones ORDER BY time_zone_id ";
    $query_result = mysqli_query(db_connect($config),$query);
    while ($info = mysqli_fetch_array($query_result))
    {
        $timezones[$count]['id'] = $info['id'];
        $timezones[$count]['country_code'] = $info['country_code'];
        $timezones[$count]['time_zone_id'] = $info['time_zone_id'];
        $timezones[$count]['gmt'] = $info['gmt'];
        $timezones[$count]['dst'] = $info['dst'];
        $timezones[$count]['raw'] = $info['raw'];
        if($selected!="")
        {
            if($selected==$info['id'] or $selected==$info['time_zone_id'])
            {
                $timezones[$count]['selected'] = $selected_text;
            }
            else
            {
                $timezones[$count]['selected'] = "";
            }
        }
        $count++;
    }

    return $timezones;
}

function get_language_list($config,$selected="",$selected_text='selected',$active=false)
{
    $language = array();
    $count = 0;
    $where = "";
    if($active){
        $where = " WHERE active = '1' ";
    }
    $query = "SELECT * FROM ".$config['db']['pre']."languages $where ORDER BY id";
    $query_result = mysqli_query(db_connect($config),$query);
    while ($info = mysqli_fetch_array($query_result))
    {
        $language[$count]['id'] = $info['id'];
        $language[$count]['code'] = $info['code'];
        $language[$count]['direction'] = $info['direction'];
        $language[$count]['name'] = $info['name'];
        $language[$count]['file_name'] = $info['file_name'];
        $language[$count]['active'] = $info['active'];
        $language[$count]['default'] = $info['default'];
        if($selected!="")
        {
            if($selected==$info['id'] or $selected==$info['code'])
            {
                $language[$count]['selected'] = $selected_text;
            }
            else
            {
                $language[$count]['selected'] = "";
            }
        }
        $count++;
    }

    return $language;
}

function get_language_by_id($config,$id){
    $query = "SELECT * FROM ".$config['db']['pre']."languages WHERE id='" . validate_input($id) . "' LIMIT 1";
    $query_result = mysqli_query(db_connect($config), $query);
    $info = mysqli_fetch_assoc($query_result);
    return $info;
}
function get_language_by_code($code){
    global $config;
    $query = "SELECT * FROM ".$config['db']['pre']."languages WHERE code='". validate_input($code)."' LIMIT 1";
    $query_result = mysqli_query(db_connect($config), $query);
    $info = mysqli_fetch_assoc($query_result);
    if($info)
        return $info;
    else
        return false;
}
function get_lang_code_by_filename($lang){
    global $config;
    $query = "SELECT code FROM ".$config['db']['pre']."languages WHERE file_name='" . validate_input($lang) . "' LIMIT 1";
    $query_result = mysqli_query(db_connect($config), $query);
    $info = mysqli_fetch_assoc($query_result);
    return $info['code'];
}

function get_current_lang_direction($config){
    $query = "SELECT direction FROM ".$config['db']['pre']."languages WHERE file_name='" . $config['lang'] . "' LIMIT 1";
    $query_result = mysqli_query(db_connect($config), $query);
    $info = mysqli_fetch_assoc($query_result);
    return $info['direction'];
}
/***********************************NEW*****************************/

function get_countryID_by_state_id($config,$code){
    return substr($code,0,2);
}

function get_countryName_by_sortname($config,$sortname){
    global $mysqli;
    $query = "SELECT name FROM ".$config['db']['pre']."countries WHERE code='".$sortname."' LIMIT 1";
    $query_result = @mysqli_query($mysqli, $query);
    $info = @mysqli_fetch_assoc($query_result);
    return $info['name'];
}

function get_countryName_by_id($config,$id){
    $query = "SELECT name FROM ".$config['db']['pre']."countries WHERE code='" . $id . "' LIMIT 1";
    $query_result = @mysqli_query(db_connect($config), $query);
    $info = @mysqli_fetch_assoc($query_result);
    return $info['name'];
}

function get_stateName_by_id($config,$id){
    $query = "SELECT name FROM ".$config['db']['pre']."subadmin1 WHERE code='" . $id . "' LIMIT 1";
    $query_result = mysqli_query(db_connect($config), $query);
    $info = mysqli_fetch_assoc($query_result);
    return $info['name'];
}

function get_cityName_by_id($config,$id){
    $query = "SELECT name FROM ".$config['db']['pre']."cities WHERE id='" . $id . "' LIMIT 1";
    $query_result = mysqli_query(db_connect($config), $query);
    $info = mysqli_fetch_assoc($query_result);
    return $info['name'];
}

function get_cityDetail_by_id($config,$id){
    $query = "SELECT * FROM ".$config['db']['pre']."cities WHERE id='" . $id . "' LIMIT 1";
    $query_result = mysqli_query(db_connect($config), $query);
    $info = mysqli_fetch_assoc($query_result);
    return $info;
}

function get_lat_long_of_country($config,$country_code){
    global $mysqli;
    $query = "SELECT capital FROM ".$config['db']['pre']."countries WHERE code='".$country_code."' LIMIT 1";
    $query_result = mysqli_query($mysqli, $query);
    $info = mysqli_fetch_assoc($query_result);

    $query2 = "SELECT latitude,longitude FROM ".$config['db']['pre']."cities WHERE asciiname='" . $info['capital'] . "' LIMIT 1";
    $query_result2 = mysqli_query($mysqli, $query2);
    $info2 = @mysqli_fetch_assoc($query_result2);

    return $info2;
}

function get_country_list($config,$selected="",$selected_text='selected',$installed=1)
{
    $countries = array();
    $count = 0;
    if($installed){
        $query = "SELECT id,code,asciiname,languages FROM ".$config['db']['pre']."countries where active = '1' ORDER BY asciiname";
    }else{
        $query = "SELECT id,code,asciiname,languages FROM ".$config['db']['pre']."countries ORDER BY asciiname";
    }

    $query_result = mysqli_query(db_connect($config),$query);
    while ($info = mysqli_fetch_array($query_result))
    {
        $countries[$count]['id'] = $info['id'];
        $countries[$count]['code'] = $info['code'];
        $countries[$count]['lowercase_code'] = strtolower($info['code']);
        $countries[$count]['name'] = $info['asciiname'];
        $countries[$count]['lang'] = getLangFromCountry($info['languages']);
        if($selected!="")
        {
            if(is_array($selected))
            {
                foreach($selected as $select)
                {

                    $select = strtoupper(str_replace('"','',$select));
                    if($select == $info['id'])
                    {
                        $countries[$count]['selected'] = $selected_text;
                    }
                }
            }
            else{
                if($selected==$info['id'] or $selected==$info['code'] or $selected==$info['asciiname'])
                {
                    $countries[$count]['selected'] = $selected_text;
                }
                else
                {
                    $countries[$count]['selected'] = "";
                }
            }
        }
        $count++;
    }

    return $countries;
}

function startsWith($haystack, $needles)
{
    foreach ((array) $needles as $needle) {
        if ($needle !== '' && substr($haystack, 0, strlen($needle)) === (string) $needle) {
            return true;
        }
    }

    return false;
}
function getLangFromCountry($languages)
{
    global $config;
    // Get language code
    $langCode = $hrefLang = '';
    if (trim($languages) != '') {
        // Get the country's languages codes
        $countryLanguageCodes = explode(',', $languages);

        // Get all languages
        $availableLanguages = get_language_list($config);

        /*$availableLanguages = Cache::remember('languages.all', self::$cacheExpiration, function () {
            $availableLanguages = LanguageModel::all();
            return $availableLanguages;
        });*/

        if (count($availableLanguages) > 0) {
            $found = false;
            foreach ($countryLanguageCodes as $isoLang) {
                foreach ($availableLanguages as $language) {
                    if (startsWith(strtolower($isoLang), strtolower($language['code']))) {
                        $langCode = $language['code'];
                        $hrefLang = $isoLang;
                        $found = true;
                        break;
                    }
                }
                if ($found) {
                    break;
                }
            }
        }
    }

    // Get language info
    if ($langCode != '') {
        return $langCode;
    } else {
        $lang = get_lang_code_by_filename($config['default_lang']);
    }

    return $lang;
}

function get_customField_exist_id($config,$id){
    $query = "SELECT 1 FROM `".$config['db']['pre']."custom_fields` where custom_id = '$id' LIMIT 1";
    $query_result = mysqli_query(db_connect($config), $query);
    $numRow = mysqli_num_rows($query_result);
    return $numRow;
}

function get_customField_title_by_id($config,$id){
    $custom_fields_title = "";
    $query = "SELECT custom_title,translation_lang,translation_name FROM `".$config['db']['pre']."custom_fields` where custom_id = '$id' LIMIT 1";
    $query_result = mysqli_query(db_connect($config), $query);
    $rows = mysqli_num_rows($query_result);
    if($rows > 0){}
    $info = mysqli_fetch_assoc($query_result);
    if($config['lang_code'] != 'en'){
        if($info['translation_lang'] != '' && $info['translation_name'] != ''){
            $translation_lang = explode(',',$info['translation_lang']);
            $translation_name = explode(',',$info['translation_name']);

            $count = 0;
            foreach($translation_lang as $key=>$value)
            {
                if($value != '')
                {
                    $translation[$translation_lang[$key]] = $translation_name[$key];

                    $count++;
                }
            }

            $trans_name = (isset($translation[$config['lang_code']]))? $translation[$config['lang_code']] : '';

            if($trans_name != ''){
                $custom_fields_title = stripslashes($trans_name);
            }else{
                $custom_fields_title = stripslashes($info['custom_title']);
            }
        }
    }else{
        $custom_fields_title = stripslashes($info['custom_title']);
    }
    return $custom_fields_title;
}

function get_customOption_by_id($option_id){
    global $config;
    $query = "SELECT title FROM ".$config['db']['pre']."custom_options WHERE `option_id` = '".$option_id."' LIMIT 1";
    $query_result = mysqli_query(db_connect($config),$query);
    $info = mysqli_fetch_assoc($query_result);
    if($config['lang_code'] != 'en'){
        $customoption = get_category_translation("custom_option",$option_id);
        $info['title'] = $customoption['title'];
    }
    return $info['title'];
}

function add_post_customField_data($category_id,$subcategory_id,$product_id){

    global $config;
    $mysqli = db_connect($config);

    $custom_fields = get_customFields_by_catid($config, $mysqli, $category_id, $subcategory_id);

    foreach ($custom_fields as $key => $value) {
        if ($value['userent']) {
            $field_id = $value['id'];
            $field_type = $value['type'];
            if($field_type == "textarea")
                $field_data = validate_input($value['default'],true);
            else
                $field_data = validate_input($value['default']);

            if(isset($product_id)){
                $exist = 0;
                //Checking Data exist
                $query = "SELECT 1 FROM `".$config['db']['pre']."custom_data` where product_id = '".$product_id."' and field_id = '".$field_id."' LIMIT 1";
                $query_result = mysqli_query(db_connect($config), $query);
                $exist = mysqli_num_rows($query_result);

                if($exist > 0){
                    //Update here
                    $sql = "UPDATE `".$config['db']['pre']."custom_data` set field_type = '".$field_type."', field_data = '".$field_data."' where product_id = '".$product_id."' and field_id = '".$field_id."' LIMIT 1";
                    $mysqli->query($sql);
                }else{
                    //Insert here
                    if($field_data != "") {
                        $sql = "INSERT into `" . $config['db']['pre'] . "custom_data` set
                        product_id = '" . $product_id . "',
                        field_id = '" . $field_id . "',
                        field_type = '" . $field_type . "',
                        field_data = '" . $field_data . "'
                        ";
                        $mysqli->query($sql);
                    }
                }
            }
        }
    }
}

function get_customFields_by_catid($config,$con,$maincatid=null,$subcatid=null,$require=true,$fields=array(),$data=array())
{
    $custom_fields = array();

    if(isset($subcatid) && $subcatid != "" && is_numeric($subcatid)){
        $query = "SELECT * FROM `".$config['db']['pre']."custom_fields` WHERE find_in_set($subcatid,custom_subcatid) <> 0 order by custom_id ASC";
    }elseif(isset($maincatid) && $maincatid != "" && is_numeric($maincatid)){
        $query = "SELECT * FROM `".$config['db']['pre']."custom_fields` WHERE find_in_set($maincatid,custom_catid) <> 0 order by custom_id ASC";
    }else{
        $query = "SELECT * FROM `".$config['db']['pre']."custom_fields` WHERE custom_anycat = 'any' order by custom_id ASC";
    }

    $query_result = @mysqli_query ($con,$query) OR error(mysqli_error($con));
    mysqli_num_rows($query_result);
    while ($info = @mysqli_fetch_array($query_result))
    {
        $custom_fields[$info['custom_id']]['id'] = $info['custom_id'];
        $custom_fields[$info['custom_id']]['type'] = $info['custom_type'];
        $custom_fields[$info['custom_id']]['name'] = $info['custom_name'];
        $custom_fields[$info['custom_id']]['title'] = stripslashes($info['custom_title']);
        $custom_fields[$info['custom_id']]['maxlength'] = $info['custom_max'];



        if($config['lang_code'] != 'en'){
            if($info['translation_lang'] != '' && $info['translation_name'] != ''){
                $translation_lang = explode(',',$info['translation_lang']);
                $translation_name = explode(',',$info['translation_name']);

                $count = 0;
                foreach($translation_lang as $key=>$value)
                {
                    if($value != '')
                    {
                        $translation[$translation_lang[$key]] = $translation_name[$key];

                        $count++;
                    }
                }

                $trans_name = (isset($translation[$config['lang_code']]))? $translation[$config['lang_code']] : '';

                if($trans_name != ''){
                    $custom_fields[$info['custom_id']]['title'] = stripslashes($trans_name);
                }else{
                    $custom_fields[$info['custom_id']]['title'] = stripslashes($info['custom_title']);
                }
            }
        }

        $required = "";
        if($require){
            if($info['custom_required'] == 1){
                $required = "required";
                $custom_fields[$info['custom_id']]['required'] = "required";
            }
            else{
                $custom_fields[$info['custom_id']]['required'] = "";
            }
        }

        if(isset($_REQUEST['custom'][$info['custom_id']]))
        {
            if($custom_fields[$info['custom_id']]['type'] == "checkboxes"){
                $checkbox1=$_REQUEST['custom'][$info['custom_id']];
                if(is_array($checkbox1)){
                    $chk="";
                    $chkCount = 0;
                    foreach($checkbox1 as $chk1)
                    {
                        if($chkCount == 0)
                            $chk .= $chk1;
                        else
                            $chk .= ",".$chk1;

                        $chkCount++;
                    }
                    $custom_fields[$info['custom_id']]['default'] = $chk;
                }
                else{
                    $custom_fields[$info['custom_id']]['default'] = $_REQUEST['custom'][$info['custom_id']];
                }

            }
            else{
                //$custom_fields[$info['custom_id']]['default'] = substr(strip_tags($_REQUEST['custom'][$info['custom_id']]),0,$info['custom_max']);
                $custom_fields[$info['custom_id']]['default'] = $_REQUEST['custom'][$info['custom_id']];
            }

            $custom_fields[$info['custom_id']]['userent'] = 1;
        }
        else
        {
            $custom_fields[$info['custom_id']]['default'] = $info['custom_default'];
            $custom_fields[$info['custom_id']]['userent'] = 0;
        }

        foreach($fields as $key=>$value)
        {
            if($value != '')
            {
                if($value == $info['custom_id']){
                    $custom_fields[$info['custom_id']]['default'] = $data[$key];
                    break;
                }

            }
        }

        //Text-field
        if($info['custom_type'] == 'text-field'){
            $textbox = '<input name="custom['.$info['custom_id'].']" id="custom['.$info['custom_id'].']" class="form-control"  type="text" value="'.$custom_fields[$info['custom_id']]['default'].'" '.$required.' placeholder="'.$custom_fields[$info['custom_id']]['title'].'"/>';
            $custom_fields[$info['custom_id']]['textbox'] = $textbox;
        }
        else{
            $custom_fields[$info['custom_id']]['textbox'] = '';
        }

        //Textarea
        if($info['custom_type'] == 'textarea'){
            $textarea= '<textarea class="materialize-textarea form-control" name="custom['.$info['custom_id'].']" id="custom['.$info['custom_id'].']" '.$required.' placeholder="'.$custom_fields[$info['custom_id']]['title'].'">'.$custom_fields[$info['custom_id']]['default'].'</textarea><p class="help-block">Html tags are allow.</p>';
            $custom_fields[$info['custom_id']]['textarea'] = $textarea;
        }
        else{
            $custom_fields[$info['custom_id']]['textarea'] = '';
        }

        //SelectList
        if($info['custom_type'] == 'drop-down')
        {
            $options = explode(',',stripslashes($info['custom_options']));

            //$selectbox = '<select class="meterialselect" name="custom['.$info['custom_id'].']" '.$required.'><option value="" selected>'.$info['custom_title'].'</option>';
            $selectbox = '';
            foreach($options as $key3=>$value3)
            {
                $option_title = get_customOption_by_id($value3);
                if($value3 == $custom_fields[$info['custom_id']]['default'])
                {
                    $selectbox.= '<option value="'.$value3.'" selected>'.$option_title.'</option>';
                }
                else
                {
                    $selectbox.= '<option value="'.$value3.'">'.$option_title.'</option>';
                }
            }
            //$selectbox.= '</select>';

            $custom_fields[$info['custom_id']]['selectbox'] = $selectbox;
        }
        else
        {
            $custom_fields[$info['custom_id']]['selectbox'] = '';
        }

        //RadioButton
        if($info['custom_type'] == 'radio-buttons')
        {
            $options = explode(',',stripslashes($info['custom_options']));
            $radiobtn = "";
            $i = 0;
            foreach($options as $key3=>$value3)
            {
                $option_title = get_customOption_by_id($value3);
                if($value3 == $custom_fields[$info['custom_id']]['default'])
                {
                    $radiobtn .= '<div class="radio radio-primary radio-inline"><input class="with-gap" type="radio" name="custom['.$info['custom_id'].']" id="'.$value3.$i.'" value="'.$value3.'" checked />';
                    $radiobtn .= '<label for="'.$value3.$i.'">'.$option_title.'</label></div>';
                }
                else
                {
                    $radiobtn .= '<div class="radio radio-primary radio-inline"><input class="with-gap" type="radio" name="custom['.$info['custom_id'].']" id="'.$value3.$i.'" value="'.$value3.'" />';
                    $radiobtn .= '<label for="'.$value3.$i.'">'.$option_title.'</label></div>';
                }
                $i++;
            }
            $custom_fields[$info['custom_id']]['radio'] = $radiobtn;
        }
        else
        {
            $custom_fields[$info['custom_id']]['radio'] = '';
        }

        //Checkbox
        if($info['custom_type'] == 'checkboxes')
        {
            $options = explode(',',stripslashes($info['custom_options']));
            $Checkbox = "";
            $CheckboxBootstrap = "";
            $j = 0;
            $selected = "";
            foreach($options as $key4=>$value4)
            {
                //print_r($_REQUEST['custom'][$info['custom_id']]);
                $default_checkbox = $custom_fields[$info['custom_id']]['default'];
                if(is_array($default_checkbox)){
                    $checked = $custom_fields[$info['custom_id']]['default'];
                }else{
                    $checked = explode(',',$custom_fields[$info['custom_id']]['default']);
                }

                foreach ($checked as $val)
                {
                    if($value4 == $val)
                    {
                        $selected = "checked";
                        break;
                    }
                    else{
                        $selected = "";
                    }
                }

                $option_title = get_customOption_by_id($value4);
                $Checkbox .= '<div class="col-md-4 col-sm-4"><input class="with-gap" type="checkbox" name="custom['.$info['custom_id'].'][]" id="'.$value4.$j.'" value="'.$value4.'" '.$selected.' />';
                $Checkbox .= '<label for="'.$value4.$j.'">'.$option_title.'</label></div>';

                //$CheckboxBootstrap .= '<label for="'.$value4.$j.'" class="'.$selected.'">'.$value4.'<input class="with-gap" type="checkbox" name="custom['.$info['custom_id'].'][]" id="'.$value4.$j.'" value="'.$value4.'" '.$selected.' /></label>';

                $CheckboxBootstrap .= '
                <div class="checkbox checkbox-inline checkbox-primary">
                    <input type="checkbox" name="custom['.$info['custom_id'].'][]" id="'.$value4.$j.'" value="'.$value4.'" '.$selected.' />
                    <label for="'.$value4.$j.'" >'.$option_title.'</label>
                </div>';

                $j++;
            }
            $custom_fields[$info['custom_id']]['checkbox'] = $Checkbox;
            $custom_fields[$info['custom_id']]['checkboxBootstrap'] = $CheckboxBootstrap;
        }
        else
        {
            $custom_fields[$info['custom_id']]['checkbox'] = '';
            $custom_fields[$info['custom_id']]['checkboxBootstrap'] = '';
        }
    }

    return $custom_fields;
}

function create_slug($string){
    return slugify($string);
}

function create_post_slug($config,$title)
{
    $slug = create_slug($title);

    $query = "SELECT COUNT(*) AS NumHits FROM ".$config['db']['pre']."product WHERE slug  LIKE '$slug%'";
    $result = mysqli_query(db_connect($config),$query);
    $row = mysqli_fetch_assoc($result);
    $numHits = $row['NumHits'];

    return ($numHits > 0) ? ($slug.'-'.$numHits) : $slug;
}

function check_category_exists($cat_id){
    global $config;
    $mysqli = db_connect($config);

    $prep_stmt = "SELECT cat_id FROM ".$config['db']['pre']."catagory_main WHERE cat_id = ? LIMIT 1";
    $stmt = $mysqli->prepare($prep_stmt);

    // check existing email
    if ($stmt) {
        $stmt->bind_param('s', $cat_id);
        $stmt->execute();
        $stmt->store_result();

        return true;

    } else {
        return false;
        $stmt->close();
    }
}

function check_sub_category_exists($cat_id){
    global $config;
    $mysqli = db_connect($config);

    $prep_stmt = "SELECT sub_cat_id FROM ".$config['db']['pre']."catagory_sub WHERE sub_cat_id = ? LIMIT 1";
    $stmt = $mysqli->prepare($prep_stmt);

    // check existing email
    if ($stmt) {
        $stmt->bind_param('s', $cat_id);
        $stmt->execute();
        $stmt->store_result();

        return true;

    } else {
        return false;
        $stmt->close();
    }
}

function get_category_id_by_slug($config,$slug)
{
    $query = "SELECT cat_id FROM ".$config['db']['pre']."catagory_main WHERE slug = '$slug' limit 1";
    $result = mysqli_query(db_connect($config),$query);
    $count = mysqli_num_rows($result);
    if($count > 0){
        $info = mysqli_fetch_assoc($result);
        return $info['cat_id'];
    }else{
        $query = "SELECT translation_id FROM ".$config['db']['pre']."category_translation WHERE slug = '$slug' and category_type = 'main' limit 1";
        $result = mysqli_query(db_connect($config),$query);
        $info = mysqli_fetch_assoc($result);
        return $info['translation_id'];
    }
}

function get_subcategory_id_by_slug($config,$slug){
    $query = "SELECT sub_cat_id FROM ".$config['db']['pre']."catagory_sub WHERE slug = '$slug' limit 1";
    $result = mysqli_query(db_connect($config),$query);
    $count = mysqli_num_rows($result);
    if($count > 0){
        $info = mysqli_fetch_assoc($result);
        return $info['sub_cat_id'];
    }else{
        $query = "SELECT translation_id FROM ".$config['db']['pre']."category_translation WHERE slug = '$slug' and category_type = 'sub' limit 1";
        $result = mysqli_query(db_connect($config),$query);
        $info = mysqli_fetch_assoc($result);
        return $info['translation_id'];
    }
}

function create_category_slug($config,$title)
{
    $slug = create_slug($title);

    $query = "SELECT COUNT(*) AS NumHits FROM ".$config['db']['pre']."catagory_main WHERE slug  LIKE '$slug%'";
    $result = mysqli_query(db_connect($config),$query);
    $row = mysqli_fetch_assoc($result);
    $numHits = $row['NumHits'];

    return ($numHits > 0) ? ($slug.'-'.$numHits) : $slug;
}

function create_sub_category_slug($config,$title)
{
    $slug = create_slug($title);

    $query = "SELECT COUNT(*) AS NumHits FROM ".$config['db']['pre']."catagory_sub WHERE slug  LIKE '$slug%'";
    $result = mysqli_query(db_connect($config),$query);
    $row = mysqli_fetch_assoc($result);
    $numHits = $row['NumHits'];

    return ($numHits > 0) ? ($slug.'-'.$numHits) : $slug;
}

function create_category_translation_slug($config,$title)
{
    $slug = create_slug($title);

    $query = "SELECT COUNT(*) AS NumHits FROM ".$config['db']['pre']."category_translation WHERE slug  LIKE '$slug%'";
    $result = mysqli_query(db_connect($config),$query);
    $row = mysqli_fetch_assoc($result);
    $numHits = $row['NumHits'];

    return ($numHits > 0) ? ($slug.'-'.$numHits) : $slug;
}

function get_category_translation_detail($config,$cattype,$catid){
    $query = "SELECT title,slug FROM ".$config['db']['pre']."category_translation WHERE translation_id = '$catid' AND lang_code = '".$config['lang_code']."' AND category_type = '$cattype' LIMIT 1";
    $query_result = mysqli_query(db_connect($config),$query);
    $info = mysqli_fetch_assoc($query_result);

    return $info;
}

function get_category_translation($cattype,$catid){
    global $config;
    $query = "SELECT title,slug FROM `".$config['db']['pre']."category_translation` where translation_id = '$catid' AND lang_code = '".$config['lang_code']."'  AND category_type = '$cattype' LIMIT 1";
    $query_result = mysqli_query(db_connect($config), $query);
    $info = mysqli_fetch_assoc($query_result);
    return $info;
}

function delete_language_translation($config,$type,$translation_id){
    $query = "DELETE FROM `".$config['db']['pre']."category_translation` where translation_id = '$translation_id' AND category_type = '$type'";
    if(db_connect($config)->query($query)){
        return true;
    }else{
        return false;
    }
}

function get_maincategory($config,$selected="",$selected_text='selected'){
    $cat = array();

    $query = "SELECT * FROM ".$config['db']['pre']."catagory_main ORDER by cat_order ASC";
    $query_result = mysqli_query(db_connect($config),$query);
    while($info = mysqli_fetch_assoc($query_result)){
        $cat[$info['cat_id']]['id'] = $info['cat_id'];
        $cat[$info['cat_id']]['icon'] = $info['icon'];

        if($config['lang_code'] != 'en'){
            $maincat = get_category_translation("main",$info['cat_id']);
            $cat[$info['cat_id']]['name'] = $maincat['title'];
            $cat[$info['cat_id']]['slug'] = $maincat['slug'];
        }else{
            $cat[$info['cat_id']]['name'] = $info['cat_name'];
            $cat[$info['cat_id']]['slug'] = $info['slug'];
        }

        if($selected!="") {
            if($selected==$info['cat_id'] || $selected==$info['cat_name'])
            {
                $cat[$info['cat_id']]['selected'] = $selected_text;
            }else{
                $cat[$info['cat_id']]['selected'] = "";
            }
        }else
        {
            $cat[$info['cat_id']]['selected'] = "";
        }
    }

    return $cat;
}

function get_maincat_by_id($config,$id){
    global $config;
    $query = "SELECT * FROM ".$config['db']['pre']."catagory_main WHERE `cat_id` = '".$id."' LIMIT 1";
    $query_result = mysqli_query(db_connect($config),$query);
    $info = mysqli_fetch_assoc($query_result);
    if($config['lang_code'] != 'en'){
        $maincat = get_category_translation("main",$info['cat_id']);
        $info['cat_name'] = $maincat['title'];
        $info['slug'] = $maincat['slug'];

    }
    return $info;
}

function get_subcategories(){
    global $config,$con;
    $query = "SELECT * FROM ".$config['db']['pre']."catagory_sub ";
    $query_result = mysqli_query ($con, $query) OR error(mysqli_error($con));

    while($row = $query_result->fetch_assoc()) // use fetch_assoc here
    {
        $info[] = $row; // assign each value to array
    }

    $subcat = array();
    foreach ($info as $key => $value){
        $subcat[$key]['id'] = $value['sub_cat_id'];
        $subcat[$key]['main_cat_id'] = $value['main_cat_id'];
        $subcat[$key]['name'] = $value['sub_cat_name'];
        $subcat[$key]['slug'] = $value['slug'];

        if($config['lang_code'] != 'en'){
            $subcat_trans = get_category_translation("sub",$value['sub_cat_id']);
            $subcat[$key]['name'] = $subcat_trans['title'];
            $subcat[$key]['slug'] = $subcat_trans['slug'];
        }
    }

    return $subcat;
}

function get_subcat_by_id($config,$id){
    $query = "SELECT * FROM ".$config['db']['pre']."catagory_sub WHERE `sub_cat_id` = '".$id."' LIMIT 1";
    $query_result = mysqli_query(db_connect($config),$query);
    $info = mysqli_fetch_assoc($query_result);
    if($config['lang_code'] != 'en'){
        $subcat = get_category_translation("sub",$info['sub_cat_id']);
        $info['sub_cat_name'] = $subcat['title'];
        $info['slug'] = $subcat['slug'];
    }
    return $info;
}

function get_subcat_of_maincat($config,$category_id,$adcount=false,$selected="",$selected_text='selected'){
    $subcat = array();
    $query = "SELECT * FROM ".$config['db']['pre']."catagory_sub WHERE `main_cat_id` = '".$category_id."' ORDER by cat_order ASC";
    $query_result = mysqli_query(db_connect($config),$query);
    $count = mysqli_num_rows($query_result);
    while($info = mysqli_fetch_assoc($query_result)){
        $subcat[$info['sub_cat_id']]['id'] = $info['sub_cat_id'];
        $subcat[$info['sub_cat_id']]['photo_show'] = $info['photo_show'];
        $subcat[$info['sub_cat_id']]['price_show'] = $info['price_show'];

        if($config['lang_code'] != 'en'){
            $subcategory = get_category_translation("sub",$info['sub_cat_id']);

            $subcat[$info['sub_cat_id']]['name'] = $subcategory['title'];
            $subcat[$info['sub_cat_id']]['slug'] = $subcategory['slug'];
        }else{
            $subcat[$info['sub_cat_id']]['name'] = $info['sub_cat_name'];
            $subcat[$info['sub_cat_id']]['slug'] =  $info['slug'];
        }

        $get_main = get_maincat_by_id($config,$category_id);
        $category_slug = $get_main['slug'];

        $subcat_slug = $subcat[$info['sub_cat_id']]['slug'];
        $subcat[$info['sub_cat_id']]['link'] = $config['site_url'].'category/'.$category_slug.'/'.$subcat_slug;

        if($adcount){
            $subcat[$info['sub_cat_id']]['adcount'] = get_items_count($config,false,"active",false,$info['sub_cat_id'],null,true);
        }

        if($selected!="") {
            if($selected==$info['sub_cat_id'] || $selected==$info['sub_cat_name'])
            {
                $subcat[$info['sub_cat_id']]['selected'] = $selected_text;
            }
        }else
        {
            $subcat[$info['sub_cat_id']]['selected'] = "";
        }
    }

    return $subcat;
}

function get_categories_dropdown($config,$lang){
    $dropdown = '<ul class="dropdown-menu category-change" id="category-change">
                          <li><a href="#" data-cat-type="all"><i class="fa fa-th"></i>'.$lang['ALL-CATEGORIES'].'</a></li>';

    $query1 = "SELECT * FROM ".$config['db']['pre']."catagory_main ORDER by cat_order ASC";
    $query_result1 = mysqli_query(db_connect($config),$query1);
    while ($info1 = mysqli_fetch_array($query_result1))
    {
        $cat_icon = $info1['icon'];
        $catname = $info1['cat_name'];
        $cat_id = $info1['cat_id'];

        if($config['lang_code'] != 'en'){
            $maincat = get_category_translation("main",$info1['cat_id']);
            $catname = $maincat['title'];
        }

        $dropdown .= '<li><a href="#" data-ajax-id="'.$cat_id.'" data-cat-type="maincat"><i class="'.$cat_icon.'"></i>'.$catname.'</a><ul>';

        $query = "SELECT * FROM ".$config['db']['pre']."catagory_sub WHERE `main_cat_id` = '".$cat_id."' ORDER by cat_order ASC";
        $query_result = mysqli_query(db_connect($config),$query);
        while ($info = mysqli_fetch_array($query_result))
        {
            $subcat_id = $info['sub_cat_id'];

            if($config['lang_code'] != 'en'){
                $subcat = get_category_translation("sub",$info['sub_cat_id']);
                $subcat_name = $subcat['title'];
            }else{
                $subcat_name = $info['sub_cat_name'];
            }

            $dropdown .= '<li><a href="#" data-ajax-id="'.$subcat_id.'" data-cat-type="subcat">'.$subcat_name.'</a></li>';
        }

        $dropdown .= '</ul></li>';
    }

    $dropdown .= '</ul>';

    return $dropdown;
}

function get_categories($config,$con,$selected=array(),$selected_text='selected')
{

    $k = 1;
    $k2 = 2;
    $jobtypes = array();
    $jobtypes2 = array();
    $parents = array();

    $query = "SELECT * FROM ".$config['db']['pre']."catagory_sub ORDER BY main_cat_id ORDER by cat_order ASC";
    $query_result = mysqli_query($con,$query);
    while ($info = mysqli_fetch_array($query_result))
    {
        if(!isset($info['parent_id']))
        {
            $info['parent_id'] = 0;
        }
        else
        {
            if(isset($parents[$info['parent_id']]))
            {
                $parents[$info['parent_id']] = ($parents[$info['parent_id']]+1);
            }
            else
            {
                $parents[$info['parent_id']] = 1;
            }
        }

        if($info['main_cat_id'] == $k2)
        {
            $jobtypes[$info['parent_id']][$info['sub_cat_id']]['sec'] = 'show';
            $k2++;
        }
        else
        {
            $jobtypes[$info['parent_id']][$info['sub_cat_id']]['sec'] = $k2;
        }
        if($info['main_cat_id'] == $k)
        {

            $query1 = "SELECT * FROM ".$config['db']['pre']."catagory_main WHERE `cat_id` = '".$info['main_cat_id']."' LIMIT 1";
            $query_result1 = mysqli_query($con,$query1);
            while ($info1 = mysqli_fetch_array($query_result1))
            {
                $jobtypes[$info['parent_id']][$info['sub_cat_id']]['icon'] = $info1['icon'];
                $jobtypes[$info['parent_id']][$info['sub_cat_id']]['main_title'] = $info1['cat_name'];
                $jobtypes[$info['parent_id']][$info['sub_cat_id']]['main_id'] = $info1['cat_id'];
                $jobtypes[$info['parent_id']][$info['sub_cat_id']]['show'] = 'yes';

                if($config['lang_code'] != 'en'){
                    $maincat = get_category_translation("main",$info1['cat_id']);
                    $jobtypes[$info['parent_id']][$info['sub_cat_id']]['main_title'] = $maincat['title'];
                }
            }

            if($k == 1)
            {
                $jobtypes[$info['parent_id']][$info['sub_cat_id']]['select'] = 'show';
            }

            $k++;

        }
        else
        {
            $jobtypes[$info['parent_id']][$info['sub_cat_id']]['show'] = 'no';
        }

        if($info['main_cat_id']++)
        {
            $jobtypes[$info['parent_id']][$info['sub_cat_id']]['section'] = 'show';
        }
        else
        {
            $jobtypes[$info['parent_id']][$info['sub_cat_id']]['section'] = 'notshow';
        }


        if($config['lang_code'] != 'en'){
            $subcat = get_category_translation("sub",$info['sub_cat_id']);
            $jobtypes[$info['parent_id']][$info['sub_cat_id']]['title'] = $subcat['title'];
        }else{
            $jobtypes[$info['parent_id']][$info['sub_cat_id']]['title'] = stripslashes($info['sub_cat_name']);
        }
        $jobtypes[$info['parent_id']][$info['sub_cat_id']]['id'] = $info['sub_cat_id'];
        $jobtypes[$info['parent_id']][$info['sub_cat_id']]['selected'] = '';
        $jobtypes[$info['parent_id']][$info['sub_cat_id']]['parent_id'] = $info['parent_id'];
        $jobtypes[$info['parent_id']][$info['sub_cat_id']]['catcount'] = 0;
        $jobtypes[$info['parent_id']][$info['sub_cat_id']]['counter'] = 0;
        $jobtypes[$info['parent_id']][$info['sub_cat_id']]['totalads'] = get_items_count($config,false,"active",$info['sub_cat_id']);
        foreach($selected as $select)
        {
            if($select==$info['sub_cat_id'])
            {
                $jobtypes[$info['parent_id']][$info['sub_cat_id']]['selected'] = $selected_text;
            }
        }
    }

    foreach($jobtypes as $key=>$value)
    {
        foreach($value as $key2=>$value2)
        {
            if(isset($parents[$key2]))
            {
                $jobtypes[$key][$key2]['catcount']  = $parents[$key2];
            }
        }
    }

    $counter = 1;

    foreach($jobtypes[0] as $key=>$value)
    {
        $value['counter'] = $counter;
        if($value['catcount'])
        {
            $value['ctype'] = 1;
        }
        else
        {
            $value['ctype'] = 0;
        }

        $jobtypes2[$key] =  $value;
        $counter++;

        if(isset($jobtypes[$key]))
        {
            foreach($jobtypes[$key] as $key2=>$value2)
            {
                $value2['counter'] = $counter;
                $value2['ctype'] = 2;

                $jobtypes2[$key2] =  $value2;

                $counter++;
            }
        }
    }

    return $jobtypes2;

}

function get_item_by_id($con,$product_id){
    global $config;
    $iteminfo = array();

    $sql = "SELECT * FROM ".$config['db']['pre']."product where  id = '".validate_input($product_id)."' limit 1";
    $result = mysqli_query($con, $sql);

    if (mysqli_num_rows($result) > 0) {
        $info = mysqli_fetch_assoc($result);
        $iteminfo['id'] = $info['id'];
        $iteminfo['title'] = $info['product_name'];
        $iteminfo['location'] = $info['location'];
        $iteminfo['city'] = get_cityName_by_id($config,$info['city']);
        $iteminfo['state'] = get_stateName_by_id($config,$info['state']);
        $iteminfo['country'] = get_countryName_by_id($config,$info['country']);
        $iteminfo['status'] = $info['status'];
        $iteminfo['view'] = $info['view'];
        $iteminfo['created_at'] = timeAgo($info['created_at']);

        $get_main = get_maincat_by_id($config,$info['category']);
        $get_sub = get_subcat_by_id($config,$info['sub_category']);
        $iteminfo['category'] = $get_main['cat_name'];
        $iteminfo['sub-category'] = $get_sub['sub_cat_name'];


        $item_author_id = $info['user_id'];
        $info2 = get_user_data($config,null,$item_author_id);

        $iteminfo['author_id'] = $item_author_id;
        $iteminfo['author_name'] = ucfirst($info2['name']);
        $iteminfo['author_username'] = ucfirst($info2['username']);
        $iteminfo['author_email'] = $info2['email'];
        $iteminfo['author_image'] = $info2['image'];

        return $iteminfo;
    }
    else {
        return false;
    }
}

function get_items($config,$userid=false,$status=null,$premium=false,$page=null,$limit=null,$sort="id",$location=false){
    $where = '';
    $item = array();
    if($userid){
        if($where == '')
            $where .= "where p.user_id = '".$userid."'";
        else
            $where .= " AND p.user_id = '".$userid."'";
    }
    if($status != null){
        if($where == '')
            $where .= "where p.status = '".$status."'";
        else
            $where .= " AND p.status = '".$status."'";
    }
    if($premium){
        if($where == '')
            $where .= "where (p.featured = '1' or p.urgent = '1' or p.highlight = '1')";
        else
            $where .= " AND (p.featured = '1' or p.urgent = '1' or p.highlight = '1')";
    }

    if($location){
        $country_code = check_user_country($config);
        if($where == '')
            $where .= "where p.country = '".$country_code."'";
        else
            $where .= " AND p.country = '".$country_code."'";
    }

    $pagelimit = "";
    if($page != null && $limit != null){
        $pagelimit = "LIMIT  ".($page-1)*$limit.",".$limit;
    }

    $query = "SELECT p.*,
c.name as cityname,
cat.cat_id as catid,
cat.cat_name as catname,
cat.slug as catslug,
scat.sub_cat_id as subcatid,
scat.sub_cat_name as subcatname,
scat.slug as subcatslug
FROM `".$config['db']['pre']."product` as p
LEFT JOIN `".$config['db']['pre']."cities` as c ON c.id = p.city
LEFT JOIN `".$config['db']['pre']."catagory_main` as cat ON cat.cat_id = p.category
LEFT JOIN `".$config['db']['pre']."catagory_sub` as scat ON scat.sub_cat_id = p.sub_category
$where ORDER BY $sort DESC $pagelimit";
    $result = db_connect($config)->query($query) OR error(mysqli_error(db_connect($config)));
    if (mysqli_num_rows($result) > 0) {
        while($info = mysqli_fetch_assoc($result)) {
            //$item[$info['id']]['product_name'] = strlimiter($info['product_name'],16);
            $item[$info['id']]['id'] = $info['id'];
            $item[$info['id']]['product_name'] = $info['product_name'];
            $item[$info['id']]['desc'] = strlimiter($info['description'],80);
            $item[$info['id']]['featured'] = $info['featured'];
            $item[$info['id']]['urgent'] = $info['urgent'];
            $item[$info['id']]['highlight'] = $info['highlight'];
            $item[$info['id']]['price'] = $info['price'];
            $item[$info['id']]['phone'] = $info['phone'];
            $item[$info['id']]['address'] = strlimiter($info['location'],20);
            $item[$info['id']]['location'] = $info['cityname'];
            $item[$info['id']]['city'] = $info['cityname'];
            $item[$info['id']]['status'] = $info['status'];
            $item[$info['id']]['created_at'] = timeAgo($info['created_at']);
            $item[$info['id']]['cat_id'] = $info['category'];
            $item[$info['id']]['sub_cat_id'] = $info['sub_category'];

            if($config['lang_code'] != 'en'){
                $maincat = get_category_translation("main",$info['category']);
                $catname = $maincat['title'];
                $catslug = $maincat['slug'];
                $subcat = get_category_translation("sub",$info['sub_category']);
                $subcatname = $subcat['title'];
                $subcatslug = $subcat['slug'];
            }else{
                $catname = $info['catname'];
                $catslug = $info['catslug'];
                $subcatname = $info['subcatname'];
                $subcatslug = $info['subcatslug'];
            }

            $item[$info['id']]['category'] = $catname;
            $item[$info['id']]['sub_category'] = $subcatname;

            $item[$info['id']]['favorite'] = check_product_favorite($config,$info['id']);

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

            $picture     =   explode(',' ,$info['screen_shot']);
            $item[$info['id']]['pic_count'] = count($picture);

            if($picture[0] != ""){
                $item[$info['id']]['picture'] = $picture[0];
            }else{
                $item[$info['id']]['picture'] = "default.png";
            }

            $price = price_format($info['price'],$info['country']);
            $item[$info['id']]['price'] = $price;

            $userinfo = get_user_data($config,null,$info['user_id']);

            $item[$info['id']]['username'] = $userinfo['username'];
            $author_url = create_slug($userinfo['username']);

            $item[$info['id']]['author_link'] = $config['site_url'].'profile/'.$author_url;

            $pro_url = create_slug($info['product_name']);
            $item[$info['id']]['link'] = $config['site_url'].'ad/' . $info['id'] . '/'.$pro_url;

            $item[$info['id']]['catlink'] = $config['site_url'].'category/'.$catslug;

            $item[$info['id']]['subcatlink'] = $config['site_url'].'subcategory/'.$subcatslug;

            $city = create_slug($item[$info['id']]['city']);
            $item[$info['id']]['citylink'] = $config['site_url'].'city/'.$info['city'].'/'.$city;

        }
    }
    else {
        //echo "0 results";
    }
    return $item;
}

function get_resubmited_items($config,$userid=false,$status=null,$page=null,$limit=null,$sort="id"){
    $where = '';
    $item = '';
    if($userid){
        if($where == '')
            $where .= "where user_id = '".$userid."'";
        else
            $where .= " AND user_id = '".$userid."'";
    }
    if($status != null){
        if($where == '')
            $where .= "where status = '".$status."'";
        else
            $where .= " AND status = '".$status."'";
    }

    $pagelimit = "";
    if($page != null && $limit != null){
        $pagelimit = "LIMIT  ".($page-1)*$limit.",".$limit;
    }
    $query = "SELECT * FROM `".$config['db']['pre']."product_resubmit` $where ORDER BY $sort DESC $pagelimit";
    $result = db_connect($config)->query($query);
    if (mysqli_num_rows($result) > 0) {
        while($info = mysqli_fetch_assoc($result)) {
            //$item[$info['id']]['product_name'] = strlimiter($info['product_name'],16);
            $item[$info['id']]['id'] = $info['id'];
            $item[$info['id']]['product_id'] = $info['product_id'];
            $item[$info['id']]['product_name'] = $info['product_name'];
            $item[$info['id']]['desc'] = strlimiter($info['description'],80);
            $item[$info['id']]['featured'] = $info['featured'];
            $item[$info['id']]['urgent'] = $info['urgent'];
            $item[$info['id']]['highlight'] = $info['highlight'];
            $item[$info['id']]['address'] = strlimiter($info['location'],20);
            $item[$info['id']]['location'] = get_cityName_by_id($config,$info['city']);
            $item[$info['id']]['city'] = $info['city'];
            $item[$info['id']]['state'] = $info['state'];
            $item[$info['id']]['country'] = $info['country'];
            $item[$info['id']]['status'] = $info['status'];
            $item[$info['id']]['created_at'] = timeago($info['created_at']);
            $item[$info['id']]['author_id'] = $info['user_id'];

            $price = price_format($info['price'],$info['country']);
            $item[$info['id']]['price'] = $price;

            $item[$info['id']]['cat_id'] = $info['category'];
            $item[$info['id']]['sub_cat_id'] = $info['sub_category'];

            $get_main = get_maincat_by_id($config,$info['category']);
            $get_sub = get_subcat_by_id($config,$info['sub_category']);
            $item[$info['id']]['category'] = $get_main['cat_name'];
            $item[$info['id']]['sub_category'] = $get_sub['sub_cat_name'];
            $catslug = $get_main['slug'];
            $subcatslug = $get_sub['slug'];

            $item[$info['id']]['favorite'] = check_product_favorite($config,$info['id']);

            $tag = explode(',', $info['tag']);
            $tag2 = array();
            foreach ($tag as $val)
            {
                //REMOVE SPACE FROM $VALUE ----
                $val = trim($val);
                $tag2[] = '<li><a href="'.$config['site_url'].'keywords/'.$val.'">'.$val.'</a> </li>';
            }
            $item[$info['id']]['tag'] = implode('  ', $tag2);

            $picture     =   explode(',' ,$info['screen_shot']);
            $item[$info['id']]['pic_count'] = count($picture);

            if($picture[0] != ""){
                $item[$info['id']]['picture'] = $picture[0];
            }else{
                $item[$info['id']]['picture'] = "default.png";
            }

            $pro_url = create_slug($info['product_name']);

            $item[$info['id']]['link'] = $config['site_url'].'ad/' . $info['id'] . '/'.$pro_url;


            $userinfo = get_user_data($config,null,$info['user_id']);

            $item[$info['id']]['username'] = $userinfo['username'];
            $author_url = create_slug($userinfo['username']);

            $item[$info['id']]['author_link'] = $config['site_url'].'profile/'.$author_url;

            $item[$info['id']]['catlink'] = $config['site_url'].'category/'.$catslug;

            $item[$info['id']]['subcatlink'] = $config['site_url'].'category/'.$catslug.'/'.$subcatslug;

            $city = create_slug($item[$info['id']]['city']);
            $item[$info['id']]['citylink'] = $config['site_url'].'city/'.$info['city'].'/'.$city;
        }
    }
    else {
        //echo "0 results";
    }
    return $item;
}

function count_product_review($config,$productid){
    $count = mysqli_num_rows(mysqli_query(db_connect($config), "SELECT rating FROM ".$config['db']['pre']."reviews WHERE productID='".$productid."' AND publish=1"));
    return $count;
}

function get_items_count($config,$userid=false,$status=null,$premium=false,$getbysubcat=null,$getbymaincat=null,$location=false){
    $where = '';
    if($userid){
        if($where == '')
            $where .= "where user_id = '".$userid."'";
        else
            $where .= " AND user_id = '".$userid."'";
    }

    if($status != null){
        if($where == '')
            $where .= "where status = '".$status."'";
        else
            $where .= " AND status = '".$status."'";
    }

    if($premium){
        if($where == '')
            $where .= "where (featured = '1' or urgent = '1' or highlight = '1')";
        else
            $where .= " AND (featured = '1' or urgent = '1' or highlight = '1')";
    }

    if($getbysubcat != null){
        if($where == '')
            $where .= "where sub_category = '".$getbysubcat."'";
        else
            $where .= " AND sub_category = '".$getbysubcat."'";
    }

    if($getbymaincat != null){
        if($where == '')
            $where .= "where category = '".$getbymaincat."'";
        else
            $where .= " AND category = '".$getbymaincat."'";
    }

    if($location){
        $country_code = check_user_country($config);
        if($where == '')
            $where .= "where country = '".$country_code."'";
        else
            $where .= " AND country = '".$country_code."'";
    }

    $query = "SELECT 1 FROM `".$config['db']['pre']."product` $where ORDER BY id";
    $result = db_connect($config)->query($query);
    $item_count = mysqli_num_rows($result);
    return $item_count;
}

function resubmited_ads_count($config,$id){
    $query = "SELECT id FROM `".$config['db']['pre']."product_resubmit` where user_id = '".$id."'";
    $result = db_connect($config)->query($query);
    $num_rows = mysqli_num_rows($result);
    return $num_rows;
}

function myads_count($config,$id){
    $query = "SELECT id FROM ".$config['db']['pre']."product WHERE `user_id` = '".$id."'";
    $query_result = mysqli_query(db_connect($config),$query);
    $num_rows = mysqli_num_rows($query_result);
    return $num_rows;
}

function active_ads_count($config,$id){
    $query = "SELECT id FROM ".$config['db']['pre']."product WHERE `user_id` = '".$id."' and status = 'active'";
    $query_result = mysqli_query(db_connect($config),$query);
    $num_rows = mysqli_num_rows($query_result);
    return $num_rows;
}

function pending_ads_count($config,$id){
    $query = "SELECT id FROM ".$config['db']['pre']."product WHERE `user_id` = '".$id."' and status = 'pending'";
    $query_result = mysqli_query(db_connect($config),$query);
    $num_rows = mysqli_num_rows($query_result);
    return $num_rows;
}

function hidden_ads_count($config,$id){
    $query = "SELECT id FROM ".$config['db']['pre']."product WHERE `user_id` = '".$id."' and status = 'hide'";
    $query_result = mysqli_query(db_connect($config),$query);
    $num_rows = mysqli_num_rows($query_result);
    return $num_rows;
}

function favorite_ads_count($config,$id){
    $query = "SELECT id FROM ".$config['db']['pre']."favads WHERE `user_id` = '".$id."'";
    $query_result = mysqli_query(db_connect($config),$query);
    $num_rows = mysqli_num_rows($query_result);
    return $num_rows;
}

function transaction_success($transaction_id)
{
    global $config;
    $mysqli = db_connect($config);

    $result = $mysqli->query("SELECT * FROM `".$config['db']['pre']."transaction` WHERE `id` = '" . $transaction_id . "' LIMIT 1");
    if (mysqli_num_rows($result) > 0) {
        // output data of each row
        $info = mysqli_fetch_assoc($result);

        $item_pro_id = $info['product_id'];
        $seller_id = $info['seller_id'];
        $item_amount = $info['amount'];
        $item_featured = $info['featured'];
        $item_urgent = $info['urgent'];
        $item_highlight = $info['highlight'];

        if($item_featured == 1){
            $mysqli->query("UPDATE ". $config['db']['pre'] . "product set featured = '$item_featured' where id='".$item_pro_id."' LIMIT 1");
        }
        if($item_urgent == 1){
            $mysqli->query("UPDATE ". $config['db']['pre'] . "product set urgent = '$item_urgent' where id='".$item_pro_id."' LIMIT 1");
        }
        if($item_highlight == 1){
            $mysqli->query("UPDATE ". $config['db']['pre'] . "product set highlight = '$item_highlight' where id='".$item_pro_id."' LIMIT 1");
        }

        $mysqli->query("UPDATE ". $config['db']['pre'] . "transaction set status = 'success' where id='".$transaction_id."' LIMIT 1");


        $query = "SELECT 1 FROM ".$config['db']['pre']."product_resubmit WHERE product_id='" . $item_pro_id . "' and user_id='" . $seller_id . "' LIMIT 1";
        $query_result = mysqli_query(db_connect($config), $query);
        $num_rows = mysqli_num_rows($query_result);
        if($num_rows == 1){
            if($item_featured == 1){
                $mysqli->query("UPDATE ". $config['db']['pre'] . "product_resubmit set featured = '$item_featured' where product_id='".$item_pro_id."' LIMIT 1");
            }
            if($item_urgent == 1){
                $mysqli->query("UPDATE ". $config['db']['pre'] . "product_resubmit set urgent = '$item_urgent' where product_id='".$item_pro_id."' LIMIT 1");
            }
            if($item_highlight == 1){
                $mysqli->query("UPDATE ". $config['db']['pre'] . "product_resubmit set highlight = '$item_highlight' where product_id='".$item_pro_id."' LIMIT 1");
            }
        }

        $result2 = $mysqli->query("SELECT * FROM `".$config['db']['pre']."balance` WHERE id = '1' LIMIT 1");
        if (mysqli_num_rows($result2) > 0) {
            $info2 = mysqli_fetch_assoc($result2);
            $current_amount=$info2['current_balance'];
            $total_earning=$info2['total_earning'];

            $updated_amount=($item_amount+$current_amount);
            $total_earning=($item_amount+$total_earning);

            $mysqli->query("UPDATE ". $config['db']['pre'] . "balance set current_balance = '" . $updated_amount . "', total_earning = '" . $total_earning . "' where id='1' LIMIT 1");
        }
        return true;
    }
    else{
        return false;
    }

}

function update_itemview($product_id,$config)
{
    mysqli_query(db_connect($config), "UPDATE `".$config['db']['pre']."product` SET `view` = view+1 WHERE `id` = '".$product_id."' LIMIT 1 ;");

}

function timeAgo($timestamp){
    global $lang;
    //$time_now = mktime(date('h')+0,date('i')+30,date('s'));
    $datetime1=new DateTime("now");
    $datetime2=date_create($timestamp);
    $diff=date_diff($datetime1, $datetime2);
    $timemsg='';
    if($diff->y > 0){
        $timemsg = $diff->y .' '. ($diff->y > 1?$lang['YEARS']:$lang['YEAR']);
    }
    else if($diff->m > 0){
        $timemsg = $diff->m .' '. ($diff->m > 1?$lang['MONTHS']:$lang['MONTH']);
    }
    else if($diff->d > 0){
        $timemsg = $diff->d .' '. ($diff->d > 1?$lang['DAYS']:$lang['DAY']);
    }
    else if($diff->h > 0){
        $timemsg = $diff->h .' '. ($diff->h > 1 ? $lang['HOURS']:$lang['HOUR']);
    }
    else if($diff->i > 0){
        $timemsg = $diff->i .' '. ($diff->i > 1?$lang['MINUTES']:$lang['MINUTE']);
    }
    else if($diff->s > 0){
        $timemsg = $diff->s .' '. ($diff->s > 1?$lang['SECONDS']:$lang['SECONDS']);
    }
    if($timemsg == "")
        $timemsg = $lang['JUST_NOW'];
    else
        $timemsg = $timemsg.' '.$lang['AGO'];

    return $timemsg;
}





/**
 * Friendly UTF-8 URL for all languages
 *
 * @param $string
 * @param string $separator
 * @return mixed|string
 */
function slugify($string, $separator = '-')
{
    // Remove accents
    $string = remove_accents($string);

    // Slug
    $string = strtolower($string);
    $string = @trim($string);
    $replace = "/(\\s|\\" . $separator . ")+/mu";
    $subst = $separator;
    $string = preg_replace($replace, $subst, $string);

    // Remove unwanted punctuation, convert some to '-'
    $punc_table = array(
        // remove
        "'" => '',
        '"' => '',
        '`' => '',
        '=' => '',
        '+' => '',
        '*' => '',
        '&' => '',
        '^' => '',
        '' => '',
        '%' => '',
        '$' => '',
        '#' => '',
        '@' => '',
        '!' => '',
        '<' => '',
        '>' => '',
        '?' => '',
        // convert to minus
        '[' => '-',
        ']' => '-',
        '{' => '-',
        '}' => '-',
        '(' => '-',
        ')' => '-',
        ' ' => '-',
        ',' => '-',
        ';' => '-',
        ':' => '-',
        '/' => '-',
        '|' => '-'
    );
    $string = str_replace(array_keys($punc_table), array_values($punc_table), $string);

    // Clean up multiple '-' characters
    $string = preg_replace('/-{2,}/', '-', $string);

    // Remove trailing '-' character if string not just '-'
    if ($string != '-') {
        $string = rtrim($string, '-');
    }

    //$string = rawurlencode($string);

    return $string;
}

/**
 * Converts all accent characters to ASCII characters.
 *
 * If there are no accent characters, then the string given is just returned.
 *
 * @since 1.2.1
 *
 * @param string $string Text that might have accent characters
 * @return string Filtered string with replaced "nice" characters.
 */
function remove_accents($string)
{
    global $config;
    if (!preg_match('/[\x80-\xff]/', $string)) {
        return $string;
    }

    if (seems_utf8($string)) {
        $chars = array(
            // Decompositions for Latin-1 Supplement
            chr(194) . chr(170) => 'a',
            chr(194) . chr(186) => 'o',
            chr(195) . chr(128) => 'A',
            chr(195) . chr(129) => 'A',
            chr(195) . chr(130) => 'A',
            chr(195) . chr(131) => 'A',
            chr(195) . chr(132) => 'A',
            chr(195) . chr(133) => 'A',
            chr(195) . chr(134) => 'AE',
            chr(195) . chr(135) => 'C',
            chr(195) . chr(136) => 'E',
            chr(195) . chr(137) => 'E',
            chr(195) . chr(138) => 'E',
            chr(195) . chr(139) => 'E',
            chr(195) . chr(140) => 'I',
            chr(195) . chr(141) => 'I',
            chr(195) . chr(142) => 'I',
            chr(195) . chr(143) => 'I',
            chr(195) . chr(144) => 'D',
            chr(195) . chr(145) => 'N',
            chr(195) . chr(146) => 'O',
            chr(195) . chr(147) => 'O',
            chr(195) . chr(148) => 'O',
            chr(195) . chr(149) => 'O',
            chr(195) . chr(150) => 'O',
            chr(195) . chr(153) => 'U',
            chr(195) . chr(154) => 'U',
            chr(195) . chr(155) => 'U',
            chr(195) . chr(156) => 'U',
            chr(195) . chr(157) => 'Y',
            chr(195) . chr(158) => 'TH',
            chr(195) . chr(159) => 's',
            chr(195) . chr(160) => 'a',
            chr(195) . chr(161) => 'a',
            chr(195) . chr(162) => 'a',
            chr(195) . chr(163) => 'a',
            chr(195) . chr(164) => 'a',
            chr(195) . chr(165) => 'a',
            chr(195) . chr(166) => 'ae',
            chr(195) . chr(167) => 'c',
            chr(195) . chr(168) => 'e',
            chr(195) . chr(169) => 'e',
            chr(195) . chr(170) => 'e',
            chr(195) . chr(171) => 'e',
            chr(195) . chr(172) => 'i',
            chr(195) . chr(173) => 'i',
            chr(195) . chr(174) => 'i',
            chr(195) . chr(175) => 'i',
            chr(195) . chr(176) => 'd',
            chr(195) . chr(177) => 'n',
            chr(195) . chr(178) => 'o',
            chr(195) . chr(179) => 'o',
            chr(195) . chr(180) => 'o',
            chr(195) . chr(181) => 'o',
            chr(195) . chr(182) => 'o',
            chr(195) . chr(184) => 'o',
            chr(195) . chr(185) => 'u',
            chr(195) . chr(186) => 'u',
            chr(195) . chr(187) => 'u',
            chr(195) . chr(188) => 'u',
            chr(195) . chr(189) => 'y',
            chr(195) . chr(190) => 'th',
            chr(195) . chr(191) => 'y',
            chr(195) . chr(152) => 'O',
            // Decompositions for Latin Extended-A
            chr(196) . chr(128) => 'A',
            chr(196) . chr(129) => 'a',
            chr(196) . chr(130) => 'A',
            chr(196) . chr(131) => 'a',
            chr(196) . chr(132) => 'A',
            chr(196) . chr(133) => 'a',
            chr(196) . chr(134) => 'C',
            chr(196) . chr(135) => 'c',
            chr(196) . chr(136) => 'C',
            chr(196) . chr(137) => 'c',
            chr(196) . chr(138) => 'C',
            chr(196) . chr(139) => 'c',
            chr(196) . chr(140) => 'C',
            chr(196) . chr(141) => 'c',
            chr(196) . chr(142) => 'D',
            chr(196) . chr(143) => 'd',
            chr(196) . chr(144) => 'D',
            chr(196) . chr(145) => 'd',
            chr(196) . chr(146) => 'E',
            chr(196) . chr(147) => 'e',
            chr(196) . chr(148) => 'E',
            chr(196) . chr(149) => 'e',
            chr(196) . chr(150) => 'E',
            chr(196) . chr(151) => 'e',
            chr(196) . chr(152) => 'E',
            chr(196) . chr(153) => 'e',
            chr(196) . chr(154) => 'E',
            chr(196) . chr(155) => 'e',
            chr(196) . chr(156) => 'G',
            chr(196) . chr(157) => 'g',
            chr(196) . chr(158) => 'G',
            chr(196) . chr(159) => 'g',
            chr(196) . chr(160) => 'G',
            chr(196) . chr(161) => 'g',
            chr(196) . chr(162) => 'G',
            chr(196) . chr(163) => 'g',
            chr(196) . chr(164) => 'H',
            chr(196) . chr(165) => 'h',
            chr(196) . chr(166) => 'H',
            chr(196) . chr(167) => 'h',
            chr(196) . chr(168) => 'I',
            chr(196) . chr(169) => 'i',
            chr(196) . chr(170) => 'I',
            chr(196) . chr(171) => 'i',
            chr(196) . chr(172) => 'I',
            chr(196) . chr(173) => 'i',
            chr(196) . chr(174) => 'I',
            chr(196) . chr(175) => 'i',
            chr(196) . chr(176) => 'I',
            chr(196) . chr(177) => 'i',
            chr(196) . chr(178) => 'IJ',
            chr(196) . chr(179) => 'ij',
            chr(196) . chr(180) => 'J',
            chr(196) . chr(181) => 'j',
            chr(196) . chr(182) => 'K',
            chr(196) . chr(183) => 'k',
            chr(196) . chr(184) => 'k',
            chr(196) . chr(185) => 'L',
            chr(196) . chr(186) => 'l',
            chr(196) . chr(187) => 'L',
            chr(196) . chr(188) => 'l',
            chr(196) . chr(189) => 'L',
            chr(196) . chr(190) => 'l',
            chr(196) . chr(191) => 'L',
            chr(197) . chr(128) => 'l',
            chr(197) . chr(129) => 'L',
            chr(197) . chr(130) => 'l',
            chr(197) . chr(131) => 'N',
            chr(197) . chr(132) => 'n',
            chr(197) . chr(133) => 'N',
            chr(197) . chr(134) => 'n',
            chr(197) . chr(135) => 'N',
            chr(197) . chr(136) => 'n',
            chr(197) . chr(137) => 'N',
            chr(197) . chr(138) => 'n',
            chr(197) . chr(139) => 'N',
            chr(197) . chr(140) => 'O',
            chr(197) . chr(141) => 'o',
            chr(197) . chr(142) => 'O',
            chr(197) . chr(143) => 'o',
            chr(197) . chr(144) => 'O',
            chr(197) . chr(145) => 'o',
            chr(197) . chr(146) => 'OE',
            chr(197) . chr(147) => 'oe',
            chr(197) . chr(148) => 'R',
            chr(197) . chr(149) => 'r',
            chr(197) . chr(150) => 'R',
            chr(197) . chr(151) => 'r',
            chr(197) . chr(152) => 'R',
            chr(197) . chr(153) => 'r',
            chr(197) . chr(154) => 'S',
            chr(197) . chr(155) => 's',
            chr(197) . chr(156) => 'S',
            chr(197) . chr(157) => 's',
            chr(197) . chr(158) => 'S',
            chr(197) . chr(159) => 's',
            chr(197) . chr(160) => 'S',
            chr(197) . chr(161) => 's',
            chr(197) . chr(162) => 'T',
            chr(197) . chr(163) => 't',
            chr(197) . chr(164) => 'T',
            chr(197) . chr(165) => 't',
            chr(197) . chr(166) => 'T',
            chr(197) . chr(167) => 't',
            chr(197) . chr(168) => 'U',
            chr(197) . chr(169) => 'u',
            chr(197) . chr(170) => 'U',
            chr(197) . chr(171) => 'u',
            chr(197) . chr(172) => 'U',
            chr(197) . chr(173) => 'u',
            chr(197) . chr(174) => 'U',
            chr(197) . chr(175) => 'u',
            chr(197) . chr(176) => 'U',
            chr(197) . chr(177) => 'u',
            chr(197) . chr(178) => 'U',
            chr(197) . chr(179) => 'u',
            chr(197) . chr(180) => 'W',
            chr(197) . chr(181) => 'w',
            chr(197) . chr(182) => 'Y',
            chr(197) . chr(183) => 'y',
            chr(197) . chr(184) => 'Y',
            chr(197) . chr(185) => 'Z',
            chr(197) . chr(186) => 'z',
            chr(197) . chr(187) => 'Z',
            chr(197) . chr(188) => 'z',
            chr(197) . chr(189) => 'Z',
            chr(197) . chr(190) => 'z',
            chr(197) . chr(191) => 's',
            // Decompositions for Latin Extended-B
            chr(200) . chr(152) => 'S',
            chr(200) . chr(153) => 's',
            chr(200) . chr(154) => 'T',
            chr(200) . chr(155) => 't',
            // Euro Sign
            chr(226) . chr(130) . chr(172) => 'E',
            // GBP (Pound) Sign
            chr(194) . chr(163) => '',
            // Vowels with diacritic (Vietnamese)
            // unmarked
            chr(198) . chr(160) => 'O',
            chr(198) . chr(161) => 'o',
            chr(198) . chr(175) => 'U',
            chr(198) . chr(176) => 'u',
            // grave accent
            chr(225) . chr(186) . chr(166) => 'A',
            chr(225) . chr(186) . chr(167) => 'a',
            chr(225) . chr(186) . chr(176) => 'A',
            chr(225) . chr(186) . chr(177) => 'a',
            chr(225) . chr(187) . chr(128) => 'E',
            chr(225) . chr(187) . chr(129) => 'e',
            chr(225) . chr(187) . chr(146) => 'O',
            chr(225) . chr(187) . chr(147) => 'o',
            chr(225) . chr(187) . chr(156) => 'O',
            chr(225) . chr(187) . chr(157) => 'o',
            chr(225) . chr(187) . chr(170) => 'U',
            chr(225) . chr(187) . chr(171) => 'u',
            chr(225) . chr(187) . chr(178) => 'Y',
            chr(225) . chr(187) . chr(179) => 'y',
            // hook
            chr(225) . chr(186) . chr(162) => 'A',
            chr(225) . chr(186) . chr(163) => 'a',
            chr(225) . chr(186) . chr(168) => 'A',
            chr(225) . chr(186) . chr(169) => 'a',
            chr(225) . chr(186) . chr(178) => 'A',
            chr(225) . chr(186) . chr(179) => 'a',
            chr(225) . chr(186) . chr(186) => 'E',
            chr(225) . chr(186) . chr(187) => 'e',
            chr(225) . chr(187) . chr(130) => 'E',
            chr(225) . chr(187) . chr(131) => 'e',
            chr(225) . chr(187) . chr(136) => 'I',
            chr(225) . chr(187) . chr(137) => 'i',
            chr(225) . chr(187) . chr(142) => 'O',
            chr(225) . chr(187) . chr(143) => 'o',
            chr(225) . chr(187) . chr(148) => 'O',
            chr(225) . chr(187) . chr(149) => 'o',
            chr(225) . chr(187) . chr(158) => 'O',
            chr(225) . chr(187) . chr(159) => 'o',
            chr(225) . chr(187) . chr(166) => 'U',
            chr(225) . chr(187) . chr(167) => 'u',
            chr(225) . chr(187) . chr(172) => 'U',
            chr(225) . chr(187) . chr(173) => 'u',
            chr(225) . chr(187) . chr(182) => 'Y',
            chr(225) . chr(187) . chr(183) => 'y',
            // tilde
            chr(225) . chr(186) . chr(170) => 'A',
            chr(225) . chr(186) . chr(171) => 'a',
            chr(225) . chr(186) . chr(180) => 'A',
            chr(225) . chr(186) . chr(181) => 'a',
            chr(225) . chr(186) . chr(188) => 'E',
            chr(225) . chr(186) . chr(189) => 'e',
            chr(225) . chr(187) . chr(132) => 'E',
            chr(225) . chr(187) . chr(133) => 'e',
            chr(225) . chr(187) . chr(150) => 'O',
            chr(225) . chr(187) . chr(151) => 'o',
            chr(225) . chr(187) . chr(160) => 'O',
            chr(225) . chr(187) . chr(161) => 'o',
            chr(225) . chr(187) . chr(174) => 'U',
            chr(225) . chr(187) . chr(175) => 'u',
            chr(225) . chr(187) . chr(184) => 'Y',
            chr(225) . chr(187) . chr(185) => 'y',
            // acute accent
            chr(225) . chr(186) . chr(164) => 'A',
            chr(225) . chr(186) . chr(165) => 'a',
            chr(225) . chr(186) . chr(174) => 'A',
            chr(225) . chr(186) . chr(175) => 'a',
            chr(225) . chr(186) . chr(190) => 'E',
            chr(225) . chr(186) . chr(191) => 'e',
            chr(225) . chr(187) . chr(144) => 'O',
            chr(225) . chr(187) . chr(145) => 'o',
            chr(225) . chr(187) . chr(154) => 'O',
            chr(225) . chr(187) . chr(155) => 'o',
            chr(225) . chr(187) . chr(168) => 'U',
            chr(225) . chr(187) . chr(169) => 'u',
            // dot below
            chr(225) . chr(186) . chr(160) => 'A',
            chr(225) . chr(186) . chr(161) => 'a',
            chr(225) . chr(186) . chr(172) => 'A',
            chr(225) . chr(186) . chr(173) => 'a',
            chr(225) . chr(186) . chr(182) => 'A',
            chr(225) . chr(186) . chr(183) => 'a',
            chr(225) . chr(186) . chr(184) => 'E',
            chr(225) . chr(186) . chr(185) => 'e',
            chr(225) . chr(187) . chr(134) => 'E',
            chr(225) . chr(187) . chr(135) => 'e',
            chr(225) . chr(187) . chr(138) => 'I',
            chr(225) . chr(187) . chr(139) => 'i',
            chr(225) . chr(187) . chr(140) => 'O',
            chr(225) . chr(187) . chr(141) => 'o',
            chr(225) . chr(187) . chr(152) => 'O',
            chr(225) . chr(187) . chr(153) => 'o',
            chr(225) . chr(187) . chr(162) => 'O',
            chr(225) . chr(187) . chr(163) => 'o',
            chr(225) . chr(187) . chr(164) => 'U',
            chr(225) . chr(187) . chr(165) => 'u',
            chr(225) . chr(187) . chr(176) => 'U',
            chr(225) . chr(187) . chr(177) => 'u',
            chr(225) . chr(187) . chr(180) => 'Y',
            chr(225) . chr(187) . chr(181) => 'y',
            // Vowels with diacritic (Chinese, Hanyu Pinyin)
            chr(201) . chr(145) => 'a',
            // macron
            chr(199) . chr(149) => 'U',
            chr(199) . chr(150) => 'u',
            // acute accent
            chr(199) . chr(151) => 'U',
            chr(199) . chr(152) => 'u',
            // caron
            chr(199) . chr(141) => 'A',
            chr(199) . chr(142) => 'a',
            chr(199) . chr(143) => 'I',
            chr(199) . chr(144) => 'i',
            chr(199) . chr(145) => 'O',
            chr(199) . chr(146) => 'o',
            chr(199) . chr(147) => 'U',
            chr(199) . chr(148) => 'u',
            chr(199) . chr(153) => 'U',
            chr(199) . chr(154) => 'u',
            // grave accent
            chr(199) . chr(155) => 'U',
            chr(199) . chr(156) => 'u',
        );

        // Used for locale-specific rules
        $locale = $config['lang_code'] = get_current_lang_code($config);

        if ('de_DE' == $locale || 'de_DE_formal' == $locale) {
            $chars[chr(195) . chr(132)] = 'Ae';
            $chars[chr(195) . chr(164)] = 'ae';
            $chars[chr(195) . chr(150)] = 'Oe';
            $chars[chr(195) . chr(182)] = 'oe';
            $chars[chr(195) . chr(156)] = 'Ue';
            $chars[chr(195) . chr(188)] = 'ue';
            $chars[chr(195) . chr(159)] = 'ss';
        } elseif ('da_DK' === $locale) {
            $chars[chr(195) . chr(134)] = 'Ae';
            $chars[chr(195) . chr(166)] = 'ae';
            $chars[chr(195) . chr(152)] = 'Oe';
            $chars[chr(195) . chr(184)] = 'oe';
            $chars[chr(195) . chr(133)] = 'Aa';
            $chars[chr(195) . chr(165)] = 'aa';
        }

        $string = strtr($string, $chars);
    } else {
        $chars = array();
        // Assume ISO-8859-1 if not UTF-8
        $chars['in'] = chr(128) . chr(131) . chr(138) . chr(142) . chr(154) . chr(158) . chr(159) . chr(162) . chr(165) . chr(181) . chr(192) . chr(193) . chr(194) . chr(195) . chr(196) . chr(197) . chr(199) . chr(200) . chr(201) . chr(202) . chr(203) . chr(204) . chr(205) . chr(206) . chr(207) . chr(209) . chr(210) . chr(211) . chr(212) . chr(213) . chr(214) . chr(216) . chr(217) . chr(218) . chr(219) . chr(220) . chr(221) . chr(224) . chr(225) . chr(226) . chr(227) . chr(228) . chr(229) . chr(231) . chr(232) . chr(233) . chr(234) . chr(235) . chr(236) . chr(237) . chr(238) . chr(239) . chr(241) . chr(242) . chr(243) . chr(244) . chr(245) . chr(246) . chr(248) . chr(249) . chr(250) . chr(251) . chr(252) . chr(253) . chr(255);

        $chars['out'] = "EfSZszYcYuAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy";

        $string = strtr($string, $chars['in'], $chars['out']);
        $double_chars = array();
        $double_chars['in'] = array(chr(140), chr(156), chr(198), chr(208), chr(222), chr(223), chr(230), chr(240), chr(254));
        $double_chars['out'] = array('OE', 'oe', 'AE', 'DH', 'TH', 'ss', 'ae', 'dh', 'th');
        $string = str_replace($double_chars['in'], $double_chars['out'], $string);
    }

    return $string;
}

/**
 * Checks to see if a string is utf8 encoded.
 *
 * NOTE: This function checks for 5-Byte sequences, UTF8
 *       has Bytes Sequences with a maximum length of 4.
 *
 * @author bmorel at ssi dot fr (modified)
 * @since 1.2.1
 *
 * @param string $str The string to be checked
 * @return bool True if $str fits a UTF-8 model, false otherwise.
 */
function seems_utf8($str)
{
    mbstring_binary_safe_encoding();
    $length = strlen($str);
    reset_mbstring_encoding();
    for ($i = 0; $i < $length; $i++) {
        $c = ord($str[$i]);
        if ($c < 0x80) {
            $n = 0;
        } // 0bbbbbbb
        elseif (($c & 0xE0) == 0xC0) {
            $n = 1;
        } // 110bbbbb
        elseif (($c & 0xF0) == 0xE0) {
            $n = 2;
        } // 1110bbbb
        elseif (($c & 0xF8) == 0xF0) {
            $n = 3;
        } // 11110bbb
        elseif (($c & 0xFC) == 0xF8) {
            $n = 4;
        } // 111110bb
        elseif (($c & 0xFE) == 0xFC) {
            $n = 5;
        } // 1111110b
        else {
            return false;
        } // Does not match any model
        for ($j = 0; $j < $n; $j++) { // n bytes matching 10bbbbbb follow ?
            if ((++$i == $length) || ((ord($str[$i]) & 0xC0) != 0x80)) {
                return false;
            }
        }
    }

    return true;
}
/**
 * Set the mbstring internal encoding to a binary safe encoding when func_overload
 * is enabled.
 *
 * When mbstring.func_overload is in use for multi-byte encodings, the results from
 * strlen() and similar functions respect the utf8 characters, causing binary data
 * to return incorrect lengths.
 *
 * This function overrides the mbstring encoding to a binary-safe encoding, and
 * resets it to the users expected encoding afterwards through the
 * `reset_mbstring_encoding` function.
 *
 * It is safe to recursively call this function, however each
 * `mbstring_binary_safe_encoding()` call must be followed up with an equal number
 * of `reset_mbstring_encoding()` calls.
 *
 * @since 3.7.0
 *
 * @see reset_mbstring_encoding()
 *
 * @staticvar array $encodings
 * @staticvar bool  $overloaded
 *
 * @param bool $reset Optional. Whether to reset the encoding back to a previously-set encoding.
 *                    Default false.
 */
function mbstring_binary_safe_encoding($reset = false)
{
    static $encodings = array();
    static $overloaded = null;

    if (is_null($overloaded)) {
        $overloaded = function_exists('mb_internal_encoding') && (ini_get('mbstring.func_overload') & 2);
    }

    if (false === $overloaded) {
        return;
    }

    if (!$reset) {
        $encoding = mb_internal_encoding();
        array_push($encodings, $encoding);
        mb_internal_encoding('ISO-8859-1');
    }

    if ($reset && $encodings) {
        $encoding = array_pop($encodings);
        mb_internal_encoding($encoding);
    }
}

/**
 * Reset the mbstring internal encoding to a users previously set encoding.
 *
 * @see mbstring_binary_safe_encoding()
 *
 * @since 3.7.0
 */
function reset_mbstring_encoding()
{
    mbstring_binary_safe_encoding(true);
}
?>