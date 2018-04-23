<?php 
include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");
include_once("$srcdir/encounter.inc");
require_once("$srcdir/formdata.inc.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/acl.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/formatting.inc.php");
require_once("$srcdir/erx_javascript.inc.php");

?>
<html>
<head>
<link rel="canonical" href="http://www.example.com" />
<script src="https://apis.google.com/js/platform.js" async defer></script>
</head>
<body>
    <g:hangout render="createhangout"
        invites="[
		
		{ id : '<?php echo $email_id?>', invite_type : 'EMAIL' },
          { id :'pavithras@gmail.com', invite_type : 'EMAIL' }]"
    </g:hangout>
<?php
	echo sqlInsert("insert into hangout set date=now(),user='".$_SESSION['authUser']."'");
	?>

</body>
</html>