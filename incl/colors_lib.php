<?php



class wn8_color
{
	static function getter($argument)
	{
		switch ($argument) {

			case $argument <= 299:  	// 0-299
				return "#930D0D";		// very bad

			case $argument <= 449:  	// 300-449
				return "#CD3333";		// bad

			case $argument <= 649:  	// 450-649
				return "#CC7A00";		// below average

			case $argument <= 899:  	// 650-899
				return "#CCB800";		// average

			case $argument <= 1199: 	// 900-1199
				return "#849B24";		// above average

			case $argument <= 1599: 	// 1200-1599
				return "#4D7326";		// good

			case $argument <= 1999: 	// 1600-1999
				return "#4099BF";		// very good

			case $argument <= 2499: 	// 2000-2499
				return "#3972C6";		// great

			case $argument <= 2899: 	// 2500-2899
				return "#793DB6";		// unicum

			case $argument > 2900:  	// 2900+
				return "#793DB6";		// super unicum

			default:
				return "#000000";		// in case unknown value is passed
		}
	}
}



class winrate_color
{
	static function getter($argument)
	{
		switch ($argument) {

			case $argument < 46:  		// under 46%
				return "#930D0D";		// very bad

			case $argument = 46:  		// 46%
				return "#CD3333";		// bad

			case $argument = 47:  		// 47%
				return "#CC7A00";		// below average

			case $argument <= 49:  		// 48-49%
				return "#CCB800";		// average

			case $argument <= 51: 		// 50-51%
				return "#849B24";		// above average

			case $argument <= 53: 		// 52-53%
				return "#4D7326";		// good

			case $argument <= 55: 		// 54-55%
				return "#4099BF";		// very good

			case $argument <= 59: 		// 56-59%
				return "#3972C6";		// great

			case $argument <= 64: 		// 60-64%
				return "#793DB6";		// unicum

			case $argument > 65:  		// 65% and above
				return "#793DB6";		// super unicum
				
			default:
				return "#000000";		// in case unknown value is passed
		}
	}
}


?>