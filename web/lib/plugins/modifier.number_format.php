<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty number_format modifier plugin
 *
 * Type:     modifier<br>
 * Name:     number_format<br>
 * Purpose:  format number via number_format
 * @param string or integer
 * @return string
 */
function smarty_modifier_number_format($string)
{
    return number_format(intval($string));
}

/* vim: set expandtab: */

?>
