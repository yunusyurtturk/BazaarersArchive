<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');

require_once(BASE_PATH.'/model/class_misc.php');
require_once(BASE_PATH.'/model/items/class_new_item.php');

require_once(BASE_PATH.'/model/users/class_user.php');
require_once(BASE_PATH.'/model/admin/class_admin.php');
require_once(BASE_PATH.'/controller/mobile/controller_base.php');

class CAdminController extends CBaseController
{
	private $action;
    private $isAdmin;

    private $adminControl;
	
	
	function __construct(array $request, array $dependicies = array()){
		parent::__construct($request, $dependicies);
		
		$this->action   = $this->GetRequest('action');
        $this->adminControl = new CAdmin($this->uid, array('db' => $this->db));
        $this->isAdmin = $this->adminControl->IsAdmin();
		
	}
	function IsAdmin()
    {

        return $this->isAdmin;
    }
    function DisplayCategoriesForParentUpdateRecursively($selectedCat, $cats)
    {
        echo '<ul>';

        foreach ($cats as $cat) {

            echo '<li>'.$cat['catname'].'  <a href="'.$_SERVER['PHP_SELF'].'?action=changeParentOfCategory&catID='.$selectedCat.'&parent='.$cat['catid'].'">Make This Parent</a> </li>';
            if(isset($cat['subCats'])){

                $this->DisplayCategoriesForParentUpdateRecursively($selectedCat, $cat['subCats']);
            }

        }

        echo '</ul>';
    }

    function DisplayCategoriesRecursively($cats)
    {
        echo '<ul>';

        foreach ($cats as $cat) {

            echo '<li>'.$cat['catname'].'  <a href="'.$_SERVER['PHP_SELF'].'?action=removeCategory&catID='.$cat['catid'].'">Delete</a>
             - <a href="'.$_SERVER['PHP_SELF'].'?action=changeCategoryName&catID='.($cat['catid']).'">Change Name</a> - 
             <a href="'.$_SERVER['PHP_SELF'].'?action=changeParentOfCategory&catID='.($cat['catid']).'">Change Parent</a> 
             - <a href="'.$_SERVER['PHP_SELF'].'?action=addSiblingCategory&catID='.($cat['catid']).'">Add Sibling</a>
             - <a href="'.$_SERVER['PHP_SELF'].'?action=addCategory&parent='.($cat['catid']).'">Add Child</a>
             </li>';
            if(isset($cat['subCats'])){

                $this->DisplayCategoriesRecursively($cat['subCats']);
            }

        }

        echo '</ul>';
    }
	function RunAction(){
	
		$returnVal = array();

        if(true == $this->isAdmin) {
            switch ($this->action) {

                case "categories":
                    $cats = CCategory::SGetChildsRecursive(0, $this->db);
                    $this->DisplayCategoriesRecursively($cats);
                    break;
                case "addSiblingCategory":
                    $catID = $this->GetRequest('catID');

                    $newCatName = $this->GetRequest('name');

                    if(!empty($catID) && is_numeric($catID) && $catID > 0){



                        if(empty($newCatName)){

                            $returnVal = $this->AddCatToSiblingForm($catID);
                        }else{

                            $returnVal = $this->adminControl->AddCatToSibling($catID, $newCatName);
                            if(false == $returnVal['error']){

                                echo 'Category Added: '.$newCatName;
                            }else{

                                echo 'Problem while adding category. Reason: ' .$returnVal['reason'];
                            }

                        }

                    }
                    break;
                case "addCategory":

                    $parentID = $this->GetRequest('parent');
                    $newCatName = $this->GetRequest('name');

                    if(!empty($parentID) && is_numeric($parentID) && $parentID > 0){



                        if(empty($newCatName)){

                            $returnVal = $this->AddCatToParentForm($parentID);
                        }else{

                            $returnVal = $this->adminControl->AddCatToParent($parentID, $newCatName);
                            if(false == $returnVal['error']){

                                echo 'Category Added: '.$newCatName;
                            }else{

                                echo 'Problem while adding category. Reason: ' .$returnVal['reason'];
                            }

                        }

                    }


                    break;
                case "removeCategory":
                    $catID = $this->GetRequest('catID');
                    $returnVal = $this->adminControl->RemoveCategory($catID);

                    if(false == $returnVal['error']){

                        echo 'Category deleted';
                    }else{

                        echo 'Problem while deleting category. Reason: ' .$returnVal['reason'];
                    }
                    break;
                case "changeCategoryName":
                    $catID = $this->GetRequest('catID');
                    $newCatName = $this->GetRequest('name');

                    if(!empty($catID) && is_numeric($catID)){

                        if(!empty($newCatName)){

                            $returnVal = $this->adminControl->ChangeCatName($catID, $newCatName);
                            if(false == $returnVal['error']){

                                echo 'Cat name is changed to '.$newCatName;
                            }else{

                                echo 'Error occured: ' .$returnVal['reason'];
                            }

                        }else{

                            $this->ChangeCatNameForm($catID);
                        }
                    }



                    break;

                case "allOptions":
                case "changeParentOfCategory":
                    $catID = $this->GetRequest('catID');
                    $parentID = $this->GetRequest('parent');

                    if(isset($parentID) && $parentID > 0){

                        $returnVal = $this->adminControl->ChangeParent($catID, $parentID);
                        if(false == $returnVal['error']){

                            echo 'Parent is changed';
                        }else{

                            echo 'Error occured:'. $returnVal['reason'];
                        }
                    }else{

                        $this->ChangeParentForm($catID);
                    }

                    break;

                case "allOptions":

                    break;

                case "deleteLogs":
                    $this->adminControl->DeleteAllLogs();
                case "allLogs":
                default:
                    $this->DisplayAdminOptions();
                    $logs = $this->adminControl->GetAllLogs();
                    echo '<table>';
                    echo '<tr><th>#</th><th>uid</th><th>method</th><th>class</th><th>time</th><th>Method Params</th><th>URL</th><th>Additional Info</th><th>level</th><th>Message</th></tr>';
                    foreach ($logs as $log) {

                        echo '<tr><td>' . $log['logid'] . '</td>
							<td>' . $log['uid'] . '</td>
							<td>' . $log['method'] . '</td>
							<td>' . $log['class'] . '</td>
							<td>' . date('H:i:s d:m:Y ', $log['time']) . '</td>
							<td>' . $log['methodParams'] . '</td>
							<td>' . $log['url'] . '</td>
							<td>' . $log['additionalInfo'] . '</td>
							<td>' . $log['level'] . '</td>
							<td>' . $log['message'] . '</td>
							';
                    }
                    break;


            }
        }
		return $returnVal;
		
	}

    private function  AddCatToSiblingForm($catID)
    {
        $cat = new CCategory($catID, array('db' => $this->db));
        echo 'Sibling cat name =  '.$cat->GetName();

        echo '<form method="post" action="'.$_SERVER['PHP_SELF'].'?action=addSiblingCategory&catID='.$catID.'">';
        echo '<input type="text" name="name" value="" />';

        echo '<input type="submit">';
        echo '</form>';



    }
    private function AddCatToParentForm($parentID)
    {
        $parent = new CCategory($parentID, array('db' => $this->db));
        echo 'Parent name =  '.$parent->GetName();

        echo '<form method="post" action="'.$_SERVER['PHP_SELF'].'?action=addCategory&parent='.$parentID.'">';
        echo '<input type="text" name="name" value="" />';

        echo '<input type="submit">';
        echo '</form>';
    }
    private function  ChangeCatNameForm($catID)
    {
        $cat = new CCategory($catID, array('db' => $this->db));
        $parent = new CCategory($cat->GetParent(), array('db' => $this->db));
        echo 'Cat name =  '.$cat->GetName();

        echo '<form method="post" action="'.$_SERVER['PHP_SELF'].'?action=changeCategoryName&catID='.$catID.'">';
        echo '<input type="text" name="name" value="'.$cat->GetName().'" />';

        echo '<input type="submit">';
        echo '</form>';
        
        
        
    }

	private function  ChangeParentForm($catID)
    {
        $cat = new CCategory($catID, array('db' => $this->db));
        echo 'Select new parent category for '.$cat->GetName();
        echo '<br /> Current Parent is:'.$cat->GetParent();

        $cats = CCategory::SGetChildsRecursive(0, $this->db);

        $this->DisplayCategoriesForParentUpdateRecursively($catID, $cats);
    }
	private function DisplayAdminOptions()
	{
		echo '<table>';
		echo '<tr><td><a href="'.$_SERVER['PHP_SELF'].'?action=deleteLogs">Delete all logs </a></td></tr>';
        echo '<tr><td><a href="'.$_SERVER['PHP_SELF'].'?action=categories">List categories </a></td></tr>';
        echo '<tr><td><a href="'.$_SERVER['PHP_SELF'].'?action=addCategory">AddCategory </a></td></tr>';
        echo '<tr><td><a href="'.$_SERVER['PHP_SELF'].'?action=removeCategory">Remove Category </a></td></tr>';
        echo '<tr><td><a href="'.$_SERVER['PHP_SELF'].'?action=changeCategoryName">Change Category Names </a></td></tr>';
		
		echo '</ table>';
		
	}
	
	
}