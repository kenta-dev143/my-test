<?php
    //2009/05/11
    //2010/03/21 kamitani mod ($zen_lengthは全角を意識した文字数という前提で...
    //つまり全角で何文字表示できるかを指定すればOK、仮にオーバーして...が付いても
    //その...も含めて指定された全角文字数内で収めてくれるので、あまり気にせず
    //とにかく全角で何文字までなら表示できるかを指定すればOK
    function smarty_modifier_mb_truncate_with_tag($string, $zen_length = 80, $etc = '…'){
      if ($zen_length == 0){
        return '';
      }
        $etc_len = strlen(mb_convert_encoding($etc, "sjis-win",_ENCODING_SRC));
        $etc_added = false;
        $taf_skip = false;
        $han_length = $zen_length * 2;
        $han_length = $han_length - $etc_len;
        $add_han_len = 0;
        $ret_str = "";
        for($i=0;$i<mb_strlen($string,_ENCODING_SRC);$i++){
            $one = mb_substr($string, $i, 1,_ENCODING_SRC);
            if($tag_skip == true){
                $ret_str .= $one;
                if($one==">"){
                    $tag_skip = false;
                }
            }else{
                if($one=="<"){
                    $tag_skip = true;
                    if($etc_added==true){
                        $str4 = strtolower( mb_substr($string, $i, 4,_ENCODING_SRC) );
                        $str5 = strtolower( mb_substr($string, $i, 5,_ENCODING_SRC) );
                        $str6 = strtolower( mb_substr($string, $i, 6,_ENCODING_SRC) );
                        if($str4=="<br>"){
                            $i += 3;
                            $tag_skip = false;
                        }elseif($str5=="<br/>"){
                            $i += 4;
                            $tag_skip = false;
                        }elseif($str6=="<br />"){
                            $i += 5;
                            $tag_skip = false;
                        }else{
                            $ret_str .= $one;
                        }
                    }else{
                        $ret_str .= $one;
                    }
                }else{
                    $one_sjis = mb_convert_encoding($one, "sjis-win",_ENCODING_SRC); //2014/07/31 Add
                    if(strlen($one_sjis)==1){
                        $add_han_len = $add_han_len + 1;
                    }else{
                        $add_han_len = $add_han_len + 2;
                    }
                    if( $add_han_len <= $han_length ){
                        $ret_str .= $one;
                    }else{
                        if($etc_added==false){
                            $ret_str .= $etc;
                            $etc_added = true;
                        }
                    }
                }
            }

        }

        return $ret_str;
    }
?>