<?php
    if( !defined("_PROJECT_DISP_NAME") ){
        die("System Error");
    }

    if( $_SESSION[_PROJECT_NAME]['admin_login']['admin_id'] == "" ){
        die('System Error');
    }

    if( $_SESSION[_PROJECT_NAME]['admin_login']['admin_master_kengen'] != "1" ){
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
    if( $_request['exec'] == "search"){
        unset( $this_sess['search_condition'] );
        $this_sess['search_condition'] = array();
        $this_sess['search_condition'] = _array_merge( $this_sess['search_condition'], $_request );
    }elseif( $_request['offset'] != "" ){
        $this_sess['search_condition']['offset'] = $_request['offset'];
    }elseif( $_request['sess_no_init'] == "" ){
        unset( $this_sess['search_condition'] );
        $this_sess['search_condition']['order_by'] = "area_id asc";
    }

    if( $this_sess['search_condition']['order_by'] == '' ){
        $this_sess['search_condition']['order_by'] = "area_id asc";
    }

    // ******************************************************************************************************
    // 並び順配列作成
    // ******************************************************************************************************
    // $order_by_arr = array();
    // $order_by_arr['area_id asc'] = "所属ID順";
    // $blade->assign('order_by_arr',$order_by_arr);


    // --------------------------------------------------- //
    // 共通WHERE
    // --------------------------------------------------- //
    $where = "";
    $where .= "area_delete_date is null";
    $where .= " and area_event_id = '"._as($_SESSION[_PROJECT_NAME]['select_event_id'])."'";

    // --------------------------------------------------- //
    // 検索条件
    // --------------------------------------------------- //
    if($this_sess['search_condition']['area_name'] !=""){
        $where .= " and area_name like '%"._as( $this_sess['search_condition']['area_name'] )."%'";
    }


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
    $sql .= " select count(m_area.area_id) as all_cnt "."\n";
    $sql .= " from m_area"."\n";
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
        $sql .= " from m_area"."\n";
        // where句
        $sql .= " where ".$where."\n";
        // order by句
        if( $this_sess['search_condition']['order_by'] != '' ){
            $sql .= " order by ".$this_sess['search_condition']['order_by']."\n";
        }
        $sql .= " limit ".$offset." , ".$limit;
        $main_recs = _select($sql);
        for ($idx=0; $idx < _count($main_recs); $idx++) {
            $main_recs[$idx]['area_tanmatsuhaichi_disp'] = "";
            $main_recs[$idx]['area_tanmatsuhaichi_disp'] = $_conf_tanmatsuhaichi_kbn[$main_recs[$idx]['area_tanmatsuhaichi_kbn']];
        }
    }

    _make_pagenavi2( $blade, $_request, $offset, $allcnt, $limit );

    _setAssign($blade,$this_sess);
    $blade->assign('main_recs', $main_recs);

    $blade->assign('title_bgcolor',$select_event_rec['event_title_bgcolor']);

    $contents_title = "会場エリア設定（".$select_event_rec['event_name'] .":".$select_event_rec['event_kaijyou_name']."）";
    $active_menu = "area_list";
    $contents_tpl = "area_list";
