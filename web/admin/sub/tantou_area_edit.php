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
                                "tanarea_name,担当エリア名"    => "need",
                              );

                $err_msg = _check( $chks, $_request );

                // 所属名の重複チェック
                $sql = "";
                $sql .= " select tanarea_id"."\n";
                $sql .= " from m_tantou_area"."\n";
                $sql .= " where "."\n";
                $sql .= "   tanarea_delete_date is null ";
                $sql .= "   and tanarea_name = '". _as( $_request['tanarea_name'] ) ."'";
                if ( $_request['mode'] == 'update' ){
                    $sql .= "   and tanarea_id != '" . _as( $this_sess['id'] ) ."'";
                }
                $chk_recs = _select($sql);
                if ( _count($chk_recs) > 0 ){
                    $err_msg[] = "この担当エリア名は既に登録済みです。";
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
                    $max_recs = _select( "select coalesce(max(tanarea_id),'0') as max_id from m_tantou_area");
                    $this_sess['id'] = ( $max_recs[0]['max_id'] + 1 );
                }

                $array = array();

                $array['tanarea_name']                 = "'"._as($this_sess['tanarea_name'])."'"; //'所属名',
                $array['tanarea_update_date']          = "'".$_now_timestamp."'";

                switch( $this_sess['mode'] ){
                    case 'insert':
                        $array['tanarea_id']           = "'"._as($this_sess['id'])."'";
                        $array['tanarea_insert_date']  = "'".$_now_timestamp."'";
                        _insert( 'm_tantou_area', $array);

                        $success_msg = "登録しました。";
                    break;
                    case 'update':
                        $where = "tanarea_id='"._as($this_sess['id'])."'";
                        _update( 'm_tantou_area', $array, $where );
                        $success_msg = "変更が完了いたしました。";
                    break;
                    case 'delete':
                        $array = array();
                        $array['tanarea_delete_date']  = "'".$_now_timestamp."'";
                        $where = "tanarea_id='"._as($this_sess['id'])."'";
                        _update( 'm_tantou_area', $array, $where );
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
                    header('Location: index.php?page=tantou_area_list&sess_no_init=1');//OK1
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
            $sql .= " from m_tantou_area ";
            $sql .= " where ";
            $sql .= "     tanarea_delete_date is null";
            $sql .= "     and tanarea_id='"._as($_request['id'])."'";
            $main_rec = _select($sql);
            $this_sess = $main_rec[0];
            $this_sess['id'] = $main_rec[0]['tanarea_id'];
            $this_sess['mode'] = "update";
        }else{
            // 新規登録
            $this_sess['mode'] = "insert";
        }

        $this_sess['token'] = $token;
    }

    _setAssign($blade,$this_sess);

    $contents_title = "担当エリア管理 詳細";
    $active_menu = "tantou_area_list";
    $contents_tpl = "tantou_area_edit";
