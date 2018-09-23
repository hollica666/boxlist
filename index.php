<?php
// Path to root directory of app.
define("ROOTPATH", dirname(__FILE__));

// Path to app folder.
define("APPPATH", ROOTPATH."/php/");


// Check if SSL enabled
$protocol = isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] && $_SERVER["HTTPS"] != "off"
    ? "https://" : "http://";

// Define APPURL
$site_url = $protocol
    . $_SERVER["SERVER_NAME"]
    . (dirname($_SERVER["SCRIPT_NAME"]) == DIRECTORY_SEPARATOR ? "" : "/")
    . trim(str_replace("\\", "/", dirname($_SERVER["SCRIPT_NAME"])), "/");

define("SITEURL", $site_url);

$config['app_url'] = SITEURL."/php/";
$config['site_url'] = SITEURL."/";

include ROOTPATH . '/includes/classes/AltoRouter.php';

// Start routing.
$router = new AltoRouter();
$bp = trim(str_replace("\\", "/", dirname($_SERVER["SCRIPT_NAME"])), "/");
$router->setBasePath($bp ? "/".$bp : "");
/* Setup the URL routing. This is production ready. */
// Main routes that non-customers see
$router->map('GET|POST','/', 'home.php');
$router->map('GET|POST','/home/[a:lang]?/?', 'home.php');
$router->map('GET|POST','/home/[a:lang]?/[a:country]?/?', 'home.php');
$router->map('GET|POST','/signup/?', 'signup.php');
$router->map('GET|POST','/index1/?', 'index1.php');
$router->map('GET|POST','/index2/?', 'index2.php');
$router->map('GET|POST','/login/?', 'login.php');
$router->map('GET|POST','/logout/?', 'logout.php');
$router->map('GET|POST','/message/?', 'message.php');
$router->map('GET|POST','/forgot/?', 'forgot.php');
$router->map('GET|POST','/dashboard/?', 'dashboard.php');
$router->map('GET|POST','/myads/?', 'ad-my.php');
$router->map('GET|POST','/pending/?', 'ad-pending.php');
$router->map('GET|POST','/favourite/?', 'ad-favourite.php');
$router->map('GET|POST','/hidden/?', 'ad-hidden.php');
$router->map('GET|POST','/resubmission/?', 'ad-resubmission.php');
$router->map('GET|POST','/transaction/?', 'transaction.php');
$router->map('GET|POST','/account-setting/?', 'account-setting.php');
$router->map('GET|POST','/report/?', 'report.php');
$router->map('GET|POST','/contact/?', 'contact.php');
$router->map('GET|POST','/sitemap/?', 'sitemap.php');
$router->map('GET|POST','/countries/?', 'countries.php');
$router->map('GET|POST','/faq/?', 'faq.php');
$router->map('GET|POST','/feedback/?', 'feedback.php');
$router->map('GET|POST','/payment/?', 'payment.php');
$router->map('GET|POST','/ipn/?', 'ipn.php');
$router->map('GET|POST','/test/?', 'test.php');
// Special (GET processing, etc)

$router->map('GET|POST','/profile/[*:username]?/[*:page]?/?','profile.php');
$router->map('GET|POST','/ad/[i:id]?/[*:slug]?/?', 'ad-detail.php');
$router->map('GET|POST','/post-ad/[a:lang]?/[a:country]?/[a:action]?/?', 'ad-post.php');
$router->map('GET|POST','/edit-ad/[i:id]?/[a:lang]?/[a:country]?/[a:action]?/?', 'ad-edit.php');
$router->map('GET|POST','/listing/?', 'listing.php');
$router->map('GET|POST','/category/[*:cat]?/[*:subcat]?/?', 'listing.php');
$router->map('GET|POST','/sub-category/[*:subcat]?/[*:slug]?/?', 'listing.php');
$router->map('GET|POST','/city/[i:city]?/[*:slug]?/?', 'listing.php');
$router->map('GET|POST','/keywords/[*:keywords]?/?', 'listing.php');
$router->map('GET|POST','/page/[a:id]?/?', 'html.php');
$router->map('GET','/sitemap.xml/?', 'xml.php');

// API Routes

/* Match the current request */
$match = $router->match();
if($match) {
    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        $_GET = array_merge($match['params'],$_GET);
    }

    require APPPATH.$match['target'];
}
else {
    header("HTTP/1.0 404 Not Found");
   require APPPATH.'404.php';
}
?>