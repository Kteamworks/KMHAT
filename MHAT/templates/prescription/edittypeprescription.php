<?php
require_once("../../interface/globals.php");
$presid = $_POST['presid'];
$type = $_POST['type'];
$value = $_POST['value'];
$qry = 'UPDATE prescriptions SET '.$type.'="'.$value.'" where (id ="'. $presid .'")';
    $qry_res = sqlStatement($qry);
    if ($qry_res) {
         $result = array('success' => 'true');
    } else {
		$dberr = mysql_error();
         $result = array('success' => 'false', 'message' => 'Something happened');
		echo $dberr;
    }
	
 echo json_encode($result);

	?>