<?php


    // ******************************************************************************************************
    // INCLUDE FILES
    // ******************************************************************************************************
    $project_name_prefix = "enquete_";
    require( "../lib/environment.php" );
    
    require( "../lib/UserBlade.php" );
    require( "../lib/lang.php" );
    require( "../lib/inc.php" );
    require( "../lib/check.php" );
    //require( "../lib/picture.php" )z;
    require( "../lib/project.php" );

    if($_request['evekey']==""){
        _disp404();
    }

    $blade = new UserBlade();

    $_target_dir = _SYSTEM_ROOT_DIR."/";
    $php_dir = $_target_dir;


    // ******************************************************************************************************
    // PHP処理
    // ******************************************************************************************************

    if( _file_exists(_SYSTEM_ROOT_DIR."/enquete/sub/" . $_request['page'] . '.php' ) ){
        $conn = _dbConnect();
        //_query( $conn, "begin" );

        $event_recs = _select("select * from m_event where event_url_key='"._as($_request['evekey'])."' and event_delete_date is null");
        if(_count($event_recs)==0){
            _disp404();
        }
        $event_rec = $event_recs[0];

        require( _SYSTEM_ROOT_DIR."/enquete/sub/" . $_request['page'] . '.php' );

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
    $blade->assign('_CHARSET_OUTPUT',_CHARSET_OUTPUT);
    $blade->assign('_PROJECT_DISP_NAME', _PROJECT_DISP_NAME);
    $blade->assign('_SYSTEM_ROOT_URL', _SYSTEM_ROOT_URL);
    $blade->assign('_SYSTEM_ROOT_URLS', _SYSTEM_ROOT_URLS);
    $blade->assign('_SYSTEM_ROOT_DIR', _SYSTEM_ROOT_DIR);
    $blade->assign( '_COPYRIGHT', _COPYRIGHT );
    $blade->assign( 'contents_tpl', $contents_tpl );
    $blade->assign( 'contents_title', $contents_title );
    $blade->assign('login', $_SESSION[_PROJECT_NAME]['user_login']);
    $blade->assign( 'page', $_request['page'] );
    $blade->assign( 'err_msg', $err_msg );
    $blade->assign( 'ime_mode', $ime_mode );
    $blade->assign('rand', rand());
    $blade->assign('ss', session_id());
    $blade->assign('success_msg', $success_msg);
    $blade->assign('smartphone_kbn', $smartphone_kbn);
    $blade->assign('event_rec', $event_rec);



// ******************************************************************************************************
    // Bladeで画面を表示
    // ******************************************************************************************************
    $blade->template_dir = _SYSTEM_ROOT_DIR."/views/enquete";

    if($_request['page'] == "login"){
        //ログイン画面の場合
        $blade->display( $_request['page'] );
    }else{
        $blade->display( preg_replace('/\.html$/', '', $contents_tpl) );
    }
