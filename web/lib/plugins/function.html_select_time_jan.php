<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {html_select_time_jan} function plugin
 *
 * Type:     function<br>
 * Name:     html_select_time_jan<br>
 * Purpose:  Prints the dropdowns for time selection
 * @link http://smarty.php.net/manual/en/language.function.html.select.time.php {html_select_time_jan}
 *          (Smarty online manual)
 * @param array
 * @param Smarty
 * @return string
 * @uses smarty_make_timestamp()
 */
function smarty_function_html_select_time_jan($params, &$smarty)
{
    require_once $smarty->_get_plugin_filepath('shared','make_timestamp');
    require_once $smarty->_get_plugin_filepath('function','html_options');
    /* Default values. */
    $prefix             = "Time_";
    $time               = time();
    $display_hours      = true;
    $display_minutes    = true;
    $display_seconds    = true;
    $display_meridian   = true;
    $use_24_hours       = true;
    $minute_interval    = 1;
    $second_interval    = 1;

    $all_empty       = null;
    $hour_empty       = null;
    $minute_empty     = null;
    $second_empty      = null;

    /* Should the select boxes be part of an array when returned from PHP?
       e.g. setting it to "birthday", would create "birthday[Hour]",
       "birthday[Minute]", "birthday[Seconds]" & "birthday[Meridian]".
       Can be combined with prefix. */
    $field_array        = null;
    $all_extra          = null;
    $hour_extra         = null;
    $minute_extra       = null;
    $second_extra       = null;
    $meridian_extra     = null;

    foreach ($params as $_key=>$_value) {
        switch ($_key) {
            case 'prefix':
            case 'time':
            case 'field_array':
            case 'all_extra':
            case 'hour_extra':
            case 'minute_extra':
            case 'second_extra':
            case 'meridian_extra':
            case 'hour_empty':
            case 'minute_empty':
            case 'second_empty':
                $$_key = (string)$_value;
                break;

            case 'all_empty':
                $$_key = (string)$_value;
                $second_empty = $minute_empty = $hour_empty = $all_empty;
                break;

            case 'display_hours':
            case 'display_minutes':
            case 'display_seconds':
            case 'display_meridian':
            case 'use_24_hours':
                $$_key = (bool)$_value;
                break;

            case 'minute_interval':
            case 'second_interval':
                $$_key = (int)$_value;
                break;

            default:
                $smarty->trigger_error("[html_select_time_jan] unknown parameter $_key", E_USER_WARNING);
        }
    }

    if($time==""){
        if($hour_empty!==null || $minute_empty!==null || $second_empty!==null) {
            //空要素ある
            $time = "";
        }else{
            //空要素ない
            $time = time();
            $time = smarty_make_timestamp($time);
        }
    }else{
        $time = smarty_make_timestamp($time);
    }

    $html_result = '';

    if ($display_hours) {
        $hours=array();
        if($hour_empty!==null) {
            $hours[''] = $hour_empty;
        }
        $w_hours       = $use_24_hours ? range(0, 23) : range(1, 12);
        $hours = array_merge($hours,$w_hours);

        if($hour_empty!==null) {
            $w_cnt = count($hours) - 1;
        }else{
            $w_cnt = count($hours);
        }

        $hour_fmt = $use_24_hours ? '%H' : '%I';
        for ($i = 0, $for_max = $w_cnt; $i < $for_max; $i++)
            $hours[$i] = sprintf('%02d', $hours[$i]);
        $html_result .= '<select name=';
        if (null !== $field_array) {
            $html_result .= '"' . $field_array . '[' . $prefix . 'Hour]"';
        } else {
            $html_result .= '"' . $prefix . 'Hour"';
        }
        if (null !== $hour_extra){
            $html_result .= ' ' . $hour_extra;
        }
        if (null !== $all_extra){
            $html_result .= ' ' . $all_extra;
        }
        $html_result .= '>'."\n";

        if($time=="") {
            $html_result .= smarty_function_html_options(array('output'          => $hours,
                                                               'values'          => $hours,
                                                               'selected'      => '',
                                                               'print_result' => false),
                                                         $smarty);
        }else{
            $html_result .= smarty_function_html_options(array('output'          => $hours,
                                                               'values'          => $hours,
                                                               'selected'      => strftime($hour_fmt, $time),
                                                               'print_result' => false),
                                                         $smarty);
        }

        $html_result .= "</select>時\n";
    }

    if ($display_minutes) {
        $all_minutes = range(0, 59);
        if($minute_empty!==null) {
            $minutes[''] = $minute_empty;
        }
        for ($i = 0, $for_max = count($all_minutes); $i < $for_max; $i+= $minute_interval)
            $minutes[] = sprintf('%02d', $all_minutes[$i]);
        //$selected = intval(floor(strftime('%M', $time) / $minute_interval) * $minute_interval);
        //2008/09/10 mod
        //$selected = sprintf('%02d', intval(floor(strftime('%M', $time) / $minute_interval) * $minute_interval));
        //2010/03/11 mod ------ Strat ------
        if($time!=""){
            $selected = sprintf('%02d', intval(floor(strftime('%M', $time) / $minute_interval) * $minute_interval));
        }
        //2010/03/11 mod ------ End ------

        $html_result .= '<select name=';
        if (null !== $field_array) {
            $html_result .= '"' . $field_array . '[' . $prefix . 'Minute]"';
        } else {
            $html_result .= '"' . $prefix . 'Minute"';
        }
        if (null !== $minute_extra){
            $html_result .= ' ' . $minute_extra;
        }
        if (null !== $all_extra){
            $html_result .= ' ' . $all_extra;
        }
        $html_result .= '>'."\n";

        if($time=="") {
            $html_result .= smarty_function_html_options(array('output'          => $minutes,
                                                               'values'          => $minutes,
                                                               'selected'      => '',
                                                               'print_result' => false),
                                                         $smarty);
        }else{
            $html_result .= smarty_function_html_options(array('output'          => $minutes,
                                                               'values'          => $minutes,
                                                               'selected'      => $selected,
                                                               'print_result' => false),
                                                         $smarty);
        }

        $html_result .= "</select>分\n";
    }

    if ($display_seconds) {
        $all_seconds = range(0, 59);
        if($second_empty!==null) {
            $seconds[''] = $second_empty;
        }
        for ($i = 0, $for_max = count($all_seconds); $i < $for_max; $i+= $second_interval)
            $seconds[] = sprintf('%02d', $all_seconds[$i]);
        $selected = intval(floor(strftime('%S', $time) / $second_interval) * $second_interval);
        $html_result .= '<select name=';
        if (null !== $field_array) {
            $html_result .= '"' . $field_array . '[' . $prefix . 'Second]"';
        } else {
            $html_result .= '"' . $prefix . 'Second"';
        }

        if (null !== $second_extra){
            $html_result .= ' ' . $second_extra;
        }
        if (null !== $all_extra){
            $html_result .= ' ' . $all_extra;
        }
        $html_result .= '>'."\n";

        if($time=="") {
            $html_result .= smarty_function_html_options(array('output'          => $seconds,
                                                               'values'          => $seconds,
                                                               'selected'      => '',
                                                               'print_result' => false),
                                                         $smarty);
        }else{
            $html_result .= smarty_function_html_options(array('output'          => $seconds,
                                                               'values'          => $seconds,
                                                               'selected'      => $selected,
                                                               'print_result' => false),
                                                         $smarty);
        }
        $html_result .= "</select>秒\n";
    }

    if ($display_meridian && !$use_24_hours) {
        $html_result .= '<select name=';
        if (null !== $field_array) {
            $html_result .= '"' . $field_array . '[' . $prefix . 'Meridian]"';
        } else {
            $html_result .= '"' . $prefix . 'Meridian"';
        }

        if (null !== $meridian_extra){
            $html_result .= ' ' . $meridian_extra;
        }
        if (null !== $all_extra){
            $html_result .= ' ' . $all_extra;
        }
        $html_result .= '>'."\n";

        $html_result .= smarty_function_html_options(array('output'          => array('AM', 'PM'),
                                                           'values'          => array('am', 'pm'),
                                                           'selected'      => strtolower(strftime('%p', $time)),
                                                           'print_result' => false),
                                                     $smarty);
        $html_result .= "</select>\n";
    }

    return $html_result;
}

/* vim: set expandtab: */

?>
