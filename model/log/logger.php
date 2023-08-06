<?php

/**
 * Level [0-9]  : Bad users
 * Level [10-19]: Unexpected results
 * 
 */
require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');

require_once(BASE_PATH.'/model/class_base_model_with_db.php');
require_once(BASE_PATH.'/model/defs/global_definitions.php');

class CLogger
{
	private $uid;
	private static $instance = null;
	private $db;
	
	private function __construct($uid, array $dependicies = array())
	{
		if(isset($dependicies['db'])){
			$this->db = $dependicies['db'];
		}else{
	
			$this->DIContainer = new CContainer();
			$this->db = $this->DIContainer->GetDBService(true);
		}
		
	}
	
	public static function GetLogger()
	{
		if(null == self::$instance){
			
			$container = new CContainer();
			$db = $container->GetDBService(true);
			
			self::$instance = new CLogger($db);
			
			return self::$instance;
		}else{
			
			return self::$instance;
		}
	}
	
	public static function Dump($dump){
		
		$returnVal = '';
		foreach( $dump as $k=>$v )
		{
			if(is_object($v) || is_array($v)){
				
				 $returnVal .= $k . ' : ' . self::Dump($v) ."\n";
			}
			else{
				$returnVal .= $k . ' : ' . $v ."\n";
			}
		}
		return $returnVal;
	}
	public static function &GetVarDumpStr($dump){
		ob_start();
		print_r($dump);
		$returnVal = ob_get_contents();
		ob_end_clean();
		// [yy] daha iyisini bul
		$returnVal = str_replace('root', 'user', $returnVal);
		$returnVal = str_replace('132423132', '111111111', $returnVal);
		return $returnVal;
		
	}

	function DLog($uid = 0, $methodName, $className, $callingParams, $url, $additionalInfo,  $message)
	{
		$this->Log($uid = 0, $methodName, $className, $callingParams, $url, $additionalInfo, 31,  'Debug:'.$message);
	}
	function Log($uid = 0, $methodName, $className, $callingParams, $url, $additionalInfo, $level,  $message)
	{	
		/* Zaman
		 * Fonksiyon Adi
		 * Dosya Adi
		 * Fonksiyon Parametreleri
		 * Hangi URL ve REQUEST'lerle geldigi
		 * Log Seviyesi
		 */
		$paramsStr = '';
        try {
            $paramsStr = serialize($callingParams);


            $this->db->Prepare('INSERT INTO logs (uid, method, class, time, methodParams, url, additionalInfo, level, message)
                    VALUES
                    (:uid, :method, :class, :time, :methodParams, :url, :additionalInfo, :level, :message)');

            $params[] = new CDBParam('uid', $uid, PDO::PARAM_INT);
            $params[] = new CDBParam('method', $methodName, PDO::PARAM_STR);
            $params[] = new CDBParam('class', $className, PDO::PARAM_STR);

            $params[] = new CDBParam('time', time(), PDO::PARAM_INT);


            $params[] = new CDBParam('methodParams', $paramsStr, PDO::PARAM_STR);
            $params[] = new CDBParam('url', $url, PDO::PARAM_STR);
            $params[] = new CDBParam('additionalInfo', $additionalInfo, PDO::PARAM_STR);
            $params[] = new CDBParam('level', $level, PDO::PARAM_INT);
            $params[] = new CDBParam('message', $message, PDO::PARAM_STR);


            $this->db->EnableErrorInfo();
            $this->db->Execute($params);
        }catch (Exception $e){


        }
	
	}
	
}