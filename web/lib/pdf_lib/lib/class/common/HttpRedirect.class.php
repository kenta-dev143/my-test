<?php
require_once(dirname( __FILE__ ) . "/common.inc.php" );
require_once(dirname( __FILE__ ) . "/Config.class.php" );
class HttpRedirect {

	public function redirect($actionName, $methodName, $param = "") {
		
		if( Config::$config['common']['use_mod_rewrite']) {
			HttpRedirect::redirectHtml($actionName, $methodName, $param);
		} else {
			HttpRedirect::redirectPhp($actionName, $methodName, $param);
		}
		
		
		
		
		/**
		//とりあえずindexへ
		$str = sprintf("index.php?action=%s&method=%s");
		if( is_array($param) ) {
			foreach ($param as $key => $value) {
				$str .= sprintf("&%s=%s", $key, urlencode($value));
			}
		} else {
			$str .= "&" . $param;
		}
		header("Location: " . $str);
		exit;
		**/
	}
	
	private function redirectHtml($actionName, $methodName, $param = "") {

		
		$path = $actionName;
		if($methodName != "" && $methodName != "index") {
			$path .= "_" . $methodName;
		}
		
		
		if( $path != "" &&  substr( $path , strlen($path) - 1,  1) != "/") {
			$path .= ".html?";
		} else {
			$path .= "?";
		}
		
		if( is_array($param) ) {
			foreach ($param as $key => $value) {
				$path .= sprintf("&%s=%s", $key, urlencode($value));
			}
		} else {
			$path .= "" . $param;
		}
		
		
		header("Location: " . $path);
		exit;


	}


	private function redirectPhp($actionName, $methodName, $param = "") {
	
		$str = sprintf("index.php?action=%s&method=%s", $actionName, $methodName);
		if( is_array($param) ) {
			foreach ($param as $key => $value) {
				$str .= sprintf("&%s=%s", $key, urlencode($value));
			}
		} else {
			$str .= "&" . $param;
		}
		header("Location: " . $str);
		exit;

	}


}


?>