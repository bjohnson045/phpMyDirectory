<?php
if(!defined('IN_PMD')) exit();

function format_number($number) {
    global $PMDR;
    // Remove any extra formatting we might have as this will break number_format
    $number = floatval(preg_replace('/[^\d\.]+/','',$number));
    // Format the number according to the settings
    return number_format($number,$PMDR->getLanguage('decimalplaces'),$PMDR->getLanguage('decimalseperator'),$PMDR->getLanguage('thousandseperator'));
}

function format_number_currency($number) {
    global $PMDR;
    return $PMDR->getLanguage('currency_prefix').format_number($number).$PMDR->getLanguage('currency_suffix');
}

function format_number_currency_input($number) {
    global $PMDR;
    $decimal_places = intval($PMDR->getLanguage('decimalplaces'));
    $number = preg_replace('/[^\d]+/','',$number);
    for($x = 0; $x < $decimal_places; $x++) {
        $number *= 0.1;
    }
    return $number;
}
?>