<?php
$config['lang'] = check_user_lang($config);
$config['lang_code'] = get_current_lang_code();
$config['tpl_name'] = check_user_theme($config);
function get_random_id()
{
    $random = '';

    for ($i = 1; $i <= 8; $i++)
    {
        $random.= mt_rand(0, 9);
    }

    return $random;
}



function change_user_country($config,$country_code)
{
    if(get_option($config,"country_type") == "multi"){
        $countryName = get_countryName_by_sortname($config,$country_code);
        if(!$countryName) return;
        $_SESSION['user']['country'] = $country_code;
        set_user_currency($config,$country_code);
    }
}

function check_user_country($config)
{
    if(isset($_SESSION['user']['country']))
    {
        $country = $_SESSION['user']['country'];
    }
    else{

        /*$ip = $_SERVER['REMOTE_ADDR'];
        $details = json_decode(file_get_contents("http://ipinfo.io/{$ip}/json"));

        if(isset($details->country) && $config['country_type'] == 'multi'){
            $_SESSION['user']['country'] = $details->country;
            $country = $details->country;
        }else{
            $_SESSION['user']['country'] = $config['specific_country'];
            $country = $_SESSION['user']['country'];
        }*/

        $_SESSION['user']['country'] = $config['specific_country'];
        $country = $_SESSION['user']['country'];
    }
    return $country;
}

function set_user_currency($config,$country)
{
    $query = "SELECT currency_code FROM ".$config['db']['pre']."countries WHERE code='" . $country . "' LIMIT 1";
    $query_result = mysqli_query(db_connect($config), $query);
    $info = mysqli_fetch_assoc($query_result);
    $currency_code = $info['currency_code'];


    $query = "SELECT * FROM ".$config['db']['pre']."currencies WHERE code='" . $currency_code . "' LIMIT 1";
    $query_result = mysqli_query(db_connect($config), $query);
    $currency_info = mysqli_fetch_assoc($query_result);

    /*$config['currency_code'] = $info['code'];
    $config['currency_sign'] = $info['html_entity'];
    $config['currency_pos'] = $info['in_left'];*/

    return $currency_info;
}

function change_user_lang($lang_code){
    global $config;
    $lang_code = get_language_by_code($lang_code);
    if(!$lang_code) return;
    $cookie_name = "Quick_lang";
    $cookie_value = $lang_code['file_name'];
    setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/");
    if($config['userlangsel'] == '1')
    {
        $config['lang'] = $lang_code['file_name'];
    }
}

function check_user_lang($config)
{
    if($config['userlangsel'] == '1')
    {
        $cookie_name = "Quick_lang";
        if(isset($_COOKIE[$cookie_name])) {
            $config['lang'] = $_COOKIE[$cookie_name];
        }
    }
    return $config['lang'];
}

function get_current_lang_code(){
    global $config;
    $con = db_connect($config);
    $query = "SELECT code FROM ".$config['db']['pre']."languages WHERE file_name='" . $config['lang'] . "' LIMIT 1";
    $query_result = mysqli_query($con,$query) OR error(mysqli_error($con),__LINE__,__FILE__);
    $info = mysqli_fetch_assoc($query_result);
    return $info['code'];
}

function check_user_theme($config)
{
    if($config['userthemesel'])
    {
        $cookie_name = "Quick_theme";
        if(isset($_COOKIE[$cookie_name])) {
            $config['tpl_name'] = $_COOKIE[$cookie_name];
        }
    }

    return $config['tpl_name'];
}

function check_account_exists($config,$email)
{
    $mysqli = db_connect($config);

    $prep_stmt = "SELECT id FROM ".$config['db']['pre']."user WHERE email = ? LIMIT 1";
    $stmt = $mysqli->prepare($prep_stmt);

    // check existing email
    if ($stmt) {
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();

        return $stmt->num_rows;

    } else {
        return 0;
        $stmt->close();
    }
}

function check_table_row_exists($id_field_name,$id_value,$table_name)
{
    global $config;
    $mysqli = db_connect($config);

    $prep_stmt = "SELECT id FROM ".$config['db']['pre'].".$table_name WHERE $id_field_name = ? LIMIT 1";
    $stmt = $mysqli->prepare($prep_stmt);

    // check existing email
    if ($stmt) {
        $stmt->bind_param('s', $id_value);
        $stmt->execute();
        $stmt->store_result();

        return $stmt->num_rows;

    } else {
        return 0;
        $stmt->close();
    }
}

function check_username_exists($config,$username)
{
    $mysqli = db_connect($config);

    /*$query = "SELECT id FROM ".$config['db']['pre']."user WHERE username='" . $username . "' LIMIT 1";
    $query_result = mysqli_query(db_connect($config), $query);
    $num_rows = mysqli_num_rows($query_result);

    return $num_rows;*/

    // check existing username
    $prep_stmt = "SELECT id FROM ".$config['db']['pre']."user WHERE username = ? LIMIT 1";
    $stmt = $mysqli->prepare($prep_stmt);

    if ($stmt) {
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $stmt->store_result();

        return $stmt->num_rows;
        $stmt->close();

    } else {
        return 0;
        $stmt->close();
    }
}

function createusernameslug($config,$title)
{
    $slug = $title;

    $query = "SELECT COUNT(*) AS NumHits FROM ".$config['db']['pre']."user WHERE username  LIKE '$slug%'";
    $result = mysqli_query(db_connect($config),$query);
    $row = mysqli_fetch_assoc($result);
    $numHits = $row['NumHits'];

    return ($numHits > 0) ? ($slug.$numHits) : $slug;
}

function checkSocialUser($config,$userData = array(),$picname){
    $mysqli = db_connect($config);
    if(!empty($userData)){

        $fullname = $userData['first_name'].' '.$userData['last_name'];
        $fbfirstname = $userData['first_name'];

        // Check whether user data already exists in database
        $prevQuery = "SELECT * FROM ".$config['db']['pre']."user WHERE (oauth_provider = '".$userData['oauth_provider']."' AND oauth_uid = '".$userData['oauth_uid']."') OR email = '".validate_input($userData['email'])."'";
        $prevResult = mysqli_query($mysqli, $prevQuery);
        if(mysqli_num_rows($prevResult)>0){
            // Update user data if already exists
            /*$query = "UPDATE ".$config['db']['pre']."user SET
            name = '$fullname',
            email = '".$userData['email']."',
            gender = '".$userData['gender']."',
            image = '".$picname."',
            oauth_link = '".$userData['link']."',
            updated_at = '".date("Y-m-d H:i:s")."'
            WHERE oauth_provider = '".$userData['oauth_provider']."' AND oauth_uid = '".$userData['oauth_uid']."'";*/
            //$update = mysqli_query(db_connect($config), $query);

            // Get user data from the database
            $result = mysqli_query($mysqli, $prevQuery);
            $userData = $result->fetch_assoc();
        }else{

            //mysql query to select field username if it's equal to the username that we check '
            $sql = "select username from ".$config['db']['pre']."user where username = '".$fbfirstname."'";
            $result = mysqli_query($mysqli,$sql);

            //if number of rows fields is bigger them 0 that means it's NOT available '
            if(mysqli_num_rows($result)>0){
                $username = createusernameslug($config,$fbfirstname);
            }
            else{
                $username = $fbfirstname;
            }

            $password = get_random_id();
            $pass_hash = password_hash($password, PASSWORD_DEFAULT, ['cost' => 13]);
            // Insert user data
            $query = "INSERT INTO ".$config['db']['pre']."user SET
            oauth_provider = '".$userData['oauth_provider']."',
            oauth_uid = '".$userData['oauth_uid']."',
            status = '1',
            name = '$fullname',
            username = '$username',
            password_hash='" . $pass_hash . "',
            email = '".$userData['email']."',
            sex = '".$userData['gender']."',
            image = '".$picname."',
            oauth_link = '".$userData['link']."',
            created_at = '".date("Y-m-d H:i:s")."',
            updated_at = '".date("Y-m-d H:i:s")."'";
            $insert = mysqli_query($mysqli, $query);

            $user_id = $mysqli->insert_id;
            // Get user data from the database
            $userData['id'] = $user_id;
            $userData['username'] = $username;
            $userData['password_hash'] = $pass_hash;
            $userData['status'] = 2;
        }


    }

    // Return user data
    return $userData;
}

function get_user_data($config,$username,$userid=true){
    if($username != null){
        $query = "SELECT * FROM ".$config['db']['pre']."user WHERE username='".validate_input($username)."' LIMIT 1";
    }
    else{
        $query = "SELECT * FROM ".$config['db']['pre']."user WHERE id='".validate_input($userid)."' LIMIT 1";
    }

    $query_result = mysqli_query(db_connect($config), $query);
    if (mysqli_num_rows($query_result) > 0)
    {
        $info = mysqli_fetch_assoc($query_result);

        $userinfo['id']         = $info['id'];
        $userinfo['username']   = $info['username'];
        $userinfo['user_type']  = $info['user_type'];
        $userinfo['name']       = $info['name'];
        $userinfo['email']      = $info['email'];
        $userinfo['confirm']      = $info['confirm'];
        $userinfo['password']      = $info['password_hash'];
        $userinfo['forgot']      = $info['forgot'];
        $userinfo['status']     = $info['status'];
        $userinfo['view']       = $info['view'];
        $userinfo['image']      = $info['image'];
        $userinfo['tagline']    = $info['tagline'];
        $userinfo['description'] = $info['description'];
        $userinfo['sex']        = $info['sex'];
        $userinfo['phone']      = $info['phone'];
        $userinfo['postcode']   = $info['postcode'];
        $userinfo['address']    = $info['address'];
        $userinfo['country']    = $info['country'];
        $userinfo['city']       = $info['city'];
        $userinfo['lastactive'] = $info['lastactive'];
        $userinfo['online']     = $info['online'];
        $userinfo['created_at'] = timeAgo($info['created_at']);
        $userinfo['updated_at'] = $info['updated_at'];

        $userinfo['facebook']   = $info['facebook'];
        $userinfo['twitter']    = $info['twitter'];
        $userinfo['googleplus'] = $info['googleplus'];
        $userinfo['instagram']  = $info['instagram'];
        $userinfo['linkedin']   = $info['linkedin'];
        $userinfo['youtube']    = $info['youtube'];

        return $userinfo;
    }
    else{
       return 0;
    }
}

function get_user_id($config,$username){
    $query = "SELECT id FROM ".$config['db']['pre']."user WHERE username='" . $username . "' LIMIT 1";
    $query_result = mysqli_query(db_connect($config), $query);
    if (mysqli_num_rows($query_result) > 0)
    {
        $info = mysqli_fetch_assoc($query_result);
        $user_id = $info['id'];
        return $user_id;
    }
    else{
        return FALSE;
    }
}

function get_user_id_by_email($config,$email){
    $query = "SELECT id FROM ".$config['db']['pre']."user WHERE email='" . $email . "' LIMIT 1";
    $query_result = mysqli_query(db_connect($config), $query);
    if (mysqli_num_rows($query_result) > 0)
    {
        $info = mysqli_fetch_assoc($query_result);
        $user_id = $info['id'];
        return $user_id;
    }
    else{
        return FALSE;
    }
}

function get_username_by_email($config,$email){
    $query = "SELECT username FROM ".$config['db']['pre']."user WHERE email='" . $email . "' LIMIT 1";
    $query_result = mysqli_query(db_connect($config), $query);
    if (mysqli_num_rows($query_result) > 0)
    {
        $info = mysqli_fetch_assoc($query_result);
        $username = $info['username'];
        return $username;
    }
    else{
        return FALSE;
    }
}

function is_seller($config,$username)
{
    $query = "SELECT user_type FROM ".$config['db']['pre']."user WHERE username='" . $username . "' LIMIT 1";
    $query_result = mysqli_query(db_connect($config), $query);
    if (mysqli_num_rows($query_result) > 0)
    {
        $info = mysqli_fetch_assoc($query_result);
        $user_type = $info['user_type'];
        if($user_type == "seller")
            return TRUE;
        else
            return FALSE;
    }
    else
    {
        return FALSE;
    }
}

function create_user_session($userid,$username,$password){
    $user_browser = $_SERVER['HTTP_USER_AGENT']; // Get the user-agent string of the user.

    $user_id = preg_replace("/[^0-9]+/", "", $userid); // XSS protection as we might print this value
    $_SESSION['user']['id']  = $user_id;

    $username = preg_replace("/[^a-zA-Z0-9_\-]+/", "", $username); // XSS protection as we might print this value
    $_SESSION['user']['username'] = $username;

    $_SESSION['user']['login_string'] = hash('sha512', $password . $user_browser);
}
function userlogin($config,$email,$password)
{
    global $user_id, $username,  $db_password, $where;
    $mysqli = db_connect($config);

    $regex = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';

    if(!preg_match("/^[[:alnum:]]+$/", $email))
    {
        if(!preg_match($regex,$email))
        {
            return false;
        }
        else{
            //checking in email
            $where = " WHERE email = ? ";
        }
    }
    else{
        //checking in username
        $where = " WHERE username = ? ";
    }

    // Using prepared statements means that SQL injection is not possible.
    if ($stmt = $mysqli->prepare("SELECT id, status, username, password_hash 
        FROM `".$config['db']['pre']."user`
        $where
        LIMIT 1")) {
        $stmt->bind_param('s', $email);  // Bind "$email" to parameter.
        $stmt->execute();    // Execute the prepared query.
        $stmt->store_result();

        // get variables from result.
        $stmt->bind_result($user_id, $status, $username, $db_password);
        $stmt->fetch();

        if ($stmt->num_rows == 1) {
            // If the user exists we check if the account is locked
            // from too many login attempts

            /*if (checkbrute($config, $user_id, $mysqli) == true) {
                // Account is locked
                // Send an email to user saying their account is locked
                return false;
            } else {
                // Check if the password in the database matches
                // the password the user submitted. We are using
                // the password_verify function to avoid timing attacks.

            }*/
            if (password_verify($password, $db_password)) {
                // Password is correct!

                // Login successful.
                $userinfo = array();
                $userinfo['id'] = $user_id;
                $userinfo['username'] = $username;
                $userinfo['password'] = $db_password;
                $userinfo['status'] = $status;

                return $userinfo;

            } else {
                // Password is not correct
                // We record this attempt in the database
                $now = time();
                $mysqli->query("INSERT INTO `".$config['db']['pre']."login_attempts` (user_id, time)
                                    VALUES ('$user_id', '$now')");
                return false;
            }
        } else {
            // No user exists.
            return false;
        }
    }
	
}

function checkloggedin($config)
{
    global $password;
    $mysqli = db_connect($config);
    // Check if all session variables are set
    if (isset($_SESSION['user']['id'],
        $_SESSION['user']['username'],
        $_SESSION['user']['login_string'])) {

        $user_id = $_SESSION['user']['id'];
        $login_string = $_SESSION['user']['login_string'];
        $username = $_SESSION['user']['username'];

        // Get the user-agent string of the user.
        $user_browser = $_SERVER['HTTP_USER_AGENT'];

        if ($stmt = $mysqli->prepare("SELECT password_hash FROM `".$config['db']['pre']."user` WHERE id = ? LIMIT 1")) {
            // Bind "$user_id" to parameter.
            $stmt->bind_param('i', $user_id);
            $stmt->execute();   // Execute the prepared query.
            $stmt->store_result();

            if ($stmt->num_rows == 1) {
                // If the user exists get variables from result.
                $stmt->bind_result($password);
                $stmt->fetch();
                $login_check = hash('sha512', $password . $user_browser);

                if (hash_equals($login_check, $login_string) ){
                    // Logged In!!!!
                    return true;
                } else {
                    // Not logged in
                    return false;
                }
            } else {
                // Not logged in
                return false;
            }
        } else {
            // Not logged in
            return false;
        }
    } else {
        // Not logged in
        return false;
    }
}

function update_lastactive($config)
{
    if(isset($_SESSION['user']['id']))
    {
        mysqli_query(db_connect($config), "UPDATE `".$config['db']['pre']."user` SET `lastactive` = NOW() WHERE `id` = '".addslashes($_SESSION['user']['id'])."' LIMIT 1 ;");

    }
}

function send_forgot_email($to,$id)
{
    global $config,$lang,$link;
	$time = time();
	$rand = getrandnum(10);
	$forgot = md5($time.'_:_'.$rand.'_:_'.$to);
	
	mysqli_query(db_connect($config), "UPDATE `".$config['db']['pre']."user` SET `forgot` = '".$forgot."' WHERE `id` =".validate_input($id)." LIMIT 1");

    $get_userdata = get_user_data($config,null,$id);
    $to_name = $get_userdata['name'];

    $page = new HtmlTemplate();
    $page->html = $config['email_sub_forgot_pass'];
    $page->SetParameter ('EMAIL', $to);
    $page->SetParameter ('USER_FULLNAME', $to_name);
    $email_subject = $page->CreatePageReturn($lang,$config,$link);

    $forget_password_link = $config['site_url']."login?forgot=".$forgot."&r=".$rand."&e=".$to."&t=".$time;
    $page = new HtmlTemplate();
    $page->html = $config['email_message_forgot_pass'];
    $page->SetParameter ('FORGET_PASSWORD_LINK', $forget_password_link);
    $page->SetParameter ('EMAIL', $to);
    $page->SetParameter ('USER_FULLNAME', $to_name);
    $email_body = $page->CreatePageReturn($lang,$config,$link);

    email($to,$to_name,$email_subject,$email_body,$config);
}

function getrandnum($length)
{
    $randstr='';
    srand((double)microtime()*1000000);
    $chars = array ( 'a','b','C','D','e','f','G','h','i','J','k','L','m','N','P','Q','r','s','t','U','V','W','X','y','z','1','2','3','4','5','6','7','8','9');
    for ($rand = 0; $rand <= $length; $rand++)
    {
        $random = rand(0, count($chars) -1);
        $randstr .= $chars[$random];
    }

    return $randstr;
}

function randomPassword() {
    $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
    $pass = array(); //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < 8; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass); //turn the array into a string
}

function update_profileview($user_id,$config)
{
    mysqli_query(db_connect($config), "UPDATE `".$config['db']['pre']."user` SET `view` = view+1 WHERE `id` = '".$user_id."' LIMIT 1 ;");

}



/********************SECURE LOGIN*********************/
function sec_session_start() {
    define("CAN_REGISTER", "any");
    define("DEFAULT_ROLE", "member");
    define("SECURE", FALSE);    // FOR DEVELOPMENT ONLY!!!!
    $session_name = 'sec_session_id';   // Set a custom session name
    $secure = SECURE;
    // This stops JavaScript being able to access the session id.
    $httponly = true;
    // Forces sessions to only use cookies.
    if (ini_set('session.use_only_cookies', 1) === FALSE) {
        header("Location: ../error.php?err=Could not initiate a safe session (ini_set)");
        exit();
    }
    // Gets current cookies params.
    $cookieParams = session_get_cookie_params();
    session_set_cookie_params($cookieParams["lifetime"], $cookieParams["path"], $cookieParams["domain"], $secure, $httponly);
    // Sets the session name to the one set above.
    session_name($session_name);
    session_start();            // Start the PHP session
    session_regenerate_id();    // regenerated the session, delete the old one.
}

function checkbrute($config, $user_id, $mysqli) {
    // Get timestamp of current time
    $now = time();

    // All login attempts are counted from the past 2 hours.
    $valid_attempts = $now - (2 * 60 * 60);

    if ($stmt = $mysqli->prepare("SELECT time 
                             FROM `".$config['db']['pre']."login_attempts` 
                             WHERE user_id = ? 
                            AND time > '$valid_attempts'")) {
        $stmt->bind_param('i', $user_id);

        // Execute the prepared query.
        $stmt->execute();
        $stmt->store_result();

        // If there have been more than 5 failed logins
        if ($stmt->num_rows > 5) {
            return true;
        } else {
            return false;
        }
    }
}

function esc_url($url) {

    if ('' == $url) {
        return $url;
    }

    $url = preg_replace('|[^a-z0-9-~+_.?#=!&;,/:%@$\|*\'()\\x80-\\xff]|i', '', $url);

    $strip = array('%0d', '%0a', '%0D', '%0A');
    $url = (string) $url;

    $count = 1;
    while ($count) {
        $url = str_replace($strip, '', $url, $count);
    }

    $url = str_replace(';//', '://', $url);

    $url = htmlentities($url);

    $url = str_replace('&amp;', '&#038;', $url);
    $url = str_replace("'", '&#039;', $url);

    if ($url[0] !== '/') {
        // We're only interested in relative links from $_SERVER['PHP_SELF']
        return '';
    } else {
        return $url;
    }
}
?>