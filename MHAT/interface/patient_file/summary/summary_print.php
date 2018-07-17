<?php
require_once("../../globals.php");
 require_once("$srcdir/patient.inc");
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
$pgender=$result_patient['sex'];
$pstreet = $result_patient['street'];
?> 
<html>

<head>
<?php html_header_show();?>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href='http://fonts.googleapis.com/css?family=Source+Sans+Pro:400,700' rel='stylesheet' type='text/css'>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
<link rel="stylesheet" type="text/css" href="../../../library/js/fancybox/jquery.fancybox-1.2.6.css" media="screen" />
<style type="text/css">@import url(../../../library/dynarch_calendar.css);</style>
  <link rel="stylesheet" href="../../../dist/css/AdminLTE.min.css">
  	<link rel="stylesheet" href="style.css"  />
		<link rel="stylesheet" href="../../../library/css/mycss.css"  />
	
<script type="text/javascript" src="../../../library/textformat.js"></script>
<script type="text/javascript" src="../../../library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="../../../library/dynarch_calendar_setup.js"></script>
<script type="text/javascript" src="../../../library/dialog.js"></script>
<script type="text/javascript" src="../../../library/js/jquery-1.6.4.min.js"></script>
<script type="text/javascript" src="../../../library/js/common.js"></script>
<script type="text/javascript" src="../../../library/js/fancybox/jquery.fancybox-1.2.6.js"></script>
<div class="container-fluid no-margin" id="print-page">

				<div class="body">
<div class="table-title">
<div class="row auo-mar">
<img WIDTH='700pt' src='../../pic/medii_rx.jpg' style=" margin-bottom: 6%;"/>
<p style="display:inline"><b>Serial No:</b>&nbsp;</th><td><?php echo $pserial ?></p>
<p class="pull-right"><b>Date:</b>&nbsp;</th><td><?php echo date("d-M-y H:i:s A") ?></p>
</div>
<!--<div style="text-align: center">
<p class="doc-head"><?php //echo $doctor ?>, MBBS;DPM;MD; FRCPsych</p>
<p>Registration No: 13954 (T C Medical Council)</p>
</div>-->
<div class="row pdata">
<p style='display: inline;'>Patient Full Name: <?php echo $pfname; ?>&nbsp<?php echo $plname ?>&nbsp<?php echo $pmname ?></p><p class="pull-right">Gender: <?php echo $pgender ?></p>
</div>
<div class="row pdata">
<p style='display: inline;'>Patient’s Address and Phone number: <?php echo $pstreet ?>, <?php echo $pmob ?></p><p class="pull-right">Age: <?php echo $page ?> Years</p>
</div>
</div>
<?php $qry2 = "SELECT *,a.encounter AS enc FROM form_encounter a WHERE a.pid = ? and a.deleted=0 order by a.encounter";
          $visit_detail = sqlStatement($qry2, array($pid));
      ?>
<div>
<h3>Patient Visit Details</h3>

<table class="table table-responsive"  style="table-layout: fixed; width: 100%">
<thead>
<tr>
<th class="text-left" width="20%">Date </th>
<th class="text-left" width="40%">Notes </th>
<th class="text-left" width="50%">Medicine Prescribed  </th>
</tr>
</thead>
		 
<tbody class="table-hover">

 		<?php  
		$enc=NULL;
		 while ($visit_detail2 = sqlFetchArray($visit_detail)){		
		 ?>
<tr>
<td class="text-left" width="20%"><?php echo date("d-M-Y",strtotime($visit_detail2['date'])); ?></td>
<td class="text-left" style="word-wrap: break-word" width="40%"><?php echo $visit_detail2['reason']; ?> </td>
<td class="text-left" width="30%">
<?php 
$enc1=$visit_detail2['enc'];
$qry2 = "SELECT *
FROM prescriptions
WHERE patient_id = ?
AND encounter = ?";
          $prescription = sqlStatement($qry2, array($pid,$enc1));
           $pres=sqlFetchArray($prescription);
         if($pres!=null){ ?>  

<table class="table table-responsive">
<thead>
<tr>
<th class="text-left">Drug</th>
<th class="text-left">Prescription</th>
</tr>
</thead>
		 
<tbody class="table-hover">
 		<?php  foreach($prescription as $pres) {
			  
		 if($pres['form'] == 1) { $drug_form = 'TAB'; }
			else if($pres['form'] == 2) { $drug_form = 'SYR'; }
			else if($pres['form'] == 3) { $drug_form = 'INJ'; }
			$qtyz = str_replace(".00", "", (string)number_format ($pres['dosage'], 2, ".", ""));
						 $times = explode('-',$pres['drug_intervals']);
  $time1 = $times[0];
    $time2 = $times[1];
	  $time3 = $times[2];
	if($time1 == 0.5) {
		$f1= '<span>&#189;</span>';
	} else {
		$f1 = $time1;
	}
	if($time2 == 0.5) {
		$f2= '<span>&#189;</span>';
	} else {
		$f2 = $time2;
	}	if($time3 == 0.5) {
		$f3= '<span>&#189;</span>';
	} else {
		$f3 = $time3;
	}
$interval= $f1.'-'.$f2.'-'.$f3;			?>
<tr>
<td class="text-left"><?php echo $drug_form ?>. &nbsp;<?php echo $pres['drug']; ?> <?php echo $qtyz ?> mg</td>
<td class="text-left"><?php echo $interval; ?> for <?php echo $pres['duration']?> Weeks</td>
</tr>
<?php
		  }
		  ?>
</tbody>
</table>
<?php }?>
		  </td>
</tr>
<?php
		  }
		  ?>
</tbody>
</table>
</div>
<?php $qry2 = "SELECT *
FROM prescriptions
WHERE patient_id = ?
AND encounter = ?";
          $prescription = sqlStatement($qry2, array($pid,$encounter));
           $pres=sqlFetchArray($prescription);
          if($pres!=null){ ?>
<div class="table-title">
<h2>Prescription</h2>

<table class="table-fill">
<thead>
<tr>
<th class="text-left">Drug</th>
<th class="text-left">Prescription</th>
</tr>
</thead>
		 
<tbody class="table-hover">
 		<?php  foreach($prescription as $pres) {
			  
		 if($pres['form'] == 1) { $drug_form = 'TAB'; }
			else if($pres['form'] == 2) { $drug_form = 'SYR'; }
			else if($pres['form'] == 3) { $drug_form = 'INJ'; }
			$qtyz = str_replace(".00", "", (string)number_format ($pres['dosage'], 2, ".", ""));
						 $times = explode('-',$pres['drug_intervals']);
  $time1 = $times[0];
    $time2 = $times[1];
	  $time3 = $times[2];
	if($time1 == 0.5) {
		$f1= '<span>&#189;</span>';
	} else {
		$f1 = $time1;
	}
	if($time2 == 0.5) {
		$f2= '<span>&#189;</span>';
	} else {
		$f2 = $time2;
	}	if($time3 == 0.5) {
		$f3= '<span>&#189;</span>';
	} else {
		$f3 = $time3;
	}
$interval= $f1.'-'.$f2.'-'.$f3;			?>
<tr>
<td class="text-left"><?php echo $pres['drug']; ?>&nbsp;<sub>(<?php echo $drug_form ?>)</sub> <?php echo $qtyz ?> mg</td>
<td class="text-left"><?php echo $interval; ?> (<?php echo $pres['drug_meal_time'] ?>) for <?php echo $pres['duration']?> Weeks</td>
</tr>
<?php
		  }
		  ?>
</tbody>
</table>
</div>
		  <?php }?>
<?php

				
				
			echo ("<div class='finalsigndiv table-title'>\n");
			$su=$_SESSION['authUser'];
			$footerow = sqlQuery("select fname,lname from users where username='$su'");
			$printingperson=$footerow['fname']." ".$footerow['lname'];	
            echo (xl('<B> Printed By: ') .$printingperson. "<br><br>");
			
            echo (xl(''). date('F j, Y, g:i a'));
	        echo ("</div>\n");
              	?>

  </div>
  				<div style="margin-left:15px">
					

										<a href="#" class="css_button" onclick="var prtContent = document.getElementById('print-page');
var WinPrint = window.open('', '', 'left=0,top=0,width=800,height=900,toolbar=0,scrollbars=0,status=0');
WinPrint.document.write(prtContent.innerHTML);
WinPrint.document.close();
WinPrint.focus();
WinPrint.print();
WinPrint.close();">
						<span>
							Print						</span>
					</a>
									</div>
</div>

	</div>
  </div>
  </body>
</html>
