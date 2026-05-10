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
                //$sql .= "   and (user_raijyou_yotei_time is not null or user_raijyou_yotei_time != '')";
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

                if($user_recs[0]['user_big_cate']==7){
                    //AC社員

                    $sql = "";
                    $sql .= " select *"."\n";
                    $sql .= " from v_admin"."\n";
                    $sql .= " where admin_delete_date is null"."\n";
                    $sql .= " and admin_mail = '"._as($user_recs[0]['user_login_id'])."'"."\n";
                    $admin_rec = _select( $sql );
                    if ( $admin_rec[0]['admin_id'] != '' ){
                        $where = " admin_id = '"._as($admin_rec[0]['admin_id'])."'";

                        $array = array();
                        $array['admin_update_date'] = "'".$_now_timestamp."'";
                        $array['admin_login_pass']  = "'".md5($new_pass)."'";

                        _update( 'm_admin', $array, $where );
                    }

                    // まだ終了していないイベント
                    $sql  = "";
                    $sql .= " select event_id"."\n";
                    $sql .= " from m_event"."\n";
                    $sql .= " where event_delete_date is null"."\n";
                    $sql .= "  and event_url_key != '"._as( $event_rec['event_id'] )."'";
                    $sql .= "  and event_raikainri_ymd_ed > '".date("Y/m/d",strtotime(date("Y/m/d")."-1month"))."'"."\n";
                    $sql .= " order by event_id"."\n";
                    $active_event_recs = _select( $sql );
                    for ($loop=0; $loop < _count($active_event_recs); $loop++) {

                        $array = array();
                        // 来場者情報の存在チェック
                        $sql = "";
                        $sql .= " select user_id"."\n";
                        $sql .= " from v_user"."\n";
                        $sql .= " where user_delete_date is null"."\n";
                        $sql .= "  and user_event_id = '"._as($active_event_recs[ $loop ]['event_id'])."'"."\n";
                        $sql .= "  and user_login_id = '"._as($user_recs[0]['user_login_id'])."'"."\n";
                        $w_user_recs2 = _select( $sql );
                        if ( $w_user_recs2[0]['user_id'] != '' ){
                            $array = array();
                            $array['user_pass']        = "'".md5($new_pass)."'";
                            $array['user_update_date'] = "'".$_now_timestamp."'"; //更新日時',
            
                            $where = " user_id = '"._as($w_user_recs2[0]['user_id'])."'";
                            _update( 'm_user', $array, $where );
                        }
                    } // まだ終了していないイベント LOOP

                }

                // smarty set
                $msm = new UserBlade();

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

                $ret = _bladeFetchFromMailTplDB( $msm, $mail_tpl );
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
    $contents_tpl = "pass_reissue.html";
