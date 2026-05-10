<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Include the {@link shared.make_timestamp.php} plugin
 */
require_once $smarty->_get_plugin_filepath('shared','make_timestamp');
/**
 * Smarty date_format modifier plugin
 *
 * Type:     modifier<br>
 * Name:     date_format<br>
 * Purpose:  format datestamps via strftime<br>
 * Input:<br>
 *         - string: input date string
 *         - format: strftime format for output
 *         - default_date: default date if $string is empty
 * @link http://smarty.php.net/manual/en/language.modifier.date.format.php
 *          date_format (Smarty online manual)
 * @param string
 * @param string
 * @param string
 * @return string|void
 * @uses smarty_make_timestamp()
 */
function smarty_modifier_date_format($string, $format="%b %e, %Y", $default_date=null)
{
    //[K-Cre MOD] msec remove
    if(strpos($string,".")!==FALSE){
        // . find
        $_fld = explode(".",$string);
        $string = $_fld[0];
    }

    if (substr(PHP_OS,0,3) == 'WIN') {
           $_win_from = array ('%e',  '%T',       '%D');
           $_win_to   = array ('%#d', '%H:%M:%S', '%m/%d/%y');
           $format = str_replace($_win_from, $_win_to, $format);
    }
    if($string != '') {
        //return strftime($format, smarty_make_timestamp($string));
        //[K-Cre MOD]
        //return mb_convert_encoding(strftime($format, smarty_make_timestamp($string)),_ENCODING_SRC,"auto");
        //[K-Cre MOD] Internal版として修正 -------- Strat --------
        //$_kanji_from = array ('年','月','日','時','分','秒');
        //$_kanji_to   = array ('___DMY_Y___', '___DMY_M___','___DMY_D___','___DMY_H___','___DMY_I___','___DMY_S___');
        //[K-Cre MOD] 2010/02/12 %kで漢字曜日１文字変換機能追加 ------ Strat ----
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
        //[K-Cre MOD] 2010/02/12 %kで漢字曜日１文字変換機能追加 ------ End ----


        $_none_kanji_format = str_replace($_kanji_from, $_kanji_to, $format);
        $_none_kanji_ret =  strftime($_none_kanji_format, smarty_make_timestamp($string));
        return str_replace($_kanji_to, $_kanji_from,$_none_kanji_ret);
        //[K-Cre MOD] Internal版として修正 -------- End --------
    } elseif (isset($default_date) && $default_date != '') {
        //return strftime($format, smarty_make_timestamp($default_date));
        //[K-Cre MOD]
        //return mb_convert_encoding(strftime($format, smarty_make_timestamp($default_date)),_ENCODING_SRC,"auto");
        //[K-Cre MOD] Internal版として修正 -------- Strat --------
        //$_kanji_from = array ('年','月','日','時','分','秒');
        //$_kanji_to   = array ('___DMY_Y___', '___DMY_M___','___DMY_D___','___DMY_H___','___DMY_I___','___DMY_S___');
        //[K-Cre MOD] 2010/02/12 %kで漢字曜日１文字変換機能追加 ------ Strat ----
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
        //[K-Cre MOD] 2010/02/12 %kで漢字曜日１文字変換機能追加 ------ End ----

        $_none_kanji_format = str_replace($_kanji_from, $_kanji_to, $format);
        $_none_kanji_ret =  strftime($_none_kanji_format, smarty_make_timestamp($default_date));
        return str_replace($_kanji_to, $_kanji_from,$_none_kanji_ret);
        //[K-Cre MOD] Internal版として修正 -------- End --------
    } else {
        return;
    }
}

/* vim: set expandtab: */

?>
