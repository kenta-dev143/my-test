<?php
    if($_request['exec'] == 'img_upload_space'){
        //IE10未満用初期iframeページ
        echo "<html><body></body></html>";
        exit();
    }
    if($_request['exec'] == 'img_upload' || $_request['exec'] == 'img_upload_del'){
            $fldName = $_request['img_upload_fldname'];
            unset( $_SESSION[_PROJECT_NAME][$page][$fldName.'_del'] );

            if($_request['exec'] == 'img_upload_del'){
                $_request[$fldName.'_del'] = "1";
            }

            $_imgSize = _getImageData($_FILES[$fldName]['tmp_name']);

            if($_request['exec'] == 'img_upload_del'){
                $_request['img_flg'] = 0;
            }elseif(is_uploaded_file($_FILES[$fldName]['tmp_name'])){
                $_request['img_flg'] = 1;
            }else{
                $_request['img_flg'] = 0;
            }

            $this_sess = _array_merge( $this_sess, $_request );

            $mbyte = $_UPLOAD_IMG_MAX_SIZE; //MB

            if(is_uploaded_file($_FILES[$fldName]['tmp_name']) ){
                $ext = _get_extension($_FILES[$fldName]['name']);
                if( strtolower($ext)!="gif" && strtolower($ext)!="png" && strtolower($ext)!="jpg" && strtolower($ext)!="jpeg"){
                    //拡張子.gif .png .jpg .jpegでない
                    $err_msg[$fldName][] = "画像ファイルはGIF又はPNG又はJPEGのファイルをアップロードしてください。";
                }
            }

            if($_FILES[$fldName]['size'] > ($mbyte * 1024 * 1024)){
                $err_msg[$fldName][] = "画像ファイルサイズは".$mbyte."MBまでです。";
            }


            if(_count($err_msg)==0){
                $img_err_msg = _disp_confirmimg( $_request['mode'], "new_tmp", $fldName, $this_sess, $disp_img_size_recs[$fldName]['w'], $disp_img_size_recs[$fldName]['h'], $img_size_recs[$fldName]['w'], $img_size_recs[$fldName]['h'] );
                $err_msg = _array_merge($err_msg,$img_err_msg);
            }

            if(_count($err_msg)>0){
                $this_sess['img_flg'] = 0;
            }

            //if($_request['img_upload_br']=="old"){
            if($_request['br']=="old"){

                echo '<html><head>'."\r\n";
                echo '<script>'."\r\n";
                echo 'function _doInit(){'."\r\n";
                if($_request['exec'] == 'img_upload_del'){
                    echo "parent.imageReturn('".$fldName."','DE')"."\r\n";
                }elseif($this_sess['img_flg']=="1"){
                    if( substr($this_sess[$fldName.'_size'],0,6)=="height"){
                        echo "parent.imageReturn('".$fldName."','OK_h')"."\r\n";
                    }else{
                        echo "parent.imageReturn('".$fldName."','OK_w')"."\r\n";
                    }
                }elseif(_count($err_msg)>0){
                    echo "parent.imageerrReturn('".$fldName."','".$err_msg[$fldName][0]."')";
                }else{
                    echo "parent.imageReturn('".$fldName."','画像が投稿できませんでした')"."\r\n";
                }
                echo '}'."\r\n";
                echo '</script>'."\r\n";
                echo '<body onLoad="_doInit();"></body></html>'."\r\n";

            }else{
                header( 'Content-Type: plain/text; charset=utf-8', true ); 
                if($_request['exec'] == 'img_upload_del'){
                        echo "DE";
                }elseif($this_sess['img_flg']=="1"){
                    if( substr($this_sess[$fldName.'_size'],0,6)=="height"){
                        echo "OK_h";
                    }else{
                        echo "OK_w";
                    }
                }elseif(_count($err_msg)>0){
                    //echo "parent.imageerrReturn('".$fldName."','".$err_msg[$fldName][0]."')";
                    echo $err_msg[$fldName][0];
                }else{
                    echo "画像が投稿できませんでした";
                }
            }
            exit();
    }
