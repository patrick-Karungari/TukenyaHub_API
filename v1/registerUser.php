
<?php 

require_once '../includes/DbOperations.php';

$response = array(); 

if($_SERVER['REQUEST_METHOD']=='POST'){
	if(
		isset($_POST['username']) and 
			isset($_POST['email']) and
			isset($_POST['uniNum']) and 
				isset($_POST['password']) and
				isset($_POST['image']))
		{
		//operate the data further 

		$db = new DbOperations(); 

		$result = $db->createUser( 	$_POST['email'],
									$_POST['password'],
									$_POST['uniNum'],
									$_POST['username'],
									$_POST['image']
								);
		if($result == UPLOAD_SUCCESS){
			$response['code'] = "0";
			$response['error'] = false; 
			$response['message'] = "User registered successfully";
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

echo json_encode($response);
