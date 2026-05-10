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
    //require( "../lib/picture.php" )z;
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

    if( $_request['tanarea_id'] == "" ){
        //来場者編集から呼ばれたので、所属選択後に担当者を選択してもらうため
        $return_arr['data']['syozoku_recs'] = array();
    }else{
        $conn = _dbConnect();

        $sql = "";
        $sql .= " select m_syozoku.*"."\n";
        $sql .= " from m_tantou_area"."\n";
        $sql .= " inner join m_syozoku on(syozoku_tanarea_id = tanarea_id and syozoku_delete_date is null)"."\n";
        $sql .= " where tanarea_delete_date is null"."\n";
        $sql .= " and tanarea_id = "._as($_request['tanarea_id'])."\n";
        $sql .= " order by tanarea_id asc"."\n";
        $syozoku_recs = _select($sql);

        $return_arr['data']['syozoku_recs'] = $syozoku_recs;

        _dbDisconnect( $conn );
    }

    header('Content-type: application/json;  charset="UTF-8"');
    jsonPush($return_arr);
    exit();
