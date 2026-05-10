<?php
    //2009/05/11
    //2010/03/21 kamitani mod ($lengthは全角を意識した文字数という前提で...)
    function smarty_modifier_mb_truncate($string, $length = 80, $etc = '…'){
      if ($length == 0){
        return '';
      }
#       if (mb_strlen($string,_ENCODING_SRC) > $length) {
#         $string = mb_substr($string, 0, $length,_ENCODING_SRC);
#         return $string.$etc;
#       } else {
#         return $string;
#       }
        $han_length = $length * 2;
        $add_han_len = 0;
        $ret_str = "";
        for($i=0;$i<mb_strlen($string,_ENCODING_SRC);$i++){
            $one = mb_substr($string, $i, 1,_ENCODING_SRC);
            if(strlen($one)==1){
                $add_han_len = $add_han_len + 1;
            }else{
                $add_han_len = $add_han_len + 2;
            }
            if( $add_han_len > $han_length ){
                $ret_str .= $etc;
                break;
            }
            $ret_str .= $one;
        }
        return $ret_str;
    }
?>