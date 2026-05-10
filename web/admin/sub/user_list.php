<?php
    if( !defined("_PROJECT_DISP_NAME") ){
        die("System Error");
    }

    if( $_SESSION[_PROJECT_NAME]['admin_login']['admin_id'] == "" ){
        die('System Error');
    }


    const AC_SETTING_NAME_ENDNOTE = '_ac'; //2021/11/08 Add


    // if( $_SESSION[_PROJECT_NAME]['admin_login']['admin_master_kengen'] != "1" ){
    //     die('System Error');
    // }

    $_as = function($s) {
        return _as($s);
    };

    // ******************************************************************************************************
    // 初期値
    // ******************************************************************************************************
    $page = $_request['page'];
    $this_sess = &$_SESSION[_PROJECT_NAME][$page];
    $err_msg = array();
    $kk_ids = ''; // 外部企業ID

    $table_kaijyou_inout = 't_kaijyou_inout';
    $table_user = 'm_user';
    $table_v_user = 'v_user';

    if ($select_event_rec['event_archived_flg'] == '1')
    {
        $table_kaijyou_inout = 'a_kaijyou_inout';
        $table_user = 'a_user';
        $table_v_user = 'v_auser';
    }

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
    //2021/11/08 Mod ----------- Before -------
    // if( $_request['exec'] == "search" || $_request['exec'] == "pass_change_csv" || $_request['exec'] == "user_list_csv" ){
    //2021/11/08 Mod ----------- After -------
    if( $_request['exec'] == "search" || $_request['exec'] == "pass_change_csv" || $_request['exec'] == "user_list_csv"  || $_request['exec'] == "user_qr_dl" ){
    //2021/11/08 Mod ----------- End -------
        unset( $this_sess['search_condition'] );
        $this_sess['search_condition'] = array();
        $this_sess['search_condition'] = _array_merge( $this_sess['search_condition'], $_request );
    }elseif( $_request['offset'] != "" ){
        $this_sess['search_condition']['offset'] = $_request['offset'];
    }elseif( $_request['sess_no_init'] == "" ){
        unset( $this_sess['search_condition'] );
        $this_sess['search_condition']['order_by'] = "user_kigyou_name_kana asc,user_name_kana asc";
    }

    if($this_sess['search_condition']['order_by']==''){
        $this_sess['search_condition']['order_by'] = "user_kigyou_name_kana asc,user_name_kana asc";
    }

    $raijyou_hi_st_err = false;
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

    $raijyou_hi_ed_err = false;
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

    // ******************************************************************************************************
    // 並び順配列作成
    // ******************************************************************************************************
    $order_by_arr = array();
    $order_by_arr['user_kigyou_name_kana asc,user_name_kana asc']                          = "企業名順";
    $order_by_arr['user_name_kana asc,user_kigyou_name_kana asc']                          = "氏名順";
    $order_by_arr['user_insert_date desc']                                                 = "登録日（降順）";
    $order_by_arr['user_insert_date asc']                                                  = "登録日（昇順）";
    $blade->assign('order_by_arr',$order_by_arr);

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

    $_conf_yotei_set_jyoutai = array();
    $_conf_yotei_set_jyoutai[''] = "全て";
    $_conf_yotei_set_jyoutai['mi'] = "未設定";
    $_conf_yotei_set_jyoutai['sumi'] = "設定済み";
    $blade->assign('_conf_yotei_set_jyoutai',$_conf_yotei_set_jyoutai);

    $_conf_jigo_ans = array();
    $_conf_jigo_ans[''] = "全て　　　　";
    $_conf_jigo_ans['mi'] = "未回答";
    $_conf_jigo_ans['sumi'] = "回答済み";
    $blade->assign('_conf_jigo_ans',$_conf_jigo_ans);

    // ******************************************************************************************************
    // 共通WHERE
    // ******************************************************************************************************
    $soyusai_cond_open = 0;

    $where = "";
    $where .= "user_delete_date is null";
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
            $join .= " as sub_admin on (sub_admin.admin_id = $table_v_user.user_admin_id) "."\n";
        }
        // 2021/05/14 mod ------- end ------

    } elseif ( $_SESSION[_PROJECT_NAME]['admin_login']['admin_user_kengen'] == 2 ){
        $where .= " and user_admin_id = '"._as( $_SESSION[_PROJECT_NAME]['admin_login']['admin_id'] )."'"."\n";
    }


    //集計閲覧権限が「0:全て閲覧可」は全部見れるが、「1:エリアのリアルタイム人数のみ」の場合はAC社員は見れない
    if ( $_SESSION[_PROJECT_NAME]['admin_login']['admin_syuukei_etsuran_kengen'] == 1 ){
        $where .= " and user_big_cate != 7";
    }


    if($this_sess['search_condition']['admin_id'] !=""){
        $soyusai_cond_open = 1;
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

    if($this_sess['search_condition']['user_vip_flg'] !=""){
        $where .= " and user_vip_flg = "._as($this_sess['search_condition']['user_vip_flg'])."";
    }

    if(!is_null($this_sess['search_condition']['user_big_cate'])){
        $tmpCondition = [];
        foreach ($this_sess['search_condition']['user_big_cate'] as $key => $value) {
            if ($value == '') continue;
            switch ($value) {
                case 'syoutai':
                    $tmpCondition[] = 'user_big_cate <= 4';
                    break;
                case 'raijyou':
                    $tmpCondition[] = 'user_big_cate > 4';
                    break;
                default:
                    $tmpCondition[] = 'user_big_cate = ' ._as($value);
                    break;
            }
        }

        if (count($tmpCondition) > 0) {
            $where .= ' and (';
            $where .= implode(' or ', $tmpCondition);
            $where .= ') ';
        }
    }

    if(!is_null($this_sess['search_condition']['user_mid_cate'])){
        $tmpCondition = [];
        foreach ($this_sess['search_condition']['user_mid_cate'] as $key => $value) {
            if ($value == '') continue;
            $tmpCondition[] = 'user_mid_cate = ' ._as($value);
        }

        if (count($tmpCondition) > 0) {
            $where .= ' and (';
            $where .= implode(' or ', $tmpCondition);
            $where .= ') ';
        }
    }

    if($this_sess['search_condition']['user_company_id'] !=""){
        $where .= " and user_company_id = '"._as($this_sess['search_condition']['user_company_id'])."'";
    }

    if($this_sess['search_condition']['user_kigyou_name'] !=""){
        $where .= " and user_kigyou_name like '%"._as($this_sess['search_condition']['user_kigyou_name'])."%'";
    }

    if($this_sess['search_condition']['user_kigyou_name_kana'] !=""){
        $where .= " and user_kigyou_name_kana like '%"._as($this_sess['search_condition']['user_kigyou_name_kana'])."%'";
    }

    if($this_sess['search_condition']['user_name'] !=""){
        $where .= " and user_name like '%"._as($this_sess['search_condition']['user_name'])."%'";
    }

    if($this_sess['search_condition']['user_name_kana'] !=""){
        $where .= " and user_name_kana like '%"._as($this_sess['search_condition']['user_name_kana'])."%'";
    }

    if($this_sess['search_condition']['user_mail'] !=""){
        // 2021/04/01 mod ------------------------ before ----------------------- 大幅変更
        // $where .= " and user_mail like '%"._as($this_sess['search_condition']['user_mail'])."%'";
        // 2021/04/01 mod ------------------------ after ------------------------ 大幅変更
        $where .= " and ( user_login_id like '%"._as($this_sess['search_condition']['user_mail'])."%' or user_mail like '%"._as($this_sess['search_condition']['user_mail'])."%' )";
        // 2021/04/01 mod ------------------------ end   ------------------------ 大幅変更
    }

    if(_count($this_sess['search_condition']['user_syoutai_yotei_time']) > 0){
        $where .= " and user_big_cate <= 4";
        $where .= " and (";
        $or = "";
        for ($i=0; $i < _count($this_sess['search_condition']['user_syoutai_yotei_time']); $i++) {
            if($or!="") $or .= " or ";
            $or .= " user_raijyou_yotei_time like '%"._as($this_sess['search_condition']['user_syoutai_yotei_time'][$i])."%'";
        }
        $where .= " ".$or;
        $where .= " )";
    }

    if(_count($this_sess['search_condition']['user_raijyou_yotei_time']) > 0){
        $where .= " and user_big_cate >= 5";
        $where .= " and (";
        $or = "";
        for ($i=0; $i < _count($this_sess['search_condition']['user_raijyou_yotei_time']); $i++) {
            if($or!="") $or .= " or ";
            $or .= " user_raijyou_yotei_time like '%"._as($this_sess['search_condition']['user_raijyou_yotei_time'][$i])."%'";
        }
        $where .= " ".$or;
        $where .= " )";
    }

    //予定日時設定状態
    if($this_sess['search_condition']['yotei_set_jyoutai'] =="mi"){
        $where .= " and (user_raijyou_yotei_time is null or (user_raijyou_yotei_time is not null and user_raijyou_yotei_time='') ) ";
    }elseif($this_sess['search_condition']['yotei_set_jyoutai'] =="sumi"){
        $where .= " and (user_raijyou_yotei_time is not null and user_raijyou_yotei_time!='') ";
    }

    if($this_sess['search_condition']['user_web'] !=""){
        $where .= " and user_web = "._as($this_sess['search_condition']['user_web'])."";
    }

    // 未来場者を対象とする
    if($this_sess['search_condition']['user_miraijyou'] !=""){
      $eventId = _as($_SESSION[_PROJECT_NAME]['select_event_id']);

      // 会場内のエリア入退出を管理しているテーブルをジョインする
      $join .= <<<EOL
  left join (
      select count(kinout_event_id) as kinout_event_count,
          kinout_user_id as kinout_user_id_count,
          kinout_event_id as kinout_event_id_count
      from $table_kaijyou_inout
      where kinout_delete_date is null
          and kinout_event_id = '$eventId'
          and kinout_fst_record = 1
      group by kinout_event_id, kinout_user_id
  ) t_kaijyou_inout_count on (
    t_kaijyou_inout_count.kinout_user_id_count = $table_v_user.user_id
    and t_kaijyou_inout_count.kinout_event_id_count = $table_v_user.user_event_id
  )
EOL;
      // 入退出情報が存在しない条件
      $where .= " and t_kaijyou_inout_count.kinout_event_count is null";
    }

    //通知メール状態
    if($this_sess['search_condition']['mail_status'] !=""){
        if( $this_sess['search_condition']['mail_status'] == 'mi'  ){
            $soyusai_cond_open = 1;
            $where .= " and user_mail_send_kbn = 0";
        }elseif( $this_sess['search_condition']['mail_status'] == 'sumi'  ){
            $soyusai_cond_open = 1;
            $where .= " and user_mail_send_kbn = 1";
        }elseif( $this_sess['search_condition']['mail_status'] == 'err'  ){
            $soyusai_cond_open = 1;
            $where .= " and user_mail_send_kbn = 2";
        }
    }

    //PASS設定状態
    if($this_sess['search_condition']['pass_set'] =="mi"){
        $soyusai_cond_open = 1;
        $where .= " and (user_pass is null or (user_pass is not null and user_pass = '"._as(md5('_NEED_PASS_SET_'))."' ) )";
    }elseif($this_sess['search_condition']['pass_set'] =="sumi"){
        $soyusai_cond_open = 1;
        $where .= " and (user_pass is not null and  user_pass != '"._as(md5('_NEED_PASS_SET_'))."' )";
    }

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

    if($this_sess['search_condition']['admin_syozoku_id'] !=""){
        $soyusai_cond_open = 1;
        $where .= " and admin_syozoku_id = '"._as($this_sess['search_condition']['admin_syozoku_id'])."'";
    }

    if($this_sess['search_condition']['admin_mail'] !=""){
        $soyusai_cond_open = 1;
        $where .= " and admin_mail = '"._as($this_sess['search_condition']['admin_mail'])."'";
    }

    if($this_sess['search_condition']['user_tag'] !=""){
        $soyusai_cond_open = 1;
        $where .= " and user_tag = '"._as($this_sess['search_condition']['user_tag'])."'";
    }

    if($this_sess['search_condition']['user_syounin_flg'] !=""){
        $soyusai_cond_open = 1;
        $where .= " and user_syounin_flg = "._as($this_sess['search_condition']['user_syounin_flg']);
    }

    if ($this_sess['search_condition']['user_ids'] != "") {
        $user_ids = str_replace(',', "','", $this_sess['search_condition']['user_ids']);
        $where .= " and user_id in ('" . $user_ids . "')";
    }

    //★各日の最小で検索するよう仕様変更 Mod ---------------- Before -----------------------
    // if($this_sess['search_condition']['raijyou_ymd'] !=""){
    //     $soyusai_cond_open = 1;
    //     $where .= " and substr(min_kinout_time_in,1,10) = '"._as($this_sess['search_condition']['raijyou_ymd'])."'";
    // }
    // if($this_sess['search_condition']['raijyou_hi_st'] !="" && $raijyou_hi_st_err==false){
    //     $soyusai_cond_open = 1;
    //     $where .= " and substr(min_kinout_time_in,12,5) >= '"._as($this_sess['search_condition']['raijyou_hi_st'])."'";
    // }
    // if($this_sess['search_condition']['raijyou_hi_ed'] !="" && $raijyou_hi_ed_err==false){
    //     $soyusai_cond_open = 1;
    //     $where .= " and substr(min_kinout_time_in,12,5) <= '"._as($this_sess['search_condition']['raijyou_hi_ed'])."'";
    // }
    //★各日の最小で検索するよう仕様変更 Mod ---------------- After -----------------------
    if( !is_null($this_sess['search_condition']['raijyou_ymd']) ||
       ($this_sess['search_condition']['raijyou_hi_st'] !="" && $raijyou_hi_st_err==false) ||
       ($this_sess['search_condition']['raijyou_hi_ed'] !="" && $raijyou_hi_ed_err==false) ){

        $soyusai_cond_open = 1;

        $join .= " join (";
        $join .= "    select kinout_event_id,kinout_user_id from $table_kaijyou_inout ";
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

        if($this_sess['search_condition']['raijyou_hi_st'] !="" && $raijyou_hi_st_err==false){
            $join .= "      and substr(kinout_time_in,12,5) >= '"._as($this_sess['search_condition']['raijyou_hi_st'])."' ";
        }
        if($this_sess['search_condition']['raijyou_hi_ed'] !="" && $raijyou_hi_ed_err==false){
            $join .= "      and substr(kinout_time_in,12,5) <= '"._as($this_sess['search_condition']['raijyou_hi_ed'])."' ";
        }
        $join .= "    group by kinout_user_id ";
        $join .= " ) V_kaijyou_inout on (V_kaijyou_inout.kinout_user_id=$table_v_user.user_id and V_kaijyou_inout.kinout_event_id=$table_v_user.user_event_id)";
    }
    //★各日の最小で検索するよう仕様変更 Mod ---------------- End -----------------------

    //事後アンケート回答有無
    if($this_sess['search_condition']['jigo_ans'] !=""){
        if($this_sess['search_condition']['jigo_ans'] == "sumi"){
            $soyusai_cond_open = 1;

            $join .= " inner join "."\n";
            $join .= " ( SELECT jigo_event_id,jigo_user_id FROM t_jigo_answer where jigo_event_id='"._as($_SESSION[_PROJECT_NAME]['select_event_id'])."' group by jigo_event_id,jigo_user_id )"."\n";
            $join .= " as v_jigo_ans on (v_jigo_ans.jigo_event_id = $table_v_user.user_event_id and v_jigo_ans.jigo_user_id = $table_v_user.user_id) "."\n";
        }elseif($this_sess['search_condition']['jigo_ans'] == "mi"){
            $soyusai_cond_open = 1;

            $join .= " left join "."\n";
            $join .= " ( SELECT jigo_event_id,jigo_user_id FROM t_jigo_answer where jigo_event_id='"._as($_SESSION[_PROJECT_NAME]['select_event_id'])."' group by jigo_event_id,jigo_user_id )"."\n";
            $join .= " as v_jigo_ans on (v_jigo_ans.jigo_event_id = $table_v_user.user_event_id and v_jigo_ans.jigo_user_id = $table_v_user.user_id) "."\n";

            $where .= " and v_jigo_ans.jigo_user_id is null";
        }
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

    if ($_SESSION[_PROJECT_NAME]['admin_login']['admin_kyouryoku_kigyou_flg'] == 1) {
        $where .= " and user_company_id in (" . $kk_ids . ")";
    }

    // ******************************************************************************************************

    if($_request['exec']=="pass_change_csv" && $this_sess['search_condition']['user_tag']==""){
        $err_msg[] = "パスワード設定URL案内CSVダウンロードする場合は「タグ文字列」を指定してください。";
        $_request['exec'] = "search";
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
    $sql .= " select count($table_v_user.user_id) as all_cnt from $table_v_user ";
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
    $sql .= " left join v_admin on (v_admin.admin_id = $table_v_user.user_admin_id)";
    if ( $join != '' ) $sql .= $join;
    $sql .= " where ".$where;
    $rec = _select($sql);

    $allcnt = 0;
    if($rec[0]['all_cnt'] > 0){
        $allcnt = $rec[0]['all_cnt'];
    }

    if($allcnt > 0){
        // 表示SQL
        $sql  = "";
        $sql .= " select * from $table_v_user ";
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
        $sql .= " left join v_admin on (v_admin.admin_id = $table_v_user.user_admin_id)";
        $sql .= " left join m_syozoku on (v_admin.admin_syozoku_id = m_syozoku.syozoku_id)";            //2021/07/08 Add
        $sql .= " left join m_tantou_area on (v_admin.admin_tanarea_id = m_tantou_area.tanarea_id)";    //2021/07/08 Add

        $sql .= " left join m_syoutai on ($table_v_user.user_syoutai_id = m_syoutai.syoutai_id)";          //2021/11/08 Add
        $sql .= " left join m_company on (m_syoutai.syoutai_company_id = m_company.company_id)";    //2021/11/08 Add
        $sql .= " left join m_syozoku_group on (m_syozoku_group.szkgrp_id = m_syozoku.syozoku_szkgrp_id)";


        if ( $join != '' ) $sql .= $join;
        $sql .= " where ".$where;
        $sql .= " order by ".$this_sess['search_condition']['order_by'];

        if($_request['exec']=="pass_change_csv"){

            set_time_limit(180); //3分起動
            ini_set('memory_limit',"1024M"); //メモリ拡大

            if($_request['send_flg_on']=="1"){
                _query($conn,'begin');
            }

            $csv_head = '';
            $csv_head .= '"イベント名"';
            $csv_head .= ',"氏名"';
            $csv_head .= ',"氏名カナ"';
            $csv_head .= ',"企業名"';
            $csv_head .= ',"企業名カナ"';
            $csv_head .= ',"部署"';
            $csv_head .= ',"役職"';
            $csv_head .= ',"送信先メールアドレス"';
            $csv_head .= ',"ログインID（メールアドレス）"';
            $csv_head .= ',"初期パスワード設定URL"';
            $csv_head .= ',"AC担当者メールアドレス"';
            $csv_head .= "\r\n";

            $w_flnm = "パスワード設定URL案内_".date("YmdHis").".csv";
            header("Content-Disposition: attachment; filename=\"".mb_convert_encoding( $w_flnm, "SJIS-WIN" , _ENCODING_SRC )."\"");
            header("Content-Type: application/octet-stream; name=\"".$w_flnm."\"");

            echo mb_convert_encoding( $csv_head, "SJIS-WIN" , _ENCODING_SRC );

            $result = _query( $conn, $sql );

            $row = 0;
            while( $rec = _fetchArray( $result, $row ) ){
                // 2021/04/01 mod ------------------------ before ----------------------- 大幅変更
                // $real_user_mail_addr = _getMailAddressFromID( $rec['user_mail'] );
                // $pass_change_url = _SYSTEM_ROOT_URLS."/mypage/".$select_event_rec['event_url_key']."/?page=login&setpw="._urlCodeEncode($rec['user_mail']."#"."_NEED_PASS_SET_");
                // 2021/04/01 mod ------------------------ after ------------------------ 大幅変更
                $pass_change_url = _SYSTEM_ROOT_URLS."/mypage/".$select_event_rec['event_url_key']."/?page=login&setpw="._urlCodeEncode($rec['user_login_id']."#"."_NEED_PASS_SET_");
                // 2021/04/01 mod ------------------------ end   ------------------------ 大幅変更

                $csv_buff = '';
                $csv_buff .= '"' .csvSafe($select_event_rec['event_name']).'"';
                $csv_buff .= ',"'.csvSafe($rec['user_name']).'"';
                $csv_buff .= ',"'.csvSafe($rec['user_name_kana']).'"';
                $csv_buff .= ',"'.csvSafe($rec['user_kigyou_name']).'"';
                $csv_buff .= ',"'.csvSafe($rec['user_kigyou_name_kana']).'"';
                $csv_buff .= ',"'.csvSafe($rec['user_busyo']).'"';
                $csv_buff .= ',"'.csvSafe($rec['user_yakusyoku']).'"';
                $csv_buff .= ',"'.csvSafe($rec['user_mail']).'"';
                // 2021/04/01 mod ------------------------ before ----------------------- 大幅変更
                // $csv_buff .= ',"'.csvSafe($real_user_mail_addr).'"';
                // 2021/04/01 mod ------------------------ after ------------------------ 大幅変更
                $csv_buff .= ',"'.csvSafe($rec['user_login_id']).'"';
                // 2021/04/01 mod ------------------------ end   ------------------------ 大幅変更
                $csv_buff .= ',"'.csvSafe($pass_change_url).'"';
                $csv_buff .= ',"'.csvSafe($rec['admin_mail']).'"';
                $csv_buff .= "\r\n";

                echo mb_convert_encoding( $csv_buff, "SJIS-WIN" , _ENCODING_SRC );

                //送信FLG＝１に
                if($_request['send_flg_on']=="1"){
                    $array = array();
                    $array['user_mail_send_kbn'] = "1";
                    $array['user_update_date']         = "'".$_now_timestamp."'"; //更新日時',
                    $where = "user_id = '"._as($rec['user_id'])."'";
                    _update("$table_user",$array,$where);
                }

                $row++;
            }

            _freeResult( $result );

            if($_request['send_flg_on']=="1"){
                _query($conn,'commit');
            }

            exit();
        } elseif( $_request['exec']=="user_list_csv" ){
            set_time_limit(180);             //3分起動
            ini_set('memory_limit',"1024M"); //メモリ拡大

            $user_big_cate = $this_sess['search_condition']['user_big_cate']; // 名前を短く
            if ( $user_big_cate == 'syoutai' || ( $user_big_cate <= 4 && $user_big_cate != 'raijyou')){
                $mode   = "syoutai";
                $w_time = explode("#", $select_event_rec['event_syoutai_yotei_time']);
            } elseif ( $user_big_cate == 'raijyou' || ( $user_big_cate > 4 && $user_big_cate != 'syoutai' ) ){
                $mode   = "raijyou";
                $w_time = explode("#", $select_event_rec['event_raijyou_yotei_time']);
            }

            $csv_head = '';
            $csv_head .= '"AC担当者メールアドレス"';
            $csv_head .= ',"AC担当者担当エリア"';  //2021/07/08 Add
            $csv_head .= ',"AC担当者所属"';       //2021/07/08 Add
            $csv_head .= ',"AC担当者氏名"';       //2021/07/08 Add
            $csv_head .= ',"実際の来場日時"';       //2021/07/12 Add
            $csv_head .= ',"実際の退場日時"';       //2021/07/12 Add
            $csv_head .= ',"氏名"';
            $csv_head .= ',"氏名カナ"';
            $csv_head .= ',"VIP"';
            $csv_head .= ',"大分類"';
            $csv_head .= ',"中分類"';
            $csv_head .= ',"企業名"';
            $csv_head .= ',"企業名カナ"';
            $csv_head .= ',"企業名（入力値）"';
            $csv_head .= ',"企業名カナ（入力値）"';
            $csv_head .= ',"部署"';
            $csv_head .= ',"役職"';
            $csv_head .= ',"送信先メールアドレス"';
            $csv_head .= ',"ログインID（メールアドレス）"';
            $csv_head .= ',"備考"';
            //来場予定日時(yyyy/mm/dd 時間帯など 形式の#区切り)
            for ($num=0; $num < _count($w_time) ; $num++) {
                $csv_head .= ',"'.$w_time[$num].'"';
            }
            $csv_head .= ',"WEB招待（1:WEB招待者）"';

            $csv_head .= ',"ﾊﾟｽﾜｰﾄﾞ設定状態"';
            $csv_head .= ',"マイページ設定URL通知メール送信状態"';

            $csv_head .= "\r\n";

            $w_flnm = "来場者一覧_".date("YmdHis").".csv";
            header("Content-Disposition: attachment; filename=\"".mb_convert_encoding( $w_flnm, "SJIS-WIN" , _ENCODING_SRC )."\"");
            header("Content-Type: application/octet-stream; name=\"".$w_flnm."\"");

            echo mb_convert_encoding( $csv_head, "SJIS-WIN" , _ENCODING_SRC );
            $result = _query( $conn, $sql );

            $row = 0;
            while( $rec = _fetchArray( $result, $row ) ){
                $pass_change_url = _SYSTEM_ROOT_URLS."/mypage/".$select_event_rec['event_url_key']."/?page=login&setpw="._urlCodeEncode($rec['user_login_id']."#"."_NEED_PASS_SET_");

                $csv_buff = '';
                $csv_buff .= '"'.csvSafe($rec['admin_mail']).'"';
                $csv_buff .= ',"'.csvSafe($rec['tanarea_name']).'"'; //2021/07/08 Add
                $csv_buff .= ',"'.csvSafe($rec['syozoku_name']).'"'; //2021/07/08 Add
                $csv_buff .= ',"'.csvSafe($rec['admin_name']).'"';   //2021/07/08 Add

                $kigyou_name = empty($rec['user_company_id']) ? $rec['user_kigyou_name'] : $rec['user_company_name'];
                $kigyou_name_kana = empty($rec['user_company_id']) ? $rec['user_kigyou_name_kana'] : $rec['user_company_name_kana'];

                //★各日の最小で検索するよう仕様変更 Mod ---------------- Before -----------------------
                // $csv_buff .= ',"'.csvSafe(substr($rec['min_kinout_time_in'],0,16)).'"';   //2021/07/12 Add
                //★各日の最小で検索するよう仕様変更 Mod ---------------- After -----------------------
                $sql = "";
                $sql .= " select ";
                $sql .= "   substr(kinout_time_in,1,10)";
                $sql .= "  ,min(kinout_time_in) as min_kinout_time_in ";
                $sql .= " from $table_kaijyou_inout";
                $sql .= " where kinout_delete_date is null";
                $sql .= "   and kinout_event_id = '"._as($_SESSION[_PROJECT_NAME]['select_event_id'])."'";
                $sql .= "   and kinout_fst_record = 1";
                $sql .= "   and kinout_user_id = '"._as($rec['user_id'])."'";
                $sql .= " group by substr(kinout_time_in,1,10)";
                $sql .= " order by min_kinout_time_in asc";
                $kin_recs = _select($sql);
                $disp_min_kinout_time_in = "";
                for ($j=0; $j < _count($kin_recs); $j++) {
                    if($disp_min_kinout_time_in!="") $disp_min_kinout_time_in .= "\n";
                    $disp_min_kinout_time_in .= date("Y/m/d H:i", strtotime($kin_recs[$j]['min_kinout_time_in']) );
                }
                $csv_buff .= ',"'.csvSafe($disp_min_kinout_time_in).'"';
                //★各日の最小で検索するよう仕様変更 Mod ---------------- End -----------------------


                $sql =<<<EOL
select
substr(kinout_time_out,1,10) as day_kinout_time_out,
min(kinout_time_out) as min_kinout_time_out
from $table_kaijyou_inout
where kinout_delete_date is null
and kinout_event_id = '{$_as($_SESSION[_PROJECT_NAME]['select_event_id'])}'
and kinout_fst_record = 1
and kinout_user_id = '{$_as($rec['user_id'])}'
group by substr(kinout_time_out,1,10)
order by min_kinout_time_out asc
EOL;
                $kout_recs = _select($sql);
                $disp_min_kinout_time_out = '';
                foreach ($kout_recs as $key => $value) {
                    if ($disp_min_kinout_time_out != '') $disp_min_kinout_time_out .= "\n";
                    if ($value['min_kinout_time_out'] == '') continue;
                    $disp_min_kinout_time_out .= date("Y/m/d H:i", strtotime($value['min_kinout_time_out']));
                }
                $csv_buff .= ',"'.csvSafe($disp_min_kinout_time_out).'"';

                $csv_buff .= ',"'.csvSafe($rec['user_name']).'"';
                $csv_buff .= ',"'.csvSafe($rec['user_name_kana']).'"';
                $csv_buff .= ',"'.csvSafe($_conf_vip[ $rec['user_vip_flg'] ]).'"';
                $csv_buff .= ',"'.csvSafe($_conf_big_cate[ $rec['user_big_cate'] ]).'"';
                if ( $mode == 'syoutai' ){
                    $csv_buff .= ',"'.csvSafe($_conf_mid_cate1[ $rec['user_mid_cate'] ]).'"';
                } else {
                    $csv_buff .= ',"'.csvSafe($_conf_mid_cate2[ $rec['user_mid_cate'] ]).'"';
                }
                $csv_buff .= ',"'.csvSafe($kigyou_name).'"';
                $csv_buff .= ',"'.csvSafe($kigyou_name_kana).'"';
                $csv_buff .= ',"'.csvSafe($rec['user_kigyou_name']).'"';
                $csv_buff .= ',"'.csvSafe($rec['user_kigyou_name_kana']).'"';
                $csv_buff .= ',"'.csvSafe($rec['user_busyo']).'"';
                $csv_buff .= ',"'.csvSafe($rec['user_yakusyoku']).'"';
                $csv_buff .= ',"'.csvSafe($rec['user_mail']).'"';
                $csv_buff .= ',"'.csvSafe($rec['user_login_id']).'"';
                $csv_buff .= ',"'.csvSafe($rec['user_biko']).'"';
                for ($num=0; $num < _count($w_time) ; $num++) {
                    if ( strpos( $rec['user_raijyou_yotei_time'], $w_time[$num] ) !== false ){
                        $csv_buff .= ',"1"';
                    } else {
                        $csv_buff .= ',""';
                    }
                }
                $csv_buff .= ',"'.csvSafe($rec['user_web']).'"';

                if($rec['user_pass']==md5("_NEED_PASS_SET_") ){
                    $csv_buff .= ',"未設定"';
                }else{
                    $csv_buff .= ',"済"';
                }

                if( $rec['user_mail_send_kbn']=="1"){
                    $csv_buff .= ',"済"';
                }elseif( $rec['user_mail_send_kbn']=="2"){
                    $csv_buff .= ',"NG"';
                }else{
                    $csv_buff .= ',"未"';
                }

                $csv_buff .= "\r\n";

                echo mb_convert_encoding( $csv_buff, "SJIS-WIN" , _ENCODING_SRC );
                $row++;
            }

            _freeResult( $result );
            exit();

        //2021/11/08 Add -------------- Start ----------------
        } elseif( $_request['exec']=="user_qr_dl" ){
            set_time_limit(300);             //5分起動
            ini_set('memory_limit',"1024M"); //メモリ拡大

            require_once("../lib/pdf_lib/pdf_lib.php");


            $qr_dir = _SYSTEM_ROOT_DIR."/upfile/new_tmp/QR_".date("YmdHis");

            $result = _query( $conn, $sql );

            $row = 0;
            $pdfPaths = array();
            $pdfFiles = array();
            while( $rec = _fetchArray( $result, $row ) ){


                if($row==0){
                    _mkdir($qr_dir);
                }

                $qr_code = $select_event_rec['event_area_shikibetsu_id'].substr($select_event_rec['event_id'],1)."-".intval($rec['user_big_cate'])."9-".substr($rec['user_id'],1);

                $big_cate_name = $_conf_big_cate[ $rec['user_big_cate'] ];

                // ************************
                // QR作成
                // ************************
                // $qr_filenm = rand().".png";

                // require_once('../lib/qrcode//vendor/autoload.php');
                // use Endroid\QrCode\QrCode;
                // // QRコードに埋め込む文字列の指定
                // $qrCode = new QrCode($qr_code);
                // // QRコードのサイス（単位：ピクセル）
                // $qrCode->setSize(190);
                // // QRコードの周囲の余白（単位：ピクセル）
                // $qrCode->setMargin(8);
                // //ファイルに保存
                // $qrCode->writeFile('upfile/new_tmp/'.$qr_filenm);

                // ************************
                // PDF作成
                // ************************

                //$settingName    = "raijyousya_card"; //帳票名称（セッティング名）
                $settingName = $select_event_rec['event_url_key'];

                $tplDir = _SYSTEM_ROOT_DIR . "/pdf_tpl";      //PDFテンプレートやセッテイングのベースディレクトリ

                if($rec['user_big_cate'] == 7 && file_exists($tplDir . '/' . $settingName . AC_SETTING_NAME_ENDNOTE)){
                  $settingName = $settingName . AC_SETTING_NAME_ENDNOTE;
                }

                $pdf = new Pdf($settingName, $tplDir);

                $info = array();
                // $info['waku']   = "1";
                // $info['meishi_waku']   = "1";
                // $info['meishi']   = "お名刺";
                // $info['event_name']   = $select_event_rec['event_name'];
                $info['qr_code']   = $qr_code;
                $info['qr_code_str']   = $qr_code;
                $info['user_big_cate'] = $big_cate_name;

                // 東西で大分類の表示を分ける
                if ($select_event_rec['event_area_shikibetsu_id'] == 'E' || $select_event_rec['event_area_shikibetsu_id'] == 'T' || $select_event_rec['event_area_shikibetsu_id'] == 'C') {
                    // 小売、外食の場合★を表示する
                    if ($rec['user_big_cate'] == 1 || $rec['user_big_cate'] == 2) {
                        $info['user_big_cate'] = '★';
                    } else if (($select_event_recs[0]['event_area_shikibetsu_id'] == 'E' || $select_event_recs[0]['event_area_shikibetsu_id'] == 'C') && $rec['user_big_cate'] == 5) {
                        // 東日本、中部で大分類が出展者の場合「出展社」と表示すｌる
                        $info['user_big_cate'] = '出展社';
                    } else {
                        $info['user_big_cate'] = '';
                    }
                } else if ($select_event_rec['event_area_shikibetsu_id'] == 'W' || $select_event_rec['event_area_shikibetsu_id'] == 'S' || $select_event_rec['event_area_shikibetsu_id'] == 'K') {
                    // 大分類が「小売, 外食, メーカー(招待), その他(招待), その他(来場)」以外の場合非表示
                    $allow_big_cate = [1,2,3,4,8];
                    if ( ! in_array($rec['user_big_cate'], $allow_big_cate)) {
                        $info['user_big_cate'] = '';
                    }
                }

                $user_name = $rec['user_name'];
                if ($rec['user_big_cate'] != 7) {
                  $user_name = $user_name . '様';
                }

                //$info['user_info']   = $rec['user_kigyou_name']."\n".$rec['user_busyo']."\n".$rec['user_yakusyoku']."\n".$rec['user_name']." 様";
                $info['user_info']   = $rec['user_kigyou_name']."\n　\n".$user_name;
                $info['syozoku_name'] = (empty($rec['szkgrp_name']) ? $rec['syozoku_name'] : $rec['szkgrp_name']);
                // $info['big_cate_name_bg']   = "1";
                // $info['big_cate_name']   = $big_cate_name;


                $info['user_name'] = $user_name;
                $info['user_yakusyoku'] = $rec['user_yakusyoku'];
                $kigyou_name = empty($rec['company_display_name']) ? $rec['user_kigyou_name'] : $rec['company_display_name'];
                $info['kigyou_name'] = $kigyou_name;

                $pdf->WriteInfo('TEMPLATE_1',$info);



                $w_flnm = $kigyou_name . '_' . $user_name . '.pdf';
                $w_fullpath  = $qr_dir."/". $w_flnm;
                $pdfPaths[] = $w_fullpath;
                $pdfFiles[] = $w_flnm;
                $pdf->Output($w_fullpath, 'F');    //ファイル出力

                $pdf = null;

                $row++;
            }

            _freeResult( $result );

            if(_count($pdfFiles) > 0){
                $zipFileName = "QR_".date("YmdHis").".zip";

                $zip = new ZipArchive();

                $zipTmpDir = $qr_dir."/";
                $result = $zip->open($qr_dir."/".$zipFileName, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE);
                if( $result !== true ){ //エラー処理
                  die('system error!');
                }
                for ($i=0; $i < _count($pdfFiles); $i++) {
                  $zip->addFile($pdfPaths[$i],$pdfFiles[$i]);
                }

                $zip->close();
                //ダウンロード
                header('Content-Type: application/zip; name="'.$zipFileName.'"');
                header('Content-Disposition: attachment; filename="'.$zipFileName.'"');
                header('Content-Length: '.filesize($qr_dir."/".$zipFileName));
                // ファイルを出力する前に、バッファの内容をクリア（ファイルの破損防止）
                ob_end_clean();
                echo file_get_contents($qr_dir."/".$zipFileName);

                @unlink($qr_dir."/".$zipFileName);
                _rmdir($qr_dir);

                exit();
            }

        //2021/11/08 Add -------------- End ----------------
        }


        $sql .= " limit ".$offset." , ".$limit;
        $main_recs = _select($sql);
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
            $sql .= " from $table_kaijyou_inout";
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

            $sql =<<<EOL
select
substr(kinout_time_out,1,10) as day_kinout_time_out,
min(kinout_time_out) as min_kinout_time_out
from $table_kaijyou_inout
where kinout_delete_date is null
and kinout_event_id = '{$_as($_SESSION[_PROJECT_NAME]['select_event_id'])}'
and kinout_fst_record = 1
and kinout_user_id = '{$_as($main_recs[$i]['user_id'])}'
group by substr(kinout_time_out,1,10)
order by min_kinout_time_out asc
EOL;
            $kout_recs = _select($sql);
            $disp_min_kinout_time_out = '';
            foreach ($kout_recs as $key => $value) {
                if ($disp_min_kinout_time_out != '') $disp_min_kinout_time_out .= '<br>';
                if ($value['min_kinout_time_out'] == '') continue;
                $disp_min_kinout_time_out .= date("n月j日 H:i", strtotime($value['min_kinout_time_out']));
            }
            $main_recs[$i]['disp_min_kinout_time_out'] = $disp_min_kinout_time_out;

            $main_recs[$i]['disp_big_cate'] = $_conf_big_cate_detail[$main_recs[$i]['user_big_cate']];

            if($main_recs[$i]['user_pass']==md5("_NEED_PASS_SET_") ){
                $main_recs[$i]['pass_set'] = "<span style=\"color:red;\">未設定</span>";
            }else{
                $main_recs[$i]['pass_set'] = "<span style=\"color:blue;\">済み</span>";
            }
        }

    }

    if ($_request['exec'] == "szkgrp_qrzipdl") {
        // 閲覧部署グループQRZIPダウンロード

        $dir_path = _SYSTEM_ROOT_DIR.'/upfile/qr_zip/events/';
        $event_id = $_SESSION[_PROJECT_NAME]['select_event_id'];
        $szkgrp_id = null;

        if ($_SESSION[_PROJECT_NAME]['admin_login']['admin_master_kengen'] == 1) {
            // 管理者権限の場合。選択された閲覧部署グループのZIPをダウンロードする
            if ( ! empty($_request['szkgrp_id'])) {

                $szkgrp_id = $_request['szkgrp_id'];

            }
        } else {
            $syozoku_id = $_SESSION[_PROJECT_NAME]['admin_login']['admin_syozoku_id'];

            $sql = "";
            $sql .= " select syozoku_szkgrp_id from m_syozoku ";
            $sql .= " where syozoku_delete_date is null";
            $sql .= "   and syozoku_id = '" .  _as($syozoku_id) . "'";
            $syozoku_recs = _select($sql);
            $szkgrp_id = $syozoku_recs[0]['syozoku_szkgrp_id'];
        }

        if ( ! is_null($szkgrp_id)) {
            $event_dir_path = glob($dir_path . $event_id . "*/");
            $file = glob($event_dir_path[0] . "QR_" . $szkgrp_id . "*");

            if (count($file) > 0) {

                $file_name = basename($file[0]);
                $path = $file[0];

                // ストリームに出力
                header('Content-Type: application/zip; name="' . $file_name . '"');
                header('Content-Disposition: attachment; filename="' . $file_name . '"');
                header('Content-Length: '.filesize($path));
                readfile($path);
            } else {
                $err_msg[] = "該当する閲覧部署グループのZIPファイルが存在しません。";
            }
        } else {
            $err_msg[] = "該当する閲覧部署グループが存在しません。";
        }
    }

    _make_pagenavi2( $blade, $_request, $offset, $allcnt, $limit );

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
        for ($j=0; $j < _count($this_sess['search_condition']['user_syoutai_yotei_time']); $j++) {
            if($this_sess['search_condition']['user_syoutai_yotei_time'][$j] == $wArr[$i]){
                $checked = "checked";
                break;
            }
        }
        $_conf_syoutai_yotei_time[$ymd]['his'][] = array('hi'=>$hi, 'checked'=>$checked);
    }
    $blade->assign('_conf_syoutai_yotei_time',$_conf_syoutai_yotei_time);

    //(来場者)来場予定日時
    $wArr = explode("#", $select_event_rec['event_raijyou_yotei_time']);
    $_conf_raijyou_yotei_time = array();
    $_conf_jitsu_raijyou_yotei_time = array();
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
        $_conf_raijyou_yotei_time[$ymd]['disp_ymd'] = $disp_ymd;
        if( $ymd!='2999/01/01' ){
            $_conf_jitsu_raijyou_yotei_time[$ymd]['disp_ymd'] = $disp_ymd;
        }

        $checked="";
        for ($j=0; $j < _count($this_sess['search_condition']['user_raijyou_yotei_time']); $j++) {
            if($this_sess['search_condition']['user_raijyou_yotei_time'][$j] == $wArr[$i]){
                $checked = "checked";
                break;
            }
        }
        $_conf_raijyou_yotei_time[$ymd]['his'][] = array('hi'=>$hi, 'checked'=>$checked);
        if( $ymd!='2999/01/01' ){
            $_conf_jitsu_raijyou_yotei_time[$ymd]['his'][] = array('hi'=>$hi, 'checked'=>$checked);
        }
    }
    $blade->assign('_conf_raijyou_yotei_time',$_conf_raijyou_yotei_time);
    $blade->assign('_conf_jitsu_raijyou_yotei_time',$_conf_jitsu_raijyou_yotei_time);

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
        $_conf_syozoku[ $syozoku_recs[$i]['syozoku_id'] ] = $syozoku_recs[$i]['syozoku_name'];
    }
    $blade->assign('_conf_syozoku',$_conf_syozoku);

    // 閲覧部署グループマスタ
    $sql = "";
    $sql .= "select * from m_syozoku_group";
    $sql .= " where";
    $sql .= " szkgrp_delete_date is null";
    $sql .= " and szkgrp_hidden_flg = 0";
    $sql .= " order by szkgrp_id asc";
    $syozoku_grp_recs = _select($sql);
    $_conf_syozoku_grp = array();
    for ($i=0; $i < _count($syozoku_grp_recs); $i++) {
        $_conf_syozoku_grp[ $syozoku_grp_recs[$i]['szkgrp_id'] ] = $syozoku_grp_recs[$i]['szkgrp_name'];
    }
    $blade->assign('_conf_syozoku_grp',$_conf_syozoku_grp);

    $loop = 1;
    foreach ($_conf_big_cate_detail as $id => $anme) {
        if ( $loop == 1 ){
            $_edited_big_cate[ 'syoutai' ] = "【招待者】 --------------------";
        }elseif( $loop == 5 ){
            $_edited_big_cate[ 'raijyou' ] = "【来場者】 --------------------";
        }
        $_edited_big_cate[ $id ] = $anme;
        $loop++;
    }


    $blade->assign('_conf_vip',$_conf_vip);
    $blade->assign('_conf_big_cate',$_conf_big_cate);
    // $blade->assign('_conf_big_cate_detail',$_conf_big_cate_detail); mod 2021.05.24
    $blade->assign('_edited_big_cate',$_edited_big_cate);
    $blade->assign('_conf_mid_cate',$_conf_mid_cate);
    $blade->assign('_conf_user_syounin_flg',$_conf_user_syounin_flg); // 2021.06.05
    $blade->assign('soyusai_cond_open',$soyusai_cond_open);

    if($select_event_rec['event_name']==""){
        $contents_title = "来場日時登録";
    }else{
        $contents_title = "来場日時登録" . "（".$select_event_rec['event_name'] .":".$select_event_rec['event_kaijyou_name']."）";
    }

    $blade->assign('title_bgcolor',$select_event_rec['event_title_bgcolor']);

    $active_menu = "user_list";
    $contents_tpl = "user_list";
