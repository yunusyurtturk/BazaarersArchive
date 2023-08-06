<?php
class CNewGroupInfo
{
	public $gname;
	public $gdescription;
	public $gpic;
	public $glat;
	public $glng;
	public $uid;

	function __construct($gname, $gdescription, $gpic, $glat, $glng, $uid, array $images){
		$this->gname 		= $gname;
		$this->gdescription 	= $gdescription;
		$this->gpic 	    = $gpic;
		$this->glat 		= $glat;
		$this->glng  		= $glng;
		$this->uid 	= $uid;

	}
}
class CGroupCreate
{
	private $location;
	private $groupInfo;
	
	function __construct(CNewGroupInfo $groupInfo, array $fields){
		
		$this->groupInfo = $groupInfo;
		$this->location = new CLocation($groupInfo->glat, $groupInfo->glng);
	}
	
}