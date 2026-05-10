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
    }
    $sql .= " order by company_id asc";
    $company_recs = _select($sql);
    $_conf_company = array();
    for ($i=0; $i < _count($company_recs); $i++) {
      $_conf_company[ $company_recs[$i]['company_id'] ] = $company_recs[$i]['company_name'];
    }
    $blade->assign('_conf_company',$_conf_company);

    // 続けて登録の前回分の結果を表示
    if (isset($_SESSION[_PROJECT_NAME]['admin_login']['insert_continue_success_msg'])) {
        $success_msg = $_SESSION[_PROJECT_NAME]['admin_login']['insert_continue_success_msg'];
        unset($_SESSION[_PROJECT_NAME]['admin_login']['insert_continue_success_msg']);
    }

    // ******************************************************************************************************
    // プルダウンの組み立て (タイムリーなイベント)
    // ******************************************************************************************************
    $sql  = "";
    $sql .= " select event_id, event_pulldown_name"."\n";
    $sql .= " from m_event"."\n";
    $sql .= " where event_delete_date is null"."\n";
    $sql .= " and event_raikainri_ymd_ed > '".date("Y/m/d",strtotime(date("Y/m/d")."-1month"))."'"."\n";
    $sql .= " order by event_id"."\n";
    $recs = _select($sql);
    $_conf_join_event = array();
    $timery_event_in_str = "";
    for ($loop=0; $loop < _count($recs); $loop++) {
        $_conf_join_event[ $recs[$loop]['event_id'] ] = $recs[$loop]['event_pulldown_name'];
        if($timery_event_in_str!="") $timery_event_in_str .= ",";
        $timery_event_in_str .= "'"._as($recs[$loop]['event_id'])."'";
    }
    $blade->assign('_conf_join_event',$_conf_join_event);


    // ******************************************************************************************************
    // 登録・更新・削除
    // ******************************************************************************************************
    if($_request['exec'] == 'save'){

        if($this_sess['token'] != $_request['token']){
            $err_msg[] = 'このデータは処理できませんでした。';
        }elseif( _count( $this_sess ) <= 0 ){
            //**** リロードやボタンダブルクリックでの２重登録抑制
            $err_msg[] = 'このデータは既に処理済みです。';
        }else{
            if($_request['mode'] !='delete'){
                $chks = array(
                                "syoutai_vip_flg,VIP"          => "seisuu",
                                "syoutai_big_cate,大分類"          => "need,seisuu",
                                "syoutai_mid_cate,中分類"          => "need,seisuu",
                                "syoutai_company_id,企業名"                => "need",
//                                "syoutai_kigyou_name,企業名"                 => "need",
//                                "syoutai_kigyou_name_kana,企業名カナ"        => "zenkana",
                                "syoutai_busyo,部署"                         => "need", // 2021.05.21 add
                                "syoutai_name,氏名"                          => "need",
                                "syoutai_name_kana,氏名カナ"                 => "zenkana",
                                "syoutai_mail,メールアドレス"                => "need,email",
                                "syoutai_login_id,ログインID"                => "need,email",
                              );
                $err_msg = _check( $chks, $_request );

                // メールアドレスの重複チェック 2020.12.19 add
                $sql = "";
                $sql .= " select syoutai_id"."\n";
                $sql .= " from v_syoutai"."\n";
                $sql .= " where "."\n";
                $sql .= "   syoutai_delete_date is null ";
                $sql .= "   and syoutai_login_id = '". _as( $_request['syoutai_login_id'] ) ."'";

                if ( $_request['mode'] == 'update' ){
                    $sql .= "   and syoutai_id != '" . _as( $this_sess['id'] ) ."'";
                }
                $chk_recs = _select($sql);
                if ( _count($chk_recs) > 0 ){
                    $err_msg[] = "このログインIDは既に登録済みです。";
                }

                // ID(メアド)から実際のメールアドレス部分抽出
                $real_user_mail_addr = _getMailAddressFromID( $_request['syoutai_login_id'] );

                // emailアドレスの形式チェック
                if ( _emailCheck($real_user_mail_addr, '') === false ){
                    $err_msg[]  = "ログインIDを正しく入力して下さい。";
                }

                if( strpos($_request['syoutai_mail'],'@nippon-access.co.jp')!==FALSE ){
                    $err_msg[]  = "メールアドレスに日本アクセスのメールアドレスは指定できません。";
                }

                if($_request['syoutai_big_cate']!=""){
                    if( intval($_request['syoutai_big_cate']) <= 4){
                        //招待者
                        $this_sess['raijyousya_kbn'] = 1;
                    }else{
                        //来場者
                        $this_sess['raijyousya_kbn'] = 2;
                    }
                }

                $sql = "";
                $sql .= " select company_id" . "\n";
                $sql .= " from m_company"."\n";
                $sql .= " where "."\n";
                $sql .= "   company_delete_date is null ";
                $sql .= "   and company_id = '". _as( $_request['syoutai_company_id'] ) ."'";
                $company_recs = _select($sql);
                if ( _count($company_recs) == 0)
                {
                  $err_msg[] = "入力された企業が企業マスタに存在しません。";
                }

                //**************************************************
                //来場者情報の同時作成のチェック処理
                //**************************************************
                // if ( $_request['user_make_flg'] == 1 ){
                //     if ( $_request['join_event_id'] == '' ){
                //         $err_msg[]  = "来場者情報を作成する場合はイベントを選択して下さい。";
                //     }
                //     if ( $_request['user_admin_id'] == '' ){
                //         $err_msg[]  = "来場者情報を作成する場合は担当者を選択して下さい。";
                //     }
                // } else {
                //     unset( $_request['join_event_id'] );
                // }

                //**** POST値をセッションにマージ ****
                $this_sess = _array_merge( $this_sess, $_request );
            }else{
                //削除はチェックなし
                $this_sess['mode'] = "delete";
            }

            if(_count($err_msg) == 0){

                _query($conn,'begin');

                //**************************************************
                //新規の場合新ID発番
                //**************************************************
                if( $this_sess['mode'] == "insert" ){
                    $max_recs = _select( "select coalesce(max(substring(syoutai_id,2)),'0') as max_id from m_syoutai");
                    $this_sess['id'] = sprintf("s%08d", $max_recs[0]['max_id'] + 1 );
                }

                $array = array();
                $array_n = array();
                $array_m = array();

                // $array['syoutai_sansan_id']           = "'"._as($this_sess['syoutai_sansan_id'])."'"; //SANSANID 2021.05.17 del
                $array['syoutai_vip_flg']             = ""._e2z($this_sess['syoutai_vip_flg']).""; //VIPフラグ（1:VIP）',
                $array['syoutai_big_cate']            = ""._e2n($this_sess['syoutai_big_cate'])."";//大分類',
                $array['syoutai_mid_cate']            = ""._e2n($this_sess['syoutai_mid_cate']).""; //中分類',
                $array['syoutai_company_id']          = ""._as($this_sess['syoutai_company_id'])."";//企業ID
//                $array['syoutai_kigyou_name']         = "'"._as($this_sess['syoutai_kigyou_name'])."'"; //企業名',
//                $array['syoutai_kigyou_name_kana']    = "'"._as($this_sess['syoutai_kigyou_name_kana'])."'"; //企業名カナ',
                $array['syoutai_busyo']               = "'"._as($this_sess['syoutai_busyo'])."'"; //部署',
                $array['syoutai_yakusyoku']           = "'"._as($this_sess['syoutai_yakusyoku'])."'"; //役職',
                $array['syoutai_update_date']         = "'".$_now_timestamp."'"; //更新日時',
                $array['syoutai_tag']                = "'"._as($this_sess['syoutai_tag'])."'"; //タグ文字列',
                $array['syoutai_biko']                = "'"._as($this_sess['syoutai_biko'])."'"; //備考',

                $array['syoutai_last_upd_id']         = "'"._as( $_SESSION[_PROJECT_NAME]['admin_login']['admin_id'] )."'"; //最終更新者ID',

                $array_n['sn_syoutai_name']           = "'"._as($this_sess['syoutai_name'])."'"; //'氏名',
                $array_n['sn_syoutai_name_kana']      = "'"._as($this_sess['syoutai_name_kana'])."'"; //氏名カナ',

                $array_m['sm_syoutai_mail']           = "'"._as($this_sess['syoutai_mail'])."'";      // メールアドレス',
                $array_m['sm_syoutai_login_id']       = "'"._as($this_sess['syoutai_login_id'])."'"; // ログインid',

                switch( $this_sess['mode'] ){
                     case 'insert':
                        $array['syoutai_id']              = "'"._as($this_sess['id'])."'";
                        $array['syoutai_insert_date']     = "'".$_now_timestamp."'";
                        $array['syoutai_last_upd_naiyou'] = "'"._as( '新規登録' )."'"; //最終更新内容',
                        _insert( 'm_syoutai', $array);

                        $array_n['sn_syoutai_id']      = "'"._as($this_sess['id'])."'";
                        _insert( 'm_sname', $array_n);

                        $array_m['sm_syoutai_id']         = "'"._as($this_sess['id'])."'";
                        _insert( 'm_smail', $array_m);

                        $success_msg = "登録しました。";
                        $onload_flg = 1;

                     break;
                     case 'update':
                        $array['syoutai_last_upd_naiyou']  = "'"._as( '修正' )."'"; //最終更新内容',

                        $where = "syoutai_id='"._as($this_sess['id'])."'";
                        _update( 'm_syoutai', $array, $where );

                        $where = "sn_syoutai_id='"._as($this_sess['id'])."'";
                        _update( 'm_sname', $array_n, $where );

                        $where = "sm_syoutai_id='"._as($this_sess['id'])."'";
                        _update( 'm_smail', $array_m, $where );

                        $success_msg = "変更が完了いたしました。";
                        $onload_flg = 1;
                     break;
                     case 'delete':
                        $array['syoutai_last_upd_naiyou']  = "'"._as( '削除' )."'"; //最終更新内容',

                        $array = array();
                        $array['syoutai_delete_date']      = "'".$_now_timestamp."'";
                        $where = "syoutai_id='"._as($this_sess['id'])."'";
                        _update( 'm_syoutai', $array, $where );
                        $success_msg = "削除しました。";
                     break;
                }

                // ******************************************************************************************************
                //来場者情報の同時作成処理
                // ******************************************************************************************************
                // if( $_request['user_make_flg'] == 1 && $_request['mode'] !='delete' ){

                //     $sql = "";
                //     $sql .= " select user_id"."\n";
                //     $sql .= " from v_user"."\n";
                //     $sql .= " where user_delete_date is null"."\n";
                //     $sql .= "  and user_event_id = '"._as($_request['join_event_id'] )."'"."\n";
                //     $sql .= "  and user_syoutai_id = '"._as( $this_sess['id'] )."'"."\n";
                //     $chk_recs = _select($sql);

                //     $array = array();
                //     $array_n = array();
                //     $array_m = array();

                //     if ( $chk_recs[0]['user_id'] != '' ){
                //         // update
                //         $user_id = $chk_recs[0]['user_id'];

                //     } else {
                //         // insert
                //         $max_recs = _select( "select coalesce(max(substring(user_id,2)),'0') as max_id from m_user");
                //         $user_id = sprintf("u%08d", $max_recs[0]['max_id'] + 1 );

                //         $array['user_id']                  = "'"._as( $user_id )."'";
                //         $array['user_insert_date']         = "'".$_now_timestamp."'";
                //         $array['user_pass']                = "'"._as( md5( "_NEED_PASS_SET_" ) )."'"; //パスワード',

                //         $array_n['un_user_id']             = "'"._as( $user_id )."'";
                //         $array_m['um_user_id']             = "'"._as( $user_id )."'";
                //     }

                //     // 来場予定日時(任意)
                //     $yotei = "";
                //     for ($num=0; $num < _count($_request['syoutai_yotei_time']); $num++) {
                //         if($yotei!="") $yotei .= "#";
                //         $yotei .= $_request['syoutai_yotei_time'][$num];
                //     }
                //     $_request['user_raijyou_yotei_time']  = $yotei;
                //     $this_sess['user_raijyou_yotei_time'] = $yotei;

                //     $array['user_event_id']            = "'"._as($this_sess['join_event_id'] )."'"; //イベントID（e0001）',
                //     $array['user_admin_id']            = "'"._as($this_sess['user_admin_id'])."'"; //担当者ID（a0000001）',
                //     $array['user_syoutai_id']          = "'"._as($this_sess['id'])."'"; //招待者ID（s0000001）',
                //     $array['user_vip_flg']             = ""._e2z($this_sess['syoutai_vip_flg']).""; //VIPフラグ（1:VIP）',
                //     $array['user_big_cate']            = ""._e2n($this_sess['syoutai_big_cate'])."";//大分類',
                //     $array['user_mid_cate']            = ""._e2n($this_sess['syoutai_mid_cate']).""; //中分類',
                //     $array['user_kigyou_name']         = "'"._as($this_sess['syoutai_kigyou_name'])."'"; //企業名',
                //     $array['user_kigyou_name_kana']    = "'"._as($this_sess['syoutai_kigyou_name_kana'])."'"; //企業名カナ',
                //     $array['user_busyo']               = "'"._as($this_sess['syoutai_busyo'])."'"; //部署',
                //     $array['user_yakusyoku']           = "'"._as($this_sess['syoutai_yakusyoku'])."'"; //役職',
                //     $array['user_raijyou_yotei_time']  = "'"._as( $this_sess['user_raijyou_yotei_time'] )."'"; //来場予定日時（yyyy/mm/dd HH:ii 形式）',
                //     $array['user_biko']                = "'"._as($this_sess['syoutai_biko'])."'"; //備考',
                //     $array['user_syounin_flg']         = 1; //'WEB招待の承認フラグ(0:未承認、1:承認済み)',
                //     $array['user_update_date']         = "'".$_now_timestamp."'";

                //     $array_n['un_user_name']           = "'"._as($this_sess['syoutai_name'])."'"; //'氏名',
                //     $array_n['un_user_name_kana']      = "'"._as($this_sess['syoutai_name_kana'])."'"; //氏名カナ',

                //     $array_m['um_user_mail']           = "'"._as($this_sess['syoutai_mail'])."'"; //メールアドレス',
                //     $array_m['um_user_login_id']       = "'"._as($this_sess['syoutai_login_id'])."'"; // ログインid',

                //     if ( $chk_recs[0]['user_id'] != '' ){
                //         // update
                //         $where = "user_id = '"._as($user_id)."'";
                //         _update( 'm_user', $array, $where);

                //         $where = "un_user_id = '"._as($user_id)."'";
                //         _update( 'm_uname', $array_n, $where);

                //         $where = "um_user_id = '"._as($user_id)."'";
                //         _update( 'm_umail', $array_m, $where);

                //     } else {
                //         // insert
                //         _insert( 'm_user', $array);
                //         _insert( 'm_uname', $array_n);
                //         _insert( 'm_umail', $array_m);
                //     }

                // } // 来場者情報の同時作成処理


                if( $_request['mode'] =='update'){
                    //修正時は、m_user系もupdate
                    $sql = "";
                    $sql .= " select user_id"."\n";
                    $sql .= " from v_user"."\n";
                    $sql .= " where user_delete_date is null"."\n";
                    $sql .= "  and user_syoutai_id = '"._as( $this_sess['id'] )."'"."\n";
                    $chk_recs = _select($sql);
                    for ($i=0; $i < _count($chk_recs); $i++) {
                        $user_id = $chk_recs[$i]['user_id'];

                        $array = array();
                        $array_n = array();
                        $array_m = array();


                        $array['user_vip_flg']             = ""._e2z($this_sess['syoutai_vip_flg']).""; //VIPフラグ（1:VIP）',
                        $array['user_big_cate']            = ""._e2n($this_sess['syoutai_big_cate'])."";//大分類',
                        $array['user_mid_cate']            = ""._e2n($this_sess['syoutai_mid_cate']).""; //中分類',
                        $array['user_company_id']          = ""._as($this_sess['syoutai_company_id'])."";//企業ID
                        $array['user_kigyou_name']         = "'"._as($this_sess['syoutai_kigyou_name'])."'"; //企業名',
                        $array['user_kigyou_name_kana']    = "'"._as($this_sess['syoutai_kigyou_name_kana'])."'"; //企業名カナ',
                        $array['user_busyo']               = "'"._as($this_sess['syoutai_busyo'])."'"; //部署',
                        $array['user_yakusyoku']           = "'"._as($this_sess['syoutai_yakusyoku'])."'"; //役職',
                        $array['user_biko']                = "'"._as( $this_sess['syoutai_biko'] )."'"; //備考',
                        $array['user_update_date']         = "'".$_now_timestamp."'";

                        $array_n['un_user_name']           = "'"._as($this_sess['syoutai_name'])."'"; //'氏名',
                        $array_n['un_user_name_kana']      = "'"._as($this_sess['syoutai_name_kana'])."'"; //氏名カナ',

                        $array_m['um_user_mail']           = "'"._as($this_sess['syoutai_mail'])."'"; //メールアドレス',
                        $array_m['um_user_login_id']       = "'"._as($this_sess['syoutai_login_id'])."'"; // ログインid',

                        // update

                        // m_user の情報を更新する
                        // イベントが終了（イベント終了日 + 30日）している場合は大分類は更新しない
                        // イベントが終了していない場合は大分類も更新する

                        // イベント終了日時 +30日 が経過している
                        // +30日を DATE_ADD(m_event.event_kaisai_ymd_ed, INTERVAL 30 DAY) で記述すると MySQL に依存するので
                        // 基準日（現在日）から -30日する事で実現する
                        $today = date('Y-m-d', strtotime('-30 day'));

                        // 終了しているイベントと終了していないイベントのIDを取得する
                        $sql = 'SELECT m_event.event_id ';
                        $sql .= 'FROM m_event ';
                        $sql .= 'INNER JOIN m_user ON (m_event.event_id = m_user.user_event_id ';
                        $sql .= 'AND m_user.user_id = '. "'" ._as($user_id) . "') ";
                        foreach(['past', 'future'] as $tense) {
                            $array_tmp = $array;
                            // 終了しているイベント
                            if ($tense == 'past') {
                                unset($array_tmp['user_big_cate']);
                                $sql_tmp = $sql . 'WHERE m_event.event_kaisai_ymd_ed <= ' . "'" . _as($today) . "'";
                            }
                            // 終了していないイベント
                            else {
                                $sql_tmp = $sql . 'WHERE m_event.event_kaisai_ymd_ed > ' . "'" . _as($today) . "'";
                            }
                            $rec_event = _select($sql_tmp);
                            // イベントが取得出来ない場合はその後の処理は行わない
                            if(count($rec_event) == 0) continue;

                            $event_ids = [];
                            foreach($rec_event as $row) {
                                $event_ids[] = "'" . $row['event_id'] . "'";
                            }
                            $event_ids = implode(',', $event_ids);

                            // 指定した招待者の情報を更新する
                            $where = "user_id = '"._as($user_id)."'";
                            $where .= " AND user_event_id IN (".$event_ids.")";
                            _update( 'm_user', $array_tmp, $where);
                        }

                        $where = "un_user_id = '"._as($user_id)."'";
                        _update( 'm_uname', $array_n, $where);

                        $where = "um_user_id = '"._as($user_id)."'";
                        _update( 'm_umail', $array_m, $where);

                    }

                }elseif( $_request['mode'] =='delete'){
                    //削除時は、m_user系もdelete_date設定

                    // 2023/07 仕様削除
//                    if($timery_event_in_str!=""){
//                        $sql = "";
//                        $sql .= " select user_id"."\n";
//                        $sql .= " from v_user"."\n";
//                        $sql .= " where user_delete_date is null"."\n";
//                        $sql .= "  and user_syoutai_id = '"._as( $this_sess['id'] )."'"."\n";
//                        $sql .= " and user_event_id in (".$timery_event_in_str.")"; //タイムリーなイベントのみ削除するため
//                        $chk_recs = _select($sql);
//                        for ($i=0; $i < _count($chk_recs); $i++) {
//                            $user_id = $chk_recs[$i]['user_id'];
//
//                            $array = array();
//                            $array['user_delete_date']         = "'".$_now_timestamp."'";
//
//                            // update
//                            $where = "";
//                            $where .= "user_id = '"._as($user_id)."'";
//                            _update( 'm_user', $array, $where);
//
//                        }
//                    }

                }


                _query($conn,'commit');

                $w_id = $this_sess['id'];
                $w_mode = $this_sess['mode'];
                $_SESSION[_PROJECT_NAME][$page] = array();
                unset( $_SESSION[_PROJECT_NAME][$page] );
                unset( $this_sess );
                $this_sess = array();

                if($w_mode != 'delete'){
                    $_request['exec'] = "";
                    $_request['id'] = $w_id;

                    // 連続登録の場合
                    if (count($err_msg) ==0 && $_request['insert_continue'] != '') {
                        $_SESSION[_PROJECT_NAME]['admin_login']['insert_continue_success_msg'] = $success_msg;
                        header('Location: index.php?page=syoutai_edit&from_page=syoutai_list');
                        exit();
                    }

                }else{
                    header('Location: index.php?page=syoutai_list&sess_no_init=1');
                    exit();
                }
            } else {
                // エラー処理
                unset( $this_sess['user_admin_id'] );
                unset( $this_sess['join_event_id'] );
            }
        }
    }

    // ******************************************************************************************************
    // 初期・完了画面
    // ******************************************************************************************************
    if($_request['exec'] != 'save' && _count($err_msg) == 0){
        $token = rand();
        unset( $_SESSION[_PROJECT_NAME][$page] );
        unset( $this_sess );
        $this_sess = &$_SESSION[_PROJECT_NAME][$page];
        if( $_request['id'] != "" ){
            // 編集
            $sql  = "";
            $sql .= " select ";
            $sql .= "   * ";
            $sql .= " from v_syoutai ";
            $sql .= " where ";
            $sql .= "   syoutai_delete_date is null";
            $sql .= " and syoutai_id='"._as($_request['id'])."'";
            $main_rec = _select($sql);
            $this_sess = $main_rec[0];
            $this_sess['id'] = $main_rec[0]['syoutai_id'];
            $this_sess['raijyousya_kbn'] = $_conf_raijyousya_kbn[$this_sess['syoutai_big_cate']];
            $this_sess['mode'] = "update";

            // 来場者情報の作成完了後は入力欄に値を入れない
            if ( $_request['user_make_flg'] != 1 ){
                $this_sess['join_event_id'] = $_request['join_event_id'];
            }

            // ----------------------------------------------------------------------------------------------------- //
            // 最終更新者・最終更新内容の表示
            // ----------------------------------------------------------------------------------------------------- //
            if ( $main_rec[0]['syoutai_last_upd_id'] != '' && $main_rec[0]['syoutai_last_upd_naiyou'] != '' ){
                $syoutai_last_upd_disp = "最終更新者：";

                $sql = "";
                $sql .= " select user_name as name, user_busyo as busyo, 'ユーザー' as kbn"."\n";
                $sql .= " from v_user"."\n";
                $sql .= " where user_id = '"._as( $main_rec[0]['syoutai_last_upd_id'] )."'"."\n";
                $sql .= " union"."\n";
                $sql .= " select admin_name as name,syozoku_name as busyo, '担当者' as kbn"."\n";
                $sql .= " from v_admin"."\n";
                $sql .= " left join m_syozoku on (v_admin.admin_syozoku_id = m_syozoku.syozoku_id)"."\n";
                $sql .= " where admin_id = '"._as( $main_rec[0]['syoutai_last_upd_id'] )."'"."\n";
                $last_user_rec = _select( $sql );
                if ( $last_user_rec[0]['kbn'] == '担当者' && $last_user_rec[0]['busyo'] != ''){
                    $syoutai_last_upd_disp .= $last_user_rec[0]['busyo']." ";
                } elseif( $last_user_rec[0]['kbn'] == 'ユーザー' ){
                    $syoutai_last_upd_disp .= "ユーザー ";
                }

                if ( $last_user_rec[0]['name'] != '' ){
                    $syoutai_last_upd_disp .= $last_user_rec[0]['name']." ";
                }

                $w_buff = "";
                if ( $main_rec[0]['syoutai_last_upd_naiyou'] == '新規登録' || $main_rec[0]['syoutai_last_upd_naiyou'] == 'CSV一括新規登録' ){
                    $w_buff = date( 'Y/m/d H:i:s', strtotime( $main_rec[0]['syoutai_insert_date'] ));
                } elseif ( $main_rec[0]['syoutai_last_upd_naiyou'] == '修正' ){
                    $w_buff = date( 'Y/m/d H:i:s', strtotime( $main_rec[0]['syoutai_update_date'] ));
                } elseif ( $main_rec[0]['syoutai_last_upd_naiyou'] == '削除' ){
                    $w_buff = date( 'Y/m/d H:i:s', strtotime( $main_rec[0]['syoutai_delete_date'] ));
                }

                $syoutai_last_upd_disp .= "(".$w_buff."[".$main_rec[0]['syoutai_last_upd_naiyou']."])";
                $this_sess['syoutai_last_upd_disp'] = $syoutai_last_upd_disp;
            }

            // ----------------------------------------------------------------------------------------------------- //
            // 招待しているイベントの表示
            // ----------------------------------------------------------------------------------------------------- //
            $sql = "";
            $sql .= " select user_raijyou_yotei_time, user_web, event_pulldown_name"."\n";
            $sql .= " from m_user"."\n";
            $sql .= " inner join m_event on (m_user.user_event_id = m_event.event_id)"."\n";
            $sql .= " where user_delete_date is null"."\n";
            $sql .= " and user_syoutai_id = '"._as( $_request['id'] )."'"."\n";
            $sql .= " order by event_kaisai_ymd_st asc"."\n";
            $sanka_event_recs = _select( $sql );
            for ($idx=0; $idx < _count($sanka_event_recs); $idx++) {
                if ( $sanka_event_recs[$idx]['user_raijyou_yotei_time'] != '' ){
                    $sanka_event_recs[$idx]['genti_tenjikai_disp'] = "○";
                }
                if ( $sanka_event_recs[$idx]['user_web'] == '1' ){
                    $sanka_event_recs[$idx]['web_tenjikai_disp'] = "○";
                }
            }
        }else{
            // 新規登録
            $this_sess['mode'] = "insert";
        }

        $this_sess['token'] = $token;
    }


    // ----------------------------------------------------------------------------------------------------- //
    // イベントに招待する担当者
    // ----------------------------------------------------------------------------------------------------- //
    // イベントに招待する担当者は維持する
    $this_sess['user_admin_id'] = $_request['user_admin_id'];
    // イベントに招待する担当者が空だった場合
    if ( $this_sess['user_admin_id'] == '' ){
        // ログインIDを設定する
        $this_sess['user_admin_id'] = $_SESSION[_PROJECT_NAME]['admin_login']['admin_id'];
    }

    if ( $this_sess['user_admin_id'] != ''){
        $sql = "";
        $sql .= " select admin_name,syozoku_id, syozoku_name "."\n";
        $sql .= " from v_admin "."\n";
        $sql .= " left join m_syozoku on (m_syozoku.syozoku_id = v_admin.admin_syozoku_id) "."\n";
        $sql .= " where admin_delete_date is null "."\n";
        $sql .= " and syozoku_delete_date is null "."\n";
        $sql .= " and admin_id = '"._as( $this_sess['user_admin_id'] )."'";
        $tantou_recs = _select($sql);

        $this_sess['syozoku_name']     = $tantou_recs[0]['syozoku_name'];
        $this_sess['admin_name']       = $tantou_recs[0]['admin_name'];
        $this_sess['admin_syozoku_id'] = $tantou_recs[0]['syozoku_id'];
    }

    _setAssign($blade,$this_sess);

    $blade->assign('sanka_event_recs',$sanka_event_recs);

    $blade->assign('_conf_vip',$_conf_vip);
    $blade->assign('_conf_big_cate',$_conf_big_cate);
    $blade->assign('_conf_mid_cate',$_conf_mid_cate);

    $blade->assign('syoutai_page', 1);
    $blade->assign('onload_flg', _e2z($onload_flg));
    $blade->assign('from_page', $_request['from_page']);

    if($this_sess['mode']=="insert"){
        $modeStr = "新規登録";
    }else{
        $modeStr = "編集";
    }

    $contents_title = "来場者マスタ".$modeStr;
    $active_menu = "syoutai_list";
    $contents_tpl = "syoutai_edit";
