<?php
    if( !defined("_PROJECT_DISP_NAME") ){
        die("System Error");
    }

    if( $_SESSION[_PROJECT_NAME]['admin_login']['admin_id'] == "" ){
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
    if( $_request['exec'] == "search" || $_request['exec'] == "csv_download"){
        unset( $this_sess['search_condition'] );
        $this_sess['search_condition'] = array();
        $this_sess['search_condition'] = _array_merge( $this_sess['search_condition'], $_request );
    }elseif( $_request['offset'] != "" ){
        $this_sess['search_condition']['offset'] = $_request['offset'];
    }elseif( $_request['sess_no_init'] == "" ){
        unset( $this_sess['search_condition'] );
        $this_sess['search_condition']['order_by'] = "company_id asc";
    }

    if( $this_sess['search_condition']['order_by'] == '' ){
        $this_sess['search_condition']['order_by'] = "company_id asc";
    }

    // ******************************************************************************************************
    // 並び順配列作成
    // ******************************************************************************************************
    $order_by_arr = array();
    $order_by_arr['company_id asc'] = "登録順(昇順)";
    $order_by_arr['company_id desc'] = "登録順(降順)";
    $blade->assign('order_by_arr',$order_by_arr);

    // --------------------------------------------------- //
    // 共通WHERE
    // --------------------------------------------------- //
    $where = "";
    $where .= " true "."\n";
    if ($_SESSION[_PROJECT_NAME]['admin_login']['admin_master_kengen'] != "1") {
        $where .= " and tcr_request_admin_id = '" . $_SESSION[_PROJECT_NAME]['admin_login']['admin_id'] . "' ";
    }


    // --------------------------------------------------- //
    // 検索条件
    // --------------------------------------------------- //

    // ******************************************************************************************************
    // プルダウンの組み立て
    // ******************************************************************************************************

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
    $sql .= " select count(tcr_id) as all_cnt "."\n";
    $sql .= " from t_company_request"."\n";
    $sql .= " where ".$where;
    $rec = _select($sql);

    $allcnt = 0;
    if($rec[0]['all_cnt'] > 0){
        $allcnt = $rec[0]['all_cnt'];
    }

    if($allcnt > 0){
        // 表示SQL
        $sql  = "";
        $sql .= " select *"."\n";
        $sql .= " from t_company_request"."\n";
        // where句
        $sql .= " where ".$where."\n";
        // order by句
        $sql .= " order by tcr_insert_date desc"."\n";
        $sql .= " limit ".$offset." , ".$limit;
        $main_recs = _select($sql);
    }

    _make_pagenavi2( $blade, $_request, $offset, $allcnt, $limit );

    _setAssign($blade,$this_sess);
    $blade->assign('big_cate', $_conf_big_cate);
    $blade->assign('main_recs', $main_recs);
    $blade->assign('set_pattern', $_request['set_pattern']);

    $contents_title = "企業マスタ申請一覧画面";
    $active_menu = "company_request_list";
    $contents_tpl = "company_request_list";

    var_dump($success_msg);
    var_dump($err_msg);
