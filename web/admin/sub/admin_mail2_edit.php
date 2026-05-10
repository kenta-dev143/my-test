<?php
    if( !defined("_PROJECT_DISP_NAME") ){
        die("System Error");
    }

    if( $_SESSION[_PROJECT_NAME]['admin_login']['admin_id'] == "" ){
        die('System Error');
    }

    if ($_SESSION[_PROJECT_NAME]['admin_login']['admin_kyouryoku_kigyou_flg'] == 1)
    {
        die('Permission Denied');
    }

    // ******************************************************************************************************
    // 初期値
    // ******************************************************************************************************
    $page = $_request['page'];
    $this_sess = &$_SESSION[_PROJECT_NAME][$page];
    $err_msg = array();

    $login_id = $_SESSION[_PROJECT_NAME]['admin_login']['admin_id'];


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
                            "admin_mail2,通知先メールアドレス２" => "email",
                        );

            $err_msg = _check( $chks, $_request );

            //**** POST値をセッションにマージ ****
            $this_sess = _array_merge( $this_sess, $_request );

            if(_count($err_msg) == 0){

                _query($conn,'begin');

                $array = array();
                $array['admin_update_date']        = "'".$_now_timestamp."'";
                $where = "admin_id='"._as( $login_id )."'";
                _update( 'm_admin', $array, $where );

                $array_m = array();
                $array_m['am_admin_mail2']              = "'"._as( $this_sess['admin_mail2'] )."'";
                $where = "am_admin_id='"._as( $login_id )."'";
                _update( 'm_amail', $array_m, $where );

                $success_msg = "通知用メールアドレスを変更しました。";
                if ( $this_sess['admin_mail2'] == '' ) $success_msg = "通知用メールアドレスを解除しました。";

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
        $sql .= " and admin_id ='"._as($login_id)."'";
        $main_rec = _select($sql);
        $this_sess = $main_rec[0];
        $this_sess['token'] = $token;
    }

    _setAssign($blade,$this_sess);

    $contents_title = "通知先メールアドレス設定";
    $active_menu = "admin_mail2_edit";
    $contents_tpl = "admin_mail2_edit";
