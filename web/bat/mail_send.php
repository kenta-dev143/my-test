<?php
    //毎分と毎分３０秒後の実行で、実質３０秒ごとに実行
    // [本番環境]
    // * * * * * cd /var/www/vhosts/tenjikai.nippon-access.co.jp/bat; /usr/bin/php mail_send.php >/var/log/mail_send.log 2>&1
    // * * * * * sleep 30; cd /var/www/vhosts/tenjikai.nippon-access.co.jp/bat; /usr/bin/php mail_send.php >/var/log/mail_send.log 2>&1
    // [テスト環境]
    // * * * * * cd /var/www/vhosts/test-tenjikai.nippon-access.co.jp/bat; /usr/bin/php mail_send.php >/var/log/mail_send.log 2>&1
    // * * * * * sleep 30; cd /var/www/vhosts/test-tenjikai.nippon-access.co.jp/bat; /usr/bin/php mail_send.php >/var/log/mail_send.log 2>&1
    //
    // http://localhost74/access_tenjikai_sys/web/bat/mail_send.php

    //なぜかtenjikaiのAWSでは存在しない$_SERVER['HTTP_HOST']を参照するとバッチでエラーになるので
    if(!isset($_SERVER['HTTP_HOST'])){
        $_SERVER['HTTP_HOST']="";
    }

    // ******************************************************************************************************
    // INCLUDE FILES
    // ******************************************************************************************************
    $project_name_prefix = "bat_";
    require( "../lib/environment.php" );
    require( "../lib/Smarty.class.php" );
    require( "../lib/UserSmarty.php" );
    require( "../lib/lang.php" );
    require( "../lib/inc.php" );
    require( "../lib/check.php" );
    require( "../lib/picture.php" );
    require( "../lib/project.php" );

    $conn = null;
    $head_rec = array();

    // エラーハンドラ関数
    function myErrorHandler($errno, $errstr, $errfile, $errline)
    {
        global $conn, $_now_timestamp, $head_rec;

        if($conn != null && $head_rec['mailhd_id']!=""){

            if (!(error_reporting() & $errno)) {
                // error_reporting 設定に含まれていないエラーコードのため、
                // 標準の PHP エラーハンドラに渡されます。
                return;
            }

            switch ($errno) {
            case E_USER_ERROR:
                $err_no_str = "E_USER_ERROR";
                break;
            case E_USER_WARNING:
                $err_no_str = "E_USER_WARNING";
                break;

            case E_USER_NOTICE:
                $err_no_str = "E_USER_NOTICE";
                break;

            default:
                $err_no_str = "OTHER";
                break;
            }

            $date = date("Y/m/d H:i:s");
            $yobi = _getYoubi(substr($date,0,10));

            ob_start();
            echo "\n-------------------\n";
            echo $date."(".$yobi.") ".$err_no_str." [".$errno."]". $errstr."\n";
            echo "\n-------------------\n";
            debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

            $deb_tra_str .= ob_get_contents();
            ob_end_clean();
            $deb_tra_str .= "\n\n";


            //ステータスを「9:送信エラー」に更新
            _query($conn,"begin");

            $array = array();
            $array['mailhd_status'] = "9";
            $array['mailhd_error_detail'] = "'"._as($deb_tra_str)."'";
            $array['mailhd_update_date'] = "'".$_now_timestamp."'";
            $where = "mailhd_id = "._as($head_rec['mailhd_id']);
            _update("t_mail_head",$array,$where);

            _query($conn,"commit");

            //DB切断
            _dbDisconnect( $conn );
        }

        exit();



    }

    $old_error_handler = set_error_handler("myErrorHandler");

    set_time_limit(0); //時間制限なし

    if($_SERVER['HTTP_HOST']!=""){
        echo "Start ".date("Y/m/d H:i:s")."<br>";
    }

    //DB接続
    $conn = _dbConnect();

    $sql = "";
    $sql .= "select * from t_mail_head ";
    $sql .= " where";
    $sql .= " mailhd_delete_date is null";
    $sql .= " and mailhd_status=0";
    $sql .= " and mailhd_yoyaku_ymdhi <= '".date("Y/m/d H:i")."'";
    $sql .= " order by mailhd_yoyaku_ymdhi asc, mailhd_id asc";
    $sql .= " limit 0 , 1";
    $head_recs = _select($sql);

    if( _count($head_recs) > 0 ){
        $head_rec = $head_recs[0];

        //ステータスを「1:送信処理中」に更新
        _query($conn,"begin");

        $array = array();
        $array['mailhd_status'] = "1";
        $array['mailhd_update_date'] = "'".$_now_timestamp."'";
        $where = "mailhd_id = "._as($head_rec['mailhd_id']);
        _update("t_mail_head",$array,$where);

        _query($conn,"commit");

        $touroku_admin_recs = _select("select * from v_admin where admin_id='"._as($head_rec['mailhd_insert_admin_id'])."'");

        // *******************************
        //メイン処理
        // *******************************
        if ( $head_rec['mailhd_mailt_key'] == 'pass_set_annai_admin'){
            // 担当者パスワード設定URL通知
            $sql = "";
            $sql .= " select * "."\n";
            $sql .= " from t_mail_list"."\n";
            $sql .= " inner join v_admin on (t_mail_list.maills_user_id = v_admin.admin_id and v_admin.admin_delete_date is null)"."\n";
            $sql .= " left join m_syozoku on (m_syozoku.syozoku_id = v_admin.admin_syozoku_id and m_syozoku.syozoku_delete_date is null)"."\n";
            $sql .= " where maills_delete_date is null"."\n";
            $sql .= "  and maills_mailhd_id = "._as($head_rec['mailhd_id'])."\n";
            $result = _query( $conn, $sql );

            $row = 0;
            while( $rec = _fetchArray( $result, $row ) ){
                $msm = new UserBlade();


                $pass_change_url = _SYSTEM_ROOT_URLS."/admin/"."?page=admin_pass_set&setpw="._urlCodeEncode($rec['admin_id']."#_NEED_PASS_SET_");

                $data_rec = array();
                $data_rec['tantou_syozoku']    = $rec['syozoku_name'];
                $data_rec['tantou_name']       = $rec['admin_name'];
                $data_rec['tantou_mail']       = $rec['admin_mail'];
                $data_rec['pass_set_url']      = $pass_change_url;
                $data_rec['admin_url']         = _SYSTEM_ROOT_URLS."/admin/";
                _setAssign($msm,$data_rec);

                //subjectとbodyを区切り文字で繋いでまとめて文字列にし、％タグをBladeタグに変換
                $all = $head_rec['mailhd_subject']."___###kugiri###___".$head_rec['mailhd_body'];
                $all = str_replace('&lt;%', '<%', $all);
                $all = str_replace('%&gt;', '%>', $all);
                $all = str_replace('<%', '{{ $', $all);
                $all = str_replace('%>', ' }}', $all);
                $tpl_source = $all;

                //テンプレ文字列からfetch実行
                $_buff = $msm->fetchString($tpl_source);

                list($title,$body) = explode("___###kugiri###___", $_buff);

                $attach = array();
                $adHeader = array();

                // if( $head_rec['mailhd_test_send_flg']!=1 ){
                //     //Return-Path設定
                //     $adHeader['Return-Path'] = "retadr-".$rec['admin_id']."@"._RETURN_PATH_MAIL_DOMAIN;
                // }

                _accessSendMail( _SYSTEM_INFO_MAIL, _SYSTEM_INFO_MAIL_NAME, $rec['maills_mail_address'], $rec['admin_name']." 様", $title, $body,$attach, $adHeader);

                unset($msm);

                $row++;
            }
            _freeResult( $result );
        }else{
            // m_user向け汎用
            $sql = "";
            $sql .= "select * from t_mail_list ";
            $sql .= " join v_user on (t_mail_list.maills_user_id = v_user.user_id)";
            $sql .= " join m_event on (t_mail_list.maills_event_id = m_event.event_id)";
            $sql .= " left join v_admin on (v_admin.admin_id = v_user.user_admin_id)";
            $sql .= " left join m_syozoku on (m_syozoku.syozoku_id = v_admin.admin_syozoku_id) ";
            $sql .= " where";
            $sql .= " maills_delete_date is null";
            $sql .= " and maills_mailhd_id = "._as($head_rec['mailhd_id']);
            $sql .= " and user_delete_date is null";

            $result = _query( $conn, $sql );

            $row = 0;
            while( $rec = _fetchArray( $result, $row ) ){

                $msm = new UserBlade();

                $pass_change_url = _SYSTEM_ROOT_URLS."/mypage/".$rec['event_url_key']."/?page=login&setpw="._urlCodeEncode($rec['user_login_id']."#_NEED_PASS_SET_");
                $exhibition_url = _SYSTEM_ROOT_URLS."/exhibition/".$rec['event_exhibition_url_key']."/";
                $kigyou_name = empty($rec['user_company_id']) || $rec['user_company_id'] == 1 ? $rec['user_kigyou_name'] : $rec['user_company_name'];

                $data_rec = array();
                $data_rec['event_name']        = $rec['event_name'];
                $data_rec['kigyou_name']       = $kigyou_name;
                $data_rec['name']              = $rec['user_name'];
                $data_rec['mypage_url']        = _SYSTEM_ROOT_URLS."/mypage/".$rec['event_url_key']."/";
                $data_rec['login_id']          = $rec['user_login_id'];
                $data_rec['mypage_manual_url'] = _SYSTEM_ROOT_URLS."/mypage/user_login_info.pdf";
                $data_rec['tantou_syozoku']    = $rec['syozoku_name'];
                $data_rec['tantou_name']       = $rec['admin_name'];
                $data_rec['tantou_mail']       = $rec['admin_mail'];
                $data_rec['pass_set_url']      = $pass_change_url;
                $data_rec['exhibition_url']    = $exhibition_url;
                $qr_link = null;
                if($head_rec['mailhd_mailt_key']=="pass_set_annai"
                    || $head_rec['mailhd_mailt_key']=="pass_set_annai2"
                    || $head_rec['mailhd_mailt_key']=="signup_syounin_real") {
                    $qr_link = _create_qr_link($rec['user_event_id'], $rec['user_id']);
                    $data_rec['qr_url'] = $qr_link;
                }

                _setAssign($msm,$data_rec);

                //subjectとbodyを区切り文字で繋いでまとめて文字列にし、％タグをBladeタグに変換
                $all = $head_rec['mailhd_subject']."___###kugiri###___".$head_rec['mailhd_body'];
                $all = str_replace('&lt;%', '<%', $all);
                $all = str_replace('%&gt;', '%>', $all);
                $all = str_replace('<%', '{{ $', $all);
                $all = str_replace('%>', ' }}', $all);
                $tpl_source = $all;

                //テンプレ文字列からfetch実行
                $_buff = $msm->fetchString($tpl_source);

                list($title,$body) = explode("___###kugiri###___", $_buff);

                $attach = array();

                $adHeader = array();

                if( ($head_rec['mailhd_mailt_key']=="pass_set_annai" || $head_rec['mailhd_mailt_key']=="pass_set_annai_web_only"
                     || $head_rec['mailhd_mailt_key']=="pass_set_annai2" || $head_rec['mailhd_mailt_key']=="pass_set_annai_web_only2"
                     || $head_rec['mailhd_mailt_key']=="signup_syounin_real" || $head_rec['mailhd_mailt_key']=="signup_syounin_web")
                    && $head_rec['mailhd_test_send_flg']!=1){
                    //PASS設定通知で、テスト送信でなければ

                    // //担当者にも通知
                    // if($rec['admin_mail']!=""){
                    //     if( _emailCheck($rec['admin_mail'],'') != false ){
                    //         $adHeader['Cc'] .= $rec['admin_mail'];
                    //     }
                    // }
                    // if($rec['admin_mail2']!=""){
                    //     if( _emailCheck($rec['admin_mail2'],'') != false ){
                    //         if($adHeader['Cc']!="") $adHeader['Cc'] .= ",";
                    //         $adHeader['Cc'] .= $rec['admin_mail2'];
                    //     }
                    // }

                    //Return-Path設定
                    $adHeader['Return-Path'] = "retadr-".$rec['user_id']."@"._RETURN_PATH_MAIL_DOMAIN;

                    //送信済みに
                    // _query($conn,"begin");
                    $array = array();
                    $array['user_mail_send_kbn'] = "1";
                    $array['user_update_date'] = "'".$_now_timestamp."'";
                    $where = "user_id = '"._as($rec['user_id'])."'";
                    _update("m_user",$array,$where);
                    // _query($conn,"commit");
                }


                _accessSendMail( _SYSTEM_INFO_MAIL, _SYSTEM_INFO_MAIL_NAME, $rec['maills_mail_address'], $rec['user_name']." 様", $title, $body,$attach, $adHeader);

                unset($msm);

                // if( ($head_rec['mailhd_mailt_key']=="pass_set_annai" || $head_rec['mailhd_mailt_key']=="pass_set_annai_web_only"
                //      || $head_rec['mailhd_mailt_key']=="pass_set_annai2" || $head_rec['mailhd_mailt_key']=="pass_set_annai_web_only2"
                //      || $head_rec['mailhd_mailt_key']=="signup_syounin_real" || $head_rec['mailhd_mailt_key']=="signup_syounin_web")
                //     && $head_rec['mailhd_test_send_flg']!=1 && $rec['user_big_cate']!=7){
                //承認通知の場合はCC的に担当者に送信はしない
                if( ($head_rec['mailhd_mailt_key']=="pass_set_annai" || $head_rec['mailhd_mailt_key']=="pass_set_annai_web_only"
                     || $head_rec['mailhd_mailt_key']=="pass_set_annai2" || $head_rec['mailhd_mailt_key']=="pass_set_annai_web_only2")
                    && $head_rec['mailhd_test_send_flg']!=1 && $rec['user_big_cate']!=7){

                    //pass設定案内系で、テスト送信でなく、対象ユーザーがAC社員でなけでば
                    //担当者にも通知

                    $msm2 = new UserBlade();

                    //2021/06/03 Mod ----------- Before ------------
                    // $msm2->assign('_SYSTEM_ROOT_URLS',_SYSTEM_ROOT_URLS);
                    // $msm2->assign('user_rec', $rec);
                    // $msm2->assign('admin_rec', $rec);
                    // $msm2->assign('touroku_admin_rec', $touroku_admin_recs[0]);
                    // $msm2->assign('event_rec',$rec);

                    // // template set
                    // $mail_tpl = "syoutai_tsuuchi.tpl";

                    // $title_and_body = _smartyFetch( $msm2, _SYSTEM_ROOT_DIR . '/mail/' . $mail_tpl );
                    // $w_arr = explode ("\n", $title_and_body, 2 );
                    // $title = array_shift( $w_arr );
                    // if( substr( $title, strlen( $title ) - 1, 1 ) == "\r" ){
                    //     $title = substr( $title, 0, strlen( $title ) - 1 );
                    // }
                    // $body = join( "\n", $w_arr );
                    //2021/06/03 Mod ----------- After ------------

                    $data_rec = array();
                    $data_rec['event_name']        = $rec['event_name'];
                    $data_rec['tantou_syozoku']    = $rec['syozoku_name'];
                    $data_rec['tantou_name']       = $rec['admin_name'];
                    $data_rec['tantou_mail']       = $rec['admin_mail'];
                    $data_rec['touroku_tantou_name']       = $touroku_admin_recs[0]['admin_name'];
                    $data_rec['kigyou_name']       = $rec['user_company_name'];
                    $data_rec['name']              = $rec['user_name'];
                    $data_rec['admin_url']         = _SYSTEM_ROOT_URLS . "/admin/";
                    $data_rec['pass_reset_url']    = _SYSTEM_ROOT_URLS . "/admin/?page=pass_reissue";
                    if ($qr_link !== null) {
                        $data_rec['qr_url']       = $qr_link;
                    }

                    _setAssign($msm2,$data_rec);

                    // template set
                    $mail_tpl = "syoutai_tsuuchi.tpl";

                    $ret = _bladeFetchFromMailTplDB( $msm2, $mail_tpl );
                    $title = $ret['subject'];
                    $body = $ret['body'];
                    //2021/06/03 Mod ----------- End ------------

                    $attach = array();
                    //_sendMailEx( _SYSTEM_INFO_MAIL, _SYSTEM_INFO_MAIL_NAME, $real_user_mail_addr, $this_sess['user_name']." 様", $title, $body,$attach );
                    _accessSendMail( _SYSTEM_INFO_MAIL, _SYSTEM_INFO_MAIL_NAME, $rec['admin_mail'], $rec['admin_name']." 様", $title, $body,$attach );

                    if($rec['admin_mail2']!=""){
                        _accessSendMail( _SYSTEM_INFO_MAIL, _SYSTEM_INFO_MAIL_NAME, $rec['admin_mail2'], $rec['admin_name']." 様", $title, $body,$attach );
                    }
                    unset($msm2);
                }

                $row++;
            }

            _freeResult( $result );

        }
        // *******************************
        // *******************************


        //ステータスを「2:送信処理完了」に更新
        _query($conn,"begin");

        $array = array();
        $array['mailhd_status'] = "2";
        $array['mailhd_update_date'] = "'".$_now_timestamp."'";
        $where = "mailhd_id = "._as($head_rec['mailhd_id']);
        _update("t_mail_head",$array,$where);

        _query($conn,"commit");

    }

    //DB切断
    _dbDisconnect( $conn );

    if($_SERVER['HTTP_HOST']!=""){
        echo "End ".date("Y/m/d H:i:s")."<br>";
    }

    exit();
