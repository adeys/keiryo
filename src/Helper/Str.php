<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 13/07/2019
 * Time: 18:56
 */

namespace Keiryo\Helper;

class Str
{

    /**
     * @param string $string
     * @return string
     */
    public static function slugify(string $string): string
    {
        $string = preg_replace('~\W+~', '-', $string);
        $string = trim($string, '-');

        return strtolower($string);
    }

    /**
     * @param int $length
     * @return string
     * @throws \Exception
     */
    public static function random(int $length = 20): string
    {
        $string = '';

        while (($len = strlen($string)) < $length) {
            $size = $length - $len;
            $bytes = random_bytes($size);
            $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
        }

        return $string;
    }
}
