<?php

    require( "lib/environment.php" );
    require( "lib/Smarty.class.php" );
    require( "lib/UserSmarty.php" );
    require( "lib/lang.php" );
    require( "lib/inc.php" );
    require( "lib/check.php" );
    require( "lib/picture.php" );
    require( "lib/project.php" );

    const AC_SETTING_NAME_ENDNOTE = '_ac';

    if($_request['event_id']=="" || $_request['user_id']==""){
        die("System Error1 [URLが正しくありません]");
    }

    $conn = _dbConnect();

    $event_recs = _select("select * from m_event where event_id='"._as($_request['event_id'])."'");
    $sql = "";
    $sql .= " select * from v_user ";
    $sql .= " left join v_admin on (v_user.user_admin_id = v_admin.admin_id)";
    $sql .= " left join m_syozoku on (m_syozoku.syozoku_id=v_admin.admin_syozoku_id)";
    $sql .= " left join m_syozoku_group on (m_syozoku_group.szkgrp_id = m_syozoku.syozoku_szkgrp_id)";
    $sql .= "  where user_id='" . _as($_request['user_id']) . "'";
    $user_recs = _select($sql);

    $company = _select("select * from m_company where company_id = '" . _as($user_recs[0]['user_company_id']) . "' and company_id not in (1)");

    _dbDisconnect( $conn );

    if(_count($event_recs)==0 || _count($user_recs)==0){
        die("System Error2 [URLが正しくありません]");
    }

    //QRコード（W0002-99-12345678）
    // $qr_code = $event_recs[0]['event_area_shikibetsu_id'].substr($event_recs[0]['event_id'],1)."-".$user_recs[0]['user_big_cate']."9-".substr($user_recs[0]['user_id'],1);
    //大分類無い場合の場合に備えて
    $qr_code = $event_recs[0]['event_area_shikibetsu_id'].substr($event_recs[0]['event_id'],1)."-".intval($user_recs[0]['user_big_cate'])."9-".substr($user_recs[0]['user_id'],1);

    $big_cate_name = $_conf_big_cate[ $user_recs[0]['user_big_cate'] ];

    // ************************
    // QR作成
    // ************************
    // $qr_filenm = rand().".png";

    // require_once('lib/qrcode//vendor/autoload.php');
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
    require_once("lib/pdf_lib/pdf_lib.php");

    //$settingName    = "raijyousya_card"; //帳票名称（セッティング名）
    $settingName = $event_recs[0]['event_url_key'];

    $tplDir = _SYSTEM_ROOT_DIR . "/pdf_tpl";      //PDFテンプレートやセッテイングのベースディレクトリ

    if($user_recs[0]['user_big_cate'] == 7 && file_exists($tplDir . '/' . $settingName . AC_SETTING_NAME_ENDNOTE)){
      $settingName = $settingName . AC_SETTING_NAME_ENDNOTE;
    }

    $pdf = new Pdf($settingName, $tplDir);

    $info = array();
    // $info['waku']   = "1";
    // $info['meishi_waku']   = "1";
    // $info['meishi']   = "お名刺";
    // $info['event_name']   = $event_recs[0]['event_name'];
    $info['qr_code']   = $qr_code;
    $info['qr_code_str']   = $qr_code;
    $info['user_big_cate'] = $big_cate_name;

    // 東西で大分類の表示を分ける
    if ($event_recs[0]['event_area_shikibetsu_id'] == 'E' || $event_recs[0]['event_area_shikibetsu_id'] == 'T' || $event_recs[0]['event_area_shikibetsu_id'] == 'C') {
        // 小売、外食の場合★を表示する
        if ($user_recs[0]['user_big_cate'] == 1 || $user_recs[0]['user_big_cate'] == 2) {
            $info['user_big_cate'] = '　★　';
        } else if (($event_recs[0]['event_area_shikibetsu_id'] == 'E' || $event_recs[0]['event_area_shikibetsu_id'] == 'C') && $user_recs[0]['user_big_cate'] == 5) {
            // 東日本、中部で大分類が出展者の場合「出展社」と表示する
            $info['user_big_cate'] = '出展社';
        } else {
            $info['user_big_cate'] = '';
        }
    } else if ($event_recs[0]['event_area_shikibetsu_id'] == 'W' || $event_recs[0]['event_area_shikibetsu_id'] == 'S' || $event_recs[0]['event_area_shikibetsu_id'] == 'K') {
        // 大分類が「小売, 外食, メーカー(招待), その他(招待), その他(来場)」以外の場合非表示
        $allow_big_cate = [1,2,3,4,8];
        if ( ! in_array($user_recs[0]['user_big_cate'], $allow_big_cate)) {
            $info['user_big_cate'] = '';
        }
    }

    $user_name = $user_recs[0]['user_name'];
    if ($user_recs[0]['user_big_cate'] != 7) {
      $user_name = $user_name . '様';
    }

    //$info['user_info']   = $user_recs[0]['user_kigyou_name']."\n".$user_recs[0]['user_busyo']."\n".$user_recs[0]['user_yakusyoku']."\n".$user_recs[0]['user_name']." 様";
    $info['user_info']   = $user_recs[0]['user_kigyou_name']."\n　\n".$user_name;
    $info['syozoku_name'] = (empty($user_recs[0]['szkgrp_name']) ? $user_recs[0]['syozoku_name'] : $user_recs[0]['szkgrp_name']);
    // $info['big_cate_name_bg']   = "1";
    // $info['big_cate_name']   = $big_cate_name;


    $info['user_name'] = $user_name;
    $info['user_yakusyoku'] = $user_recs[0]['user_yakusyoku'];
    $info['kigyou_name'] = empty($company) ? $user_recs[0]['user_kigyou_name'] : $company[0]['company_display_name'];

    $pdf->WriteInfo('TEMPLATE_1',$info);

//    $down_flnm      = $qr_code.'.pdf';         //ダウンロードファイル名
//    $pdf->Output($down_flnm, 'I');    //一時保存
    $down_flnm      = $info['kigyou_name'] . '_' . $user_name . '.pdf';         //ダウンロードファイル名

    $dist = 'D';
    $allow_dists = ['I', 'D', 'F', 'FI', 'FD', 'E', 'S'];
    if (! empty($_request['dist']) && in_array($dist, $allow_dists)) {
        $dist = $_request['dist'];
    }
    $pdf->Output($down_flnm, $dist);    //ダウンロード

