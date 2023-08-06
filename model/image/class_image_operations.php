<?php

define('SCREEN_SIZE_XL_XH', 35);
define('SCREEN_SIZE_L_XH',  25);
define('SCREEN_SIZE_N_XH',  15);

define('SCREEN_SIZE_XL_H', 34);
define('SCREEN_SIZE_L_H',  24);
define('SCREEN_SIZE_N_H',  14);

define('SCREEN_SIZE_XL_M', 33);
define('SCREEN_SIZE_L_M',  23);
define('SCREEN_SIZE_N_M',  13);

define('SCREEN_SIZE_XL_L', 32);
define('SCREEN_SIZE_L_L',  22);
define('SCREEN_SIZE_N_L',  12);





class CScreenResolution
{
	public $width;
	public $height;

}

class CImageOperations
{
	private $imName;
	private $imPath;
	private $resolution;
	private $imagesBase;
	public  $imType;
	function __construct($imType, $density = SCREEN_SIZE_XL_H)
	{
		
		
		$this->imagesBase = BASE_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$imType;

		$this->SetDensity($density);
		
	}
	
	function SetDensity($density){
		$resolution = new CScreenResolution();
		$width;
		$height;

		switch($density){
			
			/* XHdpi Screens */
			case SCREEN_SIZE_XL_XH:
				$width =  1920;
				$height = 2560;
			break;
			case SCREEN_SIZE_L_XH:
				$width =  1440;
				$height = 1920;
			break;
			case SCREEN_SIZE_N_XH:
				$width =  960;
				$height = 1280;
			break;
			
			/* Hdpi Screens */
			case SCREEN_SIZE_XL_H:
				$width =  1440;
				$height = 1920;
				break;
			case SCREEN_SIZE_L_H:
				$width =  1080;
				$height = 1440;
				break;
			case SCREEN_SIZE_N_H:
				$width =  720;
				$height = 960;
				break;
				

			/* Mdpi Screens */
			case SCREEN_SIZE_XL_M:
				$width =  960;
				$height = 1280;
				break;
			case SCREEN_SIZE_L_M:
				$width =  720;
				$height = 960;
				break;
			case SCREEN_SIZE_N_M:
				$width =  480;
				$height = 640;
				break;
			/* Ldpi Screens */
			case SCREEN_SIZE_XL_L:
				$width =  720;
				$height = 960;
				break;
			case SCREEN_SIZE_L_L:
				$width =  540;
				$height = 720;
				break;
			case SCREEN_SIZE_N_L:
				$width =  360;
				$height = 480;
				break;
		}
		
		if(empty($width) || empty($height)){
			
			$resolution->width = 1080;
			$resolution->height = 1440;
		}else{
			$resolution->width = $width;
			$resolution->height = $height;
			
		}
		
		
		
		$this->resolution = $resolution;
		
		return true;
		
	}
	
	function GetImage($imName){
		
		$opNeeded = false;
		$this->imName = $imName;
		$imDirBase = $this->imagesBase;
		$usePath = $imDirBase;
		$originalImage = $usePath.DIRECTORY_SEPARATOR.$imName;

		$imResolutionDirBase = $imDirBase.DIRECTORY_SEPARATOR.$this->resolution->width.'x'.$this->resolution->height;
		
		if(file_exists($originalImage))
		{
			$picData = getimagesize($originalImage);
			
			if(false != $picData){
				
				$originalWidth  = intval($picData['0']);
				if($originalWidth > $this->resolution->width){
					$opNeeded = true;
				}
			}
		}
		if(false && false == $opNeeded){
			
			return  $originalImage;
		}else{
			
			if(is_dir($imResolutionDirBase)){
				$usePath = $imResolutionDirBase;
			}else{
				if($this->CreateResolutionDir($imResolutionDirBase)){ /* Sadece eger dizin yoksa olusturur */
					$usePath = $imResolutionDirBase;
				}
			}
			if(is_dir($usePath)){
				
				if(file_exists($usePath.DIRECTORY_SEPARATOR.$imName)){
					
					return $usePath.DIRECTORY_SEPARATOR.$imName;
				}else{
					$image = $usePath.DIRECTORY_SEPARATOR.$imName;
					$this->imPath = $usePath;
					
					
					$newImage = &$this->CreateResizedImage($this->resolution->width, $this->resolution->height);
	
					if(false != $newImage){
						
						if($this->SaveResizedImage($newImage, $image)){
							
							return  $image;
						}
						
					}
					
		
				}
			}
		}
		return false;
		
	}
	function SaveResizedImage(&$newImage, &$destination)
	{
		
		$returnVal = Imagejpeg($newImage, $destination, 80 );
		chmod($destination, 775);
		imagedestroy($newImage);
		return $returnVal;
	}
	function CreateResolutionDir($imResolutionDirBase)
	{
		$returnVal = false;
		if(is_dir($imResolutionDirBase)){
			if(opendir($imResolutionDirBase)){
				$returnVal =  true;
			}
		}else{
			if(mkdir($imResolutionDirBase)){
				chmod($imResolutionDirBase, 775);
				return true;
			}
		}
		
		return $returnVal;
	}
	function FitResize($max_width, $max_height){
		
	}
	function &CreateResizedImage($newWidth, $newHeight)
	{
		$returnVal = false;
		$originalImage = $this->imagesBase.DIRECTORY_SEPARATOR.$this->imName;
		
		if(file_exists($originalImage))
		{
			$picData = getimagesize($originalImage);
			
			if(false != $picData){
				
				$width   = intval($picData['0']);
				$height  = intval($picData['1']);
				$mime = $picData['mime'];

				if($width > $newWidth){

					$ratio   = $width / $newWidth;	/* Orjinal resmin genisliginin yeni genislige orani */

					$newHeight = $height / $ratio;
				}else{
					$newWidth = $width;
					$newHeight = $height;

				}


				/*
				if($width <= $newWidth){
					$newWidth = $width;
					$newHeight= $height / $ratio;
				}else{

		
					$newWidth = $newWidth;
					$newHeight= floor($height / $ratio);
				}*/
				
				if($mime == "image/gif")
					$source=imagecreatefromgif($originalImage);
				elseif($mime == "image/jpeg")
					$source=imagecreatefromjpeg($originalImage);
				elseif($mime == "image/jpg")
					$source=imagecreatefromjpeg($originalImage);
				elseif($mime == "image/bmp")
					$source=imagecreatefromwbmp($originalImage);
				elseif($mime == "image/x-pn")
					$source=imagecreatefrompng($originalImage);
				elseif($mime == "image/png")
					$source=imagecreatefrompng($originalImage);
				else
					$source	= imagecreatefromjpeg($originalImage);
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
				ob_start();
				$picture=ob_get_contents();
				ob_end_clean();
				
				imagedestroy($dest);
				*/
			}
		}
		return $returnVal;
	}
	function ImageCreateFromBMP(){
		
	}
	
}