<?php
    if( !defined("_PROJECT_DISP_NAME") ){
        die("System Error");
    }

    if( $_SESSION[_PROJECT_NAME]['admin_login']['admin_id'] == "" ){
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

    // ******************************************************************************************************
    // 検索
    // ******************************************************************************************************
    if( $_request['exec'] == "search" || $_request['exec'] == "csv_download" || $_request['exec'] == "csv_edit_download"){
        unset( $this_sess['search_condition'] );
        $this_sess['search_condition'] = array();
        $this_sess['search_condition'] = _array_merge( $this_sess['search_condition'], $_request );
    }elseif( $_request['offset'] != "" ){
        $this_sess['search_condition']['offset'] = $_request['offset'];
    }elseif( $_request['sess_no_init'] == "" ){
        unset( $this_sess['search_condition'] );
        $this_sess['search_condition']['order_by'] = "syoutai_kigyou_name_kana asc,syoutai_name_kana asc";
    }

    if($this_sess['search_condition']['order_by']==''){
        $this_sess['search_condition']['order_by'] = "syoutai_kigyou_name_kana asc,syoutai_name_kana asc";
    }

    // ******************************************************************************************************
    // 並び順配列作成
    // ******************************************************************************************************
    $order_by_arr = array();
    $order_by_arr['syoutai_company_name_kana asc,syoutai_name_kana asc']                      = "企業名順";
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


    // ******************************************************************************************************
    // 共通WHERE
    // ******************************************************************************************************
    $join = "";

    $where = "";
    $where .= "syoutai_delete_date is null";

    if($this_sess['search_condition']['syoutai_vip_flg'] !=""){
        $where .= " and syoutai_vip_flg = "._as($this_sess['search_condition']['syoutai_vip_flg'])."";
    }

    if(!is_null($this_sess['search_condition']['syoutai_big_cate'])){
        $tmpCondition = [];
        foreach ($this_sess['search_condition']['syoutai_big_cate'] as $key => $value) {
            if ($value == '') continue;
            $tmpCondition[] = 'syoutai_big_cate = ' ._as($value);
        }

        if (count($tmpCondition) > 0) {
            $where .= ' and (';
            $where .= implode(' or ', $tmpCondition);
            $where .= ') ';
        }
    }

    if(!is_null($this_sess['search_condition']['syoutai_mid_cate'])){
        $tmpCondition = [];
        foreach ($this_sess['search_condition']['syoutai_mid_cate'] as $key => $value) {
            if ($value == '') continue;
            $tmpCondition[] = 'syoutai_mid_cate = ' ._as($value);
        }

        if (count($tmpCondition) > 0) {
            $where .= ' and (';
            $where .= implode(' or ', $tmpCondition);
            $where .= ') ';
        }
    }

    if($this_sess['search_condition']['syoutai_company_id'] !=""){
      $where .= " and syoutai_company_id = '"._as($this_sess['search_condition']['syoutai_company_id'])."'";
    }

    if($this_sess['search_condition']['syoutai_kigyou_name'] !=""){
        $where .= " and syoutai_kigyou_name like '%"._as($this_sess['search_condition']['syoutai_kigyou_name'])."%'";
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
        $where .= " and ( syoutai_login_id like '%"._as($this_sess['search_condition']['syoutai_mail'])."%' or syoutai_login_id like '%"._as($this_sess['search_condition']['syoutai_mail'])."%')";
    }

    if($this_sess['search_condition']['syoutai_yakusyoku'] != ''){
        $s = $this_sess['search_condition']['syoutai_yakusyoku'];
        $s = str_replace('　', ' ', $s);
        $array = explode(' ', $s);

        $tmpCondition = [];
        foreach ($array as $key => $value) {
            if ($value == '') continue;
            $tmpCondition[] = "syoutai_yakusyoku like '%" ._as($value) . "%'";
        }

        if (count($tmpCondition) > 0) {
            $where .= ' and (';
            $where .= implode(' or ', $tmpCondition);
            $where .= ') ';
        }
    }

    if($this_sess['search_condition']['syoutai_tag'] !=""){
        $where .= " and syoutai_tag like '%"._as($this_sess['search_condition']['syoutai_tag'])."%'";
    }

    if ($_SESSION[_PROJECT_NAME]['admin_login']['admin_kyouryoku_kigyou_flg'] == 1) {
        $where .= " and syoutai_company_id in (" . $kk_ids . ")";
    }

    // ---------------------------------------------------------------------------------------- //

    // 選択のイベント、担当者に該当する来場者情報
    if( $this_sess['search_condition']['join_event_id'] != "" ){
        $join .= " inner join";
        $join .= " ( select user_syoutai_id ";
        $join .= "   from v_user ";

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

            $join .= " inner join "."\n";
            $join .= " (select admin_id from v_admin where admin_delete_date is null and admin_syozoku_id in (".$syozoku_ids.") )"."\n";
            $join .= " as sub_admin on (sub_admin.admin_id = v_user.user_admin_id) "."\n";
        }

        $join .= "   where user_delete_date is null and user_big_cate in (1,2,3,4) and";
        $join .= " user_event_id = '"._as($this_sess['search_condition']['join_event_id'])."'";
        if( $this_sess['search_condition']['join_admin_id'] != ""){
            $join .= " and";
            $join .= " user_admin_id = '"._as($this_sess['search_condition']['join_admin_id'])."'";
        }
        $join .= " group by user_syoutai_id";
        $join .= " ) as juser on (juser.user_syoutai_id = v_syoutai.syoutai_id)  "."\n";

    }

    // 選択のイベントに該当しない来場者情報
    if ( $this_sess['search_condition']['not_join_event_id'] != "" ) {
        $join .= " left join";
        $join .= " ( select user_syoutai_id from v_user where user_delete_date is null and user_big_cate in (1,2,3,4) and";
        $join .= " user_event_id = '"._as($this_sess['search_condition']['not_join_event_id'])."'";
        $join .= " ) as nuser on (nuser.user_syoutai_id = v_syoutai.syoutai_id)  "."\n";

        $where .= " and nuser.user_syoutai_id is null";
    }

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


    // ---------------------------------------------------------------------------------------- //


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
        $sql .= " limit ".$offset." , ".$limit;

        $main_recs = _select($sql);
        for ($i=0; $i < _count($main_recs); $i++) {
            $main_recs[$i]['disp_big_cate'] = $_conf_big_cate_detail[$main_recs[$i]['syoutai_big_cate']];
        }
    }

    _make_pagenavi2( $blade, $_request, $offset, $allcnt, $limit );


    // ******************************************************************************************************
    // CSVダウンロード処理
    // ******************************************************************************************************
    if($_request['exec']=="csv_download"){

        set_time_limit(180); //3分起動
        ini_set('memory_limit',"1024M"); //メモリ拡大

        $csv_head = '';
        // $csv_head .=  '"SANSANID"'; 2021.05.17 del
        $csv_head .= '"来場者ID"';
        $csv_head .= ',"招待者氏名"';
        $csv_head .= ',"招待者氏名カナ"';
        $csv_head .= ',"VIP"';
        $csv_head .= ',"大分類"';
        $csv_head .= ',"中分類"';
        $csv_head .= ',"企業名（企業マスタ）"';
        $csv_head .= ',"企業名カナ（企業マスタ）"';
        $csv_head .= ',"企業名（来場者マスタ）"';
        $csv_head .= ',"企業名カナ（来場者マスタ）"';
        $csv_head .= ',"部署"';
        $csv_head .= ',"役職"';
        $csv_head .= ',"送信先メールアドレス"';
        $csv_head .= ',"ログインID"';
        $csv_head .= ',"AC担当部署（部署・支店）"';
        $csv_head .= ',"備考"';
        $csv_head .= "\r\n";

        $w_flnm = "来場者マスタ一覧_".date("YmdHis").".csv";
        header("Content-Disposition: attachment; filename=\"".mb_convert_encoding( $w_flnm, "SJIS-WIN" , _ENCODING_SRC )."\"");
        header("Content-Type: application/octet-stream; name=\"".$w_flnm."\"");

        echo mb_convert_encoding( $csv_head, "SJIS-WIN" , _ENCODING_SRC );

        // 表示SQL
        $sql  = "";
        $sql .= " select * from v_syoutai ";
        $sql .= $join;
        $sql .= " where ".$where;
        $sql .= " order by ".$this_sess['search_condition']['order_by'];
        $result = _query( $conn, $sql );

        $sql = "";
        $sql .= " select *"."\n";
        $sql .= " from m_event"."\n";
        $sql .= " where event_delete_date is null"."\n";
        $sql .= " and event_area_shikibetsu_id = 'W'"."\n";
        // $sql .= " and event_raikainri_ymd_ed <= CURRENT_DATE()"."\n";
        $sql .= " and event_raikainri_ymd_ed <= '".date("Y/m/d",strtotime("-1 month"))."'"."\n";
        $sql .= " order by event_raikainri_ymd_ed desc"."\n";
        $bef_west_recs = _select($sql);

        $sql = "";
        $sql .= " select *"."\n";
        $sql .= " from m_event"."\n";
        $sql .= " where event_delete_date is null"."\n";
        $sql .= " and event_area_shikibetsu_id = 'E'"."\n";
        // $sql .= " and event_raikainri_ymd_ed <= CURRENT_DATE()"."\n";
        $sql .= " and event_raikainri_ymd_ed <= '".date("Y/m/d",strtotime("-1 month"))."'"."\n";
        $sql .= " order by event_raikainri_ymd_ed desc"."\n";
        $bef_east_recs = _select($sql);

        $row = 0;
        while( $rec = _fetchArray( $result, $row ) ){

            $bef_west_busho = '';
            $bef_east_busho = '';

            if (_count($bef_west_recs) > 0){
                $sql = "";
                $sql .= " select user_id"."\n";
                $sql .= "   , user_admin_id"."\n";
                $sql .= "   , m_syozoku.syozoku_name"."\n";
                $sql .= "   , v_admin.admin_name"."\n";
                $sql .= " from v_user"."\n";
                $sql .= " inner join v_admin on (v_user.user_admin_id = v_admin.admin_id)"."\n";
                $sql .= " left join m_syozoku on (m_syozoku.syozoku_id = v_admin.admin_syozoku_id)"."\n";
                $sql .= " where user_event_id = '"._as($bef_west_recs[0]['event_id'])."'"."\n";
                $sql .= " and user_login_id = '"._as($rec['syoutai_login_id'])."'"."\n";
                $bef_west_user_rec = _select($sql);
                $bef_west_busho = $bef_west_user_rec[0]['syozoku_name'];
            }

            if (_count($bef_east_recs) > 0){
                $sql = "";
                $sql .= " select user_id"."\n";
                $sql .= "   , user_admin_id"."\n";
                $sql .= "   , m_syozoku.syozoku_name"."\n";
                $sql .= "   , v_admin.admin_name"."\n";
                $sql .= " from v_user"."\n";
                $sql .= " inner join v_admin on (v_user.user_admin_id = v_admin.admin_id)"."\n";
                $sql .= " left join m_syozoku on (m_syozoku.syozoku_id = v_admin.admin_syozoku_id)"."\n";
                $sql .= " where user_event_id = '"._as($bef_east_recs[0]['event_id'])."'"."\n";
                $sql .= " and user_login_id = '"._as($rec['syoutai_login_id'])."'"."\n";
                $bef_east_user_rec = _select($sql);
                $bef_east_busho = $bef_east_user_rec[0]['syozoku_name'];
            }

            $vip = "";
            if ( $rec['syoutai_vip_flg'] != 0 ) $vip = $_conf_vip[ $rec['syoutai_vip_flg'] ];

            $csv_buff = '';
            // $csv_buff .=  '"'.$rec['syoutai_sansan_id'].'"'; 2021.05.17 del
            $csv_buff .= '"' .csvSafe($rec['syoutai_id']).'"';
            $csv_buff .= ',"' .csvSafe($rec['syoutai_name']).'"';
            $csv_buff .= ',"'.csvSafe($rec['syoutai_name_kana']).'"';
            $csv_buff .= ',"'.csvSafe($vip).'"';
            $csv_buff .= ',"'.csvSafe($_conf_big_cate[ $rec['syoutai_big_cate'] ]).'"';
            $csv_buff .= ',"'.csvSafe($_conf_mid_cate[ $rec['syoutai_mid_cate'] ]).'"';
            $csv_buff .= ',"'.csvSafe($rec['syoutai_company_name']).'"';
            $csv_buff .= ',"'.csvSafe($rec['syoutai_company_name_kana']).'"';
            $csv_buff .= ',"'.csvSafe($rec['syoutai_kigyou_name']).'"';
            $csv_buff .= ',"'.csvSafe($rec['syoutai_kigyou_name_kana']).'"';
            $csv_buff .= ',"'.csvSafe($rec['syoutai_busyo']).'"';
            $csv_buff .= ',"'.csvSafe($rec['syoutai_yakusyoku']).'"';
            $csv_buff .= ',"'.csvSafe($rec['syoutai_mail']).'"';
            $csv_buff .= ',"'.csvSafe($rec['syoutai_login_id']).'"';
            $csv_buff .= ',"' . $bef_west_busho . '/' . $bef_east_busho . '"';
            $csv_buff .= ',"'.csvSafe($rec['syoutai_biko']).'"';
            $csv_buff .= "\r\n";
            echo mb_convert_encoding( $csv_buff, "SJIS-WIN" , _ENCODING_SRC );
            $row++;
        }
        _freeResult( $result );

        exit();

    } else if($_request['exec']=="csv_edit_download") {

      if ($allcnt > 100000)
      {
          $err_msg[] = '一括編集用フォームダウンロードは、3000件が上限です。';
          $blade->assign('err_msg', $err_msg);
      }
      else
      {
          set_time_limit(180); //3分起動
          ini_set('memory_limit',"1024M"); //メモリ拡大

          $csv_dir = _SYSTEM_ROOT_DIR."/upfile/new_tmp/CSV_".date("YmdHis");
          _mkdir($csv_dir);
          $w_flnm = 'raijyousya_editdata';
          $w_fullpath  = $csv_dir."/". $w_flnm;
          $fp = fopen($w_fullpath, 'a');

          $csv_head = '';
          $csv_head .=  '"来場者ID ※編集不可"';
          $csv_head .= ',"来場者氏名"';
          $csv_head .= ',"来場者氏名カナ"';
          $csv_head .= ',"VIP"';
          $csv_head .= ',"大分類"';
          $csv_head .= ',"中分類"';
          $csv_head .= ',"企業名（企業マスタ）"';
          $csv_head .= ',"部署"';
          $csv_head .= ',"役職"';
          $csv_head .= ',"来場者メールアドレス"';
          $csv_head .= ',"来場者ログインID"';
          $csv_head .= ',"AC担当部署（部署・支店）"';
          $csv_head .= ',"企業名（入力値）"';
          $csv_head .= ',"タグ文字列（来場者マスタ時）"';
          $csv_head .= ',"備考"';
          $csv_head .= "\r\n";
          fputs($fp, mb_convert_encoding( $csv_head, "SJIS-WIN" , _ENCODING_SRC ));

          $mid_cate = $_conf_mid_cate1 + $_conf_mid_cate2;

          // 表示SQL
          $sql  = "";
          $sql .= " select * from v_syoutai ";
          $sql .= $join;
          $sql .= " where ".$where;
          $sql .= " order by ".$this_sess['search_condition']['order_by'];
          $result = _query( $conn, $sql );

          $sql = "";
          $sql .= " select *"."\n";
          $sql .= " from m_event"."\n";
          $sql .= " where event_delete_date is null"."\n";
          $sql .= " and event_area_shikibetsu_id = 'W'"."\n";
          // $sql .= " and event_raikainri_ymd_ed <= CURRENT_DATE()"."\n";
          $sql .= " and event_raikainri_ymd_ed <= '".date("Y/m/d",strtotime("-1 month"))."'"."\n";
          $sql .= " order by event_raikainri_ymd_ed desc"."\n";
          $bef_west_recs = _select($sql);

          $sql = "";
          $sql .= " select *"."\n";
          $sql .= " from m_event"."\n";
          $sql .= " where event_delete_date is null"."\n";
          $sql .= " and event_area_shikibetsu_id = 'E'"."\n";
          // $sql .= " and event_raikainri_ymd_ed <= CURRENT_DATE()"."\n";
          $sql .= " and event_raikainri_ymd_ed <= '".date("Y/m/d",strtotime("-1 month"))."'"."\n";
          $sql .= " order by event_raikainri_ymd_ed desc"."\n";
          $bef_east_recs = _select($sql);

          $row = 0;
          while( $rec = _fetchArray( $result, $row ) ){

              $bef_west_busho = '';
              $bef_east_busho = '';

              if (_count($bef_west_recs) > 0){
                  $sql = "";
                  $sql .= " select user_id"."\n";
                  $sql .= "   , user_admin_id"."\n";
                  $sql .= "   , m_syozoku.syozoku_name"."\n";
                  $sql .= "   , v_admin.admin_name"."\n";
                  $sql .= " from v_user"."\n";
                  $sql .= " inner join v_admin on (v_user.user_admin_id = v_admin.admin_id)"."\n";
                  $sql .= " left join m_syozoku on (m_syozoku.syozoku_id = v_admin.admin_syozoku_id)"."\n";
                  $sql .= " where user_event_id = '"._as($bef_west_recs[0]['event_id'])."'"."\n";
                  $sql .= " and user_login_id = '"._as($rec['syoutai_login_id'])."'"."\n";
                  $bef_west_user_rec = _select($sql);
                  $bef_west_busho = $bef_west_user_rec[0]['syozoku_name'];
              }

              if (_count($bef_east_recs) > 0){
                  $sql = "";
                  $sql .= " select user_id"."\n";
                  $sql .= "   , user_admin_id"."\n";
                  $sql .= "   , m_syozoku.syozoku_name"."\n";
                  $sql .= "   , v_admin.admin_name"."\n";
                  $sql .= " from v_user"."\n";
                  $sql .= " inner join v_admin on (v_user.user_admin_id = v_admin.admin_id)"."\n";
                  $sql .= " left join m_syozoku on (m_syozoku.syozoku_id = v_admin.admin_syozoku_id)"."\n";
                  $sql .= " where user_event_id = '"._as($bef_east_recs[0]['event_id'])."'"."\n";
                  $sql .= " and user_login_id = '"._as($rec['syoutai_login_id'])."'"."\n";
                  $bef_east_user_rec = _select($sql);
                  $bef_east_busho = $bef_east_user_rec[0]['syozoku_name'];
              }

              $vip = "";
              if ( $rec['syoutai_vip_flg'] != 0 ) $vip = $_conf_vip[ $rec['syoutai_vip_flg'] ];

              $company_name = '';
              if ( ! is_null($rec['syoutai_company_id']) && ! empty($rec['syoutai_company_id']))
              {
                  $sql  = "";
                  $sql .= " select * from m_company ";
                  $sql .= " where company_id = " . $rec['syoutai_company_id'];
                  $sql .= " and company_delete_date IS NULL";
                  $company_result = _select( $sql );

                  if (count($company_result) > 0)
                  {
                      $company_name = $company_result[0]['company_name'];
                  }
              }

              $csv_buff = '';
              $csv_buff .=  '"'.$rec['syoutai_id'].'"';
              $csv_buff .= ',"' .csvSafe($rec['syoutai_name']).'"';
              $csv_buff .= ',"'.csvSafe($rec['syoutai_name_kana']).'"';
              $csv_buff .= ',"'.csvSafe($vip).'"';
              $csv_buff .= ',"'.csvSafe($_conf_big_cate[ $rec['syoutai_big_cate'] ]).'"';
              $csv_buff .= ',"'.csvSafe($mid_cate[ $rec['syoutai_mid_cate'] ]).'"';
              $csv_buff .= ',"'.csvSafe($company_name).'"';
              $csv_buff .= ',"'.csvSafe($rec['syoutai_busyo']).'"';
              $csv_buff .= ',"'.csvSafe($rec['syoutai_yakusyoku']).'"';
              $csv_buff .= ',"'.csvSafe($rec['syoutai_mail']).'"';
              $csv_buff .= ',"'.csvSafe($rec['syoutai_login_id']).'"';
              $csv_buff .= ',"' . $bef_west_busho . '/' . $bef_east_busho . '"';
              $csv_buff .= ',"'.csvSafe($rec['syoutai_kigyou_name']).'"';
              $csv_buff .= ',"'.csvSafe($rec['syoutai_tag']).'"';
              $csv_buff .= ',"'.csvSafe($rec['syoutai_biko']).'"';
              $csv_buff .= "\r\n";
              fputs($fp, mb_convert_encoding( $csv_buff, "SJIS-WIN" , _ENCODING_SRC ));
              $row++;
          }
          fclose($fp);
          _freeResult( $result );

          $excel_flnm = 'raijyousya_upload_form.xlsm';
          if ($_SESSION[_PROJECT_NAME]['admin_login']['admin_kyouryoku_kigyou_flg'] == 1) {
              $excel_flnm = 'raijyousya_upload_form_EC.xlsm';
          }
          $excel_path = _SYSTEM_ROOT_DIR."/admin/xlsm/" . $excel_flnm;
          $zipFileName = "raijyousya_upload_form.zip";

          $zip = new ZipArchive();

          $zipTmpDir = $csv_dir."/";
          $result = $zip->open($csv_dir."/".$zipFileName, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE);
          if( $result !== true ){ //エラー処理
              die('system error!');
          }

          $zip->addFile($w_fullpath, $w_flnm);
          $zip->addFile($excel_path, $excel_flnm);
          $zip->close();

          //ダウンロード
          header('Content-Type: application/zip; name="'.$zipFileName.'"');
          header('Content-Disposition: attachment; filename="'.$zipFileName.'"');
          header('Content-Length: '.filesize($csv_dir."/".$zipFileName));
          // ファイルを出力する前に、バッファの内容をクリア（ファイルの破損防止）
          ob_end_clean();
          echo file_get_contents($csv_dir."/".$zipFileName);

          @unlink($csv_dir."/".$zipFileName);
          _rmdir($csv_dir);

          exit();
      }

    }elseif($_request['exec'] == 'syoutai_form_download'){

        $w_flnm = "raijyousya_upload_form.xlsm";
        header("Content-Disposition: attachment; filename=\"".mb_convert_encoding( $w_flnm, "SJIS-WIN" , _ENCODING_SRC )."\"");
        header("Content-Type: application/octet-stream; name=\"".$w_flnm."\"");

        echo file_get_contents(_SYSTEM_ROOT_DIR."/admin/xlsm/raijyousya_upload_form.xlsm");
        exit();

    } else if ($_request['exec'] == 'syoutai_delete') {

        $array = array();
        $array['syoutai_last_upd_naiyou']  = "'"._as( '来場者一括削除' )."'"; //最終更新内容',
        $array['syoutai_delete_date']      = "'".$_now_timestamp."'";

        _query($conn,'begin');

        foreach ($_request['syoutai_delete_ids'] as $id) {
            $where = "syoutai_id='"._as($id)."'";
            _update( 'm_syoutai', $array, $where );

        }

        _query($conn,'commit');

        $this_sess['delete_msg'] = "削除しました。";
        header('Location: index.php?page=syoutai_list');
        exit();
    }

    _setAssign($blade,$this_sess);
    $blade->assign('main_recs', $main_recs);


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
    $sql .= " and syozoku_hidden_flg = 0 ";
    $sql .= " order by syozoku_id asc";
    $syozoku_recs = _select($sql);
    $_conf_syozoku = array();
    for ($i=0; $i < _count($syozoku_recs); $i++) {
        $_conf_syozoku[ $syozoku_recs[$i]['syozoku_id'] ] = $syozoku_recs[$i]['syozoku_name'];
    }
    $blade->assign('_conf_syozoku',$_conf_syozoku);

    // //担当者
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
    $blade->assign('_conf_mid_cate',$_conf_mid_cate);
    $blade->assign('syoutai_page', 1);
    if (isset($this_sess['delete_msg'])) {
        $blade->assign('delete_msg', $this_sess['delete_msg']);
        unset($this_sess['delete_msg']);
    }


    $contents_title = "来場者マスタ 一覧";

    $active_menu = "syoutai_list";
    $contents_tpl = "syoutai_list";