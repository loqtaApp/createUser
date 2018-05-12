<?php
header('Content-Type: application/json');
error_reporting(0);
//initialize request  to create order with wehook

require __DIR__.'/vendor/autoload.php';

use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Auth;
$data = json_decode(json_decode(file_get_contents('php://input'),true),true);

if(sizeof($data) > 0){
$checkwatan = preg_match("/^056/", $data['mobile']);


/***
 * initliaze request
*/


$serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/google-services.json');

$firebase = (new Factory)
    ->withServiceAccount($serviceAccount)
    ->create();

$auth = $firebase->getAuth();


try{

	$users = $auth->getUser($data['uid']);


	$db = $firebase->getDatabase();


	//	$db->getReference('users/')->push($users->uid)->set($users->uid);
	$reference = $db->getReference('settings_v2/store_settings') ;
	$snapshot = $reference->getSnapshot();
	$value = $snapshot->getValue();

	$userRef = $db->getReference('users/' . $users->uid);
	$userSnapshot = $userRef->getSnapshot();
	$userValue = $userSnapshot->getValue();

	//check if have notified Before

	 $checkShowPopupBefore = ((!isset($userValue['popupFlag'])) || $userValue['popupFlag'] == true)? true:false ;

	 // check watania code

   $result = [];
	 if($checkwatan && $checkShowPopupBefore && $value['popupFlag']){
		 $result['status'] = true;
		 $result['image'] = $value['popupImage'];

	 }else{
		 $result['status'] = false;

	 }


	 echo json_encode($result);

}catch(Exception $e){
  echo $e;
}
}

?>
