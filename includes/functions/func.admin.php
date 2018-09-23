<?php
require_once('func.global.php');

function check_allow()
{
    if(isset($_SESSION['admin']['id']) && $_SESSION['admin']['id'] == 1)
    {
        return TRUE;
    }
    else
    {
        return TRUE;
    }
}
function check_update_available(){
    global $config;
    //Check For An Update
    $getVersions = file_get_contents('https://bylancer.com/api/quickad-release-versions.php') or die ('ERROR');
    $versionList = explode("\n", $getVersions);
    foreach ($versionList as $aV) {
        if ($aV > $config['version']) {
            return $aV;
        }
    }
    return false;
}
function admin_session_start() {
    define("CAN_REGISTER", "no");
    define("DEFAULT_ROLE", "admin");
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
function checkloggedadmin($config)
{
    global $password;
    $mysqli = db_connect($config);
    // Check if all session variables are set
    if (isset($_SESSION['admin']['id'],
        $_SESSION['admin']['username'],
        $_SESSION['admin']['login_string'])) {

        $user_id = $_SESSION['admin']['id'];
        $login_string = $_SESSION['admin']['login_string'];
        $username = $_SESSION['admin']['username'];

        // Get the user-agent string of the user.
        $user_browser = $_SERVER['HTTP_USER_AGENT'];

        if ($stmt = $mysqli->prepare("SELECT password_hash FROM `".$config['db']['pre']."admins` WHERE id = ? LIMIT 1")) {
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
        echo '<script>window.location="login.php"</script>';
    }
}

function adminlogin($config,$email,$password)
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
    $sql = "SELECT id, username, password_hash 
        FROM `".$config['db']['pre']."admins`
        $where
        LIMIT 1";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param('s', $email);  // Bind "$email" to parameter.
        $stmt->execute();    // Execute the prepared query.
        $stmt->store_result();

        // get variables from result.
        $stmt->bind_result($user_id, $username, $db_password);
        $stmt->fetch();

        if ($stmt->num_rows == 1) {
            // If the user exists we check if the account is locked
            // from too many login attempts

            // Check if the password in the database matches
            // the password the user submitted. We are using
            // the password_verify function to avoid timing attacks.
            if (password_verify($password, $db_password)) {
                // Password is correct!
                // Login successful.
                $user_browser = $_SERVER['HTTP_USER_AGENT']; // Get the user-agent string of the user.
                $user_id = preg_replace("/[^0-9]+/", "", $user_id); // XSS protection as we might print this value
                $_SESSION['admin']['id']  = $user_id;
                $username = preg_replace("/[^a-zA-Z0-9_\-]+/", "", $user_id); // XSS protection as we might print this value
                $_SESSION['admin']['username'] = $username;
                $_SESSION['admin']['login_string'] = hash('sha512', $db_password . $user_browser);

                return true;

            } else {
                // Password is not correct
                return false;
            }
        } else {
            // No user exists.
            return false;
        }
    }

}

function validStrLen($str, $min, $max, $con, $config){
    $len = strlen($str);
    if($len < $min){
        return "Username is too short, minimum is $min characters ($max max)";
    }
    elseif($len > $max){
        return "Username is too long, maximum is $max characters ($min min).";
    }
    elseif(!preg_match("/^[a-zA-Z0-9]+$/", $str))
    {
        return "Only use numbers and letters please";
    }
    else{
        //get the username
        $username = mysqli_real_escape_string($con, $_POST['username']);

        //mysql query to select field username if it's equal to the username that we check '
        $result = mysqli_query($con, "select username from `".$config['db']['pre']."userdata` where username = '".$username."'");

        //if number of rows fields is bigger them 0 that means it's NOT available '
        if(mysqli_num_rows($result)>0){
            //and we send 0 to the ajax request
            return "Error: Username not available";
        }
    }
    return TRUE;
}
?>