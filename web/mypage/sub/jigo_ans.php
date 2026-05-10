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

    $sql  = "";
    $sql .= " select * from m_question ";
    $sql .= " where ";
    $sql .= "   que_event_id='"._as($event_rec['event_id'])."'";
    $que_recs = _select($sql);
    if($que_recs[0]['que_koukai_flg']=="1"){
        $blade->assign('jigo_enq_open',1);
    }

    //**************************************************
    // 各種基本変数作成
    //**************************************************
    $page = $_request['page'];
    $this_sess = &$_SESSION[_PROJECT_NAME][$page];
    $err_msg = array();

    switch( $_request['exec'] ){
        case 'save':

            $questions = _makeQuestion($event_rec['event_id']);

            $row=array();

            foreach ($questions as $q => $opt) {
                // リクエストを変数に格納
                if($_request[$q]){
                    $row[$q] = $_request[$q];
                }
            }
            $_request['row'] = $row;
            //$no = 0;
            foreach ($questions as $q => $opt) {
                //$no++;
                $no = $opt['no'];
                // 必須チェック
                switch ($opt['type']) {
                    case 'text':
                        if($_request['row'][$q]=='' && $opt['hissu_flg'] == '1'){
                            $err_msg[] = "「【設問".$no."】".$opt['question']."」は必須です。";
                        }
                    case 'sub_text':
                        if($_request['row'][$q]!=''){
                            if(!_zen_maxLenCheck($_request['row'][$q],256,"")){
                                $err_msg[] = "「【設問".$no."】".$opt['question']."」は256文字以下で入力してください。";
                            }
                        }
                        break;
                    case 'radio':
                        if($_request['row'][$q]=='' && $opt['hissu_flg'] == '1'){
                            $err_msg[] = "「【設問".$no."】".$opt['question']."」を選択してください。";
                        }else{
                            if($_request['row'][$q]!=''){
                                $flag = 1;
                                foreach($opt['options'] as $k => $v) {
                                    if( trim($_request['row'][$q]) == trim($k) ) {
                                        $flag = 0;
                                        break;
                                    }
                                }
                                if($flag==1) {
                                    $err_msg[] = "「【設問".$no."】".$opt['question']."」は不正な値です。";
                                }
                            }
                        }
                        break;
                    case 'checkbox':
                        if(_count($_request['row'][$q])==0 && $opt['hissu_flg'] == '1'){

                            $err_msg[] = "「【設問".$no."】".$opt['question']."」は1つ以上選択してください。";
                        }
                        if(_count($_request['row'][$q])>0){
                            $flag = 1;
                            foreach($opt['options'] as $k => $v) {
                                foreach($_request['row'][$q] as $key => $val) {
                                    if(trim($val) == trim($k) ) {
                                        $flag = 0;
                                        break 2;
                                    }
                                }
                            }
                            if($flag==1) {
                                $err_msg[] = "「【設問".$no."】".$opt['question']."」は不正な値です。";
                            }
                        }
                        break;
                }
            }


            foreach ($_request['row'] as $key => $value) {
                if(is_array($value)){
                    $_request['row'][$key]['disp'] = implode("、",$value);
                }
            }

            //**** POST値をセッションにマージ ****
            $this_sess = _array_merge( $this_sess, $_request );

            if( _count( $err_msg ) == 0 ){

                _query( $conn, "begin" );


                $array = array();
                $array['jigo_user_id']     = "'"._as($_SESSION[_PROJECT_NAME]['user_login']['user_id'])."'";
                $array['jigo_event_id']     = "'"._as($event_rec['event_id'])."'";
                $array['jigo_insert_date']   = "'".$_now_timestamp."'";
                $array['jigo_update_date']   = "'".$_now_timestamp."'";
                foreach ($questions as $q => $opt) {
                    // 必須チェック
                    switch ($opt['type']) {
                        case 'text':
                            $array['jigo_'.$q] = "'"._as($this_sess['row'][$q])."'";
                            break;
                        case 'radio':
                            $array['jigo_'.$q] = "'"._as($this_sess['row'][$q])."'";
                            break;
                        case 'checkbox':
                            $w_ans = str_replace("、",",",$this_sess['row'][$q]['disp']);
                            $array['jigo_'.$q] = "'"._as($w_ans)."'";
                            break;
                        case 'sub_text':
                            if($this_sess['row'][$q] != ""){
                                $w_q = str_replace("sub_","",$q);
                                $w_ans = ltrim($array['jigo_'.$w_q],"'");
                                $w_ans = rtrim($w_ans,"'");
                                $w_ans = $w_ans."#".$this_sess['row'][$q];

                                $array['jigo_'.$w_q] = "'"._as($w_ans)."'";
                            }
                            break;
                    }
                }
                _insert( 't_jigo_answer', $array);

                _query( $conn, "commit" );

                $success_msg = "回答が完了いたしました<br><span style=\"font-size:16px;\">引き続き、マイページをご利用ください。";

            }

            // 正常終了した場合のみセッション(画面入力)をクリアする
            if ( _count($err_msg) == 0 ){
                $_SESSION[_PROJECT_NAME][$page] = array();
                unset( $_SESSION[_PROJECT_NAME][$page] );
                unset( $this_sess );
                $this_sess = array();
            }

            break;

        //################################################################################################
        // 登録画面
        //################################################################################################
        default:

            unset( $_SESSION[_PROJECT_NAME][$page] );
            unset( $this_sess );
            $this_sess = &$_SESSION[_PROJECT_NAME][$page];
            $this_sess = array();

            $sql = "";
            $sql .= "select";
            $sql .= " *";
            $sql .= " from t_jigo_answer";
            $sql .= " where";
            $sql .= " jigo_event_id = '"._as($event_rec['event_id'])."'";
            $sql .= " and jigo_user_id = '"._as($_SESSION[_PROJECT_NAME]['user_login']['user_id'])."'";
            $ans_recs = _select($sql);
            if( _count($ans_recs) > 0 ){
                $blade->assign('kaitouzumi',1);
            }   

            //アンケート部分のHTML自動生成
            $questions = _makeQuestion($event_rec['event_id']);


            break;
    }

    //**************************************************
    //セッションのアサイン
    //**************************************************
    $blade->assign('questions',$questions);
    $blade->assign('exec',$_request['exec']);
    _setAssign( $blade, $this_sess );

    //（編集画面）ページテンプレート
    $contents_tpl = "jigo_ans.html";
