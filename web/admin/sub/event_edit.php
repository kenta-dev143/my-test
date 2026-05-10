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

    $_conf_pulldown_disp = array();
    $_conf_pulldown_disp['1'] = "プルダウン表示する";
    $_conf_pulldown_disp['2'] = "権限1,2のみ表示する";
    $_conf_pulldown_disp['0'] = "プルダウン表示しない";
    $blade->assign('_conf_pulldown_disp',$_conf_pulldown_disp);

    // ******************************************************************************************************
    // 初期値
    // ******************************************************************************************************
    $page = $_request['page'];
    $this_sess = &$_SESSION[_PROJECT_NAME][$page];
    $err_msg = array();

    if( $_request['mode'] == 'insert' ){
        set_time_limit(120); //2分に延長
    }

    // ******************************************************************************************************
    // 登録・更新・削除
    // ******************************************************************************************************
    if($_request['exec'] == 'save'){

        if( $this_sess['token'] != $_request['token'] ){
            $err_msg[] = 'このデータは処理できませんでした。';
        }elseif( _count( $this_sess ) <= 0 ){
            //**** リロードやボタンダブルクリックでの２重登録抑制
            $err_msg[] = 'このデータは既に処理済みです。';
        }else{
            if($_request['mode'] !='delete' && $_request['mode'] !='archive'){
                $_request['event_area_shikibetsu_id'] = strtoupper($_request['event_area_shikibetsu_id']);

                $chks = array(
                                "event_name,イベント名"    => "need",
                                "event_area_shikibetsu_id,イベント地域識別ID"    => "need,eiji,len=1",
                                "event_pulldown_name,プルダウン用イベント名"    => "need",
                                "event_pulldown_disp_flg,プルダウン表示"    => "need,seisuu",
                                "event_kaijyou_name,会場名"    => "need",
                                "event_url_key,マイページ等のURL用Key"    => "need,eisuubar",
                                "event_exhibition_url_key,exhibitionのURL用Key"    => "eisuubar", //2021/05/25 Add
                                "event_kaisai_ymd_st,開催期間(開始)"    => "need,date",
                                "event_kaisai_ymd_ed,開催期間(終了)"    => "need,date",
                                "event_raikainri_ymd_st,来場管理期間(開始)"    => "need,date",
                                "event_raikainri_ymd_ed,来場管理期間(終了)"    => "need,date",
                              );

                $err_msg = _check( $chks, $_request );

                if(_count($err_msg)==0){
                    // イベント名の重複チェック
                    $sql = "";
                    $sql .= " select event_id"."\n";
                    $sql .= " from m_event"."\n";
                    $sql .= " where "."\n";
                    $sql .= "   event_delete_date is null ";
                    $sql .= "   and event_name = '". _as( $_request['event_name'] ) ."'";
                    if ( $_request['mode'] == 'update' ){
                        $sql .= "   and event_id != '" . _as( $this_sess['id'] ) ."'";
                    }
                    $chk_recs = _select($sql);
                    if ( _count($chk_recs) > 0 ){
                        $err_msg[] = "指定されたイベント名は既に登録済みです。";
                    }

                    // プルダウン用イベント名の重複チェック
                    $sql = "";
                    $sql .= " select event_id"."\n";
                    $sql .= " from m_event"."\n";
                    $sql .= " where "."\n";
                    $sql .= "   event_delete_date is null ";
                    $sql .= "   and event_pulldown_name = '". _as( $_request['event_pulldown_name'] ) ."'";
                    if ( $_request['mode'] == 'update' ){
                        $sql .= "   and event_id != '" . _as( $this_sess['id'] ) ."'";
                    }
                    $chk_recs = _select($sql);
                    if ( _count($chk_recs) > 0 ){
                        $err_msg[] = "指定されたプルダウン用イベント名は既に登録済みです。";
                    }

                    // URL用Keyの重複チェック
                    $sql = "";
                    $sql .= " select event_id"."\n";
                    $sql .= " from m_event"."\n";
                    $sql .= " where "."\n";
                    $sql .= "   event_delete_date is null ";
                    $sql .= "   and event_url_key = '". _as( $_request['event_url_key'] ) ."'";
                    if ( $_request['mode'] == 'update' ){
                        $sql .= "   and event_id != '" . _as( $this_sess['id'] ) ."'";
                    }
                    $chk_recs = _select($sql);
                    if ( _count($chk_recs) > 0 ){
                        $err_msg[] = "指定されたマイページ等のURL用Keyは既に登録済みです。";
                    }

                    //2021/05/25 Add ---------- Start ---------
                    // exhibitionのURL用Keyの重複チェック
                    if($_request['event_exhibition_url_key']!=""){
                        $sql = "";
                        $sql .= " select event_id"."\n";
                        $sql .= " from m_event"."\n";
                        $sql .= " where "."\n";
                        $sql .= "   event_delete_date is null ";
                        $sql .= "   and event_exhibition_url_key = '". _as( $_request['event_exhibition_url_key'] ) ."'";
                        if ( $_request['mode'] == 'update' ){
                            $sql .= "   and event_id != '" . _as( $this_sess['id'] ) ."'";
                        }
                        $chk_recs_2 = _select($sql);
                        if ( _count($chk_recs_2) > 0 ){
                            $err_msg[] = "指定されたexhibitionのURL用Keyは既に登録済みです。";
                        }
                    }
                    //2021/05/25 Add ---------- End ---------

                    if($_request['event_kaisai_ymd_st'] > $_request['event_kaisai_ymd_ed']){
                        $err_msg[] = "開催期間の開始と終了が逆転しています。";
                    }

                    if($_request['event_raikainri_ymd_st'] > $_request['event_raikainri_ymd_ed']){
                        $err_msg[] = "来場管理期間の開始と終了が逆転しています。";
                    }

                }

                // *****************************
                // 招待情報
                // *****************************
                $d_set=0;
                $t_set=0;
                for ($idx=0; $idx <= 5; $idx++) {
                    if(trim($_request['syoutai_ymd_'.$idx])!=""){
                        $d_set++;
                    }
                    for ($i=0; $i < 10; $i++) {
                        if(trim($_request['syoutai_time_'.$idx."_".$i]) != ""){
                            $t_set++;
                        }
                    }
                }
                if($d_set==0 && $t_set==0){
                    $err_msg[] = "(招待者)来場予定日時を指定してください。";
                }else{
                    $dateErr = false;
                    for ($idx=0; $idx <= 5; $idx++) {
                        if(trim($_request['syoutai_ymd_'.$idx])!=""){
                            if( _dateCheck(trim($_request['syoutai_ymd_'.$idx]),'')==false ){
                                $err_msg[] = "(招待者)来場予定日時で日付が正しくない部分があります。";
                                $dateErr = true;
                            }else{
                                $setCnt = 0;
                                for ($i=0; $i < 10; $i++) {
                                    if(trim($_request['syoutai_time_'.$idx."_".$i]) != ""){
                                        $setCnt++;
                                    }
                                }
                                if($setCnt==0){
                                    $err_msg[] = "(招待者)来場予定日時の".trim($_request['syoutai_ymd_'.$idx])."の時間帯が指定されていません。";
                                }
                            }
                        }else{
                            $setCnt = 0;
                            for ($i=0; $i < 10; $i++) {
                                if(trim($_request['syoutai_time_'.$idx."_".$i]) != ""){
                                    $setCnt++;
                                }
                            }
                            if($setCnt>0){
                                $err_msg[] = "(招待者)来場予定日時で日付が指定されていない部分があります。";
                            }
                        }
                    }
                    if($dateErr==false){
                        $saveDate = "";
                        for ($idx=0; $idx <= 5; $idx++) {
                            if(trim($_request['syoutai_ymd_'.$idx])!=""){
                                if($saveDate!=""){
                                    if($saveDate >= trim($_request['syoutai_ymd_'.$idx]) ){
                                        $err_msg[] = "(招待者)来場予定日時で日付の入力順序が日付順になっていません。";
                                    }
                                }
                                $saveDate = trim($_request['syoutai_ymd_'.$idx]);
                            }
                        }
                    }
                }

                // *****************************
                // 来場情報
                // *****************************
                $d_set=0;
                $t_set=0;
                for ($idx=0; $idx <= 5; $idx++) {
                    if(trim($_request['raijyou_ymd_'.$idx])!=""){
                        $d_set++;
                    }
                    for ($i=0; $i < 10; $i++) {
                        if(trim($_request['raijyou_time_'.$idx."_".$i]) != ""){
                            $t_set++;
                        }
                    }
                }
                if($d_set==0 && $t_set==0){
                    $err_msg[] = "(来場者)来場予定日時を指定してください。";
                }else{
                    $dateErr = false;
                    for ($idx=0; $idx <= 5; $idx++) {
                        if(trim($_request['raijyou_ymd_'.$idx])!=""){
                            if( _dateCheck(trim($_request['raijyou_ymd_'.$idx]),'')==false ){
                                $err_msg[] = "(来場者)来場予定日時で日付が正しくない部分があります。";
                                $dateErr = true;
                            }else{
                                $setCnt = 0;
                                for ($i=0; $i < 10; $i++) {
                                    if(trim($_request['raijyou_time_'.$idx."_".$i]) != ""){
                                        $setCnt++;
                                    }
                                }
                                if($setCnt==0){
                                    $err_msg[] = "(来場者)来場予定日時の".trim($_request['raijyou_ymd_'.$idx])."の時間帯が指定されていません。";
                                }
                            }
                        }else{
                            $setCnt = 0;
                            for ($i=0; $i < 10; $i++) {
                                if(trim($_request['raijyou_time_'.$idx."_".$i]) != ""){
                                    $setCnt++;
                                }
                            }
                            if($setCnt>0){
                                $err_msg[] = "(来場者)来場予定日時で日付が指定されていない部分があります。";
                            }
                        }
                    }
                    if($dateErr==false){
                        $saveDate = "";
                        for ($idx=0; $idx <= 5; $idx++) {
                            if(trim($_request['raijyou_ymd_'.$idx])!=""){
                                if($saveDate!=""){
                                    if($saveDate >= trim($_request['raijyou_ymd_'.$idx]) ){
                                        $err_msg[] = "(来場者)来場予定日時で日付の入力順序が日付順になっていません。";
                                    }
                                }
                                $saveDate = trim($_request['raijyou_ymd_'.$idx]);
                            }
                        }
                    }
                }


                $_request['event_rsignup_start_ymdhi'] = "";
                if( $_request['event_rsignup_start_ymd']!="" || $_request['event_rsignup_start_hh']!="" || $_request['event_rsignup_start_ii']!=""){
                    if( $_request['event_rsignup_start_ymd']=="" || $_request['event_rsignup_start_hh']=="" || $_request['event_rsignup_start_ii']==""){
                        $err_msg[] = "来場日時登録(r-signup)受付開始日時は全項目指定してください。";
                    }else{
                        if( _dateCheck($_request['event_rsignup_start_ymd'],'')==false ){
                            $err_msg[] = "来場日時登録(r-signup)受付開始日時の日付が正しくありません。";
                        }
                        $_request['event_rsignup_start_hi'] = $_request['event_rsignup_start_hh'].":".$_request['event_rsignup_start_ii'];
                        if( _timeCheck($_request['event_rsignup_start_hi'],'')==false ){
                            $err_msg[] = "来場日時登録(r-signup)受付開始日時の時刻が正しくありません。";
                        }
                        if(_count($err_msg)==0){
                            $_request['event_rsignup_start_ymdhi'] = $_request['event_rsignup_start_ymd']." ".$_request['event_rsignup_start_hi'];
                        }
                    }
                }

                $_request['event_rsignup_end_ymdhi'] = "";
                if( $_request['event_rsignup_end_ymd']!="" || $_request['event_rsignup_end_hh']!="" || $_request['event_rsignup_end_ii']!=""){
                    if( $_request['event_rsignup_end_ymd']=="" || $_request['event_rsignup_end_hh']=="" || $_request['event_rsignup_end_ii']==""){
                        $err_msg[] = "来場日時登録(r-signup)受付終了日時は全項目指定してください。";
                    }else{
                        if( _dateCheck($_request['event_rsignup_end_ymd'],'')==false ){
                            $err_msg[] = "来場日時登録(r-signup)受付終了日時の日付が正しくありません。";
                        }
                        $_request['event_rsignup_end_hi'] = $_request['event_rsignup_end_hh'].":".$_request['event_rsignup_end_ii'];
                        if( _timeCheck($_request['event_rsignup_end_hi'],'')==false ){
                            $err_msg[] = "来場日時登録(r-signup)受付終了日時の時刻が正しくありません。";
                        }
                        if(_count($err_msg)==0){
                            $_request['event_rsignup_end_ymdhi'] = $_request['event_rsignup_end_ymd']." ".$_request['event_rsignup_end_hi'];
                        }
                    }
                }

                //**** POST値をセッションにマージ ****
                $this_sess = _array_merge( $this_sess, $_request );
            }else{

                $sql = "";
                $sql .= " select *"."\n";
                $sql .= " from m_event"."\n";
                $sql .= " where "."\n";
                $sql .= "   event_id = '" . _as( $this_sess['id'] ) ."'";
                $chk_recs = _select($sql);

                if ($_request['mode'] == 'archive') {
                    $this_sess['mode'] = "archive";
                    $now = date("Y/m/d");

                    if ($chk_recs[0]['event_archived_flg'] == '1') {
                        $err_msg[] = "すでにアーカイブ済みです。";
                    } else if (strtotime($now) <= strtotime($chk_recs[0]['event_kaisai_ymd_ed'])) {
                        $err_msg[] = "イベント開催期間中にアーカイブはできません。";
                    }
                }

                if (count($err_msg) == 0 && $chk_recs[0]['event_archived_flg'] != '1') {
                    set_time_limit(120); //2分に延長
                    _query($conn,'begin');

                    // m_userのアーカイブ
                    $sql = "";
                    $sql .= " select *"."\n";
                    $sql .= " from m_user"."\n";
                    $sql .= " where "."\n";
                    $sql .= "   user_event_id = '" . _as( $this_sess['id'] ) ."'";
                    $user_recs = _select($sql);

                    $query = "";
                    $query .= " INSERT INTO a_user";
                    $query .= $sql;
                    _query($conn, $query);

                    $user_ids = array();
                    foreach ($user_recs as $user) {
                        $user_ids[] = $user['user_id'];
                    }

                    unset($user_recs);

                    // m_unameのアーカイブ
                    $sql = "";
                    $sql .= " select *"."\n";
                    $sql .= " from m_uname"."\n";
                    $sql .= " where "."\n";
                    $sql .= "   un_user_id in ('" . implode("','", $user_ids) ."')";

                    $query = "";
                    $query .= " INSERT INTO a_uname";
                    $query .= $sql;
                    _query($conn, $query);

                    // m_umailのアーカイブ
                    $sql = "";
                    $sql .= " select *"."\n";
                    $sql .= " from m_umail"."\n";
                    $sql .= " where "."\n";
                    $sql .= "   um_user_id in ('" . implode("','", $user_ids) ."')";

                    $query = "";
                    $query .= " INSERT INTO a_umail";
                    $query .= $sql;
                    _query($conn, $query);

                    // t_area_inoutのアーカイブ
                    $sql = "";
                    $sql .= " select *"."\n";
                    $sql .= " from t_area_inout"."\n";
                    $sql .= " where "."\n";
                    $sql .= "   ainout_event_id = '" . _as( $this_sess['id'] ) ."'";

                    $query = "";
                    $query .= " INSERT INTO a_area_inout";
                    $query .= $sql;
                    _query($conn, $query);

                    // t_kaijyou_inoutのアーカイブ
                    $sql = "";
                    $sql .= " select *"."\n";
                    $sql .= " from t_kaijyou_inout"."\n";
                    $sql .= " where "."\n";
                    $sql .= "   kinout_event_id = '" . _as( $this_sess['id'] ) ."'";

                    $query = "";
                    $query .= " INSERT INTO a_kaijyou_inout";
                    $query .= $sql;
                    _query($conn, $query);

                    // 元データの物理削除
                    _delete('m_user', "user_event_id = '" . _as( $this_sess['id'] ) ."'");
                    _delete('m_uname', "un_user_id in ('" . implode("','", $user_ids) ."')");
                    _delete('m_umail', "um_user_id in ('" . implode("','", $user_ids) ."')");
                    _delete('t_area_inout', "ainout_event_id = '" . _as( $this_sess['id'] ) ."'");
                    _delete('t_kaijyou_inout', "kinout_event_id = '" . _as( $this_sess['id'] ) ."'");

                    // qr_linkの物理削除
                    _delete('t_qr_link', "ql_event_id = '" . _as( $this_sess['id'] ) ."'");

                    // イベントのアーカイブ済みフラグを立てる
                    $array = array();
                    $array['event_archived_flg'] = 1;
                    $where = "event_id='"._as($this_sess['id'])."'";
                    _update( 'm_event', $array, $where);

                    _query($conn,'commit');

                    if ($_request['mode'] == 'archive') {
                        $success_msg = "アーカイブしました。";
                    }
                }

                if ($_request['mode'] == 'delete') {
                    //削除はチェックなし
                    $this_sess['mode'] = "delete";
                }
            }

            if(_count($err_msg) == 0){

                // *****************************
                // 招待情報
                // *****************************
                $this_sess['event_syoutai_yotei_time'] = "";
                for ($idx=0; $idx <= 5; $idx++) {
                    if(trim($this_sess['syoutai_ymd_'.$idx])!=""){
                        for ($i=0; $i < 10; $i++) {
                            if(trim($this_sess['syoutai_time_'.$idx."_".$i]) != ""){
                                if($this_sess['event_syoutai_yotei_time']!="") $this_sess['event_syoutai_yotei_time'] .= "#";
                                $this_sess['event_syoutai_yotei_time'] .= trim($this_sess['syoutai_ymd_'.$idx])." ".trim($this_sess['syoutai_time_'.$idx."_".$i]);
                            }
                        }
                    }
                }

                // *****************************
                // 来場情報
                // *****************************
                $this_sess['event_raijyou_yotei_time'] = "";
                for ($idx=0; $idx <= 5; $idx++) {
                    if(trim($this_sess['raijyou_ymd_'.$idx])!=""){
                        for ($i=0; $i < 10; $i++) {
                            if(trim($this_sess['raijyou_time_'.$idx."_".$i]) != ""){
                                if($this_sess['event_raijyou_yotei_time']!="") $this_sess['event_raijyou_yotei_time'] .= "#";
                                $this_sess['event_raijyou_yotei_time'] .= trim($this_sess['raijyou_ymd_'.$idx])." ".trim($this_sess['raijyou_time_'.$idx."_".$i]);
                            }
                        }
                    }
                }

                _query($conn,'begin');

                //**************************************************
                //新規の場合新ID発番
                //**************************************************
                if( $this_sess['mode'] == "insert" ){
                    $max_recs = _select( "select coalesce(max(substring(event_id,2)),'0') as max_id from m_event");
                    $this_sess['id'] = sprintf("e%04d", $max_recs[0]['max_id'] + 1 );
                }

                $array = array();

                $array['event_name']                 = "'"._as($this_sess['event_name'])."'";
                $array['event_area_shikibetsu_id']                 = "'"._as($this_sess['event_area_shikibetsu_id'])."'";
                $array['event_pulldown_name']                 = "'"._as($this_sess['event_pulldown_name'])."'";
                $array['event_pulldown_disp_flg']                 = _e2z($this_sess['event_pulldown_disp_flg']);
                $array['event_kaijyou_name']                 = "'"._as($this_sess['event_kaijyou_name'])."'";
                $array['event_url_key']                 = "'"._as($this_sess['event_url_key'])."'";
                $array['event_exhibition_url_key']                 = "'"._as($this_sess['event_exhibition_url_key'])."'"; //2021/05/25 Add
                $array['event_kaisai_ymd_st']                 = "'"._as($this_sess['event_kaisai_ymd_st'])."'";
                $array['event_kaisai_ymd_ed']                 = "'"._as($this_sess['event_kaisai_ymd_ed'])."'";
                $array['event_raikainri_ymd_st']                 = "'"._as($this_sess['event_raikainri_ymd_st'])."'";
                $array['event_raikainri_ymd_ed']                 = "'"._as($this_sess['event_raikainri_ymd_ed'])."'";
                $array['event_syoutai_yotei_time']                 = "'"._as($this_sess['event_syoutai_yotei_time'])."'";
                $array['event_raijyou_yotei_time']                 = "'"._as($this_sess['event_raijyou_yotei_time'])."'";
                $array['event_exhibition_name']                 = "'"._as($this_sess['event_exhibition_name'])."'";
                $array['event_title_bgcolor']                 = "'"._as($this_sess['event_title_bgcolor'])."'";
                $array['event_rsignup_start_ymdhi']                 = "'"._as($this_sess['event_rsignup_start_ymdhi'])."'";
                $array['event_rsignup_end_ymdhi']                 = "'"._as($this_sess['event_rsignup_end_ymdhi'])."'";
                $array['event_compare_event_id']                 = "'"._as($this_sess['event_compare_event_id'])."'";


                $array['event_update_date']          = "'".$_now_timestamp."'";

                switch( $this_sess['mode'] ){
                    case 'insert':
                        $array['event_id']           = "'"._as($this_sess['id'])."'";
                        $array['event_insert_date']  = "'".$_now_timestamp."'";
                        _insert( 'm_event', $array);

                        $success_msg = "登録しました。";
                    break;
                    case 'update':
                        $where = "event_id='"._as($this_sess['id'])."'";
                        _update( 'm_event', $array, $where );
                        $success_msg = "変更が完了いたしました。";
                    break;
                    case 'delete':
                        $array = array();
                        $array['event_delete_date']  = "'".$_now_timestamp."'";
                        $where = "event_id='"._as($this_sess['id'])."'";
                        _update( 'm_event', $array, $where );
                        $success_msg = "削除しました。";
                    break;
                }

                //**************************************************
                //m_admin から m_user を作成する
                //**************************************************
                if ( $this_sess['mode'] == 'insert' && _count($err_msg) == 0 ){
                    if( $this_sess['mode'] == "insert" ){
                        $max_recs = _select( "select coalesce(max(substring(user_id,2)),'0') as max_id from m_user");
                        $now_user_id  = $max_recs[0]['max_id'];
                    }

                    $row = 0;
                    $array = array();
                    $array_n = array();
                    $array_m = array();

                    $sql = "";
                    $sql .= " select v_admin.*"."\n";
                    $sql .= "  , syozoku_name"."\n";
                    $sql .= " from v_admin"."\n";
                    $sql .= " left join m_syozoku on (admin_syozoku_id = syozoku_id and syozoku_delete_date is null)"."\n";
                    $sql .= " where admin_delete_date is null"."\n";
                    $sql .= " and admin_id != 'a0000001'"."\n"; //システム管理者は除く
                    $sql .= " order by admin_id asc"."\n";
                    $result = _query( $conn, $sql );
                    while( $rec = _fetchArray( $result, $row ) ){
                        $now_user_id++;
                        $user_id  = sprintf("u%08d", $now_user_id);

                        $array['user_id']                  = "'"._as($user_id)."'"; //ユーザID（u000000001）',
                        $array['user_event_id']            = "'"._as($this_sess['id'])."'"; //イベントID（e0001）',
                        $array['user_vip_flg']             = 0; //VIPフラグ（1:VIP）',
                        $array['user_big_cate']            = 7; //大分類 (AC社員)',
                        $array['user_mid_cate']               = _e2n($rec['admin_mid_cate']); //中分類',
                        $array['user_yakusyoku']               = "'"._as($rec['admin_yakusyoku'])."'"; //役職',
                        $array['user_kigyou_name']         = "'"._as('株式会社日本アクセス')."'"; //企業名',
                        $array['user_kigyou_name_kana']    = "'"._as('カブシキガイシャニッポンアクセス')."'"; //企業名カナ',
                        $array['user_busyo']               = "'"._as($rec['syozoku_name'])."'"; //部署',
                        $array['user_pass']                = "'"._as($rec['admin_login_pass'])."'"; //部署',
                        $array['user_admin_id']            = "'"._as($rec['admin_id'])."'"; //担当者ID（a0000001）',
                        $array['user_web']                 = 1; //WEB招待（1:WEB招待者）',
                        $array['user_mail_send_kbn']       = 1; //'PASS設定URLメール送信区分(0:未送信、1:送信済み、2:送信エラー)',
                        $array['user_syounin_flg']         = 1; //'WEB招待の承認フラグ(0:未承認、1:承認済み)',
                        $array['user_company_id']          = _e2n($rec['admin_company_id']); // 企業ID
                        $array['user_insert_date']         = "'".$_now_timestamp."'"; //作成日時
                        $array['user_update_date']         = "'".$_now_timestamp."'"; //更新日時',
                        _insert( 'm_user', $array);

                        $array_n['un_user_id']             = "'"._as($user_id)."'"; //ユーザID（u000000001）',
                        $array_n['un_user_name']           = "'"._as($rec['admin_name'])."'"; //'氏名',
                        _insert( 'm_uname', $array_n);

                        $array_m['um_user_id']             = "'"._as($user_id)."'"; //ユーザID（u000000001）',
                        $array_m['um_user_mail']           = "'"._as($rec['admin_mail'])."'"; //メールアドレス',
                        $array_m['um_user_login_id']       = "'"._as($rec['admin_mail'])."'"; // ログインid',
                        _insert( 'm_umail', $array_m);

                        $row++;
                    }
                    _freeResult( $result );

                    if ( $row > 0 ){
                        $success_msg .= "<br>AC社員を ".number_format($row)."件 来場者に登録しました。";
                    }

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
                }else{
                    _query( $conn, "commit" );
                    header('Location: index.php?page=event_list&sess_no_init=1');//OK1
                    exit();
                }
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
            $sql .= " from m_event ";
            $sql .= " where ";
            $sql .= "     event_delete_date is null";
            $sql .= "     and event_id='"._as($_request['id'])."'";
            $main_rec = _select($sql);

            // *****************************
            // 招待情報
            // *****************************
            $syoutaiArr = array();
            $wArr = explode("#", $main_rec[0]['event_syoutai_yotei_time'] );
            for ($i=0; $i < count($wArr); $i++) {
                $flds = explode(" ", $wArr[$i]);
                $ymd = $flds[0];
                $time = $flds[1];
                if( _count($syoutaiArr[$ymd]) == 0){
                    $syoutaiArr[$ymd] = array();
                }
                $syoutaiArr[$ymd][] = $time;
            }
            //5日分に整える
            for ($i=_count($syoutaiArr); $i < 5 ; $i++) {
                $syoutaiArr['dmy_'.$i] = array('','','','','','','','','','');
            }
            //時間帯10個に整える
            foreach ($syoutaiArr as $ymd => $tims) {
                for ($i=_count($syoutaiArr[$ymd]); $i < 10; $i++) {
                    $syoutaiArr[$ymd][] = "";
                }
            }
            //assignの形に
            $idx = 0;
            foreach ($syoutaiArr as $ymd => $tims) {
                if(substr($ymd,0,4)=="dmy_"){
                    $main_rec[0]['syoutai_ymd_'.$idx] = "";
                }else{
                    $main_rec[0]['syoutai_ymd_'.$idx] = $ymd;
                }
                for ($i=0; $i < _count($syoutaiArr[$ymd]); $i++) {
                    $main_rec[0]['syoutai_time_'.$idx."_".$i] = $syoutaiArr[$ymd][$i];
                }
                $idx++;
            }

            // *****************************
            // 来場情報
            // *****************************
            $raijyouArr = array();
            $wArr = explode("#", $main_rec[0]['event_raijyou_yotei_time'] );
            for ($i=0; $i < count($wArr); $i++) {
                $flds = explode(" ", $wArr[$i]);
                $ymd = $flds[0];
                $time = $flds[1];
                if( _count($raijyouArr[$ymd]) == 0){
                    $raijyouArr[$ymd] = array();
                }
                $raijyouArr[$ymd][] = $time;
            }
            //5日分に整える
            for ($i=_count($raijyouArr); $i < 5 ; $i++) {
                $raijyouArr['dmy_'.$i] = array('','','','','','','','','','');
            }
            //時間帯10個に整える
            foreach ($raijyouArr as $ymd => $tims) {
                for ($i=_count($raijyouArr[$ymd]); $i < 10; $i++) {
                    $raijyouArr[$ymd][] = "";
                }
            }
            //assignの形に
            $idx = 0;
            foreach ($raijyouArr as $ymd => $tims) {
                if(substr($ymd,0,4)=="dmy_"){
                    $main_rec[0]['raijyou_ymd_'.$idx] = "";
                }else{
                    $main_rec[0]['raijyou_ymd_'.$idx] = $ymd;
                }
                for ($i=0; $i < _count($raijyouArr[$ymd]); $i++) {
                    $main_rec[0]['raijyou_time_'.$idx."_".$i] = $raijyouArr[$ymd][$i];
                }
                $idx++;
            }


            if( $main_rec[0]['event_rsignup_start_ymdhi'] != ""){
                list($w_ymd,$w_hi) = explode(" ", $main_rec[0]['event_rsignup_start_ymdhi'],2);
                $main_rec[0]['event_rsignup_start_ymd'] = $w_ymd;
                list($w_hh,$w_ii) = explode(":", $w_hi,2);
                $main_rec[0]['event_rsignup_start_hh'] = $w_hh;
                $main_rec[0]['event_rsignup_start_ii'] = $w_ii;
            }

            if( $main_rec[0]['event_rsignup_end_ymdhi'] != ""){
                list($w_ymd,$w_hi) = explode(" ", $main_rec[0]['event_rsignup_end_ymdhi'],2);
                $main_rec[0]['event_rsignup_end_ymd'] = $w_ymd;
                list($w_hh,$w_ii) = explode(":", $w_hi,2);
                $main_rec[0]['event_rsignup_end_hh'] = $w_hh;
                $main_rec[0]['event_rsignup_end_ii'] = $w_ii;
            }

            $this_sess = $main_rec[0];
            $this_sess['id'] = $main_rec[0]['event_id'];
            $this_sess['mode'] = "update";
        }else{
            // 新規登録

            // *****************************
            // 招待情報
            // *****************************
            for ($idx=0; $idx <= 5; $idx++) {
                $this_sess['syoutai_ymd_'.$idx] = "";
                for ($i=0; $i < 10; $i++) {
                    $this_sess['syoutai_time_'.$idx."_".$i] = "";
                }
            }

            // *****************************
            // 来場情報
            // *****************************
            for ($idx=0; $idx <= 5; $idx++) {
                $this_sess['raijyou_ymd_'.$idx] = "";
                for ($i=0; $i < 10; $i++) {
                    $this_sess['raijyou_time_'.$idx."_".$i] = "";
                }
            }

            $this_sess['event_title_bgcolor'] = "#b3dfff";

            $this_sess['mode'] = "insert";
        }

        $this_sess['token'] = $token;
    }

    _setAssign($blade,$this_sess);

    $_conf_hh = array();
    for ($i=0; $i <= 23; $i++) {
        $_conf_hh[ sprintf("%02d",$i) ] = sprintf("%02d",$i);
    }
    $blade->assign('_conf_hh',$_conf_hh);

    $_conf_mm = array();
    for ($i=0; $i <= 59; $i++) {
        $_conf_mm[ sprintf("%02d",$i) ] = sprintf("%02d",$i);
    }
    $blade->assign('_conf_mm',$_conf_mm);

    $_wrk_event_area_shikibetsu_id = array();
    foreach ($_conf_event_area_shikibetsu_id as $key => $value) {
        $_wrk_event_area_shikibetsu_id[$key] = $key."（".$value."）";
    }
    $blade->assign('_wrk_event_area_shikibetsu_id',$_wrk_event_area_shikibetsu_id);

    $sql = 'select event_id, event_name, event_pulldown_name from m_event where event_delete_date is null';
    if (!empty($this_sess['id'])){
        $sql .= " and event_id !='". _as($this_sess['id']) ."'";
    }

    $recs = _select($sql);
    $compare_events = [];
    foreach ($recs as $row) {
        $compare_events[$row['event_id']] = $row['event_pulldown_name'];
    }
    $blade->assign('compare_events', $compare_events);

    $contents_title = "イベント管理 詳細";
    $active_menu = "event_list";
    $contents_tpl = "event_edit";
