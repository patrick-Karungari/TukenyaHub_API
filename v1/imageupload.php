<?php
 function upload(){
    $response = array(); 
	if($_SERVER['REQUEST_METHOD']=='POST'){
        require_once('../includes/DbConnect.php');
        require_once '../includes/Constants.php';
        $con = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME) or die('Unable to Connect...');
		if (isset($_POST['uniNum']) and isset($_POST['image'])){
            error_reporting(E_ALL);
			ini_set('display_errors', 1);
            $image = $_POST['image'];
            $uniNum = $_POST['uniNum'];
            $upload_path = 'uploads/';
            $upload_url = 'https://427ffbcf.ngrok.io/TukenyaHub/PhotoUpload/'.$upload_path;
            $fileActualExt = 'jpg';
            $fileNameNew = str_replace("/","_",$uniNum).".".$fileActualExt;
		    $fileDestination = $_SERVER['DOCUMENT_ROOT'].'/TukenyaHub/PhotoUpload/uploads/'.$fileNameNew;
		    $file_url = $upload_url . str_replace("/","_",$uniNum) . '.jpg';
		    $stmt = $con->prepare("UPDATE `users`  SET `imagePath` = ? , `imageName` = ? WHERE `uniNum` = ?");
            echo $con->error;
            $stmt->bind_param("sss",$file_url,$fileNameNew,$uniNum);
			//adding the path and name to database 
			if($stmt->execute()){
                file_put_contents($fileDestination,base64_decode($image));
                //filling response array with values 
                $response['error'] = false; 
                $response['uniNum'] = $uniNum; 
                $response['url'] = $file_url; 
                $response['name'] = $fileNameNew;
			
		    }
		
		    //mysqli_close($con);	
	    }else{
		    $response['error']=true;
		    $response['message']='Please choose a file';
        }
    }

    echo json_encode($response);
}
?>