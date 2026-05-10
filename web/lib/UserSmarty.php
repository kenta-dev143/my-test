<?php

//class UserSmarty extends Smarty {
//2017/04/10 Mod
class UserSmarty extends SmartyBC {

   function UserSmarty()
   {

        //2017/04/10 Add
        parent::__construct();
        
        //2017/04/10 Del
        //$this->Smarty();

        //2017/04/10 Mod -------------- Before ---------------------
// #        $this->template_dir = '';
//         $this->compile_dir = _ROOT_CACHE_DIR . '/templates_c/';
//         //$this->config_dir = '';
//         $this->cache_dir = _ROOT_CACHE_DIR . '/cache/';
        //2017/04/10 Mod -------------- After ---------------------
        $this->setCompileDir( _ROOT_CACHE_DIR . '/templates_c/');
        $this->setCacheDir( _ROOT_CACHE_DIR . '/cache/' );
        //2017/04/10 Mod -------------- End ---------------------

        $this->caching = false;
        $this->force_compile = true;

   }


}
