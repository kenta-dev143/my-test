<?php
    if( !defined("_PROJECT_DISP_NAME") ){
        die("System Error");
    }

    if( $_SESSION[_PROJECT_NAME]['user_login']['user_id']=="" ){
        die("System Error");
    }

    if($_SESSION[_PROJECT_NAME]['direct_login']=="1"){
        header("Location: "._SYSTEM_ROOT_URLS."/mypage/".$event_rec['event_url_key']."/");
        exit();
    }

    // ******************************************************************************************************
    // 初期値
    // ******************************************************************************************************
    $page = $_request['page'];
    $this_sess = &$_SESSION[_PROJECT_NAME][$page];
    $err_msg = array();


    // ******************************************************************************************************
    // 登録・更新・削除
    // ******************************************************************************************************
    if($_request['exec'] == 'save'){

        if($this_sess['token'] != $_request['token']){
            $err_msg[] = 'このデータは処理できませんでした。';
        }elseif( _count( $this_sess ) <= 0 ){
            //**** リロードやボタンダブルクリックでの２重登録抑制
            $err_msg[] = 'このデータは既に処理済みです。';
        }else{


            if( _count($_request['raijyou_yotei_time'])==0 ){
                $err_msg[] = "来場予定日時が選択されていません。";
                $_request['user_raijyou_yotei_time'] = "";
            }else{
                $yotei = "";
                for ($i=0; $i < _count($_request['raijyou_yotei_time']); $i++) { 
                    if($yotei!="") $yotei .= "#";
                    $yotei .= $_request['raijyou_yotei_time'][$i];
                }
                $_request['user_raijyou_yotei_time'] = $yotei;
            }

            //**** POST値をセッションにマージ ****
            $this_sess = _array_merge( $this_sess, $_request );



            if(_count($err_msg) == 0){

                _query($conn,'begin');

                //**************************************************
                //新規の場合新ID発番
                //**************************************************
                $array = array();

                $array['user_raijyou_yotei_time'] = "'"._as($this_sess['user_raijyou_yotei_time'])."'"; //来場予定日時（yyyy/mm/dd HH:ii 形式）',
                $array['user_update_date']         = "'".$_now_timestamp."'"; //更新日時',

                $where = "user_id='"._as($_SESSION[_PROJECT_NAME]['user_login']['user_id'])."'";
                _update( 'm_user', $array, $where );
                $success_msg = "変更が完了いたしました<br><span style=\"font-size:16px;\">引き続き、マイページをご利用ください。";

                _query($conn,'commit');

                $_SESSION[_PROJECT_NAME][$page] = array();
                unset( $_SESSION[_PROJECT_NAME][$page] );
                unset( $this_sess );
                $this_sess = array();


                $_request['exec'] = "";
            }
        }
    }

    // ******************************************************************************************************
    // 初期・完了画面
    // ******************************************************************************************************
    if($_request['exec'] != 'save' && _count($err_msg) == 0){
        $token = rand();
        unset( $_SESSION[_PROJECT_NAME][$page] );
        unset( $this_sess );
        $this_sess = &$_SESSION[_PROJECT_NAME][$page];



        $sql  = "";
        $sql .= " select ";
        $sql .= "   * ";
        $sql .= " from v_user ";
        $sql .= " left join v_admin on (v_admin.admin_id = v_user.user_admin_id) ";
        $sql .= " where ";
        $sql .= "     user_delete_date is null";
        $sql .= "     and user_id='"._as($_SESSION[_PROJECT_NAME]['user_login']['user_id'])."'";
        $main_rec = _select($sql);
        $main_rec[0]['raijyousya_kbn'] = $_conf_raijyousya_kbn[$main_rec[0]['user_big_cate']]; 
        $this_sess = $main_rec[0];

        
        $sankashinai_cnt=0;
        $sankasuru_cnt=0;
        if($this_sess['raijyousya_kbn']=="1"){
            $wrkArr = explode("#", $this_sess['user_raijyou_yotei_time']);
            for ($i=0; $i < _count($wrkArr); $i++) { 
                $wrkDtArr = explode(" ",$wrkArr[$i],2);
                if( $wrkDtArr[0] == '2999/01/01' ){
                    $sankashinai_cnt++;
                }else{
                    $sankasuru_cnt++;
                }
            }
            if($sankasuru_cnt==0 && $sankashinai_cnt > 0){
                $this_sess['sanka_shinai_only'] = true;
            }else{
                $this_sess['sanka_shinai_only'] = false;                
            }
        }else{
            $this_sess['sanka_shinai_only'] = false;                
        }


        $this_sess['token'] = $token;
    }

    _setAssign($blade,$this_sess);

    if($this_sess['raijyousya_kbn']=="1"){
        //(招待者)来場予定日時
        $wArr = explode("#", $event_rec['event_syoutai_yotei_time']);
        $_conf_raijyou_yotei_time = array();
        $_conf_raijyou_yotei_time2 = array();
        for ($i=0; $i < _count($wArr); $i++) { 
            $dtArr = explode(" ", $wArr[$i],2);
            $ymd = $dtArr[0];
            //2021/07/08 Mod --------- Before ------
            // $disp_ymd = date("n月j日",strtotime($dtArr[0]) )."（"._getYoubi($dtArr[0])."）";
            //2021/07/08 Mod --------- After ------
            if( $ymd=='2999/01/01' ){
                $disp_ymd = "　　　　　　";
            }else{
                $disp_ymd = date("n月j日",strtotime($dtArr[0]) )."（"._getYoubi($dtArr[0])."）";
            }
            //2021/07/08 Mod --------- End ------
            $hi = $dtArr[1];
            $_conf_raijyou_yotei_time[$ymd]['disp_ymd'] = $disp_ymd;

            $checked="";
            if($this_sess['user_raijyou_yotei_time']!="" && $this_sess['raijyousya_kbn']=="1") { 
                if( strpos($this_sess['user_raijyou_yotei_time'],$wArr[$i]) !== FALSE ){
                    $checked = "checked";
                }
            }
            $_conf_raijyou_yotei_time[$ymd]['his'][] = array('hi'=>$hi, 'checked'=>$checked);
            // 2021.06.01 add ---------- Start ----------
            $ymd_hi = $ymd." ".$hi;
            if($ymd=='2999/01/01'){
                $ymd_hi_val = $hi;
            }else{
                $ymd_hi_val = $ymd_hi;
            }
            $_conf_raijyou_yotei_time2[$ymd_hi] = $ymd_hi_val;
            // 2021.06.01 add ---------- End   ----------

        }
        $blade->assign('_conf_raijyou_yotei_time',$_conf_raijyou_yotei_time);
        $blade->assign('_conf_raijyou_yotei_time2',$_conf_raijyou_yotei_time2); // 2021.06.01 add

    }elseif($this_sess['raijyousya_kbn']=="2"){

        //(招待者)来場予定日時
        $wArr = explode("#", $event_rec['event_raijyou_yotei_time']);
        $_conf_raijyou_yotei_time = array();
        for ($i=0; $i < _count($wArr); $i++) { 
            $dtArr = explode(" ", $wArr[$i],2);
            $ymd = $dtArr[0];
            //2021/07/08 Mod --------- Before ------
            // $disp_ymd = date("n月j日",strtotime($dtArr[0]) )."（"._getYoubi($dtArr[0])."）";
            //2021/07/08 Mod --------- After ------
            if( $ymd=='2999/01/01' ){
                $disp_ymd = "　　　　　　";
            }else{
                $disp_ymd = date("n月j日",strtotime($dtArr[0]) )."（"._getYoubi($dtArr[0])."）";
            }
            //2021/07/08 Mod --------- End ------
            $hi = $dtArr[1];
            $_conf_raijyou_yotei_time[$ymd]['disp_ymd'] = $disp_ymd;

            $checked="";
            if($this_sess['user_raijyou_yotei_time']!="" && $this_sess['raijyousya_kbn']=="2") { 
                if( strpos($this_sess['user_raijyou_yotei_time'],$wArr[$i]) !== FALSE ){
                    $checked = "checked";
                }
            }
            $_conf_raijyou_yotei_time[$ymd]['his'][] = array('hi'=>$hi, 'checked'=>$checked);
        }
        $blade->assign('_conf_raijyou_yotei_time',$_conf_raijyou_yotei_time);
    }

    //QRコード（W0002-99-12345678）
    $qr_code = $event_rec['event_area_shikibetsu_id'].substr($event_rec['event_id'],1)."-".$_SESSION[_PROJECT_NAME]['user_login']['user_big_cate']."9-".substr($_SESSION[_PROJECT_NAME]['user_login']['user_id'],1);
    $blade->assign('qr_code',$qr_code);

    $contents_tpl = "raijyou_yotei.html";
