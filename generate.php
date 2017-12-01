<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

error_reporting(E_ALL);
ini_set('display_errors', 1);

$config = include 'incl/config_incl.php';
include 'incl/colors_lib.php';
include 'incl/functions_lib.php';
include 'incl/signature_lib.php';
include 'incl/wn8_lib.php';


/**
 * fetch an image resource from a given url
 * @param  string $icon_url (cURL compatible url string)
 * @return string           (binary representation of the downloaded image)
 */
function getClanIcon($icon_url){
	$ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $icon_url); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

/**
 * main function for building signature. Given playerID, it will fill in all required labels and images
 * @param  string $player_id 	(wargaming playerID)
 * @return null            		(nothing to be returned)
 */	
function make_signature($player_id){
	// fetch config with default values and create a signature class object 
	global $config;
	$labels = new signature($player_id);
	$max_bar_val = max($labels->getValue("btl_lt"),$labels->getValue("btl_mt"),$labels->getValue("btl_ht"),$labels->getValue("btl_td"),$labels->getValue("btl_spg"));
	$bar_size 	 = $config['bars']['size'];

	// load all needed icons: signature background, clan logo, player's rank in clan, tank class wn8 progressbars
	$signature = imagecreatefrompng('img/background.png');
	$icon = [
		'clan' => imagecreatefromstring(getClanIcon($labels->getValue("clan_icon"))),
		'rank' => imagecreatefrompng('img/epaulettes/' . $labels->getValue("clan_rank") . '.png')
	];
	$pb = [
		'lightTank'	=> [
			'left'     		=> imagecreatefrompng('img/wn8_bars/'. wn8_name::getter($labels->getValue('wn8_lt'))  .'_left.png'),
			'middle'   		=> imagecreatefrompng('img/wn8_bars/'. wn8_name::getter($labels->getValue('wn8_lt')) .'_middle.png'),
			'right'    		=> imagecreatefrompng('img/wn8_bars/'. wn8_name::getter($labels->getValue('wn8_lt')) .'_right.png'),
			'progress' 		=> calculateProgress($max_bar_val, $labels->getValue("btl_lt"), $bar_size),
		],
		'mediumTank'=> [
			'left'     		=> imagecreatefrompng('img/wn8_bars/'. wn8_name::getter($labels->getValue('wn8_mt')) .'_left.png'),
			'middle'   		=> imagecreatefrompng('img/wn8_bars/'. wn8_name::getter($labels->getValue('wn8_mt')) .'_middle.png'),
			'right'    		=> imagecreatefrompng('img/wn8_bars/'. wn8_name::getter($labels->getValue('wn8_mt')) .'_right.png'),
			'progress' 		=> calculateProgress($max_bar_val, $labels->getValue("btl_mt"), $bar_size),
		],
		'heavyTank' => [
			'left'     		=> imagecreatefrompng('img/wn8_bars/'. wn8_name::getter($labels->getValue('wn8_ht')) .'_left.png'),
			'middle'   		=> imagecreatefrompng('img/wn8_bars/'. wn8_name::getter($labels->getValue('wn8_ht')) .'_middle.png'),
			'right'    		=> imagecreatefrompng('img/wn8_bars/'. wn8_name::getter($labels->getValue('wn8_ht')) .'_right.png'),
			'progress' 		=> calculateProgress($max_bar_val, $labels->getValue("btl_ht"), $bar_size),
		],
		'AT-SPG' 	=> [
			'left'     		=> imagecreatefrompng('img/wn8_bars/'. wn8_name::getter($labels->getValue('wn8_td')) .'_left.png'),
			'middle'   		=> imagecreatefrompng('img/wn8_bars/'. wn8_name::getter($labels->getValue('wn8_td')) .'_middle.png'),
			'right'    		=> imagecreatefrompng('img/wn8_bars/'. wn8_name::getter($labels->getValue('wn8_td')) .'_right.png'),
			'progress' 		=> calculateProgress($max_bar_val, $labels->getValue("btl_td"), $bar_size),
		],
		'SPG' 		=> [
			'left'     		=> imagecreatefrompng('img/wn8_bars/'. wn8_name::getter($labels->getValue('wn8_spg')) .'_left.png'),
			'middle'   		=> imagecreatefrompng('img/wn8_bars/'. wn8_name::getter($labels->getValue('wn8_spg')) .'_middle.png'),
			'right'    		=> imagecreatefrompng('img/wn8_bars/'. wn8_name::getter($labels->getValue('wn8_spg')) .'_right.png'),
			'progress' 		=> calculateProgress($max_bar_val, $labels->getValue("btl_spg"), $bar_size),
		],
	];	

	// put all icons on their designated places
	foreach($config['icons'] as $key => $val) {
		imagecopy($signature, $icon[$key], $val['posX'], $val['posY'], 0, 0, imagesx($icon[$key]), imagesy($icon[$key]));
	}
	// calculate "progress" for each tank class and draw it accordingly
	foreach ($config['bars']['posY'] as $key => $val) {
		// right end (start of progress): pre-set X-Y offset from signature right-top border
		imagecopy($signature, $pb[$key]['right'], $config['bars']['posX'], $val, 0, 0, imagesx($pb[$key]['right']), imagesy($pb[$key]['right']));
		$progr_x = 0;
		while ($progr_x < $pb[$key]['progress']) {
			// middle part (the actual progress): pre-set Y offset from top and X offset from right + the progress value
			imagecopy($signature, $pb[$key]['middle'], $config['bars']['posX'] - $progr_x, $val, 0, 0, imagesx($pb[$key]['middle']), imagesy($pb[$key]['middle']));
			$progr_x += imagesx($pb[$key]['middle']);
		}
		// left part (finishing touch; end of progress): pre-set Y offset from top and X offset from right + the progress value + width on the left progressbar part
		imagecopy($signature, $pb[$key]['left'], $config['bars']['posX'] - $progr_x - imagesx($pb[$key]['left']) + imagesx($pb[$key]['middle']), $val, 0, 0, imagesx($pb[$key]['left']), imagesy($pb[$key]['left']));
	}


	// fill in all text labels (and color them if necessary)
	foreach($config['labels'] as $key => $val){
		// pre-set standard values, to be overriden if necessary
		$RGB      = hex2RGB($config['text']['color']);
		$alpha    = $config['text']['alpha'];
		$txt_size = $config['text']['size']['labels'];
		$offsetX  = $val['posX'];
		
		if($val['name']=='nickname'){
			$txt_size = $config['text']['size']['names'];
			$bbox     = imagettfbbox($txt_size, 0, $config['text']['font'], $labels->getValue($val['name']));
			$offsetX  = $config['dim']['width']/2 - ($bbox[2]-$bbox[0])/2;
		}

		if($val['name']=='clan_name'){
			$RGB      = hex2RGB($labels->getValue('clan_color'));
			$alpha    = 0;
			$txt_size = $config['text']['size']['names'];
			$bbox     = imagettfbbox($txt_size, 0, $config['text']['font'], $labels->getValue($val['name']));
			$offsetX  = $config['dim']['clan']/2 - ($bbox[2]-$bbox[0])/2;
		}

		if(strpos($val['name'], 'best_tier') !== false ){
			preg_match('/\d{1,2}/', $val['name'], $rgx);
			$RGB      = hex2RGB(wn8_color::getter($labels->getValue('best_tier'.$rgx[0].'_wn8')));
			$alpha    = 0;	
			$txt_size = $config['text']['size']['tanks'];
		}

		$color_label = imagecolorallocatealpha($signature, $RGB['red'], $RGB['green'], $RGB['blue'], $alpha);

		imagettftext($signature, $txt_size, 0, $offsetX, $val['posY'], $color_label, $config['text']['font'], $labels->getValue($val['name']));

	}

	// set proper header for image output
	header("Content-type: image/png");
	// display the image 
	imagepng($signature);
	// save it at proper location
	imagepng($signature, "autogenerated/".$player_id.".png");

	// be memory friendly and clean up / destroy all image resources
	foreach ($icon as $key => $val) {
		imagedestroy($icon[$key]);
	}
	foreach ($pb as $tankClass => $arr) {
		foreach ($arr as $key => $val) {
			imagedestroy($pb[$tankClass][$key]);
		}
	}
	imagedestroy($signature);
}


$player_id = $_GET['id'];

if (isset($player_id) && is_numeric($player_id)) {
	make_signature($player_id);
}else{
	header("HTTP/1.0 404 Not Found");
	die('Page not found.');
}


?>