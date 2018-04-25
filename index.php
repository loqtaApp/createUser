<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

header('Content-Type: application/json');
error_reporting(E_ALL);
//initialize request  to create order with wehook


require __DIR__.'/vendor/autoload.php';

use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Auth;
$data = json_decode(json_decode(file_get_contents('php://input'),true),true);


$mainURL = "https://f3aa0d6659405ab34f9c0af85d0f2ef9:590b142f0e9922bd187703cd6729bae8@loqta-ps.myshopify.com/admin/customers/".$data['customer']['id']."/metafields.json";

/***
 * initliaze request
 */
$headers = array(
	'Content-Type:application/json'
);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $mainURL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$result = json_decode(curl_exec($ch),true);

if($result == NULL){
	$passoerd = "012012";
}else {
	$passoerd = $result['metafields'][0]['value'];
}

$serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/google-services.json');

$firebase = (new Factory)
    ->withServiceAccount($serviceAccount)
    ->create();

$auth = $firebase->getAuth();
try{
  $users = $auth->createUserWithEmailAndPassword($data['customer']['email'], $passoerd);
  echo "true";
}catch(Exception $e){
  echo $e;
}




?>
