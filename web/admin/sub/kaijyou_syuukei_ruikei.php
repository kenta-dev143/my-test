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

    $table_kaijyou_inout = 't_kaijyou_inout';
    $table_user = 'm_user';

    if ($select_event_rec['event_archived_flg'] == '1')
    {
        $table_kaijyou_inout = 'a_kaijyou_inout';
        $table_user = 'a_user';
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
        $contents_title = "会場全体集計（累計）";

    }else{
        $contents_title = "会場全体集計（累計）" . "（".$select_event_rec['event_name'] .":".$select_event_rec['event_kaijyou_name']."）";

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
            $main_recs = array();

            $total_rec = array();
            $total_rec['time'] = "合計";
            foreach ($_conf_big_cate1 as $bcate1_id => $bcate1_name) {
                $total_rec[$bcate1_id] = 0;
            }
            $total_rec['b_goukei'] = 0;
            foreach ($_conf_big_cate2 as $bcate2_id => $bcate2_name) {
                $total_rec[$bcate2_id] = 0;
            }
            $total_rec['taijyou_cnt'] = 0;
            $total_rec['all_goukei'] = 0;

            foreach ($_conf_jikantai as $time_key => $time_value) {

                $rec = array();
                $rec['time'] = $time_value;
                foreach ($_conf_big_cate1 as $bcate1_id => $bcate1_name) {
                    $rec[$bcate1_id] = 0;
                }
                $rec['b_goukei'] = 0;
                foreach ($_conf_big_cate2 as $bcate2_id => $bcate2_name) {
                    $rec[$bcate2_id] = 0;
                }
                $rec['taijyou_cnt'] = 0;
                $rec['all_goukei'] = 0;

                list($from,$to) = explode("-",$time_key);

                $sql = "";
                $sql .= "select";
                $sql .= "  user_big_cate";
                $sql .= " ,count(kinout_id) as cnt";
                $sql .= " ,sum( case when (kinout_time_out is not null and kinout_time_out!='') then 1 else 0 end ) as taijyou_cnt";
                $sql .= " from $table_kaijyou_inout";
                $sql .= " join $table_user on ($table_user.user_id = $table_kaijyou_inout.kinout_user_id)";
                $sql .= " where";
                $sql .= " kinout_delete_date is null";
                $sql .= " and kinout_event_id = '"._as($_SESSION[_PROJECT_NAME]['select_event_id'])."'";
                $sql .= " and kinout_fst_record = 1"; //初回レコードのみ対象に
                $sql .= " and kinout_time_in is not null";
                $sql .= " and kinout_time_in != ''";

                $array = [];
                foreach($event_syoutai_yotei_ymd_array as $ymd) {
                    $s = "(kinout_time_in >= '".$ymd." ".$from.":00'";
                    $s .= " and kinout_time_in < '".$ymd." ".$to.":00')";
                    $array[] = $s;
                }
                $sql .= ' and ( ' . implode(' OR ', $array) . ' ) ';

                $sql .= " group by user_big_cate";

                $wRecs = _select($sql);
                for ($i=0; $i < _count($wRecs); $i++) {
                    $rec[$wRecs[$i]['user_big_cate']] += intval($wRecs[$i]['cnt']);
                    $total_rec[$wRecs[$i]['user_big_cate']] += intval($wRecs[$i]['cnt']);
                    if( $_conf_big_cate1[$wRecs[$i]['user_big_cate']] != ""){
                        $rec['b_goukei'] += intval($wRecs[$i]['cnt']);
                        $total_rec['b_goukei'] += intval($wRecs[$i]['cnt']);
                    }

                    $rec['taijyou_cnt'] += intval($wRecs[$i]['taijyou_cnt']);
                    $total_rec['taijyou_cnt'] += intval($wRecs[$i]['taijyou_cnt']);

                    $rec['all_goukei'] += intval($wRecs[$i]['cnt']);
                    $total_rec['all_goukei'] += intval($wRecs[$i]['cnt']);
                }

                $main_recs[] = $rec;
            }
            $main_recs[] = $total_rec;
            $blade->assign('main_recs',$main_recs);


            // ******************************************************************************************************
            // 担当エリア別集計
            // ******************************************************************************************************
            $area_summary = [];

            $sql = 'select * from m_tantou_area where tanarea_delete_date is null order by tanarea_id';
            $wRecs = _select($sql);
            foreach ($wRecs as $row) {
                $m_tantou_area[$row['tanarea_id']] = $row['tanarea_name'];
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
                $sql .= " and kinout_event_id = '"._as($_SESSION[_PROJECT_NAME]['select_event_id'])."'";
                $sql .= " and kinout_fst_record = 1"; //初回レコードのみ対象に
                $sql .= " and kinout_time_in is not null";
                $sql .= " and kinout_time_in != ''";
                //$sql .= " and kinout_time_in >= '".$this_sess['search_condition']['select_ymd']." ".$from.":00'";
                //$sql .= " and kinout_time_in < '".$this_sess['search_condition']['select_ymd']." ".$to.":00'";

                $array = [];
                foreach($event_syoutai_yotei_ymd_array as $ymd) {
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
                $area_summary[] = $rec;
            }
            $area_summary[] = $category_total;

            $blade->assign('m_tantou_area', $m_tantou_area);
            $blade->assign('area_summary',$area_summary);
        }
    }

    _setAssign($blade,$this_sess);

    $blade->assign('_conf_big_cate1',$_conf_big_cate1);
    $blade->assign('_conf_big_cate2_html',$_conf_big_cate2_html);
    $blade->assign('_conf_jikantai',$_conf_jikantai);

    $blade->assign('title_bgcolor',$select_event_rec['event_title_bgcolor']);

    $active_menu = "kaijyou_syuukei_ruikei";
    $contents_tpl = "kaijyou_syuukei_ruikei";
