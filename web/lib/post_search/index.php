<?
    //5900111 ---> ２行続く＋（その他）
    //5900105 ===> 2つある
    //5900158 ===> 3つある
    require('../environment.php');
    require('../lang.php');
    require('../inc.php');

    //DB接続
    $conn = _dbConnect();
    _query( $conn, "begin" );

    $search_no = $_request['post_no'];
    $_opn = $_request['opn'];

    $hainashi_search_no = str_replace("-","",$search_no);
    if(strlen($hainashi_search_no) != 7){
        $err_str = "郵便番号の指定が不正です。";
    }

    $list = array();
    $idx = 0;
    if($err_str==""){
        //郵便番号CSVデータファイル
        //$csv_file = "KEN_ALL.CSV";
        //$csv_file = "27OSAKA.CSV";

        //$fp = fopen($csv_file,"r");
        //flock($fp,LOCK_EX);
        $save_tyou = "";

        $sql = "";
        $sql .= " select * from m_postno_data where fld2 = '" . _as($hainashi_search_no) . "'";
        $recs = _select($sql);

        for($i=0;$i<_count($recs);$i++ ){
            //$todoufuken_no  = substr($recs[$i]['fld0'],0,2); //都道府県No
            $todoufuken_no  = intval(substr($recs[$i]['fld0'],0,2)); //都道府県No
            $todoufuken = $recs[$i]['fld6']; //都道府県
            $shikugun   = $recs[$i]['fld7']; //市区郡町村
            $tyou       = $recs[$i]['fld8']; //町名

            if($tyou=="以下に掲載がない場合"){
                $tyou = "";
            }

            $list[$idx]['todoufuken_no'] = $todoufuken_no;
            $list[$idx]['todoufuken'] = $todoufuken;
            $list[$idx]['shikugun'] = $shikugun;


           $find = "（";
           $find2 = "）";
           $pos1 = strpos($tyou, $find);
           $pos2 = strpos($tyou, $find2);

#            if( $pos1 !== FALSE){ //stripos
#                //町名に「（」があれば「（」含んで以降全部消す
#                $tyou = substr($tyou,0,$pos1);
#            }elseif($pos2 !== FALSE){
#                //町名に「（」がなくて「）」だけ有る場合は１つ前の町名にする
#                $tyou = $save_tyou;
#            }
#2012/12/12 Mod --------------------- Strat ------
            if( $pos1 !== FALSE){
                if( $pos2 !== FALSE){
                    //町名に「（」と「）」があれば「（」含んで以降全部消す
                    $tyou = substr($tyou, 0, $pos1);
                }else{
                    //町名に「（」あるが「）」がない場合、次行続くパターン
                    //「（」含んで以降全部消してSAVE
                    $tyou = substr($tyou, 0, $pos1);
                    $save_tyou = $tyou;
                }
            }else{
                if( $save_tyou !=  ""){
                    //町名に「（」がなくてSAVEがあれば、それを設定
                    $tyou = $save_tyou;
                }
                if( $pos2 !== FALSE){
                    //「）」があればSAVE終了
                    $save_tyou = "";
                }
            }
#2012/12/12 Mod --------------------- End ------



            $list[$idx]['tyou'] = $tyou;

            //2012/12/12 Del
            //$save_tyou = $tyou;

            $idx++;

        }

        //flock($fp, LOCK_UN);
        //fclose($fp);

        //重複しているものを１つにする
        $list2 = array();
        for($i=0;$i<_count($list);$i++){
            $find=false;
            for($j=0;$j<_count($list2);$j++){
                if($list[$i]['todoufuken_no'] == $list2[$j]['todoufuken_no'] &&
                   $list[$i]['todoufuken'] == $list2[$j]['todoufuken'] &&
                   $list[$i]['shikugun'] == $list2[$j]['shikugun'] &&
                   $list[$i]['tyou'] == $list2[$j]['tyou'] ){
                    $find=true;
                }
            }
            if($find==false){
                $list2[] = $list[$i];
            }
        }

    }

    //DB切断
    _query( $conn, "commit" );
    _dbDisconnect( $conn );

    header( "Content-Type: text/html; charset=utf-8" ); //ヘッダー情報

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>住所検索</title>
<script language="JavaScript">
<!-- //
function _jyuusyoSet(tno,tnm,shi,tyo){
<? if($_opn=="dlg"){ ?>
    var opener=window.dialogArguments;
    opener._postSearchResultSet(1,tno,tnm,shi,tyo);
    self.window.close();
<? }else{ ?>
    if(window.opener.closed){
        window.close();
    }else{
        window.opener._postSearchResultSet(1,tno,tnm,shi,tyo);
        window.close();
    }
<? } ?>
}
function _cancel(){
<? if($_opn=="dlg"){ ?>
    var opener=window.dialogArguments;
    opener._postSearchResultSet(0,'','','','');
    self.window.close();
<? }else{ ?>
    if(window.opener.closed){
        window.close();
    }else{
        window.opener._postSearchResultSet(0,'','','','');
        window.close();
    }
<? } ?>
}
// -->
</script>
</head>
<? if(_count($list2)==1){ ?>
    <body onLoad="_jyuusyoSet(<? echo "'" . _hs($list2[0]['todoufuken_no']) ."','" . _hs($list2[0]['todoufuken']) ."','". _hs($list2[0]['shikugun']) ."','". _hs($list2[0]['tyou']) ."'"; ?>);">
    </body>
<? }else{ ?>
    <? if($_opn=="dlg"){ ?>
    <body topmargin=5 leftmargin=5>
    <? }else{ ?>
    <body onLoad="window.moveTo(0, 0);window.resizeTo(500, 480); window.focus();" onBlur="window.focus();">
    <? } ?>
    <?
    if($err_str != ""){
        echo "<br><br><font color=\"red\">"._hs($err_str)."</font>";
    }else{
        if(_count($list)==0){
            echo "<br><br><font color=\"red\">指定された郵便番号に該当する住所が見つかりませんでした。</font>";
        }else{
            echo "検索結果<br>以下の住所が見つかりました。該当する住所を選択してください。<br><br>";
            for($i=0;$i<_count($list2);$i++){
                echo "<img src=\"blue_si.gif\"><a href=\"#\" onClick=\"_jyuusyoSet('" . _hs($list2[$i]['todoufuken_no']) ."','" . _hs($list2[$i]['todoufuken']) ."','". _hs($list2[$i]['shikugun']) ."','". _hs($list2[$i]['tyou']) ."');\">";
                echo _hs($list2[$i]['todoufuken']) . _hs($list2[$i]['shikugun']) . _hs($list2[$i]['tyou']);
                echo "</a><br>";
            }
        }
    }

    ?>
    <br>
    <input type="button" value="閉じる" onClick="_cancel();">
    <br>
    </body>
<? } ?>
</html>
