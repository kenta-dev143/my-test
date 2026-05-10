<?php

    //(やり直すなら以下を実行してから)
    // update m_user set user_syoutai_id = null;
    // delete from m_syoutai;
    // delete from m_sname;
    // delete from m_smail;

    $project_name_prefix = "api_";
    require( "../lib/environment.php" );
    require( "../lib/Smarty.class.php" );
    require( "../lib/UserSmarty.php" );
    require( "../lib/lang.php" );
    require( "../lib/inc.php" );
    require( "../lib/check.php" );
    require( "../lib/project.php" );

    set_time_limit(600); //10分起動
    ini_set('memory_limit',"1024M"); //メモリ拡大

    //DB接続
    $conn = _dbConnect();

    _query($conn,"begin");

    $now = date('Y-m-d H:i:s');
    $row = 0;

    $sql = "";
    $sql .= " select"."\n";
    $sql .= "    m_user.user_id as user_id"."\n";
    $sql .= "   ,m_uname.un_user_name as user_name"."\n";
    $sql .= "   ,m_uname.un_user_name_kana as user_name_kana"."\n";
    $sql .= "   ,m_user.user_vip_flg as user_vip_flg"."\n";
    $sql .= "   ,m_user.user_big_cate as user_big_cate"."\n";
    $sql .= "   ,m_user.user_mid_cate as user_mid_cate"."\n";
    $sql .= "   ,m_user.user_kigyou_name as user_kigyou_name"."\n";
    $sql .= "   ,m_user.user_kigyou_name_kana as user_kigyou_name_kana"."\n";
    $sql .= "   ,m_user.user_busyo as user_busyo"."\n";
    $sql .= "   ,m_user.user_yakusyoku as user_yakusyoku"."\n";
    $sql .= "   ,m_umail.um_user_mail as user_mail"."\n";
    $sql .= "   ,m_umail.um_user_login_id as user_login_id"."\n";
    $sql .= "   ,m_user.user_biko as user_biko"."\n";
    $sql .= " from m_user"."\n";
    $sql .= " inner join m_uname on (m_uname.un_user_id = m_user.user_id)"."\n";
    $sql .= " inner join m_umail on (m_umail.um_user_id = m_user.user_id)"."\n";
    $sql .= " inner join ( "."\n";
    $sql .= "     select max(v_user.user_id) as max_user_id from v_user  "."\n";
    $sql .= "     where user_delete_date is null and user_big_cate in (1,2,3,4) and user_mail != '' and user_login_id != '' "."\n";
    $sql .= "       and user_event_id in ('e0001','e0002')"; //2021春のみ
    $sql .= "     group by user_login_id "."\n";
    $sql .= " ) as g_user on (g_user.max_user_id = m_user.user_id)"."\n";
    $sql .= " where user_delete_date is null"."\n";
    $sql .= "   and user_big_cate in (1,2,3,4)"."\n";
    $sql .= "   and um_user_mail != ''"."\n";
    $sql .= "   and um_user_login_id != ''"."\n";
    $sql .= "   and user_event_id in ('e0001','e0002')"; //2021春のみ
    $sql .= " order by user_id asc"."\n";

    $result = _query($conn, $sql);
    while( $rec = _fetchArray( $result, $row ) ){

        $syoutai_id = sprintf("s%08d", ($row + 1) );

        $array_s = array();
        $array_s['syoutai_id']               = "'"._as($syoutai_id)."'";
        if ( $rec['user_vip_flg'] != '' ){
            $array_s['syoutai_vip_flg']          = _as($rec['user_vip_flg']);
        }
        if ( $rec['user_big_cate'] != '' ){
            $array_s['syoutai_big_cate']         = _as($rec['user_big_cate']);
        }
        if ( $rec['user_mid_cate'] != '' ){
            $array_s['syoutai_mid_cate']         = _as($rec['user_mid_cate']);
        }
        $array_s['syoutai_kigyou_name']      = "'"._as($rec['user_kigyou_name'])."'";
        $array_s['syoutai_kigyou_name_kana'] = "'"._as($rec['user_kigyou_name_kana'])."'";
        $array_s['syoutai_busyo']            = "'"._as($rec['user_busyo'])."'";
        $array_s['syoutai_yakusyoku']        = "'"._as($rec['user_yakusyoku'])."'";
        $array_s['syoutai_biko']             = "'"._as($rec['user_biko'])."'";
        $array_s['syoutai_insert_date']      = "'".$now."'";
        $array_s['syoutai_update_date']      = "'".$now."'";
        _insert('m_syoutai', $array_s);
        
        $array_sn = array();
        $array_sn['sn_syoutai_id']           = "'"._as($syoutai_id)."'";
        $array_sn['sn_syoutai_name']         = "'"._as($rec['user_name'])."'";
        $array_sn['sn_syoutai_name_kana']    = "'"._as($rec['user_name_kana'])."'";
        _insert('m_sname', $array_sn);

        $array_sm = array();
        $array_sm['sm_syoutai_id']           = "'"._as($syoutai_id)."'";
        $array_sm['sm_syoutai_mail']         = "'"._as($rec['user_mail'])."'";
        $array_sm['sm_syoutai_login_id']     = "'"._as($rec['user_login_id'])."'";
        _insert('m_smail', $array_sm);

        $sql = "";
        $sql .= " update m_user"."\n";
        $sql .= " inner join m_umail on (m_umail.um_user_id = m_user.user_id)"."\n";
        $sql .= " set user_syoutai_id = '"._as($syoutai_id)."'"."\n";
        $sql .= " where um_user_login_id = '"._as($rec['user_login_id'])."'";
        _query($conn, $sql);
        
        $row++;
    }
    _freeResult( $result );

    $file_dir     = _SYSTEM_ROOT_DIR.'/upfile/new_tmp/';
    $file_handler = fopen($file_dir . "dupli_data_" . date("Ymd_Hi") . ".csv","w");

    $w_csv  = "";
    $w_csv .= ""  . "\"ユーザID\"";
    $w_csv .= "," . "\"イベント\"";
    $w_csv .= "," . "\"担当者ID\"";
    $w_csv .= "," . "\"招待者ID\"";
    $w_csv .= "," . "\"ユーザ名\"";
    $w_csv .= "," . "\"ユーザ名カナ\"";
    $write_str = mb_convert_encoding($w_csv,"SJIS-WIN",_ENCODING_SRC) . "\r\n";
    fwrite( $file_handler , $write_str);

    $sql = "";
    $sql .= " select *"."\n";
    $sql .= " from v_user"."\n";
    $sql .= " inner join ( select user_login_id as dup_login_id, count(user_id) as cnt from v_user where user_delete_date is null and user_big_cate in (1,2,3,4) and user_event_id in ('e0001','e0002') and user_mail != '' and user_login_id != '' group by user_login_id ) as g_user on (g_user.dup_login_id = v_user.user_login_id)"."\n";
    $sql .= " inner join m_event on (m_event.event_id = v_user.user_event_id)"."\n";
    $sql .= " where cnt > 1"."\n";
    $sql .= "  and user_big_cate in (1,2,3,4)"."\n";
    $sql .= "  and user_event_id in ('e0001','e0002')"; //2021春のみ
    $sql .= " order by user_login_id, user_id"."\n";
    $dup_recs = _select( $sql );
    for ($i=0; $i < _count($dup_recs);  $i++) { 
        $w_csv  = "";
        $w_csv .= ""  . "\""._as( $dup_recs[$i]['user_id'] )."\"";
        $w_csv .= "," . "\""._as( $dup_recs[$i]['event_pulldown_name'] )."\"";
        $w_csv .= "," . "\""._as( $dup_recs[$i]['user_admin_id'] )."\"";
        $w_csv .= "," . "\""._as( $dup_recs[$i]['user_syoutai_id'] )."\"";
        $w_csv .= "," . "\""._as( $dup_recs[$i]['user_name'] )."\"";
        $w_csv .= "," . "\""._as( $dup_recs[$i]['user_name_kana'] )."\"";

        $write_str = mb_convert_encoding($w_csv,"SJIS-WIN",_ENCODING_SRC) . "\r\n";
        fwrite( $file_handler , $write_str);

    }
    fclose( $file_handler );

    echo ($row + 1)."件 招待者データの作成が完了しました。"."<br>";
    echo $now."<br>";

    _query($conn,"commit");

    //DB切断
    _dbDisconnect( $conn );
