<?php
    if( !defined("_PROJECT_DISP_NAME") ){
        die("System Error");
    }

    if( $_SESSION[_PROJECT_NAME]['user_login']['user_id']=="" ){
        die("System Error");
    }

    if($_SESSION[_PROJECT_NAME]['direct_login']=="1"){
        # ログアウト処理
        unset($_SESSION[_PROJECT_NAME]['user_login']);
        header("Location: "._SYSTEM_ROOT_URLS."/mypage/".$event_rec['event_url_key']."/");
        exit();
    }

    $sql  = "";
    $sql .= " select ";
    $sql .= "   * ";
    $sql .= " from v_user ";
    $sql .= " left join v_admin on (v_admin.admin_id = v_user.user_admin_id) ";
    $sql .= " where ";
    $sql .= "     user_delete_date is null";
    $sql .= "     and user_id='"._as($_SESSION[_PROJECT_NAME]['user_login']['user_id'])."'";
    $main_rec = _select($sql);

    $sql  = "";
    $sql .= " select ";
    $sql .= "   * ";
    $sql .= " from v_user ";
    $sql .= " left join v_admin on (v_admin.admin_id = v_user.user_admin_id) ";
    $sql .= " where ";
    $sql .= "     user_delete_date is null";
    $sql .= "     and user_event_id='"._as($event_rec['event_id'])."'";
    $sql .= "     and user_agent_mail='"._as($main_rec[0]['user_login_id'])."'";
    $user_recs = _select($sql);

    if (count($user_recs) > 0) {
        $blade->assign('agent_flag', 1);
    }

    $sql  = "";
    $sql .= " select * from m_question ";
    $sql .= " where ";
    $sql .= "   que_event_id='"._as($event_rec['event_id'])."'";
    $que_recs = _select($sql);
    if($que_recs[0]['que_koukai_flg']=="1"){
        $blade->assign('jigo_enq_open',1);
    }

    $raijyousya_kbn = $_conf_raijyousya_kbn[$_SESSION[_PROJECT_NAME]['user_login']['user_big_cate']]; 
    $sankashinai_cnt=0;
    $sankasuru_cnt=0;
    if($raijyousya_kbn=="1"){
        $wrkArr = explode("#", $_SESSION[_PROJECT_NAME]['user_login']['user_raijyou_yotei_time']);
        for ($i=0; $i < _count($wrkArr); $i++) { 
            $wrkDtArr = explode(" ",$wrkArr[$i],2);
            if( $wrkDtArr[0] == '2999/01/01' ){
                $sankashinai_cnt++;
            }else{
                $sankasuru_cnt++;
            }
        }
        if($sankasuru_cnt==0 && $sankashinai_cnt > 0){
            $sanka_shinai_only = true;
        }else{
            $sanka_shinai_only = false;                
        }
    }else{
        $sanka_shinai_only = false;                
    }
    $blade->assign('sanka_shinai_only',$sanka_shinai_only);

    $contents_tpl = "mypage.html";
