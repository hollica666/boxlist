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
    $_GET['page'] = 1;

$limit = 4;

if(checkloggedin($config)) {
    $ses_userdata = get_user_data($config,$_SESSION['user']['username']);
    $author_image = $ses_userdata['image'];

    $total_item = resubmited_ads_count($config,$_SESSION['user']['id']);

    $items = get_resubmited_items($config,$_SESSION['user']['id'],"",$_GET['page'],$limit);

    $pagging = pagenav($total_item,$_GET['page'],$limit,$link['RESUBMITADS']);
    // Output to template
    $page = new HtmlTemplate ('templates/' . $config['tpl_name'] . '/ad-resubmission.tpl');
    $page->SetParameter ('OVERALL_HEADER', create_header($lang['RESUBMITED-ADS']));
    $page->SetParameter ('RESUBMITADS', $total_item);
    $page->SetParameter ('HIDDENADS', hidden_ads_count($config,$_SESSION['user']['id']));
    $page->SetParameter ('PENDINGADS', pending_ads_count($config,$_SESSION['user']['id']));
    $page->SetParameter ('FAVORITEADS', favorite_ads_count($config,$_SESSION['user']['id']));
    $page->SetParameter ('MYADS', myads_count($config,$_SESSION['user']['id']));
    $page->SetLoop ('ITEM', $items);
    $page->SetLoop ('PAGES', $pagging);
    $page->SetParameter ('TOTALITEM', $total_item);
    $page->SetParameter ('AUTHORUNAME', ucfirst($ses_userdata['username']));
    $page->SetParameter ('AUTHORNAME', ucfirst($ses_userdata['name']));
    $page->SetParameter ('AUTHORIMG', $author_image);
    $page->SetLoop ('HTMLPAGE', get_html_pages($config));
    $page->SetParameter('COPYRIGHT_TEXT', get_option($config,"copyright_text"));
    $page->SetParameter ('OVERALL_FOOTER', create_footer());
    $page->CreatePageEcho();
}
else{
    error($lang['PAGENOTEXIST'], __LINE__, __FILE__, 1);
    exit();
}
?>