<?php

class CAddItemFormErrors{
	public $itemNameHasError;
	public $countHasError;
	public $categoryHasError;
	public $imagesHasError;
	public $pricingHasError;
	public $descriptionHasError;
	public $priceHasError;

	function __construct()
	{
		$this->itemNameHasError 	= false;
		$this->countHasError 		= false;
		$this->categoryHasError 	= false;
		$this->imagesHasError 		= false;
		$this->pricingHasError 		= false;
		$this->descriptionHasError  = false;
		$this->priceHasError 		= false;


	}


}
class CWebView
{
	
	function __construct(){
		
		

	}
	function GetNotLoggedInTopMenuTemplateValues()
	{

		return array(

			'top-menu-add-item-text' => _('Add'),
			'top-menu-add-item-link' => '/additem',

			'top-menu-login-text' => _('Sign In'),
			'top-menu-register-text' => _('Sign Up'),

			'top-menu-login-link' => '/login',
			'top-menu-register-link' => '/register',
		);
	}
	function GetLoggedInTopMenuTemplateValues()
	{

		return array(
			'is-logged-in' => true,
			'top-menu-add-item-text' => _('Add'),
			'top-menu-news-text' => _('News'),
			'top-menu-my-profile-text' => _('Profile'),
			'top-menu-item-messages-text' => _('Messages'),
			'top-menu-logout-text' => _('Logout'),


			'top-menu-add-item-link' => '/additem',
			'top-menu-news-link' => '/news',
			'top-menu-my-profile-link' => '/myprofile',
			'top-menu-item-messages-link' => '/itemmessages',
			'top-menu-logout-link' => '/logout',

			'searchrange-link' => '/explore',
			'followings-items-link' => '/followeds-items',
	
			'followers-link' => '/followers',
			'followings-link' => '/followings',
			'my-items-link' => '/myitems',


			'user-top-menu-explore-text' => _('Explore'),
			'user-top-menu-followings-items-text' => _('Items of people you follow'),

			'user-top-menu-followers-text' => _('Followers'),
			'user-top-menu-followings-text' => _('Followings'),
			'user-top-menu-my-items-text' => _('Items')
		);
	}
	function GetHeaderTemplateValues()
	{
		return array('page-title' => 'Bazaarers',
			'site-addr' => $this->GetSiteAddressNS(),


			);


	}
	function GetTopMenuTemplateValues()
	{
		return array('logo-image' => $this->GetLogoImage(),
			'logo-image-alt' =>'',
			'header-text' => $this->GetHeaderText(),
			'header-text-secondary'=> $this->GetHeaderSecondaryText());

	}
	static function LoadTemplate($file)
	{
		$extension = '.mustache';
		return file_get_contents($file.$extension);
	}
	function GetSiteAddress()
	{

		if('www.bazaarers.com' == $_SERVER['HTTP_HOST']){
			return 'https://'.$_SERVER['HTTP_HOST'];
		}else{
			return 'http://'.$_SERVER['HTTP_HOST'];
		}
	
	
	}
	
	function GetSiteAddressNS()
	{
		return 'http://'.$_SERVER['HTTP_HOST'];
		
		
	}
	function GetHeader($headerText = '')
	{
		return '
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Bootstrap Example</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
  <link rel="stylesheet" href="'.$this->GetSiteAddress().'/oop/web/css/main.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
  <script src="https://npmcdn.com/imagesloaded@4.1/imagesloaded.pkgd.min.js"></script>
  <script src="'.$this->GetSiteAddress().'/oop/web/js/masonry.pkgd.min.js"></script>
  
  <script src="'.$this->GetSiteAddress().'/oop/web/js/mustache.min.js"></script>
  <script src="'.$this->GetSiteAddress().'/oop/web/js/main.js"></script>
  

</head>
<style>
	
</style>
<body onload="requestLocation();">';
	}
	
	function GetHeaderText($bazaarers = ''){
		
		return 'BAZAARERS';
	}
	
	function GetLogoImage($bazaarers = ''){
		
		return '/oop/resources/logo.png';
	}
	
	function GetHeaderSecondaryText(){
	
		return 'Social and location based trading(In Beta, maybe Alpha?)';
	}

	function &GetMyProfileShareTemplateValues()
	{
		$returnVal = array(
			'whatsapp-share-link' => '',
			'facebook-share-link' => '',
			'twitter-share-link' => ''


		);

		return $returnVal;
	}
	function &GetMyProfilePasswordOpsTemplateValues()
	{
		$returnVal = array(
			'myprofile-password-ops-title' => _('Password Ops'),
			'password-ops-current-password-label' => _('Change password'),
			'password-ops-current-password-name' => 'current_password',
			'password-ops-new-password-label' => _('New password'),
			'password-ops-new-password-name' => 'new_password',
			'password-ops-action' => '?action=changePassword',
			'password-ops-button-text' => _('Change')

		);
		return $returnVal;
	}


	function &GetMyProfileUserpicOpsTemplateValues()
	{
		$returnVal = array(
			'userpic-update-form-action' => '?action=changeProfilePic',
			'userpic-update-button-text' => _('Change'),
			'userpic-update-input-name' => 'uploadedfile[]'
		);

		return $returnVal;
	}

	function &GetMyProfileLocationOpsTemplateValues()
	{
		$returnVal = array(
			'myprofile-location-ops-title' => _('Location Settings'),
			'location-ops-label' => _('Update location'),
			'location-ops-action' => '',
			'location-ops-button-text' => _('Update')
		);

		return $returnVal;
	}

	function &GetMyProfileProfileOpsTemplateValues()
	{
		$returnVal = array(
		'myprofile-profile-ops-title' => _('Profile Settings'),
		'profile-ops-public-text' => _('Public'),
		'profile-ops-private-text' => _('Private'),
		'profile-ops-label' => _('Profile Visibility')
		);

		return $returnVal;
	}


	function &GetMyProfileEmailOpsTemplateValues($email)
	{
		$returnVal = array(
			'myprofile-email-ops-title' => _('Email'),
			'email-ops-current-email-label' => _('Your email'),
			'email-ops-email-address' => $email,


		);
		return $returnVal;
	}

	function &GetMyProfileTemplateValues($editable = false)
	{
		$returnVal = array(
			'userinfo-update-form-action' => '',
			'userinfo-update-cancel-button-text' => _('Cancel'),
			'userinfo-update-button-text' => _('Update'),
			'userinfo-update-input-text-name' => 'userinfo',
			'userinfo-update-action-text' => _('Update'),

			'userpic-update-form-action' => '',
			'userpic-update-input-file-name' => '',

			'location-update-form-action' => '',
			'location-update-input-lat-name' => '',
			'location-update-input-lng-name' => '',
		);

		if(true === $editable){
			$returnVal = array_merge($returnVal, array('userProfileEdit' => true));
		}
		return $returnVal;

	}
	function &GetAddItemFormTemplateParams(CAddItemFormErrors $errors = null,  array $selectedValues = null)
	{
		require_once(BASE_PATH.'/model/items/module_items_defs.php');
		/* 1 - Generic Params */
		$addItemFormGenericParams =  array(
			'additem-form-title' => _('Add Item Form'),
			'additem-form-action' => 'additem.php?action=additem',

			'itemname-hint' => _('Title'),
			'itemname-input-name' => 'header',

			'amount-hint' => _('Amount'),
			'amount-input-name' => 'amount',

			'category-hint' => _('Category'),
			'category-input-name' => 'category',

			'images-hint' => _('Images'),
			

			'pricing-type-hint' => _('Pricing'),
			'pricing-type-name' => 'priceType',


			'price-hint' => _('Price'),
			'price-input-name' => 'price',

			'description-hint' => _('Description'),
			'description-input-name' => 'description',

			'hidden-name' => 'formapproved',
			'hidden-value' => 'formapproved',



		);
		if(null != $errors){
			($errors->itemNameHasError)?$addItemFormGenericParams['itemname-has-error']		  =true:'';
			($errors->categoryHasError)?$addItemFormGenericParams['category-has-error']		  =true:'';
			($errors->countHasError)?$addItemFormGenericParams['amount-has-error']			  =true:'';
			($errors->descriptionHasError)?$addItemFormGenericParams['description-has-error'] =true:'';
			($errors->imagesHasError)?$addItemFormGenericParams['images-has-error']			  =true:'';
			($errors->priceHasError)?$addItemFormGenericParams['price-has-error']			  =true:'';
			($errors->pricingHasError)?$addItemFormGenericParams['pricing-has-error'] 		  =true:'';
		}


		/* 2 - Categories - */
		require_once(BASE_PATH.'/controller/mobile/controller_cats.php');

		$categories = new CCategoriesController(array('parent' => 0));
		$childCats = $categories->RunAction();


		if ($childCats['hassubcats']) {

			$resultArray['categories-header'] = _('Categories');
			$childCount = count($childCats['catnames']);
			for ($i = 0; $i < $childCount; $i++) {

				$resultArray['categories'][] = array('catid' => $childCats['catids'][$i], 'catname' => $childCats['catnames'][$i]);
			}
		}


		$addItemFormGenericParams['categories'] = $resultArray['categories'];

		/* 3 - Price Types */

		$isPriceTypeSelected = array(PRICE_TYPE_PRICE => false, PRICE_TYPE_FREE => false, PRICE_TYPE_DEAL => false);

		if(!isset($selectedValues)  OR !isset($selectedValues['price-type'])){

			$isPriceTypeSelected[PRICE_TYPE_PRICE] = true;

		}else{

			$isPriceTypeSelected[$selectedValues['price-type']] = true;
		}

		$addItemFormGenericParams['pricing-types'][] = array('pricing-type-name' => 'priceType',
			'pricing-type-value' => PRICE_TYPE_PRICE,
			'selected' => $isPriceTypeSelected[PRICE_TYPE_PRICE],
			'should-have-price-value' => 'true',
			'price-type-text' => _('Price')
		);
		$addItemFormGenericParams['pricing-types'][] = array('pricing-type-name' => 'priceType',
			'pricing-type-value' => PRICE_TYPE_FREE,
			'selected' => $isPriceTypeSelected[PRICE_TYPE_FREE],
			'should-have-price-value' => 'false',
			'price-type-text' => _('Free')
		);
		$addItemFormGenericParams['pricing-types'][] = array('pricing-type-name' => 'priceType',
			'pricing-type-value' => PRICE_TYPE_DEAL,
			'selected' => $isPriceTypeSelected[PRICE_TYPE_DEAL],
			'should-have-price-value' => 'false',
			'price-type-text' => _('Deal')
		);

		return $addItemFormGenericParams;
	}
	
}