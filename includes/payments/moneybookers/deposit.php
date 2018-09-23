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
    }
}else{
    exit('Invalid Process');
}

$merchant_id = get_option($config,'skrill_merchant_id');
?>

<style type="text/css">
<!--
.style1 {
	font-size: 14px;
	font-family: Verdana, Arial, Helvetica, sans-serif;
}
-->
</style>

<body onload="javascript:document.moneybookers.submit();">
<form name="moneybookers" action="https://www.moneybookers.com/app/payment.pl" method="post">
    <input type="hidden" name="pay_to_email" value="<?php echo $merchant_id;?>">
    <input type="hidden" name="detail1_description" value="Description:<?php echo $trans_desc; ?>">
    <input type="hidden" name="detail1_text" value="Deposit into <?php echo $config['site_title']; ?>">
    <input type="hidden" name="amount" value="<?php echo $amount; ?>">
    <input type="hidden" name="currency" value="<?php echo $config['currency_code']; ?>">
    <input type="hidden" name="transaction_id" value="<?php echo $transaction_id ?>">
    <input type="hidden" name="status_url" value="<?php echo $link['PAYMENT'].'/?i=moneybookers'; ?>">
    <input type="hidden" name="return_url" value="<?php echo $link['PAYMENT'].'/?status=0&id='.$transaction_id ?>; ?>">
</form>

    
<div align="center" class="style1">Transfering you to the moneybookers.com Secure payment system, if you are not forwarded <a href="javascript:document.moneybookers.submit();">click here</a></div>
</body>