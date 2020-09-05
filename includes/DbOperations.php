<?php

use phpDocumentor\Reflection\Types\Array_;

class DbOperations
{

    private $con;

    function __construct()
    {

        require_once dirname(__FILE__) . '/DbConnect.php';

        $db = new DbConnect();

        $this->con = $db->connect();
    }

    /*CRUD -> C -> CREATE */

    public function createUser($username, $pass, $uniNum, $email, $image)
    {
        $image = $_POST['image'];
        $uniNum = $_POST['uniNum'];
        $upload_path = 'uploads/';
        $upload_url = 'https://b34efdf434c5.ngrok.io/TukenyaHub/PhotoUpload/' . $upload_path;
        $fileActualExt = 'jpg';
        $fileNameNew = str_replace("/", "_", $uniNum) . "." . $fileActualExt;
        $fileDestination = $_SERVER['DOCUMENT_ROOT'] . '/TukenyaHub/PhotoUpload/uploads/' . $fileNameNew;
        $file_url = $upload_url . str_replace("/", "_", $uniNum) . '.jpg';
        if ($this->isUserExist($email, $uniNum) == NEW_USER) {
            $stmt = $this->con->prepare("INSERT INTO users ( username, password, email, uniNum) VALUES ( ?, ?, ?, ?)");
            echo $this->con->error;
            $stmt->bind_param("ssss", $email, $pass, $username, $uniNum);
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
            if ($stmt->execute()) {
                $stmt = $this->con->prepare("UPDATE `users`  SET `imagePath` = ? , `imageName` = ? WHERE `uniNum` = ?");
                echo $this->con->error;
                $stmt->bind_param("sss", $file_url, $fileNameNew, $uniNum);
                //adding the path and name to database 
                if ($stmt->execute()) {
                    file_put_contents($fileDestination, base64_decode($image));
                    return UPLOAD_SUCCESS;
                } else {
                    return UPLOAD_FAILURE;
                }
                return USER_CREATED;
            } else {
                return USER_FAILURE;
            }
        } else {
            return USER_EXISTS;
        }
    }

    /*
            The Update Operation
            The function will update an existing user
            from the database 
        */
    public function updateUser($email, $name, $uniNum, $id)
    {
        $stmt = $this->con->prepare("UPDATE users SET email = ?, username = ?, uniNum = ? WHERE id = ?");
        $stmt->bind_param("sssi", $email, $name, $uniNum, $id);
        if ($stmt->execute())
            return true;
        return false;
    }

    /*  
            The method is returning the password of a given user
            to verify the given password is correct or not
        */
    private function getUsersPasswordByEmail($email)
    {
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
    public function getUserByEmail($email)
    {
        $stmt = $this->con->prepare("SELECT id, email, username, uniNum FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($id, $email, $username, $uniNum);
        $stmt->fetch();
        $user = array();
        $user['id'] = $id;
        $user['email'] = $email;
        $user['username'] = $username;
        $user['uniNum'] = $uniNum;
        return $user;
    }

    /*
            The Update Operation
            This function will update the password for a specified user
        */
    public function updatePassword($currentpassword, $newpassword, $email)
    {
        $hashed_password = $this->getUsersPasswordByEmail($email);
        if ($currentpassword == $hashed_password) {
            $stmt = $this->con->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->bind_param("ss", $newpassword, $email);
            if ($stmt->execute())
                return PASSWORD_CHANGED;
            return PASSWORD_NOT_CHANGED;
        } else {
            return PASSWORD_DO_NOT_MATCH;
        }
    }

    /*  
            The method is returning the password of a given user
            to verify the given password is correct or not
        */
    private function getUsersPasswordByuniNum($uniNum)
    {
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

    public function userLogin($uniNum, $pass)
    {
        $hashpassword = $this->getUsersPasswordByuniNum($uniNum);
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        $userByuniNum = $this->getUserByuniNum($uniNum);
        $imageLctn = $userByuniNum['imagePath'];
        if ($pass = $hashpassword) {
            $stmt = $this->con->prepare("SELECT id,imagepath FROM users WHERE uniNum = ? AND password = ?");
            echo $this->con->error;
            $stmt->bind_param("ss", $uniNum, $hashpassword);
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    return USER_AUTHENTICATED;
                } else {
                    return  USER_PASSWORD_DO_NOT_MATCH;
                }
            }
        } else {
            return USER_NOT_FOUND;
        }
    }

    public function getUserByUsername($username)
    {
        $stmt = $this->con->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }


    /*
		
		The Read Operation
		This function reads a specified user from database
	*/
    public function getUserByuniNum($uniNum)
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        $stmt = $this->con->prepare("SELECT id, email, username, uniNum, imagePath FROM users WHERE uniNum = ?");
        echo $this->con->error;
        $stmt->bind_param("s", $uniNum);
        if ($stmt->execute()) {
            $stmt->bind_result($id, $email, $username, $uniNum, $imagePath);
            $stmt->fetch();
            $user = array();
            $user['id'] = $id;
            $user['imagePath']  = $imagePath;
            $user['email'] = $email;
            $user['username'] = $username;
            $user['uniNum'] = $uniNum;
            return $user;
        }
    }


    public function getResults3($uniNum){
        $allArray = array();
        $yArray = array();
        $sArray = array();
        $rArray = array();
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        $stmt = $this->con->prepare("SELECT yearCode FROM codes");
        echo $this->con->error;
        if ($stmt->execute()) {
            $stmt->bind_result($yCode);
            $years = array();
            while ($stmt->fetch()) {               
                $year = array();
                $year['Year'] = $yCode;
                array_push($years, $year);
                $stmt->store_result();
                //mysqli_next_result($this->con);
                $stmt->free_result();
                $stmt = $this->con->prepare("SELECT semCode FROM codes");
                if ($stmt->execute()) {
                    $stmt->bind_result($sCode);
                    $sems = array();
                    while ($stmt->fetch()) {
                        $sem = array();
                        $sem = $sCode;
                        array_push($sems, $sem);
                    }
                    echo json_encode($sems);
                }

            }
            echo json_encode($years);
            
        }

    }

    public function getResults2($uniNum)
    {
        $con = mysqli_connect("localhost", "root", "", "Tukenya");
        // Check connection
        if (mysqli_connect_errno()) {
            echo "Failed to connect to MySQL: " . mysqli_connect_error();
        }
        $allArray = array();
        $yArray = array();
        $sArray = array();
        $rArray = array();
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        $sql = mysqli_query($con, "SELECT yearCode FROM codes limit 5");
        
        if (mysqli_num_rows($sql) > 0) {
            $row = mysqli_fetch_all($sql);
            $count=0;
            foreach ($row as $yResult) {
                ++$count;
                $yArray[] = $yResult;               
                //echo json_encode($yArray);
                $sql = mysqli_query($con, "SELECT semCode FROM codes");
                if (mysqli_num_rows($sql) > 0) {
                    $row = mysqli_fetch_all($sql);
                    if (mysqli_num_rows($sql) > 0) {
                        $row = mysqli_fetch_all($sql);
                        foreach ($row as $sResult) {
                            $sArray[] = $sResult;
                            $sql = mysqli_query($con, "SELECT DISTINCT subjects.subjectName,score.subCode,score.grade
                            from students 
                            join score on score.uniNum = students.uniNum 
                            join subjects on score.subCode = subjects.subjectCode
                            WHERE students.uniNum = '$uniNum' AND
                            subjects.semCode = '$yResult[0]' AND
                            subjects.yCode = 'Y.$count'
                            ");
                            if (mysqli_num_rows($sql) > 0) {
                                $row = mysqli_fetch_all($sql);
                                foreach ($row as $sResult) {
                                    $rArray[] = array($sResult[0]['']);
                                }
                            }
                        }
                        $sArray['Semester'] = $row;
                    }
                }
                $allArray ['Results'][]= array('Y'.$count=>$sArray);
            }
            
            echo json_encode($allArray);
            //echo json_encode($yArray);
        } 
        
    }


    public function getResults($uniNum)
    {
        $con = mysqli_connect("localhost", "root", "", "Tukenya");
        // Check connection
        if (mysqli_connect_errno()) {
            echo "Failed to connect to MySQL: " . mysqli_connect_error();
        }
        $allArray = array();
        $yArray = array();
        $sArray = array();
        $rArray = array();
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        $stmt = $this->con->prepare("SELECT yearCode FROM codes");
        echo $this->con->error;
        if ($stmt->execute()) {
            $sql = mysqli_query($con, "SELECT yearCode FROM codes");
            $results = mysqli_fetch_all($sql, MYSQLI_ASSOC);
            if (mysqli_num_rows($sql) > 0) {
                foreach ($results as $yResult) {
                    $yArray['Year'] = $yResult->yearCode;
                    //$stmt = $this->con->prepare("SELECT semCode FROM codes");
                    $sql = mysqli_query($con, "SELECT semCode FROM codes");
                    //$results = mysqli_fetch_all($sql, MYSQLI_ASSOC);
                    // if ($stmt->execute()) {
                    $results = mysqli_fetch_all($sql, MYSQLI_ASSOC);
                    if (mysqli_num_rows($sql) > 0) {
                        $con->close();
                        $sql->close();
                        mysqli_free_result($sql);
                        foreach ($results as $sResult) {
                            $stmt = $this->con->prepare("SELECT DISTINCT subjects.subjectName,score.subCode,score.grade
                                from students 
                                join score on score.uniNum = students.uniNum 
                                join subjects on score.subCode = subjects.subjectCode
                                WHERE students.uniNum = ? AND
                                subjects.semCode = ? AND
                                subjects.yCode = ?
                                ");
                            $stmt->bind_param("sss", $uniNum, $yResult->yearCode, $sResult->semCode);
                            if ($stmt->execute()) {
                                $stmt->bind_result($subjectName, $subCode, $grade);
                                $stmt->fetch();
                                $sArray['Sem'] = $sResult->semCode;
                                $rArray['subjectName'] = $subjectName;
                                $rArray['subjectCode'] = $subCode;
                                $rArray['subjectGrade'] = $grade;
                                $allArray = array($yArray => array($sArray => $rArray));
                            }
                        }
                    }
                    // }
                }
            }
        }
        return $allArray;
    }

    /*
            The Read Operation
            Function is returning all the users from database
        */
    public function getAllUsers()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        $stmt = $this->con->prepare("SELECT id, email, username, uniNum FROM users;");
        $stmt->execute();
        echo $this->con->error;
        $stmt->bind_result($id, $email, $username, $uniNum);
        $users = array();
        while ($stmt->fetch()) {
            $user = array();
            $user['id'] = $id;
            $user['email'] = $email;
            $user['username'] = $username;
            $user['uniNum'] = $uniNum;
            array_push($users, $user);
        }
        return $users;
    }

    private function isUserExist($email, $uniNum)
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        $stmt = $this->con->prepare("SELECT * FROM users WHERE email = ? OR uniNum = ?");
        echo $this->con->error;
        $stmt->bind_param("ss",  $email, $uniNum);
        $stmt->execute();
        $results = $stmt->fetch();
        if ($results == null) {
            return NEW_USER;
        } else {
            return USER_EXISTS;
        }
    }

    /*
            The Delete Operation
            This function will delete the user from database
        */
    public function deleteUser($id)
    {
        $stmt = $this->con->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute())
            return true;
        return false;
    }
}
