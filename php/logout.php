<?php
require_once('includes/config.php');
require_once('includes/classes/class.template_engine.php');
require_once('includes/classes/class.country.php');
require_once('includes/functions/func.global.php');
require_once('includes/functions/func.users.php');
require_once('includes/functions/func.sqlquery.php');
require_once('includes/lang/lang_'.$config['lang'].'.php');

db_connect($config);

sec_session_start();

// Unset all session values
$_SESSION = array();

// get session parameters
$params = session_get_cookie_params();

// Delete the actual cookie.
setcookie(session_name(),
    '', time() - 42000,
    $params["path"],
    $params["domain"],
    $params["secure"],
    $params["httponly"]);

// Remove access token from session
unset($_SESSION['facebook_access_token']);
//Unset token and user data from session
unset($_SESSION['token']);

session_unset('user');
session_unset('chatHistory');
session_unset('openChatBoxes');

// Destroy session
session_destroy();

echo "<script>window.location='login'</script>";
?>