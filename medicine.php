<?php

/**
 * Make two-level search of medicine items
 */

/**
 * Include the common fucntions file
 */
include_once 'common.php';

/**
 * A medicine item 
 */
class Item
{
    /**
     * The id of the item in the database
     * @var string
     */
    public $id;
    /**
     * Representation (name) of the item
     * @var string
     */
    public $name;
    
    function __construct($id, $name) {
        $this->id = $id;
        $this->name = $name;
    }
    
}

/**
 * Finds all the items corresponding to the search term
 * 
 * @param string $searchTerm <p>The search term, wich should be met in the items names</p>
 * @param int $limitation [optional] <p>The maximum number of items returned</p>
 * 
 * @return array An array of the found items
 */
function getItemsByTerm($searchTerm, $limitation = 0)
{
    // TODO Write the real code to search the items
    $array = array(new Item("12", "Some name"), new Item("13", "Another name"), new Item("14", "The third element"));
    return $array;
}

/**
 * Returns the array of availability of an item in stores
 * 
 * @param string $itemId Id of the item to find
 * @param string $cityId Id of the city to search in
 * 
 * @return array Array of the stores and available qualities.
 * Each element: 
 * - sid - store id,
 * - q - quantity,
 * - p - price
 */
function getItemsAvailability($itemId, $cityId) {
    // TODO Return the number of items at each store
    $array = array();
    $array[] = array("sid"=>"31", "q"=>12, "p"=>100);
    $array[] = array("sid"=>"32", "q"=>11.5, "p"=>110);
    $array[] = array("sid"=>"33", "q"=>13.75, "p"=>109);
    return $array;
}

/**
 * Checks the var to be set and returns the information about the missing var and cancels the execution if it's not set
 * 
 * @param array $varArray Array of the vars
 * @param string $varName Name of the variable to be checked 
 */
function checkVar($varArray, $varName)
{
    if(!isset($varArray[$varName])){
        die(generateJson(array("error" => "$varName is not set"), 411));
    }
}

/**
 * Returns the not-implemented exception
 */
function notImplemented() {
    die(generateJson(array("error" => "Not implemented"), 412));
}

if(extension_loaded('zlib'))
	$t = ob_start('ob_gzhandler');

$vars = getPassedVars();

//checkVar($vars, "level");
if($vars['level'] == "2") {
    checkVar($vars, "itemid");
    checkVar($vars, "cityid");
    $availability = getItemsAvailability($vars['itemid'], $vars['cityid']);
    echo generateJson($availability);
} else {
    checkVar($vars, "q");
    $items = getItemsByTerm($vars['q'], (isset($vars['limit']) ? $vars['limit'] : 0));
    echo generateJson($items);
}

?>
