<?php
/**
 * Strings::substr
 *
 * @copyright  (c) 2005 Harry Fuecks
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
 */
function _substr($str, $offset, $length = null) {
    $str = (string)$str;
    $offset = (int)$offset;
    if(!is_null($length)) {
        $length = (int)$length;
    }

    if($length === 0) {
        return '';
    }
    if($offset < 0 && $length < 0 && $length < $offset) {
        return '';
    }

    if($offset < 0) {
        $strlen = strlen(utf8_decode($str));
        $offset = $strlen + $offset;
        if($offset < 0) {
            $offset = 0;
        }
    }

    $Op = '';
    $Lp = '';

    if($offset > 0) {
        $Ox = (int)($offset/65535);
        $Oy = $offset%65535;
        if($Ox) {
            $Op = '(?:.{65535}){'.$Ox.'}';
        }
        $Op = '^(?:'.$Op.'.{'.$Oy.'})';
    } else {
        $Op = '^';
    }

    if(is_null($length)) {
        $Lp = '(.*)$';
    } else {
        if(!isset($strlen)) {
            $strlen = strlen(utf8_decode($str));
        }
        if($offset > $strlen) {
            return '';
        }
        if($length > 0) {
            $length = min($strlen-$offset, $length);
            $Lx = (int)($length / 65535);
            $Ly = $length % 65535;
            if($Lx) {
                $Lp = '(?:.{65535}){'.$Lx.'}';
            }
            $Lp = '('.$Lp.'.{'.$Ly.'})';
        } else if($length < 0) {
            if($length < ($offset - $strlen)) {
                return '';
            }
            $Lx = (int)((-$length)/65535);
            $Ly = (-$length)%65535;
            if($Lx) {
                $Lp = '(?:.{65535}){'.$Lx.'}';
            }
            $Lp = '(.*)(?:'.$Lp.'.{'.$Ly.'})$';
        }
    }

    if(!preg_match( '#'.$Op.$Lp.'#us',$str, $match )) {
        return '';
    }
    return $match[1];
}
?>