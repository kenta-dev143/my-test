<?php
    //5900111 ---> ２行続く＋（その他）
    //5900105 ===> 2つある
    require('../environment.php');
    require('../lang.php');
    require('../inc.php');

    set_time_limit(600); //10分稼動

    function _as2($_val){
        return mysql_real_escape_string( mb_convert_encoding($_val,'UTF8','sjis') );
    }

    //郵便番号CSVデータファイル
    $csv_file = "KEN_ALL.CSV";
    //$csv_file = "27OSAKA.CSV";

    //DB接続
    $conn = _dbConnect();
    _query( $conn, "begin" );


    $fp = fopen($csv_file,"r");
    flock($fp,LOCK_EX);
    $save_tyou = "";
    while($rec = fgetcsv($fp,10000,",") ){
        $array = array();
        $array['fld0'] = "'" . _as2($rec[0]) . "'";
        $array['fld1'] = "'" . _as2($rec[1]) . "'";
        $array['fld2'] = "'" . _as2($rec[2]) . "'";
        $array['fld3'] = "'" . _as2($rec[3]) . "'";
        $array['fld4'] = "'" . _as2($rec[4]) . "'";
        $array['fld5'] = "'" . _as2($rec[5]) . "'";
        $array['fld6'] = "'" . _as2($rec[6]) . "'";
        $array['fld7'] = "'" . _as2($rec[7]) . "'";
        $array['fld8'] = "'" . _as2($rec[8]) . "'";
        $array['fld9'] = "'" . _as2($rec[9]) . "'";
        $array['fld10'] = "'" . _as2($rec[10]) . "'";
        $array['fld11'] = "'" . _as2($rec[11]) . "'";
        $array['fld12'] = "'" . _as2($rec[12]) . "'";
        $array['fld13'] = "'" . _as2($rec[13]) . "'";
        $array['fld14'] = "'" . _as2($rec[14]) . "'";


        _insert("m_postno_data",$array);
    }
    flock($fp, LOCK_UN);
    fclose($fp);

    //DB切断
    _query( $conn, "commit" );
    _dbDisconnect( $conn );

    header( "Content-Type: text/html; charset=utf-8" ); //ヘッダー情報

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>郵便番号データCSVをDBにインポート</title>
</head>
<body>
<H2>郵便番号データCSVをDBにインポート</h2>
<hr>
完了したと思う
<br>
</body>
</html>
