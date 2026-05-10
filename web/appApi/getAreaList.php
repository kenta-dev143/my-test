<?php

    $return_arr = array();
    $return_arr['status'] = "OK";
    $return_arr['error_message'] = "";
    $return_arr['data'] = array();

    if($_request['event_id']=="" ){

        $sql  = "";
        $now_date = date('Y/m/d');
        $sql .= " select * from m_event ";
        $sql .= " where event_delete_date is null ";
        $sql .= " and event_raikainri_ymd_st <= '" . $now_date . "'";
        $sql .= " and event_raikainri_ymd_ed >= '" . $now_date . "'";
        $sql .= " order by event_insert_date asc";
        $event_recs = _select($sql);

        if (_count($event_recs) == 0)
        {
            _ngReturn( "本日開催中のイベントがありません。" );
        }

        $event_id = $event_recs[0]['event_id'];
        $area_recs = _select("select * from m_area where area_delete_date is null and area_event_id='" . $event_id . "' order by area_id asc");
        // if(_count($area_recs)==0){
        //     _ngReturn( "指定されたイベントIDでのエリアは存在しません。" );
        // }

        $return_arr['data']['event_id'] = $event_id;
        $return_arr['data']['event_name'] = $event_recs[0]['event_name'];
        $return_arr['data']['area_list'] = array();
        for ($i=0; $i < _count($area_recs); $i++) {
            $return_arr['data']['area_list'][$i] = array();

            $return_arr['data']['area_list'][$i]['area_id'] = $area_recs[$i]['area_id']; //area ID（システム主キー）
            $return_arr['data']['area_list'][$i]['area_name'] = $area_recs[$i]['area_name']; //エリア名
            $return_arr['data']['area_list'][$i]['area_max'] = $area_recs[$i]['area_max']; //キャパ数
            $return_arr['data']['area_list'][$i]['area_tanmatsuhaichi_kbn'] = $area_recs[$i]['area_tanmatsuhaichi_kbn']; //端末配置区分（1:入口出口で２台別配置、2:１台で出入口共通）
        }

    }else{

        $event_recs = _select("select * from m_event where event_delete_date is null and event_id = '"._as($_request['event_id'])."'");
        if(_count($event_recs)==0){
            _ngReturn( "指定されたイベントIDのイベントは存在しません。" );
        }
        $area_recs = _select("select * from m_area where area_delete_date is null and area_event_id='"._as($_request['event_id'])."' order by area_id asc");
        // if(_count($area_recs)==0){
        //     _ngReturn( "指定されたイベントIDでのエリアは存在しません。" );
        // }

        $return_arr['data']['event_name'] = $event_recs[0]['event_name'];
        $return_arr['data']['area_list'] = array();
        for ($i=0; $i < _count($area_recs); $i++) {
            $return_arr['data']['area_list'][$i] = array();

            $return_arr['data']['area_list'][$i]['area_id'] = $area_recs[$i]['area_id']; //area ID（システム主キー）
            $return_arr['data']['area_list'][$i]['area_name'] = $area_recs[$i]['area_name']; //エリア名
            $return_arr['data']['area_list'][$i]['area_max'] = $area_recs[$i]['area_max']; //キャパ数
            $return_arr['data']['area_list'][$i]['area_tanmatsuhaichi_kbn'] = $area_recs[$i]['area_tanmatsuhaichi_kbn']; //端末配置区分（1:入口出口で２台別配置、2:１台で出入口共通）
        }
    }
