<?php
if( !defined("_PROJECT_DISP_NAME") ){
    die("System Error");
}

if( $_SESSION[_PROJECT_NAME]['admin_login']['admin_id'] == "" ){
    die('System Error');
}

if ($_SESSION[_PROJECT_NAME]['admin_login']['admin_kyouryoku_kigyou_flg'] == 1)
{
    die('Permission Denied');
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

if($_request['exec']=="company_csv_upload"){

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
                    $data_arr['company_name']               = trim($csv_row[0]); //企業名
                    $data_arr['company_display_name']       = trim($csv_row[1]); //企業表示名
                    $data_arr['company_name_kana']          = trim($csv_row[2]); //企業名カナ
                    $data_arr['company_big_cate_name']      = trim($csv_row[3]); //WEB登録区分
                    $data_arr['company_daisy']              = trim($csv_row[4]); //DAISY
                    $data_arr['company_web_showcases']      = trim($csv_row[5]); //WEB展示会

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
                        "company_name,(${line}行目) 企業名"                         => "need",
                        "company_display_name,(${line}行目) 企業表示名"             => "need",
                        "company_name_kana,(${line}行目) 企業名カナ"                => "need,zenkana",
                    );
                    $w_err = _check( $chks, $data_arr );

                    if(_count($w_err)==0){

                        //大分類名存在チェック
                        $company_big_cate = "";
                        $wArr = array();
                        foreach ($_conf_big_cate as $key => $value) {
                            $wArr[] = "「".$value."」";
                            if( $value == $data_arr['company_big_cate_name']){
                                $company_big_cate = $key;
                                break;
                            }
                        }
                        if($company_big_cate==""){
                            $w_err[] = "(${line}行目) WEB登録区分の名称は".implode("、", $wArr)."で指定してください。";
                        }
                    }

                    if( _count($w_err) > 0 ){
                        $line_err = _array_merge($line_err, $w_err);
                        $error_line_count++;
                        continue;
                    }else{

                        $array = array();

                        $array['company_name']               = "'"._as($data_arr['company_name'])."'";          //企業名
                        $array['company_display_name']       = "'"._as($data_arr['company_display_name'])."'";  //企業表示名
                        $array['company_name_kana']          = "'"._as($data_arr['company_name_kana'])."'";     //企業名カナ
                        $array['company_big_cate']           = ""._e2n($company_big_cate)."";                   //大分類
                        $array['company_daisy']              = "'"._as($data_arr['company_daisy'])."'";         //DAISY
                        $array['company_web_showcases']      = "'"._as($data_arr['company_web_showcases'])."'"; //WEB展示会

                        _insert( 'm_company', $array);

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

                    _export_company_list(_SYSTEM_ROOT_DIR . '/upfile/company');
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

}

// ******************************************
// 招待者レイアウト
// ******************************************
//大分類
$company_big_list = "";
$wArr = array();
foreach ($_conf_big_cate as $key => $value) {
    $wArr[] = "「".$value."」";
}
$company_big_list = implode("or<br>", $wArr) . "<br>で指定";


$company_layout = "";
$company_layout .= "<table border=\"1\" style=\"width:600px;\">";
$company_layout .= "<tr><th style=\"background-color:#ffeeee;width:250px;\">項目名</th><th style=\"background-color:#ffeeee;width:350px;\">内容</th></tr>";
$company_layout .= "<tr><td>企業名 <span class='text-danger'>※必須</span></td><td>（例）株式会社 日本アクセス</td></tr>";
$company_layout .= "<tr><td>企業表示名 <span class='text-danger'>※必須</span></td><td>（例）(株)日本アクセス</td></tr>";
$company_layout .= "<tr><td>企業名カナ <span class='text-danger'>※必須</span></td><td>（例）ニッポンアクセス</td></tr>";
$company_layout .= "<tr><td>WEB登録区分 <span class='text-danger'>※必須</span></td><td>".$company_big_list."</td></tr>";
$company_layout .= "<tr><td>DAISY</td><td></td></tr>";
$company_layout .= "<tr><td>WEB展示会</td><td></td></tr>";
$company_layout .= "</table>";
$blade->assign('company_layout',$company_layout);

$blade->assign('line_err',$line_err);

$contents_title = "企業マスタ一括登録";
$active_menu = "company_list";
$contents_tpl = "company_ikkatsu_touroku";
