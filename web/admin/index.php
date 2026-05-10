<?php
    // ******************************************************************************************************
    // INCLUDE FILES
    // ******************************************************************************************************
    $project_name_prefix = "admin_";
    require( "../lib/environment.php" );
    require( "../lib/UserBlade.php" );
    require( "../lib/lang.php" );
    require( "../lib/inc.php" );
    require( "../lib/check.php" );
    require( "../lib/picture.php" );
    require( "../lib/project.php" );

    $blade = new UserBlade();

    if($_request['err_message']!=""){
        $mail_mess = "";
        $mail_mess .= base64_decode( $_request['err_message']);
        $mail_mess .= "\n-----------------------------------------------------------------------\n";
        $mail_mess .= "\$_SERVER\n";
        $mail_mess .= "-----------------------------------------------------------------------\n";
        ob_start();
        print_r($_SERVER);
        $mail_mess .= ob_get_contents();
        ob_end_clean();

        $mail_mess .= "\n-----------------------------------------------------------------------\n";
        $mail_mess .= "\$_SESSION\n";
        $mail_mess .= "-----------------------------------------------------------------------\n";
        ob_start();
        print_r($_SESSION);
        $mail_mess .= ob_get_contents();
        ob_end_clean();

        _sendMail("syougai.err.tuuchi@gmail.com", "syougai.err.tuuchi@gmail.com", "【"._PROJECT_DISP_NAME."】障害報告", $mail_mess );
    }

    // ******************************************************************************************************
    // ログインに成功した後の初期ページ名
    // ******************************************************************************************************
    //DB接続
    $conn = _dbConnect();

    $_login_after_page = "kaijyou_syuukei";

    // ログイン済、かつ パスワード再設定の場合
    if( $_SESSION[_PROJECT_NAME]['admin_login']['admin_id'] == "" && $_request['page']!="pass_reissue" && $_request['page']!="admin_pass_set"){
        if ($_request['page'] == 'company_request_list' || $_request['page'] == 'company_request_edit') {
            _set_login_after_page($_request['page']);
        }
        $_request['page'] = "login";
    }elseif( $_request['page'] == "" ){
        // 集計閲覧権限（0:全て閲覧可、1:エリアのリアルタイム人数のみ）
        if ($_SESSION[_PROJECT_NAME]['admin_login']['admin_kyouryoku_kigyou_flg'] == 1) {
            $_login_after_page = "user_list";
        } else if ( $_SESSION[_PROJECT_NAME]['admin_login']['admin_syuukei_etsuran_kengen'] == 1){
            // 会場エリア集計
            $_login_after_page = "area_syuukei";
        } else {
            // 会場全体集計
            $_login_after_page = "kaijyou_syuukei";
        }

        if ( $_SESSION[_PROJECT_NAME]['admin_login']['admin_mail'] != 'admin' ){
            $sql  = "";
            $sql .= " select * "."\n";
            $sql .= " from m_agreement"."\n";
            $sql .= " where agree_admin_id = '"._as($_SESSION[_PROJECT_NAME]['admin_login']['admin_id'])."'"."\n";
            $agree_rec = _select($sql);
            if ( _count($agree_rec) == 0 ){
                // 個人情報の同意画面
                $_login_after_page = "agreement";
            }
        }

        $_request['page'] = $_login_after_page;
    }

    if($_request['page']!=""){
        if( _eisuuBarCheck($_request['page'],'') == false ){
            _dispError();
        }
    }
    if($_request['from_page']!=""){
        if( _eisuuBarCheck($_request['from_page'],'') == false ){
            _dispError();
        }
    }

    // ******************************************************************************************************
    // PHP処理
    // ******************************************************************************************************
    //_query( $conn, "begin" );

    //プルダウン表示イベント取得
    $sql = "select * from  m_event where event_delete_date is null ";
    // 表示と権限1,2のみ表示
    if ($_SESSION[_PROJECT_NAME]['admin_login']['admin_id'] == 'a0000001' ||
        $_SESSION[_PROJECT_NAME]['admin_login']['admin_master_kengen'] == 1 ||
        ($_SESSION[_PROJECT_NAME]['admin_login']['admin_user_kengen'] == 0 &&
         $_SESSION[_PROJECT_NAME]['admin_login']['admin_syuukei_etsuran_kengen'] == 0)) {
         $sql .= "and (event_pulldown_disp_flg=1 or event_pulldown_disp_flg=2) ";
    }
    // 表示のみ
    else {
        $sql .= "and event_pulldown_disp_flg=1 ";
    }

    $sql .= " order by event_kaisai_ymd_st asc";


    $pull_event_recs = _select($sql);
    $_conf_puldown_event = array();
    $fst_event_id = "";
    $select_event_id = "";
    for ($i=0; $i < _count($pull_event_recs); $i++) {

        if($fst_event_id=="") $fst_event_id = $pull_event_recs[$i]['event_id'];

        $_conf_puldown_event[ $pull_event_recs[$i]['event_id'] ] = $pull_event_recs[$i]['event_pulldown_name'];

    }

    if($_SESSION[_PROJECT_NAME]['select_event_id']==""){
        $_SESSION[_PROJECT_NAME]['select_event_id'] = $select_event_id;
    }
    if($_request['eve_chg']=="1"){
        $select_event_id = $_request['select_event_id'];
        $_SESSION[_PROJECT_NAME]['select_event_id'] = $select_event_id;
    }
    $blade->assign('_conf_puldown_event',$_conf_puldown_event);

    //プルダウン選択イベント取得
    $sql = "";
    $sql .= "select * from  m_event where event_id = '"._as($_SESSION[_PROJECT_NAME]['select_event_id'])."'";
    $select_event_recs = _select($sql);
    $select_event_rec = $select_event_recs[0];

    if( _file_exists( './sub/' . $_request['page'] . '.php' ) ){
        require( './sub/' . $_request['page'] . '.php' );
    }else{
        header("HTTP/1.0 404 Not Found");
        header( "Content-Type: text/html; charset=" . _CHARSET_OUTPUT );
        die( '<html><body>404 ページが見つかりません！</body></html>' );
    }


    //DB切断
    _dbDisconnect( $conn );

    // ******************************************************************************************************
    // ASSIGN
    // ******************************************************************************************************
    header( "Content-Type: text/html; charset=" . _CHARSET_OUTPUT );
    $blade->assign('_CHARSET_OUTPUT',_CHARSET_OUTPUT);
    $blade->assign('_PROJECT_DISP_NAME', _PROJECT_DISP_NAME);
    $blade->assign('_SYSTEM_ROOT_DIR', _SYSTEM_ROOT_DIR);
    $blade->assign('_SYSTEM_ROOT_URL', _SYSTEM_ROOT_URL);
    $blade->assign('_SYSTEM_ROOT_URLS', _SYSTEM_ROOT_URLS);
    $blade->assign( '_MANAGE_TITLE', _MANAGE_TITLE );
    $blade->assign( '_COPYRIGHT', _COPYRIGHT );
    $blade->assign( 'contents_tpl', $contents_tpl );
    $blade->assign('active_menu',$active_menu);
    $blade->assign( 'contents_title', $contents_title );
    $blade->assign('login', $_SESSION[_PROJECT_NAME]['admin_login']);
    $blade->assign( 'page', $_request['page'] );
    $blade->assign( 'err_msg', $err_msg );
    $blade->assign( 'err_fld_msg', $_GLOBAL_fld_msg );
    $blade->assign( 'ime_mode', $ime_mode );
    $blade->assign('rand', rand());
    $blade->assign('ss', session_id());
    $blade->assign('_login_after_page', $_login_after_page);
    $blade->assign('success_msg', $success_msg);
    $blade->assign('project_name_prefix', $project_name_prefix);
    $blade->assign('_KANKYOU_STR', $_KANKYOU_STR);
    $blade->assign('_UPLOAD_IMG_MAX_SIZE',$_UPLOAD_IMG_MAX_SIZE);
    $blade->assign('img_size_recs',$img_size_recs);
    $blade->assign('disp_img_size_recs',$disp_img_size_recs);
    $blade->assign('disp_mode',$disp_mode);
    $blade->assign('select_event_id',$_SESSION[_PROJECT_NAME]['select_event_id']);
    $blade->assign('select_event_rec',$select_event_rec);
    $blade->assign('flash_message', _get_flash_message());
    if( $_SERVER['SERVER_ADDR']=="172.29.1.10"){
        $server_info = "WEB1";
    }elseif( $_SERVER['SERVER_ADDR']=="172.29.2.10"){
        $server_info = "WEB2";
    }else{
        $server_info = "Other(".$_SERVER['SERVER_ADDR'].")";
    }
    $blade->assign('server_info',$server_info);

    // ******************************************************************************************************
    // Blade で画面を表示
    // ******************************************************************************************************
    $blade->template_dir = _SYSTEM_ROOT_DIR . '/views/admin';

    if($_request['page'] == "login" || $_request['page'] == "pass_reissue" || $_request['page'] == "agreement" || $_request['page'] == "admin_pass_set"){
        //ログイン画面の場合
        $blade->display( $_request['page'] );
    }elseif($_request['page'] == "ajax" ){
        //単一の独自画面などの場合(ポップアップ画面など、標準の管理画面のヘッダやサイドメニューが必要ないような画面の場合（※特別な画面）)
        $blade->display( preg_replace('/\.html$/', '', $contents_tpl) );
        exit();
    }else{
        //標準の管理画面
        $blade->display( "main" );
    }
