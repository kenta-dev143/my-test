<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {html_checkboxes} function plugin
 *
 * File:       function.html_checkboxes.php<br>
 * Type:       function<br>
 * Name:       html_checkboxes<br>
 * Date:       24.Feb.2003<br>
 * Purpose:    Prints out a list of checkbox input types<br>
 * Input:<br>
 *           - name       (optional) - string default "checkbox"
 *           - values     (required) - array
 *           - options    (optional) - associative array
 *           - checked    (optional) - array default not set
 *           - separator  (optional) - ie <br> or &nbsp;
 *           - output     (optional) - the output next to each checkbox
 *           - assign     (optional) - assign the output as an array to this variable
 * Examples:
 * <pre>
 * {html_checkboxes values=$ids output=$names}
 * {html_checkboxes values=$ids name='box' separator='<br>' output=$names}
 * {html_checkboxes values=$ids checked=$checked separator='<br>' output=$names}
 * </pre>
 * @link http://smarty.php.net/manual/en/language.function.html.checkboxes.php {html_checkboxes}
 *      (Smarty online manual)
 * @author     Christopher Kvarme <christopher.kvarme@flashjab.com>
 * @author credits to Monte Ohrt <monte at ohrt dot com>
 * @version    1.0
 * @param array
 * @param Smarty
 * @return string
 * @uses smarty_function_escape_special_chars()
 */
function smarty_function_html_checkboxes($params, &$smarty)
{
    require_once $smarty->_get_plugin_filepath('shared','escape_special_chars');

    $name = 'checkbox';
    $values = null;
    $options = null;
    $selected = null;
    $separator = '';
    $labels = true;
    $output = null;
    $break_separator = '';             //2006/06/16 K-Creation Add
    $break_separator_interval = 1;     //2006/06/16 K-Creation Add

    $extra = '';

    foreach($params as $_key => $_val) {
        switch($_key) {
            case 'name':
            case 'separator':
                $$_key = $_val;
                break;

            case 'labels':
                $$_key = (bool)$_val;
                break;

            case 'options':
                $$_key = (array)$_val;
                break;

            case 'values':
            case 'output':
                $$_key = array_values((array)$_val);
                break;

            case 'checked':
            case 'selected':
                $selected = array_map('strval', array_values((array)$_val));
                break;

            case 'checkboxes':
                $smarty->trigger_error('html_checkboxes: the use of the "checkboxes" attribute is deprecated, use "options" instead', E_USER_WARNING);
                $options = (array)$_val;
                break;

            case 'assign':
                break;

            case 'break_separator':                //2006/06/16 K-Creation Add
                $$_key = $_val;                    //2006/06/16 K-Creation Add
                break;                            //2006/06/16 K-Creation Add

            case 'break_separator_interval':    //2006/06/16 K-Creation Add
                $$_key = $_val;                    //2006/06/16 K-Creation Add
                break;                            //2006/06/16 K-Creation Add

            default:
                if(!is_array($_val)) {
                    $extra .= ' '.$_key.'="'.smarty_function_escape_special_chars($_val).'"';
                } else {
                    $smarty->trigger_error("html_checkboxes: extra attribute '$_key' cannot be an array", E_USER_NOTICE);
                }
                break;
        }
    }

    if (!isset($options) && !isset($values))
        return ''; /* raise error here? */

    settype($selected, 'array');
    $_html_result = array();

    $cnt = 0; //2006/06/16 K-Creation Add
    if (isset($options)) {

        foreach ($options as $_key=>$_val){
            //$_html_result[] = smarty_function_html_checkboxes_output($name, $_key, $_val, $selected, $extra, $separator, $labels);
            //2006/06/16 K-Creation Mod
            $cnt++;

            $w_sep = $separator;
            if($break_separator!='' && $break_separator_interval!=''){
                if($cnt == $break_separator_interval){
                    $cnt = 0;
                    $w_sep = $break_separator;
                }
            }
            $_html_result[] = smarty_function_html_checkboxes_output($name, $_key, $_val, $selected, $extra, $w_sep, $labels);
        }

    } else {
        foreach ($values as $_i=>$_key) {
            $_val = isset($output[$_i]) ? $output[$_i] : '';
            //$_html_result[] = smarty_function_html_checkboxes_output($name, $_key, $_val, $selected, $extra, $separator, $labels);
            //2006/06/16 K-Creation Mod
            $cnt++;

            $w_sep = $separator;
            if($break_separator!='' && $break_separator_interval!=''){
                if($cnt == $break_separator_interval){
                    $cnt = 0;
                    $w_sep = $break_separator;
                }
            }
            $_html_result[] = smarty_function_html_checkboxes_output($name, $_key, $_val, $selected, $extra, $w_sep, $labels);
        }

    }

    if(!empty($params['assign'])) {
        $smarty->assign($params['assign'], $_html_result);
    } else {
        return implode("\n",$_html_result);
    }

}

function smarty_function_html_checkboxes_output($name, $value, $output, $selected, $extra, $separator, $labels) {
    $_output = '';
    if ($labels) $_output .= '<label>';
    $_output .= '<input type="checkbox" name="'
        . smarty_function_escape_special_chars($name) . '[]" value="'
        . smarty_function_escape_special_chars($value) . '"';

    if (in_array((string)$value, $selected)) {
        $_output .= ' checked="checked"';
    }
    $_output .= $extra . ' />' . $output;
    if ($labels) $_output .= '</label>';
    $_output .=  $separator;

    return $_output;
}

?>
