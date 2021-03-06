<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST,OPTIONS');
header('Cache-Control: no-cache');
header('Pragma: no-cache');
header('Content-Type: application/json');
error_reporting(0);
//initialize request  to create order with wehook

require __DIR__ . '/vendor/autoload.php';

use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Auth;

$data = json_decode(file_get_contents('php://input'), true);

$mainURL = "https://f3aa0d6659405ab34f9c0af85d0f2ef9:590b142f0e9922bd187703cd6729bae8@loqta-ps.myshopify.com/admin/customers/" . $data['id'] . "/metafields.json";

/* * *
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
$result = json_decode(curl_exec($ch), true);

$passoerd = "012012";
$resultAfterAddUser = array();
if (sizeof($result["metafields"]) > 0) {
    $passoerd = $result['metafields'][0]['value'];
}

$serviceAccount = ServiceAccount::fromJsonFile(__DIR__ . '/google-services.json');

$firebase = (new Factory)
        ->withServiceAccount($serviceAccount)
        ->create();

$auth = $firebase->getAuth();
try {
    $users = $auth->createUserWithEmailAndPassword($data['email'], $passoerd);

    //create user or update on firebase
    $country = '';
    $city = '';

    $db = $firebase->getDatabase();
    if (is_array($data['addresses']) && sizeof($data['addresses']) > 0) {
        $country = $data['addresses'][0]['country'];
        $city = $data['addresses'][0]['city'];
    }

    //	$db->getReference('users/')->push($users->uid)->set($users->uid);
    $reference = $db->getReference('users/' . $users->uid);
    $snapshot = $reference->getSnapshot();
    $value = $snapshot->getValue();

    $postData = array(
        'customer_id' => $data['id'],
        'first_name' => $data['first_name'],
        'last_name' => $data['last_name'],
        'orders_count' => $data['orders_count'],
        'phone' => $data['phone'],
        'email' => $data['email'],
        'country' => $country,
        'city' => $city,
        'wataniaDiscount' => ( (!isset($value['wataniaDiscount'])) || $value['wataniaDiscount'] == true) ? false : true
    );


    $db->getReference('users/' . $users->uid)->set($postData);
    $resultAfterAddUser['status'] = true;
} catch (Exception $e) {
    $resultAfterAddUser['status'] = false;
}
        echo json_encode($resultAfterAddUser);
?>
