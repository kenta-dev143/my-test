<?php
    if( !defined("_PROJECT_DISP_NAME") ){
        die("System Error");
    }

    if( $_SESSION[_PROJECT_NAME]['admin_login']['admin_id'] == "" ){
        die('System Error');
    }

    if ($select_event_rec['event_archived_flg'] == '1') {
        die('System Error');
    }

    // ******************************************************************************************************
    // 初期値
    // ******************************************************************************************************
    $page = $_request['page'];
    $this_sess = &$_SESSION[_PROJECT_NAME][$page];
    $err_msg = array();
    $kk_ids = ''; // 外部企業ID

    // 企業マスタ
    $sql = "";
    $sql .= "select * from m_company";
    $sql .= " where";
    $sql .= " company_delete_date is null";
    if ($_SESSION[_PROJECT_NAME]['admin_login']['admin_kyouryoku_kigyou_flg'] == 1) {
        $kk_sql = "";
        $kk_sql .= " select GROUP_CONCAT(company_id) as ids ";
        $kk_sql .= " from c_admin_companies ";
        $kk_sql .= " where admin_id = '" . _as($_SESSION[_PROJECT_NAME]['admin_login']['admin_id']) . "'";
        $kk_ids_recs = _select($kk_sql);
        $sql .= " and company_id in (" . $kk_ids_recs[0]['ids'] . ")";
        $kk_ids = $kk_ids_recs[0]['ids'];
    }
    $sql .= " order by company_id asc";
    $company_recs = _select($sql);
    $_conf_company = array();
    for ($i=0; $i < _count($company_recs); $i++) {
        $_conf_company[ $company_recs[$i]['company_id'] ] = $company_recs[$i]['company_name'];
    }
    $blade->assign('_conf_company',$_conf_company);

    set_time_limit(600); //10分起動

    // ******************************************************************************************************
    // 検索
    // ******************************************************************************************************
    if( $_request['exec'] == "search" || $_request['exec'] == "csv_download" || $_request['exec'] == "import_csv_download"){
        unset( $this_sess['chk_syoutai_arr'] );
        $this_sess['chk_syoutai_arr'] = array();
        unset( $this_sess['search_condition'] );
        $this_sess['search_condition'] = array();
        $this_sess['search_condition'] = _array_merge( $this_sess['search_condition'], $_request );
        unset( $this_sess['user_admin_id'] );
        unset( $this_sess['user_tag'] );
        $this_sess['user_web'] = 1;
        $this_sess['user_raijyou_yotei_time'] = ""; // 2021.05.31 add

    }elseif( $_request['offset'] != ""  || $_request['exec'] == "list" || $_request['exec'] == "userMake" || $_request['exec'] == "gotoDetail"){
        // page_naviのリンク、または来場者情報作成ボタン
        if( $_request['offset'] != ""){
            $this_sess['search_condition']['offset'] = $_request['offset'];
        }

        for ($i=0; $i < _count($_request['syoutai_ids']); $i++) {
            if( $_request['syoutai_chks'][$i] == "1"){
                $this_sess['chk_syoutai_arr'][ $_request['syoutai_ids'][$i] ] = $_request['syoutai_ids'][$i];
            }else{
                $this_sess['chk_syoutai_arr'][ $_request['syoutai_ids'][$i] ] = "";
                unset( $this_sess['chk_syoutai_arr'][ $_request['syoutai_ids'][$i] ] );
            }
        }

        if ( $_request['user_admin_id'] != '' ){
            $this_sess['user_admin_id'] = $_request['user_admin_id'];
        }else{
            $this_sess['user_admin_id'] = "";
        }

        $this_sess['user_tag'] = $_request['user_tag'];

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

        if ( $_request['user_web'] == '1' ){
            $this_sess['user_web'] = 1;
        }else{
            $this_sess['user_web'] = 0;
        }

        // 来場予定日時
        $yotei = "";
        // 2021.05.31 mod ---------- Before ----------
        // for ($i=0; $i < _count($_request['syoutai_yotei_time']); $i++) {
        //     if($yotei!="") $yotei .= "#";
        //     $yotei .= $_request['syoutai_yotei_time'][$i];
        // }
        // 2021.05.31 mod ---------- After  ----------
        $yotei = $_request['syoutai_yotei_time'];
        // 2021.05.31 mod ---------- End    ----------
        $_request['user_raijyou_yotei_time']  = $yotei;
        $this_sess['user_raijyou_yotei_time'] = $yotei;

        if($_request['exec'] == "gotoDetail"){
            header("Location: ?page=syoutai_edit&id=".$_request['syoutai_id']."&from_page=".$page );
            exit();
        }

    }elseif( $_request['sess_no_init'] == "" ){
        unset( $this_sess['search_condition'] );
        $this_sess['search_condition']['order_by'] = "syoutai_kigyou_name_kana asc,syoutai_name_kana asc";
        $this_sess['chk_syoutai_arr'] = array();
        $this_sess['search_condition']['t_event_id'] = $_SESSION[_PROJECT_NAME]['select_event_id'];

        $this_sess['user_tag'] = "";

        $this_sess['mail_tsuuchi'] = 0;

        $this_sess['mail_timing'] = "now";
        $this_sess['user_raijyou_yotei_time'] = "";
        $this_sess['user_web'] = 1;
        $this_sess['user_raijyou_yotei_time'] = ""; // 2021.05.31 add
    }

    if($this_sess['search_condition']['order_by']==''){
        $this_sess['search_condition']['order_by'] = "syoutai_kigyou_name_kana asc,syoutai_name_kana asc";
    }

    // ******************************************************************************************************
    // 並び順配列作成
    // ******************************************************************************************************
    $order_by_arr = array();
    $order_by_arr['syoutai_kigyou_name_kana asc,syoutai_name_kana asc']                       = "企業名順";
    $order_by_arr['syoutai_name_kana asc,syoutai_kigyou_name_kana asc']                       = "氏名順";
    $order_by_arr['syoutai_insert_date desc']                                                 = "登録日（降順）";
    $order_by_arr['syoutai_insert_date asc']                                                  = "登録日（昇順）";
    $blade->assign('order_by_arr',$order_by_arr);

    // ******************************************************************************************************
    // プルダウンの組み立て
    // ******************************************************************************************************
    $sql  = "";
    $sql .= " select event_id, event_pulldown_name"."\n";
    $sql .= " from m_event"."\n";
    $sql .= " where event_delete_date is null"."\n";
    $sql .= " order by event_id"."\n";
    $recs = _select($sql);
    $_conf_join_event = array();
    for ($loop=0; $loop < _count($recs); $loop++) {
        $_conf_join_event[ $recs[$loop]['event_id'] ] = $recs[$loop]['event_pulldown_name'];
    }
    $blade->assign('_conf_join_event',$_conf_join_event);

    $_conf_touroku_event = $_conf_join_event[ $this_sess['search_condition']['t_event_id'] ];
    $blade->assign('_conf_touroku_event',$_conf_touroku_event);


    // ******************************************************************************************************
    // ラジオボタンの組み立て
    // ******************************************************************************************************
    $_conf_user_web_radio = array();
    $_conf_user_web_radio[1] = "WEB展示会（ガイドブック）にも招待する";
    $_conf_user_web_radio[0] = "WEB展示会（ガイドブック）には招待しない";
    $blade->assign('_conf_user_web_radio',$_conf_user_web_radio);

    // ******************************************************************************************************
    // 来場者情報 (m_user) 作成処理
    // ******************************************************************************************************
    if ( $_request['exec']=="userMake" && _count($this_sess['chk_syoutai_arr']) == 0 ){
        // 来場者情報の作成処理で、チェックが１つもなければエラー
        $err_msg[]  = "来場者情報を作成する場合は、チェックボックスをチェックして下さい。";

    } elseif ( $_request['exec']=="userMake" && _count($this_sess['chk_syoutai_arr']) > 0 ){

        if($this_sess['user_admin_id']==""){
            $err_msg[]  = "担当者を選択して下さい。";
        }else{

            $ids = "";
            foreach ($this_sess['chk_syoutai_arr'] as $key => $value) {
                if ($ids != "") $ids .= ",";
                $ids .= "'"._as($value)."'";
            }

            $sql  = "";
            $sql .= " select user_syoutai_id"."\n";
            $sql .= " from m_user"."\n";
            $sql .= " where user_syoutai_id in (".$ids.")"."\n";
            $sql .= " and user_delete_date is null"."\n";
            $sql .= " and user_event_id = '"._as( $this_sess['search_condition']['t_event_id'] )."'"."\n";
            $chk_recs = _select($sql);
            if ( _count($chk_recs) > 0){
                $err_msg[]  = "来場者情報に既に作成済みの招待者が含まれています。「検索」ボタンからやり直してください。";
            }

            // 2021.05.27 add ----------------- Start -----------------
            if ( $this_sess['user_raijyou_yotei_time'] == '' && $this_sess['user_web'] == 0){
                $err_msg[]  = "来場予定日時の選択、WEB展示会（ガイドブック）のいずれかを選択して下さい。";
            }
            // 2021.05.27 add ----------------- End   -----------------

            // 2021/07/07 add ----------------- Start -----------------
            if ( $this_sess['user_raijyou_yotei_time'] == '' && $this_sess['user_web'] == 1){
                $chk_ng = false;
                foreach ($this_sess['chk_syoutai_arr'] as $key => $syoutai_id) {

                    $sql = "";
                    $sql .= " select *"."\n";
                    $sql .= " from v_syoutai"."\n";
                    $sql .= " where syoutai_delete_date is null"."\n";
                    $sql .= "  and syoutai_id = '"._as($syoutai_id)."'"."\n";
                    $w_recs = _select($sql);
                    if (_count($w_recs) > 0){
                        if($w_recs[0]['syoutai_big_cate']==1 || $w_recs[0]['syoutai_big_cate']==2 || $w_recs[0]['syoutai_big_cate']==7 || $w_recs[0]['syoutai_big_cate']==8){
                            //大分類が「1:小売、2:外食、7:AC社員、8:その他(来場) はWEB招待OK


                        }else{
                            //大分類が「1:小売、2:外食、7:AC社員、8:その他(来場) 以外は、WEB招待は強制で「OFF」になるので、来場予定日時の選択の選択なしで、WEB招待はNG
                            $chk_ng = true;
                            break;
                        }
                    }

                }
                if($chk_ng == true){
                    $err_msg[]  = "大分類が「小売、外食」以外の方を招待する場合は「WEB展示会（ガイドブック）招待」は固定で「招待しない」になるため、来場予定日時の選択が必要です。";
                }

            }
            // 2021/07/07 add ----------------- End   -----------------

            if ( _count($err_msg) == 0 ){
                _query($conn, "begin");

                // 最大IDを取得しておく
                $max_recs = _select( "select coalesce(max(substring(user_id,2)),'0') as max_id from m_user");
                $now_user_id = $max_recs[0]['max_id'];

                // 初期化
                $error_line_count = 0;
                $insert_success = 0;
                $insert_user_ids = array();

                // 担当者情報
                $sql = "";
                $sql .= " select *"."\n";
                $sql .= " from v_admin a"."\n";
                $sql .= " join m_syozoku s on s.syozoku_id = a.admin_syozoku_id"."\n";
                $sql .= " where a.admin_delete_date is null"."\n";
                $sql .= " and a.admin_id = '" . _as($this_sess['user_admin_id']) . "'" ."\n";
                $admin_recs = _select($sql);

                // 来場予定日時
                 $yotei = "";
                 for ($i=0; $i < _count($_request['syoutai_yotei_time']); $i++) {
                     if($yotei!="") $yotei .= "#";
                     $yotei .= $_request['syoutai_yotei_time'][$i];
                 }
                 $_request['user_raijyou_yotei_time']  = $yotei;
                 $this_sess['user_raijyou_yotei_time'] = $yotei;

                // main loop
                foreach ($this_sess['chk_syoutai_arr'] as $key => $syoutai_id) {
                    if($error_line_count > 200) {
                        // 一定数のエラーを許容する場合はメッセージを変更し、ループ後のエラーチェックをする
                        $line_err[] = 'エラー行が200件以上発生しましたので取り込み処理を中断しました。';
                        break;
                    }

                    $sql = "";
                    $sql .= " select *"."\n";
                    $sql .= " from v_syoutai"."\n";
                    $sql .= " where syoutai_delete_date is null"."\n";
                    $sql .= "  and syoutai_id = '"._as($syoutai_id)."'"."\n";
                    $recs = _select($sql);
                    if (_count($recs) == 0){
                        $w_err[] = "(招待者id:".$syoutai_id.") 招待者データが見つかりません。";
                    }

                    $sql = "";
                    $sql .= " select *"."\n";
                    $sql .= " from v_user"."\n";
                    $sql .= " where user_delete_date is null"."\n";
                    $sql .= "  and user_event_id = '"._as( $this_sess['search_condition']['t_event_id'] )."'"."\n";
                    $sql .= "  and user_login_id = '"._as($recs[0]['syoutai_login_id'])."'"."\n";
                    $recs2 = _select($sql);
                    if (_count($recs2) > 0){
                        $w_err[]  = "来場者情報に既に同じログインID(".$recs[0]['syoutai_login_id'].")で作成済みの招待者が含まれています。";
                    }

                    if( _count($w_err) > 0 ){
                        $line_err = _array_merge($line_err, $w_err);
                        $error_line_count++;
                        continue;

                    } else {
                        $rec  = $recs[0];

                        // user_id
                        $now_user_id = $now_user_id + 1;
                        $user_id = sprintf("u%08d", $now_user_id );

                        $array = array();
                        $array_n = array();
                        $array_m = array();

                        $array['user_id']                  = "'"._as( $user_id )."'";
                        $array['user_event_id']            = "'"._as( $this_sess['search_condition']['t_event_id'] )."'"; //イベントID（e0001）',
                        $array['user_admin_id']            = "'"._as( $this_sess['user_admin_id'] )."'"; //担当者ID（a0000001）',
                        $array['user_admin_syozoku_id']    = "'"._as( $admin_recs[0]['admin_syozoku_id'])."'";
                        $array['user_admin_syozoku_group_id'] = "'"._as( $admin_recs[0]['syozoku_szkgrp_id'])."'";
                        $array['user_syoutai_id']          = "'"._as( $syoutai_id )."'"; //招待者ID（s0000001）',
                        $array['user_vip_flg']             = ""._e2z( $rec['syoutai_vip_flg'] ).""; //VIPフラグ（1:VIP）',
                        $array['user_big_cate']            = ""._e2n( $rec['syoutai_big_cate'] )."";//大分類',
                        $array['user_mid_cate']            = ""._e2n( $rec['syoutai_mid_cate'] ).""; //中分類',
                        $array['user_company_id']          = ""._e2n( $rec['syoutai_company_id'] ).""; //企業ID',
                        $array['user_kigyou_name']         = "'"._as( $rec['syoutai_kigyou_name'] )."'"; //企業名',
                        $array['user_kigyou_name_kana']    = "'"._as( $rec['syoutai_kigyou_name_kana'] )."'"; //企業名カナ',
                        $array['user_busyo']               = "'"._as( $rec['syoutai_busyo'] )."'"; //部署',
                        $array['user_yakusyoku']           = "'"._as( $rec['syoutai_yakusyoku'] )."'"; //役職',
                        $array['user_biko']                = "'"._as( $rec['syoutai_biko'] )."'"; //備考',
                        $array['user_pass']                = "'"._as( md5( "_NEED_PASS_SET_" ) )."'"; //パスワード',
                        $array['user_raijyou_yotei_time']  = "'"._as( $this_sess['user_raijyou_yotei_time'] )."'"; //来場予定日時（yyyy/mm/dd HH:ii 形式）',

                        //2021/07/07 Mod -------- Before ---------------
                        // $array['user_web']                 = ""._e2z($this_sess['user_web']).""; //WEB招待（1:WEB招待者）',
                        //2021/07/07 Mod -------- After ---------------
                        if($rec['syoutai_big_cate']==1 || $rec['syoutai_big_cate']==2 || $rec['syoutai_big_cate']==7 || $rec['syoutai_big_cate']==8){
                            //大分類が「1:小売、2:外食、7:AC社員、8:その他(来場) のみWEB招待できるので、WEB招待チェックの画面指示に従う
                            $array['user_web']                 = ""._e2z($this_sess['user_web']).""; //WEB招待（1:WEB招待者）',
                        }else{
                            //大分類が「1:小売、2:外食、7:AC社員、8:その他(来場) のみWEB招待できるので、それ以外は強制で「OFF:WEB招待しない」に。。。
                            $array['user_web']                 = "0"; //WEB招待（1:WEB招待者）',
                        }
                        //2021/07/07 Mod -------- End ---------------

                        if ( $this_sess['user_tag'] != '' ){
                            $array['user_tag']             = "'"._as( $this_sess['user_tag'] )."'"; //ユーザタグ',
                        }
                        $array['user_syounin_flg']         = 1; //'WEB招待の承認フラグ(0:未承認、1:承認済み)',
                        $array['user_insert_date']         = "'".$_now_timestamp."'";
                        $array['user_update_date']         = "'".$_now_timestamp."'";
                        _insert( 'm_user', $array);
                        $insert_user_ids[] = $user_id;

                        $array_n['un_user_id']             = "'"._as( $user_id )."'";
                        $array_n['un_user_name']           = "'"._as( $rec['syoutai_name'] )."'"; //'氏名',
                        $array_n['un_user_name_kana']      = "'"._as( $rec['syoutai_name_kana'] )."'"; //氏名カナ',
                        _insert( 'm_uname', $array_n);

                        $array_m['um_user_id']             = "'"._as( $user_id )."'";
                        $array_m['um_user_mail']           = "'"._as( $rec['syoutai_mail'] )."'"; //メールアドレス',
                        $array_m['um_user_login_id']       = "'"._as( $rec['syoutai_login_id'] )."'"; // ログインid',
                        _insert( 'm_umail', $array_m);

                        $insert_success++;
                    }
                } // main loop 終端

                if($error_line_count > 0) {
                    $err_msg[] = 'エラーがあったので登録処理を停止しました。(全ての登録がキャンセルされました)';
                    _query( $conn, "rollback" );
                }else{
                    if($insert_success > 0){

                        // ******* 招待メール送信処理(Start) *******
                        if($this_sess['mail_tsuuchi']=="1"){

                            $sql = "";
                            $sql .= "select * from m_mail_template";
                            $sql .= " where";
                            $sql .= " mailt_delete_date is null";
                            if ( $this_sess['user_raijyou_yotei_time'] != ''){
                                $sql .= " and mailt_key = 'pass_set_annai'";
                            }else{
                                $sql .= " and mailt_key = 'pass_set_annai_web_only'";
                            }
                            $tpl_recs = _select($sql);
                            if( _count($tpl_recs) > 0 ){

                                $array = array();
                                $array['mailhd_mailt_name'] = "'"._as($tpl_recs[0]['mailt_name'])."'";
                                $array['mailhd_mailt_key'] = "'"._as($tpl_recs[0]['mailt_key'])."'";
                                $array['mailhd_subject'] = "'"._as($tpl_recs[0]['mailt_subject'])."'";
                                $array['mailhd_body'] = "'"._as($tpl_recs[0]['mailt_body'])."'";
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

                                $mailhd_id = $conn->insert_id; //insertされたAUTO_INCREMENTの値取得

                                foreach ($this_sess['chk_syoutai_arr'] as $key => $syoutai_id) {

                                    $sql = "";
                                    $sql .= " select *"."\n";
                                    $sql .= " from v_syoutai"."\n";
                                    $sql .= " join v_user on (v_user.user_syoutai_id = v_syoutai.syoutai_id and v_user.user_event_id='"._as($this_sess['search_condition']['t_event_id'])."')"."\n";
                                    $sql .= " where syoutai_delete_date is null"."\n";
                                    $sql .= "  and user_delete_date is null"."\n";
                                    $sql .= "  and syoutai_id = '"._as($syoutai_id)."'"."\n";
                                    $recs = _select($sql);
                                    if( _count($recs) > 0){
                                        $rec = $recs[0];

                                        $array = array();
                                        $array['maills_mailhd_id'] = "'"._as($mailhd_id)."'";
                                        $array['maills_user_id'] = "'"._as($rec['user_id'])."'";
                                        $array['maills_mail_address'] = "'"._as($rec['user_mail'])."'";
                                        $array['maills_event_id'] = "'"._as($this_sess['search_condition']['t_event_id'])."'";
                                        $array['maills_insert_date'] = "'".$_now_timestamp."'";
                                        $array['maills_update_date'] = "'".$_now_timestamp."'";
                                        _insert('t_mail_list',$array);
                                    }
                                }
                            }

                        }
                        // ******* 招待メール送信処理(End) *******


                        unset( $this_sess['chks'] );
                        unset( $this_sess['syoutai_chks'] );
                        unset( $this_sess['syoutai_ids'] );
                        $success_msg = $insert_success."件登録しました。";
                    }
                    _query($conn, "commit");
                }
                unset( $this_sess['chk_syoutai_arr'] );
                $this_sess['chk_syoutai_arr'] = array();

                $now_date = date('Y/m/d');
                $sql = "";
                $sql .= " select * from m_event ";
                $sql .= " where event_id='"._as($this_sess['search_condition']['t_event_id'])."' ";
                $sql .= " and event_raikainri_ymd_st <= '" . $now_date . "'";
                $sql .= " and event_raikainri_ymd_ed >= '" . $now_date . "'";
                $event_recs = _select($sql);

                if (count($insert_user_ids) > 0 && _count($event_recs) > 0) {
                    // 登録したユーザーのみの来場日時登録画面を表示する
                    $user_ids = implode(',', $insert_user_ids);
                    header('Location: index.php?page=user_list&exec=search&user_ids=' . $user_ids);//OK1
                    exit();
                }
            }
        }
    }

    // ******************************************************************************************************
    // 共通WHERE
    // ******************************************************************************************************
    $join = "";

    $where = "";
    $where .= "syoutai_delete_date is null";

    if($this_sess['search_condition']['syoutai_vip_flg'] !=""){
        $where .= " and syoutai_vip_flg = "._as($this_sess['search_condition']['syoutai_vip_flg'])."";
    }

    if($this_sess['search_condition']['syoutai_big_cate'] !=""){
        $where .= " and syoutai_big_cate = "._as($this_sess['search_condition']['syoutai_big_cate'])."";
    }

    if($this_sess['search_condition']['syoutai_mid_cate'] !=""){
        $where .= " and syoutai_mid_cate = "._as($this_sess['search_condition']['syoutai_mid_cate'])."";
    }

    if($this_sess['search_condition']['syoutai_kigyou_name'] !=""){
        $where .= " and syoutai_kigyou_name like '%"._as($this_sess['search_condition']['syoutai_kigyou_name'])."%'";
    }

    if($this_sess['search_condition']['syoutai_company_id'] !=""){
        $where .= " and syoutai_company_id = '"._as($this_sess['search_condition']['syoutai_company_id'])."'";
    }

    if($this_sess['search_condition']['syoutai_kigyou_name_kana'] !=""){
        $where .= " and syoutai_kigyou_name_kana like '%"._as($this_sess['search_condition']['syoutai_kigyou_name_kana'])."%'";
    }

    if($this_sess['search_condition']['syoutai_sansan_id'] != ""){
        $where .= " and syoutai_sansan_id like '%"._as($this_sess['search_condition']['syoutai_sansan_id'])."%'";
    }

    if($this_sess['search_condition']['syoutai_name'] !=""){
        $where .= " and syoutai_name like '%"._as($this_sess['search_condition']['syoutai_name'])."%'";
    }

    if($this_sess['search_condition']['syoutai_name_kana'] !=""){
        $where .= " and syoutai_name_kana like '%"._as($this_sess['search_condition']['syoutai_name_kana'])."%'";
    }

    if($this_sess['search_condition']['syoutai_mail'] !=""){
        $where .= " and ( syoutai_mail like '%"._as($this_sess['search_condition']['syoutai_mail'])."%' or syoutai_login_id like '%"._as($this_sess['search_condition']['syoutai_mail'])."%' )";
    }

    if($this_sess['search_condition']['syoutai_tag'] !=""){
        $where .= " and syoutai_tag like '%"._as($this_sess['search_condition']['syoutai_tag'])."%'";
    }

    if ($_SESSION[_PROJECT_NAME]['admin_login']['admin_kyouryoku_kigyou_flg'] == 1) {
        $where .= " and syoutai_company_id in (" . $kk_ids . ")";
    }

    // ---------------------------------------------------------------------------------------- //

    // 作成するイベントの来場者情報を除外
    if ( $this_sess['search_condition']['t_event_id'] != "" ) {
        $join .= " left join v_user as t_event_user ";
        $join .= " on (t_event_user.user_syoutai_id = v_syoutai.syoutai_id and user_event_id = '"._as($this_sess['search_condition']['t_event_id'])."' and user_delete_date is null )"."\n";

        // 招待済みを含めないかどうか
        if($this_sess['search_condition']['not_include_invited'] == '1') {
            $where .= " and t_event_user.user_syoutai_id is null";
        }

        $join .= " left join (select admin_id, admin_name, admin_syozoku_id from v_admin where admin_delete_date is null) as t_event_admin ";
        $join .= " on (t_event_user.user_admin_id = t_event_admin.admin_id) ";

        $join .= " left join (select syozoku_id, syozoku_name from m_syozoku where syozoku_delete_date is null) as t_event_syozoku ";
        $join .= " on (t_event_admin.admin_syozoku_id = t_event_syozoku.syozoku_id) ";
    }

    // 選択のイベント、担当者に該当する来場者情報
    if( $this_sess['search_condition']['join_event_id'] != "" ){
        $sql = "";
        $sql .= " select event_archived_flg";
        $sql .= " from m_event";
        $sql .= " where event_id = '"._as($this_sess['search_condition']['join_event_id'])."'";
        $sql .= " and event_delete_date is null";
        $event_rec = _select($sql);

        $table_user = 'v_user';
        if (count($event_rec) > 0 && $event_rec[0]['event_archived_flg'] == '1')
        {
            $table_user = 'v_auser';
        }

        $join .= " inner join";
        $join .= " ( select user_syoutai_id as juser_user_syoutai_id ";
        $join .= "   from $table_user ";

        $join .= "   where user_delete_date is null and user_big_cate in (1,2,3,4) and";
        $join .= " user_event_id = '"._as($this_sess['search_condition']['join_event_id'])."'";

        if ( $this_sess['search_condition']['admin_syozoku_id'] != '' ){
            $sql = "";
            $sql .= " select syozoku_szkgrp_id"."\n";
            $sql .= " from m_syozoku"."\n";
            $sql .= " where syozoku_id = '"._as( $this_sess['search_condition']['admin_syozoku_id'] )."'"."\n";
            $sql .= "  and syozoku_delete_date is null"."\n";
            $syozoku_recs = _select( $sql );
            if ( $syozoku_recs[0]['syozoku_szkgrp_id'] == '' ){
                $syozoku_ids = "'".$this_sess['search_condition']['admin_syozoku_id']."'";
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

            $join .= " and user_admin_syozoku_id in (" . $syozoku_ids . ")";

        }

        if ( $this_sess['search_condition']['admin_syozoku_group_id'] != '' ) {
            $sql = "";
            $sql .= " select r_szkgrp_id"."\n";
            $sql .= " from c_syozoku_groups"."\n";
            $sql .= " where szkgrp_id = '"._as( $this_sess['search_condition']['admin_syozoku_group_id'] )."'"."\n";
            $syozoku_group_recs = _select( $sql );
            $syozoku_group_ids = "'" . _as( $this_sess['search_condition']['admin_syozoku_group_id'] ) . "'";
            for ($n=0; $n < _count($syozoku_group_recs); $n++) {
                if ($syozoku_group_recs[$n]['r_szkgrp_id'] != $this_sess['search_condition']['admin_syozoku_group_id']) {
                    $syozoku_group_ids .= ",'".$syozoku_group_recs[$n]['r_szkgrp_id']."'";
                }
            }
//            $join .= " and user_admin_syozoku_group_id = '"._as($this_sess['search_condition']['admin_syozoku_group_id'])."'";
            $join .= " and user_admin_syozoku_group_id in (" . $syozoku_group_ids . ")";
        }


        if( $this_sess['search_condition']['join_admin_id'] != ""){
            $join .= " and";
            $join .= " user_admin_id = '"._as($this_sess['search_condition']['join_admin_id'])."'";
        }
        $join .= " group by juser_user_syoutai_id";
        $join .= " ) as juser on (juser.juser_user_syoutai_id = v_syoutai.syoutai_id)  "."\n";
    }

    // 選択のイベントに該当しない来場者情報
    if ( $this_sess['search_condition']['not_join_event_id'] != "" ) {
        $join .= " left join";
        $join .= " ( select user_syoutai_id from v_user where user_delete_date is null and user_big_cate in (1,2,3,4) and";
        $join .= " user_event_id = '"._as($this_sess['search_condition']['not_join_event_id'])."'";
        $join .= " ) as nuser on (nuser.user_syoutai_id = v_syoutai.syoutai_id)  "."\n";

        $where .= " and nuser.user_syoutai_id is null";
    }

    unset( $this_sess['search_condition']['syozoku_name']);
    unset( $this_sess['search_condition']['admin_name']);
    //unset( $this_sess['search_condition']['admin_syozoku_id']);
    if($this_sess['search_condition']['join_admin_id'] !=""){
        $sql = "";
        $sql .= " select admin_name,syozoku_id, syozoku_name "."\n";
        $sql .= " from v_admin "."\n";
        $sql .= " left join m_syozoku on (m_syozoku.syozoku_id = v_admin.admin_syozoku_id) "."\n";
        $sql .= " where admin_delete_date is null "."\n";
        $sql .= " and syozoku_delete_date is null "."\n";
        $sql .= " and admin_id = '"._as($this_sess['search_condition']['join_admin_id'])."'";
        $tantou_recs = _select($sql);

        $this_sess['search_condition']['syozoku_name']     = $tantou_recs[0]['syozoku_name'];
        $this_sess['search_condition']['admin_name']       = $tantou_recs[0]['admin_name'];
        //$this_sess['search_condition']['admin_syozoku_id'] = $tantou_recs[0]['syozoku_id'];
    }

    unset( $this_sess['user_syozoku_name'] );
    unset( $this_sess['user_admin_name'] );
    unset( $this_sess['user_admin_syozoku_id'] );

    // イベントに招待する担当者は維持する
    $this_sess['user_admin_id'] = $_request['user_admin_id'];
    // イベントに招待する担当者が空だった場合
    if ( $this_sess['user_admin_id'] == '' ){
        // ログインIDを設定する
        $this_sess['user_admin_id'] = $_SESSION[_PROJECT_NAME]['admin_login']['admin_id'];
    }

    if ( $this_sess['user_admin_id'] != '' ){
        $sql = "";
        $sql .= " select admin_name,syozoku_id, syozoku_name "."\n";
        $sql .= " from v_admin "."\n";
        $sql .= " left join m_syozoku on (m_syozoku.syozoku_id = v_admin.admin_syozoku_id) "."\n";
        $sql .= " where admin_delete_date is null "."\n";
        $sql .= " and syozoku_delete_date is null "."\n";
        $sql .= " and admin_id = '"._as($this_sess['user_admin_id'])."'";
        $tantou_recs = _select($sql);

        $this_sess['user_syozoku_name']     = $tantou_recs[0]['syozoku_name'];
        $this_sess['user_admin_name']       = $tantou_recs[0]['admin_name'];
        $this_sess['user_admin_syozoku_id'] = $tantou_recs[0]['syozoku_id'];

    }

    // ---------------------------------------------------------------------------------------- //
    if ( $this_sess['search_condition']['t_event_id'] != '' ){
        // ******************************************************************************************************
        // データ抽出
        // ******************************************************************************************************
        $limit = 50;
        $offset = 0;
        if( $this_sess['search_condition']['offset'] != "" ){
            $offset = intval( $this_sess['search_condition']['offset'] );
        }

        // 件数取得SQL
        $sql  = "";
        $sql .= " select count(v_syoutai.syoutai_id) as all_cnt from v_syoutai ";
        $sql .= $join;
        $sql .= " where ".$where;
        $rec = _select($sql);

        $allcnt = 0;
        if($rec[0]['all_cnt'] > 0){
            $allcnt = $rec[0]['all_cnt'];
        }

        if($allcnt > 0){
            // 表示SQL
            $sql  = "";
            $sql .= " select * from v_syoutai ";
            $sql .= $join;
            $sql .= " where ".$where;
            $sql .= " order by ".$this_sess['search_condition']['order_by'];
            if($_request['exec']!="csv_download" && $_request['exec']!="import_csv_download"){
                $sql .= " limit ".$offset." , ".$limit;
            }

            if($_request['exec']=="csv_download") {
                set_time_limit(180); //3分起動
                ini_set('memory_limit', "1024M"); //メモリ拡大

                $csv_head = '';
                // $csv_head .=  '"SANSANID"'; 2021.05.17 del
                $csv_head .= '"招待者氏名"';
                $csv_head .= ',"招待者氏名カナ"';
                $csv_head .= ',"VIP"';
                $csv_head .= ',"大分類"';
                $csv_head .= ',"中分類"';
                $csv_head .= ',"企業名"';
                $csv_head .= ',"企業名カナ"';
                $csv_head .= ',"部署"';
                $csv_head .= ',"役職"';
                $csv_head .= ',"送信先メールアドレス"';
                $csv_head .= ',"ログインID"';
                $csv_head .= ',"備考"';
                $csv_head .= "\r\n";

                $w_flnm = "来場者データへの登録検索_" . date("YmdHis") . ".csv";
                header("Content-Disposition: attachment; filename=\"" . mb_convert_encoding($w_flnm, "SJIS-WIN", _ENCODING_SRC) . "\"");
                header("Content-Type: application/octet-stream; name=\"" . $w_flnm . "\"");

                echo mb_convert_encoding($csv_head, "SJIS-WIN", _ENCODING_SRC);

                $result = _query($conn, $sql);

                $row = 0;
                while ($rec = _fetchArray($result, $row)) {
                    $vip = "";
                    if ($rec['syoutai_vip_flg'] != 0) $vip = $_conf_vip[$rec['syoutai_vip_flg']];

                    $csv_buff = '';
                    // $csv_buff .=  '"'.$rec['syoutai_sansan_id'].'"'; 2021.05.17 del
                    $csv_buff .= '"' . csvSafe($rec['syoutai_name']) . '"';
                    $csv_buff .= ',"' . csvSafe($rec['syoutai_name_kana']) . '"';
                    $csv_buff .= ',"' . csvSafe($vip) . '"';
                    $csv_buff .= ',"' . csvSafe($_conf_big_cate1[$rec['syoutai_big_cate']]) . '"';
                    $csv_buff .= ',"' . csvSafe($_conf_mid_cate1[$rec['syoutai_mid_cate']]) . '"';
                    $csv_buff .= ',"' . csvSafe($rec['syoutai_company_name']) . '"';
                    $csv_buff .= ',"' . csvSafe($rec['syoutai_company_name_kana']) . '"';
                    $csv_buff .= ',"' . csvSafe($rec['syoutai_busyo']) . '"';
                    $csv_buff .= ',"' . csvSafe($rec['syoutai_yakusyoku']) . '"';
                    $csv_buff .= ',"' . csvSafe($rec['syoutai_mail']) . '"';
                    $csv_buff .= ',"' . csvSafe($rec['syoutai_login_id']) . '"';
                    $csv_buff .= ',"' . csvSafe($rec['syoutai_biko']) . '"';
                    $csv_buff .= "\r\n";
                    echo mb_convert_encoding($csv_buff, "SJIS-WIN", _ENCODING_SRC);
                    $row++;
                }
                _freeResult($result);

                exit();

            } else if ($_request['exec']=="import_csv_download") {

                set_time_limit(180); //3分起動
                ini_set('memory_limit', "1024M"); //メモリ拡大

                $event_ids = explode(",", $this_sess['search_condition']['export_events']);

                $event_sql  = "";
                $event_sql .= " select * from m_event ";
                $event_sql .= " where event_id in ('" . implode("','", $event_ids) ."') ";
                $event_sql .= " order by event_insert_date asc";
                $events = _select($event_sql);

                $csv_head = [];
                $csv_head[] = '"来場者マスタID"';
                $csv_head[] = '"来場者氏名"';
                $csv_head[] = '"大分類"';
                $csv_head[] = '"企業名"';
                $csv_head[] = '"部署"';
                $csv_head[] = '"役職"';
                $csv_head[] = '"メールアドレス"';
                $csv_head[] = '"WEB招待"';
                $csv_head[] = '"メール通知（1:後から, 0:送信しない）"';
                $csv_head[] = '"AC担当者メールアドレス"';
                $csv_head[] = '"タグ文字列"';

                $event_info = [];

                foreach ($events as $event) {
                    $event_id = $event['event_id'];
                    $event_name = $event['event_name'];
                    $event_pulldown_name = $event['event_pulldown_name'];
                    $start_index = count($csv_head);
                    $raijou_yotei_times = [];

                    $raijou_head = $event_pulldown_name . '_来場日時_';
                    $raijou_yotei_time = explode("#", $event['event_raijyou_yotei_time'] );
                    foreach ($raijou_yotei_time as $raijou_yotei) {
                        $raijou_yotei_times[] = $raijou_yotei;
                        $csv_head[] = '"' . $raijou_head . str_replace(' ', '_', $raijou_yotei) . '"';
                    }

                    $end_index = count($csv_head) - 1;

                    $event_info[] = [
                        'event_id' => $event_id,
                        'event_name' => $event_name,
                        'start_index' => $start_index,
                        'end_index' => $end_index,
                        'raijou_yotei_times' => $raijou_yotei_times
                    ];
                }

                $event_info_json = json_encode($event_info, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);

                $csv_info .= '"' . csvSafe($event_info_json) . '"';
                $csv_info .= "\r\n";

                $csv_head_str .= implode(',', $csv_head);
                $csv_head_str .= "\r\n";

                $w_flnm = "来場日時一括登録・編集CSV_" . date("YmdHis") . ".csv";
                header("Content-Disposition: attachment; filename=\"" . mb_convert_encoding($w_flnm, "SJIS-WIN", _ENCODING_SRC) . "\"");
                header("Content-Type: application/octet-stream; name=\"" . $w_flnm . "\"");

                echo mb_convert_encoding($csv_info, "SJIS-WIN", _ENCODING_SRC);
                echo mb_convert_encoding($csv_head_str, "SJIS-WIN", _ENCODING_SRC);

                $result = _query($conn, $sql);

                $row = 0;
                while ($rec = _fetchArray($result, $row)) {
                    $vip = "";
                    if ($rec['syoutai_vip_flg'] != 0) $vip = $_conf_vip[$rec['syoutai_vip_flg']];

                    $csv_buff = '';
                    $csv_buff .= '"' . csvSafe($rec['syoutai_id']) . '"';
                    $csv_buff .= ',"' . csvSafe($rec['syoutai_name']) . '"';
                    $csv_buff .= ',"' . csvSafe($_conf_big_cate[$rec['syoutai_big_cate']]) . '"';
                    $csv_buff .= ',"' . csvSafe($rec['syoutai_company_name']) . '"';
                    $csv_buff .= ',"' . csvSafe($rec['syoutai_busyo']) . '"';
                    $csv_buff .= ',"' . csvSafe($rec['syoutai_yakusyoku']) . '"';
                    $csv_buff .= ',"' . csvSafe($rec['syoutai_mail']) . '"';
                    $csv_buff .= ',"' . csvSafe('') . '"';
                    $csv_buff .= ',"' . csvSafe('') . '"';
                    $csv_buff .= ',"' . csvSafe('') . '"';
                    $csv_buff .= ',"' . csvSafe('') . '"';
                    $csv_buff .= "\r\n";
                    echo mb_convert_encoding($csv_buff, "SJIS-WIN", _ENCODING_SRC);
                    $row++;
                }
                _freeResult($result);

                exit();

            }else{

                $main_recs = _select($sql);
                for ($i=0; $i < _count($main_recs); $i++) {
                    $main_recs[$i]['disp_big_cate'] = $_conf_big_cate_detail[$main_recs[$i]['syoutai_big_cate']];
                }

            }
        }

        _make_pagenavi2( $blade, $_request, $offset, $allcnt, $limit );

    }

    _setAssign($blade,$this_sess);
    $blade->assign('main_recs', $main_recs);

    //(招待者)来場予定日時
    $wArr = explode("#", $select_event_rec['event_syoutai_yotei_time']);
    $_conf_syoutai_yotei_time = array();
    for ($i=0; $i < _count($wArr); $i++) {
        $dtArr = explode(" ", $wArr[$i],2);
        $ymd = $dtArr[0];
        //2021/07/08 Mod --------- Before ------
        // $disp_ymd = date("n月j日",strtotime($dtArr[0]) )."（"._getYoubi($dtArr[0])."）";
        //2021/07/08 Mod --------- After ------
        if( $ymd=='2999/01/01' ){
            $disp_ymd = "　　　　　　";
        }else{
            $disp_ymd = date("n月j日",strtotime($dtArr[0]) )."（"._getYoubi($dtArr[0])."）";
        }
        //2021/07/08 Mod --------- End ------
        $hi = $dtArr[1];
        $_conf_syoutai_yotei_time[$ymd]['disp_ymd'] = $disp_ymd;

        $checked="";
        if( $this_sess['user_raijyou_yotei_time'] != "") {
            if( strpos($this_sess['user_raijyou_yotei_time'],$wArr[$i]) !== FALSE ){
                $checked = "checked";
            }
        }
        $_conf_syoutai_yotei_time[$ymd]['his'][] = array('hi'=>$hi, 'checked'=>$checked);
        // 2021.05.31 add ------ Start ------
        $ymd_hi = $ymd .' '. $hi;

        //2021/07/08 Mod -------- After ------
        // $_conf_syoutai_yotei_time2[$ymd_hi] = $ymd_hi;
        //2021/07/08 Mod -------- After ------
        $ymd_hi_val = $ymd_hi;
        $ymd_hi_val = str_replace("2999/01/01 ", "", $ymd_hi_val);
        $_conf_syoutai_yotei_time2[$ymd_hi] = $ymd_hi_val;
        //2021/07/08 Mod -------- End ------

        // 2021.05.31 add ------ End   ------

    }
    $blade->assign('_conf_syoutai_yotei_time',$_conf_syoutai_yotei_time);
    $blade->assign('_conf_syoutai_yotei_time2',$_conf_syoutai_yotei_time2);

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
    $sql .= " and syozoku_hidden_flg = 0";
    $sql .= " order by syozoku_id asc";
    $syozoku_recs = _select($sql);
    $_conf_syozoku = array();
    for ($i=0; $i < _count($syozoku_recs); $i++) {
        $_conf_syozoku[$syozoku_recs[$i]['syozoku_id']] = $syozoku_recs[$i]['syozoku_name'];
    }
    $blade->assign('_conf_syozoku',$_conf_syozoku);

    //閲覧部署グループマスタ
    $sql = "";
    $sql .= "select * from m_syozoku_group";
    $sql .= " where";
    $sql .= " szkgrp_delete_date is null";
    $sql .= " and szkgrp_hidden_flg = 0";
    $sql .= " order by szkgrp_id asc";
    $syozoku_group_recs = _select($sql);
    $_conf_syozoku_group = array();
    for ($i=0; $i < _count($syozoku_group_recs); $i++) {
        $_conf_syozoku_group[ $syozoku_group_recs[$i]['szkgrp_id'] ] = $syozoku_group_recs[$i]['szkgrp_name'];
    }
    $blade->assign('_conf_syozoku_group',$_conf_syozoku_group);

    // エクスポート対象イベント
    $sql  = "";
    $sql .= " select * from m_event ";
    $sql .= " where event_kaisai_ymd_st > " . '"' . $_now_timestamp .'" ';
    $sql .= " order by event_insert_date asc";
    $event_recs = _select($sql);
    $_conf_events = array();
    $_conf_selected_events = array();
    for ($i=0; $i < _count($event_recs); $i++) {
        $_conf_events[$event_recs[$i]['event_id']] = $event_recs[$i]['event_name'];
        $_conf_selected_events[] = $event_recs[$i]['event_id'];
    }
    $blade->assign('_conf_events',$_conf_events);
    $blade->assign('_conf_selected_events', $_conf_selected_events);

    //担当者
    // $sql = "";
    // $sql .= "select * from v_admin";
    // $sql .= " join v_user on (v_admin.admin_id = v_user.user_admin_id)";
    // $sql .= " left join m_syozoku on (m_syozoku.syozoku_id = v_admin.admin_syozoku_id)";
    // $sql .= " where";
    // $sql .= " admin_delete_date is null";
    // $sql .= " and admin_mail != 'admin'";
    // $sql .= " and user_delete_date is null";
    // $sql .= " and user_event_id = '"._as($_SESSION[_PROJECT_NAME]['select_event_id'])."'";
    // if($this_sess['search_condition']['admin_syozoku_id']!=""){
    //     $sql .= " and admin_syozoku_id = '".$this_sess['search_condition']['admin_syozoku_id']."'";
    // }
    // $sql .= " order by admin_tanarea_id asc,admin_syozoku_id asc,admin_id asc";
    // $tan_recs = _select($sql);
    // $_conf_tantousya = array();
    // for ($i=0; $i < _count($tan_recs); $i++) {
    //     $_conf_tantousya[ $tan_recs[$i]['admin_id'] ] = $tan_recs[$i]['syozoku_name']." ".$tan_recs[$i]['admin_name'];
    // }
    // $blade->assign('_conf_tantousya',$_conf_tantousya);

    $blade->assign('_conf_join_event',$_conf_join_event);


    $before_event_west = $bef_west_recs[0]['event_pulldown_name'];
    $before_event_east = $bef_east_recs[0]['event_pulldown_name'];

    $blade->assign('before_event_west', $before_event_west);
    $blade->assign('before_event_east', $before_event_east);

    $blade->assign('_conf_vip',$_conf_vip);
    $blade->assign('_conf_big_cate',$_conf_big_cate);
    $blade->assign('_conf_big_cate_detail',$_conf_big_cate_detail);
    $blade->assign('_conf_mid_cate',$_conf_mid_cate);
    $blade->assign('syoutai_page', 1);

    $contents_title = "来場者データへの登録";

    $blade->assign('title_bgcolor',$select_event_rec['event_title_bgcolor']);

    $active_menu = "user_list";
    $contents_tpl = "syoutai_raijyoudata_make";
