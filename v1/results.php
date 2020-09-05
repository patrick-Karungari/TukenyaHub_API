
<?php 

require_once '../includes/DbOperations.php';

$response = array(); 

if($_SERVER['REQUEST_METHOD']=='POST'){
	if(isset($_POST['uniNum'])){
		//operate the data further 

		$db = new DbOperations(); 

		$result = $db->getResults3( 
									$_POST['uniNum'],
								
								);
		if($result != null){
			
			$response = $result;
		}elseif($result == USER_FAILURE){
			$response['code'] = "1";
			$response['error'] = true; 
			$response['message'] = "Some error occurred please try again";			
		}elseif($result == USER_EXISTS){
			$response['code'] = "2";
			$response['error'] = true; 
			$response['message'] = "It seems you are already registered, please choose a different email and username";						
		}

	}else{
		$response['error'] = true; 
		$response['message'] = "Required fields are missing";
	}
}else{
	$response['error'] = true; 
	$response['message'] = "Invalid Request";
}

echo json_encode($result);
