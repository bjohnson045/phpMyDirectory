<?php
/**
 * Strings::str_pad
 *
 * @copyright  (c) 2005 Harry Fuecks
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
 */
function _str_pad($str, $final_str_length, $pad_str = ' ', $pad_type = STR_PAD_RIGHT) {
    if(Strings::is_ascii($str) AND Strings::is_ascii($pad_str)) {
        return str_pad($str, $final_str_length, $pad_str, $pad_type);
    }

    $str_length = Strings::strlen($str);

    if($final_str_length <= 0 OR $final_str_length <= $str_length) {
        return $str;
    }

    $pad_str_length = Strings::strlen($pad_str);
    $pad_length = $final_str_length - $str_length;

    if($pad_type == STR_PAD_RIGHT) {
        $repeat = ceil($pad_length / $pad_str_length);
        return Strings::substr($str.str_repeat($pad_str, $repeat), 0, $final_str_length);
    }

    if($pad_type == STR_PAD_LEFT) {
        $repeat = ceil($pad_length / $pad_str_length);
        return Strings::substr(str_repeat($pad_str, $repeat), 0, floor($pad_length)).$str;
    }

    if($pad_type == STR_PAD_BOTH) {
        $pad_length /= 2;
        $pad_length_left = floor($pad_length);
        $pad_length_right = ceil($pad_length);
        $repeat_left = ceil($pad_length_left / $pad_str_length);
        $repeat_right = ceil($pad_length_right / $pad_str_length);

        $pad_left = Strings::substr(str_repeat($pad_str, $repeat_left), 0, $pad_length_left);
        $pad_right = Strings::substr(str_repeat($pad_str, $repeat_right), 0, $pad_length_right);
        return $pad_left.$str.$pad_right;
    }

    return false;
}
?>