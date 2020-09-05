<?php 

	class DbOperations{
		
		private $con; 

		function __construct(){

			require_once dirname(__FILE__).'/DbConnect.php';

			$db = new DbConnect();

			$this->con = $db->connect();

        }

		/*CRUD -> C -> CREATE */

		public function createUser($username, $pass, $uniNum, $email, $image){
            $image = $_POST['image'];
            $uniNum = $_POST['uniNum'];
            $upload_path = 'uploads/';
            $upload_url = 'https://47edff7b.ngrok.io/TukenyaHub/PhotoUpload/'.$upload_path;
            $fileActualExt = 'jpg';
            $fileNameNew = str_replace("/","_",$uniNum).".".$fileActualExt;
		    $fileDestination = $_SERVER['DOCUMENT_ROOT'].'/TukenyaHub/PhotoUpload/uploads/'.$fileNameNew;
		    $file_url = $upload_url . str_replace("/","_",$uniNum) . '.jpg';
			if($this->isUserExist($username,$email,$uniNum)){
			    return USER_EXISTS; 
			}else{
			        $stmt = $this->con->prepare("INSERT INTO users ( username, password, email, uniNum) VALUES ( ?, ?, ?, ?)");
				    echo $this->con->error;
				    $stmt->bind_param("ssss",$email,$pass,$username,$uniNum);
                    error_reporting(E_ALL);
                    ini_set('display_errors', 1);
				    if($stmt->execute()){
                        $stmt = $this->con->prepare("UPDATE `users`  SET `imagePath` = ? , `imageName` = ? WHERE `uniNum` = ?");
                        echo $this->con->error;
                        $stmt->bind_param("sss",$file_url,$fileNameNew,$uniNum);
                        //adding the path and name to database 
			            if($stmt->execute()){
                            file_put_contents($fileDestination,base64_decode($image));
                            return UPLOAD_SUCCESS;
                        }else{
                            return UPLOAD_FAILURE;
                        }
					    return USER_CREATED; 
                    }else{
					    return USER_FAILURE;					
                    }
                }
           
		}

		   /*
            The Update Operation
            The function will update an existing user
            from the database 
        */
        public function updateUser($email, $name, $uniNum, $id){
            $stmt = $this->con->prepare("UPDATE users SET email = ?, username = ?, uniNum = ? WHERE id = ?");
            $stmt->bind_param("sssi", $email, $username, $uniNum, $id);
            if($stmt->execute())
                return true; 
            return false; 
		}
		
 /*  
            The method is returning the password of a given user
            to verify the given password is correct or not
        */
        private function getUsersPasswordByEmail($email){
            $stmt = $this->con->prepare("SELECT password FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute(); 
            $stmt->bind_result($password);
            $stmt->fetch(); 
            return $password; 
        }

 
/*
            The Read Operation
            This function reads a specified user from database
        */
        public function getUserByEmail($email){
            $stmt = $this->con->prepare("SELECT id, email, username, uniNum FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute(); 
            $stmt->bind_result($id, $email, $username, $uniNum);
            $stmt->fetch(); 
            $user = array(); 
            $user['id'] = $id; 
            $user['email']=$email; 
            $user['username'] = $username; 
            $user['uniNum'] = $uniNum; 
            return $user; 
		}
	
        /*
            The Update Operation
            This function will update the password for a specified user
        */
        public function updatePassword($currentpassword, $newpassword, $email){
            $hashed_password = $this->getUsersPasswordByEmail($email);
            if($currentpassword == $hashed_password){
                $stmt = $this->con->prepare("UPDATE users SET password = ? WHERE email = ?");
                $stmt->bind_param("ss",$newpassword, $email);
                if($stmt->execute())
                    return PASSWORD_CHANGED;
                return PASSWORD_NOT_CHANGED;
            }else{
                return PASSWORD_DO_NOT_MATCH; 
            }
        }

		/*  
            The method is returning the password of a given user
            to verify the given password is correct or not
        */
        private function getUsersPasswordByuniNum($uniNum){
			error_reporting(E_ALL);
			ini_set('display_errors', 1);
			
			$stmt = $this->con->prepare("SELECT password FROM users WHERE uniNum = ?");
			echo $this->con->error;
            $stmt->bind_param("s", $uniNum);
            $stmt->execute(); 
            $stmt->bind_result($password);
            $stmt->fetch(); 
            return $password; 
		}
		
		public function userLogin($uniNum, $pass){
			$hashpassword = $this->getUsersPasswordByuniNum($uniNum);
			error_reporting(E_ALL);
			ini_set('display_errors', 1);
            $userByuniNum = $this->getUserByuniNum($uniNum);
            $imageLctn = $userByuniNum['imagePath'];
			if($pass = $hashpassword){	
				$stmt = $this->con->prepare("SELECT id,imagepath FROM users WHERE uniNum = ? AND password = ?");
				echo $this->con->error;
				$stmt->bind_param("ss",$uniNum,$hashpassword);
				if($stmt->execute()){
					$stmt->store_result();
					if ($stmt->num_rows == 1 ){										 
						return USER_AUTHENTICATED;		
					} else  {						
						return  USER_PASSWORD_DO_NOT_MATCH;	
					}	                 
				}
			}else{
                return USER_NOT_FOUND;	
            }	 
		}

		public function getUserByUsername($username){
			$stmt = $this->con->prepare("SELECT * FROM users WHERE username = ?");
			$stmt->bind_param("s",$username);
			$stmt->execute();
			return $stmt->get_result()->fetch_assoc();
		}
		

			/*
		
		The Read Operation
		This function reads a specified user from database
	*/
	public function getUserByuniNum($uniNum){
		error_reporting(E_ALL);
		ini_set('display_errors', 1);
		$stmt = $this->con->prepare("SELECT id, email, username, uniNum, imagePath FROM users WHERE uniNum = ?");
		echo $this->con->error;
        $stmt->bind_param("s", $uniNum);
        if($stmt->execute()){
            $stmt->bind_result($id, $email, $username, $uniNum, $imagePath);
		    $stmt->fetch(); 
		    $user = array(); 
            $user['id'] = $id;
            $user['imagePath']  = $imagePath;
		    $user['email']=$email; 
		    $user['username'] = $username; 
		    $user['uniNum'] = $uniNum; 
		    return $user; 
        }
		
		
	}


  /*
            The Read Operation
            Function is returning all the users from database
        */
        public function getAllUsers(){
			error_reporting(E_ALL);
		    ini_set('display_errors', 1);
            $stmt = $this->con->prepare("SELECT id, email, username, uniNum FROM users;");
			$stmt->execute(); 
			echo $this->con->error;
            $stmt->bind_result($id, $email, $username, $uniNum);
            $users = array(); 
            while($stmt->fetch()){ 
                $user = array(); 
                $user['id'] = $id; 
                $user['email']=$email; 
                $user['username'] = $username; 
                $user['uniNum'] = $uniNum; 
                array_push($users, $user);
            }             
            return $users; 
		}
		
		private function isUserExist($username, $email, $uniNum){
			error_reporting(E_ALL);
		    ini_set('display_errors', 1);
			$stmt = $this->con->prepare("SELECT id FROM users WHERE email = ? OR uniNum = ?");
			echo $this->con->error;
			$stmt->bind_param("ss",  $email, $uniNum);
			$stmt->execute(); 
			$stmt->store_result(); 
			return $stmt->num_rows > 0; 
		}

		 /*
            The Delete Operation
            This function will delete the user from database
        */
        public function deleteUser($id){
            $stmt = $this->con->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $id);
            if($stmt->execute())
                return true; 
            return false; 
            
        }
        
	}
?>