<?php

require_once(dirname( __FILE__ ) . "/common.inc.php" );
class Config {

	public static $config = null;
	public function init($additionalFileName = null) {
	
		$f = dirname( __FILE__ ) . "/../../config/default.ini";
		$base = parse_ini_file($f, true);
		
		self::arrayReplaceValues($base);
		
		
		$ar = array();
		if($additionalFileName != null) {
			$f = DOC_ROOT . "/src/config/" . $additionalFileName;
			$ar = parse_ini_file($f, true);
			self::arrayReplaceValues($ar);
		}
		
		self::$config = self::arrayMerge($base, $ar);
		
	}
	
	public function append($additionalFileName) {
		
		if(self::$config == null) {
			self::init();
		}
		
		$f = DOC_ROOT . "/src/config/" . $additionalFileName;
		if(!file_exists ($f)) {
			return;
		}

		$ar = parse_ini_file($f, true);
		self::arrayReplaceValues($ar);
		self::$config = self::arrayMerge(self::$config, $ar);

	}
	
	public function appendByFilePath($path) {
	
		if(self::$config == null) {
			self::init();
		}
		
		if(!file_exists ($path)) {
			return;
		}
		
		$ar = parse_ini_file($path, true);
		self::arrayReplaceValues($ar);
		self::$config = self::arrayMerge(self::$config, $ar);
	
	}
	
	private function arrayMerge($base, $ar) {
		self::arrayMerge2($base, $ar);
		return $base;
	}

	private function arrayMerge2(&$base_array, $ar) {
		foreach ($ar as $key => $value) {
			if(is_array($ar[$key])) {
				self::arrayMerge2($base_array[$key], $ar[$key]);
				continue;
			}
			$base_array[$key] = $ar[$key];
		}
	}
	
	
	private function arrayReplaceValues(&$ar) {
		
		foreach ($ar as $key => $value) {
			if(is_array($ar[$key])) {
				self::arrayReplaceValues($ar[$key]);
				continue;
			}
			$ar[$key] = str_replace("{DOC_ROOT}", DOC_ROOT, $ar[$key]);
			$ar[$key] = str_replace("{SYS_ROOT}", SYS_ROOT, $ar[$key]);
			$base_array[$key] = $ar[$key];
		}
		
	}


}

?>
