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
                                "mailt_name,テンプレート名"     => "need",
                                "mailt_subject,件名"            => "need",
                                "mailt_body,メール本文"         => "need",
                              );

                $err_msg = _check( $chks, $_request );

                // 重複チェック
                $sql  = "";
                $sql .= " select *";
                $sql .= " from m_mail_template ";
                $sql .= " where ";
                $sql .= "   mailt_delete_date is null";
                $sql .= "  and mailt_name = '". _as( $_request['mailt_name'] ) ."'";
                if ( $_request['mode'] == 'update' ){
                    $sql .= "     and mailt_key !='"._as($_request['mailt_key'])."'";
                }
                $chk_recs = _select($sql);
                if ( _count($chk_recs) > 0 ){
                    $err_msg[] = "このテンプレート名は既に登録済みです。";
                }

                //使えるキーワードチェック
                if( $this_sess['mode'] == "insert" ){
                    $mailt_system_use_only = 0;
                }else{
                    $mailt_system_use_only = $this_sess['mailt_system_use_only'];
                }
                $_use_ok_keywords = array();
                if($mailt_system_use_only==0){
                    $_use_ok_keywords = _array_merge( $_use_ok_keywords, $_conf_mail_template_keywords['common'] );
                }
                if($this_sess['mailt_key']!="" && _count($_conf_mail_template_keywords[$this_sess['mailt_key']]) > 0 ){
                    $_use_ok_keywords = _array_merge( $_use_ok_keywords, $_conf_mail_template_keywords[$this_sess['mailt_key']] );
                }
                if($_request['mailt_subject']!=""){
                    preg_match_all('/<%([^%]+)%>/is',$_request['mailt_subject'],$matches,PREG_PATTERN_ORDER);
                    for ($i=0; $i < _count($matches[1]); $i++) {
                        $keyword = $matches[1][$i];
                        if($_use_ok_keywords[$keyword]==""){
                            $err_msg[] = "キーワード <%".$keyword."%> はこのテンプレートでは利用できません。";
                        }
                    }
                }
                if($_request['mailt_body']!=""){
                    preg_match_all('/&lt;%([^%]+)%&gt;/is',$_request['mailt_body'],$matches,PREG_PATTERN_ORDER);
                    for ($i=0; $i < _count($matches[1]); $i++) {
                        $keyword = $matches[1][$i];
                        if($_use_ok_keywords[$keyword]==""){
                            $err_msg[] = "キーワード <%".$keyword."%> はこのテンプレートでは利用できません。";
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

                _query($conn,'begin');

                //**************************************************
                //新規の場合新ID発番
                //**************************************************
                if( $this_sess['mode'] == "insert" ){
                    $this_sess['mailt_key'] = "mt_".date('YmdHis');
                }
                $array = array();
                $array['mailt_name']               = "'"._as($this_sess['mailt_name'])."'"; //'テンプレート名',
                $array['mailt_subject']            = "'"._as($this_sess['mailt_subject'])."'"; //'件名',
                $array['mailt_body']               = "'"._as($this_sess['mailt_body'])."'"; //'メール本文',
                $array['mailt_update_date']        = "'".$_now_timestamp."'";

                switch( $this_sess['mode'] ){
                    case 'insert':
                        $array['mailt_key']        = "'"._as($this_sess['mailt_key'])."'";
                        $array['mailt_insert_date'] = "'".$_now_timestamp."'";
                        _insert( 'm_mail_template', $array);

                        $success_msg = "登録しました。";
                    break;
                    case 'update':
                        $where = "mailt_key='"._as($this_sess['mailt_key'])."'";
                        _update( 'm_mail_template', $array, $where );
                        $success_msg = "変更が完了いたしました。";
                    break;
                    case 'delete':
                        $array = array();
                        $array['mailt_delete_date'] = "'".$_now_timestamp."'";
                        $where = "mailt_key='"._as($this_sess['mailt_key'])."'";
                        _update( 'm_mail_template', $array, $where );
                        $success_msg = "削除しました。";
                    break;
                }

                _query($conn,'commit');

                $w_mailt_key = $this_sess['mailt_key'];
                $w_mode = $this_sess['mode'];
                $_SESSION[_PROJECT_NAME][$page] = array();
                unset( $_SESSION[_PROJECT_NAME][$page] );
                unset( $this_sess );
                $this_sess = array();

                if($w_mode != 'delete'){
                    $_request['exec'] = "";
                    $_request['mailt_key'] = $w_mailt_key;
                }else{
                    _query( $conn, "commit" );
                    header('Location: index.php?page=mail_template_list&sess_no_init=1');//OK1
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
        if( $_request['mailt_key'] != "" ){
            // 編集
            $sql  = "";
            $sql .= " select ";
            $sql .= "   * ";
            $sql .= " from m_mail_template ";
            $sql .= " where ";
            $sql .= "     mailt_delete_date is null";
            $sql .= "     and mailt_key ='"._as($_request['mailt_key'])."'";
            $main_rec = _select($sql);
            $this_sess = $main_rec[0];
            $this_sess['mailt_key'] = $main_rec[0]['mailt_key'];
            $this_sess['mode'] = "update";
        }else{
            // 新規登録
            $this_sess['mode'] = "insert";
            $this_sess['mailt_system_use_only'] = "0";
        }

        $this_sess['token'] = $token;
    }

    _setAssign($blade,$this_sess);
    $blade->assign('_conf_mail_template_keywords',$_conf_mail_template_keywords);

    $contents_title = "メールテンプレート管理 詳細";
    $active_menu = "mail_template_list";
    $contents_tpl = "mail_template_edit";
