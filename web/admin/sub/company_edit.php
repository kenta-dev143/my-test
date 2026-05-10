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
                                "company_name,企業名"              => "need",
                                "company_display_name,表示名"      => "need",
                                "company_name_kana,企業名カナ"     => "need",
                                "company_big_cate,大分類"          => "need,seisuu",
                            );

                $err_msg = _check( $chks, $_request );

                //**** POST値をセッションにマージ ****
                $this_sess = _array_merge( $this_sess, $_request );
            }else{

                //削除はチェックなし
                $this_sess['mode'] = "delete";
            }

            if(_count($err_msg) == 0){

                _query($conn,'begin');

                $array = array();
                $array_n = array();
                $array_m = array();

                $array['company_name']          = "'"._as($this_sess['company_name'])."'";
                $array['company_display_name']  = "'"._as($this_sess['company_display_name'])."'";
                $array['company_name_kana']     = "'"._as($this_sess['company_name_kana'])."'";
                $array['company_big_cate']      = "" . _e2n($this_sess['company_big_cate']) . "";
                $array['company_daisy']         = "'"._as($this_sess['company_daisy'])."'";
                $array['company_web_showcases'] = "'"._as($this_sess['company_web_showcases'])."'";
                $array['company_business_management_flg'] = _e2z($_request['company_business_management_flg']);

                switch( $this_sess['mode'] ){
                    case 'insert':
                        _insert( 'm_company', $array);

                        $success_msg = "登録しました。";
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

                $w_id = $this_sess['id'];
                $w_mode = $this_sess['mode'];
                $_SESSION[_PROJECT_NAME][$page] = array();
                unset( $_SESSION[_PROJECT_NAME][$page] );
                unset( $this_sess );
                $this_sess = array();

                _export_company_list(_SYSTEM_ROOT_DIR . '/upfile/company');

                if($w_mode != 'delete'){
                    $_request['exec'] = "";
                    $_request['id'] = $w_id;
                }else{
                    _query( $conn, "commit" );
                    header('Location: index.php?page=company_list&sess_no_init=1');//OK1
                    exit();
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

    $contents_title = "企業管理 詳細";
    $active_menu = "company_list";
    $contents_tpl = "company_edit";
