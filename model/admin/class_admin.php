<?php

require_once(BASE_PATH.'/model/items/module_items_defs.php');
require_once(BASE_PATH.'/model/class_base_model_with_db.php');
require_once(BASE_PATH.'/model/db/class_db.php');

require_once(BASE_PATH.'/model/image/images_cfg.php');

class CAdmin extends CModelBaseWithDB
{
	private $admin_id;
	function __construct($id, array $dependicies = array())
	{
		parent::__construct($dependicies);
	
	}

	function IsAdmin()
    {

        return true;
    }
	
	function DeleteAllLogs()
	{
		$returnVal = false;
		$this->db->Prepare('TRUNCATE TABLE  logs');
		if($this->db->Execute()){
			
			$returnVal = true;
		}
		
		return $returnVal;
	}
	function GetAllLogs()
	{
		$returnVal = array();
		$this->db->Prepare('SELECT * FROM logs ORDER BY logid DESC');
		if($this->db->Execute()){
			if($this->db->RowCount()>0)
			{
				$returnVal = $this->db->FetchAll();
				
			}
		}
		
		return $returnVal;
	}
    function AddCatToSibling($catID, $newCatName)
    {
        $returnVal = array();
        $returnVal['error'] = true;

        if(is_numeric($catID) && $catID > 0) {
            $cat = new CCategory($catID, array('db' => $this->db));

            if ($cat->IsExist()) {

                $parentID = $cat->GetParent();

                $this->db->Prepare('INSERT INTO categories (catname, parent) VALUES(:catname, :parent)');
                $params[] = new CDBParam('catname', $newCatName, PDO::PARAM_STR);
                $params[] = new CDBParam('parent', $parentID, PDO::PARAM_STR);


                if ($this->db->Execute($params)) {
                    if ($this->db->RowCount() > 0) {
                        $returnVal['error'] = false;
                        $returnVal['added'] = true;

                    }
                }else{
                    $returnVal['reason'] = 'Query not executed';
                }
            }else{
                $returnVal['reason'] = 'Category not exist';
            }
        }else{
            $returnVal['reason'] = 'Top level or invalid category';
        }

        return $returnVal;
    }

	function AddCatToParent($parentID, $newCatName)
    {
        $returnVal = array();
        $returnVal['error'] = true;
        $parent = new CCategory($parentID, array('db' => $this->db));

        if($parent->IsExist()){

            $this->db->Prepare('INSERT INTO categories (catname, parent) VALUES(:catname, :parent)');
            $params[] = new CDBParam('catname', $newCatName, PDO::PARAM_STR);
            $params[] = new CDBParam('parent', $parentID, PDO::PARAM_STR);


            if($this->db->Execute($params)){
                if($this->db->RowCount()>0)
                {
                    $returnVal['error'] = false;
                    $returnVal['added'] = true;

                }
            }
        }

        return $returnVal;
    }

    function RemoveCategory($catID)
    {
        $returnVal = array();
        $returnVal['error'] = true;
        $cat = new CCategory($catID, array('db' => $this->db));

        if($cat->IsExist()){

            if($cat->HasSubCats()){

                $returnVal['error'] = true;
                $returnVal['reason'] = 'Has Childs';
            }else{

                $this->db->Prepare('DELETE FROM categories WHERE catid=:catid');
                $params[] = new CDBParam('catid', $catID, PDO::PARAM_STR);

                if($this->db->Execute($params)){
                    if($this->db->RowCount()>0)
                    {
                        $returnVal['error'] = false;
                        $returnVal['deleted'] = true;

                    }
                }
            }


        }

        return $returnVal;
    }
    function ChangeParent($catID, $newParentID)
    {
        $returnVal = array();
        $returnVal['error'] = true;
        $cat = new CCategory($catID, array('db' => $this->db));
        $parent = new CCategory($newParentID, array('db' => $this->db));

        if($parent->IsExist()){

            if($cat->IsExist()){

                $this->db->Prepare('UPDATE categories SET parent=:parent WHERE catid=:catid');
                $params[] = new CDBParam('catid', $catID, PDO::PARAM_STR);
                $params[] = new CDBParam('parent', $newParentID, PDO::PARAM_STR);

                if($this->db->Execute($params)){
                    if($this->db->RowCount()>0)
                    {
                        $returnVal['error'] = false;
                        $returnVal['changed'] = true;

                    }else{

                        $returnVal['reason'] = 'No entry is changed';
                    }
                }else{
                    $returnVal['reason'] = 'Query not executed';
                }
            }else{

                $returnVal['reason'] = 'Cat not exist';
            }
        }else{

            $returnVal['reason'] = 'Parent not exist';
        }

        return $returnVal;

    }

	function ChangeCatName($catID, $newName){

        $returnVal = array();
        $returnVal['error'] = true;
        $cat = new CCategory($catID, array('db' => $this->db));

        if($cat->IsExist()){



            $this->db->Prepare('UPDATE  categories SET catname=:catname WHERE catid=:catid');
            $params[] = new CDBParam('catid', $catID, PDO::PARAM_STR);
            $params[] = new CDBParam('catname', $newName, PDO::PARAM_STR);

            if($this->db->Execute($params)){
                if($this->db->RowCount()>0)
                {
                    $returnVal['error'] = false;
                    $returnVal['changed'] = true;

                }
            }

        }

        return $returnVal;
	}
	function DeleteCat($cit){
		
	}

	function DeleteUser($userid){
		
	}
	function DeleteUserItems($userid){
		
	}
	function DeleteItem($iid){
		
	}
	function ChangeUsername($uid, $new_username){
		
	}
	function ChangeItemTitle($iid, $title){
		
	}
	function ChangeITemDescription($iid, $description){
		
	}
}