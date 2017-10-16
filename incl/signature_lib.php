<?php

require 'functions_lib.php';

class signature 
{
	// private $player_id;
	private $player_data;
	private $account_tank_stats;
	private $exp_tank_stats;
	private $clan_data;
	private $tank_details;

	private $wargaming_id;
	private $player_nickname;
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

	private $battles_lt;
	private $battles_mt;
	private $battles_ht;
	private $battles_td;
	private $battles_spg;

	private $iii;

	public function __construct($player_id){
		// $this->player_id   				= getPlayerID();
		$this->player_data 				= getPlayerData($player_id);
		$this->account_tank_stats		= getTankStatistics($player_id);
		$this->exp_tank_stats			= getExpectedTankValues();
		$this->clan_data				= getClanData($this->player_data->clan_id);
		$this->tank_details 			= getTankDetails();

		$this->player_nickname			= $this->player_data->nickname;
		$this->wargaming_id				= $player_id;
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
	    $this->battles_lt				= 0;
		$this->battles_mt				= 0;
		$this->battles_ht				= 0;
		$this->battles_td				= 0;
		$this->battles_spg				= 0;
	    $this->iii 						= 0;
	    $this->avgTier 					= 0;


		foreach($this->account_tank_stats as $this->tank_stats){
	        $this->tank_id                	= $this->tank_stats->tank_id;
	        $this->tank_battles           	= $this->tank_stats->all->battles;
	        $this->avgTier 					+= $this->tank_details->{$this->tank_id}->tier * $this->tank_battles;

        	switch ($this->tank_details->{$this->tank_id}->type) {
            	case 'lightTank':
            		$this->battles_lt 	+= $this->tank_battles;
            		break;
            	case 'mediumTank':
            		$this->battles_mt 	+= $this->tank_battles;
            		break;
            	case 'heavyTank':
            		$this->battles_ht 	+= $this->tank_battles;
            		break;
            	case 'AT-SPG':
            		$this->battles_td 	+= $this->tank_battles;
            		break;
            	case 'SPG':
            		$this->battles_spg 	+= $this->tank_battles;
            		break;
            	
            	default:
            		# code...
            		break;
            }

	        if(isset($this->exp_tank_stats[$this->tank_id])){
	            $this->expected_damage     	+= 		 $this->exp_tank_stats[$this->tank_id]->expDamage *  $this->tank_battles;
	            $this->expected_spot       	+= 		 $this->exp_tank_stats[$this->tank_id]->expSpot * 	 $this->tank_battles;
	            $this->expected_frag       	+= 		 $this->exp_tank_stats[$this->tank_id]->expFrag * 	 $this->tank_battles;
	            $this->expected_def        	+= 		 $this->exp_tank_stats[$this->tank_id]->expDef * 	 $this->tank_battles;
	            $this->expected_win        	+= 0.01 * $this->exp_tank_stats[$this->tank_id]->expWinRate * $this->tank_battles;

	            


	        }
	    }

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
			case 'r_winrate':
				// return $this->
				break;
			case 'winrate':
				return round($this->wins / $this->battles * 100, 2);
				break;
			case 'r_WN8':
				// return $this->
				break;
			case 'WN8':
				return round($this->WN8);
				break;
			case 'clan_name':
				return $this->clan_data->tag;
				break;
			case 'clan_icon':
				return $this->clan_data->emblems->x64->portal;
				break;
			case 'clan_color':
				return $this->clan_data->color;
				break;
			case 'clan_rank':
				// echo "<pre>";
				// 	print_r($this->clan_data->members);
				// echo"</pre>";
				// break;
				
				foreach ($this->clan_data->members as $member) {
					// echo $member->account_id ."-". $this->wargaming_id ."<br />";
					if ($member->account_id == $this->wargaming_id){
						return $member->role;
					}
				}
				break;
			case 'btl_lt':
				return $this->battles_lt;
				break;
			case 'btl_mt':
				return $this->battles_mt;
				break;
			case 'btl_ht':
				return $this->battles_ht;
				break;
			case 'btl_td':
				return $this->battles_td;
				break;
			case 'btl_spg':
				return $this->battles_spg;
				break;
			default:
				break;
		}
	}
}

?>