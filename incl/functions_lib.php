<?php

require "config_incl.php";



/**
 * a generic HTTP requester
 * 
 * @param  string $url (URL to be opened)
 * @return string      (HTTP response)
 */
function curlFetch($url)
{
	$curl = curl_init();

	curl_setopt_array($curl, array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "GET",
		CURLOPT_HTTPHEADER => array(
			"cache-control: no-cache"
		),
	));

	$response = curl_exec($curl);
	$err = curl_error($curl);

	curl_close($curl);

	if ($err) {
	  echo "cURL Error #:" . $err;
	} 
	return $response;
}


/**
 * find player's ID by name
 * 
 * @param  string $player_name 	(player's in-game name)
 * @return integer              (playerID)
 */	
function getPlayerID($player_name)
{
	global $application_id, $API_base;

	$url = $API_base . "wot/account/list/?application_id={$application_id}&search={$player_name}";
	$API_response = curlFetch($url);
	$json = json_decode($API_response);

	$account_id = $json->data[0]->account_id;

	return $account_id;
}


/**
 * fetch all player data
 * 
 * @param  string $account_id 	(playerID)
 * @return JSON             	(player's whole JSON object)
 */	
function getPlayerData($account_id){
	global $application_id, $API_base;

	$url = $API_base . "wot/account/info/?application_id={$application_id}&account_id={$account_id}";
	$API_response = curlFetch($url);
	$json = json_decode($API_response);

	$player_data = $json->data->$account_id;

	return $player_data;
}


/**
 * fetch player's progress on each tank
 * 
 * @param  string $account_id 	(playerID)
 * @return JSON             	(tanks' whole JSON object)
 */
function getTankStatistics($account_id){
	global $application_id, $API_base;

	$url = $API_base . "wot/tanks/stats/?application_id={$application_id}&account_id={$account_id}";
	$API_response = curlFetch($url);
	$json = json_decode($API_response);

	$tank_stats = $json->data->$account_id;

	return $tank_stats;
}


/**
 * fetch all tank info from WarGaming's tankopedia
 * 
 * @return JSON 		(tankopedia's JSON object)
 */
function getTankDetails(){
	global $application_id, $API_base;

	$url = $API_base . "wot/encyclopedia/vehicles/?application_id={$application_id}";
	$API_response = curlFetch($url);
	$json = json_decode($API_response);
// echo $API_response."<br />";
	$tank_details = $json->data;

	return $tank_details;
}


/**
 * fetch latest expected tank stats for WN8 calculation
 * 
 * @return array 		(Returns expected stats for all known tanks.)
 */
function getExpectedTankValues(){
	$url = "http://www.wnefficiency.net/exp/expected_tank_values_30.json";
	$API_response = curlFetch($url);
	$json = json_decode($API_response);
	$temp = array_values($json->data);
	$exp_tank_stats = [];

	foreach ($temp as $key => $val) {
		$exp_tank_stats[$val->IDNum] = $val;
	}

	return $exp_tank_stats;
}


/**
 * fetch all clan related info from WarGaming API
 * 
 * @param  string $clan_id 	(player's clan ID)
 * @return JSON          	(clan's entire JSON object (if applicable). Returns false if player is not member of a clan)
 */
function getClanData($clan_id){
	global $application_id, $API_base;

	$url = $API_base . "wgn/clans/info/?application_id={$application_id}&clan_id={$clan_id}";
	$API_response = curlFetch($url);
	$json = json_decode($API_response);

	$clan_data = $json->data->$clan_id;

	return $clan_data;
}


/**
* Convert a hexa decimal color code to its RGB equivalent
*
* @param string $hexStr 			(hexadecimal color value)
* @param boolean $returnAsString 	(if set true, returns the value separated by the separator character. Otherwise returns associative array)
* @param string $seperator 			(to separate RGB values. Applicable only if second parameter is true.)
* @return array or string 			(depending on second parameter. Returns False if invalid hex color value)
*/                                                                                                 
function hex2RGB($hexStr, $returnAsString = false, $seperator = ',') {
    $hexStr = preg_replace("/[^0-9A-Fa-f]/", '', $hexStr); // Gets a proper hex string
    $rgbArray = array();
    if (strlen($hexStr) == 6) { //If a proper hex code, convert using bitwise operation. No overhead... faster
        $colorVal = hexdec($hexStr);
        $rgbArray['red'] = 0xFF & ($colorVal >> 0x10);
        $rgbArray['green'] = 0xFF & ($colorVal >> 0x8);
        $rgbArray['blue'] = 0xFF & $colorVal;
    } elseif (strlen($hexStr) == 3) { //if shorthand notation, need some string manipulations
        $rgbArray['red'] = hexdec(str_repeat(substr($hexStr, 0, 1), 2));
        $rgbArray['green'] = hexdec(str_repeat(substr($hexStr, 1, 1), 2));
        $rgbArray['blue'] = hexdec(str_repeat(substr($hexStr, 2, 1), 2));
    } else {
        return false; //Invalid hex color code
    }
    return $returnAsString ? implode($seperator, $rgbArray) : $rgbArray; // returns the rgb string or the associative array
}


/**
 * get base multiplier for progressbar
 * 
 * @param  integer $maxVal  (maximum value progressbar can get)
 * @param  integer $currVal (current value of the progressbar)
 * @param  integer $barSize (width of the progressbar to calculate progress for)
 * @return integer          (decimal value (pixel increment) to extend the progressbar)
 */
function calculateProgress($maxVal, $currVal, $barSize) {
	$ratio = $currVal / $maxVal;
	return round($barSize*$ratio);
}

?>