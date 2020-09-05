<?php 

require_once '../includes/DbOperations.php';

$response = array(); 

if(($_SERVER['REQUEST_METHOD']=='POST')){
	if(isset($_POST['uniNum']) and isset($_POST['password'])){
		$db = new DbOperations(); 
		if(($db->userLogin($_POST['uniNum'], $_POST['password']))==USER_AUTHENTICATED){
			$user = $db->getUserByuniNum($_POST['uniNum']);
			$response['error'] = false;
			$response['id'] = $user["id"]; 
			$response['email'] = $user['email'];
			$response['username'] = $user['username'];
			$response['uniNum'] = $user['uniNum'];
			$response['imagePath'] = $user['imagePath'];
		}else{
			$response['error'] = true; 
			$response['title'] = "Error signing in."; 
			$response['code'] = 1;
			$response['message'] = "Invalid username or password";			
		}

	}else{
		$response['code'] = 2;
		$response['error'] = true; 
		$response['title'] = "Empty Fields"; 
		$response['message'] = "Required fields are missing";
	}
	
}else{
	$response['code'] = 3;
	$response['error'] = true; 
	$response['title'] = "Server Unavailable"; 
	$response['message'] = "Please Try Again Later.";
}

echo json_encode($response);
?>