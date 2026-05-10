<?php
// 総合チェックロジック
// 「 _check() 」
//
// 書式 ： $errors = _check( チェック情報 , リクエスト情報 );
//
//
//--------------------------------(カット＆ペースト用、全指定)-----------------------------------
//               ,"p_xxxx,ｘｘｘｘｘｘｘ"
//                     => "need,min=?,max=?,number,eisuu,zenkana,date,email,mobilemail,tel,post,match=p_xxxx"
//--------------------------------(カット＆ペースト用、全指定)-----------------------------------
//
// 指定できるチェック
//      need        :必須チェック
//      len=?       :桁数チェック(半角単位[byte数]文字数で指定)     //2017/09/11 Add
//      min=?       :最小桁数チェック(半角単位[byte数]文字数で指定)
//      max=?       :最大桁数チェック(半角単位[byte数]文字数で指定)
//      zmin=?      :最小桁数チェック(全角単位文字数で指定)
//      zmax=?      :最大桁数チェック(全角単位文字数で指定)
//      number      :半角数値チェック(0-9)小数点ＯＫ
//      numberKeta  :半角数値チェック(0-9)小数点ＯＫで整数部と少数部の桁数チェック版
//      seisuu      :半角数値チェック(0-9)小数点ＮＧ
//      minusoknumber :半角数値チェック(0-9)小数点ＯＫ 負の小数ＯＫ
//      minusokseisuu :半角数値チェック(0-9)小数点ＮＧ 負の整数ＯＫ
//      eisuu       :半角英数チェック(a-z,A-Z,0-9)
//      eiji        :半角英字チェック(a-z,A-Z)
//      eisuubar    :半角英数チェック(a-z,A-Z,0-9,「-」「_」)
//      hankaku     :半角チェック
//      zenkaku     :全角のみ(半角不可)チェック
//      zenkana     :全角カナチェック
//      hankana     :半角カナチェック
//      zenhira     :全角ひらがなチェック
//      date        :日付チェック(yyyy/mm/dd書式チェック)
//      email       :E-Mail書式チェック
//      mobilemail  :携帯メールのドメインかチェック
//      tel         :電話番号書式チェック(ハイフン付きの 9999-9999-9999形式チェック)
//      tel2        :電話番号書式チェック(ハイフンなし)
//      post        :郵便番号書式チェック(999-9999形式チェック)
//      match=xxx   :他フィールドと同値かチェック(確認用パスワードなどに利用)
//      url          :URL書式チェック
//      kisyuizon   :機種依存文字チェック（SRC<->DB間の文字コード変換不可チェック）2010/01/22 Add
//
// 例   ： $chks = array(
//                        "p_id,ID"
//                              => "need,max=5"
//                       ,"p_pass1,パスワード"
//                              => "need,min=4,max=8"
//                       ,"p_pass2,パスワード"
//                              => "need,min=4,max=8,match=p_pass1"
//                       ,"p_name_kanji,氏名（漢字）"
//                              => "need,zmax=30"
//                       ,"p_name_kana,氏名（カナ）"
//                              => "need,zmax=30,zenkana"
//                       ,"p_mail_pc,E-Mail(PC)"
//                              => "need,max=40,email"
//                       ,"p_mail_mobile,E-Mail(携帯)"
//                              => "need,max=40,email,mobilemail"
//                       ,"p_birth,誕生日"
//                              => "need,max=10,date"
//                       ,"p_post,郵便番号"
//                              => "need,max=8,post"
//                       ,"p_age,年齢"
//                              => "need,max=3,seisuu"
//                       ,"p_tel,電話番号"
//                              => "need,max=13,tel"
//                  );
//         $errors = _check( $chks , $_request );
//         if( _count($errors) > 0 ){
//               //エラーあり！！
//              for($i=0;$i<_count($errors);$i++){
//                  echo "<font color=red>" . _hs($errors[$i]) . "</font><br>";
//              }
//          }
//
$_GLOBAL_err_msg = array();
$_GLOBAL_fld_msg = array();

function _check($chkInfo,$arr, $err_clear = true){
    global $_GLOBAL_err_msg, $_GLOBAL_fld_msg, $_conf_msg;
    global $_lang;
    if($_lang==''){ $_lang='J'; }

    if($err_clear==true){
        $_GLOBAL_err_msg = array();
        $_GLOBAL_fld_msg = array();
    }

    $chk_fldkey = array();
    $chk_fldname = array();
    $chk_fldval = array();
    $save_chk_fldname = array();
    $idx=0;
    //while(list($chk_key,$chk_val) = each($chkInfo)){
    //2009/03/02 Mod
    foreach($chkInfo as $chk_key => $chk_val){
//2010/09/21 mod -----------
#         $fld = split(',',$chk_key);
        $fld = explode(',',$chk_key);
//2010/09/21 mod -----------
        $chk_fldkey[$idx] = $fld[0];
        $chk_fldname[$idx] = $fld[1];
        $chk_fldval[$idx] = $chk_val;
        $save_chk_fldname[$fld[0]] = $fld[1];
        $idx++;
    }
    for($c=0;$c<$idx;$c++){
        $need_sumi = 0;
        foreach($arr as $req_key => $req_val){
            if( ! is_array( $req_val ) ){
                //配列でないのでチェック
                if( $chk_fldkey[$c] == $req_key && $req_key != ""){
                    $GLOBALS['fldkey'] = $chk_fldkey[$c];
//2010/09/21 mod -----------
#                     $chks = split(',',$chk_fldval[$c]);
                    $chks = explode(',',$chk_fldval[$c]);
//2010/09/21 mod -----------
                    for($i=0;$i<_count($chks);$i++){
//2010/09/21 mod -----------
#                         $fld2 = split('=',$chks[$i]);
                        $fld2 = explode('=',$chks[$i]);
//2010/09/21 mod -----------
                        $chk_type = $fld2[0];
                        $chk_subval = $fld2[1];
                        switch($chk_type){
                            case 'need': //必須チェック
                                $need_sumi = 1;
                                _needCheck($req_val,$chk_fldname[$c]);
                                break;
                            //2017/09/11 Add ---------------- Strat -------------
                            case 'len': //桁数チェック（keta=???）
                                _lenCheck($req_val,$chk_subval,$chk_fldname[$c]);
                                break;
                            //2017/09/11 Add ---------------- End -------------
                            case 'max': //最大桁数チェック（max=???）
                                _maxLenCheck($req_val,$chk_subval,$chk_fldname[$c]);
                                break;
                            case 'max': //最大桁数チェック（max=???）
                                _maxLenCheck($req_val,$chk_subval,$chk_fldname[$c]);
                                break;
                            case 'min': //最小桁数チェック（min=???）
                                _minLenCheck($req_val,$chk_subval,$chk_fldname[$c]);
                                break;
                            //2007/08/31 Add ---------- Start ----------
                            case 'zmax': //最大桁数チェック（zmax=???）全角文字数版
                                _zen_maxLenCheck($req_val,$chk_subval,$chk_fldname[$c]);
                                break;
                            case 'zmin': //最小桁数チェック（zmin=???）全角文字数版
                                _zen_minLenCheck($req_val,$chk_subval,$chk_fldname[$c]);
                                break;
                            //2007/08/31 Add ---------- End ----------
                            case 'number': //数値チェック（小数点ＯＫ、マイナスNG）
                                _numCheck($req_val,$chk_fldname[$c]);
                                break;
                            case 'numberKeta': //数値チェック（小数点ＯＫ、マイナスNG、桁数指定）
                                _numCheck($req_val,$chk_fldname[$c],false,$chk_subval);
                                break;

                            case 'seisuu': //数値チェック（小数点ＮＧ）
                                _seisuuCheck($req_val,$chk_fldname[$c]);
                                break;
                            //2011/02/25 Add ---------- Start ----------
                            case 'minusoknumber': //数値チェック（小数点ＯＫ,マイナスＯＫ）
                                _numCheck($req_val,$chk_fldname[$c],true);
                                break;
                            case 'minusokseisuu': //数値チェック（小数点ＮＧ,マイナスＯＫ）
                                _seisuuCheck($req_val,$chk_fldname[$c],true);
                                break;
                            //2011/02/25 Add ---------- End ----------
                            case 'eisuu': //英数字チェック
                                _eisuuCheck($req_val,$chk_fldname[$c]);
                                break;
                            case 'eiji': //英字チェック
                                _eijiCheck($req_val,$chk_fldname[$c]);
                                break;
                            case 'eisuubar': //英数字と「-」「_」チェック
                                _eisuuBarCheck($req_val,$chk_fldname[$c]);
                                break;
                            case 'email': //E-Mail書式チェック
                                _emailCheck($req_val,$chk_fldname[$c]);
                                break;
                            case 'mobilemail': //携帯メアドドメインチェック
                                _mobileMailCheck($req_val,$chk_fldname[$c]);
                                break;
                            case 'url': //URLチェック
                                _urlCheck($req_val,$chk_fldname[$c]);
                                break;
                            case 'tel': //電話番号書式チェック(ハイフン付き形式チェック)
                                _telCheck($req_val,$chk_fldname[$c]);
                                break;
                            case 'tel2': //電話番号書式チェック(ハイフン無し形式チェック)
                                _tel2Check($req_val,$chk_fldname[$c]);
                                break;
                            case 'post': //郵便番号書式チェック(999-9999形式チェック)
                                _postCheck($req_val,$chk_fldname[$c]);
                                break;
                            case 'post2': //郵便番号書式チェック(9999999形式チェック)
                                _post2Check($req_val,$chk_fldname[$c]);
                                break;
                            case 'hankaku': //半角カナかチェック
                                _hankakuCheck($req_val,$chk_fldname[$c]);
                                break;
                            case 'zenkaku': //全角のみかチェック
                                _zenkakuCheck($req_val,$chk_fldname[$c]);
                                break;
                            case 'zenkana': //全角カナかチェック
                                _zenkanaCheck($req_val,$chk_fldname[$c]);
                                break;
                            case 'hankana': //半角カナかチェック
                                _hankanaCheck($req_val,$chk_fldname[$c]);
                                break;
                            case 'zenhira': //全角ひらがなかチェック
                                _zenhiraCheck($req_val,$chk_fldname[$c]);
                                break;
                            case 'match': //指定フィールドと同じ値かチェック (match=xxxxx)
                                _matchCehck($req_val,$arr[$chk_subval],$chk_fldname[$c],$save_chk_fldname[$chk_subval]);
                                break;
                            case 'date': //日付書式かチェック (yyyy/mm/dd形式で正しい日付かチェック)
                                _dateCheck($req_val,$chk_fldname[$c]);
                                break;
                            case 'time': //時刻書式かチェック (hh:nn形式で正しい時刻かチェック)
                                _timeCheck($req_val,$chk_fldname[$c]);
                                break;
                            case 'time24': //24:00以降もOK版 時刻書式かチェック (hh:nn形式で正しい時刻かチェック)
                                _time24Check($req_val,$chk_fldname[$c]);
                                break;

                            //2010/01/22 Add
                            case 'kisyuizon': //SRC<->DB間の文字コード変換不可チェック（機種依存文字チェック）
                                _kisyuizonCheck($req_val,$chk_fldname[$c]);
                                break;

                            //2013/07/10 Add Start ----------------------------
                            default:
                                if(trim($chk_type) != ""){
                                    if(function_exists($chk_type)==true ){
                                        $chk_type($req_val,$chk_fldname[$c]);
                                    }else{
                                        header( "Content-Type: text/html; charset=" . _CHARSET_OUTPUT );
                                        die("<html><head></head><body>_check()関数での、パラメータエラー（"._hs($chk_type)."）</body></html>");
                                    }
                                }
                                break;
                            //2013/07/10 Add End   ----------------------------
                        } //switch
                    } //for
                } //if
            } //if
        } //while
//2010/09/21 mod -----------
#         $chks = split(',',$chk_fldval[$c]);
        $chks = explode(',',$chk_fldval[$c]);
//2010/09/21 mod -----------
        for($i=0;$i<_count($chks);$i++){
//2010/09/21 mod -----------
            $fld2 = explode('=',$chks[$i]);
//2010/09/21 mod -----------
            $chk_type = $fld2[0];
            $chk_subval = $fld2[1];
            switch($chk_type){
                case 'need': //必須チェック
                    if( $need_sumi == 0){
#                        $_GLOBAL_err_msg[] = $chk_fldname[$c] . "は必須入力です。";
                        $_GLOBAL_err_msg[]  = sprintf($_conf_msg[$_lang]['chk'][3],$chk_fldname[$c]);
                        $_GLOBAL_fld_msg[$chk_fldkey[$c]][]  = sprintf($_conf_msg[$_lang]['chk'][3],$chk_fldname[$c]);
                    }
                    break;
            } //switch
        } //for
    } //for
    return $_GLOBAL_err_msg;
}

function _getErrorCount(){
    global $_GLOBAL_err_msg, $_conf_msg;
    global $_lang;
    if($_lang==''){ $_lang='J'; }


    return _count($_GLOBAL_err_msg);
}

//必須チェック
function _needCheck($input_text,$err) {
    global $_GLOBAL_err_msg,$_GLOBAL_fld_msg, $_conf_msg;
    global $_lang;
    if($_lang==''){ $_lang='J'; }


    if(is_null($input_text) || trim($input_text) == ''){
        if( $err != ""){
            $_GLOBAL_err_msg[]  = sprintf($_conf_msg[$_lang]['chk'][3],$err);
            $_GLOBAL_fld_msg[$GLOBALS['fldkey']][]  = sprintf($_conf_msg[$_lang]['chk'][3],$err);
        }
        return false;
    }else{
        return true;
    }
}

//数値チェック（小数点ＯＫ）
# 2011/02/23 -------------mod -------------
# function _numCheck($val,$err){
//function _numCheck($val,$err,$signed=false){
//2017/09/27 Mod
// $ketaには「整数部桁数.小数部桁数」で指定する（例) 4.2 なら xxxx.xx
function _numCheck($val,$err,$signed=false,$keta=-1){
# 2011/02/23 -------------mod -------------
    global $_GLOBAL_err_msg,$_GLOBAL_fld_msg, $_conf_msg;
    global $_lang;
    if($_lang==''){ $_lang='J'; }

# 2011/02/25 -------------mod start-------------

if($val=="") return true; //2011/05/11 Add

    //2017/09/27 Mod ------ Before ----------
    // if(strpos($val, ".") === false){
    //     if($signed){
    //         $regex = '/^\-?[0-9]+$/';
    //     } else {
    //         $regex = '/^[0-9]+$/';
    //     }
    // } else {
    //     if($signed){
    //         $regex = '/^\-?[0-9]+\.[0-9]+$/';
    //     } else {
    //         $regex = '/^[0-9]+\.[0-9]+$/';
    //     }
    // }
    //2017/09/27 Mod ------ After ----------
    if($keta==-1){
        if(strpos($val, ".") === false){
            if($signed){
                $regex = '/^\-?[0-9]+$/';
            } else {
                $regex = '/^[0-9]+$/';
            }
        } else {
            if($signed){
                $regex = '/^\-?[0-9]+\.[0-9]+$/';
            } else {
                $regex = '/^[0-9]+\.[0-9]+$/';
            }
        }
    }else{
        $wArr = explode('.',''.$keta);
        $seisuu_keta = intval($wArr[0]);
        $syousuu_keta = intval($wArr[1]);

        if(strpos($val, ".") === false){
            if(strlen( str_replace("-","", $val) ) > $seisuu_keta){
                if( $err != ""){
                    $_GLOBAL_err_msg[]  = sprintf($_conf_msg[$_lang]['chk'][4]."整数部:".$seisuu_keta."桁、小数部:".$syousuu_keta,$err);
                    $_GLOBAL_fld_msg[$GLOBALS['fldkey']][]  = sprintf($_conf_msg[$_lang]['chk'][4]."整数部:".$seisuu_keta."桁、小数部:".$syousuu_keta,$err);
                }
                return false;
            }

            if($signed){
                $regex = '/^\-?[0-9]+$/';
            } else {
                $regex = '/^[0-9]+$/';
            }
        } else {

            $wrk = str_replace("-","", $val);
            $wArr2 = explode(".",$wrk);
            if(strlen( $wArr2[0] ) > $seisuu_keta){
                if( $err != ""){
                    $_GLOBAL_err_msg[]  = sprintf($_conf_msg[$_lang]['chk'][4]."整数部:".$seisuu_keta."桁、小数部:".$syousuu_keta,$err);
                    $_GLOBAL_fld_msg[$GLOBALS['fldkey']][]  = sprintf($_conf_msg[$_lang]['chk'][4]."整数部:".$seisuu_keta."桁、小数部:".$syousuu_keta,$err);
                }
                return false;
            }
            if(strlen( $wArr2[1] ) > $syousuu_keta){
                if( $err != ""){
                    $_GLOBAL_err_msg[]  = sprintf($_conf_msg[$_lang]['chk'][4]."整数部:".$seisuu_keta."桁、小数部:".$syousuu_keta,$err);
                    $_GLOBAL_fld_msg[$GLOBALS['fldkey']][]  = sprintf($_conf_msg[$_lang]['chk'][4]."整数部:".$seisuu_keta."桁、小数部:".$syousuu_keta,$err);
                }
                return false;
            }

            if($signed){
                $regex = '/^\-?[0-9]+\.[0-9]+$/';
            } else {
                $regex = '/^[0-9]+\.[0-9]+$/';
            }
        }

    }
    //2017/09/27 Mod ------ End ----------


    //if(!preg_match('/^[\-\d]*$/', $val)){
    //if(!preg_match('/^[\d]*$/', $val)){
    # if(!preg_match('/^[0-9.]*$/', $val)){
    if(!preg_match($regex, $val)){
        if( $err != ""){
#            $_GLOBAL_err_msg[]  = $err . " は半角数字で入力してください。";
            $_GLOBAL_err_msg[]  = sprintf($_conf_msg[$_lang]['chk'][4],$err);
            $_GLOBAL_fld_msg[$GLOBALS['fldkey']][]  = sprintf($_conf_msg[$_lang]['chk'][4],$err);
        }
        return false;
    }else{
        return true;
    }
# 2011/02/25 -------------mod end-------------

}

//数値チェック（小数点ＮＧ）
# 2011/02/23 -------------mod -------------
# function _seisuuCheck($val,$err){
function _seisuuCheck($val,$err,$signed=false){
# 2011/02/23 -------------mod -------------
    global $_GLOBAL_err_msg,$_GLOBAL_fld_msg, $_conf_msg;
    global $_lang;
    if($_lang==''){ $_lang='J'; }

# 2011/02/23 -------------mod -------------
    if($signed){
        $regex = '/^\-?[0-9]*$/';
    } else {
        $regex = '/^[0-9]*$/';
    }
    # if(!preg_match('/^[0-9]*$/', $val)){
    if(!preg_match($regex, $val)){
        if( $err != ""){
#            $_GLOBAL_err_msg[]  = $err . " は半角整数で入力してください。";
            $_GLOBAL_err_msg[]  = sprintf($_conf_msg[$_lang]['chk'][5],$err);
            $_GLOBAL_fld_msg[$GLOBALS['fldkey']][]  = sprintf($_conf_msg[$_lang]['chk'][5],$err);
        }
        return false;
    }else{
        return true;
    }
# 2011/02/23 -------------mod -------------
}

//英数字チェック
function _eisuuCheck($val,$err){
    global $_GLOBAL_err_msg,$_GLOBAL_fld_msg, $_conf_msg;
    global $_lang;
    if($_lang==''){ $_lang='J'; }


    if(!preg_match('/^[a-zA-Z\d]*$/', $val)){
        if( $err != ""){
#            $_GLOBAL_err_msg[]  = $err . " は半角英数字で入力してください。";
            $_GLOBAL_err_msg[]  = sprintf($_conf_msg[$_lang]['chk'][6],$err);
            $_GLOBAL_fld_msg[$GLOBALS['fldkey']][]  = sprintf($_conf_msg[$_lang]['chk'][6],$err);
        }
        return false;
    }else{
        return true;
    }
}

//英字チェック
function _eijiCheck($val,$err){
    global $_GLOBAL_err_msg,$_GLOBAL_fld_msg, $_conf_msg;
    global $_lang;
    if($_lang==''){ $_lang='J'; }


    if(!preg_match('/^[a-zA-Z]*$/', $val)){
        if( $err != ""){
#            $_GLOBAL_err_msg[]  = $err . " は半角英字で入力してください。";
            $_GLOBAL_err_msg[]  = sprintf($_conf_msg[$_lang]['chk'][23],$err);
            $_GLOBAL_fld_msg[$GLOBALS['fldkey']][]  = sprintf($_conf_msg[$_lang]['chk'][23],$err);
        }
        return false;
    }else{
        return true;
    }


}

//英数字と「-」「_」チェック
function _eisuuBarCheck($val,$err){
    global $_GLOBAL_err_msg,$_GLOBAL_fld_msg, $_conf_msg;
    global $_lang;
    if($_lang==''){ $_lang='J'; }


    if(!preg_match('/^[a-zA-Z\d\-_]*$/', $val)){
        if( $err != ""){
#            $_GLOBAL_err_msg[]  = $err . " は半角英数字と「_」「-」で入力してください。";
            $_GLOBAL_err_msg[]  = sprintf($_conf_msg[$_lang]['chk'][1],$err);
            $_GLOBAL_fld_msg[$GLOBALS['fldkey']][]  = sprintf($_conf_msg[$_lang]['chk'][1],$err);
        }
        return false;
    }else{
        return true;
    }
}

//2017/09/11 Add ---------------- Strat -------------
//桁チェック
function _lenCheck($input_value,$len,$err){
    global $_GLOBAL_err_msg,$_GLOBAL_fld_msg, $_conf_msg;
    global $_lang;
    if($_lang==''){ $_lang='J'; }


    if($input_value!='' && mb_strlen($input_value) != $len){
        if( $err != ""){
            $_GLOBAL_err_msg[]  = "" . $err . "は" . sprintf($_conf_msg[$_lang]['chk'][26],$len);
            $_GLOBAL_fld_msg[$GLOBALS['fldkey']][]  = "" . $err . "は" . sprintf($_conf_msg[$_lang]['chk'][26],$len);
        }
        return false;
    }else{
        return true;
    }
}
//2017/09/11 Add ---------------- End -------------

//長さチェック(max)
function _maxLenCheck($input_value,$max,$err){
    global $_GLOBAL_err_msg,$_GLOBAL_fld_msg, $_conf_msg;
    global $_lang;
    if($_lang==''){ $_lang='J'; }


    //if(strlen($input_value) > $max){
    //2011/03/04 Mod -------- Strat ---------
    if(mb_strlen($input_value) > $max){
    //2011/03/04 Mod -------- End ---------
        if( $err != ""){
#            $_GLOBAL_err_msg[] = $err . "は" . $max . "桁以下で入力してください。";
            $_GLOBAL_err_msg[]  = "" . $err . "は" . sprintf($_conf_msg[$_lang]['chk'][7],$max);
            $_GLOBAL_fld_msg[$GLOBALS['fldkey']][]  = "" . $err . "は" . sprintf($_conf_msg[$_lang]['chk'][7],$max);
        }
        return false;
    }else{
        return true;
    }
}

//長さチェック(min)
function _minLenCheck($input_value,$min,$err){
    global $_GLOBAL_err_msg,$_GLOBAL_fld_msg, $_conf_msg;
    global $_lang;
    if($_lang==''){ $_lang='J'; }


    //if(strlen($input_value) < $min){
    //2011/03/04 Mod -------- Strat ---------
    //$sjis_str = mb_convert_encoding( $input_value,'sjis-win',_ENCODING_SRC);
    //if(mb_strlen($sjis_str) < $min){
    //2018/05/16 Mod
    if(mb_strlen($input_value) < $min){
    //2011/03/04 Mod -------- End ---------
        if( $err != ""){
#            $_GLOBAL_err_msg[] = $err . "は" . $min . "桁以上で入力してください。";
            $_GLOBAL_err_msg[]  = "" . $err . "は" . sprintf($_conf_msg[$_lang]['chk'][2],$min);
            $_GLOBAL_fld_msg[$GLOBALS['fldkey']][]  = "" . $err . "は" . sprintf($_conf_msg[$_lang]['chk'][2],$min);
        }
        return false;
    }else{
        return true;
    }
}

//長さチェック(zmax)全角文字数版 2007/08/31 Add
function _zen_maxLenCheck($input_value,$max,$err){
    global $_GLOBAL_err_msg,$_GLOBAL_fld_msg, $_conf_msg;
    global $_lang;
    if($_lang==''){ $_lang='J'; }


    //if(strlen($input_value) > ($max*2)){
    //2011/03/04 Mod -------- Strat ---------
    $sjis_str = mb_convert_encoding( $input_value,'sjis-win',_ENCODING_SRC);
    if(strlen($sjis_str) > ($max*2)){
    //2011/03/04 Mod -------- End ---------
        if( $err != ""){
#            $_GLOBAL_err_msg[] = $err . "は" . $max . "文字以下で入力してください。";
            $_GLOBAL_err_msg[]  = "" . $err . "は" . sprintf($_conf_msg[$_lang]['chk'][24],$max);
            $_GLOBAL_fld_msg[$GLOBALS['fldkey']][]  = "" . $err . "は" . sprintf($_conf_msg[$_lang]['chk'][24],$max);
        }
        return false;
    }else{
        return true;
    }
}

//長さチェック(zmin)全角文字数版 2007/08/31 Add
function _zen_minLenCheck($input_value,$min,$err){
    global $_GLOBAL_err_msg,$_GLOBAL_fld_msg, $_conf_msg;
    global $_lang;
    if($_lang==''){ $_lang='J'; }


    //if(strlen($input_value) < ($min*2)){
    //2011/03/04 Mod -------- Strat ---------
    $sjis_str = mb_convert_encoding( $input_value,'sjis-win',_ENCODING_SRC);
    if(strlen($sjis_str) < ($min*2)){
    //2011/03/04 Mod -------- End ---------
        if( $err != ""){
#            $_GLOBAL_err_msg[] = $err . "は" . $min . "文字以上で入力してください。";
            $_GLOBAL_err_msg[]  = "" . $err . "は" . sprintf($_conf_msg[$_lang]['chk'][25],$min);
            $_GLOBAL_fld_msg[$GLOBALS['fldkey']][]  = "" . $err . "は" . sprintf($_conf_msg[$_lang]['chk'][25],$min);
        }
        return false;
    }else{
        return true;
    }
}

//Emailチェック
function _emailCheck($TheValue,$err)
{
    global $_GLOBAL_err_msg,$_GLOBAL_fld_msg, $_conf_msg;
    global $_lang;
    global $_ILLEGAL_MAIL_OK_FLG;

    if($_lang==''){ $_lang='J'; }
    if($_ILLEGAL_MAIL_OK_FLG==''){ $_ILLEGAL_MAIL_OK_FLG = false; }

    if($TheValue=="") return true;

    //2010/02/05 Add
//2010/09/21 mod -----------
#     $w_sp = split('@',$TheValue);
    $w_sp = explode('@',$TheValue);
//2010/09/21 mod -----------
    if(_count($w_sp) != 2){
        if( $err != ""){
#            $_GLOBAL_err_msg[] = $err . "が不正です。";
            $_GLOBAL_err_msg[]  = sprintf($_conf_msg[$_lang]['chk'][8],$err);
            $_GLOBAL_fld_msg[$GLOBALS['fldkey']][]  = sprintf($_conf_msg[$_lang]['chk'][8],$err);
        }
        return false;
    }

    if($_ILLEGAL_MAIL_OK_FLG == true ){
        $EmailPattern = "/^([a-zA-Z0-9\/\?_\-])+([\.a-zA-Z0-9\/\?_\-])*@([a-zA-Z0-9_\-])+(\.[a-zA-Z0-9_\-]+)+/";
    }else{
        $EmailPattern = "/^([a-zA-Z0-9])+([\.a-zA-Z0-9\/_\-])*@([a-zA-Z0-9_\-])+(\.[a-zA-Z0-9_\-]+)+/";
    }

    if(preg_match($EmailPattern, $TheValue))
    {
        if($_ILLEGAL_MAIL_OK_FLG != true ){
            if(strpos($TheValue,"..") !== FALSE){
                if( $err != ""){
    #               $_GLOBAL_err_msg[] = $err . "で「..」を含むアドレスは登録できません。";
                    $_GLOBAL_err_msg[]  = sprintf($_conf_msg[$_lang]['chk'][20],$err);
                    $_GLOBAL_fld_msg[$GLOBALS['fldkey']][]  = sprintf($_conf_msg[$_lang]['chk'][20],$err);
                }
                return false;
            }
            if(strpos($TheValue,".@") !== FALSE){
                if( $err != ""){
    #               $_GLOBAL_err_msg[] = $err . "で「.@」を含むアドレスは登録できません。";
                    $_GLOBAL_err_msg[]  = sprintf($_conf_msg[$_lang]['chk'][21],$err);
                    $_GLOBAL_fld_msg[$GLOBALS['fldkey']][]  = sprintf($_conf_msg[$_lang]['chk'][21],$err);
                }
                return false;
            }
        }

        return true;
    }else{
        if( $err != ""){
#            $_GLOBAL_err_msg[] = $err . "が不正です。";
            $_GLOBAL_err_msg[]  = sprintf($_conf_msg[$_lang]['chk'][8],$err);
            $_GLOBAL_fld_msg[$GLOBALS['fldkey']][]  = sprintf($_conf_msg[$_lang]['chk'][8],$err);
        }
        return false;
    }
}

//郵便番号チェック
function _postCheck($post_code,$err)
{
    global $_GLOBAL_err_msg,$_GLOBAL_fld_msg, $_conf_msg;
    global $_lang;
    if($_lang==''){ $_lang='J'; }


    if($post_code=="") return true;

    //if( ! ereg("^[0-9][0-9][0-9]-[0-9][0-9][0-9][0-9]$",$post_code) ){
    //2017/04/10 mod
    if( ! preg_match('/^[0-9][0-9][0-9]-[0-9][0-9][0-9][0-9]$/',$post_code) ){
        if( $err != ""){
#            $_GLOBAL_err_msg[] = $err . "は半角数字 XXX-XXXX になるように入力してください。";
            $_GLOBAL_err_msg[]  = sprintf($_conf_msg[$_lang]['chk'][9],$err);
            $_GLOBAL_fld_msg[$GLOBALS['fldkey']][]  = sprintf($_conf_msg[$_lang]['chk'][9],$err);
        }
        return false;
    }else{
        return true;
    }
}

//郵便番号チェック2(ハイフンなし)
function _post2Check($post_code,$err)
{
    global $_GLOBAL_err_msg,$_GLOBAL_fld_msg, $_conf_msg;
    global $_lang;
    if($_lang==''){ $_lang='J'; }


    if($post_code=="") return true;

    //if( ! ereg("^[0-9][0-9][0-9]-[0-9][0-9][0-9][0-9]$",$post_code) ){
    //2017/04/10 mod
    if( ! preg_match('/^[0-9][0-9][0-9][0-9][0-9][0-9][0-9]$/',$post_code) ){
        if( $err != ""){
            $_GLOBAL_err_msg[]  = $err."が正しくありません。";
            $_GLOBAL_fld_msg[$GLOBALS['fldkey']][]  = $err."が正しくありません。";
        }
        return false;
    }else{
        return true;
    }
}

//電話番号チェック（ハイフンあり）
function _telCheck($tel_number,$err)
{
    global $_GLOBAL_err_msg,$_GLOBAL_fld_msg, $_conf_msg;
    global $_lang;
    if($_lang==''){ $_lang='J'; }


    if($tel_number=="") return true;

#    if( ! eregi("^([0-9]+)-([a-z,0-9,-]+)",$Tel)){
#    if( ! ereg("^[0-9-]{9,30}$",$tel_number) ){
    //if( ! ereg("^0[0-9]{1,3}-[0-9]{2,4}-[0-9]{3,4}$",$tel_number) ){
    //Mod 2011/02/21
    //if( ! ereg("^0[0-9]{1,3}-[0-9]{2,4}-[0-9]{2,4}$",$tel_number) ){
    //2017/04/10 Mod
    if( ! preg_match('/^0[0-9]{1,6}-[0-9]{1,4}-[0-9]{2,4}$/',$tel_number) ){
        if( $err != ""){
#            $_GLOBAL_err_msg[] = $err . "の内容が誤っています。";
            $_GLOBAL_err_msg[]  = sprintf($_conf_msg[$_lang]['chk'][10],$err);
            $_GLOBAL_fld_msg[$GLOBALS['fldkey']][]  = sprintf($_conf_msg[$_lang]['chk'][10],$err);
        }
        return false;
    }else{
        //return true;
        //2013/03/26 Mod --------- Strat -----------
        if( strlen(str_replace("-","",$tel_number)) != 10 && strlen(str_replace("-","",$tel_number)) != 11 ){
            if( $err != ""){
                //$_GLOBAL_err_msg[] = $err . "の内容が誤っています。";
                $_GLOBAL_err_msg[]  = sprintf($_conf_msg[$_lang]['chk'][10],$err);
                $_GLOBAL_fld_msg[$GLOBALS['fldkey']][]  = sprintf($_conf_msg[$_lang]['chk'][10],$err);
            }
            return false;
        }else{
            return true;
        }
        //2013/03/26 Mod --------- End -----------
    }
}

//電話番号チェック2(ハイフンなし)
function _tel2Check($tel_number,$err)
{
    global $_GLOBAL_err_msg,$_GLOBAL_fld_msg, $_conf_msg;
    global $_lang;
    if($_lang==''){ $_lang='J'; }


    if($tel_number=="") return true;

    //if( ! ereg("^0[0-9]{1,11}$",$tel_number) ){
    //2017/04/10 mod
    if( ! preg_match('/^0[0-9]{1,12}$/',$tel_number) ){
        if( $err != ""){
            $_GLOBAL_err_msg[]  = sprintf($_conf_msg[$_lang]['chk'][10],$err);
            $_GLOBAL_fld_msg[$GLOBALS['fldkey']][]  = sprintf($_conf_msg[$_lang]['chk'][10],$err);
        }
        return false;
    }else{
        if( strlen(str_replace("-","",$tel_number)) != 10 && strlen(str_replace("-","",$tel_number)) != 11 ){
            if( $err != ""){
                $_GLOBAL_err_msg[]  = sprintf($_conf_msg[$_lang]['chk'][10],$err);
                $_GLOBAL_fld_msg[$GLOBALS['fldkey']][]  = sprintf($_conf_msg[$_lang]['chk'][10],$err);
            }
            return false;
        }else{
            return true;
        }
    }
}

//半角チェック
function _hankakuCheck($value,$err) {
    global $_GLOBAL_err_msg,$_GLOBAL_fld_msg, $_conf_msg;
    global $_lang;
    if($_lang==''){ $_lang='J'; }

    if($value=="") return true;

    //if (strlen($value) == mb_strlen($value)) {
    //2018/08/05 Mod --- Start ---
    $sjis_str = mb_convert_encoding( $value,'sjis-win',_ENCODING_SRC);
    if (strlen($sjis_str) == mb_strlen($value) ) {
    //2018/08/05 Mod --- End ---
        return true;
    }else{
      if( $err != ""){
#        $_GLOBAL_err_msg[] = $err . "は半角で入力してください。";
        $_GLOBAL_err_msg[]  = sprintf($_conf_msg[$_lang]['chk'][18],$err);
        $_GLOBAL_fld_msg[$GLOBALS['fldkey']][]  = sprintf($_conf_msg[$_lang]['chk'][18],$err);
      }
      return false;
    }
}

//全角のみ(半角不可)チェック
function _zenkakuCheck($value,$err) {
    global $_GLOBAL_err_msg,$_GLOBAL_fld_msg, $_conf_msg;
    global $_lang;
    if($_lang==''){ $_lang='J'; }

    if($value=="") return true;

    //if (strlen($value) == (mb_strlen($value) * 2) ) {
    //2011/03/04 Mod -------- Strat ---------
    $sjis_str = mb_convert_encoding( $value,'sjis-win',_ENCODING_SRC);
    if (strlen($sjis_str) == (mb_strlen($value) * 2) ) {
    //2011/03/04 Mod -------- End ---------
        return true;
    }else{
      if( $err != ""){
#        $_GLOBAL_err_msg[] = $err . "は全角で入力してください。";
        $_GLOBAL_err_msg[]  = sprintf($_conf_msg[$_lang]['chk'][19],$err);
        $_GLOBAL_fld_msg[$GLOBALS['fldkey']][]  = sprintf($_conf_msg[$_lang]['chk'][19],$err);
      }
      return false;
    }
}

//全角ひらがなチェック
function _zenhiraCheck($value,$err){
    global $_GLOBAL_err_msg,$_GLOBAL_fld_msg, $_conf_msg;
    global $_lang;
    if($_lang==''){ $_lang='J'; }

    if($value=="") return true;

    if (!mb_ereg("^[ぁ-んー－ 　]+$", $value))  {
        if( $err != ""){
            #$_GLOBAL_err_msg[] = $err . "は全角ひらがなで入力してください。";
            $_GLOBAL_err_msg[]  = sprintf($_conf_msg[$_lang]['chk'][22],$err);
            $_GLOBAL_fld_msg[$GLOBALS['fldkey']][]  = sprintf($_conf_msg[$_lang]['chk'][22],$err);
        }
        return false;
    }
    return true;
}

//半角カタカナチェック
function _hankanaCheck($value,$err){
    global $_GLOBAL_err_msg,$_GLOBAL_fld_msg, $_conf_msg;
    global $_lang;
    if($_lang==''){ $_lang='J'; }

    if($value=="") return true;

    //if (!mb_ereg("^[ｱ-ﾝﾞﾟｧ-ｫｬ-ｮｯｰ｡｢｣､]+$", $value)) {
    if (!preg_match('/^[ｦ-ﾟｰ \-･､\.\_\(\) ]+$/u', $value)){
    //if (!mb_ereg('^[｡-ﾟ]+$', $str) ){
        $_GLOBAL_err_msg[]  = sprintf($_conf_msg[$_lang]['chk'][27],$err);
        $_GLOBAL_fld_msg[$GLOBALS['fldkey']][]  = sprintf($_conf_msg[$_lang]['chk'][27],$err);
        return false;
    }else{
        return true;
    }
}

//全角カタカナチェック
function _zenkanaCheck($value,$err){
    global $_GLOBAL_err_msg,$_GLOBAL_fld_msg, $_conf_msg;
    global $_lang;
    if($_lang==''){ $_lang='J'; }



    if($value=="") return true;
//echo strhex($value);
    $ng = 0;
    if(mb_internal_encoding()=="SJIS"){
        #(SJIS版) 、、「－」
        $ans = preg_replace( '/\x83[\x40-\x96]/', '',$value);   //ァ-ヶ
        $ans = preg_replace( '/\x81\x40/', '',$ans);            //全角スペース
        $ans = preg_replace( '/\x20/', '',$ans);                //半角スペース
        $ans = preg_replace( '/\x81\x5b/', '',$ans);            //「ー」
        $ans = preg_replace( '/（/', '',$ans);            //「（」口座名義用に追加 2006/11/24
        $ans = preg_replace( '/）/', '',$ans);            //「）」口座名義用に追加 2006/11/24
        if( strlen($ans)>0 ){
            $ng = 1;
        }
    }elseif(mb_internal_encoding()=="EUC-JP"){
        #(EUC版)
        //if( ! ereg( "^(\xA5[\xA1-\xF6]|\xA1\xBC|\xA1\xA6|\xA1\xA1|\x20)+$", $value)){
        //2017/04/10 mod
        if( ! preg_match( '/^(\xA5[\xA1-\xF6]|\xA1\xBC|\xA1\xA6|\xA1\xA1|\x20)+$/', $value)){
            $ng = 1;
        }
    }else{
        #その他UTF8 2010/0228 Add
        if (!mb_ereg("^[ァ-ンー－― 　ヴ・１２３４５６７８９０1234567890＆（）]+$", $value))  {
            $ng = 1;
        }
    }

    if($ng==1){
      if( $err != ""){
#        $_GLOBAL_err_msg[] = $err . "は全角カナで入力してください。";
        $_GLOBAL_err_msg[]  = sprintf($_conf_msg[$_lang]['chk'][11],$err);
        $_GLOBAL_fld_msg[$GLOBALS['fldkey']][]  = sprintf($_conf_msg[$_lang]['chk'][11],$err);
      }
      return false;
    }else{
      return true;
    }

}
function strhex($string)
{
  $hex="";
  for ($i=0;$i<strlen($string);$i++)
      $hex.=dechex(ord($string[$i]));
  return $hex;
}

//比較チェック
function _matchCehck($match1,$match2,$err1,$err2)
{
    global $_GLOBAL_err_msg,$_GLOBAL_fld_msg, $_conf_msg;
    global $_lang;
    if($_lang==''){ $_lang='J'; }

    if( $match1!="" && $match2!=""){
        //if( !ereg($match1,$match2)){
        if( $match1!=$match2 ){
            if( $err1 != ""){
#                $_GLOBAL_err_msg[] = "入力された" . $err1 . "と" . $err2 ."の内容が一致しません。";
                $_GLOBAL_err_msg[]  = sprintf($_conf_msg[$_lang]['chk'][12],$err1,$err2);
                $_GLOBAL_fld_msg[$GLOBALS['fldkey']][]  = sprintf($_conf_msg[$_lang]['chk'][12],$err1,$err2);
            }
            return false;
        }else{
            return true;
        }
    }
    return true;
}

//日付チェック(yyyy/mm/dd)
function _dateCheck($input_data,$err)
{
    global $_GLOBAL_err_msg,$_GLOBAL_fld_msg, $_conf_msg;
    global $_lang;
    if($_lang==''){ $_lang='J'; }


    if($input_data=="") return true;

    //if( ! ereg("^[1-2][0-9][0-9][0-9]/[0-1][0-9]/[0-3][0-9]$",$input_data) ){
    //2017/04/10 mod
    if( ! preg_match('/^[1-2][0-9][0-9][0-9]\/[0-1][0-9]\/[0-3][0-9]$/',$input_data) ){
        if( $err != ""){
#            $_GLOBAL_err_msg[] = $err . "は半角数字 yyyy/mm/dd 形式で入力してください。";
            $_GLOBAL_err_msg[]  = sprintf($_conf_msg[$_lang]['chk'][13],$err);
            $_GLOBAL_fld_msg[$GLOBALS['fldkey']][]  = sprintf($_conf_msg[$_lang]['chk'][13],$err);
        }
        return false;
    }

    $out_data = explode("/",$input_data );

    if ( !checkdate($out_data[1], $out_data[2], $out_data[0])) {
        if( $err != ""){
#            $_GLOBAL_err_msg[] = $err . "には正しい日付を入力してください。";
            $_GLOBAL_err_msg[]  = sprintf($_conf_msg[$_lang]['chk'][14],$err);
            $_GLOBAL_fld_msg[$GLOBALS['fldkey']][]  = sprintf($_conf_msg[$_lang]['chk'][14],$err);
        }
        return false;
    }else{
        return true;
    }
}

//時刻チェック(hh:nn)
function _timeCheck($input_data,$err)
{
    global $_GLOBAL_err_msg,$_GLOBAL_fld_msg, $_conf_msg;
    global $_lang;
    if($_lang==''){ $_lang='J'; }


    if($input_data=="") return true;

    //if( ! ereg("^[0-2][0-9]:[0-5][0-9]$",$input_data) ){
    //2017/04/10 Mod
    if( ! preg_match('/^[0-2][0-9]:[0-5][0-9]$/',$input_data) ){
        if( $err != ""){
#            $_GLOBAL_err_msg[] = $err . "は半角数字 hh:mm 形式で入力してください。";
            $_GLOBAL_err_msg[]  = sprintf($_conf_msg[$_lang]['chk'][15],$err);
            $_GLOBAL_fld_msg[$GLOBALS['fldkey']][]  = sprintf($_conf_msg[$_lang]['chk'][15],$err);
        }
        return false;
    }

    $out_data = explode(":",$input_data );

    if ( (intval($out_data[0]) > 23) || (intval($out_data[1]) > 59) ) {
        if( $err != ""){
#            $_GLOBAL_err_msg[] = $err . "には正しい時刻を入力してください。";
            $_GLOBAL_err_msg[]  = sprintf($_conf_msg[$_lang]['chk'][16],$err);
            $_GLOBAL_fld_msg[$GLOBALS['fldkey']][]  = sprintf($_conf_msg[$_lang]['chk'][16],$err);
        }
        return false;
    }else{
        return true;
    }
}

function _time24Check($input_data,$err){
    if( $input_data=="24:00" ||
        $input_data=="25:00" ||
        $input_data=="26:00" ||
        $input_data=="27:00" ||
        $input_data=="28:00" ||
        $input_data=="29:00" ||
        $input_data=="30:00" ||
        $input_data=="31:00" ||
        $input_data=="32:00" ||
        $input_data=="33:00" ||
        $input_data=="34:00" ||
        $input_data=="35:00" ||
        $input_data=="36:00" ){
        return true;
    } 
    return _timeCheck($input_data,$err);
}

//時刻チェック(hh:nn:ss) 2016/12/08 Add
function _hhmmssCheck($input_data,$err)
{
    global $_GLOBAL_err_msg,$_GLOBAL_fld_msg, $_conf_msg;
    global $_lang;
    if($_lang==''){ $_lang='J'; }


    if($input_data=="") return true;

    //if( ! ereg("^[0-2][0-9]:[0-5][0-9]:[0-5][0-9]$",$input_data) ){
    //2017/04/10 Mod
    if( ! preg_match('/^[0-2][0-9]:[0-5][0-9]:[0-5][0-9]$/',$input_data) ){
        if( $err != ""){
#            $_GLOBAL_err_msg[] = $err . "は半角数字 hh:mm 形式で入力してください。";
            $_GLOBAL_err_msg[]  = sprintf($_conf_msg[$_lang]['chk'][15],$err);
            $_GLOBAL_fld_msg[$GLOBALS['fldkey']][]  = sprintf($_conf_msg[$_lang]['chk'][15],$err);
        }
        return false;
    }

    $out_data = explode(":",$input_data );

    if ( (intval($out_data[0]) > 23) || (intval($out_data[1]) > 59) || (intval($out_data[2]) > 59) ) {
        if( $err != ""){
#            $_GLOBAL_err_msg[] = $err . "には正しい時刻を入力してください。";
            $_GLOBAL_err_msg[]  = sprintf($_conf_msg[$_lang]['chk'][16],$err);
            $_GLOBAL_fld_msg[$GLOBALS['fldkey']][]  = sprintf($_conf_msg[$_lang]['chk'][16],$err);
        }
        return false;
    }else{
        return true;
    }
}

//携帯メアドチェック
function _mobileMailCheck($mail_addr,$err)
{
    global $_GLOBAL_err_msg,$_GLOBAL_fld_msg, $_conf_msg;
    global $_lang;
    if($_lang==''){ $_lang='J'; }


    if($mail_addr=="") return true;

    $err_flg = false;

//2010/09/21 mod -----------
#     $fld = split('@',$mail_addr);
    $fld = explode('@',$mail_addr);
//2010/09/21 mod -----------

    switch ($fld[1]) {
        case "docomo.ne.jp":
            $err_flg = true;
            break;
        case "docomo-camera.ne.jp":
            $err_flg = true;
            break;
        case "ebilling.ne.jp":
            $err_flg = true;
            break;
        case "docomo-bill.ne.jp":
            $err_flg = true;
            break;
        case "mail.visualnet.mopera.ne.jp":
            $err_flg = true;
            break;
        case "d.vodafone.ne.jp":
            $err_flg = true;
            break;
        case "h.vodafone.ne.jp":
            $err_flg = true;
            break;
        case "t.vodafone.ne.jp":
            $err_flg = true;
            break;
        case "c.vodafone.ne.jp":
            $err_flg = true;
            break;
        case "r.vodafone.ne.jp":
            $err_flg = true;
            break;
        case "k.vodafone.ne.jp":
            $err_flg = true;
            break;
        case "n.vodafone.ne.jp":
            $err_flg = true;
            break;
        case "s.vodafone.ne.jp":
            $err_flg = true;
            break;
        case "q.vodafone.ne.jp":
            $err_flg = true;
            break;
        case "k.vodafone.ne.jp":
            $err_flg = true;
            break;
        case "ezweb.ne.jp":
            $err_flg = true;
            break;
        case "ido.ne.jp":
            $err_flg = true;
            break;
        case "sky.tkk.ne.jp":
            $err_flg = true;
            break;
        case "sky.tkc.ne.jp":
            $err_flg = true;
            break;
        case "sky.tu-ka.ne.jp":
            $err_flg = true;
            break;
        case "softbank.ne.jp":
            $err_flg = true;
            break;
        //2008/12/25 Add
        case "disney.ne.jp":
            $err_flg = true;
            break;
        default:
            if(strlen($fld[1])>=9){
                if( substr($fld[1],strlen($fld[1])-9,9)=="pdx.ne.jp"){
                    $err_flg = true;
                }
            }
            break;
    }

    if( $err_flg == false ){
        if( $err != ""){
#            $_GLOBAL_err_msg[] = $err . "携帯メールアドレスではありません。";
            $_GLOBAL_err_msg[]  = sprintf($_conf_msg[$_lang]['chk'][17],$err);
            $_GLOBAL_fld_msg[$GLOBALS['fldkey']][]  = sprintf($_conf_msg[$_lang]['chk'][17],$err);
        }
    }else{
        $err_flg = _emailCheck( $mail_addr, $err );
    }
    return $err_flg;
}

function _urlCheck( $url_str, $err ) {
    global $_GLOBAL_err_msg,$_GLOBAL_fld_msg, $_conf_msg;
    global $_lang;
    if($_lang==''){ $_lang='J'; }


    if( $url_str == "" ) return true;

    //if( !ereg( "^(http|https):\/\/([a-zA-Z0-9]|\.|\-|_|/|\?|=|~|%|&|#)+$", $url_str ) ) {
    //2017/04/10 Mod
    if( !preg_match( '/^(http|https):\/\/([a-zA-Z0-9]|\.|\-|_|\/|\?|=|~|%|&|#)+$/', $url_str ) ) {
        if( $err != ""){
#            $_GLOBAL_err_msg[] = $err . "が不正です。";
            $_GLOBAL_err_msg[]  = sprintf($_conf_msg[$_lang]['chk'][8],$err);
            $_GLOBAL_fld_msg[$GLOBALS['fldkey']][]  = sprintf($_conf_msg[$_lang]['chk'][8],$err);
        }
        return false;
    }else{
        return true;
    }
}

function _dateCheckMake( $_base_name , &$_request, $hissu, $err ){
    global $_GLOBAL_err_msg,$_GLOBAL_fld_msg, $_conf_msg;
    global $_lang;
    if($_lang==''){ $_lang='J'; }

    if($hissu==false){
        if($_request['Year_'. $_base_name] == "" && $_request['Month_'. $_base_name] == "" && $_request['Day_'. $_base_name] == ""){
            return true;
        }
    }else{
        if($_request['Year_'. $_base_name] == "" || $_request['Month_'. $_base_name] == "" || $_request['Day_'. $_base_name] == ""){
            if( $err != ""){
#                $_GLOBAL_err_msg[] = $err . "は必須入力です。";
                $_GLOBAL_err_msg[]  = sprintf($_conf_msg[$_lang]['chk'][3],$err);
                $_GLOBAL_fld_msg[$GLOBALS['fldkey']][]  = sprintf($_conf_msg[$_lang]['chk'][3],$err);
            }
            return false;
        }
    }

    $mm = $_request['Month_'. $_base_name];
    if( _seisuuCheck($mm,'')){
        $mm = sprintf("%02d",$mm);
    }

    $dd = $_request['Day_'. $_base_name];
    if( _seisuuCheck($dd,'')){
        $dd = sprintf("%02d",$dd);
    }

    $ymd = $_request['Year_'. $_base_name] . "/" . $mm . "/" . $dd;
    $ret = _dateCheck($ymd,$err);
    if($ret){
        $_request[$_base_name] = $ymd;
    }
    return $ret;

}

//2010/01/22 Add
//SRC<->DB間の文字コード変換不可チェック（機種依存文字チェック）
function _kisyuizonCheck($value,$err){
    global $_GLOBAL_err_msg,$_GLOBAL_fld_msg, $_conf_msg;
    global $_lang;
    if($_lang==''){ $_lang='J'; }

    if($value=="") return true;

    $_val_moto = $value;
    $_val_conv = mb_convert_encoding(mb_convert_encoding($_val_moto,_ENCODING_DB,_ENCODING_SRC),_ENCODING_SRC,_ENCODING_DB);

    if ($_val_moto != $_val_conv)  {
        if( $err != ""){
            $ng_moji = "";
            for($i=0;$i<mb_strlen($_val_moto);$i++){
                if(mb_substr($_val_moto,$i,1) != mb_substr($_val_conv,$i,1)){
                    if($ng_moji!="") $ng_moji .= ",";
                    $ng_moji .= mb_substr($_val_moto,$i,1);
                }
            }
            #$_GLOBAL_err_msg[] = $err . "に利用できない文字が含まれています。";
            $_GLOBAL_err_msg[]  = sprintf($_conf_msg[$_lang]['chk'][26],$err,$ng_moji);
            $_GLOBAL_fld_msg[$GLOBALS['fldkey']][]  = sprintf($_conf_msg[$_lang]['chk'][26],$err,$ng_moji);
        }
        return false;
    }
    return true;
}

