<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');
require_once(BASE_PATH.'/model/defs/global_definitions.php');

require_once(BASE_PATH.'/model/log/logger.php');

/**
 * 
 * @author Yunus Y.
 * $_FILES must be applied re_array_files($_FILES)
 * before using this class
 *
 */
class CImageManipulation
{
	private $maxwidth  = 1080;
	private $maxheight = 760;
	private $picture;
	
	function __construct($image){
		$this->SetImage($image);
	}
	function SetImage($image){
		$this->picture = $image;
	}
	private function IsNameValid(){

		if(preg_match("`^[-0-9A-Z_\.]+$`i", $this->picture['name'])){
			return true;
		}else{
			return false;
		}

	}
	private function IsFileSizeValid(){
		
		if($this->picture['size'] > 2097152){ //2 MB
			return false;
		}else{
			return true;
		}
	}
	private function GetMime()
	{
		$info =$this->GetDimensionsAndMime();	
		return $info['mime'];
	}
	private function IsFileSuccessed(){
		if (UPLOAD_ERR_OK == $this->picture["error"]){
			return true;
		}else{
			return false;
		}
	}
	private function IsTypeValid(){
		
		if ($this->picture['type'] != "image/gif" && $this->picture['type'] != "image/bmp" && $this->picture['type'] != "image/jpeg" && $this->picture['type'] != "image/png")
		{
			return false;
		}else{
			return true;
		}
	}
	private function GetDimensionsAndMime(){
		$info = getimagesize($this->picture['tmp_name']);
		return array('width'=> intval($info['0']), 'height'=> intval($info['1']), 'mime' => strtolower($info['mime']));
	}
	private function IsDimensionsValid(){
		$picdata = $this->GetDimensionsAndMime();
		if(intval($picdata['width']) > 0 && intval($picdata['height']) > 0){
			
			return true;
		}else{
			
			return false;
		}
		
	}
	
	private function IsImageValid(){
		
		if($this->IsFileSizeValid() && $this->IsNameValid() && $this->IsTypeValid() && $this->IsFileSuccessed() && $this->IsDimensionsValid()){
			return true;
		}else{
			return false;
		}
	}
	function AddTempImage()
	{
		$returnVal = array();
		$returnVal['err'] = ERROR_ERROR;
		
		if($this->IsImageValid()){
			$ext = '.jpg';
			$name =  CMisc::CreateRandomString(36); /* 36 + . + extension = 40  cCc */
			$filename = $name.$ext;
			$dir = $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.'tempimages'.DIRECTORY_SEPARATOR;
			
			if(move_uploaded_file($this->picture["tmp_name"], $dir.$filename)){
				
				return $filename;
			}
		}
		return false;
	}
	function SaveImageTo(&$filename, &$image, $to)
	{
		$dir = dirname($filename);
		chmod($dir, 0777);
		
		
		$returnVal = Imagejpeg($image, $filename, 80 );

		if(false == $returnVal){

			$logger = CLogger::GetLogger();
			$logger->Log($this->uid,
				__FUNCTION__,
				__CLASS__,
				func_get_args(),
				$_SERVER['PHP_SELF'],
				$_SERVER['QUERY_STRING'],
				21,
				'ImageJPEG fonksiyonu hata verdi:'.$filename);
		}
		imagedestroy($image);
		
		chmod($dir, 0755);
		chmod($filename, 0775);

		echo $filename;
		return $returnVal;
		
		/*
		$returnval = false;
		
		$dir = BASE_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$to.DIRECTORY_SEPARATOR;
		$filepath = $dir.$filename;
		chmod($dir, 0777);
		
		$file = fopen($filepath,'w');
		
		if(@fwrite($file, $image)){
			$returnval = $filename;
		}else{
			$returnVal = false;
		}
		@fclose($file);
		chmod($dir, 0755);
		chmod($filepath, 0644);
		
		return $returnval;*/
	}
	function &ResizeImage($newWidth, $newHeight){
		$returnVal = false;
		
		$picInfo = $this->GetDimensionsAndMime();
		$mime = $picInfo['mime'];
		$width = $picInfo['width'];
		$height = $picInfo['height'];
		
		$picture = $this->picture['tmp_name'];
		ini_set ("memory_limit", "100M");
		
		if($mime == "image/gif")
			$source=imagecreatefromgif($picture);
		elseif($mime == "image/jpeg")
			$source=imagecreatefromjpeg($picture);
		elseif($mime == "image/jpg")
			$source=imagecreatefromjpeg($picture);
		elseif($mime == "image/bmp")
			$source=imagecreatefromwbmp($picture);
		elseif($mime == "image/x-pn")
			$source=imagecreatefrompng($picture);
		elseif($mime == "image/png")
			$source=imagecreatefrompng($picture);
		else
			$source	= imagecreatefromjpeg($picture);
		
		if(false != $source){
				
			$dest	= imagecreatetruecolor($newWidth,$newHeight);
			if(false != $dest){
		
				$white 	= imagecolorallocate($dest, 255, 255, 255);
				if(false != $white){
						
					if(imagecopyresampled($dest, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height)){
						
						$returnVal = &$dest;
						
					}
				}
			}
		}
		/*
		if(isset($source)){
			
			$dest= imagecreatetruecolor($newWidth,$newHeight);
			
			$white = imagecolorallocate($dest, 255, 255, 255);
			
		
			imagefilledrectangle($dest, 0, 0, $newWidth, $newHeight, $white); // Make the background white
			imagecopyresized($dest, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
			
			ob_start();
			Imagejpeg($dest, 80);
			$pic=ob_get_contents();
			ob_end_clean();
			imagedestroy($dest);

			return $pic;
			
			
			
		}else{
			echo "--false--";
		}*/
		return $returnVal; 
		
	}
}