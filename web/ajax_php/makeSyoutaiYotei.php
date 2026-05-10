<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');

    // ******************************************************************************************************
    // INCLUDE FILES
    // ******************************************************************************************************
    $project_name_prefix = "admin_";
    require( "../lib/environment.php" );
    require( "../lib/Smarty.class.php" );
    require( "../lib/UserSmarty.php" );
    require( "../lib/lang.php" );
    require( "../lib/inc.php" );
    require( "../lib/check.php" );
    require( "../lib/project.php" );

    // *****************************
    // NG返却関数
    // *****************************
    function _ngReturn($errMsg){
        global $conn;

        if($conn!==null){
            _dbDisconnect( $conn );    
        }
        $return_arr = array();
        $return_arr['status'] = "NG";
        $return_arr['error_message'] = $errMsg;
        header('Content-type: application/json;  charset="UTF-8"');
        jsonPush($return_arr);
        exit();
    }

    $return_arr = array();
    $return_arr['status'] = "OK";
    $return_arr['error_message'] = "";
    $return_arr['data'] = array();

    if( $_request['event_id']=="" ){
        _ngReturn( "イベントが選択されていません。" );
    }else{
        $conn = _dbConnect();

        $sql = "";
        $sql .= " select event_syoutai_yotei_time"."\n";
        $sql .= " from m_event"."\n";
        $sql .= " where event_delete_date is null"."\n";
        $sql .= " and event_id = '"._as($_request['event_id'])."'"."\n";
        $event_recs = _select($sql);

        $formatted_array = array();
        // 例) "2021/07/27 10:00〜#2021/07/27 12:00〜#2021/07/27 14:00〜#2021/07/28 10:00〜#2021/07/28 12:00〜#2021/07/28 14:00〜"
        $wArr = explode( "#", $event_recs[0]['event_syoutai_yotei_time'] );
        for ($i=0; $i < _count($wArr); $i++) { 
            $dtArr = explode(" ", $wArr[$i],2);
            $w_ymd = $dtArr[0];
            // 例) "7月27日(火)"
            $disp_ymd = date("n月j日",strtotime($dtArr[0]) )."（"._getYoubi($dtArr[0])."）";
            $hi = $dtArr[1];

            $formatted_array[ $w_ymd ]['disp_ymd'] = $disp_ymd;
            $formatted_array[ $w_ymd ]['his'][] = array('hi'=>$hi);

        }
        
        $w_html = "";
        foreach ($formatted_array as $ymd => $info) {
            $w_html .= "<p class=\"syoutai\" style=\"margin-left: 5px;margin-bottom:0px;\">".$info['disp_ymd']."</p>";
            foreach ($info['his'] as $num => $hi_info) {
                $id_ymd = str_replace( '/', '', $ymd );
                $ymd_hi = $ymd." ".$hi_info['hi']; // 空白区切り 例) "2021/07/27 10:00〜"

                $w_html .= "　"; // インデント
                $w_html .= "<input class=\"syoutai_chk\" type=\"checkbox\" name=\"syoutai_yotei_time[]\" value=\"".$ymd_hi."\" id=\"syoutai_".$id_ymd.$hi_info['hi']."\" />";
                $w_html .= "<label class=\"syoutai\" for=\"syoutai_".$ymd_hi."\">".$hi_info['hi']."&nbsp;</label>";
            }
        }

        $return_arr['data']['syoutai_yotei_time'] = $w_html;

        _dbDisconnect( $conn );
    }

    header('Content-type: application/json;  charset="UTF-8"');
    jsonPush($return_arr);
    exit();
