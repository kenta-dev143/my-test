<?php
    if( !defined("_PROJECT_DISP_NAME") ){
        die("System Error");
    }

    if( $_SESSION[_PROJECT_NAME]['user_login']['user_id']=="" ){
        die("System Error");
    }

    // ******************************************************************************************************
    // 初期値
    // ******************************************************************************************************
    $page = $_request['page'];
    $this_sess = &$_SESSION[_PROJECT_NAME][$page];
    $err_msg = array();


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
                if($_request['user_web']=="") $_request['user_web'] = "0";

                $chks = array(
                                "new_user_pass,パスワード"         => "need,eisuubar,min=4",
                                "new_user_pass_chk,パスワード(確認用)"         => "need,eisuubar,min=4,match=new_user_pass",
                              );


                $err_msg = _check( $chks, $_request );


                //**** POST値をセッションにマージ ****
                $this_sess = _array_merge( $this_sess, $_request );
            }else{

                //削除はチェックなし
                $this_sess['mode'] = "delete";
            }



            if(_count($err_msg) == 0){

                _query($conn,'begin');
                $array = array();

                // $array['user_pass'] = "'"._as($this_sess['user_pass'])."'"; 2020.12.18 mod
                $array['user_pass'] = "'"._as( md5($this_sess['new_user_pass']) )."'";
                $array['user_update_date'] = "'".$_now_timestamp."'"; //更新日時',

                $where = "user_id='"._as($_SESSION[_PROJECT_NAME]['user_login']['user_id'])."'";
                _update( 'm_user', $array, $where );

                _query($conn,'commit');

                $sql  = "";
                $sql .= " select ";
                $sql .= "   * ";
                $sql .= " from v_user ";
                $sql .= " where ";
                $sql .= "     user_delete_date is null";
                $sql .= "     and user_id='"._as($_SESSION[_PROJECT_NAME]['user_login']['user_id'])."'";
                $user_rec = _select($sql);
                $_SESSION[_PROJECT_NAME]['user_login'] = $user_rec[0];
                $_SESSION[_PROJECT_NAME]['user_login']['event_rec'] = $event_rec;

                if ( $user_rec[0]['user_big_cate'] == 7 ){
                    $sql = "";
                    $sql .= " select *"."\n";
                    $sql .= " from v_admin"."\n";
                    $sql .= " where admin_delete_date is null"."\n";
                    $sql .= " and admin_mail = '"._as($user_rec[0]['user_login_id'])."'"."\n";
                    $admin_rec = _select( $sql );
                    if ( $admin_rec[0]['admin_id'] != '' ){
                        $where = " admin_id = '"._as($admin_rec[0]['admin_id'])."'";

                        $array = array();
                        $array['admin_update_date'] = "'".$_now_timestamp."'";
                        $array['admin_login_pass']  = "'"._as( md5($this_sess['new_user_pass']) )."'";

                        _update( 'm_admin', $array, $where );
                    }

                    // まだ終了していないイベント
                    $sql  = "";
                    $sql .= " select event_id"."\n";
                    $sql .= " from m_event"."\n";
                    $sql .= " where event_delete_date is null"."\n";
                    $sql .= "  and event_url_key != '"._as( $_request['evekey'] )."'";
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
                        $sql .= "  and user_login_id = '"._as($user_rec[0]['user_login_id'])."'"."\n";
                        $user_rec2 = _select( $sql );
                        if ( $user_rec2[0]['user_id'] != '' ){
                            $array = array();
                            $array['user_pass']        = "'"._as( md5($this_sess['new_user_pass']) )."'";
                            $array['user_update_date'] = "'".$_now_timestamp."'"; //更新日時',
            
                            $where = " user_id = '"._as($user_rec2[0]['user_id'])."'";
                            _update( 'm_user', $array, $where );
                        }
                    } // まだ終了していないイベント LOOP
                }

                $success_msg = "変更が完了いたしました<br><span style=\"font-size:16px;\">引き続き、マイページをご利用ください。";

                $w_mode = $this_sess['mode'];
                $_SESSION[_PROJECT_NAME][$page] = array();
                unset( $_SESSION[_PROJECT_NAME][$page] );
                unset( $this_sess );
                $this_sess = array();


                $_request['exec'] = "";

                if($_SESSION[_PROJECT_NAME]['direct_login']=="1"){
                    $_SESSION[_PROJECT_NAME]['direct_login'] = "";

                    $user_raijyou_yotei_time = $_SESSION[_PROJECT_NAME]['user_login']['user_raijyou_yotei_time'];

                    # ログアウト処理
                    unset($_SESSION[_PROJECT_NAME]['user_login']);
                    // if ( $user_raijyou_yotei_time != '' ){
                    //     $blade->assign('goto_mypage',1);
                    // } else {
                    //     $blade->assign('goto_mypage',0);
                    // }
                    $blade->assign('user_raijyou_yotei_time',$user_raijyou_yotei_time);
                    $blade->assign('init_pass_change',1);

                    //2021/05/25 Mod ----- Before ------
                    // $ex_login_dir = "";
                    // if($event_rec['event_url_key']=='w2021fc-s'){
                    //     $ex_login_dir = "west2021s/";    
                    // }elseif($event_rec['event_url_key']=='e2021fc-s'){
                    //     $ex_login_dir = "east2021s/";    
                    // }
                    //2021/05/25 Mod ----- After ------
                    $ex_login_dir = $event_rec['event_exhibition_url_key']."/";
                    //2021/05/25 Mod ----- End ------
                    $ex_login_url = _SYSTEM_ROOT_URLS."/exhibition/".$ex_login_dir;
                    $blade->assign('ex_login_url',$ex_login_url);

                }

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
        $sql .= " from v_user ";
        $sql .= " where ";
        $sql .= "     user_delete_date is null";
        $sql .= "     and user_id='"._as($_SESSION[_PROJECT_NAME]['user_login']['user_id'])."'";
        $main_rec = _select($sql);
        //$main_rec[0]['user_pass_chk'] = $main_rec[0]['user_pass'];
        $this_sess = $main_rec[0];

        $this_sess['token'] = $token;
    }


    _setAssign($blade,$this_sess);

    $contents_tpl = "pass_edit.html";
