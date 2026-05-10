<?
    //5900111 ---> ２行続く＋（その他）
    //5900105 ===> 2つある
    require('../environment.php');
    require('../lang.php');
    require('../inc.php');

    if($_GET['post_no'] != ""){
        $search_no = $_GET['post_no'];
    }if($_POST['post_no'] != ""){
        $search_no = $_POST['post_no'];
    }

    if($_GET['opn'] != ""){
        $_opn = $_GET['opn'];
    }if($_POST['opn'] != ""){
        $_opn = $_POST['opn'];
    }

    $hainashi_search_no = str_replace("-","",$search_no);
    if(strlen($hainashi_search_no) != 7){
        $err_str = "郵便番号の指定が不正です。";
    }

    $list = array();
    $idx = 0;
    if($err_str==""){
        //郵便番号CSVデータファイル
        $csv_file = "KEN_ALL.CSV";
        //$csv_file = "27OSAKA.CSV";

        $fp = fopen($csv_file,"r");
        flock($fp,LOCK_EX);
        $save_tyou = "";
        while($rec = fgetcsv($fp,10000,",") ){
            $todoufuken_no  = substr($rec[0],0,2); //都道府県No
            $post_no    = $rec[2]; //郵便番号７桁
            $todoufuken = $rec[6]; //都道府県
            $shikugun   = $rec[7]; //市区郡町村
            $tyou       = $rec[8]; //町名

            if($tyou=="以下に掲載がない場合"){
                $tyou = "";
            }

            if($post_no == $hainashi_search_no){

                $list[$idx]['todoufuken_no'] = $todoufuken_no;
                $list[$idx]['todoufuken'] = $todoufuken;
                $list[$idx]['shikugun'] = $shikugun;


               $find = "（";
               $find2 = "）";
               $pos1 = strpos($tyou, $find);
               $pos2 = strpos($tyou, $find2);

#                if( $pos1 !== FALSE){ //stripos
#                    //町名に「（」があれば「（」含んで以降全部消す
#                    $tyou = substr($tyou,0,$pos1);
#                }elseif($pos2 !== FALSE){
#                    //町名に「（」がなくて「）」だけ有る場合は１つ前の町名にする
#                    $tyou = $save_tyou;
#                }
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

        }
        flock($fp, LOCK_UN);
        fclose($fp);

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
    <body onLoad="window.focus();" onBlur="window.focus();">
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
                echo "<img src=\"blue_si.gif\"><a href=\"javascript:_jyuusyoSet('" . htmlspecialchars($list2[$i]['todoufuken_no']) ."','" . htmlspecialchars($list2[$i]['todoufuken']) ."','". htmlspecialchars($list2[$i]['shikugun']) ."','". htmlspecialchars($list2[$i]['tyou']) ."');void(0);\">";
                echo htmlspecialchars($list2[$i]['todoufuken']) . htmlspecialchars($list2[$i]['shikugun']) . htmlspecialchars($list2[$i]['tyou']);
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
