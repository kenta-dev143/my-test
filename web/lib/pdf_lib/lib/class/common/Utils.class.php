<?php

require_once(dirname( __FILE__ ) . "/common.inc.php" );
class Utils {

	/** 開発環境かどうかを返す */
	public function isDevelop() {
		if( isset($HTTP_ENV_VARS['windir']) || isset($HTTP_ENV_VARS['WINDIR']) || getenv('windir') != ''){
			return true;
		}
		return false;
	}
	
	public function stringToArray($str, $delimiter = ",") {
	
		if($str === null) {
			$str = "";
		}
		$ar = explode($delimiter, $str);
		if($ar == null) {
			$ar = array();
		}
		return $ar;
	
	}
	
	
	public function arrayToString($ar, $delimiter = ",") {
		if($ar == null || !is_array($ar)) {
			return "";
		}
		
		$str = "";
		
		for($i=0; $i < sizeof($ar); $i++) {
		
			if($str != "") {
				$str .= $delimiter;
			}
			$str .= $ar[$i];
		}
		return $str;
	}
	
	public function createCodeNameArray($code_column, $name_column, $list, $with_empty = true, $empty_name_value="---") {
		$ret = array();
		
		if($with_empty) {
			$ret[''] = $empty_name_value;
		}
		for($i=0; $i < sizeof($list); $i++) {
			$code = $list[$i][$code_column];
			$name = $list[$i][$name_column];
			$ret[$code] = $name;
		}
		return $ret;
	}
	
	public function createCodeFormatNameArray($code_column, $name_columns, $format,  $list, $with_empty = true, $empty_name_value="---") {
	
		$ret = array();
		if($with_empty) {
			$ret[''] = $empty_name_value;
		}
		
		
		for($i=0; $i < sizeof($list); $i++) {
			$code = $list[$i][$code_column];
			$l = array();
			for($j=0; $j < sizeof($name_columns); $j++) {
				$name_column = $name_columns[$j];
				$name = $list[$i][$name_column];
				$l[] = $name;
			}
			$ret[$code] =  vsprintf ($format, $l);
		}
		return $ret;
	}
	
	public function timeStringToDateString($time) {
	
		if($time == "") {
			return $time;
		}
		$ar = explode(" ", $time);
		if($ar == null || sizeof($ar) < 2) {
			return $time;
		}
		return $ar[0];
	
	}
	
	
	public function getAge($birthday, $targetDay = null) {
	
		$birthday = str_replace ("-","",$birthday);
		$birthday = str_replace ("/","",$birthday);
		if($targetDay == null) {
			$targetDay = date('Ymd');
		}
		$targetDay = str_replace ("-","",$targetDay);
		$targetDay = str_replace ("/","",$targetDay);
	    return floor(($targetDay-$birthday)/10000);
	}
    
	public function prevDate($date) {
    	$t = strtotime(sprintf("%s -1 day", $date));
		return date('Y-m-d', $t);
    }

	public function nextDate($date) {
    	$t = strtotime(sprintf("%s +1 day", $date));
		return date('Y-m-d', $t);
    }    
    
    
    
		
    


    
	
}

?>
