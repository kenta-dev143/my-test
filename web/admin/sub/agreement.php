<?php
    if( !defined("_PROJECT_DISP_NAME") ){
        die("System Error");
    }

    $admin = $_SESSION[_PROJECT_NAME]['admin_login']['admin_id'];

    if( $_request['exec'] == "agree" ){

        //入力書式チェック
        $chks = array(
            "agreement,同意" => "need",
            );
        $err_msg = _check( $chks , $_request );
        if ( _count($err_msg) == 0 ){
            $array = array();
            $array['agree_admin_id']    = "'"._as($admin)."'";
            $array['agree_insert_date'] = "'".$_now_timestamp."'";
            _insert( 'm_agreement', $array );

            // 集計閲覧権限（0:全て閲覧可、1:エリアのリアルタイム人数のみ）
            if ($_SESSION[_PROJECT_NAME]['admin_login']['admin_kyouryoku_kigyou_flg'] == 1) {
                $_login_after_page = "user_list";
            } elseif ( $_SESSION[_PROJECT_NAME]['admin_login']['admin_syuukei_etsuran_kengen'] == 1){
                // 会場エリア集計
                $_login_after_page = "area_syuukei";
            } else {
                // 会場全体集計
                $_login_after_page = "kaijyou_syuukei";
            }

            $_request['page'] = $_login_after_page;
            require("sub/" . $_login_after_page . ".php");
        }
    }
