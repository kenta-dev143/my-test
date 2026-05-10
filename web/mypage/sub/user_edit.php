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

            if($_request['mode'] !='delete'){

                $chks = array(
//                                "user_kigyou_name,企業名"                 => "need",
                                //"user_busyo,部署"                 => "need",
                                //"user_yakusyoku,役職"                 => "need",
                                "user_name,氏名"                 => "need",
                                // "user_mail,メールアドレス"         => "need,email", 2020.12.19 mod
                                // "user_pass,パスワード"         => "need,eisuubar,min=4",
                                // "user_raijyou_yotei_time,来場予定日時"         => "need",
                                // "user_admin_id,担当者"         => "need",
                              );
                if($_SESSION[_PROJECT_NAME]['user_login']['user_raijyou_yotei_time']!="" && $_request['ex']!="1"){
                    $chk2 = array(
                                "user_mail,メールアドレス"         => "need,email",
//                                "user_kigyou_name_kana,企業名カナ"                 => "need,zenkana",
                                "user_name_kana,氏名カナ"                 => "need,zenkana"
                        );
                    $chks = array_merge($chks, $chk2);
                }
                if($_request['ex']=="1"){
                    $chk3 = array(
                                "user_big_cate,業種"                 => "need",
                                "user_pass,パスワード"         => "eisuubar",
                                "user_pass_chk,パスワード(確認用)"         => "eisuubar",
                        );
                    $chks = array_merge($chks, $chk3);
                }

                $err_msg = _check( $chks, $_request );

                // メールアドレスの重複チェック 2020.12.19 add
                $sql = "";
                $sql .= " select user_id"."\n";
                $sql .= " from v_user"."\n";
                $sql .= " where "."\n";
                $sql .= "   user_delete_date is null ";
                $sql .= "   and user_event_id = '"._as( $event_recs[0]['event_id'] )."'";
                // 2021/04/01 mod ------------------------ before ----------------------- 大幅変更
                // $sql .= "   and user_mail = '". _as( $_request['user_mail'] ) ."'";
                // 2021/04/01 mod ------------------------ after ------------------------ 大幅変更
                $sql .= "   and user_login_id = '". _as( $_request['user_mail'] ) ."'";
                // 2021/04/01 mod ------------------------ end   ------------------------ 大幅変更
                $sql .= "   and user_id != '" . _as( $_SESSION[_PROJECT_NAME]['user_login']['user_id'] ) ."'";
                $chk_recs = _select($sql);
                if ( _count($chk_recs) > 0 ){
                    $err_msg[] = "このメールアドレスは既に登録済みです。";
                }

                // 2021/04/01 mod ------------------------ before ----------------------- 大幅変更
                // // チェック用の変数 初期化
                // $user_mail_ck = $_request['user_mail'];

                // // @の個数チェック
                // $atmark_count = mb_substr_count ( $_request['user_mail'], '@');
                // if ( $atmark_count >= 2 ){
                //     $pos = strpos( $_request['user_mail'], '@');
                //     $pos = strpos( $_request['user_mail'], '@', ($pos+1));
                //     $user_mail_ck = substr($_request['user_mail'], 0, $pos);
                // }
                // 2021/04/01 mod ------------------------ after ------------------------ 大幅変更
                // ID(メアド)から実際のメールアドレス部分抽出
                $user_mail_ck = _getMailAddressFromID( $_request['user_mail'] );
                // 2021/04/01 mod ------------------------ end   ------------------------ 大幅変更

                // emailアドレスの形式チェック
                if ( _emailCheck($user_mail_ck, '') === false ){
                    $err_msg[]  = "メールアドレスを正しく入力して下さい。";

                }

                if($_SESSION[_PROJECT_NAME]['user_login']['user_raijyou_yotei_time']!="" && $_request['ex']!="1"){
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
                }

                if(_count($err_msg) == 0){
                    if($_request['user_pass']!="" && strlen($_request['user_pass'])<4){
                            $err_msg[]  = "パスワードは4桁以上で入力てください。";
                    }
                    if($_request['ex']=="1" && $_request['user_pass']!="" && $_request['user_pass_chk']!=""){
                        if($_request['user_pass']!=$_request['user_pass_chk']){
                            $err_msg[]  = "パスワードが確認用と一致しません。";
                        }
                    }
                }

                //**** POST値をセッションにマージ ****
                $this_sess = _array_merge( $this_sess, $_request );
            }else{

                //削除はチェックなし
                $this_sess['mode'] = "delete";
            }



            if(_count($err_msg) == 0){

                //**************************************************
                //新規の場合新ID発番
                //**************************************************

                _query($conn,'begin');
                $array = array();

                $array['user_kigyou_name']  = "'"._as($this_sess['user_kigyou_name'])."'"; //企業名',
                if($_request['ex']!="1"){
                    $array['user_kigyou_name_kana'] = "'"._as($this_sess['user_kigyou_name_kana'])."'"; //企業名カナ',
                }
                $array['user_busyo'] = "'"._as($this_sess['user_busyo'])."'"; //部署',
                $array['user_yakusyoku'] = "'"._as($this_sess['user_yakusyoku'])."'"; //役職',

                if($_request['ex']!="1"){
                    $array['user_raijyou_yotei_time'] = "'"._as($this_sess['user_raijyou_yotei_time'])."'"; //来場予定日時（yyyy/mm/dd HH:ii 形式）',
                }

                if($_request['ex']=="1"){
                    $array['user_big_cate']            = ""._e2n($this_sess['user_big_cate'])."";//大分類',
                    if($_request['user_pass']!=""){
                        $array['user_pass'] = "'"._as(md5($this_sess['user_pass']))."'";
                    }
                }


                $array['user_update_date'] = "'".$_now_timestamp."'"; //更新日時',

                $where = "user_id='"._as($_SESSION[_PROJECT_NAME]['user_login']['user_id'])."'";
                _update( 'm_user', $array, $where );

                $array_n = array();
                $array_n['un_user_name'] = "'"._as($this_sess['user_name'])."'"; //'氏名',
                if($_request['ex']!="1"){
                    $array_n['un_user_name_kana'] = "'"._as($this_sess['user_name_kana'])."'"; //氏名カナ',
                }
                $where = "un_user_id='"._as($_SESSION[_PROJECT_NAME]['user_login']['user_id'])."'";
                _update( 'm_uname', $array_n, $where );

                if($_request['ex']!="1"){
                    // 2021.06.01 add --------- Start --------------
                    $sql  = "";
                    $sql .= " select user_mail";
                    $sql .= " from v_user ";
                    $sql .= " where ";
                    $sql .= "     user_delete_date is null";
                    $sql .= "     and user_id='"._as($_SESSION[_PROJECT_NAME]['user_login']['user_id'])."'";
                    $user_rec = _select($sql);
                    if ( $user_rec[0]['user_mail'] != $this_sess['user_mail'] ){
                    // 2021.06.01 add --------- Start --------------
                        // 変更がある場合のみ更新する
                        $array_m = array();
                        // 2021/04/01 mod ------------------------ before ----------------------- 大幅変更
                        // $array_m['um_user_mail'] = "'"._as($this_sess['user_mail'])."'"; //メールアドレス',
                        // 2021/04/01 mod ------------------------ after ------------------------ 大幅変更
                        $array_m['um_user_mail']     = "'"._as($user_mail_ck)."'"; //メールアドレス',
                        $array_m['um_user_login_id'] = "'"._as($this_sess['user_mail'])."'"; //ログインID',
                        // 2021/04/01 mod ------------------------ end   ------------------------ 大幅変更
                        
                        $where = "um_user_id='"._as($_SESSION[_PROJECT_NAME]['user_login']['user_id'])."'";
                        _update( 'm_umail', $array_m, $where );
                    }
                }

                //**************************************************
                // 招待者マスタの更新
                //**************************************************
                if ( $this_sess['user_syoutai_id'] != '' ){
                    $array = array();
                    $array['syoutai_big_cate']             = ""._e2n($this_sess['user_big_cate'])."";//大分類',
                    $array['syoutai_mid_cate']             = ""._e2n($this_sess['user_mid_cate']).""; //中分類',
                    $array['syoutai_kigyou_name']          = "'"._as($this_sess['user_kigyou_name'])."'"; //企業名',
                    if( $_request['ex']!="1" ){
                        $array['syoutai_kigyou_name_kana']     = "'"._as($this_sess['user_kigyou_name_kana'])."'"; //企業名カナ',
                    }
                    $array['syoutai_busyo']                = "'"._as($this_sess['user_busyo'])."'"; //部署',
                    $array['syoutai_yakusyoku']            = "'"._as($this_sess['user_yakusyoku'])."'"; //役職',
                    $array['syoutai_last_upd_naiyou']      = "'"._as( '修正' )."'"; //最終更新内容',
                    $array['syoutai_busyo']                = "'"._as($this_sess['user_busyo'])."'"; //部署',
                    $array['syoutai_yakusyoku']            = "'"._as($this_sess['user_yakusyoku'])."'"; //役職',
                    $array['syoutai_update_date']          = "'".$_now_timestamp."'"; //更新日時',
                    
                    $array_n = array();
                    $array_n['sn_syoutai_name']            = "'"._as($this_sess['user_name'])."'"; //'氏名',
                    if( $_request['ex']!="1" ){
                        $array_n['sn_syoutai_name_kana']       = "'"._as($this_sess['user_name_kana'])."'"; //氏名カナ',
                    }
                    
                    $where = "syoutai_id='"._as( $this_sess['user_syoutai_id'] )."'";
                    _update( 'm_syoutai', $array, $where );

                    $where = "sn_syoutai_id='"._as( $this_sess['user_syoutai_id'] )."'";
                    _update( 'm_sname', $array_n, $where );

                    if($_request['ex']!="1"){
                        $array_m = array();
                        $array_m['sm_syoutai_mail']        = "'"._as($this_sess['user_mail'])."'"; //メールアドレス',
                        $array_m['sm_syoutai_login_id']    = "'"._as($this_sess['user_login_id'])."'"; // ログインid',
                        $where = "sm_syoutai_id='"._as( $this_sess['user_syoutai_id'] )."'";
                        _update( 'm_smail', $array_m, $where );
                    }
                }

                _query($conn,'commit');

                $sql  = "";
                $sql .= " select ";
                $sql .= "   * ";
                $sql .= " from v_user ";
                $sql .= " where ";
                $sql .= "     user_delete_date is null";
                $sql .= "     and user_id='"._as($_SESSION[_PROJECT_NAME]['user_login']['user_id'])."'";
                $user_rec = _select($sql);
                $_SESSION[_PROJECT_NAME]['user_login'] = $user_rec[0];
                $_SESSION[_PROJECT_NAME]['user_login']['event_rec'] = $event_rec;

                if($_request['ex']=="1"){
                    //2021/05/25 Mod ----- Before ------
                    // $ex_login_dir = "";
                    // if($event_rec['event_url_key']=='w2021fc-s'){
                    //     $ex_login_dir = "west2021s/";    
                    // }elseif($event_rec['event_url_key']=='e2021fc-s'){
                    //     $ex_login_dir = "east2021s/";    
                    // }
                    //2021/05/25 Mod ----- After ------
                    $ex_login_dir = $event_rec['event_exhibition_url_key']."/";
                    //2021/05/25 Mod ----- End ------
                    $ex_login_url = _SYSTEM_ROOT_URLS."/exhibition/".$ex_login_dir;

                    $success_msg = '<center>変更が完了いたしました<br><div class="button-area" style="margin-top:5px;padding-top:0px;"><button onClick="location.href=\''.$ex_login_url.'\';" type="submit" class="blue-button t-hover" style="width:300px;margin-top:5px;margin-bottom:10px;"><span>WEB展示会(ガイドブック)へ</span></button></div></center>';
                }else{
                    $success_msg = "変更が完了いたしました<br><span style=\"font-size:16px;\">引き続き、マイページをご利用ください。";
                }

                $w_mode = $this_sess['mode'];
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

        if($_request['ex']=="1"){
            $main_rec[0]['big_cate_name'] = $_conf_big_cate1_web[$main_rec[0]['user_big_cate']];
        }else{
            $main_rec[0]['big_cate_name'] = $_conf_big_cate[$main_rec[0]['user_big_cate']];
        }
        $main_rec[0]['mid_cate_name'] = $_conf_mid_cate[$main_rec[0]['user_mid_cate']];
        $main_rec[0]['raijyousya_kbn'] = $_conf_raijyousya_kbn[$main_rec[0]['user_big_cate']]; 
        $main_rec[0]['user_pass'] = "";

        $this_sess = $main_rec[0];

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

    //担当者
    $sql = "";
    $sql .= "select * from v_admin";
    $sql .= " where";
    $sql .= " admin_delete_date is null";
    $sql .= " and admin_mail != 'admin'";
    $sql .= " order by admin_tanarea_id asc,admin_syozoku_id asc,admin_id asc";
    $tan_recs = _select($sql);
    $_conf_tantousya = array();
    for ($i=0; $i < _count($tan_recs); $i++) { 
        $_conf_tantousya[ $tan_recs[$i]['admin_id'] ] = $tan_recs[$i]['admin_name'];
    }
    $blade->assign('_conf_tantousya',$_conf_tantousya);


    $blade->assign('_conf_vip',$_conf_vip);
    $blade->assign('_conf_big_cate',$_conf_big_cate);
    $blade->assign('_conf_mid_cate',$_conf_mid_cate);
    $blade->assign('_conf_big_cate1_web',$_conf_big_cate1_web);



    $contents_tpl = "user_edit.html";
