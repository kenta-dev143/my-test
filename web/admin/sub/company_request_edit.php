<?php
    if( !defined("_PROJECT_DISP_NAME") ){
        die("System Error");
    }

    if( $_SESSION[_PROJECT_NAME]['admin_login']['admin_id'] == "" ){
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

    $blade->assign('admin_name', $_SESSION[_PROJECT_NAME]['admin_login']['admin_name']);
    $blade->assign('_conf_legal_personality', $_conf_legal_personality);

    $_conf_legal_personality_position = array();
    $_conf_legal_personality_position[1] = '前';
    $_conf_legal_personality_position[2] = '後';
    $blade->assign('_conf_legal_personality_position', $_conf_legal_personality_position);

    // ******************************************************************************************************
    // 登録・更新・削除
    // ******************************************************************************************************
    if($_request['exec'] == 'save'){

        if( $this_sess['token'] != $_request['token'] ){
            $err_msg[] = 'このデータは処理できませんでした。';
        }elseif( _count( $this_sess ) <= 0 ){
            //**** リロードやボタンダブルクリックでの２重登録抑制
            $err_msg[] = 'このデータは既に処理済みです。';
        }else{

            if($_request['mode'] !='delete'){
                $chks = array(
                    "legal_personality,法人格"              => "need",
                    "legal_personality_position,法人格位置" => "need",
                    "name,企業名"                           => "need",
                    "display_name,表示名"                   => "need",
                    "name_kana,企業名カナ"                  => "need",
                    "big_cate,大分類"                       => "need,seisuu",
                );

                $err_msg = _check( $chks, $_request );

                //**** POST値をセッションにマージ ****
                $this_sess = _array_merge( $this_sess, $_request );
            }else{

                //削除はチェックなし
                $this_sess['mode'] = "delete";
            }

            if(_count($err_msg) == 0){

                $kigyou_name = $this_sess['name'];
                $kigyou_full_name = $this_sess['name'];
                if ($_request['legal_personality'] != 99)
                {
                    // 法人格位置
                    if ($_request['legal_personality_position'] === "1")
                    {
                        // 前
                        $kigyou_full_name = $_conf_legal_personality[$_request['legal_personality']] . $kigyou_name;
                    }
                    else
                    {
                        // 後
                        $kigyou_full_name = $kigyou_name . $_conf_legal_personality[$_request['legal_personality']];
                    }
                }

                _query($conn,'begin');

                $array = array();

                $array['tcr_request_admin_id']   = "'"._as($_SESSION[_PROJECT_NAME]['admin_login']['admin_id'])."'";
                $array['tcr_name']               = "'"._as($kigyou_name)."'";
                $array['tcr_full_name']          = "'"._as($kigyou_full_name)."'";
                $array['tcr_display_name']       = "'"._as($this_sess['display_name'])."'";
                $array['tcr_name_kana']          = "'"._as($this_sess['name_kana'])."'";
                $array['tcr_legal_personality']  = "'"._as($_conf_legal_personality[$this_sess['legal_personality']])."'";
                $array['tcr_big_cate']           = "" . _e2n($this_sess['big_cate']) . "";
                $array['tcr_address']            = "'"._as($this_sess['address'])."'";
                $array['tcr_tel']                = "'"._as($this_sess['tel'])."'";
                $array['tcr_url']                = "'"._as($this_sess['url'])."'";
                $array['tcr_memo']               = "'"._as($this_sess['memo'])."'";

                switch( $this_sess['mode'] ){
                    case 'insert':
                    case 'insert_next':
                        _insert( 't_company_request', $array);

                        _set_flash_message("登録しました。");
                    break;
                    case 'update':
                        $where = "company_id='"._as($this_sess['id'])."'";
                        _update( 'm_company', $array, $where );

                        $success_msg = "変更が完了いたしました。";
                    break;
                    case 'delete':
                        $array = array();
                        $array['company_delete_date']  = "'".$_now_timestamp."'";
                        $where = "company_id='"._as($this_sess['id'])."'";
                        _update( 'm_company', $array, $where );
                        $success_msg = "削除しました。";
                    break;
                }

                _query($conn,'commit');

                $w_mode = $this_sess['mode'];
                $_SESSION[_PROJECT_NAME][$page] = array();
                unset( $_SESSION[_PROJECT_NAME][$page] );
                unset( $this_sess );
                $this_sess = array();


                if($w_mode === 'insert'){
                    header('Location: index.php?page=company_request_list&sess_no_init=1');
                    exit;
                } else if ($w_mode === 'insert_next') {
                    header('Location: index.php?page=company_request_edit');
                    exit;
                }
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
        if( $_request['id'] != "" ){
            // 編集
            $sql  = "";
            $sql .= " select ";
            $sql .= "   * ";
            $sql .= " from m_company ";
            $sql .= " where ";
            $sql .= "     company_delete_date is null";
            $sql .= "     and company_id ='"._as($_request['id'])."'";
            $main_rec = _select($sql);
            $this_sess = $main_rec[0];
            $this_sess['id'] = $main_rec[0]['company_id'];
            $this_sess['mode'] = "update";

        }else{
            // 新規登録
            $this_sess['mode'] = "insert";
        }

        $this_sess['token'] = $token;
    }

    $blade->assign('_conf_big_cate',$_conf_big_cate);

    _setAssign($blade,$this_sess);

    $contents_title = "企業申請画面";
    $active_menu = "company_request_list";
    $contents_tpl = "company_request_edit";
