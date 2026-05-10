<?php
    if( !defined("_PROJECT_DISP_NAME") ){
        die("System Error");
    }

    if( $_SESSION[_PROJECT_NAME]['admin_login']['admin_id'] == "" ){
        die('System Error');
    }

    //メール送信機能は権限１・２（全体集計見れる）人のみに
    if( $_SESSION[_PROJECT_NAME]['admin_login']['admin_syuukei_etsuran_kengen'] == "1" ){
        die('System Error');
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

    $sentakuTaisyouCnt = 0;
    $limit = 50;

    // ******************************************************************************************************
    // 検索
    // ******************************************************************************************************
    if( $_request['exec'] == "search" ){
        unset( $this_sess['nochk_user_arr'] );
        $this_sess['nochk_user_arr'] = array();
        unset( $this_sess['search_condition'] );
        $this_sess['search_condition'] = array();
        $this_sess['search_condition'] = _array_merge( $this_sess['search_condition'], $_request );
    }elseif( $_request['exec'] == "save" ){

        if ( $_request['mail_tsuuchi'] == '1' ){
            $this_sess['mail_tsuuchi'] = 1;
        }else{
            $this_sess['mail_tsuuchi'] = 0;
        }

        if ( $_request['mail_timing'] == 'ato' ){
            $this_sess['mail_timing'] = "ato";
        }else{
            $this_sess['mail_timing'] = "now";
        }

        for ($i=0; $i < _count($_request['user_ids']); $i++) {
            if( $_request['user_chks'][$i] != "1"){
                $this_sess['nochk_user_arr'][ $_request['user_ids'][$i] ] = $_request['user_ids'][$i];
            }else{
                $this_sess['nochk_user_arr'][ $_request['user_ids'][$i] ] = "";
                unset( $this_sess['nochk_user_arr'][ $_request['user_ids'][$i] ] );
            }
        }

    }elseif( $_request['offset'] != ""  || $_request['exec'] == "gotoDetail"){
        if( $_request['offset'] != ""){
            $this_sess['search_condition']['offset'] = $_request['offset'];
        }


        if ( $_request['mail_tsuuchi'] == '1' ){
            $this_sess['mail_tsuuchi'] = 1;
        }else{
            $this_sess['mail_tsuuchi'] = 0;
        }

        if ( $_request['mail_timing'] == 'ato' ){
            $this_sess['mail_timing'] = "ato";
        }else{
            $this_sess['mail_timing'] = "now";
        }

        for ($i=0; $i < _count($_request['user_ids']); $i++) {
            if( $_request['user_chks'][$i] != "1"){
                $this_sess['nochk_user_arr'][ $_request['user_ids'][$i] ] = $_request['user_ids'][$i];
            }else{
                $this_sess['nochk_user_arr'][ $_request['user_ids'][$i] ] = "";
                unset( $this_sess['nochk_user_arr'][ $_request['user_ids'][$i] ] );
            }
        }

        if($_request['exec'] == "gotoDetail"){
            header("Location: ?page=user_edit&id=".$_request['user_id']."&from_page=".$page );
            exit();
        }
    }elseif( $_request['sess_no_init'] == "" ){
        unset( $this_sess['search_condition'] );
        $this_sess['search_condition']['order_by'] = "user_kigyou_name_kana asc,user_name_kana asc";
        $this_sess['search_condition']['ignore_mitouroku_flag'] = 0;

        $this_sess['search_condition']['syoutai_raijyou'] = "";
        $this_sess['mail_tsuuchi'] = 0;
        $this_sess['mail_timing'] = "now";

        $this_sess['nochk_user_arr'] = array();

        $_request['exec'] = "search";
    }

    if($this_sess['search_condition']['order_by']==''){
        $this_sess['search_condition']['order_by'] = "user_kigyou_name_kana asc,user_name_kana asc";
    }

    // ******************************************************************************************************
    // 並び順配列作成
    // ******************************************************************************************************
    $order_by_arr = array();
    $order_by_arr['user_kigyou_name_kana asc,user_name_kana asc']                          = "企業名順";
    $order_by_arr['user_name_kana asc,user_kigyou_name_kana asc']                          = "氏名順";
    $order_by_arr['user_insert_date desc']                                                 = "登録日（降順）";
    $order_by_arr['user_insert_date asc']                                                  = "登録日（昇順）";
    $blade->assign('order_by_arr',$order_by_arr);


    $_conf_syoutai_raijyou = array();
    $_conf_syoutai_raijyou['syoutai']          = "WEB展示会(ｶﾞｲﾄﾞﾌﾞｯｸ)ｻｲﾝｱｯﾌﾟ分";
    $_conf_syoutai_raijyou['raijyou']          = "来場者系ｻｲﾝｱｯﾌﾟ分(出展社・運営サポート・施工業・その他)";
    $_conf_syoutai_raijyou['raijyou_syutten']  = "　├来場者系ｻｲﾝｱｯﾌﾟ分(出展社)";
    $_conf_syoutai_raijyou['raijyou_sekou']    = "　├来場者系ｻｲﾝｱｯﾌﾟ分(運営サポート・施工業)";
    $_conf_syoutai_raijyou['raijyou_sonota']   = "　└来場者系ｻｲﾝｱｯﾌﾟ分(その他)";
    $_conf_syoutai_raijyou['syoutai_s']        = "招待者系ｻｲﾝｱｯﾌﾟ分(メーカー・商社／卸・その他)";
    $_conf_syoutai_raijyou['syoutai_s_maker']  = "　├招待者系ｻｲﾝｱｯﾌﾟ分(メーカー)";
    $_conf_syoutai_raijyou['syoutai_s_orosi']  = "　├招待者系ｻｲﾝｱｯﾌﾟ分(商社／卸)";
    $_conf_syoutai_raijyou['syoutai_s_sonota'] = "　└招待者系ｻｲﾝｱｯﾌﾟ分(その他)";
    $blade->assign('_conf_syoutai_raijyou',$_conf_syoutai_raijyou);

    // $_conf_mail_status = array();
    // $_conf_mail_status[''] = "全て";
    // $_conf_mail_status['mi'] = "未送信";
    // $_conf_mail_status['sumi'] = "送信済み";
    // $_conf_mail_status['err'] = "送信エラー";
    // $blade->assign('_conf_mail_status',$_conf_mail_status);

    // $_conf_pass_set = array();
    // $_conf_pass_set[''] = "全て";
    // $_conf_pass_set['mi'] = "未設定";
    // $_conf_pass_set['sumi'] = "設定済み";
    // $blade->assign('_conf_pass_set',$_conf_pass_set);


    if($_request['exec'] == "search" || $_request['offset'] != "" || $_request['sess_no_init']!="" ||
       $_request['exec'] == "save"){

        if( _count($err_msg) == 0 ){

            // ******************************************************************************************************
            // 共通WHERE
            // ******************************************************************************************************
            $where = "";
            $where .= "user_delete_date is null";
            $where .= " and user_syounin_flg = 0"; //未承認
            $where .= " and user_event_id = '"._as($_SESSION[_PROJECT_NAME]['select_event_id'])."'";

            // -------------------------------------- //
            // WHERE権限による制御
            // -------------------------------------- //
            // 担当ユーザーのみ閲覧フラグ（0:全て閲覧可、1:支店部署に紐づくユーザーのみ閲覧可、2:自身に紐づくユーザーのみ閲覧可）
            $join = "";
            if ( $_SESSION[_PROJECT_NAME]['admin_login']['admin_user_kengen'] == 1 ){
                // 2021/05/14 mod ------- before ------
                // $join .= " inner join "."\n";
                // $join .= " (select admin_id from v_admin where admin_delete_date is null and admin_syozoku_id = '"._as($_SESSION[_PROJECT_NAME]['admin_login']['admin_syozoku_id'])."')"."\n";
                // $join .= " as sub_admin on (sub_admin.admin_id = v_user.user_admin_id) "."\n";
                // 2021/05/14 mod ------- after ------
                $admin_syozoku_id = $_SESSION[_PROJECT_NAME]['admin_login']['admin_syozoku_id'];
                if ( $admin_syozoku_id != '' ){
                    $sql = "";
                    $sql .= " select syozoku_szkgrp_id"."\n";
                    $sql .= " from m_syozoku"."\n";
                    $sql .= " where syozoku_id = '"._as( $admin_syozoku_id )."'"."\n";
                    $sql .= "  and syozoku_delete_date is null"."\n";
                    $syozoku_recs = _select( $sql );
                    if ( $syozoku_recs[0]['syozoku_szkgrp_id'] == '' ){
                        $syozoku_ids = "'".$admin_syozoku_id."'";
                    } else {
                        $sql = "";
                        $sql .= " select syozoku_szkgrp_id, syozoku_id"."\n";
                        $sql .= " from m_syozoku"."\n";
                        $sql .= " where syozoku_szkgrp_id = '"._as( $syozoku_recs[0]['syozoku_szkgrp_id'] )."'"."\n";
                        $sql .= "  and syozoku_delete_date is null"."\n";
                        $syozoku_recs = _select( $sql );
                        for ($n=0; $n < _count($syozoku_recs); $n++) {
                            if ( $n > 0 ) $syozoku_ids .= ",";
                            $syozoku_ids .= "'".$syozoku_recs[$n]['syozoku_id']."'";
                        }
                    }

                    $join .= " inner join "."\n";
                    $join .= " (select admin_id from v_admin where admin_delete_date is null and admin_syozoku_id in (".$syozoku_ids.") )"."\n";
                    $join .= " as sub_admin on (sub_admin.admin_id = v_user.user_admin_id) "."\n";
                }
                // 2021/05/14 mod ------- end ------

            } elseif ( $_SESSION[_PROJECT_NAME]['admin_login']['admin_user_kengen'] == 2 ){
                $where .= " and user_admin_id = '"._as( $_SESSION[_PROJECT_NAME]['admin_login']['admin_id'] )."'"."\n";
            }

            //招待者・来序者
            if($this_sess['search_condition']['syoutai_raijyou'] !=""){
                if ( $this_sess['search_condition']['syoutai_raijyou'] == 'syoutai'  ){
                    $where .= " and user_big_cate <= 4";
                    //リアル未招待
                    $where .= " and ( user_raijyou_yotei_time is null or ( user_raijyou_yotei_time is not null and user_raijyou_yotei_time = '' ) ) ";
                    //WEB招待
                    $where .= " and user_web = 1";
                } elseif ( $this_sess['search_condition']['syoutai_raijyou'] == 'raijyou'  ){
                    $where .= " and user_big_cate in (5,6,8)";
                    //リアル招待
                    $where .= " and ( user_raijyou_yotei_time is not null and user_raijyou_yotei_time != '' ) ";
                } elseif ( $this_sess['search_condition']['syoutai_raijyou'] == 'raijyou_syutten'  ){
                    $where .= " and user_big_cate = 5";
                    //リアル招待
                    $where .= " and ( user_raijyou_yotei_time is not null and user_raijyou_yotei_time != '' ) ";
                } elseif ( $this_sess['search_condition']['syoutai_raijyou'] == 'raijyou_sekou'  ){
                    $where .= " and user_big_cate = 6";
                    //リアル招待
                    $where .= " and ( user_raijyou_yotei_time is not null and user_raijyou_yotei_time != '' ) ";
                } elseif ( $this_sess['search_condition']['syoutai_raijyou'] == 'raijyou_sonota'  ){
                    $where .= " and user_big_cate = 8";
                    //リアル招待
                    $where .= " and ( user_raijyou_yotei_time is not null and user_raijyou_yotei_time != '' ) ";
                } else if ( $this_sess['search_condition']['syoutai_raijyou'] == 'syoutai_s'  ) {
                    $where .= " and user_big_cate <= 4";
                    $where .= " and user_mid_cate in (35, 40, 99)";
                    $where .= " and ( user_raijyou_yotei_time is not null and user_raijyou_yotei_time != '' ) ";
                } else if ( $this_sess['search_condition']['syoutai_raijyou'] == 'syoutai_s_maker'  ) {
                    $where .= " and user_big_cate <= 4";
                    $where .= " and user_mid_cate = 40";
                    $where .= " and ( user_raijyou_yotei_time is not null and user_raijyou_yotei_time != '' ) ";
                } else if ( $this_sess['search_condition']['syoutai_raijyou'] == 'syoutai_s_orosi'  ) {
                    $where .= " and user_big_cate <= 4";
                    $where .= " and user_mid_cate = 35";
                    $where .= " and ( user_raijyou_yotei_time is not null and user_raijyou_yotei_time != '' ) ";
                } else if ( $this_sess['search_condition']['syoutai_raijyou'] == 'syoutai_s_sonota'  ) {
                    $where .= " and user_big_cate <= 4";
                    $where .= " and user_mid_cate = 99";
                    $where .= " and ( user_raijyou_yotei_time is not null and user_raijyou_yotei_time != '' ) ";
                }
            }else{

//                    $where .= " and (";
//
//                    $where .= "     (";
//                    $where .= "            user_big_cate <= 4";
//                    $where .= "        and ( user_raijyou_yotei_time is null or ( user_raijyou_yotei_time is not null and user_raijyou_yotei_time = '' ) ) ";
//                    $where .= "        and user_web = 1";
//                    $where .= "     )";
//
//                    $where .= "     or ";
//
//                    $where .= "     (";
//                    $where .= "            user_big_cate in (5,6,8)";
//                    $where .= "        and ( user_raijyou_yotei_time is not null and user_raijyou_yotei_time != '' ) ";
//                    $where .= "     )";
//
//                    $where .= " )";
            }

            // //通知メール状態
            // if($this_sess['search_condition']['mail_status'] !=""){
            //     if( $this_sess['search_condition']['mail_status'] == 'mi'  ){
            //         $where .= " and user_mail_send_kbn = 0";
            //     }elseif( $this_sess['search_condition']['mail_status'] == 'sumi'  ){
            //         $where .= " and user_mail_send_kbn = 1";
            //     }elseif( $this_sess['search_condition']['mail_status'] == 'err'  ){
            //         $where .= " and user_mail_send_kbn = 2";
            //     }
            // }

            // //リアル招待
            // if( $this_sess['search_condition']['syoutai_yotei_time_ari'] != ""){
            //     $where .= " and ( user_raijyou_yotei_time is not null and user_raijyou_yotei_time != '' ) ";
            // }

            // //WEB招待
            // if($this_sess['search_condition']['user_web'] !=""){
            //     $where .= " and user_web = "._as($this_sess['search_condition']['user_web'])."";
            // }

            // //PASS設定状態
            // if($this_sess['search_condition']['pass_set'] =="mi"){
            //     $where .= " and (user_pass is null or (user_pass is not null and user_pass = '"._as(md5('_NEED_PASS_SET_'))."' ) )";
            // }elseif($this_sess['search_condition']['pass_set'] =="sumi"){
            //     $where .= " and (user_pass is not null and  user_pass != '"._as(md5('_NEED_PASS_SET_'))."' )";
            // }

            //企業名
            if($this_sess['search_condition']['user_kigyou_name'] !=""){
                $where .= " and user_kigyou_name like '%"._as($this_sess['search_condition']['user_kigyou_name'])."%'";
            }

            //企業名カナ
            if($this_sess['search_condition']['user_kigyou_name_kana'] !=""){
                $where .= " and user_kigyou_name_kana like '%"._as($this_sess['search_condition']['user_kigyou_name_kana'])."%'";
            }

            if ($this_sess['search_condition']['ignore_mitouroku_flag'] == "1") {
                $where .= " and user_company_id  is not null and user_company_id != 1 ";
            }

            // //VIP
            // if($this_sess['search_condition']['user_vip_flg'] !=""){
            //     $where .= " and user_vip_flg = "._as($this_sess['search_condition']['user_vip_flg'])."";
            // }

            //氏名
            if($this_sess['search_condition']['user_name'] !=""){
                $where .= " and user_name like '%"._as($this_sess['search_condition']['user_name'])."%'";
            }

            //氏名カナ
            if($this_sess['search_condition']['user_name_kana'] !=""){
                $where .= " and user_name_kana like '%"._as($this_sess['search_condition']['user_name_kana'])."%'";
            }

            //メアド
            if($this_sess['search_condition']['user_mail'] !=""){
                $where .= " and ( user_login_id like '%"._as($this_sess['search_condition']['user_mail'])."%' or user_mail like '%"._as($this_sess['search_condition']['user_mail'])."%' )";
            }

            //担当エリア
            if($this_sess['search_condition']['admin_tanarea_id'] !=""){
                $where .= " and admin_tanarea_id = "._as($this_sess['search_condition']['admin_tanarea_id'])."";
            }

            //担当者支店名・部署名
            if($this_sess['search_condition']['admin_syozoku_id'] !=""){
                $where .= " and admin_syozoku_id = '"._as($this_sess['search_condition']['admin_syozoku_id'])."'";
            }

            //担当者の指定
            if($this_sess['search_condition']['admin_id'] !=""){
                $where .= " and admin_id = '"._as($this_sess['search_condition']['admin_id'])."'";

                $sql = "";
                $sql .= " select admin_name,syozoku_id, syozoku_name "."\n";
                $sql .= " from v_admin "."\n";
                $sql .= " left join m_syozoku on (m_syozoku.syozoku_id = v_admin.admin_syozoku_id) "."\n";
                $sql .= " where admin_delete_date is null "."\n";
                $sql .= " and syozoku_delete_date is null "."\n";
                $sql .= " and admin_id = '"._as($this_sess['search_condition']['admin_id'])."'";
                $tantou_recs = _select($sql);

                $this_sess['search_condition']['syozoku_name']     = $tantou_recs[0]['syozoku_name'];
                $this_sess['search_condition']['admin_name']       = $tantou_recs[0]['admin_name'];
                $this_sess['search_condition']['admin_syozoku_id'] = $tantou_recs[0]['syozoku_id'];

            }

            //担当者メールアドレス(PC)
            if($this_sess['search_condition']['admin_mail'] !=""){
                $where .= " and admin_mail = '"._as($this_sess['search_condition']['admin_mail'])."'";
            }

            // //タグ文字
            // if($this_sess['search_condition']['user_tag'] !=""){
            //     $where .= " and user_tag = '"._as($this_sess['search_condition']['user_tag'])."'";
            // }



            // ******************************************************************************************************
            // データ抽出
            // ******************************************************************************************************
            $offset = 0;
            if( $this_sess['search_condition']['offset'] != "" ){
                $offset = intval( $this_sess['search_condition']['offset'] );
            }
            // 件数取得SQL
            $sql  = "";
            $sql .= " select ";
            $sql .= "    count(v_user.user_id) as all_cnt";
            // $sql .= "   ,max(coalesce(v_user.user_raijyou_yotei_time,'')) as max_raijyou_yotei_time";
            // $sql .= "   ,min(coalesce(v_user.user_raijyou_yotei_time,'')) as min_raijyou_yotei_time";
            $sql .= " from v_user ";
            // $sql .= " left join (";
            // $sql .= "    select";
            // $sql .= "       kinout_user_id";
            // $sql .= "      ,kinout_event_id";
            // $sql .= "      ,min(kinout_time_in) as min_kinout_time_in";
            // $sql .= "    from t_kaijyou_inout";
            // $sql .= "    group by kinout_user_id,kinout_event_id";
            // $sql .= " ) V_kaijyou_inout on (V_kaijyou_inout.kinout_user_id=v_user.user_id and V_kaijyou_inout.kinout_event_id=v_user.user_event_id)";
            $sql .= " left join v_admin on (v_admin.admin_id = v_user.user_admin_id)";
            if ( $join != '' ) $sql .= $join;
            $sql .= " where ".$where;

            $all_recs = _select($sql);

            $allcnt = 0;
            if($all_recs[0]['all_cnt'] > 0){
                $allcnt = $all_recs[0]['all_cnt'];
            }
            // $max_raijyou_yotei_time = "".$all_recs[0]['max_raijyou_yotei_time'];
            // $min_raijyou_yotei_time = "".$all_recs[0]['min_raijyou_yotei_time'];
            // if($max_raijyou_yotei_time=="" && $min_raijyou_yotei_time==""){
            //     $real_ari = false;
            //     $web_ari = true;
            // }elseif($max_raijyou_yotei_time!="" && $min_raijyou_yotei_time!=""){
            //     $real_ari = true;
            //     $web_ari = false;
            // }else{
            //     $real_ari = true;
            //     $web_ari = true;
            // }

            $sentakuTaisyouCnt = $allcnt - _count($this_sess['nochk_user_arr']);
            //$this_sess['sess_sentakuTaisyouCnt'] =  $sentakuTaisyouCnt;

            if( $_request['exec'] == "save" && $sentakuTaisyouCnt==0){
                $err_msg[] = "承認対象者が１人もいません。";
            }

            if($allcnt > 0){
                // 表示SQL
                $sql_base  = "";
                $sql_base .= " select v_user.*,coalesce(v_user.user_raijyou_yotei_time,'') as raijyou_yotei_time from v_user ";

                // $sql_base .= " left join (";
                // $sql_base .= "    select";
                // $sql_base .= "       kinout_user_id";
                // $sql_base .= "      ,kinout_event_id";
                // $sql_base .= "      ,min(kinout_time_in) as min_kinout_time_in";
                // $sql_base .= "    from t_kaijyou_inout";
                // $sql_base .= "    group by kinout_user_id,kinout_event_id";
                // $sql_base .= " ) V_kaijyou_inout on (V_kaijyou_inout.kinout_user_id=v_user.user_id and V_kaijyou_inout.kinout_event_id=v_user.user_event_id)";
                $sql_base .= " left join v_admin on (v_admin.admin_id = v_user.user_admin_id)";
                if ( $join != '' ) $sql_base .= $join;
                $sql_base .= " where ".$where;

                if( $_request['exec'] == "save" && $sentakuTaisyouCnt>0){

                    _query($conn,'begin');

                    $sql = $sql_base." order by raijyou_yotei_time asc";


                    if($this_sess['mail_tsuuchi']=="1"){

                        $tpl_sql = "";
                        $tpl_sql .= "select * from m_mail_template";
                        $tpl_sql .= " where";
                        $tpl_sql .= " mailt_delete_date is null";
                        $tpl_sql .= " and mailt_key = 'signup_syounin_real'";
                        $real_tpl_recs = _select($tpl_sql);

                        $tpl_sql = "";
                        $tpl_sql .= "select * from m_mail_template";
                        $tpl_sql .= " where";
                        $tpl_sql .= " mailt_delete_date is null";
                        $tpl_sql .= " and mailt_key = 'signup_syounin_web'";
                        $web_tpl_recs = _select($tpl_sql);

                        $real_ari = false;
                        $web_ari = false;
                        $row = 0;
                        $result = _query( $conn, $sql );
                        while( $rec = _fetchArray( $result, $row ) ){
                            $rec['checked'] = "checked";
                            if ( _count($this_sess['nochk_user_arr']) > 0 ){
                                if( array_search( $rec['user_id'] , $this_sess['nochk_user_arr'] ) !== FALSE ){
                                    $rec['checked'] = "";
                                }
                            }

                            if($rec['checked']=="checked"){
                                if($rec['raijyou_yotei_time']!=""){
                                    $real_ari = true;
                                }else{
                                    $web_ari = true;
                                }
                            }
                            $row++;
                        }

                        if($real_ari==true){
                            $array = array();
                            $array['mailhd_mailt_name'] = "'"._as($real_tpl_recs[0]['mailt_name'])."'";
                            $array['mailhd_mailt_key'] = "'"._as($real_tpl_recs[0]['mailt_key'])."'";
                            $array['mailhd_subject'] = "'"._as($real_tpl_recs[0]['mailt_subject'])."'";
                            $array['mailhd_body'] = "'"._as($real_tpl_recs[0]['mailt_body'])."'";
                            if($this_sess['mail_timing']=="ato"){
                                $array['mailhd_yoyaku_ymdhi']    = "'2099/12/31 23:59'";
                            }else{
                                $array['mailhd_yoyaku_ymdhi'] = "'"._as(date("Y/m/d H:i"))."'";
                            }
                            $array['mailhd_status'] = "0";
                            $array['mailhd_test_send_flg'] = "0"; //0:本番送信
                            $array['mailhd_insert_admin_id'] = "'"._as($_SESSION[_PROJECT_NAME]['admin_login']['admin_id'])."'";

                            $array['mailhd_insert_date'] = "'".$_now_timestamp."'";
                            $array['mailhd_update_date'] = "'".$_now_timestamp."'";
                            _insert('t_mail_head',$array);

                            $real_mailhd_id = $conn->insert_id; //insertされたAUTO_INCREMENTの値取得
                        }
                        if($web_ari==true){
                            $array = array();
                            $array['mailhd_mailt_name'] = "'"._as($web_tpl_recs[0]['mailt_name'])."'";
                            $array['mailhd_mailt_key'] = "'"._as($web_tpl_recs[0]['mailt_key'])."'";
                            $array['mailhd_subject'] = "'"._as($web_tpl_recs[0]['mailt_subject'])."'";
                            $array['mailhd_body'] = "'"._as($web_tpl_recs[0]['mailt_body'])."'";
                            if($this_sess['mail_timing']=="ato"){
                                $array['mailhd_yoyaku_ymdhi']    = "'2099/12/31 23:59'";
                            }else{
                                $array['mailhd_yoyaku_ymdhi'] = "'"._as(date("Y/m/d H:i"))."'";
                            }
                            $array['mailhd_status'] = "0";
                            $array['mailhd_test_send_flg'] = "0"; //0:本番送信
                            $array['mailhd_insert_admin_id'] = "'"._as($_SESSION[_PROJECT_NAME]['admin_login']['admin_id'])."'";

                            $array['mailhd_insert_date'] = "'".$_now_timestamp."'";
                            $array['mailhd_update_date'] = "'".$_now_timestamp."'";
                            _insert('t_mail_head',$array);

                            $web_mailhd_id = $conn->insert_id; //insertされたAUTO_INCREMENTの値取得
                        }

                    }

                    $result = _query( $conn, $sql );

                    $row = 0;
                    while( $rec = _fetchArray( $result, $row ) ){

                        $rec['checked'] = "checked";
                        if ( _count($this_sess['nochk_user_arr']) > 0 ){
                            if( array_search( $rec['user_id'] , $this_sess['nochk_user_arr'] ) !== FALSE ){
                                $rec['checked'] = "";
                            }
                        }

                        if($rec['checked']=="checked"){
                            //承認済みに
                            $array = array();
                            $array['user_syounin_flg'] = "1";
                            $array['user_update_date'] = "'".$_now_timestamp."'";
                            $upd_where = "user_id = '"._as($rec['user_id'])."'";
                            _update("m_user",$array,$upd_where);


                            if($this_sess['mail_tsuuchi']=="1"){
                                $array = array();
                                if($rec['raijyou_yotei_time']!=''){
                                    $array['maills_mailhd_id'] = "'"._as($real_mailhd_id)."'";
                                }else{
                                    $array['maills_mailhd_id'] = "'"._as($web_mailhd_id)."'";
                                }
                                $array['maills_user_id'] = "'"._as($rec['user_id'])."'";
                                $array['maills_mail_address'] = "'"._as($rec['user_mail'])."'";
                                $array['maills_event_id'] = "'"._as($_SESSION[_PROJECT_NAME]['select_event_id'])."'";
                                $array['maills_insert_date'] = "'".$_now_timestamp."'";
                                $array['maills_update_date'] = "'".$_now_timestamp."'";
                                _insert('t_mail_list',$array);
                            }
                        }

                        $row++;
                    }
                    _freeResult( $result );

                    _query($conn,'commit');

                    // $this_sess  = array();
                    // unset( $_SESSION[_PROJECT_NAME][$page] );
                    // unset( $this_sess );
                    // $this_sess = &$_SESSION[_PROJECT_NAME][$page];
                    unset( $this_sess['nochk_user_arr'] );
                    $this_sess['nochk_user_arr'] = array();

                    $_request['exec'] = "search";

                    $success_msg = "承認処理を完了しました。";

                    //件数取り直し
                    $sql  = "";
                    $sql .= " select ";
                    $sql .= "    count(v_user.user_id) as all_cnt";
                    $sql .= " from v_user ";
                    $sql .= " left join v_admin on (v_admin.admin_id = v_user.user_admin_id)";
                    if ( $join != '' ) $sql .= $join;
                    $sql .= " where ".$where;

                    $all_recs = _select($sql);

                    $allcnt = 0;
                    if($all_recs[0]['all_cnt'] > 0){
                        $allcnt = $all_recs[0]['all_cnt'];
                    }
                    $sentakuTaisyouCnt = $allcnt - _count($this_sess['nochk_user_arr']);
                    //$this_sess['sess_sentakuTaisyouCnt'] =  $sentakuTaisyouCnt;

                }

                $sql = $sql_base." order by ".$this_sess['search_condition']['order_by'];
                $sql .= " limit ".$offset." , ".$limit;

                $main_recs = _select($sql);
                $chkOffCnt = 0;
                $fstChkIdx = -1;
                for ($i=0; $i < _count($main_recs); $i++) {
                    $times = explode("#",$main_recs[$i]['user_raijyou_yotei_time']);
                    // if($times!=""){
                    if($main_recs[$i]['user_raijyou_yotei_time']!=""){
                        list($w_ymd,$w_time) = explode(" ",$times[0],2);
                        $hoka = "";
                        if(_count($times) > 1) $hoka = " 他";
                        //2021/07/08 Mod -------- Before ---------
                        // $main_recs[$i]['disp_user_raijyou_yotei_time'] = date("n月j日", strtotime($w_ymd) )." ".$w_time.$hoka;
                        //2021/07/08 Mod -------- After ---------
                        if($w_ymd=='2999/01/01'){
                            $main_recs[$i]['disp_user_raijyou_yotei_time'] = $w_time.$hoka;
                        }else{
                            $main_recs[$i]['disp_user_raijyou_yotei_time'] = date("n月j日", strtotime($w_ymd) )." ".$w_time.$hoka;
                        }
                        //2021/07/08 Mod -------- End ---------
                    }
                    if($main_recs[$i]['min_kinout_time_in']!=""){
                        $main_recs[$i]['disp_min_kinout_time_in'] = date("n月j日 H:i", strtotime($main_recs[$i]['min_kinout_time_in']) );
                    }
                    $main_recs[$i]['disp_big_cate'] = $_conf_big_cate_detail[$main_recs[$i]['user_big_cate']];

                    // if($main_recs[$i]['user_pass']==md5("_NEED_PASS_SET_") ){
                    //     $main_recs[$i]['pass_set'] = "<span style=\"color:red;\">未設定</span>";
                    // }else{
                    //     $main_recs[$i]['pass_set'] = "<span style=\"color:blue;\">済み</span>";
                    // }


                    if( (intval($main_recs[$i]['user_big_cate']) <= 4) && $main_recs[$i]['user_raijyou_yotei_time']=="" && $main_recs[$i]['user_web']==1){
                        $main_recs[$i]['signup_kbn'] = "web";
                    }else{
                        $main_recs[$i]['signup_kbn'] = "real";
                    }

                    $main_recs[$i]['checked'] = "checked";
                    if ( _count($this_sess['nochk_user_arr']) > 0 ){
                        if( array_search( $main_recs[$i]['user_id'] , $this_sess['nochk_user_arr'] ) !== FALSE ){
                            $main_recs[$i]['checked'] = "";
                            $chkOffCnt++;
                        }
                    }
                    if($fstChkIdx==-1){
                        if($main_recs[$i]['checked'] == "checked"){
                            $fstChkIdx = $i;
                        }
                    }
                }

                if( $chkOffCnt == $limit){
                    $blade->assign('allChkOff',"1");
                }else{
                    $blade->assign('allChk',"");
                }

                _make_pagenavi2( $blade, $_request, $offset, $allcnt, $limit );

                $blade->assign('main_recs', $main_recs);
            }

        }
    }

    _setAssign($blade,$this_sess);

    // //(招待者)来場予定日時
    // $wArr = explode("#", $select_event_rec['event_syoutai_yotei_time']);
    // $_conf_syoutai_yotei_time = array();
    // for ($i=0; $i < _count($wArr); $i++) {
    //     $dtArr = explode(" ", $wArr[$i],2);
    //     $ymd = $dtArr[0];
    //     $disp_ymd = date("n月j日",strtotime($dtArr[0]) )."（"._getYoubi($dtArr[0])."）";
    //     $hi = $dtArr[1];
    //     $_conf_syoutai_yotei_time[$ymd]['disp_ymd'] = $disp_ymd;

    //     $checked="";
    //     for ($j=0; $j < _count($this_sess['search_condition']['user_syoutai_yotei_time']); $j++) {
    //         if($this_sess['search_condition']['user_syoutai_yotei_time'][$j] == $wArr[$i]){
    //             $checked = "checked";
    //             break;
    //         }
    //     }
    //     $_conf_syoutai_yotei_time[$ymd]['his'][] = array('hi'=>$hi, 'checked'=>$checked);
    // }
    // $blade->assign('_conf_syoutai_yotei_time',$_conf_syoutai_yotei_time);

    // //(来場者)来場予定日時
    // $wArr = explode("#", $select_event_rec['event_raijyou_yotei_time']);
    // $_conf_raijyou_yotei_time = array();
    // for ($i=0; $i < _count($wArr); $i++) {
    //     $dtArr = explode(" ", $wArr[$i],2);
    //     $ymd = $dtArr[0];
    //     $disp_ymd = date("n月j日",strtotime($dtArr[0]) )."（"._getYoubi($dtArr[0])."）";
    //     $hi = $dtArr[1];
    //     $_conf_raijyou_yotei_time[$ymd]['disp_ymd'] = $disp_ymd;

    //     $checked="";
    //     for ($j=0; $j < _count($this_sess['search_condition']['user_raijyou_yotei_time']); $j++) {
    //         if($this_sess['search_condition']['user_raijyou_yotei_time'][$j] == $wArr[$i]){
    //             $checked = "checked";
    //             break;
    //         }
    //     }
    //     $_conf_raijyou_yotei_time[$ymd]['his'][] = array('hi'=>$hi, 'checked'=>$checked);
    // }
    // $blade->assign('_conf_raijyou_yotei_time',$_conf_raijyou_yotei_time);

    //担当者エリア
    $sql = "";
    $sql .= "select * from m_tantou_area";
    $sql .= " where";
    $sql .= " tanarea_delete_date is null";
    $sql .= " order by tanarea_id asc";
    $tanarea_recs = _select($sql);
    $_conf_tanarea = array();
    for ($i=0; $i < _count($tanarea_recs); $i++) {
        $_conf_tanarea[ $tanarea_recs[$i]['tanarea_id'] ] = $tanarea_recs[$i]['tanarea_name'];
    }
    $blade->assign('_conf_tanarea',$_conf_tanarea);

    //所属支店部署マスタ
    $sql = "";
    $sql .= "select * from m_syozoku";
    $sql .= " where";
    $sql .= " syozoku_delete_date is null";
    $sql .= " order by syozoku_id asc";
    $syozoku_recs = _select($sql);
    $_conf_syozoku = array();
    for ($i=0; $i < _count($syozoku_recs); $i++) {
        $_conf_syozoku[ $syozoku_recs[$i]['syozoku_id'] ] = $syozoku_recs[$i]['syozoku_name'];
    }
    $blade->assign('_conf_syozoku',$_conf_syozoku);

    $blade->assign('sentakuTaisyouCnt',$sentakuTaisyouCnt);
    //$blade->assign('sess_sentakuTaisyouCnt',$this_sess['sess_sentakuTaisyouCnt']);

    $blade->assign('this_page_limit',$limit);
    $blade->assign('main_recs_count', _count($main_recs) );

    $blade->assign('_conf_vip',$_conf_vip);
    // if($_request['exec'] == "search" || $_request['offset'] != "" || $_request['sess_no_init']!=""){
    //     $blade->assign('list_disp',"1");
    // }


    $blade->assign('title_bgcolor',$select_event_rec['event_title_bgcolor']);

    $active_menu = "signup_list";

    if($select_event_rec['event_name']==""){
        $contents_title = "サインアップ承認";
    }else{
        $contents_title = "サインアップ承認" . "（".$select_event_rec['event_name'] .":".$select_event_rec['event_kaijyou_name']."）";
    }

    $contents_tpl = "signup_list";
