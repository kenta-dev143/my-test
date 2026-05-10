<?php

require('../environment.php');
require('../lang.php');
require('../inc.php');


$no = mb_convert_encoding($_request['no'],_ENCODING_SRC,"UTF-8");

//DB接続
$conn = _dbConnect();
_query( $conn, "begin" );


//ニックネーム
$sql  = "select * from m_postno_data where fld2 = '" ._as( $no) . "'";
$recs = _select( $sql );

//検索結果が複数か？
$find_flg = false;
if(_count($recs) > 0){
    $todou_data = "";

    $list = array();
    $idx = 0;

    $save_tyou = ""; //2012/12/12 Add

    for($i=0;$i<_count($recs);$i++ ){
        $todoufuken_no  = substr($recs[$i]['fld0'],0,2); //都道府県No
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

#        if( $pos1 !== FALSE){ //stripos
#            //町名に「（」があれば「（」含んで以降全部消す
#            $tyou = substr($tyou,0,$pos1);
#        }elseif($pos2 !== FALSE){
#            //町名に「（」がなくて「）」だけ有る場合は１つ前の町名にする
#            $tyou = $save_tyou;
#        }
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

    for($i=0;$i<_count($list2);$i++){
        $todou_data .= intval($list2[$i]['todoufuken_no'])."#".$list2[$i]['shikugun']."#".$list2[$i]['tyou']."#".$list2[$i]['todoufuken']."_";
        $find_flg = true;
    }
}

//DB切断
_query( $conn, "commit" );
_dbDisconnect( $conn );

$ret = "";
if($find_flg == true){
    $ret .= "seikou#".$todou_data;
}else{
    $ret .= "sippai#";
}

$ret = "OK" . $ret;
echo mb_convert_encoding($ret,"UTF-8",_ENCODING_SRC);

exit();
?>