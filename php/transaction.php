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
    $transactions = array();
    $count = 0;
    $query = "SELECT * FROM `".$config['db']['pre']."transaction` WHERE seller_id='".$_SESSION['user']['id']."' order by id desc";
    $result = $mysqli->query($query);
    $total_item = mysqli_num_rows($result);
    while ($row = mysqli_fetch_assoc($result)) {
        $transactions[$count]['id'] = $row['id'];
        $transactions[$count]['product_id'] = $row['product_id'];
        $transactions[$count]['product_name'] = $row['product_name'];
        $transactions[$count]['amount'] = $row['amount'];
        $transactions[$count]['payment_by'] = $row['transaction_gatway'];
        $transactions[$count]['time'] = date('d M Y h:i A', $row['transaction_time']);

        $pro_url = create_slug($row['product_name']);
        $product_link = $config['site_url'].'ad/' . $row['id'] . '/'.$pro_url;
        $transactions[$count]['product_link'] = $product_link;

        $featured = $row['featured'];
        $urgent = $row['urgent'];
        $highlight = $row['highlight'];

        $premium = '';
        if ($featured == "1") {
            $premium = $premium . '<span class="label label-warning">Featured</span>';
        }

        if ($urgent == "1") {
            $premium = $premium . '<span class="label label-success">Urgent</span>';
        }

        if ($highlight == "1") {
            $premium = $premium . '<span class="label label-info">Highlight</span>';
        }

        $t_status = $row['status'];
        $status = '';
        if ($t_status == "success") {
            $status = '<span class="label label-success">Success</span>';
        } elseif ($t_status == "pending") {
            $status = '<span class="label label-warning">Pending</span>';
        } elseif ($t_status == "failed") {
            $status = '<span class="label label-danger">Failed</span>';
        }else{
            $status = '<span class="label label-danger">Cancel</span>';
        }

        $transactions[$count]['premium'] = $premium;
        $transactions[$count]['status'] = $status;

        $count++;
    }
    // Output to template
    $page = new HtmlTemplate ('templates/' . $config['tpl_name'] . '/transaction.tpl');
    $page->SetParameter ('OVERALL_HEADER', create_header($lang['MY-ADS']));
    $page->SetLoop ('TRANSACTIONS', $transactions);
    $page->SetLoop ('PAGES', pagenav($total_item,$_GET['page'],20,$link['TRANSACTION'] ,0));
    $page->SetParameter ('TOTALITEM', $total_item);
    $page->SetLoop ('HTMLPAGE', get_html_pages($config));
    $page->SetParameter('COPYRIGHT_TEXT', get_option($config,"copyright_text"));
    $page->SetParameter ('AUTHORUNAME', ucfirst($ses_userdata['username']));
    $page->SetParameter ('AUTHORNAME', ucfirst($ses_userdata['name']));
    $page->SetParameter ('AUTHORIMG', $author_image);
    $page->SetParameter ('OVERALL_FOOTER', create_footer());

    $page->CreatePageEcho();
}
else{
    error($lang['PAGENOTEXIST'], __LINE__, __FILE__, 1);
    exit();
}
?>