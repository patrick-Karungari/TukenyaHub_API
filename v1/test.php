
<?php

require_once '../includes/DbConnect.php';
$db = new DbConnect();


$con =mysqli_connect("localhost","root","","tukenya");
$user = array();
if (($_SERVER['REQUEST_METHOD'] == 'POST')) {
	if (isset($_POST['uniNum'])) {
		
			$results = mysqli_query($con,"SELECT DISTINCT 
			subjects.subjectName,score.grade,subjects.semCode,subjects.yCode
			from students 
			join score on score.uniNum = students.uniNum 
			join subjects on score.subCode = subjects.subjectCode  
			WHERE students.uniNum = 'ABMI/01656/2016'
			ORDER BY `subjects`.`yCode` ASC, `subjects`.`semCode` ASC");
			while ($r = mysqli_fetch_assoc($results)) {
				$user['Results'][$results->yCode] = $r;
			}

			echo json_encode($user);
		
		
	}
}

?>