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
        $contents_title = "会場エリア集計";

    }else{
        $contents_title = "会場エリア集計" . "（".$select_event_rec['event_name'] .":".$select_event_rec['event_kaijyou_name']."）";

        $area_recs = _select("select * from m_area where area_delete_date is null and area_event_id='"._as($_SESSION[_PROJECT_NAME]['select_event_id'])."' order by area_id asc");
        $blade->assign('area_recs',$area_recs);

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
            $sql .= " and ainout_time_in >= '".$this_sess['search_condition']['select_ymd']." ".$from.":00'";
            $sql .= " and ainout_time_in < '".$this_sess['search_condition']['select_ymd']." ".$to.":00'";
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

        //滞在者
        $taizai_recs = array();
        $all_area_max = 0;
        for ($i=0; $i < _count($area_recs); $i++) {
            $taizai_recs[$area_recs[$i]['area_id']]['cnt'] = 0;
            $taizai_recs[$area_recs[$i]['area_id']]['max'] = $area_recs[$i]['area_max'];
            $all_area_max += $area_recs[$i]['area_max'];
        }

        $all_area_cnt = 0;
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
        $sql .= " and ainout_time_in >= '".$this_sess['search_condition']['select_ymd']." 00:00:00'";
        $sql .= " and ainout_time_in < '".$this_sess['search_condition']['select_ymd']." 24:00:00'";
        $sql .= " and (ainout_time_out is null or (ainout_time_out is not null and ainout_time_out='') )";
        $sql .= " group by ainout_area_id";
        $wRecs = _select($sql);
        for ($i=0; $i < _count($wRecs); $i++) {
            $taizai_recs[$wRecs[$i]['ainout_area_id']]['cnt'] += intval($wRecs[$i]['cnt']);
            $all_area_cnt += intval($wRecs[$i]['cnt']);
        }
        $blade->assign('taizai_recs',$taizai_recs);

        $blade->assign('all_area_cnt',$all_area_cnt);
        $blade->assign('all_area_max',$all_area_max);

    }

    _setAssign($blade,$this_sess);

    $blade->assign('_conf_big_cate1',$_conf_big_cate1);
    $blade->assign('_conf_big_cate2',$_conf_big_cate2);
    $blade->assign('_conf_jikantai',$_conf_jikantai);

    $blade->assign('title_bgcolor',$select_event_rec['event_title_bgcolor']);

    $active_menu = "area_syuukei";
    $contents_tpl = "area_syuukei";
