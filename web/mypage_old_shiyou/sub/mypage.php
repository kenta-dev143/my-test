<?php
    if( !defined("_PROJECT_DISP_NAME") ){
        die("System Error");
    }

    if( $_SESSION[_PROJECT_NAME]['user_login']['user_id']=="" ){
        die("System Error");
    }

    if($_SESSION[_PROJECT_NAME]['direct_login']=="1"){
        # ログアウト処理
        unset($_SESSION[_PROJECT_NAME]['user_login']);
        header("Location: "._SYSTEM_ROOT_URLS."/mypage/".$event_rec['event_url_key']."/");
        exit();
    }


    $contents_tpl = "mypage.html";
