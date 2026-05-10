<?php
    if( !defined("_PROJECT_DISP_NAME") ){
        die("System Error");
    }
    if( $_request['ymd']=="" ){
        die("System Error2");
    }

    $kaitou_zumi = false;

    if($_request['exec']=="save"){
        if($_request['qr_code']==""){
            $err_msg[] = "QRコードが読み込まれませんでした。(再度読み込みを行ってください)";
        }elseif( strlen($_request['qr_code']) != 17){
            $err_msg[] = "QRコードが正しく読み込まれませんでした。(再度読み込みを行ってください)";
        }else{
            list($fst,$scd,$code) = explode("-", $_request['qr_code']);
            $rd_event_area_shikibetsu_id = substr($fst,0,1);
            $rd_event_id = "e".substr($fst,1);
            $rd_user_big_cate = substr($scd,0,1);
            $rd_dummy = substr($scd,1);
            $rd_user_id = "u".$code;
            if( $_conf_event_area_shikibetsu_id[$rd_event_area_shikibetsu_id] == ""){
                $err_msg[] = "このQRコードは正しくありません。(ERR001)";
            }elseif( $event_rec['event_area_shikibetsu_id'] != $rd_event_area_shikibetsu_id){
                $err_msg[] = "このQRコードは正しくありません。(ERR002)";
            }elseif( $event_rec['event_id'] != $rd_event_id){
                $err_msg[] = "このQRコードは正しくありません。(ERR003)";
            }else{
                $user_recs = _select("select * from v_user where user_delete_date is null and user_id='"._as($rd_user_id)."'");
                if(_count($user_recs)==0){
                    $err_msg[] = "このQRコードは正しくありません。(ERR004)";
                }else{
                    if($user_recs[0]['user_event_id']!=$rd_event_id){
                        $err_msg[] = "このQRコードは正しくありません。(ERR005)";
                    // }elseif($user_recs[0]['user_big_cate']!=$rd_user_big_cate){
                    //     $err_msg[] = "このQRコードは正しくありません。(ERR006)";
                    }else{
                        _query($conn,'begin');

                        $now = $_request['ymd']." ".date("H:i:s");

                        //未退出のエリアレコードあるか？
                        $sql = "";
                        $sql .= "select ";
                        $sql .= " * ";
                        $sql .= " from t_area_inout";
                        $sql .= " where";
                        $sql .= " ainout_delete_date is null";
                        $sql .= " and ainout_user_id='"._as($rd_user_id)."'";
                        $sql .= " and ainout_event_id='"._as($rd_event_id)."'";
                        // $sql .= " and ainout_area_id="._as($_request['area_id'])."";
                        $sql .= " and ainout_time_in is not null";
                        $sql .= " and ainout_time_in != ''";
                        $sql .= " and ainout_time_in >= '".$_request['ymd']." 00:00:00'";
                        $sql .= " and ainout_time_in < '".$_request['ymd']." 24:00:00'";
                        $sql .= " and (ainout_time_out is null or (ainout_time_out is not null and ainout_time_out='') )";
                        $mi_recs = _select($sql);
                        if(_count($mi_recs) > 0){
                            for ($i=0; $i < _count($mi_recs); $i++) { 
                                $now_minnus1 = date("Y/m/d H:i:s",strtotime($now." -1 seconds"));
                                $array = array();
                                $array['ainout_time_out'] = "'"._as($now_minnus1)."'";
                                $array['ainout_update_date'] = "'".$_now_timestamp."'";
                                $where = "ainout_id = ".$mi_recs[$i]['ainout_id'];
                                _update('t_area_inout',$array,$where);
                            }
                        }

                        //未退出の会場レコードあるか？
                        $sql = "";
                        $sql .= "select ";
                        $sql .= " * ";
                        $sql .= " from t_kaijyou_inout";
                        $sql .= " where";
                        $sql .= " kinout_delete_date is null";
                        $sql .= " and kinout_user_id='"._as($rd_user_id)."'";
                        $sql .= " and kinout_event_id='"._as($rd_event_id)."'";
                        $sql .= " and kinout_time_in is not null";
                        $sql .= " and kinout_time_in != ''";
                        $sql .= " and kinout_time_in >= '".$_request['ymd']." 00:00:00'";
                        $sql .= " and kinout_time_in < '".$_request['ymd']." 24:00:00'";
                        $sql .= " and (kinout_time_out is null or (kinout_time_out is not null and kinout_time_out='') )";
                        $mi_recs = _select($sql);
                        if(_count($mi_recs) > 0){
                            for ($i=0; $i < _count($mi_recs); $i++) { 
                                $now_minnus1 = date("Y/m/d H:i:s",strtotime($now." -1 seconds"));
                                $array = array();
                                $array['kinout_time_out'] = "'"._as($now_minnus1)."'";
                                $array['kinout_update_date'] = "'".$_now_timestamp."'";
                                $where = "kinout_id = ".$mi_recs[$i]['kinout_id'];
                                _update('t_kaijyou_inout',$array,$where);
                            }
                        }

                        _query($conn,'commit');

                    }
                }
            }
        }
    }

    if($project_name_prefix == "app_"){
        $return_arr['data']['inout_kbn'] = "out";
    } else {

        $blade->assign('ymd', $_request['ymd']);
        $blade->assign('disp_ymd', date("n月j日",strtotime($_request['ymd']) )."（"._getYoubi($_request['ymd'])."）" );

        if($_request['ymd']!=date("Y/m/d")){
            $blade->assign('ymd_alert','<span style="color:red;">[注意]本日ではありません</span>');
        }

        $sql = "";
        $sql .= "select";
        $sql .= " count(kinout_id) as cnt";
        $sql .= " from t_kaijyou_inout";
        $sql .= " where";
        $sql .= " kinout_delete_date is null";
        $sql .= " and kinout_event_id='"._as($event_rec['event_id'])."'";
        $sql .= " and kinout_time_in is not null";
        $sql .= " and kinout_time_in != ''";
        $sql .= " and kinout_time_in >= '".$_request['ymd']." 00:00:00'";
        $sql .= " and kinout_time_in < '".$_request['ymd']." 24:00:00'";
        $sql .= " and (kinout_time_out is null or (kinout_time_out is not null and kinout_time_out='') )";
        $cnt_recs = _select($sql);
        $blade->assign('cnt',intval($cnt_recs[0]['cnt']));

        $contents_tpl = "kaijyou_out.html";
    }
