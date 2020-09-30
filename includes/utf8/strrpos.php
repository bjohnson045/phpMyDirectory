<?php
/**
 * Strings::strrpos
 *
 * @copyright  (c) 2005 Harry Fuecks
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
 */
function _strrpos($str, $needle, $offset = null) {
    if (is_null($offset)) {
        $ar = explode($needle, $str);
        if (count($ar) > 1) {
            array_pop($ar);
            $str = join($needle,$ar);
            return Strings::strlen($str);
        }
        return false;
    } else {
        if (!is_int($offset)) {
            trigger_error('Strings::strrpos expects parameter 3 to be long',E_USER_WARNING);
            return FALSE;
        }

        $str = Strings::substr($str, $offset);

        if(false !==($pos = Strings::strrpos($str, $needle))) {
            return $pos + $offset;
        }
        return FALSE;
    }
}
?>