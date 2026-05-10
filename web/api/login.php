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
    //require( "../lib/picture.php" )z;
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
    $return_arr['data'] = array();

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
                       "login_id,メールアドレス"          => "need",
                       "login_pass,パスワード"   => "need,eisuubar,min=4"
                      );
        $err_msg = _check( $chks , $_request );

        // ID(メアド)から実際のメールアドレス部分抽出
        $real_user_mail_addr = _getMailAddressFromID( $_request['login_id'] );
        // emailアドレスの形式チェック
        if ( _emailCheck($real_user_mail_addr, '') === false ){
            $err_msg[]  = "メールアドレスを正しく入力して下さい。";
        }

        if(count($err_msg)==0){
            //DB読んでチェック
            $sql  = "";
            $sql .= " select * from v_user ";
            $sql .= " left join v_admin on (v_admin.admin_id=v_user.user_admin_id)";
            $sql .= " left join m_syozoku on (v_admin.admin_syozoku_id=m_syozoku.syozoku_id)";
            $sql .= " where ";
            $sql .= "   user_delete_date is null ";
            $sql .= "   and user_syounin_flg = 1"; // 2021.06.05 add
            $sql .= "   and user_event_id = '" . _as( $event_rec['event_id'] )  . "'";
            // 2021/04/01 mod ------------------------ before ----------------------- 大幅変更
            // $sql .= "   and user_mail = '" . _as( $_request['login_id'] )  . "'";
            // 2021/04/01 mod ------------------------ after ------------------------ 大幅変更
            $sql .= "   and user_login_id = '" . _as( $_request['login_id'] )  . "'";
            // 2021/04/01 mod ------------------------ end   ------------------------ 大幅変更
            $managechk_recs = _select( $sql);
            if(count( $managechk_recs ) > 0){
                // if($managechk_recs[0]['user_pass'] == $_request['login_pass']){ 2020.12.18 mod
                if($managechk_recs[0]['user_pass'] == md5($_request['login_pass']) ){

                    if($managechk_recs[0]['user_web'] == "1"){

                        //2021/07/07 Add 【2021秋季西日本は2021/07/13になるまでAC社員しかログインできないようにする】 ------------------ Start ---------------
                        if($_request['evekey']=="w2021fc-f" && date("Y/m/d") < '2021/07/13' && $managechk_recs[0]['user_big_cate']!=7){
                            _ngReturn( "公開日は7月13日です。" );
                        }
                        //2021/07/07 Add 【2021秋季西日本は2021/07/13になるまでAC社員しかログインできないようにする】 ------------------ End ---------------

                        //2021/02/12 Add --------------- Strat ---------------
                        //ログインログ記録

                        _query($conn,'begin');

                        $array = array();
                        $array['ullog_kbn'] = "'exhibition'";
                        $array['ullog_user_id'] = "'"._as($managechk_recs[0]['user_id'])."'";
                        $array['ullog_user_event_id'] = "'"._as($managechk_recs[0]['user_event_id'])."'";
                        $array['ullog_user_kigyou_name'] = "'"._as($managechk_recs[0]['user_kigyou_name'])."'";
                        $array['ullog_user_busyo'] = "'"._as($managechk_recs[0]['user_busyo'])."'";
                        $array['ullog_user_yakusyoku'] = "'"._as($managechk_recs[0]['user_yakusyoku'])."'";
                        $array['ullog_user_name'] = "'"._as($managechk_recs[0]['user_name'])."'";
                        // 2021/04/01 mod ------------------------ before ----------------------- 大幅変更
                        // $array['ullog_user_mail'] = "'"._as($managechk_recs[0]['user_mail'])."'";
                        // 2021/04/01 mod ------------------------ after ------------------------ 大幅変更
                        $array['ullog_user_mail'] = "'"._as($managechk_recs[0]['user_login_id'])."'";
                        // 2021/04/01 mod ------------------------ end   ------------------------ 大幅変更
                        $array['ullog_admin_id'] = "'"._as($managechk_recs[0]['admin_id'])."'";
                        $array['ullog_admin_name'] = "'"._as($managechk_recs[0]['admin_name'])."'";
                        $array['ullog_admin_mail'] = "'"._as($managechk_recs[0]['admin_mail'])."'";
                        $array['ullog_insert_date'] = "'".$_now_timestamp."'";
                        _insert('t_uloginlog',$array);

                        _query($conn,'commit');
                        //2021/02/12 Add --------------- End ---------------

                        // $real_user_mail_addr = _getMailAddressFromID( $managechk_recs[0]['user_mail'] ); 2021/04/01
                        // $return_arr['data']['login_id'] = "".$managechk_recs[0]['user_mail'];
                        // $return_arr['data']['mail'] = "".$real_user_mail_addr;
                        $return_arr['data']['user_name'] = "".$managechk_recs[0]['user_name'];
                        $return_arr['data']['user_name_kana'] = "".$managechk_recs[0]['user_name_kana'];
                        $return_arr['data']['vip'] = "".$managechk_recs[0]['user_vip_flg'];
                        $return_arr['data']['big_cate'] = "".$_conf_big_cate[ $managechk_recs[0]['user_big_cate'] ];
                        $return_arr['data']['mid_cate'] = "".$_conf_mid_cate[ $managechk_recs[0]['user_mid_cate'] ];
                        $return_arr['data']['kigyou_name'] = "".$managechk_recs[0]['user_kigyou_name'];
                        $return_arr['data']['kigyou_name_kana'] = "".$managechk_recs[0]['user_kigyou_name_kana'];
                        $return_arr['data']['busyo'] = "".$managechk_recs[0]['user_busyo'];
                        $return_arr['data']['yakusyoku'] = "".$managechk_recs[0]['user_yakusyoku'];
                        $return_arr['data']['raijyou_yotei_ymdhi'] = "".$managechk_recs[0]['user_raijyou_yotei_time'];

                        // $return_arr['data']['tantou_mail'] = "".$managechk_recs[0]['admin_mail'];
                        // $return_arr['data']['tantou_name'] = "".$managechk_recs[0]['admin_name'];
                        // $return_arr['data']['tantou_syozoku'] = "".$managechk_recs[0]['syozoku_name'];
                    }else{
                        _ngReturn( "WEB展示会（ガイドブック）には招待されていないアカウントです。" );
                    }

                }else{
                    _ngReturn( "ログインIDまたはパスワードが不正です。" );
                }

            }else{
                _ngReturn( "ログインIDまたはパスワードが不正です。" );
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
