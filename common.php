<?php
/**
 * Common functions for the project
 */

/**
 * Returns the array of all the variables passed
 * 
 * @return array Array of the passed vars
 */
function getPassedVars()
{
    $array = $_GET;
    $array = array_merge($array, $_POST);
    return $array;
}

/**
 * Generates the result in JSON format
 * 
 * @param Object $object <p>The object to be passed as a result</p>
 * @param string $code [optional]<p>The code to be returned as a result</p>
 */
function generateJson($object, $code=200) {
    $resObj = array("code" => $code, "result" => $object);
    return json_encode($resObj);
}

?>
