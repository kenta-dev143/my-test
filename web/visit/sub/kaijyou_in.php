<?php
    if( !defined("_PROJECT_DISP_NAME") ){
        die("System Error");
    }
    if( $_request['ymd']=="" ){
        die("System Error2");
    }

    $kaitou_zumi = false;

    if($_request['exec']=="qrread"){
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
//                        $qr_code = $_request['qr_code'];
//                        $user_rec = $user_recs[0];
//
//                        $helth_recs = _select("select * from t_helth where helth_delete_date is null and helth_user_id='"._as($rd_user_id)."' and helth_event_id='"._as($rd_event_id)."' and helth_ymd='"._as($_request['ymd'])."'");
//                        if(_count($helth_recs) > 0){
//                            if($helth_recs[0]['helth_ans']=='y'){
                                $_request['exec'] = "save";
                                $_request['ans'] = 'y';
                                $kaitou_zumi = true;
//                            }
//                        }
                    }
                }
            }
        }
    }
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
                        //$sql .= " and (kinout_time_out is null or (kinout_time_out is not null and kinout_time_out='') )";
                        $kai_in_recs = _select($sql);
                        if(_count($kai_in_recs) > 0){
                            for ($i=0; $i < _count($kai_in_recs); $i++) {
                                if($kai_in_recs[$i]['kinout_time_out']==""){
                                    //未退出
                                    $now_minnus1 = date("Y/m/d H:i:s",strtotime($now." -1 seconds"));
                                    $array = array();
                                    $array['kinout_time_out'] = "'"._as($now_minnus1)."'";
                                    $array['kinout_update_date'] = "'".$_now_timestamp."'";
                                    $where = "kinout_id = ".$kai_in_recs[$i]['kinout_id'];
                                    _update('t_kaijyou_inout',$array,$where);
                                }
                            }
                        }

                        if($_request['ans']=="y"){


                            //会場入場レコード作成
                            $array = array();
                            $array['kinout_user_id'] = "'"._as($rd_user_id)."'";
                            $array['kinout_event_id'] = "'"._as($rd_event_id)."'";
                            $array['kinout_time_in'] = "'"._as($now)."'";
                            $array['kinout_time_out'] = "''";
                            if(_count($kai_in_recs) == 0){
                                //会場INレコードがまだなかった
                                $array['kinout_fst_record'] = "1";
                            }else{
                                $array['kinout_fst_record'] = "0";
                            }
                            $array['kinout_insert_date'] = "'".$_now_timestamp."'";
                            $array['kinout_update_date'] = "'".$_now_timestamp."'";
                            _insert('t_kaijyou_inout',$array);

                            //健康記録
                            if($kaitou_zumi==false){

                                $helth_recs = _select("select * from t_helth where helth_delete_date is null and helth_user_id='"._as($rd_user_id)."' and helth_event_id='"._as($rd_event_id)."' and helth_ymd='"._as($_request['ymd'])."'");

                                $array = array();
                                $array['helth_ans'] = "'"._as($_request['ans'])."'";
                                $array['helth_update_date'] = "'".$_now_timestamp."'";

                                if(_count($helth_recs) == 0){
                                    $array['helth_user_id'] = "'"._as($rd_user_id)."'";
                                    $array['helth_event_id'] = "'"._as($rd_event_id)."'";
                                    $array['helth_ymd'] = "'"._as($_request['ymd'])."'";
                                    $array['helth_insert_date'] = "'".$_now_timestamp."'";
                                    _insert('t_helth',$array);
                                }else{
                                    $where = "helth_delete_date is null and helth_user_id='"._as($rd_user_id)."' and helth_event_id='"._as($rd_event_id)."' and helth_ymd='"._as($_request['ymd'])."'";
                                    _update('t_helth',$array,$where);
                                }
                            }

                        }elseif($_request['ans']=="n"){
                            $helth_recs = _select("select * from t_helth where helth_delete_date is null and helth_user_id='"._as($rd_user_id)."' and helth_event_id='"._as($rd_event_id)."' and helth_ymd='"._as($_request['ymd'])."'");

                            $array = array();
                            $array['helth_ans'] = "'"._as($_request['ans'])."'";
                            $array['helth_update_date'] = "'".$_now_timestamp."'";

                            if(_count($helth_recs) == 0){
                                $array['helth_user_id'] = "'"._as($rd_user_id)."'";
                                $array['helth_event_id'] = "'"._as($rd_event_id)."'";
                                $array['helth_ymd'] = "'"._as($_request['ymd'])."'";
                                $array['helth_insert_date'] = "'".$_now_timestamp."'";
                                _insert('t_helth',$array);
                            }else{
                                $where = "helth_delete_date is null and helth_user_id='"._as($rd_user_id)."' and helth_event_id='"._as($rd_event_id)."' and helth_ymd='"._as($_request['ymd'])."'";
                                _update('t_helth',$array,$where);
                            }
                        }else{

                        }

                        _query($conn,'commit');

                        if($project_name_prefix != "app_"){
                            $blade->assign('complete', 1);
                        }


                        if(_count($kai_in_recs) == 0){
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
                                // 健康チェックの回答
                                if ( $_request['ans'] == "y" ){
                                    $helth_ans = "はい";
                                } else if  ( $_request['ans'] == "n" ){
                                    $helth_ans = "いいえ";
                                } else{
                                    $sql = "";
                                    $sql .= " select *"."\n";
                                    $sql .= " from t_helth"."\n";
                                    $sql .= " where helth_delete_date is null"."\n";
                                    $sql .= "  and helth_user_id = '"._as($rd_user_id)."'"."\n";
                                    $sql .= "  and helth_event_id = '"._as($rd_event_id)."'"."\n";
                                    $sql .= "  and helth_ymd = '"._as($_request['ymd'])."'"."\n";
                                    $helth_recs = _select($sql);
                                    if ( $helth_recs[0]['helth_ans'] == "y" ){
                                        $helth_ans = "はい";
                                    } else if  ( $helth_recs[0]['helth_ans'] == "n" ){
                                        $helth_ans = "いいえ";
                                    }
                                }

                                // smarty set
                                $msm = new UserBlade();
                                //2021/06/03 Mod ----------- Before ------------
                                // $msm->assign('_SYSTEM_ROOT_URLS', _SYSTEM_ROOT_URLS);
                                // $msm->assign('helth_ans', $helth_ans);
                                // $msm->assign('m_event', $event_rec);
                                // $msm->assign('m_user',  $user_recs[0]);
                                // $msm->assign('m_admin', $admin_recs[0]);

                                // // template set
                                // if( $helth_ans == "はい"){
                                //     $mail_tpl = "kaijyou_in_notice.tpl";
                                // }else{
                                //     $mail_tpl = "kaijyou_not_in_notice.tpl";
                                // }

                                // $title_and_body = _smartyFetch( $msm, _SYSTEM_ROOT_DIR . '/mail/' . $mail_tpl );
                                // $w_arr = explode ("\n", $title_and_body, 2 );
                                // $title = array_shift( $w_arr );
                                // if( substr( $title, strlen( $title ) - 1, 1 ) == "\r" ){
                                //     $title = substr( $title, 0, strlen( $title ) - 1 );
                                // }
                                // $body = join( "\n", $w_arr );
                                //2021/06/03 Mod ----------- After ------------
                                $data_rec = array();
                                $data_rec['event_name']        = $event_rec['event_name'];
                                $data_rec['kigyou_name']       = empty($user_recs[0]['user_company_name']) ? $user_recs[0]['user_kigyou_name'] : $user_recs[0]['user_company_name'];
                                $data_rec['name']              = $user_recs[0]['user_name'];
                                $data_rec['tantou_name']       = $admin_recs[0]['admin_name'];
                                _setAssign($msm,$data_rec);

                                // template set
                                if( $helth_ans == "はい"){
                                    $mail_tpl = "kaijyou_in_notice.tpl";
                                }else{
                                    $mail_tpl = "kaijyou_not_in_notice.tpl";
                                }

                                $ret = _bladeFetchFromMailTplDB( $msm, $mail_tpl );
                                $title = $ret['subject'];
                                $body = $ret['body'];
                                //2021/06/03 Mod ----------- End ------------

                                // 入場者が「招待者」の場合のみメールを送信する
                                if ($user_recs[0]['user_big_cate'] < 5 && $user_recs[0]['reception_mail_flg'] == 1) {
                                    $attach = array();
                                    // _sendMailEx( _SYSTEM_INFO_MAIL, _SYSTEM_INFO_MAIL_NAME, $w_mail, $w_mail_name, $title, $body, $attach );
                                    _accessSendMail( _SYSTEM_INFO_MAIL, _SYSTEM_INFO_MAIL_NAME, $w_mail, $w_mail_name, $title, $body, $attach );
                                }
                            }
                        }
                    } // 入場処理 終端
                }
            }
        }
    }

    if($project_name_prefix == "app_"){
        $return_arr['data']['inout_kbn'] = "in";
    } else {

        $blade->assign('ymd', $_request['ymd']);
        $blade->assign('disp_ymd', date("n月j日",strtotime($_request['ymd']) )."（"._getYoubi($_request['ymd'])."）" );

        if($_request['ymd']!=date("Y/m/d")){
            $blade->assign('ymd_alert','<span style="color:red;">[注意]本日ではありません</span>');
        }

        $blade->assign('now_md', date("md"));

//        if($_request['exec']=="qrread" && $qr_code!=""){
//            $blade->assign('qr_code',$qr_code);
//            $blade->assign('user_rec',$user_rec);
//            $contents_tpl = "kaijyou_in_ans.html";
//        }else{

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
            if($_request['exec']=="save" && $_request['ans']=="n"){
                $contents_tpl = "kaijyou_in_wait.html";
            }else{
                $contents_tpl = "kaijyou_in.html";
            }
//    }
    }
