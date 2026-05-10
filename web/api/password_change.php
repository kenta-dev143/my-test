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
        if(_count($event_recs)==0){
            _ngReturn( "現在は存在しないイベントのURLです。" );
        }
        $event_rec = $event_recs[0];

        //入力書式チェック
        $chks = array(
                       "login_id,メールアドレス"                  => "need,email",
                       "login_pass,変更前パスワード"              => "need,eisuubar,min=4",
                       "new_login_pass,新パスワード"              => "need,eisuubar,min=4",
                       "new_login_pass_chk,新パスワード(確認用)"  => "need,eisuubar,min=4,match=new_login_pass"
                      );
        $err_msg = _check( $chks , $_request );

        // ID(メアド)から実際のメールアドレス部分抽出
        $real_user_mail_addr = _getMailAddressFromID( $_request['login_id'] );
        // emailアドレスの形式チェック
        if ( _emailCheck($real_user_mail_addr, '') === false ){
            $err_msg[]  = "メールアドレスを正しく入力して下さい。";
        }

        if(_count($err_msg)==0){
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
            if(_count( $managechk_recs ) > 0){

                if($managechk_recs[0]['user_pass'] == md5($_request['login_pass']) ){

                    if($managechk_recs[0]['user_web'] == "1"){

                        _query($conn,"begin");

                        $upd_r = array();
                        $upd_r['user_pass']        = "'"._as( md5($_request['new_login_pass']) )."'";
                        $upd_r['user_update_date'] = "'".date('Y-m-d H:i:s')."'";
                        $where = "user_id = '"._as($managechk_recs[0]['user_id'])."'";
                        _update( 'm_user', $upd_r, $where );
        
                        _query($conn,"commit");

                    }else{
                        _ngReturn( "WEB展示会（ガイドブック）には招待されていないアカウントです。" );
                    }

                }else{
                    _ngReturn( "ログインIDまたは変更前パスワードが不正です。" );
                }

            }else{
                _ngReturn( "ログインIDまたは変更前パスワードが不正です。" );
            }
        }
        if(_count($err_msg) > 0){
            _ngReturn( $err_msg[0] );
        }


        _dbDisconnect( $conn );
    }

    header('Content-type: application/json;  charset="UTF-8"');
    jsonPush($return_arr);
    exit();
