<?php /* Smarty version 2.6.2, created on 2018-06-28 12:08:52
         compiled from C:/xampp/htdocs/KMHAT/MHAT/templates/prescription/general_edit.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'xl', 'C:/xampp/htdocs/KMHAT/MHAT/templates/prescription/general_edit.html', 107, false),array('function', 'amcCollect', 'C:/xampp/htdocs/KMHAT/MHAT/templates/prescription/general_edit.html', 131, false),array('function', 'html_select_date', 'C:/xampp/htdocs/KMHAT/MHAT/templates/prescription/general_edit.html', 152, false),array('function', 'html_options', 'C:/xampp/htdocs/KMHAT/MHAT/templates/prescription/general_edit.html', 158, false),array('function', 'html_radios', 'C:/xampp/htdocs/KMHAT/MHAT/templates/prescription/general_edit.html', 246, false),)), $this); ?>
<html>
<head>
<?php html_header_show(); ?>

<link rel="stylesheet" href="<?php echo $this->_tpl_vars['CSS_HEADER']; ?>
" type="text/css">
<link rel="stylesheet" href="<?php echo $this->_tpl_vars['WEBROOT']; ?>
/interface/themes/jquery.autocomplete.css" type="text/css">
<?php echo '
<style type="text/css">
    .text {
        font-size: 9pt;
    }
</style>
'; ?>

    <link data-require="bootstrap-css@*" data-semver="2.3.2" rel="stylesheet" href="<?php echo $this->_tpl_vars['WEBROOT']; ?>
/library/bootstrap/bootstrap-combined.min.css" />
    <link href="<?php echo $this->_tpl_vars['WEBROOT']; ?>
/library/bootstrap/bootstrap-dialog.min.css" rel="stylesheet" />
	<script src="<?php echo $this->_tpl_vars['WEBROOT']; ?>
/library/bootstrap/angular.js"></script>
    <script data-require="jquery@*" data-semver="2.0.1" src="<?php echo $this->_tpl_vars['WEBROOT']; ?>
/library/bootstrap/jquery-2.0.1.min.js"></script>
    <script data-require="bootstrap@2.3.2" data-semver="2.3.2" src="http://netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/js/bootstrap.min.js"></script>
			<script src="<?php echo $this->_tpl_vars['WEBROOT']; ?>
/library/bootstrap/bootstrap-dialog.min.js" type="text/javascript"></script>
    <script data-require="ui-bootstrap@0.3.0" data-semver="0.3.0" src="<?php echo $this->_tpl_vars['WEBROOT']; ?>
/library/bootstrap/ui-bootstrap-tpls-0.9.0.min.js"></script>
<script language="Javascript">
<?php echo '
		function my_process () {
			// Pass the variable
			opener.document.prescribe.drug.value = document.lookup.drug.value;
			// Close the window
			window.self.close();
		}
'; ?>

</script>
<?php echo '
'; ?>

<!---Gen Look up-->
<script type="text/javascript" src="<?php echo $this->_tpl_vars['WEBROOT']; ?>
/library/dialog.js"></script>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['WEBROOT']; ?>
/library/js/jquery-1.2.2.min.js"></script>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['WEBROOT']; ?>
/library/js/jquery.bgiframe.min.js"></script>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['WEBROOT']; ?>
/library/js/jquery.dimensions.pack.js"></script>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['WEBROOT']; ?>
/library/js/jquery.autocomplete.pack.js"></script>
<?php echo '
<script language=\'JavaScript\'>

 // This holds all the default drug attributes.
'; ?>

 var drugopts = [<?php echo $this->_tpl_vars['DRUG_ATTRIBUTES']; ?>
];
 var drug_name  = [<?php echo $this->_tpl_vars['DRUG_NAMES']; ?>
];
  drug_names=drug_name.toString().split(",");

 var drug_id1 = [<?php echo $this->_tpl_vars['DRUG_ID']; ?>
];
 drug_id=drug_id1.toString().split(",");
<?php echo '

 // Helper to choose an option from its value.
 function selchoose(sel, value) {
  var o = sel.options;
  for (i = 0; i < o.length; ++i) {
   o[i].selected = (o[i].value == value);
  }
 }

 // Fill in default values when a drop-down drug is selected.
 function drugselected(sel) {
  var f = document.forms[0];
  var i = f.drug_id.selectedIndex - 1;
  if (i >= 0) {
   var d = drugopts[i];
   f.drug.value = d[0];
   selchoose(f.form, d[1]);
   f.dosage.value = d[2];
   f.size.value = d[3];
   selchoose(f.unit, d[4]);
   selchoose(f.route, d[5]);
   selchoose(f.interval, d[6]);
   selchoose(f.substitute, d[7]);
   f.quantity.value = d[8];
   f.disp_quantity.value = d[8];
   selchoose(f.refills, d[9]);
   f.per_refill.value = d[10];
  }
 }

 // Invoke the popup to dispense a drug.
 function dispense() {
  var f = document.forms[0];
  dlgopen(\'interface/drugs/dispense_drug.php\' +
   '; ?>
'?drug_id=<?php echo $this->_tpl_vars['prescription']->get_drug_id(); ?>
' +<?php echo '
   \'&prescription=\' + f.id.value +
   \'&quantity=\' + f.disp_quantity.value +
   \'&fee=\' + f.disp_fee.value,
   \'_blank\', 400, 200);
 }

 function quantityChanged() {
  var f = document.forms[0];
  f.per_refill.value = f.quantity.value;
  if (f.disp_quantity) {
   f.disp_quantity.value = f.quantity.value;
  }
 }

</script>
'; ?>

</head>
<body class="body_top">

<form name="prescribe" id="prescribe" method="post" action="<?php echo $this->_tpl_vars['FORM_ACTION']; ?>
">
<table>
    <tr><td class="title"><font><b><?php echo smarty_function_xl(array('t' => 'Add Prescription'), $this);?>
</b></font>&nbsp;</td>
	</tr>
	</table>
<table class="displaynone">
    <tr><td class="title"><font><b><?php echo smarty_function_xl(array('t' => 'Add'), $this);?>
/<?php echo smarty_function_xl(array('t' => 'Edit'), $this);?>
</b></font>&nbsp;</td>
		<td><a href=# onclick="submitfun();" class="css_button_small"><span><?php echo smarty_function_xl(array('t' => 'Save'), $this);?>
</span></a>
		<?php if ($this->_tpl_vars['DRUG_ARRAY_VALUES']): ?>
		&nbsp; &nbsp; &nbsp; &nbsp;
		<?php if ($this->_tpl_vars['prescription']->get_refills() >= $this->_tpl_vars['prescription']->get_dispensation_count()): ?>
		<input type="submit" name="disp_button" value="<?php echo smarty_function_xl(array('t' => 'Save and Dispense'), $this);?>
" />
		<input type="text" name="disp_quantity" size="2" maxlength="10" value="<?php echo $this->_tpl_vars['DISP_QUANTITY']; ?>
" />
		units, ₹
		<input type="text" name="disp_fee" size="5" maxlength="10" value="<?php echo $this->_tpl_vars['DISP_FEE']; ?>
" />
		<?php else: ?>&nbsp;
		<?php echo smarty_function_xl(array('t' => 'prescription has reached its limit of'), $this);?>
 <?php echo $this->_tpl_vars['prescription']->get_refills(); ?>
 <?php echo smarty_function_xl(array('t' => 'refills'), $this);?>
.
		<?php endif; ?>
		<?php endif; ?>
         <a class='css_button_small' href="controller.php?prescription&list&id=<?php echo $this->_tpl_vars['prescription']->patient->id; ?>
"><span><?php echo smarty_function_xl(array('t' => 'Back'), $this);?>
</span></a>
</td></tr>
</table>

<?php  if ($GLOBALS['enable_amc_prompting']) {  ?>
  <div  class="displaynone" style='float:right;margin-right:25px;border-style:solid;border-width:1px;'>
    <div style='float:left;margin:5px 5px 5px 5px;'>
      <?php echo smarty_function_amcCollect(array('amc_id' => 'e_prescribe_amc','patient_id' => $this->_tpl_vars['prescription']->patient->id,'object_category' => 'prescriptions','object_id' => $this->_tpl_vars['prescription']->id), $this);?>

      <?php if (! $this->_tpl_vars['amcCollectReturn']): ?>
        <input type="checkbox" id="escribe_flag" name="escribe_flag">
      <?php else: ?>
        <input type="checkbox" id="escribe_flag" name="escribe_flag" checked>
      <?php endif; ?>
      <span class="text"><?php echo smarty_function_xl(array('t' => 'E-Prescription?'), $this);?>
</span><br>
    </div>
  </div>
<?php  }  ?>

<table CELLSPACING="0" CELLPADDING="3" BORDER="0">
<tr class="displaynone">
  <td COLSPAN="1" class="text" ALIGN="right" VALIGN="MIDDLE" ><?php echo smarty_function_xl(array('t' => 'Currently Active'), $this);?>
</td>
  <td COLSPAN="2" ALIGN="LEFT" VALIGN="MIDDLE" >
    <input type="checkbox" name="active" value="1"<?php if ($this->_tpl_vars['prescription']->get_active() > 0): ?> checked<?php endif; ?> />
  </td>
</tr>
<!-- <tr class="displaynone">
	<td COLSPAN="1" class="text" ALIGN="right" VALIGN="MIDDLE" ><?php echo smarty_function_xl(array('t' => 'Starting Date'), $this);?>
</td>
	<td COLSPAN="2" ALIGN="LEFT" VALIGN="MIDDLE" >
		<?php echo smarty_function_html_select_date(array('start_year' => "-10",'end_year' => "+5",'time' => $this->_tpl_vars['prescription']->start_date,'prefix' => 'start_date_'), $this);?>

	</td>
</tr>
<tr class="displaynone">
	<td COLSPAN="1" class="text" ALIGN="right" VALIGN="MIDDLE" ><?php echo smarty_function_xl(array('t' => 'Provider'), $this);?>
</td>
	<td COLSPAN="2" ALIGN="LEFT" VALIGN="MIDDLE" >
		<?php echo smarty_function_html_options(array('name' => 'provider_id','options' => $this->_tpl_vars['prescription']->provider->utility_provider_array(),'selected' => $this->_tpl_vars['prescription']->provider->get_id()), $this);?>

		<input type="hidden" name="patient_id" value="<?php echo $this->_tpl_vars['prescription']->patient->id; ?>
" />
	</td>
</tr> -->
<tr class="displaynone">
	<td COLSPAN="1" class="text" ALIGN="right" VALIGN="MIDDLE" ><?php echo smarty_function_xl(array('t' => 'Drug'), $this);?>
</td>
	<td COLSPAN="2" ALIGN="LEFT" VALIGN="MIDDLE" >
            <input type="input" size="20" name="drug" id="drug" value="<?php echo $this->_tpl_vars['prescription']->drug; ?>
"/>
            <a href="javascript:;" id="druglookup" class="small" name="B4" onclick="$('#hiddendiv').show(); document.getElementById('hiddendiv').innerHTML='&lt;iframe src=&quot;controller.php?prescription&amp;lookup&amp;drug=&quot; width=&quot;100%&quot;height=&quot;52&quot; scrolling=&quot;no&quot; frameborder=&quot;no&quot;&gt;&lt;/iframe&gt;'">
            (<?php echo smarty_function_xl(array('t' => 'click here to search'), $this);?>
)</a>
            <div id=hiddendiv style="display:none">&nbsp;</div>
	</td>
</tr>
<?php if ($this->_tpl_vars['DRUG_ARRAY_VALUES']): ?>
<tr ng-controller="Main" ng-click="showAlert(dessert)" class="displayblock">
	<td COLSPAN="1" class="text" ALIGN="right" VALIGN="MIDDLE" >&nbsp; <?php echo smarty_function_xl(array('t' => 'in-house'), $this);?>
 </td>
	<td COLSPAN="2" ALIGN="LEFT" VALIGN="MIDDLE" >
	<input type = "text" ng-model = "yourName" placeholder = "Search by drug name...">
		<select name="drug_id" onchange="drugselected(this)" multiple ng-model="selectedValues" ng-change="search2()">
		<option  ng-repeat="cust in customers | filter: yourName" value="<% cust.id %>"><% cust.name %></option>
     
		</select>
	</td><br>
	<!-- <td><% selectedValues %></td> -->
</tr>
<?php endif; ?>
<tr class="displaynone">
	<td COLSPAN="1" class="text" ALIGN="right" VALIGN="MIDDLE" ><?php echo smarty_function_xl(array('t' => 'Quantity'), $this);?>
</td>
	<td COLSPAN="2" ALIGN="LEFT" VALIGN="MIDDLE" >
		<input TYPE="TEXT" NAME="quantity" id="quantity" SIZE="10" MAXLENGTH="31"
		 VALUE="<?php echo $this->_tpl_vars['prescription']->quantity; ?>
"
		 onchange="quantityChanged()" />
	</td>
</tr>
<?php if ($this->_tpl_vars['SIMPLIFIED_PRESCRIPTIONS'] && ! $this->_tpl_vars['prescription']->size): ?>
<tr style='display:none;'>
<?php else: ?>
<tr class="displaynone">
<?php endif; ?>
	<td COLSPAN="1" class="text" ALIGN="right" VALIGN="MIDDLE" ><?php echo smarty_function_xl(array('t' => 'Medicine Units'), $this);?>
</td>
	<td COLSPAN="2" ALIGN="LEFT" VALIGN="MIDDLE" >
		<input TYPE="TEXT" NAME="size" id="size" SIZE="11" MAXLENGTH="10" VALUE="<?php echo $this->_tpl_vars['prescription']->size; ?>
"/>
		<select name="unit" id="unit"><?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['prescription']->unit_array,'selected' => $this->_tpl_vars['prescription']->unit), $this);?>
</select>
	</td>
</tr>
<tr class="displaynone">
	<td COLSPAN="1" class="text" ALIGN="right" VALIGN="MIDDLE" ><?php echo smarty_function_xl(array('t' => 'Take'), $this);?>
</td>
	<td COLSPAN="2" class="text" ALIGN="LEFT" VALIGN="MIDDLE" >
<?php if ($this->_tpl_vars['SIMPLIFIED_PRESCRIPTIONS'] && ! $this->_tpl_vars['prescription']->form && ! $this->_tpl_vars['prescription']->route && ! $this->_tpl_vars['prescription']->interval): ?>
		<input TYPE="text" NAME="dosage" id="dosage" SIZE="30" MAXLENGTH="100" VALUE="<?php echo $this->_tpl_vars['prescription']->dosage; ?>
" />
		<input type="hidden" name="form" id="form" value="0" />
		<input type="hidden" name="route" id="route" value="0" />
		<input type="hidden" name="interval" id="interval" value="0" />
<?php else: ?>
		<input TYPE="TEXT" NAME="dosage" id="dosage" SIZE="2" MAXLENGTH="10" VALUE="<?php echo $this->_tpl_vars['prescription']->dosage; ?>
"/> <?php echo smarty_function_xl(array('t' => 'in'), $this);?>

		<select name="form" id="form"><?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['prescription']->form_array,'selected' => $this->_tpl_vars['prescription']->form), $this);?>
</select>
		<select name="route" id="route"><?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['prescription']->route_array,'selected' => $this->_tpl_vars['prescription']->route), $this);?>
</select>
		<select name="interval" id="interval"><?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['prescription']->interval_array,'selected' => $this->_tpl_vars['prescription']->interval), $this);?>
</select>
<?php endif; ?>
	</td>
</tr>
<!-- <tr>
	<td COLSPAN="1" class="text" ALIGN="right" VALIGN="MIDDLE" ><?php echo smarty_function_xl(array('t' => 'Refills'), $this);?>
</td>
	<td COLSPAN="2" class="text" ALIGN="LEFT" VALIGN="MIDDLE" >
		<?php echo smarty_function_html_options(array('name' => 'refills','options' => $this->_tpl_vars['prescription']->refills_array,'selected' => $this->_tpl_vars['prescription']->refills), $this);?>

<?php if ($this->_tpl_vars['SIMPLIFIED_PRESCRIPTIONS']): ?>
		<input TYPE="hidden" ID="per_refill" NAME="per_refill" VALUE="<?php echo $this->_tpl_vars['prescription']->per_refill; ?>
" />
<?php else: ?>
		&nbsp; &nbsp; # <?php echo smarty_function_xl(array('t' => 'of tablets'), $this);?>
:
		<input TYPE="TEXT" ID="per_refill" NAME="per_refill" SIZE="2" MAXLENGTH="10" VALUE="<?php echo $this->_tpl_vars['prescription']->per_refill; ?>
" />
<?php endif; ?>
	</td>
</tr> -->
<tr class="displaynone">
	<td COLSPAN="1" class="text" ALIGN="right" VALIGN="MIDDLE" ><?php echo smarty_function_xl(array('t' => 'Notes'), $this);?>
</td>
	<td COLSPAN="2" class="text" ALIGN="LEFT" VALIGN="MIDDLE" >
	<textarea name="note" cols="30" rows="2" wrap="virtual"><?php echo $this->_tpl_vars['prescription']->note; ?>
</textarea>
	</td>
</tr>
<!-- <tr>
<?php if ($this->_tpl_vars['WEIGHT_LOSS_CLINIC']): ?>
  <td COLSPAN="1" class="text" ALIGN="right" VALIGN="MIDDLE" ><?php echo smarty_function_xl(array('t' => 'Substitution'), $this);?>
</td>
  <td COLSPAN="2" ALIGN="LEFT" VALIGN="MIDDLE" >
    <?php echo smarty_function_html_options(array('name' => 'substitute','options' => $this->_tpl_vars['prescription']->substitute_array,'selected' => $this->_tpl_vars['prescription']->substitute), $this);?>

  </td>
<?php else: ?>
  <td COLSPAN="1" class="text" ALIGN="right" VALIGN="MIDDLE" ><?php echo smarty_function_xl(array('t' => 'Add to Medication List'), $this);?>
</td>
  <td COLSPAN="2" class="text" ALIGN="LEFT" VALIGN="MIDDLE" >
    <?php echo smarty_function_html_radios(array('name' => 'medication','options' => $this->_tpl_vars['prescription']->medication_array,'selected' => $this->_tpl_vars['prescription']->medication), $this);?>

    &nbsp; &nbsp;
    <?php echo smarty_function_html_options(array('name' => 'substitute','options' => $this->_tpl_vars['prescription']->substitute_array,'selected' => $this->_tpl_vars['prescription']->substitute), $this);?>

  </td>
<?php endif; ?>

</tr>-->
</table>

<input type="hidden" name="id" value="<?php echo $this->_tpl_vars['prescription']->id; ?>
" />
<input type="hidden" name="process" value="<?php echo $this->_tpl_vars['PROCESS']; ?>
" />
<script language='JavaScript'>
<?php echo $this->_tpl_vars['ENDING_JAVASCRIPT']; ?>

</script>
</form>
<div  id="prescription-order">
<?php if ($this->_tpl_vars['PREVIOUS_DRUG']): ?>
<label>Last visit prescription</label>


<table class="table table-striped">
<thead>
<th>Name </th>
<th>Time </th>
<th>Meal </th>
<th>Duration</th>
</thead>
<tbody>

<td><?php echo $this->_tpl_vars['PREVIOUS_DRUG']; ?>
 </td><td><?php echo $this->_tpl_vars['drug_intervals']; ?>
 </td><td><?php echo $this->_tpl_vars['drug_meal_time']; ?>
</td><td> <?php echo $this->_tpl_vars['duration']; ?>
</td><td> <?php echo $this->_tpl_vars['time_frame']; ?>
</td>
</tbody>
</table>
<input name="click" type="checkbox"  id="check1" onchange="cTrig()"/> Continue with same prescription
<?php endif; ?>
</div>
<?php echo '
<!-- for the fancy jQuery stuff -->
<script>
function cTrig() { 
      if (document.getElementById(\'check1\').checked == true) {
               var box= confirm("Are you sure you want to prescribe same medicines?");
        if (box==true) {
            
			document.getElementById(\'check1\').checked = true;
			    $.ajax({
        url: \'templates/prescription/save_last_prescription.php\',
        success: function(result){
        alert(JSON.stringify(result));
    }});
	document.getElementById(\'check1\').disabled = true;
        } else {
           document.getElementById(\'check1\').checked = false;
		   }
      } else {

         return false;   
      }
    }
var fiddleApp = angular.module(\'fiddleApp\', [\'ui.bootstrap\'], function($interpolateProvider) {
        $interpolateProvider.startSymbol(\'<%\');
        $interpolateProvider.endSymbol(\'%>\');
    });

fiddleApp.controller(\'Main\', [\'$scope\', \'$http\', \'$modal\', \'$log\', function($scope, $http, $modal, $log) {
    // is angular loaded and working?
    // (reduce future debugging)
 $scope.greeting = \'hello\';
    $scope.selectedValues = [];
	
	  var customer = [];
	 
		for (var i = 0, len = drug_id.length; i < len; i++) {
		for (var j = 0, lens = drug_names.length; j <= lens; j++) {
	if (i == j ) {
		
    customer.push(\'{ \' + \'"id"\' + \' : \' + \'"\' + drug_id[i] + \'"\' + \', \' + \'"name"\' + \' : \' + \'"\' + drug_names[j] + \'"\' + \' } \');
	}
  }
  }
$scope.customers = JSON.parse(\'[ \' + customer + \' ]\') ;

    $scope.$watch(\'selected\', function(nowSelected){
        $scope.selectedValues = [];
                			
        if( ! nowSelected ){
            // here we\'ve initialized selected already
            // but sometimes that\'s not the case
            // then we get null or undefined
            return;
        }
        angular.forEach(nowSelected, function(val){

            $scope.selectedValues.push( val.id );
        });

    });

$scope.search2 = function () {

var datas = $scope.selectedValues;
var req = {
 method: \'POST\',
 url: \'templates/prescription/angularpost.php\',
 headers: {
   \'Content-Type\': \'application/x-www-form-urlencoded\'
 },
 data: datas
}
 var modalInstance = $modal.open({
                templateUrl: \'templates/prescription/modal-form.php?id=\'+datas,
		 controller: ModalInstanceCtrl,
                scope: $scope,
                resolve: {
                    userForm: function () {
                        return $scope.userForm;
                    }
                }
            });
			            modalInstance.result.then(function (selectedItem) {
                $scope.selected = selectedItem;
            }, function () {
                $log.info(\'Modal dismissed at: \' + new Date());
            });
    
}

}]);
var ModalInstanceCtrl = function ($scope,$http, $modalInstance, userForm) {
    $scope.form = {}

    $scope.submitForm = function () {
        if ($scope.form.userForm.$valid) {
			var FormData = {
			\'drugId\' : $scope.selectedValues,
			\'dosagetype\' : this.form.userForm.dosagetype.$modelValue,
      \'quantity\' : this.form.userForm.name.$modelValue,
      \'units\' : this.form.userForm.username.$modelValue,
	  \'take1\' : this.form.userForm.take1.$modelValue,
	  \'take2\' : this.form.userForm.take2.$modelValue,
	  \'take3\' : this.form.userForm.take3.$modelValue,
	  \'duration\' : this.form.userForm.duration.$modelValue,
	  \'note\' : this.form.userForm.note.$modelValue,
	  \'take\' : this.form.userForm.email.$modelValue,
	  	  	  \'time_frame\' : this.form.userForm.time_frame.$modelValue,
    };
	var req = {
 method: \'POST\',
 url: \'templates/prescription/angularpost.php\',
 headers: {
   \'Content-Type\': \'application/x-www-form-urlencoded\'
 },
 data: FormData
}
            $http(req).success(function(data){
                    console.log(data);
					if(data == \'1\') {
					BootstrapDialog.alert(\'The Drug has been saved as prescription!\');
					}
					else {
					BootstrapDialog.alert(\'Oops! something went wrong please try again.\');
					}
                    }).error(function(error){
                    console.log(error);
               });
 
            $modalInstance.close(\'closed\');
        } else {
            console.log(\'userform is not in scope\');
        }
    };

    $scope.cancel = function () {
        $modalInstance.dismiss(\'cancel\');
    };
	};

// http://docs.angularjs.org/guide/bootstrap Manual Initialization
angular.bootstrap(document, [\'fiddleApp\']);
</script>
<script type="text/javascript">

function submitfun() {
    top.restoreSession();
    if (CheckForErrors(this)) {
        document.forms["prescribe"].submit();
    }
    else {
        return false;
    }
}

function iframetopardiv(string){
    var name=string
    document.getElementById(\'drug\').value=name;
    $("#hiddendiv").html( "&nbsp;" );
    $(\'#hiddendiv\').hide();
}

function cancelParlookup () {
    $(\'#hiddendiv\').hide();
    $("#hiddendiv").html( "&nbsp;" );
}

$().ready(function() {
    $("#drug").autocomplete(\'library/ajax/prescription_drugname_lookup.php\',
                            {
                            width: 200,
                            scrollHeight: 100,
                            selectFirst: true
                            });
    $("#drug").focus();
    $("#prescribe").submit(function() { return CheckForErrors(this) });
    $("#druglookup").click(function() { DoDrugLookup(this) });
});


// pop up a drug lookup window with the value of the drug name, if we have one
function DoDrugLookup(eObj) {
    drugname = "";
    if ($(\'#drug\').val() != "") { drugname = $(\'#drug\').val(); }
    $("#druglist").css(\'display\',\'block\');
    document.lookup.action=\'controller.php?prescription&edit&id=&pid=';  echo $this->_tpl_vars['prescription']->patient->id;  echo '&drug=sss\'+drugname;
    drugPopup = window.open(\'controller.php?prescription&lookup&drug=\'+drugname, \'drugPopup\', \'width=400,height=50,menubar=no,titlebar=no,left = 825,top = 400\');
    drugPopup.opener = self;
    return true;
}


// check the form for required fields before submitting
var CheckForErrors = function(eObj) {
    // REQUIRED FIELDS
    if (CheckRequired(\'drug\') == false) { return false; }
    //if (CheckRequired(\'quantity\') == false) { return false; }
    //if (CheckRequired(\'unit\') == false) { return false; }
    //if (CheckRequired(\'size\') == false) { return false; }
    //if (CheckRequired(\'dosage\') == false) { return false; }
    //if (CheckRequired(\'form\') == false) { return false; }
    //if (CheckRequired(\'route\') == false) { return false; }
    //if (CheckRequired(\'interval\') == false) { return false; }

    return top.restoreSession();
};

function CheckRequired(objID) {

    // for text boxes
    if ($(\'#\'+objID).is(\'input\')) {
        if ($(\'#\'+objID).val() == "") {
            alert("';  echo smarty_function_xl(array('t' => 'Missing a required field'), $this); echo '");
            $(\'#\'+objID).css("backgroundColor", "pink");
            return false;
        }
    }

    // for select boxes
    if ($(\'#\'+objID).is(\'select\')) {
        if ($(\'#\'+objID).val() == "0") {
            alert("';  echo smarty_function_xl(array('t' => 'Missing a required field'), $this); echo '");
            $(\'#\'+objID).css("backgroundColor", "pink");
            return false;
        }
    }

    return true;
}
var QueryString = function () {
  // This function is anonymous, is executed immediately and 
  // the return value is assigned to QueryString!
  var query_string = {};
  var query = window.location.search.substring(1);
  var vars = query.split("&");
  for (var i=0;i<vars.length;i++) {
    var pair = vars[i].split("=");
        // If first entry with this name
    if (typeof query_string[pair[0]] === "undefined") {
      query_string[pair[0]] = decodeURIComponent(pair[1]);
        // If second entry with this name
    } else if (typeof query_string[pair[0]] === "string") {
      var arr = [ query_string[pair[0]],decodeURIComponent(pair[1]) ];
      query_string[pair[0]] = arr;
        // If third or later entry with this name
    } else {
      query_string[pair[0]].push(decodeURIComponent(pair[1]));
    }
  } 

  if(!query_string.id) {
var elems = document.getElementsByClassName("displaynone");
  for (i = 0; i < elems.length; i++) {
    elems[i].style.display = \'none\';
    elems[i].style.top = \'100%\';
  }
  }
  else {
  document.getElementsByClassName("displayblock").style.display = \'none\';
  }
  return query_string;
}();
</script>
'; ?>


</html>