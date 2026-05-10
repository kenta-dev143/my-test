<?php


    // ******************************************************************************************************
    // INCLUDE FILES
    // ******************************************************************************************************
    $project_name_prefix = "mypage_";
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


    //ログイン中だが、現在アクセスされたURLのイベントと異なるイベントのユーザーなら強制ログアウト
    if( $_SESSION[_PROJECT_NAME]['user_login']['user_id'] != "" ){
        if($_SESSION[_PROJECT_NAME]['user_login']['event_rec']['event_url_key'] != $_request['evekey']){
            unset($_SESSION[_PROJECT_NAME]['user_login']);
        }
    }

    $blade = new UserBlade();

    // ******************************************************************************************************
    // ログインに成功した後の初期ページ名
    // ******************************************************************************************************
    if($_request['ex']=="1"){
        $_login_after_page = "user_edit";
    }else{
        $_login_after_page = "mypage";
    }

    // リクエストがパスワード再発行でなければ 2020.12.19 add
    if ( $_request['page'] != 'pass_reissue' ) {
        if( $_SESSION[_PROJECT_NAME]['user_login']['user_id'] == "" ){
            $_request['page'] = "login";
        }elseif( $_request['page'] == "" ){
            $_request['page'] = $_login_after_page;
        }
    }

    if($_request['ex']=="1" && ($_request['page']=="" || $_request['page']=="mypage") ){
        $_request['page'] = "user_edit";
    }


    if($_request['page']!=""){
        if( _eisuuBarCheck($_request['page'],'') == false ){
            _dispError();
        }
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

    if( _file_exists(_SYSTEM_ROOT_DIR."/mypage/sub/" . $_request['page'] . '.php' ) ){
        $conn = _dbConnect();
        //_query( $conn, "begin" );

        $event_recs = _select("select * from m_event where event_url_key='"._as($_request['evekey'])."' and event_delete_date is null");
        if(_count($event_recs)==0){
            _disp404();
        }
        $event_rec = $event_recs[0];

        require( _SYSTEM_ROOT_DIR."/mypage/sub/" . $_request['page'] . '.php' );

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
    $blade->assign('evekey', $_request['evekey']);
    $blade->assign('direct_login', $_SESSION[_PROJECT_NAME]['direct_login']);


    // ******************************************************************************************************
    // Bladeで画面を表示
    // ******************************************************************************************************
    if($_request['ex']=="1"){
        $blade->template_dir = _SYSTEM_ROOT_DIR."/views/mypage_ex";
    }else{
        $blade->template_dir = _SYSTEM_ROOT_DIR."/views/mypage";
    }

    if($_request['page'] == "login"){
        //ログイン画面の場合
        $blade->display( $_request['page'] );
    }else{
        $blade->display( preg_replace('/\.html$/', '', $contents_tpl) );
    }
