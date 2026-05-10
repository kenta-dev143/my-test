<?php

    require( "lib/environment.php" );
    require( "lib/Smarty.class.php" );
    require( "lib/UserSmarty.php" );
    require( "lib/lang.php" );
    require( "lib/inc.php" );
    require( "lib/check.php" );
    require( "lib/picture.php" );
    require( "lib/project.php" );

    if($_request['eid']=="" || $_request['uid']==""){
        die("System Error1 [URLが正しくありません]");
    }

    $conn = _dbConnect();

    $event_recs = _select("select * from m_event where event_id='"._as($_request['eid'])."'");
    $user_recs = _select("select * from v_user where user_id='"._as($_request['uid'])."'");

    _dbDisconnect( $conn );

    if(_count($event_recs)==0 || _count($user_recs)==0){
        die("System Error2 [URLが正しくありません]");
    }

    //QRコード（W0002-99-12345678）
    // $qr_code = $event_recs[0]['event_area_shikibetsu_id'].substr($event_recs[0]['event_id'],1)."-".$user_recs[0]['user_big_cate']."9-".substr($user_recs[0]['user_id'],1);
    //大分類無い場合の場合に備えて
    $qr_code = $event_recs[0]['event_area_shikibetsu_id'].substr($event_recs[0]['event_id'],1)."-".intval($user_recs[0]['user_big_cate'])."9-".substr($user_recs[0]['user_id'],1);

    // ************************
    // QR作成
    // ************************
    $qr_filenm = rand().".png";

    require_once('lib/qrcode//vendor/autoload.php');
    use Endroid\QrCode\QrCode;
    // QRコードに埋め込む文字列の指定
    $qrCode = new QrCode($qr_code);
    // QRコードのサイス（単位：ピクセル）
    $qrCode->setSize(190);
    // QRコードの周囲の余白（単位：ピクセル）
    $qrCode->setMargin(8);

    header('Content-Type: '.$qrCode->getContentType());
    echo $qrCode->writeString();

