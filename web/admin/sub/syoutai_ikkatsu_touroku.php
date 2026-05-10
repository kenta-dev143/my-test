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

    $line_err = array();

    if($_request['exec']=="syoutai_csv_upload"){

        set_time_limit(180); //3分起動
        ini_set('memory_limit',"1024M"); //メモリ拡大

        setlocale(LC_ALL, 'ja_JP.UTF-8');
        if (is_uploaded_file($_FILES["csv_file"]["tmp_name"])) {
            $extension = '.'._get_extension($_FILES['csv_file']['name']);
            $temp_file = _SYSTEM_ROOT_DIR . "/upfile/new_tmp/" . rand() . $extension;
            $file_move = move_uploaded_file($_FILES['csv_file']['tmp_name'], $temp_file );

            if ($file_move !== false) {

                //UTF8化しえ書き直す
                $buff = mb_convert_encoding(file_get_contents($temp_file), "UTF8", "SJIS-WIN");
                $buff = str_replace("\r\n", "\n", $buff);
                $buff = str_replace("\r", "\n", $buff);

                $fp = fopen($temp_file,"w");
                fwrite($fp, $buff);
                fclose($fp);

                //UTF8化したファイルを開く
                $fp = fopen($temp_file, "r");
                if($fp !== false){

                    _query($conn, "begin");

                    // 最大IDを取得しておく
                    $max_recs = _select( "select coalesce(max(substring(syoutai_id,2)),'0') as max_id from m_syoutai");
                    $now_syoutai_id = $max_recs[0]['max_id'];

                    $error_line_count = 0;
                    $insert_success = 0;
                    $line = 0;
                    while (($csv_row = fgetcsv($fp)) !== FALSE) {

                        if($error_line_count > 200) {
                            // 一定数のエラーを許容する場合はメッセージを変更し、ループ後のエラーチェックをする
                            $line_err[] = 'エラー行が200件以上発生しましたので取り込み処理を中断しました。';
                            break;
                        }
                        ++$line;

                        $data_arr = array();
                        // $data_arr['syoutai_sansan_id']          = trim($csv_row[0]); //SANSAN ID 2021.05.17 del
                        $data_arr['syoutai_name']               = trim($csv_row[1]); //氏名
                        $data_arr['syoutai_name_kana']          = trim($csv_row[2]); //氏名カナ
                        $data_arr['syoutai_vip_flg']            = strtoupper( trim($csv_row[3]) ); //VIP
                        $data_arr['big_cate_name']              = trim($csv_row[4]); //大分類
                        $data_arr['mid_cate_name']              = trim($csv_row[5]); //中分類
                        $data_arr['syoutai_kigyou_name']        = trim($csv_row[6]); //企業名
//                        $data_arr['syoutai_kigyou_name_kana']   = trim($csv_row[6]); //企業名カナ2021.11.14 del
                        $data_arr['syoutai_busyo']              = trim($csv_row[7]); //部署
                        $data_arr['syoutai_yakusyoku']          = trim($csv_row[8]); //役職
                        $data_arr['syoutai_mail']               = trim($csv_row[9]); //メールアドレス
                        $data_arr['syoutai_login_id']           = trim($csv_row[10]); //ログインID
                        $data_arr['syoutai_tag']                = trim($csv_row[13]); //タグ文字列
                        $data_arr['syoutai_biko']               = trim($csv_row[14]); //備考

                        $data_arr['syoutai_name_kana']          = mb_convert_kana($data_arr['syoutai_name_kana'],"KV");
                        $data_arr['syoutai_kigyou_name_kana']   = mb_convert_kana($data_arr['syoutai_kigyou_name_kana'],"KV");

                        $data_arr['syoutai_mail']     = str_replace("‐","-",$data_arr['syoutai_mail']);
                        $data_arr['syoutai_login_id'] = str_replace("‐","-",$data_arr['syoutai_login_id']);
                        $data_arr['syoutai_vip_flg']  = str_replace("　","",$data_arr['syoutai_vip_flg']);

                        if($data_arr['big_cate_name'] == "メーカー") $data_arr['big_cate_name'] = "メーカー(招待)";
                        if($data_arr['big_cate_name'] == "その他") $data_arr['big_cate_name'] = "その他(招待)";

                        unset($csv_row);

                        //ヘッダ行
                        if($line ==1){
                            if( _count($line_err) > 0 ) {
                                $error_line_count++;
                                $line_err[] = 'ヘッダ行にエラーがあった為、取り込み処理を中断しました。';
                                break;
                            }
                            continue;
                        }

                        // CSV行データチェック開始
                        $chks = array(
                                        "syoutai_name,(${line}行目) 氏名"                           => "need",
                                        "syoutai_name_kana,(${line}行目) 氏名カナ"                  => "zenkana",
                                        "syoutai_kigyou_name,(${line}行目) 企業名"                  => "need",
                                        "syoutai_mail,(${line}行目) 招待者メールアドレス"           => "need,email",
                                        "syoutai_login_id,(${line}行目) 招待者ログインID"           => "need,email",
                                        );
                        $w_err = _check( $chks, $data_arr );

                        if(_count($w_err)==0){
                            if($data_arr['syoutai_vip_flg'] != "" && $data_arr['syoutai_vip_flg'] != "VIP"){
                                $w_err[] = "(${line}行目) VIPの値は「VIP」又は空で指定してください。";
                            }

                            //大分類名存在チェック
                            $syoutai_big_cate = "";
                            $wArr = array();
                            $big_cate = $_conf_big_cate1 + $_conf_big_cate2;
                            foreach ($big_cate as $key => $value) {
                                $wArr[] = "「".$value."」";
                                if( $value == $data_arr['big_cate_name']){
                                    $syoutai_big_cate = $key;
                                    break;
                                }
                            }
                            if($syoutai_big_cate==""){
                                $w_err[] = "(${line}行目) 大分類の名称は".implode("、", $wArr)."で指定してください。";
                            }

                            //中分類名存在チェック
                            $syoutai_mid_cate = "";
                            $wArr = array();
                            $mid_cate = $_conf_mid_cate1 + $_conf_mid_cate2;
                            foreach ($mid_cate as $key => $value) {
                                $wArr[] = "「".$value."」";
                                if( $value == $data_arr['mid_cate_name']){
                                    $syoutai_mid_cate = $key;
                                    break;
                                }
                            }
                            if($syoutai_mid_cate==""){
                                $w_err[] = "(${line}行目) 中分類の名称は".implode("、", $wArr)."で指定してください。";
                            }

                            //招待者ログインID存在チェック
                            $sql = "";
                            $sql .= " select syoutai_id"."\n";
                            $sql .= " from v_syoutai"."\n";
                            $sql .= " where "."\n";
                            $sql .= "   syoutai_delete_date is null ";
                            $sql .= "   and syoutai_login_id = '". _as( $data_arr['syoutai_login_id'] ) ."'";
                            $chk_recs = _select($sql);
                            if ( _count($chk_recs) > 0 ){
                                $w_err[] = "(${line}行目) 招待者ログインIDは別の招待者(or来場者)で登録されています。";
                            }

                            // ID(メアド)から実際のメールアドレス部分抽出
                            $real_user_mail_addr = _getMailAddressFromID( $data_arr['syoutai_login_id'] );
                            // emailアドレスの形式チェック
                            if ( _emailCheck($real_user_mail_addr, '') === false ){
                                $w_err[]  = "(${line}行目) 招待者ログインIDを正しく入力して下さい。";
                            }

                            // 企業マスタ存在チェック
                            $company_id = '';
                            $sql = "";
                            $sql .= " select company_id"."\n";
                            $sql .= " from m_company"."\n";
                            $sql .= " where "."\n";
                            $sql .= "   company_delete_date is null ";
                            $sql .= "   and company_name = '". _as( $data_arr['syoutai_kigyou_name'] ) ."'";
                            $chk_recs = _select($sql);
                            if ( _count($chk_recs) == 0 ){
                              $w_err[] = "(${line}行目) 企業マスタに「" . $data_arr['syoutai_kigyou_name'] . "」が存在しません。";
                            } else {
                              $company_id = $chk_recs[0]['company_id'];
                            }
                        }

                        if( _count($w_err) > 0 ){
                            $line_err = _array_merge($line_err, $w_err);
                            $error_line_count++;
                            continue;
                        }else{

                            $array = array();

                            $now_syoutai_id = $now_syoutai_id + 1;
                            $syoutai_id = sprintf("s%08d", $now_syoutai_id );

                            $array   = array();
                            $array_n = array();
                            $array_m = array();

                            $array['syoutai_id']                  = "'"._as($syoutai_id)."'";
                            // $array['syoutai_sansan_id']           =  "'"._as($data_arr['syoutai_sansan_id'])."'"; // SANSAN ID 2021.05.17 del

                            if($data_arr['syoutai_vip_flg']==""){
                                $array['syoutai_vip_flg']             = "0"; //VIPフラグ（1:VIP）',
                            }else{
                                $array['syoutai_vip_flg']             = "1"; //VIPフラグ（1:VIP）',
                            }

                            $array['syoutai_big_cate']            = ""._e2n($syoutai_big_cate)."";//大分類',
                            $array['syoutai_mid_cate']            = ""._e2n($syoutai_mid_cate).""; //中分類',
                            $array['syoutai_company_id']          = "'"._as($company_id)."'"; //企業ID',
//                            $array['syoutai_kigyou_name']         = "'"._as($data_arr['syoutai_kigyou_name'])."'"; //企業名',
//                            $array['syoutai_kigyou_name_kana']    = "'"._as($data_arr['syoutai_kigyou_name_kana'])."'"; //企業名カナ',
                            $array['syoutai_busyo']               = "'"._as($data_arr['syoutai_busyo'])."'"; //部署',
                            $array['syoutai_yakusyoku']           = "'"._as($data_arr['syoutai_yakusyoku'])."'"; //役職',
                            $array['syoutai_tag']                 = "'"._as($data_arr['syoutai_tag'])."'"; //タグ文字列',

                            $array['syoutai_biko']                = "'"._as($data_arr['syoutai_biko'])."'"; //備考',

                            $array['syoutai_update_date']         = "'".$_now_timestamp."'"; //更新日時',
                            $array['syoutai_insert_date']         = "'".$_now_timestamp."'";

                            $array['syoutai_last_upd_id']         = "'"._as($_SESSION[_PROJECT_NAME]['admin_login']['admin_id'])."'"; //最終更新者ID',
                            $array['syoutai_last_upd_naiyou']     = "'"._as('CSV一括新規登録')."'"; //最終更新内容',

                            $array_n['sn_syoutai_id']             = "'"._as($syoutai_id)."'";
                            $array_n['sn_syoutai_name']           = "'"._as($data_arr['syoutai_name'])."'"; //'氏名',
                            $array_n['sn_syoutai_name_kana']      = "'"._as($data_arr['syoutai_name_kana'])."'"; //氏名カナ',

                            $array_m['sm_syoutai_id']             = "'"._as($syoutai_id)."'";
                            $array_m['sm_syoutai_mail']           = "'"._as($data_arr['syoutai_mail'])."'";    // メールアドレス',
                            $array_m['sm_syoutai_login_id']       = "'"._as($data_arr['syoutai_login_id'])."'"; // ログインid',

                            _insert( 'm_syoutai', $array);
                            _insert( 'm_sname',   $array_n);
                            _insert( 'm_smail',   $array_m);

                            $insert_success++;
                            unset($data_arr); // データ開放
                        }
                    } //while
                    if($error_line_count > 0) {
                        $err_msg[] = 'エラーがあったので登録処理を停止しました。(全ての登録がキャンセルされました)';
                        _query( $conn, "rollback" );
                    }else{
                        if($insert_success > 0){
                            $success_msg = $insert_success."件登録しました。";
                            $onload_flg = 1;
                        }
                        _query($conn, "commit");
                    }
                    fclose($fp);

                } else {
                    $err_msg[] = "ファイルの読込みに失敗しました";
                }
                @unlink($temp_file);

            }else{
                $err_msg[] = "ファイル移動に失敗しました。";
            }
        } else {
            $err_msg[] = "ファイルが選択されていません。";
        }

    }elseif($_request['exec'] == 'syoutai_tpl_download'){

        $csv_head = '';
        // $csv_head .=  '"SANSANID"'; 2021.05.17 del
        $csv_head .= ' "招待者氏名"';
        $csv_head .= ',"招待者氏名カナ"';
        $csv_head .= ',"VIP"';
        $csv_head .= ',"大分類"';
        $csv_head .= ',"中分類"';
        $csv_head .= ',"企業名"';
        $csv_head .= ',"企業名カナ"';
        $csv_head .= ',"部署"';
        $csv_head .= ',"役職"';
        $csv_head .= ',"招待者メールアドレス"';
        $csv_head .= ',"招待者ログインID"';
        $csv_head .= ',"備考"';
        $csv_head .= "\r\n";

        $w_flnm = "招待者一括アップロードテンプレート.csv";
        header("Content-Disposition: attachment; filename=\"".mb_convert_encoding( $w_flnm, "SJIS-WIN" , _ENCODING_SRC )."\"");
        header("Content-Type: application/octet-stream; name=\"".$w_flnm."\"");

        echo mb_convert_encoding( $csv_head, "SJIS-WIN" , _ENCODING_SRC );
        exit();

    }elseif($_request['exec'] == 'syoutai_form_download'){

        $w_flnm = "raijyousya_upload_form.xlsm";
        header("Content-Disposition: attachment; filename=\"".mb_convert_encoding( $w_flnm, "SJIS-WIN" , _ENCODING_SRC )."\"");
        header("Content-Type: application/octet-stream; name=\"".$w_flnm."\"");

        echo file_get_contents(_SYSTEM_ROOT_DIR."/admin/xlsm/raijyousya_upload_form.xlsm");
        exit();

    }

    // ******************************************
    // 招待者レイアウト
    // ******************************************
    //大分類
    $syoutai_big_list = "";
    $wArr = array();
    foreach ($_conf_big_cate1 + $_conf_big_cate2 as $key => $value) {
        $wArr[] = "「".$value."」";
    }
    $syoutai_big_list = implode("or<br>", $wArr) . "<br>で指定";

    //中分類
    $syoutai_mid_list = "";
    $wArr = array();
    foreach ($_conf_mid_cate1 + $_conf_mid_cate2 as $key => $value) {
        $wArr[] = "「".$value."」";
    }
    $syoutai_mid_list = implode("or<br>", $wArr) . "<br>で指定";

    $syoutai_layout = "";
    $syoutai_layout .= "<table border=\"1\" style=\"width:600px;\">";
    $syoutai_layout .= "<tr><th style=\"background-color:#ffeeee;width:250px;\">項目名</th><th style=\"background-color:#ffeeee;width:350px;\">内容</th></tr>";
    // $syoutai_layout .= "<tr><td>SANSAN ID</td><td>（例）ABCD1234 </td></tr>"; 2021.05.17 del
    $syoutai_layout .= "<tr><td>来場者氏名 <span class='text-danger'>※必須</span></td><td>（例）山田 太郎</td></tr>";
    $syoutai_layout .= "<tr><td>来場者氏名カナ</td><td>（例）ヤマダ タロウ</td></tr>";
    $syoutai_layout .= "<tr><td>VIP</td><td>VIPの場合「VIP」と指定</td></tr>";
    $syoutai_layout .= "<tr><td>大分類 <span class='text-danger'>※必須</span></td><td>".$syoutai_big_list."</td></tr>";
    $syoutai_layout .= "<tr><td>中分類 <span class='text-danger'>※必須</span></td><td>".$syoutai_mid_list."</td></tr>";
    $syoutai_layout .= "<tr><td>企業名(企業マスタ) <span class='text-danger'>※必須</span></td><td>（例）株式会社○○○○</td></tr>";
    $syoutai_layout .= "<tr><td>部署 <span class='text-danger'>※必須</span></td><td>（例）食品事業部</td></tr>";
    $syoutai_layout .= "<tr><td>役職</td><td>（例）課長</td></tr>";
    $syoutai_layout .= "<tr><td>来場者メールアドレス</td><td>（例）xxxx@xxx.com ※重複可</td></tr>";
    $syoutai_layout .= "<tr><td>来場者ログインID</td><td>（例）xxxx@xxx.com ※重複不可</td></tr>";
    $syoutai_layout .= "<tr><td>備考</td><td></td></tr>";
    $syoutai_layout .= "</table>";
    $blade->assign('syoutai_layout',$syoutai_layout);

    $blade->assign('line_err',$line_err);
    $blade->assign('syoutai_page', 1);
    $blade->assign('onload_flg', _e2z($onload_flg));

    $contents_title = "来場者マスタ一括登録";
    $active_menu = "syoutai_list";
    $contents_tpl = "syoutai_ikkatsu_touroku";
