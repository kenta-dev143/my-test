<?php
    if( !defined("_PROJECT_DISP_NAME") ){
        die("System Error");
    }

    if( $_SESSION[_PROJECT_NAME]['admin_login']['admin_id'] == "" ){
        die('System Error');
    }

    // if( $_SESSION[_PROJECT_NAME]['admin_login']['admin_master_kengen'] != "1" ){
    //     die('System Error');
    // }

    // ******************************************************************************************************
    // 初期値
    // ******************************************************************************************************
    $page = $_request['page'];

    $this_sess = &$_SESSION[_PROJECT_NAME][$page];
    $err_msg = array();

    $admin = $_SESSION[_PROJECT_NAME]['admin_login'];
    $user_web_force_kengen = $admin['admin_login_kengen'] == '1' && $admin['admin_user_kengen'] == '0' && $admin['admin_syuukei_etsuran_kengen'] == '0';
    $blade->assign('user_web_force_kengen', $user_web_force_kengen);

    $table_area_inout = 't_area_inout';
    $table_v_user = 'v_user';

    if ($select_event_rec['event_archived_flg'] == '1')
    {
        $table_area_inout = 'a_area_inout';
        $table_v_user = 'v_auser';
    }


    // ******************************************************************************************************
    // 登録・更新・削除
    // ******************************************************************************************************
    if($_request['exec'] == 'save'){

        if ($select_event_rec['event_archived_flg'] == '1')
        {
            $err_msg[] = 'このデータはアーカイブ済みです。';
        }elseif($this_sess['token'] != $_request['token']){
            $err_msg[] = 'このデータは処理できませんでした。';
        }elseif( _count( $this_sess ) <= 0 ){
            //**** リロードやボタンダブルクリックでの２重登録抑制
            $err_msg[] = 'このデータは既に処理済みです。';
        }else{

            // -------------------------------------------------------
            // 招待者の処理を分岐する add 2021.05.25
            // -------------------------------------------------------
            if($_request['user_big_cate']!=""){
                if( intval($_request['user_big_cate']) <= 4){
                    //招待者
                    $this_sess['raijyousya_kbn'] = 1;
                }else{
                    //来場者
                    $this_sess['raijyousya_kbn'] = 2;
                }
            }else{
                $this_sess['raijyousya_kbn'] = "";
            }

            if($_request['mode'] !='delete'){
                if($_request['user_web']=="") $_request['user_web'] = "0";
                if($_request['mypage_notice']=="") $_request['mypage_notice'] = "0";
                if($_request['new_user_pass_make']=="") $_request['new_user_pass_make'] = "0";

                // *** checkbox の処理追加

                // 招待者はSKIP add 2021.05.25
                if ( $this_sess['raijyousya_kbn'] != 1 ){
                    $chks = array(
                                    "user_vip_flg,VIP"          => "seisuu",
                                    "user_big_cate,大分類"          => "seisuu",
                                    "user_mid_cate,中分類"          => "seisuu",
                                    "user_web,WEB展示会（ガイドブック）招待者"          => "seisuu",
                                    "user_web_force,強制WEB展示会参加"  => "seisuu",
                                    //"user_kigyou_name,企業名"          => "need",
                                    //"user_kigyou_name_kana,企業名カナ" => "zenkana",
                                    //"user_busyo,部署"                => "need",
                                    //"user_yakusyoku,役職"            => "need",
                                    "user_name,氏名"                   => "need",
                                    "user_name_kana,氏名カナ"          => "zenkana",
                                    "user_mail,メールアドレス"         => "need,email",
                                    "user_login_id,ログインID"         => "need,email",
                                    "user_admin_id,担当者"             => "need",
                                    "reception_mail_flg,会場受付時メール送信フラグ"  => "seisuu",
                                    "user_admin_mail_1,追加担当者メールアドレス１"         => "email",
                                    "user_admin_mail_2,追加担当者メールアドレス２"         => "email",
                                    "user_admin_mail_3,追加担当者メールアドレス３"         => "email",
                                    "user_agent_mail,代理登録者メールアドレス" => "email",
                                );


                    $err_msg = _check( $chks, $_request );

                    if($_SESSION[_PROJECT_NAME]['select_event_id']==""){
                        $err_msg[] = "イベントが選択されていません";
                    }

                    // メールアドレスの重複チェック 2020.12.19 add
                    $sql = "";
                    $sql .= " select user_id"."\n";
                    $sql .= " from v_user"."\n";
                    $sql .= " where "."\n";
                    $sql .= "   user_delete_date is null ";
                    $sql .= "   and user_event_id = '"._as( $_SESSION[_PROJECT_NAME]['select_event_id'] )."'";
                    // 2021/04/01 mod ------------------------ before ----------------------- 大幅変更
                    // $sql .= "   and user_mail = '". _as( $_request['user_mail'] ) ."'";
                    // 2021/04/01 mod ------------------------ after ------------------------ 大幅変更
                    $sql .= "   and user_login_id = '". _as( $_request['user_login_id'] ) ."'";
                    // 2021/04/01 mod ------------------------ end   ------------------------ 大幅変更
                    if ( $_request['mode'] == 'update' ){
                        $sql .= "   and user_id != '" . _as( $this_sess['id'] ) ."'";
                    }
                    $chk_recs = _select($sql);
                    if ( _count($chk_recs) > 0 ){
                        $err_msg[] = "このログインIDは既に登録済みです。";
                    }

                    // ID(メアド)から実際のメールアドレス部分抽出
                    $real_user_mail_addr = _getMailAddressFromID( $_request['user_login_id'] );
                    // emailアドレスの形式チェック
                    if ( _emailCheck($real_user_mail_addr, '') === false ){
                        $err_msg[]  = "ログインIDを正しく入力して下さい。";

                    }
                } else {
                    // ---------------------------------------- //
                    // raijyousya_kbn 1 : 招待者 のチェック処理
                    // ---------------------------------------- //
                    $chks = array(
                        "user_admin_id,担当者"             => "need",
                    );
                    $err_msg = _check( $chks, $_request );

                    if($_SESSION[_PROJECT_NAME]['select_event_id']==""){
                        $err_msg[] = "イベントが選択されていません";
                    }

                } // 招待者はSKIP 終端 2021.05.25

                if($this_sess['raijyousya_kbn']==1){
                    // 2021.05.31 mod -------- Before --------
                     if( _count($_request['syoutai_yotei_time'])==0 ){
                         //$err_msg[] = "招待者の来場予定日時が指定されていません。";
                         $_request['user_raijyou_yotei_time'] = "";
                     }else{
                         $yotei = "";
                         for ($i=0; $i < _count($_request['syoutai_yotei_time']); $i++) {
                             if($yotei!="") $yotei .= "#";
                             $yotei .= $_request['syoutai_yotei_time'][$i];
                         }
                         $_request['user_raijyou_yotei_time'] = $yotei;
                     }
                    // 2021.05.31 mod -------- Before --------
//                    $_request['user_raijyou_yotei_time'] = "";
//                    if ( $_request['syoutai_yotei_time'] != '' ){
//                        $_request['user_raijyou_yotei_time']  = $_request['syoutai_yotei_time'];
//                        $this_sess['user_raijyou_yotei_time'] = $_request['syoutai_yotei_time'];
//                    }
                }elseif($this_sess['raijyousya_kbn']==2){
                    if( _count($_request['raijyou_yotei_time'])==0 ){
                        //$err_msg[] = "来場者の来場予定日時が指定されていません。";
                        $_request['user_raijyou_yotei_time'] = "";
                    }else{
                        $yotei = "";
                        for ($i=0; $i < _count($_request['raijyou_yotei_time']); $i++) {
                            if($yotei!="") $yotei .= "#";
                            $yotei .= $_request['raijyou_yotei_time'][$i];
                        }
                        $_request['user_raijyou_yotei_time'] = $yotei;
                    }
                }

                //2021/07/07 Add ----------- Start ------------
                if($_request['user_web']=="1"){
                    if ($_request['user_web_force'] != "1" || ! $user_web_force_kengen) {
                        if( $_request['user_big_cate']!=1 && $_request['user_big_cate']!=2 && $_request['user_big_cate']!=7 && $_request['user_big_cate']!=8){
                          //WEB招待しているが、大分類が「1:小売、2:外食、7:AC社員、8:その他(来場) でなければエラー
                          $err_msg[] = "WEB展示会（ガイドブック）に招待できるのは「(招待者)小売、(招待者)外食、(来場者)AC社員、(来場者)その他」のみです。";
                        }
                    }

                }
                //2021/07/07 Add ----------- End ------------

                //**** POST値をセッションにマージ ****
                $this_sess = _array_merge( $this_sess, $_request );
            }else{

                //削除はチェックなし
                $this_sess['mode'] = "delete";
            }



            if(_count($err_msg) == 0){

                _query($conn,'begin');

                //**************************************************
                //新規の場合新ID発番
                //**************************************************
                if( $this_sess['mode'] == "insert" ){
                    $max_recs = _select( "select coalesce(max(substring(user_id,2)),'0') as max_id from m_user");
                    $this_sess['id'] = sprintf("u%08d", $max_recs[0]['max_id'] + 1 );
                }

                $array = array();
                $array_n = array();
                $array_m = array();

                // 招待者はSKIP add 2021.05.25
                if ( $this_sess['raijyousya_kbn'] != 1 ){
                    $array['user_event_id']            = "'"._as($_SESSION[_PROJECT_NAME]['select_event_id'])."'"; //イベントID（e0001）',
                    $array['user_vip_flg']             = ""._e2z($this_sess['user_vip_flg']).""; //VIPフラグ（1:VIP）',
                    $array['user_big_cate']            = ""._e2n($this_sess['user_big_cate'])."";//大分類',
                    $array['user_mid_cate']            = ""._e2n($this_sess['user_mid_cate']).""; //中分類',
                    $array['user_kigyou_name']         = "'"._as($this_sess['user_kigyou_name'])."'"; //企業名',
                    $array['user_kigyou_name_kana']    = "'"._as($this_sess['user_kigyou_name_kana'])."'"; //企業名カナ',
                    $array['user_busyo']               = "'"._as($this_sess['user_busyo'])."'"; //部署',
                    $array['user_yakusyoku']           = "'"._as($this_sess['user_yakusyoku'])."'"; //役職',
                }

                if ( $this_sess['new_user_pass_make'] == 1 ){
                    $new_pass = "_NEED_PASS_SET_";
                    $array['user_pass']            = "'"._as( md5( $new_pass ) )."'"; //パスワード', 2020.12.18 mod
                }

                $array['user_admin_id']            = "'"._as($this_sess['user_admin_id'])."'"; //担当者ID（a0000001）',
                $array['user_raijyou_yotei_time']  = "'"._as($this_sess['user_raijyou_yotei_time'])."'"; //来場予定日時（yyyy/mm/dd HH:ii 形式）',
                $array['user_web']                 = ""._e2z($this_sess['user_web']).""; //WEB招待（1:WEB招待者）',
                $array['user_web_force']           = ""._e2z($this_sess['user_web_force']).""; //WEB招待強制参加フラグ,
                $array['user_tag']                 = "'"._as($this_sess['user_tag'])."'"; //ユーザタグ, 2020.12.19 add
                $array['reception_mail_flg']       = ""._e2z($_request['reception_mail_flg']).""; //受付完了メール送信フラグ,
                $array['user_admin_mail_1']        = "'" . _as($this_sess['user_admin_mail_1']) . "'";
                $array['user_admin_mail_2']        = "'" . _as($this_sess['user_admin_mail_2']) . "'";
                $array['user_admin_mail_3']        = "'" . _as($this_sess['user_admin_mail_3']) . "'";
                $array['user_agent_mail']          = "'" . _as($this_sess['user_agent_mail']) . "'";

                // 招待者はSKIP add 2021.05.25
                if ( $this_sess['raijyousya_kbn'] != 1 ){
                    $array['user_biko']                = "'"._as($this_sess['user_biko'])."'"; //備考',
                }

                $array['user_syounin_flg']         = "'"._as($this_sess['user_syounin_flg'])."'"; //WEB招待の承認フラグ(0:未承認、1:承認済み), 2021.06.05 add
                $array['user_update_date']         = "'".$_now_timestamp."'"; //更新日時',

                // if ( $this_sess['new_user_pass_make'] != "" && $this_sess['mode']!="delete"){
                if ( $this_sess['mypage_notice'] == 1 && $this_sess['mode']!="delete"){
                    $array['user_mail_send_kbn'] = "1";
                }

                // 招待者はSKIP add 2021.05.25
                if ( $this_sess['raijyousya_kbn'] != 1 ){
                    $array_n['un_user_name']                = "'"._as($this_sess['user_name'])."'"; //'氏名',
                    $array_n['un_user_name_kana']           = "'"._as($this_sess['user_name_kana'])."'"; //氏名カナ',
                }

                // 招待者はSKIP add 2021.05.25
                if ( $this_sess['raijyousya_kbn'] != 1 ){
                    $array_m['um_user_mail']                = "'"._as($this_sess['user_mail'])."'"; //メールアドレス',
                    $array_m['um_user_login_id']            = "'"._as($this_sess['user_login_id'])."'"; // ログインid',
                }

                switch( $this_sess['mode'] ){
                     case 'insert':

                        $array['user_id']          = "'"._as($this_sess['id'])."'";
                        $array['user_insert_date']  = "'".$_now_timestamp."'";
                        _insert( 'm_user', $array);

                        $array_n['un_user_id']          = "'"._as($this_sess['id'])."'";
                        _insert( 'm_uname', $array_n);

                        $array_m['um_user_id']          = "'"._as($this_sess['id'])."'";
                        _insert( 'm_umail', $array_m);

                        $success_msg = "登録しました。";
                     break;
                     case 'update':
                        $where = "user_id='"._as($this_sess['id'])."'";
                        _update( 'm_user', $array, $where );

                        // 招待者はSKIP add 2021.05.25
                        if ( $this_sess['raijyousya_kbn'] != 1 ){
                            $where = "un_user_id='"._as($this_sess['id'])."'";
                            _update( 'm_uname', $array_n, $where );
                        }

                        // 招待者はSKIP add 2021.05.25
                        if ( $this_sess['raijyousya_kbn'] != 1 ){
                            $where = "um_user_id='"._as($this_sess['id'])."'";
                            _update( 'm_umail', $array_m, $where );
                        }

                        $success_msg = "変更が完了いたしました。";
                     break;
                     case 'delete':
                        $array = array();
                        $array['user_delete_date']     = "'".$_now_timestamp."'";
                        $where = "user_id='"._as($this_sess['id'])."'";
                        _update( 'm_user', $array, $where );
                        $success_msg = "削除しました。";
                     break;
                }

                //**************************************************
                // 来場者マスタの更新 2021.05.25 del
                //**************************************************
                // if ( $this_sess['user_syoutai_id'] != '' && $this_sess['mode'] != "delete"){
                //     $array = array();
                //     $array['syoutai_vip_flg']          = ""._e2z($this_sess['user_vip_flg']).""; //VIPフラグ（1:VIP）',
                //     $array['syoutai_big_cate']         = ""._e2n($this_sess['user_big_cate'])."";//大分類',
                //     $array['syoutai_mid_cate']         = ""._e2n($this_sess['user_mid_cate']).""; //中分類',
                //     $array['syoutai_kigyou_name']      = "'"._as($this_sess['user_kigyou_name'])."'"; //企業名',
                //     $array['syoutai_kigyou_name_kana'] = "'"._as($this_sess['user_kigyou_name_kana'])."'"; //企業名カナ',
                //     $array['syoutai_busyo']            = "'"._as($this_sess['user_busyo'])."'"; //部署',
                //     $array['syoutai_yakusyoku']        = "'"._as($this_sess['user_yakusyoku'])."'"; //役職',
                //     $array['syoutai_last_upd_naiyou']  = "'"._as( '修正' )."'"; //最終更新内容',
                //     $array['syoutai_busyo']            = "'"._as($this_sess['user_busyo'])."'"; //部署',
                //     $array['syoutai_yakusyoku']        = "'"._as($this_sess['user_yakusyoku'])."'"; //役職',
                //     $array['syoutai_biko']             = "'"._as($this_sess['user_biko'])."'"; //備考',
                //     $array['syoutai_update_date']      = "'".$_now_timestamp."'"; //更新日時',
                //     $array_n = array();
                //     $array_n['sn_syoutai_name']        = "'"._as($this_sess['user_name'])."'"; //'氏名',
                //     $array_n['sn_syoutai_name_kana']   = "'"._as($this_sess['user_name_kana'])."'"; //氏名カナ',
                //     $array_m = array();
                //     $array_m['sm_syoutai_mail']        = "'"._as($this_sess['user_mail'])."'"; //メールアドレス',
                //     $array_m['sm_syoutai_login_id']    = "'"._as($this_sess['user_login_id'])."'"; // ログインid',
                //     $where = "syoutai_id='"._as( $this_sess['user_syoutai_id'] )."'";
                //     _update( 'm_syoutai', $array, $where );
                //     $where = "sn_syoutai_id='"._as( $this_sess['user_syoutai_id'] )."'";
                //     _update( 'm_sname', $array_n, $where );
                //     $where = "sm_syoutai_id='"._as( $this_sess['user_syoutai_id'] )."'";
                //     _update( 'm_smail', $array_m, $where );
                //     $success_msg .= "<br>来場者マスタを更新しました。";
                // }

                // マイページURLの通知 1:する
                if ( $this_sess['mypage_notice'] == 1 && $this_sess['mode']!="delete"){
                    $admin_recs = _select("select * from v_admin left join m_syozoku on (m_syozoku.syozoku_id=v_admin.admin_syozoku_id) where admin_id='"._as($this_sess['user_admin_id'])."'");
                    $touroku_admin_recs = _select("select * from v_admin where admin_id='"._as($_SESSION[_PROJECT_NAME]['admin_login']['admin_id'])."'");

                    // smarty set
                    $msm = new UserBlade();
                    $msm->assign('_SYSTEM_ROOT_URLS',_SYSTEM_ROOT_URLS);

                    // $pass_change_url = _SYSTEM_ROOT_URLS."/mypage/".$select_event_rec['event_url_key']."/?page=login&setpw="._urlCodeEncode($this_sess['user_mail']."#".$new_pass); 2021/04/01 Del
                    $new_pass = "_NEED_PASS_SET_";
                    $pass_change_url = _SYSTEM_ROOT_URLS."/mypage/".$select_event_rec['event_url_key']."/?page=login&setpw="._urlCodeEncode($this_sess['user_login_id']."#".$new_pass);

                    $exhibition_url = _SYSTEM_ROOT_URLS."/exhibition/".$select_event_rec['event_exhibition_url_key']."/";

                    // 2021.05.27 mod -------- Before --------
                    // $msm->assign('pass_change_url', $pass_change_url);
                    // $msm->assign('user_rec',  $this_sess);
                    // $msm->assign('admin_rec', $admin_recs[0]);
                    // $msm->assign('event_rec', $select_event_rec);
                    // 2021.05.27 mod -------- After --------
                    $data_rec = array();
                    $data_rec['event_name']        = $select_event_rec['event_name'];
                    $data_rec['kigyou_name']       = $this_sess['user_kigyou_name'];
                    $data_rec['name']              = $this_sess['user_name'];
                    $data_rec['mypage_url']        = _SYSTEM_ROOT_URLS."/mypage/".$select_event_rec['event_url_key']."/";
                    $data_rec['login_id']          = $this_sess['user_login_id'];
                    $data_rec['mypage_manual_url'] = _SYSTEM_ROOT_URLS."/mypage/user_login_info.pdf";
                    $data_rec['tantou_syozoku']    = $admin_recs[0]['syozoku_name'];
                    $data_rec['tantou_name']       = $admin_recs[0]['admin_name'];
                    $data_rec['tantou_mail']       = $admin_recs[0]['admin_mail'];
                    $data_rec['pass_set_url']      = $pass_change_url;
                    $data_rec['exhibition_url']    = $exhibition_url;
                    _setAssign($msm,$data_rec);
                    // 2021.05.27 mod -------- End --------

                    //2021/05/25 Mod ----- Before ------
                    // $ex_login_dir = "";
                    // if($select_event_rec['event_url_key']=='w2021fc-s'){
                    //     $ex_login_dir = "west2021s/";
                    // }elseif($select_event_rec['event_url_key']=='e2021fc-s'){
                    //     $ex_login_dir = "east2021s/";
                    // }
                    //2021/05/25 Mod ----- After ------
                    $ex_login_dir = $select_event_rec['event_exhibition_url_key']."/";
                    //2021/05/25 Mod ----- End ------
                    $msm->assign('ex_login_dir',$ex_login_dir);

                    // template set
                    if ( $this_sess['user_raijyou_yotei_time'] != ''){
                        $mail_tpl = "pass_set_annai.tpl";
                        $qr_link = _create_qr_link($_SESSION[_PROJECT_NAME]['select_event_id'], $this_sess['id']);
                        $msm->assign('qr_url',$qr_link);
                    }else{
                        $mail_tpl = "pass_set_annai_web_only.tpl";
                    }

                    // 2021.05.27 mod -------- Before --------
                    // $title_and_body = _smartyFetch( $msm, _SYSTEM_ROOT_DIR . '/mail/' . $mail_tpl );
                    // $w_arr = explode ("\n", $title_and_body, 2 );
                    // $title = array_shift( $w_arr );
                    // if( substr( $title, strlen( $title ) - 1, 1 ) == "\r" ){
                    //     $title = substr( $title, 0, strlen( $title ) - 1 );
                    // }
                    // $body = join( "\n", $w_arr );
                    // 2021.05.27 mod -------- After --------
                    $ret = _bladeFetchFromMailTplDB( $msm, $mail_tpl );
                    $title = $ret['subject'];
                    $body = $ret['body'];
                    // 2021.05.27 mod -------- End --------

                    $attach = array();

                    //Return-Path設定
                    $adHeader = array();
                    $adHeader['Return-Path'] = "retadr-".$this_sess['id']."@"._RETURN_PATH_MAIL_DOMAIN;

                    //_sendMailEx( _SYSTEM_INFO_MAIL, _SYSTEM_INFO_MAIL_NAME, $real_user_mail_addr, $this_sess['user_name']." 様", $title, $body,$attach );
                    // _accessSendMail( _SYSTEM_INFO_MAIL, _SYSTEM_INFO_MAIL_NAME, $real_user_mail_addr, $this_sess['user_name']." 様", $title, $body,$attach ); 2021/04/01 Del
                    _accessSendMail( _SYSTEM_INFO_MAIL, _SYSTEM_INFO_MAIL_NAME, $this_sess['user_mail'], $this_sess['user_name']." 様", $title, $body,$attach , $adHeader);

                    if($this_sess['user_big_cate'] != 7){
                        //対象ユーザーがAC社員でなければ
                        // ********************************
                        // 担当者にも通知
                        // ********************************
                        $msm2 = new UserBlade();

                        //2021/06/03 Mod ----------- After ------------
                        // $msm2->assign('_SYSTEM_ROOT_URLS',_SYSTEM_ROOT_URLS);
                        // $msm2->assign('user_rec', $this_sess);
                        // $msm2->assign('admin_rec', $admin_recs[0]);
                        // $msm2->assign('touroku_admin_rec', $touroku_admin_recs[0]);
                        // $msm2->assign('event_rec',$select_event_rec);

                        // // template set
                        // $mail_tpl = "syoutai_tsuuchi.tpl";

                        // $title_and_body = _smartyFetch( $msm2, _SYSTEM_ROOT_DIR . '/mail/' . $mail_tpl );
                        // $w_arr = explode ("\n", $title_and_body, 2 );
                        // $title = array_shift( $w_arr );
                        // if( substr( $title, strlen( $title ) - 1, 1 ) == "\r" ){
                        //     $title = substr( $title, 0, strlen( $title ) - 1 );
                        // }
                        // $body = join( "\n", $w_arr );
                        //2021/06/03 Mod ----------- After ------------

                        $data_rec = array();
                        $data_rec['event_name']        = $select_event_rec['event_name'];
                        $data_rec['tantou_syozoku']    = $admin_recs[0]['syozoku_name'];
                        $data_rec['tantou_name']       = $admin_recs[0]['admin_name'];
                        $data_rec['tantou_mail']       = $admin_recs[0]['admin_mail'];
                        $data_rec['touroku_tantou_name']       = $touroku_admin_recs[0]['admin_name'];
                        $data_rec['kigyou_name']       = $this_sess['user_kigyou_name'];
                        $data_rec['name']              = $this_sess['user_name'];
                        $data_rec['admin_url']     = _SYSTEM_ROOT_URLS . "/admin/";
                        $data_rec['pass_reset_url']  = _SYSTEM_ROOT_URLS . "/admin/?page=pass_reissue";

                        _setAssign($msm2,$data_rec);

                        // template set
                        $mail_tpl = "syoutai_tsuuchi.tpl";

                        $ret = _bladeFetchFromMailTplDB( $msm2, $mail_tpl );
                        $title = $ret['subject'];
                        $body = $ret['body'];
                        //2021/06/03 Mod ----------- End ------------

                        $attach = array();
                        //_sendMailEx( _SYSTEM_INFO_MAIL, _SYSTEM_INFO_MAIL_NAME, $real_user_mail_addr, $this_sess['user_name']." 様", $title, $body,$attach );
                        _accessSendMail( _SYSTEM_INFO_MAIL, _SYSTEM_INFO_MAIL_NAME, $admin_recs[0]['admin_mail'], $admin_recs[0]['admin_name']." 様", $title, $body,$attach );

                        if($admin_recs[0]['admin_mail2']!=""){
                            _accessSendMail( _SYSTEM_INFO_MAIL, _SYSTEM_INFO_MAIL_NAME, $admin_recs[0]['admin_mail2'], $admin_recs[0]['admin_name']." 様", $title, $body,$attach );
                        }
                    }

                }

                _query($conn,'commit');

                $w_id = $this_sess['id'];
                $w_mode = $this_sess['mode'];
                $w_from_page = $this_sess['from_page'];
                $_SESSION[_PROJECT_NAME][$page] = array();
                unset( $_SESSION[_PROJECT_NAME][$page] );
                unset( $this_sess );
                $this_sess = array();


                if($w_mode != 'delete'){
                    $_request['exec'] = "";
                    $_request['id'] = $w_id;
                    $_request['from_page'] = $w_from_page;
                }else{
                    _query( $conn, "commit" );//add for redirect
                    header('Location: index.php?page=user_list&sess_no_init=1');//OK1
                    exit();
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

        if( $_request['id'] != "" ){
            // 編集
            $sql  = "";
            $sql .= " select ";
            $sql .= "   * ";
            $sql .= " from $table_v_user ";
            $sql .= " left join v_admin on (v_admin.admin_id = $table_v_user.user_admin_id) ";
            $sql .= " left join m_syozoku on (m_syozoku.syozoku_id = v_admin.admin_syozoku_id) ";
            $sql .= " where ";
            $sql .= "     user_delete_date is null";
            $sql .= "     and user_event_id ='"._as($_SESSION[_PROJECT_NAME]['select_event_id'])."'";
            $sql .= "     and user_id='"._as($_request['id'])."'";
            $main_rec = _select($sql);
            if(_count($main_rec) > 0){
                $this_sess = $main_rec[0];
                $this_sess['id'] = $main_rec[0]['user_id'];
                $this_sess['init_pass'] = $main_rec[0]['user_pass'];
                $this_sess['raijyousya_kbn'] = $_conf_raijyousya_kbn[$this_sess['user_big_cate']];
                $this_sess['mode'] = "update";

                $this_sess['NoDisplay'] = "";
                $new_pass = "_NEED_PASS_SET_" ;
                if ( $main_rec[0]['user_pass'] == md5( $new_pass ) ){
                    $pass_change_url = _SYSTEM_ROOT_URLS."/mypage/".$select_event_rec['event_url_key']."/?page=login&setpw="._urlCodeEncode($this_sess['user_login_id']."#".$new_pass);
                } else {
                    $this_sess['NoDisplay'] = "display:none;";
                }
                $this_sess['init_data'] = $main_rec[0];

                //ログイン履歴
                $sql = "";
                $sql .= "select * from t_uloginlog where ullog_user_id='"._as($_request['id'])."' order by ullog_insert_date desc";
                $log_recs = _select($sql);
                $this_sess['log_recs'] = $log_recs;


                $_as = function($s) {
                    return _as($s);
                };

                //エリアIN/OUT情報
                $sql =<<<EOL
select *
  from $table_area_inout
 inner join m_area
    on m_area.area_event_id = $table_area_inout.ainout_event_id
   and m_area.area_id = $table_area_inout.ainout_area_id
   and m_area.area_delete_date is null
 where $table_area_inout.ainout_event_id = '{$_as($_SESSION[_PROJECT_NAME]['select_event_id'])}'
   and $table_area_inout.ainout_user_id = '{$_as($this_sess['id'])}'
   and $table_area_inout.ainout_delete_date is null
 order by $table_area_inout.ainout_insert_date desc
EOL;
                $area_inout_recs = _select($sql);
                $this_sess['area_inout_recs'] = $area_inout_recs;
            }else{
                // 新規登録と同じ
                $_request['id'] = "";
                $this_sess['user_web'] = "1";
                $this_sess['mode'] = "insert";
                $this_sess['mypage_notice'] = "1";

                $this_sess['init_data'] = array();
                $this_sess['init_data']['user_syounin_flg'] = 1;
            }
        }else{
            // 新規登録
            $this_sess['user_web'] = "1";
            $this_sess['mode'] = "insert";
            $this_sess['mypage_notice'] = "1";

            $this_sess['init_data'] = array();
            $this_sess['init_data']['user_syounin_flg'] = 1;
        }

        if( $_request['from_page'] != "" ){
            $this_sess['from_page'] = $_request['from_page'];
        }

        $this_sess['token'] = $token;
    }

    // イベントに招待する担当者は維持する
    //$this_sess['user_admin_id'] = $_request['user_admin_id'];
    // イベントに招待する担当者が空だった場合
    if ( $this_sess['user_admin_id'] == '' ){
        // 新規モードの場合のみ
        if ( $this_sess['mode'] == 'insert' ){
            // ログインIDを設定する
            $this_sess['user_admin_id'] = $_SESSION[_PROJECT_NAME]['admin_login']['admin_id'];
        }
    }

    if ( $this_sess['user_admin_id'] != ''){
        $sql = "";
        $sql .= " select admin_name,syozoku_id, syozoku_name "."\n";
        $sql .= " from v_admin "."\n";
        $sql .= " left join m_syozoku on (m_syozoku.syozoku_id = v_admin.admin_syozoku_id) "."\n";
        $sql .= " where admin_delete_date is null "."\n";
        $sql .= " and syozoku_delete_date is null "."\n";
        $sql .= " and admin_id = '"._as( $this_sess['user_admin_id'] )."'";
        $tantou_recs = _select($sql);

        $this_sess['syozoku_name']     = $tantou_recs[0]['syozoku_name'];
        $this_sess['admin_name']       = $tantou_recs[0]['admin_name'];
        $this_sess['admin_syozoku_id'] = $tantou_recs[0]['syozoku_id'];
    }

    _setAssign($blade,$this_sess);

    //(招待者)来場予定日時
    $wArr = explode("#", $select_event_rec['event_syoutai_yotei_time']);
    $_conf_syoutai_yotei_time = array();
    for ($i=0; $i < _count($wArr); $i++) {
        $dtArr = explode(" ", $wArr[$i],2);
        $ymd = $dtArr[0];
        //2021/07/08 Mod --------- Before ------
        // $disp_ymd = date("n月j日",strtotime($dtArr[0]) )."（"._getYoubi($dtArr[0])."）";
        //2021/07/08 Mod --------- After ------
        if( $ymd=='2999/01/01' ){
            $disp_ymd = "　　　　　　";
        }else{
            $disp_ymd = date("n月j日",strtotime($dtArr[0]) )."（"._getYoubi($dtArr[0])."）";
        }
        //2021/07/08 Mod --------- End ------
        $hi = $dtArr[1];
        $_conf_syoutai_yotei_time[$ymd]['disp_ymd'] = $disp_ymd;

        $checked="";
        if($this_sess['user_raijyou_yotei_time']!="" && $this_sess['raijyousya_kbn']=="1") {
            if( strpos($this_sess['user_raijyou_yotei_time'],$wArr[$i]) !== FALSE ){
                $checked = "checked";
            }
        }
        $_conf_syoutai_yotei_time[$ymd]['his'][] = array('hi'=>$hi, 'checked'=>$checked);
        // 2021.05.31 add ------- Start -------
        $ymd_hi = $ymd." ".$hi;

        //2021/07/08 Mod -------- Before ------
        // $_conf_syoutai_yotei_time2[$ymd_hi] = $ymd_hi;
        //2021/07/08 Mod -------- After ------
        $ymd_hi_val = $ymd_hi;
        $ymd_hi_val = str_replace("2999/01/01 ", "", $ymd_hi_val);
        $_conf_syoutai_yotei_time2[$ymd_hi] = $ymd_hi_val;
        //2021/07/08 Mod -------- End ------

        // 2021.05.31 add ------- End   -------

    }
    $blade->assign('_conf_syoutai_yotei_time',$_conf_syoutai_yotei_time);
    $blade->assign('_conf_syoutai_yotei_time2',$_conf_syoutai_yotei_time2); // 2021.05.31 add

    //(招待者)来場予定日時
    $wArr = explode("#", $select_event_rec['event_raijyou_yotei_time']);
    $_conf_raijyou_yotei_time = array();
    for ($i=0; $i < _count($wArr); $i++) {
        $dtArr = explode(" ", $wArr[$i],2);
        $ymd = $dtArr[0];
        //2021/07/08 Mod --------- Before ------
        // $disp_ymd = date("n月j日",strtotime($dtArr[0]) )."（"._getYoubi($dtArr[0])."）";
        //2021/07/08 Mod --------- After ------
        if( $ymd=='2999/01/01' ){
            $disp_ymd = "　　　　　　";
        }else{
            $disp_ymd = date("n月j日",strtotime($dtArr[0]) )."（"._getYoubi($dtArr[0])."）";
        }
        //2021/07/08 Mod --------- End ------
        $hi = $dtArr[1];
        $_conf_raijyou_yotei_time[$ymd]['disp_ymd'] = $disp_ymd;

        $checked="";
        if($this_sess['user_raijyou_yotei_time']!="" && $this_sess['raijyousya_kbn']=="2") {
            if( strpos($this_sess['user_raijyou_yotei_time'],$wArr[$i]) !== FALSE ){
                $checked = "checked";
            }
        }
        $_conf_raijyou_yotei_time[$ymd]['his'][] = array('hi'=>$hi, 'checked'=>$checked);
    }
    $blade->assign('_conf_raijyou_yotei_time',$_conf_raijyou_yotei_time);

    //所属支店部署マスタ
    $sql = "";
    $sql .= "select * from m_syozoku";
    $sql .= " where";
    $sql .= " syozoku_delete_date is null";
    $sql .= " order by syozoku_id asc";
    $syozoku_recs = _select($sql);
    $_conf_syozoku = array();
    for ($i=0; $i < _count($syozoku_recs); $i++) {
        $_conf_syozoku[ $syozoku_recs[$i]['syozoku_id'] ] = $syozoku_recs[$i]['syozoku_name'];
    }
    $blade->assign('_conf_syozoku',$_conf_syozoku);

    //担当者
    if($this_sess['admin_syozoku_id']!=""){
        $sql = "";
        $sql .= "select * from v_admin";
        $sql .= " left join m_syozoku on (m_syozoku.syozoku_id = v_admin.admin_syozoku_id)";
        $sql .= " where";
        $sql .= " admin_delete_date is null";
        $sql .= " and admin_mail != 'admin'";
        $sql .= " and admin_syozoku_id = '".$this_sess['admin_syozoku_id']."'";
        $sql .= " order by admin_tanarea_id asc,admin_syozoku_id asc,admin_id asc";
        $tan_recs = _select($sql);
    }else{
        $tan_recs = array();
    }
    $_conf_tantousya = array();
    for ($i=0; $i < _count($tan_recs); $i++) {
        $_conf_tantousya[ $tan_recs[$i]['admin_id'] ] = $tan_recs[$i]['syozoku_name']." ".$tan_recs[$i]['admin_name'];
    }
    $blade->assign('_conf_tantousya',$_conf_tantousya);


    $blade->assign('_conf_vip',$_conf_vip);
    $blade->assign('_conf_user_syounin_flg',$_conf_user_syounin_flg); // 2021.06.05

    // $blade->assign('_conf_big_cate_detail',$_conf_big_cate_detail);
    // $blade->assign('_conf_mid_cate',$_conf_mid_cate);
    if ( $this_sess['mode'] == 'insert' || $this_sess['raijyousya_kbn'] == "2" ){
        $blade->assign('_var_big_cate', $_conf_big_cate2 );
        $blade->assign('_var_mid_cate', $_conf_mid_cate2 );
    } else {
        $blade->assign('_var_big_cate', $_conf_big_cate1 );
        $blade->assign('_var_mid_cate', $_conf_mid_cate1 );
    }

    if($this_sess['mode']=="insert"){
        $modeStr = "新規登録";
    }else{
        $modeStr = "編集";
    }

    $blade->assign('pass_change_url', $pass_change_url);

    $blade->assign('title_bgcolor',$select_event_rec['event_title_bgcolor']);

    $contents_title = "来場者".$modeStr . "（".$select_event_rec['event_name'] .":".$select_event_rec['event_kaijyou_name']."）";
    $active_menu = "user_list";
    $contents_tpl = "user_edit";
