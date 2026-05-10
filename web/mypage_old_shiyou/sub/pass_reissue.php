<?php
    if( !defined("_PROJECT_DISP_NAME") ){
        die("System Error");
    }

    // ******************************************************************************************************
    // 初期値
    // ******************************************************************************************************
    $page = $_request['page'];
    $err_msg = array();

    // ******************************************************************************************************
    // メール送信処理
    // ******************************************************************************************************
    if( $_request['exec'] == "send" ){
        //入力書式チェック
        $chks = array(
            "login_id,ID（メールアドレス）"          => "need",
           );

        $err_msg = _check( $chks , $_request );

        // ID(メアド)から実際のメールアドレス部分抽出
        $real_user_mail_addr = _getMailAddressFromID( $_request['login_id'] );
        // emailアドレスの形式チェック
        if ( _emailCheck($real_user_mail_addr, '') === false ){
            $err_msg[]  = "ID（メールアドレス）を正しく入力して下さい。";
        }

        if(_count($err_msg)==0){

            $sql  = "";
            $sql .= " select * "."\n";
            $sql .= " from v_user "."\n";
            $sql .= " where ";
            $sql .= "   user_delete_date is null ";
            $sql .= "   and (user_raijyou_yotei_time is not null or user_raijyou_yotei_time != '')";
            $sql .= "   and user_event_id = '" . _as( $event_rec['event_id'] )  . "'";
            // 2021/04/01 mod ------------------------ before ----------------------- 大幅変更
            // $sql .= "   and user_mail = '" . _as( $_request['login_id'] )  . "'";
            // 2021/04/01 mod ------------------------ after ------------------------ 大幅変更
            $sql .= "   and user_login_id = '" . _as( $_request['login_id'] )  . "'";
            // 2021/04/01 mod ------------------------ end   ------------------------ 大幅変更
            $user_recs = _select($sql);
            if(_count( $user_recs ) == 0){
                $err_msg[] = "ID（メールアドレス）に該当がありません。";
            }
        }

        if(_count($err_msg) == 0){

            _query($conn,'begin');

            // $new_pass = _makePassword();
            $new_pass = "_NEED_PASS_SET_";

            // DB更新
            $upd_r = array();
            $upd_r['user_pass'] = "'".md5($new_pass)."'";
            // $where = "";
            // $where .= "   user_delete_date is null ";
            // $where .= "   and (user_raijyou_yotei_TIME is not null or user_raijyou_yotei_TIME != '')";
            // $where .= "   and user_event_id = '" . _as( $event_rec['event_id'] )  . "'";
            // $where .= "   and user_mail = '" . _as( $_request['login_id'] )  . "'";
            $where = "user_id = '" . _as( $user_recs[0]['user_id'] )  . "'";

            _update('m_user', $upd_r, $where);

            // smarty set
            $msm = new UserSmarty();

            //2021/06/03 Mod ----------- After ------------
            // $msm->assign('_SYSTEM_ROOT_URLS',_SYSTEM_ROOT_URLS);

            // // 2021/04/01 mod ------------------------ before ----------------------- 大幅変更
            // // $pass_change_url = _SYSTEM_ROOT_URLS."/mypage/".$event_rec['event_url_key']."/?page=login&setpw="._urlCodeEncode($user_recs[0]['user_mail']."#".$new_pass);
            // // 2021/04/01 mod ------------------------ after ------------------------ 大幅変更
            // $pass_change_url = _SYSTEM_ROOT_URLS."/mypage/".$event_rec['event_url_key']."/?page=login&setpw="._urlCodeEncode($user_recs[0]['user_login_id']."#".$new_pass);
            // // 2021/04/01 mod ------------------------ end   ------------------------ 大幅変更
            // $msm->assign('pass_change_url',  $pass_change_url);

            // $msm->assign('user_rec',      $user_recs[0]);
            // $msm->assign('event_rec',$event_rec);
            // $msm->assign('ex',$_request['ex']);

            // // template set
            // $mail_tpl = "pass_reissue.tpl";

            // $title_and_body = _smartyFetch( $msm, _SYSTEM_ROOT_DIR . '/mail/' . $mail_tpl );
            // $w_arr = explode ("\n", $title_and_body, 2 );
            // $title = array_shift( $w_arr );
            // if( substr( $title, strlen( $title ) - 1, 1 ) == "\r" ){
            //     $title = substr( $title, 0, strlen( $title ) - 1 );
            // }
            // $body = join( "\n", $w_arr );
            //2021/06/03 Mod ----------- After ------------

            $pass_set_url = _SYSTEM_ROOT_URLS."/mypage/".$event_rec['event_url_key']."/?page=login&setpw="._urlCodeEncode($user_recs[0]['user_login_id']."#".$new_pass);

            $data_rec = array();
            $data_rec['event_name']        = $event_rec['event_name'];
            $data_rec['kigyou_name']       = $user_recs[0]['user_kigyou_name'];
            $data_rec['name']              = $user_recs[0]['user_name'];
            $data_rec['pass_set_url']       = $pass_set_url;
            _setAssign($msm,$data_rec);

            // template set
            $mail_tpl = "pass_reissue.tpl";

            $ret = _smartyFetchFromMailTplDB( $msm, $mail_tpl );
            $title = $ret['subject'];
            $body = $ret['body'];
            //2021/06/03 Mod ----------- End ------------
            
            $attach = array();
            // _sendMailEx( _SYSTEM_INFO_MAIL, _SYSTEM_INFO_MAIL_NAME, $real_user_mail_addr, $user_recs[0]['user_name']." 様", $title, $body,$attach );
            // _accessSendMail( _SYSTEM_INFO_MAIL, _SYSTEM_INFO_MAIL_NAME, $real_user_mail_addr, $user_recs[0]['user_name']." 様", $title, $body,$attach ); 2021/04/01 Del
            _accessSendMail( _SYSTEM_INFO_MAIL, _SYSTEM_INFO_MAIL_NAME, $user_recs[0]['user_mail'], $user_recs[0]['user_name']." 様", $title, $body,$attach );

            _query($conn,'commit');

            // 完了メッセージ
            $success_msg = "メールを送信しました。";
        }

        if(_count($err_msg) > 0){
            $sm->assign( 'login_id', $_request['login_id'] );
        }
        
    }

    $sm->assign( 'success_msg', $success_msg );
    $contents_tpl = "pass_reissue.html";
