<?php 
class CDBParam
{
	public $name;
	public $value;
	public $type;

	function __construct($name, $value, $type = PDO::PARAM_STR){
		
		$this->name  = ':'.$name;
		$this->value = $value;
		$this->type  = $type;
	}

}