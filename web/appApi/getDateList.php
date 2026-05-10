<?php

    $return_arr = array();
    $return_arr['status'] = "OK";
    $return_arr['error_message'] = "";
    $return_arr['data'] = array();

    if($_request['event_id']=="" ){
        _ngReturn( "イベントIDが指定されていません。" );
    }else{

        $event_recs = _select("select * from m_event where event_delete_date is null and event_id = '"._as($_request['event_id'])."'");
        if(_count($event_recs)==0){
            _ngReturn( "指定されたイベントIDのイベントは存在しません。" );
        }


        $w_ymd = $event_recs[0]['event_raikainri_ymd_st'];
        $select_ymd_arr = array();
        $return_arr['data']['date_list'] = array();
        $i = 0;
        while(true){
            $return_arr['data']['date_list'][$i] = array();
            $return_arr['data']['date_list'][$i]['disp_date'] = date("n月j日",strtotime($w_ymd))."（"._getYoubi($w_ymd)."）";
            if($w_ymd==date("Y/m/d")){
                $return_arr['data']['date_list'][$i]['disp_date'] .= "[本日]";
            }
            $return_arr['data']['date_list'][$i]['ymd'] = "".$w_ymd;
            $w_ymd = date("Y/m/d",strtotime($w_ymd." +1 day"));
            if($w_ymd > $event_recs[0]['event_raikainri_ymd_ed']){
                break;
            }
            $i++;
        }
    }
