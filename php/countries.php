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

$countries = array();
$count = 1;

$query = "SELECT * FROM ".$config['db']['pre']."countries where active = '1' ORDER BY asciiname";
$query_result = mysqli_query(db_connect($config),$query);
$total = mysqli_num_rows($query_result);
$divide = intval($total/4)+1;
$col = "";
while ($info = mysqli_fetch_array($query_result))
{
    $countrylang = getLangFromCountry($info['languages']);
    $countries[$count]['tpl'] = "";
    if($count == 1 or $count == $col){
        $countries[$count]['tpl'] .= '<ul class="flag-list col-xs-3 ">';
        $checkEnd = $count+$divide-1;
        $col = $count+$divide;
        //echo "Start : ".$divide."<br>";
    }
    $countries[$count]['tpl'] .= '<li><span class="flag flag-'.strtolower($info['code']).'"></span><a href="'.$config['site_url'].'home/'.$countrylang.'/'.$info['code'].'" data-id="'.$info['id'].'" data-name="'.$info['asciiname'].'">'.$info['asciiname'].'</a></li>';


    if($count == $checkEnd or $count == $total){
        $countries[$count]['tpl'] .= '</ul>';
        //echo "end : ".$checkEnd."<br>";
    }
    $count++;
}


$title = "Free Local Classified Ads in the World";
$page = new HtmlTemplate ('templates/' . $config['tpl_name'] . '/countries.tpl');
$page->SetParameter ('OVERALL_HEADER', create_header($title));
$page->SetLoop ('COUNTRYLIST',$countries);
$page->SetParameter ('OVERALL_FOOTER', create_footer());
$page->CreatePageEcho();
?>