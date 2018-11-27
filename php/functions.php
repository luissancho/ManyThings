<?php
/*
* Friendly and readable debug variable printing
*/
function printVar($var, $echo = true)
{
    $print = '<pre>' . print_r($var, true) . '</pre>';

    if ($echo) {
        echo $print;
    } else {
        return $print;
    }

    return true;
}

/*
* Translation function for Smarty
*/
function _T($string, $item = 0)
{
    if (!is_object($GLOBALS['_LANGUAGES_'])) {
        return $string;
    }

    $GLOBALS['_LANGUAGES_']->loadCurrentTranslationTable();
    $translation = $GLOBALS['_LANGUAGES_']->getTranslation($string);

    if ($item > 0) {
        $elems = explode(',', $translation);
        if (count($elems) >= $item) {
            $translation = $elems[$item - 1];
        }
    }

    return $translation;
}

/*
* Deprecated
*/
function sqlString($value)
{
    return (isset($value) && $value !== 'NULL') ? addslashes($value) : 'NULL';
}

/*
* Deprecated
*/
function sqlInt($value)
{
    return (isset($value) && $value !== 'NULL') ? intval($value) : 'NULL';
}

/*
* Deprecated
*/
function sqlFloat($value)
{
    return (isset($value) && $value !== 'NULL') ? floatval($value) : 'NULL';
}
