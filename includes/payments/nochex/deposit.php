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


$merchant_id = get_option($config,'nochex_merchant_id');
?>
<style type="text/css">
<!--
.style1 {
	font-size: 14px;
	font-family: Verdana, Arial, Helvetica, sans-serif;
}
-->
</style>
<body onload="javascript:document.nochex.submit();">
<form name="nochex" id="nochex" method="POST" action="https://secure.nochex.com/">
<input type="hidden" name="merchant_id" value="<?php echo $merchant_id;?>">
<input type="hidden" name="amount" value="<?php echo $amount; ?>">
<input type="hidden" name="description" value="<?php echo $trans_desc; ?>">
<input type="hidden" name="order_id" value="GRU1625">
<input type="hidden" name="optional_1" value="<?php echo $transaction_id; ?>">
<input type="hidden" name="responder_url" value="<?php echo $link['IPN'] . '/?i=nochex'; ?>">
</form>
<div align="center" class="style1">Transfering you to the NoChex Secure payment system, if you are not forwarded <a href="javascript:document.nochex.submit();">click here</a></div>
</body>