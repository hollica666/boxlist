<?php
require_once('../../config.php');
require_once('../../functions/func.global.php');
require_once('../../functions/func.users.php');
require_once('../../functions/func.sqlquery.php');
sec_session_start();

//Include Google client library 
include_once 'src/Google_Client.php';
include_once 'src/contrib/Google_Oauth2Service.php';

/*
 * Configuration and setup Google API
 */

// Check if SSL enabled
$protocol = isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] && $_SERVER["HTTPS"] != "off"    ? "https://" : "http://";

// Define APPURL
$site_url = $protocol
    . $_SERVER["SERVER_NAME"]
    . (dirname($_SERVER["SCRIPT_NAME"]) == DIRECTORY_SEPARATOR ? "" : "/")
    . trim(str_replace("\\", "/", dirname($_SERVER["SCRIPT_NAME"])), "/");

define("SITEURL", $site_url);
$config['site_url'] = dirname(dirname(dirname($site_url)))."/";

$clientId = get_option( $config, "google_app_id"); //Google client ID
$clientSecret = get_option( $config, "google_app_secret"); //Google client secret
$redirectURL = $config['site_url'].'includes/social_login/google/index.php'; //Callback URL

//Call Google API
$gClient = new Google_Client();
$gClient->setApplicationName('Login to '.$config['site_title']);
$gClient->setClientId($clientId);
$gClient->setClientSecret($clientSecret);
$gClient->setRedirectUri($redirectURL);

$google_oauthV2 = new Google_Oauth2Service($gClient);
?>