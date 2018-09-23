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
?>
<style type="text/css">
    <!--
    .style1 {
        font-size: 14px;
        font-family: Verdana, Arial, Helvetica, sans-serif;
    }
    -->
</style>

<body onLoad="javascript:document.paytm.submit();">
<form name="paytm" method="post" action="includes/payments/paytm/pgRedirect.php">
    <input id="ORDER_ID" tabindex="1" maxlength="20" size="20" name="ORDER_ID" autocomplete="off" value="<?php echo $transaction_id;?>">
    <input id="CUST_ID" tabindex="2" maxlength="12" size="12" name="CUST_ID" autocomplete="off" value="CUST001">
    <input id="INDUSTRY_TYPE_ID" tabindex="4" maxlength="12" size="12" name="INDUSTRY_TYPE_ID" autocomplete="off" value="Retail">
    <input id="CHANNEL_ID" tabindex="4" maxlength="12" size="12" name="CHANNEL_ID" autocomplete="off" value="WEB">
    <input title="TXN_AMOUNT" tabindex="10" type="text" name="TXN_AMOUNT" value="<?php echo $amount; ?>">
    <input value="Butt" type="submit"	onclick="">
</form>
<div align="center" class="style1">Transfering you to the paytm.com Secure payment system, if you are not forwarded <a href="javascript:document.paytm.submit();">click here</a></div>
</body>