<?php
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');

    // ******************************************************************************************************
    // INCLUDE FILES
    // ******************************************************************************************************
    $project_name_prefix = "app_";
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

    $conn = _dbConnect();


    if( ($_request['page']=="getEventList" || $_request['page']=="getDateList" || $_request['page']=="getAreaList" || $_request['page']=="getAppVersion") && _file_exists( './' . $_request['page'] . '.php' ) ){
        require( './' . $_request['page'] . '.php' );
    }elseif( _file_exists( '../visit/sub/' . $_request['page'] . '.php' ) ){

        if($_request['event_id']=="" ){
            _ngReturn( "イベントIDが指定されていません。" );
        }
        if($_request['area_id']=="" ){
            _ngReturn( "エリアIDが指定されていません。" );
        }
        if($_request['ymd']=="" ){
            _ngReturn( "来場日付が指定されていません。" );
        }
        $event_recs = _select("select * from m_event where event_id='"._as($_request['event_id'])."' and event_delete_date is null");
        if(_count($event_recs)==0){
            _ngReturn( "指定されたイベントIDのイベントは存在しません。" );
        }
        $event_rec = $event_recs[0];
        if( $event_rec['event_raikainri_ymd_st'] > $_request['ymd'] || $event_rec['event_raikainri_ymd_ed'] < $_request['ymd'] ){
            _ngReturn( "指定されたイベントでは、この来場日時は存在しません。" );
        }

        if ($_request['area_id'] != "-1") {
            $chk_area_recs = _select("select * from m_area where area_delete_date is null and area_event_id='"._as($_request['event_id'])."' and area_id="._as($_request['area_id'])."");
            if(_count($chk_area_recs)==0){
                _ngReturn( "指定されたイベントIDとエリアIDでのエリアは存在しません。" );
            }
        }

        $return_arr = array();
        $return_arr['status'] = "OK";
        $return_arr['error_message'] = "";
        $return_arr['data'] = array();

        require( '../visit/sub/' . $_request['page'] . '.php' );
    }else{
        _ngReturn( "URL指定が不正です。" );
    }


    //DB切断
    //_query( $conn, "commit" );
    _dbDisconnect( $conn );

    header('Content-type: application/json;  charset="UTF-8"');
    jsonPush($return_arr);
    exit();
