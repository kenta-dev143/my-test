<?php
    if( !defined("_PROJECT_DISP_NAME") ){
        die("System Error");
    }

    if( $_SESSION[_PROJECT_NAME]['admin_login']['admin_id'] == "" ){
        die('System Error');
    }

    // ******************************************************************************************************
    // 初期値
    // ******************************************************************************************************
    $page = $_request['page'];
    $this_sess = &$_SESSION[_PROJECT_NAME][$page];
    $err_msg = array();

    $login_id   = $_SESSION[_PROJECT_NAME]['admin_login']['admin_id'];
    $admin_mail = $_SESSION[_PROJECT_NAME]['admin_login']['admin_mail']; // 2021.05.28 add


    // ******************************************************************************************************
    // 登録・更新・削除
    // ******************************************************************************************************
    if($_request['exec'] == 'save'){

        if( $this_sess['token'] != $_request['token'] ){
            $err_msg[] = 'このデータは処理できませんでした。';
        }elseif( _count( $this_sess ) <= 0 ){
            //**** リロードやボタンダブルクリックでの２重登録抑制
            $err_msg[] = 'このデータは既に処理済みです。';
        }else{
            $chks = array(
                            "admin_login_pass,新しいパスワード" => "need,min=4,match=admin_login_pass_chk",
                            "admin_login_pass_chk,新しいパスワード（確認用）" => "need",
                        );

            $err_msg = _check( $chks, $_request );

            //**** POST値をセッションにマージ ****
            $this_sess = _array_merge( $this_sess, $_request );

            if(_count($err_msg) == 0){

                _query($conn,'begin');

                $array = array();
                $array['admin_login_pass']             = "'"._as( md5($this_sess['admin_login_pass']) )."'";
                $array['admin_update_date']            = "'".$_now_timestamp."'";

                $where = "admin_id='"._as( $login_id )."'";

                _update( 'm_admin', $array, $where );

                // まだ終了していないイベント
                $sql  = "";
                $sql .= " select event_id"."\n";
                $sql .= " from m_event"."\n";
                $sql .= " where event_delete_date is null"."\n";
                $sql .= "  and event_raikainri_ymd_ed > '".date("Y/m/d",strtotime(date("Y/m/d")."-1month"))."'"."\n";
                $sql .= " order by event_id"."\n";
                $active_event_recs = _select( $sql );
                for ($loop=0; $loop < _count($active_event_recs); $loop++) {

                    // 来場者情報の存在チェック
                    $sql = "";
                    $sql .= " select user_id, user_pass"."\n";
                    $sql .= " from v_user"."\n";
                    $sql .= " where user_delete_date is null"."\n";
                    $sql .= "  and user_event_id = '"._as($active_event_recs[ $loop ]['event_id'])."'"."\n";
                    $sql .= "  and user_login_id = '"._as($admin_mail)."'"."\n";
                    $chk_rec = _select( $sql );
                    if ( $chk_rec[0]['user_id'] != '' ){
                        // あればパスワードを更新する
                        $where = "user_id = '"._as($chk_rec[0]['user_id'])."'";
                        $array = array();
                        $array['user_pass']            = "'"._as(md5($this_sess['admin_login_pass']) )."'"; //ログインパスワード(暗号化)',
                        $array['user_update_date']     = "'".$_now_timestamp."'"; //更新日時',
                        _update( 'm_user', $array, $where);
                    }
                }


                $success_msg = "パスワードを変更しました。";

                _query($conn,'commit');

                $_request['exec'] = "";

                $_SESSION[_PROJECT_NAME][$page] = array();
                unset( $_SESSION[_PROJECT_NAME][$page] );
                unset( $this_sess );
                $this_sess = array();

            }
        }
    }

    // ******************************************************************************************************
    // 初期・完了画面
    // ******************************************************************************************************
    if( $_request['exec'] != 'save' && _count($err_msg) == 0 ){
        $token = rand();
        unset( $_SESSION[_PROJECT_NAME][$page] );
        unset( $this_sess );
        $this_sess = &$_SESSION[_PROJECT_NAME][$page];

        // 編集
        $sql  = "";
        $sql .= " select ";
        $sql .= "   * ";
        $sql .= " from v_admin ";
        $sql .= " where ";
        $sql .= "     admin_delete_date is null";
        $sql .= "     and admin_id ='"._as($login_id)."'";
        $main_rec = _select($sql);
        $this_sess = $main_rec[0];
        $this_sess['token'] = $token;
    }

    _setAssign($blade,$this_sess);

    $contents_title = "パスワード変更";
    $active_menu = "admin_pass_change";
    $contents_tpl = "admin_pass_change";
