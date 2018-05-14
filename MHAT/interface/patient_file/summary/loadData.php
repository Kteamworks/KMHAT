<?php
include_once('function.php');
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
$pgender = $result_patient['sex'];

$pstreet = $result_patient['street'];
if(isset($_POST['pageId']) && !empty($_POST['pageId'])){
   $id=$_POST['pageId'];
}else{
   $id='0';
}
$pageLimit=PAGE_PER_NO*$id;
$qry_dr = "SELECT * FROM drug_dosage WHERE drug_id = ?";
$qry = "SELECT form_encounter.date,lists.next_date, lists.title, form_encounter.id,form_encounter.provider_id,form_encounter.reason,form_encounter.encounter,form_encounter.sensitivity
FROM form_encounter
LEFT JOIN lists
ON form_encounter.pid = lists.pid
AND form_encounter.encounter =lists.encounter 
WHERE form_encounter.pid =?
AND form_encounter.date is not null
Order By form_encounter.date DESC
limit  $pageLimit,".PAGE_PER_NO;
          $res = sqlStatement($qry, array($pid));
//$query="select post,postlink from pagination order by id desc
//limit $pageLimit,".PAGE_PER_NO;
//$res=mysql_query($query);
$count=sqlNumRows($res);
$HTML='';
if($count > 0){
while($row=sqlFetchArray($res)){
   $date=$row['date'];
   $createDate = new DateTime($date);

$strip = $createDate->format('F j, Y');
   $encount = $row['encounter'];
   $app_date=$row['next_date'];
   $root = $GLOBALS['webroot'];
if($row['sensitivity'] != null) { 
  $sensitivity = '<tr>
  <th>Status</th>
    <td>'. $row['sensitivity'] .'</td>
	</tr>';
}
 if($row['title'] != null) { 
	$title = '<tr>
	<th>Diagnosis</th>
	<td>'. $row['title'] .'</td>
  </tr>';
 } 
 if($row['reason'] != null) { 
	$reason = $row['reason'];
		 }

  $qry2 = "SELECT *
FROM prescriptions
WHERE patient_id = ?
AND encounter = ?";
          $prescription = sqlStatement($qry2, array($pid,$row['encounter']));
		 
   $doctorname = sqlStatement("SELECT username FROM users WHERE id=?", array($row['provider_id']));  
   while($doctor = sqlFetchArray($doctorname)) { $doc =  $doctor['username']; };
   $HTML.='<li class="time-label"><span class="bg-green">'.$strip.'</span></li>';
   $HTML.='<li>
                                                                  <a id="encounter" href="../encounter/encounter_top.php?set_encounter='. $encount .'">  <i class="fa fa-mail-reply-all bg-yellow uni" style="margin-left: 20px;padding: 6px;border-radius: 13px;" title="View Details"></i></a>
                                                                <a class="element iframe pull-right" target="_parent" href="'.$root.'/interface/patient_file/encounter/view_form.php?formname=newpatient&amp;id='. $row['id'] .'" onclick="top.restoreSession()"><span><i class="fa fa-pencil-square-o" data-toggle="tooltip" data-placement="top" title="Edit Visit" aria-hidden="true"></i></span></a>
																<div class="timeline-item">
                                    <h3 class="timeline-header">
                                    
                                    <div class="user-block" style="display: inline-block;">
                                       
                                                                                            <img src="https://d30y9cdsu7xlg0.cloudfront.net/png/23420-200.png" class="img-circle img-bordered-sm" alt="User Image">';
																							
                                            
                                        
   $HTML.=' <span class="username">
                                          <a href="#">'. $doc .'</a>
                                          
                                        </span>
                                        <span class="description"><i class="fa fa-clock-o"></i> '. $strip.'</span>
                                      </div><!-- /.user-block -->
									  
									  <a href="'.$root.'/controller.php?prescription&edit&id=&pid='. $pid .'" class="element iframe rx_modal pull-right" onclick="setEnc('. $encount .')" data-toggle="tooltip" data-placement="top" title="Add Prescription">

 <span class="fa-stack fa-lg">
  <i class="fa fa-circle fa-stack-2x"></i>
  <i class="fa fa-medkit fa-stack-1x fa-inverse"></i>
</span></a>
 <a data-toggle="modal" data-target="#myPresView" class="elementv pull-right" title="View Prescription"> <span class="fa-stack fa-lg">
  <i class="fa fa-circle fa-stack-2x"></i>
  <i class="fa fa-eye fa-stack-1x fa-inverse"></i>
</span></a>
  <div class="modal fade" id="myPresView" role="dialog">
    <div class="modal-dialog" style="overflow-y: scroll; max-height:85%; min-width: 80%; margin-top: 50px; margin-bottom:50px;"> 
       
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Prescriptions</h4>
        </div>
       <div class="modal-body clearfix">
                <img class="pull-left img-responsive" src="image/image.jpg"  alt="image">
				<div class="body">
<div class="table-title">
<div class="row auo-mar">
<p style="display:inline"><b>Serial No:</b>&nbsp;</th><td>'.$pserial.'</p>
<p class="pull-right"><b>Visit Date:</b>&nbsp;</th><td>'.$strip.'</p>
</div>
<div style="text-align: center">
<p class="doc-head">Dr T Manoj Kumar, MBBS;DPM;MD; FRCPsych</p>
<p>Registration No: 13954 (T C Medical Council)</p>
</div>
<div class="row pdata">
<p>Patient Full Name: '.$pfname.'&nbsp'.$plname.'&nbsp'.$pmname.'</p><p class="pull-right">Gender: '.$pgender.'</p>
</div>
<div class="row pdata">
<p>Patient’s Address and Phone number: '.$pstreet.', '.$pmob.'</p><p class="pull-right">Age: '.$page.'</p>
</div>
</div>
<table class="table-fill">
<thead>
<tr>
<th class="text-left">Drug</th>
<th class="text-left">Prescription</th>
</tr>
</thead>
<tbody class="table-hover">';
 		  foreach($prescription as $pres) {
			  
		 if($pres['form'] == 1) { $drug_form = 'TAB'; }
			else if($pres['form'] == 2) { $drug_form = 'SYR'; }
			else if($pres['form'] == 3) { $drug_form = 'INJ'; }
			$qtyz = str_replace(".00", "", (string)number_format ($pres['dosage'], 2, ".", ""));
$HTML .= '<tr>
<td class="text-left">'. $drug_form.'.&nbsp; '.$pres['drug'].'&nbsp; '.$qtyz.' mg</td>
<td class="text-left">'.$pres['drug_intervals'].' ('. $pres['drug_meal_time'] .') for '.$pres['duration'].' Weeks</td>
</tr>';
		  }
		  
$HTML .='
</tbody>
</table>
  

  </div>
				</div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
	  </div>
      </div>
	  
<a href="'.$root.'/controller.php?prescription&list&id='. $pid .'" class="element iframe rx_modal pull-right" data-toggle="tooltip" data-placement="top" title="Print Prescription">

 <span class="fa-stack fa-lg">
  <i class="fa fa-circle fa-stack-2x"></i>
  <i class="fa fa-print fa-stack-1x fa-inverse"></i>
</span></a>
<a href="#" id="mail'.$encount.'" class="element pull-right" data-toggle="tooltip" data-placement="top" title="Email Prescription">

 <span class="fa-stack fa-lg">
  <i class="fa fa-circle fa-stack-2x"></i>
  <i class="fa fa-envelope fa-stack-1x fa-inverse"></i>
</span></a>
 <script type="text/javascript">
$(document).ready(function(){
			  $("#mail'.$encount.'").on("click", function(){
		  var str = this.id;
		   var thenum = str.replace( /^\D+/g, ""); 
    $.ajax({
    type: "POST",
            url: "../../tcpdf/examples/mail_pt_pres.php",
            dataType: "html",
            data: "ent="+thenum,
           beforeSend: function() {
		   $("#loading").show();
		   },
            success: function(response) {
						$("#loading").hide();
				 if (response == "1") {
            BootstrapDialog.show({
            title: "Success!",
            message: "Prescription has been emailed to the facility."
        });

            }
			else if (response == "2") {
            BootstrapDialog.show({
            title: "Failed!",
            message: "Prescription not found for the visit."
        });

            }
			else {
				 BootstrapDialog.show({
            title: "Failed!",
            message: "Unable to send emails at the moment. Please try again later."
        });
			}
    }
	   })
            return false;
	   }); 
   $(".element").tooltip();
   $(".editscript").tooltip();
  $(".iframe").fancybox( {
  "left":10,
	"overlayOpacity" : 0.0,
	"showCloseButton" : true,
	"frameHeight" : 550,
	"frameWidth" : 550
  });
  $("#fancy_close").click(function() {
	  window.location.reload();
	  });
});
$(".bs-example-modal-sm .modal-content:first").remove();
</script>
';
 $HTML.='<table class="table table-striped">'.$sensitivity;
   if(sqlNumRows($prescription) != null) {
	   $HTML.='
 <tr>

  <th>Prescription</th><td>';
 		  foreach($prescription as $pres) {
		  $results_dr = sqlStatement($qry_dr, array($pres['drug_id']));
$qtyz = str_replace(".00", "", (string)number_format ($pres['dosage'], 2, ".", ""));
		  $HTML .= 
     $pres['drug'] .' &nbsp;'.$qtyz.'mg &nbsp;:&nbsp;'. $pres['drug_intervals'] .'&nbsp('. $pres['drug_meal_time'] .') for '. $pres['duration'].' weeks<a id="'. $pres['id'].'"  class="editscript" data-toggle="modal" data-target="#myModal'. $pres['id'].'">&nbsp;&nbsp;<span><i class="fa fa-pencil-square-o" data-toggle="tooltip" data-placement="top" title="Edit Prescription" aria-hidden="true"></i></span></a>
     <!-- Modal -->

<!-- Modal -->
  <div class="modal fade" id="myModal'. $pres['id'].'" role="dialog">
    <div class="modal-dialog">
    
      <!-- Modal content-->
      <div class="modal-content">
	  <form id="merge-form'. $pres['id'].'" action="'.$root.'/templates/prescription/editprescription.php" method="POST" >
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Edit '.$pres['drug'].'</h4>
        </div>
        <div class="modal-body">
		                <div class="row">
                    <div class="col-md-4">
                    </div>
                    <div class="col-md-6" id="merge_loader'. $pres['id'].'"  style="display:none;">
                        <img src="gifloader.gif"><br/><br/><br/>
                    </div><!-- /.merge-loader -->
                </div>
                <div id="merge_body'. $pres['id'].'">
                    <div id="merge-body-alert'. $pres['id'].'">
                        <div class="row">
                            <div class="col-md-12">
                                <div id="merge-succ-alert'. $pres['id'].'" class="alert alert-success alert-dismissable" style="display:none;" >
                                    <!-- <button id="dismiss-merge'. $pres['id'].'" type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button> -->
                                    <h4><i class="icon fa fa-check"></i>Alert!</h4>
                                    <div id="message-merge-succ'. $pres['id'].'"></div>
                                </div>
                                <div id="merge-err-alert'. $pres['id'].'" class="alert alert-danger alert-dismissable" style="display:none;">
                                    <!-- <button id="dismiss-merge2" type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button> -->
                                    <h4><i class="icon fa fa-ban"></i>Alert!</h4>
                                    <div id="message-merge-err'. $pres['id'].'"></div>
                                </div>
                            </div>
                        </div>
                    </div><!-- /.merge-alert -->
                    <div id="merge-body-form'. $pres['id'].'">
         <input type="hidden" name="pres_id" value="'. $pres['id'].'">       
		<input type="hidden" name="drug_id" value="'. $pres['drug_id'].'">
		<input type="hidden" name="encounter" value="'. $row['encounter'].'">
           <div class="form-group">
            <label>Dosage Type</label>
			<select name="dosagetype" class="form-control" required>
			
    <option value="1"';
	if($pres['form'] == 1) {
		$HTML .= 'selected';
	}
		$HTML .='
	>Tablet</option>
    <option value="2"';
	if($pres['form'] == 2) {
		$HTML .= 'selected';
	}
		$HTML .='>Syrup</option>
    <option value="3"';
	if($pres['form'] == 3) {
		$HTML .= 'selected';
	}
		$HTML .='>Injection</option>
  </select>
   
        </div>
		        <div class="form-group">
            <label>Medicine Units</label>
			<select name="units" class="form-control" >
			<option value="">-- Choose Medicine Units --</option>';
		  while ($drug_data = sqlFetchArray($results_dr)) { 
		  $qtyz = str_replace(".00", "", (string)number_format ($drug_data['dosage_quantity'], 2, ".", ""));
    $HTML .= '<option value="'. $drug_data['dosage_quantity'] .'-'. $drug_data['dosage_units'] .'"';
	if($pres['dosage'] == $drug_data['dosage_quantity']) {
		$HTML .= 'selected';
	}
	$HTML .='>'. $qtyz .'&nbsp;'. $drug_data['dosage_units'] .'</option>';
			 }
			$HTML .= ' </select>
  </div>

        <!-- EMAIL -->
        <div class="form-group">
            <label>Take</label>
			<select name="take1" ng-model="take1"  style="width:45px" ng-required="false">
  <option value="0"';
  $times = explode('-',$pres['drug_intervals']);
  $time1 = $times[0];
    $time2 = $times[1];
	  $time3 = $times[2];
	if($time1 == 0) {
		$HTML .= 'selected';
	}
		$HTML .='
	>0</option>
    <option value="1"';
	if($time1 == 1) {
		$HTML .= 'selected';
	}
		$HTML .='
	>1</option>
    <option value="2"';
	if($time1 == 2) {
		$HTML .= 'selected';
	}
		$HTML .='
	>2</option>
 </select>
 <select name="take2" style="width:45px" ng-model="take2"  ng-required="false">
    <option value="0"';
	if($time2 == 0) {
		$HTML .= 'selected';
	}
		$HTML .='
	>0</option>
    <option value="1"';
	if($time2 == 1) {
		$HTML .= 'selected';
	}
		$HTML .='
	>1</option>
    <option value="2"';
	if($time2 == 2) {
		$HTML .= 'selected';
	}
		$HTML .='
	>2</option>
 </select>
 <select name="take3" style="width:45px" ng-model="take3"  ng-required="false">
   <option value="0"';
	if($time3 == 0) {
		$HTML .= 'selected';
	}
		$HTML .='
	>0</option>
    <option value="1"';
	if($time3 == 1) {
		$HTML .= 'selected';
	}
		$HTML .='
	>1</option>
    <option value="2"';
	if($time3 == 2) {
		$HTML .= 'selected';
	}
		$HTML .='
	>2</option>
 </select> 
             <select name="name" ng-model="name"  style="width:150px" ng-required="false">
  <option value="BF"';
	if($pres['drug_meal_time'] == "BF") {
		$HTML .= 'selected';
	}
		$HTML .='
	>Before Food</option>
    <option value="AF"';
	if($pres['drug_meal_time'] == "AF") {
		$HTML .= 'selected';
	}
		$HTML .='
	>After Food</option>
 </select>
        </div>
						<div class="form-group">
            <label>Duration</label>
 <select name="duration" class="form-control" >
   <option value="1"';
	if($pres['duration'] == 1) {
		$HTML .= 'selected';
	}
		$HTML .='
	>1 Week</option>
    <option value="2"';
	if($pres['duration'] == 2) {
		$HTML .= 'selected';
	}
		$HTML .='
	>2 Weeks</option>
    <option value="3"';
	if($pres['duration'] == 3) {
		$HTML .= 'selected';
	}
		$HTML .='
	>3 Weeks</option>
 </select>
	</div>
		<div class="form-group">
            <label>Notes</label>
	<textarea name="note" class="form-control" wrap="virtual" ng-required="false">'.$pres['note'] .'</textarea>
	</div>
        </div>
        <div class="modal-footer">
		<button type="submit" class="btn btn-primary" >Submit</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
		</form>
      </div>
      </div>
	  </div>
    </div>
  </div><br>
  <script type="text/javascript">$(document).ready(function(){
 
 $("#merge-form'. $pres['id'].'").on("submit", function(){
    $.ajax({
    type: "POST",
            url: $(this).attr("action"),
            dataType: "html",
            data: $(this).serialize(),
            beforeSend: function() {
            $("#merge_body").hide();
                    $("#merge_loader'. $pres['id'].'").show();
            },
            success: function(response) {
				 if (response.success == "true") {
            $("#merge_body").show();
                    $("#merge-succ-alert'. $pres['id'].'").hide();
                    $("#merge-body-alert'. $pres['id'].'").show();
                    $("#merge_loader'. $pres['id'].'").hide();
                    var message = "Error while saving";
                    $("#merge-err-alert'. $pres['id'].'").show();
                    $("#message-merge-err'. $pres['id'].'").html(message);
					 setInterval(function(){$("#merge-err-alert'. $pres['id'].'").hide(); },8000); 
					 window.location.reload();
            }
			 else {
            $("#merge_body").show();
                    $("#merge-err-alert'. $pres['id'].'").hide();
                    $("#merge-body-alert'. $pres['id'].'").show();
                    $("#merge-body-form'. $pres['id'].'").hide();
                    $("#merge_loader'. $pres['id'].'").hide();
                    var message = "'. $pres['drug'].' has been updated";
                    $("#merge-succ-alert'. $pres['id'].'").show();
                    $("#message-merge-succ'. $pres['id'].'").html(message);
					 setInterval(function(){$("#merge-succ-alert'. $pres['id'].'").hide(); $(".close").trigger("click"); },2000);
					 window.location.reload();
            }
				console.log(response);
            }
    })
            return false;
    });
	});</script>'; } 
 $HTML .='</td>
 </tr>';
   }
  if($app_date != null) {
	  
  $HTML .='<tr>
    <th>Next Appointment</th>
	<td>'. $app_date .'</td>
		</tr>';
		  }
		$HTML.='</table></h3>';
		$HTML.='<div class="timeline-body">
		<b style="font-size: 16px;">Notes:</b>
                                            <p style="word-wrap: break-word;font-family: sans-serif;font-size: 20px;">'. $reason .'</p>
                                    </div>
                                                                                                                                                                    <div class="box-body col-md-9">
                                                    <br>
                                                        <table class="table table-bordered">
                                                        <tbody>
                                                                                                                </tbody></table>
                                                    </div>
                                                                                                                <br><br>
                                    <div class="timeline-footer" style="margin-bottom:-5px">
                                                                                                                                                                                            <ul class="mailbox-attachments clearfix">
                                                                                </ul>
                                    </div>
                                </div>
                            </li><li>
                            <i class="fa fa-clock-o bg-gray"></i>
                        </li>';
}
}else{
    $HTML='No Data Found';
}
echo $HTML;
?>