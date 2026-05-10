<?php
    //なぜかtenjikaiのAWSでは存在しない$_SERVER['HTTP_HOST']を参照するとバッチでエラーになるので
    if(!isset($_SERVER['HTTP_HOST'])){
        $_SERVER['HTTP_HOST']="";
    }

    $test_info = array();
    if( $_SERVER['HTTP_HOST'] != ""){
        define('_RENKEI_TEST_MAIL_POP3',"mail.test-demo-server.com"); //POP3
        define('_RENKEI_TEST_MAIL_ID',"tenjikai_return@test-demo-server.com"); //POP3 ID
        define('_RENKEI_TEST_MAIL_PASS',"yatagarasu00"); //POP3 PASSWORD

        $test_info['test_to'] = "retadr-u00000001@test-tenjikai.nippon-access.co.jp";
        $test_info['test_ymd'] = "2021/05/28";
        $test_info['test_time'] = "01:00";
    }


    $_this_dir = dirname(__FILE__);
    $_base_dir = dirname(dirname(__FILE__));

    $project_name_prefix = "bat_";
    require( $_base_dir."/lib/environment.php" );
    require( $_base_dir."/lib/Smarty.class.php" );
    require( $_base_dir."/lib/UserSmarty.php" );
    require( $_base_dir."/lib/lang.php" );
    require( $_base_dir."/lib/inc.php" );
    require( $_base_dir."/lib/check.php" );
    require( $_base_dir."/lib/picture.php" );
    require( $_base_dir."/lib/project.php" );

    /* コマンドー送信！！ */
    function _sendcmd(&$sock, $cmd, $err_ret=false) {
      fputs($sock, $cmd."\r\n");
      $buf = fgets($sock, 512);
      if(substr($buf, 0, 3) == '+OK') {
        return $buf;
      } else {
        if($err_ret){
            return $buf;
        }else{
            die($buf);
        }
      }
      return false;
    }


    /* ヘッダと本文を分割する */
    function _mime_split($data) {
      // $part = split("\r\n\r\n", $data, 2);
      $part = explode("\r\n\r\n", $data, 2);
      // $part[1] = ereg_replace("\r\n[\t ]+", " ", $part[1]);
      $part[1] = preg_replace('/\r\n[\t ]+/', " ", $part[1]);

      return $part;
    }

    /* ヘッダと本文を分割する */
    function _mime_split2($data) {
      $part = preg_split("/\r\n[\s]*\r\n/", $data, -1, PREG_SPLIT_NO_EMPTY);
      // $part[1] = ereg_replace("\r\n[\t ]+", " ", $part[1]);
      $part[1] = preg_replace('/\r\n[\t ]+/', " ", $part[1]);

      return $part;
    }

    /* メールアドレスを抽出する */
    function _addr_search($addr) {
      // if(eregi("[-!#$%&\'*+\\./0-9A-Z^_`a-z{|}~]+@[-!#$%&\'*+\\/0-9=?A-Z^_`a-z{|}~]+\.[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+", $addr, $fromreg)) {
      if(preg_match('/^([a-zA-Z0-9\/\?_\-])+([\.a-zA-Z0-9\/\?_\-])*@([a-zA-Z0-9_\-])+(\.[a-zA-Z0-9_\-]+)+/i', $addr, $fromreg)) {

        return $fromreg[0];
      } else {
        return false;
      }
    }

    
    // ******************************************************************************************
    // ******************************************************************************************
    // ******************************************************************************************

    if( strpos(strtolower(__FILE__),"apacheroot") !== FALSE){
        set_time_limit(90); //90秒稼動
        ini_set("memory_limit","1024M"); //メモリ拡大
    }


    $update_cnt = 0;

    //*****************
    // 受信処理
    //*****************
    $num = 0;
    if( $_SERVER['HTTP_HOST'] != ""){
        $host = _RENKEI_TEST_MAIL_POP3; //POP3
        $user = _RENKEI_TEST_MAIL_ID; //POP3 ID
        $pass = _RENKEI_TEST_MAIL_PASS; //POP3 PASSWORD

        if( $sock = fsockopen($host, 110, $err, $errno, 10) ){

            $buf = fgets($sock, 512);
            if(substr($buf, 0, 3) != '+OK') die($buf);
            $buf = _sendcmd($sock, "USER $user");
            $buf = _sendcmd($sock, "PASS $pass");
            $data = _sendcmd($sock, "STAT");//STAT -件数とサイズ取得 +OK 8 1234
            sscanf($data, '+OK %d %d', $mail_num, $size);
            if($mail_num == "0") { //件数が0==なかったら
                $buf = _sendcmd($sock, "QUIT"); //バイバイ(きる)
                fclose($sock); //socectを閉じる
                echo "mail none";
                exit();
            }
            // 件数分
            // for($i=1;$i<=$num;$i++) {
            //1件しか処理させない
            for($i=1;$i<=1;$i++) {
               $line = _sendcmd($sock, "RETR $i");//RETR n -  n番目のメッセージ取得（ヘッダ含）
                //while (!ereg("^\.\r\n",$line)) {//EOFの.まで読む  .CRLF
                while (!preg_match('/^\.\r\n/',$line) ){//EOFの.まで読む  .CRLF
                   $line = fgets($sock,512);
                    $dat[$i].= $line;
                }
                
                //メールBOXから削除
                //$data = _sendcmd($sock, "DELE $i");//DELE n n番目のメッセージ削除
                $num++;
            }
            $buf = _sendcmd($sock, "QUIT"); //バイバイ
            fclose($sock);



            if( $_SERVER['HTTP_HOST'] != "" ){
                header( "Content-Type: text/html; charset=utf-8" );
                echo "cnt=".$num."<hr>"; 
            }

            //*****************
            // 解析処理
            //*****************
        }else{
            $num = 0;
        }
    }else{
        $num = 1;
        $dat = array();
        $dat[1] = @file_get_contents("php://stdin");
    }


    if( $num > 0){
        $conn = _dbConnect();
    }


    for($j=1;$j<=$num;$j++) {

        //メール解析のために改行統一(CRLFに)
        $dat[$j] = str_replace("\r\n", "\n", $dat[$j]);
        $dat[$j] = str_replace("\r", "\n", $dat[$j]);
        $dat[$j] = str_replace("\n", "\r\n", $dat[$j]);

        //ループごとに変数初期化
        $subject = "";
        $from = "";
        $part = array();
        $text = "";

        //headとbodyに分ける
        list($head, $body) = _mime_split($dat[$j]);

        // //メール日付の取得
        // // eregi("Date:[ \t]*([^\r\n]+)", $head, $datereg);
        // preg_match('/Date:[ \t]*([^\r\n]+)/i', $head, $datereg);

        // $mail_date = strtotime($datereg[1]);
        // if($mail_date == -1){
        //     //日付取得できなかったので、仮で現在日時をセット
        //     $mail_date = time();
        // }

        // //件名の抽出
        // // $head = ereg_replace("\r\n? ", "", $head);
        // $head = preg_replace('/\r\n? /', "", $head);
        // if(preg_match('/\nSubject:[ \t]*([^\r\n]+)/i', $head, $subreg)) {
        //     $subject = $subreg[1];
        //     $enc = "auto";
        //     while (preg_match('/(.*)=\?iso-2022-jp\?B\?([^\?]+)\?=(.*)/i',$subject,$regs)) {//MIME Bﾃﾞｺｰﾄﾞ
        //         $subject = $regs[1] . base64_decode($regs[2]) . $regs[3];
        //         $enc = "ISO-2022-JP";
        //     }
        //     while (preg_match('/(.*)=\?iso-2022-jp\?Q\?([^\?]+)\?=(.*)/i',$subject,$regs)) {//MIME Qﾃﾞｺｰﾄﾞ
        //         $subject = $regs[1].quoted_printable_decode($regs[2]).$regs[3];
        //         $enc = "ISO-2022-JP";
        //     }
        //     while (preg_match('/(.*)=\?utf[-]*8\?B\?([^\?]+)\?=(.*)/i',$subject,$regs)) {//MIME Bﾃﾞｺｰﾄﾞ
        //         $subject = $regs[1] . base64_decode($regs[2]) . $regs[3];
        //         $enc = "UTF8";
        //     }
        //     while (preg_match('/(.*)=\?utf[-]*8\?Q\?([^\?]+)\?=(.*)/i',$subject,$regs)) {//MIME Qﾃﾞｺｰﾄﾞ
        //         $subject = $regs[1].quoted_printable_decode($regs[2]).$regs[3];
        //         $enc = "UTF8";
        //     }
        //     while (preg_match('/(.*)=\?cp932\?B\?([^\?]+)\?=(.*)/i',$subject,$regs)) {//MIME Bﾃﾞｺｰﾄﾞ
        //         $subject = $regs[1] . base64_decode($regs[2]) . $regs[3];
        //         $enc = "CP932";
        //     }
        //     while (preg_match('/(.*)=\?cp932\?Q\?([^\?]+)\?=(.*)/i',$subject,$regs)) {//MIME Qﾃﾞｺｰﾄﾞ
        //         $subject = $regs[1].quoted_printable_decode($regs[2]).$regs[3];
        //         $enc = "CP932";
        //     }
        //     while (preg_match('/(.*)=\?shift_jis\?B\?([^\?]+)\?=(.*)/i',$subject,$regs)) {//MIME Bﾃﾞｺｰﾄﾞ
        //         $subject = $regs[1] . base64_decode($regs[2]) . $regs[3];
        //         $enc = "SJIS-WIN";
        //     }
        //     while (preg_match('/(.*)=\?shift_jis\?Q\?([^\?]+)\?=(.*)/i',$subject,$regs)) {//MIME Qﾃﾞｺｰﾄﾞ
        //         $subject = $regs[1].quoted_printable_decode($regs[2]).$regs[3];
        //         $enc = "SJIS-WIN";
        //     }

        //     /* 文字コードコンバートJIS→UTF8 */
        //     //$subject = mb_convert_encoding($subject, "UTF8", "JIS,SJIS,UTF8");
        //     //$subject = mb_convert_encoding($subject, "UTF8", "auto");
        //     if($enc!="UTF8"){
        //         $subject = mb_convert_encoding($subject, "UTF8", $enc);    
        //     }
            
        //     $subject = trim($subject);

        // }
        // // 送信者アドレスの抽出
        // if(preg_match('/[\r\n]+From:[ \t]*([^\r\n]+)/i', $head, $freg)) {
        //     $from = _addr_search($freg[1]);
        // } elseif(preg_match('/Reply-To:[ \t]*([^\r\n]+)/i', $head, $freg)) {
        //     $from = _addr_search($freg[1]);
        // } elseif(preg_match('/Return-Path:[ \t]*([^\r\n]+)/i', $head, $freg)) {
        //     $from = _addr_search($freg[1]);
        // }
        // $from = trim($from);

        // 受信者アドレスの抽出
        if(preg_match('/[\r\n]+To:[ \t]*([^\r\n]+)/i', $head, $treg)) {
            $to = _addr_search($treg[1]);
        }
        $to = trim($to);

        // // マルチパートならばバウンダリに分割
        // if(preg_match('/\nContent-type:.*multipart\//i',$head)) {
        //     preg_match('/boundary="([^"]+)"/i', $head, $boureg);
        //     $part = explode("--".$boureg[1],$body);
        //     if(preg_match('/boundary="([^"]+)"/i', $part[1], $boureg2)) {//multipart/altanative or multipart/related
        //         $npart = explode("--".$boureg2[1],$part[1]);
        //         if(preg_match('/boundary="([^"]+)"/i', $npart[1], $boureg3)) {//multipart/altanative or multipart/related
        //             $npart2 = explode("--".$boureg3[1],$npart[1]);
        //             array_splice($npart, 1, 1, $npart2);
        //         }
        //         array_splice($part, 1, 1, $npart);
        //     }
        // } else {
        //     $part[0] = $dat[$j];// 普通のテキストメール
        // }

        // //マルチパートごとのループ
        // foreach ($part as $multi) {


        //     $cid = "";
        //     $filename = "";

        //     //headとbodyに分割
        //     list($m_head, $m_body) = _mime_split($multi);
        //     // $m_body = ereg_replace("\r\n\.\r\n$", "", $m_body);
        //     $m_body = preg_replace('/\r\n\.\r\n$/', "", $m_body);

        //     if(trim($m_body)==''){
        //         list($m_head, $m_body) = _mime_split2($multi);
        //         // $m_body = ereg_replace("\r\n\.\r\n$", "", $m_body);
        //         $m_body = preg_replace('/\r\n\.\r\n$/', "", $m_body);
        //     }

        //     if(!preg_match('/\nContent-type: *([^;\n]+)/i', $m_head, $type)){
        //         continue;
        //     }

        //     list($main, $sub) = explode("/", $type[1]);

        //     // 本文
        //     if(strtolower($main) == "text") {
        //         if(strtolower($sub) == "plain" && trim($text) != "") continue;

        //         $b64_in = 0;
        //         if(preg_match('/Content-Transfer-Encoding:.*base64\r\n/i', $m_head)){
        //             $b64_in = 1;
        //             $m_body = base64_decode($m_body);
        //         }
        //         if($b64_in == 0){
        //             if(preg_match('/Content-Transfer-Encoding:.*base64$/i', $m_head)){
        //                 $b64_in = 1;
        //                 $m_body = base64_decode($m_body);
        //             }
        //         }

        //         $quo_in = 0;
        //         if(preg_match('/Content-Transfer-Encoding:.*quoted-printable\r\n/i', $m_head)){
        //             $quo_in = 1;
        //             $m_body = quoted_printable_decode($m_body);
        //         }
        //         if($quo_in == 0){
        //             if(preg_match('/Content-Transfer-Encoding:.*quoted-printable$/i', $m_head)){
        //                 $quo_in = 1;
        //                 $m_body = quoted_printable_decode($m_body);
        //             }
        //         }

        //         $enc2 = "auto";
        //         if(preg_match('/\nContent-type: *[^;]+;[ \r\n\t]*charset=\"*([^\"\r\n\t ]+)/i', $m_head, $charset)){
        //             $enc2 = $charset[1];
        //             $enc2 = strtoupper($enc2);
        //             if($enc2=="UTF-8") $enc2 = "UTF8";
        //         }
        //         $enc2 = trim($enc2);

        //         //$text = mb_convert_encoding($m_body, "UTF8", "JIS,SJIS,UTF8");
        //         //$text = mb_convert_encoding($m_body, "UTF8", "auto");

        //         $text = $m_body;
        //         if($enc2!="UTF8"){
        //             $text = mb_convert_encoding($text, "UTF8", $enc2);    
        //         }


        //         if(strtolower($sub) == "html"){
        //             $text = str_replace("<BODY", "<body", $text);
        //             $text = str_replace("</BODY>", "</body>", $text);
        //             if( strpos( $text,"<body") !== FALSE){
        //                 $w_arr = explode("<body", $text);
        //                 $text = $w_arr[1];
        //                 $w_arr = explode(">", $text,2);
        //                 $text = $w_arr[1];

        //             }
        //             if( strpos( $text,"</body>") !== FALSE){
        //                 $w_arr = explode("</body>", $text);
        //                 $text = $w_arr[0];
        //             }

        //             $text = str_replace("<div=", "<div", $text);
        //             $text = str_replace("<font=", "<font", $text);

        //             if($html_mail_to_text){
        //                 $text = str_replace("<br>", "\r\n", $text);
        //                 $text = str_replace("<BR>", "\r\n", $text);
        //                 $text = str_replace("<Br>", "\r\n", $text);
        //                 //$text = strip_tags($text);
        //             }
        //         }else{
        //             // $text = strip_tags($text);
        //             // $text = nl2br($text);
        //         }

        //         // mac削除
        //         // $text = ereg_replace("Content-type: multipart/appledouble;[[:space:]]boundary=(.*)","",$text);
        //         $text = preg_replace('/Content-type: multipart\/appledouble;[[:space:]]boundary=(.*)/',"",$text);

        //         // \nに統一
        //         $text = str_replace("\r\n", "\r",$text);
        //         $text = str_replace("\r", "\n",$text);
        //         $text = preg_replace("/\n{2,}/", "\n\n", $text);
        //         $text = str_replace("\n", "\r\n", $text);
        //     }

        //     // if(eregi('boundary="([^"]+)"', $body, $boureg2)){
        //     //     // ファイル名を抽出
        //     //     if(eregi("name=\"?([^\"\n]+)\"?",$m_head, $filereg)) {
        //     //         $filename = ereg_replace("[\t\r\n]", "", $filereg[1]);
        //     //         while (eregi("(.*)=\?iso-2022-jp\?B\?([^\?]+)\?=(.*)",$filename,$regs)) {
        //     //             $filename = $regs[1].base64_decode($regs[2]).$regs[3];
        //     //             /* 文字コードコンバートJIS→UTF8 */
        //     //            //$filename = mb_convert_encoding($filename, "UTF8", "JIS,SJIS,UTF8");
        //     //            $filename = mb_convert_encoding($filename, "UTF8", "auto");
        //     //         }
        //     //     }
        //     // }

        // }//マルチパートごとのループ


        if( $_SERVER['HTTP_HOST'] != ""){
            //localのブラウザテスト時は$to入れ替え
            if($test_info['test_to']!="") $to = $test_info['test_to'];
        }
 
        // if($to != "" && $text!=""){
        if($to != ""){
            //toがある
            $wArr1 = explode("@", $to);
            $wArr2 = explode("-", $wArr1[0]);
            if(count($wArr2)==2){
                //@の前が「-」で２つに別れてる
                $koteiPre = $wArr2[0];
                $user_id = $wArr2[1];
                if($koteiPre=="retadr"){
                    if( strlen($user_id)==9 && substr($user_id,0,1)=="u" && _seisuuCheck(substr($user_id,1),'')!=false){
                        //uxxxxxxxxの値である
                        $sql = "";
                        $sql .= "select * from v_user where user_id='".$user_id."'";
                        $user_recs = _select($sql);
                        if(count($user_recs) > 0){
                            $user_rec = $user_recs[0];

                            _query( $conn, "begin" );

                            $array = array();
                            $array['user_mail_send_kbn'] = "2"; //error
                            $array['user_update_date'] = "'".$_now_timestamp."'";
                            $where = "user_id = '"._as($user_id)."'";
                            _update("m_user",$array,$where);        

                            _query( $conn, "commit");

                            $update_cnt++;
                        }


                    }
                }
            }

        }//登録メアド
    }//メール1件ずつ


    if( $num > 0){
        _dbDisconnect( $conn );
    }



    if( $_SERVER['HTTP_HOST'] != ""){ echo "<hr>end update_cnt=".$update_cnt . " (".date("H:i:s").")"; }

    exit();
