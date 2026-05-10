<?php
    if( !defined("_PROJECT_DISP_NAME") ){
        die("System Error");
    }

    $_SESSION[_PROJECT_NAME]['direct_login'] = "";

    if( $_request['page'] == "login" && $_request['setpw'] != '' ){
        $id_pass = _urlCodeDecode($_request['setpw']);
        list($id,$pass) = explode("#", $id_pass,2);
        if($id!="" && $pass!=""){
            if($pass=="_NEED_PASS_SET_"){
                $_request['exec'] = 'login';
                $_request['login_id'] = $id;
                $_request['login_pass'] = $pass;
                $_login_after_page = "pass_edit";
                $_SESSION[_PROJECT_NAME]['direct_login'] = "1";
            }
        }
    }
    if( $_request['exec'] == "login" && $_request['page'] == 'login' ){

        unset( $_SESSION[_PROJECT_NAME]['user_login'] );

        //入力書式チェック
        $chks = array(
                       "login_id,メールアドレス"          => "need,email",
                       "login_pass,パスワード"   => "need,eisuubar,min=4"
                      );
        $err_msg = _check( $chks , $_request );

        // ID(メアド)から実際のメールアドレス部分抽出
        $real_user_mail_addr = _getMailAddressFromID( $_request['login_id'] );
        // emailアドレスの形式チェック
        if ( _emailCheck($real_user_mail_addr, '') === false ){
            $err_msg[]  = "メールアドレスを正しく入力して下さい。";
        }

        if(_count($err_msg)==0){
            //DB読んでチェック
            $sql  = "";
            $sql .= " select * from v_user ";
            $sql .= " where ";
            $sql .= "   user_delete_date is null ";
            if($_request['setpw'] == ''){
                //$sql .= "   and (user_raijyou_yotei_time is not null and user_raijyou_yotei_time != '')";
            }
            $sql .= "   and user_event_id = '" . _as( $event_rec['event_id'] )  . "'";
            // 2021/04/01 mod ------------------------ before ----------------------- 大幅変更
            // $sql .= "   and user_mail = '" . _as( $_request['login_id'] )  . "'";
            // 2021/04/01 mod ------------------------ after ------------------------ 大幅変更
            $sql .= "   and user_login_id = '" . _as( $_request['login_id'] )  . "'";
            // 2021/04/01 mod ------------------------ end   ------------------------ 大幅変更
            $managechk_recs = _select($sql);
            if(_count( $managechk_recs ) > 0){
                // if($managechk_recs[0]['user_pass'] == $_request['login_pass']){ 2020.12.18 mod
                if($managechk_recs[0]['user_pass'] == md5( $_request['login_pass'] )){
                    //2021/02/12 Add --------------- Strat ---------------
                    //ログインログ記録

                    _query($conn,'begin');

                    $w_admin_recs = _select("select * from v_admin where admin_id='"._as($managechk_recs[0]['user_admin_id'])."'");

                    $array = array();
                    $array['ullog_kbn']              = "'mypage'";
                    $array['ullog_user_id']          = "'"._as($managechk_recs[0]['user_id'])."'";
                    $array['ullog_user_event_id']    = "'"._as($managechk_recs[0]['user_event_id'])."'";
                    $array['ullog_user_kigyou_name'] = "'"._as($managechk_recs[0]['user_kigyou_name'])."'";
                    $array['ullog_user_busyo']       = "'"._as($managechk_recs[0]['user_busyo'])."'";
                    $array['ullog_user_yakusyoku']   = "'"._as($managechk_recs[0]['user_yakusyoku'])."'";
                    $array['ullog_user_name']        = "'"._as($managechk_recs[0]['user_name'])."'";
                    // 2021/04/01 mod ------------------------ before ----------------------- 大幅変更
                    // $array['ullog_user_mail'] = "'"._as($managechk_recs[0]['user_mail'])."'";
                    // 2021/04/01 mod ------------------------ after ------------------------ 大幅変更
                    $array['ullog_user_mail']        = "'"._as($managechk_recs[0]['user_mail'])."'";
                    $array['ullog_user_login_id']    = "'"._as($managechk_recs[0]['user_login_id'])."'";
                    // 2021/04/01 mod ------------------------ end   ------------------------ 大幅変更
                    $array['ullog_admin_id']         = "'"._as($w_admin_recs[0]['admin_id'])."'";
                    $array['ullog_admin_name']       = "'"._as($w_admin_recs[0]['admin_name'])."'";
                    $array['ullog_admin_mail']       = "'"._as($w_admin_recs[0]['admin_mail'])."'";
                    $array['ullog_insert_date']      = "'".$_now_timestamp."'";
                    _insert('t_uloginlog',$array);

                    _query($conn,'commit');
                    //2021/02/12 Add --------------- End ---------------


                    //COOKIE処理
                    if($_request['save_chk'] != ""){
                        $w_kigen = time()+60*60*24*365;
                        setcookie("ck_"._PROJECT_NAME."_user_login[login_id]", $_request['login_id'] , $w_kigen , "/");
                        setcookie("ck_"._PROJECT_NAME."_user_login[login_pass]", $_request['login_pass'] , $w_kigen , "/");
                        setcookie("ck_"._PROJECT_NAME."_user_login[save_chk]", 'checked' , $w_kigen , "/");
                    }else{
                        $w_kigen = time()+60*60*24*365;
                        setcookie("ck_"._PROJECT_NAME."_user_login[login_id]", "" , $w_kigen , "/");
                        setcookie("ck_"._PROJECT_NAME."_user_login[login_pass]", "" , $w_kigen , "/");
                        setcookie("ck_"._PROJECT_NAME."_user_login[save_chk]", "" , $w_kigen , "/");
                    }

                    
                    $_SESSION[_PROJECT_NAME]['user_login'] = $managechk_recs[0];
                    $_request['page'] = $_login_after_page;
                    //require("sub/" . $_login_after_page . ".php");
                    if ( $_request['ex'] == 1 && $managechk_recs[0]['user_big_cate'] == 7){
                        header( "Location: "._SYSTEM_ROOT_URLS."/mypage/".$_request['evekey']."/" );
                        exit();
                    } else {
                        require( _SYSTEM_ROOT_DIR."/mypage/sub/" . $_login_after_page . '.php' );
                    }


                }else{
                    $err_msg[] = "ログインIDまたはパスワードが不正です。";
                    $_GLOBAL_fld_msg['login_pass'][]  = "ログインIDまたはパスワードが不正です。";
                }

            }else{
                $err_msg[] = "ログインIDまたはパスワードが不正です。";
                $_GLOBAL_fld_msg['login_pass'][]  = "ログインIDまたはパスワードが不正です。";
            }
        }
        if(_count($err_msg) > 0){

                if($_SESSION[_PROJECT_NAME]['direct_login']=="1" && _count($err_msg)>0){
                    $_SESSION[_PROJECT_NAME]['direct_login'] = "";
                    $_request['login_id'] = "";
                    $_request['login_pass'] = "";
                    $err_msg = array();
                    $err_msg[] = "指定されたパスワード設定URLは無効です。";
                }
                $sm->assign( 'login_id', $_request['login_id'] );
                $sm->assign( 'login_pass', $_request['login_pass'] );
                $sm->assign( 'save_chk', $_request['save_chk'] );
        }
    }elseif($_request['page'] == "login" && $_request['exec'] == "logout"){
        # ログアウト処理
        unset($_SESSION[_PROJECT_NAME]['user_login']);

        if($_COOKIE['ck_'._PROJECT_NAME.'_user_login']['save_chk']!=""){
            $_request['login_id'] = $_COOKIE['ck_'._PROJECT_NAME.'_user_login']['login_id'];
            $_request['login_pass'] = $_COOKIE['ck_'._PROJECT_NAME.'_user_login']['login_pass'];
            $_request['save_chk'] = $_COOKIE['ck_'._PROJECT_NAME.'_user_login']['save_chk'];
            $sm->assign( 'login_id', $_request['login_id'] );
            $sm->assign( 'login_pass', $_request['login_pass'] );
            $sm->assign( 'save_chk', $_request['save_chk'] );
        }
    }else{
        if($_COOKIE['ck_'._PROJECT_NAME.'_user_login']['save_chk']!=""){
            $_request['login_id'] = $_COOKIE['ck_'._PROJECT_NAME.'_user_login']['login_id'];
            $_request['login_pass'] = $_COOKIE['ck_'._PROJECT_NAME.'_user_login']['login_pass'];
            $_request['save_chk'] = $_COOKIE['ck_'._PROJECT_NAME.'_user_login']['save_chk'];
            $sm->assign( 'login_id', $_request['login_id'] );
            $sm->assign( 'login_pass', $_request['login_pass'] );
            $sm->assign( 'save_chk', $_request['save_chk'] );
        }
    }
    
