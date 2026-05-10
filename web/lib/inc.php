<?php
    //2017/04/17 Add ----- Start -------
    //JavaScript のようなスクリプト言語からはアクセスできなくする。
    ini_set('session.cookie_httponly', true);
    //クリックジャッキング対策
    //frame/iframe 要素の表示を禁止したい場合にはDENY
    //frame/iframe要素の表示を禁止したい場合にはSAMEORIGIN
    header('X-Frame-Options: SAMEORIGIN');
    //IEにコンテンツの内容を解析させない（ファイルの内容からファイルの種類を決定させない）。
    header("X-Content-Type-Options: nosniff");
    //XSSフィルタのON/OFFの設定。XSSをブラウザが検知するとレンダリングを止める。
    header('X-XSS-Protection:1; mode=block');
    //PHPのバージョンを返さない。
    header('X-Powered-By: Secret');
    //2017/04/17 Add ----- End -------

    //Add 2010/08/31 ---- Start ----------------------------------------------
    //*** Oracle用 global 変数
    $_oracle_bind_arr = array();
    $_stid = NULL;
    //Add 2010/08/31 ---- End   ----------------------------------------------

    //2017/09/30 Add
    define('LF',"\n"); //SQL文に付けるとdebugに便利  $sql .= LF . "select * from ...";

    //ページナビ2を使用した時の表示最大ページ数 _make_pagenavi2()
    define('_PAGENAVI2_PAGE_MAX',10);

   // **************************************************************************************
    // ログ設定
    // **************************************************************************************
    //$_log_enable = "OFF";
    $_log_enable = "ON";
    $_logdir = $_APACHE_ROOT_DIR."/log/";

    mb_language("Japanese");
    if($_internal_encoding == ""){
        mb_internal_encoding(_ENCODING_SRC);
        mb_regex_encoding(_ENCODING_SRC);
    }else{
        mb_internal_encoding($_internal_encoding);
        mb_regex_encoding($_internal_encoding);
    }

    //2008/11/05 ここに移動
    putenv("TZ=JST-9");

    //2011/03/04 Add -------------- Strat ----------------
    $smapho_useragents = array(
      'iPhone',         // Apple iPhone
      'iPod',           // Apple iPod touch
      'Android',        // 1.5+ Android
      'dream',          // Pre 1.5 Android
      'CUPCAKE',        // 1.5+ Android
      'blackberry9500', // Storm
      'blackberry9530', // Storm
      'blackberry9520', // Storm v2
      'blackberry9550', // Storm v2
      'blackberry9800', // Torch
      'webOS',          // Palm Pre Experimental
      'incognito',      // Other iPhone browser
      'webmate'         // Other iPhone browser
    );
    $smapho_pattern = '/'.implode('|', $smapho_useragents).'/i';
    $is_smapho =  preg_match($smapho_pattern, $_SERVER['HTTP_USER_AGENT'],$matches);
    //2011/03/04 Add -------------- End ----------------
    //携帯端末か？
    $_user_agt = explode("/",$_SERVER['HTTP_USER_AGENT']);
    //2011/03/04 Add -------------- Strat ----------------
    $smartphone_kbn = "";
    if ( $is_smapho ) {
       // SmartPhone
       $smartphone_kbn = $matches[0];
    }
    //2011/03/04 Add -------------- End ----------------


    //2017/09/29 Add --------- Start -----------
    $tablet_useragents = array(
      'tab',
      'pad'
    );
    $tablet_pattern = '/'.implode('|', $tablet_useragents).'/i';
    $is_tablet =  preg_match($tablet_pattern, strtolower($_SERVER['HTTP_USER_AGENT']),$matches);
    $tablet_kbn = "";
    if ( $is_tablet ) {
       $tablet_kbn = $matches[0];
    }
    //2017/09/29 Add --------- End -----------


    $tanmatsu_kbn = "PC";

    //セッション
    //2013/02/28 Add --------- Strat -------------
    if($_COOKIE['PHPSESSID'] != ""){
        if(!preg_match('/^[a-zA-Z0-9\-]*$/', $_COOKIE['PHPSESSID'])){
            die("Access Error (ck)");
        }
    }
    //2013/02/28 Add --------- End -------------
    if($_session_non_start != "1"){
        // $_w_ss = ""; //2008/11/05 Add
        if($_REQUEST['ss']!=""){
            @session_id($_REQUEST['ss']);
        }

        session_cache_limiter(' private_no_expire');
        session_start();
        $_REQUEST['ss'] = session_id();
        $ss = $_REQUEST['ss'];
    }


    //アクセスキー
    $ime_mode['hiragana'] = " style=\"ime-mode:active\" ";
    $ime_mode['katakana'] = " style=\"ime-mode:active\" ";
    $ime_mode['eiji'] = " style=\"ime-mode:disabled\" ";
    $ime_mode['suuji'] = " style=\"ime-mode:disabled\" ";

    $access_key = "ACCESSKEY";


    //$_SESSION['_level'] = $_level;

    //$_GET, $_POST, $_COOKIE, $_FILES の取得
    $_request = array();
    $_request = _getRequest();


    //2021/07/01 Add ---------- Start --------
    if($_kinkyuu_mnt_flg == true){
        if($_request['kinkyuu_mnt_mode']!="") $_SESSION[_PROJECT_NAME]['kinkyuu_mnt_mode'] = $_request['kinkyuu_mnt_mode'];
        if($_SESSION[_PROJECT_NAME]['kinkyuu_mnt_mode']==""){
            header( "Content-Type: text/html; charset=" . _CHARSET_OUTPUT );
            die("只今サーバーメンテナンス中です。ご迷惑おかけいたしますが、今しらくお待ち下さい。");
        }
    }
    //2021/07/01 Add ---------- End --------

    //2008/11/05 上に移動
    //現在日時取得
#     putenv("TZ=JST-9");
    $_now_timestamp = date("Y-m-d H:i:s");
    $_now_datetime = date("Y/m/d H:i:s");
    list($_now_date,$_now_time) = explode(" ",$_now_datetime);
    list($_now_yyyy,$_now_mm,$_now_dd) = explode("/",$_now_date);
    $_now_stamp = date("YmdHis");

    // if( ($HTTP_ENV_VARS["windir"] != "" || $HTTP_ENV_VARS["WINDIR"] != "") && $_log_enable == "ON" && $_SESSION['log_ses_id']!=session_id()){
    //     $file_handler = fopen($_logdir . "log"  . date("Ymd") . ".txt","w");
    //     fclose( $file_handler );
    //     $_SESSION['log_ses_id'] = session_id();
    // }
    function _putLog($str){
        global $_log_enable, $_logdir, $HTTP_ENV_VARS;
        if( ($HTTP_ENV_VARS["windir"] != "" || $HTTP_ENV_VARS["WINDIR"] != "") && $_log_enable == "ON"){
            mb_language('Japanese');
            //$write_str = date("Y/m/d H:i:s") . " "  . mb_convert_encoding($str,"SJIS",_ENCODING_SRC) . "\r\n";
            $write_str = date("Y/m/d H:i:s") . " "  . mb_convert_encoding($str,"UTF8",_ENCODING_SRC) . "\r\n";
            $file_handler = fopen($_logdir . "log" . date("Ymd") . ".txt","a");
            fwrite( $file_handler , $write_str);
            fclose( $file_handler );
        }
    }
    function _putLog_r($arr){
        global $_log_enable, $_logdir, $HTTP_ENV_VARS;
        if( ($HTTP_ENV_VARS["windir"] != "" || $HTTP_ENV_VARS["WINDIR"] != "") && $_log_enable == "ON"){
            mb_language('Japanese');
            ob_start();
            print_r($arr);
            $str = ob_get_contents();
            ob_end_clean();
            $str = str_replace("\r","",$str);
            $str = str_replace("\n","\r\n",$str);
            // $write_str = date("Y/m/d H:i:s") . " "  . mb_convert_encoding($str,"SJIS",_ENCODING_SRC) . "\r\n";
            $write_str = date("Y/m/d H:i:s") . " "  . mb_convert_encoding($str,"UTF8",_ENCODING_SRC) . "\r\n";
            $file_handler = fopen($_logdir . "log" . date("Ymd") . ".txt","a");
            fwrite( $file_handler , $write_str);
            fclose( $file_handler );
        }
    }

    function _putLog_dump($arr){
        global $_log_enable, $_logdir, $HTTP_ENV_VARS;
        if( ($HTTP_ENV_VARS["windir"] != "" || $HTTP_ENV_VARS["WINDIR"] != "") && $_log_enable == "ON"){
            mb_language('Japanese');
            ob_start();
            var_dump($arr);
            $str = ob_get_contents();
            ob_end_clean();
            $str = str_replace("\r","",$str);
            $str = str_replace("\n","\r\n",$str);
            // $write_str = date("Y/m/d H:i:s") . " "  . mb_convert_encoding($str,"SJIS",_ENCODING_SRC) . "\r\n";
            $write_str = date("Y/m/d H:i:s") . " "  . mb_convert_encoding($str,"UTF8",_ENCODING_SRC) . "\r\n";
            $file_handler = fopen($_logdir . "log" . date("Ymd") . ".txt","a");
            fwrite( $file_handler , $write_str);
            fclose( $file_handler );
        }
    }

    //---------------------------------------------------------
    // 簡易_print()
    //---------------------------------------------------------
    function _print( &$_arr ){
        global $HTTP_ENV_VARS;

        if( strpos( strtolower(__FILE__),"apacheroot")!==FALSE){
            echo $_arr . "<br>";
        }
    }
    function _print_r( &$_arr ){
        global $HTTP_ENV_VARS;

        if( strpos( strtolower(__FILE__),"apacheroot")!==FALSE){
            echo "<pre>";
            print_r($_arr);
            echo "</pre><br>";
        }
    }


    //***************************************************************************************
    // 以降関数
    //***************************************************************************************

    //------------------------------------------------------------
    // glob()同様の関数
    //------------------------------------------------------------
    function _glob($_path){
        $_ret = array();

        $path_info = pathinfo($_path);

        $ptn = str_replace(".","\.", $path_info['basename']);
        if(strstr($path_info['basename'],".") ){
            $ptn = str_replace("*","[^\.]*", $ptn);
        }else{
            $ptn = str_replace("*",".*", $ptn);
        }

        if ($dh = opendir($path_info['dirname'])) {
            while (($flnm = readdir($dh)) !== false) {
                if( $flnm != '.' && $flnm != '..' ){
                    if( preg_match ("/^".$ptn."$/",$flnm) ){
                        $_ret[] = $path_info['dirname'] . "/" . $flnm;
                    }
                }
            }
            closedir($dh);
        }

        if(_count($_ret)==0){
            return false;
        }else{
            return $_ret;
        }
    }


    //------------------------------------------------------------
    // _rmdir()同様の関数(中身のファイルやディレクトリも削除する)
    //------------------------------------------------------------
    function _rmdir($_dir){

        if( _file_exists($_dir) ){
            // 削除するディレクトリの中身を検索
            //$files = glob( $_dir . '/*' );
            //2017/12/10 Mod
            $files = _glob( $_dir . '/*' );

            // ディレクトリの中身を削除する
            if( $files !== false ){
                for( $i = 0; $i < _count($files); $i++ ){
                    if($files[$i] != "." && $files[$i] != ".."){
                        if( is_dir($files[$i]) ){
                            _rmdir($files[$i]);
                        }else{
                            unlink($files[$i]);
                        }
                    }
                }
            }
            // ディレクトリを削除する
            $dir = glob( $_dir );

            if($dir !== false){
                rmdir($dir[0]);

                return false;
            }else{
                return true;
            }
        }else{
            return false;
        }

    }

    //------------------------------------------------------------
    // copy()同様の関数(元ファイルなければなにもしない、先ファイルあれば消してからコピー)
    //------------------------------------------------------------
    function _copy($src,$dst){
        if( _file_exists($src) ){
            if( _file_exists($dst) ){
                unlink($dst);
            }
            copy( $src, $dst );
        }else{
            return false;
        }
    }

    //------------------------------------------------------------
    // ディレクトリが無い場合は、作成する
    //------------------------------------------------------------
    function _mkdir($_path){
#        $_ret = array();
#
#        $path_info = pathinfo($_path);
#
#        if ( opendir($path_info['dirname']) === false) {
#            print($path_info['dirname']);
#            mkdir($path_info['dirname']);
#        }

        $_ret = array();
        clearstatcache();
        if ( is_dir($_path) === false) {
            @mkdir($_path,0777);
            @chmod($_path, 0777); //2009/10/07 Add
        }

    }

    //------------------------------------------------------------
    // ファイルの存在を確認する。（file_existsと同様の動き)
    //------------------------------------------------------------
    function _file_exists($_file_dir){

        // キャッシュをクリアーする
        clearstatcache();

        return file_exists($_file_dir);

    }

    //------------------------------------------------------------
    // ファイルかどうかを確認する。（is_fileと同様の動き) 2007/07/03 Add
    //------------------------------------------------------------
    function _is_file($_file_path){

        // キャッシュをクリアーする
        clearstatcache();

        return is_file($_file_path);

    }

    //------------------------------------------------------------
    // ディレクトリかどうかを確認する。（is_dirと同様の動き) 2007/07/03 Add
    //------------------------------------------------------------
    function _is_dir($_dir_path){

        // キャッシュをクリアーする
        clearstatcache();

        return is_dir($_dir_path);

    }

    //------------------------------------------------------------
    // ファイルのサイズを確認する。（filesizeと同様の動き)
    //------------------------------------------------------------
    function _filesize($_file_dir){

        // キャッシュをクリアーする
        clearstatcache();

        return filesize($_file_dir);

    }

    //***************************************************************
    // URL上で隠蔽したいコードなどをURLパラメータ用暗号化文字列に変換
    //***************************************************************
    function _urlCodeEncode( $code ){
        return urlencode(base64_encode(gzcompress($code . "_#_" . md5($code),9)) );
    }

    //***************************************************************
    // _urlCodeEncode() で暗号化した文字列から元のコードを復元
    //       ※指定された文字列が不正であった場合は空文字を返す
    //       ※共通処理でurldecode()されている前提
    //***************************************************************
    function _urlCodeDecode( $urlCode ){
        $code_md5 = @gzuncompress(base64_decode( $urlCode ));
//2010/09/21 mod -----------
#         $flds = split("_",$code_md5);
        $flds = explode("_#_",$code_md5);
//2010/09/21 mod -----------
        if( md5($flds[0]) == $flds[1] ){
            return $flds[0];
        }else{
            return "";
        }
    }

    //---------------------------------------------------------
    // オートリンク : _AutoLink
    //        引数：文字列
    //        戻値：調整後文字列
    //---------------------------------------------------------
    function _AutoLink($_val){

        //$_val = ereg_replace("(https?|ftp)(://[[:alnum:]\+\$\;\?\.%,!#~*/:@&=_-]+)", "<a href=\"\\1\\2\" target=\"_blank\">\\1\\2</a>", $_val);
        //$_val = ereg_replace("[^=\'\">]([0-9a-zA-Z./_-]+)@([0-9a-zA-Z./_-]+\.[0-9a-zA-Z]{2,4})", " <a href=\"mailto:\\1@\\2\">\\1&#64;\\2</a>", $_val);
        //return( $_val );

        $_val = str_replace("&quot;","￡￡",$_val);
        $_val = ereg_replace("(https?|ftp)(://[[:alnum:]\+\$\;\?\.%,!#~*/:@&=_-]+)", "<a href=\"\\1\\2\" target=\"_blank\">\\1\\2</a>", $_val);
        //2006/11/10 Add
        $_val = ereg_replace("<a href=\"([^>]*)\.\" ([^>]*)>([^<]*)\.</a>", "<a href=\"\\1\" \\2>\\3</a>.", $_val);

        $_val = ereg_replace("([^\x83\x81])([0-9a-zA-Z./_-]+)@([0-9a-zA-Z./_-]+\.[0-9a-zA-Z]{2,4})", "\\1<a href=\"mailto:\\2@\\3\">\\2&#64;\\3</a>", $_val);
        $_val = str_replace("￡￡","&quot;",$_val);
        return( $_val );
    }

    //---------------------------------------------------------
    //$_POST,$_GETを調整し$_REQUESTに : _getRequest
    //        引数：なし
    //        戻値：$_REQUEST
    //---------------------------------------------------------
    function _getRequest()
    {
        global    $argv, $argc;
        //2021/06/18 Mod ------------ Before -----------
        // //if(get_magic_quotes_gpc())
        // //{
        //     _parse_stripslashes($_POST,"POST");
        //     _parse_stripslashes($_GET,"GET");
        // //}
        // //$_REQUEST = _array_merge($_REQUEST,$_POST);
        // //$_REQUEST = _array_merge($_REQUEST,$_GET);
        // //2009/03/02 Mod
        // $_REQUEST = _array_merge($_REQUEST,$_POST);
        // $_REQUEST = _array_merge($_REQUEST,$_GET);
        //2021/06/18 Mod ------------ After -----------
        if (function_exists('getallheaders')){
            $req_header = getallheaders();
            $req_header = array_change_key_case($req_header, CASE_LOWER);
            if( strpos(strtolower($req_header['content-type']), "json") !== FALSE ){
                $json_str = file_get_contents("php://input");
                $json_arr = json_decode($json_str, true);
                $_REQUEST = _array_merge($_REQUEST,$json_arr);
            }else{
                _parse_stripslashes($_POST,"POST");
                _parse_stripslashes($_GET,"GET");
                $_REQUEST = _array_merge($_REQUEST,$_POST);
                $_REQUEST = _array_merge($_REQUEST,$_GET);
            }
        }else{
            _parse_stripslashes($_POST,"POST");
            _parse_stripslashes($_GET,"GET");
            $_REQUEST = _array_merge($_REQUEST,$_POST);
            $_REQUEST = _array_merge($_REQUEST,$_GET);
        }
        //2021/06/18 Mod ------------ End -----------

        //while(list($key,$val) = each($_REQUEST)){
        //2009/03/02 Mod
        foreach($_REQUEST as $key => $val){
            if(substr($key,0,9)=="__kanji__"){
                unset($_REQUEST[$key]);
            }
        }

        //ARGを取得.
        if($argc>0)
        {
            for($ii=1;$ii<$argc;$ii++)
            {
                $arg_split = preg_split('/=/', $argv[$ii]);
                $_REQUEST[$arg_split[0]] = urldecode($arg_split[1]);
            }
        }
        return $_REQUEST;
    }

    //---------------------------------------------------------
    //stripslashesを行う（配列になっていても再帰的に行う） : _parse_stripslashes
    //        引数：なし
    //        戻値：$_REQUEST
    //---------------------------------------------------------
    function _parse_stripslashes(&$arg,$method)
    {
        global $_POST_ZENKAKU_SPACE_TRIM; //2008/07/22 Add
        global $_POST_HANKAKU_SPACE_TRIM; //2017/11/23 Add

        if(is_array($arg))
        {
            $_w_arg = $arg;
            //reset($_w_arg);
            //while(list($key,$val) = each($_w_arg)){
            //2009/03/02 Mod
            foreach($_w_arg as $key => $val){
                if(substr($key,0,9)=="__kanji__"){
                    $moto_key = $key;
                    $new_key = substr($moto_key,9);
                    $new_key = str_replace('_equal_','=',$new_key);
                    $new_key = str_replace('_plus_','+',$new_key);
                    $new_key = str_replace('_slashe_','/',$new_key);
                    $new_key = base64_decode($new_key);
                    $arg[$new_key] = $arg[$moto_key];
                    unset($arg[$moto_key]);
                    $key = $new_key;
                }
                _parse_stripslashes($arg[$key],$method);
            }
        }
        else
        {
            if( $method != "POST" ){
                //$arg = urldecode($arg);
            }
#            $arg = mb_convert_encoding($arg,_ENCODING_SRC,"auto");


            // $arg = trim($arg);
            //2017/11/23 Mod
            if($_POST_HANKAKU_SPACE_TRIM==true){
                $arg = trim($arg);
            }

            //2008/07/22 Add ------------------- Start ------------------------
            //2017/10/05 Mod ---------- Before ------------
            // if($_POST_ZENKAKU_SPACE_TRIM==true){
            //     while(true){
            //         if(substr($arg,0,2)=="　"){
            //             $arg = substr($arg,2);
            //         }elseif(substr($arg,0,1)==" "){
            //             $arg = substr($arg,1);
            //         }else{
            //             break;
            //         }
            //     }
            //     while(true){
            //         if(substr($arg,strlen($arg)-2,2)=="　"){
            //             $arg = substr($arg,0,strlen($arg)-2);
            //         }elseif(substr($arg,strlen($arg)-1,1)==" "){
            //             $arg = substr($arg,0,strlen($arg)-1);
            //         }else{
            //             break;
            //         }
            //     }
            // }
            //2017/10/05 Mod ---------- After ------------
            // if($_POST_ZENKAKU_SPACE_TRIM==true){
            //     while(true){
            //         if(mb_substr($arg,0,1)=="　"){
            //             $arg = mb_substr($arg,1);
            //         }elseif(mb_substr($arg,0,1)==" "){
            //             $arg = mb_substr($arg,1);
            //         }else{
            //             break;
            //         }
            //     }
            //     while(true){
            //         if(mb_substr($arg,strlen($arg)-1,1)=="　"){
            //             $arg = mb_substr($arg,0,mb_strlen($arg)-1);
            //         }elseif(mb_substr($arg,mb_strlen($arg)-1,1)==" "){
            //             $arg = mb_substr($arg,0,mb_strlen($arg)-1);
            //         }else{
            //             break;
            //         }
            //     }
            // }
            //2017/10/05 Mod ---------- End ------------
            //2017/10/31 Mod ---------- Start ------------
            if($_POST_ZENKAKU_SPACE_TRIM==true){
                while(true){
                    if(mb_substr($arg,0,1)=="　"){
                        $arg = mb_substr($arg,1);
                    }elseif(mb_substr($arg,0,1)==" "){
                        $arg = mb_substr($arg,1);
                    }elseif(mb_substr($arg,0,1)=="\r"){
                        $arg = mb_substr($arg,1);
                    }elseif(mb_substr($arg,0,1)=="\n"){
                        $arg = mb_substr($arg,1);
                    }elseif(mb_substr($arg,0,1)=="\t"){
                        $arg = mb_substr($arg,1);
                    }else{
                        break;
                    }
                }
                while(true){
                    if(mb_substr($arg,-1,1)=="　"){
                        $arg = mb_substr($arg,0,mb_strlen($arg)-1);
                    }elseif(mb_substr($arg,-1,1)==" "){
                        $arg = mb_substr($arg,0,mb_strlen($arg)-1);
                    }elseif(mb_substr($arg,-1,1)=="\r"){
                        $arg = mb_substr($arg,0,mb_strlen($arg)-1);
                    }elseif(mb_substr($arg,-1,1)=="\n"){
                        $arg = mb_substr($arg,0,mb_strlen($arg)-1);
                    }elseif(mb_substr($arg,-1,1)=="\t"){
                        $arg = mb_substr($arg,0,mb_strlen($arg)-1);
                    }else{
                        break;
                    }
                }
            }
            //2017/10/31 Mod ---------- End ------------

            //2008/07/22 Add ------------------- End ------------------------


        }
    }

    //---------------------------------------------------------
    // htmlspecialcharsラップ : _hs
    //        引数：出力したい文字列
    //        戻値：調整後文字列
    //---------------------------------------------------------
    function _hs($_val){
        $_val = htmlspecialchars($_val);
        return( $_val );
    }

    //---------------------------------------------------------
    // addslashesラップ : _as
    //        引数：文字列
    //        戻値：調整後文字列
    //---------------------------------------------------------
    function _as($_val, $_db_engine=""){
        global $_USE_DB_ENGINE;
        global $_internal_encoding;
        //Add 2010/08/31
        global $_oracle_bind_arr;
        global $conn; //2017/03/10 Add

        // MySQLかPostgresの判定
        if($_db_engine==""){
            if($_USE_DB_ENGINE=="") {
                $_USE_DB_ENGINE="PostgreSQL";
            }
            $_db_engine = $_USE_DB_ENGINE;
        }

        // 変換
        if(strtolower($_db_engine)=="postgresql"){
            if($_internal_encoding==""){
                $_val = mb_convert_encoding($_val,_ENCODING_DB,_ENCODING_SRC);
            }
            //$_val = addslashes($_val);
            $_val = pg_escape_string($_val);
        }elseif(strtolower($_db_engine)=="mysql"){
            if($_internal_encoding==""){
                $_val = mb_convert_encoding($_val,_ENCODING_DB,_ENCODING_SRC);
            }

            //2017/03/10 Mod ---------- Before --------------
            // $_val = mysql_real_escape_string( $_val );
            //2017/03/10 Mod ---------- After --------------
            if (version_compare(PHP_VERSION, '5.6.0') >= 0) {
                //5.6.0 より新しいバージョンのPHP
                $_val = $conn->real_escape_string( $_val );
            }else{
                $_val = mysql_real_escape_string( $_val );
            }
            //2017/03/10 Mod ---------- End --------------

        }elseif(strtolower($_db_engine)=="sqlserver"){ //SQLServer
            if(_ENCODING_DB != _ENCODING_SRC){
                if($_internal_encoding==""){
                    $_val = mb_convert_encoding($_val,_ENCODING_DB,_ENCODING_SRC);
                }
            }
            $_val = addslashes( $_val );
        }elseif(strtolower($_db_engine)=="sqlite"){ //SQLite
            if(_ENCODING_DB != _ENCODING_SRC){
                if($_internal_encoding==""){
                    $_val = mb_convert_encoding($_val,_ENCODING_DB,_ENCODING_SRC);
                }
            }
            $_val = sqlite_escape_string( $_val );
        //Add 2010/08/31 --- Start ---------------------------------------------------
        }elseif(strtolower($_db_engine)=="oracle"){ //Oracle
            if($_internal_encoding==""){
                $_val = mb_convert_encoding($_val,_ENCODING_DB,_ENCODING_SRC);
            }
            //返却文字列設定
            $_oracle_bind_name = ":BIND" . sprintf("%06d",_count($_oracle_bind_arr) );
            $_oracle_bind_arr[$_oracle_bind_name] = $_val;
            $_val = $_oracle_bind_name;
        //Add 2010/08/31 --- End   ---------------------------------------------------
        }
        return( $_val );
    }

    //---------------------------------------------------------
    // 空文字を０にする : _e2z
    //        引数：文字列
    //        戻値：調整後文字列
    //---------------------------------------------------------
    function _e2z($_val){
        if(trim($_val)==""){
            $_val = 0;
        }
        return $_val;
    }

    //---------------------------------------------------------
    // ランダムパスワード発行: _makePassword
    // 引数：$lenパスワード桁数（省略時8桁）
    // 戻値：生成されたパスワード
    //---------------------------------------------------------
    function _makePassword($len = 8) {
        $salt = "abcdefghijklmnopqrstuvwxyz1234567890";
        srand((double)microtime()*1000000);
        $i = 0;
        while ($i < $len) { // パスワードの長さを指定できます。
            $num = rand() % 33;
            $tmp = substr($salt, $num, 1);
            $pass = $pass . $tmp;
            $i++;
        }
        return $pass;
    }

    //---------------------------------------------------------
    // 指定された0～35の数字を0～Zの半角1文字にする
    // 引数：変換したい数値
    // 戻値：生成された文字列
    //---------------------------------------------------------
    function _getABCNum($val){
        $str_list = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";

        $intVal = intval($val);
        $tmp = substr($str_list, $intVal, 1);
        return $tmp;
    }

    //---------------------------------------------------------
    // エラーメッセージ表示 : _errMsg
    //        引数：コネクション
    //        　　　エラーメッセージ
    //        戻値：なし
    //---------------------------------------------------------
    function _errMsg( &$_conn, $_err_str, $_db_engine="" ){
        global $_USE_DB_ENGINE;
        global $kdebugger; //2017/09/11 kdebugger
        global $_now_timestamp, $head_rec;

//        global $_myconn;
        // MySQLかPostgresの判定
        if($_db_engine==""){
            if($_USE_DB_ENGINE=="") {
                $_USE_DB_ENGINE="PostgreSQL";
            }
            $_db_engine = $_USE_DB_ENGINE;
        }

        if(strtolower($_db_engine)=="postgresql"){
            if( $_conn ){
                //@mysql_query('rollback',$_conn);
                @pg_close($_conn);
            }
        }elseif(strtolower($_db_engine)=="mysql"){
            //2017/03/10 Mod --------------- Before --------------
            // if( $_conn ){
            //     @mysql_close( $_conn );
            // }
            //2017/03/10 Mod --------------- After --------------
            if (version_compare(PHP_VERSION, '5.6.0') >= 0) {
                //5.6.0 より新しいバージョンのPHP
                if( $_conn ){


                    if(_count($head_rec) > 0){
                        //ステータスを「9:送信エラー」に更新
                        _query($_conn,"begin");

                        $array = array();
                        $array['mailhd_status'] = "9";
                        $array['mailhd_error_detail'] = "'"._as($_err_str)."'";
                        $array['mailhd_update_date'] = "'".$_now_timestamp."'";
                        $where = "mailhd_id = "._as($head_rec['mailhd_id']);
                        _update("t_mail_head",$array,$where);

                        _query($_conn,"commit");
                    }

                    $_conn->close();
                }
            }else{
                if( $_conn ){
                    @mysql_close( $_conn );
                }
            }
            //2017/03/10 Mod --------------- End --------------
        }elseif(strtolower($_db_engine)=="sqlserver"){
            if( $_conn ){
                @mssql_close( $_conn );
            }
        }elseif(strtolower($_db_engine)=="sqlite"){
            if( $_conn ){
                @sqlite_close( $_conn );
            }
        //Add 2010/08/31 --- Start --------------------------
        }elseif(strtolower($_db_engine)=="oracle"){
            if( $_conn ){
                @oci_close( $_conn );
            }
        //Add 2010/08/31 --- End   --------------------------
        }
        header( "Content-Type: text/html; charset=" . _CHARSET_OUTPUT );
        echo "<HTML>\n";
        echo "<HEAD>\n";
        echo "<META HTTP-EQUIV=\"Content-Type\" CONTENT=\"text/html;charset=" . _CHARSET_OUTPUT . "\">\n";

        // kdebugger ------- Strat ----------
        echo "<script type=\"text/javascript\" src=\""._SYSTEM_ROOT_URLS."/js/jquery-3.2.1.min.js\"></script>\n";
        // kdebugger ------- End ----------

        echo "</HEAD>\n";
        echo "<BODY>\n";
        echo "<center>\n";
        echo "<br><br><FONT SIZE=4 COLOR=RED><br>${_err_str}</FONT>\n";

        //2012/12/14 Add -- Start --
        // if($_SESSION[_PROJECT_NAME]['login']['admin_id']!="" ||
        //    $_SESSION[_PROJECT_NAME]['login']['admin_login_id']!="" ||
        //    $_SESSION[_PROJECT_NAME]['manage_login']['admin_id'] == "" ||
        //    $_SESSION[_PROJECT_NAME]['manage_login']['admin_login_id'] == "" ){
        //     $b64 = base64_encode($_err_str);
        //     echo "<br><br><form action=\"index.php\" method=\"post\">\n";
        //     echo "<input type=\"hidden\" name=\"err_message\" value=\"".$b64."\">\n";
        //     echo "<input type=\"submit\" value=\"障害報告する\">\n";
        //     echo "</form>\n";
        // }
        //2012/12/14 Add -- End --

        echo "</center>\n";

        // kdebugger ------- Strat ----------
        if($kdebugger) kd_echo($_err_str);
        if($kdebugger) kdebugger(get_defined_vars());
        // kdebugger ------- End ----------

        echo "</BODY>\n";
        echo "</HTML>\n";
        exit();
    }

    //---------------------------------------------------------
    // DB接続 : _dbConnect
    //        引数：なし
    //        戻値：コネクション
    //---------------------------------------------------------
    function _dbConnect($_db_engine=""){
        global $_USE_DB_ENGINE;
        global $_kankyou_kbn;
        global $_conf_msg;
        global $_lang;
        if($_lang==''){
            $_lang='J';
        }
        // MySQLかPostgresの判定
        if($_db_engine==""){
            if($_USE_DB_ENGINE=="") {
                $_USE_DB_ENGINE="PostgreSQL";
            }
            $_db_engine = $_USE_DB_ENGINE;
        }

        //DB接続
        if(strtolower($_db_engine)=="postgresql"){
            // Postgres
            //if( $HTTP_ENV_VARS["windir"] != "" || $HTTP_ENV_VARS["WINDIR"] != "" ){
                //開発環境
                @$_conn = pg_connect("host=" . _PG_DB_SERVER . " "
                                     . "dbname=" . _PG_DB_NAME . " "
                                     . "user=" . _PG_DB_UID . " "
                                     . "password=" . _PG_DB_PASS );
            //}else{
            //    //本番環境
            //    @$_conn = pg_connect("dbname=" . _PG_DB_NAME . " "
            //                         . "user=" . _PG_DB_UID  );
            //}
            //DB選択
            if( !$_conn ){
                _errMsg($_conn,$_conf_msg[$_lang]['inc'][1]);
    #            _errMsg($_conn,'データベース接続エラー');
            }
        }elseif(strtolower($_db_engine)=="mysql"){
            // MySQL

            //2017/03/10 Mod -------------- Before ------------------
            // $_conn = mysql_connect( _MY_DB_SERVER, _MY_DB_UID, _MY_DB_PASS );
            // if( ! $_conn){
            //     _errMsg( $_conn, $_conf_msg[$_lang]['inc'][1] );
            // }else{
            //     if( ! mysql_select_db( _MY_DB_NAME, $_conn ) ){
            //         _errMsg( $_conn, $_conf_msg[$_lang]['inc'][1] );
            //     }
            // }
            //2017/03/10 Mod -------------- After ------------------
            if (version_compare(PHP_VERSION, '5.6.0') >= 0) {
                //5.6.0 より新しいバージョンのPHP
                $_conn = new mysqli(_MY_DB_SERVER, _MY_DB_UID, _MY_DB_PASS, _MY_DB_NAME);
                if( ! $_conn){
                    _errMsg( $_conn, $_conf_msg[$_lang]['inc'][1] );
                }else{
                    if ($_conn->connect_error) {
                        _errMsg( $_conn, $_conf_msg[$_lang]['inc'][1] );
                    }
                }
            }else{
                $_conn = mysql_connect( _MY_DB_SERVER, _MY_DB_UID, _MY_DB_PASS );
                if( ! $_conn){
                    _errMsg( $_conn, $_conf_msg[$_lang]['inc'][1] );
                }else{
                    if( ! mysql_select_db( _MY_DB_NAME, $_conn ) ){
                        _errMsg( $_conn, $_conf_msg[$_lang]['inc'][1] );
                    }
                }
            }
            //2017/03/10 Mod -------------- End ------------------

            if(_ENCODING_DB=="EUC-JP"){
                @_query($_conn,'set names ujis','MySql');
                @_query($_conn,'SET CHARACTER SET ujis','MySql');
            }elseif(_ENCODING_DB=="UTF-8" || _ENCODING_DB=="UTF8"){
                @_query($_conn,'set names '._ENCODING_DB_CHARSET_NAME,'MySql');
                //2010/07/26 Del 無い方がいいみたいなので「～」とかが化けるっぽい
                //@_query($_conn,'SET CHARACTER SET utf8','MySql');

                //2017/03/10 Add -------------- Start ------------------
                if (version_compare(PHP_VERSION, '5.6.0') >= 0) {
                    //5.6.0 より新しいバージョンのPHP
                    if( $_conn){
                        $_conn->set_charset(_ENCODING_DB_CHARSET_NAME);
                    }
                }
                //2017/03/10 Add -------------- End ------------------
            }
        }elseif(strtolower($_db_engine)=="sqlserver"){
            // SQLServer
            $_conn = mssql_connect( _MS_DB_SERVER, _MS_DB_UID, _MS_DB_PASS );
            if( ! $_conn){
                _errMsg( $_conn, $_conf_msg[$_lang]['inc'][1] );
            }else{
                if( ! mssql_select_db( _MS_DB_NAME, $_conn ) ){
                    _errMsg( $_conn, $_conf_msg[$_lang]['inc'][1] );
                }
            }
        }elseif(strtolower($_db_engine)=="sqlite"){
            // SQLite
            $_conn = sqlite_open( _SL_DB_NAME, 0666, $_sqlite_open_errmsg );
            if( ! $_conn){
                _errMsg( $_conn, $_conf_msg[$_lang]['inc'][1] );
            }
        //Add 2010/08/31 --- Start -----------------------------------------------------
        }elseif(strtolower($_db_engine)=="oracle"){
            // Oracle
                @$_conn = oci_connect( _ORA_DB_UID, _ORA_DB_PASS, _ORA_DB_SERVER . ':' . _ORA_DB_PORT . '/' . _ORA_DB_SERVICE_NAME, _ORA_DB_CHAR_SET);
            if( !$_conn ){
                _errMsg($_conn,$_conf_msg[$_lang]['inc'][1]);
            }
        //Add 2010/08/31 --- End   -----------------------------------------------------
        }
        return( $_conn );

    }

    //---------------------------------------------------------
    // DB切断 : _dbDisconnect
    //        引数：コネクション
    //        戻値：なし
    //---------------------------------------------------------
    function _dbDisconnect(&$_conn, $_db_engine=""){
        global $_USE_DB_ENGINE;

        // MySQLかPostgresの判定
        if($_db_engine==""){
            if($_USE_DB_ENGINE=="") {
                $_USE_DB_ENGINE="PostgreSQL";
            }
            $_db_engine = $_USE_DB_ENGINE;
        }

        // コネクション
        if(strtolower($_db_engine)=="postgresql"){
            @pg_close($_conn);
        }elseif(strtolower($_db_engine)=="mysql"){
            //2017/03/10 Mod ------------ Before --------------
            //@mysql_close( $_conn );
            //2017/03/10 Mod ------------ After --------------
            if (version_compare(PHP_VERSION, '5.6.0') >= 0) {
                //5.6.0 より新しいバージョンのPHP
                $_conn->close();
            }else{
                @mysql_close( $_conn );
            }
            //2017/03/10 Mod ------------ End --------------
        }elseif(strtolower($_db_engine)=="sqlserver"){
            //@mysql_close( $_conn );
            //2010/09/16 Mod
            @mssql_close( $_conn );
        }elseif(strtolower($_db_engine)=="sqlite"){
            @sqlite_close( $_conn );
        //Add Start --- 2010/08/31 ---------------------
        }elseif(strtolower($_db_engine)=="oracle"){
            @oci_close( $_conn );
        //Add End   --- 2010/08/31 ---------------------
        }
    }

    //---------------------------------------------------------
    // クエリ発行 : _query
    //        引数：クエリ(SQL文)
    //        戻値：結果セット
    //---------------------------------------------------------
    //---------------------------------------------------------
    // クエリ発行 : _query
    //        引数：クエリ(SQL文)
    //        戻値：結果セット
    //---------------------------------------------------------
    function _query( &$_conn, $_query, $_db_engine=""){
        global $_USE_DB_ENGINE;
        global $_conf_msg;
        global $_lang;
        //Add 2010/08/31 --- Start ------------
        global $_oracle_bind_arr;
        global $_stid;
        //Add 2010/08/31 --- End   ------------

        global $_KB_SQL_GLOBAL_BUFF,$_KB_SQL_GLOBAL_BUFF_SET_CNT,$kdebugger; //2017/09/11 kdebugger

        if($_lang==''){ $_lang='J'; }

        // MySQLかPostgresの判定
        if($_db_engine==""){
            if($_USE_DB_ENGINE=="") {
                $_USE_DB_ENGINE="PostgreSQL";
            }
            $_db_engine = $_USE_DB_ENGINE;
        }

        if(strtolower($_db_engine)=="postgresql"){
            $w_query = $_query;

            if( substr(strtolower(trim($_query)),0,5) == "begin"){
                $GLOBALS['_tran_flag'] = "ON";
            }elseif( substr(strtolower(trim($_query)),0,6) == "commit" ||
                     substr(strtolower(trim($_query)),0,8) == "rollback"){
                $GLOBALS['_tran_flag'] = "";
            }

            if( substr(strtolower(trim($_query)),0,6) == "insert" ||
                substr(strtolower(trim($_query)),0,6) == "update" ||
                substr(strtolower(trim($_query)),0,6) == "delete" ){
                if($GLOBALS['_tran_flag'] != "ON" ){
                    _errMsg($_conn,"[not tran]".$_conf_msg[$_lang]['inc'][2] .
                         "<!-- " . _hs(mb_convert_encoding($w_query,_ENCODING_SRC,_ENCODING_DB)) . " -->"
                         );
                }
            }


            @$_result=pg_exec($_conn,$w_query);
        #    if( ! $_result ){
            if( $_result == FALSE ){ //結果が0件の場合は通らない
                $_pg_err_msg = @pg_errormessage($_conn);
                @$_dmy=pg_exec($_conn,'rollback');
                _errMsg($_conn,$_conf_msg[$_lang]['inc'][2] .
    #            _errMsg($_conn,'データベース操作に失敗しました。' .
                     "<!-- " . _hs(mb_convert_encoding($_pg_err_msg,_ENCODING_SRC,_ENCODING_DB)) . " -->" .
                     "<!-- " . _hs(mb_convert_encoding($w_query,_ENCODING_SRC,_ENCODING_DB)) . " -->"
                     );
            }
        }elseif(strtolower($_db_engine)=="mysql"){
            //$w_query = mb_convert_encoding( $_query, _ENCODING_DB, _ENCODING_SRC  );
            $w_query = $_query;

            //2017/03/10 Mod ------------- Before ------------------
            // if( ! $_result = @mysql_query( $w_query, $_conn ) ){
            //     $_my_err_msg = @mysql_error($_conn);
            //     @mysql_query( "rollback", $_conn ); //2008/07/04 Add
            //     _errMsg($_conn,$_conf_msg[$_lang]['inc'][2] .
            //          "<!-- " . _hs(mb_convert_encoding($_my_err_msg,_ENCODING_SRC,_ENCODING_DB)) . " -->" .
            //          "<!-- " . _hs(mb_convert_encoding($w_query,_ENCODING_SRC,_ENCODING_DB)) . " -->"
            //          );
            // }
            //2017/03/10 Mod ------------- After ------------------

            //2017/09/11 kdebugger ---- Strat -----
            if($kdebugger==true){
                if( strtolower(substr(trim($w_query),0,6))=="select" ||
                    strtolower(substr(trim($w_query),0,6))=="insert" ||
                    strtolower(substr(trim($w_query),0,6))=="update" ||
                    strtolower(substr(trim($w_query),0,6))=="delete" ||
                    strtolower(substr(trim($w_query),0,5))=="begin" ||
                    strtolower(substr(trim($w_query),0,6))=="commit" ){

                    if($_KB_SQL_GLOBAL_BUFF_SET_CNT == 100){
                        $_KB_SQL_GLOBAL_BUFF .= '＊＊＊＊＊＊＊＊　実行SQLが100個を超えましたので、kdebuggerへの出力を停止しました。　＊＊＊＊＊＊＊＊<br>';
                        $_KB_SQL_GLOBAL_BUFF_SET_CNT++;
                    }elseif($_KB_SQL_GLOBAL_BUFF_SET_CNT > 100){

                    }else{
                        $_KB_SQL_GLOBAL_BUFF_SET_CNT++;
                    }
                    if($_KB_SQL_GLOBAL_BUFF_SET_CNT <= 100){
                        $_KB_SQL_GLOBAL_BUFF .= '＝＝＝＝実行SQL文＝＝＝＝<br>';
                        $_kd_sql = $w_query;
                        $_kd_sql = str_replace("\r\n","\n",$_kd_sql);
                        $_kd_sql = str_replace("\r","\n",$_kd_sql);
                        $_KB_SQL_GLOBAL_BUFF .= "<pre>". _hs($_kd_sql) ."</pre>";
                    }
                }
            }
            //2017/09/11 kdebugger ---- End -----

            if (version_compare(PHP_VERSION, '5.6.0') >= 0) {
                //5.6.0 より新しいバージョンのPHP
                $_result = @$_conn->query($w_query);
                if (!$_result) {
                    //2017/09/11 kdebugger ---- Strat -----
                    if($kdebugger==true){
                        if($_KB_SQL_GLOBAL_BUFF_SET_CNT <= 100){
                            $_KB_SQL_GLOBAL_BUFF .= "<hr>";
                        }
                    }
                    //2017/09/11 kdebugger ---- End -----
                    $_my_err_msg = @$_conn->error;
                    @$_conn->query( "rollback" );
                    if( strpos( strtolower(__FILE__),"apacheroot")!==FALSE){
                        _errMsg($_conn,$_conf_msg[$_lang]['inc'][2] .
                             " <hr>" . _hs(mb_convert_encoding($_my_err_msg,_ENCODING_SRC,_ENCODING_DB)) . " " .
                             " <hr>" . _hs(mb_convert_encoding($w_query,_ENCODING_SRC,_ENCODING_DB)) . " "
                             );
                    }else{
                        _errMsg($_conn,$_conf_msg[$_lang]['inc'][2] .
                             "<!-- " . _hs(mb_convert_encoding($_my_err_msg,_ENCODING_SRC,_ENCODING_DB)) . " -->" .
                             "<!-- " . _hs(mb_convert_encoding($w_query,_ENCODING_SRC,_ENCODING_DB)) . " -->"
                             );
                    }
                }

                //2017/09/11 kdebugger ---- Strat -----
                if($kdebugger==true){
                    if($_KB_SQL_GLOBAL_BUFF_SET_CNT <= 100){
                        if( strtolower(substr(trim($w_query),0,6))=="select"){
                            $num_rows = $_result->num_rows;
                            $_KB_SQL_GLOBAL_BUFF .= '<br><span style="font-weight:bold;">■select結果レコード数：'.$num_rows.'件</span>';
                            if($num_rows > 0){
                                $row = 0;
                                $rec = _fetchArray( $_result, $row );
                                $_result->data_seek(0);
                            }
                        }elseif(strtolower(substr(trim($w_query),0,6))=="insert" ||
                            strtolower(substr(trim($w_query),0,6))=="update" ||
                            strtolower(substr(trim($w_query),0,6))=="delete" ){
                            $num_rows = $_conn->affected_rows;
                            $_KB_SQL_GLOBAL_BUFF .= '<br><span style="font-weight:bold;">■実行結果数：'.$num_rows.'件</span>';
                        }
                        if( strtolower(substr(trim($w_query),0,6))=="select"){
                            if($num_rows > 0){
                                $_KB_SQL_GLOBAL_BUFF .= '<br><span style="font-weight:bold;">■最初のレコード</span><br>';
                                ob_start();
                                print_r($rec);
                                $_kd_buff = ob_get_contents();
                                ob_end_clean();
                                $_kd_buff = str_replace("\r\n","\n",$_kd_buff);
                                $_kd_buff = str_replace("\r","\n",$_kd_buff);
                                $_KB_SQL_GLOBAL_BUFF .= "<pre>". _hs($_kd_buff) ."</pre>";
                            }
                        }
                        $_KB_SQL_GLOBAL_BUFF .= "<hr>";
                    }
                }
                //2017/09/11 kdebugger ---- End -----
            }else{
                if( ! $_result = @mysql_query( $w_query, $_conn ) ){
                    //2017/09/11 kdebugger ---- Strat -----
                    if($kdebugger==true){
                        if($_KB_SQL_GLOBAL_BUFF_SET_CNT <= 100){
                            $_KB_SQL_GLOBAL_BUFF .= "<hr>";
                        }
                    }
                    //2017/09/11 kdebugger ---- End -----
                    $_my_err_msg = @mysql_error($_conn);
                    @mysql_query( "rollback", $_conn ); //2008/07/04 Add
                    _errMsg($_conn,$_conf_msg[$_lang]['inc'][2] .
                         "<!-- " . _hs(mb_convert_encoding($_my_err_msg,_ENCODING_SRC,_ENCODING_DB)) . " -->" .
                         "<!-- " . _hs(mb_convert_encoding($w_query,_ENCODING_SRC,_ENCODING_DB)) . " -->"
                         );
                }

                //2017/09/11 kdebugger ---- Strat -----
                if($kdebugger==true){
                    if($_KB_SQL_GLOBAL_BUFF_SET_CNT <= 100){
                        if( strtolower(substr(trim($w_query),0,6))=="select"){
                            $num_rows = mysql_num_rows($_result);
                            $_KB_SQL_GLOBAL_BUFF .= '<br><span style="font-weight:bold;">■select結果レコード数：'.$num_rows.'件</span>';
                            if($num_rows > 0){
                                $row = 0;
                                $rec = _fetchArray( $_result, $row );
                                mysql_data_seek($_result,0);
                            }
                        }elseif(strtolower(substr(trim($w_query),0,6))=="insert" ||
                            strtolower(substr(trim($w_query),0,6))=="update" ||
                            strtolower(substr(trim($w_query),0,6))=="delete" ){
                            $num_rows = mysql_affected_rows($_conn);
                            $_KB_SQL_GLOBAL_BUFF .= '<br><span style="font-weight:bold;">■実行結果数：'.$num_rows.'件</span>';
                        }
                        if( strtolower(substr(trim($w_query),0,6))=="select"){
                            if($num_rows > 0){
                                $_KB_SQL_GLOBAL_BUFF .= '<br><span style="font-weight:bold;">■最初のレコード</span><br>';
                                ob_start();
                                print_r($rec);
                                $_kd_buff = ob_get_contents();
                                ob_end_clean();
                                $_kd_buff = str_replace("\r\n","\n",$_kd_buff);
                                $_kd_buff = str_replace("\r","\n",$_kd_buff);
                                $_KB_SQL_GLOBAL_BUFF .= "<pre>". _hs($_kd_buff) ."</pre>";
                            }
                        }
                        $_KB_SQL_GLOBAL_BUFF .= "<hr>";
                    }
                }
                //2017/09/11 kdebugger ---- End -----
            }
            //2017/03/10 Mod ------------- End ------------------

        }elseif(strtolower($_db_engine)=="sqlserver"){
            //$w_query = mb_convert_encoding( $_query, _ENCODING_DB, _ENCODING_SRC  );
            $w_query = $_query;

            if( ! $_result = @mssql_query( $w_query, $_conn ) ){
                $_my_err_msg = @mssql_get_last_message($_conn);
                @$_dmy = mssql_query( "ROLLBACK TRANSACTION", $_conn );
                if( $_SERVER['HTTP_HOST'] == "localhost" || strpos($_SERVER['HTTP_HOST'],".dyndns.org")!==FALSE ){
                    _errMsg($_conn,"<!--".$_conf_msg[$_lang]['inc'][2]."-->" .
                         "<!-- " . _hs(mb_convert_encoding($_my_err_msg,_ENCODING_SRC,_ENCODING_DB)) . " -->" .
                         "<!-- " . _hs(mb_convert_encoding($w_query,_ENCODING_SRC,_ENCODING_DB)) . " -->"
                         );
                }else{
                    _errMsg($_conn,"<!--".$_conf_msg[$_lang]['inc'][2]."-->" .
                         //"<!-- " . _hs(mb_convert_encoding($_my_err_msg,_ENCODING_SRC,"UTF-8")) . " -->" .
                         //"<!-- " . _hs(mb_convert_encoding($w_query,_ENCODING_SRC,"UTF-8")) . " -->"
                         "<!-- " . _hs(mb_convert_encoding($_my_err_msg,_ENCODING_SRC,_ENCODING_DB)) . " -->" .
                         "<!-- " . _hs(mb_convert_encoding($w_query,_ENCODING_SRC,_ENCODING_DB)) . " -->"
                         );

                }
            }
        }elseif(strtolower($_db_engine)=="sqlite"){
            $w_query = $_query;

//2010/09/21 mod -----------
#             $_var = split("\.",PHP_VERSION);
            $_var = preg_split("/\./",PHP_VERSION);
//2010/09/21 mod -----------

            if($_var[0]>=5 && $_var[1]>=1){
                $_result = @sqlite_query( $_conn,$w_query,SQLITE_BOTH ,$_sl_err_msg );
            }else{
                $_sl_err_msg = "";
                $_result = @sqlite_query( $_conn,$w_query,SQLITE_BOTH );
            }

            if( ! $_result ){
                $_sl_err_msg .= " " . @sqlite_last_error($_conn);
                @$_dmy=sqlite_exec($_conn,'rollback');
                if(_ENCODING_SRC != _ENCODING_DB){
                    _errMsg($_conn,$_conf_msg[$_lang]['inc'][2] .
                         "<!-- " . _hs(mb_convert_encoding($_sl_err_msg,_ENCODING_SRC,_ENCODING_DB)) . " -->" .
                         "<!-- " . _hs(mb_convert_encoding($w_query,_ENCODING_SRC,_ENCODING_DB)) . " -->"
                         );
                }else{
                    _errMsg($_conn,$_conf_msg[$_lang]['inc'][2] .
                         "<!-- " . _hs($_sl_err_msg) . " -->" .
                         "<!-- " . _hs($w_query) . " -->"
                         );

                }
            }
        //Add 2010/08/31 --- Start ------------------------------------------------------------------------------
        }elseif(strtolower($_db_engine)=="oracle"){
            $w_query = $_query;

            //beginならなにもしない
            if(strtolower($w_query) == "begin"){
                $_result = 1;
                return( $_result );
            }

            //commitならコミット処理
            if(strtolower($w_query) == "commit"){
                $_result = @oci_commit($_conn);
                if($_result == false){
                    $oracle_err = @oci_error($_conn);
                    @oci_rollback($_conn);
                    _errMsg($_conn,$_conf_msg[$_lang]['inc'][2] .
                         "<!-- " . _hs(mb_convert_encoding($oracle_err['message']." ".$oracle_err['sqltext']."(offset:".$oracle_err['offset'].")",_ENCODING_SRC,_ENCODING_DB)) . " -->" .
                         "<!-- " . _hs(mb_convert_encoding($w_query,_ENCODING_SRC,_ENCODING_DB)) . " -->"
                         );
                }
                return( $_result );
            }

            //シングルクォートを取り除く
            if( _count($_oracle_bind_arr) > 0 ){
                foreach($_oracle_bind_arr as $_bind_name => $_bind_value){
                    if( strpos($w_query,$_bind_name) !== false){
                        $w_query = str_replace( "'".$_bind_name."'", $_bind_name, $w_query);
                    }
                }
            }

            //offset と limit の値を取得して、Oracle用にSQL文を再構築する

//2010/09/21 mod -----------
#             $wfld = split(" +",$w_query); //spaceで区切る正規表現
            $wfld = preg_split("/ +/",$w_query); //spaceで区切る正規表現
//2010/09/21 mod -----------

            $offset_val_idx = -1;
            $limit_val_idx = -1;
            $offset_val = "";
            $limit_val = "";
            for($i=0;$i<_count($wfld);$i++){
                if( $i == $offset_val_idx ){
                    $offset_val = $wfld[$i];
                }elseif( $i == $limit_val_idx ){
                    $limit_val = $wfld[$i];
                }
                if( strtolower($wfld[$i]) == "offset"){
                    $offset_val_idx = $i + 1;
                }elseif( strtolower($wfld[$i]) == "limit"){
                    $limit_val_idx = $i + 1;
                }
            }
            if($limit_val != ""){
            //-- limitの値があった場合
                if($offset_val == ""){
                    $offset_val = "0";
                }
                $offset_val = intval($offset_val);
                $limit_val = intval($limit_val);

                $st_rownum = $offset_val + 1;
                $ed_rownum = $offset_val + $limit_val;

//2010/09/21 mod -----------
#                 $wfld2 = split(" limit ",$w_query);
#                 $wfld3 = split(" offset ",$wfld2[0]);
                $wfld2 = explode(" limit ",$w_query);
                $wfld3 = explode(" offset ",$wfld2[0]);
//2010/09/21 mod -----------

                $sub_query = $wfld3[0];

                $exec_sql  = "";
                $exec_sql .= "select V_OFFSETLIMIT_SUB_QUERY2.* from (";
                $exec_sql .= "   select V_OFFSETLIMIT_SUB_QUERY.*, rownum as sub_query_rownum from (" . $sub_query . ") V_OFFSETLIMIT_SUB_QUERY";
                $exec_sql .= " ) V_OFFSETLIMIT_SUB_QUERY2";
                $exec_sql .= " where sub_query_rownum between " . $st_rownum . " and " . $ed_rownum;
                $w_query  = $exec_sql;
            }

            //パース
            $_stid = @oci_parse($_conn, $w_query);
            if($_stid == false){
                $oracle_err = @oci_error($_conn);
                @oci_rollback($_conn);
                _errMsg($_conn,$_conf_msg[$_lang]['inc'][2] .
                     "<!-- " . _hs(mb_convert_encoding($oracle_err['message']." ".$oracle_err['sqltext']."(offset:".$oracle_err['offset'].")",_ENCODING_SRC,_ENCODING_DB)) . " -->" .
                     "<!-- " . _hs(mb_convert_encoding($w_query,_ENCODING_SRC,_ENCODING_DB)) . " -->"
                     );
            }

            //バインド
            if( _count($_oracle_bind_arr) > 0 ){
                foreach($_oracle_bind_arr as $_bind_name => $_bind_value){
                    if( strpos($w_query,$_bind_name) !== false){
                        $_bind_result = @oci_bind_by_name($_stid, $_bind_name, $_oracle_bind_arr[$_bind_name]);
                        if($_bind_result == false){
                            $oracle_err = @oci_error($_stid);
                            @oci_rollback($_conn);
                            _errMsg($_conn,$_conf_msg[$_lang]['inc'][2] .
                                 "<!-- " . _hs(mb_convert_encoding($oracle_err['message']." ".$oracle_err['sqltext']."(offset:".$oracle_err['offset'].")",_ENCODING_SRC,_ENCODING_DB)) . " -->" .
                                 "<!-- " . _hs(mb_convert_encoding($w_query,_ENCODING_SRC,_ENCODING_DB)) . " -->"
                                 );
                        }
                    }
                }
            }

            //SQL実行
            if(phpversion() <= '5.3.1'){
                $_ORA_DB_NO_AUTO_COMMIT = OCI_DEFAULT;
            }else{
                $_ORA_DB_NO_AUTO_COMMIT = OCI_NO_AUTO_COMMIT;
            }
            $_result = @oci_execute($_stid, $_ORA_DB_NO_AUTO_COMMIT);

            if( $_result == FALSE ){
                $oracle_err = @oci_error($_stid);
                @oci_rollback($_conn);
                _errMsg($_conn,$_conf_msg[$_lang]['inc'][2] .
                     "<!-- " . _hs(mb_convert_encoding($oracle_err['message']." ".$oracle_err['sqltext']."(offset:".$oracle_err['offset'].")",_ENCODING_SRC,_ENCODING_DB)) . " -->" .
                     "<!-- " . _hs(mb_convert_encoding($w_query,_ENCODING_SRC,_ENCODING_DB)) . " -->"
                     );
            }
        //Add 2010/08/31 --- End   ------------------------------------------------------------------------------
        }
        return( $_result );
    }

    //---------------------------------------------------------
    // FETCH実行 : _fetchArray
    //        引数：結果セット
    //        戻値：当該行結果配列
    //---------------------------------------------------------
    function _fetchArray( &$_result , &$_row, $_db_engine=""){
        global $_USE_DB_ENGINE;
        //Add 2010/08/31
        global $_stid;

        // MySQLかPostgresの判定
        if($_db_engine==""){
            if($_USE_DB_ENGINE=="") {
                $_USE_DB_ENGINE="PostgreSQL";
            }
            $_db_engine = $_USE_DB_ENGINE;
        }

        if(strtolower($_db_engine)=="postgresql"){
            @$_ret = pg_fetch_array($_result, $_row, PGSQL_ASSOC);
            if($_ret != false){
                //while(list($key,$val) = each($_ret)){
                //2009/03/02 Mod
                foreach($_ret as $key => $val){
                    $_ret[$key] = mb_convert_encoding($val,_ENCODING_SRC,_ENCODING_DB);
                }
            }
        }elseif(strtolower($_db_engine)=="mysql"){

            //2017/03/10 Mod ------------ Before -----------------
            // $_ret = mysql_fetch_array( $_result );
            // if( $_ret ){
            //     //while( list( $_key, $_val ) = each( $_ret ) ){
            //     //2009/03/02 Mod
            //     foreach($_ret as $_key => $_val){
            //         $_ret[$_key] = mb_convert_encoding( $_val, _ENCODING_SRC, _ENCODING_DB );
            //     }
            // }
            //2017/03/10 Mod ------------ After -----------------
            if (version_compare(PHP_VERSION, '5.6.0') >= 0) {
                //5.6.0 より新しいバージョンのPHP
                $_ret = $_result->fetch_assoc();
                if( $_ret ){
                    foreach($_ret as $_key => $_val){
                        $_ret[$_key] = mb_convert_encoding( $_val, _ENCODING_SRC, _ENCODING_DB );
                    }
                }
            }else{
                $_ret = mysql_fetch_array( $_result, MYSQL_ASSOC );
                if( $_ret ){
                    //while( list( $_key, $_val ) = each( $_ret ) ){
                    //2009/03/02 Mod
                    foreach($_ret as $_key => $_val){
                        $_ret[$_key] = mb_convert_encoding( $_val, _ENCODING_SRC, _ENCODING_DB );
                    }
                }
            }
            //2017/03/10 Mod ------------ End -----------------

        }elseif(strtolower($_db_engine)=="sqlserver"){ //SQLServer

            $_ret = mssql_fetch_array( $_result );

            if( $_ret ){
                if(_ENCODING_SRC != _ENCODING_DB){
                    //while( list( $_key, $_val ) = each( $_ret ) ){
                    //2009/03/02 Mod
                    foreach($_ret as $_key => $_val){
                        if($_val==" "){
                            $_val = trim($_val);
                        }
                        $_ret[$_key] = mb_convert_encoding( $_val, _ENCODING_SRC, _ENCODING_DB );
                    }
                }else{
                    //while( list( $_key, $_val ) = each( $_ret ) ){
                    //2009/03/02 Mod
                    foreach($_ret as $_key => $_val){
                        if($_val==" "){
                            $_val = trim($_val);
                        }
                        $_ret[$_key] = $_val;
                    }
                }
            }

        }elseif(strtolower($_db_engine)=="sqlite"){

            $_ret = sqlite_fetch_array( $_result );

            if( $_ret ){
                //while( list( $_key, $_val ) = each( $_ret ) ){
                //2009/03/02 Mod
                foreach($_ret as $_key => $_val){
                    $_ret[$_key] = mb_convert_encoding( $_val, _ENCODING_SRC, _ENCODING_DB );
                }
            }
        //Add 2010/08/31 --- Start ----------------------------------------------------------------
        }elseif(strtolower($_db_engine)=="oracle"){
            $_ret = @oci_fetch_array($_stid, OCI_ASSOC+OCI_RETURN_NULLS+OCI_RETURN_LOBS);
            if($_ret != false){
                foreach($_ret as $key => $val){
                    $_ret[strtolower($key)] = mb_convert_encoding($val,_ENCODING_SRC,_ENCODING_DB);
                    unset($_ret[$key]);
                }
            }
        //Add 2010/08/31 --- End ----------------------------------------------------------------
        }

        return( $_ret );
    }

    //---------------------------------------------------------
    // 結果セット開放 : _freeResult
    //        引数：結果セット
    //        戻値：なし
    //---------------------------------------------------------
    function _freeResult(&$_result, $_db_engine=""){
        global $_USE_DB_ENGINE;
        //Add 2010/08/31
        global $_stid;

        // MySQLかPostgresの判定
        if($_db_engine==""){
            if($_USE_DB_ENGINE=="") {
                $_USE_DB_ENGINE="PostgreSQL";
            }
            $_db_engine = $_USE_DB_ENGINE;
        }

        if(strtolower($_db_engine)=="postgresql"){
            @pg_free_result($_result);
        }elseif(strtolower($_db_engine)=="mysql"){
            //2017/03/10 Mod ------------- Before ---------------
            // @mysql_free_result( $_result );
            //2017/03/10 Mod ------------- After ---------------
            if (version_compare(PHP_VERSION, '5.6.0') >= 0) {
                //5.6.0 より新しいバージョンのPHP
                $_result->free();
            }else{
                @mysql_free_result( $_result );
            }
            //2017/03/10 Mod ------------- End ---------------
        }elseif(strtolower($_db_engine)=="sqlserver"){
            @mssql_free_result( $_result );
        }elseif(strtolower($_db_engine)=="sqlite"){
            //@sqlite_free_result( $_result );
        //Add 2010/08/31 --- Start --------------------
        }elseif(strtolower($_db_engine)=="oracle"){
            @oci_free_statement($_stid);
        //Add 2010/08/31 --- Start --------------------
        }
    }

    //---------------------------------------------------------
    // ページ送り作成  ：_make_pagenavi
    //             引数：smarty
    //                 ：リクエスト
    //                 ：オフセット
    //                 ：カウント
    //                 ：リミット
    //---------------------------------------------------------
    function _make_pagenavi(&$blade, $_request, $offset, $count, $limit ){
        if($count > 0){
            $blade->assign('page_flg', '1');
        }else{
            $blade->assign('page_flg', '0');
        }

        // 次へのボタン
        if($offset + $limit < $count)
            $blade->assign('page_next', '1');
        else
            $blade->assign('page_next', '0');

        // 前へのボタン
        if($offset - $limit >= 0)
            $blade->assign('page_back', '1');
        else
            $blade->assign('page_back', '0');

        $pages = array();
        for($i = 0; $i * $limit < $count; $i++){
            $pages[$i]['offset'] = $i * $limit;
            if($offset != $i * $limit)
                $pages[$i]['link'] = 1;
            else
                $pages[$i]['link'] = 0;
            $pages[$i]['separator'] = 1;
        }
        $pages[$i-1]['separator'] = 0;
        $blade->assign('page_navi', $pages);
        // 総件数
        $blade->assign('count', $count);
        // 開始件数
        $blade->assign('st_count', $offset + 1);
        // 最終件数
        if($count < $limit + $offset)
            $blade->assign('en_count', $count);
        else
            $blade->assign('en_count', $limit + $offset);

        // オフセット
        $blade->assign('offset_next', $offset + $limit);
        $blade->assign('offset_back', $offset - $limit);
        // ページ
        $blade->assign('page', $_request['page']);
    }

    //---------------------------------------------------------
    // ページ送り作成  ：_make_pagenavi2
    //             引数：smarty
    //                 ：リクエスト
    //                 ：オフセット
    //                 ：カウント
    //                 ：リミット
    //
    //       --environmentの設定値 表示最大ページ数--
    //         _PAGENAVI2_PAGE_MAX
    //---------------------------------------------------------
    function _make_pagenavi2(&$blade, $_request, $offset, $count, $limit ){
        // ページナビの表示フラグ
        if($count > 0){
            $blade->assign('page_flg', '1');
        }else{
            $blade->assign('page_flg', '0');
        }

        // 表示する場合
        if($count > 0){
            // 次へのボタン
            if($offset + $limit < $count){
                $blade->assign('page_next', '1');
            }else{
                $blade->assign('page_next', '0');
            }

            // 前へのボタン
            if($offset - $limit >= 0){
                $blade->assign('page_back', '1');
            }else{
                $blade->assign('page_back', '0');
            }

            // 総件数
            $blade->assign('count', $count);

            // ページ
            $blade->assign('page', $_request['page']);


            // 総ページ数
            $all_page_cnt = ceil($count / $limit);

            // 表示最大ページ数を半分にした場合、少ない方のページ数
            if(_PAGENAVI2_PAGE_MAX <= 2){
                $half_min_pagenavi2 = 0;
            }else{
                $half_min_pagenavi2 = floor((_PAGENAVI2_PAGE_MAX - 1) / 2);
            }

            // 表示最大ページ数を半分にした場合、多い方のページ数
            if(_PAGENAVI2_PAGE_MAX <= 1){
                $half_max_pagenavi2 = 0;
            }else{
                $half_max_pagenavi2 = ceil((_PAGENAVI2_PAGE_MAX - 1) / 2);
            }

            // オフセットによるピボットページ番号の設定
            $pivot_page_num = ceil(($offset + $limit) / $limit);
            $blade->assign('pivot_page_num', $pivot_page_num);
            if($offset - $limit >= 0){
                $blade->assign('back_page_num', $pivot_page_num - 1);
            }
            if($offset + $limit < $count){
                $blade->assign('next_page_num', $pivot_page_num + 1);
            }

            // スタートページ
            if($all_page_cnt <= _PAGENAVI2_PAGE_MAX){
            //-- 表示最大ページ数より総ページ数が少ない場合
                $start_page_num = 1;
            }elseif($pivot_page_num == 1){
            //-- 現在のピボットページが最初のページの場合
                $start_page_num = 1;
            }elseif($pivot_page_num == $all_page_cnt){
            //-- 現在のピボットページが最後のページの場合
                $start_page_num = $all_page_cnt - _PAGENAVI2_PAGE_MAX + 1;
            }elseif($pivot_page_num - 1 > 0 && (_PAGENAVI2_PAGE_MAX == 1 || _PAGENAVI2_PAGE_MAX == 2)){
            //-- 現在のピボットページの前にページがあり、かつ表示最大ページ数が１か２の時
                if(_PAGENAVI2_PAGE_MAX == 1){
                    $start_page_num = $pivot_page_num;
                }else{
                    $start_page_num = $pivot_page_num - 1;
                }
            }elseif($pivot_page_num - 1 <= $half_min_pagenavi2){
            //-- 現在のピボットページの前のページ数が、表示最大ページ数より小さい場合
                $start_page_num = 1;
            }elseif($pivot_page_num - 1 > $half_min_pagenavi2){  // ?floor() か ceil() か社長に聞く
            //-- 現在のピボットページの前のページ数が、表示最大ページ数より大きい場合
                if($all_page_cnt - $pivot_page_num < $half_max_pagenavi2){
                    $start_page_num = $all_page_cnt - _PAGENAVI2_PAGE_MAX + 1;
                }else{
                    $start_page_num = $pivot_page_num - $half_min_pagenavi2;
                }
            }

            // 表示するページのオフセット・リンク・セパレータの設定
            $pages = array();
            for($i=0, $j=$start_page_num-1; ($i < _PAGENAVI2_PAGE_MAX) && (($j * $limit) < $count); $i++, $j++){
                $pages[$i]['offset']   = $j * $limit;
                $pages[$i]['page_num'] = $j+1;
                if($offset != $j * $limit){
                    $pages[$i]['link'] = 1;
                }else{
                    $pages[$i]['link'] = 0;
                }
                $pages[$i]['separator'] = 1;
            }
            $pages[$i-1]['separator'] = 0;
            $blade->assign('page_navi', $pages);

            // 開始件数
            $blade->assign('st_count', $offset + 1);

            // 最終件数
            if($limit + $offset > $count){
                $blade->assign('en_count', $count);
            }else{
                $blade->assign('en_count', $limit + $offset);
            }

            // 最初の・・・
            if($all_page_cnt > _PAGENAVI2_PAGE_MAX && $start_page_num != 1){
                $blade->assign('start_dot',1);
            }

            // 最後の・・・
            if($all_page_cnt > _PAGENAVI2_PAGE_MAX && $start_page_num + _PAGENAVI2_PAGE_MAX - 1 < $all_page_cnt){
                $blade->assign('end_dot',1);
            }

            // オフセット
            $blade->assign('offset_next', $offset + $limit);
            $blade->assign('offset_back', $offset - $limit);

            // 最初へのオフセット・最後へのオフセット
            $blade->assign('offset_first', 0);
            $blade->assign('offset_last', ($all_page_cnt - 1) * $limit);

            // 前の _PAGENAVI2_PAGE_MAX 件、次の _PAGENAVI2_PAGE_MAX 件
            if($all_page_cnt > _PAGENAVI2_PAGE_MAX && $start_page_num != 1){
                $offset_back_page_num = $start_page_num - _PAGENAVI2_PAGE_MAX; //  + $half_max_pagenavi2
                if($offset_back_page_num < 1){
                    $offset_back_page_num = 1;
                }
                $blade->assign('offset_back_page', ($offset_back_page_num - 1) * $limit);
            }
            if($all_page_cnt > _PAGENAVI2_PAGE_MAX && $start_page_num + _PAGENAVI2_PAGE_MAX - 1 < $all_page_cnt){
                $offset_next_page_num = $start_page_num + _PAGENAVI2_PAGE_MAX; //  + $half_min_pagenavi2
                if($offset_next_page_num > $all_page_cnt){
                    $offset_next_page_num = $all_page_cnt;
                }
                $blade->assign('offset_next_page', ($offset_next_page_num - 1) * $limit);
            }
        }
    }


    //---------------------------------------------------------
    //ＤＢに登録する：_select
    //          引数：ＤＢ名
    //              ：条件配列
    //          戻値：結果
    //---------------------------------------------------------
    function _select( $_sql ){
        global $conn;

        $result = _query( $conn, $_sql );

        $recs = array();
        $row = 0;
        while( $rec = _fetchArray( $result, $row ) ){
            $recs[$row] = $rec;
            $row++;
        }

        _freeResult( $result );

        return( $recs );
    }

    //---------------------------------------------------------
    //ＤＢに登録する：_insert
    //          引数：ＤＢ名
    //              ：登録配列
    //---------------------------------------------------------
    function _insert( $_db, $_array ){
        global $conn;

        $sql = "insert into " . $_db . " ( ";
        $sql2 = " values( ";

#         list( $_key, $_val ) = each( $_array );
#
#         $sql  .= $_key;
#         $sql2 .= $_val;
#         while( list( $_key, $_val ) = each( $_array ) ){
#             $sql  .= ", " . $_key;
#             $sql2 .= ", " . $_val;
#         }
#2009/03/02 Mod ------------- Strat -----------
        $_ins_fst_flg = 1;
        foreach($_array as $_key => $_val){
            if($_ins_fst_flg==1){
                $_ins_fst_flg = 0;
                $sql  .= $_key;
                $sql2 .= $_val;
            }else{
                $sql  .= ", " . $_key;
                $sql2 .= ", " . $_val;
            }
        }
#2009/03/02 Mod ------------- End -----------

        $sql  .= " )";
        $sql2 .= " )";

        $sql .= $sql2;

        _query( $conn, $sql);
    }

    //---------------------------------------------------------
    //ＤＢに更新する：_update
    //          引数：ＤＢ名
    //              ：更新配列
    //              ：キー配列
    //---------------------------------------------------------
    function _update( $_db, $_array, $_where ){
        global $conn;

        if($_where==""){
            die("<html><body>UPDATE SQL NOT WHERE</body></html>");
        }

        $sql = "update " . $_db . " set ";

        $cnt = 0;
        //while( list( $_key, $_val ) = each( $_array ) ){
        //2009/03/02 Mod
        foreach($_array as $_key => $_val){
            if($cnt > 0 ){
                $kugiri = ",";
            }else{
                $kugiri = "";
            }

            $sql .= $kugiri . " " . $_key . " = " . $_val;
            $cnt++;
        }

#        $cnt = _count($_where);
#        if( $cnt > 0 ){
#            $sql .= " where ";
#
#            list( $_key, $_val ) = each( $_where );
#
#            $sql .= $_key . " = " . $_val;
#
#            if( $cnt > 1 ){
#                while( list( $_key, $_val ) = each( $_where ) ){
#                    $sql .= " and " . $_key . " = " . $_val;
#                }
#            }
#        }
        $sql .= " where " . $_where;

        _query( $conn, $sql);
    }

    //---------------------------------------------------------
    //ＤＢに削除する：_delete
    //          引数：ＤＢ名
    //              ：削除配列
    //              ：キー配列
    //---------------------------------------------------------
    function _delete( $_db, $_where ){
        global $conn;

        if($_where==""){
            die("<html><body>DELETE SQL NOT WHERE</body></html>");
        }

        $sql = "delete from " . $_db . " where " . $_where;

#        $cnt = 0;
#        while( list( $_key, $_val ) = each( $_where ) ){
#
#            if( $cnt < 1 ){
#                $sql .= $_key . " = " . $_val;
#            }else{
#                $sql .= " and " . $_key . " = " . $_val;
#            }
#            $cnt++;
#        }

        _query( $conn, $sql );
    }


    //---------------------------------------------------------
    // SQLServer用ストアド実行関数
    //        引数：ストアド名
    //              パラメータ配列
    //---------------------------------------------------------
    function _mssqlSPExec($_sp_name,&$_params){
        //ストアド情報取得
        $sql = "";
        $sql .= "SELECT";
        $sql .= " OBJ.id AS obj_id";
        $sql .= " ,OBJ.name AS obj_name";
        $sql .= " ,CLM.name AS para_name";
        $sql .= " ,TYP.name AS para_type";
        $sql .= " ,CLM.prec AS para_len";
        $sql .= " ,(CASE CLM.isoutparam";
        $sql .= "      WHEN 0 THEN 'in'";
        $sql .= "      WHEN 1 THEN 'inout'";
        $sql .= "   END) AS para_inout";
        $sql .= " ,CLM.colorder as para_order";
        $sql .= " FROM";
        $sql .= " sysobjects AS OBJ";
        $sql .= " LEFT JOIN syscolumns AS CLM";
        $sql .= " ON OBJ.id = CLM.id";
        $sql .= " LEFT JOIN systypes AS TYP";
        $sql .= " ON CLM.xtype = TYP.xtype";
        $sql .= " WHERE";
        $sql .= " OBJECTPROPERTY(OBJ.id, N'IsProcedure') = 1 and";
        $sql .= " TYP.name != 'sysname' and";
        $sql .= " OBJ.name='"._as($_sp_name)."'";
        $sql .= " ORDER BY";
        $sql .= " CLM.colorder";
        $para_recs = _select($sql);

        if(_count($para_recs) != _count($_params)){
            die("SP Para count unmatch!!");
        }

        $sp = mssql_init($_sp_name); // ストアドプロシージャ名

        for($i=0;$i<_count($para_recs);$i++){
            if($para_recs[$i]['para_inout']=="inout"){
                $inout =true;
            }else{
                $inout =false;
            }

            if($_params[$i]===null){
                $_null = true;
            }else{
                $_null = false;
            }
            switch($para_recs[$i]['para_type']){
                case 'bigint' :
                case 'int' :
                    mssql_bind($sp, $para_recs[$i]['para_name'], $_params[$i], SQLINT4, $inout, $_null);
                    break;
                case 'smallint' :
                    mssql_bind($sp, $para_recs[$i]['para_name'], $_params[$i], SQLINT2, $inout, $_null);
                    break;
                case 'tinyint' :
                    mssql_bind($sp, $para_recs[$i]['para_name'], $_params[$i], SQLINT1, $inout, $_null);
                    break;
                case 'bit' :
                    mssql_bind($sp, $para_recs[$i]['para_name'], $_params[$i], SQLBIT, $inout, $_null);
                    break;
                case 'char' :
                case 'nchar' :
                    mssql_bind($sp, $para_recs[$i]['para_name'], $_params[$i], SQLCHAR, $inout, $_null, $para_recs[$i]['para_len']);
                    break;
                case 'varchar' :
                case 'nvarchar' :
                    mssql_bind($sp, $para_recs[$i]['para_name'], $_params[$i], SQLVARCHAR, $inout, $_null, $para_recs[$i]['para_len']);
                    break;
                case 'text' :
                case 'ntext' :
                    mssql_bind($sp, $para_recs[$i]['para_name'], $_params[$i], SQLTEXT, $inout, $_null);
                    break;
                case 'decimal' :
                case 'numeric' :
                case 'money' :
                case 'float' :
                case 'real' :
                    mssql_bind($sp, $para_recs[$i]['para_name'], $_params[$i], SQLFLT8, $inout, $_null);
                    break;
                case 'smallmoney' :
                    mssql_bind($sp, $para_recs[$i]['para_name'], $_params[$i], SQLFLT4, $inout, $_null);
                    break;
                case 'datetime' :
                case 'smalldatetime' :
                case 'timestamp' :
                    mssql_bind($sp, $para_recs[$i]['para_name'], $_params[$i], SQLVARCHAR, $inout, $_null);
                    break;
            }
        }
        mssql_execute($sp);

    }
function _mssql_bind($a,$name,$val,$type,$inout,$null,$len=-1){
    echo $name.",".$val.",".$type.",".$inout.",".$null.",".$len."<br>";
}
    //---------------------------------------------------------
    // SQL関数３桁カンマ区切りのラップ : _commma3keta
    //        引数：フィールド文字列
    //        戻値：関数化した文字列
    //---------------------------------------------------------
    function _comma3keta($_fld){
        $_ret_str = "FORMAT(" . $_fld . ",0)";
        return( $_ret_str );
    }


    //---------------------------------------------------------
    // Base64データ作成 : _file2Base64
    //        引数：ファイルデータ
    //        　　　ファイルサイズ
    //        戻値：Base64エンコードデータ
    //---------------------------------------------------------
    function _file2Base64( $_file, $_file_size){
        $_FH = fopen($_file,"rb");
        $_bin_data = fread($_FH,$_file_size);
        fclose($_FH);
        $_bin_data = base64_encode($_bin_data);
        return( $_bin_data );
    }

    //---------------------------------------------------------
    // 曜日文字取得 : _getYoubi
    //        引数：対象日（yyyy/mm/dd）か（yyyy-mm-dd）
    //        戻値：曜日文字(漢字一文字)
    //---------------------------------------------------------
    //function _getYoubi( &$_str_date){
    //2008/07/29 Mod
    function _getYoubi( $_str_date){
        $_str_date = str_replace("-","/",$_str_date );//2014/08/21 Add
        $_array_date = explode("/",$_str_date );
        $_ans_date  = mktime (0,0,0
                                ,intval($_array_date[1])
                                ,intval($_array_date[2])
                                ,intval($_array_date[0]));
        $_weekday = date("l",$_ans_date);
        switch( $_weekday ){
            case "Monday": $_retweekday = "月"; break;
            case "Tuesday": $_retweekday = "火"; break;
            case "Wednesday": $_retweekday = "水"; break;
            case "Thursday": $_retweekday = "木"; break;
            case "Friday": $_retweekday = "金"; break;
            case "Saturday": $_retweekday = "土"; break;
            case "Sunday": $_retweekday = "日"; break;
        }
        return( $_retweekday );
    }

    //和暦変換用の関数
    // 引数：19910202 or 1991/02/02 or 1991-02-02 の形式のみ変換します。
    //変換できれば：昭和43年4月12日
    //変換でなければ：空文字
    function _seireki2wareki($ymd){
        if(strlen($ymd)==8){
            $y = substr($ymd,0,4);
            $m = substr($ymd,4,2);
            $d = substr($ymd,6,2);
        }elseif(strlen($ymd)==10){
            $y = substr($ymd,0,4);
            $m = substr($ymd,5,2);
            $d = substr($ymd,8,2);
        }else{
            return "";
        }

        $ymd = sprintf("%04d%02d%02d", $y, $m, $d);
        if ($ymd <= "19120729") {
            $gg = "明治";
            $yy = $y - 1867;
        } elseif ($ymd >= "19120730" && $ymd <= "19261224") {
            $gg = "大正";
            $yy = $y - 1911;
        } elseif ($ymd >= "19261225" && $ymd <= "19890107") {
            $gg = "昭和";
            $yy = $y - 1925;
        } elseif ($ymd >= "19890108" && $ymd <= "20190430") {
            $gg = "平成";
            $yy = $y - 1988;
        } elseif ($ymd >= "20190501") {
            $gg = "令和";
            $yy = $y - 2018;
        }

        if($yy==1){
            $yy = "元";
        }
        $wareki = $gg.$yy."年".intval($m)."月".intval($d)."日";

        return $wareki;
    }

    //西暦変換用の関数
    // 引数：元号yymmdd の形式のみ変換します。
    //変換できれば：1968/04/05
    //変換でなければ：空文字
    function _wareki2seireki($gymd){
        if(strlen($gymd)==10){
            $g = substr($gymd,0,4);
            $y = substr($gymd,4,2);
            $m = substr($gymd,6,2);
            $d = substr($gymd,8,2);
        }else{
            return "";
        }
        if(_seisuuCheck($y . $m . $d, '')==false){
            return "";
        }

        if($g=="明治"){
            $yyyy = $y + 1867;
            $yyyymmdd = sprintf("%04d%02d%02d", $yyyy, $m, $d);
            if ($yyyymmdd <= "19120729") {
                $yyyymmdd = sprintf("%04d/%02d/%02d", $yyyy, $m, $d);
                if(_dateCheck($yyyymmdd,'')==false){
                    return "";
                }else{
                    return $yyyymmdd;
                }
            }else{
                return "";
            }
        }elseif($g=="大正"){
            $yyyy = $y + 1911;
            $yyyymmdd = sprintf("%04d%02d%02d", $yyyy, $m, $d);
            if ($yyyymmdd >= "19120730" && $yyyymmdd <= "19261224") {
                $yyyymmdd = sprintf("%04d/%02d/%02d", $yyyy, $m, $d);
                if(_dateCheck($yyyymmdd,'')==false){
                    return "";
                }else{
                    return $yyyymmdd;
                }
            }else{
                return "";
            }
        }elseif($g=="昭和"){
            $yyyy = $y + 1925;
            $yyyymmdd = sprintf("%04d%02d%02d", $yyyy, $m, $d);
            if ($yyyymmdd >= "19261225" && $yyyymmdd <= "19890107") {
                $yyyymmdd = sprintf("%04d/%02d/%02d", $yyyy, $m, $d);
                if(_dateCheck($yyyymmdd,'')==false){
                    return "";
                }else{
                    return $yyyymmdd;
                }
            }else{
                return "";
            }
        }elseif($g=="平成"){
            $yyyy = $y + 1988;
            $yyyymmdd = sprintf("%04d%02d%02d", $yyyy, $m, $d);

            if ($yyyymmdd >= "19890108") {
                $yyyymmdd = sprintf("%04d/%02d/%02d", $yyyy, $m, $d);
                if(_dateCheck($yyyymmdd,'')==false){
                    return "";
                }else{
                    return $yyyymmdd;
                }
            }else{
                return "";
            }
        }elseif($g=="令和"){
            $yyyy = $y + 2018;
            $yyyymmdd = sprintf("%04d%02d%02d", $yyyy, $m, $d);

            if ($yyyymmdd >= "20190501") {
                $yyyymmdd = sprintf("%04d/%02d/%02d", $yyyy, $m, $d);
                if(_dateCheck($yyyymmdd,'')==false){
                    return "";
                }else{
                    return $yyyymmdd;
                }
            }else{
                return "";
            }
        }
        return "";
    }

    //---------------------------------------------------------
    // Smartyコンパイル制御 ：_smartyDisplay
    //        引数：Smartyインスタンス
    //              テンプレートファイル
    //        戻値：なし
    //---------------------------------------------------------
    function _smartyDisplay(&$_smarty,$_template){
        $_buff = _smartyFetch($_smarty,$_template);
        echo $_buff;
    }
    //---------------------------------------------------------
    // Smartyコンパイル制御 ：_smartyFetch
    //        引数：Smartyインスタンス
    //              テンプレートファイル
    //        戻値：コンパイル結果
    //---------------------------------------------------------
    function _smartyFetch(&$_smarty,$_template){

        //2018/08/01 Del
        // $_smarty->register_prefilter('_smartyPreFilter');
        // $_smarty->register_postfilter('_smartyPostFilter');

        //return $_smarty->fetch($_template);
        //2007/12/05 Mod
        $_buff = $_smarty->fetch($_template);

        return $_buff;
    }
    //---------------------------------------------------------
    // Bladeコンパイル制御 ：_bladeDisplay
    //        引数：UserBladeインスタンス
    //              テンプレート名
    //        戻値：なし
    //---------------------------------------------------------
    function _bladeDisplay(&$_blade, $_template){
        echo _bladeFetch($_blade, $_template);
    }
    //---------------------------------------------------------
    // Bladeコンパイル制御 ：_bladeFetch
    //        引数：UserBladeインスタンス
    //              テンプレート名
    //        戻値：コンパイル結果
    //---------------------------------------------------------
    function _bladeFetch(&$_blade, $_template){
        return $_blade->fetch($_template);
    }

//2018/08/01 Del
//     //function _smartyPreFilter($_buff, &$_smarty)
//     //2017/03/10 Mod
//     function _smartyPreFilter($_buff, $_smarty)
//     {
//         global $_internal_encoding;
//         global $_USE_DB_ENGINE; //2008/09/10 Add

//         //2008/09/10 Add ------------ Strat -------------
//         if($_db_engine==""){
//             if($_USE_DB_ENGINE=="") {
//                 $_USE_DB_ENGINE="PostgreSQL";
//             }
//             $_db_engine = $_USE_DB_ENGINE;
//         }
//         //2008/09/10 Add ------------ End -------------

//         //MSSQL Server
//         //base64_decode
// #         if($_db_engine=="sqlserver"){ //2008/09/10 Add
// #2014/08/11 Mod
//         if(strtolower($_db_engine)=="sqlserver"){ //2008/09/10 Add

//             while(preg_match('/\$([^\[\{\}]*)\[([^\]]+)\]/is',$_buff,$matches,PREG_OFFSET_CAPTURE) > 0){
//                 $_kanji_mae = $matches[1][0];
//                 $_kanji     = $matches[2][0];

//                 $_enc = base64_encode($_kanji);
//                 $_enc = str_replace('/','_slashe_',$_enc);
//                 $_enc = str_replace('+','_plus_',$_enc);
//                 $_enc = str_replace('=','_equal_',$_enc);
//                 $_enc = "__ENC__" . $_enc;

//                 $_buff = preg_replace('/\$[^\[\{\}]*\[[^\]]+\]/is',
//                                 '$'.$_kanji_mae.$_enc,
//                                 $_buff,1);
//             }

//             while(preg_match('/name ?= ?"([^"]+)"/is',$_buff,$matches,PREG_OFFSET_CAPTURE) > 0){
//                 $_kanji = $matches[1][0];
//                 if(!preg_match('/^[a-zA-Z\d_\-\{\}\.\[\]\$\!\%\&\(\)\=\|\+]*$/', $_kanji)){
//                     $_enc = base64_encode($_kanji);
//                     $_enc = str_replace('/','_slashe_',$_enc);
//                     $_enc = str_replace('+','_plus_',$_enc);
//                     $_enc = str_replace('=','_equal_',$_enc);
//                     $_enc = "__kanji__".$_enc;
//                 }else{
//                     $_enc = $_kanji;
//                 }
//                 $_buff = preg_replace('/name ?= ?"([^"]+)"/is',
//                                 'name     ="'.$_enc.'"',
//                                 $_buff,1);
//             }
//         } //2008/09/10 Add


//         if($_internal_encoding==""){
//             return mb_convert_encoding($_buff,"EUC-JP",_ENCODING_SRC);
//         }else{
//             return $_buff;
//         }
//     }

//2018/08/01 Del
//     //function _smartyPostFilter($_buff, &$_smarty)
//     //2017/03/10 Mod
//     function _smartyPostFilter($_buff, $_smarty)
//     {
//         global $_internal_encoding;
//         if($_internal_encoding==""){
//             $_buff = mb_convert_encoding($_buff,_ENCODING_SRC,"EUC-JP");
//         }

//         return $_buff;
//     }

    //---------------------------------------------------------
    // Smarty一括assign ：_setView
    //        引数：BladeOneインスタンス
    //              出力配列
    //---------------------------------------------------------
    function _setAssign(&$_blade, $_array){
        if( is_array( $_array ) ){
            //while(list($key, $val) = each($_array)){
            //2009/03/02 mod
            foreach($_array as $key => $val){
                if($key != 'rand' && $key != 'ss'){
                    $_blade->assign( $key, $val);
                }
            }
        }
    }


    //---------------------------------------------------------
    // メール送信 : _sendMail
    //       引数 : 差出・宛先・件名・本文
    //---------------------------------------------------------
    function _sendMail($_from, $_to, $_subject, $_body){
        //2011/07/05 Add --- Strat ---
        $_body = str_replace("\r\n","\n",$_body);
        $_body = str_replace("\r","\n",$_body);
        //2011/07/05 Add --- End ---

        // ヘッダ
        $_head  = "";
        $_head .= "From:" . $_from . "\n";
        $_head .= "Reply-To:" . $_from . "\n";
        // $_head .= "X-Mailer: PHP/" . phpversion() . "\n";
        $_head .= "X-Mailer: PHPMailer 5.2.23 (https://github.com/PHPMailer/PHPMailer)\n";    //X-Mailer偽装
        $_head .= "MIME-version: 1.0\n";
        // $_head .= "Content-Type: text/plain; charset=ISO-2022-JP\n";
        // $_head .= "Content-Transfer-Encoding: 7bit";
        $_head .= "Content-Type: text/plain; charset=UTF-8\n";
        $_head .= "Content-Transfer-Encoding: 8bit\n";

        //mb_language("Japanese");
        //$_ret = mb_send_mail($_to, $_subject, $_body, $_head);

        $_conv_subject = _mime_head($_subject);
        // $_conv_body = mb_convert_encoding($_body,"JIS",_ENCODING_SRC);
        $_conv_body = mb_convert_encoding($_body,"UTF-8",_ENCODING_SRC);
        $_ret = mail($_to, $_conv_subject, $_conv_body, $_head,'-f'.$_from);

        return $_ret;
    }

    //---------------------------------------------------------
    // メール送信 : _sendMail
    //       引数 : 差出Adr・差出名・宛先Adr・宛先名・件名・本文・添付ファイル
    //
    //         添付ファイルはなくても必ず配列で！
    //         $attaches[n]['data'] = base64_encodeされたファイルデータ
    //         $attaches[n]['filename'] = パスなしファイル名
    //         $attaches[n]['type'] = Contentタイプ　例）image/gif, application/vnd.ms-excelなど
    //---------------------------------------------------------
    // function _sendMailEx($_from_adr,$_from_name, $_to_adr, $_to_name, $_subject, $_body, $attaches){
    //2021/05/27 Mod
    function _sendMailEx($_from_adr,$_from_name, $_to_adr, $_to_name, $_subject, $_body, $attaches, $addHeader=array() ){
        global $_KANKYOU_STR;

        if($_KANKYOU_STR=="local" || substr($_KANKYOU_STR,0,4)=="test"){
            $_subject = "【テスト】".$_subject;
        }

        //2011/07/05 Add --- Strat ---
        $_body = str_replace("\r\n","\n",$_body);
        $_body = str_replace("\r","\n",$_body);
        //2011/07/05 Add --- End ---

        $boundary = md5(uniqid(rand())); //バウンダリー文字

        // ヘッダ
        $_head  = "";
        if($_from_name==""){
            $_head .= "From: " . $_from_adr . "\n";
        }else{
            $_head .= "From: "._mime_head($_from_name)." <" . $_from_adr . ">\n";
        }
        $_head .= "Reply-To:" . $_from_adr . "\n";

        foreach ($addHeader as $key => $value) {
            if(strtoupper($key) != "RETURN-PATH"){
                $_head .= $key.":" . $value . "\n";
            }
        }

        // $_head .= "X-Mailer: PHP/" . phpversion() . "\n";
        $_head .= "X-Mailer: PHPMailer 5.2.23 (https://github.com/PHPMailer/PHPMailer)\n";    //X-Mailer偽装
        $_head .= "MIME-version: 1.0\n";
        if(_count($attaches)>0){
             //添付ファイルがあれば
            $_head .= "Content-Type: multipart/mixed;\n";
            $_head .= "\tboundary=\"$boundary\"\n";

            $msg .= "This is a multi-part message in MIME format.\n\n";
            $msg .= "--$boundary\n";
            // $msg .= "Content-Type: text/plain; charset=ISO-2022-JP\n";
            // $msg .= "Content-Transfer-Encoding: 7bit\n\n";
            $msg .= "Content-Type: text/plain; charset=UTF-8\n";
            $msg .= "Content-Transfer-Encoding: 8bit\n\n";

        }else{
            // $_head .= "Content-Type: text/plain; charset=ISO-2022-JP\n";
            // $_head .= "Content-Transfer-Encoding: 7bit\n";
            $_head .= "Content-Type: text/plain; charset=UTF-8\n";
            $_head .= "Content-Transfer-Encoding: 8bit\n";
        }

        //mb_language("Japanese");
        //$_ret = mb_send_mail($_to, $_subject, $_body, $_head);

        $_conv_subject = _mime_head($_subject);
        // $_conv_body = mb_convert_encoding($_body,"JIS",_ENCODING_SRC);
        if(_ENCODING_SRC!="UTF-8"){
            $_conv_body = mb_convert_encoding($_body,"UTF-8",_ENCODING_SRC);
        }else{
            $_conv_body = $_body;
        }

        $msg .= $_conv_body;
        $msg .= "\n--$boundary--";

        if(_count($attaches)>0){
             //添付ファイルがあれば
            for($i=0;$i<_count($attaches);$i++){
                $f_encoded = chunk_split($attaches[$i]['data']); //エンコードして分割
                $msg .= "\n\n--$boundary\n";
                $msg .= "Content-Type: " . $attaches[$i]['type'] . ";\n";
                $msg .= " name=\""._mime_head($attaches[$i]['filename'])."\"\n";
                $msg .= "Content-Transfer-Encoding: base64\n";
                $msg .= "Content-Disposition: attachment;\n";
                $msg .= " filename=\""._mime_head($attaches[$i]['filename'])."\"\n\n";
                $msg .= "$f_encoded\n";
            }
            $msg .= "--$boundary--";
        }

        if($_to_name==""){
            $_to_str = $_to_adr;
        }else{
            $_to_str = _mime_head($_to_name)." <" . $_to_adr . ">";
        }

        $return_path = $_from_adr;
        foreach ($addHeader as $key => $value) {
            if(strtoupper($key) == "RETURN-PATH"){
                $return_path = $value;
                break;
            }
        }

        // $_ret = mail($_to_str, $_conv_subject, $msg, $_head,'-f '.$_from_adr);
        $_ret = mail($_to_str, $_conv_subject, $msg, $_head,'-f '.$return_path);

        return $_ret;
    }

    function _sendMailHtml($_from_adr,$_from_name, $_to_adr, $_to_name, $_subject, $_body, $attaches, $addHeader=array() ){
        global $_KANKYOU_STR;

        if($_KANKYOU_STR=="local" || substr($_KANKYOU_STR,0,4)=="test"){
            $_subject = "【テスト】".$_subject;
        }

        //2011/07/05 Add --- Strat ---
        // $_body = str_replace("\r\n","\n",$_body);
        // $_body = str_replace("\r","\n",$_body);
        //2011/07/05 Add --- End ---

        $boundary = md5(uniqid(rand())); //バウンダリー文字

        // ヘッダ
        $_head  = "";
        if($_from_name==""){
            $_head .= "From: " . $_from_adr . "\n";
        }else{
            $_head .= "From: "._mime_head($_from_name)." <" . $_from_adr . ">\n";
        }
        $_head .= "Reply-To:" . $_from_adr . "\n";

        foreach ($addHeader as $key => $value) {
            if(strtoupper($key) != "RETURN-PATH"){
                $_head .= $key.":" . $value . "\n";
            }
        }

        // $_head .= "X-Mailer: PHP/" . phpversion() . "\n";
        $_head .= "X-Mailer: PHPMailer 5.2.23 (https://github.com/PHPMailer/PHPMailer)\n";    //X-Mailer偽装
        $_head .= "MIME-version: 1.0\n";

        $_head .= "Content-Type: multipart/mixed;\n";
        $_head .= "\tboundary=\"$boundary\"\n";

        $msg .= "This is a multi-part message in MIME format.\n\n";
        $msg .= "--$boundary\n";
        // $msg .= "Content-Type: text/plain; charset=ISO-2022-JP\n";
        // $msg .= "Content-Transfer-Encoding: 7bit\n\n";
        $msg .= "Content-Type: text/html; charset=UTF-8\n";
        $msg .= "Content-Transfer-Encoding: 8bit\n\n";

        //mb_language("Japanese");
        //$_ret = mb_send_mail($_to, $_subject, $_body, $_head);

        $_conv_subject = _mime_head($_subject);
        // $_conv_body = mb_convert_encoding($_body,"JIS",_ENCODING_SRC);
        if(_ENCODING_SRC!="UTF-8"){
            $_conv_body = mb_convert_encoding($_body,"UTF-8",_ENCODING_SRC);
        }else{
            $_conv_body = $_body;
        }

        $msg .= $_conv_body;
        $msg .= "\n--$boundary--";


        if(_count($attaches)>0){
             //添付ファイルがあれば
            for($i=0;$i<_count($attaches);$i++){
                $f_encoded = chunk_split($attaches[$i]['data']); //エンコードして分割
                $msg .= "\n\n--$boundary\n";
                $msg .= "Content-Type: " . $attaches[$i]['type'] . ";\n";
                $msg .= " name=\""._mime_head($attaches[$i]['filename'])."\"\n";
                $msg .= "Content-Transfer-Encoding: base64\n";
                $msg .= "Content-Disposition: attachment;\n";
                $msg .= " filename=\""._mime_head($attaches[$i]['filename'])."\"\n\n";
                $msg .= "$f_encoded\n";
            }
            $msg .= "--$boundary--";
        }

        if($_to_name==""){
            $_to_str = $_to_adr;
        }else{
            $_to_str = _mime_head($_to_name)." <" . $_to_adr . ">";
        }

        $return_path = $_from_adr;
        foreach ($addHeader as $key => $value) {
            if(strtoupper($key) == "RETURN-PATH"){
                $return_path = $value;
                break;
            }
        }

        // $_ret = mail($_to_str, $_conv_subject, $msg, $_head,'-f '.$_from_adr);
        $_ret = mail($_to_str, $_conv_subject, $msg, $_head,'-f '.$return_path);

        return $_ret;
    }

    //2018/08/28 Add
    //---------------------------------------------------------
    // メール送信 : _sendMailByQdmail
    //   SMTP送信のメール送信関数
    // この関数を利用するには、以下のライブラリ取り込みが必要
    // require_once('../lib/qdmail/qdmail.php');
    // require_once('../lib/qdmail/qdsmtp.php');
    // ＜添付ファイルの指定方法＞
    //          $attaches = array();
    //          $attaches[] = array( 'ファイルパス' , 'ファイル名' );
    //          $attaches[] = array( _SYSTEM_ROOT_DIR.'/upfile/new_tmp/aaa.txt' , 'aaaa.txt' );
    //---------------------------------------------------------
    function _sendMailByQdmail($fromAddr,$fromName,$toAddr,$toName,$subject,$body,$attaches){
        // SMTPでのメール送信関数
        // $fromAddr：送信元メールアドレス
        // $fromName：送信元名（日本語OK）
        // $to：送信先メールアドレス
        // $toName：送信先名（日本語OK）
        // $subject：件名（日本語OK）
        // $body：本文（日本語OK）

        //SMTP送信
        $mail = new Qdmail();
        $mail -> smtp(true);
        $param = array(
            'host'=>_SMTP_HOST,
            'port'=> 587 ,
            'from'=>$fromAddr,
            'protocol'=>'SMTP_AUTH',
            'user'=>_SMTP_UID,
            'pass' => _SMTP_PASS,
        );
        $mail ->smtpServer($param);
        if($toName!=""){
            $mail ->to($toAddr,$toName);
        }else{
            $mail ->to($toAddr);
        }

        $mail ->subject($subject);
        if($fromName!=""){
            $mail ->from($fromAddr,$fromName);
        }else{
            $mail ->from($fromAddr);
        }
        $mail ->text($body);

        //$mail -> timeZone( '+0900' );  // 日本時間の場合
        $mail -> addHeader('Date',date("r").'(JST)');


        //添付
        if(_count($attaches) > 0){
            $mail -> attach ( $attaches );
        }

        $return_flag = $mail ->send();
        return $return_flag;
    }

    //---------------------------------------------------------
    // MIMEヘッド作成 : _mime_head
    //        引数：元文字列
    //        戻値：変換後文字列
    //---------------------------------------------------------
    function _mime_head($usr_str){            //MIMEエンコード
        //$usr_str = stripslashes($usr_str);//\は取る

        //JISに変換
        // $enc = mb_convert_encoding($usr_str,"JIS",_ENCODING_SRC);
        $enc = mb_convert_encoding($usr_str,"UTF-8",_ENCODING_SRC); // 2016/05/23 Mod

        // return "=?ISO-2022-JP?B?" . base64_encode($enc) . "?=";    //Bヘッダ＋エンコード
        return "=?UTF-8?B?" . base64_encode($enc) . "?=";    //Bヘッダ＋エンコード   // 2016/05/23 Mod

    }

    //---------------------------------------------------------
    // 実行中のPHPファイルのディレクトリパス取得: _getPhpDirPath
    //   ※最後/なし
    //---------------------------------------------------------
    function _getPhpDirPath(){
//2010/09/21 mod -----------
#         $warr = split('/',$_SERVER['SCRIPT_FILENAME']);
        $warr = explode('/',$_SERVER['SCRIPT_FILENAME']);
//2010/09/21 mod -----------

        $dmy = array_pop( $warr );
        $_php_dir_path = join('/',$warr);
        return $_php_dir_path;
    }

    //---------------------------------------------------------
    // 配列だけJOINの結果を返す: _join
    //   ※最後/なし
    //---------------------------------------------------------
    function _join($key, $array){
        if(is_array($array)){
            $result = join($key, $array);
        }else{
            $result = '';
        }

        return $result;
    }

    //====================================================================
    // 使い方
    //     _getHolidayName("日付文字列");
    //
    //          日付文字列には「-」や「/」で区切られた年月日形式の文字列が
    //          指定できます。
    //          例）2006-01-01, 2006-2-3, 1999/08/09, 1999/4/5 .... など
    //     指定した日付が不正な場合や祝日出ない場合は「空文字」が
    //                             祝日であった場合は「祝日名(漢字)」が
    //                             帰ります。
    //====================================================================

    function _getHolidayName($_arg_chk_date){
        $_chk_date = trim($_arg_chk_date);
        if($_chk_date=="") return "";

        $_chk_date = str_replace("-","/",$_chk_date);

        if( _dateCheck($_chk_date,'')==false ){
            return "";
        }

        $wArr = explode("/",$_chk_date);

        $holi_list = _getHolidayList($wArr[0]);
        foreach ($holi_list as $date => $holiName) {
            if($date==$_chk_date){
                return $holiName;
            }
        }
        return "";
    }

    function _getHolidayNameOLD($_arg_chk_date)
    {
        define("MONDAY","2");

        $_chk_date = trim($_arg_chk_date);
        if($_chk_date=="") return "";

        if( ! ereg("^[1-2][0-9][0-9][0-9]/[0-1]?[0-9]/[0-3]?[0-9]$",$_chk_date) ){
            if( ! ereg("^[1-2][0-9][0-9][0-9]-[0-1]?[0-9]-[0-3]?[0-9]$",$_chk_date) ){
                return "";
            }else{
                $out_data = explode("-",$_chk_date );
            }
        }else{
            $out_data = explode("/",$_chk_date );
        }


        if ( !checkdate($out_data[1], $out_data[2], $out_data[0])) {
            return "";
        }

        $MyDate = mktime(0,0,0,intval($out_data[1]),intval($out_data[2]),intval($out_data[0]));

        $cstImplementHoliday = mktime(0,0,0,4,12,1973); // 振替休日施行

        $HolidayName = prvHolidayChk($MyDate);
        if ($HolidayName == "") {
          if (_getWeekday($MyDate) == MONDAY) {
              // 月曜以外は振替休日判定不要
              // 5/6(火,水)の判定はprvHolidayChkで処理済
              // 5/6(月)はここで判定する
              if ($MyDate >= $cstImplementHoliday) {
                  $YesterDay = mktime(0,0,0,_getMonth($MyDate),
                                          (_getDay($MyDate) - 1),_getYear($MyDate));
                  $HolidayName = prvHolidayChk($YesterDay);
                  if ($HolidayName != "") {
                      $Result = "振替休日";
                  } else {
                      $Result = "";
                  }
              } else {
                  $Result = "";
              }
          } else {
              $Result = "";
          }
        } else {
          $Result = $HolidayName;
        }

      return $Result;
    }

    //========================================================================

    //***********************************************************************
    // Google カレンダーを利用したバージョン Start 2013/01/29 Add
    // Google カレンダーを利用したバージョン 2016/71/04 Mod
    //***********************************************************************
    function _getHolidayList($arg_yyyy){
        if( isset($_SESSION[_PROJECT_NAME]['holiday_list'][$arg_yyyy]) ){
            return $_SESSION[_PROJECT_NAME]['holiday_list'][$arg_yyyy];
        }
        //PHP5.2以上でないと利用できません
        $st_date = date("Y-m-d",strtotime($arg_yyyy."-01-01"))."T00%3A00%3A00.000Z";
        $ed_date = date("Y-m-d",strtotime($arg_yyyy."-12-31"))."T00%3A00%3A00.000Z";

        // $api_key = 'AIzaSyAP2ae1tvhpyU1y9UkedcyYeUMk3mGn9Uc';
        $api_key = _GCAL_API_KEY;
        $holidays_id = 'japanese__ja@holiday.calendar.google.com';
        $holidays_url = sprintf(
            'https://www.googleapis.com/calendar/v3/calendars/%s/events?'.
            'key=%s&timeMin=%s&timeMax=%s&maxResults=%d&orderBy=startTime&singleEvents=true',
            $holidays_id,
            $api_key,
            $st_date,
            $ed_date,
            50
        );

        if( $results = file_get_contents($holidays_url, true))
        {
            $results = json_decode($results);
            $holidays = array();
            foreach($results->items as $item)
            {
                $date = strtotime((string) $item->start->date);
                $title = (string) $item->summary;
                if( strtoupper(_ENCODING_SRC) != "UTF8" && strtoupper(_ENCODING_SRC) != "UTF-8" ){
                    $title = mb_convert_encoding($title,_ENCODING_SRC,"UTF8");
                }
                $holidays[date('Y/m/d', $date)] = $title;
            }
            ksort($holidays);
        }
        $_SESSION[_PROJECT_NAME]['holiday_list'][$arg_yyyy] = $holidays;
        return $holidays;
    }

    function _getHolidayListByDB($arg_yyyy){
        global $conn;


        // CREATE TABLE m_holiday
        // (
        //   holi_yyyy text NOT NULL,
        //   holi_ymd text NOT NULL,
        //   holi_name text,
        //   CONSTRAINT m_holiday_pkey PRIMARY KEY (holi_yyyy,holi_ymd)
        // )
        // WITHOUT OIDS;

        $cur_recs = _select("select * from m_holiday where holi_yyyy='"._as($arg_yyyy)."' order by holi_ymd");
        if(_count($cur_recs) > 0){
            $holidays = array();
            for($i=0;$i<_count($cur_recs);$i++){
                $holidays[ $cur_recs[$i]['holi_ymd'] ] = $cur_recs[$i]['holi_name'];
            }
        }else{

            //PHP5.2以上でないと利用できません
            $st_date = date("Y-m-d",strtotime($arg_yyyy."-01-01"));
            $ed_date = date("Y-m-d",strtotime($arg_yyyy."-12-31"));

            $holidays_url = sprintf(
                    'http://www.google.com/calendar/feeds/%s/public/full-noattendees?start-min=%s&start-max=%s&max-results=%d&alt=json' ,
                    'outid3el0qkcrsuf89fltf7a4qbacgt9@import.calendar.google.com' , // 'japanese@holiday.calendar.google.com' ,
                    $st_date ,  // 取得開始日
                    $ed_date ,  // 取得終了日
                    50              // 最大取得数
                    );

            $holidays = array();

            if ( $results = file_get_contents($holidays_url) ) {
                   $results = json_decode($results, true);
                    foreach ($results['feed']['entry'] as $val ) {
                            $date  = $val['gd$when'][0]['startTime'];
                            $date = date("Y/m/d",strtotime($date));
                            $title = $val['title']['$t'];
                            $arr_title = explode("/",$title);
                            $title = trim($arr_title[0]);
                            if( strtoupper(_ENCODING_SRC) != "UTF8" && strtoupper(_ENCODING_SRC) != "UTF-8" ){
                                $title = mb_convert_encoding($title,_ENCODING_SRC,"UTF8");
                            }
                            $holidays[$date] = $title;
                    }
                    ksort($holidays);
            }

            _query( $conn, "begin" );
            foreach($holidays as $_holi_ymd => $_holi_name){
                $array = array();
                $array['holi_yyyy'] = "'" . _as($arg_yyyy) ."'";
                $array['holi_ymd'] = "'" . _as($_holi_ymd) ."'";
                $array['holi_name'] = "'" . _as($_holi_name) ."'";
                _insert('m_holiday',$array);
            }
            _query( $conn, "commit" );
        }

        return $holidays;
    }

    //***********************************************************************
    // Google カレンダーを利用したバージョン Start 2013/01/29 End
    //***********************************************************************


    function prvHolidayChk($MyDate)
    {
      define("MONDAY","2");
      define("TUESDAY","3");
      define("WEDNESDAY","4");

      //$cstImplementTheLawOfHoliday = mktime(0,0,0,7,20,1948);  // 祝日法施行
      $cstImplementTheLawOfHoliday = "1948-07-20";  // 祝日法施行

      $cstShowaTaiso = mktime(0,0,0,2,24,1989);                // 昭和天皇大喪の礼

      //$cstAkihitoKekkon = mktime(0,0,0,4,10,1959);             // 明仁親王の結婚の儀
      $cstAkihitoKekkon = "1959-04-10";             // 明仁親王の結婚の儀

      $cstNorihitoKekkon = mktime(0,0,0,6,9,1993);             // 徳仁親王の結婚の儀
      $cstSokuireiseiden = mktime(0,0,0,11,12,1990);           // 即位礼正殿の儀

      $MyYear = _getYear($MyDate);
      $MyMonth = _getMonth($MyDate);
      $MyDay = _getDay($MyDate);


      $chkMyDate = date("Y-m-d",$MyDate);
      if ($chkMyDate < $cstImplementTheLawOfHoliday)
          return "";    // 祝日法施行以前
      else;

      $Result = "";
      switch ($MyMonth) {
      // １月 //
      case 1:
          if ($MyDay == 1) {
              $Result = "元日";
          } else {
              if ($MyYear >= 2000) {
                  $strNumberOfWeek =
                            (floor(($MyDay - 1) / 7) + 1) . _getWeekday($MyDate);
                  if ($strNumberOfWeek == "22") {    //Monday:2
                      $Result = "成人の日";
                  } else;
              } else {
                  if ($MyDay == 15) {
                      $Result = "成人の日";
                  } else;
              }
          }
          break;
      // ２月 //
      case 2:
          if ($MyDay == 11) {
              if ($MyYear >= 1967) {
                  $Result = "建国記念の日";
              } else;
          } elseif ($MyDate == $cstShowaTaiso) {
              $Result = "昭和天皇の大喪の礼";
          } else;
          break;
      // ３月 //
      case 3:
          if ($MyDay == prvDayOfSpringEquinox($MyYear)) {    // 1948～2150以外は[99]
              $Result = "春分の日";                        // が返るので､必ず≠になる
          } else;
          break;
      // ４月 //
      case 4:
          if ($MyDay == 29) {
              if ($MyYear >= 2007) {
                  $Result = "昭和の日";
              } elseif ($MyYear >= 1989) {
                  $Result = "みどりの日";
              } else {
                  $Result = "天皇誕生日";
              }
          } elseif ($chkMyDate == $cstAkihitoKekkon) {
              $Result = "皇太子明仁親王の結婚の儀";
          } else;
          break;
      // ５月 //
      case 5:
          if ($MyDay == 3) {
              $Result = "憲法記念日";
          } elseif ($MyDay == 4) {
              if ($MyYear >= 2007) {
                  $Result = "みどりの日";
              } elseif ($MyYear >= 1986) {
                  if (_getWeekday($MyDate) > MONDAY) {
                      // 5/4が日曜日は『只の日曜』､月曜日は『憲法記念日の振替休日』(～2006年)
                      $Result = "国民の休日";
                  } else;
              } else;
          } elseif ($MyDay == 5) {
              $Result = "こどもの日";
          } elseif ($MyDay == 6) {
              if ($MyYear >= 2007) {
                  if( (_getWeekday($MyDate) == TUESDAY) || (_getWeekday($MyDate) == WEDNESDAY) ){
                      $Result = "振替休日";    // [5/3,5/4が日曜]ケースのみ、ここで判定
                  } else;
              } else;
          } else;
          break;
      // ６月 //
      case 6:
          if ($MyDate == $cstNorihitoKekkon) {
              $Result = "皇太子徳仁親王の結婚の儀";
          } else;
          break;
      // ７月 //
      case 7:
          if ($MyYear >= 2003) {
              $strNumberOfWeek =
                        (floor(($MyDay - 1) / 7) + 1) . _getWeekday($MyDate);
              if ($strNumberOfWeek == "32") {    //Monday:2
                  $Result = "海の日";
              } else;
          } elseif ($MyYear >= 1996) {
              if ($MyDay == 20) {
                  $Result = "海の日";
              } else;
          } else;
          break;
      // ９月 //
      case 9:
          //第３月曜日(15～21)と秋分日(22～24)が重なる事はない
          $MyAutumnEquinox = prvDayOfAutumnEquinox($MyYear);
          if ($MyDay == $MyAutumnEquinox) {    // 1948～2150以外は[99]
              $Result = "秋分の日";            // が返るので､必ず≠になる
          } else {
              if ($MyYear >= 2003) {
                  $strNumberOfWeek =
                          (floor(($MyDay - 1) / 7) + 1) . _getWeekday($MyDate);
                  if ($strNumberOfWeek == "32") {    //Monday:2
                      $Result = "敬老の日";
                  } elseif (_getWeekday($MyDate) == TUESDAY) {
                      if ($MyDay == ($MyAutumnEquinox - 1)) {
                          $Result = "国民の休日";
                      } else;
                  } else;
              } elseif ($MyYear >= 1966) {
                  if ($MyDay == 15) {
                      $Result = "敬老の日";
                  } else;
              } else;
          }
          break;
      // １０月 //
      case 10:
          if ($MyYear >= 2000) {
              $strNumberOfWeek =
                        (floor(( $MyDay - 1) / 7) + 1) . _getWeekday($MyDate);
              if ($strNumberOfWeek == "22") {    // Monday:2
                  $Result = "体育の日";
              } else;
          } elseif ($MyYear >= 1966) {
              if ($MyDay == 10) {
                  $Result = "体育の日";
              } else;
          } else;
          break;
      // １１月 //
      case 11:
          if ($MyDay == 3) {
              $Result = "文化の日";
          } elseif ($MyDay == 23) {
              $Result = "勤労感謝の日";
          } elseif ($MyDate == cstSokuireiseiden) {
              $Result = "即位礼正殿の儀";
          } else;
          break;
      // １２月 //
      case 12:
          if ($MyDay == 23) {
              if ($MyYear >= 1989) {
                  $Result = "天皇誕生日";
              } else;
          } else;
          break;
      }
      return $Result;
    }

    //======================================================================
    //  春分/秋分日の略算式は
    //    『海上保安庁水路部 暦計算研究会編 新こよみ便利帳』
    //  で紹介されている式です。
    function prvDayOfSpringEquinox($MyYear)
    {
      if ($MyYear <= 1947)
          $Result = 99; //祝日法施行前
      elseif ($MyYear <= 1979)
          // floor 関数は[VBAのInt関数]に相当
          $Result = floor(20.8357 +
                (0.242194 * ($MyYear - 1980)) - floor(($MyYear - 1980) / 4));
      elseif ($MyYear <= 2099)
          $Result = floor(20.8431 +
                (0.242194 * ($MyYear - 1980)) - floor(($MyYear - 1980) / 4));
      elseif ($MyYear <= 2150)
          $Result = floor(21.851 +
                (0.242194 * ($MyYear - 1980)) - floor(($MyYear - 1980) / 4));
      else
          $Result = 99; //2151年以降は略算式が無いので不明

      return $Result;
    }

    //========================================================================
    function prvDayOfAutumnEquinox($MyYear)
    {
      if ($MyYear <= 1947)
          $Result = 99; //祝日法施行前
      elseif ($MyYear <= 1979)
          // floor 関数は[VBAのInt関数]に相当
          $Result = floor(23.2588 +
                (0.242194 * ($MyYear - 1980)) - floor(($MyYear - 1980) / 4));
      elseif ($MyYear <= 2099)
          $Result = floor(23.2488 +
                (0.242194 * ($MyYear - 1980)) - floor(($MyYear - 1980) / 4));
      elseif ($MyYear <= 2150)
          $Result = floor(24.2488 +
                (0.242194 * ($MyYear - 1980)) - floor(($MyYear - 1980) / 4));
      else
          $Result = 99; //2151年以降は略算式が無いので不明

      return $Result;
    }

    function _getWeekday($MyDate){
      return strftime("%w",$MyDate) + 1;  // 日(1),月(2)‥‥土(7)
    }

    function _getYear($MyDate){
      return strftime("%Y",$MyDate) - 0;  // 数値で返す
    }

    function _getMonth($MyDate){
      return strftime("%m",$MyDate) - 0;  // 数値で返す
    }

    function _getDay($MyDate){
      return strftime("%d",$MyDate) - 0;  // 数値で返す
    }


    //**********************************************************************
    //  GoogleAPI使った郵便番号から住所検索関数
    //**********************************************************************
    function _getAddressByPostUseGoogleApi($post_no){
        $post_no = str_replace("-", "", $post_no);

        $ret_arr = array();

        if(strlen($post_no)==7 and _seisuuCheck($post_no,'')==true ){

            //$buff = file_get_contents("http://maps.googleapis.com/maps/api/geocode/json?address=".$post_no."&language=ja&sensor=false");
            //2018/09/06 Mod
            $buff = file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?address=".$post_no."&language=ja&sensor=false&key="._GOOGLE_API_KEY);
            $obj = json_decode($buff);

            $addr_info_arr =  $obj->results[0]->address_components;
            $address1 = "";
            $address2 = "";
            $address3 = "";
            for ($i=0; $i < _count($addr_info_arr); $i++) {
                if($addr_info_arr[$i]->types[0]!="postal_code" && $addr_info_arr[$i]->types[0]!="country" ){
                    if($addr_info_arr[$i]->types[0]=="administrative_area_level_1"){
                            $address1 = $addr_info_arr[$i]->long_name;
                    }elseif($addr_info_arr[$i]->types[0]=="sublocality_level_1"){
                            $address3 = $addr_info_arr[$i]->long_name;
                    }else{
                        $address2 = $addr_info_arr[$i]->long_name . $address2;
                    }
                }
            }
            $ret_arr[0] = $address1;
            $ret_arr[1] = $address2;
            $ret_arr[2] = $address3;
        }

        return $ret_arr;
    }


    //---------------------------------------------------------
    // URL存在チェック
    //---------------------------------------------------------
    function url_exists($url) {
        $a_url = parse_url($url);
        if (!isset($a_url['port'])){
            $a_url['port'] = 80;
        }
        $errno = 0;
        $errstr = '';
        $timeout = 30;
        if(isset($a_url['host']) && $a_url['host']!=gethostbyname($a_url['host'])){
            $fid = fsockopen($a_url['host'], $a_url['port'], $errno, $errstr, $timeout);
            if (!$fid){
                return false;
            }
            $page = isset($a_url['path'])  ?$a_url['path']:'';
            $page .= isset($a_url['query'])?'?'.$a_url['query']:'';
            fputs($fid, 'HEAD '.$page.' HTTP/1.0'."\r\n".'Host: '.$a_url['host']."\r\n\r\n");
            $head = fread($fid, 4096);
            $head = substr($head,0,strpos($head, 'Connection: close'));
            fclose($fid);
            if (preg_match('#^HTTP/.*\s+[200|302]+\s#i', $head)) {
                return true;
            }else{
                return false;
            }
        } else {
            return false;
        }
    }

    //---------------------------------------------------------
    // 全角文字を指定バイト数でカットしてカットされた場合「...」などをつける
    //---------------------------------------------------------
    function _mulutiByteStrCut($arg_str,$limit,$arg_after_str){
        $str = mb_convert_encoding($arg_str,"SJIS-WIN",_ENCODING_SRC);
        $after_str = mb_convert_encoding($arg_after_str,"SJIS-WIN",_ENCODING_SRC);

        $han_length = $limit;
        $add_han_len = 0;
        $ret_str = "";
        for($i=0;$i<mb_strlen($str,"SJIS-WIN");$i++){
            $one = mb_substr($str, $i, 1,"SJIS-WIN");
            if(strlen($one)==1){
                $add_han_len = $add_han_len + 1;
            }else{
                $add_han_len = $add_han_len + 2;
            }
            if( $add_han_len > $han_length ){
                //$ret_str .= $after_str;
                //「…」などの長さも考慮して
                $ret_str = mb_substr($ret_str,0,mb_strlen($ret_str,"SJIS-WIN") - mb_strlen($after_str,"SJIS-WIN"),"SJIS-WIN") . $after_str;
                break;
            }
            $ret_str .= $one;
        }
        return mb_convert_encoding($ret_str,_ENCODING_SRC,"SJIS-WIN");
    }
    //2007/07/02 Add ------------------ Start -------------------------

    //---------------------------------------------------------
    //画像のみならず、すべてのファイルをアップロード
    //
    //        引数：$_id          対象管理者ID
    //        　　  $_filename    ファイル名の形式
    //        戻値：ファイルデータ['file_name']
    //        　　　　　　　　　　['img_opt']
    //---------------------------------------------------------
    function _disp_file($_dir_id, $_tagname, &$_sess){
        $_filename = $_sess[$_tagname];
        //ファイルのフォルダIDを返す
        $_sess[$_tagname . '_id'] = $_dir_id;

        // 格納先ディレクトリ
        $_fil_dir = _SYSTEM_ROOT_DIR . '/upfile/' . $_dir_id . '/';
        $_tmp_dir = _SYSTEM_ROOT_DIR . '/upfile/new_tmp/';
        // 格納先ディレクトリが無い場合は、作成する
        @mkdir($_fil_dir, 0777);
        @chmod($_fil_dir, 0777); //2009/10/07 Add

        if( _is_file($_tmp_dir . $_sess[$_tagname . '_tmp_data']) && $_sess[$_tagname . '_tmp_data']!=""){
            // ファイル有り
            //$_sess[$_tagname] = $_filename;
        }elseif( _is_file($_fil_dir . $_filename) ){
            // ファイル有り
            $_sess[$_tagname] = $_filename;
        }else{
            // ファイル無し
            return;
        }

        return;
    }

    //---------------------------------------------------------
    //画像表示 : _disp_confirmfile
    //
    //        引数：$_id          対象管理者ID
    //        　　  $_filename    ファイル名
    //        　　　$_tagname     fileタグ名
    //        　　　$_del_flg     削除フラグ
    //        戻値：ファイル情報
    //---------------------------------------------------------
    function _disp_confirmfile($_mode, $_dir_id, $_tagname, &$_sess, $_chk_ext_arr=null){

        $_filename = $_sess[$_tagname]; //com_movie
        $_del_flg = $_sess[$_tagname . "_del"]; //com_movie_del
        $_ret_err_msg = array(); //err_msg
        $_sess[$_tagname . "_id"] = $_dir_id; //com_movie_id(shop_id)

        //template directory path
        $_tmp_dir = _SYSTEM_ROOT_DIR . '/upfile/new_tmp/';

        if($_dir_id==""){
            $_dir_id = "new_tmp";
        }

        // ディレクトリが無い場合は、作成する
        @mkdir($_tmp_dir, 0777);
        @chmod($_tmp_dir, 0777); //2009/10/07 Add

        //@mkdir($_fil_dir, 0777);
        //@chmod($_fil_dir, 0777); //2009/10/07 Add

        if(is_uploaded_file($_FILES[$_tagname]['tmp_name']) ){
            $_sess[$_tagname . "_del"] = "";
            if( $_mode != "delete" ){

                //拡張子チェック必要な場合
                if($_chk_ext_arr != null){
                    $_chk_ext = _get_extension($_FILES[$_tagname]['name']);
                    $ext_ok = false;
                    for($i=0;$i<_count($_chk_ext_arr);$i++){
                        if(trim($_chk_ext_arr[$i]) != ""){
                            if(strtolower(trim($_chk_ext_arr[$i])) == strtolower($_chk_ext)){
                                $ext_ok = true;
                                break;
                            }
                        }
                    }
                    if( $ext_ok == false){
                        $_str = "";
                        for($i=0;$i<_count($_chk_ext_arr);$i++){
                            $_str .= "「." . $_chk_ext_arr[$i] . "」";
                        }
                        $_ret_err_msg[] = "アップロードできるファイルの拡張子は".$_str."のみです。";
                        return $_ret_err_msg;
                    }
                }

                /**
                 *temp_fileの入れ替え
                 *テンプレート二重生成防止
                 */
                $wk_tmp_data = $_sess[$_tagname . '_tmp_data'];
                $_tmp_path = $_tmp_dir . $_sess[$_tagname . '_tmp_data'] . $_sess[$_tagname . '_tmp_data_ext'];
                if( $wk_tmp_data != "" ){
                    if(_is_file($_tmp_path)){
                        unlink($_tmp_path);
                    }
                }

                /**
                 *temp_fileの入れ替え
                 *$_del_flg off -> uploadした場合
                 */
                 $wk_upload = $_FILES[$_tagname]['tmp_name'];
                 $wk_file_name = $_sess[$_tagname . '_tmp_filename'];
                 if( $wk_upload != $wk_file_name ){
                     if( $wk_tmp_data != "" ){
                        if(_is_file($_tmp_path)){
                            unlink($_tmp_path);
                            $_sess[$_tagname . "_del"] = "";
                            $_del_flg="";
                            $_sess[$_tagname . '_tmp_data_ext'] = "";
                            $_sess[$_tagname . '_tmp_data'] = "";
                        }
                     }
                 }

                // 拡張子の取得
                $extension = '.'._get_extension($_FILES[$_tagname]['name']);
                //ランダムの固定
                $wk_rand = mt_rand();
                // UPされたファイルをシステムに見れる場所にコピーする
                $_tmp_path = $_tmp_dir  . $wk_rand . $extension;
                move_uploaded_file($_FILES[$_tagname]['tmp_name'], $_tmp_path );
                // 拡張子
                $_sess[$_tagname . '_tmp_data_ext'] = $extension;
                //テンプレートファイルをセッションに格納
                $_sess[$_tagname . '_tmp_data'] = $wk_rand;
                $_sess[$_tagname . '_tmp_filename'] = $_FILES[$_tagname]['tmp_name'];
            }else{
                /**
                 *$_mode -> deleteアップロードしたまま削除を選択した場合
                 *temp file を削除
                 */
                if($_sess[$_tagname . '_tmp_data'] != ""){
                    if( _is_file($_tmp_path)){
                        unlink($_tmp_path); //テンプレートフォルダにあるファイルを削除する
                        $_sess[$_tagname . "_del"] = "";
                        $_del_flg="";
                    }
                }
                $_sess[$_tagname . '_tmp_data_ext'] = "";
                $_sess[$_tagname . '_tmp_data'] = "";
                _disp_file($_dir_id, $_tagname, $_sess);
                return $_ret_err_msg;
            }

        }
        else{
            if($_sess[$_tagname . '_tmp_data'] != ""){
                if($_del_flg != ""){
                    $_tmp_path = $_tmp_dir .  $_sess[$_tagname . '_tmp_data'] . $_sess[$_tagname . '_tmp_data_ext'];
                    if( _is_file($_tmp_path)){
                        unlink($_tmp_path);
                    }
                    $_sess[$_tagname . "_del"]="";
                    $_del_flg="";
                    $_sess[$_tagname . '_tmp_data'] = "";
                    $_sess[$_tagname . '_tmp_data_ext'] = "";
                }
            }
            _disp_file($_dir_id, $_tagname, $_sess);

            //$_sess[$_tagname . "_del"] = $_del_flg;
            return $_ret_err_msg;
        }

        return $_ret_err_msg;
    }



    //---------------------------------------------------------
    //画像表示 : _disp_savefile
    //
    //        引数：$_id          対象管理者ID
    //        　　  $_ss_data     画像バイナリデータ
    //        　　  $_filename    ファイル名
    //        　　　$_ext         保存拡張子
    //        　　　$_del_flg     削除フラグ
    //        戻値：ファイルが変更・追加の場合はファイル名
    //        　　　ファイルが削除の場合は""
    //        　　　ファイルの変更が無い場合はfalse
    //---------------------------------------------------------
    function _disp_savefile($_mode, $_id, $_tagname, &$_sess, $_file_base_name){

        /**
         *一時ファイル格納先ディレクトリ
         *格納先ディレクトリ
         */
        $_tmp_dir = _SYSTEM_ROOT_DIR . '/upfile/new_tmp/';
        $_fil_dir = _SYSTEM_ROOT_DIR . '/upfile/' . $_id . '/';

        /**
         *file name(databaseのファイル情報)
         *file path
         *temp file -> file.extention
         *temp path -> templateDirectory/temp file name . extention
         */
        $_file_name = $_sess[$_tagname];
        $_file_path = $_fil_dir . $_file_name;
        $_tmp_file = $_sess[$_tagname . '_tmp_data'];
        $_tmp_path = $_tmp_dir . $_sess[$_tagname . '_tmp_data'] . $_sess[$_tagname . '_tmp_data_ext'];

        if($_mode=="delete"){
            $_del_flg = "on";
        }else{
            $_del_flg = $_sess[$_tagname.'_del'];
        }

        //temp process(save用 file name)
        if($_del_flg == ""){
            if( $_tmp_file != "" ){
                $_sess[$_tagname] = $_file_base_name . $_sess[$_tagname.'_tmp_data_ext'];
                $_save_file = $_file_base_name . $_sess[$_tagname.'_tmp_data_ext'];
                $_save_path = $_fil_dir . $_save_file;
            }
        }

        // ディレクトリが無い場合は、作成する
        @mkdir($_fil_dir, 0777);
        @chmod($_fil_dir, 0777); //2009/10/07 Add



        /**
         *image delete process
         *File削除処理
         */
        if( $_tmp_file != "" ){
            /**
              *$_file_path->既に登録されているファイル情報
              *$_file_path = $_fil_dir(Directory) . $_file_name(既存ファイル名)
              */
            if( _is_file($_file_path) ){
                unlink($_file_path);
            }
            /**
             *$_save_path->新規登録するファイルの経路
             *$_save_path = $_fil_dir(Directory) . $_save_file(新規ファイル名)
             */
            copy($_tmp_path,$_save_path);
            @chmod($_save_path,0666);

        }else{ //upLoadがない場合
            if( $_del_flg != ""){
                //既に登録されているファイルの削除
                if( _is_file($_file_path) ){
                    unlink( $_file_path );
                }
                //※セッション削除
                $_sess[$_tagname] = "";
            }
        }

        //一時ファイルが残っていれば削除
        if( _is_file($_tmp_path) ){
            unlink($_tmp_path);
        }

        return;

    }
    //2007/07/02 Add ------------------ End -------------------------

    //2007/07/20 Add ------------------ Start -----------------------
    function _postRequest( $targeturl, $data, $post="80" ) {

        if( _urlCheck( $targeturl,'')==false){
            return "";
        }

        //$port = 80;
        $query = "";
        $post = "";

        // postデータの作成
        foreach( $data as $key => $value ) {
        if( $post != "" ) $post .= "&";
        $post .= $key . "=" . urlencode( $value );
        }

        // targeturl先URLからホスト名やパスを取り出す
        $url = parse_url( $targeturl );
        if( isset( $url['query']) ) $query = "?".$url['query'];
        if( isset( $url['port'])  ) $port = $url['port'];

        // HTTPリクエストの作成
        $req = "POST ".$url['path'].$query." HTTP/1.0\r\n";
        $req .= "Host: ".$url['host']."\r\n";
        $req .= "User-Agent: PHP/".phpversion()."\r\n";
        $req .= "Content-type: application/x-www-form-urlencoded\r\n";
        $req .= "Content-Length: ".strlen($post)."\r\n\r\n";
        $req .= $post."\r\n";

        // ソケットを開く
        $fn = @fsockopen( $url['host'], $port );
        if( $fn === FALSE ){
            return "";
        }

        // 送信
        fputs( $fn, $req );
        // レスポンス受信
        $res = "";
        while( !feof($fn) ) {
            $res = $res . fgets( $fn );
        }
        fclose($fn);

//2010/09/21 mod -----------
#         $part = split("\r\n\r\n", $res, 2);]
        $part = preg_split("/\r\n\r\n/", $res, 2);
//2010/09/21 mod -----------

        $part[1] = ereg_replace("\r\n[\t ]+", " ", $part[1]);

        return $part[1];

    }
    //2007/07/20 Add ------------------ End -------------------------


    //2008/01/30 Add ----- PHP5対応 ---- Strat -----------
    function _array_merge($array1,$array2){
            if( !(is_array($array1)) ){
                return $array2;
            }
            if( !(is_array($array2)) ){
                return $array1;
            }
            return array_merge($array1,$array2);
    }
    //2008/01/30 Add ----- PHP5対応 ---- End -----------

    function _getAge($birth){
        $ty = date("Y");
        $tm = date("m");
        $td = date("d");
        list($by, $bm, $bd) = explode('/', $birth);
        $age = $ty - $by;
        if($tm * 100 + $td < $bm * 100 + $bd) $age--;
        return $age;
    }

    function _e2n($_val){
        if($_val==""){
            return "NULL";
        }else{
            return $_val;
        }
    }

    //2010/09/13 Add --- Start ----------
    function _e2phpnull($_val){
        if($_val==""){
            return null;
        }else{
            return $_val;
        }
    }
    //2010/09/13 Add --- End   ----------

    //**********************************************************************
    //  CKEditorエディタ関数
    //**********************************************************************
    function _strip_tag($_str){
        if($_str != ""){
            //Scriptタグ削除
            $_str = preg_replace("/<\s?script\s?[^>]*?\s?>.*<\s?\/script\s?>/is", "", $_str);
            $_str = preg_replace("/<\s?include\s?[^>]*?\s?>.*/is", "", $_str);
        }
        return $_str;
    }


    //**********************************************************************
    //  WYSIWYGエディタ関数
    //  $_mode     : モード
    //  $_id       : ディレクトリ名
    //  $_tagname  : 投稿、変数対象のフィールド名
    //  $_file_name: 画像名
    //  $_$sess    : セッション
    //**********************************************************************
    function _html_edit_save($_mode,$_id,$_tagname,$_file_name_id,&$_sess){

        $_dir_name = $_id."_";
        $_base_name = $_file_name_id."_";

        //共通パタン
        $default_pattern = '/<IMG([^>]*)src="([^>" ]*)id=([^>" ]*)"([^>]*)>/is';
        //$delete_pattern  = '/<IMG([^>]*)src="([^>"]*)"([^>]*)>/is';
        $delete_pattern  = '/<IMG([^>]*)src="'.str_replace("/","\/",_SYSTEM_ROOT_URL).'\/upfile\/'.$_id.'\/([^>" ]*)"([^>]*)>/is';
        if($_mode == 'delete'){
            while(preg_match($delete_pattern,$_sess[$_tagname],$matches,PREG_OFFSET_CAPTURE) > 0){
                $del_img_path = _SYSTEM_ROOT_DIR."/upfile/".$_id."/".$matches[2][0];
                if(_file_exists($del_img_path)){
                    @unlink($del_img_path);
                }
                $_sess[$_tagname] = preg_replace('/<IMG([^>]*)src="'.str_replace("/","\/",_SYSTEM_ROOT_URL).'\/upfile\/'.$_id.'\/'.$matches[2][0].'"([^>]*)>/is','',$_sess[$_tagname]);
            }
        }else{
            //_print_r($_sess);
            //文字変換
            while(preg_match($default_pattern,$_sess[$_tagname],$matches,PREG_OFFSET_CAPTURE) > 0){
                $img_ext = $_SESSION[_PROJECT_NAME]['temp_img_sess'][$matches[3][0].'_tmp_data_ext'];
                $img_name = $_dir_name.$_base_name.substr($matches[3][0],8);

                $_sess[$_tagname] = preg_replace($default_pattern,
                                    '<IMG $1 src="'._SYSTEM_ROOT_URL.'/upfile/'.$_id.'/'.$img_name.$img_ext.'"'.' $4>',$_sess[$_tagname],1);
                //画像登録
                _disp_saveimg( $_mode,$_id,$matches[3][0], $_SESSION[_PROJECT_NAME]['temp_img_sess'], $img_name);
            }
            if($_mode == 'update'){
                //画像アップなしで本文から画像を削除する場合、アップファイルからごみを掃除する
                $moto_img_arr = array();
                while(preg_match($delete_pattern,$_sess['init_data'][$_tagname],$matches,PREG_OFFSET_CAPTURE) > 0){
                    //$mto_img_path = str_replace(_SYSTEM_ROOT_URL,_SYSTEM_ROOT_DIR,$matches[2][0]);
                    $mto_img_path = _SYSTEM_ROOT_DIR."/upfile/".$_id."/".$matches[2][0];
                    if(_file_exists($mto_img_path)){
                        $moto_img_arr[] = $matches[2][0];
                    }
                    $_sess['init_data'][$_tagname] = preg_replace('/<IMG([^>]*)src="'.str_replace("/","\/",_SYSTEM_ROOT_URL).'\/upfile\/'.$_id.'\/'.$matches[2][0].'"([^>]*)>/is','',$_sess['init_data'][$_tagname]);
                }
                //現在のセッションを違うセッションに格納する
                $_sess['curretn_sess'] = $_sess;
                $current_img_arr = array();
                while(preg_match($delete_pattern,$_sess['curretn_sess'][$_tagname],$matches,PREG_OFFSET_CAPTURE) > 0){
                    //$crr_img_path = str_replace(_SYSTEM_ROOT_URL,_SYSTEM_ROOT_DIR,$matches[2][0]);
                    $crr_img_path = _SYSTEM_ROOT_DIR."/upfile/".$_id."/".$matches[2][0];
                    if(_file_exists($crr_img_path)){
                        $current_img_arr[] = $matches[2][0];
                    }
                    $_sess['curretn_sess'][$_tagname] = preg_replace('/<IMG([^>]*)src="'.str_replace("/","\/",_SYSTEM_ROOT_URL).'\/upfile\/'.$_id.'\/'.$matches[2][0].'"([^>]*)>/is','',$_sess['curretn_sess'][$_tagname]);
                }
                //元のセッションと現在のセッションを比較して削除された画像があれば、画像を削除する
                if(_count($current_img_arr) > 0){
                    for($i=0;$i<_count($current_img_arr);$i++){
                        for($j=0;$j<_count($moto_img_arr);$j++){
                            if($current_img_arr[$i] == $moto_img_arr[$j]){
                                $moto_img_arr[$j] = "";
                            }
                        }
                    }
                    for($i=0;$i<_count($moto_img_arr);$i++){
                        if($moto_img_arr[$i] != ""){
                            $del_img = _SYSTEM_ROOT_DIR."/upfile/".$_id."/".$moto_img_arr[$i];
                            if(_file_exists($del_img)){
                                @unlink($del_img);
                            }
                        }
                    }
                }elseif(_count($current_img_arr) <= 0 && _count($moto_img_arr) > 0){
                    //現在画像セッションがなく、元のセッションには画像がある場合削除する(画像が一個)
                    $del_img = _SYSTEM_ROOT_DIR."/upfile/".$_id."/".$moto_img_arr[0];
                    if(_file_exists($del_img)){
                        @unlink($del_img);
                    }
                }
            }
        }
    }

    function _disp404(){
        global $blade;
        header("HTTP/1.0 404 Not Found");
        if( file_exists( _SYSTEM_ROOT_DIR.'/404.html' ) ){
            $blade->assign('_SYSTEM_ROOT_DIR', _SYSTEM_ROOT_DIR);
            $blade->assign('_SYSTEM_ROOT_URL', _SYSTEM_ROOT_URL);
            $blade->assign('_SYSTEM_ROOT_URLS', _SYSTEM_ROOT_URLS);
            $blade->assign('page', "404");
            _smartyDisplay( $blade,  _SYSTEM_ROOT_DIR.'/404.html' );
        }else{
            echo '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">'."\n";
            echo '<html><head>'."\n";
            echo '<title>404 Not Found</title>'."\n";
            echo '</head><body>'."\n";
            echo '<h1>Not Found</h1>'."\n";
            echo '<p>The requested URL '. _hs($_SERVER["REQUEST_URI"]).' was not found on this server.</p>'."\n";
            echo '</body></html>'."\n";
        }
        exit;
    }

    function _dispError(){
        echo '<!DOCTYPE html>'."\n";
        echo '<html lang="ja">'."\n";
        echo '<head>'."\n";
        echo '<meta charset="UTF-8">'."\n";
        echo '<meta http-equiv="X-UA-Compatible" content="IE=edge">'."\n";
        echo '<title>不正アクセス検知</title>'."\n";
        echo '<meta name="viewport" content="width=device-width, initial-scale=1">'."\n";
        echo '</head><body>'."\n";
        echo '<h1>不正なアクセスが検知されました</h1>'."\n";
        echo '</body></html>'."\n";
        exit;
    }

    function csvSafe($val){
        return str_replace('"', '""', $val);
    }

    function _syousuuTrim($val){
        $wArr = explode(".", $val);
        $str = "";
        if($wArr[1]!=""){
            $str = $wArr[1];
            while($str!="") {
                if(substr($str,-1,1)=="0"){
                    $str = substr($str,0,-1);
                }else{
                    break;
                }
            }
        }

        $ret = $wArr[0];
        if($str!=""){
            $ret .= "." . $str;
        }
        return $ret;
    }

    //2017/12/10 Add ------------ Start ---------------
    function _tinymceInit( &$this_sess, $id ){
        if($id==""){
            $this_sess['tinyMCE_tmp_id'] = rand();
            $img_dir = "/upfile/new_tmp/".$this_sess['tinyMCE_tmp_id'];
        }else{
            $img_dir = "/upfile/user/".$id;
        }
        $this_sess['tinyMCE_img_url']  = _SYSTEM_ROOT_URLS . $img_dir; // 画像格納フォルダURL
        $this_sess['tinyMCE_img_dir']  = _SYSTEM_ROOT_DIR  . $img_dir; // 画像格納フォルダパス
        _mkdir($this_sess['tinyMCE_img_dir']);
    }

    function _tinymceSave( &$this_sess, $upfile_dir, $html_body){
        if( $this_sess['mode'] == "insert"){
            if($this_sess['tinyMCE_tmp_id']!=""){
                _mkdir( _SYSTEM_ROOT_DIR.$upfile_dir);
                $img_dir = _SYSTEM_ROOT_DIR."/upfile/new_tmp/".$this_sess['tinyMCE_tmp_id'];
                $_files = _glob($img_dir."/*");
                for ($i=0; $i < _count($_files); $i++) {
                    if( $_files[$i]!="" && substr($_files[$i],-11)!=".quarantine" && substr($_files[$i],-4)!=".tmb" && substr($_files[$i],-9)!=".DS_Store"){
                        copy( $_files[$i], _SYSTEM_ROOT_DIR.$upfile_dir."/".basename($_files[$i]));
                    }
                }
                _rmdir($img_dir);
                $html_body = str_replace("../upfile/new_tmp/".$this_sess['tinyMCE_tmp_id'], _SYSTEM_ROOT_URLS.$upfile_dir, $html_body );
            }
        }else{
            $html_body = str_replace("..".$upfile_dir, _SYSTEM_ROOT_URLS.$upfile_dir, $html_body );
        }

        return $html_body;

    }
    //2017/12/10 Add ------------ End ---------------

    function _count($obj){
        if(is_array($obj)){
            return count($obj);
        }else{
            return 0;
        }
    }
?>