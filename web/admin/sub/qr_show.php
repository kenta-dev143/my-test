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
    // 登録・更新・削除
    // ******************************************************************************************************
    if($_request['exec'] == 'input'){

        if ( empty($_request['qr_code'])){
            $err_msg[] = "QRコードを入力してください。";
        } else if ( strlen($_request['qr_code']) != 17) {
            $err_msg[] = "QRコードが正しく読み込まれませんでした。(フォーマットエラー)";
        } else {
            list($fst,$scd,$code) = explode("-", $_request['qr_code']);
            $qr_event_area_shikibetsu_id = substr($fst,0,1);
            $qr_event_id = "e".substr($fst,1);
            $qr_user_big_cate = substr($scd,0,1);
            $qr_dummy = substr($scd,1);
            $qr_user_id = "u".$code;

            $sql = "";
            $sql .= " select * ";
            $sql .= " from m_event ";
            $sql .= " where event_delete_date is null ";
            $sql .= " and event_id = '" . _as($qr_event_id) . "'";
            $event_recs = _select($sql);
            if ( _count($event_recs) == 0 ) {
                $err_msg[] = "QRコードが正しく読み込まれませんでした。(有効なイベントが存在しません)";
            }

            $sql = "";
            $sql .= " select * ";
            $sql .= " from v_user ";
            $sql .= " where user_delete_date is null ";
            $sql .= " and user_id = '" . _as($qr_user_id) . "'";
            $user_recs = _select($sql);
            if ( _count($user_recs) == 0 ) {
                $err_msg[] = "QRコードが正しく読み込まれませんでした。(有効なユーザーが存在しません)";
            }

            if (_count($err_msg) == 0) {
                echo '<script type="text/javascript">';
                echo 'window.open("../qr_print.pdf?dist=I&user_id=' . $qr_user_id . '&event_id=' . $qr_event_id . '", "_blank");';
                echo '</script>';
            }
        }
    }

    // ******************************************************************************************************
    // 初期・完了画面
    // ******************************************************************************************************

    _setAssign($blade,$this_sess);

    $contents_title = "QRコードPDF表示";
    $active_menu = "qr_show";
    $contents_tpl = "qr_show";
