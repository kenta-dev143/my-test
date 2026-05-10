<?php


    // ******************************************************************************************************
    // INCLUDE FILES
    // ******************************************************************************************************
    $project_name_prefix = "mypage_";
    require( "../lib/environment.php" );
    require( "../lib/Smarty.class.php" );
    require( "../lib/UserSmarty.php" );
    require( "../lib/lang.php" );
    require( "../lib/inc.php" );
    require( "../lib/check.php" );
    //require( "../lib/picture.php" )z;
    require( "../lib/project.php" );

    if($_request['evekey']==""){
        _disp404();
    }

    $sm = new UserSmarty();

    // リクエストがパスワード再発行でなければ 2020.12.19 add
    if ( $_request['page'] == '' ) {
        $_request['page'] = "pre_registration";
    }


    // $parse = parse_url($_SERVER['REQUEST_URI']);
    // if($parse['path']==""){
    //     $parse['path'] = "/";
    // }
    // $_fld = explode("/",$parse['path']);
    // if($_fld[_count($_fld)-1]!=""){
    //     $contents_tpl = $_fld[_count($_fld)-1];
    //     $_fld[_count($_fld)-1] = "";
    // }else{
    //     $contents_tpl = "index.html";
    // }
    // $_req_uri = join("/",$_fld);

    // $_root_dir = _SYSTEM_ROOT_DIR;
    // $_target_dir = $_root_dir . $_req_uri;    
    // $php_dir = $_target_dir;

    $_target_dir = _SYSTEM_ROOT_DIR."/";
    $php_dir = $_target_dir;

    // ******************************************************************************************************
    // PHP処理
    // ******************************************************************************************************

    if( _file_exists(_SYSTEM_ROOT_DIR."/signup/sub/" . $_request['page'] . '.php' ) ){
        $conn = _dbConnect();
        //_query( $conn, "begin" );

        $event_recs = _select("select * from m_event where event_url_key='"._as($_request['evekey'])."' and event_delete_date is null");
        if(_count($event_recs)==0){
            _disp404();
        }
        $event_rec = $event_recs[0];

        require( _SYSTEM_ROOT_DIR."/signup/sub/" . $_request['page'] . '.php' );

        //_query( $conn, "commit" );
        _dbDisconnect( $conn );

    // } elseif( _file_exists( $_target_dir . $_request['page'] . '.html' ) ){
    //     $contents_tpl = $_request['page'] . '.html';
    } else {
        _disp404();
    }


    // ******************************************************************************************************
    // ASSIGN
    // ******************************************************************************************************
    $sm->assign('_CHARSET_OUTPUT',_CHARSET_OUTPUT);
    $sm->assign('_PROJECT_DISP_NAME', _PROJECT_DISP_NAME);
    $sm->assign('_SYSTEM_ROOT_URL', _SYSTEM_ROOT_URL);
    $sm->assign('_SYSTEM_ROOT_URLS', _SYSTEM_ROOT_URLS);
    $sm->assign('_SYSTEM_ROOT_DIR', _SYSTEM_ROOT_DIR);
    $sm->assign( '_COPYRIGHT', _COPYRIGHT );
    $sm->assign( 'contents_tpl', $contents_tpl );
    $sm->assign( 'contents_title', $contents_title );
    $sm->assign('login', $_SESSION[_PROJECT_NAME]['user_login']);
    $sm->assign( 'page', $_request['page'] );
    $sm->assign( 'err_msg', $err_msg );
    $sm->assign( 'ime_mode', $ime_mode );
    $sm->assign('rand', rand());
    $sm->assign('ss', session_id());
    $sm->assign('success_msg', $success_msg);
    $sm->assign('smartphone_kbn', $smartphone_kbn);
    $sm->assign('event_rec', $event_rec);
    $sm->assign('evekey', $_request['evekey']);
    $sm->assign('direct_login', $_SESSION[_PROJECT_NAME]['direct_login']);


    // ******************************************************************************************************
    // Smartyで画面を表示
    // ******************************************************************************************************
    $sm->template_dir = _SYSTEM_ROOT_DIR."/signup/".$_request['evekey']."_templates/";
    $sm->config_dir   = _SYSTEM_ROOT_DIR."/signup/".$_request['evekey']."_templates/";

    if($_request['page'] == "login"){
        //ログイン画面の場合
        _smartyDisplay( $sm, $_request['page'].".html" );
    }else{
        _smartyDisplay( $sm, $contents_tpl );
    }
