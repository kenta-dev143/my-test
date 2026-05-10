<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty autolink modifier plugin
 *
 * Type:     modifier<br>
 * Name:     autolink<br>
 * Purpose:  autolink
 * @link http://smarty.php.net/manual/en/language.modifier.autolink.php
 *          autolink (Smarty online manual)
 * @param string
 * @return string
 */
function smarty_modifier_autolink($string)
{
    return _AutoLink($string);
}

?>
