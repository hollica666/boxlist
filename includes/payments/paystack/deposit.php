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


        $paystack_public_key = get_option($config,'paystack_public_key');
        $userdata = get_user_data($config, $_SESSION['user']['username']);
        $email = $userdata['email'];
    }
}else{
    exit('Invalid Process');
}
?>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
    function payWithPaystack(){
        var handler = PaystackPop.setup({
            key: '<?php echo $paystack_public_key; ?>',
            email: '<?php echo $email; ?>',
            amount: <?php echo $amount; ?>,
            ref: '<?php echo $transaction_id; ?>', // generates a pseudo-unique reference. Please replace with a reference you generated. Or remove the line entirely so our API will generate one for you
            metadata: {
                custom_fields: [
                    {
                        display_name: "Blank",
                        product_id: "Blank",
                        value: "Blank"
                    }
                ]
            },
            callback: function(response){
                var transaction_id = response.reference;
                var payment_status = "success";
                $('#transaction_id').val(transaction_id);
                $('#payment_status').val(payment_status);
                $("#paystack").submit();
            },
            onClose: function(){
                $('#transaction_id').val("Null");
                $('#payment_status').val("canceled");
                $("#paystack").submit();
            }
        });
        handler.openIframe();
    }
</script>

<body onLoad="javascript:payWithPaystack();">
<form name="paystack" id="paystack" action="<?php echo $link['IPN'].'/?i=paystack'; ?>" method="post">
    <input type="hidden" name="cmd" value="_xclick">
    <input type="hidden" name="custom" id="transaction_id" value="">
    <input type="hidden" name="status" id="payment_status" value="">
</form>
</body>