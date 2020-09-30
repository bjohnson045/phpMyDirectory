<?php
/**
 * Strings::from_unicode
 *
 * The Original Code is Mozilla Communicator client code.
 * The Initial Developer of the Original Code is Netscape Communications Corporation.
 * Portions created by the Initial Developer are Copyright (C) 1998 the Initial Developer.
 * Ported to PHP by Henri Sivonen <hsivonen@iki.fi>, see http://hsivonen.iki.fi/php-utf8/
 * Slight modifications to fit with phputf8 library by Harry Fuecks <hfuecks@gmail.com>.
 *
 * @copyright  (c) 2005 Harry Fuecks
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
 */
function _from_unicode($arr) {
    ob_start();

    foreach (array_keys($arr) as $k) {
        # ASCII range (including control chars)
        if(($arr[$k] >= 0) && ($arr[$k] <= 0x007f)) {
            echo chr($arr[$k]);
        # 2 byte sequence
        } else if ($arr[$k] <= 0x07ff) {
            echo chr(0xc0 | ($arr[$k] >> 6));
            echo chr(0x80 | ($arr[$k] & 0x003f));
        # Byte order mark (skip)
        } else if($arr[$k] == 0xFEFF) {
            // nop -- zap the BOM
        # Test for illegal surrogates
        } else if ($arr[$k] >= 0xD800 && $arr[$k] <= 0xDFFF) {
            // found a surrogate
            trigger_error('_from_unicode: Illegal surrogate at index: '.$k.', value: '.$arr[$k],E_USER_WARNING);
            return false;
        # 3 byte sequence
        } else if ($arr[$k] <= 0xffff) {
            echo chr(0xe0 | ($arr[$k] >> 12));
            echo chr(0x80 | (($arr[$k] >> 6) & 0x003f));
            echo chr(0x80 | ($arr[$k] & 0x003f));
        # 4 byte sequence
        } else if ($arr[$k] <= 0x10ffff) {
            echo chr(0xf0 | ($arr[$k] >> 18));
            echo chr(0x80 | (($arr[$k] >> 12) & 0x3f));
            echo chr(0x80 | (($arr[$k] >> 6) & 0x3f));
            echo chr(0x80 | ($arr[$k] & 0x3f));
        } else {
            trigger_error('Strings::from_unicode: Codepoint out of Unicode range at index: '.$k.', value: '.$arr[$k],E_USER_WARNING);
            // out of range
            return false;
        }
    }

    $result = ob_get_contents();
    ob_end_clean();
    return $result;
}
?>