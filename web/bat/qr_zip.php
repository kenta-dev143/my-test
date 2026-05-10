<?php

    function is_positive_integer($value) {
        return is_numeric($value) && intval($value) == $value && $value > 0;
    }

    //なぜかtenjikaiのAWSでは存在しない$_SERVER['HTTP_HOST']を参照するとバッチでエラーになるので
    if(!isset($_SERVER['HTTP_HOST'])){
        $_SERVER['HTTP_HOST']="";
    }

    error_reporting(E_ALL & ~E_NOTICE);

    // ******************************************************************************************************
    // INCLUDE FILES
    // ******************************************************************************************************
    $project_name_prefix = "bat_";
    require_once( "../lib/environment.php" );
    require_once( "../lib/Smarty.class.php" );
    require_once( "../lib/UserSmarty.php" );
    require_once( "../lib/lang.php" );
    require_once( "../lib/inc.php" );
    require_once( "../lib/check.php" );
    require_once( "../lib/picture.php" );
    require_once( "../lib/project.php" );
    require_once("../lib/pdf_lib/pdf_lib.php");

    const AC_SETTING_NAME_ENDNOTE = '_ac';

    $dir_path = _SYSTEM_ROOT_DIR.'/upfile/qr_zip/events/';
    _mkdir($dir_path);

    $conn = null;

    set_time_limit(0); //時間制限なし
    ini_set('memory_limit',"1024M"); //メモリ拡大

    if($_SERVER['HTTP_HOST']!=""){
        echo "Start ".$_now_timestamp."<br>";
    }

    //DB接続
    $conn = _dbConnect();

    $sql  = "";
    $sql .= " select * from m_event ";
    $sql .= " where event_delete_date is null ";
    $event_recs = _select($sql);

    // 開催日が未来日のイベントのみを対象にする
    $events = array();
    foreach ($event_recs as $event_rec) {
        $event_kaisai = new DateTime($event_rec['event_kaisai_ymd_st']);
        $now = new DateTime($_now_timestamp);

        if ($event_kaisai > $now) {
            $events[] = $event_rec;
        }
    }

    $sql = "";
    $sql .= " SELECT * FROM m_syozoku_group ";
    $sql .= " where  szkgrp_delete_date is null ";

    if (isset($argv[1]) && is_positive_integer($argv[1]) && isset($argv[2]) && is_positive_integer($argv[2])) {
        $target_ids = array();

        foreach (range($argv[1], $argv[2]) as $i) {
            $target_ids[] = sprintf('bg%06d', $i);
        }

        $sql .= " and szkgrp_id in ('" . implode("','", $target_ids) . "') ";
    }

    $syozoku_group_recs = _select($sql);

    foreach ($events as $event) {

        $event_dir_name = $event['event_id'] . '_' . $event['event_pulldown_name'] . '/';
        $event_dir_path = $dir_path . $event_dir_name;
        _mkdir($event_dir_path);

        foreach ($syozoku_group_recs as $syozoku_group) {


            $sql = "";
            $sql .= " SELECT * FROM v_user ";
            $sql .= " left join v_admin on v_user.user_admin_id = v_admin.admin_id ";
            $sql .= " left join m_syozoku on (v_admin.admin_syozoku_id = m_syozoku.syozoku_id)";
            $sql .= " left join m_tantou_area on (v_admin.admin_tanarea_id = m_tantou_area.tanarea_id)";
            $sql .= " left join m_syoutai on (v_user.user_syoutai_id = m_syoutai.syoutai_id)";
            $sql .= " left join m_company on (m_syoutai.syoutai_company_id = m_company.company_id)";
            $sql .= " left join m_syozoku_group on (m_syozoku_group.szkgrp_id = m_syozoku.syozoku_szkgrp_id)";
            $sql .= " where v_admin.admin_syozoku_id in (select syozoku_id from m_syozoku where syozoku_szkgrp_id = '" . $syozoku_group['szkgrp_id'] . "') ";
            $sql .= " and v_user.user_event_id = '" . $event['event_id'] . "' ";
            $sql .= " and v_user.user_delete_date is null ";
            $user_recs = _select($sql);

            $pdfPaths = array();
            $pdfFiles = array();

            foreach ($user_recs as $rec) {

                if (empty($rec['user_raijyou_yotei_time'])) {
                    // 来場予定日時の登録がないユーザーはQRを作成しない
                    continue;
                }

                $qr_code = $event['event_area_shikibetsu_id'] . substr($event['event_id'], 1) . "-" . intval($rec['user_big_cate']) . "9-" . substr($rec['user_id'], 1);

                $big_cate_name = $_conf_big_cate[$rec['user_big_cate']];

                // ************************
                // PDF作成
                // ************************

                $settingName = $event['event_url_key'];
                $tplDir = _SYSTEM_ROOT_DIR . "/pdf_tpl";      //PDFテンプレートやセッテイングのベースディレクトリ

                if ($rec['user_big_cate'] == 7 && file_exists($tplDir . '/' . $settingName . AC_SETTING_NAME_ENDNOTE)) {
                    $settingName = $settingName . AC_SETTING_NAME_ENDNOTE;
                }

                $pdf = new Pdf($settingName, $tplDir);

                $info = array();
                $info['qr_code'] = $qr_code;
                $info['qr_code_str'] = $qr_code;
                $info['user_big_cate'] = $big_cate_name;

                // 東西で大分類の表示を分ける
                if ($event['event_area_shikibetsu_id'] == 'E' || $event['event_area_shikibetsu_id'] == 'T' || $event['event_area_shikibetsu_id'] == 'C') {
                    // 小売、外食の場合★を表示する
                    if ($rec['user_big_cate'] == 1 || $rec['user_big_cate'] == 2) {
                        $info['user_big_cate'] = '　★　';
                    } else if (($event_recs[0]['event_area_shikibetsu_id'] == 'E' || $event_recs[0]['event_area_shikibetsu_id'] == 'C') && $user_recs[0]['user_big_cate'] == 5) {
                        // 東日本、中部で大分類が出展者の場合「出展社」と表示すｌる
                        $info['user_big_cate'] = '出展社';
                    } else {
                        $info['user_big_cate'] = '';
                    }
                } else if ($event['event_area_shikibetsu_id'] == 'W' || $event['event_area_shikibetsu_id'] == 'S' || $event['event_area_shikibetsu_id'] == 'K') {
                    // 大分類が「小売, 外食, メーカー(招待), その他(招待), その他(来場)」以外の場合非表示
                    $allow_big_cate = [1, 2, 3, 4, 8];
                    if (!in_array($rec['user_big_cate'], $allow_big_cate)) {
                        $info['user_big_cate'] = '';
                    }
                }

                $user_name = $rec['user_name'];
                if ($rec['user_big_cate'] != 7) {
                    $user_name = $user_name . '様';
                }

                $info['user_info'] = $rec['user_kigyou_name'] . "\n　\n" . $user_name;
                $info['syozoku_name'] = (empty($rec['szkgrp_name']) ? $rec['syozoku_name'] : $rec['szkgrp_name']);


                $info['user_name'] = $user_name;
                $info['user_yakusyoku'] = $rec['user_yakusyoku'];
                $kigyou_name = empty($rec['company_display_name']) ? $rec['user_kigyou_name'] : $rec['company_display_name'];
                $info['kigyou_name'] = $kigyou_name;

                $pdf->WriteInfo('TEMPLATE_1', $info);


                $w_flnm = $kigyou_name . '_' . $user_name . '.pdf';
                $w_fullpath = $event_dir_path . "/" . $w_flnm;
                $pdfPaths[] = $w_fullpath;
                $pdfFiles[] = $w_flnm;
                $pdf->Output($w_fullpath, 'F');    //ファイル出力

                unset($pdf);
                $pdf = null;
            }

            if (_count($pdfFiles) > 0) {
                $zipFileName = "QR_" . $syozoku_group['szkgrp_id'] . '_' . $syozoku_group['szkgrp_name'] . ".zip";

                $zip = new ZipArchive();

                $result = $zip->open($event_dir_path . "/" . $zipFileName, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE);
                if ($result !== true) { //エラー処理
                    die('system error!');
                }
                for ($i = 0; $i < _count($pdfFiles); $i++) {
                    $zip->addFile($pdfPaths[$i], $pdfFiles[$i]);
                }

                $zip->close();

                for ($i = 0; $i < _count($pdfFiles); $i++) {
                    @unlink($pdfPaths[$i]);
                }
            }
        }
    }

    //DB切断
    _dbDisconnect( $conn );

    if($_SERVER['HTTP_HOST']!=""){
        echo "End ".$_now_timestamp."<br>";
    }

    echo "\n" . 'END ' . "memory_usage: " . memory_get_usage() / (1024 * 1024) . "MB" . "\n" . "memory_peak_usage: " . memory_get_peak_usage() / (1024 * 1024) . "MB" . "\n";;

    exit();
