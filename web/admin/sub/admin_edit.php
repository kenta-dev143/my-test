<?php
    if( !defined("_PROJECT_DISP_NAME") ){
        die("System Error");
    }

    if( $_SESSION[_PROJECT_NAME]['admin_login']['admin_id'] == "" ){
        die('System Error');
    }

    if( $_SESSION[_PROJECT_NAME]['admin_login']['admin_master_kengen'] != "1" ){
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

    $_ac_mid_cate = array();
    foreach ($_conf_mid_cate2 as $key => $value) {
        if(substr($value,0,2)=="AC"){
            $_ac_mid_cate[$key] = $value;
        }
    }
    $blade->assign('_ac_mid_cate',$_ac_mid_cate);

    // 担当者エリア
    $sql = "";
    $sql .= "select * from m_tantou_area";
    $sql .= " where";
    $sql .= " tanarea_delete_date is null";
    $sql .= " order by tanarea_id asc";
    $tanarea_recs = _select($sql);
    $_conf_tanarea = array();
    for ($i=0; $i < _count($tanarea_recs); $i++) {
        $_conf_tanarea[ $tanarea_recs[$i]['tanarea_id'] ] = $tanarea_recs[$i]['tanarea_name'];
    }
    $blade->assign('_conf_tanarea',$_conf_tanarea);

    // 所属支店部署マスタ
    $sql = "";
    $sql .= "select * from m_syozoku";
    $sql .= " where";
    $sql .= " syozoku_delete_date is null";
    $sql .= " and syozoku_hidden_flg = 0";
    $sql .= " order by syozoku_id asc";
    $syozoku_recs = _select($sql);
    $_conf_syozoku = array();
    for ($i=0; $i < _count($syozoku_recs); $i++) {
        $_conf_syozoku[ $syozoku_recs[$i]['syozoku_id'] ] = $syozoku_recs[$i]['syozoku_name'];
    }
    $blade->assign('_conf_syozoku',$_conf_syozoku);

    // ログイン権限
    $blade->assign('_conf_login_kengen',$_conf_login_kengen);
    // 担当ユーザ権限
    $blade->assign('_conf_user_kengen',$_conf_user_kengen);
    // マスター権限
    $blade->assign('_conf_master_kengen',$_conf_master_kengen);
    // 集計閲覧権限
    $blade->assign('_conf_syuukei_etsuran_kengen',$_conf_syuukei_etsuran_kengen);

    // 企業マスタ
    $sql = "";
    $sql .= "select * from m_company";
    $sql .= " where";
    $sql .= " company_delete_date is null";
    $sql .= " order by company_id asc";
    $company_recs = _select($sql);
    $_conf_company = array();
    for ($i=0; $i < _count($company_recs); $i++) {
        $_conf_company[ $company_recs[$i]['company_id'] ] = $company_recs[$i]['company_name'];
    }
    $blade->assign('_conf_company',$_conf_company);


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

            if($_request['mode'] !='delete'){
                $chks = array(
                                "admin_name,担当者指名"                     => "need",
                                "admin_mail,ログインID(メールアドレス)"     => "need,email",
                                "admin_tanarea_id,担当エリア"               => "seisuu",
                                "admin_syozoku_id,所属支店・部署"           => "need",
                                "admin_user_kengen,担当ユーザ権限"          => "need,seisuu",
                                "admin_syuukei_etsuran_kengen,集計閲覧権限" => "need,seisuu",
                                "admin_master_kengen,マスター管理権限"      => "need,seisuu",
                                "admin_login_kengen,ログイン権限"           => "need,seisuu",

                            );

                // 2021.06.01 mod ---------- Start ----------
                // if ( $this_sess['mode'] == "insert" ){
                //     $chks = array_merge($chks, array( "admin_login_pass,ログインパスワード" => 'need,min=4') );
                // }else{
                //     if($_request['admin_login_pass']!=""){
                //         $chks = array_merge($chks, array( "admin_login_pass,ログインパスワード" => 'min=4') );
                //     }
                // }
                // 2021.06.01 mod ---------- End   ----------

                $err_msg = _check( $chks, $_request );

                if ( _count($err_msg) == 0 ){
                    // メールアドレスの重複チェック
                    $sql = "";
                    $sql .= " select admin_id"."\n";
                    $sql .= " from v_admin"."\n";
                    $sql .= " where "."\n";
                    $sql .= "   admin_delete_date is null ";
                    $sql .= "   and admin_mail = '". _as( $_request['admin_mail'] ) ."'";
                    if ( $_request['mode'] == 'update' ){
                        $sql .= "   and admin_id != '" . _as( $this_sess['id'] ) ."'";
                    }
                    $chk_recs = _select($sql);
                    if ( _count($chk_recs) > 0 ){
                        $err_msg[] = "このメールアドレスは既に登録済みです。";
                    }
                }

                if($_request['mail_notice']=="1" && $_request['admin_login_kengen']=="0"){
                    $err_msg[] = "ログイン権限がない担当者にはメール通知できません。";
                }

                if($_request['new_user_pass_make']=="1"){
                    $this_sess['NoDisplay'] = "";
                }

                if ($_request['admin_company_id'] != "") {
                    $sql = "";
                    $sql .= " select company_id" . "\n";
                    $sql .= " from m_company"."\n";
                    $sql .= " where "."\n";
                    $sql .= "   company_delete_date is null ";
                    $sql .= "   and company_id = ". $_request['admin_company_id'];
                    $admin_company_recs = _select($sql);
                    if ( _count($admin_company_recs) === 0) {
                        $err_msg[] = "選択された企業が企業マスタに存在しません。";
                    }
                }

                if ($_request['kyouryoku_kigyou_flg'] == "1") {
                    if ( ! isset($_request['kyouryoku_kigyou_ids']) || _count($_request['kyouryoku_kigyou_ids']) == 0) {
                        $err_msg[] = "外部企業を選択してください。";
                    } else {
                        if (_count($_request['kyouryoku_kigyou_ids']) > 0) {
                            $sql = "";
                            $sql .= " select company_id" . "\n";
                            $sql .= " from m_company"."\n";
                            $sql .= " where "."\n";
                            $sql .= "   company_delete_date is null ";
                            $sql .= "   and company_id in ( ". implode(',', $_request['kyouryoku_kigyou_ids']) .")";
                            $company_recs = _select($sql);
                            if ( _count($company_recs) !== _count($_request['kyouryoku_kigyou_ids'])) {
                                $err_msg[] = "入力された外部企業が企業マスタに存在しません。";
                            }
                        }
                    }
                }

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
                    $max_recs = _select( "select coalesce(max(substring(admin_id,2)),'0') as max_id from m_admin");
                    $this_sess['id'] = sprintf("a%07d", $max_recs[0]['max_id'] + 1 );
                }

                $array = array();
                $array_n = array();
                $array_m = array();

                // 2021.06.01 mod ---------- Before ----------
                // if ( $this_sess['admin_login_pass'] != '' ){
                //     $array['admin_login_pass']         = "'"._as( md5($this_sess['admin_login_pass']) )."'";
                //     $array['admin_init_pass'] = "'". _as( _urlCodeEncode($this_sess['admin_login_pass']) ) . "'";
                // }
                // 2021.06.01 mod ---------- After ----------
                if ( $this_sess['new_user_pass_make'] != "" ){
                    $new_pass = "_NEED_PASS_SET_";
                    $array['admin_login_pass']            = "'"._as( md5( $new_pass ) )."'"; //パスワード', 2020.12.18 mod
                }
                // 2021.06.01 mod ---------- End   ----------
                $array['admin_tanarea_id']             = _e2n($this_sess['admin_tanarea_id']);
                $array['admin_syozoku_id']             = "'"._as($this_sess['admin_syozoku_id'])."'";
                $array['admin_user_kengen']            = _e2z($this_sess['admin_user_kengen']);
                $array['admin_login_kengen']           = _e2z($this_sess['admin_login_kengen']);
                $array['admin_master_kengen']          = _e2z($this_sess['admin_master_kengen']);
                $array['admin_syuukei_etsuran_kengen'] = _e2z($this_sess['admin_syuukei_etsuran_kengen']);

                $array['admin_mid_cate']            = _e2n($this_sess['admin_mid_cate']); //中分類',
                $array['admin_yakusyoku']           = "'"._as($this_sess['admin_yakusyoku'])."'"; //役職',
                $array['admin_kyouryoku_kigyou_flg'] = _e2z($this_sess['kyouryoku_kigyou_flg']);
                $array['admin_company_id']           = _e2n($this_sess['admin_company_id']);

                $array['admin_update_date']            = "'".$_now_timestamp."'";

                $array_n['an_admin_name']                   = "'"._as($this_sess['admin_name'])."'";

                $array_m['am_admin_mail']                   = "'"._as($this_sess['admin_mail'])."'";

                switch( $this_sess['mode'] ){
                    case 'insert':
                        $array['admin_id']             = "'"._as($this_sess['id'])."'";
                        $array['admin_insert_date']    = "'".$_now_timestamp."'";
                        _insert( 'm_admin', $array);

                        $array_n['an_admin_id']             = "'"._as($this_sess['id'])."'";
                        _insert( 'm_aname', $array_n);

                        $array_m['am_admin_id']             = "'"._as($this_sess['id'])."'";
                        _insert( 'm_amail', $array_m);

                        $success_msg = "登録しました。";
                    break;
                    case 'update':
                        $where = "admin_id='"._as($this_sess['id'])."'";
                        _update( 'm_admin', $array, $where );

                        $where = "an_admin_id='"._as($this_sess['id'])."'";
                        _update( 'm_aname', $array_n, $where );

                        $where = "am_admin_id='"._as($this_sess['id'])."'";
                        _update( 'm_amail', $array_m, $where );

                        $success_msg = "変更が完了いたしました。";
                    break;
                    case 'delete':
                        $array = array();
                        $array['admin_delete_date']  = "'".$_now_timestamp."'";
                        $where = "admin_id='"._as($this_sess['id'])."'";
                        _update( 'm_admin', $array, $where );
                        $success_msg = "削除しました。";
                    break;
                }

                if ( $this_sess['mode'] == 'insert' || $this_sess['mode'] == 'update' ){
                    $this_sess['syozoku_name'] = $_conf_syozoku[ $this_sess['admin_syozoku_id'] ];

                    // 2021.06.01 mod ---------- Before ----------
                    // if ( $this_sess['admin_login_pass'] != '' ){
                    //     $pass_word = md5($this_sess['admin_login_pass']);
                    // 2021.06.01 mod ---------- After ----------
                    if ( $this_sess['new_user_pass_make'] != "" ){
                        $new_pass = "_NEED_PASS_SET_";
                        $pass_word = "'"._as( md5( $new_pass ) )."'";
                    // 2021.06.01 mod ---------- End   ----------
                    } else {
                        $sql = "";
                        $sql .= " select admin_login_pass"."\n";
                        $sql .= " from m_admin"."\n";
                        $sql .= " where admin_delete_date is null"."\n";
                        $sql .= " and admin_id = '"._as($this_sess['id'])."'"."\n";
                        $admin_rec = _select($sql);
                        $pass_word = $admin_rec[0]['admin_login_pass'];
                    }

                    if ($this_sess['kyouryoku_kigyou_flg'] == 0 || _count($this_sess['kyouryoku_kigyou_ids']) == 0)
                    {
                        _delete('c_admin_companies', "admin_id = '" . _as($this_sess['id']) . "'");
                    }
                    else
                    {
                        $sql = "";
                        $sql .= " select GROUP_CONCAT(company_id) as ids ";
                        $sql .= " from c_admin_companies ";
                        $sql .= " where admin_id = '" . _as($this_sess['id']) . "'";
                        $kigyou = _select($sql);
                        $kigyou_ids = explode(',', $kigyou[0]['ids']);

                        foreach ($kigyou_ids as $saved_id)
                        {
                            if ($saved_id !== '' && ! in_array($saved_id, $this_sess['kyouryoku_kigyou_ids'], true))
                            {
                                _delete('c_admin_companies', "admin_id = '" . _as($this_sess['id']) . "' and company_id = " . _as($saved_id));
                            }
                        }

                        foreach ($this_sess['kyouryoku_kigyou_ids'] as $select_id)
                        {
                            if ( ! in_array($select_id, $kigyou_ids, true))
                            {
                                $array = array();
                                $array['admin_id'] = "'" . _as($this_sess['id']) . "'";
                                $array['company_id'] = _as($select_id);
                                _insert('c_admin_companies', $array);
                            }
                        }
                    }

                    // まだ終了していないイベント
                    $sql  = "";
                    $sql .= " select event_id"."\n";
                    $sql .= " from m_event"."\n";
                    $sql .= " where event_delete_date is null"."\n";
                    $sql .= "  and event_raikainri_ymd_ed > '".date("Y/m/d",strtotime(date("Y/m/d")."-1month"))."'"."\n";
                    $sql .= " order by event_id"."\n";
                    $active_event_recs = _select( $sql );
                    for ($loop=0; $loop < _count($active_event_recs); $loop++) {

                        $array = array();
                        $array_n = array();
                        $array_m = array();

                        // 来場者情報の存在チェック
                        $sql = "";
                        $sql .= " select user_id, user_pass"."\n";
                        $sql .= " from v_user"."\n";
                        $sql .= " where user_delete_date is null"."\n";
                        $sql .= "  and user_event_id = '"._as($active_event_recs[ $loop ]['event_id'])."'"."\n";
                        $sql .= "  and user_login_id = '"._as($this_sess['admin_mail'])."'"."\n";
                        $chk_rec = _select( $sql );
                        if ( $chk_rec[0]['user_id'] == '' ){
                            // insert
                            $max_recs = _select( "select coalesce(max(substring(user_id,2)),'0') as max_id from m_user");
                            $user_id  = sprintf("u%08d", ($max_recs[0]['max_id']+1));

                            $array['user_id']                  = "'"._as($user_id)."'"; //ユーザID（u000000001）',
                            $array['user_event_id']            = "'"._as($active_event_recs[ $loop ]['event_id'])."'"; //イベントID（e0001）',
                            $array['user_vip_flg']             = 0; //VIPフラグ（1:VIP）',
                            $array['user_big_cate']            = 7; //大分類 (AC社員)',
                            $array['user_mid_cate']            = _e2n($this_sess['admin_mid_cate']); //中分類 (来場者)その他',
                            $array['user_yakusyoku']           = "'"._as($this_sess['admin_yakusyoku'])."'"; //役職
                            $array['user_kigyou_name']         = "'"._as('株式会社日本アクセス')."'"; //企業名',
                            $array['user_kigyou_name_kana']    = "'"._as('カブシキガイシャニッポンアクセス')."'"; //企業名カナ',
                            $array['user_busyo']               = "'"._as($this_sess['syozoku_name'])."'"; //部署',
                            $array['user_pass']                = "'"._as($pass_word )."'"; //ログインパスワード(暗号化)',
                            $array['user_admin_id']            = "'"._as($this_sess['admin_id'])."'"; //担当者ID（a0000001）',
                            $array['user_web']                 = 1; //WEB招待（1:WEB招待者）',
                            $array['user_mail_send_kbn']       = 1; //'PASS設定URLメール送信区分(0:未送信、1:送信済み、2:送信エラー)',
                            $array['user_syounin_flg']         = 1; //'WEB招待の承認フラグ(0:未承認、1:承認済み)',
                            $array['user_company_id']          = _e2n($this_sess['admin_company_id']); // 企業ID
                            $array['user_insert_date']         = "'".$_now_timestamp."'"; //作成日時
                            $array['user_update_date']         = "'".$_now_timestamp."'"; //更新日時',
                            _insert( 'm_user', $array);

                            $array_n['un_user_id']             = "'"._as($user_id)."'"; //ユーザID（u000000001）',
                            $array_n['un_user_name']           = "'"._as($this_sess['admin_name'])."'"; //'氏名',
                            _insert( 'm_uname', $array_n);

                            $array_m['um_user_id']             = "'"._as($user_id)."'"; //ユーザID（u000000001）',
                            $array_m['um_user_mail']           = "'"._as($this_sess['admin_mail'])."'"; //メールアドレス',
                            $array_m['um_user_login_id']       = "'"._as($this_sess['admin_mail'])."'"; // ログインid',
                            _insert( 'm_umail', $array_m);
                        } else {
                            // update
                            $where = "user_id = '"._as($chk_rec[0]['user_id'])."'";

                            $array['user_event_id']            = "'"._as($active_event_recs[ $loop ]['event_id'])."'"; //イベントID（e0001）',
                            $array['user_vip_flg']             = 0; //VIPフラグ（1:VIP）',
                            $array['user_big_cate']            = 7; //大分類 (AC社員)',
                            $array['user_mid_cate']            = _e2n($this_sess['admin_mid_cate']); //中分類 (来場者)その他',
                            $array['user_yakusyoku']           = "'"._as($this_sess['admin_yakusyoku'])."'"; //役職
                            $array['user_kigyou_name']         = "'"._as('株式会社日本アクセス')."'"; //企業名',
                            $array['user_kigyou_name_kana']    = "'"._as('カブシキガイシャニッポンアクセス')."'"; //企業名カナ',
                            $array['user_busyo']               = "'"._as($this_sess['syozoku_name'])."'"; //部署',
                            $array['user_pass']                = "'"._as($pass_word )."'"; //ログインパスワード(暗号化)',
                            $array['user_admin_id']            = "'"._as($this_sess['admin_id'])."'"; //担当者ID（a0000001）',
                            $array['user_web']                 = 1; //WEB招待（1:WEB招待者）',
                            $array['user_mail_send_kbn']       = 1; //'PASS設定URLメール送信区分(0:未送信、1:送信済み、2:送信エラー)',
                            $array['user_company_id']          = _e2n($this_sess['admin_company_id']); // 企業ID
                            $array['user_update_date']         = "'".$_now_timestamp."'"; //更新日時',
                            _update( 'm_user', $array, $where);

                            $where = "un_user_id = '"._as($chk_rec[0]['user_id'])."'";
                            $array_n['un_user_name']           = "'"._as($this_sess['admin_name'])."'"; //'氏名',
                            _update( 'm_uname', $array_n, $where);
                        }
                    }
                }

                //**************************************************
                // メール通知
                //**************************************************
                // ログイン権限ありでメール通知「する」なら、マイページURLの通知
                if ( $_request['mail_notice'] == 1 && $this_sess['mode']!="delete" && $this_sess['admin_login_kengen']=="1"){

                    // smarty set
                    $msm = new UserBlade();
                    $msm->assign('_SYSTEM_ROOT_URLS',_SYSTEM_ROOT_URLS);

                    $pass_change_url = _SYSTEM_ROOT_URLS."/admin/"."?page=admin_pass_set&setpw="._urlCodeEncode($this_sess['id']."#_NEED_PASS_SET_");

                    $data_rec = array();
                    $data_rec['tantou_syozoku']    = $this_sess['syozoku_name'];
                    $data_rec['tantou_name']       = $this_sess['admin_name'];
                    $data_rec['tantou_mail']       = $this_sess['admin_mail'];
                    $data_rec['pass_set_url']      = $pass_change_url;
                    $data_rec['admin_url']         = _SYSTEM_ROOT_URLS."/admin/";
                    _setAssign($msm,$data_rec);

                    // template set
                    $mail_tpl = "pass_set_annai_admin.tpl";

                    $ret = _bladeFetchFromMailTplDB( $msm, $mail_tpl );
                    $title = $ret['subject'];
                    $body = $ret['body'];

                    $attach = array();
                    _accessSendMail( _SYSTEM_INFO_MAIL, _SYSTEM_INFO_MAIL_NAME, $this_sess['admin_mail'], $this_sess['admin_name']." 様", $title, $body,$attach );

                }

                _query($conn,'commit');

                $w_id = $this_sess['id'];
                $w_mode = $this_sess['mode'];
                $_SESSION[_PROJECT_NAME][$page] = array();
                unset( $_SESSION[_PROJECT_NAME][$page] );
                unset( $this_sess );
                $this_sess = array();


                if($w_mode != 'delete'){
                    $_request['exec'] = "";
                    $_request['id'] = $w_id;
                }else{
                    _query( $conn, "commit" );
                    header('Location: index.php?page=admin_list&sess_no_init=1');//OK1
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
            $sql .= " from v_admin ";
            $sql .= " where ";
            $sql .= "     admin_delete_date is null";
            $sql .= "     and admin_id ='"._as($_request['id'])."'";
            $main_rec = _select($sql);
            $this_sess = $main_rec[0];
            $this_sess['id'] = $main_rec[0]['admin_id'];
            $this_sess['mode'] = "update";

            $sql = "";
            $sql .= " select c.company_id, c.company_name ";
            $sql .= " from c_admin_companies cac ";
            $sql .= " join m_company c on cac.company_id = c.company_id ";
            $sql .= " where cac.admin_id = '" . _as($_request['id']) . "'";
            $kyouryoku_kigyou = _select($sql);
            $this_sess['kyouryoku_kigyou'] = $kyouryoku_kigyou;

            $this_sess['NoDisplay'] = "";
            $new_pass = "_NEED_PASS_SET_" ;
            if ( $main_rec[0]['admin_login_pass'] == md5( $new_pass ) ){
                $this_sess['pass_change_url'] = _SYSTEM_ROOT_URLS."/admin/"."?page=admin_pass_set&setpw="._urlCodeEncode($this_sess['id']."#_NEED_PASS_SET_");

            } else {
                $this_sess['NoDisplay'] = "display:none;";
            }

        }else{
            // 新規登録
            $this_sess['mode'] = "insert";
            $this_sess['admin_user_kengen']            = 0;
            $this_sess['admin_master_kengen']          = 1;
            $this_sess['admin_syuukei_etsuran_kengen'] = 0;
            $this_sess['admin_login_kengen']           = 1;
            $this_sess['mail_notice']                  = 1;

        }

        $this_sess['token'] = $token;
    }

    _setAssign($blade,$this_sess);

    $contents_title = "担当者管理 詳細";
    $active_menu = "admin_list";
    $contents_tpl = "admin_edit";
