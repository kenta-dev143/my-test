<?php

require_once(dirname( __FILE__ ) . "/common.inc.php" );
class SmartyUtils {


	public static $isInit = false;
	
	public static function init(&$smarty) {

		if(self::$isInit) {
			return;
		}
		
		self::$isInit = true;
		$o = new SmartyUtils();
		$methods = get_class_methods("SmartyUtils");
		for($i=0; $i < sizeof($methods); $i++) {
			$method = "system_";
			for($j=0; $j < strlen($methods[$i]); $j++) {
				$s1 = substr($methods[$i], $j, 1);
				$s2 = strtolower($s1); 
				if($s1 != $s2) {
					$method .= "_";
				}
				$method .= $s2;
			}
			$smarty->registerPlugin("function",$method, array($o, $methods[$i]));
		}
		
	}
	
	public function outputCheckedString($params) {
	
		$type = $params['type'];
		if($type == "list" ) {
			
			$values = $params['values'];
			$target = $params['target'];
			if($values == null) {
				$values = array();
			}
			
			
			for($i=0; $i < sizeof($values); $i++) {
				if ( $values[$i] == $target) {
					return "checked";
				}
			}
			return "";
		
		} else {
			$value = $params['value'];
			if(strlen($value) == 0 || $value == "0") {
				return "";
			}
			return "checked";
		}
		
		
	}


	public function outputRadioCheckedString($params) {
	
		$target = $params['target'];
		$value = $params['value'];
		if($target == $value) {
			return "checked";
		}
		return "";
		
	}

	public function outputSelectedValueName($params) {
	
		$value = $params['value'];
		$suffix = $params['suffix'];
		$prefix = $params['prefix'];
		$list = $params['list'];
		if( $list == null) {
			$list = $params['options'];
		}
		
		if($value === null || $value === "") {
			$value = $params['selected'];
		}
		
		if($value === null || $value === "") {
			return "";
		}
		
		$s = sprintf("%s%s%s",$prefix, $list[$value], $suffix);
		return htmlspecialchars($s);
	}
	
}

?>
