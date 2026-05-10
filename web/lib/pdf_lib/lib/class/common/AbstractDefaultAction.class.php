<?php

require_once(dirname( __FILE__ ) . "/common.inc.php" );
require_once(dirname( __FILE__ ) . "/Config.class.php" );
require_once(dirname( __FILE__ ) . "/IUseDb.class.php" );
require_once(dirname( __FILE__ ) . "/IUseSession.class.php" );
require_once(dirname( __FILE__ ) . "/IUseSmarty.class.php" );
require_once(dirname( __FILE__ ) . "/IAction.class.php" );
require_once(dirname( __FILE__ ) . "/HttpRedirect.class.php" );
require_once(dirname( __FILE__ ) . "/Utils.class.php" );
require_once(dirname( __FILE__ ) . "/SmartyUtils.class.php" );



abstract class AbstractDefaultAction implements IUseDb,IUseSession,IUseSmarty,IAction {

	protected $db = null;
	protected $smarty = null;
	protected $session = null;
    protected $request = null;
	protected $frame = "";
	protected $actionName = "";
	protected $methodName = "";
	protected $actionMethodName = "";
	protected $template = "";
	public $config;
	protected function init($method) {
	
	}
	
	public function execute() {
        
        $this->request = $_REQUEST;
        
		SmartyUtils::init($this->smarty);
		
		
		
		$this->config = Config::$config;
		if($this->smarty != null) {
			$system = array();
			if(Utils::isDevelop()) {
				$system = $this->config['system_dev'];
			} else {
				$system = $this->config['system'];
			}
			$this->smarty->assign("system", $system);
		}
		
		$this->db->begin();

		$method = $this->methodName;
		if($method == "") {
			$method = "index";
		}
		
		//$action =  str_replace("Action","",get_class($this));
		//$action = strtolower($action);
		$action = $this->actionName;
		
		$this->template = sprintf("%s_%s.html",$action, $method);
		
		
		if($this->smarty->templateExists("frame.html")) {
			$this->frame = "frame.html";
		} else {
			$this->frame = "";
		}
		
		$this->init($method);

		
		if(method_exists ($this , "request_" . $method) ) {
			call_user_func(array($this, "request_" . $method));
		}
		
		//template読込
		$this->smarty->assign("template", $this->template);
		$this->smarty->assignByRef('this', $this); 
		if($this->frame != "") {
			$this->smarty->display($this->frame);
		} else if($this->template != "") {
			$this->smarty->display($this->template);
		} else {
			//何もしない。
		}
		$this->db->commit();
	}
	
	
	protected function getDocRoot() {
		if(Utils::isDevelop()) {
			$system = $this->config['system_dev'];
		} else {
			$system = $this->config['system'];
		}
		return $system['url_doc_root'];
	}
	
	public function redirect($actionName, $methodName, $param = "") {
		HttpRedirect::redirect($actionName, $methodName, $param);
	}
	
	public function setDb($db) {
		$this->db = $db;
	}
	
	public function setSession(&$session) {
		$this->session = &$session;
	}
	
	public function setSmarty($smarty) {
		$this->smarty = $smarty;
	}
	
	public function setActionName($action) {
		$this->actionName = $action;
	}
	
	public function getActionName() {
		return $this->actionName;
	}


	public function setMethodName($method) {
		$this->methodName = $method;
	}
	
	public function getMethodName() {
		return $this->methodName;
	}
	
	public function setActionMethodName($actionMethodName) {
		$this->actionMethodName = $actionMethodName;
	}
	
	
	
}


?>