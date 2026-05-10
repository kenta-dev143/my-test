<?php
/**
 * Smarty {img_upload_button} function plugin
 *
 * Type:     function<br>
 * Name:     img_upload_button<br>
 * Input:<br>
 *           - fld_name       フィールド名
 *           - now_img_dir   現在の画像ファイルのupfile配下のフォルダのURLパス（最後スラッシュ有り）
 */
function smarty_function_img_upload_button($params, &$smarty)
{
    require_once $smarty->_get_plugin_filepath('shared','escape_special_chars');

    $fld_name = null;


    foreach($params as $_key => $_val) {
        switch($_key) {
            case 'fld_name':
                $$_key = (string)$_val;
                break;

        }
    }

    if (!isset($fld_name))
        return '';

    $_html_result = '';

    $_SYSTEM_ROOT_URL = $smarty->_tpl_vars['_SYSTEM_ROOT_URL'];
    $disp_img_size_recs = $smarty->_tpl_vars['disp_img_size_recs'];
    $project_name_prefix = $smarty->_tpl_vars['project_name_prefix'];
    $page = $smarty->_tpl_vars['page'];
    $rand = $smarty->_tpl_vars['rand'];
    $img_fldtag_val = $smarty->_tpl_vars[$fld_name];
    $img_size = $smarty->_tpl_vars[$fld_name.'_size'];
    $tmp_data = $smarty->_tpl_vars[$fld_name.'_tmp_data'];

    $_html_result .= '<p>'."\n";
    $_html_result .= '    <script>'."\n";
    $_html_result .= '    if( (msie>10)||(msie==0) ){'."\n";
    $_html_result .= '        document.write(\'<input type="file" name="'.$fld_name.'" style="display:none;" onChange="_picPost(\\\''.$fld_name.'\\\');"/>\');'."\n";
    $_html_result .= '        document.write(\'<input type="button" value="画像ファイル選択" class="comBtn01" onClick="_picSelect(\\\''.$fld_name.'\\\');" />\');'."\n";
    $_html_result .= '    }else{'."\n";
    $_html_result .= '        //IE10以下'."\n";
    $_html_result .= '        document.write(\'<iframe name="hid_frame1" src="./?page='.$page.'&exec=img_upload_space" width="0" height="0"></iframe>\');'."\n";
    $_html_result .= '        document.write(\'<input type="file" name="'.$fld_name.'" onChange="_picPost(\\\''.$fld_name.'\\\');">\');'."\n";
    $_html_result .= '        document.write(\'<input type="hidden" name="br" value="old" />\');'."\n";
    $_html_result .= '    }'."\n";
    $_html_result .= '    </script>'."\n";
    $_html_result .= '</p>'."\n";

    return $_html_result;

}


?>
