<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');


require_once(BASE_PATH.'/model/category/class_categories.php');
require_once(BASE_PATH.'/controller/mobile/controller_base.php');

class CCategoriesController extends CBaseController
{
	private $action;
	
	private $parent;
	
	function __construct(array $request,  array $dependicies = array()){
		
		parent::__construct($request, $dependicies);
	
		$this->action   = $this->GetRequest('action');
		$catID = $this->GetRequest('parent');
		$this->parent = (is_numeric($catID) && $catID >= 0)?$catID:0;
	

	}
	function RunAction(){
	
		$returnVal = array();
		$returnVal['hassubcats']=0;
		
		
		switch($this->action){
			case 'allcats':
				$cat = new CCategory($this->parent, array('db', $this->db));
				$returnVal['catid'] = $this->parent;

				if($cat->IsExist()){
					$subCats = $cat->GetChildsRecursive();

				}

				$returnVal = $subCats;

			break;
			default:
				
				$cat = new CCategory($this->parent, array('db', $this->db));
				$returnVal['catid'] = $this->parent;
				
				if($cat->IsExist()){
					$subCats = $cat->GetChilds();
					$returnVal = array_merge($returnVal, $subCats);
					
					if($cat->HasSubCats()){
						
						$returnVal['hassubcats']=1;
					}
					
					$returnVal['catname'] = _($cat->GetName($this->parent));
					$returnVal['catid'] = $this->parent;
				}
			
			
		}
		return $returnVal;
		
	}
	
	
}