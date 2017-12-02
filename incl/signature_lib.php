<?php


class signature 
{
	private $player_data;
	private $account_tank_stats;
	private $exp_tank_stats;
	private $clan_data;
	private $tank_details;
	private $wargaming_id;
	private $player_nickname;
	private $personal_rating;
	private $battles;
    private $win_rate;
    private $hits_percents;
    private $average_exp;
    private $wins;
    private $spotted;
    private $frags;
    private $damage_dealt;
    private $capture_points;
    private $capture_points_dropped;
    private $expected_damage;
    private $expected_spot;
    private $expected_frag;
    private $expected_def;
    private $expected_win;
    private $tank_stats;
    private $tank_id;
    private $tank_battles;
    private $avgDamage;
    private $avgSpot;
    private $avgFrag;
    private $avgDef;
    private $avgWin;
    private $avgTier;
    private $rWINc;
    private $rDAMAGEc;
	private $rFRAGc;
	private $rSPOTc;
	private $rDEFc;
	private $WN8;
	private $best_tier;
	private $tanks_actual;
	private $tanks_expected;


	public function __construct($player_id){
		$this->player_data 				= getPlayerData($player_id);
		$this->account_tank_stats		= getTankStatistics($player_id);
		$this->exp_tank_stats			= getExpectedTankValues();
		$this->clan_data				= getClanData($this->player_data->clan_id);
		$this->tank_details 			= getTankDetails();
		$this->player_nickname			= $this->player_data->nickname;
		$this->wargaming_id				= $player_id;
		$this->personal_rating			= $this->player_data->global_rating;
		$this->battles                  = $this->player_data->statistics->all->battles; //Total battles
	    $this->win_rate                 = (($this->player_data->statistics->all->wins / $this->battles) * 100); //Average win rate
	    $this->hits_percents			= $this->player_data->statistics->all->hits / $this->player_data->statistics->all->shots * 100;
	    $this->average_exp				= $this->player_data->statistics->all->battle_avg_xp;
	    $this->wins                     = $this->player_data->statistics->all->wins;
	    $this->spotted                  = $this->player_data->statistics->all->spotted; //Total spotted
	    $this->frags                    = $this->player_data->statistics->all->frags; //Total kills
	    $this->damage_dealt             = $this->player_data->statistics->all->damage_dealt; //Total damage
	    $this->capture_points           = $this->player_data->statistics->all->capture_points; //Total capture points
	    $this->capture_points_dropped 	= $this->player_data->statistics->all->dropped_capture_points; //Total capture points
	    $this->expected_damage          = 0; //How much damage is expected on average from all tanks on account
	    $this->expected_spot            = 0; //How much spotting is expected on average from all tanks on account
	    $this->expected_frag            = 0; //How many kills is expected on average from all tanks on account
	    $this->expected_def             = 0; //How many capture points is expected on average from all tanks on account
	    $this->expected_win             = 0; //How many wins are expected on average from all tanks on account
		$this->WN8						= 0;
		$this->best_tier 				= ['6' => ['name' => '', 'wn8' => 0], '8' => ['name' => '', 'wn8' => 0], '10' => ['name' => '', 'wn8' => 0]];
	    $this->avgTier 					= 0;
	    $this->tanks_actual 			= (object)[];
		$this->tanks_expected 			= (object)[];

		foreach($this->account_tank_stats as $this->tank_stats){
	        $this->tank_id                	= $this->tank_stats->tank_id;
			$this->tank_battles           	= $this->tank_stats->all->battles;
			if(property_exists($this->tank_details, $this->tank_id)){
			$this->avgTier 					+= $this->tank_details->{$this->tank_id}->tier * $this->tank_battles;
			$this->tankTier					= $this->tank_details->{$this->tank_id}->tier;


			foreach ($this->tank_stats->all as $label => $value) {
				if (!property_exists($this->tanks_actual, $this->tank_details->{$this->tank_id}->type)) {
					$this->tanks_actual->{$this->tank_details->{$this->tank_id}->type} = (object)[];
				}
				if (!property_exists($this->tanks_actual->{$this->tank_details->{$this->tank_id}->type}, $label)) {
					$this->tanks_actual->{$this->tank_details->{$this->tank_id}->type}->{$label} = $value;
				}else{
					$this->tanks_actual->{$this->tank_details->{$this->tank_id}->type}->{$label} += $value;
				}
			}
			if (!property_exists($this->tanks_actual->{$this->tank_details->{$this->tank_id}->type}, '_count')) {
				$this->tanks_actual->{$this->tank_details->{$this->tank_id}->type}->{'_count'} = 0;
			}else{
				$this->tanks_actual->{$this->tank_details->{$this->tank_id}->type}->{'_count'} += 1;
			}

			switch ($this->tank_details->{$this->tank_id}->tier) {
				case 7:
				case 9:
					break;
				case 6:
            	case 8:	
            	case 10:
					if($this->tank_stats->wn8 > $this->best_tier[$this->tankTier]['wn8']){
						$this->best_tier[$this->tankTier]['name'] = $this->tank_details->{$this->tank_id}->short_name;
						$this->best_tier[$this->tankTier]['wn8']  = $this->tank_stats->wn8;
					}
            		break;
            	default:
            		break;
            }

	        if(isset($this->exp_tank_stats[$this->tank_id])){

				foreach ($this->exp_tank_stats[$this->tank_id] as $label => $value) {
					if (!property_exists($this->tanks_expected, $this->tank_details->{$this->tank_id}->type)) {
						$this->tanks_expected->{$this->tank_details->{$this->tank_id}->type} = (object)[];
					}
					if (!property_exists($this->tanks_expected->{$this->tank_details->{$this->tank_id}->type}, $label)) {
						$this->tanks_expected->{$this->tank_details->{$this->tank_id}->type}->{$label} = $value * $this->tank_battles;
					}else{
						$this->tanks_expected->{$this->tank_details->{$this->tank_id}->type}->{$label} += $value * $this->tank_battles;
					}
				}

	            $this->expected_damage     	+= 		 $this->exp_tank_stats[$this->tank_id]->expDamage *  $this->tank_battles;
	            $this->expected_spot       	+= 		 $this->exp_tank_stats[$this->tank_id]->expSpot * 	 $this->tank_battles;
	            $this->expected_frag       	+= 		 $this->exp_tank_stats[$this->tank_id]->expFrag * 	 $this->tank_battles;
	            $this->expected_def        	+= 		 $this->exp_tank_stats[$this->tank_id]->expDef * 	 $this->tank_battles;
	            $this->expected_win        	+= 0.01 * $this->exp_tank_stats[$this->tank_id]->expWinRate * $this->tank_battles;
			}
		}
	    } // ------------------

	    $this->avgDamage                    = $this->damage_dealt 				/ $this->expected_damage;
	    $this->avgSpot                      = $this->spotted 					/ $this->expected_spot;
	    $this->avgFrag                      = $this->frags 						/ $this->expected_frag;
	    $this->avgDef                       = $this->capture_points_dropped 	/ $this->expected_def;
	    $this->avgWin                       = $this->wins 						/ $this->expected_win;
	    $this->avgTier						= $this->avgTier 					/ $this->battles;

	    $this->rWINc                       	= max(0, 								($this->avgWin 		- 0.71) / (1 - 0.71) );
	    $this->rDAMAGEc                    	= max(0, 								($this->avgDamage 	- 0.22) / (1 - 0.22) );
		$this->rFRAGc                      	= max(0, min($this->rDAMAGEc + 0.2,  	($this->avgFrag 	- 0.12) / (1 - 0.12)));
		$this->rSPOTc                      	= max(0, min($this->rDAMAGEc + 0.1,  	($this->avgSpot 	- 0.38) / (1 - 0.38)));
		$this->rDEFc                       	= max(0, min($this->rDAMAGEc + 0.1,  	($this->avgDef 		- 0.10) / (1 - 0.10)));

	    $this->WN8                         	=  980 * $this->rDAMAGEc;
	    $this->WN8                         	+= 210 * $this->rDAMAGEc * $this->rFRAGc;
	    $this->WN8                         	+= 155 * $this->rFRAGc	* $this->rSPOTc;
	    $this->WN8                         	+= 75  * $this->rDEFc 	* $this->rFRAGc;
	    $this->WN8                         	+= 145 * MIN(1.8, 		  $this->rWINc);
	}
	public function getValue($param)
	{
		switch($param){
			case 'nickname':
				return $this->player_nickname;
				break;
			case 'battles':
				return $this->battles;
				break;
			case 'avg_exp':
				return $this->average_exp;
				break;
			case 'avg_dmg':
				return round($this->damage_dealt / $this->battles);
				break;
			case 'avg_frags':
				return round($this->avgFrag, 2);
				break;
			case 'avg_tier':
				return round($this->avgTier, 1);
				break;
			case 'hitratio':
				return round($this->hits_percents, 2);
				break;
			case 'winrate':
				return round($this->wins / $this->battles * 100, 2);
				break;
			case 'WN8':
				return round($this->WN8);
				break;
			case 'personal_rating':
				return $this->personal_rating;
				break;
			// clan related data
			case 'clan_name':
				return "[".$this->clan_data->tag."]";
				break;
			case 'clan_icon':
				return $this->clan_data->emblems->x64->portal;
				break;
			case 'clan_color':
				return $this->clan_data->color;
				break;
			case 'clan_rank':
				foreach ($this->clan_data->members as $member) {
					if ($member->account_id == $this->wargaming_id){
						return $member->role;
					}
				}
				break;
			// total battles per tank class
			case 'btl_lt':
				return $this->tanks_actual->lightTank->battles;
				break;
			case 'btl_mt':
				return $this->tanks_actual->mediumTank->battles;
				break;
			case 'btl_ht':
				return $this->tanks_actual->heavyTank->battles;
				break;
			case 'btl_td':
				return $this->tanks_actual->{'AT-SPG'}->battles;
				break;
			case 'btl_spg':
				return $this->tanks_actual->SPG->battles;
				break;
			// total wn8 per tank class
			case 'wn8_lt':
				return calculateTankClassWN8($this->tanks_actual->lightTank, $this->tanks_expected->lightTank);
				break;
			case 'wn8_mt':
				return calculateTankClassWN8($this->tanks_actual->mediumTank, $this->tanks_expected->mediumTank);
				break;
			case 'wn8_ht':
				return calculateTankClassWN8($this->tanks_actual->heavyTank, $this->tanks_expected->heavyTank);
				break;
			case 'wn8_td':
				return calculateTankClassWN8($this->tanks_actual->{'AT-SPG'}, $this->tanks_expected->{'AT-SPG'});
				break;
			case 'wn8_spg':
				return calculateTankClassWN8($this->tanks_actual->SPG, $this->tanks_expected->SPG);
				break;
			// best tank per each SH tier
			case 'best_tier6_name':
				return $this->best_tier['6']['name'];
				break;
			case 'best_tier8_name':
				return $this->best_tier['8']['name'];
				break;
			case 'best_tier10_name':
				return $this->best_tier['10']['name'];
				break;
			case 'best_tier6_wn8':
				return $this->best_tier['6']['wn8'];
				break;
			case 'best_tier8_wn8':
				return $this->best_tier['8']['wn8'];
				break;
			case 'best_tier10_wn8':
				return $this->best_tier['10']['wn8'];
				break;
			default:
				break;
		}
	}
}

?>