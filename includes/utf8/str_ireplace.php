<?php
/**
 * Strings::ireplace
 *
 * @copyright  (c) 2005 Harry Fuecks
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
 */
function _str_ireplace($search, $replace, $str, $count = null){
    if(!is_array($search)) {
        $slen = strlen($search);
        if($slen == 0) {
            return $str;
        }
        $lendif = strlen($replace) - strlen($search);
        $search = Strings::strtolower($search);
        $search = preg_quote($search);
        $lstr = Strings::strtolower($str);
        $i = 0;
        $matched = 0;
        while(preg_match('/(.*)'.$search.'/Us',$lstr, $matches)) {
            if($i === $count) {
                break;
            }
            $mlen = strlen($matches[0]);
            $lstr = substr($lstr, $mlen);
            $str = substr_replace($str, $replace, $matched+strlen($matches[1]), $slen);
            $matched += $mlen + $lendif;
            $i++;
        }
        return $str;
    } else {
        foreach(array_keys($search) as $k) {
            if(is_array($replace)) {
                if(array_key_exists($k,$replace)) {
                    $str = Strings::str_ireplace($search[$k], $replace[$k], $str, $count);
                } else {
                    $str = Strings::str_ireplace($search[$k], '', $str, $count);
                }
            } else {
                $str = Strings::str_ireplace($search[$k], $replace, $str, $count);
            }
        }
        return $str;
    }
}
?>