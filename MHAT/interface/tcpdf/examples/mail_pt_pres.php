<?php
// Copyright (C) 2010-2015 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This is an inventory transactions list.

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//

require_once("../../globals.php");
require_once($GLOBALS['fileroot'] . "/library/classes/Controller.class.php");
require_once($GLOBALS['fileroot'] . "/library/classes/Prescription.class.php");
require_once($GLOBALS['fileroot'] . "/controllers/C_Prescription.class.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/acl.inc");
require_once("$srcdir/formatting.inc.php");
require_once("$srcdir/sql.inc");
require_once("$srcdir/formdata.inc.php");
require_once("$srcdir/classes/postmaster.php"); 
require_once("tcpdf_include.php");

   $createDate = new DateTime();

$strip = $createDate->format('F j, Y');
/* $query = "SELECT drug,quantity FROM prescriptions WHERE date_added >= NOW() - INTERVAL 1 DAY";
$res = sqlStatement($query);
$prescriptions = array();
  while ($row = sqlFetchArray($res)) {
	  $prescriptions[] = $row;
  }
  foreach($prescriptions as $prescription) {
	 //Comment these later. This is just for testing
	 $prescription['drug']="Crocin 5 mg";
	 $prescription['quantity']=10;
    $message .='<table><tr><th>Drug:</th><td>'.$prescription['drug'].'</td></tr><tr><th>Quantity:</th><td>'.$prescription['quantity'].'</td></tr></table></div>';
  } */
  

	// ---------------------------------------------------------


$ent = $_POST['ent'];
 $qry2 = "SELECT *
FROM prescriptions
WHERE patient_id = ?
AND encounter = ?";
          $prescription = sqlStatement($qry2, array($pid,$ent));
 if(sqlNumRows($prescription)!=0) {

$message = "";
$result_patient = getPatientData($pid, "*, DATE_FORMAT(DOB,'%Y-%m-%d') as DOB_YMD");
$pserial = $result_patient['genericname1'];
$pfname = $result_patient['fname'];
$plname = $result_patient['lname'];
$pmname = $result_patient['mname'];
$page = $result_patient['age'];
$pmob = $result_patient['phone_cell'];
$plocality = $result_patient['locality'];
$pcity = $result_patient['city'];
$pstate = $result_patient['state'];
$facility = $result_patient['facility_id'];

$pgender = $result_patient['sex'];

$pstreet = $result_patient['street'];
	$visit_pres=sqlStatement("select prescriber from form_encounter where encounter='".$ent."'");
			$visit_pres1=sqlFetchArray($visit_pres);
			$visit_pres2=$visit_pres1['prescriber'];
  while ($row = sqlFetchArray($prescription)) { 
	  $prescriptions[] = $row;
  }
$logo = '<img class="pull-left" style="width: 100%" src="images/image.jpg"  alt="image">';
if($visit_pres2==51)
{
$message .= '<div style="  font: 87.5%/1.5em "Lato", sans-serif;
  margin: 0;">
<div style="   display: block;
  margin: auto;
  max-width: 600px;
  padding:5px;
  width: 100%;">
<p style=" display: inline-block;vertical-align: top;"><b>Serial No:</b> '.$pserial.'</p>
<p style=" display: inline-block;vertical-align: top;float:right"><b>Date:</b> '.$strip.'</p>
<p style="text-align: center">Dr. Parvez Thekkumpurath MBBS; MRCPsych</p>
<p style="text-align: center">Registration No: 28550 (T C Medical Council)</p>
<p style=" display: inline-block;vertical-align: top;"><b>Patient Full Name:</b> '.$pfname.' '.$plname.' '.$pmname.'</p>
<p  style=" display: inline-block;vertical-align: top;float:right" ><b>Gender:</b> '.$pgender.'</p>
<p style=" display: inline-block;vertical-align: top;"><b>Patient’s Address and Phone number:</b> '.$pstreet.', '.$pmob.'</p><p  style=" display: inline-block;vertical-align: top;float:right"><b>Age:</b> '.$page.' Years</p>
</div>
<table style="  border-radius:3px;
  border-collapse: collapse;
  height: 320px;
  margin: auto;
  max-width: 600px;
  padding:5px;
  width: 100%;
  box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
  animation: float 5s infinite;">
<thead>
<tr style="  border-top: 1px solid #C1C3D1;
  border-bottom-: 1px solid #C1C3D1;
  color:#666B85;
  font-size:16px;
  font-weight:normal;
  text-shadow: 0 1px 1px rgba(256, 256, 256, 0.1);">
<th class="text-left" style="  color:#D5DDE5;
  background:#1b1e24;
  border-bottom:4px solid #9ea7af;
  border-right: 1px solid #343a45;
  font-size:23px;
  font-weight: 100;
  padding:24px;
  text-align:left;
  text-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
  vertical-align:middle;">Drug</th>
<th class="text-left" style="  color:#D5DDE5;
  background:#1b1e24;
  border-bottom:4px solid #9ea7af;
  border-right: 1px solid #343a45;
  font-size:23px;
  font-weight: 100;
  padding:24px;
  text-align:left;
  text-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
  vertical-align:middle;">Prescription</th>
</tr>
</thead>
<tbody class="table-hover">';
 		  foreach($prescriptions as $pres) {
			  
		 if($pres['form'] == 1) { $drug_form = 'TAB'; }
			else if($pres['form'] == 2) { $drug_form = 'SYR'; }
			else if($pres['form'] == 3) { $drug_form = 'INJ'; }
			if($pres['time_frame']==1)
		{
			$time_frame1="Day(s)";
	}else if($pres['time_frame']==2)
		{
			$time_frame1="Week(s)";
		}else  if($pres['time_frame']==3)
		{
		$time_frame1="Month(s)";
		}
		else  if($pres['time_frame']==4)
		{
		$time_frame1="Year(s)";
		}
			$qtyz = str_replace(".00", "", (string)number_format ($pres['dosage'], 2, ".", ""));
if($pres['form'] == 3)
{
$message .= '<tr>
<td class="text-left" style="  background:#FFFFFF;
  padding:20px;
  text-align:left;
  vertical-align:middle;
  font-weight:300;
  font-size:18px;
  text-shadow: -1px -1px 1px rgba(0, 0, 0, 0.1);
  border-right: 1px solid #C1C3D1;">'.$drug_form.'.&nbsp; '.$pres['drug'].'&nbsp; '.$qtyz.' mg</td>
<td class="text-left" style="  background:#FFFFFF;
  padding:20px;
  text-align:left;
  vertical-align:middle;
  font-weight:300;
  font-size:18px;
  text-shadow: -1px -1px 1px rgba(0, 0, 0, 0.1);
  border-right: 1px solid #C1C3D1;"> deep i/m once in every '.$pres['duration'].' '.$time_frame1.'</td>
</tr>';
}else
{
	
$message .= '<tr>
<td class="text-left" style="  background:#FFFFFF;
  padding:20px;
  text-align:left;
  vertical-align:middle;
  font-weight:300;
  font-size:18px;
  text-shadow: -1px -1px 1px rgba(0, 0, 0, 0.1);
  border-right: 1px solid #C1C3D1;">'.$drug_form.'.&nbsp; '.$pres['drug'].'&nbsp; '.$qtyz.' mg</td>
<td class="text-left" style="  background:#FFFFFF;
  padding:20px;
  text-align:left;
  vertical-align:middle;
  font-weight:300;
  font-size:18px;
  text-shadow: -1px -1px 1px rgba(0, 0, 0, 0.1);
  border-right: 1px solid #C1C3D1;">'.$pres['drug_intervals'].' ('. $pres['drug_meal_time'] .') for '.$pres['duration'].' '.$time_frame1.'</td>
</tr>';	
	
	
}
		  }
		  
$message .='
</tbody>
</table><br><br>
<div class="signdiv">
Signature:<img src="images/Parmez_sig.jpg" /><br><br>Dispensed By: &nbsp;&nbsp;&nbsp;<br><br>Date of Dispensing: &nbsp;&nbsp;&nbsp;'.$strip.'</div></div>';
}else{
$message .= '<div style="  font: 87.5%/1.5em "Lato", sans-serif;
  margin: 0;">
<div style="   display: block;
  margin: auto;
  max-width: 600px;
  padding:5px;
  width: 100%;">
<p style=" display: inline-block;vertical-align: top;"><b>Serial No:</b> '.$pserial.'</p>
<p style=" display: inline-block;vertical-align: top;float:right"><b>Date:</b> '.$strip.'</p>
<p style="text-align: center">Dr T Manoj Kumar, MBBS;DPM;MD; FRCPsych</p>
<p style="text-align: center">Registration No: 13954 (T C Medical Council)</p>
<p style=" display: inline-block;vertical-align: top;"><b>Patient Full Name:</b> '.$pfname.' '.$plname.' '.$pmname.'</p>
<p  style=" display: inline-block;vertical-align: top;float:right" ><b>Gender:</b> '.$pgender.'</p>
<p style=" display: inline-block;vertical-align: top;"><b>Patient’s Address and Phone number:</b> '.$pstreet.', '.$pmob.'</p><p  style=" display: inline-block;vertical-align: top;float:right"><b>Age:</b> '.$page.' Years</p>
</div>
<table style="  border-radius:3px;
  border-collapse: collapse;
  height: 320px;
  margin: auto;
  max-width: 600px;
  padding:5px;
  width: 100%;
  box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
  animation: float 5s infinite;">
<thead>
<tr style="  border-top: 1px solid #C1C3D1;
  border-bottom-: 1px solid #C1C3D1;
  color:#666B85;
  font-size:16px;
  font-weight:normal;
  text-shadow: 0 1px 1px rgba(256, 256, 256, 0.1);">
<th class="text-left" style="  color:#D5DDE5;
  background:#1b1e24;
  border-bottom:4px solid #9ea7af;
  border-right: 1px solid #343a45;
  font-size:23px;
  font-weight: 100;
  padding:24px;
  text-align:left;
  text-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
  vertical-align:middle;">Drug</th>
<th class="text-left" style="  color:#D5DDE5;
  background:#1b1e24;
  border-bottom:4px solid #9ea7af;
  border-right: 1px solid #343a45;
  font-size:23px;
  font-weight: 100;
  padding:24px;
  text-align:left;
  text-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
  vertical-align:middle;">Prescription</th>
</tr>
</thead>
<tbody class="table-hover">';
 		  foreach($prescriptions as $pres) {
			  
		 if($pres['form'] == 1) { $drug_form = 'TAB'; }
			else if($pres['form'] == 2) { $drug_form = 'SYR'; }
			else if($pres['form'] == 3) { $drug_form = 'INJ'; }
			if($pres['time_frame']==1)
		{
			$time_frame1="Day(s)";
	}else if($pres['time_frame']==2)
		{
			$time_frame1="Week(s)";
		}else  if($pres['time_frame']==3)
		{
		$time_frame1="Month(s)";
		}
		else  if($pres['time_frame']==4)
		{
		$time_frame1="Year(s)";
		}
			
			$qtyz = str_replace(".00", "", (string)number_format ($pres['dosage'], 2, ".", ""));
if($pres['form'] == 3)
{
$message .= '<tr>
<td class="text-left" style="  background:#FFFFFF;
  padding:20px;
  text-align:left;
  vertical-align:middle;
  font-weight:300;
  font-size:18px;
  text-shadow: -1px -1px 1px rgba(0, 0, 0, 0.1);
  border-right: 1px solid #C1C3D1;">'.$drug_form.'.&nbsp; '.$pres['drug'].'&nbsp; '.$qtyz.' mg</td>
<td class="text-left" style="  background:#FFFFFF;
  padding:20px;
  text-align:left;
  vertical-align:middle;
  font-weight:300;
  font-size:18px;
  text-shadow: -1px -1px 1px rgba(0, 0, 0, 0.1);
  border-right: 1px solid #C1C3D1;"> deep i/m once in every '.$pres['duration'].' '.$time_frame1.'</td>
</tr>';
}else
{
	
$message .= '<tr>
<td class="text-left" style="  background:#FFFFFF;
  padding:20px;
  text-align:left;
  vertical-align:middle;
  font-weight:300;
  font-size:18px;
  text-shadow: -1px -1px 1px rgba(0, 0, 0, 0.1);
  border-right: 1px solid #C1C3D1;">'.$drug_form.'.&nbsp; '.$pres['drug'].'&nbsp; '.$qtyz.' mg</td>
<td class="text-left" style="  background:#FFFFFF;
  padding:20px;
  text-align:left;
  vertical-align:middle;
  font-weight:300;
  font-size:18px;
  text-shadow: -1px -1px 1px rgba(0, 0, 0, 0.1);
  border-right: 1px solid #C1C3D1;">'.$pres['drug_intervals'].' ('. $pres['drug_meal_time'] .') for '.$pres['duration'].' '.$time_frame1.'</td>
</tr>';	
	
	
}
		  }
		  
$message .='
</tbody>
</table><br><br><div class="signdiv">
Signature: <img src="images/Manoj_sig.jpeg" /><br><br>Dispensed By: &nbsp;&nbsp;&nbsp;<br><br>Date of Dispensing: &nbsp;&nbsp;&nbsp;'.$strip.'</div></div>';	
	
	
	
	
	
}

		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false, true);

	// set document information
	$pdf->SetCreator(PDF_CREATOR);
	$pdf->SetAuthor('Dr.Manoj');
	$pdf->SetTitle('Prescriptions');
	$pdf->SetSubject('Email Prescriptions');
	$pdf->SetKeywords('MHAT, Patients prescriptions for today');

	// set default header data
	$pdf->SetHeaderData("../images/image.jpg", '180', ''.'', '');

	// set header and footer fonts
	$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
	$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

	// set default monospaced font
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

	// set margins
	$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
	$pdf->SetHeaderMargin('-2');
	$pdf->SetFooterMargin('0');
	
	// set auto page breaks
	$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

	// set image scale factor
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

	// set some language-dependent strings (optional)
	if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
	require_once(dirname(__FILE__).'/lang/eng.php');
	$pdf->setLanguageArray($l);
	}
	// set default font subsetting mode
	$pdf->setFontSubsetting(true);

	// Set font
	$pdf->SetFont('helvetica', '', 14, '', true);
	
	// Add a page
	// This method has several options, check the source code documentation for more information.
	$pdf->AddPage();
// Print text using writeHTMLCell()

	$pdf->writeHTMLCell(180, 178, '', '', $message, 0, 1, 0, true, '', true);
    $pdf->Output($_SERVER['DOCUMENT_ROOT'] . '/'.$pfname.'_prescription.pdf', 'F');
		    $mail = new MyMailer();
	
    $email_subject=xl('MHAT Patient Prescriptions');
    $email_sender="kavaiidev01@gmail.com";
    $mail->AddReplyTo($email_sender, $email_sender);
    $mail->SetFrom($email_sender, $email_sender);
    $mail->Subject = $email_subject;
   // $mail->MsgHTML("<html><body><div class='wrapper'>".$logo."&nbsp;".$message."</div></body></html>");
    $mail->MsgHTML("<html><body><div class='wrapper'><p>Find the prescription for ".$pfname." in attachment.</p></div></body></html>");
    $mail->IsHTML(true);
    $mail->AltBody = $message;
$querye = "SELECT * FROM facility WHERE id=".$facility;
$res_em = sqlStatement($querye);
/* $rows = array();
while($row1 = sqlFetchArray($res_em)) {
    $rows[] = $row1;
}

  foreach ($rows as $eid) {
*/
while($row1 = sqlFetchArray($res_em)) {
    $mail->AddAddress($row1['email'], 'MHAT');
  }
  //$mail->AddAddress('sada059@gmail.com', 'MHAT');
//  foreach($p_rows as $patient) { 

	$mail->AddAttachment($_SERVER['DOCUMENT_ROOT']."/".$pfname."_prescription.pdf");	
 // }	
    if ($mail->Send()) {
        echo "1";
    } else {
        $email_status = $mail->ErrorInfo;
        error_log("EMAIL ERROR: ".$email_status,0);
        return false;
    }
}
	
 else {
	 echo "2";
 }
	?>