<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty emoji_img modifier plugin
 *
 * Type:     modifier<br>
 * Name:     emoji_img<br>
 * Purpose:  絵文字を画像に変換する
 * @param string
 * @param size
 * @return string
 */

/**
* KLib の オリジナル Add by 2008/07/28
*/
function smarty_modifier_emoji_img($string, $size = 16)
{
    global $jskey2js_arr,$imkey2im_arr,$ezkey2ez_arr;

    // _SYSTEM_ROOT_URL が未設定の場合
    if(_SYSTEM_ROOT_URL == NULL || _SYSTEM_ROOT_URL == ""){
        return $string;
    }

    // SoftBankの絵文字画像への変換
#     reset($jskey2js_arr);
#     while( list($key,$val) = each($jskey2js_arr) ){
#         $cnv = "<img src=\"" . _SYSTEM_ROOT_URL . "/lib/emoji/sb/" . substr($key,4,4) . ".gif\" width=\"" . $size . "\" border=\"0\">";
#         $string = str_replace($key,$cnv,$string);
#         $string = str_replace(strtolower($key),$cnv,$string);
#     }
#
#     // I-MODEの絵文字画像への変換
#     reset($imkey2im_arr);
#     while( list($key,$val) = each($imkey2im_arr) ){
#         $cnv = "<img src=\"" . _SYSTEM_ROOT_URL . "/lib/emoji/i/" . substr($key,4,4) . ".gif\" width=\"" . $size . "\" border=\"0\">";
#         $string = str_replace($key,$cnv,$string);
#         $string = str_replace(strtolower($key),$cnv,$string);
#     }
#
#     // AUの絵文字画像への変換
#     reset($ezkey2ez_arr);
#     while( list($key,$val) = each($ezkey2ez_arr) ){
#         $cnv = "<img src=\"" . _SYSTEM_ROOT_URL . "/lib/emoji/au/" . substr($key,4,4) . ".gif\" width=\"" . $size . "\" border=\"0\">";
#         $string = str_replace($key,$cnv,$string);
#         $string = str_replace(strtolower($key),$cnv,$string);
#     }
#2009/10/06 Mod -------------- Strat ------------
    // SoftBankの絵文字画像への変換
    if( strpos($string,"(#s:") !== FALSE ){
        $keys = array();
        $cnvs = array();
        foreach( $jskey2js_arr as $key => $val){
            $keys[] = $key;
            $cnvs[] = "<img src=\"" . _SYSTEM_ROOT_URL . "/lib/emoji/sb/" . substr($key,4,4) . ".gif\" width=\"" . $size . "\" border=\"0\">";
        }
        $string = str_replace($keys,$cnvs,$string);
    }

    // I-MODEの絵文字画像への変換
    if( strpos($string,"(#i:") !== FALSE ){
        $keys = array();
        $cnvs = array();
        foreach( $imkey2im_arr as $key => $val){
            $keys[] = $key;
            $cnvs[] = "<img src=\"" . _SYSTEM_ROOT_URL . "/lib/emoji/i/" . substr($key,4,4) . ".gif\" width=\"" . $size . "\" border=\"0\">";
        }
        $string = str_replace($keys,$cnvs,$string);
    }

    // AUの絵文字画像への変換
    if( strpos($string,"(#e:") !== FALSE ){
        $keys = array();
        $cnvs = array();
        foreach( $ezkey2ez_arr as $key => $val){
            $keys[] = $key;
            $cnvs[] = "<img src=\"" . _SYSTEM_ROOT_URL . "/lib/emoji/au/" . substr($key,4,4) . ".gif\" width=\"" . $size . "\" border=\"0\">";
        }
        $string = str_replace($keys,$cnvs,$string);

    }
#2009/10/06 Mod -------------- End ------------

    return $string;
}

/* vim: set expandtab: */

?>
