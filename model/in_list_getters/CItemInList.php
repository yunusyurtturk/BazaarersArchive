<?php

require_once(BASE_PATH.'/model/items/class_items.php');
require_once(BASE_PATH.'/model/users/class_user.php');
/**
 * Created by PhpStorm.
 * User: Elektronik
 * Date: 6/5/2016
 * Time: 3:45
 */
class CItemInList extends CModelBaseWithDB
{
    private $iid;
    private $location;
    private $isLocationValid;

    function __construct(CLocation $location = null, array $dependicies = array())
    {

        parent::__construct($dependicies);
        if(null != $location && $location->IsValid()){

            $this->location = $location;
        }else{
            $this->location = null;
        }
    }

    function &GetItem(&$iid)
    {
        $item = new CItems($iid, array('db' => $this->db));
        $owner = new CUser($item->GetOwnerID(), array('db' => $this->db));

        $returnVal['iid']       = $iid;
        $returnVal['header']       =  $item->GetTitle();
        $returnVal['uid']       =  $item->GetOwnerID();

        $returnVal['price']     = $item->GetPriceStr();
        $returnVal['itempic']   = $item->GetMainPic();
        $returnVal['mainpic']   = $item->GetMainPic();	//[yy] fazlalik?
        $returnVal['itemowner'] = $item->GetOwnerName();
        $returnVal['cat']       = $item->GetCategoryID();
        $returnVal['user-profile-url'] = '/user/'.$returnVal['uid'];

        if(null != $this->location){

            $returnVal['location'] = CLocations::GetDistanceBetween($this->location, $owner->GetLocation());
        }



        $returnVal['ownerpic'] = '/userpics/'.$returnVal['uid'].'.jpg';

        if(CItems::SIsNew($item->GetAddTime())){

            $returnVal['new-item'] = true;
        }

        return $returnVal;

    }
}