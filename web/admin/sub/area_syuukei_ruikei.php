<?php

    _disp404();
    exit;

    if( !defined("_PROJECT_DISP_NAME") ){
        die("System Error");
    }

    if( $_SESSION[_PROJECT_NAME]['admin_login']['admin_id'] == "" ){
        die('System Error');
    }

    // ******************************************************************************************************
    // 初期値
    // ******************************************************************************************************
    $page = $_request['page'];
    $this_sess = &$_SESSION[_PROJECT_NAME][$page];
    $err_msg = array();

    $table_area_inout = 't_area_inout';

    if ($select_event_rec['event_archived_flg'] == '1')
    {
        $table_area_inout = 'a_area_inout';
    }

    // ******************************************************************************************************
    // 検索
    // ******************************************************************************************************
    if( $_request['exec'] == "search"){
        unset( $this_sess['search_condition'] );
        $this_sess['search_condition'] = array();
        $this_sess['search_condition'] = _array_merge( $this_sess['search_condition'], $_request );
    }


    if($_SESSION[_PROJECT_NAME]['select_event_id']==""){
        $contents_title = "会場エリア集計（累計）";

    }else{
        $contents_title = "会場エリア集計（累計）" . "（".$select_event_rec['event_name'] .":".$select_event_rec['event_kaijyou_name']."）";

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

        // (招待者)来場予定日時の設定が行なわれている場合
        if (count($event_syoutai_yotei_ymd_array) > 0) {
            // エリア情報
            $area_recs = _select("select * from m_area where area_delete_date is null and area_event_id='"._as($_SESSION[_PROJECT_NAME]['select_event_id'])."' order by area_id asc");
            $blade->assign('area_recs',$area_recs);

            $main_recs = array();

            $total_rec = array();
            $total_rec['time'] = "合計";
            for ($i=0; $i < _count($area_recs); $i++) {
                $total_rec[$area_recs[$i]['area_id']] = 0;
            }
            $total_rec['all_area'] = 0;

            foreach ($_conf_jikantai as $time_key => $time_value) {

                $rec = array();
                $rec['time'] = $time_value;
                for ($i=0; $i < _count($area_recs); $i++) {
                    $rec[$area_recs[$i]['area_id']] = 0;
                }
                $rec['all_area'] = 0;

                list($from,$to) = explode("-",$time_key);

                $sql = "";
                $sql .= "select";
                $sql .= "  ainout_area_id";
                $sql .= " ,count(ainout_id) as cnt";
                $sql .= " from $table_area_inout";
                $sql .= " join m_area on (m_area.area_id = $table_area_inout.ainout_area_id)";
                $sql .= " where";
                $sql .= " ainout_delete_date is null";
                $sql .= " and area_delete_date is null";
                $sql .= " and ainout_event_id = '"._as($_SESSION[_PROJECT_NAME]['select_event_id'])."'";
                $sql .= " and ainout_time_in is not null";
                $sql .= " and ainout_time_in != ''";

                $array = [];
                foreach($event_syoutai_yotei_ymd_array as $ymd) {
                    $s = "(ainout_time_in >= '".$ymd." ".$from.":00'";
                    $s .= " and ainout_time_in < '".$ymd." ".$to.":00')";
                    $array[] = $s;
                }
                $sql .= ' and ( ' . implode(' OR ', $array) . ' ) ';

                $sql .= " group by ainout_area_id";

                $wRecs = _select($sql);
                for ($i=0; $i < _count($wRecs); $i++) {
                    $rec[$wRecs[$i]['ainout_area_id']] += intval($wRecs[$i]['cnt']);
                    $total_rec[$wRecs[$i]['ainout_area_id']] += intval($wRecs[$i]['cnt']);

                    $rec['all_area'] += intval($wRecs[$i]['cnt']);
                    $total_rec['all_area'] += intval($wRecs[$i]['cnt']);
                }

                $main_recs[] = $rec;
            }
            $main_recs[] = $total_rec;
            $blade->assign('main_recs',$main_recs);
        }
    }

    _setAssign($blade,$this_sess);

    $blade->assign('_conf_big_cate1',$_conf_big_cate1);
    $blade->assign('_conf_big_cate2',$_conf_big_cate2);
    $blade->assign('_conf_jikantai',$_conf_jikantai);

    $blade->assign('title_bgcolor',$select_event_rec['event_title_bgcolor']);

    $active_menu = "area_syuukei_ruikei";
    $contents_tpl = "area_syuukei_ruikei";
