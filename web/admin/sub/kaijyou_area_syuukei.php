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

    $table_area_inout = 't_area_inout';
    $table_user = 'm_user';

    if ($select_event_rec['event_archived_flg'] == '1')
    {
        $table_area_inout = 'a_area_inout';
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


    if($_SESSION[_PROJECT_NAME]['select_event_id']=="")
    {
        $contents_title = "会場エリア別集計";

    } else if ($_request['exec'] == "area_csv") {
        set_time_limit(180);             //3分起動
        ini_set('memory_limit',"1024M"); //メモリ拡大

        $csv_head = '';
        $csv_head .= '"来場者名"';
        $csv_head .= ',"大分類"';
        $csv_head .= ',"エリア名"';
        $csv_head .= ',"入場時刻"';
        $csv_head .= ',"退場時刻"';
        $csv_head .= "\r\n";

        $sql = "";
        $sql .= " select * from t_area_inout ";
        $sql .= " join v_user on (v_user.user_id = t_area_inout.ainout_user_id) ";
        $sql .= " join m_area on (m_area.area_id = t_area_inout.ainout_area_id) ";
        $sql .= " where ainout_delete_date is null ";
        $sql .= " and ainout_event_id = '"._as($_SESSION[_PROJECT_NAME]['select_event_id'])."'";
        $sql .= " order by ainout_time_in asc ";

        $w_flnm = $select_event_rec['event_name'] . "_エリア入退場一覧_".date("YmdHis").".csv";
        header("Content-Disposition: attachment; filename=\"".mb_convert_encoding( $w_flnm, "SJIS-WIN" , _ENCODING_SRC )."\"");
        header("Content-Type: application/octet-stream; name=\"".$w_flnm."\"");

        echo mb_convert_encoding( $csv_head, "SJIS-WIN" , _ENCODING_SRC );
        $result = _query( $conn, $sql );

        $row = 0;
        while( $rec = _fetchArray( $result, $row ) ) {
            $csv_buff = '';
            $csv_buff .= '"'.csvSafe($rec['user_name']).'"';
            $csv_buff .= ',"'.csvSafe($_conf_big_cate[$rec['user_big_cate']]).'"';
            $csv_buff .= ',"'.csvSafe($rec['area_name']).'"';
            $csv_buff .= ',"'.csvSafe($rec['ainout_time_in']).'"';
            $csv_buff .= ',"'.csvSafe($rec['ainout_time_out']).'"';
            $csv_buff .= "\r\n";

            echo mb_convert_encoding( $csv_buff, "SJIS-WIN" , _ENCODING_SRC );
            $row++;
        }

        _freeResult( $result );
        exit();
    }else{
        $contents_title = "会場エリア別集計" . "（".$select_event_rec['event_name'] .":".$select_event_rec['event_kaijyou_name']."）";

        $sql = " select * ";
        $sql .= " from m_area ";
        $sql .= " where area_delete_date is null ";
        $sql .= " and area_event_id = '"._as($_SESSION[_PROJECT_NAME]['select_event_id'])."' ";
        $event_area_recs = _select($sql);

        $area_names = array();
        foreach ($event_area_recs as $row) {
            $area_names[$row['area_id']] = $row['area_name'];
        }
        $blade->assign('area_names',$area_names);

        if (count($event_area_recs) > 0) {
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

            foreach ($event_area_recs as $area) {
                $area_recs = array();
//                $area_recs['area_name'] = $area['area_name'];
                $total_rec = array();
                $total_rec['time'] = "合計";
                foreach ($_conf_big_cate1 as $bcate1_id => $bcate1_name) {
                    $total_rec[$bcate1_id] = 0;
                }
                $total_rec['b_goukei'] = 0;
                $total_rec['all_goukei'] = 0;
                $total_rec['taijyou_cnt'] = 0;
                foreach ($_conf_big_cate2 as $bcate2_id => $bcate2_name) {
                    $total_rec[$bcate2_id] = 0;
                }

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
                    $sql .= " ,count(ainout_id) as cnt";
                    $sql .= " ,sum( case when (ainout_time_out is not null and ainout_time_out!='') then 1 else 0 end ) as taijyou_cnt";
                    $sql .= " from $table_area_inout";
                    $sql .= " join $table_user on ($table_user.user_id = $table_area_inout.ainout_user_id)";
                    $sql .= " where";
                    $sql .= " ainout_delete_date is null";
                    $sql .= " and ainout_area_id = '"._as($area['area_id'])."'";
                    $sql .= " and ainout_event_id = '"._as($_SESSION[_PROJECT_NAME]['select_event_id'])."'";
                    $sql .= " and ainout_time_in is not null";
                    $sql .= " and ainout_time_in != ''";
                    $sql .= " and ainout_time_in >= '".$this_sess['search_condition']['select_ymd']." ".$from.":00'";
                    $sql .= " and ainout_time_in < '".$this_sess['search_condition']['select_ymd']." ".$to.":00'";
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

                    $area_recs[] = $rec;
                }
                $area_recs[] = $total_rec;
                $main_recs[$area['area_id']] = $area_recs;
            }
            $blade->assign('main_recs',$main_recs);
        } else {
            $blade->assign('no_area', 1);
        }

        //滞在者
        $taizai_recs = array();
        $big_cate_total = array();
        $area_total = array();
        $all_total = 0;

        $sql = "";
        $sql .= "select";
        $sql .= "  ainout_area_id";
        $sql .= "  ,user_big_cate";
        $sql .= "  ,area_name";
        $sql .= "  ,count(ainout_id) as cnt";
        $sql .= " from $table_area_inout";
        $sql .= " join v_user on (v_user.user_id = $table_area_inout.ainout_user_id)";
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
        $sql .= " group by ainout_area_id, user_big_cate";
        $wRecs = _select($sql);
        for ($i=0; $i < _count($wRecs); $i++) {
            $taizai_recs[$wRecs[$i]['ainout_area_id']][$wRecs[$i]['user_big_cate']]['cnt'] += intval($wRecs[$i]['cnt']);
            $big_cate_total[$wRecs[$i]['user_big_cate']] += intval($wRecs[$i]['cnt']);
            $area_total[$wRecs[$i]['ainout_area_id']] += intval($wRecs[$i]['cnt']);
            $all_total += intval($wRecs[$i]['cnt']);
        }
        $blade->assign('taizai_recs',$taizai_recs);

        $blade->assign('big_cate_total',$big_cate_total);
        $blade->assign('area_total',$area_total);
        $blade->assign('all_total',$all_total);
    }

    _setAssign($blade,$this_sess);

    $blade->assign('_conf_big_cate1',$_conf_big_cate1);
    $blade->assign('_conf_big_cate2_html',$_conf_big_cate2_html);
    $blade->assign('_conf_jikantai',$_conf_jikantai);
    $blade->assign('_conf_big_cate', $_conf_big_cate);

    $blade->assign('title_bgcolor',$select_event_rec['event_title_bgcolor']);

    $active_menu = "kaijyou_area_syuukei";
    $contents_tpl = "kaijyou_area_syuukei";
