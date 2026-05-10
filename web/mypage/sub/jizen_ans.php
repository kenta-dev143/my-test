<?php
    if( !defined("_PROJECT_DISP_NAME") ){
        die("System Error");
    }

    if( $_SESSION[_PROJECT_NAME]['user_login']['user_id']=="" ){
        die("System Error");
    }

    if($_SESSION[_PROJECT_NAME]['direct_login']=="1"){
        header("Location: "._SYSTEM_ROOT_URLS."/mypage/".$event_rec['event_url_key']."/");
        exit();
    }

    $today = date("Y/m/d");
    //$today = "2021/07/15";

    $event_recs = _select("select * from m_event where event_delete_date is null and event_id='".$_SESSION[_PROJECT_NAME]['user_login']['user_event_id']."'") ;
    $event_rec = $event_recs[0];

    if (isset($_request['debug'])) {
        $today = $event_rec['event_kaisai_ymd_st'];
    }

    $kaitou_ok = false;
    if(_count($event_recs) > 0){
        if( $event_recs[0]['event_raikainri_ymd_st'] <= $today && $today <= $event_recs[0]['event_raikainri_ymd_ed']){
            $kaitou_ok = true;
        }
    }

    $helth_recs = _select("select * from t_helth where helth_delete_date is null and helth_user_id='"._as($_SESSION[_PROJECT_NAME]['user_login']['user_id'])."' and helth_event_id='"._as($_SESSION[_PROJECT_NAME]['user_login']['user_event_id'])."' and helth_ymd='"._as($today)."'");

    if($_request['exec']=="save"){

        _query($conn,'begin');

        $now = $today." ".date("H:i:s");


        $array = array();
        $array['helth_ans'] = "'"._as($_request['ans'])."'";
        $array['helth_update_date'] = "'".$_now_timestamp."'";

        if(_count($helth_recs) == 0){
            $array['helth_user_id'] = "'"._as($_SESSION[_PROJECT_NAME]['user_login']['user_id'])."'";
            $array['helth_event_id'] = "'"._as($_SESSION[_PROJECT_NAME]['user_login']['user_event_id'])."'";
            $array['helth_ymd'] = "'"._as($today)."'";
            $array['helth_insert_date'] = "'".$_now_timestamp."'";
            _insert('t_helth',$array);
        }else{
            $where = "helth_delete_date is null and helth_user_id='"._as($_SESSION[_PROJECT_NAME]['user_login']['user_id'])."' and helth_event_id='"._as($_SESSION[_PROJECT_NAME]['user_login']['user_event_id'])."' and helth_ymd='"._as($today)."'";
            _update('t_helth',$array,$where);
        }


        _query($conn,'commit');

    }

    //健康記録
    $helth_recs = _select("select * from t_helth where helth_delete_date is null and helth_user_id='"._as($_SESSION[_PROJECT_NAME]['user_login']['user_id'])."' and helth_event_id='"._as($_SESSION[_PROJECT_NAME]['user_login']['user_event_id'])."' and helth_ymd='"._as($today)."'");
    $kizon_kaitou = "";
    if($helth_recs[0]['helth_ans']=='y'){
        // $kizon_kaitou = "「はい」とご回答済みです。";
        $kizon_kaitou = "「なし」とご回答済みです。";
    }elseif($helth_recs[0]['helth_ans']=='n'){
        // $kizon_kaitou = "「いいえ」とご回答済みです。";
        $kizon_kaitou = "「あり」とご回答済みです。";
    }

    $blade->assign('ymd', $today);
    $blade->assign('disp_ymd', date("n月j日",strtotime($today) )."（"._getYoubi($today)."）" );
    $blade->assign('event_rec',$event_rec);
    $blade->assign('kaitou_ok',$kaitou_ok);
    $blade->assign('kizon_kaitou',$kizon_kaitou);
    $blade->assign('exec',$_request['exec']);

    $blade->assign('now_date_and_time',date("Y年m月d日 H時i分"));

    $contents_tpl = "jizen_ans.html";
