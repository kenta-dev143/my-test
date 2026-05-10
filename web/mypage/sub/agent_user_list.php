<?php
    if( !defined("_PROJECT_DISP_NAME") ){
        die("System Error");
    }

    if( $_SESSION[_PROJECT_NAME]['user_login']['user_id']=="" ){
        die("System Error");
    }

    if($_SESSION[_PROJECT_NAME]['direct_login']=="1"){
        header("Location: "._SYSTEM_ROOT_URLS."/mypage/".$event_rec['event_url_key']."/");
        exit();
    }

    // ******************************************************************************************************
    // 初期値
    // ******************************************************************************************************
    $page = $_request['page'];
    $this_sess = &$_SESSION[_PROJECT_NAME][$page];
    $err_msg = array();

    if ($_request['exec'] === 'qr_send') {
        if (empty($_request['user_id'])) {
            die("System Error");
        }

        $user_id = $_request['user_id'];
        $sql  = "";
        $sql .= " select ";
        $sql .= "   * ";
        $sql .= " from v_user ";
        $sql .= " left join v_admin on (v_admin.admin_id = v_user.user_admin_id) ";
        $sql .= " where ";
        $sql .= "     user_delete_date is null";
        $sql .= "     and user_id='"._as($user_id)."'";
        $user_recs = _select($sql);

        if (count($user_recs) === 0) {
            die("System Error");
        }

        $user = $user_recs[0];
        $qr_link = _create_qr_link($user['user_event_id'], $user['user_id']);

        $event_recs = _select("select * from m_event where event_id='"._as($user['user_event_id'])."' and event_delete_date is null");
        if(_count($event_recs)==0){
            die("System Error");
        }
        $event_rec = $event_recs[0];

        $kigyou_name = $user['user_kigyou_name'];
        if (! empty($user['user_company_id'])) {
            $company_recs = _select(" select * from m_company where company_id = " . $user['user_company_id']);
            if (count($company_recs) === 0) {
                $company = $company_recs[0];
                $kigyou_name = $company['company_name'];
            }
        }

        // smarty set
        $msm = new UserBlade();
        $data_rec = array();
        $data_rec['event_name']         = $event_rec['event_name'];
        $data_rec['kigyou_name']        = $kigyou_name;
        $data_rec['name']               = $user['user_name'];
        $data_rec['tantou_name']        = $user['admin_name'];
        $data_rec['qr_url']            = $qr_link;
        _setAssign($msm,$data_rec);


        // template set
        $mail_tpl = "qr_link_resending.tpl";

        $ret = _bladeFetchFromMailTplDB( $msm, $mail_tpl );

        $title = $ret['subject'];
        $body = $ret['body'];

        $attach = array();

        // 対象ユーザーに送信
        _accessSendMail( _SYSTEM_INFO_MAIL, _SYSTEM_INFO_MAIL_NAME, $user['user_mail'], $user['user_name']."様", $title, $body,$attach );
        // 代理登録者に送信
        _accessSendMail( _SYSTEM_INFO_MAIL, _SYSTEM_INFO_MAIL_NAME, $user['user_agent_mail'], $user['user_name']."様", $title, $body,$attach );

    } else {
        // ******************************************************************************************************
        // 初期
        // ******************************************************************************************************
        unset( $_SESSION[_PROJECT_NAME][$page] );
        unset( $this_sess );
        $this_sess = &$_SESSION[_PROJECT_NAME][$page];
    }

    $sql  = "";
    $sql .= " select ";
    $sql .= "   * ";
    $sql .= " from v_user ";
    $sql .= " left join v_admin on (v_admin.admin_id = v_user.user_admin_id) ";
    $sql .= " where ";
    $sql .= "     user_delete_date is null";
    $sql .= "     and user_id='"._as($_SESSION[_PROJECT_NAME]['user_login']['user_id'])."'";
    $main_rec = _select($sql);
    $main_rec[0]['raijyousya_kbn'] = $_conf_raijyousya_kbn[$main_rec[0]['user_big_cate']];

    $sql  = "";
    $sql .= " select ";
    $sql .= "   * ";
    $sql .= " from v_user ";
    $sql .= " left join v_admin on (v_admin.admin_id = v_user.user_admin_id) ";
    $sql .= " where ";
    $sql .= "     user_delete_date is null";
    $sql .= "     and user_event_id='"._as($event_rec['event_id'])."'";
    $sql .= "     and user_agent_mail='"._as($main_rec[0]['user_login_id'])."'";
    $user_recs = _select($sql);

    $blade->assign('user_recs', $user_recs);

    $contents_tpl = "agent_user_list.html";
