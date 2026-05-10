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
                                "area_name,エリア"                 => "need",
                                "area_max,最大人数"                => "need,seisuu",
                                "area_tanmatsuhaichi_kbn,端末配置" => "need,seisuu",
                              );

                $err_msg = _check( $chks, $_request );

                // エリアの重複チェック
                $sql = "";
                $sql .= " select area_id"."\n";
                $sql .= " from m_area"."\n";
                $sql .= " where "."\n";
                $sql .= "   area_delete_date is null ";
                $sql .= "   and area_event_id = '"._as($_SESSION[_PROJECT_NAME]['select_event_id'])."'";
                $sql .= "   and area_name = '". _as( $_request['area_name'] ) ."'";
                if ( $_request['mode'] == 'update' ){
                    $sql .= "   and area_id != '" . _as( $this_sess['id'] ) ."'";
                }
                $chk_recs = _select($sql);
                if ( _count($chk_recs) > 0 ){
                    $err_msg[] = "このエリア名は既に登録済みです。";
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
                    $max_recs = _select( "select coalesce(max(area_id),'0') as max_id from m_area" );
                    $this_sess['id'] = ( $max_recs[0]['max_id'] + 1 );
                }


                $array = array();

                $array['area_name']               = "'"._as($this_sess['area_name'])."'"; //'エリア名',
                $array['area_max']                = _as($this_sess['area_max']); //'キャパ数',
                $array['area_tanmatsuhaichi_kbn'] = _as($this_sess['area_tanmatsuhaichi_kbn']); //'端末配置区分（1:入口出口で２台別配置、2:１台で出入口共通）',
                $array['area_update_date']        = "'".$_now_timestamp."'";

                switch( $this_sess['mode'] ){
                    case 'insert':
                        $array['area_id']           = "'"._as($this_sess['id'])."'";
                        $array['area_event_id']     = "'"._as($_SESSION[_PROJECT_NAME]['select_event_id'])."'";
                        $array['area_insert_date']  = "'".$_now_timestamp."'";
                        _insert( 'm_area', $array);

                        $success_msg = "登録しました。";
                    break;
                    case 'update':
                        $where = "area_id='"._as($this_sess['id'])."'";
                        $where .= " and area_event_id = '"._as($_SESSION[_PROJECT_NAME]['select_event_id'])."'";
                        _update( 'm_area', $array, $where );
                        $success_msg = "変更が完了いたしました。";
                    break;
                    case 'delete':
                        $array = array();
                        $array['area_delete_date']  = "'".$_now_timestamp."'";
                        $where = "area_id='"._as($this_sess['id'])."'";
                        $where .= " and area_event_id = '"._as($_SESSION[_PROJECT_NAME]['select_event_id'])."'";
                        _update( 'm_area', $array, $where );
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
                    header('Location: index.php?page=area_list&sess_no_init=1');//OK1
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
            $sql .= " from m_area ";
            $sql .= " where ";
            $sql .= "     area_delete_date is null";
            $sql .= "     and area_id ='"._as($_request['id'])."'";
            $sql .= "     and area_event_id ='"._as($_SESSION[_PROJECT_NAME]['select_event_id'])."'";
            $main_rec = _select($sql);
            if(_count($main_rec) > 0){
                $this_sess = $main_rec[0];
                $this_sess['id'] = $main_rec[0]['area_id'];
                $this_sess['mode'] = "update";
            }else{
                $_request['id'] = "";
                // 新規登録と同じ
                $this_sess['mode'] = "insert";
                $this_sess['area_tanmatsuhaichi_kbn'] = 1;
            }
        }else{
            // 新規登録
            $this_sess['mode'] = "insert";
            $this_sess['area_tanmatsuhaichi_kbn'] = 1;
        }

        $this_sess['token'] = $token;
    }

    _setAssign($blade,$this_sess);
    $blade->assign( '_conf_tanmatsuhaichi_kbn', $_conf_tanmatsuhaichi_kbn );

    $blade->assign('title_bgcolor',$select_event_rec['event_title_bgcolor']);

    $contents_title = "会場エリア設定 詳細";
    $active_menu = "area_list";
    $contents_tpl = "area_edit";
