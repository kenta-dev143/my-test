<?php
    if( !defined("_PROJECT_DISP_NAME") ){
        die("System Error");
    }

    if( $_request['exec'] == "login" && $_request['page'] == 'login' ){

        unset( $_SESSION[_PROJECT_NAME]['admin_login'] );

        //入力書式チェック
        $chks = array(
                       "login_id,メールアドレス"          => "need",
                       "login_pass,パスワード"   => "need,eisuubar,min=4"
                      );
        $err_msg = _check( $chks , $_request );

        if( $_request['login_id']!="admin"){
            if( _emailCheck($_request['login_id'],'') == false ){
                $err_msg[] = "メールアドレスが正しくありません。";
            }
        }

        if(_count($err_msg)==0){
            //DB読んでチェック
            $sql  = "";
            $sql .= " select * from v_admin ";
            $sql .= " where ";
            $sql .= "   admin_delete_date is null ";
            $sql .= "   and admin_mail = '" . _as( $_request['login_id'] )  . "'";
            $managechk_recs = _select( $sql);
            if(_count( $managechk_recs ) > 0){
                // if($managechk_recs[0]['admin_login_pass'] == $_request['login_pass']){ 2020.12.18 mod
                if($managechk_recs[0]['admin_login_pass'] == md5($_request['login_pass']) ){

                    // 管理画面ログイン権限（0:不可、1:可）
                    if( $managechk_recs[0]['admin_login_kengen'] == 1 ){

                        $_SESSION[_PROJECT_NAME]['select_event_id'] = "";
                        $_login_after_page = _get_login_after_page();

                        //COOKIE処理
                        if($_request['save_chk'] != ""){
                            $w_kigen = time()+60*60*24*365;
                            setcookie("ck_"._PROJECT_NAME."_admin_login[login_id]", $_request['login_id'] , $w_kigen , "/");
                            setcookie("ck_"._PROJECT_NAME."_admin_login[login_pass]", $_request['login_pass'] , $w_kigen , "/");
                            setcookie("ck_"._PROJECT_NAME."_admin_login[save_chk]", 'checked' , $w_kigen , "/");
                        }else{
                            $w_kigen = time()+60*60*24*365;
                            setcookie("ck_"._PROJECT_NAME."_admin_login[login_id]", "" , $w_kigen , "/");
                            setcookie("ck_"._PROJECT_NAME."_admin_login[login_pass]", "" , $w_kigen , "/");
                            setcookie("ck_"._PROJECT_NAME."_admin_login[save_chk]", "" , $w_kigen , "/");
                        }

                        if ($managechk_recs[0]['admin_kyouryoku_kigyou_flg'] == 1) {
                            $_login_after_page = "user_list";
                        }
                        else if ( ! empty($_login_after_page)) {
                            // 特に何もしないが$_login_after_pageを上書きさせない
                        }
                        // 集計閲覧権限（0:全て閲覧可、1:エリアのリアルタイム人数のみ）
                        else if ( $managechk_recs[0]['admin_syuukei_etsuran_kengen'] == 1){
                            // 会場エリア集計
                            $_login_after_page = "area_syuukei";
                        } else {
                            // 会場全体集計
                            $_login_after_page = "kaijyou_syuukei";
                        }

                        if ( $_request['login_id'] != 'admin' ){
                            $sql  = "";
                            $sql .= " select * "."\n";
                            $sql .= " from m_agreement"."\n";
                            $sql .= " where agree_admin_id = '"._as( $managechk_recs[0]['admin_id'] )."'"."\n";
                            $agree_rec = _select($sql);
                            if ( _count($agree_rec) == 0 ){
                                // 個人情報の同意画面
                                $_login_after_page = "agreement";
                            }
                        }

                        $_SESSION[_PROJECT_NAME]['admin_login'] = $managechk_recs[0];
                        $_request['page'] = $_login_after_page;
                        require("sub/" . $_login_after_page . ".php");
                    } else {
                        $err_msg[] = "管理者画面へのログイン権限がありません。";
                        $_GLOBAL_fld_msg['login_pass'][]  = "管理者画面へのログイン権限がありません。";
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
                $blade->assign( 'login_id', $_request['login_id'] );
                $blade->assign( 'login_pass', $_request['login_pass'] );
                $blade->assign( 'save_chk', $_request['save_chk'] );
        }
    }elseif($_request['page'] == "login" && $_request['exec'] == "logout"){
        # ログアウト処理
        $_SESSION[_PROJECT_NAME]['select_event_id'] = "";
        unset($_SESSION[_PROJECT_NAME]['admin_login']);
        unset($_SESSION[_PROJECT_NAME]['select_event_id']);

        if($_COOKIE['ck_'._PROJECT_NAME.'_admin_login']['save_chk']!=""){
            $_request['login_id'] = $_COOKIE['ck_'._PROJECT_NAME.'_admin_login']['login_id'];
            $_request['login_pass'] = $_COOKIE['ck_'._PROJECT_NAME.'_admin_login']['login_pass'];
            $_request['save_chk'] = $_COOKIE['ck_'._PROJECT_NAME.'_admin_login']['save_chk'];
            $blade->assign( 'login_id', $_request['login_id'] );
            $blade->assign( 'login_pass', $_request['login_pass'] );
            $blade->assign( 'save_chk', $_request['save_chk'] );
        }
    }else{
        if($_COOKIE['ck_'._PROJECT_NAME.'_admin_login']['save_chk']!=""){
            $_request['login_id'] = $_COOKIE['ck_'._PROJECT_NAME.'_admin_login']['login_id'];
            $_request['login_pass'] = $_COOKIE['ck_'._PROJECT_NAME.'_admin_login']['login_pass'];
            $_request['save_chk'] = $_COOKIE['ck_'._PROJECT_NAME.'_admin_login']['save_chk'];
            $blade->assign( 'login_id', $_request['login_id'] );
            $blade->assign( 'login_pass', $_request['login_pass'] );
            $blade->assign( 'save_chk', $_request['save_chk'] );
        }
    }
