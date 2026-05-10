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

    // ******************************************************************************************************
    // 初期値
    // ******************************************************************************************************
    $page = $_request['page'];
    $this_sess = &$_SESSION[_PROJECT_NAME][$page];
    $err_msg = array();
    $line_err = array();

    if($_request['exec']=="raijyou_csv_upload"){

        set_time_limit(180); //3分起動
        ini_set('memory_limit',"1024M"); //メモリ拡大

        setlocale(LC_ALL, 'ja_JP.UTF-8');
        if (is_uploaded_file($_FILES["csv_file"]["tmp_name"])) {
            // if(!file_exists(_SYSTEM_ROOT_DIR . '/upfile/new_tmp')){
            //     @mkdir(_SYSTEM_ROOT_DIR . '/upfile/new_tmp');
            //     if(!file_exists(_SYSTEM_ROOT_DIR . '/upfile/new_tmp')){
            //         exit("ディレクトリの作成に失敗しました");
            //     }
            // }
            $extension = '.' . _get_extension($_FILES['csv_file']['name']);
            $temp_file = _SYSTEM_ROOT_DIR . "/upfile/new_tmp/" . rand() . $extension;
            $file_move = move_uploaded_file($_FILES['csv_file']['tmp_name'], $temp_file);

            if ($file_move !== false) {

                //UTF8化して書き直す
                $buff = mb_convert_encoding(file_get_contents($temp_file), "UTF8", "SJIS-WIN");
                $buff = str_replace("\r\n", "\n", $buff);
                $buff = str_replace("\r", "\n", $buff);

                $fp = fopen($temp_file, "w");
                fwrite($fp, $buff);
                fclose($fp);

                //UTF8化したファイルを開く
                $fp = fopen($temp_file, "r");
                if ($fp !== false) {

                    _query($conn, "begin");

                    // 最大IDを取得しておく
                    $max_recs = _select("SELECT COALESCE(MAX(SUBSTRING(user_id,2)),'0') AS max_id FROM m_user");
                    $now_user_id = $max_recs[0]['max_id'];

                    $EVENT_LINE_NUM = 1; // イベント情報の入っている行数（１行目想定）
                    $HEADER_LINE_NUM = 2; // ヘッダーの行数（２行目想定）
                    $event_info = null;
                    $mail_send_ids = array();
                    $error_line_count = 0;
                    $insert_success = 0;
                    $line = 0;
                    while (($csv_row = fgetcsv($fp)) !== FALSE) {

                        if ($error_line_count > 200) {
                            // 一定数のエラーを許容する場合はメッセージを変更し、ループ後のエラーチェックをする
                            $line_err[] = 'エラー行が200件以上発生しましたので取り込み処理を中断しました。';
                            break;
                        }
                        ++$line;

                        if (empty($csv_row)) {
                            continue;
                        }

                        if ($line == $EVENT_LINE_NUM) {
                            $event_json = $csv_row[0];
                            if (is_null($event_json)) {
                                $line_err[] = 'イベント情報が取得出来ませんでした。';
                                break;
                            } else {
                                $event_info = json_decode($event_json, true);
                                foreach ($event_info as $index => $event) {
                                    // イベント存在チェック
                                    $sql = "";
                                    $sql .= " select * " . "\n";
                                    $sql .= " from m_event" . "\n";
                                    $sql .= " where " . "\n";
                                    $sql .= "   event_delete_date is null ";
                                    $sql .= "   and event_id = '" . _as($event['event_id']) . "'";
                                    $event_recs = _select($sql);
                                    if (_count($event_recs) == 0) {
                                        $line_err[] = "(${line}行目) イベント設定に指定されているイベントがマスタに登録されていません。";
                                        break;
                                    } else {
                                        // イベントのマスタも追加しておく
                                        $event_info[$index]['event'] = $event_recs[0];
                                        // 後からメール送信する対象の情報を入れる配列
                                        $event_info[$index]['mail_info'] = array();
                                    }
                                }
                                continue;
                            }
                        }

                        // ヘッダー
                        if ($line == $HEADER_LINE_NUM) {
                            $end_event = end($event_info);
                            $csv_count = count($csv_row);
                            $check_count = (int) $end_event['end_index'] + 1;

                            if ($csv_count !== $check_count) {
                                $line_err[] = "ヘッダーの列数とイベント設定が違っています。";
                                break;
                            }
                            continue;
                        }

                        $user_raijyou_yotei_time = array();

                        $data_arr = array();
                        $data_arr['syoutai_id'] = trim($csv_row[0]);    // 招待者（来場者マスタ）ID
                        $data_arr['user_web'] = trim($csv_row[7]);      // web招待
                        $data_arr['mail_send_flg'] = trim($csv_row[8]); // メール送信フラグ（1: あとで送信, 0: 送信しない）
                        $data_arr['admin_mail'] = trim($csv_row[9]);    // 担当者メールアドレス
                        $data_arr['user_tag'] = trim($csv_row[10]);     // タグ文字列

                        $w_err = array();

                        // CSV行データチェック開始
                        $chks = array(
                            "syoutai_id,(${line}行目) 来場者マスタID" => "need",
                            "admin_mail,(${line}行目) 担当者メールアドレス" => "need,email",
                        );
                        $w_err = _check($chks, $data_arr);

                        $syoutai_rec = null;
                        $admin_recs = null;

                        if (_count($w_err) == 0) {
                            //来場者マスタID存在チェック
                            $sql = "";
                            $sql .= " select * " . "\n";
                            $sql .= " from v_syoutai" . "\n";
                            $sql .= " where " . "\n";
                            $sql .= "   syoutai_delete_date is null ";
                            $sql .= "   and syoutai_id = '" . _as($data_arr['syoutai_id']) . "'";

                            $chk_recs = _select($sql);
                            if (_count($chk_recs) == 0) {
                                $w_err[] = "(${line}行目) 来場者マスタIDに該当する来場者が来場者マスタに登録されていません。";
                            } else {
                                $syoutai_rec = $chk_recs[0];
                            }

                            //担当者メールアドレス存在チェック
                            $sql = "";
                            $sql .= " select admin_id" . "\n";
                            $sql .= " from v_admin" . "\n";
                            $sql .= " where " . "\n";
                            $sql .= "   admin_delete_date is null ";
                            $sql .= "   and admin_mail = '" . _as($data_arr['admin_mail']) . "'";
                            $admin_recs = _select($sql);
                            if (_count($admin_recs) == 0) {
                                $w_err[] = "(${line}行目) 担当者メールアドレスに該当する担当者が担当者マスタに登録されていません。";
                            }

                            if ( ! is_null($syoutai_rec)) {
                                if ($data_arr['user_web'] != "" && $data_arr['user_web'] != "1") {
                                    $w_err[] = "(${line}行目) " . "WEB招待の値は「1」又は空で指定してください。";
                                } else {
                                    //2021/07/07 Add ----------- Start ------------
                                    if ($data_arr['user_web'] == "1") {
                                        if ($syoutai_rec['syoutai_big_cate'] != 1
                                            && $syoutai_rec['syoutai_big_cate'] != 2
                                            && $syoutai_rec['syoutai_big_cate'] != 7
                                            && $syoutai_rec['syoutai_big_cate'] != 8) {
                                            //WEB招待しているが、大分類が「1:小売、2:外食、7:AC社員、8:その他(来場) でなければエラー
                                            // $err_msg[] = "WEB展示会（ガイドブック）に招待できるのは「(招待者)小売、(招待者)外食、(来場者)AC社員」のみです。";
                                            $w_err[] = "(${line}行目) " . "WEB展示会（ガイドブック）に招待できるのは大分類が「AC社員」と「その他(来場)」のみです。";
                                        }
                                    }
                                    //2021/07/07 Add ----------- End ------------
                                }
                            }
                        }

                        if (_count($w_err) > 0) {
                            $line_err = _array_merge($line_err, $w_err);
                            $error_line_count++;
                            continue;
                        }

                        $data_arr['user_name'] = trim($syoutai_rec['syoutai_name']); // 氏名
                        $data_arr['user_name_kana'] = trim($syoutai_rec['syoutai_name_kana']); // 氏名カナ
                        $data_arr['user_vip_flg'] = strtoupper($syoutai_rec['syoutai_vip_flg']); //VIP
                        $data_arr['user_big_cate'] = trim($syoutai_rec['syoutai_big_cate']); //大分類
                        $data_arr['user_mid_cate'] = trim($syoutai_rec['syoutai_mid_cate']); //中分類
                        $data_arr['user_company_id'] = trim($syoutai_rec['syoutai_company_id']); //企業名ID
                        $data_arr['user_kigyou_name'] = "'"._as( $syoutai_rec['syoutai_kigyou_name'] )."'"; //企業名',
                        $data_arr['user_kigyou_name_kana'] = "'"._as( $syoutai_rec['syoutai_kigyou_name_kana'] )."'"; //企業名カナ',
                        $data_arr['user_busyo'] = trim($syoutai_rec['syoutai_busyo']); //部署
                        $data_arr['user_yakusyoku'] = trim($syoutai_rec['syoutai_yakusyoku']); //役職
                        $data_arr['user_mail'] = trim($syoutai_rec['syoutai_mail']); //メールアドレス
                        $data_arr['user_login_id'] = trim($syoutai_rec['syoutai_login_id']); //ログインID

                        $data_arr['admin_mail'] = str_replace("‐", "-", $data_arr['admin_mail']);

//                        if ($data_arr['big_cate_name'] == "メーカー") $data_arr['big_cate_name'] = "メーカー(出展)";
//                        if ($data_arr['big_cate_name'] == "その他") $data_arr['big_cate_name'] = "その他(来場)";

                        $data_arr['raijou_yotei_times'] = array();

                        // イベントごとの来場予定日時(yyyy/mm/dd 時間帯など 形式の#区切り)
                        foreach ($event_info as $event) {
                            $start_index = (int) $event['start_index'];
                            $end_index = (int) $event['end_index'];
                            $raijou_yotei_times = array_slice($csv_row, $start_index, $end_index - $start_index);
                            $db_raijou_yotei_times = explode("#", $event['event']['event_raijyou_yotei_time']);
                            $raijou_yotei = '';

                            foreach ($raijou_yotei_times as $index => $yotei) {
                                if ($yotei != '0' && ! empty($yotei)) {
                                    if ( ! empty($raijou_yotei)) {
                                        $raijou_yotei .= '#';
                                    }
                                    $raijou_yotei .= $db_raijou_yotei_times[$index];
                                }
                            }

                            $data_arr['raijou_yotei_times'][$event['event_id']] = $raijou_yotei;
                        }

                        unset($csv_row);

                        if (_count($w_err) > 0) {
                            $line_err = _array_merge($line_err, $w_err);
                            $error_line_count++;
                            continue;
                        } else {

                            foreach ($event_info as $index => $event) {
                                $sql = "";
                                $sql .= " select * " . "\n";
                                $sql .= " from v_user" . "\n";
                                $sql .= " where " . "\n";
                                $sql .= "   user_delete_date is null ";
                                $sql .= "   and user_syoutai_id = '" . _as($data_arr['syoutai_id']) . "'";
                                $sql .= "   and user_event_id = '" . _as($event['event_id']) . "'";
                                $user_recs = _select($sql);

                                if (count($user_recs) > 0) {
                                    // 更新

                                    $array = array();
                                    $array['user_admin_id'] = "'" . _as($admin_recs[0]['admin_id']) . "'"; //担当者ID（a0000001）',
                                    $array['user_raijyou_yotei_time'] = "'" . _as($data_arr['raijou_yotei_times'][$event['event_id']]) . "'"; //来場予定日時（yyyy/mm/dd HH:ii 形式）',
                                    $array['user_tag'] = "'" . _as($data_arr['user_tag']) . "'"; // タグ文字列

                                    $where = 'user_id = ' . "'" . _as($user_recs[0]['user_id']) . "'";

                                    _update("m_user",$array,$where);

                                } else {
                                    // 新規

                                    if (empty($data_arr['raijou_yotei_times'][$event['event_id']])) {
                                        // 来場予定日時の指定が無い場合は新規登録しない
                                        continue;
                                    }

                                    $now_user_id = $now_user_id + 1;
                                    $user_id = sprintf("u%08d", $now_user_id);

                                    $array = array();
                                    $array_n = array();
                                    $array_m = array();

                                    $array['user_id'] = "'" . _as($user_id) . "'";
                                    $array['user_event_id'] = "'" . _as($event['event_id']) . "'"; //イベントID（e0001）',
                                    $array['user_admin_id'] = "'" . _as($admin_recs[0]['admin_id']) . "'"; //担当者ID（a0000001）',
                                    $array['user_syoutai_id'] = "'" . _as($data_arr['syoutai_id']) . "'"; // 来場者マスタID
                                    if ($data_arr['user_vip_flg'] == "") {
                                        $array['user_vip_flg'] = "0"; //VIPフラグ（1:VIP）',
                                    } else {
                                        $array['user_vip_flg'] = "1"; //VIPフラグ（1:VIP）',
                                    }

                                    $array['user_big_cate'] = "" . _as($data_arr['user_big_cate']) . "";//大分類',
                                    $array['user_mid_cate'] = "" . _e2n($data_arr['user_mid_cate']) . ""; //中分類',
                                    $array['user_company_id'] = "'" . _as($data_arr['user_company_id']) . "'"; //企業ID',
                                    $array['user_kigyou_name'] = "'"._as( $data_arr['syoutai_kigyou_name'] )."'"; //企業名',
                                    $array['user_kigyou_name_kana'] = "'"._as( $data_arr['syoutai_kigyou_name_kana'] )."'"; //企業名カナ',
                                    $array['user_busyo'] = "'" . _as($data_arr['user_busyo']) . "'"; //部署',
                                    $array['user_yakusyoku'] = "'" . _as($data_arr['user_yakusyoku']) . "'"; //役職',
                                    $array['user_pass'] = "'" . _as(md5("_NEED_PASS_SET_")) . "'"; //パスワード', 2020.12.18 mod


                                    $array['user_raijyou_yotei_time'] = "'" . _as($data_arr['raijou_yotei_times'][$event['event_id']]) . "'"; //来場予定日時（yyyy/mm/dd HH:ii 形式）',


                                    $array['user_web'] = "" . _e2z($data_arr['user_web']) . ""; //WEB招待（1:WEB招待者）',
                                    $array['user_biko'] = "'" . _as($data_arr['user_biko']) . "'"; //備考',

                                    $array['user_update_date'] = "'" . $_now_timestamp . "'"; //更新日時',
                                    $array['user_insert_date'] = "'" . $_now_timestamp . "'";
                                    $array['user_syounin_flg'] = 1; //'WEB招待の承認フラグ(0:未承認、1:承認済み)',
                                    $array['user_tag'] = "'" . _as($data_arr['user_tag']) . "'"; // タグ文字列

                                    $array_n['un_user_id'] = "'" . _as($user_id) . "'";
                                    $array_n['un_user_name'] = "'" . _as($data_arr['user_name']) . "'"; //'氏名',
                                    $array_n['un_user_name_kana'] = "'" . _as($data_arr['user_name_kana']) . "'"; //氏名カナ',

                                    $array_m['um_user_id'] = "'" . _as($user_id) . "'";
                                    // 2021/04/01 mod ------------------------ before ----------------------- 大幅変更
                                    // $array_m['um_user_mail']                = "'"._as($data_arr['user_mail'])."'"; //メールアドレス',
                                    // 2021/04/01 mod ------------------------ after ------------------------ 大幅変更
                                    $array_m['um_user_mail'] = "'" . _as($data_arr['user_mail']) . "'";    // メールアドレス',
                                    $array_m['um_user_login_id'] = "'" . _as($data_arr['user_login_id']) . "'"; // ログインid',
                                    // 2021/04/01 mod ------------------------ end   ------------------------ 大幅変更

                                    _insert('m_user', $array);
                                    _insert('m_uname', $array_n);
                                    _insert('m_umail', $array_m);

                                    if ($data_arr['mail_send_flg'] == '1') {
                                        $event_info[$index]['mail_info'][] = array(
                                            'user_id' => $array_m['um_user_id'],
                                            'mail_address' => $array_m['um_user_mail'],
                                            'user_raijyou_yotei_time' => $array['user_raijyou_yotei_time']
                                        );
                                    }
                                }
                            }

                            $insert_success++;
                            unset($data_arr); // データ開放
                        }
                    } //while
                    if ($error_line_count > 0) {
                        $err_msg[] = 'エラーがあったので登録処理を停止しました。(全ての登録がキャンセルされました)';
                        _query($conn, "rollback");
                    } else {
                        if ($insert_success > 0) {
                            $success_msg = $insert_success . "件登録しました。";

                            // メール送信登録
                            $send_nomals = array();
                            $send_webs = array();

                            foreach ($event_info as $event) {
                                $send_nomals[$event['event_id']] = array();
                                $send_webs[$event['event_id']] = array();
                                foreach ($event['mail_info'] as $mail_info) {
                                    if ($mail_info['user_raijyou_yotei_time'] != '') {
                                        $send_nomals[$event['event_id']][] = $mail_info;
                                    } else {
                                        $send_webs[$event['event_id']][] = $mail_info;
                                    }
                                }
                            }

                            $sql = "";
                            $sql .= "select * from m_mail_template";
                            $sql .= " where";
                            $sql .= " mailt_delete_date is null";
                            $sql .= " and mailt_key = 'pass_set_annai'";
                            $nomal_tpl_recs = _select($sql);

                            if (_count($nomal_tpl_recs) > 0 && _count($send_nomals) > 0) {
                                foreach ($send_nomals as $index => $mail_infos) {
                                    $event_id = $index;
                                    if (_count($mail_infos) > 0) {
                                        $tpl_recs = $nomal_tpl_recs[0];
                                        $array = array();
                                        $array['mailhd_mailt_name'] = "'"._as($tpl_recs['mailt_name'])."'";
                                        $array['mailhd_mailt_key'] = "'"._as($tpl_recs['mailt_key'])."'";
                                        $array['mailhd_subject'] = "'"._as($tpl_recs['mailt_subject'])."'";
                                        $array['mailhd_body'] = "'"._as($tpl_recs['mailt_body'])."'";
                                        $array['mailhd_yoyaku_ymdhi']    = "'2099/12/31 23:59'";
                                        $array['mailhd_status'] = "0";
                                        $array['mailhd_test_send_flg'] = "0"; //0:本番送信
                                        $array['mailhd_insert_admin_id'] = "'"._as($_SESSION[_PROJECT_NAME]['admin_login']['admin_id'])."'";

                                        $array['mailhd_insert_date'] = "'".$_now_timestamp."'";
                                        $array['mailhd_update_date'] = "'".$_now_timestamp."'";
                                        _insert('t_mail_head',$array);

                                        $mailhd_id = $conn->insert_id; //insertされたAUTO_INCREMENTの値取得

                                        foreach ($mail_infos as $mail_info) {
                                            $array = array();
                                            $array['maills_mailhd_id'] = "'"._as($mailhd_id)."'";
                                            $array['maills_user_id'] = $mail_info['user_id'];
                                            $array['maills_mail_address'] = $mail_info['mail_address'];
                                            $array['maills_event_id'] = "'"._as($event_id)."'";
                                            $array['maills_insert_date'] = "'".$_now_timestamp."'";
                                            $array['maills_update_date'] = "'".$_now_timestamp."'";
                                            _insert('t_mail_list',$array);
                                        }
                                    }
                                }

                            }

                            $sql = "";
                            $sql .= "select * from m_mail_template";
                            $sql .= " where";
                            $sql .= " mailt_delete_date is null";
                            $sql .= " and mailt_key = 'pass_set_annai_web_only'";
                            $web_tpl_recs = _select($sql);

                            if (_count($web_tpl_recs) > 0 && _count($send_webs) > 0) {
                                foreach ($send_webs as $index => $mail_infos) {
                                    $event_id = $index;
                                    if (_count($mail_infos) > 0) {
                                        $tpl_recs = $web_tpl_recs[0];
                                        $array = array();
                                        $array['mailhd_mailt_name'] = "'"._as($tpl_recs['mailt_name'])."'";
                                        $array['mailhd_mailt_key'] = "'"._as($tpl_recs['mailt_key'])."'";
                                        $array['mailhd_subject'] = "'"._as($tpl_recs['mailt_subject'])."'";
                                        $array['mailhd_body'] = "'"._as($tpl_recs['mailt_body'])."'";
                                        $array['mailhd_yoyaku_ymdhi']    = "'2099/12/31 23:59'";
                                        $array['mailhd_status'] = "0";
                                        $array['mailhd_test_send_flg'] = "0"; //0:本番送信
                                        $array['mailhd_insert_admin_id'] = "'"._as($_SESSION[_PROJECT_NAME]['admin_login']['admin_id'])."'";

                                        $array['mailhd_insert_date'] = "'".$_now_timestamp."'";
                                        $array['mailhd_update_date'] = "'".$_now_timestamp."'";
                                        _insert('t_mail_head',$array);

                                        $mailhd_id = $conn->insert_id; //insertされたAUTO_INCREMENTの値取得

                                        foreach ($mail_infos as $mail_info) {
                                            $array = array();
                                            $array['maills_mailhd_id'] = "'"._as($mailhd_id)."'";
                                            $array['maills_user_id'] = $mail_info['user_id'];
                                            $array['maills_mail_address'] = $mail_info['mail_address'];
                                            $array['maills_event_id'] = "'"._as($event_id)."'";
                                            $array['maills_insert_date'] = "'".$_now_timestamp."'";
                                            $array['maills_update_date'] = "'".$_now_timestamp."'";
                                            _insert('t_mail_list',$array);
                                        }
                                    }
                                }

                            }
                        }
                        _query($conn, "commit");
                    }

                    fclose($fp);

                } else {
                    $err_msg[] = "ファイルの読込みに失敗しました";
                }

                @unlink($temp_file);

            } else {
                $err_msg[] = "ファイル移動に失敗しました。";
            }
        } else {
            $err_msg[] = "ファイルが選択されていません。";
        }

    }

    // ******************************************
    // 来場者レイアウト
    // ******************************************
    //大分類
    $raijyou_big_list = "";
    $wArr = array();
    foreach ($_conf_big_cate as $key => $value) {
        $wArr[] = "「".$value."」";
    }
    $raijyou_big_list = implode("or<br>", $wArr) . "<br>で指定";

    //中分類
    $raijyou_mid_list = "";
    $wArr = array();
    foreach ($_conf_mid_cate2 as $key => $value) {
        $wArr[] = "「".$value."」";
    }
    $raijyou_mid_list = implode("or<br>", $wArr) . "<br>で指定";


    $raijyou_layout = "";
    $raijyou_layout .= "<table border=\"1\" style=\"width:500px;\">";
    $raijyou_layout .= "<tr><th style=\"background-color:#ffeeee;width:250px;\">項目名</th><th style=\"background-color:#ffeeee;width:250px;\">内容</th></tr>";
    $raijyou_layout .= "<tr><td>来場者マスタID</td><td>（例）s000xxxxx <strong>※変更厳禁</strong></td></tr>";
    $raijyou_layout .= "<tr><td>来場者氏名</td><td>（例）山田 太郎</td></tr>";
    $raijyou_layout .= "<tr><td>大分類</td><td>".$raijyou_big_list."</td></tr>";
    $raijyou_layout .= "<tr><td>企業名</td><td>（例）株式会社○○○○</td></tr>";
    $raijyou_layout .= "<tr><td>部署</td><td>（例）食品事業部</td></tr>";
    $raijyou_layout .= "<tr><td>役職</td><td>（例）課長</td></tr>";
    $raijyou_layout .= "<tr><td>メールアドレス</td><td>（例）xxxx@xxx.com ※重複可</td></tr>";
    $raijyou_layout .= "<tr><td>WEB招待</td><td>招待の場合「1」を指定</td></tr>";
    $raijyou_layout .= "<tr><td>メール通知</td><td>（例）1: あとから送信, 以外は送信しない</td></tr>";
    $raijyou_layout .= "<tr><td>AC担当者メールアドレス</td><td>（例）xxxx@nippon-access.co.jp</td></tr>";
    $raijyou_layout .= "<tr><td>タグ文字列</td><td>任意の文字列を指定可能</td></tr>";
    $raijyou_layout .= "<tr><td>来場日時</td><td>来場予定の場合「1」を指定</td></tr>";
    $raijyou_layout .= "</table>";

    $blade->assign('raijyou_layout',$raijyou_layout);
    $blade->assign('line_err',$line_err);

    $blade->assign('title_bgcolor',$select_event_rec['event_title_bgcolor']);

    $contents_title = "来場者一括登録・編集用CSVアップロード";
    $active_menu = "user_list";
    $contents_tpl = "syoutai_raijyoudata_ikkatsu_touroku";
