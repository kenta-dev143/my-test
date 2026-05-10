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
    $line_err = array();

    $_ac_mid_cate = array();
    foreach ($_conf_mid_cate2 as $key => $value) {
        if(substr($value,0,2)=="AC"){
            $_ac_mid_cate[$key] = $value;
        }
    }
    $blade->assign('_ac_mid_cate',$_ac_mid_cate);

    if($_request['exec']=="csv_upload"){
        setlocale(LC_ALL, 'ja_JP.UTF-8');
        if (is_uploaded_file($_FILES["csv_file"]["tmp_name"])) {

            set_time_limit(180); //3分起動
            ini_set('memory_limit',"1024M"); //メモリ拡大

            $extension  = '.'._get_extension($_FILES['csv_file']['name']);
            $temp_file  = _SYSTEM_ROOT_DIR . "/upfile/new_tmp/" . rand() . $extension;
            $w_fname    = date("YmdHis") . "_admin_ikkatsu_touroku.csv";
            // $temp_file2 = _SYSTEM_ROOT_DIR . "/upfile/new_tmp/" . $w_fname; // 2021.06.02 del
            $file_move  = move_uploaded_file($_FILES['csv_file']['tmp_name'], $temp_file );

            $buff = mb_convert_encoding(file_get_contents($temp_file), "UTF8", "SJIS-WIN");
            $buff = str_replace("\r\n", "\n", $buff);
            $buff = str_replace("\r", "\n", $buff);

            $fp = fopen($temp_file,"w");
            fwrite($fp, $buff);
            fclose($fp);

            // 2021.06.02 add -------- Start --------
            // まだ終了していないイベントの取得 (m_user作成用)
            $sql  = "";
            $sql .= " select event_id"."\n";
            $sql .= " from m_event"."\n";
            $sql .= " where event_delete_date is null"."\n";
            $sql .= "  and event_raikainri_ymd_ed > '".date("Y/m/d",strtotime(date("Y/m/d")."-1month"))."'"."\n";
            $sql .= " order by event_id"."\n";
            $active_event_recs = _select( $sql );
            // 2021.06.02 add -------- End    --------


            if ($file_move !== false) {
                $fp = fopen($temp_file, "r");
                // $fp2 = fopen($temp_file2,"w");
                if($fp !== false){

                    _query($conn, "begin");

                    // 最大IDを取得しておく
                    $max_recs = _select( "select coalesce(max(substring(admin_id,2)),'0') as max_id from m_admin");
                    $now_admin_id = $max_recs[0]['max_id'];

                    // 2021.06.02 del ----------- Start -----------
                    // $w_csv = "";
                    // $w_csv .= "" . "\""."担当者名"."\"";
                    // $w_csv .= "," . "\""."担当エリア"."\"";
                    // $w_csv .= "," . "\""."所属(支店・部署)"."\"";
                    // $w_csv .= "," . "\""."担当者メールアドレス（通知先メールアドレス１）"."\"";
                    // $w_csv .= "," . "\""."パスワード"."\"";
                    // $w_csv .= "\n";
                    // $w_csv = mb_convert_encoding($w_csv, "SJIS-WIN", "UTF8");
                    // fwrite($fp2, $w_csv);
                    // 2021.06.02 del ----------- End -----------

                    // マイページURLの通知 1:する
                    if ( $_request['mail_notice'] == 1 ){
                        $sql = "";
                        $sql .= "select * from m_mail_template";
                        $sql .= " where";
                        $sql .= " mailt_delete_date is null";
                        $sql .= " and mailt_key = 'pass_set_annai_admin'";
                        $tpl_recs = _select($sql);
                        if (_count($tpl_recs) == 0){
                            $err_msg[] = 'システムエラー：メールテンプレートの取得が出来ませんでした。';
                        } else {
                            $array = array();
                            $array['mailhd_mailt_name']      = "'"._as($tpl_recs[0]['mailt_name'])."'";
                            $array['mailhd_mailt_key']       = "'"._as($tpl_recs[0]['mailt_key'])."'";
                            $array['mailhd_subject']         = "'"._as($tpl_recs[0]['mailt_subject'])."'";
                            $array['mailhd_body']            = "'"._as($tpl_recs[0]['mailt_body'])."'";
                            if($_request['mail_timing']=="ato"){
                                $array['mailhd_yoyaku_ymdhi']    = "'2099/12/31 23:59'";
                            }else{
                                $array['mailhd_yoyaku_ymdhi']    = "'"._as(date("Y/m/d H:i"))."'";
                            }
                            $array['mailhd_status']          = "0";
                            $array['mailhd_test_send_flg']   = 0; //0:本番送信
                            $array['mailhd_insert_admin_id'] = "'"._as($_SESSION[_PROJECT_NAME]['admin_login']['admin_id'])."'";
                            $array['mailhd_insert_date']     = "'".$_now_timestamp."'";
                            $array['mailhd_update_date']     = "'".$_now_timestamp."'";
                            _insert('t_mail_head',$array);

                            $mailhd_id = $conn->insert_id; //insertされたAUTO_INCREMENTの値取得

                        }
                    }

                    $error_line_count = 0;
                    $insert_success   = 0;
                    $update_success   = 0;
                    $line = 0;
                    $t_mail_list_insert_cnt = 0;
                    while (($csv_row = fgetcsv($fp)) !== FALSE) {
                        $w_err = array();

                        if($error_line_count > 20) {
                            // 一定数のエラーを許容する場合はメッセージを変更し、ループ後のエラーチェックをする
                            $err_msg[] = 'エラー行が20件以上発生しましたので取り込み処理を中断しました。';
                            $err_msg = _array_merge($err_msg, $line_err);
                            break;
                        }
                        ++$line;

                        //ヘッダ行はスルー
                        if($line ==1){
                            continue;
                        }

                        $data_arr = array();
                        $data_arr['admin_name']                   = trim($csv_row[0]); // 担当者名
                        $data_arr['admin_mail']                   = trim($csv_row[1]); // メールアドレス(ログインID)
                        $data_arr['admin_syozoku_code']           = trim($csv_row[2]); // 所属支店・部署コード
                        $data_arr['admin_mid_date']           = trim($csv_row[3]); // 中分類
                        $data_arr['admin_yakusyoku']           = trim($csv_row[4]); // 役職
                        $data_arr['admin_user_kengen']            = trim($csv_row[5]); // 担当ユーザーのみ閲覧フラグ（0:全て閲覧可、1:支店部署に紐づくユーザーのみ閲覧可、2:自身に紐づくユーザーのみ閲覧可）
                        $data_arr['admin_master_kengen']          = trim($csv_row[6]); // マスター管理権限（0:なし、1:あり）
                        $data_arr['admin_syuukei_etsuran_kengen'] = trim($csv_row[7]); // 集計閲覧権限（0:全て閲覧可、1:エリアのリアルタイム人数のみ）
                        $data_arr['admin_login_kengen']           = trim($csv_row[8]); // 管理画面ログイン権限（0:不可、1:可）
                        $data_arr['admin_kyouryoku_kigyou_flg']   = trim($csv_row[9]); // 外部企業フラグ
                        $data_arr['admin_kyouryoku_kigyou_names'] = trim($csv_row[10]); // 外部企業名　複数の場合「//」区切り
                        unset($csv_row);

                        // CSV行データチェック開始
                        $chks = array(
                                        "admin_name,(${line}行目) 担当者名"                                           => "need",
                                        "admin_mail,(${line}行目) 担当者メールアドレス（通知先メールアドレス１）"     => "need,email",
                                        "admin_syozoku_code,(${line}行目) 所属(支店・部署)コード"                     => "need",
                                        "admin_user_kengen,(${line}行目) 来場者閲覧権限"                              => "need",
                                        "admin_master_kengen,(${line}行目) マスター管理権限"                          => "need",
                                        "admin_syuukei_etsuran_kengen,(${line}行目) 集計閲覧権限"                     => "need",
                                        "admin_login_kengen,(${line}行目) 管理画面ログイン権限"                       => "need",
                                      );

                        // $line_err = _check( $chks, $data_arr );
                        $w_err = _check( $chks, $data_arr );

                        $fnd_mid_key = "";

                        if(_count($w_err)==0){
                            if($data_arr['admin_user_kengen'] != 0 && $data_arr['admin_user_kengen'] != 1 && $data_arr['admin_user_kengen'] != 2){
                                $w_err[] = "(${line}行目) 来場者閲覧権限の値は 0 or 1 or 2 で指定してください。";
                            }

                            if($data_arr['admin_master_kengen'] != 0 && $data_arr['admin_master_kengen'] != 1){
                                $w_err[] = "(${line}行目) マスター管理権限の値は 0 or 1 で指定してください。";
                            }

                            if($data_arr['admin_syuukei_etsuran_kengen'] != 0 && $data_arr['admin_syuukei_etsuran_kengen'] != 1){
                                $w_err[] = "(${line}行目) 集計閲覧権限の値は 0 or 1 で指定してください。";
                            }

                            if($data_arr['admin_login_kengen'] != 0 && $data_arr['admin_login_kengen'] != 1){
                                $w_err[] = "(${line}行目) 管理画面ログイン権限の値は 0 or 1 で指定してください。";
                            }

                            if($data_arr['admin_mid_date']!=""){
                                $fnd_mid_key = array_search($data_arr['admin_mid_date'],$_ac_mid_cate);
                                if( $fnd_mid_key===FALSE ){
                                    $fnd_mid_key = "";
                                    $w_err[] = "(${line}行目) 中分類の名称は".implode(" or ",$_ac_mid_cate)."の値で指定してください";
                                }
                            }

                        }

                        if( _count($w_err) == 0 ){
                            $sql = "";
                            $sql .= " select *"."\n";
                            $sql .= " from m_syozoku"."\n";
                            $sql .= " left join m_tantou_area on (syozoku_tanarea_id = tanarea_id and tanarea_delete_date is null)"."\n";
                            $sql .= " where syozoku_delete_date is null"."\n";
                            $sql .= " and syozoku_code = '"._as($data_arr['admin_syozoku_code'])."'"."\n";
                            $syozoku_recs = _select( $sql );
                            if ( _count($syozoku_recs) == 0 ){
                                $w_err[] = "(${line}行目) 該当する 所属(支店・部署) がありません。";
                            }
                            $data_arr['admin_tanarea_name'] = $syozoku_recs[0]['tanarea_name'];

                            // 2021.05.01 del
                            // unset($tanarea_recs);
                            // if ( $data_arr['admin_tanarea_name'] != '' ){
                            //     $sql = "";
                            //     $sql .= " select tanarea_id"."\n";
                            //     $sql .= " from m_tantou_area"."\n";
                            //     $sql .= " where tanarea_name = '"._as($data_arr['admin_tanarea_name'])."'"."\n";
                            //     $sql .= "   and tanarea_delete_date is null"."\n";
                            //     $tanarea_recs = _select( $sql );
                            //     if ( _count($tanarea_recs) == 0 ){
                            //         $w_err[] = "(${line}行目) 該当する所属名がありません。";
                            //     }
                            // }
                        }

                        if( _count($w_err) == 0 ) {
                            if ($data_arr['admin_kyouryoku_kigyou_flg'] == "1") {
                                if ( ! isset($data_arr['admin_kyouryoku_kigyou_names']) || empty($data_arr['admin_kyouryoku_kigyou_names'])) {
                                    $w_err[] = "(${line}行目) 外部企業を選択してください。";
                                } else {
                                    $kyouryoku_kigyou_names = explode('//', $data_arr['admin_kyouryoku_kigyou_names']);

                                    $sql = "";
                                    $sql .= " select company_id" . "\n";
                                    $sql .= " from m_company"."\n";
                                    $sql .= " where "."\n";
                                    $sql .= "   company_delete_date is null ";
                                    $sql .= '   and company_name in ( "'. implode('","', $kyouryoku_kigyou_names) .'")';
                                    $company_recs = _select($sql);
                                    if ( _count($company_recs) !== _count($kyouryoku_kigyou_names)) {
                                        $w_err[] = "(${line}行目) 入力された外部企業が企業マスタに存在しないものがあります。";
                                    }
                                }
                            }
                        }

                        if( _count($w_err) > 0 ){
                            $line_err = _array_merge($line_err, $w_err);
                        }

                        if( _count($w_err) == 0 ) {
                            $sql = "";
                            // $sql .= " select admin_id"."\n";
                            $sql .= " select *"."\n";
                            $sql .= " from v_admin"."\n";
                            $sql .= " where admin_mail = '"._as($data_arr['admin_mail'])."'"."\n";
                            $sql .= " and admin_delete_date is null"."\n";
                            $chk_recs = _select( $sql );
                            // 2021.05.17 del
                            // if ( _count($chk_recs) > 0 ){
                            //     $line_err[] = "(${line}行目) 担当者メールアドレス１は既に登録があります。";
                            // }
                        }

                        if( _count($line_err) > 0 ) {
                            $error_line_count++;
                            continue;
                        }else{

                            $array = array();

                            $syozoku_id = "";
                            $tanarea_id = "";
                            $syozoku_id = $syozoku_recs[0]['syozoku_id'];
                            // if ($tanarea_recs[0]['tanarea_id'] != '') $tanarea_id = $tanarea_recs[0]['tanarea_id'];
                            if ( $syozoku_recs[0]['tanarea_id'] != '') $tanarea_id = $syozoku_recs[0]['tanarea_id'];

                            if ( $chk_recs[0]['admin_id'] == '' ){
                                $now_admin_id = $now_admin_id + 1;
                                $admin_id   = sprintf("a%07d", $now_admin_id );
                                // $admin_pass = _makePassword(); 2021.06.02 mod
                                $admin_pass = "_NEED_PASS_SET_";

                                //**************************************************
                                // m_admin 作成
                                //**************************************************
                                $array = array();
                                $array['admin_id']                     = "'"._as($admin_id)."'";
                                $array['admin_login_pass']             = "'"._as( md5($admin_pass) )."'";
                                // $array['admin_init_pass']              = "'"._as(_urlCodeEncode($admin_pass) )."'"; // 2021.06.02 del
                                if ( $tanarea_id != '' ){
                                    $array['admin_tanarea_id']         = "'"._as( $tanarea_id )."'";
                                }
                                $array['admin_syozoku_id']             = "'"._as( $syozoku_id )."'";
                                $array['admin_mid_cate']             = _e2n( $fnd_mid_key );
                                $array['admin_yakusyoku']             = "'"._as( $data_arr['admin_yakusyoku'] )."'";

                                $array['admin_user_kengen']            = ""._e2z($data_arr['admin_user_kengen'])."";
                                $array['admin_master_kengen']          = ""._e2z($data_arr['admin_master_kengen'])."";
                                $array['admin_syuukei_etsuran_kengen'] = ""._e2z($data_arr['admin_syuukei_etsuran_kengen'])."";
                                $array['admin_login_kengen']           = ""._e2z($data_arr['admin_login_kengen'])."";
                                $array['admin_kyouryoku_kigyou_flg']   = ""._e2z($data_arr['admin_kyouryoku_kigyou_flg'])."";

                                $array['admin_insert_date']            = "'".$_now_timestamp."'";
                                $array['admin_update_date']            = "'".$_now_timestamp."'";
                                _insert('m_admin', $array);

                                $array_n = array();
                                $array_n['an_admin_id']                = "'"._as($admin_id)."'";
                                $array_n['an_admin_name']              = "'"._as($data_arr['admin_name'])."'";
                                _insert('m_aname', $array_n);

                                $array_m = array();
                                $array_m['am_admin_id']                = "'"._as($admin_id)."'";
                                $array_m['am_admin_mail']              = "'"._as($data_arr['admin_mail'])."'";
                                $array_m['am_admin_mail2']             = "''";
                                _insert('m_amail', $array_m);

                                $insert_success++;

                                //**************************************************
                                // メール通知
                                //**************************************************
                                // マイページURLの通知 1:する
                                if ( $_request['mail_notice'] == 1 ){
                                    if($data_arr['admin_login_kengen']=="1"){ //ログイン権限あり人だけメール送信
                                        $array = array();
                                        $array['maills_mailhd_id']    = "'"._as($mailhd_id)."'";
                                        $array['maills_user_id']      = "'"._as($admin_id)."'";
                                        $array['maills_mail_address'] = "'"._as($data_arr['admin_mail'])."'";
                                        // 未使用 $array['maills_event_id']     = "''";
                                        $array['maills_insert_date']  = "'".$_now_timestamp."'";
                                        $array['maills_update_date']  = "'".$_now_timestamp."'";
                                        _insert('t_mail_list', $array);

                                        $t_mail_list_insert_cnt++;
                                    }
                                }

                                //**************************************************
                                // m_user 作成
                                //**************************************************
                                for ($loop=0; $loop < _count($active_event_recs); $loop++) {
                                    $array = array();
                                    $array_n = array();
                                    $array_m = array();
                                    $max_recs = _select( "select coalesce(max(substring(user_id,2)),'0') as max_id from m_user");
                                    $user_id  = sprintf("u%08d", ($max_recs[0]['max_id']+1));

                                    $array['user_id']                  = "'"._as($user_id)."'"; //ユーザID（u000000001）',
                                    $array['user_event_id']            = "'"._as($active_event_recs[ $loop ]['event_id'])."'"; //イベントID（e0001）',
                                    $array['user_vip_flg']             = 0; //VIPフラグ（1:VIP）',
                                    $array['user_big_cate']            = 7; //大分類 (AC社員)',

                                    //$array['user_mid_cate']            = 199; //中分類 (来場者)その他',
                                    $array['user_mid_cate']             = _e2n( $fnd_mid_key );

                                    $array['user_yakusyoku']             = "'"._as( $data_arr['admin_yakusyoku'] )."'";

                                    $array['user_kigyou_name']         = "'"._as('株式会社日本アクセス')."'"; //企業名',
                                    $array['user_kigyou_name_kana']    = "'"._as('カブシキガイシャニッポンアクセス')."'"; //企業名カナ',
                                    $array['user_busyo']               = "'"._as($syozoku_recs[0]['syozoku_name'])."'"; //部署',
                                    $array['user_pass']                = "'"._as( md5($admin_pass) )."'"; //ログインパスワード(暗号化)',
                                    $array['user_admin_id']            = "'"._as($admin_id)."'"; //担当者ID（a0000001）',
                                    $array['user_web']                 = 1; //WEB招待（1:WEB招待者）',
                                    $array['user_mail_send_kbn']       = 1; //'PASS設定URLメール送信区分(0:未送信、1:送信済み、2:送信エラー)',
                                    $array['user_syounin_flg']         = 1; //'WEB招待の承認フラグ(0:未承認、1:承認済み)',
                                    $array['user_insert_date']         = "'".$_now_timestamp."'"; //作成日時
                                    $array['user_update_date']         = "'".$_now_timestamp."'"; //更新日時',
                                    _insert( 'm_user', $array);

                                    $array_n['un_user_id']             = "'"._as($user_id)."'"; //ユーザID（u000000001）',
                                    $array_n['un_user_name']           = "'"._as($data_arr['admin_name'])."'"; //'氏名',
                                    _insert( 'm_uname', $array_n);

                                    $array_m['um_user_id']             = "'"._as($user_id)."'"; //ユーザID（u000000001）',
                                    $array_m['um_user_mail']           = "'"._as($data_arr['admin_mail'])."'"; //メールアドレス',
                                    $array_m['um_user_login_id']       = "'"._as($data_arr['admin_mail'])."'"; // ログインid',
                                    _insert( 'm_umail', $array_m);

                                } // for 終端

                            } else {
                                $admin_id   = $chk_recs[0]['admin_id'];
                                $admin_pass_md5 = $chk_recs[0]['admin_login_pass'];

                                // -------------------------
                                $array = array();
                                if ( $tanarea_id != '' ){
                                    $array['admin_tanarea_id']         = "'"._as($tanarea_id)."'";
                                }
                                $array['admin_syozoku_id']             = "'"._as($syozoku_id)."'";
                                $array['admin_mid_cate']             = _e2n( $fnd_mid_key );
                                $array['admin_yakusyoku']             = "'"._as( $data_arr['admin_yakusyoku'] )."'";
                                $array['admin_user_kengen']            = ""._e2z($data_arr['admin_user_kengen'])."";
                                $array['admin_master_kengen']          = ""._e2z($data_arr['admin_master_kengen'])."";
                                $array['admin_syuukei_etsuran_kengen'] = ""._e2z($data_arr['admin_syuukei_etsuran_kengen'])."";
                                $array['admin_login_kengen']           = ""._e2z($data_arr['admin_login_kengen'])."";
                                $array['admin_kyouryoku_kigyou_flg']   = ""._e2z($data_arr['admin_kyouryoku_kigyou_flg'])."";
                                $array['admin_update_date']            = "'".$_now_timestamp."'";

                                $where = "admin_id = '"._as($admin_id)."'";

                                _update( 'm_admin', $array, $where);

                                // -------------------------
                                $array_n = array();
                                $array_n['an_admin_name']              = "'"._as($data_arr['admin_name'])."'";

                                $where = "an_admin_id = '"._as($admin_id)."'";

                                _update( 'm_aname', $array_n, $where);

                                // -------------------------
                                $array_m = array();
                                $array_m['am_admin_mail']              = "'"._as($data_arr['admin_mail'])."'";

                                $where = "am_admin_id = '"._as($admin_id)."'";

                                _update( 'm_amail', $array_m, $where);

                                //**************************************************
                                // m_user 作成
                                //**************************************************
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
                                    $sql .= "  and user_login_id = '"._as($data_arr['admin_mail'])."'"."\n";
                                    $chk_rec = _select( $sql );
                                    if ( $chk_rec[0]['user_id'] == '' ){
                                        // insert
                                        $max_recs = _select( "select coalesce(max(substring(user_id,2)),'0') as max_id from m_user");
                                        $user_id  = sprintf("u%08d", ($max_recs[0]['max_id']+1));

                                        $array['user_id']                  = "'"._as($user_id)."'"; //ユーザID（u000000001）',
                                        $array['user_event_id']            = "'"._as($active_event_recs[ $loop ]['event_id'])."'"; //イベントID（e0001）',
                                        $array['user_vip_flg']             = 0; //VIPフラグ（1:VIP）',
                                        $array['user_big_cate']            = 7; //大分類 (AC社員)',

                                        // $array['user_mid_cate']            = 199; //中分類 (来場者)その他',
                                        $array['user_mid_cate']             = "'"._as( $fnd_mid_key )."'";

                                        $array['user_yakusyoku']             = "'"._as( $data_arr['admin_yakusyoku'] )."'";

                                        $array['user_kigyou_name']         = "'"._as('株式会社日本アクセス')."'"; //企業名',
                                        $array['user_kigyou_name_kana']    = "'"._as('カブシキガイシャニッポンアクセス')."'"; //企業名カナ',
                                        $array['user_busyo']               = "'"._as($syozoku_recs[0]['syozoku_name'])."'"; //部署',
                                        $array['user_pass']                = "'"._as( $admin_pass_md5 )."'"; //ログインパスワード(暗号化)',
                                        $array['user_admin_id']            = "'"._as($admin_id)."'"; //担当者ID（a0000001）',
                                        $array['user_web']                 = 1; //WEB招待（1:WEB招待者）',
                                        $array['user_mail_send_kbn']       = 1; //'PASS設定URLメール送信区分(0:未送信、1:送信済み、2:送信エラー)',
                                        $array['user_syounin_flg']         = 1; //'WEB招待の承認フラグ(0:未承認、1:承認済み)',
                                        $array['user_insert_date']         = "'".$_now_timestamp."'"; //作成日時
                                        $array['user_update_date']         = "'".$_now_timestamp."'"; //更新日時',
                                        _insert( 'm_user', $array);

                                        $array_n['un_user_id']             = "'"._as($user_id)."'"; //ユーザID（u000000001）',
                                        $array_n['un_user_name']           = "'"._as($data_arr['admin_name'])."'"; //'氏名',
                                        _insert( 'm_uname', $array_n);

                                        $array_m['um_user_id']             = "'"._as($user_id)."'"; //ユーザID（u000000001）',
                                        $array_m['um_user_mail']           = "'"._as($data_arr['admin_mail'])."'"; //メールアドレス',
                                        $array_m['um_user_login_id']       = "'"._as($data_arr['admin_mail'])."'"; // ログインid',
                                        _insert( 'm_umail', $array_m);

                                    } else {
                                        // update
                                        $where = "user_id = '"._as($chk_rec[0]['user_id'])."'";

                                        $array['user_event_id']            = "'"._as($active_event_recs[ $loop ]['event_id'])."'"; //イベントID（e0001）',
                                        // $array['user_vip_flg']             = 0; //VIPフラグ（1:VIP）',
                                        $array['user_big_cate']            = 7; //大分類 (AC社員)',

                                        // $array['user_mid_cate']            = 199; //中分類 (来場者)その他',
                                        $array['user_mid_cate']             = "'"._as( $fnd_mid_key )."'";

                                        $array['user_yakusyoku']             = "'"._as( $data_arr['admin_yakusyoku'] )."'";

                                        // $array['user_kigyou_name']         = "'"._as('株式会社日本アクセス')."'"; //企業名',
                                        // $array['user_kigyou_name_kana']    = "'"._as('カブシキガイシャニッポンアクセス')."'"; //企業名カナ',
                                        $array['user_busyo']               = "'"._as($syozoku_recs[0]['syozoku_name'])."'"; //部署',
                                        // $array['user_web']                 = 1; //WEB招待（1:WEB招待者）',
                                        // $array['user_mail_send_kbn']       = 1; //'PASS設定URLメール送信区分(0:未送信、1:送信済み、2:送信エラー)',
                                        $array['user_update_date']         = "'".$_now_timestamp."'"; //更新日時',
                                        _update( 'm_user', $array, $where);

                                        $where = "un_user_id = '"._as($chk_rec[0]['user_id'])."'";
                                        $array_n['un_user_name']           = "'"._as($data_arr['admin_name'])."'"; //'氏名',
                                        _update( 'm_uname', $array_n, $where);
                                    }
                                } // for 終端

                                $update_success++;
                            }

                            _delete('c_admin_companies', "admin_id = '" . _as($admin_id) . "'");

                            if ($data_arr['admin_kyouryoku_kigyou_flg'] == "1")
                            {
                                $kyouryoku_kigyou_names = explode('//', $data_arr['admin_kyouryoku_kigyou_names']);

                                $sql = "";
                                $sql .= " select company_id" . "\n";
                                $sql .= " from m_company"."\n";
                                $sql .= " where "."\n";
                                $sql .= "   company_delete_date is null ";
                                $sql .= '   and company_name in ( "'. implode('","', $kyouryoku_kigyou_names) .'")';
                                $company_recs = _select($sql);

                                foreach ($company_recs as $rec)
                                {
                                    $array = array();
                                    $array['admin_id'] = "'" . _as($admin_id) . "'";
                                    $array['company_id'] = _as($rec['company_id']);
                                    _insert('c_admin_companies', $array);
                                }
                            }

                            // 2021.06.02 del
                            // 新規作成の場合、パスワードを記録する。
                            // if ( $chk_recs[0]['admin_id'] == '' ){
                            //     $w_csv = "";
                            //     $w_csv .= ""  . "\""._as($data_arr['admin_name'])."\"";
                            //     $w_csv .= "," . "\""._as($data_arr['admin_tanarea_name'])."\"";
                            //     $w_csv .= "," . "\""._as($data_arr['admin_syozoku_name'])."\"";
                            //     $w_csv .= "," . "\""._as($data_arr['admin_mail'])."\"";
                            //     $w_csv .= "," . "\""._as($admin_pass)."\"";
                            //     $w_csv .= "\n";
                            //     $w_csv = mb_convert_encoding($w_csv, "SJIS-WIN", "UTF8");
                            //     fwrite($fp2, $w_csv);
                            // }

                            unset($data_arr); // データ開放
                        }
                    } //while

                    if($t_mail_list_insert_cnt==0 && ! empty($mailhd_id)){
                        //メール送信予約が一見もリストを作成しなかったので、メール予約データヘッダ削除
                        $where = "mailhd_id=".$mailhd_id;
                        _delete('t_mail_head',$where);
                    }

                    if( $error_line_count > 0 ) {
                        $err_msg[] = 'エラーがあったので登録処理を停止しました。';
                        var_dump($line_err);
                        _query( $conn, "rollback" );

                    }else{
                        if($insert_success > 0 || $update_success > 0){
                            $success_msg = "担当者の一括登録が完了しました。【新規】".number_format($insert_success)."件 ";
                            $success_msg .= "【更新】".number_format($update_success)."件";
                        }
                        _query($conn, "commit");
                    }

                    fclose($fp);
                    // fclose($fp2); 2021.06.02 del
                    @unlink($temp_file);

                } else {
                    $err_msg[] = "ファイルの読込みに失敗しました";
                }

                // if( _count($err_msg) > 0 ) { // 2021.06.02 del
                //     @unlink($temp_file2);
                // }
            }else{
                $err_msg[] = "ファイル移動に失敗しました。";
            }
        } else {
            $err_msg[] = "ファイルが選択されていません。";
        }

    // } elseif($_request['exec']=="download"){
    //     header("Content-Type: application/vnd.ms-excel");
    //     header('Content-Disposition: attachment; filename="'.$_request['dl_file'].'"');
    //     readfile(_SYSTEM_ROOT_DIR . "/upfile/new_tmp/".$_request['dl_file']);
    //     exit();

    } elseif($_request['exec'] == 'admin_download'){

        $w_flnm = "管理者リスト_".date('Ymd').".csv";
        header("Content-Disposition: attachment; filename=\"".mb_convert_encoding( $w_flnm, "SJIS-WIN" , _ENCODING_SRC )."\"");
        header("Content-Type: application/octet-stream; name=\"".$w_flnm."\"");

        $csv_head = '';
        $csv_head .=  '"担当者名"';
        $csv_head .= ',"メールアドレス(ログインID)"';
        $csv_head .= ',"所属支店・部署名"';
        $csv_head .= ',"来場者ﾃﾞｰﾀでの中分類"';
        $csv_head .= ',"役職"';
        $csv_head .= ',"ユーザー閲覧範囲"';
        $csv_head .= ',"マスター管理権限"';
        $csv_head .= ',"集計閲覧権限"';
        $csv_head .= ',"管理画面ログイン権限"';
        $csv_head .= "\r\n";
        echo mb_convert_encoding( $csv_head, "SJIS-WIN" , _ENCODING_SRC );

        $sql = "";
        $sql .= " select *"."\n";
        $sql .= " from v_admin"."\n";
        $sql .= " left join m_syozoku on (syozoku_id = admin_syozoku_id)"."\n";
        $sql .= " left join m_tantou_area on (syozoku_tanarea_id = tanarea_id and tanarea_delete_date is null)"."\n";
        $sql .= " where admin_delete_date is null"."\n";
        $sql .= "  and syozoku_delete_date is null"."\n";
        $sql .= " order by admin_id desc"."\n";
        $result = _query( $conn, $sql );
        while( $rec = _fetchArray( $result, $row ) ){
            $w_csv = "";
            $w_csv .=  '"'. csvSafe( $rec['admin_name'] ) .'"'; // 担当者名
            $w_csv .= ',"'. csvSafe( $rec['admin_mail'] ) .'"'; // メールアドレス(ログインID)
            $w_csv .= ',"'. csvSafe( $rec['syozoku_name'] ) .'"'; // 所属支店・部署名
            $w_csv .= ',"'. csvSafe( $_ac_mid_cate[$rec['admin_mid_cate']] ) .'"'; // 中分類
            $w_csv .= ',"'. csvSafe( $rec['admin_yakusyoku'] ) .'"'; // 役職
            $w_csv .= ',"'. csvSafe( $rec['admin_user_kengen'] ) .'"'; // 担当ユーザーのみ閲覧フラグ
            $w_csv .= ',"'. csvSafe( $rec['admin_master_kengen'] ) .'"'; // マスター管理権限
            $w_csv .= ',"'. csvSafe( $rec['admin_syuukei_etsuran_kengen'] ) .'"'; // 集計閲覧権限
            $w_csv .= ',"'. csvSafe( $rec['admin_login_kengen'] ) .'"'; // 管理画面ログイン権限

            // 行の終端 (改行コード)
            $w_csv .= "\r\n";

            echo mb_convert_encoding( $w_csv, "SJIS-WIN" , _ENCODING_SRC );
            $row++;
        }
        _freeResult( $result );

        exit();

    } elseif($_request['exec'] == 'csv_tpl_download'){

        $w_flnm = "tantousya_upload_form.xlsm";
        header("Content-Disposition: attachment; filename=\"".mb_convert_encoding( $w_flnm, "SJIS-WIN" , _ENCODING_SRC )."\"");
        header("Content-Type: application/octet-stream; name=\"".$w_flnm."\"");

        echo file_get_contents(_SYSTEM_ROOT_DIR."/admin/xlsm/" . $w_flnm);
        exit();
    }


    // $dlFilesPath = _glob(_SYSTEM_ROOT_DIR . "/upfile/new_tmp/*_admin_ikkatsu_touroku.csv");
    // $downLoadFiles = array();
    // for ($i=0; $i < _count($dlFilesPath); $i++) {
    //     $pIndfo = pathinfo($dlFilesPath[$i]);
    //     $downLoadFiles[ $pIndfo['basename'] ] = substr($pIndfo['basename'],0,4)."年".substr($pIndfo['basename'],4,2)."月".substr($pIndfo['basename'],6,2)."日"
    //                                           ." ".substr($pIndfo['basename'],8,2).":".substr($pIndfo['basename'],10,2)."アップロード分" ;
    // }
    // arsort($downLoadFiles);
    // $blade->assign('downLoadFiles',$downLoadFiles);

    // ******************************************
    // 担当者 一括登録レイアウト
    // ******************************************
    // 担当者名
    // メールアドレス(ログインID)
    // 担当エリア名
    // 所属支店・部署名
    // 担当ユーザーのみ閲覧フラグ（0:全て閲覧可、1:支店部署に紐づくユーザーのみ閲覧可、2:自身に紐づくユーザーのみ閲覧可）
    // マスター管理権限（0:なし、1:あり）
    // 集計閲覧権限（0:全て閲覧可、1:エリアのリアルタイム人数のみ）
    // 管理画面ログイン権限（0:不可、1:可）

    $csv_layout = "";
    $csv_layout .= "<table border=\"1\" style=\"width:500px;\">";
    $csv_layout .= "<tr><th style=\"background-color:#ffeeee;width:250px;\">項目名</th><th style=\"background-color:#ffeeee;width:250px;\">内容</th></tr>";
    $csv_layout .= "<tr><td>担当者名</td><td>（例）山田 太郎</td></tr>";
    $csv_layout .= "<tr><td>担当者メールアドレス</td><td>（例）xxxx@nippon-access.co.jp</td></tr>";
    // $csv_layout .= "<tr><td>担当エリア名</td><td>（例）近畿</td></tr>"; 2021.05.17 del
    $csv_layout .= "<tr><td>所属支店・部署名</td><td>（例）食品安全管理部</td></tr>";
    $csv_layout .= "<tr><td>来場者ﾃﾞｰﾀ<br>での中分類</td><td>「".implode("」 or <br>「",$_ac_mid_cate)."」</td></tr>";
    $csv_layout .= "<tr><td>役職</td><td>（例）部長</td></tr>";
    $csv_layout .= "<tr><td>ユーザー閲覧範囲</td><td>0:全て閲覧可<br>1:支店部署に紐づくユーザーのみ閲覧可<br>2:自身に紐づくユーザーのみ閲覧可</td></tr>";
    $csv_layout .= "<tr><td>マスター管理権限</td><td>0:なし、1:あり</td></tr>";
    $csv_layout .= "<tr><td>集計閲覧権限</td><td>0:全て閲覧可<br>1:エリアのリアルタイム人数のみ</td></tr>";
    $csv_layout .= "<tr><td>管理画面ログイン権限</td><td>0:不可、1:可</td></tr>";
    $csv_layout .= "<tr><td>外部企業担当者権限</td><td>0:AC担当者、1:外部担当者</td></tr>";
    $csv_layout .= "<tr><td>外部企業名</td><td>複数の場合は//区切りで登録</td></tr>";
    $csv_layout .= "</table>";

    $blade->assign( 'csv_layout', $csv_layout);
    $blade->assign( 'line_err', $line_err );

    if ( $_request['exec'] == '' || $_request['mail_notice'] != ''){
        $blade->assign( 'mail_notice', 1 );
    }
    if ( $_request['exec'] == ''){
        $blade->assign( 'mail_timing', "now" );
    }elseif ( $_request['mail_timing'] != ''){
        $blade->assign( 'mail_timing', $_request['mail_timing'] );
    }

    $contents_title = "担当者一括登録";
    $active_menu = "admin_ikkatsu_touroku";
    $contents_tpl = "admin_ikkatsu_touroku";
