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
if(checkloggedin($config)) {

    if(isset($_GET['uname'])){
        $chatusername = $_GET['uname'];
        $chatuserid = $_GET['uid'];

        $chat_userdata = get_user_data($config,$chatusername);
        $chatuser_image = $chat_userdata['image'];
        if($chatuser_image == "")
            $chatuser_image = "avatar_default.png";
    }else{
        $chatusername = "";
        $chatuserid = "";
        $chatuser_image = "";
    }

    if(!$config['wchat_purchase_code'])
        error($lang['PAGENOTEXIST'], __LINE__, __FILE__, 1);
    if($config['wchat_on_off'] == 'on') {
        $ses_userdata = get_user_data($config, $_SESSION['user']['username']);
        $author_image = $ses_userdata['image'];
        $page = new HtmlTemplate ("templates/" . $config['tpl_name'] . "/wchat.tpl");
        $page->SetParameter ('OVERALL_HEADER', create_header($lang['MESSAGE']));
        $page->SetParameter ('USERIMG', $author_image);
        $page->SetParameter('CHAT_USER_ID',$chatuserid);
        $page->SetParameter ('CHAT_USERNAME',$chatusername);
        $page->SetParameter ('CHAT_USERIMG',$chatuser_image);
        $page->SetParameter('COPYRIGHT_TEXT', $config['copyright_text']);
        $page->SetParameter ('OVERALL_FOOTER', create_footer());
        $page->CreatePageEcho();
    }else
        error($lang['PAGENOTEXIST'], __LINE__, __FILE__, 1);
}else
    headerRedirect($link['LOGIN']);
?>