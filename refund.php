
        <?php
        
        session_start();
if (strlen($_SESSION['uid']) == "") {
    header('location:logout.php');
}

include_once('function.php');
$obj = new DB_con();


        include 'token.php'; 
        $credentials_json = file_get_contents('config.json'); 
        $credentials_arr = json_decode($credentials_json,true);

        if ((isset($_GET['paymentID'])) && (isset($_GET['trxID'])) && (isset($_GET['amount']))){
         
            function refund()
            {        
                getToken();
                global $credentials_arr;
                $post_token = array(
                    'paymentID' => $_GET['paymentID'],
                    'amount' => $_GET['amount'],
                    'trxID' => $_GET['trxID'],
                    'sku' => 'sku',
                    'reason' => 'Quality issue'
                );
        
                $url = curl_init($credentials_arr['base_url']."/checkout/payment/refund");
                $post_token = json_encode($post_token);
                $header = array(
                    'Content-Type:application/json',
                    'Authorization:'. $_SESSION["token"],
                    'X-APP-Key:'. $credentials_arr['app_key']
                );
        
                curl_setopt($url, CURLOPT_HTTPHEADER, $header);
                curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($url, CURLOPT_POSTFIELDS, $post_token);
                curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
                $result_data = curl_exec($url);
                curl_close($url);
        
                $response = json_decode($result_data, true);
    
                // return $result_data;
            }   
            echo refund();
            $id = $_GET['id'];
            $result = $obj->delete_bks($id);
if($result){
 echo "<script>alert('refund success');</script>"; 
echo "<script>window.location.href = 'payments.php'</script>";     
}
        }

            ?>