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
    $where .= "company_delete_date is null"."\n";

    // --------------------------------------------------- //
    // 検索条件
    // --------------------------------------------------- //


    if($this_sess['search_condition']['company_id'] !=""){
        $where .= " and company_id = '"._as( $this_sess['search_condition']['company_id'] )."'";
    }

    if($this_sess['search_condition']['company_name'] !=""){
        $where .= " and company_name like '%"._as( $this_sess['search_condition']['company_name'] )."%'";
    }

    // ******************************************************************************************************
    // CSVダウンロード処理
    // ******************************************************************************************************
    if($_request['exec']=="csv_download"){

      set_time_limit(180); //3分起動
      ini_set('memory_limit',"1024M"); //メモリ拡大

      $csv_head = '';
      // $csv_head .=  '"SANSANID"'; 2021.05.17 del
      $csv_head .= '"ID"';
      $csv_head .= ',"企業名"';
      $csv_head .= ',"企業表示名"';
      $csv_head .= ',"企業名カナ"';
      $csv_head .= ',"WEB登録区分"';
      $csv_head .= ',"DAISY"';
      $csv_head .= ',"WEB展示会"';
      $csv_head .= "\r\n";

      $w_flnm = "企業マスタ一覧_".date("YmdHis").".csv";
      header("Content-Disposition: attachment; filename=\"".mb_convert_encoding( $w_flnm, "SJIS-WIN" , _ENCODING_SRC )."\"");
      header("Content-Type: application/octet-stream; name=\"".$w_flnm."\"");

      echo mb_convert_encoding( $csv_head, "SJIS-WIN" , _ENCODING_SRC );

      // 表示SQL
      $sql  = "";
      $sql .= " select *"."\n";
      $sql .= " from m_company"."\n";
      $sql .= " where ".$where."\n";
      if( $this_sess['search_condition']['order_by'] != '' ){
        $sql .= " order by ".$this_sess['search_condition']['order_by']."\n";
      }
      $result = _query( $conn, $sql );

      $row = 0;
      while( $rec = _fetchArray( $result, $row ) ){

        $csv_buff = '';
        $csv_buff .= '"' .csvSafe($rec['company_id']).'"';
        $csv_buff .= ',"'.csvSafe($rec['company_name']).'"';
        $csv_buff .= ',"'.csvSafe($rec['company_display_name']).'"';
        $csv_buff .= ',"'.csvSafe($rec['company_name_kana']).'"';
        $csv_buff .= ',"'.csvSafe($_conf_big_cate1[ $rec['company_big_cate'] ]).'"';
        $csv_buff .= ',"'.csvSafe($rec['company_daisy']).'"';
        $csv_buff .= ',"'.csvSafe($rec['company_web_showcases']).'"';
        $csv_buff .= "\r\n";
        echo mb_convert_encoding( $csv_buff, "SJIS-WIN" , _ENCODING_SRC );
        $row++;
      }
      _freeResult( $result );

      exit();

    } else if ($_request['exec'] == "none_update") {
        // m_syoutai,m_userテーブルの未登録企業で登録されているレコードを、kigyou_nameで完全一致する企業マスタと紐付ける

        _query($conn,'begin');

        // m_syoutai
        $sql = "";
        $sql .= " update m_syoutai s";
        $sql .= " join m_company c on c.company_name = s.syoutai_kigyou_name";
        $sql .= " set s.syoutai_company_id = c.company_id";
        $sql .= " where s.syoutai_delete_date is null";
        $sql .= " and c.company_delete_date is null";
        $sql .= " and (syoutai_company_id = 1 or syoutai_company_id is null)";
        _query($conn, $sql);

        // m_user
        $sql = "";
        $sql .= " update m_user u";
        $sql .= " join m_company c on c.company_name = u.user_kigyou_name";
        $sql .= " set u.user_company_id = c.company_id";
        $sql .= " where u.user_delete_date is null";
        $sql .= " and c.company_delete_date is null";
        $sql .= " and (u.user_company_id = 1 or u.user_company_id is null)";
        _query($conn, $sql);

        _query( $conn, "commit" );
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
    $sql .= " select count(company_id) as all_cnt "."\n";
    $sql .= " from m_company"."\n";
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
        $sql .= " from m_company"."\n";
        // where句
        $sql .= " where ".$where."\n";
        // order by句
        if( $this_sess['search_condition']['order_by'] != '' ){
            $sql .= " order by ".$this_sess['search_condition']['order_by']."\n";
        }
        $sql .= " limit ".$offset." , ".$limit;
        $main_recs = _select($sql);
    }

    _make_pagenavi2( $blade, $_request, $offset, $allcnt, $limit );

    _setAssign($blade,$this_sess);
    $blade->assign('big_cate', $_conf_big_cate);
    $blade->assign('main_recs', $main_recs);
    $blade->assign('set_pattern', $_request['set_pattern']);

    $contents_title = "企業管理";
    $active_menu = "company_list";
    $contents_tpl = "company_list";
