<?php
if (!defined("_PROJECT_DISP_NAME"))
{
    die("System Error");
}

if ($_SESSION[_PROJECT_NAME]['admin_login']['admin_id'] == "")
{
    die('System Error');
}

if ($_SESSION[_PROJECT_NAME]['admin_login']['admin_master_kengen'] != "1")
{
    die('System Error');
}

if ($_SESSION[_PROJECT_NAME]['admin_login']['admin_kyouryoku_kigyou_flg'] == 1)
{
    die('Permission Denied');
}


// ******************************************************************************************************
// 初期値
// ******************************************************************************************************
$page      = $_request['page'];
$this_sess = &$_SESSION[_PROJECT_NAME][$page];
$err_msg   = array();

// ******************************************************************************************************
// CSVダウンロード処理
// ******************************************************************************************************
if ($_request['exec'] == "csv_download")
{

    set_time_limit(180); //3分起動
    ini_set('memory_limit', "1024M"); //メモリ拡大

    $csv_head = '';
    // $csv_head .=  '"SANSANID"'; 2021.05.17 del
    $csv_head .= '"ID"';
    $csv_head .= ',"企業名"';
    $csv_head .= ',"企業表示名"';
    $csv_head .= ',"企業名カナ"';
    $csv_head .= ',"大分類"';
    $csv_head .= ',"ステータス"';
    $csv_head .= ',"差し戻し理由"';
    $csv_head .= ',"承認者名"';
    $csv_head .= ',"更新日時"';
    $csv_head .= "\r\n";

    $w_flnm = "企業承認履歴_" . date("YmdHis") . ".csv";
    header("Content-Disposition: attachment; filename=\"" . mb_convert_encoding($w_flnm, "SJIS-WIN", _ENCODING_SRC) . "\"");
    header("Content-Type: application/octet-stream; name=\"" . $w_flnm . "\"");

    echo mb_convert_encoding($csv_head, "SJIS-WIN", _ENCODING_SRC);

    $sql = "";
    $sql .= " select ";
    $sql .= "   * ";
    $sql .= " from t_company_request ";
    $sql .= " left join v_admin on (v_admin.admin_id = t_company_request.tcr_approver_id) ";
    $sql .= " where t_company_request.tcr_status <> '0'";
    $sql .= " order by t_company_request.tcr_id desc ";

    $result = _query($conn, $sql);

    $row = 0;
    while ($rec = _fetchArray($result, $row))
    {

        $csv_buff = '';
        $csv_buff .= '"' . csvSafe($rec['tcr_id']) . '"';
        $csv_buff .= ',"' . csvSafe($rec['tcr_full_name']) . '"';
        $csv_buff .= ',"' . csvSafe($rec['tcr_display_name']) . '"';
        $csv_buff .= ',"' . csvSafe($rec['tcr_name_kana']) . '"';
        $csv_buff .= ',"' . csvSafe($_conf_big_cate1[$rec['tcr_big_cate']]) . '"';
        $csv_buff .= ',"' . csvSafe($rec['tcr_status'] == '1' ? '承認' : '却下') . '"';
        $csv_buff .= ',"' . csvSafe($rec['tcr_reject_reason']) . '"';
        $csv_buff .= ',"' . csvSafe($rec['admin_name']) . '"';
        $csv_buff .= ',"' . csvSafe($rec['tcr_update_date']) . '"';
        $csv_buff .= "\r\n";
        echo mb_convert_encoding($csv_buff, "SJIS-WIN", _ENCODING_SRC);
        $row++;
    }
    _freeResult($result);

    exit();

} else if ($_request['exec'] == 'save' && isset($this_sess['c_request']))
{
    // ******************************************************************************************************
    // 登録・更新・削除
    // ******************************************************************************************************
    if ($this_sess['token'] != $_request['token'])
    {
        $err_msg[] = 'このデータは処理できませんでした。';
    } elseif (_count($this_sess) <= 0)
    {
        //**** リロードやボタンダブルクリックでの２重登録抑制
        $err_msg[] = 'このデータは既に処理済みです。';
    } else
    {

        if ($_request['mode'] === 'reject')
        {
            $chks = array(
                "reject_reason,差し戻し理由" => "need",
            );

            $err_msg = _check($chks, $_request);
        }

        //**** POST値をセッションにマージ ****
        $this_sess = _array_merge($this_sess, $_request);

        if (_count($err_msg) == 0)
        {

            _query($conn, 'begin');
            $array = array();
            $array['tcr_approver_id'] = "'" . _as($_SESSION[_PROJECT_NAME]['admin_login']['admin_id']) . "'";

            switch ($this_sess['mode'])
            {
                case 'approve':
                    $array['tcr_status'] = 1;
                    $where               = "tcr_id='" . _as($this_sess['id']) . "'";
                    _update('t_company_request', $array, $where);

                    // m_companyを新規登録する
                    $c_request = $this_sess['c_request'];
                    $c_array = array();
                    $c_array['company_name']         = "'" . _as($c_request['tcr_full_name']) . "'";
                    $c_array['company_display_name'] = "'" . _as($c_request['tcr_display_name']) . "'";
                    $c_array['company_name_kana']    = "'" . _as($c_request['tcr_name_kana']) . "'";
                    $c_array['company_big_cate']     = "" . _e2n($c_request['tcr_big_cate']) . "";

                    _insert('m_company', $c_array);

                    _set_flash_message("承認しました。");
                    break;
                case 'reject':
                    $array['tcr_status']        = 2;
                    $array['tcr_reject_reason'] = "'" . _as($this_sess['reject_reason']) . "'";
                    $where                      = "tcr_id='" . _as($this_sess['id']) . "'";
                    _update('t_company_request', $array, $where);

                    _set_flash_message("却下しました。");

                    break;
            }

            $sql = "";
            $sql .= " select ";
            $sql .= "   * ";
            $sql .= " from t_company_request ";
            $sql .= " left join v_admin on (v_admin.admin_id=t_company_request.tcr_request_admin_id) ";
            $sql .= " where t_company_request.tcr_id='" . _as($this_sess['id']) . "'";

            $rec = _select($sql);

            // 承認または差し戻しのメール送信
            $msm = new UserBlade();
            $msm->assign('_SYSTEM_ROOT_URLS',_SYSTEM_ROOT_URLS);

            $data_rec = array();
            $data_rec['admin_name']           = $rec[0]['admin_name'];
            $data_rec['request_date']         = (new DateTime($rec[0]['tcr_insert_date']))->format('Y年m月d日 H:i');
            $data_rec['request_company_name'] = $rec[0]['tcr_full_name'];
            $data_rec['reject_reason']        = $rec[0]['tcr_reject_reason'];
            _setAssign($msm,$data_rec);

            $mail_tpl = "company_request_approve";
            if ($this_sess['mode'] == 'reject') {
                $mail_tpl = "company_request_reject";
            }

            $ret = _bladeFetchFromMailTplDB( $msm, $mail_tpl );
            $title = $ret['subject'];
            $body = $ret['body'];

            $attach = array();
            _accessSendMail( _SYSTEM_INFO_MAIL, _SYSTEM_INFO_MAIL_NAME, $rec[0]['admin_mail'], $rec[0]['admin_name']." さん", $title, $body,$attach );

            _query($conn, 'commit');

            if ($this_sess['next_id'] == '') {
                $_SESSION[_PROJECT_NAME][$page] = array();
                unset($_SESSION[_PROJECT_NAME][$page]);
                unset($this_sess);
                $this_sess = array();

                header('Location: index.php?page=company_request_list&sess_no_init=1');//OK1
                exit;
            }
        }
    }
}

// ******************************************************************************************************
// 初期・完了画面
// ******************************************************************************************************
if ($_request['exec'] != 'save' && _count($err_msg) == 0)
{
    $token = rand();
    unset($_SESSION[_PROJECT_NAME][$page]);
    unset($this_sess);
    $this_sess = &$_SESSION[_PROJECT_NAME][$page];

}

$where = " where tcr_status = 0 ";
if ($_request['id'] != "")
{
    $where .= " and tcr_id <= " . _as($_request['id']) . " ";
}

$sql = "";
$sql .= " select ";
$sql .= "   * ";
$sql .= " from t_company_request ";
$sql .= $where;
$sql .= " order by tcr_id desc ";
$sql .= " limit 2";

$main_rec = _select($sql);
$this_sess['c_request'] = $main_rec[0];
$this_sess['id']        = $main_rec[0]['tcr_id'];

$this_sess['token'] = $token;
$blade->assign('rec', $main_rec[0]);
$blade->assign('next_id', isset($main_rec[1]) ? $main_rec[1]['tcr_id'] : "");
$this_sess['next_id'] = isset($main_rec[1]) ? $main_rec[1]['tcr_id'] : "";

$sql = "";
$sql .= " select ";
$sql .= "   * ";
$sql .= " from v_admin ";
$sql .= " left join m_syozoku on (m_syozoku.syozoku_id=v_admin.admin_syozoku_id)";
$sql .= " where ";
$sql .= "     v_admin.admin_id ='" . _as($main_rec[0]['tcr_request_admin_id']) . "'";

$admin_rec = _select($sql);
$blade->assign('admin_rec', $admin_rec[0]);

$similarity_companies = array();

if (count($admin_rec) > 0) {
    $sql = "";
    $sql .= " select ";
    $sql .= "   * ";
    $sql .= " from m_company ";
    $sql .= " where ";
    $sql .= " company_delete_date is null ";
    $sql .= " and company_name like '%" . _as($main_rec[0]['tcr_name']) . "%' ";
    $sql .= " order by company_insert_date desc ";

    $similarity_companies = _select($sql);
}

$blade->assign('similarity_companies', $similarity_companies);

$blade->assign('_conf_big_cate', $_conf_big_cate);

_setAssign($blade, $this_sess);

$contents_title = "企業承認画面";
$active_menu    = "company_request_list";
$contents_tpl   = "company_request_approve.html";
