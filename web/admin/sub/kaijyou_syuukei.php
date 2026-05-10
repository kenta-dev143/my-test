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
        $contents_title = "会場全体集計";

    }else{
        $contents_title = "会場全体集計" . "（".$select_event_rec['event_name'] .":".$select_event_rec['event_kaijyou_name']."）";

        $w_ymd = $select_event_rec['event_raikainri_ymd_st'];
        $select_ymd_arr = array();
        $hit_today_ymd = "";
        while(true){
            $select_ymd_arr[$w_ymd] = date("n月j日",strtotime($w_ymd))."（"._getYoubi($w_ymd)."）";
            if($w_ymd==date("Y/m/d")) $hit_today_ymd = $w_ymd;
            $w_ymd = date("Y/m/d",strtotime($w_ymd." +1 day"));
            if($w_ymd > $select_event_rec['event_raikainri_ymd_ed']){
                break;
            }
        }
        $blade->assign('select_ymd_arr',$select_ymd_arr);

        if($this_sess['search_condition']['select_ymd']==""){
            if($hit_today_ymd!=""){
                $this_sess['search_condition']['select_ymd'] = $hit_today_ymd;
            }else{
                $this_sess['search_condition']['select_ymd'] = $select_event_rec['event_raikainri_ymd_st'];
            }
        }elseif($this_sess['search_condition']['select_ymd']!="" &&
                $this_sess['search_condition']['select_ymd'] >= $select_event_rec['event_raikainri_ymd_st'] &&
                $this_sess['search_condition']['select_ymd'] <= $select_event_rec['event_raikainri_ymd_ed'] ){
            //OK
        }else{
            $this_sess['search_condition']['select_ymd'] = $select_event_rec['event_raikainri_ymd_st'];
        }

        if( _dateCheck($this_sess['search_condition']['select_ymd'],'')==false ){
            _dispError();
        }

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
            $sql .= " and kinout_time_in >= '".$this_sess['search_condition']['select_ymd']." ".$from.":00'";
            $sql .= " and kinout_time_in < '".$this_sess['search_condition']['select_ymd']." ".$to.":00'";
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
            $sql .= " and kinout_time_in >= '".$this_sess['search_condition']['select_ymd']." ".$from.":00'";
            $sql .= " and kinout_time_in < '".$this_sess['search_condition']['select_ymd']." ".$to.":00'";
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

    _setAssign($blade,$this_sess);

    $blade->assign('_conf_big_cate1',$_conf_big_cate1);
    $blade->assign('_conf_big_cate2_html',$_conf_big_cate2_html);
    $blade->assign('_conf_jikantai',$_conf_jikantai);

    $blade->assign('title_bgcolor',$select_event_rec['event_title_bgcolor']);

    $active_menu = "kaijyou_syuukei";
    $contents_tpl = "kaijyou_syuukei";
