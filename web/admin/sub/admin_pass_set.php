<?php
    if( !defined("_PROJECT_DISP_NAME") ){
        die("System Error");
    }
    if( !defined("_PROJECT_DISP_NAME") ){
        die("System Error");
    }

    // ******************************************************************************************************
    // 初期値
    // ******************************************************************************************************
    $page = $_request['page'];
    $this_sess = &$_SESSION[_PROJECT_NAME][$page];
    $err_msg = array();

    // #####
    if( $_request['setpw'] != '' ){
        $id_pass = _urlCodeDecode($_request['setpw']);
        list($admin_id,$pass) = explode("#", $id_pass,2);
        if ( $pass != '_NEED_PASS_SET_' ){
            die('このURLは無効です。');
        }

        if( _emailCheck($admin_id,'')==false){
            //admin_id だったのでこのまま
        }else{
            //メアドだったので、admin_idを取得
            $sql  = "";
            $sql .= " select ";
            $sql .= "   * ";
            $sql .= " from v_admin ";
            $sql .= " where ";
            $sql .= "   admin_delete_date is null";
            $sql .= "   and admin_mail = '"._as($admin_id)."'";
            $a_chk_rec = _select($sql);
            if( _count($a_chk_rec) == 0 ){
                die('このURLは無効です。');
            }else{
                $admin_id = $a_chk_rec[0]['admin_id'];
            }
        }


    } else {
        die('このURLは無効です。');
    }

    // ******************************************************************************************************
    // 登録・更新・削除
    // ******************************************************************************************************
    if($_request['exec'] == 'save'){
        if($this_sess['token'] != $_request['token']){
            $err_msg[] = 'このデータは処理できませんでした。';
        }elseif( _count( $this_sess ) <= 0 ){
            //**** リロードやボタンダブルクリックでの２重登録抑制
            $err_msg[] = 'このデータは既に処理済みです。';
        }else{
            if($_request['mode'] !='delete'){
                $chks = array(
                                "new_user_pass,パスワード"               => "need,eisuubar,min=4",
                                "new_user_pass_chk,パスワード(確認用)"   => "need,eisuubar,min=4,match=new_user_pass",
                              );
                $err_msg = _check( $chks, $_request );

                //**** POST値をセッションにマージ ****
                $this_sess = _array_merge( $this_sess, $_request );
            }else{
                //削除はチェックなし
                $this_sess['mode'] = "delete";
            }

            $sql  = "";
            $sql .= " select *";
            $sql .= " from v_admin ";
            $sql .= " where ";
            $sql .= "   admin_delete_date is null";
            $sql .= "   and admin_id = '"._as($admin_id)."'";
            $admin_rec = _select($sql);
            if ( $admin_rec[0]['admin_login_pass'] != md5( "_NEED_PASS_SET_" ) ){
                $err_msg[] = 'このデータは既に処理済みです。';
            }
            
            if(_count($err_msg) == 0){
                // ----------------------------------- //
                // m_admin 更新
                // ----------------------------------- //
                _query($conn,'begin');
                $array = array();
                $array['admin_login_pass']  = "'"._as( md5($this_sess['new_user_pass']) )."'";
                $array['admin_update_date'] = "'".$_now_timestamp."'"; //更新日時',

                $where = "admin_id='"._as($admin_id)."'";
                _update( 'm_admin', $array, $where );

                // ----------------------------------- //
                // m_user 更新
                // ----------------------------------- //
                $sql  = "";
                $sql .= " select *";
                $sql .= " from v_user ";
                $sql .= " where ";
                $sql .= "      user_delete_date is null";
                $sql .= "  and user_mail='"._as( $admin_rec[0]['admin_mail'] )."'";
                $user_recs = _select($sql);
                for ($loop=0; $loop < _count($user_recs); $loop++) { 
                    $array = array();
                    $array['user_pass']        = "'"._as( md5($this_sess['new_user_pass']) )."'";
                    $array['user_update_date'] = "'".$_now_timestamp."'"; //更新日時',
    
                    $where = " user_id = '"._as($user_recs[$loop]['user_id'])."'";
                    _update( 'm_user', $array, $where );
                }

                $success_msg = "変更が完了いたしました。<br><span style=\"font-size:16px;\">";
                _query($conn,'commit');

                $blade->assign('init_pass_change',1);

                unset( $this_sess );
                $this_sess = array();
            }
        }
    }

    // ******************************************************************************************************
    // 初期・完了画面
    // ******************************************************************************************************
    if($_request['exec'] != 'save' && _count($err_msg) == 0){
        $token = rand();
        unset( $_SESSION[_PROJECT_NAME][$page] );
        unset( $this_sess );
        $this_sess = &$_SESSION[_PROJECT_NAME][$page];

        $sql  = "";
        $sql .= " select ";
        $sql .= "   * ";
        $sql .= " from v_admin ";
        $sql .= " where ";
        $sql .= "   admin_delete_date is null";
        $sql .= "   and admin_id = '"._as($admin_id)."'";
        $main_rec = _select($sql);
        if (_count($main_rec) == 0){
            die('このURLは無効です。');
        }
        if ( $main_rec[0]['admin_login_pass'] != md5( "_NEED_PASS_SET_" ) ){
            $err_msg[] = 'このデータは既に処理済みです。';
            $blade->assign('init_pass_change',1);
        }

        $this_sess = $main_rec[0];

        // ## NEED_PASS_SETでなければパスワードを設定済みです

        $this_sess['token'] = $token;
    }
    $this_sess['setpw'] = $_request['setpw'];

    _setAssign($blade,$this_sess);
