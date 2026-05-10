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
        $this_sess['search_condition']['order_by'] = "mailt_delete_fuka desc, mailt_system_use_only desc, mailt_insert_date asc";
    }

    if( $this_sess['search_condition']['order_by'] == '' ){
        $this_sess['search_condition']['order_by'] = "mailt_delete_fuka desc, mailt_system_use_only desc, mailt_insert_date asc";
    }

    // ******************************************************************************************************
    // 並び順配列作成
    // ******************************************************************************************************


    // --------------------------------------------------- //
    // 共通WHERE
    // --------------------------------------------------- //
    $where = "";
    $where .= "mailt_delete_date is null";

    // --------------------------------------------------- //
    // 検索条件
    // --------------------------------------------------- //
    if($this_sess['search_condition']['mailt_name'] !=""){
        $where .= " and mailt_name like '%"._as( $this_sess['search_condition']['mailt_name'] )."%'";
    }

    if($this_sess['search_condition']['mailt_subject'] !=""){
        $where .= " and mailt_subject like '%"._as( $this_sess['search_condition']['mailt_subject'] )."%'";
    }


    // ******************************************************************************************************
    // データ抽出
    // ******************************************************************************************************
    // $limit = 50;
    // $offset = 0;
    // if( $this_sess['search_condition']['offset'] != "" ){
    //     $offset = intval( $this_sess['search_condition']['offset'] );
    // }

    // 件数取得SQL
    $sql  = "";
    $sql .= " select count(m_mail_template.mailt_key) as all_cnt "."\n";
    $sql .= " from m_mail_template"."\n";
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
        $sql .= " from m_mail_template"."\n";
        // where句
        $sql .= " where ".$where."\n";
        // order by句
        if( $this_sess['search_condition']['order_by'] != '' ){
            $sql .= " order by ".$this_sess['search_condition']['order_by']."\n";
        }
        // $sql .= " limit ".$offset." , ".$limit;
        $main_recs = _select($sql);
        for ($idx=0; $idx < _count($main_recs); $idx++) {

        }
    }
    // _make_pagenavi2( $sm, $_request, $offset, $allcnt, $limit );

    _setAssign($blade,$this_sess);
    $blade->assign('main_recs', $main_recs);
    $blade->assign('count', $allcnt);

    $contents_title = "メールテンプレート管理";
    $active_menu = "mail_template_list";
    $contents_tpl = "mail_template_list";
