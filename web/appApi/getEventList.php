<?php

    $return_arr = array();
    $return_arr['status'] = "OK";
    $return_arr['error_message'] = "";
    $return_arr['data'] = array();

    $event_recs = _select("select * from m_event where event_delete_date is null and event_pulldown_disp_flg=1 order by event_raikainri_ymd_st asc");
    if(_count($event_recs)==0){
        _ngReturn( "現在利用しているイベントが存在しません" );
    }

    $return_arr['data']['event_list'] = array();
    for ($i=0; $i < _count($event_recs); $i++) {
        $return_arr['data']['event_list'][$i] = array();
        $return_arr['data']['event_list'][$i]['event_id'] = $event_recs[$i]['event_id'];
        $return_arr['data']['event_list'][$i]['event_name'] = $event_recs[$i]['event_name'];
    }
