<?php
    if( !defined("_PROJECT_DISP_NAME") ){
        die("System Error");
    }
    // ******************************************************************************************************
    // 初期値
    // ******************************************************************************************************
    $page = $_request['page'];
    $this_sess = &$_SESSION[_PROJECT_NAME][$page];
    $err_msg = array();

    // ******************************************************************************************************
    // メール送信処理
    // ******************************************************************************************************
    if( $_request['exec'] == "send" ){
        if($this_sess['token'] != $_request['token']){
            $err_msg[] = 'このデータは処理できませんでした。';
        }elseif( _count( $this_sess ) <= 0 ){
            //**** リロードやボタンダブルクリックでの２重登録抑制
            $err_msg[] = 'このデータは既に処理済みです。';
        }else{
            //入力書式チェック
            $chks = array(
                "login_id,メールアドレス"          => "need,email",
               );

            $err_msg = _check( $chks , $_request );


            if(_count($err_msg)==0){

                $sql  = "";
                $sql .= " select * "."\n";
                $sql .= " from v_admin "."\n";
                $sql .= " left join m_syozoku on (v_admin.admin_syozoku_id = m_syozoku.syozoku_id and syozoku_delete_date is null) "."\n"; // 2021.06.02 add
                $sql .= " where ";
                $sql .= "   admin_delete_date is null ";
                $sql .= "   and admin_mail = '" . _as( $_request['login_id'] )  . "'";
                $admin_recs = _select($sql);
                if(_count( $admin_recs ) == 0){
                    $err_msg[] = "ID（メールアドレス）に該当がありません。";
                }
            }

            if(_count($err_msg) == 0){

                _query($conn,'begin');

                // $new_pass = _makePassword(); 2021.06.02 mod
                $new_pass = "_NEED_PASS_SET_";

                // DB更新
                $upd_r = array();
                $upd_r['admin_login_pass'] = "'".md5($new_pass)."'";
                $where = " admin_id = '"._as($admin_recs[0]['admin_id'])."'";

                _update('m_admin', $upd_r, $where);

                // 2020.06.02 mod --------- Before ---------
                // smarty set
                // $msm = new UserBlade();
                // $msm->assign('_SYSTEM_ROOT_URLS',_SYSTEM_ROOT_URLS);
                // $msm->assign('new_pass',      $new_pass);
                // $msm->assign('admin_rec',      $admin_recs[0]);
                // // template set
                // $mail_tpl = "admin_pass_change.tpl";
                // $title_and_body = _smartyFetch( $msm, _SYSTEM_ROOT_DIR . '/mail/' . $mail_tpl );
                // $w_arr = explode ("\n", $title_and_body, 2 );
                // $title = array_shift( $w_arr );
                // if( substr( $title, strlen( $title ) - 1, 1 ) == "\r" ){
                //     $title = substr( $title, 0, strlen( $title ) - 1 );
                // }
                // $body = join( "\n", $w_arr );
                // $attach = array();
                // // _sendMailEx( _SYSTEM_INFO_MAIL, _SYSTEM_INFO_MAIL_NAME, $_request['login_id'], $admin_recs[0]['admin_name']." 様", $title, $body,$attach );
                // _accessSendMail( _SYSTEM_INFO_MAIL, _SYSTEM_INFO_MAIL_NAME, $_request['login_id'], $admin_recs[0]['admin_name']." 様", $title, $body,$attach );
                // 2020.06.02 mod --------- After ---------
                // smarty set
                $msm = new UserBlade();
                $msm->assign('_SYSTEM_ROOT_URLS',_SYSTEM_ROOT_URLS);

                $pass_change_url = _SYSTEM_ROOT_URLS."/admin/"."?page=admin_pass_set&setpw="._urlCodeEncode($admin_recs[0]['admin_id']."#_NEED_PASS_SET_");

                $data_rec = array();
                $data_rec['tantou_syozoku']    = $admin_recs[0]['syozoku_name'];
                $data_rec['tantou_name']       = $admin_recs[0]['admin_name'];
                $data_rec['tantou_mail']       = $admin_recs[0]['admin_mail'];
                $data_rec['pass_set_url']      = $pass_change_url;
                $data_rec['admin_url']         = _SYSTEM_ROOT_URLS."/admin/";
                _setAssign($msm,$data_rec);

                // template set
                $mail_tpl = "admin_pass_reissue.tpl";

                $ret = _bladeFetchFromMailTplDB( $msm, $mail_tpl );
                $title = $ret['subject'];
                $body = $ret['body'];

                $attach = array();
                _accessSendMail( _SYSTEM_INFO_MAIL, _SYSTEM_INFO_MAIL_NAME, $admin_recs[0]['admin_mail'], $admin_recs[0]['admin_name']." 様", $title, $body,$attach );
                // 2020.06.02 mod --------- End   ---------

                _query($conn,'commit');

                // 完了メッセージ
                $success_msg = "メールを送信しました。";
            }

        }        

        if(_count($err_msg) > 0){
            $blade->assign( 'login_id', $_request['login_id'] );
        }
    }

    if($_request['exec'] != 'save' && _count($err_msg) == 0){
        $token = rand();
        unset( $_SESSION[_PROJECT_NAME][$page] );
        unset( $this_sess );
        $this_sess = &$_SESSION[_PROJECT_NAME][$page];

        $this_sess['token'] = $token;
    }

    _setAssign($blade,$this_sess);

    $blade->assign( 'success_msg', $success_msg );
    $contents_tpl = "pass_reissue";
