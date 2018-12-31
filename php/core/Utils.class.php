<?php

namespace ManyThings\Core;

use Exception;
use PHPMailer;

class Utils extends Core
{
    public static function stripslashesDeep($mixed)
    {
        $mixed = is_array($mixed) ? array_map([self, 'stripslashesDeep'], $mixed) : stripslashes($mixed);

        return $mixed;
    }

    public static function isNumber($string)
    {
        $number = self::getDI()->config->number;

        if ($number->thou_sep != '') {
            $string = str_replace($number->thou_sep, '', $string);
        }
        if ($number->dec_point != '.') {
            $string = str_replace($number->dec_point, '.', $string);
        }

        return is_numeric($string);
    }

    public static function isJson($string)
    {
        return is_string($string) && is_array(json_decode($string, true)) && json_last_error() == JSON_ERROR_NONE;
    }

    public static function isHash($string)
    {
        return preg_match('#^[a-f0-9]{24}$#i', $string);
    }

    public static function formatCurrency($value, $dec = null, $decForce = true)
    {
        $currency = self::getDI()->config->currency;

        $value = self::numberToString($value, $dec, $decForce);
        $formatted = str_replace('%s', $currency->symbol, $currency->format);
        $value = str_replace('%v', $value, $formatted);

        return $value;
    }

    public static function stringToNumber($value)
    {
        $number = self::getDI()->config->number;

        if ($number->thou_sep != '') {
            $value = str_replace($number->thou_sep, '', $value);
        }
        if ($number->dec_point != '.') {
            $value = str_replace($number->dec_point, '.', $value);
        }

        if (preg_match('#([0-9\.\-]+)#', $value, $match)) {
            $value = floatval($match[0]);
        } else {
            $value = floatval($value);
        }

        return $value;
    }

    /*
     * $dec: max number of decimal digits
     * $decForce: force max decimals if not integer
     */
    public static function numberToString($value, $dec = null, $decForce = true)
    {
        $number = self::getDI()->config->number;

        if (is_null($dec)) {
            $dec = $number->decimals;
        }

        $parts = explode('.', $value);

        $sign = ($value < 0) ? '-' : '';
        $integer = number_format(floatval(ltrim($parts[0], '-')), 0, $number->dec_point, $number->thou_sep);

        $decimal = (isset($parts[1])) ? rtrim($parts[1], '0') : '';
        $decimal = substr($decimal, 0, $dec);
        $decimal = ($decimal != '' && $decForce) ? str_pad($decimal, $dec, '0', STR_PAD_RIGHT) : $decimal;

        $value = $sign . $integer;
        $value .= ($decimal != '') ? ',' . $decimal : '';

        return $value;
    }

    public static function bytesToString($value, $dec = 2)
    {
        $sufList = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $sufIndex = floor((strlen($value) - 1) / 3);

        $size = $value / pow(1024, $sufIndex);
        $value = number_format($size, $dec, ',', '.');
        $value .= ' ' . $sufList[$sufIndex];

        return $value;
    }

    public static function getDirectorySize($path)
    {
        $dirs = [];
        $bytes = 0;

        $dirs[] = $path;
        while (count($dirs) > 0) {
            $dir = array_shift($dirs);

            $dh = @opendir($dir);
            while (false !== ($file = @readdir($dh))) {
                $filePath = $dir . '/' . $file;

                if (strpos($file, '.') !== 0) {
                    if (!@is_dir($filePath)) {
                        $bytes += @filesize($filePath);
                    } else {
                        $dirs[] = $filePath;
                    }
                }
            }
            @closedir($dh);
        }

        return $bytes;
    }

    public static function jsonToText($json)
    {
        $array = json_decode($json, true);
        $text = self::arrayToText($array);

        return $text;
    }

    public static function arrayToText($array, $prefix = '', $html = true)
    {
        $eol = ($html) ? '<br />' : PHP_EOL;
        $tab = ($html) ? '&nbsp;&nbsp;&nbsp;&nbsp;' : '    ';
        $text = '';

        foreach ($array as $key => $val) {
            if (is_array($val)) {
                $text .= $prefix . '[' . $key . ']' . $eol;
                $text .= self::arrayToText($val, $prefix . $tab);
            } else {
                $text .= $prefix . '[' . $key . '] ' . $val . $eol;
            }
        }

        return $text;
    }

    public static function linesToArray($string)
    {
        return self::arrayTrim(explode(PHP_EOL, $string));
    }

    public static function arrayTrim($array)
    {
        $newArray = [];

        foreach ($array as $key => $value) {
            $newArray[$key] = trim($value);
        }

        return $newArray;
    }

    public static function snakeToCamelCase($val)
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $val)));
    }

    public static function snakeToWords($val)
    {
        return ucwords(str_replace('_', ' ', $val));
    }

    public static function formatExcel($val)
    {
        return iconv('UTF-8', 'Windows-1252', strip_tags($val));
    }

    public static function uploadFile($inFile, $outFile)
    {
        return copy($inFile, $outFile);
    }

    protected static function setMemoryForImage($file, $factor = 2)
    {
        $image = @getimagesize($file);

        $width = $image[0];
        $height = $image[1];
        $bits = $image['bits'] ?: 8;
        $channels = $image['channels'] ?: 3;

        $mb = pow(2, 20);
        $kb = pow(2, 10);
        $curLimit = @ini_get('memory_limit') * $mb;
        $curUsage = @memory_get_usage();

        $needed = round((($width * $height * $bits * $channels / 8) + (64 * $kb)) * $factor);

        if (($curUsage + $needed) > $curLimit) {
            $newLimit = ceil(($curUsage + $needed) / $mb);
            @ini_set('memory_limit', $newLimit . 'M');

            return $newLimit;
        } else {
            return $curLimit;
        }
    }

    public static function uploadImage($inFile, $outFile, $maxWidth = 0, $maxHeight = 0, $quality = 80)
    {
        $inExt = strtolower(array_pop(explode('.', $inFile)));
        $outExt = strtolower(array_pop(explode('.', $outFile)));

        list($imgWidth, $imgHeight) = @getimagesize($inFile);

        if ($imgWidth > $maxWidth || $imgHeight > $maxHeight) {
            if ($imgWidth >= $imgHeight) {
                $width = $maxWidth;
                $height = $imgHeight * ($maxWidth / $imgWidth);
                if ($height > $maxHeight) {
                    $width = $imgWidth * ($maxHeight / $imgHeight);
                    $height = $maxHeight;
                }
            } else {
                $width = $img_width * ($maxHeight / $imgHeight);
                $height = $maxHeight;
                if ($width > $maxWidth) {
                    $width = $maxWidth;
                    $height = $imgHeight * ($maxWidth / $imgWidth);
                }
            }
        } else {
            $width = $imgWidth;
            $height = $imgHeight;
        }

        $width = round($width);
        $height = round($height);

        $frame = @imagecreatetruecolor($width, $height);

        $newMemoryLimit = self::setMemoryForImage($inFile, 2);

        if ($inExt == 'gif') {
            $fileImage = @imagecreatefromgif($inFile);
            if (!$fileImage) {
                return false;
            }
        } elseif ($inExt == 'jpg' || $inExt == 'jpeg') {
            $fileImage = @imagecreatefromjpeg($inFile);
            if (!$fileImage) {
                return false;
            }
        } elseif ($inExt == 'png') {
            $fileImage = @imagecreatefrompng($inFile);
            if (!$fileImage) {
                return false;
            }
        } else {
            return false;
        }

        @imagecopyresampled($frame, $fileImage, 0, 0, 0, 0, $width, $height, $imgWidth, $imgHeight);

        if ($outExt == 'gif') {
            @imagegif($frame, $outFile);
        } elseif ($outExt == 'jpg' || $outExt == 'jpeg') {
            @imagejpeg($frame, $outFile, $quality);
        } elseif ($outExt == 'png') {
            @imagepng($frame, $outFile);
        } else {
            return false;
        }

        @imagedestroy($frame);
        @imagedestroy($fileImage);

        return true;
    }

    public static function uploadImageFixed($inFile, $outFile, $fixedWidth, $fixedHeight, $quality = 80)
    {
        $inExt = strtolower(array_pop(explode('.', $inFile)));
        $outExt = strtolower(array_pop(explode('.', $outFile)));

        list($imgWidth, $imgHeight) = @getimagesize($inFile);

        $width = $imgHeight * ($fixedWidth / $fixedHeight);
        if ($width > $imgWidth) {
            $width = $imgWidth;
            $height = $imgWidth * ($fixedHeight / $fixedWidth);
        } else {
            $height = $imgHeight;
        }

        $width = round($width);
        $height = round($height);

        $frame = @imagecreatetruecolor($fixedWidth, $fixedHeight);

        $newMemoryLimit = self::setMemoryForImage($inFile, 2);

        if ($inExt == 'gif') {
            $fileImage = @imagecreatefromgif($inFile);
            if (!$fileImage) {
                return false;
            }
        } elseif ($inExt == 'jpg' || $inExt == 'jpeg') {
            $fileImage = @imagecreatefromjpeg($inFile);
            if (!$fileImage) {
                return false;
            }
        } elseif ($inExt == 'png') {
            $fileImage = @imagecreatefrompng($inFile);
            if (!$fileImage) {
                return false;
            }
        } else {
            return false;
        }

        @imagecopy($frame, $fileImage, 0, 0, 0, 0, $width, $height);
        @imagecopyresampled($frame, $fileImage, 0, 0, 0, 0, $fixedWidth, $fixedHeight, $width, $height);

        if ($outExt == 'gif') {
            @imagegif($frame, $outFile);
        } elseif ($outExt == 'jpg' || $outExt == 'jpeg') {
            @imagejpeg($frame, $outFile, $quality);
        } elseif ($outExt == 'png') {
            @imagepng($frame, $outFile);
        } else {
            return false;
        }

        @imagedestroy($frame);
        @imagedestroy($fileImage);

        return true;
    }

    public static function sendEmail($template, $emailto, $emailfrom, $data, $subject = '', $attachments = null)
    {
        $config = self::getDI()->config;
        $response = self::getDI()->response;

        if ($emailfrom == '') {
            return true;
        }

        $emailsTo = explode(',', $emailto);

        $response->setParam('email', $config->app->email);
        foreach ($data as $key => $val) {
            $response->setParam($key, $data[$key]);
        }
        $content = trim($response->fetch('emails/' . $template));

        $mail = new PHPMailer();

        $mail->setFrom($emailfrom, $config->app->site_name);
        $mail->Subject = $subject;
        $mail->Body = $content;
        $mail->AltBody = $mail->html2text($content);
        $mail->ContentType = 'Content-type: text/html; charset=utf-8';
        $mail->CharSet = 'utf-8';
        $mail->isHTML(true);

        foreach ($emailsTo as $email) {
            $mail->addAddress($email);
        }
        if ($config->app->email_bcc != '') {
            $mail->addBCC($config->app->email_bcc);
        }

        if (isset($attachments)) {
            foreach ($attachments as $path) {
                $mail->addAttachment($path);
            }
        }

        $result = $mail->send();

        if ($mail->isError()) {
            throw new AppException('Email Exception: ' . $mail->ErrorInfo);
        }

        return $result;
    }

    public static function generateHashCode($long = 8)
    {
        // 35 chars -> 8 times = 2.251.875.390.625 different = 4,44e-13% chance
        $chars = ['1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];

        srand((float) microtime() * 1000000);

        $string = '';
        for ($i = 0; $i < $long; $i++) {
            $index = rand() % (count($chars) - 1);
            $string .= $chars[$index];
        }

        $data =
        [
            'string' => $string,
            'md5' => md5($string)
        ];

        return $data;
    }

    public static function getWeightedRandKey($weights)
    {
        $sum = array_sum($weights);

        srand((float) microtime() * 1000000);
        $r = rand() % 1000;

        $offset = 0;
        foreach ($weights as $k => $v) {
            $offset += ($v / $sum * 1000);
            if ($r <= $offset) {
                return $k;
            }
        }
    }

    public static function getStringCode($long = 8)
    {
        $code = self::generateHashCode($long);

        return $code['string'];
    }

    public static function parseCsvFile($file, $del = ',', $headers = true)
    {
        $data = [];

        if (($fh = fopen($file, 'r')) !== false) {
            if ($headers && ($line = fgetcsv($fh, 0, $del)) !== false) {
                $headers = $line;
            } else {
                $headers = [];
            }

            while (($line = fgetcsv($fh, 0, $del)) !== false) {
                $item = [];
                for ($i = 0; $i < count($line); $i++) {
                    if (!empty($headers)) {
                        $item[$headers[$i]] = $line[$i];
                    } else {
                        $item[$i] = $line[$i];
                    }
                }

                $data[] = $item;
            }

            fclose($fh);
        }

        return $data;
    }

    public static function getPasswordHash($password, $workFactor = null)
    {
        if (is_null($workFactor) || !is_int($workFactor)) {
            $workFactor = 8;
        }

        $factor = sprintf('%02s', $workFactor);
        $saltBytes = self::getSaltBytes();
        $salt = '$2a$' . $factor . '$' . $saltBytes;

        $hash = crypt($password, $salt);

        return $hash;
    }

    public static function getSaltBytes()
    {
        $numberBytes = 16;

        $safeBytes = '';
        while (strlen($safeBytes) < 22) {
            $randomBytes = openssl_random_pseudo_bytes($numberBytes);

            $base64bytes = base64_encode($randomBytes);
            $safeBytes = self::filterAlnum($base64bytes);

            if (empty($safeBytes)) {
                continue;
            }
        }

        return $safeBytes;
    }

    public static function filterAlnum($value)
    {
        $filtered = '';
        $value = (string) $value;
        $valueL = strlen($value);
        $zeroChar = chr(0);

        for ($i = 0; $i < $valueL; $i++) {
            if ($value[$i] == $zeroChar) {
                break;
            }

            if (ctype_alnum($value[$i]) === true) {
                $filtered .= $value[$i];
            }
        }

        return $filtered;
    }

    public static function encodeSearchLink($string)
    {
        $string = strtolower($string);
        $string = preg_replace('/\s+/', '-', $string);

        return $string;
    }

    public static function encodeSearchLinkCloud($string)
    {
        $string = self::removeVowelAccents($string);
        $string = strtolower($string);
        $string = preg_replace('/\s+/', '-', $string);

        return $string;
    }

    public static function decodeSearchLink($string)
    {
        $string = strtolower($string);
        $string = str_replace('-', ' ', $string);

        return $string;
    }

    public static function ucFirst($string)
    {
        $string = strtoupper(substr($string, 0, 1)) . substr($string, 1);

        return $string;
    }

    public static function lcFirst($string)
    {
        $string = strtolower(substr($string, 0, 1)) . substr($string, 1);

        return $string;
    }

    public static function sanitizeString($string)
    {
        $string = preg_replace_callback(
            '/[.!?] .*?\w/',
            function ($matches) {
                return mb_strtoupper($matches[0]);
            },
            ucfirst(mb_strtolower(trim($string)))
        );

        return $string;
    }

    public static function sanitizeName($string)
    {
        $string = ucwords(mb_strtolower(trim($string)));

        return $string;
    }

    public static function makeLink($string)
    {
        $string = self::cleanString($string);

        $string = strtolower($string);
        $string = preg_replace('/\s+/', '-', $string);
        $string = preg_replace('|-+|', '-', $string);
        $string = trim($string, '-');

        return $string;
    }

    public static function cleanString($string)
    {
        $string = strip_tags($string);

        // Preserve escaped octets.
        $string = preg_replace('|%([a-fA-F0-9][a-fA-F0-9])|', '---$1---', $string);
        // Remove percent signs and questions mark that are not part of an octet.
        $string = str_replace('%', '', $string);
        $string = str_replace('¿', '', $string);
        $string = str_replace('º', '', $string);
        $string = str_replace('€', '', $string);
        // Restore octets.
        $string = preg_replace('|---([a-fA-F0-9][a-fA-F0-9])---|', '%$1', $string);

        if (self::seemsUtf8($string)) {
            $string = utf8_decode($string);
        }
        $string = self::removeAccents($string);

        $string = preg_replace('/&.+?;/', '', $string); // Kill entities
        $string = preg_replace('/[^%A-Za-z0-9 _-]/', '', $string);
        $string = trim($string);

        return $string;
    }

    public static function decodeString($string)
    {
        $string = strip_tags($string);

        if (self::seemsUtf8($string)) {
            $string = utf8_decode($string);
        }

        $string = trim($string);

        return $string;
    }

    public static function ensureUtf8($mixed)
    {
        if (is_array($mixed)) {
            foreach ($mixed as $key => $value) {
                $mixed[$key] = self::ensureUtf8($value);
            }
        } elseif (is_string($mixed) && preg_match('/[\x80-\xff]/', $mixed) && !mb_check_encoding($mixed, 'UTF-8')) {
            if (mb_check_encoding($mixed, 'ISO-8859-1')) {
                $mixed = mb_convert_encoding($mixed, 'UTF-8', 'ISO-8859-1');
            } else {
                $mixed = utf8_encode($mixed);
            }
        }

        return $mixed;
    }

    protected static function removeAccents($string)
    {
        if (!preg_match('/[\x80-\xff]/', $string)) {
            return $string;
        }

        if (self::seemsUtf8($string)) {
            $chars =
            [
                // Decompositions for Latin-1 Supplement
                chr(195) . chr(128) => 'A', chr(195) . chr(129) => 'A',
                chr(195) . chr(130) => 'A', chr(195) . chr(131) => 'A',
                chr(195) . chr(132) => 'A', chr(195) . chr(133) => 'A',
                chr(195) . chr(135) => 'C', chr(195) . chr(136) => 'E',
                chr(195) . chr(137) => 'E', chr(195) . chr(138) => 'E',
                chr(195) . chr(139) => 'E', chr(195) . chr(140) => 'I',
                chr(195) . chr(141) => 'I', chr(195) . chr(142) => 'I',
                chr(195) . chr(143) => 'I', chr(195) . chr(145) => 'N',
                chr(195) . chr(146) => 'O', chr(195) . chr(147) => 'O',
                chr(195) . chr(148) => 'O', chr(195) . chr(149) => 'O',
                chr(195) . chr(150) => 'O', chr(195) . chr(153) => 'U',
                chr(195) . chr(154) => 'U', chr(195) . chr(155) => 'U',
                chr(195) . chr(156) => 'U', chr(195) . chr(157) => 'Y',
                chr(195) . chr(159) => 's', chr(195) . chr(160) => 'a',
                chr(195) . chr(161) => 'a', chr(195) . chr(162) => 'a',
                chr(195) . chr(163) => 'a', chr(195) . chr(164) => 'a',
                chr(195) . chr(165) => 'a', chr(195) . chr(167) => 'c',
                chr(195) . chr(168) => 'e', chr(195) . chr(169) => 'e',
                chr(195) . chr(170) => 'e', chr(195) . chr(171) => 'e',
                chr(195) . chr(172) => 'i', chr(195) . chr(173) => 'i',
                chr(195) . chr(174) => 'i', chr(195) . chr(175) => 'i',
                chr(195) . chr(177) => 'n', chr(195) . chr(178) => 'o',
                chr(195) . chr(179) => 'o', chr(195) . chr(180) => 'o',
                chr(195) . chr(181) => 'o', chr(195) . chr(182) => 'o',
                chr(195) . chr(182) => 'o', chr(195) . chr(185) => 'u',
                chr(195) . chr(186) => 'u', chr(195) . chr(187) => 'u',
                chr(195) . chr(188) => 'u', chr(195) . chr(189) => 'y',
                chr(195) . chr(191) => 'y',
                // Decompositions for Latin Extended-A
                chr(196) . chr(128) => 'A', chr(196) . chr(129) => 'a',
                chr(196) . chr(130) => 'A', chr(196) . chr(131) => 'a',
                chr(196) . chr(132) => 'A', chr(196) . chr(133) => 'a',
                chr(196) . chr(134) => 'C', chr(196) . chr(135) => 'c',
                chr(196) . chr(136) => 'C', chr(196) . chr(137) => 'c',
                chr(196) . chr(138) => 'C', chr(196) . chr(139) => 'c',
                chr(196) . chr(140) => 'C', chr(196) . chr(141) => 'c',
                chr(196) . chr(142) => 'D', chr(196) . chr(143) => 'd',
                chr(196) . chr(144) => 'D', chr(196) . chr(145) => 'd',
                chr(196) . chr(146) => 'E', chr(196) . chr(147) => 'e',
                chr(196) . chr(148) => 'E', chr(196) . chr(149) => 'e',
                chr(196) . chr(150) => 'E', chr(196) . chr(151) => 'e',
                chr(196) . chr(152) => 'E', chr(196) . chr(153) => 'e',
                chr(196) . chr(154) => 'E', chr(196) . chr(155) => 'e',
                chr(196) . chr(156) => 'G', chr(196) . chr(157) => 'g',
                chr(196) . chr(158) => 'G', chr(196) . chr(159) => 'g',
                chr(196) . chr(160) => 'G', chr(196) . chr(161) => 'g',
                chr(196) . chr(162) => 'G', chr(196) . chr(163) => 'g',
                chr(196) . chr(164) => 'H', chr(196) . chr(165) => 'h',
                chr(196) . chr(166) => 'H', chr(196) . chr(167) => 'h',
                chr(196) . chr(168) => 'I', chr(196) . chr(169) => 'i',
                chr(196) . chr(170) => 'I', chr(196) . chr(171) => 'i',
                chr(196) . chr(172) => 'I', chr(196) . chr(173) => 'i',
                chr(196) . chr(174) => 'I', chr(196) . chr(175) => 'i',
                chr(196) . chr(176) => 'I', chr(196) . chr(177) => 'i',
                chr(196) . chr(178) => 'IJ', chr(196) . chr(179) => 'ij',
                chr(196) . chr(180) => 'J', chr(196) . chr(181) => 'j',
                chr(196) . chr(182) => 'K', chr(196) . chr(183) => 'k',
                chr(196) . chr(184) => 'k', chr(196) . chr(185) => 'L',
                chr(196) . chr(186) => 'l', chr(196) . chr(187) => 'L',
                chr(196) . chr(188) => 'l', chr(196) . chr(189) => 'L',
                chr(196) . chr(190) => 'l', chr(196) . chr(191) => 'L',
                chr(197) . chr(128) => 'l', chr(197) . chr(129) => 'L',
                chr(197) . chr(130) => 'l', chr(197) . chr(131) => 'N',
                chr(197) . chr(132) => 'n', chr(197) . chr(133) => 'N',
                chr(197) . chr(134) => 'n', chr(197) . chr(135) => 'N',
                chr(197) . chr(136) => 'n', chr(197) . chr(137) => 'N',
                chr(197) . chr(138) => 'n', chr(197) . chr(139) => 'N',
                chr(197) . chr(140) => 'O', chr(197) . chr(141) => 'o',
                chr(197) . chr(142) => 'O', chr(197) . chr(143) => 'o',
                chr(197) . chr(144) => 'O', chr(197) . chr(145) => 'o',
                chr(197) . chr(146) => 'OE', chr(197) . chr(147) => 'oe',
                chr(197) . chr(148) => 'R', chr(197) . chr(149) => 'r',
                chr(197) . chr(150) => 'R', chr(197) . chr(151) => 'r',
                chr(197) . chr(152) => 'R', chr(197) . chr(153) => 'r',
                chr(197) . chr(154) => 'S', chr(197) . chr(155) => 's',
                chr(197) . chr(156) => 'S', chr(197) . chr(157) => 's',
                chr(197) . chr(158) => 'S', chr(197) . chr(159) => 's',
                chr(197) . chr(160) => 'S', chr(197) . chr(161) => 's',
                chr(197) . chr(162) => 'T', chr(197) . chr(163) => 't',
                chr(197) . chr(164) => 'T', chr(197) . chr(165) => 't',
                chr(197) . chr(166) => 'T', chr(197) . chr(167) => 't',
                chr(197) . chr(168) => 'U', chr(197) . chr(169) => 'u',
                chr(197) . chr(170) => 'U', chr(197) . chr(171) => 'u',
                chr(197) . chr(172) => 'U', chr(197) . chr(173) => 'u',
                chr(197) . chr(174) => 'U', chr(197) . chr(175) => 'u',
                chr(197) . chr(176) => 'U', chr(197) . chr(177) => 'u',
                chr(197) . chr(178) => 'U', chr(197) . chr(179) => 'u',
                chr(197) . chr(180) => 'W', chr(197) . chr(181) => 'w',
                chr(197) . chr(182) => 'Y', chr(197) . chr(183) => 'y',
                chr(197) . chr(184) => 'Y', chr(197) . chr(185) => 'Z',
                chr(197) . chr(186) => 'z', chr(197) . chr(187) => 'Z',
                chr(197) . chr(188) => 'z', chr(197) . chr(189) => 'Z',
                chr(197) . chr(190) => 'z', chr(197) . chr(191) => 's',
                // Euro Sign
                chr(226) . chr(130) . chr(172) => 'E',
                // GBP (Pound) Sign
                chr(194) . chr(163) => ''
            ];

            $string = strtr($string, $chars);
        } else {
            // Assume ISO-8859-1 if not UTF-8
            $chars['in'] = chr(128) . chr(131) . chr(138) . chr(142) . chr(154) . chr(158)
                . chr(159) . chr(162) . chr(165) . chr(181) . chr(192) . chr(193) . chr(194)
                . chr(195) . chr(196) . chr(197) . chr(199) . chr(200) . chr(201) . chr(202)
                . chr(203) . chr(204) . chr(205) . chr(206) . chr(207) . chr(209) . chr(210)
                . chr(211) . chr(212) . chr(213) . chr(214) . chr(216) . chr(217) . chr(218)
                . chr(219) . chr(220) . chr(221) . chr(224) . chr(225) . chr(226) . chr(227)
                . chr(228) . chr(229) . chr(231) . chr(232) . chr(233) . chr(234) . chr(235)
                . chr(236) . chr(237) . chr(238) . chr(239) . chr(241) . chr(242) . chr(243)
                . chr(244) . chr(245) . chr(246) . chr(248) . chr(249) . chr(250) . chr(251)
                . chr(252) . chr(253) . chr(255);

            $chars['out'] = 'EfSZszYcYuAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy';

            $string = strtr($string, $chars['in'], $chars['out']);
            $double_chars['in'] = [chr(140), chr(156), chr(198), chr(208), chr(222), chr(223), chr(230), chr(240), chr(254)];
            $double_chars['out'] = ['OE', 'oe', 'AE', 'DH', 'TH', 'ss', 'ae', 'dh', 'th'];
            $string = str_replace($double_chars['in'], $double_chars['out'], $string);
        }

        return $string;
    }

    protected static function removeVowelAccents($string)
    {
        if (self::seemsUtf8($string)) {
            $chars =
            [
                // Decompositions for Latin-1 Supplement
                chr(195) . chr(128) => 'A', chr(195) . chr(129) => 'A',
                chr(195) . chr(130) => 'A', chr(195) . chr(131) => 'A',
                chr(195) . chr(132) => 'A', chr(195) . chr(133) => 'A',
                chr(195) . chr(136) => 'E',
                chr(195) . chr(137) => 'E', chr(195) . chr(138) => 'E',
                chr(195) . chr(139) => 'E', chr(195) . chr(140) => 'I',
                chr(195) . chr(141) => 'I', chr(195) . chr(142) => 'I',
                chr(195) . chr(143) => 'I',
                chr(195) . chr(146) => 'O', chr(195) . chr(147) => 'O',
                chr(195) . chr(148) => 'O', chr(195) . chr(149) => 'O',
                chr(195) . chr(150) => 'O', chr(195) . chr(153) => 'U',
                chr(195) . chr(154) => 'U', chr(195) . chr(155) => 'U',
                chr(195) . chr(156) => 'U',
                chr(195) . chr(160) => 'a',
                chr(195) . chr(161) => 'a', chr(195) . chr(162) => 'a',
                chr(195) . chr(163) => 'a', chr(195) . chr(164) => 'a',
                chr(195) . chr(165) => 'a', chr(195) . chr(167) => 'c',
                chr(195) . chr(168) => 'e', chr(195) . chr(169) => 'e',
                chr(195) . chr(170) => 'e', chr(195) . chr(171) => 'e',
                chr(195) . chr(172) => 'i', chr(195) . chr(173) => 'i',
                chr(195) . chr(174) => 'i', chr(195) . chr(175) => 'i',
                chr(195) . chr(178) => 'o',
                chr(195) . chr(179) => 'o', chr(195) . chr(180) => 'o',
                chr(195) . chr(181) => 'o', chr(195) . chr(182) => 'o',
                chr(195) . chr(182) => 'o', chr(195) . chr(185) => 'u',
                chr(195) . chr(186) => 'u', chr(195) . chr(187) => 'u',
                chr(195) . chr(188) => 'u',
                // Decompositions for Latin Extended-A
                chr(196) . chr(128) => 'A', chr(196) . chr(129) => 'a',
                chr(196) . chr(130) => 'A', chr(196) . chr(131) => 'a',
                chr(196) . chr(132) => 'A', chr(196) . chr(133) => 'a',
                chr(196) . chr(146) => 'E', chr(196) . chr(147) => 'e',
                chr(196) . chr(148) => 'E', chr(196) . chr(149) => 'e',
                chr(196) . chr(150) => 'E', chr(196) . chr(151) => 'e',
                chr(196) . chr(152) => 'E', chr(196) . chr(153) => 'e',
                chr(196) . chr(154) => 'E', chr(196) . chr(155) => 'e',
                chr(196) . chr(168) => 'I', chr(196) . chr(169) => 'i',
                chr(196) . chr(170) => 'I', chr(196) . chr(171) => 'i',
                chr(196) . chr(172) => 'I', chr(196) . chr(173) => 'i',
                chr(196) . chr(174) => 'I', chr(196) . chr(175) => 'i',
                chr(196) . chr(176) => 'I', chr(196) . chr(177) => 'i',
                chr(197) . chr(140) => 'O', chr(197) . chr(141) => 'o',
                chr(197) . chr(142) => 'O', chr(197) . chr(143) => 'o',
                chr(197) . chr(144) => 'O', chr(197) . chr(145) => 'o',
                chr(197) . chr(168) => 'U', chr(197) . chr(169) => 'u',
                chr(197) . chr(170) => 'U', chr(197) . chr(171) => 'u',
                chr(197) . chr(172) => 'U', chr(197) . chr(173) => 'u',
                chr(197) . chr(174) => 'U', chr(197) . chr(175) => 'u',
                chr(197) . chr(176) => 'U', chr(197) . chr(177) => 'u',
                chr(197) . chr(178) => 'U', chr(197) . chr(179) => 'u'
            ];

            $string = strtr($string, $chars);
        } else {
            // Assume ISO-8859-1 if not UTF-8
            $chars['in'] = chr(128)
                . chr(181) . chr(192) . chr(193) . chr(194)
                . chr(195) . chr(196) . chr(197) . chr(200) . chr(201) . chr(202)
                . chr(203) . chr(204) . chr(205) . chr(206) . chr(207) . chr(210)
                . chr(211) . chr(212) . chr(213) . chr(214) . chr(216) . chr(217) . chr(218)
                . chr(219) . chr(220) . chr(224) . chr(225) . chr(226) . chr(227)
                . chr(228) . chr(232) . chr(233) . chr(234) . chr(235)
                . chr(236) . chr(237) . chr(238) . chr(239) . chr(241) . chr(242) . chr(243)
                . chr(244) . chr(245) . chr(246) . chr(248) . chr(249) . chr(250) . chr(251)
                . chr(252);

            $chars['out'] = 'EuAAAAAAEEEEIIIIOOOOOOUUUUaaaaaaeeeeiiiinoooooouuuu';

            $string = strtr($string, $chars['in'], $chars['out']);
        }

        return $string;
    }

    protected static function seemsUtf8($string)
    {
        // by bmorel at ssi dot fr
        $length = strlen($string);
        for ($i = 0; $i < $length; $i++) {
            if (ord($string[$i]) < 0x80) {
                continue;
            } // 0bbbbbbb
            elseif ((ord($string[$i]) & 0xE0) == 0xC0) {
                $n = 1;
            } // 110bbbbb
            elseif ((ord($string[$i]) & 0xF0) == 0xE0) {
                $n = 2;
            } // 1110bbbb
            elseif ((ord($string[$i]) & 0xF8) == 0xF0) {
                $n = 3;
            } // 11110bbb
            elseif ((ord($string[$i]) & 0xFC) == 0xF8) {
                $n = 4;
            } // 111110bb
            elseif ((ord($string[$i]) & 0xFE) == 0xFC) {
                $n = 5;
            } // 1111110b
            else {
                return false;
            } // Does not match any model
            for ($j = 0; $j < $n; $j++) { // n bytes matching 10bbbbbb follow ?
                if ((++$i == $length) || ((ord($string[$i]) & 0xC0) != 0x80)) {
                    return false;
                }
            }
        }

        return true;
    }

    protected static function utf8UriEncode($utf8_string, $length = 0)
    {
        $unicode = '';
        $values = [];
        $num_octets = 1;
        $unicode_length = 0;

        $string_length = strlen($utf8_string);
        for ($i = 0; $i < $string_length; $i++) {
            $value = ord($utf8_string[$i]);

            if ($value < 128) {
                if ($length && ($unicode_length >= $length)) {
                    break;
                }
                $unicode .= chr($value);
                $unicode_length++;
            } else {
                if (count($values) == 0) {
                    $num_octets = ($value < 224) ? 2 : 3;
                }

                $values[] = $value;

                if ($length && ($unicode_length + ($num_octets * 3)) > $length) {
                    break;
                }
                if (count($values) == $num_octets) {
                    if ($num_octets == 3) {
                        $unicode .= '%' . dechex($values[0]) . '%' . dechex($values[1]) . '%' . dechex($values[2]);
                        $unicode_length += 9;
                    } else {
                        $unicode .= '%' . dechex($values[0]) . '%' . dechex($values[1]);
                        $unicode_length += 6;
                    }

                    $values = [];
                    $num_octets = 1;
                }
            }
        }

        return $unicode;
    }
}
