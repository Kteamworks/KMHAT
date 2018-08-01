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
$from_date=$_GET['from_date'];
$to_date=$_GET['to_date'];
$facility=$_GET['facility'];
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
if($facility)
{
	$where .= " AND e.pc_facility = '$facility'";
}
   
 $qry2 = "SELECT
  	e.pc_eventDate, e.pc_endDate, e.pc_startTime, e.pc_endTime, e.pc_duration, e.pc_recurrtype, e.pc_recurrspec, e.pc_recurrfreq, e.pc_catid, e.pc_eid, 
  	e.pc_title, e.pc_hometext, e.pc_apptstatus, 
  	p.fname, p.mname, p.lname, p.pid, p.pubpid, p.phone_home, p.phone_cell, f.name facility_name,p.genericname1,
  	u.fname AS ufname, u.mname AS umname, u.lname AS ulname, u.id AS uprovider_id,p.local_clinic_no, 
	
  	c.pc_catname, c.pc_catid 
  	FROM openemr_postcalendar_events AS e 
	 
  	LEFT OUTER JOIN patient_data AS p ON p.pid = e.pc_pid 
	LEFT OUTER JOIN facility AS f ON f.id = e.pc_facility
  	LEFT OUTER JOIN users AS u ON u.id = e.pc_aid 
	LEFT OUTER JOIN openemr_postcalendar_categories AS c ON c.pc_catid = e.pc_catid
where pc_pid != '' AND pc_eventDate>='".$from_date."' AND pc_eventDate <= '".$to_date."'$where	";
       $prescription = sqlStatement($qry2);
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

  while ($row = sqlFetchArray($prescription)) { 
	  $prescriptions[] = $row;
  }
$logo = '<img class="pull-left" style="width: 100%" src="images/image.jpg"  alt="image">';
$message .= '<div style="  font: 87.5%/1.5em "Lato", sans-serif;
  margin: 0;">
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
  vertical-align:middle;">Patient Name</th>
  <th class="text-left" style="  color:#D5DDE5;
  background:#1b1e24;
  border-bottom:4px solid #9ea7af;
  border-right: 1px solid #343a45;
  font-size:23px;
  font-weight: 100;
  padding:24px;
  text-align:left;
  text-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
  vertical-align:middle;">Date</th>
<th class="text-left" style="  color:#D5DDE5;
  background:#1b1e24;
  border-bottom:4px solid #9ea7af;
  border-right: 1px solid #343a45;
  font-size:23px;
  font-weight: 100;
  padding:24px;
  text-align:left;
  text-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
  vertical-align:middle;">From Time</th>
  <th class="text-left" style="  color:#D5DDE5;
  background:#1b1e24;
  border-bottom:4px solid #9ea7af;
  border-right: 1px solid #343a45;
  font-size:23px;
  font-weight: 100;
  padding:24px;
  text-align:left;
  text-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
  vertical-align:middle;">To Time</th>
   <th class="text-left" style="  color:#D5DDE5;
  background:#1b1e24;
  border-bottom:4px solid #9ea7af;
  border-right: 1px solid #343a45;
  font-size:23px;
  font-weight: 100;
  padding:24px;
  text-align:left;
  text-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
  vertical-align:middle;">Local Clinic No</th>
</tr>
</thead>
<tbody class="table-hover">';
 		  foreach($prescriptions as $pres) {
			//$qtyz = str_replace(".00", "", (string)number_format ($pres['dosage'], 2, ".", ""));
$message .= '<tr>
<td class="text-left" style="  background:#FFFFFF;
  padding:20px;
  text-align:left;
  vertical-align:middle;
  font-weight:300;
  font-size:18px;
  text-shadow: -1px -1px 1px rgba(0, 0, 0, 0.1);
  border-right: 1px solid #C1C3D1; nowrap">'.$pres['fname'].'.&nbsp; '.$pres['lname'].'&nbsp; '.$pres['mname'].'</td>
<td class="text-left" style="  background:#FFFFFF;
  padding:20px;
  text-align:left;
  vertical-align:middle;
  font-weight:300;
  font-size:18px;
  text-shadow: -1px -1px 1px rgba(0, 0, 0, 0.1);
  border-right: 1px solid #C1C3D1;">'.oeFormatShortDate($pres['pc_eventDate']).'</td>
  <td class="text-left" style="  background:#FFFFFF;
  padding:20px;
  text-align:left;
  vertical-align:middle;
  font-weight:300;
  font-size:18px;
  text-shadow: -1px -1px 1px rgba(0, 0, 0, 0.1);
  border-right: 1px solid #C1C3D1;">'.$pres['pc_startTime'].'</td>
  <td class="text-left" style="  background:#FFFFFF;
  padding:20px;
  text-align:left;
  vertical-align:middle;
  font-weight:300;
  font-size:18px;
  text-shadow: -1px -1px 1px rgba(0, 0, 0, 0.1);
  border-right: 1px solid #C1C3D1;">'.$pres['pc_endTime'].'</td>
  <td class="text-left" style="  background:#FFFFFF;
  padding:20px;
  text-align:left;
  vertical-align:middle;
  font-weight:300;
  font-size:18px;
  text-shadow: -1px -1px 1px rgba(0, 0, 0, 0.1);
  border-right: 1px solid #C1C3D1;">'.$pres['local_clinic_no'].'</td>
</tr>';
		  }
		  
$message .='
</tbody>
</table><br><br></div>';
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
    $pdf->Output($_SERVER['DOCUMENT_ROOT'] . '/'.'appointment.pdf', 'F');
		    $mail = new MyMailer();
	
    $email_subject=xl('MHAT Appointments');
    $email_sender="kavaiidev01@gmail.com";
    $mail->AddReplyTo($email_sender, $email_sender);
    $mail->SetFrom($email_sender, $email_sender);
    $mail->Subject = $email_subject;
	$querye1 = "SELECT * FROM facility WHERE id='".$_SESSION['facility_id']. "'";
$res_em1 = sqlStatement($querye1);
$res_em2=sqlFetchArray($res_em1);
$facility_name=$res_em2['name'];
   // $mail->MsgHTML("<html><body><div class='wrapper'>".$logo."&nbsp;".$message."</div></body></html>");
    $mail->MsgHTML("<html><body><div class='wrapper'><p>Find the Appointments in attachment.</p></div></body></html>");
    $mail->IsHTML(true);
    $mail->AltBody = $message;
/* $rows = array();
while($row1 = sqlFetchArray($res_em)) {
    $rows[] = $row1;
}

  foreach ($rows as $eid) {
*/
$querye = "SELECT * FROM facility WHERE id='".$_SESSION['facility_id']. "'";
$res_em = sqlStatement($querye);
while($row1 = sqlFetchArray($res_em)) {
    $mail->AddAddress($row1['email'], 'MHAT');
  }
  //$mail->AddAddress('sada059@gmail.com', 'MHAT');
//  foreach($p_rows as $patient) { 

	$mail->AddAttachment($_SERVER['DOCUMENT_ROOT']."/"."appointment.pdf");
 // }	
    if ($mail->Send()) {
        echo "EMAIL SENT SUCCESSFULLY";
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