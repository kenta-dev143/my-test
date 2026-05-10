<?php
//Unicode(UTF-8)対応
$_internal_encoding = "UTF8";

//プロジェクト和名
define("_PROJECT_DISP_NAME","access_tenjikai_sys");

//プロジェクトキーワード
define("_PROJECT_NAME",$project_name_prefix."klib2");

//管理画面のタイトル
define("_MANAGE_TITLE","来場者管理システム 管理画面");

//管理画面フッタ部のコピーライト記述
define("_COPYRIGHT","Copyright (C)NIPPON ACCESS All Rights Reserved.");

//コンテンツタイプ
define("_CONTENT_TYPE_PC","text/html");

// QRコードリンクの有効期限（日）
define("_QRCODE_EXPIRES_DAY", 7);

// ***************************************************
// ***************************************************
//緊急メンテナンス時は以下の「$_kinkyuu_mnt_flg」をtrueに
// ***************************************************
$_kinkyuu_mnt_flg = false;
// ***************************************************
// ***************************************************

//macの場合 www.xxxx.com:80 みたいに:80が付いてくるので取り払う
if(strpos($_SERVER['HTTP_HOST'],":") !== FALSE){
    $_httphost_wrk_arr = explode(":",$_SERVER['HTTP_HOST']);
    $_SERVER['HTTP_HOST'] = $_httphost_wrk_arr[0];
}

//2017/04/10 Add -------- Start ------------
if (version_compare(PHP_VERSION, '7.0.0') >= 0) {
    //7.0.0 より新しいバージョンのPHP
    define( 'SMARTY_PLUGINS_DIR' , dirname(__FILE__) . DIRECTORY_SEPARATOR . 'plugins_3.1.30' . DIRECTORY_SEPARATOR );
}
//2017/04/10 Add -------- End ------------

$_APACHE_ROOT_DIR = "";
$_ENV["COMPUTERNAME"] = getenv("COMPUTERNAME"); // 2020.12.18 add nishimura

if( strpos( strtolower(__FILE__),"visitor_management")!==FALSE || substr($_SERVER['HTTP_HOST'],0,8) == "192.168."){
    //開発環境の場合
    $_KANKYOU_STR = "local";
    $kdebugger = false;

    $_fl_arr = explode("/",__FILE__);
    $HTTP_ENV_VARS['windir'] = "1";
    $_APACHE_ROOT_DIR = "/".$_fl_arr[1]."/".$_fl_arr[2];

    // if( substr(__FILE__,0,7) == "/Users/"){
    //     $_fl_arr = explode("/",__FILE__);
    //     $HTTP_ENV_VARS['windir'] = "1";
    //     $_APACHE_ROOT_DIR = "/".$_fl_arr[1]."/".$_fl_arr[2]."/ApacheRoot";
    // }else{
    //     $_APACHE_ROOT_DIR = "C:/ApacheRoot";
    //     // 西村開発PC 2020.12.18 add nishimura
    //     if( $_ENV["COMPUTERNAME"] == "MBAPPE" ){
    //         $HTTP_ENV_VARS['windir'] = "1";
    //     }

    // }

    //DB接続情報
    $_USE_DB_ENGINE = "MySQL";

    //DB接続情報
    define( "_MY_DB_SERVER", "localhost" );
    define( "_MY_DB_NAME", "AC_EXHIBITION" );
    define( "_MY_DB_UID", "root" );
    define( "_MY_DB_PASS", "root" );

    //ドメインのルート（システムプロジェクトのルートではない）
    define( "_DOMAIN_ROOT_DIR", $_APACHE_ROOT_DIR );
    //システムプロジェクトのルート
    define( "_SYSTEM_ROOT_DIR", $_APACHE_ROOT_DIR."/web" );

    //Smartyキャッシュディレクトリ
    define( "_ROOT_CACHE_DIR", $_APACHE_ROOT_DIR."/web/lib/blade_cache" );

    //システムのルート
    define("_SYSTEM_ROOT_URL", "http://".$_SERVER['HTTP_HOST']."");

    //システムのルート(SSL版) ※httpsを使わないサイトの場合はhttp://のアドレスを記述すること
    define("_SYSTEM_ROOT_URLS","http://".$_SERVER['HTTP_HOST']."");

    //*画像アップなどのDir ( _id_ 部分は指定IDで置き換え)
    define("_UPFILE_DIR",$_APACHE_ROOT_DIR."/web/upfile/_id_" );

    //PHPソースのエンコード
    define("_ENCODING_SRC" , "UTF8" );
    //DBのエンコード
    define("_ENCODING_DB" , "UTF8" );
    define("_ENCODING_DB_CHARSET_NAME" , "utf8mb4" ); //set namesで使う 「utf8mb4」や「utf8」
    //http出力文字コード
    define("_CHARSET_OUTPUT" , "utf-8" );

    //エラーメール受信メアドの@以降のドメイン部分
    define("_RETURN_PATH_MAIL_DOMAIN" , "k-creation.co.jp" );

    //*GDでGIF変換有効か
    $_GD_GIF_ENEBLE = true;

    //REQUESTの半角スペースTrimを行うかどうか
    $_POST_HANKAKU_SPACE_TRIM = true;
    //REQUESTの全角スペースTrimを行うかどうか
    $_POST_ZENKAKU_SPACE_TRIM = true;

    //*image magicのパス ($_GD_GIF_ENEBLEがfalse時ImageMagickを利用しGIF変換)
    $convert = "c:/usr/bin/convert";

    //*メアドチェックで「..」や「.@」を許すかどうか
    $_ILLEGAL_MAIL_OK_FLG = true;

    //HTMLテンプレートに書かれているI-mode絵文字をPCでそのまま出力するかどうか
    $_PC_TPL_EMOJI_DISP = false;

    // 管理者メールアドレス (パスワード再発行で使用)
    define( "_SYSTEM_INFO_MAIL", "test@k-creation.co.jp" );
    define( "_SYSTEM_INFO_MAIL_NAME", "日本アクセス" );
        

// }elseif( $_SERVER['HTTP_HOST']=="54.248.115.135" || $_SERVER['HTTP_HOST']=="ec2-54-248-115-135.ap-northeast-1.compute.amazonaws.com" || $_SERVER['HTTP_HOST']=="172.29.2.10"){

//     //テスト環境環境の場合
//     $_KANKYOU_STR = "test_temp";

//     //DB接続情報
//     $_USE_DB_ENGINE = "MySQL";

//     define( "_MY_DB_SERVER", "ac-exhibition.cvxx7tvgonxu.ap-northeast-1.rds.amazonaws.com" );
//     define( "_MY_DB_NAME", "AC_EXHIBITION_TEST" );
//     define( "_MY_DB_UID", "access" );
//     define( "_MY_DB_PASS", "Access01!" );

//     //ドメインのルート（システムプロジェクトのルートではない）
//     define( "_DOMAIN_ROOT_DIR", "/var/www/vhosts/tenjikai.nippon-access.co.jp" );
//     //システムプロジェクトのルート
//     define( "_SYSTEM_ROOT_DIR", "/var/www/vhosts/tenjikai.nippon-access.co.jp" );

//     //Smartyキャッシュディレクトリ
//     define( "_ROOT_CACHE_DIR", "/var/www/vhosts/tenjikai.nippon-access.co.jp/lib/blade_cache" );

//     //システムのルート
//     define("_SYSTEM_ROOT_URL", "http://".$_SERVER['HTTP_HOST']."");

//     //システムのルート(SSL版) ※httpsを使わないサイトの場合はhttp://のアドレスを記述すること
//     define("_SYSTEM_ROOT_URLS","http://".$_SERVER['HTTP_HOST']."");

//     //画像アップなどのDir ( _id_ 部分は指定IDで置き換え)
//     define("_UPFILE_DIR","/var/www/vhosts/tenjikai.nippon-access.co.jp/upfile/_id_" );

//     //PHPソース@のエンコード
//     define("_ENCODING_SRC" , "UTF8" );
//     //DBのエンコード
//     define("_ENCODING_DB" , "UTF8" );
//     define("_ENCODING_DB_CHARSET_NAME" , "utf8mb4" ); //set namesで使う 「utf8mb4」や「utf8」
//     //http出力文字コード
//     define("_CHARSET_OUTPUT" , "utf-8" );

//     //エラーメール受信メアドの@以降のドメイン部分
//     define("_RETURN_PATH_MAIL_DOMAIN" , "k-creation.co.jp" );

//     //GDでGIF変換有効か
//     $_GD_GIF_ENEBLE = true;

//     //REQUESTの半角スペースTrimを行うかどうか
//     $_POST_HANKAKU_SPACE_TRIM = true;
//     //REQUESTの全角スペースTrimを行うかどうか
//     $_POST_ZENKAKU_SPACE_TRIM = true;

//     //image magicのパス ($_GD_GIF_ENEBLEがfalse時ImageMagickを利用しGIF変換)
//     $convert = "/usr/bin/convert";

//     //メアドチェックで「..」や「.@」を許すかどうか
//     $_ILLEGAL_MAIL_OK_FLG = true;

//     //HTMLテンプレートに書かれているI-mode絵文字をPCでそのまま出力するかどうか
//     $_PC_TPL_EMOJI_DISP = false;

//     // 管理者メールアドレス (パスワード再発行で使用)
//     define( "_SYSTEM_INFO_MAIL", "tenjikai-info.nippon-access.co.jp@k-creation.co.jp" );
//     define( "_SYSTEM_INFO_MAIL_NAME", "日本アクセス" );

}elseif( __FILE__ == "/var/www/vhosts/test-tenjikai.nippon-access.co.jp/lib/environment.php" ){

    //テスト環境環境の場合
    $_KANKYOU_STR = "test";

    //DB接続情報
    $_USE_DB_ENGINE = "MySQL";

    define( "_MY_DB_SERVER", "ac-dev-exhibition.cvxx7tvgonxu.ap-northeast-1.rds.amazonaws.com" );
    define( "_MY_DB_NAME", "AC_EXHIBITION" );
    define( "_MY_DB_UID", "access" );
    define( "_MY_DB_PASS", "Access01!" );

    //ドメインのルート（システムプロジェクトのルートではない）
    define( "_DOMAIN_ROOT_DIR", "/var/www/vhosts/test-tenjikai.nippon-access.co.jp" );
    //システムプロジェクトのルート
    define( "_SYSTEM_ROOT_DIR", "/var/www/vhosts/test-tenjikai.nippon-access.co.jp" );

    //Smartyキャッシュディレクトリ
    define( "_ROOT_CACHE_DIR", "/var/www/vhosts/test-tenjikai.nippon-access.co.jp/lib/blade_cache" );

    //システムのルート
    define("_SYSTEM_ROOT_URL", "https://test-tenjikai.nippon-access.co.jp");

    //システムのルート(SSL版) ※httpsを使わないサイトの場合はhttp://のアドレスを記述すること
    define("_SYSTEM_ROOT_URLS","https://test-tenjikai.nippon-access.co.jp");

    //画像アップなどのDir ( _id_ 部分は指定IDで置き換え)
    define("_UPFILE_DIR","/var/www/vhosts/test-tenjikai.nippon-access.co.jp/upfile/_id_" );

    //PHPソース@のエンコード
    define("_ENCODING_SRC" , "UTF8" );
    //DBのエンコード
    define("_ENCODING_DB" , "UTF8" );
    define("_ENCODING_DB_CHARSET_NAME" , "utf8mb4" ); //set namesで使う 「utf8mb4」や「utf8」
    //http出力文字コード
    define("_CHARSET_OUTPUT" , "utf-8" );

    //エラーメール受信メアドの@以降のドメイン部分
    define("_RETURN_PATH_MAIL_DOMAIN" , "test-tenjikaimail.nippon-access.co.jp" );


    //GDでGIF変換有効か
    $_GD_GIF_ENEBLE = true;

    //REQUESTの半角スペースTrimを行うかどうか
    $_POST_HANKAKU_SPACE_TRIM = true;
    //REQUESTの全角スペースTrimを行うかどうか
    $_POST_ZENKAKU_SPACE_TRIM = true;

    //image magicのパス ($_GD_GIF_ENEBLEがfalse時ImageMagickを利用しGIF変換)
    $convert = "/usr/bin/convert";

    //メアドチェックで「..」や「.@」を許すかどうか
    $_ILLEGAL_MAIL_OK_FLG = true;

    //HTMLテンプレートに書かれているI-mode絵文字をPCでそのまま出力するかどうか
    $_PC_TPL_EMOJI_DISP = false;

    // 管理者メールアドレス (パスワード再発行で使用)
    define( "_SYSTEM_INFO_MAIL", "tenjikai-info@nippon-access.co.jp" );
    define( "_SYSTEM_INFO_MAIL_NAME", "日本アクセス" );

}else{
    //本番環境の場合
    $_KANKYOU_STR = "honban";


    //DB接続情報
    $_USE_DB_ENGINE = "MySQL";

    define( "_MY_DB_SERVER", "ac-exhibition.cvxx7tvgonxu.ap-northeast-1.rds.amazonaws.com" );
    define( "_MY_DB_NAME", "AC_EXHIBITION" );
    define( "_MY_DB_UID", "access" );
    define( "_MY_DB_PASS", "Access01!" );

    //ドメインのルート（システムプロジェクトのルートではない）
    define( "_DOMAIN_ROOT_DIR", "/var/www/vhosts/tenjikai.nippon-access.co.jp" );
    //システムプロジェクトのルート
    define( "_SYSTEM_ROOT_DIR", "/var/www/vhosts/tenjikai.nippon-access.co.jp" );

    //Smartyキャッシュディレクトリ
    define( "_ROOT_CACHE_DIR", "/var/www/vhosts/tenjikai.nippon-access.co.jp/lib/blade_cache" );

    //システムのルート
    define("_SYSTEM_ROOT_URL", "https://tenjikai.nippon-access.co.jp");

    //システムのルート(SSL版) ※httpsを使わないサイトの場合はhttp://のアドレスを記述すること
    define("_SYSTEM_ROOT_URLS","https://tenjikai.nippon-access.co.jp");

    //画像アップなどのDir ( _id_ 部分は指定IDで置き換え)
    define("_UPFILE_DIR","/var/www/vhosts/tenjikai.nippon-access.co.jp/upfile/_id_" );

    //PHPソース@のエンコード
    define("_ENCODING_SRC" , "UTF8" );
    //DBのエンコード
    define("_ENCODING_DB" , "UTF8" );
    define("_ENCODING_DB_CHARSET_NAME" , "utf8mb4" ); //set namesで使う 「utf8mb4」や「utf8」
    //http出力文字コード
    define("_CHARSET_OUTPUT" , "utf-8" );

    //エラーメール受信メアドの@以降のドメイン部分
    define("_RETURN_PATH_MAIL_DOMAIN" , "tenjikaimail.nippon-access.co.jp" );

    //GDでGIF変換有効か
    $_GD_GIF_ENEBLE = true;

    //REQUESTの半角スペースTrimを行うかどうか
    $_POST_HANKAKU_SPACE_TRIM = true;
    //REQUESTの全角スペースTrimを行うかどうか
    $_POST_ZENKAKU_SPACE_TRIM = true;

    //image magicのパス ($_GD_GIF_ENEBLEがfalse時ImageMagickを利用しGIF変換)
    $convert = "/usr/bin/convert";

    //メアドチェックで「..」や「.@」を許すかどうか
    $_ILLEGAL_MAIL_OK_FLG = true;

    //HTMLテンプレートに書かれているI-mode絵文字をPCでそのまま出力するかどうか
    $_PC_TPL_EMOJI_DISP = false;

    // 管理者メールアドレス (パスワード再発行で使用)
    define( "_SYSTEM_INFO_MAIL", "tenjikai-info@nippon-access.co.jp" );
    define( "_SYSTEM_INFO_MAIL_NAME", "日本アクセス" );
}
?>