<?php
require_once("../../interface/globals.php");

// $usrname = mysql_real_escape_string($data->uname);
$ptid = $pid;

$drug_intervals = $_POST["take1"].'-'.$_POST["take2"].'-'.$_POST["take3"];
// $con = mysql_connect('localhost', 'root', '');
$pres_id = $_POST["pres_id"];
$drug_id =  $_POST["drug_id"];
$ent = $_POST['encounter'];
$note = $_POST['note'];
$dosage = $_POST['dosagetype'];
$quantity = $_POST['name'];
$duration = $_POST['duration'];
$drug_units = explode('-',$_POST['units']);
$drug_dosage = $drug_units[0];
$drug_unit = $drug_units[1];
$time_frame = $_POST['time_frame'];
$drug = 'select * from drugs where drug_id ="' . $drug_id . '"  limit 1';
	$drugs1 =  sqlStatement($drug);
	$drugs = mysql_fetch_object($drugs1);
// mysql_select_db('mhat', $con);
$qry = 'UPDATE prescriptions SET note="'.$note.'",date_modified="'. date('Y-m-d h:i:s').'", drug_intervals="'.$drug_intervals.'",drug_meal_time="'.$quantity.'",form="'.$dosage.'",dosage="'.$drug_dosage.'",unit="'.$drugs->unit.'",duration="'.$duration.'",time_frame="'.$time_frame.'" where (id ="'. $pres_id .'" AND drug_id ="' . $drug_id . '" AND patient_id = "' . $ptid . '" AND encounter= "'.$ent.'")';
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
