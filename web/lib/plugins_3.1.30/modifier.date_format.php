<?php
/**
 * Smarty plugin
 *
 * @package    Smarty
 * @subpackage PluginsModifier
 */

/**
 * Smarty date_format modifier plugin
 * Type:     modifier<br>
 * Name:     date_format<br>
 * Purpose:  format datestamps via strftime<br>
 * Input:<br>
 *          - string: input date string
 *          - format: strftime format for output
 *          - default_date: default date if $string is empty
 *
 * @link   http://www.smarty.net/manual/en/language.modifier.date.format.php date_format (Smarty online manual)
 * @author Monte Ohrt <monte at ohrt dot com>
 *
 * @param string $string       input date string
 * @param string $format       strftime format for output
 * @param string $default_date default date if $string is empty
 * @param string $formatter    either 'strftime' or 'auto'
 *
 * @return string |void
 * @uses   smarty_make_timestamp()
 */
function smarty_modifier_date_format($string, $format = null, $default_date = '', $formatter = 'auto')
{
    if ($format === null) {
        $format = Smarty::$_DATE_FORMAT;
    }
    /**
     * require_once the {@link shared.make_timestamp.php} plugin
     */
    require_once(SMARTY_PLUGINS_DIR . 'shared.make_timestamp.php');
    if ($string != '' && $string != '0000-00-00' && $string != '0000-00-00 00:00:00') {
        //[K-Cre] 2017/03/10 mod ------------- Before ------------------
        //$timestamp = smarty_make_timestamp($string);
        //[K-Cre] 2017/03/10 mod ------------- After ------------------
        if(strpos($format,"%k")!==FALSE){
            $_weekday = date("l",strtotime($string));
            switch( $_weekday ){
                case "Monday": $_retweekday = "月"; break;
                case "Tuesday": $_retweekday = "火"; break;
                case "Wednesday": $_retweekday = "水"; break;
                case "Thursday": $_retweekday = "木"; break;
                case "Friday": $_retweekday = "金"; break;
                case "Saturday": $_retweekday = "土"; break;
                case "Sunday": $_retweekday = "日"; break;
            }
            $format = str_replace("%k",$_retweekday,$format);
        }
        $_kanji_from = array ('年','月','日','時','分','秒','火','水','木','金','土');
        $_kanji_to   = array ('___DMY_Y___', '___DMY_M___','___DMY_D___','___DMY_H___','___DMY_I___','___DMY_S___','___WDAY_TUE___','___WDAY_WED___','___WDAY_THU___','___WDAY_FRI___','___WDAY_SAT___');

        $_none_kanji_format = str_replace($_kanji_from, $_kanji_to, $format);
        $_none_kanji_ret =  strftime($_none_kanji_format, smarty_make_timestamp($string));
        return str_replace($_kanji_to, $_kanji_from,$_none_kanji_ret);
        //[K-Cre] 2017/03/10 mod ------------- End ------------------

    } elseif ($default_date != '') {
        //[K-Cre] 2017/03/10 mod ------------- Before ------------------
        //$timestamp = smarty_make_timestamp($default_date);
        //[K-Cre] 2017/03/10 mod ------------- After ------------------
        if(strpos($format,"%k")!==FALSE){
            $_weekday = date("l",strtotime($default_date));
            switch( $_weekday ){
                case "Monday": $_retweekday = "月"; break;
                case "Tuesday": $_retweekday = "火"; break;
                case "Wednesday": $_retweekday = "水"; break;
                case "Thursday": $_retweekday = "木"; break;
                case "Friday": $_retweekday = "金"; break;
                case "Saturday": $_retweekday = "土"; break;
                case "Sunday": $_retweekday = "日"; break;
            }
            $format = str_replace("%k",$_retweekday,$format);
        }
        $_kanji_from = array ('年','月','日','時','分','秒','火','水','木','金','土');
        $_kanji_to   = array ('___DMY_Y___', '___DMY_M___','___DMY_D___','___DMY_H___','___DMY_I___','___DMY_S___','___WDAY_TUE___','___WDAY_WED___','___WDAY_THU___','___WDAY_FRI___','___WDAY_SAT___');
        
        $_none_kanji_format = str_replace($_kanji_from, $_kanji_to, $format);
        $_none_kanji_ret =  strftime($_none_kanji_format, smarty_make_timestamp($default_date));
        return str_replace($_kanji_to, $_kanji_from,$_none_kanji_ret);
        //[K-Cre] 2017/03/10 mod ------------- End ------------------

    } else {
        return;
    }
    if ($formatter == 'strftime' || ($formatter == 'auto' && strpos($format, '%') !== false)) {
        if (DS == '\\') {
            $_win_from = array('%D', '%h', '%n', '%r', '%R', '%t', '%T');
            $_win_to = array('%m/%d/%y', '%b', "\n", '%I:%M:%S %p', '%H:%M', "\t", '%H:%M:%S');
            if (strpos($format, '%e') !== false) {
                $_win_from[] = '%e';
                $_win_to[] = sprintf('%\' 2d', date('j', $timestamp));
            }
            if (strpos($format, '%l') !== false) {
                $_win_from[] = '%l';
                $_win_to[] = sprintf('%\' 2d', date('h', $timestamp));
            }
            $format = str_replace($_win_from, $_win_to, $format);
        }

        return strftime($format, $timestamp);
    } else {
        return date($format, $timestamp);
    }
}
