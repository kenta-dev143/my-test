<?php
/**
 * Smarty {img_upload_disp_area} function plugin
 *
 * Type:     function<br>
 * Name:     img_upload_disp_area<br>
 * Input:<br>
 *           - fld_name       フィールド名
 *           - now_img_dir   現在の画像ファイルのupfile配下のフォルダのURLパス（最後スラッシュ有り）
 */
function smarty_function_img_upload_disp_area($params, &$smarty)
{
    require_once $smarty->_get_plugin_filepath('shared','escape_special_chars');

    $fld_name = null;
    $now_img_dir = null;


    foreach($params as $_key => $_val) {
        switch($_key) {
            case 'fld_name':
                $$_key = (string)$_val;
                break;

            case 'now_img_dir':
                $$_key = (string)$_val;
                break;

        }
    }

    if (!isset($fld_name) || !isset($now_img_dir))
        return '';

    $_html_result = '';

    $_SYSTEM_ROOT_URLS = $smarty->_tpl_vars['_SYSTEM_ROOT_URLS'];
    $disp_img_size_recs = $smarty->_tpl_vars['disp_img_size_recs'];
    $project_name_prefix = $smarty->_tpl_vars['project_name_prefix'];
    $page = $smarty->_tpl_vars['page'];
    $_session_path = $smarty->_tpl_vars['_session_path'];
    $rand = $smarty->_tpl_vars['rand'];
    $img_fldtag_val = $smarty->_tpl_vars[$fld_name];
    $img_size = $smarty->_tpl_vars[$fld_name.'_size'];
    $tmp_data = $smarty->_tpl_vars[$fld_name.'_tmp_data'];
    $img_del = $smarty->_tpl_vars[$fld_name.'_del'];

    $_html_result .= '<p style="min-height:'.$disp_img_size_recs[$fld_name]['h'].'px;position: relative;">';
    if( $tmp_data != "" && $img_del!="1"){
        $_html_result .= '<img id="'.$fld_name.'_tag" height="'.$disp_img_size_recs[$fld_name]['h'].'" src="'.$_SYSTEM_ROOT_URLS.'/lib/tmpimgdisp.php?project_name_prefix='.$project_name_prefix.'&page='.$page.'&session_path='.$_session_path.'&id='.$fld_name.'&rand='.$rand.'" '.$img_size.'><br>';
    }elseif( $img_fldtag_val != "" && $img_del!="1"){
        $_html_result .= '<img id="'.$fld_name.'_tag" height="'.$disp_img_size_recs[$fld_name]['h'].'" src="'.$now_img_dir.$img_fldtag_val.'?rand='.$rand.'"><br>';
    }else{
        $_html_result .= '<img id="'.$fld_name.'_tag" height="'.$disp_img_size_recs[$fld_name]['h'].'" width="'.$disp_img_size_recs[$fld_name]['w'].'" src="'.$_SYSTEM_ROOT_URLS.'/lib/img_upload/img/img_dummy.jpg">';
    }    
    $_html_result .= '<img src="'.$_SYSTEM_ROOT_URLS.'/lib/img_upload/img/loading.gif" id="'.$fld_name.'_loading_gif" style="display:none;position:absolute;left:32px;top:32px;">';

    //2017/11/24 mod ---------- Before -----------
    // if( ($tmp_data != "" || $img_fldtag_val != "")  && $img_del!="1"){
    //     $_html_result .= '<input type="button" value="削除" style="position:absolute;left:5px;top:5px;" id="'.$fld_name.'_img_del_btn" onClick="_picDel(\''.$fld_name.'\');" />';
    // }
    //2017/11/24 mod ---------- After -----------
    $disp_none = "display:none;";
    if( ($tmp_data != "" || $img_fldtag_val != "")  && $img_del!="1"){
        $disp_none = "";
    }
    $_html_result .= '<input type="button" value="削除" style="position:absolute;left:5px;top:5px;'.$disp_none.'" id="'.$fld_name.'_img_del_btn" onClick="_picDel(\''.$fld_name.'\');" />';
    //2017/11/24 mod ---------- End -----------

    $_html_result .= '</p>';
    $_html_result .= '<p class="txtError" id="'.$fld_name.'_err" style="text-align:left;display:none;"></p>';

    return $_html_result;

}


?>