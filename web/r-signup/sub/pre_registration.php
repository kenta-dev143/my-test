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

    $_conf_rsignup_big_cate1 = array();
    $_conf_rsignup_big_cate1[5] = "出展社";
    $blade->assign('_conf_rsignup_big_cate1',$_conf_rsignup_big_cate1);

    $blade->assign('_conf_legal_personality', $_conf_legal_personality);

    $_conf_legal_personality_position = array();
    $_conf_legal_personality_position[1] = '前';
    $_conf_legal_personality_position[2] = '後';
    $blade->assign('_conf_legal_personality_position', $_conf_legal_personality_position);

    if ( $event_rec['event_area_shikibetsu_id'] == 'W' ){
        $manual_pdf_name = "touroku_manual_west.pdf";
    } elseif ( $event_rec['event_area_shikibetsu_id'] == 'E' ){
        $manual_pdf_name = "touroku_manual_east.pdf";
    } elseif ( $event_rec['event_area_shikibetsu_id'] == 'T' ){
        $manual_pdf_name = "touroku_manual_touhoku.pdf";
    }

    $blade->assign('manual_pdf_name',$manual_pdf_name);

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

            // メールアドレスで全角の場合、半角に置換の対象キー名
            $keys = [
                'user_login_id',
                'user_login_id_chk',
                'admin_mail',
                'admin_mail_chk',
            ];

            // メールアドレスで全角の場合、半角に置換
            foreach ($keys as $key => $value) {
                if (!isset($_request[$value])) continue;
                $_request[$value] = mb_convert_kana($_request[$value], 'a');
            }

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
                $array['rsup_event_id']      = "'"._as($event_rec['event_id'])."'";
                $array['rsup_user_mail']     = "'"._as($this_sess['user_mail'])."'";
                $array['rsup_user_login_id'] = "'"._as($this_sess['user_login_id'])."'"; //2021/04/01 add
                $array['rsup_admin_mail']    = "'"._as($this_sess['admin_mail'])."'";
                $array['rsup_agent_mail']    = "'"._as($this_sess['agent_mail'])."'"; // 代理登録者メールアドレス
                $array['rsup_insert_date']   = "'".$_now_timestamp."'"; //更新日時',
                $array['rsup_update_date']   = "'".$_now_timestamp."'"; //更新日時',
                _insert('t_rsignup',$array);

                //INSERTしたID取得
                $recs = _select("SELECT LAST_INSERT_ID() as rsup_id");
                $rsup_id = $recs[0]['rsup_id'];

                //IDとメアドを暗号化しURL作成
                $str = $rsup_id . ";" . $event_rec['event_id'] . ";" . $this_sess['user_login_id'];
                $ango = _urlCodeEncode($str);
                $hontouroku_url = _SYSTEM_ROOT_URLS."/r-signup/".$event_rec['event_url_key']."/?exec=regist&iu=".$ango;

                // $ex_login_dir = $event_rec['event_exhibition_url_key']."/";
                // $ex_login_url = _SYSTEM_ROOT_URLS."/exhibition/".$ex_login_dir;

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
                // $data_rec['exhibition_url'] = $ex_login_url;
                $data_rec['tantou_name']    = $admin_recs[0]['admin_name'];
                $data_rec['tantou_mail']    = $admin_recs[0]['admin_mail'];
                _setAssign($msm,$data_rec);
                // 2021.06.04 mod ------------- END     -------------


                // template set
                $mail_tpl = "r_signup_kari_touroku.tpl";

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
                $p_rsup_id = $wArr[0];
                $p_event_id = $wArr[1];
                $p_user_login_id = $wArr[2]; //2021/04/01 mod

                if($p_event_id != $event_rec['event_id']){
                    $err_msg[] = "このURLは無効なURLです。";
                    $url_mukou = 1;
                }else{
                    $rsup_recs = _select("select * from t_rsignup where rsup_delete_date is null and rsup_id="._as($p_rsup_id)." and rsup_user_login_id='".$p_user_login_id."' and rsup_event_id='".$p_event_id."'");
                    if(_count($rsup_recs)==0){
                        $err_msg[] = "無効なURLです。";
                        $url_mukou = 1;
                    }else{
                        $this_sess = $rsup_recs[0];
                        $syoutai = _select(" select * from v_syoutai where syoutai_login_id = '" . $p_user_login_id . "' and syoutai_delete_date is null");
                        if ( ! empty($syoutai))
                        {
                          $this_sess['syoutai_id'] = $syoutai[0]['syoutai_id'];
                          $syoutai_data = [
                            'user_big_cate' => in_array($syoutai[0]['syoutai_big_cate'], array_keys($_conf_rsignup_big_cate1)) ? $syoutai[0]['syoutai_big_cate'] : '',
                            'user_name' => $syoutai[0]['syoutai_name'],
                            'user_name_kana' => $syoutai[0]['syoutai_name_kana'],
                            'user_kigyou_name' => $syoutai[0]['syoutai_company_name'],
                            'user_kigyou_name_kana' => $syoutai[0]['syoutai_company_name_kana'],
                            'user_busyo' => $syoutai[0]['syoutai_busyo'],
                            'user_yakusyoku' => $syoutai[0]['syoutai_yakusyoku'],
                          ];
                          if ($syoutai[0]['syoutai_company_id'] == '1')
                          {
                            $syoutai_data['user_kigyou_name'] = $syoutai[0]['syoutai_kigyou_name'];
                            $syoutai_data['user_kigyou_name_kana'] = $syoutai[0]['syoutai_kigyou_name_kana'];
                          }
                          $this_sess = _array_merge( $this_sess, $syoutai_data );
                        }
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
            if ( $this_sess['rsup_id'] == '' ){
                $err_msg[] = "セッションが切断されました。メール記載のURLから再度アクセスしてください。";
                $url_mukou = 1;
                $blade->assign('url_mukou',$url_mukou);

            } else {
                $stringNormalize = function($s) use ($_conf_legal_personality){
                    // 入力された会社名、会社名（カナ）から法人格を削除
                    $tmp_conf_legal_personality = $_conf_legal_personality;
                    unset($tmp_conf_legal_personality[99]);
                    $s = str_replace($tmp_conf_legal_personality, '', $s);

                    // --------------------------------------------
                    // 入力された会社名、会社名（カナ）から法人各略を削除
                    // --------------------------------------------
                    // 法人各略一覧を管理しているCSVのパス
                    $csv_path = _SYSTEM_ROOT_DIR . '/lib/csv/delete_company_abbreviation.csv';
                    $csv = new SplFileObject($csv_path);
                    $csv->setFlags(
                        SplFileObject::READ_CSV |    // CSV 列として行を読み込む
                        SplFileObject::READ_AHEAD |  // 先読み/巻き戻しで読み出す。
                        SplFileObject::SKIP_EMPTY |  // 空行は読み飛ばす
                        SplFileObject::DROP_NEW_LINE // 空行は読み飛ばす
                    );

                    foreach ($csv as $line => $row) {
                        if ($line == 0) continue;
                        $s = str_replace($row[1], '', $s);
                    }

                    //先頭スペース(全角半角共)は全て削除
                    //最終スペース(全角半角共)は全て削除
                    $s = preg_replace('/\A[\p{Cc}\p{Cf}\p{Z}]++|[\p{Cc}\p{Cf}\p{Z}]++\z/u', '', $s);

                    //半角カナは全角カナに置換 (半角中黒を全角中黒に置換)
                    //全角英数は半角英数に置換
                    //バ、パなどの濁点、半濁点が別文字の場合は、１文字に置換
                    //全角記号は半角記号に置換
                    $s = mb_convert_kana($s, 'KVa');

                    //文字列中の連続スペース(全角、半角共)は半角スペース１つに
                    $s = preg_replace('/　/', ' ', $s);
                    $s = preg_replace('/\s+/', ' ', $s); // 連続するスペースをまとめる

                    return $s;
                };

                // 招待者じゃない場合は入力された会社名、会社名（カナ）から法人格を削除
                if ( ! isset($this_sess['syoutai_id']) || empty($this_sess['syoutai_id'])) {
                    $_request['user_kigyou_name'] = $stringNormalize($_request['user_kigyou_name']);
                    $_request['user_kigyou_name_kana'] = $stringNormalize($_request['user_kigyou_name_kana']);
                }

                $chks = array(
                                "user_big_cate,来場者区分"                 => "need",
                                "user_name,氏名"                 => "need",
                                "user_name_kana,氏名カナ"                 => "zenkana",
                                "user_kigyou_name,会社名"                 => "need",
                                "user_kigyou_name_kana,会社名カナ"                 => "need,zenkana",
                                "user_busyo,部署"                 => "need",
                                //"user_yakusyoku,役職"                 => "need",
                              );
                if ( ! isset($this_sess['syoutai_id']) || empty($this_sess['syoutai_id']))
                {
                  $chks["user_company_legal_personality,法人格"] = "need";
                }

                $err_msg = _check( $chks, $_request );

                if( _count($_request['raijyou_yotei_time'])==0 ){
                    $err_msg[] = "来場予定日時が選択されていません。";
                    $_request['user_raijyou_yotei_time'] = "";
                }else{
                    $yotei = "";
                    for ($i=0; $i < _count($_request['raijyou_yotei_time']); $i++) {
                        if($yotei!="") $yotei .= "#";
                        $yotei .= $_request['raijyou_yotei_time'][$i];
                    }
                    $_request['user_raijyou_yotei_time'] = $yotei;
                }

                $kigyou_name = $_request['user_kigyou_name'];
                if (( ! isset($this_sess['syoutai_id']) || empty($this_sess['syoutai_id'])) && $_request['user_company_legal_personality'] != 99)
                {
                  // 法人格位置
                  if ($_request['user_company_legal_personality_position'] === "1")
                  {
                    // 前
                    $kigyou_name = $_conf_legal_personality[$_request['user_company_legal_personality']] . $kigyou_name;
                  }
                  else
                  {
                    // 後
                    $kigyou_name = $kigyou_name . $_conf_legal_personality[$_request['user_company_legal_personality']];
                  }
                }
                $this_sess['disp_kigyou_name'] = $kigyou_name;

                //**** POST値をセッションにマージ ****
                $this_sess = _array_merge( $this_sess, $_request );
            }
            // 2021.06.30 add ------------ end   ----------------

            if( _count( $err_msg ) == 0 ){
                $this_sess['disp_user_big_cate'] = $_conf_rsignup_big_cate1[$this_sess['user_big_cate']];
                $this_sess['disp_user_raijyou_yotei_time'] = str_replace("#","<br>", $this_sess['user_raijyou_yotei_time'] );
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
                $sql .= "   and admin_mail = '"._as($this_sess['rsup_admin_mail'])."'"."\n";
                $admin_recs = _select($sql);

                $array = array();
                $array['user_id']                  = "'"._as($this_sess['id'])."'";
                $array['user_event_id']            = "'"._as($this_sess['rsup_event_id'])."'"; //イベントID（e0001）',
                $array['user_admin_id']            = "'"._as($admin_recs[0]['admin_id'])."'"; //担当者ID（a0000001）',
                $array['user_vip_flg']             = "0"; //VIPフラグ（1:VIP）',

                //大分類 5:メーカー(出展)、6:運営サポート・施工業
                $array['user_big_cate']            = ""._e2n($this_sess['user_big_cate'])."";

                //中分類
                if($this_sess['user_big_cate']=="5"){
                    $array['user_mid_cate']            = "105"; //中分類(105:メーカー)
                }elseif($this_sess['user_big_cate']=="6"){
                    $array['user_mid_cate']            = "110"; //中分類(110:運営サポート・施工業)
                }

                $kigyou_name = $this_sess['user_kigyou_name'];
                if (( ! isset($this_sess['syoutai_id']) || empty($this_sess['syoutai_id'])) && $this_sess['user_company_legal_personality'] != 99)
                {
                  // 法人格位置
                  if ($this_sess['user_company_legal_personality_position'] === "1")
                  {
                    // 前
                    $kigyou_name = $_conf_legal_personality[$this_sess['user_company_legal_personality']] . $kigyou_name;
                  }
                  else
                  {
                    // 後
                    $kigyou_name = $kigyou_name . $_conf_legal_personality[$this_sess['user_company_legal_personality']];
                  }
                }
                $company_id = "1";

                $sql  = "";
                $sql .= " select * from m_company ";
                $sql .= " where company_name = '" . _as($kigyou_name) . "'";
                $company = _select($sql);

                if ( ! empty($company))
                {
                    $company_id = $company[0]['company_id'];
                }
                else
                {
                    $sql  = "";
                    $sql .= " select * from m_allocation_company ";
                    $sql .= " where ( allocation_company_name = '" . _as($kigyou_name) . "'";
                    $sql .= " or allocation_company_candidate_name = '" . _as($kigyou_name) . "' )";
                    $sql .= " and allocation_company_id is not null";
                    $allocation_company = _select(" select * from m_allocation_company where allocation_company_name = '" . _as($kigyou_name) . "' or allocation_company_candidate_name = '" . _as($kigyou_name) . "'");
                    if ( ! empty($allocation_company))
                    {
                      $company_id = $allocation_company[0]['allocation_company_id'];
                    }
                }

                $array['user_company_id']          = "'"._as($company_id)."'";
                $array['user_kigyou_name']         = "'"._as($kigyou_name)."'"; //企業名',
                $array['user_kigyou_name_kana']    = "'"._as($this_sess['user_kigyou_name_kana'])."'"; //企業名カナ',
                $array['user_busyo']               = "'"._as($this_sess['user_busyo'])."'"; //部署',
                $array['user_yakusyoku']           = "'"._as($this_sess['user_yakusyoku'])."'"; //役職',
                $array['user_pass']                = "'"._as(md5($this_sess['user_pass']))."'"; //パスワード',
                $array['user_raijyou_yotei_time']  = "'".$this_sess['user_raijyou_yotei_time']."'"; //来場予定日時（yyyy/mm/dd HH:ii 形式）',
                $array['user_web']                 = "0"; //WEB招待（0:WEB招待なし）',
                $array['user_tag']                 = "''"; //ユーザタグ, 2020.12.19 add
                $array['user_update_date']         = "'".$_now_timestamp."'"; //更新日時',
                $array['user_biko']                = "''"; //備考',
                $array['user_agent_mail']          = "'"._as($this_sess['rsup_agent_mail'])."'"; // 代理登録者メールアドレス
                $array['user_mail_send_kbn']       = "0"; //PASS設定URLメール送信区分(0:未送信、1:送信済み、2:送信エラー)
                $array['user_syounin_flg']         = "0"; //WEB招待の承認フラグ(0:未承認、1:承認済み)
                $array['user_insert_date']         = "'".$_now_timestamp."'";

                if ( ! isset($this_sess['syoutai_id']) || empty($this_sess['syoutai_id']))
                {
                  // 招待者マスタに追加
                  $max_recs = _select( "select coalesce(max(substring(syoutai_id,2)),'0') as max_id from m_syoutai");
                  $this_sess['syoutai_id'] = sprintf("s%08d", $max_recs[0]['max_id'] + 1 );

                  $s_array = array();
                  $s_array_n = array();
                  $s_array_m = array();

                  $s_array['syoutai_id']                  = "'" . _as($this_sess['syoutai_id']) . "'";
                  $s_array['syoutai_vip_flg']             = $array['user_vip_flg']; //VIPフラグ（1:VIP）',
                  $s_array['syoutai_big_cate']            = $array['user_big_cate'];//大分類',
                  $s_array['syoutai_mid_cate']            = $array['user_mid_cate']; //中分類',
                  $s_array['syoutai_company_id']          = $array['user_company_id'];//企業ID
                  $s_array['syoutai_kigyou_name']         = $array['user_kigyou_name']; //企業名',
                  $s_array['syoutai_kigyou_name_kana']    = $array['user_kigyou_name_kana']; //企業名カナ',
                  $s_array['syoutai_busyo']               = $array['user_busyo']; //部署',
                  $s_array['syoutai_yakusyoku']           = $array['user_yakusyoku']; //役職',
                  $s_array['syoutai_update_date']         = "'".$_now_timestamp."'"; //更新日時',

                  $s_array_n['sn_syoutai_id']             = $s_array['syoutai_id'];
                  $s_array_n['sn_syoutai_name']           = "'"._as($this_sess['user_name'])."'"; //'氏名',
                  $s_array_n['sn_syoutai_name_kana']      = "'"._as($this_sess['user_name_kana'])."'"; //氏名カナ',

                  $s_array_m['sm_syoutai_id']             = $s_array['syoutai_id'];
                  $s_array_m['sm_syoutai_mail']           = "'"._as($this_sess['rsup_user_mail'])."'";      // メールアドレス',
                  $s_array_m['sm_syoutai_login_id']       = "'"._as($this_sess['rsup_user_login_id'])."'"; // ログインid',

                  $s_array['syoutai_insert_date']     = "'".$_now_timestamp."'";
                  $s_array['syoutai_last_upd_naiyou'] = "'"._as( 'サインアップ新規登録' )."'"; //最終更新内容',
                  _insert( 'm_syoutai', $s_array);
                  _insert( 'm_sname', $s_array_n);
                  _insert( 'm_smail', $s_array_m);
                }

                $array['user_syoutai_id'] = "'" . _as($this_sess['syoutai_id']) . "'";

                _insert( 'm_user', $array);

                $array_n = array();
                $array_n['un_user_id']                  = "'"._as($this_sess['id'])."'";
                $array_n['un_user_name']                = "'"._as($this_sess['user_name'])."'"; //'氏名',
                $array_n['un_user_name_kana']                = "'"._as($this_sess['user_name_kana'])."'"; //氏名カナ
                _insert( 'm_uname', $array_n);

                $array_m = array();
                $array_m['um_user_id']          = "'"._as($this_sess['id'])."'";
                $array_m['um_user_mail']                = "'"._as($this_sess['rsup_user_mail'])."'";    // メールアドレス',
                $array_m['um_user_login_id']            = "'"._as($this_sess['rsup_user_login_id'])."'"; // ログインid',
                _insert( 'm_umail', $array_m);

                $updArray = array();
                $updArray['rsup_delete_date'] = "'".$_now_timestamp."'";
                $where = "rsup_id="._as($this_sess['rsup_id']);
                _update("t_rsignup",$updArray,$where);


                // $ex_login_dir = $event_rec['event_exhibition_url_key']."/";
                // $ex_login_url = _SYSTEM_ROOT_URLS."/exhibition/".$ex_login_dir;

                $company = _select(" select * from m_company where company_id = " . $company_id);

                // smarty set
                $msm = new UserBlade();
                $data_rec = array();
                $data_rec['event_name']         = $event_rec['event_name'];
                $data_rec['kigyou_name']        = $company_id == "1" ? $array['user_kigyou_name'] : $company[0]['company_name'];
                $data_rec['name']               = $this_sess['user_name'];
                $data_rec['login_id']           = $this_sess['rsup_user_login_id'];
                $data_rec['raijyousya_kbn']     = $_conf_rsignup_big_cate1[$this_sess['user_big_cate']];
                $data_rec['tantou_name']        = $admin_recs[0]['admin_name'];
                $data_rec['tantou_mail']        = $admin_recs[0]['admin_mail'];
                $data_rec['admin_pass_set_url'] = _SYSTEM_ROOT_URLS."/admin/?page=pass_reissue";
                $data_rec['admin_url']          = _SYSTEM_ROOT_URLS."/admin/";
                _setAssign($msm,$data_rec);


                // template set
                $mail_tpl = "r_signup_hon_touroku.tpl";

                $ret = _bladeFetchFromMailTplDB( $msm, $mail_tpl );

                $title = $ret['subject'];
                $body = $ret['body'];

                $attach = array();
                _accessSendMail( _SYSTEM_INFO_MAIL, _SYSTEM_INFO_MAIL_NAME, $this_sess['rsup_user_mail'], $this_sess['user_name']."様", $title, $body,$attach );

                if ( ! empty($this_sess['rsup_agent_mail'])) {
                    _accessSendMail( _SYSTEM_INFO_MAIL, _SYSTEM_INFO_MAIL_NAME, $this_sess['rsup_agent_mail'], $this_sess['user_name_sei']." ".$this_sess['user_name_mei']."様", $title, $body,$attach );
                }

                //担当者にも通知
                // template set
                $mail_tpl = "r_signup_syoutai_tsuuchi.tpl";

                $ret = _bladeFetchFromMailTplDB( $msm, $mail_tpl );

                $title = $ret['subject'];
                $body = $ret['body'];

                $attach = array();
                //2021/11 仕様削除
//                _accessSendMail( _SYSTEM_INFO_MAIL, _SYSTEM_INFO_MAIL_NAME, $admin_recs[0]['admin_mail'], $admin_recs[0]['admin_name']." 様", $title, $body,$attach );
//
//                if($admin_recs[0]['admin_mail2']!=""){
//                    _accessSendMail( _SYSTEM_INFO_MAIL, _SYSTEM_INFO_MAIL_NAME, $admin_recs[0]['admin_mail2'], $admin_recs[0]['admin_name']." 様", $title, $body,$attach );
//                }

                _query($conn,'commit');

                $_SESSION[_PROJECT_NAME][$page] = array();
                unset( $_SESSION[_PROJECT_NAME][$page] );
                unset( $this_sess );
                $this_sess = array();



            }

            // $blade->assign('ex_login_url',$ex_login_url);

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

    //(招待者)来場予定日時
    $wArr = explode("#", $event_rec['event_raijyou_yotei_time']);
    $_conf_raijyou_yotei_time = array();
    for ($i=0; $i < _count($wArr); $i++) {
        $dtArr = explode(" ", $wArr[$i],2);
        $ymd = $dtArr[0];
        if($ymd!="2999/01/01"){
            $disp_ymd = date("n月j日",strtotime($dtArr[0]) )."（"._getYoubi($dtArr[0])."）";
            $hi = $dtArr[1];
            $_conf_raijyou_yotei_time[$ymd]['disp_ymd'] = $disp_ymd;

            $checked="";
            if($this_sess['user_raijyou_yotei_time']!="") {
                if( strpos($this_sess['user_raijyou_yotei_time'],$wArr[$i]) !== FALSE ){
                    $checked = "checked";
                }
            }
            $_conf_raijyou_yotei_time[$ymd]['his'][] = array('hi'=>$hi, 'checked'=>$checked);
        }
    }
    $blade->assign('_conf_raijyou_yotei_time',$_conf_raijyou_yotei_time);
