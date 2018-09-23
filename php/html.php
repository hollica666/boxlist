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

$query = "SELECT * FROM `".$config['db']['pre']."pages` WHERE translation_lang = '".$config['lang_code']."' AND active = 1 and slug='" . $_GET['id'] . "' LIMIT 1";
$query_result = mysqli_query ($mysqli,$query);
while ($info = mysqli_fetch_array($query_result))
{
	$html = stripslashes($info['content']);
    $name = stripslashes($info['name']);
	$title = stripslashes($info['title']);
	$type = $info['type'];
}

if(!isset($title))
{
	message("Error",$lang['PAGENOTEXIST']);
}

if($type == 1)
{
	if(!isset($_SESSION['user']['id']))
	{
		message("Login to view",$lang['MUSTLOGINVIEWPAGE']);
	}
}

if(isset($_GET['basic']))
{
	$page = new HtmlTemplate ('templates/' . $config['tpl_name'] . '/html_content_no.tpl');
}
else
{
	$page = new HtmlTemplate ('templates/' . $config['tpl_name'] . '/html_content.tpl');
}
$page->SetParameter ('OVERALL_HEADER', create_header($name));
$page->SetParameter ('SITE_TITLE', $config['site_title']);
$page->SetParameter ('NAME', $name);
$page->SetParameter ('TITLE', $title);
$page->SetParameter ('HTML', $html);
$page->SetParameter ('OVERALL_FOOTER', create_footer());
$page->CreatePageEcho();
?>