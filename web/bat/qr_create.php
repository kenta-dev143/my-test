<?php

if(!isset($_SERVER['HTTP_HOST'])){
    $_SERVER['HTTP_HOST']="";
}

error_reporting(E_ALL & ~E_NOTICE);

require_once( "../lib/environment.php" );
require_once( "../lib/inc.php" );
require_once( "../lib/check.php" );
require_once( "../lib/project.php" );

//DB接続
$conn = _dbConnect();

$sql  = "";
$sql .= " select szkgrp_id from m_syozoku_group ";
$sql .= " where  szkgrp_delete_date is null ";
$syozoku_group_recs = _select($sql);

$syozoku_group_count = count($syozoku_group_recs);

foreach (range(1, $syozoku_group_count) as $i) {
    echo exec("php qr_zip.php " . $i . " " . $i, $output, $return_var);
}
