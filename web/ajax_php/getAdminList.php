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

    if($_request['event_id']==""){
        _ngReturn( "イベントが選択されていません。" );
    }elseif($_request['event_id']=="_PASS_" && $_request['syozoku_id']==""){
        //来場者編集から呼ばれたので、所属選択後に担当者を選択してもらうため
        $return_arr['data']['admin_recs'] = array();
    }else{
        $conn = _dbConnect();

        $sql = "";
        $sql .= "select admin_tanarea_id,admin_syozoku_id,admin_id,admin_name,syozoku_name from v_admin";
        if($_request['event_id']!="_PASS_"){
            $sql .= " join v_user on (v_admin.admin_id = v_user.user_admin_id)";
        }
        $sql .= " left join m_syozoku on (m_syozoku.syozoku_id = v_admin.admin_syozoku_id)";
        $sql .= " where";
        $sql .= " admin_delete_date is null";
        $sql .= " and admin_mail != 'admin'";
        if($_request['event_id']!="_PASS_"){
            $sql .= " and user_delete_date is null";
            $sql .= " and user_event_id = '"._as($_request['event_id'])."'";
        }
        if($_request['syozoku_id']!=""){
            $sql .= " and admin_syozoku_id = '".$_request['syozoku_id']."'";
        }
        $sql .= " group by admin_tanarea_id,admin_syozoku_id,admin_id,admin_name,syozoku_name";
        $sql .= " order by admin_tanarea_id asc,admin_syozoku_id asc,admin_id asc";
        $admin_recs = _select($sql);

        $return_arr['data']['admin_recs'] = $admin_recs;

        _dbDisconnect( $conn );
    }

    header('Content-type: application/json;  charset="UTF-8"');
    jsonPush($return_arr);
    exit();
