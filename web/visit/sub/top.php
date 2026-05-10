<?php
    if( !defined("_PROJECT_DISP_NAME") ){
        die("System Error");
    }

    $area_recs = _select("select * from m_area where area_delete_date is null and area_event_id='"._as($event_rec['event_id'])."' order by area_id asc");
    $blade->assign('area_recs',$area_recs);

    $w_ymd = $event_rec['event_raikainri_ymd_st'];
    $select_ymd_arr = array();
    while(true){
        $select_ymd_arr[$w_ymd] = date("n月j日",strtotime($w_ymd))."（"._getYoubi($w_ymd)."）";
        $w_ymd = date("Y/m/d",strtotime($w_ymd." +1 day"));
        if($w_ymd > $event_rec['event_raikainri_ymd_ed']){
            break;
        }
    }
    $blade->assign('select_ymd_arr',$select_ymd_arr);
    $blade->assign('ymd',$_request['ymd']);


    $contents_tpl = "top.html";
