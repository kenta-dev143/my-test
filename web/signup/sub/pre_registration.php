<?php
    if( !defined("_PROJECT_DISP_NAME") ){
        die("System Error");
    }

    //**************************************************
    // 初期処理
    //**************************************************
    $page = $_request['page'];
    $this_sess = &$_SESSION[_PROJECT_NAME][$page];
    $err_msg = array();
    
    switch( $_request['exec'] ){
        //################################################################################################
        // 仮登録ー確認画面
        //################################################################################################
        case 'pre_confirm':

            $chks = array(
                        "user_login_id,メールアドレス"                                => "need,email", //2021/04/01 mod
                        "user_login_id_chk,メールアドレス(確認用)"                    => "need,match=user_login_id", //2021/04/01 mod
                        "admin_mail,日本アクセス担当者メールアドレス"                 => "need,email",
                        "admin_mail_chk,日本アクセス担当者メールアドレス(確認用)"     => "need,match=admin_mail",
                        "agent_mail,代理登録者メールアドレス"                         => "email",
                        );

            $err_msg = _check( $chks, $_request );

            if(_count($err_msg)==0){

                // ID(メアド)から実際のメールアドレス部分抽出
                // 2021/04/01 mod ------------------------ before ----------------------- 大幅変更
                // $_request['real_user_mail_addr'] = _getMailAddressFromID( $_request['user_login_id'] );
                // if ( _emailCheck($_request['real_user_mail_addr'], '') === false ){
                    // 2021/04/01 mod ------------------------ after ------------------------ 大幅変更
                $_request['user_mail'] = _getMailAddressFromID( $_request['user_login_id'] );
                // emailアドレスの形式チェック
                if ( _emailCheck($_request['user_mail'], '') === false ){
                // 2021/04/01 mod ------------------------ end   ------------------------ 大幅変更
                    $err_msg[]  = "メールアドレスを正しく入力して下さい。";
                }

                $sql  = "";
                $sql .= " select * from v_user ";
                $sql .= " where ";
                $sql .= "   user_delete_date is null ";
                $sql .= "   and user_event_id = '" . _as( $event_rec['event_id'] )  . "'";
                // 2021/04/01 mod ------------------------ before ----------------------- 大幅変更
                // $sql .= "   and user_mail = '" . _as( $_request['user_mail'] )  . "'";
                // 2021/04/01 mod ------------------------ after ------------------------ 大幅変更
                $sql .= "   and user_login_id = '". _as( $_request['user_login_id'] ) ."'";
                // 2021/04/01 mod ------------------------ end   ------------------------ 大幅変更
                $user_recs = _select( $sql);
                if( _count( $user_recs ) > 0 ){
                    $err_msg[] = "このメールアドレスは既に登録済みです。";
                }

                $sql = "";
                $sql .= " select admin_id"."\n";
                $sql .= " from v_admin"."\n";
                $sql .= " where admin_delete_date is null"."\n";
                $sql .= "   and admin_mail = '"._as($_request['admin_mail'])."'"."\n";
                $admin_recs = _select($sql);
                if ( _count($admin_recs)==0 ){
                    $err_msg[] = "該当する日本アクセス担当者がいません。";
                }


            }

            //**** POST値をセッションにマージ ****
            $this_sess = _array_merge( $this_sess, $_request );
            if( _count( $err_msg ) == 0 ){
                $contents_tpl = "pre_confirmation.html";
            }else{
                $contents_tpl = "pre_registration.html";
            }
            break;

        //################################################################################################
        // 仮登録ー完了画面
        //################################################################################################
        case 'pre_save':
            if( _count( $this_sess ) <= 0 ){
                //**** リロードやボタンダブルクリックでの２重登録抑制
                $err_msg[] = 'このデータは既に処理済みです。';
            }else{

                _query($conn,'begin');

                $array = array();
                $array['sup_event_id']      = "'"._as($event_rec['event_id'])."'";
                $array['sup_user_mail']     = "'"._as($this_sess['user_mail'])."'";
                $array['sup_user_login_id'] = "'"._as($this_sess['user_login_id'])."'"; //2021/04/01 add
                $array['sup_admin_mail']    = "'"._as($this_sess['admin_mail'])."'";
                $array['sup_agent_mail']    = "'"._as($this_sess['agent_mail'])."'"; // 代理登録者メールアドレス
                $array['sup_insert_date']   = "'".$_now_timestamp."'"; //更新日時',
                $array['sup_update_date']   = "'".$_now_timestamp."'"; //更新日時',
                _insert('t_signup',$array);

                //INSERTしたID取得
                $recs = _select("SELECT LAST_INSERT_ID() as sup_id");
                $sup_id = $recs[0]['sup_id'];

                //IDとメアドを暗号化しURL作成
                $str = $sup_id . ";" . $event_rec['event_id'] . ";" . $this_sess['user_login_id'];
                $ango = _urlCodeEncode($str);
                $hontouroku_url = _SYSTEM_ROOT_URLS."/signup/".$event_rec['event_url_key']."/?exec=regist&iu=".$ango;

                $ex_login_dir = $event_rec['event_exhibition_url_key']."/";
                $ex_login_url = _SYSTEM_ROOT_URLS."/exhibition/".$ex_login_dir;

                $sql = "";
                $sql .= " select *"."\n";
                $sql .= " from v_admin"."\n";
                $sql .= " where admin_delete_date is null"."\n";
                $sql .= "   and admin_mail = '"._as($this_sess['admin_mail'])."'"."\n";
                $admin_recs = _select($sql);

                // smarty set
                $msm = new UserBlade();

                // 2021.06.04 mod ------------- Before -------------
                // $msm->assign('_SYSTEM_ROOT_URLS',_SYSTEM_ROOT_URLS);
                // $msm->assign('hontouroku_url',  $hontouroku_url);
                // $msm->assign('ex_login_url',$ex_login_url);
                // $msm->assign('admin_rec',      $admin_recs[0]);
                // $msm->assign('event_rec',$event_rec);
                // $msm->assign('user_login_id',$this_sess['user_login_id']);
                // 2021.06.04 mod ------------- After   -------------
                $data_rec = array();
                $data_rec['event_name']     = $event_rec['event_name'];
                $data_rec['login_id']       = $this_sess['user_login_id'];
                $data_rec['hontouroku_url'] = $hontouroku_url;
                $data_rec['exhibition_url'] = $ex_login_url;
                $data_rec['tantou_name']    = $admin_recs[0]['admin_name'];
                $data_rec['tantou_mail']    = $admin_recs[0]['admin_mail'];
                _setAssign($msm,$data_rec);
                // 2021.06.04 mod ------------- END     -------------


                // template set
                $mail_tpl = "signup_kari_touroku.tpl";

                // 2021.06.04 mod ------------- Before -------------
                // $title_and_body = _smartyFetch( $msm, _SYSTEM_ROOT_DIR . '/mail/' . $mail_tpl );
                // $w_arr = explode ("\n", $title_and_body, 2 );
                // $title = array_shift( $w_arr );
                // if( substr( $title, strlen( $title ) - 1, 1 ) == "\r" ){
                //     $title = substr( $title, 0, strlen( $title ) - 1 );
                // }
                // $body = join( "\n", $w_arr );
                // $attach = array();

                // _accessSendMail( _SYSTEM_INFO_MAIL, _SYSTEM_INFO_MAIL_NAME, $this_sess['user_mail'], "", $title, $body,$attach );
                // 2021.06.04 mod ------------- After   -------------
                $ret = _bladeFetchFromMailTplDB( $msm, $mail_tpl );

                $title = $ret['subject'];
                $body = $ret['body'];
                $attach = array();

                _accessSendMail( _SYSTEM_INFO_MAIL, _SYSTEM_INFO_MAIL_NAME, $this_sess['user_mail'], "", $title, $body,$attach );

                // 代理登録者の入力がある場合は、代理登録者メールアドレスにもメールを送信する
                if ( ! empty($this_sess['agent_mail'])) {
                    _accessSendMail( _SYSTEM_INFO_MAIL, _SYSTEM_INFO_MAIL_NAME, $this_sess['agent_mail'], "", $title, $body,$attach );
                }
                // 2021.06.04 mod ------------- END     -------------

                _query($conn,'commit');

                $_SESSION[_PROJECT_NAME][$page] = array();
                unset( $_SESSION[_PROJECT_NAME][$page] );
                unset( $this_sess );
                $this_sess = array();
    
            }

            $contents_tpl = "pre_thanks.html";

            break;

        //################################################################################################
        // 本登録ー入力画面
        //################################################################################################
        case 'regist':
            //**************************************************
            // 初期表示
            //**************************************************
            unset( $_SESSION[_PROJECT_NAME][$page] );
            unset( $this_sess );
            $this_sess = &$_SESSION[_PROJECT_NAME][$page];
            $this_sess = array();

            $url_mukou = "";

            if($_request['iu']==""){
                $err_msg[] = "このURLは無効です。";
                $url_mukou = 1;
            }else{
                $str = _urlCodeDecode($_request['iu']);
                $wArr = explode(";", $str);
                $p_sup_id = $wArr[0];
                $p_event_id = $wArr[1];
                $p_user_login_id = $wArr[2]; //2021/04/01 mod

                if($p_event_id != $event_rec['event_id']){
                    $err_msg[] = "このURLは無効なURLです。";
                    $url_mukou = 1;
                }else{
                    // 2021/04/01 mod ------------------------ before ----------------------- 大幅変更
                    // $sup_recs = _select("select * from t_signup where sup_id="._as($p_sup_id)." and sup_user_mail='".$p_user_mail."' and sup_event_id='".$p_event_id."'");
                    // 2021/04/01 mod ------------------------ after ------------------------ 大幅変更
                    $sup_recs = _select("select * from t_signup where sup_delete_date is null and sup_id="._as($p_sup_id)." and sup_user_login_id='".$p_user_login_id."' and sup_event_id='".$p_event_id."'");
                    // 2021/04/01 mod ------------------------ end   ------------------------ 大幅変更
                    if(_count($sup_recs)==0){
                        $err_msg[] = "無効なURLです。";
                        $url_mukou = 1;
                    }else{
                        $this_sess = $sup_recs[0];
                    }
                }
                
            }

            $blade->assign('url_mukou',$url_mukou);

            $contents_tpl = "registration.html";
            break;
        case 'regist_back':

            $contents_tpl = "registration.html";
            break;

        //################################################################################################
        // 本登録ー確認画面
        //################################################################################################
        case 'confirm':

            // 2021.06.30 add ------------ Start ----------------
            if ( $this_sess['sup_id'] == '' ){
                $err_msg[] = "セッションが切断されました。メール記載のURLから再度アクセスしてください。";
                $url_mukou = 1;
                $blade->assign('url_mukou',$url_mukou);
                
            } else {

                $chks = array(
                                "user_name_sei,姓"                 => "need",
                                "user_name_mei,名"                 => "need",
                                "user_kigyou_name,会社名"          => "need",
                                // "user_kigyou_name_kana,会社名カナ"                 => "zenkana",
                                "user_big_cate,業種"               => "need",
                                // "user_mid_cate,中分類"          => "need",
                                "user_busyo,部署"                  => "need",
                                //"user_yakusyoku,役職"                 => "need",
                            // 2021.06.04 del ---------------- Start ----------------
                            //    "user_pass,パスワード"              => "need,eisuubar,min=4",
                            //    "user_pass_chk,パスワード(確認用)"  => "need,eisuubar,min=4,match=user_pass"
                            // 2021.06.04 del ---------------- End   ----------------
                            );

                $err_msg = _check( $chks, $_request );
            }
            // 2021.06.30 add ------------ end   ----------------

            //**** POST値をセッションにマージ ****
            $this_sess = _array_merge( $this_sess, $_request );
            if( _count( $err_msg ) == 0 ){
                $this_sess['disp_user_big_cate'] = $_conf_big_cate1_web[$this_sess['user_big_cate']];
                $contents_tpl = "confirmation.html";
            }else{
                $contents_tpl = "registration.html";
            }
            break;

        //################################################################################################
        // 本登録ー完了画面
        //################################################################################################
        case 'save':
            if( _count( $this_sess ) <= 0 ){
                //**** リロードやボタンダブルクリックでの２重登録抑制
                $err_msg[] = 'このデータは既に処理済みです。';
            }else{

                _query($conn,'begin');

                $max_recs = _select( "select coalesce(max(substring(user_id,2)),'0') as max_id from m_user");
                $this_sess['id'] = sprintf("u%08d", $max_recs[0]['max_id'] + 1 );

                $this_sess['user_pass'] = "_NEED_PASS_SET_"; // 2021.06.04 add

                $sql = "";
                $sql .= " select *"."\n";
                $sql .= " from v_admin left join m_syozoku on (m_syozoku.syozoku_id=v_admin.admin_syozoku_id)"."\n";
                $sql .= " where admin_delete_date is null"."\n";
                $sql .= "   and admin_mail = '"._as($this_sess['sup_admin_mail'])."'"."\n";
                $admin_recs = _select($sql);

                $array = array();
                $array['user_id']                  = "'"._as($this_sess['id'])."'";
                $array['user_event_id']            = "'"._as($this_sess['sup_event_id'])."'"; //イベントID（e0001）',
                $array['user_admin_id']            = "'"._as($admin_recs[0]['admin_id'])."'"; //担当者ID（a0000001）',
                $array['user_vip_flg']             = "0"; //VIPフラグ（1:VIP）',
                $array['user_big_cate']            = ""._e2n($this_sess['user_big_cate'])."";//大分類',
                $array['user_mid_cate']            = "null"; //中分類',
                $array['user_kigyou_name']         = "'"._as($this_sess['user_kigyou_name'])."'"; //企業名',
                $array['user_kigyou_name_kana']    = "'"._as($this_sess['user_kigyou_name_kana'])."'"; //企業名カナ',
                $array['user_busyo']               = "'"._as($this_sess['user_busyo'])."'"; //部署',
                $array['user_yakusyoku']           = "'"._as($this_sess['user_yakusyoku'])."'"; //役職',
                $array['user_pass']                = "'"._as(md5($this_sess['user_pass']))."'"; //パスワード',
                $array['user_raijyou_yotei_time']  = "''"; //来場予定日時（yyyy/mm/dd HH:ii 形式）',
                $array['user_web']                 = "1"; //WEB招待（1:WEB招待者）',
                $array['user_tag']                 = "''"; //ユーザタグ, 2020.12.19 add
                $array['user_update_date']         = "'".$_now_timestamp."'"; //更新日時',
                $array['user_biko']                = "''"; //備考',
                $array['user_agent_mail']          = "'"._as($this_sess['sup_agent_mail'])."'"; // 代理登録者メールアドレス
                // 2021.06.04 mod ---------------- Before ----------------
                // $array['user_mail_send_kbn']       = "1";
                // 2021.06.04 mod ---------------- After  ----------------
                $array['user_mail_send_kbn']       = "0"; //PASS設定URLメール送信区分(0:未送信、1:送信済み、2:送信エラー)
                $array['user_syounin_flg']         = "0"; //WEB招待の承認フラグ(0:未承認、1:承認済み)
                // 2021.06.04 mod ---------------- End    ----------------
                $array['user_insert_date']         = "'".$_now_timestamp."'";

                _insert( 'm_user', $array);

                $array_n = array();
                $array_n['un_user_id']                  = "'"._as($this_sess['id'])."'";
                $array_n['un_user_name']                = "'"._as($this_sess['user_name_sei']." ".$this_sess['user_name_mei'])."'"; //'氏名',
                $array_n['un_user_name_kana']           = "''"; //氏名カナ',
                _insert( 'm_uname', $array_n);

                $array_m = array();
                $array_m['um_user_id']          = "'"._as($this_sess['id'])."'";
                // 2021/04/01 mod ------------------------ before ----------------------- 大幅変更
                // $array_m['um_user_mail']                = "'"._as($this_sess['sup_user_mail'])."'"; //メールアドレス',
                // 2021/04/01 mod ------------------------ after ------------------------ 大幅変更
                $array_m['um_user_mail']                = "'"._as($this_sess['sup_user_mail'])."'";    // メールアドレス',
                $array_m['um_user_login_id']            = "'"._as($this_sess['sup_user_login_id'])."'"; // ログインid',
                // 2021/04/01 mod ------------------------ end   ------------------------ 大幅変更
                _insert( 'm_umail', $array_m);


                $updArray = array();
                $updArray['sup_delete_date'] = "'".$_now_timestamp."'";
                $where = "sup_id="._as($this_sess['sup_id']);
                _update("t_signup",$updArray,$where);

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

                // smarty set
                $msm = new UserBlade();
                // 2021.06.04 mod ------------- Before -------------
                // $msm->assign('_SYSTEM_ROOT_URLS',_SYSTEM_ROOT_URLS);
                // $msm->assign('ex_login_url',$ex_login_url);
                // $msm->assign('admin_rec',      $admin_recs[0]);
                // $msm->assign('event_rec',$event_rec);
                // $msm->assign('user_rec',$this_sess);
                // 2021.06.04 mod ------------- After   -------------
                $data_rec = array();
                $data_rec['event_name']         = $event_rec['event_name'];
                $data_rec['kigyou_name']        = $this_sess['user_kigyou_name'];
                $data_rec['name']               = $this_sess['user_name_sei'].' '.$this_sess['user_name_mei'];
                $data_rec['login_id']           = $this_sess['sup_user_login_id'];
                $data_rec['exhibition_url']     = $ex_login_url;
                $data_rec['tantou_name']        = $admin_recs[0]['admin_name'];
                $data_rec['tantou_mail']        = $admin_recs[0]['admin_mail'];
                $data_rec['admin_pass_set_url'] = _SYSTEM_ROOT_URLS."/admin/?page=pass_reissue";
                $data_rec['admin_url']          = _SYSTEM_ROOT_URLS."/admin/";
                _setAssign($msm,$data_rec);
                // 2021.06.04 mod ------------- END     -------------


                // template set
                $mail_tpl = "signup_hon_touroku.tpl";

                // 2021.06.04 mod ------------- Before -------------
                // $title_and_body = _smartyFetch( $msm, _SYSTEM_ROOT_DIR . '/mail/' . $mail_tpl );
                // $w_arr = explode ("\n", $title_and_body, 2 );
                // $title = array_shift( $w_arr );
                // if( substr( $title, strlen( $title ) - 1, 1 ) == "\r" ){
                //     $title = substr( $title, 0, strlen( $title ) - 1 );
                // }
                // $body = join( "\n", $w_arr );
                // 2021.06.04 mod ------------- After   -------------
                $ret = _bladeFetchFromMailTplDB( $msm, $mail_tpl );

                $title = $ret['subject'];
                $body = $ret['body'];
                // 2021.06.04 mod ------------- END     -------------

                $attach = array();
                _accessSendMail( _SYSTEM_INFO_MAIL, _SYSTEM_INFO_MAIL_NAME, $this_sess['sup_user_mail'], $this_sess['user_name_sei']." ".$this_sess['user_name_mei']."様", $title, $body,$attach );

                if ( ! empty($this_sess['sup_agent_mail'])) {
                    _accessSendMail( _SYSTEM_INFO_MAIL, _SYSTEM_INFO_MAIL_NAME, $this_sess['sup_agent_mail'], $this_sess['user_name_sei']." ".$this_sess['user_name_mei']."様", $title, $body,$attach );
                }

                //担当者にも通知
                // template set
                $mail_tpl = "signup_syoutai_tsuuchi.tpl";

                // 2021.06.04 mod ------------- Before -------------
                // $title_and_body = _smartyFetch( $msm, _SYSTEM_ROOT_DIR . '/mail/' . $mail_tpl );
                // $w_arr = explode ("\n", $title_and_body, 2 );
                // $title = array_shift( $w_arr );
                // if( substr( $title, strlen( $title ) - 1, 1 ) == "\r" ){
                //     $title = substr( $title, 0, strlen( $title ) - 1 );
                // }
                // $body = join( "\n", $w_arr );
                // 2021.06.04 mod ------------- After   -------------
                $ret = _bladeFetchFromMailTplDB( $msm, $mail_tpl );

                $title = $ret['subject'];
                $body = $ret['body'];
                // 2021.06.04 mod ------------- END     -------------

                $attach = array();
                _accessSendMail( _SYSTEM_INFO_MAIL, _SYSTEM_INFO_MAIL_NAME, $admin_recs[0]['admin_mail'], $admin_recs[0]['admin_name']." 様", $title, $body,$attach );

                if($admin_recs[0]['admin_mail2']!=""){
                    _accessSendMail( _SYSTEM_INFO_MAIL, _SYSTEM_INFO_MAIL_NAME, $admin_recs[0]['admin_mail2'], $admin_recs[0]['admin_name']." 様", $title, $body,$attach );
                }


                _query($conn,'commit');


                $_SESSION[_PROJECT_NAME][$page] = array();
                unset( $_SESSION[_PROJECT_NAME][$page] );
                unset( $this_sess );
                $this_sess = array();


    
            }

            $blade->assign('ex_login_url',$ex_login_url);

            $contents_tpl = "thanks.html";

            break;


        //################################################################################################
        // 仮登録ー入力画面
        //################################################################################################
        default:
            if( $_request['exec'] == "pre_back" ){
                //**************************************************
                // 「戻る」ボタンで戻ってきた時
                //**************************************************
            }else{
                //**************************************************
                // 初期表示
                //**************************************************
                unset( $_SESSION[_PROJECT_NAME][$page] );
                unset( $this_sess );
                $this_sess = &$_SESSION[_PROJECT_NAME][$page];
                $this_sess = array();
            }
            $contents_tpl = "pre_registration.html";
            break;
    }

    // ******************************************************************************************************
    // ASSIGN
    // ******************************************************************************************************
    _setAssign( $blade, $this_sess );
    $blade->assign('_conf_big_cate1_web',$_conf_big_cate1_web);

