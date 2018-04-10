<?php
/**
 *
 * Patient summary screen.
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Brady Miller <brady@sparmy.com>
 * @link    http://www.open-emr.org
 */

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//

 require_once("../../globals.php");
 require_once("$srcdir/patient.inc");
 require_once("$srcdir/acl.inc");
 require_once("$srcdir/classes/Address.class.php");
 require_once("$srcdir/classes/InsuranceCompany.class.php");
 require_once("$srcdir/classes/Document.class.php");
 require_once("$srcdir/options.inc.php");
 require_once("../history/history.inc.php");
 require_once("$srcdir/formatting.inc.php");
 require_once("$srcdir/edi.inc");
 require_once("$srcdir/invoice_summary.inc.php");
 require_once("$srcdir/clinical_rules.php");

  if ($GLOBALS['concurrent_layout'] && isset($_GET['set_pid'])) {
  include_once("$srcdir/pid.inc");
  setpid($_GET['set_pid']);
 }

  $active_reminders = false;
  if ((!isset($_SESSION['alert_notify_pid']) || ($_SESSION['alert_notify_pid'] != $pid)) && isset($_GET['set_pid']) && acl_check('patients', 'med') && $GLOBALS['enable_cdr'] && $GLOBALS['enable_cdr_crp']) {
    // showing a new patient, so check for active reminders
    $active_reminders = active_alert_summary($pid,"reminders-due");
  }

function print_as_money($money) {
	preg_match("/(\d*)\.?(\d*)/",$money,$moneymatches);
	$tmp = wordwrap(strrev($moneymatches[1]),3,",",1);
	$ccheck = strrev($tmp);
	if ($ccheck[0] == ",") {
		$tmp = substr($ccheck,1,strlen($ccheck)-1);
	}
	if ($moneymatches[2] != "") {
		return "$ " . strrev($tmp) . "." . $moneymatches[2];
	} else {
		return "$ " . strrev($tmp);
	}
}

// get an array from Photos category
function pic_array($pid,$picture_directory) {
    $pics = array();
    $sql_query = "select documents.id from documents join categories_to_documents " .
                 "on documents.id = categories_to_documents.document_id " .
                 "join categories on categories.id = categories_to_documents.category_id " .
                 "where categories.name like ? and documents.foreign_id = ?";
    if ($query = sqlStatement($sql_query, array($picture_directory,$pid))) {
      while( $results = sqlFetchArray($query) ) {
            array_push($pics,$results['id']);
        }
      }
    return ($pics);
}
// Get the document ID of the first document in a specific catg.
function get_document_by_catg($pid,$doc_catg) {

    $result = array();

	if ($pid and $doc_catg) {
	  $result = sqlQuery("SELECT d.id, d.date, d.url FROM " .
	    "documents AS d, categories_to_documents AS cd, categories AS c " .
	    "WHERE d.foreign_id = ? " .
	    "AND cd.document_id = d.id " .
	    "AND c.id = cd.category_id " .
	    "AND c.name LIKE ? " .
	    "ORDER BY d.date DESC LIMIT 1", array($pid, $doc_catg) );
	    }

	return($result['id']);
}

// Display image in 'widget style'
function image_widget($doc_id,$doc_catg)
{
        global $pid, $web_root;
        $docobj = new Document($doc_id);
        $image_file = $docobj->get_url_file();
        $image_width = $GLOBALS['generate_doc_thumb'] == 1 ? '' : 'width=100';
        $extension = substr($image_file, strrpos($image_file,"."));
        $viewable_types = array('.png','.jpg','.jpeg','.png','.bmp','.PNG','.JPG','.JPEG','.PNG','.BMP'); // image ext supported by fancybox viewer
        if ( in_array($extension,$viewable_types) ) { // extention matches list
                $to_url = "<td> <a href = $web_root" .
				"/controller.php?document&retrieve&patient_id=$pid&document_id=$doc_id&as_file=false&original_file=true&disable_exit=false&show_original=true" .
				"/tmp$extension" .  // Force image type URL for fancybo
				" onclick=top.restoreSession(); class='image_modal'>" .
                " <img src = $web_root" .
				"/controller.php?document&retrieve&patient_id=$pid&document_id=$doc_id&as_file=false" .
				" $image_width alt='$doc_catg:$image_file'>  </a> </td> <td valign='center'>".
                htmlspecialchars($doc_catg) . '<br />&nbsp;' . htmlspecialchars($image_file) .
				"</td>";
        }
     	else {
				$to_url = "<td> <a href='" . $web_root . "/controller.php?document&retrieve" .
                    "&patient_id=$pid&document_id=$doc_id'" .
                    " onclick='top.restoreSession()' class='css_button_small'>" .
                    "<span>" .
                    htmlspecialchars( xl("View"), ENT_QUOTES )."</a> &nbsp;" .
					htmlspecialchars( "$doc_catg - $image_file", ENT_QUOTES ) .
                    "</span> </td>";
		}
        echo "<table><tr>";
        echo $to_url;
        echo "</tr></table>";
}

// Determine if the Vitals form is in use for this site.
$tmp = sqlQuery("SELECT count(*) AS count FROM registry WHERE " .
  "directory = 'vitals' AND state = 1");
$vitals_is_registered = $tmp['count'];

// Get patient/employer/insurance information.
//
$result  = getPatientData($pid, "*, DATE_FORMAT(DOB,'%Y-%m-%d') as DOB_YMD");
$result2 = getEmployerData($pid);
$result3 = getInsuranceData($pid, "primary", "copay, provider, DATE_FORMAT(`date`,'%Y-%m-%d') as effdate");
$insco_name = "";
if ($result3['provider']) {   // Use provider in case there is an ins record w/ unassigned insco
  $insco_name = getInsuranceProvider($result3['provider']);
}
?>
<!DOCTYPE html>
<html>

<head>
<meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">

  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Bootstrap 3.3.6 -->
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<link rel="stylesheet" type="text/css" href="../../../library/js/fancybox/jquery.fancybox-1.2.6.css" media="screen" />
  <link rel="stylesheet" href="../../../dist/css/AdminLTE.min.css">
<style type="text/css">@import url(../../../library/dynarch_calendar.css);</style>
<script type="text/javascript" src="../../../library/textformat.js"></script>
<script type="text/javascript" src="../../../library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="../../../library/dynarch_calendar_setup.js"></script>
<script type="text/javascript" src="../../../library/dialog.js"></script>
<script type="text/javascript" src="../../../library/js/jquery-1.6.4.min.js"></script>
<script type="text/javascript" src="../../../library/js/common.js"></script>
<script type="text/javascript" src="../../../library/js/fancybox/jquery.fancybox-1.2.6.js"></script>
<script type="text/javascript" language="JavaScript">

 var mypcc = '<?php echo htmlspecialchars($GLOBALS['phone_country_code'],ENT_QUOTES); ?>';

 function oldEvt(eventid) {
  dlgopen('../../main/calendar/add_edit_event.php?eid=' + eventid, '_blank', 550, 350);
 }

 function advdirconfigure() {
   dlgopen('advancedirectives.php', '_blank', 500, 450);
  }
function printid(strid)
{
	
	//window.print();
  /* //var divstyle = document.getElementById('hideonprint').style;
  //divstyle.display = 'none';
  //
	
	//function CallPrint(strid) { */
            var prtContent = document.getElementById('printb');
            var WinPrint = window.open('', '', 'letf=0,top=0,width=400,height=800,toolbar=0,scrollbars=0,status=0');
            WinPrint.document.write(prtContent.innerHTML);
            WinPrint.document.close();
            WinPrint.focus();
            WinPrint.print();
           WinPrint.close();
         
}
 function refreshme() {
  top.restoreSession();
  location.reload();
 }

 // Process click on Delete link.
 function deleteme() {
  dlgopen('../deleter.php?patient=<?php echo htmlspecialchars($pid,ENT_QUOTES); ?>', '_blank', 500, 450);
  return false;
 }

 // Called by the deleteme.php window on a successful delete.
 function imdeleted() {
<?php if ($GLOBALS['concurrent_layout']) { ?>
  parent.left_nav.clearPatient();
<?php } else { ?>
  top.restoreSession();
  top.location.href = '../main/main_screen.php';
<?php } ?>
 }

 function validate() {
  var f = document.forms[0];
<?php
if ($GLOBALS['athletic_team']) {
  echo "  if (f.form_userdate1.value != f.form_original_userdate1.value) {\n";
  $irow = sqlQuery("SELECT id, title FROM lists WHERE " .
    "pid = ? AND enddate IS NULL ORDER BY begdate DESC LIMIT 1", array($pid));
  if (!empty($irow)) {
?>
   if (confirm('Do you wish to also set this new return date in the issue titled "<?php echo htmlspecialchars($irow['title'],ENT_QUOTES); ?>"?')) {
    f.form_issue_id.value = '<?php echo htmlspecialchars($irow['id'],ENT_QUOTES); ?>';
   } else {
    alert('OK, you will need to manually update the return date in any affected issue(s).');
   }
<?php } else { ?>
   alert('You have changed the return date but there are no open issues. You probably need to create or modify one.');
<?php
  } // end empty $irow
  echo "  }\n";
} // end athletic team
?>
  return true;
 }

 function newEvt() {
  dlgopen('../../main/calendar/add_edit_event.php?patientid=<?php echo htmlspecialchars($pid,ENT_QUOTES); ?>', '_blank', 550, 350);
  return false;
 }

function sendimage(pid, what) {
 // alert('Not yet implemented.'); return false;
 dlgopen('../upload_dialog.php?patientid=' + pid + '&file=' + what,
  '_blank', 500, 400);
 return false;
}

</script>

<script type="text/javascript">

function toggleIndicator(target,div) {

    $mode = $(target).find(".indicator").text();
    if ( $mode == "<?php echo htmlspecialchars(xl('collapse'),ENT_QUOTES); ?>" ) {
        $(target).find(".indicator").text( "<?php echo htmlspecialchars(xl('expand'),ENT_QUOTES); ?>" );
        $("#"+div).hide();
	$.post( "../../../library/ajax/user_settings.php", { target: div, mode: 0 });
    } else {
        $(target).find(".indicator").text( "<?php echo htmlspecialchars(xl('collapse'),ENT_QUOTES); ?>" );
        $("#"+div).show();
	$.post( "../../../library/ajax/user_settings.php", { target: div, mode: 1 });
    }
}

$(document).ready(function(){
  var msg_updation='';
	<?php
	if($GLOBALS['erx_enable']){
		//$soap_status=sqlQuery("select soap_import_status from patient_data where pid=?",array($pid));
		$soap_status=sqlStatement("select soap_import_status,pid from patient_data where pid=? and soap_import_status in ('1','3')",array($pid));
		while($row_soapstatus=sqlFetchArray($soap_status)){
			//if($soap_status['soap_import_status']=='1' || $soap_status['soap_import_status']=='3'){ ?>
			top.restoreSession();
			$.ajax({
				type: "POST",
				url: "../../soap_functions/soap_patientfullmedication.php",
				dataType: "html",
				data: {
					patient:<?php echo $row_soapstatus['pid']; ?>,
				},
				async: false,
				success: function(thedata){
					//alert(thedata);
					msg_updation+=thedata;
				},
				error:function(){
					alert('ajax error');
				}	
			});
			<?php
			//}	
			//elseif($soap_status['soap_import_status']=='3'){ ?>
			top.restoreSession();
			$.ajax({
				type: "POST",
				url: "../../soap_functions/soap_allergy.php",
				dataType: "html",
				data: {
					patient:<?php echo $row_soapstatus['pid']; ?>,
				},
				async: false,
				success: function(thedata){
					//alert(thedata);
					msg_updation+=thedata;
				},
				error:function(){
					alert('ajax error');
				}	
			});
			<?php
			if($GLOBALS['erx_import_status_message']){ ?>
			if(msg_updation)
			  alert(msg_updation);
			<?php
			}
			//} 
		}
	}
	?>
    // load divs
    $("#stats_div").load("stats.php", { 'embeddedScreen' : true }, function() {
	// (note need to place javascript code here also to get the dynamic link to work)
        $(".rx_modal").fancybox( {
                'overlayOpacity' : 0.0,
                'showCloseButton' : true,
                'frameHeight' : 500,
                'frameWidth' : 800,
        	'centerOnScroll' : false,
        	'callbackOnClose' : function()  {
                refreshme();
        	}
        });
    });
    $("#pnotes_ps_expand").load("pnotes_fragment.php");
    $("#disclosures_ps_expand").load("disc_fragment.php");

    <?php if ($GLOBALS['enable_cdr'] && $GLOBALS['enable_cdr_crw']) { ?>
      top.restoreSession();
      $("#clinical_reminders_ps_expand").load("clinical_reminders_fragment.php", { 'embeddedScreen' : true }, function() {
          // (note need to place javascript code here also to get the dynamic link to work)
          $(".medium_modal").fancybox( {
                  'overlayOpacity' : 0.0,
                  'showCloseButton' : true,
                  'frameHeight' : 500,
                  'frameWidth' : 800,
                  'centerOnScroll' : false,
                  'callbackOnClose' : function()  {
                  refreshme();
                  }
          });
      });
    <?php } // end crw?>

    <?php if ($GLOBALS['enable_cdr'] && $GLOBALS['enable_cdr_prw']) { ?>
      top.restoreSession();
      $("#patient_reminders_ps_expand").load("patient_reminders_fragment.php");
    <?php } // end prw?>

<?php if ($vitals_is_registered && acl_check('patients', 'med')) { ?>
    // Initialize the Vitals form if it is registered and user is authorized.
    $("#vitals_ps_expand").load("vitals_fragment.php");
<?php } ?>

    // Initialize track_anything
    $("#track_anything_ps_expand").load("track_anything_fragment.php");
    
    
    // Initialize labdata
    $("#labdata_ps_expand").load("labdata_fragment.php");
	$("#patientsummary_ps_expand").load("patientsummary_fragment.php");
<?php
  // Initialize for each applicable LBF form.
  $gfres = sqlStatement("SELECT option_id FROM list_options WHERE " .
    "list_id = 'lbfnames' AND option_value > 0 ORDER BY seq, title");
  while($gfrow = sqlFetchArray($gfres)) {
?>
    $("#<?php echo $gfrow['option_id']; ?>_ps_expand").load("lbf_fragment.php?formname=<?php echo $gfrow['option_id']; ?>");
<?php
  }
?>

    // fancy box
    enable_modals();

    tabbify();

// modal for dialog boxes
  $(".large_modal").fancybox( {
    'overlayOpacity' : 0.0,
    'showCloseButton' : true,
    'frameHeight' : 600,
    'frameWidth' : 1000,
    'centerOnScroll' : false
  });

// modal for image viewer
  $(".image_modal").fancybox( {
    'overlayOpacity' : 0.0,
    'showCloseButton' : true,
    'centerOnScroll' : false,
    'autoscale' : true
  });
  
  $(".iframe1").fancybox( {
  'left':10,
	'overlayOpacity' : 0.0,
	'showCloseButton' : true,
	'frameHeight' : 300,
	'frameWidth' : 350
  });
// special size for patient portal
  $(".small_modal").fancybox( {
	'overlayOpacity' : 0.0,
	'showCloseButton' : true,
	'frameHeight' : 200,
	'frameWidth' : 380,
            'centerOnScroll' : false
  });

  <?php if ($active_reminders) { ?>
    // show the active reminder modal
    $("#reminder_popup_link").fancybox({
      'overlayOpacity' : 0.0,
      'showCloseButton' : true,
      'frameHeight' : 500,
      'frameWidth' : 500,
      'centerOnScroll' : false
    }).trigger('click');
  <?php } ?>

});

// JavaScript stuff to do when a new patient is set.
//
function setMyPatient() {
<?php if ($GLOBALS['concurrent_layout']) { ?>
 // Avoid race conditions with loading of the left_nav or Title frame.
 if (!parent.allFramesLoaded()) {
  setTimeout("setMyPatient()", 500);
  return;
 }
<?php if (isset($_GET['set_pid'])) { ?>
 parent.left_nav.setPatient(<?php echo "'" . htmlspecialchars(($result['fname']) . " " . ($result['lname']),ENT_QUOTES) .
   "'," . htmlspecialchars($pid,ENT_QUOTES) . ",'" . htmlspecialchars(($result['genericname1']),ENT_QUOTES) .
   "','', ' " . htmlspecialchars(xl('DOB') . ": " . oeFormatShortDate($result['DOB_YMD']) . " " . xl('Age') . ": " . getPatientAgeDisplay($result['DOB_YMD']), ENT_QUOTES) . "'"; ?>);
 var EncounterDateArray = new Array;
 var CalendarCategoryArray = new Array;
 var EncounterIdArray = new Array;
 var Count = 0;
<?php
  //Encounter details are stored to javacript as array.
  $result4 = sqlStatement("SELECT fe.encounter,fe.encounter_ipop,fe.date,openemr_postcalendar_categories.pc_catname FROM form_encounter AS fe ".
    " left join openemr_postcalendar_categories on fe.pc_catid=openemr_postcalendar_categories.pc_catid  WHERE fe.pid = ? order by fe.date desc", array($pid));
  if(sqlNumRows($result4)>0) {
    while($rowresult4 = sqlFetchArray($result4)) {
?>
 EncounterIdArray[Count] = '<?php echo htmlspecialchars($rowresult4['encounter'], ENT_QUOTES); ?>';
 EncounterDateArray[Count] = '<?php echo htmlspecialchars(oeFormatShortDate(date("Y-m-d", strtotime($rowresult4['date']))), ENT_QUOTES); ?>';
 CalendarCategoryArray[Count] = '<?php echo htmlspecialchars(xl_appt_category($rowresult4['pc_catname']), ENT_QUOTES); ?>';
 Count++;
<?php
    }
  }
?>
 parent.left_nav.setPatientEncounter(EncounterIdArray,EncounterDateArray,CalendarCategoryArray);
<?php } // end setting new pid ?>
 parent.left_nav.setRadio(window.name, 'dem');
 parent.left_nav.syncRadios();
<?php } // end concurrent layout ?>
}

$(window).load(function() {
 setMyPatient();
});

</script>
<style type="css/text">
.timeline li a i {
    MARGIN-LEFT: 20PX;
    padding: 6px;
    border-radius: 13px;
}
#pnotes_ps_expand {
  height:auto;
  width:100%;
}
#tid {
	
	font-size: 1px;
	
}

#printb{ width:550px; size: 4.5in 3.2in;font-size:5px}
@page{}
</style>

</head>

<body class="body_top">

<a href='../reminder/active_reminder_popup.php' id='reminder_popup_link' style='visibility: false;' class='iframe' onclick='top.restoreSession()'></a>

<?php
 $thisauth = acl_check('patients', 'demo');
 if ($thisauth) {
  if ($result['squad'] && ! acl_check('squads', $result['squad']))
   $thisauth = 0;
 }
 if (!$thisauth) {
  echo "<p>(" . htmlspecialchars(xl('Demographics not authorized'),ENT_NOQUOTES) . ")</p>\n";
  echo "</body>\n</html>\n";
  exit();
 }
 if ($thisauth) {
  echo "<table><tr><td><span class='title'>" .
   htmlspecialchars(getPatientName($pid),ENT_NOQUOTES) .
   "</span></td>";

  if (acl_check('admin', 'super')) {
   echo "<td style='padding-left:1em;'><a class='css_button iframe' href='../deleter.php?patient=" . 
    htmlspecialchars($pid,ENT_QUOTES) . "' onclick='top.restoreSession()'>" .
    "<span>".htmlspecialchars(xl('Delete'),ENT_NOQUOTES).
    "</span></a></td>";
  }
  if($GLOBALS['erx_enable']){
	echo '<td style="padding-left:1em;"><a class="css_button" href="../../eRx.php?page=medentry" onclick="top.restoreSession()">';
	echo "<span>".htmlspecialchars(xl('NewCrop MedEntry'),ENT_NOQUOTES)."</span></a></td>";
	echo '<td style="padding-left:1em;"><a class="css_button iframe1" href="../../soap_functions/soap_accountStatusDetails.php" onclick="top.restoreSession()">';
	echo "<span>".htmlspecialchars(xl('NewCrop Account Status'),ENT_NOQUOTES)."</span></a></td><td id='accountstatus'></td>";
   }
  //Patient Portal
  $portalUserSetting = true; //flag to see if patient has authorized access to portal
  if($GLOBALS['portal_onsite_enable'] && $GLOBALS['portal_onsite_address']){
    $portalStatus = sqlQuery("SELECT allow_patient_portal FROM patient_data WHERE pid=?",array($pid));
    if ($portalStatus['allow_patient_portal']=='YES') {
      $portalLogin = sqlQuery("SELECT pid FROM `patient_access_onsite` WHERE `pid`=?", array($pid));
      echo "<td style='padding-left:1em;'><a class='css_button iframe small_modal' href='create_portallogin.php?portalsite=on&patient=" . htmlspecialchars($pid,ENT_QUOTES) . "' onclick='top.restoreSession()'>";
      if (empty($portalLogin)) {
        echo "<span>".htmlspecialchars(xl('Create Onsite Portal Credentials'),ENT_NOQUOTES)."</span></a></td>";
      }
      else {
        echo "<span>".htmlspecialchars(xl('Reset Onsite Portal Credentials'),ENT_NOQUOTES)."</span></a></td>";
      }
    }
    else {
      $portalUserSetting = false;
    }
  }
  if($GLOBALS['portal_offsite_enable'] && $GLOBALS['portal_offsite_address']){
    $portalStatus = sqlQuery("SELECT allow_patient_portal FROM patient_data WHERE pid=?",array($pid));
    if ($portalStatus['allow_patient_portal']=='YES') {
      $portalLogin = sqlQuery("SELECT pid FROM `patient_access_offsite` WHERE `pid`=?", array($pid));
      echo "<td style='padding-left:1em;'><a class='css_button iframe small_modal' href='create_portallogin.php?portalsite=off&patient=" . htmlspecialchars($pid,ENT_QUOTES) . "' onclick='top.restoreSession()'>";
      if (empty($portalLogin)) {
        echo "<span>".htmlspecialchars(xl('Create Offsite Portal Credentials'),ENT_NOQUOTES)."</span></a></td>";
      }
      else {
        echo "<span>".htmlspecialchars(xl('Reset Offsite Portal Credentials'),ENT_NOQUOTES)."</span></a></td>";
      }
    }
    else {
      $portalUserSetting = false;
    }
  }
  if (!($portalUserSetting)) {
    // Show that the patient has not authorized portal access
    echo "<td style='padding-left:1em;'>" . htmlspecialchars( xl('Patient has not authorized the Patient Portal.'), ENT_NOQUOTES) . "</td>";
  }
  //Patient Portal

  // If patient is deceased, then show this (along with the number of days patient has been deceased for)
  $days_deceased = is_patient_deceased($pid);
  if ($days_deceased) {
    echo "<td style='padding-left:1em;font-weight:bold;color:red'>" . htmlspecialchars( xl('DECEASED') ,ENT_NOQUOTES) . " (" . htmlspecialchars($days_deceased,ENT_NOQUOTES) . " " .  htmlspecialchars( xl('days ago') ,ENT_NOQUOTES) . ")</td>";
  }

  echo "</tr></table>";
 }

// Get the document ID of the patient ID card if access to it is wanted here.
$idcard_doc_id = false;
if ($GLOBALS['patient_id_category_name']) {
  $idcard_doc_id = get_document_by_catg($pid, $GLOBALS['patient_id_category_name']);
}

?>
<div class="container">
<div class="row">
<div class="col-md-8">
<!-- <table cellspacing='0' cellpadding='0' border='0'>
 <tr>
  <td class="small" colspan='4'>
<a href="../history/history.php" onclick='top.restoreSession()'>
<?php echo htmlspecialchars(xl('History'),ENT_NOQUOTES); ?></a>
|
<?php //note that we have temporarily removed report screen from the modal view ?>
<a href="../report/patient_report.php" onclick='top.restoreSession()'>
<?php echo htmlspecialchars(xl('Report'),ENT_NOQUOTES); ?></a>
|
<?php //note that we have temporarily removed report screen from the modal view ?>
--><!--<a href="../reportobs/patient_report_gends.php" onclick='top.restoreSession()'>
<?php echo htmlspecialchars(xl('Discharge Summary'),ENT_NOQUOTES); ?></a>-->
<?php //note that we have temporarily removed document screen from the modal view ?>
<!-- <a href="../../../controller.php?document&list&patient_id=<?php echo $pid;?>" onclick='top.restoreSession()'>
<?php echo htmlspecialchars(xl('Documents'),ENT_NOQUOTES); ?></a>
|
<a href="../transaction/transactions.php" class='iframe large_modal' onclick='top.restoreSession()'>
<?php echo htmlspecialchars(xl('Transactions'),ENT_NOQUOTES); ?></a>
|
<a href="stats_full.php?active=all" onclick='top.restoreSession()'>
<?php echo htmlspecialchars(xl('Issues'),ENT_NOQUOTES); ?></a>
|
<a href="../tcpdf/examples/example_051.php" class='iframe large_modal' onclick='top.restoreSession()'>
<?php echo htmlspecialchars(xl('ID Card'),ENT_NOQUOTES); ?></a>
|
<a href= "" onclick='top.restoreSession()'>
<?php echo htmlspecialchars(xl('Check Out'),ENT_NOQUOTES);
$user =$_SESSION["authUser"];  
$enc=$GLOBALS['encounter'];
$s=sqlStatement("select username,newcrop_user_role from users where username='".$user."'");
$s1=  sqlFetchArray($s);
if($s1["newcrop_user_role"]=="erxnurse")
{
sqlStatement("UPDATE form_encounter SET out_to='Sent To',out_time=NOW() where encounter= '".$enc."'");
}
else{
sqlStatement("UPDATE form_encounter SET out_to='Examined By',out_time=NOW() where encounter= '".$enc."' ");
}
?></a> -->

<!-- DISPLAYING HOOKS STARTS HERE -->
<?php
	$module_query = sqlStatement("SELECT msh.*,ms.menu_name,ms.path,m.mod_ui_name,m.type FROM modules_hooks_settings AS msh
					LEFT OUTER JOIN modules_settings AS ms ON obj_name=enabled_hooks AND ms.mod_id=msh.mod_id
					LEFT OUTER JOIN modules AS m ON m.mod_id=ms.mod_id 
					WHERE fld_type=3 AND mod_active=1 AND sql_run=1 AND attached_to='demographics' ORDER BY mod_id");
	$DivId = 'mod_installer';
	if (sqlNumRows($module_query)) {
		$jid 	= 0;
		$modid 	= '';
		while ($modulerow = sqlFetchArray($module_query)) {
			$DivId 		= 'mod_'.$modulerow['mod_id'];
			$new_category 	= $modulerow['mod_ui_name'];
			$modulePath 	= "";
			$added      	= "";
			if($modulerow['type'] == 0) {
				$modulePath 	= $GLOBALS['customModDir'];
				$added		= "";
			}
			else{ 	
				$added		= "index";
				$modulePath 	= $GLOBALS['zendModDir'];
			}
			$relative_link 	= "../../modules/".$modulePath."/".$modulerow['path'];
			$nickname 	= $modulerow['menu_name'] ? $modulerow['menu_name'] : 'Noname';
			$jid++;
			$modid = $modulerow['mod_id'];			
			?>
			|
			<a href="<?php echo $relative_link; ?>" onclick='top.restoreSession()'>
			<?php echo htmlspecialchars($nickname,ENT_NOQUOTES); ?></a>
		<?php	
		}
	}
	?>
<!-- DISPLAYING HOOKS ENDS HERE -->

 <!--  </td>
 </tr>
 
</table> --><!-- end header -->

<div style='margin-top:10px'> <!-- start main content div -->
 <table border="0" cellspacing="0" cellpadding="0" width="100%">
  <tr>
   <td align="left" valign="top">
    <!-- start left column div -->
    <div style='float:left; margin-right:10px'>
     <table cellspacing=0 cellpadding=0>
      <tr<?php if ($GLOBALS['athletic_team']) echo " style='display:none;'"; ?>>
       <td>
<?php
// Billing expand collapse widget
/*$widgetTitle = xl("Billing");
$widgetLabel = "billing";
$widgetButtonLabel = xl("Edit");
$widgetButtonLink = "return newEvt();";
$widgetButtonClass = "";
$linkMethod = "javascript";
$bodyClass = "notab";
$widgetAuth = false;
$fixedWidth = true;*/
/*if ($GLOBALS['force_billing_widget_open']) {
  $forceExpandAlways = true;
}
else {
  $forceExpandAlways = false;
}
expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel,
  $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass,
  $widgetAuth, $fixedWidth, $forceExpandAlways);*/
?>
        <br>
<?php
		//PATIENT BALANCE,INS BALANCE naina@capminds.com
		$patientbalance = get_patient_balance($pid, false);
		//Debit the patient balance from insurance balance
		$insurancebalance = get_patient_balance($pid, true) - $patientbalance;
	   $totalbalance=$patientbalance + $insurancebalance;
 if ($GLOBALS['oer_config']['ws_accounting']['enabled']) {
 // Show current balance and billing note, if any.
  /*echo "<table border='0'><tr><td>" .
  "<table ><tr><td><span class='bold'><font color='red'>" .
   xlt('Patient Balance Due') .
   " : " . text(oeFormatMoney($patientbalance)) .
   "</font></span></td></tr>".
     "<tr><td><span class='bold'><font color='red'>" .
   xlt('Insurance Balance Due') .
   " : " . text(oeFormatMoney($insurancebalance)) .
   "</font></span></td></tr>".
   "<tr><td><span class='bold'><font color='red'>" .
   xlt('Total Balance Due').
   " : " . text(oeFormatMoney($totalbalance)) .
   "</font></span></td></td></tr>";
  if ($result['genericname2'] == 'Billing') {
   echo "<tr><td><span class='bold'><font color='red'>" .
    xlt('Billing Note') . ":" .
    text($result['genericval2']) .
    "</font></span></td></tr>";
  } 
  if ($result3['provider']) {   // Use provider in case there is an ins record w/ unassigned insco
   echo "<tr><td><span class='bold'>" .
    xlt('Primary Insurance') . ': ' . text($insco_name) .
    "</span>&nbsp;&nbsp;&nbsp;";
   if ($result3['copay'] > 0) {
    echo "<span class='bold'>" .
    xlt('Copay') . ': ' .  text($result3['copay']) .
     "</span>&nbsp;&nbsp;&nbsp;";
   }
   echo "<span class='bold'>" .
    xlt('Effective Date') . ': ' .  text(oeFormatShortDate($result3['effdate'])) .
    "</span></td></tr>";
  }
  echo "</table></td></tr></td></tr></table><br>";*/
 }
?>
        </div> <!-- required for expand_collapse_widget -->
       </td>
      </tr>
      <tr>
       <td>
<?php
// Demographics expand collapse widget
$widgetTitle = xl("Demographics");
$widgetLabel = "demographics";
$widgetButtonLabel = xl("Edit");
$widgetButtonLink = "demographics_full.php";
$widgetButtonClass = "";
$linkMethod = "html";
$bodyClass = "";
$widgetAuth = acl_check('patients', 'demo', '', 'write');
$fixedWidth = true;
expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel,
  $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass,
  $widgetAuth, $fixedWidth);
?>
         <div id="DEM" >
          <ul class="tabNav">
           <?php display_layout_tabs('DEM', $result, $result2); ?>
          </ul>
          <div class="tabContainer">
           <?php display_layout_tabs_data('DEM', $result, $result2); ?>
          </div>
         </div>
        </div> <!-- required for expand_collapse_widget -->
       </td>
      </tr>

      <tr>
       <td>
<?php
$insurance_count = 0;
foreach (array('primary','secondary','tertiary') as $instype) {
  $enddate = 'Present';
  $query = "SELECT * FROM insurance_data WHERE " .
    "pid = ? AND type = ? " .
    "ORDER BY date DESC";
  $res = sqlStatement($query, array($pid, $instype) );
  while( $row = sqlFetchArray($res) ) {
    if ($row['provider'] ) $insurance_count++;
  }
}

if ( $insurance_count > 0 ) {
  // Insurance expand collapse widget
  $widgetTitle = xl("Insurance");
  $widgetLabel = "insurance";
  $widgetButtonLabel = xl("Edit");
  $widgetButtonLink = "demographics_full.php";
  $widgetButtonClass = "";
  $linkMethod = "html";
  $bodyClass = "";
  $widgetAuth = acl_check('patients', 'demo', '', 'write');
  $fixedWidth = true;
  expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel,
    $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass,
    $widgetAuth, $fixedWidth);

  if ( $insurance_count > 0 ) {
?>

        <ul class="tabNav"><?php
					///////////////////////////////// INSURANCE SECTION
					$first = true;
					foreach (array('primary','secondary','tertiary') as $instype) {

						$query = "SELECT * FROM insurance_data WHERE " .
						"pid = ? AND type = ? " .
						"ORDER BY date DESC";
						$res = sqlStatement($query, array($pid, $instype) );

						$enddate = 'Present';

						  while( $row = sqlFetchArray($res) ) {
							if ($row['provider'] ) {

								$ins_description  = ucfirst($instype);
	                                                        $ins_description = xl($ins_description);
								$ins_description  .= strcmp($enddate, 'Present') != 0 ? " (".xl('Old').")" : "";
								?>
								<li <?php echo $first ? 'class="current"' : '' ?>><a href="/play/javascript-tabbed-navigation/">
								<?php echo htmlspecialchars($ins_description,ENT_NOQUOTES); ?></a></li>
								<?php
								$first = false;
							}
							$enddate = $row['date'];
						}
					}
					// Display the eligibility tab
					echo "<li><a href='/play/javascript-tabbed-navigation/'>" .
						htmlspecialchars( xl('Eligibility'), ENT_NOQUOTES) . "</a></li>";

					?></ul><?php

				} ?>

				<div class="tabContainer">
					<?php
					$first = true;
					foreach (array('primary','secondary','tertiary') as $instype) {
					  $enddate = 'Present';

						$query = "SELECT * FROM insurance_data WHERE " .
						"pid = ? AND type = ? " .
						"ORDER BY date DESC";
						$res = sqlStatement($query, array($pid, $instype) );
					  while( $row = sqlFetchArray($res) ) {
						if ($row['provider'] ) {
							?>
								<div class="tab <?php echo $first ? 'current' : '' ?>">
								<table border='0' cellpadding='0' width='100%'>
								<?php
								$icobj = new InsuranceCompany($row['provider']);
								$adobj = $icobj->get_address();
								$insco_name = trim($icobj->get_name());
								?>
								<tr>
								 <td valign='top' colspan='3'>
								  <span class='text'>
								  <?php if (strcmp($enddate, 'Present') != 0) echo htmlspecialchars(xl("Old"),ENT_NOQUOTES)." "; ?>
								  <?php $tempinstype=ucfirst($instype); echo htmlspecialchars(xl($tempinstype.' Insurance'),ENT_NOQUOTES); ?>
								  <?php if (strcmp($row['date'], '0000-00-00') != 0) { ?>
								  <?php echo htmlspecialchars(xl('from','',' ',' ').$row['date'],ENT_NOQUOTES); ?>
								  <?php } ?>
						                  <?php echo htmlspecialchars(xl('until','',' ',' '),ENT_NOQUOTES);
								    echo (strcmp($enddate, 'Present') != 0) ? $enddate : htmlspecialchars(xl('Present'),ENT_NOQUOTES); ?>:</span>
								 </td>
								</tr>
								<tr>
								 <td valign='top'>
								  <span class='text'>
								  <?php
								  if ($insco_name) {
									echo htmlspecialchars($insco_name,ENT_NOQUOTES) . '<br>';
									if (trim($adobj->get_line1())) {
									  echo htmlspecialchars($adobj->get_line1(),ENT_NOQUOTES) . '<br>';
									  echo htmlspecialchars($adobj->get_city() . ', ' . $adobj->get_state() . ' ' . $adobj->get_zip(),ENT_NOQUOTES);
									}
								  } else {
									echo "<font color='red'><b>".htmlspecialchars(xl('Unassigned'),ENT_NOQUOTES)."</b></font>";
								  }
								  ?>
								  <br>
								  <?php echo htmlspecialchars(xl('Policy Number'),ENT_NOQUOTES); ?>: 
								  <?php echo htmlspecialchars($row['policy_number'],ENT_NOQUOTES) ?><br>
								  <?php echo htmlspecialchars(xl('Plan Name'),ENT_NOQUOTES); ?>: 
								  <?php echo htmlspecialchars($row['plan_name'],ENT_NOQUOTES); ?><br>
								  <?php echo htmlspecialchars(xl('Group Number'),ENT_NOQUOTES); ?>: 
								  <?php echo htmlspecialchars($row['group_number'],ENT_NOQUOTES); ?></span>
								 </td>
								 <td valign='top'>
								  <span class='bold'><?php echo htmlspecialchars(xl('Subscriber'),ENT_NOQUOTES); ?>: </span><br>
								  <span class='text'><?php echo htmlspecialchars($row['subscriber_fname'] . ' ' . $row['subscriber_mname'] . ' ' . $row['subscriber_lname'],ENT_NOQUOTES); ?>
							<?php
								  if ($row['subscriber_relationship'] != "") {
									echo "(" . htmlspecialchars($row['subscriber_relationship'],ENT_NOQUOTES) . ")";
								  }
							?>
								  <br>
								  <?php echo htmlspecialchars(xl('S.S.'),ENT_NOQUOTES); ?>: 
								  <?php echo htmlspecialchars($row['subscriber_ss'],ENT_NOQUOTES); ?><br>
								  <?php echo htmlspecialchars(xl('D.O.B.'),ENT_NOQUOTES); ?>:
								  <?php if ($row['subscriber_DOB'] != "0000-00-00 00:00:00") echo htmlspecialchars($row['subscriber_DOB'],ENT_NOQUOTES); ?><br>
								  <?php echo htmlspecialchars(xl('Phone'),ENT_NOQUOTES); ?>: 
								  <?php echo htmlspecialchars($row['subscriber_phone'],ENT_NOQUOTES); ?>
								  </span>
								 </td>
								 <td valign='top'>
								  <span class='bold'><?php echo htmlspecialchars(xl('Subscriber Address'),ENT_NOQUOTES); ?>: </span><br>
								  <span class='text'><?php echo htmlspecialchars($row['subscriber_street'],ENT_NOQUOTES); ?><br>
								  <?php echo htmlspecialchars($row['subscriber_city'],ENT_NOQUOTES); ?>
								  <?php if($row['subscriber_state'] != "") echo ", "; echo htmlspecialchars($row['subscriber_state'],ENT_NOQUOTES); ?>
								  <?php if($row['subscriber_country'] != "") echo ", "; echo htmlspecialchars($row['subscriber_country'],ENT_NOQUOTES); ?>
								  <?php echo " " . htmlspecialchars($row['subscriber_postal_code'],ENT_NOQUOTES); ?></span>

							<?php if (trim($row['subscriber_employer'])) { ?>
								  <br><span class='bold'><?php echo htmlspecialchars(xl('Subscriber Employer'),ENT_NOQUOTES); ?>: </span><br>
								  <span class='text'><?php echo htmlspecialchars($row['subscriber_employer'],ENT_NOQUOTES); ?><br>
								  <?php echo htmlspecialchars($row['subscriber_employer_street'],ENT_NOQUOTES); ?><br>
								  <?php echo htmlspecialchars($row['subscriber_employer_city'],ENT_NOQUOTES); ?>
								  <?php if($row['subscriber_employer_city'] != "") echo ", "; echo htmlspecialchars($row['subscriber_employer_state'],ENT_NOQUOTES); ?>
								  <?php if($row['subscriber_employer_country'] != "") echo ", "; echo htmlspecialchars($row['subscriber_employer_country'],ENT_NOQUOTES); ?>
								  <?php echo " " . htmlspecialchars($row['subscriber_employer_postal_code'],ENT_NOQUOTES); ?>
								  </span>
							<?php } ?>

								 </td>
								</tr>
								<tr>
								 <td>
							<?php if ($row['copay'] != "") { ?>
								  <span class='bold'><?php echo htmlspecialchars(xl('CoPay'),ENT_NOQUOTES); ?>: </span>
								  <span class='text'><?php echo htmlspecialchars($row['copay'],ENT_NOQUOTES); ?></span>
                  <br />
							<?php } ?>
								  <span class='bold'><?php echo htmlspecialchars(xl('Accept Assignment'),ENT_NOQUOTES); ?>:</span>
								  <span class='text'><?php if($row['accept_assignment'] == "TRUE") echo xl("YES"); ?>
								  <?php if($row['accept_assignment'] == "FALSE") echo xl("NO"); ?></span>
							<?php if (!empty($row['policy_type'])) { ?>
                  <br />
								  <span class='bold'><?php echo htmlspecialchars(xl('Secondary Medicare Type'),ENT_NOQUOTES); ?>: </span>
								  <span class='text'><?php echo htmlspecialchars($policy_types[$row['policy_type']],ENT_NOQUOTES); ?></span>
							<?php } ?>
								 </td>
								 <td valign='top'></td>
								 <td valign='top'></td>
							   </tr>

							</table>
							</div>
							<?php

						} // end if ($row['provider'])
						$enddate = $row['date'];
						$first = false;
					  } // end while
					} // end foreach

					// Display the eligibility information
					echo "<div class='tab'>";
					show_eligibility_information($pid,true);
					echo "</div>";

			///////////////////////////////// END INSURANCE SECTION
			?>
			</div>

			<?php } // ?>

			</td>
		</tr>

	<!--	<tr>
			<td width='650px'>

<?php
// Notes expand collapse widget
$widgetTitle = xl("Notes");
$widgetLabel = "pnotes";
$widgetButtonLabel = xl("Edit");
$widgetButtonLink = "pnotes_full.php?form_active=1";
$widgetButtonClass = "";
$linkMethod = "html";
$bodyClass = "notab";
$widgetAuth = true;
$fixedWidth = true;
expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel,
  $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass,
  $widgetAuth, $fixedWidth);
?>

                    <br/>
                    <div style='margin-left:10px' class='text'><img src='../../pic/ajax-loader.gif'/></div><br/>
                </div>
			</td>
		</tr>
                <?php if ( (acl_check('patients', 'med')) && ($GLOBALS['enable_cdr'] && $GLOBALS['enable_cdr_prw']) ) {
                echo "<tr><td width='650px'>";
                // patient reminders collapse widget
                $widgetTitle = xl("Patient Reminders");
                $widgetLabel = "patient_reminders";
                $widgetButtonLabel = xl("Edit");
                $widgetButtonLink = "../reminder/patient_reminders.php?mode=simple&patient_id=".$pid;
                $widgetButtonClass = "";
                $linkMethod = "html";
                $bodyClass = "notab";
                $widgetAuth = true;
                $fixedWidth = true;
                expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel , $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass, $widgetAuth, $fixedWidth); ?>
                    <br/>
                    <div style='margin-left:10px' class='text'><image src='../../pic/ajax-loader.gif'/></div><br/>
                </div>
                        </td>
                </tr>
                <?php } //end if prw is activated  ?>
              
       <tr>
       <td width='650px'>
<?php
// disclosures expand collapse widget
$widgetTitle = xl("Disclosures");
$widgetLabel = "disclosures";
$widgetButtonLabel = xl("Edit");
$widgetButtonLink = "disclosure_full.php";
$widgetButtonClass = "";
$linkMethod = "html";
$bodyClass = "notab";
$widgetAuth = true;
$fixedWidth = true;
expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel,
  $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass,
  $widgetAuth, $fixedWidth);
?>
                    <br/>
                    <div style='margin-left:10px' class='text'><img src='../../pic/ajax-loader.gif'/></div><br/>
                </div>
     </td>
    </tr>		
<?php if ($GLOBALS['amendments']) { ?>
  <tr>
       <td width='650px'>
       	<?php // Amendments widget
       	$widgetTitle = xlt('Amendments');
    $widgetLabel = "amendments";
    $widgetButtonLabel = xlt("Edit");
	$widgetButtonLink = $GLOBALS['webroot'] . "/interface/patient_file/summary/main_frameset.php?feature=amendment";
	$widgetButtonClass = "iframe rx_modal";
    $linkMethod = "html";
    $bodyClass = "summary_item small";
    $widgetAuth = true;
    $fixedWidth = false;
    expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel , $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass, $widgetAuth, $fixedWidth);
       	$sql = "SELECT * FROM amendments WHERE pid = ? ORDER BY amendment_date DESC";
  $result = sqlStatement($sql, array($pid) );

  if (sqlNumRows($result) == 0) {
    echo " <table><tr>\n";
    echo "  <td colspan='$numcols' class='text'>&nbsp;&nbsp;" . xlt('None') . "</td>\n";
    echo " </tr></table>\n";
  }
  
  while ($row=sqlFetchArray($result)){
    echo "&nbsp;&nbsp;";
    echo "<a class= '" . $widgetButtonClass . "' href='" . $widgetButtonLink . "&id=" . attr($row['amendment_id']) . "' onclick='top.restoreSession()'>" . text($row['amendment_date']);
	echo "&nbsp; " . text($row['amendment_desc']);

    echo "</a><br>\n";
  } ?>
  </td>
    </tr>
<?php } ?>    		
 <?php // labdata ?>
    <tr>
     <td width='650px'>
<?php // labdata expand collapse widget
  $widgetTitle = xl("Labs");
  $widgetLabel = "labdata";
  $widgetButtonLabel = xl("Trend");
  $widgetButtonLink = "../summary/labdata.php";#"../encounter/trend_form.php?formname=labdata";
  $widgetButtonClass = "";
  $linkMethod = "html";
  $bodyClass = "notab";
  // check to see if any labdata exist
  $spruch = "SELECT procedure_report.date_collected AS date " .
			"FROM procedure_report " . 
			"JOIN procedure_order ON  procedure_report.procedure_order_id = procedure_order.procedure_order_id " . 
			"WHERE procedure_order.patient_id = ? " . 
			"ORDER BY procedure_report.date_collected DESC ";
  $existLabdata = sqlQuery($spruch, array($pid) );	
  if ($existLabdata) {
    $widgetAuth = true;
  }
  else {
    $widgetAuth = false;
  }
  $fixedWidth = true;
  expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel,
    $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass,
    $widgetAuth, $fixedWidth);
?>
      <br/>
      <div style='margin-left:10px' class='text'><img src='../../pic/ajax-loader.gif'/></div><br/>
      </div>
     </td>
    </tr>
<?php  // end labdata ?>

<?php // patient summary ?>
    <tr>
     <td width='650px'>
<?php // labdata expand collapse widget
  $widgetTitle = xl("Bed Summary");
  $widgetLabel = "patientsummary";
  $widgetButtonLink = "../summary/patientsummary.php";
  $widgetButtonClass = "";
  $linkMethod = "html";
  $bodyClass = "notab";
  // check to see if any labdata exist
  $spruch = "SELECT procedure_report.date_collected AS date " .
			"FROM procedure_report " . 
			"JOIN procedure_order ON  procedure_report.procedure_order_id = procedure_order.procedure_order_id " . 
			"WHERE procedure_order.patient_id = ? " . 
			"ORDER BY procedure_report.date_collected DESC ";
  $existLabdata = sqlQuery($spruch, array($pid) );	
  if ($existpatientdata) {
    $widgetAuth = true;
  }
  else {
    $widgetAuth = false;
  }
  $fixedWidth = true;
  expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel,
    $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass,
    $widgetAuth, $fixedWidth);
?>
      <br/>
      <div style='margin-left:10px' class='text'><img src='../../pic/ajax-loader.gif'/></div><br/>
      </div>
     </td>
    </tr>
<?php  // end patient summary ?>




<?php if ($vitals_is_registered && acl_check('patients', 'med')) { ?>
    <tr>
     <td width='650px'>
<?php // vitals expand collapse widget
  $widgetTitle = xl("Vitals");
  $widgetLabel = "vitals";
  $widgetButtonLabel = xl("Trend");
  $widgetButtonLink = "../encounter/trend_form.php?formname=vitals";
  $widgetButtonClass = "";
  $linkMethod = "html";
  $bodyClass = "notab";
  // check to see if any vitals exist
  $existVitals = sqlQuery("SELECT * FROM form_vitals WHERE pid=?", array($pid) );
  if ($existVitals) {
    $widgetAuth = true;
  }
  else {
    $widgetAuth = false;
  }
  $fixedWidth = true;
  expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel,
    $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass,
    $widgetAuth, $fixedWidth);
?>
      <br/>
      <div style='margin-left:10px' class='text'><img src='../../pic/ajax-loader.gif'/></div><br/>
      </div>
     </td>
    </tr>
<?php } // end if ($vitals_is_registered && acl_check('patients', 'med')) ?>

<?php
  // This generates a section similar to Vitals for each LBF form that
  // supports charting.  The form ID is used as the "widget label".
  //
  $gfres = sqlStatement("SELECT option_id, title FROM list_options WHERE " .
    "list_id = 'lbfnames' AND option_value > 0 ORDER BY seq, title");
  while($gfrow = sqlFetchArray($gfres)) {
?>
    <tr>
     <td width='650px'>
<?php // vitals expand collapse widget
    $vitals_form_id = $gfrow['option_id'];
    $widgetTitle = $gfrow['title'];
    $widgetLabel = $vitals_form_id;
    $widgetButtonLabel = xl("Trend");
    $widgetButtonLink = "../encounter/trend_form.php?formname=$vitals_form_id";
    $widgetButtonClass = "";
    $linkMethod = "html";
    $bodyClass = "notab";
    // check to see if any instances exist for this patient
    $existVitals = sqlQuery(
      "SELECT * FROM forms WHERE pid = ? AND formdir = ? AND deleted = 0",
      array($pid, $vitals_form_id));
    $widgetAuth = $existVitals ? true : false;
    $fixedWidth = true;
    expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel,
      $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass,
      $widgetAuth, $fixedWidth);
?>
       <br/>
       <div style='margin-left:10px' class='text'>
        <image src='../../pic/ajax-loader.gif'/>
       </div>
       <br/>
      </div> 
     </td>
    </tr>
<?php
  } // end while
?>

   </table>

  </div>

	<div>
    <table>
    <tr>
    <td>

<div>
    <?php

    // If there is an ID Card or any Photos show the widget
    $photos = pic_array($pid, $GLOBALS['patient_photo_category_name']);
    if ($photos or $idcard_doc_id )
    {
        $widgetTitle = xl("ID Card") . '/' . xl("Photos");
        $widgetLabel = "photos";
        $linkMethod = "javascript";
        $bodyClass = "notab-right";
        $widgetAuth = false;
        $fixedWidth = false;
        expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel ,
                $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass,
                $widgetAuth, $fixedWidth);
?>
<br />
<?php
    	if ($idcard_doc_id) {
        	image_widget($idcard_doc_id, $GLOBALS['patient_id_category_name']);
		}

        foreach ($photos as $photo_doc_id) {
            image_widget($photo_doc_id, $GLOBALS['patient_photo_category_name']);
        }
    }
?>

<br />
</div>
<div>
 <?php
    // Advance Directives
    if ($GLOBALS['advance_directives_warning']) {
	// advance directives expand collapse widget
	$widgetTitle = xl("Advance Directives");
	$widgetLabel = "directives";
	$widgetButtonLabel = xl("Edit");
	$widgetButtonLink = "return advdirconfigure();";
	$widgetButtonClass = "";
	$linkMethod = "javascript";
	$bodyClass = "summary_item small";
	$widgetAuth = true;
	$fixedWidth = false;
	expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel , $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass, $widgetAuth, $fixedWidth);
          $counterFlag = false; //flag to record whether any categories contain ad records
          $query = "SELECT id FROM categories WHERE name='Advance Directive'";
          $myrow2 = sqlQuery($query);
          if ($myrow2) {
          $parentId = $myrow2['id'];
          $query = "SELECT id, name FROM categories WHERE parent=?";
          $resNew1 = sqlStatement($query, array($parentId) );
          while ($myrows3 = sqlFetchArray($resNew1)) {
              $categoryId = $myrows3['id'];
              $nameDoc = $myrows3['name'];
              $query = "SELECT documents.date, documents.id " .
                   "FROM documents " .
                   "INNER JOIN categories_to_documents " .
                   "ON categories_to_documents.document_id=documents.id " .
                   "WHERE categories_to_documents.category_id=? " .
                   "AND documents.foreign_id=? " .
                   "ORDER BY documents.date DESC";
              $resNew2 = sqlStatement($query, array($categoryId, $pid) );
              $limitCounter = 0; // limit to one entry per category
              while (($myrows4 = sqlFetchArray($resNew2)) && ($limitCounter == 0)) {
                  $dateTimeDoc = $myrows4['date'];
              // remove time from datetime stamp
              $tempParse = explode(" ",$dateTimeDoc);
              $dateDoc = $tempParse[0];
              $idDoc = $myrows4['id'];
              echo "<a href='$web_root/controller.php?document&retrieve&patient_id=" .
                    htmlspecialchars($pid,ENT_QUOTES) . "&document_id=" .
                    htmlspecialchars($idDoc,ENT_QUOTES) . "&as_file=true' onclick='top.restoreSession()'>" .
                    htmlspecialchars(xl_document_category($nameDoc),ENT_NOQUOTES) . "</a> " .
                    htmlspecialchars($dateDoc,ENT_NOQUOTES);
              echo "<br>";
              $limitCounter = $limitCounter + 1;
              $counterFlag = true;
              }
          }
          }
          if (!$counterFlag) {
              echo "&nbsp;&nbsp;" . htmlspecialchars(xl('None'),ENT_NOQUOTES);
          } ?>
      </div>
 <?php  }  // close advanced dir block
 
	// This is a feature for a specific client.  -- Rod
	if ($GLOBALS['cene_specific']) {
	  echo "   <br />\n";

          $imagedir  = $GLOBALS['OE_SITE_DIR'] . "/documents/$pid/demographics";
          $imagepath = "$web_root/sites/" . $_SESSION['site_id'] . "/documents/$pid/demographics";

	  echo "   <a href='' onclick=\"return sendimage($pid, 'photo');\" " .
		"title='Click to attach patient image'>\n";
	  if (is_file("$imagedir/photo.jpg")) {
		echo "   <img src='$imagepath/photo.jpg' /></a>\n";
	  } else {
		echo "   Attach Patient Image</a><br />\n";
	  }
	  echo "   <br />&nbsp;<br />\n";

	  echo "   <a href='' onclick=\"return sendimage($pid, 'fingerprint');\" " .
		"title='Click to attach fingerprint'>\n";
	  if (is_file("$imagedir/fingerprint.jpg")) {
		echo "   <img src='$imagepath/fingerprint.jpg' /></a>\n";
	  } else {
		echo "   Attach Biometric Fingerprint</a><br />\n";
	  }
	  echo "   <br />&nbsp;<br />\n";
	}

	// This stuff only applies to athletic team use of OpenEMR.  The client
	// insisted on being able to quickly change fitness and return date here:
	//
	if (false && $GLOBALS['athletic_team']) {
	  //                  blue      green     yellow    red       orange
	  $fitcolors = array('#6677ff','#00cc00','#ffff00','#ff3333','#ff8800','#ffeecc','#ffccaa');
	  if (!empty($GLOBALS['fitness_colors'])) $fitcolors = $GLOBALS['fitness_colors'];
	  $fitcolor = $fitcolors[0];
	  $form_fitness   = $_POST['form_fitness'];
	  $form_userdate1 = fixDate($_POST['form_userdate1'], '');
	  $form_issue_id  = $_POST['form_issue_id'];
	  if ($form_submit) {
		$returndate = $form_userdate1 ? "'$form_userdate1'" : "NULL";
		sqlStatement("UPDATE patient_data SET fitness = ?, " .
		  "userdate1 = ? WHERE pid = ?", array($form_fitness, $returndate, $pid) );
		// Update return date in the designated issue, if requested.
		if ($form_issue_id) {
		  sqlStatement("UPDATE lists SET returndate = ? WHERE " .
		    "id = ?", array($returndate, $form_issue_id) );
		}
	  } else {
		$form_fitness = $result['fitness'];
		if (! $form_fitness) $form_fitness = 1;
		$form_userdate1 = $result['userdate1'];
	  }
	  $fitcolor = $fitcolors[$form_fitness - 1];
	  echo "   <form method='post' action='demographics.php' onsubmit='return validate()'>\n";
	  echo "   <span class='bold'>Fitness to Play:</span><br />\n";
	  echo "   <select name='form_fitness' style='background-color:$fitcolor'>\n";
	  $res = sqlStatement("SELECT * FROM list_options WHERE " .
		"list_id = 'fitness' ORDER BY seq");
	  while ($row = sqlFetchArray($res)) {
		$key = $row['option_id'];
		echo "    <option value='" . htmlspecialchars($key,ENT_QUOTES) . "'";
		if ($key == $form_fitness) echo " selected";
		echo ">" . htmlspecialchars($row['title'],ENT_NOQUOTES) . "</option>\n";
	  }
	  echo "   </select>\n";
	  echo "   <br /><span class='bold'>Return to Play:</span><br>\n";
	  echo "   <input type='text' size='10' name='form_userdate1' id='form_userdate1' " .
		"value='$form_userdate1' " .
		"title='" . htmlspecialchars(xl('yyyy-mm-dd Date of return to play'),ENT_QUOTES) . "' " .
		"onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' />\n" .
		"   <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22' " .
		"id='img_userdate1' border='0' alt='[?]' style='cursor:pointer' " .
		"title='" . htmlspecialchars(xl('Click here to choose a date'),ENT_QUOTES) . "'>\n";
	  echo "   <input type='hidden' name='form_original_userdate1' value='" . htmlspecialchars($form_userdate1,ENT_QUOTES) . "' />\n";
	  echo "   <input type='hidden' name='form_issue_id' value='' />\n";
	  echo "<p><input type='submit' name='form_submit' value='Change' /></p>\n";
	  echo "   </form>\n";
	}

	// Show current and upcoming appointments.
	if (isset($pid) && !$GLOBALS['disable_calendar']) {
	 $query = "SELECT e.pc_eid, e.pc_aid, e.pc_title, e.pc_eventDate, " .
	  "e.pc_startTime, e.pc_hometext, u.fname, u.lname, u.mname, " .
	  "c.pc_catname, e.pc_apptstatus " .
	  "FROM openemr_postcalendar_events AS e, users AS u, " .
	  "openemr_postcalendar_categories AS c WHERE " .
	  "e.pc_pid = ? AND e.pc_eventDate >= CURRENT_DATE AND " .
	  "u.id = e.pc_aid AND e.pc_catid = c.pc_catid " .
	  "ORDER BY e.pc_eventDate, e.pc_startTime";
	 $res = sqlStatement($query, array($pid) );

     if ( (acl_check('patients', 'med')) && ($GLOBALS['enable_cdr'] && $GLOBALS['enable_cdr_crw']) ) {
        // clinical summary expand collapse widget
        $widgetTitle = xl("Clinical Reminders");
        $widgetLabel = "clinical_reminders";
        $widgetButtonLabel = xl("Edit");
        $widgetButtonLink = "../reminder/clinical_reminders.php?patient_id=".$pid;;
        $widgetButtonClass = "";
        $linkMethod = "html";
        $bodyClass = "summary_item small";
        $widgetAuth = true;
        $fixedWidth = false;
        expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel , $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass, $widgetAuth, $fixedWidth);
        echo "<br/>";
        echo "<div style='margin-left:10px' class='text'><image src='../../pic/ajax-loader.gif'/></div><br/>";
        echo "</div>";
        } // end if crw

	// appointments expand collapse widget
        $widgetTitle = xl("Appointments");
        $widgetLabel = "appointments";
        $widgetButtonLabel = xl("Add");
        $widgetButtonLink = "return newEvt();";
        $widgetButtonClass = "";
        $linkMethod = "javascript";
        $bodyClass = "summary_item small";
        $widgetAuth = (isset($res) && $res != null);
        $fixedWidth = false;
        expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel , $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass, $widgetAuth, $fixedWidth);
        $count = 0;
        while($row = sqlFetchArray($res)) {
            $count++;
            $dayname = date("l", strtotime($row['pc_eventDate']));
            $dispampm = "am";
            $disphour = substr($row['pc_startTime'], 0, 2) + 0;
            $dispmin  = substr($row['pc_startTime'], 3, 2);
            if ($disphour >= 12) {
                $dispampm = "pm";
                if ($disphour > 12) $disphour -= 12;
            }
            $etitle = xl('(Click to edit)');
            if ($row['pc_hometext'] != "") {
                $etitle = xl('Comments').": ".($row['pc_hometext'])."\r\n".$etitle;
            }
            echo "<a href='javascript:oldEvt(" . htmlspecialchars($row['pc_eid'],ENT_QUOTES) . ")' title='" . htmlspecialchars($etitle,ENT_QUOTES) . "'>";
            echo "<b>" . htmlspecialchars(xl($dayname) . ", " . $row['pc_eventDate'],ENT_NOQUOTES) . "</b>" . xlt("Status") .  "(";
            echo " " .  generate_display_field(array('data_type'=>'1','list_id'=>'apptstat'),$row['pc_apptstatus']) . ")<br>";   // can't use special char parser on this
            echo htmlspecialchars("$disphour:$dispmin " . xl($dispampm) . " " . xl_appt_category($row['pc_catname']),ENT_NOQUOTES) . "<br>\n";
            echo htmlspecialchars($row['fname'] . " " . $row['lname'],ENT_NOQUOTES) . "</a><br>\n";
        }
        if (isset($res) && $res != null) {
            if ( $count < 1 ) { 
                echo "&nbsp;&nbsp;" . htmlspecialchars(xl('None'),ENT_NOQUOTES); 
            }
            echo "</div>";
      }
    }
            
	// Show PAST appointments.
	// added by Terry Hill to allow reverse sorting of the appointments
 	$direction = "ASC";
	if ($GLOBALS['num_past_appointments_to_show'] < 0) {
	   $direction = "DESC";
	   ($showpast = -1 * $GLOBALS['num_past_appointments_to_show'] );
	   }
	   else
	   {
	   $showpast = $GLOBALS['num_past_appointments_to_show'];
	   }
	   
	if (isset($pid) && !$GLOBALS['disable_calendar'] && $showpast > 0) {
	 $query = "SELECT e.pc_eid, e.pc_aid, e.pc_title, e.pc_eventDate, " .
	  "e.pc_startTime, e.pc_hometext, u.fname, u.lname, u.mname, " .
	  "c.pc_catname, e.pc_apptstatus " .
	  "FROM openemr_postcalendar_events AS e, users AS u, " .
	  "openemr_postcalendar_categories AS c WHERE " .
	  "e.pc_pid = ? AND e.pc_eventDate < CURRENT_DATE AND " .
	  "u.id = e.pc_aid AND e.pc_catid = c.pc_catid " .
	  "ORDER BY e.pc_eventDate $direction , e.pc_startTime DESC " . 
      "LIMIT " . $showpast;
	
     $pres = sqlStatement($query, array($pid) );

	// appointments expand collapse widget
        $widgetTitle = xl("Past Appoinments");
        $widgetLabel = "past_appointments";
        $widgetButtonLabel = '';
        $widgetButtonLink = '';
        $widgetButtonClass = '';
        $linkMethod = "javascript";
        $bodyClass = "summary_item small";
        $widgetAuth = false; //no button
        $fixedWidth = false;
        expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel , $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass, $widgetAuth, $fixedWidth);   
        $count = 0;
        while($row = sqlFetchArray($pres)) {
            $count++;
            $dayname = date("l", strtotime($row['pc_eventDate']));
            $dispampm = "am";
            $disphour = substr($row['pc_startTime'], 0, 2) + 0;
            $dispmin  = substr($row['pc_startTime'], 3, 2);
            if ($disphour >= 12) {
                $dispampm = "pm";
                if ($disphour > 12) $disphour -= 12;
            }
            if ($row['pc_hometext'] != "") {
                $etitle = xl('Comments').": ".($row['pc_hometext'])."\r\n".$etitle;
            }
            echo "<a href='javascript:oldEvt(" . htmlspecialchars($row['pc_eid'],ENT_QUOTES) . ")' title='" . htmlspecialchars($etitle,ENT_QUOTES) . "'>";
            echo "<b>" . htmlspecialchars(xl($dayname) . ", " . $row['pc_eventDate'],ENT_NOQUOTES) . "</b>" . xlt("Status") .  "(";
            echo " " .  generate_display_field(array('data_type'=>'1','list_id'=>'apptstat'),$row['pc_apptstatus']) . ")<br>";   // can't use special char parser on this
            echo htmlspecialchars("$disphour:$dispmin ") . xl($dispampm) . " ";
            echo htmlspecialchars($row['fname'] . " " . $row['lname'],ENT_NOQUOTES) . "</a><br>\n";
        }
        if (isset($pres) && $res != null) {
           if ( $count < 1 ) { 
               echo "&nbsp;&nbsp;" . htmlspecialchars(xl('None'),ENT_NOQUOTES);          
           }
        echo "</div>";
        }
    }
// END of past appointments            
            
			?>
		</div>

		<div id='stats_div'>
            <br/>
            <div style='margin-left:10px' class='text'><img src='../../pic/ajax-loader.gif'/></div><br/>
        </div>
    </td>
    </tr>
    
           <?php // TRACK ANYTHING -----
		
		// Determine if track_anything form is in use for this site.
		$tmp = sqlQuery("SELECT count(*) AS count FROM registry WHERE " .
						"directory = 'track_anything' AND state = 1");
		$track_is_registered = $tmp['count'];
		if($track_is_registered){
			echo "<tr> <td>";
			// track_anything expand collapse widget
			$widgetTitle = xl("Tracks");
			$widgetLabel = "track_anything";
			$widgetButtonLabel = xl("Tracks");
			$widgetButtonLink = "../../forms/track_anything/create.php";
			$widgetButtonClass = "";
			$widgetAuth = "";  // don't show the button
			$linkMethod = "html";
			$bodyClass = "notab";
			// check to see if any tracks exist
			$spruch = "SELECT id " .
				"FROM forms " . 
				"WHERE pid = ? " .
				"AND formdir = ? "; 
			$existTracks = sqlQuery($spruch, array($pid, "track_anything") );	

			$fixedWidth = false;
			expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel,
				$widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass,
				$widgetAuth, $fixedWidth);
?>
      <br/>
      <div style='margin-left:10px' class='text'><img src='../../pic/ajax-loader.gif'/></div><br/>
      </div>
     </td>
    </tr> -->
<?php  }  // end track_anything ?>
    </table>

	</div> 

  </td>

 </tr> 
</table>
</div>

</div>
<div class="col-md-4">

<?php
$qry = "SELECT lists.date,lists.next_date, lists.title, form_encounter.provider_id,form_encounter.reason,form_encounter.encounter
FROM form_encounter,lists
WHERE form_encounter.pid = lists.pid
AND form_encounter.pid = ?
AND form_encounter.encounter = lists.encounter
Order By form_encounter.encounter DESC";
          $pdata = sqlStatement($qry, array($pid));

			 
?>
<ul style="list-style:none">
<li id="npclick"><a href="#" class="css_button pull-right"><span><i class="fa fa-plus"></i> <?php echo xlt('New Visit'); ?></span></a><li>
<li id="iclick"><a href="#"  class="css_button pull-right" ><span><i class="fa fa-plus"></i> <?php echo xlt('Add Prescription'); ?></a></span></li>

</ul>
<br>
<ul class="timeline">
<?php 	while ($prow = sqlFetchArray($pdata)) { ?>
                        <!-- timeline time label -->
                            <li class="time-label">
                                 <span class="bg-green">
								 
                                        <?php echo $prow['date']; ?>
                                    </span>                             </li>
                            <li>
                                                                  <a id="encounter" href="../encounter/encounter_top.php?set_encounter=<?php echo $prow['encounter'] ?>">  <i class="fa fa-mail-reply-all bg-yellow uni" style="margin-left: 20px;padding: 6px;border-radius: 13px;" title="View Details"></i></a>
                                                                <div class="timeline-item">
                                    <h3 class="timeline-header">
                                    
                                    <div class="user-block" style="display: inline-block;">
                                       
                                                                                            <img src="https://d30y9cdsu7xlg0.cloudfront.net/png/23420-200.png" class="img-circle img-bordered-sm" alt="User Image">
                                            
                                        <span class="username">
                                          <a href="#"><?php $doctorname = sqlStatement("SELECT username FROM users WHERE id=?", array($prow['provider_id']));  while($doctor = sqlFetchArray($doctorname)) { echo $doctor['username']; }?></a>
                                          
                                        </span>
                                        <span class="description"><i class="fa fa-clock-o"></i> <?php echo $prow['date']; ?></span>
                                      </div><!-- /.user-block -->
                                      									<dl class="dl-horizontal">
  <dt>Diagnosis</dt>
  <dd><?php echo $prow['title']; ?></dd>
  <?php $qry2 = "SELECT prescriptions.drug,prescriptions.dosage,list_options.title
FROM list_options,prescriptions
WHERE list_options.option_id = prescriptions.interval AND list_id = 'drug_interval'
AND patient_id = ?
AND encounter = ?";
          $prescription = sqlStatement($qry2, array($pid,$prow['encounter']));
		  while ($pres = sqlFetchArray($prescription)) {
		  ?>
  <dt>Prescription</dt>
  <dd><?php echo $pres['drug']; ?> : <?php echo $pres['title']; ?></dd>
		  <?php } ?>
  <dt>Next Appointment</dt>
  <dd><?php echo $prow['next_date']; ?></dd>
</dl>  
                                    </h3>

                                    <div class="timeline-body">
                                            <p style="word-wrap: break-word;"><?php echo $prow['reason']; ?></p>
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
                            </li>
                                                   
<?php } ?>
 <li>
                            <i class="fa fa-clock-o bg-gray"></i>
                        </li>
                        <ul class="pull-right" style="padding-right:25px;padding-bottom:10px;">
                        </ul>
                    </ul>
</div>
</div>
</div> <!-- end main content div -->

<?php if (false && $GLOBALS['athletic_team']) { ?>
<script language='JavaScript'>
 Calendar.setup({inputField:"form_userdate1", ifFormat:"%Y-%m-%d", button:"img_userdate1"});

</script>
<?php } ?>
<script type="text/javascript">
$( document ).ready(function() {

		$('.tabNav li:eq(2)').remove();
		$('.tabContainer div:eq(2)').remove();
		$('.tabNav').addClass('nav nav-tabs');
		$('.tabContainer').addClass('tab-pane');
		$('#iclick').hide();
	$('#iclick').click(function (e) {
     e.preventDefault();
     var ids = $(".nav-tabs").children().length; //think about it ;)
	 var id = ids + 1;
     var tabId = 'iclick_' + id;
	 
     $('.tabNav').append('<li><a href="#iclick_' + id + '" id="newt">Prescription</a></li>');
     $('.tabContainer').append('<div class="tab" id="' + tabId + '"><iframe src="<?php echo $GLOBALS['webroot'] ?>/controller.php?prescription&edit&id=&pid=<?php echo $pid ?>" frameborder="0" width="600" height="400" scrolling="auto" id="myFrame"></iframe></div>');

     // add this

		 $('.tabNav li:nth-child(' + id + ')').addClass('current').siblings().removeClass('current');

			$("#"+tabId).addClass('current').siblings().removeClass('current');
			

});
	$('#npclick').click(function (e) {
     e.preventDefault();
     var ids = $(".nav-tabs").children().length; //think about it ;)
	 var id = ids + 1;
     var tabId = 'iclick_' + id;
     $('.tabNav').append('<li><a href="#iclick_' + id + '" id="newt">New Visit</a></li>');
     $('.tabContainer').append('<div class="tab" id="' + tabId + '"><iframe src="<?php echo $GLOBALS['webroot'] ?>/interface/forms/newpatient/new.php?autoloaded=1&calenc=" frameborder="0" width="600" height="500" scrolling="auto" id="myFrame"></iframe></div>');

     // add this

		 $('.tabNav li:nth-child(' + id + ')').addClass('current').siblings().removeClass('current');

			$("#"+tabId).addClass('current').siblings().removeClass('current');
	$('#iclick').show();
});
	 
});
</script>
</body>
</html>
