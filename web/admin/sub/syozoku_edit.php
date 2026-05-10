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
                                "syozoku_name,所属名"           => "need",
                                "syozoku_tanarea_id,担当エリア" => "need",
                              );

                $err_msg = _check( $chks, $_request );

                // 所属名の重複チェック
                $sql = "";
                $sql .= " select syozoku_id"."\n";
                $sql .= " from m_syozoku"."\n";
                $sql .= " where "."\n";
                $sql .= "   syozoku_delete_date is null ";
                $sql .= "   and syozoku_name = '". _as( $_request['syozoku_name'] ) ."'";
                if ( $_request['mode'] == 'update' ){
                    $sql .= "   and syozoku_id != '" . _as( $this_sess['id'] ) ."'";
                }
                $chk_recs = _select($sql);
                if ( _count($chk_recs) > 0 ){
                    $err_msg[] = "この所属名は既に登録済みです。";
                }

                // 所属コードの重複チェック
                $sql = "";
                $sql .= " select syozoku_id"."\n";
                $sql .= " from m_syozoku"."\n";
                $sql .= " where "."\n";
                $sql .= "   syozoku_delete_date is null ";
                $sql .= "   and syozoku_code = '". _as( $_request['syozoku_code'] ) ."'";
                if ( $_request['mode'] == 'update' ){
                    $sql .= "   and syozoku_id != '" . _as( $this_sess['id'] ) ."'";
                }
                $chk_recs = _select($sql);
                if ( _count($chk_recs) > 0 ){
                    $err_msg[] = "この所属コードは既に登録済みです。";
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
                    $max_recs = _select( "select coalesce(max(substring(syozoku_id,2)),'0') as max_id from m_syozoku");
                    $this_sess['id'] = sprintf("b%07d", $max_recs[0]['max_id'] + 1 );
                }


                $array = array();

                $array['syozoku_name']            = "'"._as($this_sess['syozoku_name'])."'"; //'所属名',
                $array['syozoku_code']            = "'"._as($this_sess['syozoku_code'])."'"; // 所属コード
                $array['syozoku_szkgrp_id']       = "'"._as($this_sess['syozoku_szkgrp_id'])."'"; //'閲覧部署グループID（bg000001）', 2021.05.13 add
                $array['syozoku_tanarea_id']      = ""._e2z($this_sess['syozoku_tanarea_id'])."";
                $array['syozoku_hidden_flg']      = ""._e2z($_request['syozoku_hidden_flg'])."";
                $array['syozoku_update_date']     = "'".$_now_timestamp."'";

                switch( $this_sess['mode'] ){
                    case 'insert':
                        $array['syozoku_id']           = "'"._as($this_sess['id'])."'";
                        $array['syozoku_insert_date']  = "'".$_now_timestamp."'";
                        _insert( 'm_syozoku', $array);

                        $success_msg = "登録しました。";
                    break;
                    case 'update':
                        $where = "syozoku_id='"._as($this_sess['id'])."'";
                        _update( 'm_syozoku', $array, $where );
                        $success_msg = "変更が完了いたしました。";
                    break;
                    case 'delete':
                        $array = array();
                        $array['syozoku_delete_date']  = "'".$_now_timestamp."'";
                        $where = "syozoku_id='"._as($this_sess['id'])."'";
                        _update( 'm_syozoku', $array, $where );
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


                if($w_mode != 'delete'){
                    $_request['exec'] = "";
                    $_request['id'] = $w_id;
                }else{
                    _query( $conn, "commit" );
                    header('Location: index.php?page=syozoku_list&sess_no_init=1');//OK1
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
            $sql .= " from m_syozoku ";
            $sql .= " left join m_syozoku_group on (m_syozoku_group.szkgrp_id = m_syozoku.syozoku_szkgrp_id and m_syozoku_group.szkgrp_delete_date is null)"."\n"; // 2021.05.13 add
            $sql .= " where ";
            $sql .= "     syozoku_delete_date is null";
            $sql .= "     and syozoku_id='"._as($_request['id'])."'";
            $main_rec = _select($sql);
            $this_sess = $main_rec[0];
            $this_sess['id'] = $main_rec[0]['syozoku_id'];
            $this_sess['mode'] = "update";
        }else{
            // 新規登録
            $this_sess['mode'] = "insert";
        }

        $this_sess['token'] = $token;
    }

    // 閲覧部署グループマスタ
    $sql = "";
    $sql .= "select * from m_syozoku_group";
    $sql .= " where";
    $sql .= " szkgrp_delete_date is null";
    $sql .= " order by szkgrp_id asc";
    $syozoku_group_recs = _select($sql);
    $_conf_syozoku_group = array();
    for ($i=0; $i < _count($syozoku_group_recs); $i++) {
        $_conf_syozoku_group[ $syozoku_group_recs[$i]['szkgrp_id'] ] = $syozoku_group_recs[$i]['szkgrp_name'];
    }
    $blade->assign('_conf_syozoku_group',$_conf_syozoku_group);


    // 担当エリア
    $sql = "";
    $sql .= " select *"."\n";
    $sql .= " from m_tantou_area"."\n";
    $sql .= " where tanarea_delete_date is null"."\n";
    $sql .= " order by tanarea_id"."\n";
    $tanarea_recs = _select($sql);
    for ($i=0; $i < _count($tanarea_recs); $i++) {
        $_conf_tanarea[ $tanarea_recs[$i]['tanarea_id'] ] = $tanarea_recs[$i]['tanarea_name'];
    }
    $blade->assign('_conf_tanarea',$_conf_tanarea);

    _setAssign($blade,$this_sess);

    $contents_title = "支店・部署管理 詳細";
    $active_menu = "syozoku_list";
    $contents_tpl = "syozoku_edit";
