<?php

    //(やり直すなら以下を実行してから)
    // update m_user set user_syoutai_id = null;
    // delete from m_syoutai;
    // delete from m_sname;
    // delete from m_smail;

    $project_name_prefix = "api_";
    require( "../lib/environment.php" );
    require( "../lib/Smarty.class.php" );
    require( "../lib/UserSmarty.php" );
    require( "../lib/lang.php" );
    require( "../lib/inc.php" );
    require( "../lib/check.php" );
    require( "../lib/project.php" );
    require( "../lib/picture.php" );


    $err_msg = array();
    $line_err = array();

    $file_name = "syozoku_group_20210521.csv";

    setlocale(LC_ALL, 'ja_JP.UTF-8');
    if( _file_exists( './' . $file_name ) ){
        set_time_limit(600); //10分起動
        ini_set('memory_limit',"1024M"); //メモリ拡大

        $extension  = '.'._get_extension($file_name);
        $temp_file  = _SYSTEM_ROOT_DIR . "/upfile/new_tmp/" . rand() . $extension;
        $file_move  = _copy( './' . $file_name, $temp_file );
    
        $buff = file_get_contents($temp_file);
        // $buff = mb_convert_encoding(file_get_contents($temp_file), "UTF8", "SJIS-WIN");
        $buff = str_replace("\r\n", "\n", $buff);
        $buff = str_replace("\r", "\n", $buff);

        $fp = fopen($temp_file,"w");
        fwrite($fp, $buff);
        fclose($fp);

        if ($file_move !== false) {
            $fp = fopen($temp_file, "r");
            if($fp !== false){
                //DB接続
                $conn = _dbConnect();
                _query($conn,"begin");

                // 最大IDを取得しておく
                $max_recs = _select( "select coalesce(max(substring(szkgrp_id,3)),'0') as max_id from m_syozoku_group");
                $now_id = $max_recs[0]['max_id'];

                $error_line_count = 0;
                $insert_success = 0;
                $update_success = 0;
                $syozoku_insert = 0;
                $line = 0;
                while (($csv_row = fgetcsv($fp)) !== FALSE) {
                    $w_err = array();
                    if($error_line_count > 10) {
                        // 一定数のエラーを許容する場合はメッセージを変更し、ループ後のエラーチェックをする
                        $err_msg[] = 'エラー行が10件発生しましたので取り込み処理を中断しました。';
                        break;
                    }
                    ++$line;

                    $data_arr = array();
                    $data_arr['syozoku_name']                   = trim($csv_row[0]); // 部署名
                    $data_arr['szkgrp_name']                 = trim($csv_row[1]); // 閲覧部署グループ名)
                    unset($csv_row);

                    // CSV行データチェック開始
                    $chks = array(
                        "syozoku_name,(${line}行目) 部署名"                   => "need",
                        "szkgrp_name,(${line}行目) 閲覧部署グループ名"        => "need",
                    );
                    $w_err = _check( $chks, $data_arr );
                    
                    if( _count($w_err) == 0 ){
                        $sql = "";
                        $sql .= " select *"."\n";
                        $sql .= " from m_syozoku"."\n";
                        $sql .= " left join m_syozoku_group on (m_syozoku_group.szkgrp_id = m_syozoku.syozoku_szkgrp_id and m_syozoku_group.szkgrp_delete_date is null)"."\n";
                        $sql .= " where syozoku_name = '"._as( $data_arr['syozoku_name'] )."'"."\n";
                        $sql .= "   and syozoku_delete_date is null"."\n";
                        $main_recs = _select( $sql );
                        if ( _count($main_recs) == 0 ){
                            // $w_err[] = "(${line}行目) 該当する 所属(支店・部署)名 がありません。";
                        }
                    }

                    if( _count($w_err) > 0 ){
                        $line_err = _array_merge($line_err, $w_err);
                        $error_line_count++;
                        continue;
                    }else{

                        $sql = "";
                        $sql .= " select *"."\n";
                        $sql .= " from m_syozoku_group"."\n";
                        $sql .= " where szkgrp_name = '"._as( $data_arr['szkgrp_name'] )."'"."\n";
                        $sql .= "   and szkgrp_delete_date is null"."\n";
                        $szkgrp_recs = _select( $sql );

                        // ***********************************************
                        // 閲覧グループ 所属支店・部署 マスタ
                        // ***********************************************
                        if ( _count($szkgrp_recs) == 0 ){
                            $now_id    = $now_id + 1;
                            $szkgrp_id = sprintf("bg%06d", $now_id );
    
                            $array = array();
                            $array['szkgrp_id']                     = "'"._as($szkgrp_id)."'";
                            $array['szkgrp_name']                   = "'"._as($data_arr['szkgrp_name'])."'";
                            $array['szkgrp_insert_date']            = "'".$_now_timestamp."'";
                            $array['szkgrp_update_date']            = "'".$_now_timestamp."'";
                            _insert( 'm_syozoku_group', $array);

                            $insert_success ++ ;
                        } else {
                            $szkgrp_id = $szkgrp_recs[0]['szkgrp_id'];
                            $where = "szkgrp_id = '"._as($szkgrp_id)."'";

                            $array = array();
                            $array['szkgrp_name']                   = "'"._as($data_arr['szkgrp_name'])."'";
                            $array['szkgrp_update_date']            = "'".$_now_timestamp."'";
                            _update('m_syozoku_group', $array, $where);

                            $update_success ++ ;
                        }

                        // ***********************************************
                        // 所属支店・部署マスタ
                        // ***********************************************
                        $sql = "";
                        $sql .= " select *"."\n";
                        $sql .= " from m_syozoku"."\n";
                        $sql .= " where syozoku_name = '"._as( $data_arr['syozoku_name'] )."'"."\n";
                        $sql .= "   and syozoku_delete_date is null"."\n";
                        $syozoku_recs = _select( $sql );
                        if ( _count($syozoku_recs) == 0 ){
                            $max_recs = _select( "select coalesce(max(substring(syozoku_id,2)),'0') as max_id from m_syozoku");
                            $syozoku_id = sprintf("b%07d", $max_recs[0]['max_id'] + 1 );
        
                            $array = array();
                            $array['syozoku_id']              = "'"._as($syozoku_id)."'";
                            $array['syozoku_szkgrp_id']       = "'"._as($szkgrp_id)."'";
                            $array['syozoku_name']            = "'"._as($data_arr['syozoku_name'])."'"; //'所属名',
                            $array['syozoku_insert_date']     = "'".$_now_timestamp."'";
                            $array['syozoku_update_date']     = "'".$_now_timestamp."'";
                            _insert( 'm_syozoku', $array);
                            $syozoku_insert++;

                        } else {
                            $where = "syozoku_id = '"._as($syozoku_recs[0]['syozoku_id'])."'";

                            $array = array();
                            $array['syozoku_szkgrp_id']             = "'"._as($szkgrp_id)."'";
                            $array['syozoku_update_date']           = "'".$_now_timestamp."'";
                            _update('m_syozoku', $array, $where);
                            
                        }

                        unset($data_arr); // データ開放
                    }

                } //while

                if( $error_line_count > 0 ) {
                    $err_msg[] = 'エラーがあったので登録処理を停止しました。';
                    $err_msg = _array_merge($err_msg, $line_err);
                    _query( $conn, "rollback" );

                }else{
                    _query($conn, "commit");
                }

                fclose($fp);
                @unlink($temp_file);

            } else {
                $err_msg[] = "ファイルの読込みに失敗しました";
            }
        }
    }

    if (_count($err_msg) > 0){
        for ($n=0; $n < _count($err_msg); $n++) { 
            echo $err_msg[$n]."<br>";
        }
    } else {
        echo "支店・部署マスタの登録 (". number_format($syozoku_insert).")"."<br>";
        echo "閲覧部署グループマスタの登録 (". number_format($insert_success).")" ."<br>";
        echo $_now_timestamp."<br>";
    }

    //DB切断
    _dbDisconnect( $conn );
