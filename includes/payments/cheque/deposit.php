<?php
header("Pragma: no-cache");
header("Cache-Control: no-cache");
header("Expires: 0");

if(isset($_GET['id'])) {
    if(!checkloggedin($config)){
        header("Location: ".$link['LOGIN']);
        exit();
    }else{
        $sql = "SELECT id,amount,transaction_description from ".$config['db']['pre']."transaction where id = '".$_GET['id']."' limit 1";
        $query = mysqli_query($mysqli,$sql);
        $rows = mysqli_num_rows($query);
        if($rows == 1){
            $fetch = mysqli_fetch_array($query);
            $amount = $fetch['amount'];
            $transaction_id = $fetch['id'];
            $trans_desc = $fetch['transaction_description'];
        }


        // assign posted variables to local variables
        $cheque_information = nl2br(get_option($config,'company_cheque_info')); ;
        $payable_to = get_option($config,'cheque_payable_to'); ;
        $item_name = $trans_desc;

        $page = new HtmlTemplate ("includes/payments/cheque/deposit.tpl");
        $page->SetParameter ('OVERALL_HEADER', create_header($lang['PAYMENT']));
        $page->SetParameter ('OVERALL_FOOTER', create_footer());
        $page->SetParameter ('TRANSACTION_ID', $transaction_id);
        $page->SetParameter ('CHEQUE_INFO', $cheque_information);
        $page->SetParameter ('PAYABLE_TO', $payable_to);
        $page->SetParameter ('ORDER_TITLE', $item_name);
        $page->SetParameter ('AMOUNT', $amount);
        $page->SetParameter ('USERNAME', $_SESSION['user']['username']);
        $page->SetParameter('SITE_TITLE', $config['site_title']);
        $page->CreatePageEcho();
    }
}else{
    exit('Invalid Process');
}
?>