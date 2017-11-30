<?php

//*****************************************
//
// moved all (I think) "configuration" 
// and dimension parameters here,
// for ease of access (and readability).
// Main code intended to stay 
// as much as possible template-like.
// 
//*****************************************

$defaults = [
	'width'		  => 468,
	'height'	  => 100,
	'pb_offsetX'  => 23,
	// offset Y for text
	'row1'        => 18,		// 1st row (battles, avg_kill)
	'row2'        => 43,		// 2nd row (avg_exp, avg_tier)
	'row3'        => 68,		// 3rd row (avg_dmg, hitratio)
	'row4'        => 18,		// personal rank, winrate
	'row5'        => 39,		// best tier 6
	'row6'        => 54,		// best tier 8
	'row7'        => 70,		// best tier 10
	'row8'        => 94,		// clan name, player name
	// offset X for text
	'col1'        => 121, 		// 1st column (battles, exp, dmg)
	'col2'        => 216, 		// 2nd column (avg_kill, avg_tier, hitratio) 
	'col3'        => 290, 		// personal rank
	'col4'        => 360, 		// winrate
	'col5'        => 310, 		// best tier 6
	'col6'        => 320, 		// best tier 8
	'col7'        => 305, 		// best tier 10
];


return [
	'API' 	=> [
			'ID'    => "########################################",
			'token' => "########################################",
			'URL'   => "https://api.worldoftanks.eu/"	// other possibilities are .ru (RU) .com (NA) .asia (ASIA)
	],
	'dim'	=> [
		'width'		=> $defaults['width'],
		'height'	=> $defaults['height'],
		'clan'		=> 76,
	],
	'text'	 => [
		'size'		=> [
			'labels'		=> 10,	// all the lables
			'names'			=> 12,	// clan name, player name
			'battles'		=> 8,	// battles count per tank class
			'tanks'			=> 8,	// best tank in tier 6, 8, 10
		],
		'color'		=> 'FFFFFF',
		'alpha'		=>	63,
		'font'		=> 'Arvo-Regular.ttf',
	],
	'labels' => [
		[
			'name'  => 'nickname',
			'posX'  => null,
			'posY'  => $defaults['row8'],
		],
		[
			'name'  => 'clan_name',
			'posX'  => null,
			'posY'  => $defaults['row8'],
		],
		[
			'name'  => 'battles',
			'posX'  => $defaults['col1'],
			'posY'  => $defaults['row1'],
		],
		[
			'name'  => 'avg_exp',
			'posX'  => $defaults['col1'],
			'posY'  => $defaults['row2'],
		],
		[
			'name'  => 'avg_dmg',
			'posX'  => $defaults['col1'],
			'posY'  => $defaults['row3'],
		],
		[
			'name'  => 'avg_frags',
			'posX'  => $defaults['col2'],
			'posY'  => $defaults['row1'],
		],
		[
			'name'  => 'avg_tier',
			'posX'  => $defaults['col2'],
			'posY'  => $defaults['row2'],
		],
		[
			'name'  => 'hitratio',
			'posX'  => $defaults['col2'],
			'posY'  => $defaults['row3'],
		],
		[
			'name'  => 'winrate',
			'posX'  => $defaults['col4'],
			'posY'  => $defaults['row4'],
		],
		[
			'name'  => 'personal_rating',
			'posX'  => $defaults['col3'],
			'posY'  => $defaults['row4'],
		],
		[
			'name'  => 'best_tier6_name',
			'posX'  => $defaults['col5'],
			'posY'  => $defaults['row5'],
		],
		[
			'name'  => 'best_tier8_name',
			'posX'  => $defaults['col6'],
			'posY'  => $defaults['row6'],
		],
		[
			'name'  => 'best_tier10_name',
			'posX'  => $defaults['col7'],
			'posY'  => $defaults['row7'],
		],
	],
	'icons'	=> [
		'clan'		=> [
			'name'  		=> 'clan_icon',
			'posX'  		=> 5,
			'posY'  		=> 5,
		],
		'rank'		=> [
			'name'  		=> 'clan_rank',
			'posX'  		=> $defaults['width']-34,
			'posY'  		=> $defaults['height']-36,
		],	
	],
	'bars'	=> [
		'size'		=> 35,
		'posX'		=> $defaults['width'] - $defaults['pb_offsetX'],
		'posY'		=> [
			'lightTank' 	=> 1,
			'mediumTank'	=> 16,
			'heavyTank' 	=> 31,
			'AT-SPG' 		=> 46,
			'SPG' 			=> 61,
		],
		
	],
];

?>