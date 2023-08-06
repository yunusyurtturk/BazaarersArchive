<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');
require_once(BASE_PATH.'/model/class_base_model_with_db.php');
require_once(BASE_PATH.'/model/db/class_db.php');

class CCategory  extends CModelBaseWithDB
{
	private $id;
	private $info;
	
function __construct($id, array $dependicies = array())
	{
		parent::__construct($dependicies);
		$this->id = $id;
	}

	static function &SGetChildsRecursive($catid, CDBConnection $db)
	{
		$siblingCats = array();

		$siblingCats = self::SGetChilds($catid, $db);

		foreach($siblingCats as &$cat){

			$catid = $cat['catid'];
			$childCats = self::SGetChilds($catid, $db);
			if(count($childCats) > 0){

				$cat['subCats'] = self::SGetChildsRecursive($catid, $db);
			}

		}

		return $siblingCats;

	}

	static function &SGetParentsRecursive($catid, CDBConnection $db)
    {

	    $parents = array($catid);
        $hasParent = true;
        $i = 0;

        while(true == $hasParent && $i < 30){

            $i++;

            $db->Prepare('SELECT * FROM categories WHERE catid=:catid');
            $params[] = new CDBParam('catid', $catid, PDO::PARAM_INT );

            if($db->Execute($params)){
                if($db->RowCount() > 0)
                {
                    while($fetch = $db->Fetch())
                    {
                        if('0' == $fetch['parent']){

                            $parents[] = '0';
                            $hasParent = false;

                        }else{

                            $parents[] = $fetch['parent'];
                            $catid = $fetch['parent'];
                        }

                    }
                }else{

                    break;
                }
            }else{

                break;
            }
        }
        $parents = array_reverse($parents);
        return $parents;


    }

	static function SGetChilds($catid, CDBConnection $db)
	{
		$returnVal = array();
		$db->Prepare('SELECT * FROM categories WHERE parent = :parent');
		$params[] = new CDBParam('parent', $catid, PDO::PARAM_INT );
		if($db->Execute($params)){
			if($db->RowCount() > 0)
			{
				while($fetch = $db->Fetch())
				{
					$returnVal[] = array('catname' => $fetch['catname'], 'catid' => $fetch['catid']);
				}
			}
		}
		return $returnVal;

	}
	function GetChilds()
	{
		$returnVal = array();
		$this->db->Prepare('SELECT * FROM categories WHERE parent = :parent');
		$params[] = new CDBParam('parent', $this->id, PDO::PARAM_INT );
		if($this->db->Execute($params)){
			if($this->db->RowCount() > 0)
			{
				$this->info['has_subcats'] = true;
				while($fetch = $this->db->Fetch())
				{
					$returnVal['catnames'][] = $fetch['catname'];
					$returnVal['catids'][]   = $fetch['catid'];
				}
			}else
			{
			
			}
		}
		return $returnVal;
	}
	function Init(){
		
		
	}
	function IsExist(){
		if(0 === (int)$this->id){
			return true;
		}
		$this->db->Prepare('SELECT catid FROM categories WHERE catid=:catid');
		$params[] = new CDBParam('catid', $this->id, PDO::PARAM_INT );
		if($this->db->Execute($params)){
			if($this->db->RowCount() > 0){
				return true;
			}
		}
		return false;
	}
	
	function GetParent(){

        $this->db->Prepare('SELECT parent FROM categories WHERE catid=:catid');
        $params[] = new CDBParam('catid', $this->id, PDO::PARAM_INT );
        if($this->db->Execute($params)){
            if($this->db->RowCount() > 0){

                $fetch = $this->db->Fetch();
                $returnVal = $fetch['parent'];
                return $returnVal;
            }
        }
        return false;
		
	}
	function GetTopLevelParent(){
		
	}
    static function SGetName($catid, $db){

        $db->Prepare('SELECT catname FROM categories WHERE catid=:catid');

        $params[] = new CDBParam('catid', $catid, PDO::PARAM_INT );

        if($db->Execute($params)){

            if($db->RowCount() > 0){

                $fetch = $db->Fetch();
                return stripslashes($fetch['catname']);
            }
        }

        return _("Error");
    }
	function GetName(){
		
		$this->db->Prepare('SELECT catname FROM categories WHERE catid=:catid');
		$params[] = new CDBParam('catid', $this->id, PDO::PARAM_INT );
		
		if($this->db->Execute($params)){
			if($this->db->RowCount() > 0){
				
				$fetch = $this->db->Fetch();

				return stripslashes($fetch['catname']);
			}
		}
		
		return _("Error");
	}
	function HasSubCats(){
		if(!$this->info['has_subcats']){
			
			$this->db->Prepare('SELECT * FROM categories WHERE parent=:parent LIMIT 1');
			$params[] = new CDBParam('parent', $this->id, PDO::PARAM_INT );
			if($this->db->Execute($params)){
				if($this->db->RowCount() > 0){
					
					$this->info['has_subcats'] = true;
				}
			}
		}
		
		return isset($this->info['has_subcats'])?$this->info['has_subcats']:false;
	}
    static function SHasSubCats($catid, CDBConnection $db)
    {
        $db->Prepare('SELECT * FROM categories WHERE parent = :parent');
        $params[] = new CDBParam('parent', $catid, PDO::PARAM_INT );
        if($db->Execute($params)){
            if($db->RowCount() > 0)
            {
                return true;
            }
        }
        return false;

    }
	
	
}