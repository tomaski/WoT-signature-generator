<?php


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
	global $config;

	$url          = $config['API']['URL'] . "wot/account/list/?application_id={$config['API']['ID']}&search={$player_name}";
	$API_response = curlFetch($url);
	$json         = json_decode($API_response);
	
	$account_id   = $json->data[0]->account_id;

	return $account_id;
}


/**
 * fetch all player data
 * 
 * @param  string $account_id 	(playerID)
 * @return JSON             	(player's whole JSON object)
 */	
function getPlayerData($account_id){
	global $config;

	$url          = $config['API']['URL'] . "wot/account/info/?application_id={$config['API']['ID']}&account_id={$account_id}";
	$API_response = curlFetch($url);
	$json         = json_decode($API_response);
	
	$player_data  = $json->data->$account_id;

	return $player_data;
}


/**
 * fetch player's progress on each tank
 * 
 * @param  string $account_id 	(playerID)
 * @return JSON             	(tanks' whole JSON object)
 */
function getTankStatistics($account_id){
	global $config;

	$url            = $config['API']['URL'] . "wot/tanks/stats/?application_id={$config['API']['ID']}&account_id={$account_id}";
	$API_response   = curlFetch($url);
	$json           = json_decode($API_response);
	
	$tank_stats     = $json->data->$account_id;
	$exp_tank_stats = getExpectedTankValues();

	foreach($tank_stats as $tank){
		$tank->wn8 = calculateTankWN8($tank->all, $exp_tank_stats[$tank->tank_id]);
	}

	return $tank_stats;
}


/**
 * fetch all tank info from WarGaming's tankopedia
 * 
 * @return JSON 		(tankopedia's JSON object)
 */
function getTankDetails(){
	global $config;

	$url          = $config['API']['URL'] . "wot/encyclopedia/vehicles/?application_id={$config['API']['ID']}";
	$API_response = curlFetch($url);
	$json         = json_decode($API_response);
	
	$tank_details = $json->data;

	return $tank_details;
}


/**
 * fetch latest expected tank stats for WN8 calculation
 * 
 * @return array 		(Returns expected stats for all known tanks.)
 */
function getExpectedTankValues(){
	// $url 		= "http://www.wnefficiency.net/exp/expected_tank_values_30.json";
	$url            = "https://static.modxvm.com/wn8-data-exp/json/wn8exp.json"; // switched to XVM expected tank values
	$API_response   = curlFetch($url);
	$json           = json_decode($API_response);
	$temp           = array_values($json->data);
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
	global $config;

	$url          = $config['API']['URL'] . "wgn/clans/info/?application_id={$config['API']['ID']}&clan_id={$clan_id}";
	$API_response = curlFetch($url);
	$json         = json_decode($API_response);
	
	$clan_data    = $json->data->$clan_id;

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
	$hexStr   = preg_replace("/[^0-9A-Fa-f]/", '', $hexStr); // Gets a proper hex string
	$rgbArray = array();

    if (strlen($hexStr) == 6) { //If a proper hex code, convert using bitwise operation. No overhead... faster
		$colorVal          = hexdec($hexStr);
		$rgbArray['red']   = 0xFF & ($colorVal >> 0x10);
		$rgbArray['green'] = 0xFF & ($colorVal >> 0x8);
		$rgbArray['blue']  = 0xFF & $colorVal;
    } elseif (strlen($hexStr) == 3) { //if shorthand notation, need some string manipulations
		$rgbArray['red']   = hexdec(str_repeat(substr($hexStr, 0, 1), 2));
		$rgbArray['green'] = hexdec(str_repeat(substr($hexStr, 1, 1), 2));
		$rgbArray['blue']  = hexdec(str_repeat(substr($hexStr, 2, 1), 2));
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


/**
 * WN8 calculation for a single tank
 * @param  object $tank_player   (single object containing player stats for selected tank)
 * @param  object $tank_expected (single object with expected values for selected tank)
 * @return integer               (decimal WN8 value)
 */
function calculateTankWN8($tank_player, $tank_expected){
	$rDAMAGE 	= ($tank_player->damage_dealt 				/ $tank_player->battles) / $tank_expected->expDamage;
	$rSPOT 		= ($tank_player->spotted 					/ $tank_player->battles) / $tank_expected->expSpot;
	$rFRAG 		= ($tank_player->frags 						/ $tank_player->battles) / $tank_expected->expFrag;
	$rDEF 		= ($tank_player->dropped_capture_points 	/ $tank_player->battles) / $tank_expected->expDef;
	$rWIN 		= ($tank_player->wins * 100					/ $tank_player->battles) / $tank_expected->expWinRate;


	$rDAMAGEc	= max(0,						($rDAMAGE	- 0.22) / (1 - 0.22) );
	$rSPOTc		= max(0, min($rDAMAGEc + 0.1,  	($rSPOT		- 0.38) / (1 - 0.38)));
	$rFRAGc		= max(0, min($rDAMAGEc + 0.2,  	($rFRAG		- 0.12) / (1 - 0.12)));
	$rDEFc		= max(0, min($rDAMAGEc + 0.1,  	($rDEF		- 0.10) / (1 - 0.10)));
	$rWINc		= max(0,						($rWIN		- 0.71) / (1 - 0.71) );
	
	$WN8 =  980 * $rDAMAGEc;
	$WN8 += 210 * $rDAMAGEc * $rFRAGc;
	$WN8 += 155 * $rFRAGc	* $rSPOTc;
	$WN8 += 75  * $rDEFc	* $rFRAGc;
	$WN8 += 145 * MIN(1.8,	  $rWINc);

	return round($WN8);
}


function calculateTankClassWN8($tank_player, $tank_expected){
	$rDAMAGE 	= $tank_player->damage_dealt 				 / $tank_expected->expDamage;
	$rSPOT 		= $tank_player->spotted 					 / $tank_expected->expSpot;
	$rFRAG 		= $tank_player->frags 						 / $tank_expected->expFrag;
	$rDEF 		= $tank_player->dropped_capture_points 	    / $tank_expected->expDef;
	$rWIN 		= $tank_player->wins						/ ($tank_expected->expWinRate * 0.01);


	$rDAMAGEc	= max(0,						($rDAMAGE	- 0.22) / (1 - 0.22) );
	$rSPOTc		= max(0, min($rDAMAGEc + 0.1,  	($rSPOT		- 0.38) / (1 - 0.38)));
	$rFRAGc		= max(0, min($rDAMAGEc + 0.2,  	($rFRAG		- 0.12) / (1 - 0.12)));
	$rDEFc		= max(0, min($rDAMAGEc + 0.1,  	($rDEF		- 0.10) / (1 - 0.10)));
	$rWINc		= max(0,						($rWIN		- 0.71) / (1 - 0.71) );
	
	$WN8 =  980 * $rDAMAGEc;
	$WN8 += 210 * $rDAMAGEc * $rFRAGc;
	$WN8 += 155 * $rFRAGc	* $rSPOTc;
	$WN8 += 75  * $rDEFc	* $rFRAGc;
	$WN8 += 145 * MIN(1.8,	  $rWINc);

	return round($WN8);
}


?>