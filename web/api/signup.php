<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');

    // ******************************************************************************************************
    // INCLUDE FILES
    // ******************************************************************************************************
    $project_name_prefix = "api_";
    require( "../lib/environment.php" );
    require( "../lib/Smarty.class.php" );
    require( "../lib/UserSmarty.php" );
    require( "../lib/lang.php" );
    require( "../lib/inc.php" );
    require( "../lib/check.php" );
    require( "../lib/project.php" );

    // *****************************
    // NG返却関数
    // *****************************
    function _ngReturn($errMsg){
        global $conn;

        if($conn!==null){
            _dbDisconnect( $conn );    
        }
        $return_arr = array();
        $return_arr['status'] = "NG";
        $return_arr['error_message'] = $errMsg;
        header('Content-type: application/json;  charset="UTF-8"');
        jsonPush($return_arr);
        exit();
    }

    $return_arr = array();
    $return_arr['status'] = "OK";
    $return_arr['error_message'] = "";

    if($_request['evekey']==""){
        _ngReturn( "URLが正しくありません。" );
    }elseif($_request['login_id']=="" || $_request['login_pass']=="" ){
        _ngReturn( "ログインIDまたはパスワードが指定されていません。" );
    }else{
        $conn = _dbConnect();

        $event_recs = _select("select * from m_event where event_url_key='"._as($_request['evekey'])."' and event_delete_date is null");
        if(count($event_recs)==0){
            _ngReturn( "現在は存在しないイベントのURLです。" );
        }
        $event_rec = $event_recs[0];

        //入力書式チェック
        $chks = array(
                        "login_id,ログインID（メールアドレス）"                => "need,email",
                        "login_pass,パスワード"                                => "need,eisuubar,min=4",
                        "access_tantou_mail,日本アクセス担当者メールアドレス"  => "need,email",
                        "user_name,氏名"                                       => "need",
                        "user_name_kana,"                                      => "need,zenkana",
                        "kigyou_name,企業名"                                   => "need",
                        "kigyou_name_kana,企業名カナ"                          => "need,zenkana",
                      );
        $err_msg = _check( $chks , $_request );

        // ID(メアド)から実際のメールアドレス部分抽出
        $real_user_mail_addr = _getMailAddressFromID( $_request['login_id'] );
        // emailアドレスの形式チェック
        if ( _emailCheck($real_user_mail_addr, '') === false ){
            $err_msg[]  = "メールアドレスを正しく入力して下さい。";
        }

        if( _count($err_msg)==0 ){
            $sql = "";
            $sql .= " select admin_id"."\n";
            $sql .= " from v_admin"."\n";
            $sql .= " where admin_delete_date is null"."\n";
            $sql .= "   and admin_mail = '"._as($_request['access_tantou_mail'])."'"."\n";
            $admin_recs = _select($sql);
            if ( _count($admin_recs)==0 ){
                _ngReturn( "該当する日本アクセス担当者がいません。" );
            }
            $user_admin_id = $admin_recs[0]['admin_id'];
        }

        if( _count($err_msg)==0 ){
            $where = "";
            $where .= "   user_delete_date is null ";
            $where .= "   and user_syounin_flg = 1"; // 2021.06.05 add
            $where .= "   and user_event_id = '" . _as( $event_rec['event_id'] )  . "'";
            // 2021/04/01 mod ------------------------ before ----------------------- 大幅変更
            // $where .= "   and user_mail = '" . _as( $_request['login_id'] )  . "'";
            // 2021/04/01 mod ------------------------ after ------------------------ 大幅変更
            $where .= "   and user_login_id = '" . _as( $_request['login_id'] )  . "'";
            // 2021/04/01 mod ------------------------ end   ------------------------ 大幅変更

            //DB読んでチェック
            $sql  = "";
            $sql .= " select * from v_user ";
            $sql .= " where ".$where;
            $managechk_recs = _select( $sql);
            if( _count( $managechk_recs ) > 0 ){
                _ngReturn( "このメールアドレスは既に登録済みです。" );

            }else{
                _query($conn,"begin");

                $max_recs = _select( "select coalesce(max(substring(user_id,2)),'0') as max_id from m_user");
                $w_id     = sprintf("u%08d", $max_recs[0]['max_id'] + 1 );

                $insert_r = array();
                $insert_r_n = array();
                $insert_r_m = array();

                $insert_r['user_id']                 = "'"._as($w_id)."'";
                $insert_r['user_event_id']           = "'"._as($event_rec['event_id'])."'";
                $insert_r['user_admin_id']           = "'"._as($user_admin_id)."'";
                // $insert_r['user_vip_flg']         = "'"._as($_request['user_vip_flg'])."'";
                // $insert_r['user_big_cate']        = "'"._as($_request['user_big_cate'])."'";
                // $insert_r['user_mid_cate']        = "'"._as($_request['user_mid_cate'])."'";
                $insert_r['user_kigyou_name']        = "'"._as($_request['kigyou_name'])."'";
                $insert_r['user_kigyou_name_kana']   = "'"._as($_request['kigyou_name_kana'])."'";
                $insert_r['user_busyo']              = "'"._as($_request['busyo'])."'";
                $insert_r['user_yakusyoku']          = "'"._as($_request['yakusyoku'])."'";
                $insert_r['user_pass']               = "'"._as( md5($_request['login_pass']) )."'";
                // $insert_r['user_biko']            = "'"._as($_request['login_id'])."'";
                $insert_r['user_raijyou_yotei_time'] = "'".""."'";
                $insert_r['user_web']                = 1;
                $insert_r['user_tag']                = "'".""."'";
                $insert_r['user_mail_send_kbn']      = 0;
                $insert_r['user_syounin_flg']        = 1; //'WEB招待の承認フラグ(0:未承認、1:承認済み)',
                $insert_r['user_insert_date']        = "'".date('Y-m-d H:i:s')."'";
                $insert_r['user_update_date']        = "'".date('Y-m-d H:i:s')."'";

                $insert_r_n['un_user_id']                 = "'"._as($w_id)."'";
                $insert_r_n['un_user_name']               = "'"._as($_request['user_name'])."'";
                $insert_r_n['un_user_name_kana']          = "'"._as($_request['user_name_kana'])."'";

                $insert_r_m['um_user_id']                 = "'"._as($w_id)."'";
                // 2021/04/01 mod ------------------------ before ----------------------- 大幅変更
                // $insert_r_m['um_user_mail']               = "'"._as($_request['login_id'])."'";
                // 2021/04/01 mod ------------------------ after ------------------------ 大幅変更
                $insert_r_m['um_user_mail']          = "'"._as($real_user_mail_addr)."'";
                $insert_r_m['um_user_login_id']      = "'"._as($_request['login_id'])."'";
                // 2021/04/01 mod ------------------------ end   ------------------------ 大幅変更

                _insert( 'm_user', $insert_r);
                _insert( 'm_uname', $insert_r_n);
                _insert( 'm_umail', $insert_r_m);

                _query($conn,"commit");
            }
        }
        if(count($err_msg) > 0){
            _ngReturn( $err_msg[0] );
        }

        _dbDisconnect( $conn );
    }

    header('Content-type: application/json;  charset="UTF-8"');
    jsonPush($return_arr);
    exit();
