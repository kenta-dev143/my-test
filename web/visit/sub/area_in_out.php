<?php
    if( !defined("_PROJECT_DISP_NAME") ){
        die("System Error1");
    }

    if( $_request['area_id']=="" ){
        die("System Error2");
    }
    if( $_request['ymd']=="" ){
        die("System Error3");
    }

    $success_inout = "";

    if( $_request['exec']=="save" ){
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

                        //未退出のレコードあるか？
                        $sql = "";
                        $sql .= "select ";
                        $sql .= " * ";
                        $sql .= " from t_area_inout";
                        $sql .= " where";
                        $sql .= " ainout_delete_date is null";
                        $sql .= " and ainout_user_id='"._as($rd_user_id)."'";
                        $sql .= " and ainout_event_id='"._as($rd_event_id)."'";
                        $sql .= " and ainout_time_in is not null";
                        $sql .= " and ainout_time_in != ''";
                        $sql .= " and ainout_time_in >= '".$_request['ymd']." 00:00:00'";
                        $sql .= " and ainout_time_in < '".$_request['ymd']." 24:00:00'";
                        $sql .= " and (ainout_time_out is null or (ainout_time_out is not null and ainout_time_out='') )";
                        $mi_recs = _select($sql);
                        //未退出のレコード有り
                        if(_count($mi_recs) > 0){
                            //該当件数分 処理する
                            $this_area_ari = false;
                            for ($i=0; $i < _count($mi_recs); $i++) {
                                if($mi_recs[$i]['ainout_area_id']==$_request['area_id']){
                                    $this_area_ari = true;
                                }
                                $array = array();
                                $array['ainout_time_out']    = "'"._as($now)."'";
                                $array['ainout_update_date'] = "'".$_now_timestamp."'";
                                $where = "ainout_id = ".$mi_recs[$i]['ainout_id'];
                                _update('t_area_inout',$array,$where);
                            }
                            if($this_area_ari == false){
                                //エリア入場レコード作成
                                $array = array();
                                $array['ainout_user_id']     = "'"._as($rd_user_id)."'";
                                $array['ainout_event_id']    = "'"._as($rd_event_id)."'";
                                $array['ainout_area_id']     = ""._as($_request['area_id'])."";
                                $array['ainout_time_in']     = "'"._as($now)."'";
                                $array['ainout_time_out']    = "''";
                                $array['ainout_insert_date'] = "'".$_now_timestamp."'";
                                $array['ainout_update_date'] = "'".$_now_timestamp."'";
                                _insert('t_area_inout',$array);

                                $success_inout = "in";
                                $success_msg = "入場しました";

                            }else{
                                $success_inout = "out";
                                $success_msg = "退場しました";
                            }
                        } else {
                            //エリア入場レコード作成
                            $array = array();
                            $array['ainout_user_id']     = "'"._as($rd_user_id)."'";
                            $array['ainout_event_id']    = "'"._as($rd_event_id)."'";
                            $array['ainout_area_id']     = ""._as($_request['area_id'])."";
                            $array['ainout_time_in']     = "'"._as($now)."'";
                            $array['ainout_time_out']    = "''";
                            $array['ainout_insert_date'] = "'".$_now_timestamp."'";
                            $array['ainout_update_date'] = "'".$_now_timestamp."'";
                            _insert('t_area_inout',$array);

                            $success_inout = "in";
                            $success_msg = "入場しました";
                        }

                        // ---------------------------------------------------------------------
                        //当日の会場レコードなければ会場INレコード作成（会場INせずに入場された方のために）
                        if($success_inout == "in"){
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
                            $kai_in_recs = _select($sql);
                            if(_count($kai_in_recs)==0){
                                //会場INレコードがまだなかった
                                //会場入場レコード作成
                                $array = array();
                                $array['kinout_user_id'] = "'"._as($rd_user_id)."'";
                                $array['kinout_event_id'] = "'"._as($rd_event_id)."'";
                                $array['kinout_time_in'] = "'"._as($now)."'";
                                $array['kinout_time_out'] = "''";
                                $array['kinout_fst_record'] = "1"; //会場INレコードがまだなかった
                                $array['kinout_insert_date'] = "'".$_now_timestamp."'";
                                $array['kinout_update_date'] = "'".$_now_timestamp."'";
                                _insert('t_kaijyou_inout',$array);
                            }
                        }
                        // ---------------------------------------------------------------------

                        _query($conn,'commit');

                        // ---------------------------------------------------------------------
                        //当日の会場レコードなければ会場INレコード作成（会場INせずに入場された方のために）
                        if($success_inout == "in"){
                            if(_count($kai_in_recs)==0){
                                //会場INレコードがまだなかった

                                // ----------------------------------------------------- //
                                // メール通知 担当者宛
                                // ----------------------------------------------------- //
                                $w_mail = "";

                                $sql = "";
                                $sql .= " select *"."\n";
                                $sql .= " from v_admin"."\n";
                                $sql .= " where admin_delete_date is null"."\n";
                                $sql .= " and admin_id = '"._as($user_recs[0]['user_admin_id'])."'"."\n";
                                $admin_recs = _select($sql);

                                $w_mail_name = $admin_recs[0]['admin_name']." 様";
                                if ( count($admin_recs) > 0 ){
                                    $w_mail = $admin_recs[0]['admin_mail'];
                                    if ( $admin_recs[0]['admin_mail2'] != '' ){
                                        $w_mail .= ",".$admin_recs[0]['admin_mail2'];
                                        $w_mail_name = ""; //admin_mail2入れたら名称は消さないといけないので
                                    }

                                    // 追加担当者アドレスを追加
                                    if ( ! empty($user_recs[0]['user_admin_mail_1']))
                                    {
                                        $w_mail .= ",".$user_recs[0]['user_admin_mail_1'];
                                        $w_mail_name = "";
                                    }
                                    if ( ! empty($user_recs[0]['user_admin_mail_2']))
                                    {
                                        $w_mail .= ",".$user_recs[0]['user_admin_mail_2'];
                                        $w_mail_name = "";
                                    }
                                    if ( ! empty($user_recs[0]['user_admin_mail_3']))
                                    {
                                        $w_mail .= ",".$user_recs[0]['user_admin_mail_3'];
                                        $w_mail_name = "";
                                    }
                                }

                                if ( $w_mail != "" ){

                                    // smarty set
                                    $msm = new UserBlade();
                                    $data_rec = array();
                                    $data_rec['event_name']        = $event_rec['event_name'];
                                    $data_rec['kigyou_name']       = $user_recs[0]['user_kigyou_name'];
                                    $data_rec['name']              = $user_recs[0]['user_name'];
                                    $data_rec['tantou_name']       = $admin_recs[0]['admin_name'];
                                    _setAssign($msm,$data_rec);

                                    // template set
                                    // if( $helth_ans == "はい"){
                                        $mail_tpl = "kaijyou_in_notice.tpl";
                                    // }else{
                                    //     $mail_tpl = "kaijyou_not_in_notice.tpl";
                                    // }

                                    $ret = _bladeFetchFromMailTplDB( $msm, $mail_tpl );
                                    $title = $ret['subject'];
                                    $body = $ret['body'];

                                    $attach = array();
                                    // エリア入退場メール送信停止
                                    //_accessSendMail( _SYSTEM_INFO_MAIL, _SYSTEM_INFO_MAIL_NAME, $w_mail, $w_mail_name, $title, $body, $attach );

                                }
                            }
                        }
                        // ---------------------------------------------------------------------


                    }
                }
            }
        }

        if($project_name_prefix == "app_"){
            if( _count($err_msg) > 0 ){
                _ngReturn( $err_msg[0] );
            }
        }
    }

    $area_recs = _select("select * from m_area where area_delete_date is null and area_event_id='"._as($event_rec['event_id'])."' and area_id="._as($_request['area_id']));
    if($project_name_prefix != "app_"){
        $blade->assign('area_rec',$area_recs[0]);
    }

    $sql = "";
    $sql .= "select";
    $sql .= " count(ainout_id) as cnt";
    $sql .= " from t_area_inout";
    $sql .= " where";
    $sql .= " ainout_delete_date is null";
    $sql .= " and ainout_event_id = '"._as($event_rec['event_id'])."'";
    $sql .= " and ainout_area_id = "._as($_request['area_id'])."";
    $sql .= " and ainout_time_in is not null";
    $sql .= " and ainout_time_in != ''";
    $sql .= " and ainout_time_in >= '".$_request['ymd']." 00:00:00'";
    $sql .= " and ainout_time_in < '".$_request['ymd']." 24:00:00'";
    $sql .= " and (ainout_time_out is null or (ainout_time_out is not null and ainout_time_out='') )";
    $now_recs = _select($sql);
    if($project_name_prefix == "app_"){
        $return_arr['data']['now_count'] = intval($now_recs[0]['cnt']);
        $return_arr['data']['inout_kbn'] = $success_inout;
    }else{
        $blade->assign('now_cnt', intval($now_recs[0]['cnt']) );

        $blade->assign('ymd', $_request['ymd']);
        $blade->assign('success', $success_msg);
        $blade->assign('disp_ymd', date("n月j日",strtotime($_request['ymd']) )."（"._getYoubi($_request['ymd'])."）" );

        if($_request['ymd']!=date("Y/m/d")){
            $blade->assign('ymd_alert','<span style="color:red;">[注意]本日ではありません</span>');
        }

        $contents_tpl = "area_in_out.html";
    }
