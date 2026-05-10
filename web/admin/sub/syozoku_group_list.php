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

    // ******************************************************************************************************
    // 検索
    // ******************************************************************************************************
    if( $_request['exec'] == "search"){
        unset( $this_sess['search_condition'] );
        $this_sess['search_condition'] = array();
        $this_sess['search_condition'] = _array_merge( $this_sess['search_condition'], $_request );
    }elseif( $_request['offset'] != "" ){
        $this_sess['search_condition']['offset'] = $_request['offset'];
    }elseif( $_request['sess_no_init'] == "" ){
        unset( $this_sess['search_condition'] );
        $this_sess['search_condition']['order_by'] = "szkgrp_id asc";
        $this_sess['search_condition']['hidden_flg'] = "0";
    }

    if( $this_sess['search_condition']['order_by'] == '' ){
        $this_sess['search_condition']['order_by'] = "szkgrp_id asc";
    }

    // ******************************************************************************************************
    // 並び順配列作成
    // ******************************************************************************************************
    // $order_by_arr = array();
    // $order_by_arr['syozoku_id asc'] = "所属ID順";
    // $blade->assign('order_by_arr',$order_by_arr);


    // --------------------------------------------------- //
    // 共通WHERE
    // --------------------------------------------------- //
    $where = "";
    $where .= "szkgrp_delete_date is null";

    // --------------------------------------------------- //
    // 検索条件
    // --------------------------------------------------- //
    if($this_sess['search_condition']['szkgrp_name'] !=""){
        $where .= " and szkgrp_name like '%"._as( $this_sess['search_condition']['szkgrp_name'] )."%'";
    }

    if (isset($this_sess['search_condition']['hidden_flg']) && $this_sess['search_condition']['hidden_flg'] != '') {
        $where .= " and szkgrp_hidden_flg = " ._as( $this_sess['search_condition']['hidden_flg'] );
    }

    // ******************************************************************************************************
    // データ抽出
    // ******************************************************************************************************

    if ($_request['exec'] == "download_csv")
    {
        set_time_limit(180);             //3分起動
        ini_set('memory_limit', "1024M"); //メモリ拡大

        $csv_head = '';
        $csv_head .= '"ID"';
        $csv_head .= ',"閲覧部署グループ名"';
        $csv_head .= ',"コード"';
        $csv_head .= ',"表示状態（0: 表示, 1: 非表示）"';
        $csv_head .= "\r\n";

        $sql = "";
        $sql .= " SELECT * FROM m_syozoku_group ";
        $sql .= " where " . $where;
        $sql .= " order by szkgrp_id asc ";

        $w_flnm = "閲覧部署グループ一覧_" . date("YmdHis") . ".csv";
        header("Content-Disposition: attachment; filename=\"" . mb_convert_encoding($w_flnm, "SJIS-WIN", _ENCODING_SRC) . "\"");
        header("Content-Type: application/octet-stream; name=\"" . $w_flnm . "\"");

        echo mb_convert_encoding($csv_head, "SJIS-WIN", _ENCODING_SRC);
        $result = _query($conn, $sql);

        $row = 0;
        while ($rec = _fetchArray($result, $row))
        {
            $csv_buff = '';
            $csv_buff .= '"' . csvSafe($rec['szkgrp_id']) . '"';
            $csv_buff .= ',"' . csvSafe($rec['szkgrp_name']) . '"';
            $csv_buff .= ',"' . csvSafe($rec['szkgrp_code']) . '"';
            $csv_buff .= ',"' . csvSafe($rec['szkgrp_hidden_flg']) . '"';
            $csv_buff .= "\r\n";

            echo mb_convert_encoding($csv_buff, "SJIS-WIN", _ENCODING_SRC);
            $row++;
        }

        _freeResult($result);
        exit();

    } else if ($_request['exec'] == "upload_csv") {
        setlocale(LC_ALL, 'ja_JP.UTF-8');
        if (is_uploaded_file($_FILES["csv_file"]["tmp_name"])) {
            set_time_limit(180); //3分起動
            ini_set('memory_limit',"1024M"); //メモリ拡大

            $extension  = '.'._get_extension($_FILES['csv_file']['name']);
            $temp_file  = _SYSTEM_ROOT_DIR . "/upfile/new_tmp/" . rand() . $extension;
            $file_move  = move_uploaded_file($_FILES['csv_file']['tmp_name'], $temp_file );

            $buff = mb_convert_encoding(file_get_contents($temp_file), "UTF8", "SJIS-WIN");
            $buff = str_replace("\r\n", "\n", $buff);
            $buff = str_replace("\r", "\n", $buff);

            $fp = fopen($temp_file,"w");
            fwrite($fp, $buff);
            fclose($fp);

            if ($file_move !== false) {
                $fp = fopen($temp_file, "r");
                if ($fp !== false) {
                    _query($conn, "begin");

                    $error_line_count = 0;
                    $update_success   = 0;
                    $line = 0;

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

                        if (count($csv_row) > 4) {
                            $w_err[] = "(${line}行目) フォーマットエラー";
                            $line_err = _array_merge($line_err, $w_err);
                            $error_line_count++;
                            continue;
                        }

                        $data_arr = array();
                        $data_arr['szkgrp_id']         = trim($csv_row[0]); // ID
                        $data_arr['szkgrp_code']       = trim($csv_row[2]); // コード
                        $data_arr['szkgrp_hidden_flg'] = trim($csv_row[3]); // 表示状態
                        unset($csv_row);

                        if ($data_arr['szkgrp_hidden_flg'] != 0 && $data_arr['szkgrp_hidden_flg'] != 1) {
                            $w_err[] = "(${line}行目) 表示状態の値は 0 or 1 で指定してください。";
                        }

                        if ( _count($w_err) == 0 ) {
                            $sql = "";
                            $sql .= " select *"."\n";
                            $sql .= " from m_syozoku_group"."\n";
                            $sql .= " where szkgrp_delete_date is null"."\n";
                            $sql .= " and szkgrp_id = '"._as($data_arr['szkgrp_id'])."'"."\n";
                            $szkgrp_recs = _select( $sql );
                            if ( _count($szkgrp_recs) == 0 ){
                                $w_err[] = "(${line}行目) 該当する閲覧部署グループがありません。";
                            }
                        }

                        if ( _count($w_err) == 0 ) {
                            $sql = "";
                            $sql .= " select *"."\n";
                            $sql .= " from m_syozoku_group"."\n";
                            $sql .= " where szkgrp_code = '"._as($data_arr['szkgrp_code'])."'"."\n";
                            $sql .= " and szkgrp_id != '"._as($data_arr['szkgrp_id'])."'"."\n";
                            $szkgrp_recs = _select( $sql );
                            if ( _count($szkgrp_recs) > 0 ){
                                $w_err[] = "(${line}行目) ご指定のコードはすでに使用されています。";
                            }
                        }

                        if( _count($w_err) > 0 ){
                            $line_err = _array_merge($line_err, $w_err);
                        }

                        if( _count($line_err) > 0 ) {
                            $error_line_count++;
                            continue;
                        } else {
                            $array = array();
                            $array['szkgrp_hidden_flg'] = "'"._as($data_arr['szkgrp_hidden_flg'])."'";
                            $update_where = "szkgrp_id = '"._as($data_arr['szkgrp_id'])."'";

                            _update( 'm_syozoku_group', $array, $update_where);

                            $update_success++;
                        }

                        unset($data_arr); // データ開放
                    }

                    if( $error_line_count > 0 ) {
                        $err_msg[] = 'エラーがあったので登録処理を停止しました。';
                        _query( $conn, "rollback" );

                    }else{
                        if($update_success > 0){
                            $success_msg = "閲覧部署グループの一括登録が完了しました。\n";
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
            }else{
                $err_msg[] = "ファイル移動に失敗しました。";
            }
        } else {
            $err_msg[] = "ファイルが選択されていません。";
        }

    }

    $limit = 50;
    $offset = 0;

    if( $this_sess['search_condition']['offset'] != "" ){
        $offset = intval( $this_sess['search_condition']['offset'] );
    }

    // 件数取得SQL
    $sql  = "";
    $sql .= " select count(m_syozoku_group.szkgrp_id) as all_cnt "."\n";
    $sql .= " from m_syozoku_group"."\n";
    $sql .= " where ".$where;
    $rec = _select($sql);

    $allcnt = 0;
    if($rec[0]['all_cnt'] > 0){
        $allcnt = $rec[0]['all_cnt'];
    }

    if($allcnt > 0){
        // 表示SQL
        $sql  = "";
        $sql .= " select *"."\n";
        $sql .= " from m_syozoku_group"."\n";
//        $sql .= " left join m_syozoku_group on (m_syozoku_group.szkgrp_id = m_syozoku.syozoku_szkgrp_id and m_syozoku_group.szkgrp_delete_date is null)"."\n"; // 2021.05.13 add
//        $sql .= " left join m_tantou_area on (m_tantou_area.tanarea_id = m_syozoku.syozoku_tanarea_id and m_tantou_area.tanarea_delete_date is null)"."\n"; // 2021.05.13 add
        // where句
        $sql .= " where ".$where."\n";
        // order by句
        if( $this_sess['search_condition']['order_by'] != '' ){
            $sql .= " order by ".$this_sess['search_condition']['order_by']."\n";
        }
        $sql .= " limit ".$offset." , ".$limit;
        $main_recs = _select($sql);
    }

    _make_pagenavi2( $blade, $_request, $offset, $allcnt, $limit );

    _setAssign($blade,$this_sess);
    $blade->assign('main_recs', $main_recs);

    $select_hidden = array(
        '' => 'すべて',
        '0' => '表示',
        '1' => '非表示'
    );
    $blade->assign('select_hidden', $select_hidden);

    $blade->assign( 'line_err', $line_err );

    $contents_title = "閲覧部署グループ管理";
    $active_menu = "syozoku_group_list";
    $contents_tpl = "syozoku_group_list";
