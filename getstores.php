<?php
mysql_connect('localhost', 'huddle', 'V86cc1Ht') or die('Cannot connect to DB. ' . mysql_error());
mysql_select_db('android') or die(mysql_error());

// Reading the required city's name to pass to 2GIS API
$cityId = is_numeric($_GET["cityid"]) ? $_GET["cityid"] : 1;
$cityname = getCityName($cityId);
if($cityname == "")
	die("Cannot find city name for id=$cityId<br>");
$url = "http://catalog.api.2gis.ru/searchinrubric?what=%D0%90%D0%BF%D1%82%D0%B5%D0%BA%D0%B8&where=$cityname&rubrics=&version=1.3&key=ruokep8523&output=json&pagesize=50&page=";
$response = file_get_contents($url . 1);
$obj = json_decode($response);
$total = (int)$obj->total;

$pages = ceil($total / 50);
$prevDataSet = getLastData($cityId);
$lastData = $prevDataSet + 1;
$count = 0;
$results = array('a' => 0, 'u' => 0, 'r' => 0, 'i' => 0);
$storeIds = array();
for($i = 1; $i <= $pages; $i++){
	$response = file_get_contents($url . $i);
	$obj = json_decode($response);
	$stores = $obj->result;
	foreach($stores as $store){
		if($store->lat == "" || $store->lon == "") 
			continue;
		$r = updateInsertStore($store, $lastData, $cityId);		
		array_push($storeIds, $store->id);
		$results[$r]++;
		$count++;
	}
}
$results['r'] = markRemovedOthers($storeIds, $lastData, $cityId);
echo "$cityname (" . date("d.m.Y H:m:s") . "): $count records have been processed:<br><br>";
if(array_sum($results) - $results['i'] > 0){
	setLastData($cityId, $lastData);
} else {
	$lastData = $prevDataSet;
}
echo $results['a'] . " records were added;<br> ".
	$results['u'] . " records were updated;<br> ".
	$results['r'] . " records were marked removed;<br> ".
	$results['i'] . " records were not changed.<br><br>
	The dataset number is $lastData, " . ($prevDataSet != $lastData ? "changed from $prevDataSet." : "not changed.");
mysql_close();

function getCityName($id){
	$query = "SELECT name FROM cities WHERE id=" . (is_numeric($id) ? $id: 1);
	$result = mysql_query($query) or die($query . "<br>" . mysql_error());
	$row = mysql_fetch_assoc($result);
	return count($row) > 0 ? $row["name"] : "";
}

function getLastData($_cityId){
	//$query = "SELECT MAX(data) as data from stores where city=$_cityId";
	$query = "SELECT data from data_versions where city=$_cityId";
	$result = mysql_query($query) or die($query . "<br>" . mysql_error());
	$row = mysql_fetch_assoc($result);
	if(mysql_num_rows($result) == 0)
		setLastData($_cityId, 1);
	return mysql_num_rows($result) > 0 ? $row["data"] : 1;
}

function setLastData($_cityId, $lastData){
	$query = "SELECT data from data_versions where city=$_cityId";
	$result = mysql_query($query) or die($query . "<br>" . mysql_error());
	if(mysql_num_rows($result) > 0)
		$query = "UPDATE data_versions set data=$lastData, date=NOW() where city=$_cityId";
	else
		$query = "INSERT INTO data_versions (city, data, date) values($_cityId, $lastData, NOW())";
	mysql_query($query) or die($query . "<BR>" . mysql_error());
}

function getShortNames(){
	$arr = array("nm" => "name",
			"adr" => "address",
			"sid" => "id",
			"gid" => "groupId");
	return $arr;
}

function markRemovedOthers($storesToStay, $lastData, $cityId){
	$query = "UPDATE stores SET deleted=TRUE, data=$lastData WHERE city=$cityId AND (sid NOT IN (" . implode(',', $storesToStay) .")) AND deleted<>TRUE";
	$result = mysql_query($query) or die($query . "<br>" . mysql_error());
	return mysql_affected_rows();
}

function updateInsertStore($store, $lastData, $cityId){
	if($store->firm_group)
		$groupId = $store->firm_group->id;
	else
		$groupId = "";
	$shortNames = getShortNames();
	$query = "SELECT * FROM stores WHERE sid=$store->id AND city=$cityId AND deleted<>TRUE";
	$result = mysql_query($query) or die($query . "<br>" . mysql_error());
	$row = mysql_fetch_assoc($result);
	$query = "";
	$act = "i";
	if(mysql_num_rows($result) > 0)
	{
		// If the store already saved in the databse
		foreach($row as $fName => $fValue){
			if($fName == "id")
				continue;
			$sName = $shortNames[$fName];
			if(($sName != null && $store->$sName !== $fValue && $store->$sName != "") || ($store->$fName !== $fValue && $store->$fName != "")){
				$query = "UPDATE stores SET lat='$store->lat', lon='$store->lon', nm='$store->name', adr='$store->address', gid='$groupId', city=$cityId, data=$lastData WHERE sid=$store->id AND city=$cityId";
				$act = 'u';
				break;
			}
		}
	}else{		
		$query = "INSERT INTO stores (sid, lat, lon, nm, adr, gid, city, data, deleted) VALUES ('$store->id', '$store->lat', '$store->lon', '$store->name', '$store->address', '$groupId', '$cityId', '$lastData', 'false')";
		$act = 'a';
	}
	if($query!=""){
		$res = mysql_query($query);
		if(!$res)
			echo($query . "<br>" . mysql_error());
	}
	return $act;
}

?>
