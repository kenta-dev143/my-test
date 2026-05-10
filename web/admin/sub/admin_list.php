<?php
    if( !defined("_PROJECT_DISP_NAME") ){
        die("System Error");
    }

    if( $_SESSION[_PROJECT_NAME]['admin_login']['admin_id'] == "" ){
        die('System Error');
    }

    if( $_SESSION[_PROJECT_NAME]['admin_login']['admin_master_kengen'] != "1" && $_request['page'] != "admin_list_select_win" ){
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

    $_ac_mid_cate = array();
    foreach ($_conf_mid_cate2 as $key => $value) {
        if(substr($value,0,2)=="AC"){
            $_ac_mid_cate[$key] = $value;
        }
    }
    $blade->assign('_ac_mid_cate',$_ac_mid_cate);

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
        $this_sess['search_condition']['order_by'] = "admin_id asc, admin_tanarea_id asc";
    }

    if( $this_sess['search_condition']['order_by'] == '' ){
        $this_sess['search_condition']['order_by'] = "admin_id asc, admin_tanarea_id asc";
    }

    if ( $_request['page'] == "admin_list_select_win" ){
        if( $_request['select_event_id'] != '' ){
            $this_sess['search_condition']['select_event_id'] = $_request['select_event_id'];
        }

        if( $_request['admin_syozoku_id'] != '' ){
            $this_sess['search_condition']['admin_syozoku_id'] = $_request['admin_syozoku_id'];
        }

        if( $_request['syoutai_only'] != '' ){
            $this_sess['search_condition']['syoutai_only'] = $_request['syoutai_only'];
        }
    }

    // ******************************************************************************************************
    // 並び順配列作成
    // ******************************************************************************************************
    $order_by_arr = array();
    $order_by_arr['admin_id asc, admin_tanarea_id asc'] = "登録順(昇順)";
    $order_by_arr['admin_id desc, admin_tanarea_id desc'] = "登録順(降順)";
    $order_by_arr['admin_tanarea_id asc, admin_id asc'] = "担当エリア順";
    $order_by_arr['admin_syozoku_id asc, admin_id asc'] = "所属(支店名・部署名)順";
    $blade->assign('order_by_arr',$order_by_arr);

    // 担当者エリア
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

    // 所属支店部署マスタ
    $sql = "";
    $sql .= "select * from m_syozoku";
    $sql .= " where";
    $sql .= " syozoku_delete_date is null";
    $sql .= " and syozoku_hidden_flg = 0 ";
    $sql .= " order by syozoku_id asc";
    $syozoku_recs = _select($sql);
    $_conf_syozoku = array();
    for ($i=0; $i < _count($syozoku_recs); $i++) {
        $_conf_syozoku[ $syozoku_recs[$i]['syozoku_id'] ] = $syozoku_recs[$i]['syozoku_name'];
    }
    $blade->assign('_conf_syozoku',$_conf_syozoku);

    // --------------------------------------------------- //
    // 共通WHERE
    // --------------------------------------------------- //
    $where = "";
    $where .= "admin_delete_date is null"."\n";
    $where .= "and v_admin.admin_id != 'a0000001'"."\n";

    // --------------------------------------------------- //
    // 検索条件
    // --------------------------------------------------- //


    if($this_sess['search_condition']['admin_id'] !=""){
        $where .= " and admin_id = '"._as( $this_sess['search_condition']['admin_id'] )."'";
    }

    if($this_sess['search_condition']['admin_name'] !=""){
        $where .= " and admin_name like '%"._as( $this_sess['search_condition']['admin_name'] )."%'";
    }

    if($this_sess['search_condition']['admin_tanarea_id'] !=""){
        $where .= " and admin_tanarea_id = '"._as( $this_sess['search_condition']['admin_tanarea_id'] )."'";
    }

    if($this_sess['search_condition']['admin_syozoku_id'] !=""){
        $where .= " and admin_syozoku_id = '"._as( $this_sess['search_condition']['admin_syozoku_id'] )."'";
    }

    if($this_sess['search_condition']['admin_mid_cate'] !=""){
        $where .= " and admin_mid_cate = "._as( $this_sess['search_condition']['admin_mid_cate'] )."";
    }

    if($this_sess['search_condition']['select_event_id']){
        $sql  = "";
        $sql .= " select event_archived_flg"."\n";
        $sql .= " from m_event"."\n";
        $sql .= " where event_id = '"._as($this_sess['search_condition']['select_event_id'])."'\n";
        $sql .= " and event_delete_date is null"."\n";
        $recs = _select($sql);

        $userTableName = 'v_user';
        if (isset($recs[0]) && $recs[0]['event_archived_flg'] === '1') {
            $userTableName = 'v_auser';
        }

        $join = " inner join ";
        $join .= " ( select user_admin_id from " . $userTableName;
        $join .= " where user_event_id = '"._as($this_sess['search_condition']['select_event_id'])."'";
        if($this_sess['search_condition']['syoutai_only'] !=""){
            $join .= " and user_big_cate in (1,2,3,4)";
        }
        $join .= "  group by user_admin_id ";
        $join .= " ) as juser on (juser.user_admin_id = v_admin.admin_id)  "."\n";
    }

    // ******************************************************************************************************
    // プルダウンの組み立て
    // ******************************************************************************************************
    $sql  = "";
    $sql .= " select event_id, event_pulldown_name"."\n";
    $sql .= " from m_event"."\n";
    $sql .= " where event_delete_date is null"."\n";
    $sql .= " order by event_id"."\n";
    $recs = _select($sql);
    $_conf_event = array();
    for ($loop=0; $loop < _count($recs); $loop++) {
        $_conf_event[ $recs[$loop]['event_id'] ] = $recs[$loop]['event_pulldown_name'];
    }
    $blade->assign('_conf_event', $_conf_event);

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
    $sql .= " select count(v_admin.admin_id) as all_cnt "."\n";
    $sql .= " from v_admin"."\n";
    $sql .= $join;
    // 担当エリア
    $sql .= " left join m_tantou_area on ( v_admin.admin_tanarea_id = m_tantou_area.tanarea_id )"."\n";
    // 属支店・部署
    $sql .= " left join m_syozoku on ( v_admin.admin_syozoku_id = m_syozoku.syozoku_id )"."\n";
    $sql .= " where ".$where;
    $rec = _select($sql);

    $allcnt = 0;
    if($rec[0]['all_cnt'] > 0){
        $allcnt = $rec[0]['all_cnt'];
    }

    if($allcnt > 0){
        // 表示SQL
        $sql  = "";
        $sql .= " select v_admin.*, m_tantou_area.*, m_syozoku.*, GROUP_CONCAT(c.company_name) as companies"."\n";
        $sql .= " from v_admin"."\n";
        $sql .= $join;
        // 担当エリア
        $sql .= " left join m_tantou_area on ( v_admin.admin_tanarea_id = m_tantou_area.tanarea_id )"."\n";
        // 属支店・部署
        $sql .= " left join m_syozoku on ( v_admin.admin_syozoku_id = m_syozoku.syozoku_id )"."\n";
        $sql .= " left join c_admin_companies cac on ( cac.admin_id = v_admin.admin_id ) " . "\n";
        $sql .= " left join m_company c on ( c.company_id = cac.company_id )" . "\n";
        // where句
        $sql .= " where ".$where."\n";
        $sql .= " group by v_admin.admin_id" . "\n";
        // order by句
        if( $this_sess['search_condition']['order_by'] != '' ){
            $sql .= " order by ".$this_sess['search_condition']['order_by']."\n";
        }
        $sql .= " limit ".$offset." , ".$limit;
        $main_recs = _select($sql);
        for ($idx=0; $idx < count($main_recs); $idx++) {
            // 中分類
            $main_recs[$idx]['admin_mid_cate_disp'] = $_ac_mid_cate[ $main_recs[$idx]['admin_mid_cate'] ];
            // ログイン権限(表示用)
            $main_recs[$idx]['admin_login_kengen_disp'] = $_conf_login_kengen[ $main_recs[$idx]['admin_login_kengen'] ];
            // 閲覧権限(表示用)
            $main_recs[$idx]['admin_syuukei_etsuran_kengen_disp'] = $_conf_syuukei_etsuran_kengen[ $main_recs[$idx]['admin_syuukei_etsuran_kengen'] ];
            // マスタ権限(表示用)
            $main_recs[$idx]['admin_master_kengen_disp'] = $_conf_master_kengen[ $main_recs[$idx]['admin_master_kengen'] ];
            // ユーザ権限(表示用)
            $main_recs[$idx]['admin_user_kengen_disp'] = $_conf_user_kengen[ $main_recs[$idx]['admin_user_kengen'] ];
        }
    }

    _make_pagenavi2( $blade, $_request, $offset, $allcnt, $limit );

    _setAssign($blade,$this_sess);
    $blade->assign('main_recs', $main_recs);
    $blade->assign('set_pattern', $_request['set_pattern']);

    $contents_title = "担当者管理";
    $active_menu = "admin_list";
    $contents_tpl = "admin_list";
