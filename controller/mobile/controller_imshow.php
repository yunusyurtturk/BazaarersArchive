<?php


require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');


require_once(BASE_PATH.'/model/image/class_image_operations.php');

require_once(BASE_PATH.'/model/class_misc.php');
require_once(BASE_PATH.'/controller/mobile/controller_base.php');
//require_once(BASE_PATH.'/model/location/class_location.php');
require_once(BASE_PATH.'/controller/mobile/result_code_base_defs.php');


class CControllerImShow  extends CBaseController
{
	private $action;
	private $image;
	private $screen;
	private $imType;
	function __construct($request, array $dependicies = array()){
	
		parent::__construct($request, $dependicies);
		$this->action   = $this->GetRequest('action');
		$this->image   = $this->GetRequest('im');
		$this->screen = $this->GetRequest('s');
		$this->imType = $this->GetRequest('t');

		if(!isset($this->imType) || empty($this->imType)){
			$this->imType = 'itempics';
		}
		
	}
	
	function RunAction(){
		$returnVal = array();
		
		switch($this->action){
				
			default:
				if(empty($this->image)){
					
					return;
				}else{
					$imOp = new CImageOperations($this->imType, $this->screen);
					$image = $imOp->GetImage($this->image);
					
					$whatIWant = substr($image, strpos($image, DIRECTORY_SEPARATOR.'oop'));

					return $image;
				}

			break;
		}
	}
	
}


