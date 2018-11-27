<?php
use ManyThings\Core\Utils;
use ManyThings\Services\SiteService;

/*
* Build Smarty modifier
* Shows assets cache path
*/
function buildSmartyFunction($params)
{
    $revPath = ABSPATH . 'resources/build/rev-manifest.json';
    $file = $params['file'] ?: '';

    if ($file && file_exists($revPath)) {
        $manifest = json_decode(file_get_contents($revPath), true);
        if ($manifest && array_key_exists($file, $manifest)) {
            if (!empty($params['embed'])) {
                return file_get_contents(ABSPATH . 'resources/build/' . $manifest[$file]);
            } else {
                return $manifest[$file];
            }
        }
    }

    return $file;
}

/*
* Number Smarty modifier
* Shows number values in proper way
*/
function numberSmartyModifier($value, $dec = null, $decForce = true)
{
    return Utils::numberToString($value, $dec, $decForce);
}

/*
* Currency Smarty modifier
* Shows currency values in proper way
*/
function currencySmartyModifier($value, $country = null)
{
    return SiteService::formatCurrency($value, $country);
}
