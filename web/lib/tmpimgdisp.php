<?php
    $project_name_prefix = $_REQUEST['project_name_prefix'];
    require( "environment.php");
    require( "inc.php" );
    require( "picture.php" );

    // 画像データ
    $page = $_request['page'];
    $id = $_request['id'];

    header('Content-type: '.$_SESSION[_PROJECT_NAME][$page][$id . '_tmp_data_mime']);
    print(base64_decode($_SESSION[_PROJECT_NAME][$page][$id . '_tmp_data']));

    exit();
?>