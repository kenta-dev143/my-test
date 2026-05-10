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

    $yotei_tims = explode("#", $select_event_rec['event_syoutai_yotei_time']);
    $yotei_tims2 = explode("#", $select_event_rec['event_raijyou_yotei_time']);


    $line_err = array();

    if($_request['exec']=="syoutai_csv_upload"){

        set_time_limit(180); //3分起動
        ini_set('memory_limit',"1024M"); //メモリ拡大

        if($_request['user_tag']!=""){

            $blade->assign('user_tag',$_request['user_tag']);

            setlocale(LC_ALL, 'ja_JP.UTF-8');
            if (is_uploaded_file($_FILES["csv_file"]["tmp_name"])) {
                // if(!file_exists(_SYSTEM_ROOT_DIR . '/upfile/new_tmp')){
                //     @mkdir(_SYSTEM_ROOT_DIR . '/upfile/new_tmp');
                //     if(!file_exists(_SYSTEM_ROOT_DIR . '/upfile/new_tmp')){
                //         exit("ディレクトリの作成に失敗しました");
                //     }
                // }
                $extension = '.'._get_extension($_FILES['csv_file']['name']);
                $temp_file = _SYSTEM_ROOT_DIR . "/upfile/new_tmp/" . rand() . $extension;
                $file_move = move_uploaded_file($_FILES['csv_file']['tmp_name'], $temp_file );

                if ($file_move !== false) {

                    //UTF8化しえ書き直す
                    $buff = mb_convert_encoding(file_get_contents($temp_file), "UTF8", "SJIS-WIN");
                    //$buff = file_get_contents($temp_file);
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
                        $max_recs = _select( "select coalesce(max(substring(user_id,2)),'0') as max_id from m_user");
                        $now_user_id = $max_recs[0]['max_id'];


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

                            $user_raijyou_yotei_time = array();

                            $data_arr = array();
                            $data_arr['admin_mail']              = trim($csv_row[0]); // 担当者メールアドレス
                            $data_arr['user_name']               = trim($csv_row[1]); // 氏名
                            $data_arr['user_name_kana']          = trim($csv_row[2]); // 氏名カナ
                            $data_arr['user_vip_flg']            = strtoupper( trim($csv_row[3]) ); //VIP
                            $data_arr['big_cate_name']           = trim($csv_row[4]); //大分類
                            $data_arr['mid_cate_name']           = trim($csv_row[5]); //中分類
                            $data_arr['user_kigyou_name']        = trim($csv_row[6]); //企業名
                            $data_arr['user_kigyou_name_kana']   = trim($csv_row[7]); //企業名カナ
                            $data_arr['user_busyo']              = trim($csv_row[8]); //部署
                            $data_arr['user_yakusyoku']          = trim($csv_row[9]); //役職
                            $data_arr['user_mail']               = trim($csv_row[10]); //メールアドレス
                            $data_arr['user_login_id']           = trim($csv_row[11]); //ログインID add 2021/04/01
                            $data_arr['user_biko']               = trim($csv_row[12]); //備考

                            $data_arr['user_name_kana'] = mb_convert_kana($data_arr['user_name_kana'],"KV");
                            $data_arr['user_kigyou_name_kana'] = mb_convert_kana($data_arr['user_kigyou_name_kana'],"KV");

                            $data_arr['admin_mail']    = str_replace("‐","-",$data_arr['admin_mail']);
                            $data_arr['user_mail']     = str_replace("‐","-",$data_arr['user_mail']);
                            $data_arr['user_login_id'] = str_replace("‐","-",$data_arr['user_login_id']); // 2021/04/21 add
                            $data_arr['user_vip_flg']  = str_replace("　","",$data_arr['user_vip_flg']);

                            if($data_arr['big_cate_name'] == "メーカー") $data_arr['big_cate_name'] = "メーカー(招待)";
                            if($data_arr['big_cate_name'] == "その他") $data_arr['big_cate_name'] = "その他(招待)";

                            //来場予定日時(yyyy/mm/dd 時間帯など 形式の#区切り)
                            $col = _count($data_arr);
                            for ($i=0; $i < _count($yotei_tims) ; $i++) { 
                                $no = $i + 1;
                                $wrk = str_replace("～", "〜", trim($csv_row[$col]) );
                                if($wrk=="　") $wrk="";
                                $data_arr['user_raijyou_yotei_time_'.$no] = $wrk; 
                                $col++;
                            }

                            $data_arr['user_web']                = trim($csv_row[$col]); //WEB招待（1:WEB招待者）
      
                            unset($csv_row);

                            //ヘッダ行
                            if($line ==1){

                                for ($i=0; $i < _count($yotei_tims) ; $i++) { 
                                    $no = $i + 1;
                                    $y_time = str_replace("～", "〜", trim($yotei_tims[$i]) );
                                    if($data_arr['user_raijyou_yotei_time_'.$no] != $y_time){
                                        $line_err[] = "(ヘッダ行) ".$no."個目の来場予定日時のタイトルが「".$yotei_tims[$i]."」になっていません、当該イベントのCSVではないか、タイトル文字列が間違えています。";
                                    } 
                                }

                                if( _count($line_err) > 0 ) {
                                    $error_line_count++;
                                    $line_err[] = 'ヘッダ行にエラーがあった為、取り込み処理を中断しました。';
                                    break;
                                }

                                continue;
                            }


                            //$line_err = array();

                            // CSV行データチェック開始
                            $chks = array(
                                            "admin_mail,(${line}行目) 担当者メールアドレス"                => "need,email",
                                            "user_name,(${line}行目) 氏名"                                 => "need",
                                            "user_name_kana,(${line}行目) 氏名カナ"                        => "zenkana",
                                            // "user_vip_flg,(${line}行目) VIP"                            => "need",
                                            // "big_cate_name,(${line}行目) 大分類名"                      => "need",
                                            // "mid_cate_name,(${line}行目) 中分類"                        => "need",
                                            "user_kigyou_name,(${line}行目) 企業名"                        => "need",
                                            "user_kigyou_name_kana,(${line}行目) 企業名カナ"               => "zenkana",
                                            // "user_busyo,(${line}行目) 部署"                             => "need",
                                            // "user_yakusyoku,(${line}行目) 役職"                         => "need",
                                            "user_mail,(${line}行目) 招待者メールアドレス"                 => "need,email",
                                            "user_login_id,(${line}行目) 招待者ログインID"                 => "need,email",
                                          );
                            $w_err = _check( $chks, $data_arr );

                            if(_count($w_err)==0){
                                if($data_arr['user_vip_flg'] != "" && $data_arr['user_vip_flg'] != "VIP"){
                                    $w_err[] = "(${line}行目) VIPの値は「VIP」又は空で指定してください。";
                                }

                                //大分類名存在チェック
                                $user_big_cate = "";
                                $wArr = array();
                                foreach ($_conf_big_cate1 as $key => $value) {
                                    $wArr[] = "「".$value."」";
                                    if( $value == $data_arr['big_cate_name']){
                                        $user_big_cate = $key;
                                        break;
                                    }
                                }
                                if($user_big_cate==""){
                                    $w_err[] = "(${line}行目) 大分類の名称は".implode("、", $wArr)."で指定してください。";
                                }

                                //中分類名存在チェック
                                $user_mid_cate = "";
                                $wArr = array();
                                foreach ($_conf_mid_cate1 as $key => $value) {
                                    $wArr[] = "「".$value."」";
                                    if( $value == $data_arr['mid_cate_name']){
                                        $user_mid_cate = $key;
                                        break;
                                    }
                                }
                                if($user_mid_cate==""){
                                    $w_err[] = "(${line}行目) 中分類の名称は".implode("、", $wArr)."で指定してください。";
                                }

                                //招待者メールアドレス存在チェック
                                $sql = "";
                                $sql .= " select user_id"."\n";
                                $sql .= " from v_user"."\n";
                                $sql .= " where "."\n";
                                $sql .= "   user_delete_date is null ";
                                $sql .= "   and user_event_id = '"._as( $_SESSION[_PROJECT_NAME]['select_event_id'] )."'";
                                // 2021/04/01 mod ------------------------ before ----------------------- 大幅変更
                                // $sql .= "   and user_mail = '". _as( $data_arr['user_mail'] ) ."'";
                                // 2021/04/01 mod ------------------------ after ------------------------ 大幅変更
                                $sql .= "   and user_login_id = '". _as( $data_arr['user_login_id'] ) ."'";
                                // 2021/04/01 mod ------------------------ end   ------------------------ 大幅変更
                                $chk_recs = _select($sql);
                                if ( _count($chk_recs) > 0 ){
                                    $w_err[] = "(${line}行目) 招待者ログインIDは別の招待者(or来場者)で登録されています。";
                                }

                                // ID(メアド)から実際のメールアドレス部分抽出
                                $real_user_mail_addr = _getMailAddressFromID( $data_arr['user_login_id'] );
                                // emailアドレスの形式チェック
                                if ( _emailCheck($real_user_mail_addr, '') === false ){
                                    $w_err[]  = "(${line}行目) 招待者ログインIDを正しく入力して下さい。";
                                }

                                //担当者メールアドレス存在チェック
                                $sql = "";
                                $sql .= " select admin_id"."\n";
                                $sql .= " from v_admin"."\n";
                                $sql .= " where "."\n";
                                $sql .= "   admin_delete_date is null ";
                                $sql .= "   and admin_mail = '". _as( $data_arr['admin_mail'] ) ."'";
                                $admin_recs = _select($sql);
                                if ( _count($admin_recs) == 0 ){
                                    $w_err[] = "(${line}行目) 担当者メールアドレスに該当する担当者が担当者マスタに登録されていません。";
                                }

                                for ($i=0; $i < count($yotei_tims) ; $i++) { 
                                    $no = $i + 1;
                                    if( $data_arr['user_raijyou_yotei_time_'.$no] != "" && $data_arr['user_raijyou_yotei_time_'.$no] != "1"){
                                        $w_err[] = "(${line}行目) 来場予定日時(".$yotei_tims[$i].")の値は「1」又は空で指定してください。";
                                    }elseif( $data_arr['user_raijyou_yotei_time_'.$no] == "1"){
                                        $user_raijyou_yotei_time[] = $yotei_tims[$i];
                                    } 
                                }

                                if($data_arr['user_web'] != "" && $data_arr['user_web'] != "1"){
                                    // $w_err[] = "(${line}行目) WEB展示会（ガイドブック）招待の値は「1」又は空で指定してください。";
                                    $w_err[] = "(${line}行目) ".$select_event_rec['event_exhibition_name']."招待の値は「1」又は空で指定してください。";
                                }else{
                                    //2021/07/07 Add ----------- Start ------------
                                    if($data_arr['user_web']=="1"){
                                        if( $user_big_cate!=1 && $user_big_cate!=2 && $user_big_cate!=7 && $user_big_cate!=8){
                                            //WEB招待しているが、大分類が「1:小売、2:外食、7:AC社員、8:その他(来場) でなければエラー
                                            // $err_msg[] = "WEB展示会（ガイドブック）に招待できるのは「(招待者)小売、(招待者)外食、(来場者)AC社員」のみです。";
                                            $w_err[] = "WEB展示会（ガイドブック）に招待できるのは大分類が「小売」と「外食」のみです。";
                                        }
                                    }
                                    //2021/07/07 Add ----------- End ------------
                                }

                            }

                            if( _count($w_err) > 0 ){
                                $line_err = _array_merge($line_err, $w_err);
                                $error_line_count++;
                                continue;
                            }else{

                                $array = array();

                                $now_user_id = $now_user_id + 1;
                                $user_id = sprintf("u%08d", $now_user_id );

                                $array = array();
                                $array_n = array();
                                $array_m = array();

                                $array['user_id']          = "'"._as($user_id)."'";
                                $array['user_event_id']            = "'"._as($_SESSION[_PROJECT_NAME]['select_event_id'])."'"; //イベントID（e0001）',
                                $array['user_admin_id']            = "'"._as($admin_recs[0]['admin_id'])."'"; //担当者ID（a0000001）',
                                if($data_arr['user_vip_flg']==""){
                                    $array['user_vip_flg']             = "0"; //VIPフラグ（1:VIP）',
                                }else{
                                    $array['user_vip_flg']             = "1"; //VIPフラグ（1:VIP）',
                                }
                                
                                $array['user_big_cate']            = ""._e2n($user_big_cate)."";//大分類',
                                $array['user_mid_cate']            = ""._e2n($user_mid_cate).""; //中分類',
                                $array['user_kigyou_name']         = "'"._as($data_arr['user_kigyou_name'])."'"; //企業名',
                                $array['user_kigyou_name_kana']    = "'"._as($data_arr['user_kigyou_name_kana'])."'"; //企業名カナ',
                                $array['user_busyo']               = "'"._as($data_arr['user_busyo'])."'"; //部署',
                                $array['user_yakusyoku']           = "'"._as($data_arr['user_yakusyoku'])."'"; //役職',
                                $array['user_pass']                = "'"._as( md5( "_NEED_PASS_SET_" ) )."'"; //パスワード', 2020.12.18 mod


                                $array['user_raijyou_yotei_time']  = "'"._as( implode("#", $user_raijyou_yotei_time) )."'"; //来場予定日時（yyyy/mm/dd HH:ii 形式）',


                                $array['user_web']                 = ""._e2z($data_arr['user_web']).""; //WEB招待（1:WEB招待者）',
                                $array['user_biko']                = "'"._as($data_arr['user_biko'])."'"; //備考',
                                $array['user_tag']                 = "'"._as($_request['user_tag'])."'"; //ユーザタグ

                                $array['user_update_date']         = "'".$_now_timestamp."'"; //更新日時',
                                $array['user_insert_date']         = "'".$_now_timestamp."'";
                                $array['user_syounin_flg']         = 1; //'WEB招待の承認フラグ(0:未承認、1:承認済み)',

                                $array_n['un_user_id']          = "'"._as($user_id)."'";
                                $array_n['un_user_name']                = "'"._as($data_arr['user_name'])."'"; //'氏名',
                                $array_n['un_user_name_kana']           = "'"._as($data_arr['user_name_kana'])."'"; //氏名カナ',

                                $array_m['um_user_id']          = "'"._as($user_id)."'";
                                // 2021/04/01 mod ------------------------ before ----------------------- 大幅変更
                                // $array_m['um_user_mail']                = "'"._as($data_arr['user_mail'])."'"; //メールアドレス',
                                // 2021/04/01 mod ------------------------ after ------------------------ 大幅変更
                                $array_m['um_user_mail']                = "'"._as($data_arr['user_mail'])."'";     // メールアドレス',
                                $array_m['um_user_login_id']            = "'"._as($data_arr['user_login_id'])."'"; // ログインid',
                                // 2021/04/01 mod ------------------------ end   ------------------------ 大幅変更

                                _insert( 'm_user', $array);
                                _insert( 'm_uname', $array_n);
                                _insert( 'm_umail', $array_m);

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
        } else {
            $err_msg[] = "タグ付け文字列が指定されていません。";
        }

    }elseif($_request['exec']=="raijyou_csv_upload"){

        set_time_limit(180); //3分起動
        ini_set('memory_limit',"1024M"); //メモリ拡大

        if($_request['user_tag2']!=""){

            $blade->assign('user_tag2',$_request['user_tag2']);

            setlocale(LC_ALL, 'ja_JP.UTF-8');
            if (is_uploaded_file($_FILES["csv_file"]["tmp_name"])) {
                // if(!file_exists(_SYSTEM_ROOT_DIR . '/upfile/new_tmp')){
                //     @mkdir(_SYSTEM_ROOT_DIR . '/upfile/new_tmp');
                //     if(!file_exists(_SYSTEM_ROOT_DIR . '/upfile/new_tmp')){
                //         exit("ディレクトリの作成に失敗しました");
                //     }
                // }
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
                        $max_recs = _select( "select coalesce(max(substring(user_id,2)),'0') as max_id from m_user");
                        $now_user_id = $max_recs[0]['max_id'];


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

                            $user_raijyou_yotei_time = array();

                            $data_arr = array();
                            $data_arr['admin_mail']              = trim($csv_row[0]); // 担当者メールアドレス
                            $data_arr['user_name']               = trim($csv_row[1]); // 氏名
                            $data_arr['user_name_kana']          = trim($csv_row[2]); // 氏名カナ
                            $data_arr['user_vip_flg']            = strtoupper( trim($csv_row[3]) ); //VIP
                            $data_arr['big_cate_name']           = trim($csv_row[4]); //大分類
                            $data_arr['mid_cate_name']           = trim($csv_row[5]); //中分類
                            $data_arr['user_kigyou_name']        = trim($csv_row[6]); //企業名
                            $data_arr['user_kigyou_name_kana']   = trim($csv_row[7]); //企業名カナ
                            $data_arr['user_busyo']              = trim($csv_row[8]); //部署
                            $data_arr['user_yakusyoku']          = trim($csv_row[9]); //役職
                            $data_arr['user_mail']               = trim($csv_row[10]); //メールアドレス
                            $data_arr['user_login_id']           = trim($csv_row[11]); //ログインID 2021/04/01 add
                            $data_arr['user_biko']               = trim($csv_row[12]); //備考

                            $data_arr['user_name_kana'] = mb_convert_kana($data_arr['user_name_kana'],"KV");
                            $data_arr['user_kigyou_name_kana'] = mb_convert_kana($data_arr['user_kigyou_name_kana'],"KV");

                            $data_arr['admin_mail']    = str_replace("‐","-",$data_arr['admin_mail']);
                            $data_arr['user_mail']     = str_replace("‐","-",$data_arr['user_mail']);
                            $data_arr['user_login_id'] = str_replace("‐","-",$data_arr['user_login_id']);
                            $data_arr['user_vip_flg']  = str_replace("　","",$data_arr['user_vip_flg']);

                            if($data_arr['big_cate_name'] == "メーカー") $data_arr['big_cate_name'] = "メーカー(出展)";
                            if($data_arr['big_cate_name'] == "その他") $data_arr['big_cate_name'] = "その他(来場)";

                            //来場予定日時(yyyy/mm/dd 時間帯など 形式の#区切り)
                            $col = _count($data_arr);
                            for ($i=0; $i < _count($yotei_tims2) ; $i++) { 
                                $no = $i + 1;
                                $wrk = str_replace("～", "〜", trim($csv_row[$col]) );
                                if($wrk=="　") $wrk="";
                                $data_arr['user_raijyou_yotei_time_'.$no] = $wrk; 
                                $col++;
                            }

                            $data_arr['user_web']                = trim($csv_row[$col]); //WEB招待（1:WEB招待者）
      
                            unset($csv_row);

                            //ヘッダ行
                            if($line ==1){

                                for ($i=0; $i < _count($yotei_tims2) ; $i++) { 
                                    $no = $i + 1;
                                    $y_time = str_replace("～", "〜", trim($yotei_tims2[$i]) );
                                    if($data_arr['user_raijyou_yotei_time_'.$no] != $y_time){
                                        $line_err[] = "(ヘッダ行) ".$no."個目の来場予定日時のタイトルが「".$yotei_tims2[$i]."」になっていません、当該イベントのCSVではないか、タイトル文字列が間違えています。";
                                    }
                                }

                                if( _count($line_err) > 0 ) {
                                    $error_line_count++;
                                    $line_err[] = 'ヘッダ行にエラーがあった為、取り込み処理を中断しました。';
                                    break;
                                }

                                continue;
                            }


                            $w_err = array();

                            // CSV行データチェック開始
                            $chks = array(
                                            "admin_mail,(${line}行目) 担当者メールアドレス"                => "need,email",
                                            "user_name,(${line}行目) 氏名"                                 => "need",
                                            "user_name_kana,(${line}行目) 氏名カナ"                        => "zenkana",
                                            // "user_vip_flg,(${line}行目) VIP"                            => "need",
                                            // "big_cate_name,(${line}行目) 大分類名"                      => "need",
                                            // "mid_cate_name,(${line}行目) 中分類"                        => "need",
                                            "user_kigyou_name,(${line}行目) 企業名"                        => "need",
                                            "user_kigyou_name_kana,(${line}行目) 企業名カナ"               => "zenkana",
                                            // "user_busyo,(${line}行目) 部署"                             => "need",
                                            // "user_yakusyoku,(${line}行目) 役職"                         => "need",
                                            "user_mail,(${line}行目) 来場者メールアドレス"                 => "need,email",
                                            "user_login_id,(${line}行目) 来場者ログインID"                 => "need,email",
                                          );
                            $w_err = _check( $chks, $data_arr );

                            if(_count($w_err)==0){
                                if($data_arr['user_vip_flg'] != "" && $data_arr['user_vip_flg'] != "VIP"){
                                    $w_err[] = "(${line}行目) VIPの値は「VIP」又は空で指定してください。";
                                }

                                //大分類名存在チェック
                                $user_big_cate = "";
                                $wArr = array();
                                foreach ($_conf_big_cate2 as $key => $value) {
                                    $wArr[] = "「".$value."」";
                                    if( $value == $data_arr['big_cate_name']){
                                        $user_big_cate = $key;
                                        break;
                                    }
                                }
                                if($user_big_cate==""){
                                    $w_err[] = "(${line}行目) 大分類の名称は".implode("、", $wArr)."で指定してください。";
                                }

                                //中分類名存在チェック
                                $user_mid_cate = "";
                                $wArr = array();
                                foreach ($_conf_mid_cate2 as $key => $value) {
                                    $wArr[] = "「".$value."」";
                                    if( $value == $data_arr['mid_cate_name']){
                                        $user_mid_cate = $key;
                                        break;
                                    }
                                }
                                if($user_mid_cate==""){
                                    $w_err[] = "(${line}行目) 中分類の名称は".implode("、", $wArr)."で指定してください。";
                                }

                                //来場者ログインID存在チェック
                                $sql = "";
                                $sql .= " select user_id"."\n";
                                $sql .= " from v_user"."\n";
                                $sql .= " where "."\n";
                                $sql .= "   user_delete_date is null ";
                                $sql .= "   and user_event_id = '"._as( $_SESSION[_PROJECT_NAME]['select_event_id'] )."'";
                                // 2021/04/01 mod ------------------------ before ----------------------- 大幅変更
                                // $sql .= "   and user_mail = '". _as( $data_arr['user_mail'] ) ."'";
                                // 2021/04/01 mod ------------------------ after ------------------------ 大幅変更
                                $sql .= "   and user_login_id = '". _as( $data_arr['user_login_id'] ) ."'";
                                // 2021/04/01 mod ------------------------ end   ------------------------ 大幅変更

                                $chk_recs = _select($sql);
                                if ( _count($chk_recs) > 0 ){
                                    $w_err[] = "(${line}行目) 来場者ログインIDは別の招待者(or来場者)で登録されています。";
                                }

                                // ID(メアド)から実際のメールアドレス部分抽出
                                $real_user_mail_addr = _getMailAddressFromID( $data_arr['user_login_id'] );
                                // emailアドレスの形式チェック
                                if ( _emailCheck($real_user_mail_addr, '') === false ){
                                    $w_err[]  = "(${line}行目) 来場者ログインIDを正しく入力して下さい。";
                                }

                                //担当者メールアドレス存在チェック
                                $sql = "";
                                $sql .= " select admin_id"."\n";
                                $sql .= " from v_admin"."\n";
                                $sql .= " where "."\n";
                                $sql .= "   admin_delete_date is null ";
                                $sql .= "   and admin_mail = '". _as( $data_arr['admin_mail'] ) ."'";
                                $admin_recs = _select($sql);
                                if ( _count($admin_recs) == 0 ){
                                    $w_err[] = "(${line}行目) 担当者メールアドレスに該当する担当者が担当者マスタに登録されていません。";
                                }

                                for ($i=0; $i < _count($yotei_tims2) ; $i++) { 
                                    $no = $i + 1;
                                    if( $data_arr['user_raijyou_yotei_time_'.$no] != "" && $data_arr['user_raijyou_yotei_time_'.$no] != "1"){
                                        $w_err[] = "(${line}行目) 来場予定日時(".$yotei_tims2[$i].")の値は「1」又は空で指定してください。";
                                    }elseif( $data_arr['user_raijyou_yotei_time_'.$no] == "1"){
                                        $user_raijyou_yotei_time[] = $yotei_tims2[$i];
                                    } 
                                }

                                if($data_arr['user_web'] != "" && $data_arr['user_web'] != "1"){
                                    $w_err[] = "(${line}行目) ".$select_event_rec['event_exhibition_name']."招待の値は「1」又は空で指定してください。";
                                }else{
                                    //2021/07/07 Add ----------- Start ------------
                                    if($data_arr['user_web']=="1"){
                                        if( $user_big_cate!=1 && $user_big_cate!=2 && $user_big_cate!=7 && $user_big_cate!=8){
                                            //WEB招待しているが、大分類が「1:小売、2:外食、7:AC社員、8:その他(来場) でなければエラー
                                            // $err_msg[] = "WEB展示会（ガイドブック）に招待できるのは「(招待者)小売、(招待者)外食、(来場者)AC社員」のみです。";
                                            $w_err[] = "WEB展示会（ガイドブック）に招待できるのは大分類が「AC社員」と「その他(来場)」のみです。";
                                        }
                                    }
                                    //2021/07/07 Add ----------- End ------------
                                }

                            }
                            if( _count($w_err) > 0 ){
                                $line_err = _array_merge($line_err, $w_err);
                                $error_line_count++;
                                continue;
                            }else{

                                $array = array();

                                $now_user_id = $now_user_id + 1;
                                $user_id = sprintf("u%08d", $now_user_id );

                                $array = array();
                                $array_n = array();
                                $array_m = array();

                                $array['user_id']          = "'"._as($user_id)."'";
                                $array['user_event_id']            = "'"._as($_SESSION[_PROJECT_NAME]['select_event_id'])."'"; //イベントID（e0001）',
                                $array['user_admin_id']            = "'"._as($admin_recs[0]['admin_id'])."'"; //担当者ID（a0000001）',
                                if($data_arr['user_vip_flg']==""){
                                    $array['user_vip_flg']             = "0"; //VIPフラグ（1:VIP）',
                                }else{
                                    $array['user_vip_flg']             = "1"; //VIPフラグ（1:VIP）',
                                }
                                
                                $array['user_big_cate']            = ""._e2n($user_big_cate)."";//大分類',
                                $array['user_mid_cate']            = ""._e2n($user_mid_cate).""; //中分類',
                                $array['user_kigyou_name']         = "'"._as($data_arr['user_kigyou_name'])."'"; //企業名',
                                $array['user_kigyou_name_kana']    = "'"._as($data_arr['user_kigyou_name_kana'])."'"; //企業名カナ',
                                $array['user_busyo']               = "'"._as($data_arr['user_busyo'])."'"; //部署',
                                $array['user_yakusyoku']           = "'"._as($data_arr['user_yakusyoku'])."'"; //役職',
                                $array['user_pass']                = "'"._as( md5( "_NEED_PASS_SET_" ) )."'"; //パスワード', 2020.12.18 mod


                                $array['user_raijyou_yotei_time']  = "'"._as( implode("#", $user_raijyou_yotei_time) )."'"; //来場予定日時（yyyy/mm/dd HH:ii 形式）',


                                $array['user_web']                 = ""._e2z($data_arr['user_web']).""; //WEB招待（1:WEB招待者）',
                                $array['user_biko']                = "'"._as($data_arr['user_biko'])."'"; //備考',
                                $array['user_tag']                 = "'"._as($_request['user_tag2'])."'"; //ユーザタグ

                                $array['user_update_date']         = "'".$_now_timestamp."'"; //更新日時',
                                $array['user_insert_date']         = "'".$_now_timestamp."'";
                                $array['user_syounin_flg']         = 1; //'WEB招待の承認フラグ(0:未承認、1:承認済み)',

                                $array_n['un_user_id']          = "'"._as($user_id)."'";
                                $array_n['un_user_name']                = "'"._as($data_arr['user_name'])."'"; //'氏名',
                                $array_n['un_user_name_kana']           = "'"._as($data_arr['user_name_kana'])."'"; //氏名カナ',

                                $array_m['um_user_id']          = "'"._as($user_id)."'";
                                // 2021/04/01 mod ------------------------ before ----------------------- 大幅変更
                                // $array_m['um_user_mail']                = "'"._as($data_arr['user_mail'])."'"; //メールアドレス',
                                // 2021/04/01 mod ------------------------ after ------------------------ 大幅変更
                                $array_m['um_user_mail']                = "'"._as($data_arr['user_mail'])."'";    // メールアドレス',
                                $array_m['um_user_login_id']            = "'"._as($data_arr['user_login_id'])."'"; // ログインid',
                                // 2021/04/01 mod ------------------------ end   ------------------------ 大幅変更

                                _insert( 'm_user', $array);
                                _insert( 'm_uname', $array_n);
                                _insert( 'm_umail', $array_m);

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
        } else {
            $err_msg[] = "タグ付け文字列が指定されていません。";
        }

    }elseif($_request['exec'] == 'syoutai_tpl_download'){

        $csv_head = '';
        $csv_head .= '"担当者メールアドレス"';
        $csv_head .= ',"招待者氏名"';
        $csv_head .= ',"招待者氏名カナ"';
        $csv_head .= ',"VIP"';
        $csv_head .= ',"大分類"';
        $csv_head .= ',"中分類"';
        $csv_head .= ',"企業名"';
        $csv_head .= ',"企業名カナ"';
        $csv_head .= ',"部署"';
        $csv_head .= ',"役職"';
        $csv_head .= ',"メールアドレス"';
        $csv_head .= ',"ログインID"';
        $csv_head .= ',"備考"';
        //来場予定日時(yyyy/mm/dd 時間帯など 形式の#区切り)
        $col = _count($data_arr);
        for ($i=0; $i < _count($yotei_tims) ; $i++) { 
            $csv_head .= ',"'.$yotei_tims[$i].'"';
        }

        $csv_head .= ',"WEB招待（1:WEB招待者）"';

        $csv_head .= "\r\n";

        $w_flnm = "招待者一括アップロードテンプレート.csv";
        header("Content-Disposition: attachment; filename=\"".mb_convert_encoding( $w_flnm, "SJIS-WIN" , _ENCODING_SRC )."\"");
        header("Content-Type: application/octet-stream; name=\"".$w_flnm."\"");

        echo mb_convert_encoding( $csv_head, "SJIS-WIN" , _ENCODING_SRC );
        exit();

    }elseif($_request['exec'] == 'raijyou_tpl_download'){

        $csv_head = '';
        $csv_head .= '"担当者メールアドレス"';
        $csv_head .= ',"来場者氏名"';
        $csv_head .= ',"来場者氏名カナ"';
        $csv_head .= ',"VIP"';
        $csv_head .= ',"大分類"';
        $csv_head .= ',"中分類"';
        $csv_head .= ',"企業名"';
        $csv_head .= ',"企業名カナ"';
        $csv_head .= ',"部署"';
        $csv_head .= ',"役職"';
        $csv_head .= ',"メールアドレス"';
        $csv_head .= ',"ログインID"';
        $csv_head .= ',"備考"';
        //来場予定日時(yyyy/mm/dd 時間帯など 形式の#区切り)
        $col = _count($data_arr);
        for ($i=0; $i < _count($yotei_tims2) ; $i++) { 
            $csv_head .= ',"'.$yotei_tims2[$i].'"';
        }

        $csv_head .= ',"WEB招待（1:WEB招待者）"';

        $csv_head .= "\r\n";

        $w_flnm = "来場者一括アップロードテンプレート.csv";
        header("Content-Disposition: attachment; filename=\"".mb_convert_encoding( $w_flnm, "SJIS-WIN" , _ENCODING_SRC )."\"");
        header("Content-Type: application/octet-stream; name=\"".$w_flnm."\"");

        echo mb_convert_encoding( $csv_head, "SJIS-WIN" , _ENCODING_SRC );
        exit();

    }

    // ******************************************
    // 招待者レイアウト
    // ******************************************
    //大分類
    $syoutai_big_list = "";
    $wArr = array();
    foreach ($_conf_big_cate1 as $key => $value) {
        $wArr[] = "「".$value."」";
    }
    $syoutai_big_list = implode("or<br>", $wArr) . "<br>で指定";

    //中分類
    $syoutai_mid_list = "";
    $wArr = array();
    foreach ($_conf_mid_cate1 as $key => $value) {
        $wArr[] = "「".$value."」";
    }
    $syoutai_mid_list = implode("or<br>", $wArr) . "<br>で指定";


    $syoutai_layout = "";
    $syoutai_layout .= "<table border=\"1\" style=\"width:500px;\">";
    $syoutai_layout .= "<tr><th style=\"background-color:#ffeeee;width:250px;\">項目名</th><th style=\"background-color:#ffeeee;width:250px;\">内容</th></tr>";
    $syoutai_layout .= "<tr><td>担当者メールアドレス</td><td>（例）xxxx@nippon-access.co.jp</td></tr>";
    $syoutai_layout .= "<tr><td>招待者氏名</td><td>（例）山田 太郎</td></tr>";
    $syoutai_layout .= "<tr><td>招待者氏名カナ</td><td>（例）ヤマダ タロウ</td></tr>";
    $syoutai_layout .= "<tr><td>VIP</td><td>VIPの場合「VIP」と指定</td></tr>";
    $syoutai_layout .= "<tr><td>大分類</td><td>".$syoutai_big_list."</td></tr>";
    $syoutai_layout .= "<tr><td>中分類</td><td>".$syoutai_mid_list."</td></tr>";
    $syoutai_layout .= "<tr><td>企業名</td><td>（例）株式会社○○○○</td></tr>";
    $syoutai_layout .= "<tr><td>企業名カナ</td><td>（例）カブシキガイシャ○○○○</td></tr>";
    $syoutai_layout .= "<tr><td>部署</td><td>（例）食品事業部</td></tr>";
    $syoutai_layout .= "<tr><td>役職</td><td>（例）課長</td></tr>";
    $syoutai_layout .= "<tr><td>メールアドレス</td><td>（例）xxxx@xxx.com ※重複可</td></tr>";
    $syoutai_layout .= "<tr><td>ログインID</td><td>（例）xxxx@xxx.com ※重複不可</td></tr>";
    $syoutai_layout .= "<tr><td>備考</td><td></td></tr>";
    //来場予定日時(yyyy/mm/dd 時間帯など 形式の#区切り)
    $col = _count($data_arr);
    for ($i=0; $i < _count($yotei_tims) ; $i++) { 
        $syoutai_layout .= "<tr><td>".$yotei_tims[$i]."</td><td>来場予定の場合「1」を指定</td></tr>";;
    }

    $syoutai_layout .= "<tr><td>WEB招待</td><td>".$select_event_rec['event_exhibition_name']."招待の場合「1」を指定</td></tr>";
    $syoutai_layout .= "</table>";
    $blade->assign('syoutai_layout',$syoutai_layout);


    // ******************************************
    // 来場者レイアウト
    // ******************************************
    //大分類
    $raijyou_big_list = "";
    $wArr = array();
    foreach ($_conf_big_cate2 as $key => $value) {
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
    $raijyou_layout .= "<tr><td>担当者メールアドレス</td><td>（例）xxxx@nippon-access.co.jp</td></tr>";
    $raijyou_layout .= "<tr><td>来場者氏名</td><td>（例）山田 太郎</td></tr>";
    $raijyou_layout .= "<tr><td>来場者氏名カナ</td><td>（例）ヤマダ タロウ</td></tr>";
    $raijyou_layout .= "<tr><td>VIP</td><td>VIPの場合「VIP」と指定</td></tr>";
    $raijyou_layout .= "<tr><td>大分類</td><td>".$raijyou_big_list."</td></tr>";
    $raijyou_layout .= "<tr><td>中分類</td><td>".$raijyou_mid_list."</td></tr>";
    $raijyou_layout .= "<tr><td>企業名</td><td>（例）株式会社○○○○</td></tr>";
    $raijyou_layout .= "<tr><td>企業名カナ</td><td>（例）カブシキガイシャ○○○○</td></tr>";
    $raijyou_layout .= "<tr><td>部署</td><td>（例）食品事業部</td></tr>";
    $raijyou_layout .= "<tr><td>役職</td><td>（例）課長</td></tr>";
    $raijyou_layout .= "<tr><td>メールアドレス</td><td>（例）xxxx@xxx.com ※重複可</td></tr>";
    $raijyou_layout .= "<tr><td>ログインID</td><td>（例）xxxx@xxx.com ※重複不可</td></tr>";
    $raijyou_layout .= "<tr><td>備考</td><td></td></tr>";
    //来場予定日時(yyyy/mm/dd 時間帯など 形式の#区切り)
    $col = _count($data_arr);
    for ($i=0; $i < _count($yotei_tims2) ; $i++) { 
        $raijyou_layout .= "<tr><td>".$yotei_tims2[$i]."</td><td>来場予定の場合「1」を指定</td></tr>";;
    }

    $raijyou_layout .= "<tr><td>WEB招待</td><td>".$select_event_rec['event_exhibition_name']."招待の場合「1」を指定</td></tr>";
    $raijyou_layout .= "</table>";
    $blade->assign('raijyou_layout',$raijyou_layout);
    $blade->assign('line_err',$line_err);

    $blade->assign('title_bgcolor',$select_event_rec['event_title_bgcolor']);

    $contents_title = "来場者一括登録" . "（".$select_event_rec['event_name'] .":".$select_event_rec['event_kaijyou_name']."）";
    $active_menu = "user_list";
    $contents_tpl = "user_ikkatsu_touroku";
