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

    set_time_limit(180); //3分起動
    ini_set('memory_limit',"1024M"); //メモリ拡大

    // ******************************************************************************************************
    // 検索
    // ******************************************************************************************************
    if( $_request['exec'] == "search" ){
        unset( $this_sess['nochk_user_arr'] );
        $this_sess['nochk_user_arr'] = array();
        unset( $this_sess['search_condition'] );
        $this_sess['search_condition'] = array();
        $this_sess['search_condition'] = _array_merge( $this_sess['search_condition'], $_request );
    }elseif( $_request['exec'] == "user_kettei" ){
        $this_sess['mail_tpl'] = "";
        $this_sess['mailt_name'] = "";
        if( $_SESSION[_PROJECT_NAME]['admin_login']['admin_mail']!="" && _emailCheck($_SESSION[_PROJECT_NAME]['admin_login']['admin_mail'],'') != false ){
            $this_sess['test_addr'] = $_SESSION[_PROJECT_NAME]['admin_login']['admin_mail'];
        }else{
            $this_sess['test_addr'] = "";
        }
        $this_sess['mail_timing'] = "now";

        for ($i=0; $i < _count($_request['user_ids']); $i++) {
            if( $_request['user_chks'][$i] != "1"){
                $this_sess['nochk_user_arr'][ $_request['user_ids'][$i] ] = $_request['user_ids'][$i];
            }else{
                $this_sess['nochk_user_arr'][ $_request['user_ids'][$i] ] = "";
                unset( $this_sess['nochk_user_arr'][ $_request['user_ids'][$i] ] );
            }
        }

    }elseif( $_request['exec'] == "tpl_change" ){
        $this_sess['mail_tpl'] = $_request['mail_tpl'];

        $this_sess['yoyaku_ymd'] = '';
        $this_sess['yoyaku_hh'] = '';
        $this_sess['yoyaku_ii'] = '';

    }elseif( $_request['exec'] == "test_send" ){

        $this_sess['mail_subject'] = $_request['mail_subject'];
        $this_sess['mail_body'] = $_request['mail_body'];
        $this_sess['test_addr'] = $_request['test_addr'];
        $this_sess['mail_timing'] = $_request['mail_timing'];

        $this_sess['yoyaku_ymd'] = $_request['yoyaku_ymd'];
        $this_sess['yoyaku_hh'] = $_request['yoyaku_hh'];
        $this_sess['yoyaku_ii'] = $_request['yoyaku_ii'];

    }elseif( $_request['exec'] == "send" ){
        $this_sess['mail_subject'] = $_request['mail_subject'];
        $this_sess['mail_body'] = $_request['mail_body'];
        $this_sess['test_addr'] = $_request['test_addr'];
        $this_sess['mail_timing'] = $_request['mail_timing'];

        $this_sess['yoyaku_ymd'] = $_request['yoyaku_ymd'];
        $this_sess['yoyaku_hh'] = $_request['yoyaku_hh'];
        $this_sess['yoyaku_ii'] = $_request['yoyaku_ii'];

    }elseif( $_request['offset'] != ""  || $_request['exec'] == "gotoDetail"){
        if( $_request['offset'] != ""){
            $this_sess['search_condition']['offset'] = $_request['offset'];
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

        $this_sess['search_condition']['syoutai_raijyou'] = "syoutai";
        $this_sess['search_condition']['mail_status'] = "mi";
        $this_sess['mail_tpl'] = "";
        $this_sess['mailt_name'] = "";
        $this_sess['mail_timing'] = "now";

        $this_sess['yoyaku_ymd'] = '';
        $this_sess['yoyaku_hh'] = '';
        $this_sess['yoyaku_ii'] = '';

        $this_sess['nochk_user_arr'] = array();
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
    $_conf_syoutai_raijyou['syoutai'] = "招待者系";
    $_conf_syoutai_raijyou['raijyou'] = "来場者系(出展社・施工業者・AC社員等)";
    $blade->assign('_conf_syoutai_raijyou',$_conf_syoutai_raijyou);

    $_conf_mail_status = array();
    $_conf_mail_status[''] = "全て";
    $_conf_mail_status['mi'] = "未送信";
    $_conf_mail_status['sumi'] = "送信済み";
    $_conf_mail_status['err'] = "送信エラー";
    $blade->assign('_conf_mail_status',$_conf_mail_status);

    $_conf_pass_set = array();
    $_conf_pass_set[''] = "全て";
    $_conf_pass_set['mi'] = "未設定";
    $_conf_pass_set['sumi'] = "設定済み";
    $blade->assign('_conf_pass_set',$_conf_pass_set);

    $_conf_jigo_ans = array();
    $_conf_jigo_ans[''] = "全て　　　　";
    $_conf_jigo_ans['mi'] = "未回答";
    $_conf_jigo_ans['sumi'] = "回答済み";
    $blade->assign('_conf_jigo_ans',$_conf_jigo_ans);

    $sql = "";
    $sql .= "select * from m_mail_template";
    $sql .= " where";
    $sql .= " mailt_delete_date is null";
    $sql .= " and mailt_system_use_only = 0";
    $sql .= " order by";
    $sql .= " mailt_delete_fuka desc";
    $tpl_recs = _select($sql);

    for ($i=0; $i < _count($tpl_recs); $i++) {

        $add_str = "";
        $tpl_recs[$i]['disabled'] = "";
        if($tpl_recs[$i]['mailt_key']=="pass_set_annai" || $tpl_recs[$i]['mailt_key']=="pass_set_annai_web_only" ||
           $tpl_recs[$i]['mailt_key']=="pass_set_annai2" || $tpl_recs[$i]['mailt_key']=="pass_set_annai_web_only2"){
            if($this_sess['search_condition']['pass_set'] !="mi"){
                $add_str = " (ﾊﾟｽﾜｰﾄﾞ設定状態「未設定」に絞り込みが必要)";
                $tpl_recs[$i]['disabled'] = "disabled";
            }
        }elseif($tpl_recs[$i]['mailt_key']=="signup_syounin_real" || $tpl_recs[$i]['mailt_key']=="signup_syounin_web"){
            if("".$this_sess['search_condition']['user_syounin_flg'] !="0"){
                $add_str = " (ｻｲﾝｱｯﾌﾟの承認状態「未承認(ログイン不可)」に絞り込みが必要)";
                $tpl_recs[$i]['disabled'] = "disabled";
            }
        }

        $tpl_recs[$i]['pulldown_str'] = $tpl_recs[$i]['mailt_name'].$add_str;
    }
    $blade->assign('tpl_recs',$tpl_recs);

    if( $_request['exec'] == "tpl_change"){
        $this_sess['mail_subject'] = "";
        $this_sess['mail_body'] = "";

        if($_request['mail_tpl']!=""){
            for ($i=0; $i < _count($tpl_recs); $i++) {
                if($tpl_recs[$i]['mailt_key'] == $_request['mail_tpl']){
                    $this_sess['mailt_name'] = $tpl_recs[$i]['mailt_name'];
                    $this_sess['mail_subject'] = $tpl_recs[$i]['mailt_subject'];
                    $this_sess['mail_body'] = $tpl_recs[$i]['mailt_body'];
                    break;
                }
            }
        }

    }elseif($_request['exec'] == "search" || $_request['offset'] != "" || $_request['sess_no_init']!="" ||
            $_request['exec'] == "user_kettei" || $_request['exec'] == "test_send" || $_request['exec'] == "send"){

        if( $_request['exec'] == "test_send"){

            $chks = array(
                            "mail_subject,件名"          => "need",
                            "mail_body,本文" => "need",
                            "test_addr,テスト送信先メールアドレス"             => "need,email",
                        );
            $err_msg = _check( $chks, $this_sess );
        }elseif( $_request['exec'] == "send" && $this_sess['mail_tpl']!=""){

            $chks = array(
                            "mail_subject,件名"          => "need",
                            "mail_body,本文" => "need",
                        );
            $err_msg = _check( $chks, $this_sess );

            if($this_sess['mail_timing'] == 'yoyaku') {
                if ($this_sess['yoyaku_ymd'] == '' || $this_sess['yoyaku_hh'] == '' || $this_sess['yoyaku_ii'] == '') {
                    $err_msg[] = "予約日時が不正です";
                }
                else {
                    $ymd = $this_sess['yoyaku_ymd'];
                    $time = $this_sess['yoyaku_hh'] . ':' . $this_sess['yoyaku_ii'];
                    if (!_dateCheck($ymd, $err) || !_timeCheck($time, $err)) {
                        $err_msg[] = "予約日時が不正です";
                    }
                }
            }
        }

        $raijyou_hi_st_err = false;
        $raijyou_hi_ed_err = false;
        if( $_request['exec'] == "search"){
            if($this_sess['search_condition']['raijyou_hi_st']!=""){
                $wrk = $this_sess['search_condition']['raijyou_hi_st'];
                $arr = explode(":", $wrk);
                if( _count($arr) == 2){
                    if( _seisuuCheck($arr[0],'')!=false && _seisuuCheck($arr[1],'')!=false ){
                        $hi = sprintf("%02d",$arr[0]).":".sprintf("%02d",$arr[1]);
                        if( _timeCheck($hi,'') == false){
                            $err_msg[] = "来場日時の開始時間が不正です";
                            $raijyou_hi_st_err = true;
                        }else{
                           $this_sess['search_condition']['raijyou_hi_st'] = $hi;
                        }
                    }else{
                        $err_msg[] = "来場日時の開始時間が不正です";
                        $raijyou_hi_st_err = true;
                    }
                }else{
                    $err_msg[] = "来場日時の開始時間が不正です";
                    $raijyou_hi_st_err = true;
                }
            }

            if($this_sess['search_condition']['raijyou_hi_ed']!=""){
                $wrk = $this_sess['search_condition']['raijyou_hi_ed'];
                $arr = explode(":", $wrk);
                if( _count($arr) == 2){
                    if( _seisuuCheck($arr[0],'')!=false && _seisuuCheck($arr[1],'')!=false ){
                        $hi = sprintf("%02d",$arr[0]).":".sprintf("%02d",$arr[1]);
                        if( _timeCheck($hi,'') == false){
                            $err_msg[] = "来場日時の開始時間が不正です";
                            $raijyou_hi_ed_err = true;
                        }else{
                           $this_sess['search_condition']['raijyou_hi_ed'] = $hi;
                        }
                    }else{
                        $err_msg[] = "来場日時の開始時間が不正です";
                        $raijyou_hi_ed_err = true;
                    }
                }else{
                    $err_msg[] = "来場日時の開始時間が不正です";
                    $raijyou_hi_ed_err = true;
                }
            }
        }

        if( _count($err_msg) == 0 ){

            // ******************************************************************************************************
            // 共通WHERE
            // ******************************************************************************************************
            $where = "";
            $where .= "user_delete_date is null";
            $where .= " and user_event_id = '"._as($_SESSION[_PROJECT_NAME]['select_event_id'])."'";

            // if( _count($this_sess['nochk_user_arr']) > 0 ){
            //     $ids = "";
            //     foreach ($this_sess['nochk_user_arr'] as $key => $value) {
            //         if ($ids != "") $ids .= ",";
            //         $ids .= "'"._as($value)."'";
            //     }
            //     $where .= " and user_id not in (".$ids.")"."\n";
            // }

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

                } elseif ( $this_sess['search_condition']['syoutai_raijyou'] == 'raijyou'  ){
                    $where .= " and user_big_cate > 4";
                }
            }

            //通知メール状態
            if($this_sess['search_condition']['mail_status'] !=""){
                if( $this_sess['search_condition']['mail_status'] == 'mi'  ){
                    $where .= " and user_mail_send_kbn = 0";
                }elseif( $this_sess['search_condition']['mail_status'] == 'sumi'  ){
                    $where .= " and user_mail_send_kbn = 1";
                }elseif( $this_sess['search_condition']['mail_status'] == 'err'  ){
                    $where .= " and user_mail_send_kbn = 2";
                }
            }

            //リアル招待
            if( $this_sess['search_condition']['syoutai_yotei_time_ari'] != ""){
                $where .= " and ( user_raijyou_yotei_time is not null and user_raijyou_yotei_time != '' ) ";
                if( $this_sess['search_condition']['sankasinai_nozoku'] != ""){
                    $where .= " and ( user_raijyou_yotei_time not like '%2999/01/01%' ) ";
                }
            }

            //WEB招待
            if($this_sess['search_condition']['user_web'] !=""){
                $where .= " and user_web = "._as($this_sess['search_condition']['user_web'])."";
            }

            //PASS設定状態
            if($this_sess['search_condition']['pass_set'] =="mi"){
                $where .= " and (user_pass is null or (user_pass is not null and user_pass = '"._as(md5('_NEED_PASS_SET_'))."' ) )";
            }elseif($this_sess['search_condition']['pass_set'] =="sumi"){
                $where .= " and (user_pass is not null and  user_pass != '"._as(md5('_NEED_PASS_SET_'))."' )";
            }

            //承認状態
            if($this_sess['search_condition']['user_syounin_flg'] !=""){
                $where .= " and user_syounin_flg = "._as($this_sess['search_condition']['user_syounin_flg']);
            }

            //企業名
            if($this_sess['search_condition']['user_kigyou_name'] !=""){
                $where .= " and user_kigyou_name like '%"._as($this_sess['search_condition']['user_kigyou_name'])."%'";
            }

            //企業名カナ
            if($this_sess['search_condition']['user_kigyou_name_kana'] !=""){
                $where .= " and user_kigyou_name_kana like '%"._as($this_sess['search_condition']['user_kigyou_name_kana'])."%'";
            }

            //VIP
            if($this_sess['search_condition']['user_vip_flg'] !=""){
                $where .= " and user_vip_flg = "._as($this_sess['search_condition']['user_vip_flg'])."";
            }

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

            if($this_sess['search_condition']['user_yakusyoku'] != ''){
                $s = $this_sess['search_condition']['user_yakusyoku'];
                $s = str_replace('　', ' ', $s);
                $array = explode(' ', $s);

                $tmpCondition = [];
                foreach ($array as $key => $value) {
                    if ($value == '') continue;
                    $tmpCondition[] = "user_yakusyoku like '%" ._as($value) . "%'";
                }

                if (count($tmpCondition) > 0) {
                    $where .= ' and (';
                    $where .= implode(' or ', $tmpCondition);
                    $where .= ') ';
                }
            }

            // 中分類
            if ($this_sess['search_condition']['user_mid_cate'] != "") {
                $where .= " and user_mid_cate = '" . _as($this_sess['search_condition']['user_mid_cate']) . "'";
            }


            //担当エリア
/*
            if($this_sess['search_condition']['admin_tanarea_id'] !=""){
                $where .= " and admin_tanarea_id = "._as($this_sess['search_condition']['admin_tanarea_id'])."";
            }
*/
            if(!is_null($this_sess['search_condition']['admin_tanarea_id'])){
                $tmpCondition = [];
                foreach ($this_sess['search_condition']['admin_tanarea_id'] as $key => $value) {
                    if ($value == '') continue;
                    $tmpCondition[] = 'admin_tanarea_id = ' ._as($value);
                }

                if (count($tmpCondition) > 0) {
                    $soyusai_cond_open = 1;
                    $where .= ' and (';
                    $where .= implode(' or ', $tmpCondition);
                    $where .= ') ';
                }
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

            //実来場日時
            //★各日の最小で検索するよう仕様変更 Mod ---------------- Before -----------------------
            // if($this_sess['search_condition']['raijyou_ymd'] !=""){
            //     $where .= " and substr(min_kinout_time_in,1,10) = '"._as($this_sess['search_condition']['raijyou_ymd'])."'";
            // }
            // if($this_sess['search_condition']['raijyou_hi_st'] !="" && $raijyou_hi_st_err==false){
            //     $where .= " and substr(min_kinout_time_in,12,5) >= '"._as($this_sess['search_condition']['raijyou_hi_st'])."'";
            // }
            // if($this_sess['search_condition']['raijyou_hi_ed'] !="" && $raijyou_hi_ed_err==false){
            //     $where .= " and substr(min_kinout_time_in,12,5) <= '"._as($this_sess['search_condition']['raijyou_hi_ed'])."'";
            // }
            //★各日の最小で検索するよう仕様変更 Mod ---------------- After -----------------------
            if( !is_null($this_sess['search_condition']['raijyou_ymd']) ||
               ($this_sess['search_condition']['raijyou_hi_st'] !="" && $raijyou_hi_st_err==false) ||
               ($this_sess['search_condition']['raijyou_hi_ed'] !="" && $raijyou_hi_ed_err==false) ){

                $soyusai_cond_open = 1;

                $join .= " join (";
                $join .= "    select kinout_event_id,kinout_user_id from t_kaijyou_inout ";
                $join .= "    where kinout_delete_date is null ";
                $join .= "      and kinout_event_id = '"._as($_SESSION[_PROJECT_NAME]['select_event_id'])."' ";
                $join .= "      and kinout_fst_record = 1 ";

                if(!is_null($this_sess['search_condition']['raijyou_ymd'])){
                    $tmpCondition = [];
                    foreach ($this_sess['search_condition']['raijyou_ymd'] as $key => $value) {
                        if ($value == '') continue;
                        $tmpCondition[] = "substr(kinout_time_in,1,10) = '" ._as($value) . "'";
                    }

                    if (count($tmpCondition) > 0) {
                        $join .= ' and (';
                        $join .= implode(' or ', $tmpCondition);
                        $join .= ') ';
                    }
                }
/*
                if($this_sess['search_condition']['raijyou_ymd'] !=""){
                    $join .= "      and substr(kinout_time_in,1,10) = '"._as($this_sess['search_condition']['raijyou_ymd'])."' ";
                }
*/
                if($this_sess['search_condition']['raijyou_hi_st'] !="" && $raijyou_hi_st_err==false){
                    $join .= "      and substr(kinout_time_in,12,5) >= '"._as($this_sess['search_condition']['raijyou_hi_st'])."' ";
                }
                if($this_sess['search_condition']['raijyou_hi_ed'] !="" && $raijyou_hi_ed_err==false){
                    $join .= "      and substr(kinout_time_in,12,5) <= '"._as($this_sess['search_condition']['raijyou_hi_ed'])."' ";
                }
                $join .= "    group by kinout_user_id ";
                $join .= " ) V_kaijyou_inout on (V_kaijyou_inout.kinout_user_id=v_user.user_id and V_kaijyou_inout.kinout_event_id=v_user.user_event_id)";
            }
            //★各日の最小で検索するよう仕様変更 Mod ---------------- End -----------------------


            //事後アンケート回答有無
            if($this_sess['search_condition']['jigo_ans'] !=""){
                if($this_sess['search_condition']['jigo_ans'] == "sumi"){
                    $join .= " inner join "."\n";
                    $join .= " ( SELECT jigo_event_id,jigo_user_id FROM t_jigo_answer where jigo_event_id='"._as($_SESSION[_PROJECT_NAME]['select_event_id'])."' group by jigo_event_id,jigo_user_id )"."\n";
                    $join .= " as v_jigo_ans on (v_jigo_ans.jigo_event_id = v_user.user_event_id and v_jigo_ans.jigo_user_id = v_user.user_id) "."\n";
                }elseif($this_sess['search_condition']['jigo_ans'] == "mi"){
                    $join .= " left join "."\n";
                    $join .= " ( SELECT jigo_event_id,jigo_user_id FROM t_jigo_answer where jigo_event_id='"._as($_SESSION[_PROJECT_NAME]['select_event_id'])."' group by jigo_event_id,jigo_user_id )"."\n";
                    $join .= " as v_jigo_ans on (v_jigo_ans.jigo_event_id = v_user.user_event_id and v_jigo_ans.jigo_user_id = v_user.user_id) "."\n";

                    $where .= " and v_jigo_ans.jigo_user_id is null";
                }
            }

            //タグ文字
            if($this_sess['search_condition']['user_tag'] !=""){
                $where .= " and user_tag = '"._as($this_sess['search_condition']['user_tag'])."'";
            }

            // 管理画面ログイン権限あり
            if($this_sess['search_condition']['include_admin'] !=""){
                $where .= " and user_big_cate = 7";
                $where .= " and EXISTS (select admin_mail from v_admin where admin_delete_date is null and admin_mail = user_mail and admin_login_kengen = 1)";
            }

            // ******************************************************************************************************
            // データ抽出
            // ******************************************************************************************************
            $offset = 0;
            if( $this_sess['search_condition']['offset'] != "" ){
                $offset = intval( $this_sess['search_condition']['offset'] );
            }
            // 件数取得SQL
            $sql  = "";
            $sql .= " select count(v_user.user_id) as all_cnt from v_user ";
            //★各日の最小で検索するよう仕様変更 Del ---------------- Start -----------------------
            // $sql .= " left join (";
            // $sql .= "    select";
            // $sql .= "       kinout_user_id";
            // $sql .= "      ,kinout_event_id";
            // $sql .= "      ,min(kinout_time_in) as min_kinout_time_in";
            // $sql .= "    from t_kaijyou_inout";
            // $sql .= "    group by kinout_user_id,kinout_event_id";
            // $sql .= " ) V_kaijyou_inout on (V_kaijyou_inout.kinout_user_id=v_user.user_id and V_kaijyou_inout.kinout_event_id=v_user.user_event_id)";
            //★各日の最小で検索するよう仕様変更 Del ---------------- End -----------------------
            $sql .= " left join v_admin on (v_admin.admin_id = v_user.user_admin_id)";
            if ( $join != '' ) $sql .= $join;
            $sql .= " where ".$where;
            $all_recs = _select($sql);

            $allcnt = 0;
            if($all_recs[0]['all_cnt'] > 0){
                $allcnt = $all_recs[0]['all_cnt'];
            }

            $sentakuTaisyouCnt = $allcnt - _count($this_sess['nochk_user_arr']);
            $this_sess['sess_sentakuTaisyouCnt'] =  $sentakuTaisyouCnt;

            if( ($_request['exec'] == "test_send" || $_request['exec'] == "send") && $this_sess['sess_sentakuTaisyouCnt']==0){
                if($this_sess['mail_tpl']!=""){
                    $err_msg[] = "送信対象者が１人もいません。";
                }
            }elseif($_request['exec'] == "user_kettei" && $this_sess['sess_sentakuTaisyouCnt']==0){
                $err_msg[] = "送信対象者が１人もいません。";
            }

            if($allcnt > 0){
                // 表示SQL
                $sql  = "";
                $sql .= " select * from v_user ";
                //★各日の最小で検索するよう仕様変更 Del ---------------- Start -----------------------
                // $sql .= " left join (";
                // $sql .= "    select";
                // $sql .= "       kinout_user_id";
                // $sql .= "      ,kinout_event_id";
                // $sql .= "      ,min(kinout_time_in) as min_kinout_time_in";
                // $sql .= "    from t_kaijyou_inout";
                // $sql .= "    group by kinout_user_id,kinout_event_id";
                // $sql .= " ) V_kaijyou_inout on (V_kaijyou_inout.kinout_user_id=v_user.user_id and V_kaijyou_inout.kinout_event_id=v_user.user_event_id)";
                //★各日の最小で検索するよう仕様変更 Del ---------------- Start -----------------------
                $sql .= " left join v_admin on (v_admin.admin_id = v_user.user_admin_id)";
                if ( $join != '' ) $sql .= $join;
                $sql .= " where ".$where;
                $sql .= " order by ".$this_sess['search_condition']['order_by'];

                if( $_request['exec'] == "test_send"){
                    $sql .= " limit 0 , 3000";
                }elseif( $_request['exec'] == "send"){
                    //limitなし
                }else{
                    $sql .= " limit ".$offset." , ".$limit;
                }

                if( $_request['exec'] == "send" && $this_sess['sess_sentakuTaisyouCnt']>0){

                    if($this_sess['mail_tpl']!=""){
                        _query($conn,'begin');

                        $array = array();
                        $array['mailhd_mailt_name'] = "'"._as($this_sess['mailt_name'])."'";
                        $array['mailhd_mailt_key'] = "'"._as($this_sess['mail_tpl'])."'";
                        $array['mailhd_subject'] = "'"._as($this_sess['mail_subject'])."'";
                        $array['mailhd_body'] = "'"._as($this_sess['mail_body'])."'";

                        switch ($this_sess['mail_timing']) {
                            case 'ato':
                                $array['mailhd_yoyaku_ymdhi'] = "'2099/12/31 23:59'";
                                break;
                            case 'yoyaku':
                                $array['mailhd_yoyaku_ymdhi'] = "'" . $this_sess['yoyaku_ymd'] . ' ' . $this_sess['yoyaku_hh'] . ':' . $this_sess['yoyaku_ii'] . "'";
                                break;
                            default:
                                $array['mailhd_yoyaku_ymdhi'] = "'"._as(date("Y/m/d H:i"))."'";
                                break;
                        }

                        $array['mailhd_status'] = "0";
                        $array['mailhd_test_send_flg'] = "0"; //0:本番送信
                        $array['mailhd_insert_admin_id'] = "'"._as($_SESSION[_PROJECT_NAME]['admin_login']['admin_id'])."'";

                        $array['mailhd_insert_date'] = "'".$_now_timestamp."'";
                        $array['mailhd_update_date'] = "'".$_now_timestamp."'";
                        _insert('t_mail_head',$array);

                        $mailhd_id = $conn->insert_id; //insertされたAUTO_INCREMENTの値取得

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
                                $array = array();
                                $array['maills_mailhd_id'] = "'"._as($mailhd_id)."'";
                                $array['maills_user_id'] = "'"._as($rec['user_id'])."'";
                                $array['maills_mail_address'] = "'"._as($rec['user_mail'])."'";
                                $array['maills_event_id'] = "'"._as($_SESSION[_PROJECT_NAME]['select_event_id'])."'";
                                $array['maills_insert_date'] = "'".$_now_timestamp."'";
                                $array['maills_update_date'] = "'".$_now_timestamp."'";
                                _insert('t_mail_list',$array);
                            }

                            $row++;
                        }
                        _freeResult( $result );

                        _query($conn,'commit');
                    }

                    $this_sess  = array();
                    unset( $_SESSION[_PROJECT_NAME][$page] );
                    unset( $this_sess );
                    $this_sess = &$_SESSION[_PROJECT_NAME][$page];

                    $success_msg = "送信予約を登録しました。";

                }else{
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

                        //★各日の最小で検索するよう仕様変更 Mod ---------------- Before -----------------------
                        // if($main_recs[$i]['min_kinout_time_in']!=""){
                        //     $main_recs[$i]['disp_min_kinout_time_in'] = date("n月j日 H:i", strtotime($main_recs[$i]['min_kinout_time_in']) );
                        // }
                        //★各日の最小で検索するよう仕様変更 Mod ---------------- After -----------------------
                        $sql = "";
                        $sql .= " select ";
                        $sql .= "   substr(kinout_time_in,1,10)";
                        $sql .= "  ,min(kinout_time_in) as min_kinout_time_in ";
                        $sql .= " from t_kaijyou_inout";
                        $sql .= " where kinout_delete_date is null";
                        $sql .= "   and kinout_event_id = '"._as($_SESSION[_PROJECT_NAME]['select_event_id'])."'";
                        $sql .= "   and kinout_fst_record = 1";
                        $sql .= "   and kinout_user_id = '"._as($main_recs[$i]['user_id'])."'";
                        $sql .= " group by substr(kinout_time_in,1,10)";
                        $sql .= " order by min_kinout_time_in asc";
                        $kin_recs = _select($sql);
                        $main_recs[$i]['disp_min_kinout_time_in'] = "";
                        for ($j=0; $j < _count($kin_recs); $j++) {
                            if($main_recs[$i]['disp_min_kinout_time_in']!="") $main_recs[$i]['disp_min_kinout_time_in'] .= "<br>";
                            $main_recs[$i]['disp_min_kinout_time_in'] .= date("n月j日 H:i", strtotime($kin_recs[$j]['min_kinout_time_in']) );
                        }
                        //★各日の最小で検索するよう仕様変更 Mod ---------------- End -----------------------

                        $main_recs[$i]['disp_big_cate'] = $_conf_big_cate_detail[$main_recs[$i]['user_big_cate']];

                        if($main_recs[$i]['user_pass']==md5("_NEED_PASS_SET_") ){
                            $main_recs[$i]['pass_set'] = "<span style=\"color:red;\">未設定</span>";
                        }else{
                            $main_recs[$i]['pass_set'] = "<span style=\"color:blue;\">済み</span>";
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
                                if( $_request['exec'] == "test_send"){
                                    break;
                                }
                            }
                        }
                    }

                    if( $chkOffCnt == $limit){
                        $blade->assign('allChkOff',"1");
                    }else{
                        $blade->assign('allChk',"");
                    }

                    if( $_request['exec'] == "test_send" && $this_sess['sess_sentakuTaisyouCnt']>0){

                        _query($conn,'begin');

                        $array = array();
                        $array['mailhd_mailt_name'] = "'"._as($this_sess['mailt_name'])."'";
                        $array['mailhd_mailt_key'] = "'"._as($this_sess['mail_tpl'])."'";
                        $array['mailhd_subject'] = "'"._as($this_sess['mail_subject'])."'";
                        $array['mailhd_body'] = "'"._as($this_sess['mail_body'])."'";
                        $array['mailhd_yoyaku_ymdhi'] = "'"._as(date("Y/m/d H:i"))."'";
                        $array['mailhd_status'] = "0";
                        $array['mailhd_test_send_flg'] = "1"; //1:テスト送信
                        $array['mailhd_insert_admin_id'] = "'"._as($_SESSION[_PROJECT_NAME]['admin_login']['admin_id'])."'";

                        $array['mailhd_insert_date'] = "'".$_now_timestamp."'";
                        $array['mailhd_update_date'] = "'".$_now_timestamp."'";
                        _insert('t_mail_head',$array);

                        $mailhd_id = $conn->insert_id; //insertされたAUTO_INCREMENTの値取得

                        $array = array();
                        $array['maills_mailhd_id'] = "'"._as($mailhd_id)."'";
                        $array['maills_user_id'] = "'"._as($main_recs[$fstChkIdx]['user_id'])."'";
                        $array['maills_mail_address'] = "'"._as($this_sess['test_addr'])."'";
                        $array['maills_event_id'] = "'"._as($_SESSION[_PROJECT_NAME]['select_event_id'])."'";
                        $array['maills_insert_date'] = "'".$_now_timestamp."'";
                        $array['maills_update_date'] = "'".$_now_timestamp."'";
                        _insert('t_mail_list',$array);

                        _query($conn,'commit');

                        $success_msg = "テスト送信を実行しました。";
                    }
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

    //(来場者)来場予定日時
    $wArr = explode("#", $select_event_rec['event_raijyou_yotei_time']);
    $_conf_raijyou_yotei_time = array();
    for ($i=0; $i < _count($wArr); $i++) {
        $dtArr = explode(" ", $wArr[$i],2);
        $ymd = $dtArr[0];
        if($ymd!='2999/01/01'){
            $disp_ymd = date("n月j日",strtotime($dtArr[0]) )."（"._getYoubi($dtArr[0])."）";
            $hi = $dtArr[1];
            $_conf_raijyou_yotei_time[$ymd]['disp_ymd'] = $disp_ymd;

            $checked="";
            for ($j=0; $j < _count($this_sess['search_condition']['user_raijyou_yotei_time']); $j++) {
                if($this_sess['search_condition']['user_raijyou_yotei_time'][$j] == $wArr[$i]){
                    $checked = "checked";
                    break;
                }
            }
            $_conf_raijyou_yotei_time[$ymd]['his'][] = array('hi'=>$hi, 'checked'=>$checked);
        }
    }
    $blade->assign('_conf_raijyou_yotei_time',$_conf_raijyou_yotei_time);

    // 中分類
    $blade->assign('_conf_mid_cate', $_conf_mid_cate);

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
    $blade->assign('_conf_user_syounin_flg',$_conf_user_syounin_flg); // 2021.06.05

    $blade->assign('sentakuTaisyouCnt',$sentakuTaisyouCnt);
    $blade->assign('sess_sentakuTaisyouCnt',$this_sess['sess_sentakuTaisyouCnt']);

    $blade->assign('this_page_limit',$limit);
    $blade->assign('main_recs_count', _count($main_recs) );

    $blade->assign('_conf_vip',$_conf_vip);
    if($_request['exec'] == "search" || $_request['offset'] != "" || $_request['sess_no_init']!=""){
        $blade->assign('list_disp',"1");
    }

    if($_request['exec'] == "send" && _count($err_msg)==0){
        if($select_event_rec['event_name']==""){
            $contents_title = "メール送信予約完了";
        }else{
            $contents_title = "メール送信予約完了" . "（".$select_event_rec['event_name'] .":".$select_event_rec['event_kaijyou_name']."）";
        }

        $contents_tpl = "user_mail_send_fin";
    }elseif($_request['exec'] == "user_kettei" || $_request['exec'] == "tpl_change" || $_request['exec'] == "test_send" || $_request['exec'] == "send"){

        if($_request['exec'] == "user_kettei" && _count($err_msg) > 0){
            $blade->assign('list_disp',"1");
            if($select_event_rec['event_name']==""){
                $contents_title = "メール送信";
            }else{
                $contents_title = "メール送信" . "（".$select_event_rec['event_name'] .":".$select_event_rec['event_kaijyou_name']."）";
            }

            $contents_tpl = "user_mail_send";
        }else{
            if($select_event_rec['event_name']==""){
                $contents_title = "メール送信内容編集";
            }else{
                $contents_title = "メール送信内容編集" . "（".$select_event_rec['event_name'] .":".$select_event_rec['event_kaijyou_name']."）";
            }

            $contents_tpl = "user_mail_send_naiyou";
        }
    }else{
        if($select_event_rec['event_name']==""){
            $contents_title = "メール送信";
        }else{
            $contents_title = "メール送信" . "（".$select_event_rec['event_name'] .":".$select_event_rec['event_kaijyou_name']."）";
        }

        $contents_tpl = "user_mail_send";
    }

    $blade->assign('title_bgcolor',$select_event_rec['event_title_bgcolor']);

    $active_menu = "user_mail_send";

    $_conf_hh = array();
    for ($i=0; $i <= 23; $i++) {
        $_conf_hh[ sprintf("%02d",$i) ] = sprintf("%02d",$i);
    }
    $blade->assign('_conf_hh',$_conf_hh);

    $_conf_mm = array();
    for ($i=0; $i <= 59; $i++) {
        $_conf_mm[ sprintf("%02d",$i) ] = sprintf("%02d",$i);
    }
    $blade->assign('_conf_mm',$_conf_mm);
