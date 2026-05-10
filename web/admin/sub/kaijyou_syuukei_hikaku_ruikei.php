<?php
    if( !defined("_PROJECT_DISP_NAME") ){
        die("System Error");
    }

    if( $_SESSION[_PROJECT_NAME]['admin_login']['admin_id'] == "" ){
        die('System Error 1');
    }
    if( $_SESSION[_PROJECT_NAME]['admin_login']['admin_syuukei_etsuran_kengen'] == 1 ){
        die('System Error 2');
    }

    // ******************************************************************************************************
    // 初期値
    // ******************************************************************************************************
    $page = $_request['page'];
    $this_sess = &$_SESSION[_PROJECT_NAME][$page];
    $err_msg = array();
    $error_pattern = '';

    $m_tantou_area = [];
    $event_summary = [];
    $area_summary = [];

    unset( $this_sess['search_condition'] );

    // ******************************************************************************************************
    // 検索
    // ******************************************************************************************************
    if($_request['exec'] == "search" || $_request['exec'] == "download_csv") {
        unset( $this_sess['search_condition'] );
        $this_sess['search_condition'] = array();
        $this_sess['search_condition'] = _array_merge( $this_sess['search_condition'], $_request );
    }

    if($_SESSION[_PROJECT_NAME]['select_event_id']==""){
        $contents_title = "会場全体集計比較（累計）";
        $error_pattern = 1;
    } else if (!is_null($select_event_rec) && empty($select_event_rec['event_compare_event_id'])) {
        $contents_title = "会場全体集計比較（累計）" . "（".$select_event_rec['event_name'] .":".$select_event_rec['event_kaijyou_name']."）";
        $error_pattern = 2;
    }else{
        $contents_title = "会場全体集計比較（累計）" . "（".$select_event_rec['event_name'] .":".$select_event_rec['event_kaijyou_name']."）";

        // イベント招待者開催期間
        $event_syoutai_yotei_ymd_array = [];

        // イベント招待者開催期間を取得
        $sql = 'select * from m_event where event_delete_date is null';
        $sql .= " and event_id ='"._as($_SESSION[_PROJECT_NAME]['select_event_id'])."'";
        $recs = _select($sql);
        if (count($recs) == 0) {
            _dispError();
        }
        $event = $recs[0];

        // イベント招待者開催期間の年月日をユニークにして取得
        $event_syoutai_yotei_time = $event['event_syoutai_yotei_time'];
        $array = explode('#', $event_syoutai_yotei_time);
        foreach($array as $row) {
            $value = explode(' ', $row);
            if (!isset($value[0]) || $value[0] == '') continue;
            $d = new DateTime($value[0]);
            if ($d->format('Y') == 2999) continue;
            $event_syoutai_yotei_ymd_array[] = $value[0];
        }
        $event_syoutai_yotei_ymd_array = array_unique($event_syoutai_yotei_ymd_array);
        $blade->assign('event_syoutai_yotei_ymd_array', $event_syoutai_yotei_ymd_array);

        // 比較対象のイベント招待者開催期間
        $compare_event_syoutai_yotei_ymd_array = [];

        // 比較対象のイベント
        $sql = 'select * from m_event where event_delete_date is null';
        $sql .= " and event_id ='". _as($select_event_rec['event_compare_event_id']) ."'";
        $recs = _select($sql);
        if (count($recs) == 0) {
            _dispError();
        }
        $compare_event = $recs[0];
        $blade->assign('compare_event',$compare_event);

        // 比較対象のイベント招待者開催期間の年月日をユニークにして取得
        $event_syoutai_yotei_time = $compare_event['event_syoutai_yotei_time'];
        $array = explode('#', $event_syoutai_yotei_time);
        foreach($array as $row) {
            $value = explode(' ', $row);
            if (!isset($value[0]) || $value[0] == '') continue;
            $d = new DateTime($value[0]);
            if ($d->format('Y') == 2999) continue;
            $compare_event_syoutai_yotei_ymd_array[] = $value[0];
        }
        $compare_event_syoutai_yotei_ymd_array = array_unique($compare_event_syoutai_yotei_ymd_array);
        $blade->assign('compare_event_syoutai_yotei_ymd_array', $compare_event_syoutai_yotei_ymd_array);

        // (招待者)来場予定日時の設定が行なわれていない場合
        if (count($event_syoutai_yotei_ymd_array) == 0 || count($compare_event_syoutai_yotei_ymd_array) == 0) {
            $error_pattern = 3;
        }
        else {
            // ******************************************************************************************************
            // 大分類別-入場者数
            // ******************************************************************************************************
            $event_summary = [
                'select' => [
                    'name' => $select_event_rec['event_pulldown_name'],
                    'id' => $_SESSION[_PROJECT_NAME]['select_event_id'],
                    'ymd' => $event_syoutai_yotei_ymd_array,
                    'is_archived' => $select_event_rec['event_archived_flg'],
                    'summary' => [],
                ],
                'compare' => [
                    'name' => $compare_event['event_pulldown_name'],
                    'id' => $select_event_rec['event_compare_event_id'],
                    'ymd' => $compare_event_syoutai_yotei_ymd_array,
                    'is_archived' => $compare_event['event_archived_flg'],
                    'summary' => [],
                ],
            ];

            foreach ($event_summary as $key => $event) {
                $table_kaijyou_inout = 't_kaijyou_inout';
                $table_user = 'm_user';

                if ($event['is_archived'] == '1')
                {
                    $table_kaijyou_inout = 'a_kaijyou_inout';
                    $table_user = 'a_user';
                }

                $category_total = [];
                $category_total['time'] = '合計';
                foreach ($_conf_big_cate1 as $bcate1_id => $bcate1_name) {
                    $category_total[$bcate1_id] = 0;
                }
                foreach ($_conf_big_cate2 as $bcate2_id => $bcate2_name) {
                    $category_total[$bcate2_id] = 0;
                }
                $category_total['syoutai_total'] = 0;
                $category_total['raijyou_total'] = 0;
                $category_total['total'] = 0;

                foreach ($_conf_jikantai as $time_key => $time_value) {
                    $rec = array();
                    $rec['time'] = $time_value;
                    foreach ($_conf_big_cate1 as $bcate1_id => $bcate1_name) {
                        $rec[$bcate1_id] = 0;
                    }
                    foreach ($_conf_big_cate2 as $bcate2_id => $bcate2_name) {
                        $rec[$bcate2_id] = 0;
                    }
                    $rec['syoutai_total'] = 0;
                    $rec['raijyou_total'] = 0;
                    $rec['total'] = 0;

                    list($from,$to) = explode("-",$time_key);

                    $sql = "";
                    $sql .= "select";
                    $sql .= "  user_big_cate";
                    $sql .= " ,count(kinout_id) as cnt";
                    $sql .= " from $table_kaijyou_inout";
                    $sql .= " join $table_user on ($table_user.user_id = $table_kaijyou_inout.kinout_user_id)";
                    $sql .= " where";
                    $sql .= " kinout_delete_date is null";
                    $sql .= " and kinout_event_id = '"._as($event['id'])."'";
                    $sql .= " and kinout_fst_record = 1"; //初回レコードのみ対象に
                    $sql .= " and kinout_time_in is not null";
                    $sql .= " and kinout_time_in != ''";

                    $array = [];
                    foreach($event['ymd'] as $ymd) {
                        $s = "(kinout_time_in >= '".$ymd." ".$from.":00'";
                        $s .= " and kinout_time_in < '".$ymd." ".$to.":00')";
                        $array[] = $s;
                    }
                    $sql .= ' and ( ' . implode(' OR ', $array) . ' ) ';

                    $sql .= " group by user_big_cate";
                    $wRecs = _select($sql);

                    foreach ($wRecs as $row) {
                        $rec[$row['user_big_cate']] += intval($row['cnt']);
                        $category_total[$row['user_big_cate']] += intval($row['cnt']);

                        if (array_key_exists($row['user_big_cate'], $_conf_big_cate1)) {
                            $rec['syoutai_total'] += intval($row['cnt']);
                            $category_total['syoutai_total'] += intval($row['cnt']);
                        }
                        else {
                            $rec['raijyou_total'] += intval($row['cnt']);
                            $category_total['raijyou_total'] +=  intval($row['cnt']);
                        }
                        $rec['total'] += intval($row['cnt']);
                        $category_total['total'] += intval($row['cnt']);
                    }
                    $event_summary[$key]['summary'][] = $rec;
                }
                $event_summary[$key]['summary'][] = $category_total;
            }

            // ******************************************************************************************************
            // 担当エリア別-入場者数（招待者）
            // ******************************************************************************************************
            $area_summary = [
                'select' => [
                    'name' => $select_event_rec['event_pulldown_name'],
                    'id' => $_SESSION[_PROJECT_NAME]['select_event_id'],
                    'ymd' => $event_syoutai_yotei_ymd_array,
                    'is_archived' => $select_event_rec['event_archived_flg'],
                    'summary' => [],
                ],
                'compare' => [
                    'name' => $compare_event['event_pulldown_name'],
                    'id' => $select_event_rec['event_compare_event_id'],
                    'ymd' => $compare_event_syoutai_yotei_ymd_array,
                    'is_archived' => $compare_event['event_archived_flg'],
                    'summary' => [],
                ],
            ];

            $sql = 'select * from m_tantou_area where tanarea_delete_date is null order by tanarea_id';
            $wRecs = _select($sql);
            foreach ($wRecs as $row) {
                $m_tantou_area[$row['tanarea_id']] = $row['tanarea_name'];
            }

            foreach ($area_summary as $key => $event) {
                $table_kaijyou_inout = 't_kaijyou_inout';
                $table_user = 'm_user';

                if ($event['is_archived'] == '1')
                {
                    $table_kaijyou_inout = 'a_kaijyou_inout';
                    $table_user = 'a_user';
                }

                $category_total = [];
                $category_total['time'] = '合計';
                $category_total['total'] = 0;
                foreach ($m_tantou_area as $id => $name) {
                    $category_total[$id] = 0;
                }

                foreach ($_conf_jikantai as $time_key => $time_value) {
                    $rec = array();
                    $rec['time'] = $time_value;
                    $rec['total'] = 0;
                    foreach ($m_tantou_area as $id => $name) {
                        $rec[$id] = 0;
                    }

                    list($from,$to) = explode("-",$time_key);

                    $sql = "";
                    $sql .= "select";
                    $sql .= "  m_tantou_area.tanarea_id";
                    $sql .= " ,count(kinout_id) as cnt";
                    $sql .= " from $table_kaijyou_inout";
                    $sql .= " join $table_user on ($table_user.user_id = $table_kaijyou_inout.kinout_user_id)";
                    $sql .= " join m_admin on (m_admin.admin_id = $table_user.user_admin_id)";
                    $sql .= " join m_tantou_area on (m_tantou_area.tanarea_id = m_admin.admin_tanarea_id)";
                    $sql .= " where";
                    $sql .= " kinout_delete_date is null";
                    $sql .= " and kinout_event_id = '"._as($event['id'])."'";
                    $sql .= " and kinout_fst_record = 1"; //初回レコードのみ対象に
                    $sql .= " and kinout_time_in is not null";
                    $sql .= " and kinout_time_in != ''";

                    $array = [];
                    foreach($event['ymd'] as $ymd) {
                        $s = "(kinout_time_in >= '".$ymd." ".$from.":00'";
                        $s .= " and kinout_time_in < '".$ymd." ".$to.":00')";
                        $array[] = $s;
                    }
                    $sql .= ' and ( ' . implode(' OR ', $array) . ' ) ';

                    $sql .= " and user_big_cate in (".implode(',', array_keys($_conf_big_cate1)).")";
                    $sql .= " group by m_tantou_area.tanarea_name";
                    $wRecs = _select($sql);

                    foreach ($wRecs as $row) {
                        $rec[$row['tanarea_id']] += intval($row['cnt']);
                        $category_total[$row['tanarea_id']] += intval($row['cnt']);

                        $rec['total'] += intval($row['cnt']);
                        $category_total['total'] += intval($row['cnt']);
                    }
                    $area_summary[$key]['summary'][] = $rec;
                }
                $area_summary[$key]['summary'][] = $category_total;
            }

            if ($_request['exec'] == "download_csv") {

                $ratio_calculation = function($from, $to) {
                    $calc = 0;
                    if ($to != 0) {
                        $calc = ($from / $to) * 100;
                        $calc = round($calc);
                    }
                    return $calc;
                };

                $echo = function($s) {
                    echo mb_convert_encoding($s, 'SJIS-WIN', _ENCODING_SRC);
                };

                $csv_head = '';
                $csv_bufff = '';

                set_time_limit(180);             //3分起動
                ini_set('memory_limit',"1024M"); //メモリ拡大

                $w_flnm = "会場全体集計比較_".date("YmdHis").".csv";
                header("Content-Disposition: attachment; filename=\"".mb_convert_encoding( $w_flnm, "SJIS-WIN" , _ENCODING_SRC )."\"");
                header("Content-Type: application/octet-stream; name=\"".$w_flnm."\"");

                $csv_head = $select_event_rec['event_pulldown_name'] . '：' . implode('、', $event_syoutai_yotei_ymd_array);
                $csv_head .= "\r\n";
                $csv_head .= $compare_event['event_pulldown_name'] . '：' . implode('、', $compare_event_syoutai_yotei_ymd_array);
                $csv_head .= "\r\n";
                $csv_head .= "\r\n";

                $csv_head .= "大分類別-入場者数";
                $csv_head .= "\r\n";
                $csv_head .= "\r\n";

                // ******************************************************************************************************
                // 入場者_合計（①招待者+②来場者）
                // ******************************************************************************************************
                $csv_head .= "入場者_合計（①招待者+②来場者）";
                $csv_head .= "\r\n";
                $csv_head .= "時間帯, " . $select_event_rec['event_pulldown_name'] . "," . $compare_event['event_pulldown_name'] . ",前回比（%）";
                $csv_head .= "\r\n";
                $echo($csv_head);

                $select_total = 0;
                $compare_total = 0;

                $csv_buff = '';
                foreach ($event_summary['select']['summary'] as $key => $row) {
                    $array = [];
                    if ($key === array_key_last($event_summary['select']['summary'])) {
                        $array[] = '入場者_合計';
                    }
                    else {
                        $array[] = $row['time'];
                    }

                    $array[] = $row['total'];
                    $array[] = $event_summary['compare']['summary'][$key]['total'];
                    $array[] = $ratio_calculation($row['total'], $event_summary['compare']['summary'][$key]['total']);
                    $csv_buff .= implode(',', $array) . "\r\n";
                    $select_total += $row['total'];
                    $compare_total += $event_summary['compare']['summary'][$key]['total'];
                }
                $csv_buff .= "\r\n";
                $echo($csv_buff);

                // ******************************************************************************************************
                // ①招待者
                // ******************************************************************************************************
                $csv_head = '';
                $csv_head .= "①招待者";
                $csv_head .= "\r\n";
                $array = [];
                $array[] = '時間帯';
                foreach ($_conf_big_cate1 as $key => $value) {
                    $array[] = '"' . $value . "\r\n" . $event_summary['select']['name'] . '"';
                    $array[] = '"' . $value . "\r\n" . $event_summary['compare']['name'] . '"';
                    $array[] = '"' . $value . "\r\n前回比（%）" . '"';
                }
                $array[] = "\"招待者_合計\r\n" . $event_summary['select']['name'] . '"';
                $array[] = "\"招待者_合計\r\n" . $event_summary['compare']['name'] . '"';
                $array[] = "\"招待者_合計\r\n前回比（%）\"";
                $csv_head .= implode(',', $array);
                $csv_head .= "\r\n";
                $echo($csv_head);

                $csv_buff = '';
                foreach ($event_summary['select']['summary'] as $key => $row) {
                    $select_total = 0;
                    $compare_total = 0;
                    $array = [];

                    if ($key === array_key_last($event_summary['select']['summary'])) {
                        $array[] = '招待者_合計';
                    }
                    else {
                        $array[] = $row['time'];
                    }

                    foreach ($_conf_big_cate1 as $bkey => $value) {
                        $array[] = $row[$bkey];
                        $array[] = $event_summary['compare']['summary'][$key][$bkey];
                        $array[] = $ratio_calculation($row[$bkey], $event_summary['compare']['summary'][$key][$bkey]);
                        $select_total += $row[$bkey];
                        $compare_total += $event_summary['compare']['summary'][$key][$bkey];
                    }
                    $array[] = $select_total;
                    $array[] = $compare_total;
                    $array[] = $ratio_calculation($select_total, $compare_total);
                    $csv_buff .= implode(',', $array);
                    $csv_buff .= "\r\n";
                }
                $csv_buff .= "\r\n";
                $echo($csv_buff);

                // ******************************************************************************************************
                // ②来場者
                // ******************************************************************************************************
                $csv_head = '';
                $csv_head .= "②来場者";
                $csv_head .= "\r\n";
                $array = [];
                $array[] = '時間帯';
                foreach ($_conf_big_cate2 as $key => $value) {
                    $array[] = '"' . $value . "\r\n" . $event_summary['select']['name'] . '"';
                    $array[] = '"' . $value . "\r\n" . $event_summary['compare']['name'] . '"';
                    $array[] = '"' . $value . "\r\n前回比（%）" . '"';
                }
                $array[] = "\"来場者_合計\r\n" . $event_summary['select']['name'] . '"';
                $array[] = "\"来場者_合計\r\n" . $event_summary['compare']['name'] . '"';
                $array[] = "\"来場者_合計\r\n前回比（%）\"";
                $csv_head .= implode(',', $array);
                $csv_head .= "\r\n";
                $echo($csv_head);

                $csv_buff = '';
                foreach ($event_summary['select']['summary'] as $key => $row) {
                    $select_total = 0;
                    $compare_total = 0;
                    $array = [];

                    if ($key === array_key_last($event_summary['select']['summary'])) {
                        $array[] = '来場者_合計';
                    }
                    else {
                        $array[] = $row['time'];
                    }

                    foreach ($_conf_big_cate2 as $bkey => $value) {
                        $array[] = $row[$bkey];
                        $array[] = $event_summary['compare']['summary'][$key][$bkey];
                        $array[] = $ratio_calculation($row[$bkey], $event_summary['compare']['summary'][$key][$bkey]);
                        $select_total += $row[$bkey];
                        $compare_total += $event_summary['compare']['summary'][$key][$bkey];
                    }
                    $array[] = $select_total;
                    $array[] = $compare_total;
                    $array[] = $ratio_calculation($select_total, $compare_total);
                    $csv_buff .= implode(',', $array);
                    $csv_buff .= "\r\n";
                }
                $csv_buff .= "\r\n";
                $echo($csv_buff);

                // ******************************************************************************************************
                // 担当エリア別-入場者数（招待者）
                // ******************************************************************************************************
                $csv_head = '';
                $csv_head .= "\r\n";
                $csv_head .= "担当エリア別-入場者数（招待者）";
                $csv_head .= "\r\n";
                $array = [];
                $array[] = '時間帯';
                foreach ($m_tantou_area as $key => $value) {
                    $array[] = '"' . $value . "\r\n" . $area_summary['select']['name'] . '"';
                    $array[] = '"' . $value . "\r\n" . $area_summary['compare']['name'] . '"';
                    $array[] = '"' . $value . "\r\n前回比（%）" . '"';
                }
                $array[] = "\"担当エリア別_合計\r\n" . $area_summary['select']['name'] . '"';
                $array[] = "\"担当エリア別_合計\r\n" . $area_summary['compare']['name'] . '"';
                $array[] = "\"担当エリア別_合計\r\n前回比（%）\"";
                $csv_head .= implode(',', $array);
                $csv_head .= "\r\n";
                $echo($csv_head);

                $csv_buff = '';
                foreach ($area_summary['select']['summary'] as $key => $row) {
                    $select_total = 0;
                    $compare_total = 0;
                    $array = [];

                    if ($key === array_key_last($event_summary['select']['summary'])) {
                        $array[] = 'エリア別招待者_合計';
                    }
                    else {
                        $array[] = $row['time'];
                    }

                    foreach ($m_tantou_area as $bkey => $value) {
                        $array[] = $row[$bkey];
                        $array[] = $area_summary['compare']['summary'][$key][$bkey];
                        $array[] = $ratio_calculation($row[$bkey], $area_summary['compare']['summary'][$key][$bkey]);
                        $select_total += $row[$bkey];
                        $compare_total += $area_summary['compare']['summary'][$key][$bkey];
                    }
                    $array[] = $select_total;
                    $array[] = $compare_total;
                    $array[] = $ratio_calculation($select_total, $compare_total);
                    $csv_buff .= implode(',', $array);
                    $csv_buff .= "\r\n";
                }
                $csv_buff .= "\r\n";
                $echo($csv_buff);

                exit();
            }
        }
    }

    _setAssign($blade, $this_sess);

    $blade->assign('_conf_big_cate1', $_conf_big_cate1);
    $blade->assign('_conf_big_cate2', $_conf_big_cate2);
    $blade->assign('m_tantou_area', $m_tantou_area);

    $blade->assign('error_pattern', $error_pattern);
    $blade->assign('title_bgcolor', $select_event_rec['event_title_bgcolor']);

    $blade->assign('event_summary', $event_summary);
    $blade->assign('area_summary', $area_summary);

    $active_menu = "kaijyou_syuukei_hikaku_ruikei";
    $contents_tpl = "kaijyou_syuukei_hikaku_ruikei";
