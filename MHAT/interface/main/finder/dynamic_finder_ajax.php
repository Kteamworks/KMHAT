<?php
// Copyright (C) 2012 Rod Roark <rod@sunsetsystems.com>
// Sponsored by David Eschelbacher, MD
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// Sanitize escapes and disable fake globals registration.
//
$sanitize_all_escapes = true;
$fake_register_globals = false;

require_once("../../globals.php");
require_once("$srcdir/formdata.inc.php");
require_once("$srcdir/formatting.inc.php");
require_once("$srcdir/jsonwrapper/jsonwrapper.php");

$popup = empty($_REQUEST['popup']) ? 0 : 1;

// With the ColReorder or ColReorderWithResize plug-in, the expected column
// ordering may have been changed by the user.  So we cannot depend on
// list_options to provide that.
//
$aColumns = explode(',', $_GET['sColumns']);

// Paging parameters.  -1 means not applicable.
//
$iDisplayStart  = isset($_GET['iDisplayStart' ]) ? 0 + $_GET['iDisplayStart' ] : -1;
$iDisplayLength = isset($_GET['iDisplayLength']) ? 0 + $_GET['iDisplayLength'] : -1;
$limit = '';
if ($iDisplayStart >= 0 && $iDisplayLength >= 0) {
  $limit = "LIMIT " . escape_limit($iDisplayStart) . ", " . escape_limit($iDisplayLength);
}

// Column sorting parameters.
//
$orderby = '';
if (isset($_GET['iSortCol_0'])) {
	for ($i = 0; $i < intval($_GET['iSortingCols']); ++$i) {
    $iSortCol = intval($_GET["iSortCol_$i"]);
		if ($_GET["bSortable_$iSortCol"] == "true" ) {
      $sSortDir = escape_sort_order($_GET["sSortDir_$i"]); // ASC or DESC
      // We are to sort on column # $iSortCol in direction $sSortDir.
      $orderby .= $orderby ? ', ' : 'ORDER BY ';
      //
      if ($aColumns[$iSortCol] == 'name') {
        //$orderby .= "fname $sSortDir, lname $sSortDir, mname $sSortDir";
		$orderby.="genericname1 desc";
      }
      else {
        $orderby .= "`" . escape_sql_column_name($aColumns[$iSortCol],array('patient_data','form_encounter')) . "` $sSortDir";
      }
		}
	}
}

// Global filtering.
//
$where = '';
if (isset($_GET['sSearch']) && $_GET['sSearch'] !== "") {
  $sSearch = add_escape_custom($_GET['sSearch']);
  foreach ($aColumns as $colname) {
    $where .= $where ? "OR " : "WHERE ( ";
    if ($colname == 'name') {
      $where .=
        "fname LIKE '$sSearch%' OR " .
        "lname LIKE '$sSearch%' OR " .
        "mname LIKE '$sSearch%' ";
    }	
    else {
      $where .= "`" . escape_sql_column_name($colname,array('patient_data','form_encounter')) . "` LIKE '$sSearch%' ";
    }
  }
  if ($where) $where .= ")";
  
}

// Column-specific filtering.
//
for ($i = 0; $i < count($aColumns); ++$i) {
  $colname = $aColumns[$i];
  if (isset($_GET["bSearchable_$i"]) && $_GET["bSearchable_$i"] == "true" && $_GET["sSearch_$i"] != '') {
    $where .= $where ? ' AND' : 'WHERE';
    $sSearch = add_escape_custom($_GET["sSearch_$i"]);
    if ($colname == 'name') {
      $where .= " ( " .
        "fname LIKE '$sSearch%' OR " .
        "lname LIKE '$sSearch%' OR " .
        "mname LIKE '$sSearch%' )";
    }
    else {
      $where .= " `" . escape_sql_column_name($colname,array('patient_data','form_encounter')) . "` LIKE '$sSearch%'";
    }
  }
}

// Compute list of column names for SELECT clause.
// Always includes pid because we need it for row identification.
//
$sellist = 'pid';
foreach ($aColumns as $colname) {
  if ($colname == 'pid') continue;
  $sellist .= ", ";
  if ($colname == 'name') {
    $sellist .= "fname, lname, mname";
  }
  else {
    $sellist .= "`" . escape_sql_column_name($colname,array('patient_data','form_encounter',)) . "`";
  }
}

// Get total number of rows in the table.
//
$user_facility=sqlStatement("SELECT * from users_facility where table_id='".$_SESSION['authUserID']."'");
$X=array();
 while($row3=sqlFetchArray($user_facility))
{
  $X[]=$row3['facility_id'];
	
}
$X = "'" . implode("', '", $X) . "'";
$user_log=sqlStatement("SELECT * from users where id='".$_SESSION['authUserID']."'");
$user_log1=sqlFetchArray($user_log);
$user_log2=$user_log1['facility_log'];
if($user_log2==1)
{
$row = sqlQuery("SELECT COUNT(id) AS count FROM patient_data where facility_id='".$_SESSION['facility_id']."'");
}else
{
	$row = sqlQuery("SELECT COUNT(id) AS count FROM patient_data where facility_id IN ($X)");
}
$iTotal = $row['count'];

// Get total number of rows in the table after filtering.
//
if($where)
{
	if($user_log2==1){
	$row = sqlQuery("SELECT COUNT(id) AS count FROM patient_data $where AND facility_id='".$_SESSION['facility_id']."'");
	}else
		
		{
			$row = sqlQuery("SELECT COUNT(id) AS count FROM patient_data $where AND facility_id IN ($X)");
		}
	//$sqlQuery = "SELECT $sellist FROM patient_data  $where AND facility_id='".$_SESSION['facility_id']."' $orderby  $limit ";
}else
	if($user_log2==1)
{
$row = sqlQuery("SELECT COUNT(id) AS count FROM patient_data  where facility_id='".$_SESSION['facility_id']."'");
}else
{
	$row = sqlQuery("SELECT COUNT(id) AS count FROM patient_data  where facility_id IN ($X)");
	
}
$iFilteredTotal = $row['count'];

// Build the output data array.
//
$out = array(
  "sEcho"                => intval($_GET['sEcho']),
  "iTotalRecords"        => $iTotal,
  "iTotalDisplayRecords" => $iFilteredTotal,
  "aaData"               => array()
);
if($where)
{
	if($user_log2==1){
	$query = "SELECT $sellist FROM patient_data  $where AND facility_id='".$_SESSION['facility_id']."'  $orderby  $limit ";
	}else
	{
		$query = "SELECT $sellist FROM patient_data  $where AND facility_id IN ($X)  $orderby  $limit ";
	}
	//var_dump($query);
	
}else if($user_log2==1)
{
$query = "SELECT $sellist FROM patient_data   where facility_id='".$_SESSION['facility_id']."' $orderby  $limit ";
}else
{
$query = "SELECT $sellist FROM patient_data   where facility_id IN ($X) $orderby  $limit ";	
}
$res = sqlStatement($query);
while ($row = sqlFetchArray($res)) {
  // Each <tr> will have an ID identifying the patient.
  $arow = array('DT_RowId' => 'pid_' . $row['pid']);
  foreach ($aColumns as $colname) {
    if ($colname == 'name') {
      $name = $row['fname'];
      if ($name && $row['lname']) $name .= ' ';
      if ($row['lname']) $name .= $row['lname'];
      if ($row['mname']) $name .= ' ' . $row['mname'];
      $arow[] = $name;
    }
    else if ($colname == 'DOB' || $colname == 'regdate' || $colname == 'ad_reviewed' || $colname == 'userdate1') {
      $arow[] = oeFormatShortDate($row[$colname]);
    }
    else {
      $arow[] = $row[$colname];
    }
  }
  $out['aaData'][] = $arow;
}

// error_log($query); // debugging

// Dump the output array as JSON.
//
echo json_encode($out);
?>
