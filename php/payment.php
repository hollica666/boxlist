<?php
require_once('includes/config.php');
require_once('includes/classes/class.template_engine.php');
require_once('includes/classes/class.country.php');
require_once('includes/functions/func.global.php');
require_once('includes/functions/func.users.php');
require_once('includes/functions/func.sqlquery.php');
require_once('includes/lang/lang_'.$config['lang'].'.php');
require_once("includes/lib/curl/curl.php");
require_once("includes/lib/curl/CurlResponse.php");
require_once('includes/seo-url.php');
$mysqli = db_connect($config);
sec_session_start();

if(isset($_GET['status']))
{
    if($_GET['status'] == 0){
        $mysqli->query("UPDATE ". $config['db']['pre'] . "transaction set status = 'cancel' where id='".$_GET['id']."' LIMIT 1");
        error($lang['DECLINED-TRANSACTION'], __LINE__, __FILE__, 1);
        exit();
    }elseif($_GET['status'] == 1){
        $mysqli->query("UPDATE ". $config['db']['pre'] . "transaction set status = 'failed' where id='".$_GET['id']."' LIMIT 1");
        error($lang['FAILED-TRANSACTION'], __LINE__, __FILE__, 1);
        exit();
    }
}

if(!isset($_GET['i']))
{
    error($lang['INVALID-PAYMENT_PROCESS'], __LINE__, __FILE__, 1);
    exit();
}

$_GET['i'] = str_replace('.','',$_GET['i']);
$_GET['i'] = str_replace('/','',$_GET['i']);
$_GET['i'] = strip_tags($_GET['i']);

if(preg_match('[^A-Za-z0-9_]',$_GET['i']))
{
    error($lang['INVALID-PAYMENT_PROCESS'], __LINE__, __FILE__, 1);
    exit();
}

if(trim($_GET['i']) == '')
{
    error($lang['INVALID-PAYMENT_PROCESS'], __LINE__, __FILE__, 1);
    exit();
}

if(isset($_GET['i']))
{
    $sql ="SELECT payment_folder FROM ".$config['db']['pre']."payments WHERE payment_folder='".$_GET['i']."' LIMIT 1";
    $payment_settings = mysqli_fetch_array(mysqli_query($mysqli,$sql));

    if(!isset($_GET['id']))
    {
        error($lang['NOT-FOUND-PAYMENT'], __LINE__, __FILE__, 1);
        exit();
    }
    else{
        $query = "SELECT 1 from ".$config['db']['pre']."transaction where id = '".$_GET['id']."' limit 1";
        $rows = mysqli_num_rows(mysqli_query($mysqli,$query));
        if($rows == 1){
            require_once('includes/payments/'.$payment_settings['payment_folder'].'/deposit.php');
        }else{
            error($lang['INVALID-PAYMENT_PROCESS'], __LINE__, __FILE__, 1);
            exit();
        }
    }
}
?>
