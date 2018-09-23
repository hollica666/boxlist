<?php
$error_message = '';


function sendNvpRequest($method, array $data,$config)
{
    $sandbox_url = 'https://api-3t.sandbox.paypal.com/nvp';
    $url = 'https://api-3t.paypal.com/nvp';
    $url = (get_option($config,'paypal_sandbox_mode') == 'Yes') ?
        $sandbox_url :
        $url;

    $curl = new Curl();
    $curl->options['CURLOPT_SSL_VERIFYPEER'] = false;
    $curl->options['CURLOPT_SSL_VERIFYHOST'] = false;

    $paypal_api_username = get_option($config,'paypal_api_username');
    $paypal_api_password = get_option($config,'paypal_api_password');
    $paypal_api_signature = get_option($config,'paypal_api_signature');

    $data['METHOD'] = $method;
    $data['VERSION'] = '76.0';
    $data['USER'] = $paypal_api_username;
    $data['PWD'] = $paypal_api_password;
    $data['SIGNATURE'] = $paypal_api_signature;

    $httpResponse = $curl->post($url, $data);
    if (!$httpResponse) {
        exit($curl->error());
    }

    // Extract the response details.
    parse_str($httpResponse, $PayPalResponse);

    if (!array_key_exists('ACK', $PayPalResponse)) {
        exit('Invalid HTTP Response for POST request to ' . $url);
    }

    return $PayPalResponse;
}

if (isset($_REQUEST['token']) && isset($_REQUEST['PayerID'])) {
    $transaction_id = $_REQUEST['id'];
    $token = $_REQUEST['token'];
    $data = array('TOKEN' => $token);
    // Send the request to PayPal.
    $response = sendNvpRequest('GetExpressCheckoutDetails', $data,$config);

    if (strtoupper($response['ACK']) == 'SUCCESS') {
        $data['PAYERID'] = $_REQUEST['PayerID'];
        $data['PAYMENTREQUEST_0_PAYMENTACTION'] = 'Sale';

        foreach (array('PAYMENTREQUEST_0_AMT', 'PAYMENTREQUEST_0_ITEMAMT', 'PAYMENTREQUEST_0_CURRENCYCODE', 'L_PAYMENTREQUEST_0') as $parameter) {
            if (array_key_exists($parameter, $response)) {
                $data[$parameter] = $response[$parameter];
            }
        }
        /*Success*/

        $result = $mysqli->query("SELECT * FROM `".$config['db']['pre']."transaction` WHERE `id` = '" . $transaction_id . "' LIMIT 1");
        if (mysqli_num_rows($result) > 0) {
            // output data of each row
            $info = mysqli_fetch_assoc($result);

            $item_pro_id = $info['product_id'];
            $item_amount = $info['amount'];
            $item_featured = $info['featured'];
            $item_urgent = $info['urgent'];
            $item_highlight = $info['highlight'];

            if($item_featured == 1){
                $mysqli->query("UPDATE ". $config['db']['pre'] . "product set featured = '$item_featured' where id='".$item_pro_id."' LIMIT 1");
            }
            if($item_urgent == 1){
                $mysqli->query("UPDATE ". $config['db']['pre'] . "product set urgent = '$item_urgent' where id='".$item_pro_id."' LIMIT 1");
            }
            if($item_highlight == 1){
                $mysqli->query("UPDATE ". $config['db']['pre'] . "product set highlight = '$item_highlight' where id='".$item_pro_id."' LIMIT 1");
            }

            $mysqli->query("UPDATE ". $config['db']['pre'] . "transaction set status = 'success' where id='".$transaction_id."' LIMIT 1");



            if(check_valid_resubmission($config,$item_pro_id)){
                if($item_featured == 1){
                    $mysqli->query("UPDATE ". $config['db']['pre'] . "product_resubmit set featured = '$item_featured' where product_id='".$item_pro_id."' LIMIT 1");
                }
                if($item_urgent == 1){
                    $mysqli->query("UPDATE ". $config['db']['pre'] . "product_resubmit set urgent = '$item_urgent' where product_id='".$item_pro_id."' LIMIT 1");
                }
                if($item_highlight == 1){
                    $mysqli->query("UPDATE ". $config['db']['pre'] . "product_resubmit set highlight = '$item_highlight' where product_id='".$item_pro_id."' LIMIT 1");
                }
            }

            $result2 = $mysqli->query("SELECT * FROM `".$config['db']['pre']."balance` WHERE id = '1' LIMIT 1");
            if (mysqli_num_rows($result2) > 0) {
                $info2 = mysqli_fetch_assoc($result2);
                $current_amount=$info2['current_balance'];
                $total_earning=$info2['total_earning'];

                $updated_amount=($item_amount+$current_amount);
                $total_earning=($item_amount+$total_earning);

                $mysqli->query("UPDATE ". $config['db']['pre'] . "balance set current_balance = '" . $updated_amount . "', total_earning = '" . $total_earning . "' where id='1' LIMIT 1");
            }
            $item_link = $config['site_url']."ad/".$item_pro_id;
            message($lang['SUCCESS'],$lang['PAYMENTSUCCESS'],$link['TRANSACTION']);
            exit();
        }
        else{
            error($lang['INVALID-TRANSACTION'], __LINE__, __FILE__, 1,$lang,$config,$link);
            exit();
        }

    }
} else {
    error($lang['INVALID-TRANSACTION'], __LINE__, __FILE__, 1,$lang,$config,$link);
    exit();
}

if (!empty($error_message)) {
    header('Location: ' . add_query_arg(array(
            'bookme_action' => 'error',
            'error_msg' => urlencode($error_message),
        ), bookme_getCurrentPageURL()
        ));
    exit;
}
?>