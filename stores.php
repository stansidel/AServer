<?php
/**
 * Include the common fucntions file
 */
include_once 'common.php';

// http://php.net/manual/en/book.outcontrol.php
if(extension_loaded('zlib'))
	$t = ob_start('ob_gzhandler');
mysql_connect('localhost', 'android', 'GhbdtnAndroid') or die('Cannot connect to DB. ' . mysql_error());
mysql_select_db('android') or die(mysql_error());

$vars = getPassedVars();

$number = $vars["number"];
$groupId = mysql_real_escape_string($vars["groupId"]);
$cityId = $vars["cityid"];
if(!is_numeric($cityId))
	die("City id must be provided.");
$data = $vars["data"];
if(!is_numeric($data))
	$data = 0;

//$query = "SELECT * FROM stores WHERE stores.lon <> '' AND stores.lat <> '' " . (($groupId != "") ? " AND stores.groupId = '$groupId'" : "") . ((is_numeric($number)) ? "LIMIT 0,$number" : "");

$query = "SELECT * FROM stores WHERE stores.lon <> '' AND stores.lat <> '' AND data > $data AND city=$cityId " . (($groupId != "") ? " AND stores.groupId = '$groupId'" : "") . ((is_numeric($number)) ? " LIMIT 0,$number" : "");

$result = mysql_query($query) or die(mysql_error());

$rows = array();
$count = 0;
$struct = array("nm"=>"", "sid"=>"", "gid"=>"", "lat"=>"", "lon"=>"", "act"=>"","adr"=>"");
while($r = mysql_fetch_assoc($result)){
	//$rows[] = $r;
	foreach($r as $fName => $fValue)
		if(array_key_exists($fName, $struct))
			$struct[$fName] = $fValue;
	// If the element marked deleted, the app should delete it, otherwise add or update it
	$struct['act'] = ($r['deleted'] ? "2" : "1");
	$rows[] = $struct;
}
$result = array("ld" => getLastData($cityId), "results" => $rows);

$out = json_encode($result);

echo $out;

mysql_close();

function getLastData($_cityId){
	//$query = "SELECT MAX(data) as data from stores where city=$_cityId";
	$query = "SELECT data from data_versions where city=$_cityId";
	$result = mysql_query($query) or die($query . "<br>" . mysql_error());
	$row = mysql_fetch_assoc($result);
	return mysql_num_rows($result) > 0 ? $row["data"] : 1;
}
?>
