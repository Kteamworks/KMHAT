<?php

require_once("../../globals.php");
require_once("$srcdir/lists.inc");
require_once("$srcdir/acl.inc");
require_once("$srcdir/forms.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/formatting.inc.php");

// get various authorization levels
$auth_notes_a  = acl_check('encounters', 'notes_a');
$auth_notes    = acl_check('encounters', 'notes');
$auth_coding_a = acl_check('encounters', 'coding_a');
$auth_coding   = acl_check('encounters', 'coding');
$auth_relaxed  = acl_check('encounters', 'relaxed');
$auth_med      = acl_check('patients'  , 'med');
$auth_demo     = acl_check('patients'  , 'demo');

$cmsportal = false;
if ($GLOBALS['gbl_portal_cms_enable']) {
  $ptdata = getPatientData($pid, 'cmsportal_login');
  $cmsportal = $ptdata['cmsportal_login'] !== '';
}
?>
<html>
<head>
<?php html_header_show();?>

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<style type="text/css">@import url(../../../library/dynarch_calendar.css);</style>
<script type="text/javascript" src="../../../library/textformat.js"></script>
<script type="text/javascript" src="../../../library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="../../../library/dynarch_calendar_setup.js"></script>

<!-- include jQuery support -->
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery.js"></script>

<script language='JavaScript'>

function checkAll(check) {
 var f = document.forms['report_form'];
 for (var i = 0; i < f.elements.length; ++i) {
  if (f.elements[i].type == 'checkbox') f.elements[i].checked = check;
 }
 return false;
}

function show_date_fun(){
  if(document.getElementById('show_date').checked == true){
    document.getElementById('date_div').style.display = '';
  }else{
    document.getElementById('date_div').style.display = 'none';
  }
  return;
}

</script>

</head>

<body class="body_top">
<div id="patient_reports"> <!-- large outer DIV -->



<form name='report_form' id="report_form" method='post' action='custom_report.php'>


<span class='title'><?php xl('Patient Report','e'); ?></span>&nbsp;&nbsp;

<!--
<a class="link_submit" href="full_report.php" onclick="top.restoreSession()">
[<?php xl('View Comprehensive Patient Report','e'); ?>]</a>
-->
<a class="link_submit" href="#" onclick="return checkAll(true)"><?php xl('Check All','e'); ?></a>
|
<a class="link_submit" href="#" onclick="return checkAll(false)"><?php xl('Clear All','e'); ?></a>
<p>

<table class="includes">
 <tr>
  <td class='text'>
   <input type='checkbox' name='include_demographics' id='include_demographics' value="demographics" checked><?php xl('Demographics','e'); ?><br>
 
  <td class='text'>
   <input type='checkbox' name='include_notes' id='include_notes' value="notes"><?php xl('Patient Notes','e'); ?><br>
  </td>
 </tr>
</table>

<br>
<input type="button" class="genreport" value="<?php xl('Generate Report','e'); ?>" />&nbsp;
<input type="button" class="genpdfrep" value="<?php xl('Download PDF','e'); ?>" />&nbsp;
<?php if ($cmsportal) { ?>
<input type="button" class="genportal" value="<?php xl('Send to Portal','e'); ?>" />
<?php } ?>
<input type='hidden' name='pdf' value='0'>
<br>

<!-- old ccr button position -->
<hr/>

<table class="issues_encounters_forms">
 <tr>

  <!-- Issues -->
  <td class='text'>
  <div class="issues">
  <span class='bold'><?php xl('Issues','e'); ?>:</span>
   <br>
   <br>

<?php if (! acl_check('patients', 'med')): ?>
<br>(Issues not authorized)

<?php else: ?>
   <table>

<?php
// get issues
$pres = sqlStatement("SELECT * FROM lists WHERE pid = $pid  " .
                    "ORDER BY type, begdate");
$lasttype = "";
while ($prow = sqlFetchArray($pres)) {
    if ($lasttype != $prow['type']) {
        $lasttype = $prow['type'];

   /****
   $disptype = $lasttype;
   switch ($lasttype) {
    case "allergy"        : $disptype = "Allergies"       ; break;
    case "problem"        :
    case "medical_problem": $disptype = "Medical Problems"; break;
    case "medication"     : $disptype = "Medications"     ; break;
    case "surgery"        : $disptype = "Surgeries"       ; break;
   }
   ****/
        $disptype = $ISSUE_TYPES[$lasttype][0];

        echo " <tr>\n";
        echo "  <td colspan='4' class='bold'><b>$disptype</b></td>\n";
        echo " </tr>\n";
    }
    $rowid = $prow['id'];
    $disptitle = trim($prow['title']) ? $prow['title'] : "[Missing Title]";

    $ieres = sqlStatement("SELECT encounter FROM issue_encounter WHERE " .
                        "pid = '$pid' AND list_id = '$rowid'");

    echo "    <tr class='text'>\n";
    echo "     <td>&nbsp;</td>\n";
    echo "     <td>";
    echo "<input type='checkbox' name='issue_$rowid' id='issue_$rowid' class='issuecheckbox' value='/";
    while ($ierow = sqlFetchArray($ieres)) {
        echo $ierow['encounter'] . "/";
    }
    echo "' />$disptitle</td>\n";
    echo "     <td>" . $prow['begdate'];

    if ($prow['enddate']) { echo " - " . $prow['enddate']; }
    else { echo " Active"; }

    echo "</td>\n";
    echo "</tr>\n";
}
?>
   </table>

<?php endif; // end of Issues output ?>

   </div> <!-- end issues DIV -->
  </td>

<!-- Encounters and Forms -->


 </tr>
</table>
<input type="button" class="genreport" value="<?php xl('Generate Report','e'); ?>" />&nbsp;
<input type="button" class="genpdfrep" value="<?php xl('Download PDF','e'); ?>" />&nbsp;
<?php if ($cmsportal) { ?>
<input type="button" class="genportal" value="<?php xl('Send to Portal','e'); ?>" />
<?php } ?>



</form>

<?php if ($cmsportal) { ?>
<input type="button" class="genportal" value="<?php xl('Send to Portal','e'); ?>" />
<?php } ?>

</div>  <!-- close patient_reports DIV -->
</body>

<script language="javascript">

// jQuery stuff to make the page a little easier to use
$(document).ready(function(){
    $(".genreport").click(function() { top.restoreSession(); document.report_form.pdf.value = 0; $("#report_form").submit(); });
    $(".genpdfrep").click(function() { top.restoreSession(); document.report_form.pdf.value = 1; $("#report_form").submit(); });
    $(".genportal").click(function() { top.restoreSession(); document.report_form.pdf.value = 2; $("#report_form").submit(); });
    $("#genfullreport").click(function() { location.href='<?php echo "$rootdir/patient_file/encounter/$returnurl";?>'; });
    //$("#printform").click(function() { PrintForm(); });
    $(".issuecheckbox").click(function() { issueClick(this); });

    // check/uncheck all Forms of an encounter
    $(".encounter").click(function() { SelectForms($(this)); });

	$(".generateCCR").click(
        function() {
                if(document.getElementById('show_date').checked == true){
                        if(document.getElementById('Start').value == '' || document.getElementById('End').value == ''){
                                alert('<?php echo addslashes( xl('Please select a start date and end date')) ?>');
                                return false;
                        }
                }
		var ccrAction = document.getElementsByName('ccrAction');
		ccrAction[0].value = 'generate';
                var raw = document.getElementsByName('raw');
                raw[0].value = 'no';
		top.restoreSession();
		ccr_form.setAttribute("target", "_blank");
		$("#ccr_form").submit();
                ccr_form.setAttribute("target", "");
	});
        $(".generateCCR_raw").click(
        function() {
                var ccrAction = document.getElementsByName('ccrAction');
                ccrAction[0].value = 'generate';
                var raw = document.getElementsByName('raw');
                raw[0].value = 'yes';
                top.restoreSession();
                ccr_form.setAttribute("target", "_blank");
                $("#ccr_form").submit();
                ccr_form.setAttribute("target", "");
        });
        $(".generateCCR_download_h").click(
        function() {
                var ccrAction = document.getElementsByName('ccrAction');
                ccrAction[0].value = 'generate';
                var raw = document.getElementsByName('raw');
                raw[0].value = 'hybrid';
                top.restoreSession();
                $("#ccr_form").submit();
        });
        $(".generateCCR_download_p").click(
        function() {
                if(document.getElementById('show_date').checked == true){
                        if(document.getElementById('Start').value == '' || document.getElementById('End').value == ''){
                                alert('<?php echo addslashes( xl('Please select a start date and end date')) ?>');
                                return false;
                        }
                }
                var ccrAction = document.getElementsByName('ccrAction');
                ccrAction[0].value = 'generate';
                var raw = document.getElementsByName('raw');
                raw[0].value = 'pure';
                top.restoreSession();
                $("#ccr_form").submit();
        });
	$(".viewCCD").click(
	function() { 
		var ccrAction = document.getElementsByName('ccrAction');
		ccrAction[0].value = 'viewccd';
                var raw = document.getElementsByName('raw');
                raw[0].value = 'no';
		top.restoreSession();
                ccr_form.setAttribute("target", "_blank"); 
		$("#ccr_form").submit();
                ccr_form.setAttribute("target", "");
	});
        $(".viewCCD_raw").click(
        function() {
                var ccrAction = document.getElementsByName('ccrAction');
                ccrAction[0].value = 'viewccd';
                var raw = document.getElementsByName('raw');
                raw[0].value = 'yes';
                top.restoreSession();
                ccr_form.setAttribute("target", "_blank");
                $("#ccr_form").submit();
                ccr_form.setAttribute("target", "");
        });
        $(".viewCCD_download").click(
        function() {
                var ccrAction = document.getElementsByName('ccrAction');
                ccrAction[0].value = 'viewccd';
                var raw = document.getElementsByName('raw');
                raw[0].value = 'pure';
                $("#ccr_form").submit();
        });
<?php if ($GLOBALS['phimail_enable']==true && $GLOBALS['phimail_ccr_enable']==true) { ?>
        $(".viewCCR_send_dialog").click(
        function() {
                $("#ccr_send_dialog").toggle();
        });
        $(".viewCCR_transmit").click(
        function() {
                $(".viewCCR_transmit").attr('disabled','disabled');
                var ccrAction = document.getElementsByName('ccrAction');
                ccrAction[0].value = 'generate';
                var ccrRecipient = $("#ccr_send_to").val();
                var raw = document.getElementsByName('raw');
                raw[0].value = 'send '+ccrRecipient;
                if(ccrRecipient=="") {
                  $("#ccr_send_message").html("<?php
       echo htmlspecialchars(xl('Please enter a valid Direct Address above.'), ENT_QUOTES);?>");
                  $("#ccr_send_result").show();
                } else {
                  $(".viewCCR_transmit").attr('disabled','disabled');
                  $("#ccr_send_message").html("<?php
       echo htmlspecialchars(xl('Working... this may take a minute.'), ENT_QUOTES);?>");
                  $("#ccr_send_result").show();
                  var action=$("#ccr_form").attr('action');
                  $.post(action, {ccrAction:'generate',raw:'send '+ccrRecipient,requested_by:'user'},
                     function(data) {
                       if(data=="SUCCESS") {
                         $("#ccr_send_message").html("<?php
       echo htmlspecialchars(xl('Your message was submitted for delivery to'), ENT_QUOTES);
                           ?> "+ccrRecipient);
                         $("#ccr_send_to").val("");
                       } else {
                         $("#ccr_send_message").html(data);
                       }
                       $(".viewCCR_transmit").removeAttr('disabled');
                  });
                }
        });
<?php }
      if ($GLOBALS['phimail_enable']==true && $GLOBALS['phimail_ccd_enable']==true) { ?>
        $(".viewCCD_send_dialog").click(
        function() {
                $("#ccd_send_dialog").toggle();
        });
        $(".viewCCD_transmit").click(
        function() {
                $(".viewCCD_transmit").attr('disabled','disabled');
                var ccrAction = document.getElementsByName('ccrAction');
                ccrAction[0].value = 'viewccd';
                var ccdRecipient = $("#ccd_send_to").val();
                var raw = document.getElementsByName('raw');
                raw[0].value = 'send '+ccdRecipient;
                if(ccdRecipient=="") {
                  $("#ccd_send_message").html("<?php
       echo htmlspecialchars(xl('Please enter a valid Direct Address above.'), ENT_QUOTES);?>");
                  $("#ccd_send_result").show();
                } else {
                  $(".viewCCD_transmit").attr('disabled','disabled');
                  $("#ccd_send_message").html("<?php
       echo htmlspecialchars(xl('Working... this may take a minute.'), ENT_QUOTES);?>");
                  $("#ccd_send_result").show();
                  var action=$("#ccr_form").attr('action');
                  $.post(action, {ccrAction:'viewccd',raw:'send '+ccdRecipient,requested_by:'user'},
                     function(data) {
                       if(data=="SUCCESS") {
                         $("#ccd_send_message").html("<?php
       echo htmlspecialchars(xl('Your message was submitted for delivery to'), ENT_QUOTES);
                           ?> "+ccdRecipient);
                         $("#ccd_send_to").val("");
                       } else {
                         $("#ccd_send_message").html(data);
                       }
                       $(".viewCCD_transmit").removeAttr('disabled');
                  });
                }
        });
<?php } ?>

});

// select/deselect the Forms related to the selected Encounter
// (it ain't pretty code folks)
var SelectForms = function (selectedEncounter) {
    if ($(selectedEncounter).attr("checked")) {
        $(selectedEncounter).parent().children().each(function(i, obj) {
            $(this).children().each(function(i, obj) {
                $(this).attr("checked", "checked");
            });
        });
    }
    else {
        $(selectedEncounter).parent().children().each(function(i, obj) {
            $(this).children().each(function(i, obj) {
                $(this).removeAttr("checked");
            });
        });
    }
}

// When an issue is checked, auto-check all the related encounters and forms
function issueClick(issue) {
    // do nothing when unchecked
    if (! $(issue).attr("checked")) return;

    $("#report_form :checkbox").each(function(i, obj) {
        if ($(issue).val().indexOf('/' + $(this).val() + '/') >= 0) {
            $(this).attr("checked", "checked");
        }
            
    });
}

</script>

</html>
