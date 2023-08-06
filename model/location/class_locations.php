<?php

class CLocations
{
	private $location;
	
	function __construct(){
		
		$location = new CLocation($lat, $lng);
	}
	function GetLatLng($lid){
		
	}
	function FindCityFromAdminAndLocale($admin, $locale){
		
	}
	function PredictCityName(CLocation $loc, $admin, $locality){
		
	}
	static function GetDistanceBetween(CLocation $loc1, CLocation $loc2){

		if($loc1->IsValid() && $loc2->IsValid()){
			$result= 6371 * acos( cos( deg2rad($loc2->lat)) * cos (deg2rad ($loc1->lat)) * cos (deg2rad ($loc1->lng) - deg2rad ($loc2->lng) ) + sin (deg2rad ($loc2->lat)) * sin(deg2rad($loc1->lat)));
			return ceil($result);
		}
		
		return '?';
	}
	
}