<?php
    if( !defined("_PROJECT_DISP_NAME") ){
        die("System Error");
    }

    if( $_SESSION[_PROJECT_NAME]['admin_login']['admin_id'] == "" ){
        die('System Error');
    }

    if( $_SESSION[_PROJECT_NAME]['admin_login']['admin_master_kengen'] != "1" ){
        die('System Error');
    }

    if ($_SESSION[_PROJECT_NAME]['admin_login']['admin_kyouryoku_kigyou_flg'] == 1)
    {
        die('Permission Denied');
    }


    // ******************************************************************************************************
    // 初期値
    // ******************************************************************************************************
    $page = $_request['page'];
    $this_sess = &$_SESSION[_PROJECT_NAME][$page];
    $err_msg = array();

    $_conf_que_koukai_flg = array();
    $_conf_que_koukai_flg[0] = "未公開";
    $_conf_que_koukai_flg[1] = "公開（事後アンケート受付開始）";
    $blade->assign('_conf_que_koukai_flg',$_conf_que_koukai_flg);

    if($_SESSION[_PROJECT_NAME]['select_event_id']!=""){

        $where = "";
        $where .= " t_jigo_answer.jigo_id is not null ";
        $where .= " and t_jigo_answer.jigo_event_id = '"._as($_SESSION[_PROJECT_NAME]['select_event_id'])."'";

        // 件数取得SQL
        $sql  = "";
        $sql .= " select count(*) as all_cnt from t_jigo_answer ";
        $sql .= "   inner join  v_user on (v_user.user_id=t_jigo_answer.jigo_user_id) ";
        $sql .= " where ".$where;
        $rec = _select($sql);
        $allcnt = 0;
        if($rec[0]['all_cnt'] > 0){
            $allcnt = $rec[0]['all_cnt'];
        }
        $blade->assign('kaitou_suu',$allcnt);

        if($_request['exec'] == 'download'){
            $sql  = "";
            $sql .= " select ";
            $sql .= "   t_jigo_answer.* ";
            $sql .= "   ,v_user.user_login_id ";
            $sql .= "   ,v_user.user_big_cate ";
            $sql .= "   ,v_user.user_mid_cate ";
            $sql .= "   ,v_user.user_kigyou_name ";
            $sql .= "   ,v_user.user_busyo ";
            $sql .= "   ,v_user.user_yakusyoku ";
            $sql .= "   ,v_user.user_name ";
            $sql .= "   ,m_event.event_name ";
            $sql .= "   ,v_admin.admin_mail ";
            $sql .= "   ,v_admin.admin_name ";
            $sql .= "   ,m_tantou_area.tanarea_name ";
            $sql .= "   ,m_syozoku.syozoku_name ";
            $sql .= " from t_jigo_answer ";
            $sql .= "   inner join  m_event on (m_event.event_id=t_jigo_answer.jigo_event_id) ";
            $sql .= "   inner join  v_user on (v_user.user_id=t_jigo_answer.jigo_user_id) ";
            $sql .= "   left join v_admin on (v_admin.admin_id = v_user.user_admin_id)";                      //2021/07/13 Add
            $sql .= "   left join m_syozoku on (v_admin.admin_syozoku_id = m_syozoku.syozoku_id)";            //2021/07/13 Add
            $sql .= "   left join m_tantou_area on (v_admin.admin_tanarea_id = m_tantou_area.tanarea_id)";    //2021/07/13 Add
            $sql .= " where ".$where;
            $sql .= " order by t_jigo_answer.jigo_insert_date asc";

            $w_flnm = "事後アンケートCSVデータ_".date("YmdHis").".csv";
            header("Content-Disposition: attachment; filename=\"".mb_convert_encoding( $w_flnm, "SJIS-WIN" , _ENCODING_SRC )."\"");
            header("Content-Type: application/octet-stream; name=\"".$w_flnm."\"");

            $result = _query( $conn, $sql );

            $row = 0;
            while( $rec = _fetchArray( $result, $row ) ){

                if($row==0){
                    $csv_head  = "";
                    //$csv_head .= "\"\",";
                    $csv_head .= "\"".$rec['event_name']."\"";
                    $csv_head .= "\r\n";
                    echo mb_convert_encoding($csv_head,"SJIS-WIN",_ENCODING_SRC);

                    $csv_head  = "";
                    $csv_head .= "\"来場者ログインID\"";
                    $csv_head .= ",\"大分類\"";
                    $csv_head .= ",\"中分類\"";
                    $csv_head .= ",\"企業名\"";
                    $csv_head .= ",\"部署\"";
                    $csv_head .= ",\"役職\"";
                    $csv_head .= ",\"氏名\"";
                    $csv_head .= ",\"AC担当者メールアドレス\"";  //2021/07/13 Add
                    $csv_head .= ",\"AC担当者担当エリア\"";     //2021/07/13 Add
                    $csv_head .= ",\"AC担当者所属\"";          //2021/07/13 Add
                    $csv_head .= ",\"AC担当者氏名\"";          //2021/07/13 Add
                    $csv_head .= ",\"設問\"";
                    $csv_head .= ",\"回答\"";
                    $csv_head .= ",\"備考\"";
                    $csv_head .= ",\"回答日時\"";
                    $csv_head .= "\r\n";
                    echo mb_convert_encoding($csv_head,"SJIS-WIN",_ENCODING_SRC);
                }


                $questions = _makeQuestion($_SESSION[_PROJECT_NAME]['select_event_id']);

                $csv_body  = "";
                $no = 0;
                foreach ($questions as $q => $opt) {
                    if(strpos($q,'sub_')===false){
                        $no++;
                        $w_que = $opt['question'];
                        $w_ans = explode("#",$rec['jigo_'.$q]);
                        $ans_arr = explode(",",$w_ans[0]);
                        foreach($ans_arr as $value){
                            $csv_body .= "\"" .   csvSafe($rec['user_login_id']) . "\"";
                            $csv_body .= ",\"" .  csvSafe($_conf_big_cate[ $rec['user_big_cate'] ]) . "\"";
                            $csv_body .= ",\"" .  csvSafe($_conf_mid_cate[ $rec['user_mid_cate'] ]) . "\"";
                            $csv_body .= ",\"" .  csvSafe($rec['user_kigyou_name']) . "\"";
                            $csv_body .= ",\"" .  csvSafe($rec['user_busyo']) . "\"";
                            $csv_body .= ",\"" .  csvSafe($rec['user_yakusyoku']) . "\"";
                            $csv_body .= ",\"" .  csvSafe($rec['user_name']) . "\"";
                            $csv_body .= ",\"".   csvSafe($rec['admin_mail']) . "\"";   //2021/07/13 Add
                            $csv_body .= ",\"".   csvSafe($rec['tanarea_name']) . "\""; //2021/07/13 Add
                            $csv_body .= ",\"".   csvSafe($rec['syozoku_name']) . "\""; //2021/07/13 Add
                            $csv_body .= ",\"".   csvSafe($rec['admin_name']) . "\"";   //2021/07/13 Add
                            $csv_body .= ",\"" .  csvSafe("【設問".$no."】" . $w_que) . "\"";
                            $csv_body .= ",\"" .  csvSafe($value) . "\"";
                            $csv_body .= ",\"" .  csvSafe($w_ans[1]) . "\"";
                            //2021/03/02 Add -- Start --
                            $csv_body .= ",\"" .  csvSafe($rec['jigo_insert_date']) . "\"";
                            //2021/03/02 Add -- End --

                            $csv_body .= "\r\n";
                        }
                    }
                }

                echo mb_convert_encoding($csv_body,"SJIS-WIN",_ENCODING_SRC);

                $row++;
            }

            _freeResult( $result );

            exit();
        }

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

                for($i=1; $i<=30; $i++){
                    $idx= sprintf('%02d',$i);
                    unset($this_sess['que_keishiki_kbn_'.$idx]);

                    //ラジオチェック
                    if(!$_conf_kaitou[$_request['que_keishiki_kbn_'.$idx]]){
                        $_request['que_keishiki_kbn_'.$idx] = "";
                    }
                }

                if($_request['mode'] !='delete'){


                    if(_count($err_msg) == 0){
                        for($i=1; $i<=30; $i++){

                            $idx= sprintf('%02d',$i);
                            if($_request['que_question_'.$idx] != ""){
                                if($_request['que_hissu_flg_'.$idx] == ""){
                                    if($i == '1'){
                                        $err_msg[] = "【設問".$i."】は必須入力です。";
                                    }
                                    $err_msg[] = "【設問".$i."】入力区分を指定してください。";
                                }elseif($_request['que_keishiki_kbn_'.$idx] == ""){
                                    if($i == '1'){
                                        $err_msg[] = "【設問".$i."】は必須入力です。";
                                    }
                                    $err_msg[] = "【設問".$i."】解答方式を入力してください。";
                                }else{
                                    if($_request['que_keishiki_kbn_'.$idx] >= '2'){

                                        if($_request['que_keishiki_kbn_'.$idx] == '3' && $_request['que_keishiki_r_comment_'.$idx] == ""){
                                            if($i == '1' && _count($err_msg) == 0){
                                                $err_msg[] = "【設問".$i."】は必須入力です。";
                                            }
                                            $err_msg[] = "【設問".$i."】テキスト入力コメントを入力してください。";
                                        }elseif($_request['que_keishiki_kbn_'.$idx] == '5' && $_request['que_keishiki_c_comment_'.$idx] == ""){
                                            if($i == '1' && _count($err_msg) == 0){
                                                $err_msg[] = "【設問".$i."】は必須入力です。";
                                            }
                                            $err_msg[] = "【設問".$i."】テキスト入力コメントを入力してください。";
                                        }

                                        if($_request['que_sentakushi_'.$idx]==""){
                                            if($i == '1' && _count($err_msg) == 0){
                                                $err_msg[] = "【設問".$i."】は必須入力です。";
                                            }
                                            $err_msg[] = "【設問".$i."】選択肢を入力してください。";
                                        }
                                    }
                                }
                            }elseif($i=='1' && $_request['que_question_'.$idx] == ""){
                                $err_msg[] = "【設問".$i."】は必須入力です。";
                                $err_msg[] = "【設問".$i."】設問文を入力してください。";
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

                    $array = array();

                    $array['que_koukai_flg'] = $this_sess['que_koukai_flg'];

                    for($i=1; $i<=30; $i++){
                        $idx= sprintf('%02d',$i);

                        if($this_sess['que_question_'.$idx] != ""){
                            //改行を「,」に変換
                            $w_sentakushi = str_replace("\r\n",",",$this_sess['que_sentakushi_'.$idx]);
                            $w_sentakushi = str_replace("\r",",",$w_sentakushi);
                            $w_sentakushi = str_replace("\n",",",$w_sentakushi);

                            $array['que_question_'.$idx]             = "'"._as($this_sess['que_question_'.$idx])."'";
                            $array['que_keishiki_kbn_'.$idx]          = _e2n($this_sess['que_keishiki_kbn_'.$idx]);
                            if($this_sess['que_keishiki_kbn_'.$idx] == '3'){
                                $array['que_keishiki_comment_'.$idx]          = "'"._as($this_sess['que_keishiki_r_comment_'.$idx])."'";
                            }elseif($this_sess['que_keishiki_kbn_'.$idx] == '5'){
                                $array['que_keishiki_comment_'.$idx]          = "'"._as($this_sess['que_keishiki_c_comment_'.$idx])."'";
                            }
                            $array['que_sentakushi_'.$idx]             = "'"._as($w_sentakushi)."'";
                            $array['que_hissu_flg_'.$idx]          = _e2z($this_sess['que_hissu_flg_'.$idx]);

                        }else{
                            $array['que_question_'.$idx]             = "''";
                            $array['que_hissu_flg_'.$idx]             = 'null';
                            $array['que_keishiki_kbn_'.$idx]          = 'null';
                            $array['que_sentakushi_'.$idx]             = "''";
                        }
                    }

                    // 更新日
                    $array['que_update_date']          = "'".$_now_timestamp."'";

                    switch( $this_sess['mode'] ){
                         case 'insert':

                            $array['que_event_id']          = "'"._as($this_sess['id'])."'";
                            $array['que_insert_date'] = "'".$_now_timestamp."'";
                            _insert( 'm_question', $array);

                            $success_msg = "登録しました。";
                         break;
                         case 'update':
                            $where  = "que_event_id='"._as($this_sess['id'])."'";
                            _update( 'm_question', $array, $where );
                            $success_msg = "修正しました。";
                         break;
                         case 'delete':
                            $where  = "que_event_id='"._as($this_sess['id'])."'";
                            _update( 'm_question', $where );
                            $success_msg = "修正しました。";
                         break;
                    }

                    $w_id = $this_sess['id'];
                    $w_eid = $this_sess['eid'];
                    $w_mode = $this_sess['mode'];
                    $_SESSION[_PROJECT_NAME][$page] = array();
                    unset( $_SESSION[_PROJECT_NAME][$page] );
                    unset( $this_sess );
                    $this_sess = array();

                    $_request['exec'] = "";
                    $_request['id'] = $w_id;
                    $_request['eid'] = $w_eid;

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
            $sql .= " select * from m_question ";
            $sql .= " where ";
            $sql .= "   que_event_id='"._as($_SESSION[_PROJECT_NAME]['select_event_id'])."'";
            $main_rec = _select($sql);
            if( _count($main_rec) > 0 ){
                for($i=1; $i<=30; $i++){
                    $idx = sprintf('%02d',$i);
                    if($main_rec[0]['que_sentakushi_'.$idx] != ""){
                        $main_rec[0]['que_sentakushi_'.$idx] = str_replace(",","\r\n",$main_rec[0]['que_sentakushi_'.$idx]);
                    }
                    if($main_rec[0]['que_keishiki_kbn_'.$idx] == '3'){
                        $main_rec[0]['que_keishiki_r_comment_'.$idx] = $main_rec[0]['que_keishiki_comment_'.$idx];
                    }elseif($main_rec[0]['que_keishiki_kbn_'.$idx] == '5'){
                        $main_rec[0]['que_keishiki_c_comment_'.$idx] = $main_rec[0]['que_keishiki_comment_'.$idx];
                    }
                }
                $this_sess = $main_rec[0];
                $this_sess['id'] = $main_rec[0]['que_event_id'];
                $this_sess['mode'] = "update";
            }else{
                // 新規登録
                $this_sess['id'] = $_SESSION[_PROJECT_NAME]['select_event_id'];
                $this_sess['que_koukai_flg'] = 0;
                $this_sess['que_hissu_flg_01'] = 1;
                $this_sess['mode'] = "insert";
            }
        }

        //rowspan
        for($i=1; $i<=30; $i++){
            $idx= sprintf('%02d',$i);
            switch($this_sess['que_keishiki_kbn_'.$idx]){
                case'2':
                case'3':
                case'4':
                case'5':
                    $this_sess['row'.$idx] = 4;
                    break;
                default:
                    $this_sess['row'.$idx] = 3;
                    break;
            }
        }


        $blade->assign('list_page', 'campaign_list');
        $blade->assign('_conf_sentaku',$_conf_sentaku);
        $blade->assign('_conf_kaitou',$_conf_kaitou);

        _setAssign($blade,$this_sess);

    }


    // ******************************************************************************************************
    // ASSIGN
    // ******************************************************************************************************

    $blade->assign('title_bgcolor',$select_event_rec['event_title_bgcolor']);

    $contents_title = "事後アンケート（".$select_event_rec['event_name'] .":".$select_event_rec['event_kaijyou_name']."）";
    $active_menu = "question_edit";
    $contents_tpl = "question_edit";

