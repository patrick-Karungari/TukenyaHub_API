<?php

if ( isset( $_FILES['pdfFile'] ) ) {
	if ($_FILES['pdfFile']['type'] == "application/pdf") {
		$source_file = $_FILES['pdfFile']['tmp_name'];
		$dest_file = $_SERVER['DOCUMENT_ROOT'].'/TukenyaHub/PhotoUpload/Documents/'.$_FILES['pdfFile']['name'];

		if (file_exists($dest_file)) {
			echo "The file name already exists!!";
		}
		else {
			$newFile = base64_encode($source_file);
			move_uploaded_file(base64_decode($newFile), $dest_file )
			or die ("Error!!");
			if($_FILES['pdfFile']['error'] == 0) {
				echo "Pdf file uploaded successfully!";
				echo "<b><u>Details : </u></b><br/>";
				echo "File Name : ".$_FILES['pdfFile']['name']."<br.>"."<br/>";
				echo "File Size : ".$_FILES['pdfFile']['size']." bytes"."<br/>";
				echo "File location : https://tukenyahub-811ee828.localhost.run/TukenyaHub/PhotoUpload/Documents/".$_FILES['pdfFile']['name'];
			}
		}
	}
	else {
		if ( $_FILES['pdfFile']['type'] != "application/pdf") {
			echo "Error occured while uploading file : ".$_FILES['pdfFile']['name']."<br/>";
			echo "Invalid  file extension, should be pdf !!"."<br/>";
			echo "Error Code : ".$_FILES['pdfFile']['error']."<br/>";
		}
	}
}
?>