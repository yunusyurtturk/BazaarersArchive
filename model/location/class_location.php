<?php

class CLocation
{
	public $lat;
	public $lng;
	
	function __construct($lat, $lng){
	
		$this->lat = $lat;
		$this->lng = $lng;
	}
	
	function IsValid(){
		if(!is_numeric($this->lat) || !is_numeric($this->lng) || abs($this->lat) < 0.1|| abs($this->lng) < 0.1 || abs($this->lat) > 180 || abs($this->lng) > 180  || empty($this->lat) || empty($this->lng)){
			return false;
		}
		return true;
	}
	function getLat(){
		if($this->IsValid()){
			
			return $this->lat;
		}else{
			return '0';
		}
	}
	function getLng(){
		if($this->IsValid()){
				
			return $this->lng;
		}else{
			return '0';
		}
	}
}