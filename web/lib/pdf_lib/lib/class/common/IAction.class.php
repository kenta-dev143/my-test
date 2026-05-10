<?php
require_once(dirname( __FILE__ ) . "/common.inc.php" );
interface IAction {

	public function setActionName($action);
	public function setMethodName($method);
	public function setActionMethodName($actionMethod);
	public function execute();
	
}






?>