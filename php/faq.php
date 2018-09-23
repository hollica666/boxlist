<?php
require_once('includes/config.php');
require_once('includes/classes/class.template_engine.php');
require_once('includes/classes/class.country.php');
require_once('includes/functions/func.global.php');
require_once('includes/functions/func.users.php');
require_once('includes/functions/func.sqlquery.php');
require_once('includes/lang/lang_'.$config['lang'].'.php');
require_once('includes/seo-url.php');
sec_session_start();
$mysqli = db_connect($config);

$count = 0;
$faq = array();

$query = "SELECT faq_id,faq_title,faq_content FROM `".$config['db']['pre']."faq_entries` WHERE translation_lang = '".$config['lang_code']."' AND active = 1 ORDER BY faq_id";
$query_result = mysqli_query ($mysqli,$query);
while ($info = mysqli_fetch_array($query_result))
{
    $count++;

    $faq[$count]['id'] = $info['faq_id'];
    $faq[$count]['title'] = stripslashes($info['faq_title']);
    $faq[$count]['content'] = stripslashes($info['faq_content']);
}

$advertise_top = get_advertise($config,"top");


$page = new HtmlTemplate ("templates/" . $config['tpl_name'] . "/faq.tpl");
$page->SetParameter ('OVERALL_HEADER', create_header($lang['FAQ']));
$page->SetLoop ('FAQ', $faq);
if(checkloggedin($config)) {
    $page->SetParameter('USER_ID', $_SESSION['user']['id']);
}else{
    $page->SetParameter('USER_ID', "");
}
/*Advertisement Fetching*/
$page->SetParameter('TOP_ADSCODE', $advertise_top['tpl']);
$page->SetParameter('TOP_ADSTATUS', $advertise_top['status']);
/*Advertisement Fetching*/
$page->SetParameter ('OVERALL_FOOTER', create_footer());
$page->CreatePageEcho();
?>