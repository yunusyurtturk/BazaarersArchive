<?php
$basePath = $_SERVER["DOCUMENT_ROOT"].'/oop';


class CMisc
{

	static function TimeDiffToString($time){
		
		if(!is_numeric($time))
			return false;
		$returnVal = '';
		if($time + 60 > time())
			$returnVal = time() - $time.' '._('seconds ago');
		else if(time() < $time + 60*60)
			$returnVal = floor((time() - $time)/60).' '._('minutes ago');
		else if(time() < $time + 60*60*24)
			$returnVal = floor((time() - $time)/(60*60)).' '._('hours ago');
		else if(time() < $time+ 60*60*24*30)
			$returnVal = floor((time() - $time)/(60*60*24)).' '._('days ago');
		else if(time() < $time + 60*60*24*30*12)
			$returnVal = floor((time() - $time)/(60*60*24*30)).' '._('months ago');
		else
			$returnVal = floor((time() - $time)/ (60 * 60 * 24 * 30 * 12)).' '._('years ago');
		return $returnVal;
	}
	static function IsEmail($email){
		$returnVal = false;
		if(filter_var($email, FILTER_VALIDATE_EMAIL)){
			$returnVal = true;
		}
		return $returnVal;
	}
	static function Hash($value)
	{
		return sha1($value);
	}
	static function CreateRandomString($length)
	{
		$chars = "abcdefghijklmnopqrstuvwxyz1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$numberofchars = strlen($chars);
		$random_str='';
		for ($ras = 0; $ras < $length; $ras++) {
			$get_value = rand(0,$numberofchars-1);
			$random_str .= $chars[$get_value];
		}
		return $random_str;
	}
	static function ReArrayFiles(&$file_post) {
		
		$file_ary = array(array());
		if(is_array($file_post) && count($file_post) > 0){

			$file_count = count($file_post['name']);
			$file_keys = array_keys($file_post);
			
			for ($i=0; $i<$file_count; $i++) {
				foreach ($file_keys as $key) {
					@$file_ary[$i][$key] = $file_post[$key][$i];
				}
			}
		}
		return $file_ary;
	}
	private static  function ConvertByteToReadable($mem_usage)
	{
	    if ($mem_usage < 1024) 
            return  $mem_usage." bytes"; 
        elseif ($mem_usage < 1048576) 
            return round($mem_usage/1024,2)." kilobytes"; 
        else 
            return round($mem_usage/1048576,2)." megabytes"; 
	}
	
	static  function GetMemoryUsage($size){
		return self::ConvertByteToReadable(memory_get_usage(true)); 
	}
	
	static  function GetPeakMemoryUsage(){
		return self::ConvertByteToReadable(memory_get_peak_usage (true)); 
	}


	static function BufferOn(){
		ob_start();
	}
	static function BufferOff($clean = true){
		if($clean){
			ob_end_clean();
		}else{
			ob_end_flush();
		}
	}
	static function GetBufferContent(){
		return ob_get_contents();
	}
	static function CleanBufferContent(){
		ob_clean();
	}
	static function DeleteTempPics(){
		
	}
	static function GenerateSiteMap(){
		
	}
	static function IsURL(){
		
	}
	static function StringToURL(){
		
	}
	
	
}