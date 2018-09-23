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
if(checkloggedin($config))
{
    $ses_userdata = get_user_data($config,$_SESSION['user']['username']);

    $author_image = $ses_userdata['image'];
    $author_lastactive = $ses_userdata['lastactive'];
    $author_country = $ses_userdata['country'];
    $updated_at = date('Y-m-d', strtotime(str_replace('-','/', $ses_userdata['updated_at'])));

    if(!isset($_POST['submit']))
    {
        // Output to template
        $page = new HtmlTemplate ('templates/' . $config['tpl_name'] . '/dashboard.tpl');
        $page->SetParameter ('OVERALL_HEADER', create_header($lang['DASHBOARD']));
        $page->SetParameter ('RESUBMITADS', resubmited_ads_count($config,$_SESSION['user']['id']));
        $page->SetParameter ('HIDDENADS', hidden_ads_count($config,$_SESSION['user']['id']));
        $page->SetParameter ('PENDINGADS', pending_ads_count($config,$_SESSION['user']['id']));
        $page->SetParameter ('FAVORITEADS', favorite_ads_count($config,$_SESSION['user']['id']));
        $page->SetParameter ('MYADS', myads_count($config,$_SESSION['user']['id']));
        $page->SetLoop('ERRORS', "");
        $page->SetLoop('COUNTRY', get_country_list($config,$ses_userdata['country']));
        $page->SetParameter ('AUTHORUNAME', ucfirst($ses_userdata['username']));
        $page->SetParameter ('AUTHORNAME', ucfirst($ses_userdata['name']));
        $page->SetParameter ('AUTHORIMG', $author_image);
        $page->SetParameter ('LASTACTIVE', $author_lastactive);
        $page->SetParameter ('EMAIL', $ses_userdata['email']);
        $page->SetParameter ('PHONE', $ses_userdata['phone']);
        $page->SetParameter ('POSTCODE', $ses_userdata['postcode']);
        $page->SetParameter ('ADDRESS', $ses_userdata['address']);
        $page->SetParameter ('CITY', $ses_userdata['city']);
        $page->SetParameter ('COUNTRY', $ses_userdata['country']);


        $page->SetParameter ('AUTHORTAGLINE', $ses_userdata['tagline']);
        $page->SetParameter ('AUTHORABOUT', $ses_userdata['description']);

        $page->SetParameter ('FACEBOOK', $ses_userdata['facebook']);
        $page->SetParameter ('TWITTER', $ses_userdata['twitter']);
        $page->SetParameter ('GOOGLEPLUS', $ses_userdata['googleplus']);
        $page->SetParameter ('INSTAGRAM', $ses_userdata['instagram']);
        $page->SetParameter ('LINKEDIN', $ses_userdata['linkedin']);
        $page->SetParameter ('YOUTUBE', $ses_userdata['youtube']);
        $page->SetParameter ('UPDATED', $updated_at);
        $page->SetLoop ('HTMLPAGE', get_html_pages($config));
        $page->SetParameter('COPYRIGHT_TEXT', get_option($config,"copyright_text"));
        $page->SetParameter ('OVERALL_FOOTER', create_footer());
        $page->CreatePageEcho();
    }
    else{
        $errors = array();
        if(!isset($_POST['heading']))
            $_POST['heading'] = "";
        if(!isset($_POST['content']))
            $_POST['content'] = "";
        if(!isset($_POST['postcode']))
            $_POST['postcode'] = "";
        if(!isset($_POST['city']))
            $_POST['city'] = "";
        if(!isset($_POST['country']))
            $_POST['country'] = "";

        $valid_formats = array("jpg","jpeg","png"); // Valid image formats

        if(!empty($_FILES['avatar']['tmp_name'])) {
            $filename = stripslashes($_FILES['avatar']['name']);
            $ext = getExtension($filename);
            $ext = strtolower($ext);
            //File extension check
            if (in_array($ext, $valid_formats)) {
                $file_avatar = $_FILES["avatar"];
                $path_avatar = "storage/profile/";
                $first_title = $_SESSION['user']['username'];

                if ($author_image != "default_user.png"){
                    $unlink = $author_image;
                    $getAvatar = fileUpload($path_avatar, $file_avatar, "image", $first_title, 225, 225,true, $unlink);
                }
                else{
                    $getAvatar = fileUpload($path_avatar, $file_avatar, "image", $first_title,225, 225,true);
                }

                if ($getAvatar != "") {
                    $avatarName = $getAvatar;
                } else {
                    $errors[]['message'] = "Avatar error: Required JPEG 150x150px image.";
                }
            }
            else {
                $errors[]['message'] = $lang['ONLY_JPG_ALLOW'];
            }
        }
        else{
            $avatarName = $author_image;
        }

        if(count($errors) > 0)
        {
            $page = new HtmlTemplate ('templates/' . $config['tpl_name'] . '/dashboard.tpl');
            $page->SetParameter ('OVERALL_HEADER', create_header($lang,"Dashboard"));
            $page->SetParameter ('RESUBMITADS', resubmited_ads_count($config,$_SESSION['user']['id']));
            $page->SetParameter ('HIDDENADS', hidden_ads_count($config,$_SESSION['user']['id']));
            $page->SetParameter ('PENDINGADS', pending_ads_count($config,$_SESSION['user']['id']));
            $page->SetParameter ('FAVORITEADS', favorite_ads_count($config,$_SESSION['user']['id']));
            $page->SetParameter ('MYADS', myads_count($config,$_SESSION['user']['id']));
            $page->SetLoop('ERRORS', $errors);
            $page->SetParameter ('AUTHORUNAME', $_SESSION['user']['username']);
            $page->SetParameter ('AUTHORNAME', $_POST['name']);
            $page->SetParameter ('LASTACTIVE', $author_lastactive);
            $page->SetParameter ('EMAIL', $ses_userdata['email']);
            $page->SetParameter ('PHONE', $_POST['phone']);
            $page->SetParameter ('POSTCODE', $_POST['postcode']);
            $page->SetParameter ('ADDRESS', $_POST['address']);
            $page->SetParameter ('CITY', $_POST['city']);
            $page->SetParameter ('COUNTRY', $_POST['country']);

            $page->SetParameter ('AUTHORTAGLINE', $_POST['heading']);
            $page->SetParameter ('AUTHORABOUT', $_POST['content']);

            $page->SetParameter ('FACEBOOK', $_POST['facebook']);
            $page->SetParameter ('TWITTER', $_POST['twitter']);
            $page->SetParameter ('GOOGLEPLUS', $_POST['googleplus']);
            $page->SetParameter ('INSTAGRAM', $_POST['instagram']);
            $page->SetParameter ('LINKEDIN', $_POST['linkedin']);
            $page->SetParameter ('YOUTUBE', $_POST['youtube']);

            $page->SetParameter ('AUTHORIMG', $author_image);
            $page->SetLoop ('HTMLPAGE', get_html_pages($config));
            $page->SetParameter('COPYRIGHT_TEXT', get_option($config,"copyright_text"));
            $page->SetParameter ('OVERALL_FOOTER', create_footer());
            $page->CreatePageEcho();
            exit();
        }
        else{
            $sql2 = "UPDATE ".$config['db']['pre']."user set
            name = '".$_POST['name']."',
            image = '".$avatarName."',
            tagline = '".$_POST['heading']."',
            description = '".$_POST['content']."',
            phone = '".$_POST['phone']."',
            postcode = '".$_POST['postcode']."',
            address = '".$_POST['address']."',
            city = '".$_POST['city']."',
            country = '".$_POST['country']."',
            facebook = '".$_POST['facebook']."',
            twitter = '".$_POST['twitter']."',
            googleplus = '".$_POST['googleplus']."',
            instagram = '".$_POST['instagram']."',
            linkedin = '".$_POST['linkedin']."',
            youtube = '".$_POST['youtube']."'
            where id='".$_SESSION['user']['id']."'
            ";
            mysqli_query($mysqli, $sql2) OR error(mysqli_error($mysqli));

            transfer($config,$link['DASHBOARD'],'Profile Updated Successfully','Profile Updated Successfully');
            exit;

        }
    }
}
else{
    headerRedirect($link['LOGIN']);
}
?>