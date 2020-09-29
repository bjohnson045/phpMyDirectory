<?php
/**
 * Strings::strpos
 *
 * @copyright  (c) 2005 Harry Fuecks
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
 */
function _strpos($str, $needle, $offset = NULL) {
    if(is_null($offset)) {
        $ar = explode($needle, $str, 2);
        if(count($ar) > 1) {
            return Strings::strlen($ar[0]);
        }
        return FALSE;
    } else {
        if(!is_int($offset)) {
            trigger_error('Strings::strpos: Offset must be an integer',E_USER_ERROR);
            return FALSE;
        }

        $str = Strings::substr($str, $offset);

        if(FALSE !== ($pos = Strings::strpos($str, $needle))) {
            return $pos + $offset;
        }
        return FALSE;
    }
}
?>