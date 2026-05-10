<?php
if( !defined("_PROJECT_DISP_NAME") ){
    die("System Error");
}

unset($_SESSION[_PROJECT_NAME]);

if($_request['exec']=="qrread"){
    if($_request['qr_code']==""){
        $err_msg[] = "QRコードが読み込まれませんでした。(再度読み込みを行ってください)";
    }elseif( strlen($_request['qr_code']) != 17){
        $err_msg[] = "QRコードが正しく読み込まれませんでした。(再度読み込みを行ってください)";
    }else{
        list($fst,$scd,$code) = explode("-", $_request['qr_code']);
        $rd_event_area_shikibetsu_id = substr($fst,0,1);
        $rd_event_id = "e".substr($fst,1);
        $rd_user_big_cate = substr($scd,0,1);
        $rd_dummy = substr($scd,1);
        $rd_user_id = "u".$code;
        if( $_conf_event_area_shikibetsu_id[$rd_event_area_shikibetsu_id] == ""){
            $err_msg[] = "このQRコードは正しくありません。(ERR001)";
        }elseif( $event_rec['event_area_shikibetsu_id'] != $rd_event_area_shikibetsu_id){
            $err_msg[] = "このQRコードは正しくありません。(ERR002)";
        }elseif( $event_rec['event_id'] != $rd_event_id){
            $err_msg[] = "このQRコードは正しくありません。(ERR003)";
        }else{
            $user_recs = _select("select * from v_user where user_delete_date is null and user_id='"._as($rd_user_id)."'");
            if(_count($user_recs)==0){
                $err_msg[] = "このQRコードは正しくありません。(ERR004)";
            }else{
                if($user_recs[0]['user_event_id']!=$rd_event_id){
                    $err_msg[] = "このQRコードは正しくありません。(ERR005)";
                }else{
                    $blade->assign('login', $_SESSION[_PROJECT_NAME]['user_login']);
                    $_SESSION[_PROJECT_NAME]['user_login'] = $user_recs[0];
                    header("Location: ?page=answer");
                    exit();
                }
            }
        }
    }
}

$contents_tpl = "qr_read.html";
