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
                                "szkgrp_name,閲覧部署グループ名" => "need"
                              );

                $err_msg = _check( $chks, $_request );

                // 所属名の重複チェック
                $sql = "";
                $sql .= " select szkgrp_id"."\n";
                $sql .= " from m_syozoku_group"."\n";
                $sql .= " where "."\n";
                $sql .= "   szkgrp_delete_date is null ";
                $sql .= "   and szkgrp_name = '". _as( $_request['szkgrp_name'] ) ."'";
                if ( $_request['mode'] == 'update' ){
                    $sql .= "   and szkgrp_id != '" . _as( $this_sess['id'] ) ."'";
                }
                $chk_recs = _select($sql);
                if ( _count($chk_recs) > 0 ){
                    $err_msg[] = "この閲覧部署グループ名は既に登録済みです。";
                }

                // 所属名の重複チェック
                $sql = "";
                $sql .= " select szkgrp_id"."\n";
                $sql .= " from m_syozoku_group"."\n";
                $sql .= " where "."\n";
                $sql .= "   szkgrp_delete_date is null ";
                $sql .= "   and szkgrp_name = '". _as( $_request['szkgrp_name'] ) ."'";
                if ( $_request['mode'] == 'update' ){
                    $sql .= "   and szkgrp_id != '" . _as( $this_sess['id'] ) ."'";
                }
                $chk_recs = _select($sql);
                if ( _count($chk_recs) > 0 ){
                    $err_msg[] = "この閲覧部署グループ名は既に登録済みです。";
                }

                // コードの重複チェック
                $sql = "";
                $sql .= " select szkgrp_id"."\n";
                $sql .= " from m_syozoku_group"."\n";
                $sql .= " where "."\n";
                $sql .= "   szkgrp_delete_date is null ";
                $sql .= "   and szkgrp_code = '". _as( $_request['szkgrp_code'] ) ."'";
                if ( $_request['mode'] == 'update' ){
                    $sql .= "   and szkgrp_id != '" . _as( $this_sess['id'] ) ."'";
                }
                $chk_recs = _select($sql);
                if ( _count($chk_recs) > 0 ){
                    $err_msg[] = "このコードは既に登録済みです。";
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
                    $max_recs = _select( "select coalesce(max(substring(szkgrp_id,3)),'0') as max_id from m_syozoku_group");
                    $this_sess['id'] = sprintf("bg%06d", $max_recs[0]['max_id'] + 1 );
                }


                $array = array();

                $array['szkgrp_name']           = "'"._as($this_sess['szkgrp_name'])."'"; //'所属名',
                $array['szkgrp_code']           = "'"._as($this_sess['szkgrp_code'])."'";
                $array['szkgrp_hidden_flg']     = ""._e2z($_request['szkgrp_hidden_flg'])."";
                $array['szkgrp_update_date']    = "'".$_now_timestamp."'";

                switch( $this_sess['mode'] ){
                    case 'insert':
                        $array['szkgrp_id']           = "'"._as($this_sess['id'])."'";
                        $array['szkgrp_insert_date']  = "'".$_now_timestamp."'";
                        _insert( 'm_syozoku_group', $array);

                        $success_msg = "登録しました。";
                    break;
                    case 'update':
                        $where = "szkgrp_id='"._as($this_sess['id'])."'";
                        _update( 'm_syozoku_group', $array, $where );
                        $success_msg = "変更が完了いたしました。";
                    break;
                    case 'delete':
                        $array = array();
                        $array['szkgrp_delete_date']  = "'".$_now_timestamp."'";
                        $where = "szkgrp_id='"._as($this_sess['id'])."'";
                        _update( 'm_syozoku_group', $array, $where );
                        $success_msg = "削除しました。";
                    break;
                }

                if ( $this_sess['mode'] == 'insert' || $this_sess['mode'] == 'update' ) {
                    if (_count($this_sess['syozoku_group_ids']) == 0)
                    {
                        _delete('c_syozoku_groups', "szkgrp_id = '" . _as($this_sess['id']) . "'");
                    }
                    else
                    {
                        $sql = "";
                        $sql .= " select GROUP_CONCAT(r_szkgrp_id) as ids ";
                        $sql .= " from c_syozoku_groups ";
                        $sql .= " where szkgrp_id = '" . _as($this_sess['id']) . "'";
                        $syozoku_groups = _select($sql);
                        $syozoku_group_ids = explode(',', $syozoku_groups[0]['ids']);

                        foreach ($syozoku_group_ids as $saved_id)
                        {
                            if ($saved_id !== '' && ! in_array($saved_id, $this_sess['syozoku_group_ids'], true))
                            {
                                _delete('c_syozoku_groups', "szkgrp_id = '" . _as($this_sess['id']) . "' and r_szkgrp_id = '" . _as($saved_id) . "'");
                            }
                        }

                        foreach ($this_sess['syozoku_group_ids'] as $select_id)
                        {
                            if ( ! in_array($select_id, $syozoku_group_ids, true))
                            {
                                $array = array();
                                $array['szkgrp_id'] = "'" . _as($this_sess['id']) . "'";
                                $array['r_szkgrp_id'] = "'" . _as($select_id) . "'";
                                _insert('c_syozoku_groups', $array);
                            }
                        }
                    }
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
                    header('Location: index.php?page=syozoku_group_list&sess_no_init=1');//OK1
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
            $sql .= " from m_syozoku_group ";
//            $sql .= " left join m_syozoku_group on (m_syozoku_group.szkgrp_id = m_syozoku.syozoku_szkgrp_id and m_syozoku_group.szkgrp_delete_date is null)"."\n"; // 2021.05.13 add
            $sql .= " where ";
            $sql .= "     szkgrp_delete_date is null";
            $sql .= "     and szkgrp_id='"._as($_request['id'])."'";
            $main_rec = _select($sql);
            $this_sess = $main_rec[0];
            $this_sess['id'] = $main_rec[0]['szkgrp_id'];
            $this_sess['mode'] = "update";

            $sql = "";
            $sql .= " select s.szkgrp_id, s.szkgrp_name ";
            $sql .= " from c_syozoku_groups csg ";
            $sql .= " join m_syozoku_group s on csg.r_szkgrp_id = s.szkgrp_id ";
            $sql .= " where csg.szkgrp_id = '" . _as($_request['id']) . "'";
            $syozoku_groups = _select($sql);
            $this_sess['syozoku_groups'] = $syozoku_groups;
        }else{
            // 新規登録
            $this_sess['mode'] = "insert";
        }

        $this_sess['token'] = $token;

        // 閲覧部署グループ
        $sql = "";
        $sql .= "select * from m_syozoku_group";
        $sql .= " where";
        $sql .= " szkgrp_delete_date is null";
        if( $_request['id'] != "" ) {
            $sql .= " and szkgrp_id != '" . _as($_request['id']) . "'";
        }
        $sql .= " order by szkgrp_id asc";
        $syozoku_group_recs = _select($sql);
        $_conf_syozoku_group = array();
        for ($i=0; $i < _count($syozoku_group_recs); $i++) {
            $_conf_syozoku_group[ $syozoku_group_recs[$i]['szkgrp_id'] ] = $syozoku_group_recs[$i]['szkgrp_name'];
        }
        $this_sess['_conf_syozoku_group'] = $_conf_syozoku_group;
    }

    _setAssign($blade,$this_sess);

    $contents_title = "閲覧部署グループ管理 詳細";
    $active_menu = "syozoku_group_list";
    $contents_tpl = "syozoku_group_edit";
