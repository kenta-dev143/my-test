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

    // ******************************************************************************************************
    // 検索
    // ******************************************************************************************************
    if($_request['exec']=="now_send"){
        if($_request['mailhd_id']!=""){

            _query($conn, "begin");

            $array = array();
            $array['mailhd_yoyaku_ymdhi'] = "'"._as(date("Y/m/d H:i"))."'";
            $array['mailhd_update_date'] = "'".$_now_timestamp."'";
            $where = "mailhd_id = "._as($_request['mailhd_id']);
            _update('t_mail_head',$array,$where);

            _query($conn, "commit");

        }
    }elseif( $_request['exec'] == "search"){
        unset( $this_sess['search_condition'] );
        $this_sess['search_condition'] = array();
        $this_sess['search_condition'] = _array_merge( $this_sess['search_condition'], $_request );
    }elseif( $_request['offset'] != "" ){
        $this_sess['search_condition']['offset'] = $_request['offset'];
    }elseif( $_request['sess_no_init'] == "" ){
        unset( $this_sess['search_condition'] );
        $this_sess['search_condition']['order_by'] = "mailhd_yoyaku_ymdhi desc, mailhd_id desc";
    }

    if( $this_sess['search_condition']['order_by'] == '' ){
        $this_sess['search_condition']['order_by'] = "mailhd_yoyaku_ymdhi desc, mailhd_id desc";
    }

    // ******************************************************************************************************
    // 並び順配列作成
    // ******************************************************************************************************
    // $order_by_arr = array();
    // $order_by_arr['mailhd_yoyaku_ymdhi desc, mailhd_id desc'] = "予約日時(降順)";
    // $blade->assign('order_by_arr',$order_by_arr);


    // --------------------------------------------------- //
    // 共通WHERE
    // --------------------------------------------------- //
    $where = "";
    $where .= "mailhd_delete_date is null";
    $where .= " and mailhd_test_send_flg = 0";

    // --------------------------------------------------- //
    // 検索条件
    // --------------------------------------------------- //
    // if($this_sess['search_condition']['xxxxx'] !=""){
    //     $where .= " and xxx like '%"._as( $this_sess['search_condition']['xxx'] )."%'";
    // }


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
    $sql .= " select count(t_mail_head.mailhd_id) as all_cnt "."\n";
    $sql .= " from t_mail_head"."\n";
    $sql .= " where ".$where;
    $rec = _select($sql);

    $allcnt = 0;
    if($rec[0]['all_cnt'] > 0){
        $allcnt = $rec[0]['all_cnt'];
    }

    if($allcnt > 0){
        // 表示SQL
        $sql  = "";
        $sql .= " select "."\n";
        $sql .= "  mailhd_id";
        $sql .= " ,mailhd_mailt_name";
        $sql .= " ,mailhd_mailt_key";
        $sql .= " ,mailhd_subject";
        $sql .= " ,mailhd_body";
        $sql .= " ,mailhd_yoyaku_ymdhi";
        $sql .= " ,mailhd_status";
        $sql .= " ,mailhd_error_detail";
        $sql .= " ,mailhd_test_send_flg";
        $sql .= " ,admin_name";
        $sql .= " ,syozoku_name";
        $sql .= " ,coalesce(count(t_mail_list.maills_mailhd_id),0) as detail_cnt";
        $sql .= " from t_mail_head"."\n";
        $sql .= " left join t_mail_list on (t_mail_list.maills_mailhd_id = t_mail_head.mailhd_id)"."\n";
        $sql .= " left join v_admin on (v_admin.admin_id = t_mail_head.mailhd_insert_admin_id)"."\n";
        $sql .= " left join m_syozoku on (m_syozoku.syozoku_id = v_admin.admin_syozoku_id)"."\n";
        // where句
        $sql .= " where ".$where."\n";

        $sql .= " group by ";
        $sql .= "  mailhd_id";
        $sql .= " ,mailhd_mailt_name";
        $sql .= " ,mailhd_mailt_key";
        $sql .= " ,mailhd_subject";
        $sql .= " ,mailhd_body";
        $sql .= " ,mailhd_yoyaku_ymdhi";
        $sql .= " ,mailhd_status";
        $sql .= " ,mailhd_error_detail";
        $sql .= " ,mailhd_test_send_flg";
        $sql .= " ,admin_name";
        $sql .= " ,syozoku_name";

        // order by句
        if( $this_sess['search_condition']['order_by'] != '' ){
            $sql .= " order by ".$this_sess['search_condition']['order_by']."\n";
        }
        $sql .= " limit ".$offset." , ".$limit;
        $main_recs = _select($sql);
    }

    _make_pagenavi2( $blade, $_request, $offset, $allcnt, $limit );

    _setAssign($blade,$this_sess);
    $blade->assign('main_recs', $main_recs);

    //$blade->assign('title_bgcolor',$select_event_rec['event_title_bgcolor']);

    $contents_title = "一括メール送信一覧";
    $active_menu = "user_mail_send_";
    $contents_tpl = "user_mail_send_list";
