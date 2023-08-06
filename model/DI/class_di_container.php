<?php
class CDIContainer
{
	private $modules = array();
	private $classInjector;
	
	function  __construct(){
		
		$this->classInjector = new CContainer();
	}
}